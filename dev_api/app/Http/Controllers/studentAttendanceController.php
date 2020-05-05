<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models2\MClass;
use App\Models2\Section;
use App\Models2\Main;
use App\Models2\User;
use App\Models2\Attendance;
use App\Models2\HR\HolidayDetails;
use App\Models2\HR\WeeklyHoliday;
use Carbon\Carbon;

class studentAttendanceController extends Controller
{
    var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';
	public function __construct(){
		if( app('request')->header('Authorization') != "" || \Input::has('token')) { $this->middleware('jwt.auth'); }
        else { $this->middleware('authApplication'); }

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['users'] = $this->panelInit->getAuthUser();
		if( !isset( $this->data['users']->id ) ) { return \Redirect::to('/'); }
    }

    public function preLoad()
    {
        if( !$this->panelInit->can( array( "Attendance.takeAttendance", "Attendance.attReport" ) ) ) { return $this->panelInit->apiOutput( false, "get Attendance", "You don't have permission to view Attendance" ); }
        $classesArray = array();
        if( $this->data['users']->role == "teacher" )
		{
			$class_ids = Main::getClassesIdsByTeacherId($this->data['users']->id);
			$classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->whereIn('id', $class_ids)->get()->toArray();
        } else { $classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->get()->toArray(); }
		foreach( $classes as $class )
		{
			$class_id = $class['id']; $name = $class['className'];
			$sections = Section::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
			$classesArray[ $class_id ] = [ 'id' => $class_id, 'name' => $name, 'sections' => $sections ];
		}
        $toReturn['classes'] = $classesArray;
        return $toReturn;
    }
    
    public function getAttendance()
    {
        if( !$this->panelInit->can( array( "Attendance.takeAttendance", "Attendance.attReport" ) ) ) { return $this->panelInit->apiOutput( false, "get Attendance", "You don't have permission to view Attendance" ); }

        // time range section ----------------------------------------
            if( \Input::has('startDate') && \Input::has('endDate') )
            {
                $startDate = \Input::get('startDate'); $endDate = \Input::get('endDate');
                $startDate = formatDate( $startDate );
                if( !$startDate ) { return $this->panelInit->apiOutput( false, "Attendance report", "Start date has invalid format" ); }
                if( !toUnixStamp( $startDate ) ) { return $this->panelInit->apiOutput( false, "Attendance report", "Start date has invalid format" ); }
                $endDate = formatDate( $endDate );
                if( !$endDate ) { return $this->panelInit->apiOutput( false, "Attendance report", "End date has invalid format" ); }
                if( !toUnixStamp( $endDate ) ) { return $this->panelInit->apiOutput( false, "Attendance report", "End date has invalid format" ); }
                $timeRange = getDatesBetweenDate( $startDate, $endDate );
            }
            else
            {
                if( \Input::has('month') )
                {
                    $date = "01/" . \Input::get('month');
                    $date = formatDate( $date );
                    if( !$date ) $date = date('Y-m-d');
                    if( !toUnixStamp( $date ) ) $date = date('Y-m-d');
                } else $date = date('Y-m-d');
                if( \Input::has('timeStep') )
                {
                    $timeStep = \Input::get('timeStep');
                    if( $timeStep == 'backward' )
                    {
                        $parse = Carbon::parse($date);
                        $date = $parse->subMonth()->format('Y-m-d');
                    }
                    elseif( $timeStep == 'forward' )
                    {
                        $parse = Carbon::parse($date);
                        $date = $parse->addMonth()->format('Y-m-d');
                    }
                }
                $timeRange = generateTimeRange( $date );
            }
            
            $start = $timeRange[0]['date']; $last = end( $timeRange )['date'];
            $allowableDays = []; $weeklyDays = [];
            $publicHolidays = HolidayDetails::where('from_date', '>=', $start);
            $publicHolidays = $publicHolidays->orwhere('to_date', '<=', $last);
            $publicHolidays = $publicHolidays->get();
            foreach( $publicHolidays as $holiday )
            {
                $mainHoliday = $holiday->holiday ? $holiday->holiday->holiday_name : '';
                $range = getDatesBetweenDate( $holiday->from_date, $holiday->to_date );
                foreach( $range as $day )
                {
                    if( !in_array( $day['date'], $allowableDays ) )
                    {
                        $allowableDays[ $day['date'] ] = [
                            'day' => $day['date'],
                            'comment' => $holiday->comment,
                            'name' => $mainHoliday
                        ];
                    }
                }
            }
            $weeklyHolidays = WeeklyHoliday::select('week_holiday_id as id', 'day_name as name', 'status')->where('status', '1')->get();
            foreach( $weeklyHolidays as $week )
            {
                $name = strtolower( $week->name );
                $weeklyDays[] = getDayName( $name );
            }
        // End of time range section ---------------------------------

        $currentRole = $this->data['users']->role;
        $highlighter = [];
        User::$withoutAppends = true;
        if( $currentRole == "parent" )
        {
            $parentId = $this->data['users']->id;
            $class_id = Main::getClassesIdsByParentId($parentId);
            $section_id = Main::getSectionIdsByParentId($parentId);
        }
        else
        {
            $class_id = \Input::get('class_id');
            $section_id = \Input::get('section_id');
        }
        $students = User::select('id', 'fullName as name', 'admission_number as num');
        if( $currentRole == "parent" )
        {
            $studentsIds = User::getStudentsIdsFromParentId($parentId);
            $students = $students->where('role', 'student')->whereIn('id', $studentsIds);
        }
        else
            $students = $students->where('role', 'student')->where('studentClass', $class_id)->where('studentSection', $section_id);
        $studentIds = $students->pluck('id');
        $students = $students->get()->toArray();

        $attendance = Attendance::select('id', 'status', 'date', 'attNotes as notes', 'studentId as student');
        $attendance = $attendance->where('date', '>=', strtotime( $start . " 00:00:00" ));
        $attendance = $attendance->where('date', '<=', strtotime( $last . " 23:59:59" ));
        $attendance = $attendance->whereIn('studentId', $studentIds);
        $attendance = $attendance->get()->toArray();
        $attendanceList = [];
        foreach( $attendance as $item )
        {
            $studentId = $item['student']; $rowId = $item['id'];
            $status = intval( $item['status'] );
            $day = date('Y-m-d', $item['date']);
            $notes = $item['notes'];
            $attendanceList[$studentId]["$day"] = [ 'id' => $rowId, 'student' => $studentId, 'status' => $status, 'date' => $day, 'notes' => $notes ];
        }
        $mixedList = []; $index = 0;
        foreach( $students as $student )
        {
            $studentId = $student['id']; $attenData = [];
            $mixedList[$index]['id'] = $student['id'];
            $mixedList[$index]['num'] = $student['num'];
            $mixedList[$index]['name'] = $student['name'];
            if( array_key_exists( $studentId, $attendanceList ) )
            {
                foreach( $timeRange as $singleDay )
                {
                    $weekDay = $singleDay['day'];
                    $dateDay = $singleDay['date'];
                    if( in_array( $weekDay, $weeklyDays ) )
                    {
                        $isAllowed = true;
                        $allowanceData = ['type' => 'Weekly', 'name' => $weekDay, 'comment' => ''];
                    }
                    elseif( array_key_exists( $dateDay, $allowableDays ) )
                    {
                        $isAllowed = true;
                        $s = $allowableDays[$dateDay];
                        $allowanceData = ['type' => 'Holiday', 'name' => $s['name'], 'comment' => $s['comment']];
                    }
                    else
                    {
                        $isAllowed = false;
                        $allowanceData = ['type' => '', 'name' => '', 'comment' => ''];
                    }
                    if( array_key_exists( $dateDay , $attendanceList[$studentId] ) )
                    {
                        $attenData[ $dateDay ] = [
                            'id' => $attendanceList[$studentId][$dateDay]['id'],
                            'status' => $attendanceList[$studentId][$dateDay]['status'],
                            'notes' => $attendanceList[$studentId][$dateDay]['notes'],
                            'date' => $dateDay,
                            'day' => $weekDay,
                            'isExist' => true,
                            'isAllowed' => $isAllowed,
                            'allowanceData' => $allowanceData
                        ];
                    }
                    else
                    {
                        $attenData[ $dateDay ] = [
                            'id' => '',
                            'status' => '',
                            'date' => $dateDay,
                            'day' => $weekDay,
                            'notes' => "",
                            'isExist' => false,
                            'isAllowed' => $isAllowed,
                            'allowanceData' => $allowanceData
                        ];
                    }
                }
                $mixedList[$index]['attendance'] = $attenData;
            }
            else
            {
                foreach( $timeRange as $singleDay )
                {
                    $weekDay = $singleDay['day'];
                    $dateDay = $singleDay['date'];
                    if( in_array( $weekDay, $weeklyDays ) )
                    {
                        $isAllowed = true;
                        $allowanceData = ['type' => 'Weekly', 'name' => $weekDay, 'comment' => ''];
                    }
                    elseif( array_key_exists( $dateDay, $allowableDays ) )
                    {
                        $isAllowed = true;
                        $s = $allowableDays[$dateDay];
                        $allowanceData = ['type' => 'Holiday', 'name' => $s['name'], 'comment' => $s['comment']];
                    }
                    else
                    {
                        $isAllowed = false;
                        $allowanceData = ['type' => '', 'name' => '', 'comment' => ''];
                    }
                    $attenData[ $dateDay ] = [
                        'id' => '',
                        'status' => '',
                        'date' => $dateDay,
                        'day' => $weekDay,
                        'notes' => "",
                        'isExist' => false,
                        'isAllowed' => $isAllowed,
                        'allowanceData' => $allowanceData
                    ];
                }
                $mixedList[$index]['attendance'] = $attenData;
            }
            $index++;
        }
        foreach( $timeRange as $indexed => $singleDay )
        {
            $weekDay = $singleDay['day']; $dateDay = $singleDay['date'];
            if( in_array( $weekDay, $weeklyDays ) )
            {
                $isAllowed = true;
                $allowanceData = ['type' => 'Weekly', 'name' => $weekDay, 'comment' => ''];
            }
            elseif( array_key_exists( $dateDay, $allowableDays ) )
            {
                $isAllowed = true;
                $s = $allowableDays[$dateDay];
                $allowanceData = ['type' => 'Holiday', 'name' => $s['name'], 'comment' => $s['comment']];
            }
            else
            {
                $isAllowed = false;
                $allowanceData = ['type' => '', 'name' => '', 'comment' => ''];
            }
            if( $isAllowed == false )
            {
                $highDay = date('jS M Y', strtotime( $dateDay ) );
                if( !array_key_exists( $dateDay, $highlighter) ) { $highlighter[$dateDay] = ['date' => $dateDay, 'highlight' => $highDay]; }
            }
            $timeRange[$indexed]['isAllowed'] = $isAllowed;
            $timeRange[$indexed]['allowanceData'] = $allowanceData;
        }
        if( \Input::has('startDate') && \Input::has('endDate') )
        {
            foreach( $mixedList as $index => $listItem )
            {
                $totalPresent = 0;
                $totalAbsents = 0;
                $totalLateing = 0;
                $totalHalfDay = 0;
                $totalHoliday = 0;
                $totalNothing = 0;
                foreach( $listItem['attendance'] as $dateName => $data )
                {
                    if( $data['isAllowed'] ) { $totalHoliday = $totalHoliday + 1; continue; }
                    if( $data['status'] === "" ) { $totalNothing = $totalNothing + 1; continue; }
                    $status = intval( $data['status'] );
                    if( $status == 0 ) { $totalAbsents = $totalAbsents + 1; continue; }
                    if( $status == 1 ) { $totalPresent = $totalPresent + 1; continue; }
                    if( $status == 2 ) { $totalLateing = $totalLateing + 1; continue; }
                    if( $status == 3 ) { $totalHoliday = $totalHoliday + 1; continue; }
                }
                $mixedList[$index]['reports'] = [
                    'Present' => $totalPresent, 'Absents' => $totalAbsents, 'Lateing' => $totalLateing,
                    'HalfDay' => $totalHalfDay, 'Holiday' => $totalHoliday, 'Nothing' => $totalNothing
                ];
            }
            foreach( $mixedList as $index => $listItem )
            {
                $reports = $listItem['reports'];
                $totalDays = $reports['Present'] + $reports['Lateing'] + $reports['HalfDay'] + $reports['Absents'];
                $greenDays = $reports['Present'] + $reports['Lateing'] + ( $reports['HalfDay'] * 0.5 );
                $presentPercntage = $totalDays == 0 ? 0 : ( ( $greenDays / $totalDays ) * 100 );
                $presentPercntage = round( $presentPercntage, 2 );
                $mixedList[$index]['reports']['percentage'] = $presentPercntage;
            }
        }

        $toReturn['status'] = "success";
        $toReturn['headers'] = $timeRange;
        $toReturn['attendance'] = $mixedList;
        
        if( !\Input::has('startDate') && !\Input::has('endDate') )
        {
            $outputName = date('F Y', strtotime( $date ) );
            $modalMonth = date('m/Y', strtotime( $date ) );
            $toReturn['month'] = $outputName;
            $toReturn['modalMonth'] = $modalMonth;
        }
        else
        {
            $toReturn['fromDate'] = date('Y-m-d', toUnixStamp( $startDate ));
            $toReturn['toDate'] = date('Y-m-d', toUnixStamp( $endDate ));
        }

        $currentDate = date("Y-m-d");
        $parse = explode("-", $currentDate);
        $yearToCompare = intval( $parse[0] );
        $monthToCompare = intval( $parse[1] );
        $dayToCompare = intval( $parse[2] );
        if( $monthToCompare < 10 ) $monthToCompare = "0" . $monthToCompare;
        if( $dayToCompare < 10 ) $dayToCompare = "0" . $dayToCompare;
        $dateToCompare = $yearToCompare . "-" . $monthToCompare . "-" . $dayToCompare;

        $toMarkPresent = [];
        $classId = intval( \Input::get('class_id') );
        foreach( $toReturn["attendance"] as $index => $payload )
        {
            foreach( $payload['attendance'] as $date => $payloadData )
            {
                if( $date == $dateToCompare )
                {
                    $studentId = $payload['id'];
                    if( $payloadData["isAllowed"] == true ) { continue; }
                    // if( intval( $payloadData["status"] ) === 0 ) { continue; }
                    if( intval( $payloadData["status"] ) === 1 ) { continue; }
                    if( intval( $payloadData["status"] ) === 2 ) { continue; }
                    if( intval( $payloadData["status"] ) === 3 ) { continue; }
                    else
                    {
                        if( $payloadData["id"] == "" || $payloadData["status"] == "" )
                        {
                            $toMarkPresent[] = [
                                "classId" => $classId,
                                "subjectId" => 0,
                                "date" => stampModifier( time() ),
                                "studentId" => $studentId,
                                "status" => 1,
                                "in_time" => "",
                                "out_time" => "",
                                "attNotes" => ""
                            ];
                            $toReturn["attendance"][$index]["attendance"][$date]["status"] = 1;
                        }
                    }
                } else continue;
            }
        }
        if( count( $toMarkPresent ) > 0 ) { Attendance::insert( $toMarkPresent ); }

        $toReturn['class_id'] = \Input::get('class_id');
        $toReturn['section_id'] = \Input::get('section_id');
        $toReturn['highlighter'] = $highlighter;
        $toReturn['isInDate'] = false;
        $toReturn['currentDate'] = $dateToCompare;
        if( array_key_exists($dateToCompare, $highlighter) ) { $toReturn['isInDate'] = true; }

        return $toReturn;
    }

    public function saveAttendance()
    {
        if( !$this->panelInit->can( array( "Attendance.takeAttendance" ) ) ) { return $this->panelInit->apiOutput( false, "take Attendance", "You don't have permission to register Attendance" ); }
        if( !\Input::has('studentId') ) { return $this->panelInit->apiOutput( false, "take Attendance", "Unable to read student data" ); }
        if( !\Input::has('studentAdmision') ) { return $this->panelInit->apiOutput( false, "take Attendance", "Unable to read student data" ); }
        if( !\Input::has('date') ) { return $this->panelInit->apiOutput( false, "take Attendance", "Unable to read attendance date" ); }
        if( !\Input::has('status') ) { return $this->panelInit->apiOutput( false, "take Attendance", "Unable to read attendance status data" ); }
        $date = \Input::get('date');
        $date = formatDate( $date );
        if( !$date ) { return $this->panelInit->apiOutput( false, "take Attendance", "Attendance date has invalid format" ); }
        $stamp = toUnixStamp( $date );
        if( !$stamp ) { return $this->panelInit->apiOutput( false, "take Attendance", "Attendance date has invalid format" ); }
        
        $studentId = \Input::get('studentId');
        $admision = \Input::get('studentAdmision');
        $status = intval( \Input::get('status') );
        $allowedStatus = [0, 1, 2, 3];
        if( !in_array( $status, $allowedStatus ) ) { return $this->panelInit->apiOutput( false, "take Attendance", "Invalid attendance status data provided" ); }
        User::$withoutAppends = true;
        $student = User::select('id', 'fullname as name', 'admission_number as num', 'studentClass as class', 'studentSection as section');
        $student = $student->where('role', 'student');
        $student = $student->where('id', $studentId);
        $student = $student->where('admission_number', $admision);
        $student = $student->first();
        if( !$student ) { return $this->panelInit->apiOutput( false, "take Attendance", "Unable to read student data" ); }
        $stampToSave = stampModifier( $stamp );
        $attendanceRow = Attendance::where('studentId', $studentId)->where('date', $stampToSave)->first();
        if( $attendanceRow )
        {
            $attendanceRow->status = $status;
            $attendanceRow->attNotes = \Input::get('notes');
            $attendanceRow->save();
        }
        else
        {
            $attendanceRow = new Attendance();
            $attendanceRow->classId = $student->class;
            $attendanceRow->subjectId = 0;
            $attendanceRow->date = $stampToSave;
            $attendanceRow->studentId = $studentId;
            $attendanceRow->status = $status;
            $attendanceRow->in_time = "";
            $attendanceRow->out_time = "";
            $attendanceRow->attNotes = \Input::get('notes');
            $attendanceRow->save();
        }
        return $this->panelInit->apiOutput( true, "take Attendance", "Attendance registered successfully" );
    }
}