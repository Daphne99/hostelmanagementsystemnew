<?php

namespace App\Http\Controllers;

use App\Models2\Main;
use App\Models2\User;
use App\Models2\MClass;
use App\Models2\Hostel;
use App\Models2\sections;
use App\Models2\StudentType;
use App\Models2\StudentCategory;
use App\Models2\exam_marks;
use App\Models2\ExamsList;
use App\Models2\transportation;
use App\Models2\transport_vehicles;
use App\Models2\Payment;
use App\Models2\SubSubject;
use App\Models2\Subject;
use App\Models2\StudentDoc;
use App\Models2\HR\HolidayDetails;
use App\Models2\HR\WeeklyHoliday;
use App\Models2\Attendance;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;

class manageStudentsController extends Controller
{
    var $data = array();
	var $panelInit ;
    var $layout = 'dashboard';
    
    public function __construct()
    {
		if(app('request')->header('Authorization') != "" || \Input::has('token')) $this->middleware('jwt.auth');
		else $this->middleware('authApplication');

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['users'] = $this->panelInit->getAuthUser();
        if(! isset( $this->data['users']->id ) ) return \Redirect::to('/');
        User::$withoutAppends = true;
    }

    public function preLoad()
    {
        $toReturn = array();
        $classesArray = MClass::select('id', 'className as name')->where('classAcademicYear', $this->panelInit->selectAcYear)->get()->toArray();
        $sections = sections::select('id', 'sectionName as name', 'classId')->get()->toArray();
        foreach( $sections as $oneSection )
        {
            $classId = $oneSection['classId'];
            $classes[$classId][] = $oneSection;
        }
        
        foreach( $classesArray as $index => $oneClass )
        {
            $classId = $oneClass['id'];
            $toReturn['classes'][$classId]['id'] = $oneClass['id'];
            $toReturn['classes'][$classId]['name'] = $oneClass['name'];
            $toReturn['classes'][$classId]['sections'] = array_key_exists($classId, $classes) ? $classes[$classId] : [];
        }
        $toReturn['types'] = StudentType::select('id', 'title as name')->get()->toArray();
        $toReturn['categories'] = StudentCategory::select('id', 'cat_title as name')->get()->toArray();
        return $toReturn;
    }

    public function viewAllStudent( $page )
    {
        $toReturn = array();
        User::$withoutAppends = true;
        $userId = $this->data['users']->id;
        $classesArray = MClass::select('id', 'className as name')->get()->toArray();
        $sectionsArray = sections::select('id', 'sectionName as name')->get()->toArray();
        foreach( $classesArray as $oneClass ) { $classId = $oneClass['id']; $classes[$classId] = $oneClass; }
        foreach( $sectionsArray as $oneSection ) { $sectionId = $oneSection['id']; $sections[$sectionId] = $oneSection; }
        
        if( $this->data['users']->role == "parent" ) { $studentsIds = User::getStudentsIdsFromParentId( $userId ); }

        $students = User::select('id', 'fullName as name', 'admission_number as admission', 'studentRollId as rollNo', 'phoneNo as phone', 'studentClass as class', 'studentSection as section');
        $students = $students->where('role', 'student');
        if( $this->data['users']->role != "parent" )
        {
            if( \Input::has('class') && \Input::get('class') != "" )
            {
                $classId = intval( \Input::get('class') );
                if( $classId != 0 ) $students = $students->where('studentClass', $classId);
            }
            if( \Input::has('section') && \Input::get('section') != "" )
            {
                $sectionId = intval( \Input::get('section') );
                if( $sectionId != 0 ) $students = $students->where('studentSection', $sectionId);
            }
            if( \Input::has('type') && \Input::get('type') != "" )
            {
                $typeId = intval( \Input::get('type') );
                if( $typeId != 0 ) $students = $students->where('studentType', $typeId);
            }
            if( \Input::has('category') && \Input::get('category') != "" )
            {
                $categoryId = intval( \Input::get('category') );
                if( $categoryId != 0 ) $students = $students->where('std_category', $categoryId);
            }
            if( \Input::has('search') && \Input::get('search') != "" )
            {
                $searchText = trim( \Input::get('search') );
                $students = $students->Where(
                    function ( $students ) use( $searchText ) {
                        $students->orwhere('fullName', 'LIKE', '%' . $searchText . '%');
                        $students->orwhere('admission_number', 'LIKE', '%' . $searchText . '%');
                    }
                );
            }
        }
        else
        {
            $students = $students->whereIn('id', $studentsIds);
        }
        $totalItems = $students->count();
        $students = $students->orderBy('id', 'ASC')
                            ->orderBy('studentRollId', 'ASC')
                            ->take(all_pagination_number())
                            ->skip(all_pagination_number() * ($page - 1) )
                            ->get()->toArray();
        
        $toReturn['students'] = $students;
        $studentIds = [];
        foreach( $toReturn['students'] as $index => $oneStudent )
        {
            $classId = $oneStudent['class'];
            $sectionId = $oneStudent['section'];
            if( array_key_exists($classId, $classes) ) { $toReturn['students'][$index]['class'] = $classes[$classId]['name']; }
            if( array_key_exists($sectionId, $sections) ) { $toReturn['students'][$index]['section'] = $sections[$sectionId]['name']; }
            $mixed[$oneStudent['id']] = ['index' => $index, 'id' => $oneStudent['id']];
            $studentIds[] = $oneStudent['id'];
        }
        $parentIds = User::getParentIdsFromStudentsIds($studentIds);
        $parents = User::select('id', 'fullName as name', 'parentOf', 'mobileNo')->whereIn('id', $parentIds)->get()->toArray();
        foreach( $parents as $oneParent )
        {
            $wardInfo = json_decode($oneParent['parentOf'], true);
            if( json_last_error() != JSON_ERROR_NONE ) $wardInfo = [];
            foreach( $wardInfo as $oneWard )
            {
                $wardId = $oneWard['id'];
                if( !array_key_exists($wardId, $mixed) ) continue;
                else
                {
                    $index = $mixed[$wardId]['index'];
                    $toReturn['students'][$index]['parentId'] = $oneParent['id'];
                    $toReturn['students'][$index]['parentName'] = $oneParent['name'];
                    $toReturn['students'][$index]['phone'] = $oneParent['mobileNo'];
                }
            }
        }
        $toReturn['totalItems'] = $totalItems;
        return $toReturn;
    }

    public function readStudent()
    {
        $userId = $this->data['users']->id;
        User::$withoutAppends = true;
        $studentId = \Input::get('student');
        $student = User::find($studentId);
        if( !$student ) { return $this->panelInit->apiOutput( false, "View Student", "Student Not Found" ); }

        $class = MClass::select('id', 'className as name')->where('id', $student->studentClass)->first();
        $section = sections::select('id', 'sectionName as name')->where('id', $student->studentSection)->first();
        $stdType = StudentType::select('id', 'title as name')->where('id', $student->studentType)->first();
        $stdCat = StudentCategory::select('id', 'cat_title as name')->where('id', $student->std_category)->first();
        $gaurdianIds = User::getParentIdsFromStudentId($studentId);
        $gaurdians = User::select('id', 'fullName as name', 'parentOf', 'address', 'username', 'mobileNo', 'email')->whereIn('id', $gaurdianIds)->get()->toArray();
        
        $studentInfo['main'] = [
            "id" => $studentId,
            "photo" => trim( $student->photo ) ? $student->photo : "user.png",
            "name" => $student->fullName,
            "adm" => $student->admission_number,
            "roll" => $student->studentRollId,
            "bio" => $student->biometric_id,
            "class" =>  !$class ? "" : $class->name,
            "section" => !$section ? "" : $section->name,
            "classId" => $student->studentClass,
            "sectionId" => $student->studentSection,
        ];

        $medical = json_decode( $student->medical, true );
        if( json_last_error() != JSON_ERROR_NONE ) $medical = [];
        $blood_group = array_key_exists("blood_group", $medical) ? $medical['blood_group'] : "";
        if( $blood_group == "? undefined:undefined ?" ) $blood_group = "-";
        $previous_data = json_decode( $student->previous_data, true );
        if( json_last_error() != JSON_ERROR_NONE ) $previous_data[0] = [];
        if( count($previous_data) == 0 ) { $previous_data[0] = []; }
        $previous_data = $previous_data[0];

        $db_gender = strtolower($student->gender);
        $fatherInfo = json_decode( $student->father_info, true ); if( json_last_error() != JSON_ERROR_NONE ) $fatherInfo = [];
        $motherInfo = json_decode( $student->mother_info, true ); if( json_last_error() != JSON_ERROR_NONE ) $motherInfo = [];
        $perma_address = json_decode( $student->perma_address, true ); if( json_last_error() != JSON_ERROR_NONE ) $perma_address = [];
        $corres_address = json_decode( $student->corres_address, true ); if( json_last_error() != JSON_ERROR_NONE ) $corres_address = [];
        
        $state = "-";
        if( array_key_exists('state', $corres_address) ) { if( trim( $corres_address['state'] ) ) { $state = $corres_address['state']; } }
        if( $state == "-" )
        {
            if( array_key_exists('state', $perma_address) ) { if( trim( $perma_address['state'] ) ) { $state = $perma_address['state']; } }
        }
        $country = "-";
        if( array_key_exists('country', $corres_address) ) { if( trim( $corres_address['country'] ) ) { $country = $corres_address['country']; } }
        if( $country == "-" )
        {
            if( array_key_exists('country', $perma_address) ) { if( trim( $perma_address['country'] ) ) { $country = $perma_address['country']; } }
        }
        if( $country == "-" ) { $country = "India"; }
        
        $gender = $db_gender == "m" || $db_gender == "male" ? "Male" : ( $db_gender == "f" || $db_gender == "female" ? "Female" : "-" );
        $studentInfo['profile'] = [
            "username" => $student->username,                       "gender" => $gender,
            "type" => $stdType ? $stdType->name : "Default",        "religion" => $student->religion ? $student->religion : "-",
            "birthday" => date("d M Y", $student->birthday),        "phone" => $student->phoneNo,
            "blood" => $blood_group,                                "address" => $student->address ? $student->address : "-",
            "email" => $student->email ? $student->email : "-",     "state" => $state,
            "category" => $stdCat ? $stdCat->name : "Default",      "country" => $country
        ];
        $stdName = explode(' ', $student->fullName);
        if( count($stdName) > 3 )
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['middle'] = $stdName[1];
            for( $i = 2; $i <= count($stdName); $i++ ) { $studentInfo['profile']['last'] .= $stdName[$i]; }
        }
        elseif( count($stdName) == 3 )
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['middle'] = $stdName[1];
            $studentInfo['profile']['last'] = $stdName[2];
        }
        elseif( count($stdName) == 2 )
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['last'] = $stdName[1];
            $studentInfo['profile']['middle'] = "";
        }
        else
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['middle'] = ""; $studentInfo['profile']['last'] = "";
        }
        $admDate = trim( $student->admission_date ) ? ( date('d/m/Y', $student->admission_date) ) : "";
        $studentInfo['main']['adm_date'] = $admDate;
        $studentInfo['main']['dob'] = date('d/m/Y', $student->birthday);
        $studentInfo['main']['gender'] = $db_gender == "m" || $db_gender == "male" ? "male" : ( $db_gender == "f" || $db_gender == "female" ? "female" : "0" );
        $studentInfo['main']['birthPlace'] = trim( $student->birthPlace ) ? $student->birthPlace : "";
        $studentInfo['main']['nationality'] = trim( $student->nationality ) ? $student->nationality : "";
        $studentInfo['main']['std_category'] = $student->std_category;
        $studentInfo['main']['std_type'] = $student->studentType;

        $studentInfo['parents'] = [
            'sibling' => [],
            'father' => [
                'name' => array_key_exists('name', $fatherInfo) ? $fatherInfo['name'] : "",
                'job' => array_key_exists('job', $fatherInfo) ? $fatherInfo['job'] : "",
                'phone' => array_key_exists('phone', $fatherInfo) ? $fatherInfo['phone'] : "",
                'qualification' => array_key_exists('qualification', $fatherInfo) ? $fatherInfo['qualification'] : "",
                'address' => array_key_exists('address', $fatherInfo) ? $fatherInfo['address'] : "",
                'email' => array_key_exists('email', $fatherInfo) ? $fatherInfo['email'] : "",
            ],
            'mother' => [
                'name' => array_key_exists('name', $motherInfo) ? $motherInfo['name'] : "",
                'job' => array_key_exists('job', $motherInfo) ? $motherInfo['job'] : "",
                'phone' => array_key_exists('phone', $motherInfo) ? $motherInfo['phone'] : "",
                'qualification' => array_key_exists('qualification', $fatherInfo) ? $fatherInfo['qualification'] : "",
                'address' => array_key_exists('address', $motherInfo) ? $motherInfo['address'] : "",
                'email' => array_key_exists('email', $motherInfo) ? $motherInfo['email'] : ""
            ],
            'gaurdian' => []
        ];
        if( array_key_exists('pic', $fatherInfo) )
        {
            $pic = trim( $fatherInfo['pic'] );
            if( $pic == "" || $pic == NULL ) { $studentInfo['parents']['father']['pic'] = "father.png"; }
            else
            {
                if( file_exists( uploads_config()['uploads_file_path'] . "/parents/$pic" ) )
                {
                    $studentInfo['parents']['father']['pic'] = $pic;
                } else { $studentInfo['parents']['father']['pic'] = "father.png"; }
            }
        } else { $studentInfo['parents']['father']['pic'] = "father.png"; }

        if( array_key_exists('pic', $motherInfo) )
        {
            $pic = trim( $motherInfo['pic'] );
            if( $pic == "" || $pic == NULL ) { $studentInfo['parents']['mother']['pic'] = "mother.png"; }
            else
            {
                if( file_exists( uploads_config()['uploads_file_path'] . "/parents/$pic" ) )
                {
                    $studentInfo['parents']['mother']['pic'] = $pic;
                } else { $studentInfo['parents']['mother']['pic'] = "mother.png"; }
            }
        } else { $studentInfo['parents']['mother']['pic'] = "mother.png"; }

        if( array_key_exists("line", $corres_address) )
        {
            $gaurdainAddress = trim( $corres_address["line"] ) ? trim( $corres_address["line"] ) : NULL;
        } else $gaurdainAddress = NULL;
        
        $siblingIds = [];
        foreach( $gaurdians as $oneGaurdian )
        {
            $gaurdianId = $oneGaurdian['id'];
            $wardInfo = json_decode($oneGaurdian['parentOf'], true);
            $addressOfGaurdian = $gaurdainAddress != NULL ? $gaurdainAddress : ( $oneGaurdian['address'] ? $oneGaurdian['address'] : "" );
            if( json_last_error() != JSON_ERROR_NONE ) $wardInfo = [];
            if( count( $wardInfo ) > 0 )
            {
                foreach( $wardInfo as $oneWard )
                {
                    $wardId = $oneWard['id'];
                    if( $wardId == $studentId )
                    {
                        $relation = $oneWard['relation'];
                        $proffesion = array_key_exists('job', $oneWard) ? $oneWard['job'] : "";
                        if( array_key_exists('phone', $oneWard) )
                        {
                            if( trim( $oneWard["phone"] ) )
                            {
                                $gaurdainMobile = $oneWard["phone"];
                            } else $gaurdainMobile = $oneGaurdian['mobileNo'];
                        } else $gaurdainMobile = $oneGaurdian['mobileNo'];

                        if( array_key_exists('address', $oneWard) )
                        {
                            if( trim( $oneWard["address"] ) )
                            {
                                $gaurdain_address = $oneWard["address"];
                            } else $gaurdain_address = $addressOfGaurdian;
                        } else $gaurdain_address = $addressOfGaurdian;

                        if( array_key_exists('username', $oneWard) )
                        {
                            if( trim( $oneWard["username"] ) )
                            {
                                $gaurdainUsername = $oneWard["username"];
                            } else $gaurdainUsername = $oneGaurdian['username'];
                        } else $gaurdainUsername = $oneGaurdian['username'];
                        // 
                        $relationId = strtolower($relation) == "father" ? "father" : ( strtolower($relation) == "mother" ? "mother" : "others" );
                        $studentInfo['parents']['gaurdian']['id'] = $gaurdianId;
                        $studentInfo['parents']['gaurdian']['name'] = $oneGaurdian['name'];
                        $studentInfo['parents']['gaurdian']['relation'] = $relation;
                        $studentInfo['parents']['gaurdian']['job'] = $proffesion;
                        $studentInfo['parents']['gaurdian']['phone'] = $gaurdainMobile;
                        $studentInfo['parents']['gaurdian']['address'] = $gaurdain_address;
                        $studentInfo['parents']['gaurdian']['email'] = $oneGaurdian['email'];
                        $studentInfo['parents']['gaurdian']['username'] = $gaurdainUsername;
                        $studentInfo['parents']['gaurdian']['relationId'] = $relationId;
                    }
                    else { $siblingIds[] = $wardId; }
                }
            }
        }

        if( count( $siblingIds ) > 0 )
        {
            $getSiblings = User::select('id','fullName as name', 'admission_number as admission')->whereIn('id', $siblingIds)->get()->toArray();
            foreach( $getSiblings as $oneSibling )
            {
                $studentInfo['parents']['sibling'][] = ['id' => $oneSibling['id'], 'name' => $oneSibling['name'], 'admission' => $oneSibling['admission']];
            }
        }

        $studentInfo['invoices'] = [];
        $studentInfo['marksheets'] = [];
        $studentInfo['disciplines'] = [];
        $studentInfo['attendances'] = [];
        $studentInfo['documents'] = [];
        $studentInfo['hostels'] = [ "name" => "-", "room" => "-", "warden" => "-", "supervisor" => "-", "fare" => "-" ];
        $studentInfo['transport'] = [ "name" => "-", "stoppage" => "-", "driver" => "-", "assistant" => "-", "fare" => "-" ];
        $studentInfo['previous'] = [ "institution" => "", "class" => "", "year" => "", "percentage" => ""];
        $studentInfo['medical'] = ["inspol" => "", "blood_group" => "", "weight" => "", "height" => "", "disab" => "", "contact" => ""];
        $studentInfo['corres'] = ["line" => "", "city" => "", "state" => "", "pin" => "", "country" => "", "phone" => "", "Mobile" => ""];
        $studentInfo['perma'] = ["line" => "", "city" => "", "state" => "", "pin" => "", "country" => "", "phone" => "", "Mobile" => ""];
        $studentInfo['assigns'] = ["transport" => "", "stoppage" => "", "hostel" => "", "room" => "", "mail" => false, "sms" => false, "phone" => false ];
        $studentInfo['assigns']['transport'] = $student->transport_vehicle;
        $studentInfo['assigns']['stoppage'] = $student->transport;
        $studentInfo['assigns']['hostel'] = $student->hostel;
        $studentInfo['assigns']['room'] = $student->room ? intval( $student->room ) : "";
        $comVia = json_decode( $student->comVia, true ); if( json_last_error() != JSON_ERROR_NONE ) { $comVia = []; }
        if( in_array("mail", $comVia) ) { $studentInfo['assigns']['mail'] = true; }
        if( in_array("sms", $comVia) ) { $studentInfo['assigns']['sms'] = true; }
        if( in_array("phone", $comVia) ) { $studentInfo['assigns']['phone'] = true; }
        
        if( array_key_exists('institution', $previous_data) ) $studentInfo['previous']['institution'] = $previous_data["institution"];
        if( array_key_exists('class', $previous_data) ) $studentInfo['previous']['class'] = $previous_data["class"];
        if( array_key_exists('year', $previous_data) ) $studentInfo['previous']['year'] = $previous_data["year"];
        if( array_key_exists('percentage', $previous_data) ) $studentInfo['previous']['percentage'] = $previous_data["percentage"];

        if( $blood_group != "-" ) $studentInfo['medical']['blood_group'] = $blood_group;
        if( array_key_exists('inspol', $medical) ) $studentInfo['medical']['inspol'] = $medical["inspol"];
        if( array_key_exists('weight', $medical) ) $studentInfo['medical']['weight'] = $medical["weight"];
        if( array_key_exists('height', $medical) ) $studentInfo['medical']['height'] = $medical["height"];
        if( array_key_exists('disab', $medical) ) $studentInfo['medical']['disab'] = $medical["disab"];
        if( array_key_exists('contact', $medical) ) $studentInfo['medical']['contact'] = $medical["contact"];
        if( trim( $studentInfo['medical']['weight'] ) ) $studentInfo['medical']['weight'] = floatval( $studentInfo['medical']['weight'] );
        if( trim( $studentInfo['medical']['height'] ) ) $studentInfo['medical']['height'] = floatval( $studentInfo['medical']['height'] );

        if( array_key_exists('line', $corres_address) ) $studentInfo['corres']['line'] = $corres_address["line"];
        if( array_key_exists('city', $corres_address) ) $studentInfo['corres']['city'] = $corres_address["city"];
        if( array_key_exists('state', $corres_address) ) $studentInfo['corres']['state'] = $corres_address["state"];
        if( array_key_exists('pin', $corres_address) ) $studentInfo['corres']['pin'] = $corres_address["pin"];
        if( array_key_exists('country', $corres_address) ) $studentInfo['corres']['country'] = $corres_address["country"];
        if( array_key_exists('phone', $corres_address) ) $studentInfo['corres']['phone'] = $corres_address["phone"];
        if( array_key_exists('Mobile', $corres_address) ) $studentInfo['corres']['Mobile'] = $corres_address["Mobile"];
        
        if( array_key_exists('line', $perma_address) ) $studentInfo['perma']['line'] = $perma_address["line"];
        if( array_key_exists('city', $perma_address) ) $studentInfo['perma']['city'] = $perma_address["city"];
        if( array_key_exists('state', $perma_address) ) $studentInfo['perma']['state'] = $perma_address["state"];
        if( array_key_exists('pin', $perma_address) ) $studentInfo['perma']['pin'] = $perma_address["pin"];
        if( array_key_exists('country', $perma_address) ) $studentInfo['perma']['country'] = $perma_address["country"];
        if( array_key_exists('phone', $perma_address) ) $studentInfo['perma']['phone'] = $perma_address["phone"];
        if( array_key_exists('Mobile', $perma_address) ) $studentInfo['perma']['Mobile'] = $perma_address["Mobile"];

        if( $this->panelInit->can('students.Attendance') )
        {
            $year_1 = \Input::has('current') ? intval( \Input::get('current') ) : intval( date('Y') );
            $year_2 = \Input::has('next') ? intval( \Input::get('next') ) : intval( date('Y') ) + 1;
            if( \Input::has('action') )
            {
                $action = \Input::get('action');
                if( $action == "backward" ) { $year_1 = $year_1 - 1; $year_2 = $year_2 - 1; }
                if( $action == "forward" ) { $year_1 = $year_1 + 1; $year_2 = $year_2 + 1; }
            }

            $apr = generateTimeRange( $year_1 . "-04-01" );
            $may = generateTimeRange( $year_1 . "-05-01" );
            $jun = generateTimeRange( $year_1 . "-06-01" );
            $jul = generateTimeRange( $year_1 . "-07-01" );
            $aug = generateTimeRange( $year_1 . "-08-01" );
            $sep = generateTimeRange( $year_1 . "-09-01" );
            $oct = generateTimeRange( $year_1 . "-10-01" );
            $nov = generateTimeRange( $year_1 . "-11-01" );
            $dec = generateTimeRange( $year_1 . "-12-01" );
            $jan = generateTimeRange( $year_2 . "-01-01" );
            $feb = generateTimeRange( $year_2 . "-02-01" );
            $mar = generateTimeRange( $year_2 . "-03-01" );

            $totalDays = [ $apr, $may, $jun, $jul, $aug, $sep, $oct, $nov, $dec, $jan, $feb, $mar ];

            $start = $apr[0]['date']; $last = end( $mar )['date'];
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

            $attendance = Attendance::select('id', 'status', 'date', 'attNotes as notes', 'studentId as student');
            $attendance = $attendance->where('date', '>=', strtotime( $start . " 00:00:00" ));
            $attendance = $attendance->where('date', '<=', strtotime( $last . " 23:59:59" ));
            $attendance = $attendance->where('studentId', $studentId);
            $attendance = $attendance->get()->toArray();
            $attendanceList = [];
            
            foreach( $attendance as $item )
            {
                $rowId = $item['id'];
                $status = intval( $item['status'] );
                $day = date('Y-m-d', $item['date']);
                $notes = $item['notes'];
                $attendanceList[$studentId]["$day"] = [ 'id' => $rowId, 'student' => $studentId, 'status' => $status, 'date' => $day, 'notes' => $notes ];
            }
            $mixedList = []; $index = 0;
            
            foreach( $totalDays as $timeRange )
            {
                $attenData = [];
                $mixedList[$index]['id'] = $studentId;
                $mixedList[$index]['name'] = $student->fullName;
                $mixedList[$index]['num'] = $student->admission_number;
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
            $finalList = [];
            foreach( $mixedList as $oneList ) { $finalList[] = $oneList['attendance']; }
            foreach( $finalList as $key => $timeRange )
            {
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
                    
                    $finalList[$key][$indexed]['isAllowed'] = $isAllowed;
                    $finalList[$key][$indexed]['allowanceData'] = $allowanceData;
                    $finalList[$key][$indexed]['normal'] = true;
                }
            }
            foreach( $finalList as $monthIndex => $monthData )
            {
                $count = count( $monthData );
                $month = getMonthNumByIndex( $monthIndex );
                $year = $monthIndex <= 8 ? $year_1 : $year_2;
                if( $count == 31 ) continue;
                else
                {
                    for( $i = ( $count + 1); $i <= 31; $i++ )
                    {
                        $day = $year . "-" . $month . "-" . $i;
                        $finalList[$monthIndex][$day] = [ "id" => "", "status" => "", "date" => $day, "day" => "", "notes" => "", "isExist" => false, "isAllowed" => false, "allowanceData" => [ "type" => "", "name" => "", "comment" => "" ], "normal" => false ];
                    }
                }
            }
            $holidays = 0; $weekends = 0; $presents = 0; $absentss = 0;
            foreach( $finalList as $key => $timeRange )
            {
                foreach( $timeRange as $indexed => $singleDay )
                {
                    if( $singleDay["status"] == 1 ) { $finalList[$key][$indexed]["name"] = "P"; $presents = $presents + 1; }
                    elseif( $singleDay["status"] === 0 ) { $finalList[$key][$indexed]["name"] = "A"; $absentss = $absentss + 1; }
                    elseif( $singleDay["isAllowed"] == true )
                    {
                        if( $singleDay["allowanceData"]["type"] == "Weekly" ) { $finalList[$key][$indexed]["name"] = "W"; $weekends = $weekends + 1; }
                        elseif( $singleDay["allowanceData"]["type"] == "Holiday" ) { $finalList[$key][$indexed]["name"] = "H"; $holidays = $holidays + 1; }
                        else { $finalList[$key][$indexed]["name"] = ""; }
                    } else { $finalList[$key][$indexed]["name"] = ""; }
                }
            }
            $days = []; for( $x = 1; $x <= 31; $x++ ) { $days[] = $x; }
            $attendances = [
                "days" => $days,
                "total" => [ "holidays" => $holidays, "weekends" => $weekends, "presents" => $presents, "absents" => $absentss ],
                "data" => $finalList,
                "current" => $year_1,
                "next" => $year_2
            ];
            $studentInfo['attendances'] = $attendances;
            if( \Input::has('action') ) return $studentInfo;
        }
        
        if( $this->panelInit->can('students.docs') )
        {
            $studentDocs = StudentDoc::where('user_id', $studentId)->get()->toArray();
            $docs = [];
            foreach( $studentDocs as $index => $oneDocument )
            {
                $file_name = $oneDocument['file_name'];
                if( !file_exists( uploads_config()['uploads_file_path'] . "/student_docs/$file_name" ) ) { continue; }
                $docs[] = $oneDocument;
            }
            $studentInfo['documents'] = $docs;
        }
        
        if( $this->panelInit->can('students.Marksheet') )
        {
            $exams = [];
            $studentExams = ExamsList::where("examClasses", "LIKE", '%"' . $student->studentClass . '"%' )->get()->toArray();
            foreach( $studentExams as $oneExam ) { $exams[$oneExam['id']] = $oneExam; }
    
            $studentMarks = exam_marks::where('studentId', $studentId)->get()->toArray();
            $examMarks = [];
            foreach( $studentMarks as $oneMark )
            {
                $examMarks[$oneMark['examId']][$oneMark['subjectId']] = [
                    'subjectId' => $oneMark['subjectId'],
                    'marks' => trim($oneMark['totalMarks']) != "" ? ( is_numeric( $oneMark['totalMarks'] ) ? floatval( $oneMark['totalMarks'] ) : $oneMark['totalMarks'] ) : "",
                    'comments' => trim($oneMark['markComments']) != "" ? $oneMark['markComments'] : "",
                ];
            }
            foreach( $exams as $index => $oneExam )
            {
                $schedule = json_decode( $oneExam['examSchedule'], true );
                if( json_last_error() != JSON_ERROR_NONE ) $schedule = [];
                $exams[$index]['examSchedule'] = $schedule;
            }
    
            foreach( $exams as $index => $oneExam )
            {
                foreach( $oneExam['examSchedule'] as $key => $oneSubject )
                {
                    if( !array_key_exists('pass_marks', $oneSubject) ) $exams[$index]['examSchedule'][$key]['pass_marks'] = "-";
                    if( !array_key_exists('max_marks', $oneSubject) ) $exams[$index]['examSchedule'][$key]['max_marks'] = "-";
                    if( !array_key_exists('subject', $oneSubject) ) continue;
                    if( !is_numeric( $oneSubject['subject'] ) ) continue;
                    $subjectId = $oneSubject['subject'];
                    if( array_key_exists('subject_type', $oneSubject) )
                    {
                        if( $oneSubject['subject_type'] == "main" ) { $exams[$index]['examSchedule'][$key]['subject'] = "m_" . $subjectId; }
                        if( $oneSubject['subject_type'] == "secondary" ) { $exams[$index]['examSchedule'][$key]['subject'] = "s_" . $subjectId; }
                    }
                    else
                    {
                        $main = Subject::find( $subjectId );
                        if( $main )
                        {
                            $exams[$index]['examSchedule'][$key]['subject'] = "m_" . $subjectId;
                            $exams[$index]['examSchedule'][$key]['subject_type'] = "main";
                            $exams[$index]['examSchedule'][$key]['name'] = $main->subjectTitle;
                        }
                        else
                        {
                            $secondary = SubSubject::find( $subjectId );
                            if( $secondary )
                            {
                                $exams[$index]['examSchedule'][$key]['subject'] = "s_" . $subjectId;
                                $exams[$index]['examSchedule'][$key]['subject_type'] = "secondary";
                                $exams[$index]['examSchedule'][$key]['name'] = $secondary->subjectTitle;
                            }
                            else
                            {
                                $exams[$index]['examSchedule'][$key]['subject'] = "m_" . $subjectId;
                                $exams[$index]['examSchedule'][$key]['subject_type'] = "main";
                            }
                        }
                    }
                }
            }
    
            foreach( $exams as $index => $oneExam )
            {
                $examId = $oneExam['id'];
                if( !array_key_exists($examId, $examMarks) )
                {
                    foreach( $oneExam['examSchedule'] as $key => $oneSubject )
                    {
                        $exams[$index]['examSchedule'][$key]['marks'] = "";
                        $exams[$index]['examSchedule'][$key]['status'] = "-";
                        $exams[$index]['examSchedule'][$key]['comments'] = "";
                    }
                }
                else
                {
                    foreach( $oneExam['examSchedule'] as $key => $oneSubject )
                    {
                        $pass = $oneSubject['pass_marks'];
                        $max = $oneSubject['max_marks'];
                        if( array_key_exists($oneSubject['subject'], $examMarks[$examId]) )
                        {
                            $oneMark = $examMarks[$examId][$oneSubject['subject']];
                            $exams[$index]['examSchedule'][$key]['marks'] = $oneMark['marks'];
                            if( $oneSubject['pass_marks'] != "-" )
                            {
                                if( floatval( $oneMark['marks'] ) >= floatval( $oneSubject['pass_marks'] ) ) { $exams[$index]['examSchedule'][$key]['status'] = "Pass"; }
                                else { $exams[$index]['examSchedule'][$key]['status'] = "Fail"; }
                            }
                            $exams[$index]['examSchedule'][$key]['comments'] = $oneMark['comments'];
                        }
                        else
                        {
                            $exams[$index]['examSchedule'][$key]['marks'] = "";
                            $exams[$index]['examSchedule'][$key]['status'] = "-";
                            $exams[$index]['examSchedule'][$key]['comments'] = "";
                        }
                    }
                }
            }
            
            $marksheets = [];
            foreach( $exams as $oneExam )
            {
                $rows = [];
                foreach( $oneExam['examSchedule'] as $oneSubject )
                {
                    $rows[] = [
                        'name' => array_key_exists('name', $oneSubject) ? $oneSubject['name'] : "",
                        'pass' => array_key_exists('pass_marks', $oneSubject) ? $oneSubject['pass_marks'] : "",
                        'max' => array_key_exists('max_marks', $oneSubject) ? $oneSubject['max_marks'] : "",
                        'status' => array_key_exists('status', $oneSubject) ? $oneSubject['status'] : "",
                        'marks' => array_key_exists('marks', $oneSubject) ? $oneSubject['marks'] : "",
                        'comments' => array_key_exists('comments', $oneSubject) ? $oneSubject['comments'] : ""
                    ];
                }
                $marksheets[] = ['name' => $oneExam['examTitle'], 'rows' => $rows];
            }
            $studentInfo['marksheets'] = $marksheets;
        }

        if( $this->panelInit->can('students.invoices') )
        {
            $invoices = Payment::where('paymentStudent', $studentId)->get()->toArray();
            foreach( $invoices as $oneInvoice )
            {
                $invoiceRow = [
                    'id' => $oneInvoice['id'],
                    'title' => $oneInvoice['paymentTitle'],
                    'dueDate' => date('d-m-Y', $oneInvoice['dueDate']),
                    'status' => intval( $oneInvoice['paymentStatus'] ),
                    'feeAmount' => floatval( $oneInvoice['paymentAmount'] ),
                    'discount' => floatval( $oneInvoice['paymentDiscount'] ),
                    'paid' => floatval( $oneInvoice['paidAmount'] ),
                    'fine' => 0,
                    'due' => 0
                ];
                if( time() > $oneInvoice['dueDate'] )
                {
                    $invoiceRow['fine'] = floatval( $oneInvoice['fine_amount'] );
                    $invoiceRow['due'] = floatval( $oneInvoice['fine_amount'] ) + ( floatval( $oneInvoice['paymentAmount'] ) - floatval( $oneInvoice['paymentDiscount'] ) );
                }
                if( floatval( $oneInvoice['paidAmount'] ) != 0 )
                {
                    $required = floatval( $oneInvoice['paymentAmount'] ) - floatval( $oneInvoice['paymentDiscount'] );
                    if( time() > $oneInvoice['dueDate'] ) $required = $required + floatval( $oneInvoice['fine_amount'] );
                    if( floatval( $oneInvoice['paidAmount'] ) < $required ) { $invoiceRow['status'] = 2; }
                }
                $studentInfo['invoices']['data'][] = $invoiceRow;
            }
            $studentInfo['invoices']['totals'] = [ 'feeAmount' => 0, 'discount' => 0, 'paid' => 0, 'fine' => 0, 'due' => 0 ];
            if( array_key_exists( "invoices", $studentInfo ) )
            {
                if( array_key_exists( "data", $studentInfo["invoices"] ) )
                {
                    foreach( $studentInfo['invoices']['data'] as $oneRow )
                    {
                        $studentInfo['invoices']['totals']['feeAmount'] = $studentInfo['invoices']['totals']['feeAmount'] + $oneRow['feeAmount'];
                        $studentInfo['invoices']['totals']['discount'] = $studentInfo['invoices']['totals']['discount'] + $oneRow['discount'];
                        $studentInfo['invoices']['totals']['paid'] = $studentInfo['invoices']['totals']['paid'] + $oneRow['paid'];
                        $studentInfo['invoices']['totals']['fine'] = $studentInfo['invoices']['totals']['fine'] + $oneRow['fine'];
                        $studentInfo['invoices']['totals']['due'] = $studentInfo['invoices']['totals']['due'] + $oneRow['due'];
                    }
                }
            }
        }

        if( $this->panelInit->can('students.transport') )
        {
            if( $student->transport != 0 )
            {
                $transport = transportation::find( $student->transport );
                if( $transport )
                {
                    $studentInfo['transport']['stoppage'] = $transport->transportTitle ? $transport->transportTitle : "-";
                    $studentInfo['transport']['fare'] = $transport->transportFare ? $transport->transportFare : "-";
                }
            }
            if( $student->transport_vehicle != 0 )
            {
                $vehicle = transport_vehicles::find( $student->transport_vehicle );
                if( $vehicle )
                {
                    $studentInfo['transport']['name'] = $vehicle->plate_number ? $vehicle->plate_number : "-";
                    $studentInfo['transport']['driver'] = $vehicle->driver_name ? $vehicle->driver_name : "-";
                    $studentInfo['transport']['assistant'] = $vehicle->assistant_name ? $vehicle->assistant_name : "-";
                }
            }
        }

        if( $this->panelInit->can('students.hostel') )
        {
            //
        }
        
        if( $this->panelInit->can('students.discipline') ) { $studentInfo['disciplines'] = []; }

        $toReturn = array();
        $toReturn["status"] = "success";
        $toReturn["studentInfo"] = $studentInfo;
        return $toReturn;
    }

    public function loadImage( $img )
    {
        header('Content-Type: image/jpeg');
        $uploads_file_path = uploads_config()['uploads_file_path'];
        if( file_exists( $uploads_file_path . "/profile/$img" ) ) { return file_get_contents($uploads_file_path . "/profile/$img"); }
        else { return file_get_contents($uploads_file_path . "/profile/user.png"); }
    }

    public function parentImage( $img )
    {
        header('Content-Type: image/jpeg');
        $uploads_file_path = uploads_config()['uploads_file_path'];
        return file_get_contents($uploads_file_path . "/parents/$img");
    }

    public function downloadDocument( $id )
    {
        User::$withoutAppends = true;
        if(!$this->panelInit->can( array("students.docs") )) { exit; }
        if( !isset( $this->data['users']->role ) ) { exit; }
        $toReturn = StudentDoc::find( $id );
        if( !$toReturn ) { echo "<br/><br/><br/><br/><br/><center>File not exist, Please contact site administrator to reupload it again.</center>"; exit; }
        if( $this->data['users']->role != "admin" && $this->data['users']->role != "teacher" )
        {
            $userId = $this->data['users']->id;
            $myWards = User::getStudentsIdsFromParentId( $userId );
            $studentsId = $toReturn->user_id;
            if( !in_array( $studentsId, $myWards ) ) { echo "<br/><br/><br/><br/><br/><center>File not exist, Please contact site administrator to reupload it again.</center>"; exit; }
        }
		if( trim( $toReturn->file_name ) == "" )
		{
			echo "<br/><br/><br/><br/><br/><center>File not exist, Please contact site administrator to reupload it again.</center>";
			exit;
		}
		if( file_exists(uploads_config()['uploads_file_path'] . '/student_docs/'.$toReturn->file_name) == true )
		{
			$fileName = preg_replace('/[^a-zA-Z0-9-_\.]/','-',$toReturn->file_title). "." .pathinfo($toReturn->file_name, PATHINFO_EXTENSION);
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=" . $fileName);
			echo file_get_contents(uploads_config()['uploads_file_path'] . '/student_docs/'.$toReturn->file_name);
		}
		else
		{
			echo "<br/><br/><br/><br/><br/><center>File not exist, Please contact site administrator to reupload it again.</center>";
		}
		exit;
    }

    public function deleteDocument()
    {
        if(!$this->panelInit->can( array("students.docs") )) { return $this->panelInit->apiOutput( false, "Delete Document", "You don't have permission to delete documents" ); }
        if( !\Input::has('documentId') ) { return $this->panelInit->apiOutput( false, "Delete Document", "Unable to load document data" ); }
        $id = \Input::get('documentId');
        $studentDoc = StudentDoc::find( $id );
        if( !$studentDoc ) { return $this->panelInit->apiOutput( false, "Delete Document", "Unable to load document data" ); }
        if( $this->data['users']->role != "admin" && $this->data['users']->role != "teacher" )
        {
            $userId = $this->data['users']->id;
            $myWards = User::getStudentsIdsFromParentId( $userId );
            $studentsId = $studentDoc->user_id;
            if( !in_array( $studentsId, $myWards ) ) { return $this->panelInit->apiOutput( false, "Delete Document", "You don't have permission to delete documents" ); }
        }
        $oldFileName = $studentDoc->file_name;
        if( file_exists( uploads_config()['uploads_file_path'] . "/student_docs/$oldFileName" ) )
        {
            unlink( uploads_config()['uploads_file_path'] . "/student_docs/$oldFileName" );
        }
        $studentDoc->delete();
        return $this->panelInit->apiOutput( true, "Delete Document", "Document deleted successfully" );
    }

    public function createDocument()
    {
        if(!$this->panelInit->can( array("students.docs") )) { return $this->panelInit->apiOutput( false, "Add Document", "You don't have permission to add documents" ); }
        if( !\Input::has('title') ) { return $this->panelInit->apiOutput( false, "Add Document", "Document Title is missing" ); }
        if( !\Input::has('studentId') ) { return $this->panelInit->apiOutput( false, "Add Document", "Unable to locate student data" ); }
        if( !\Input::hasfile('file') ) { return $this->panelInit->apiOutput( false, "Add Document", "No File attached please select one" ); }
        
        $title = trim( \Input::get('title') );
        if( !$title ) { return $this->panelInit->apiOutput( false, "Add Document", "Document Title is missing" ); }
        $studentId = \Input::get('studentId');
        $notes = \Input::has('notes') ? ( trim( \Input::get('notes') ) ? trim( \Input::get('notes') ) : "" ) : "";
        User::$withoutAppends = true;
        $student = User::where('role', 'student')->where('id', $studentId)->first();
        if( !$student ) { return $this->panelInit->apiOutput( false, "Add Document", "Unable to locate student data" ); }
        
        if( \Input::hasFile('file') )
        {
			$docFileInstance = \Input::file('file');
			if( !$this->panelInit->validate_upload( $docFileInstance ) ) {
				return $this->panelInit->apiOutput(false, "Add Document", "Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

            $newFileName = $studentId . "_" . uniqid() . "." . $docFileInstance->getClientOriginalExtension();
            $docFileInstance->move(uploads_config()['uploads_file_path'] . '/student_docs/', $newFileName);
        } else $newFileName = "";
        
        $documents = new StudentDoc();
        $documents->user_id = $studentId;
        $documents->file_title = $title;
        $documents->file_name = $newFileName;
        $documents->file_notes = $notes;
        $documents->save();

        $studentDocs = StudentDoc::where('user_id', $studentId)->get()->toArray();
        $docs = [];
        $toReturn = array();
        foreach( $studentDocs as $index => $oneDocument )
        {
            $file_name = $oneDocument['file_name'];
            if( !file_exists( uploads_config()['uploads_file_path'] . "/student_docs/$file_name" ) ) { continue; }
            $docs[] = $oneDocument;
        }
        $toReturn["status"] = "success";
        $toReturn["title"] = "Add Document";
        $toReturn["message"] = "Document uploaded successfully";
        $toReturn["documents"] = $docs;
        return $toReturn;
    }

    public function updateDocument()
    {
        if(!$this->panelInit->can( array("students.docs") )) { return $this->panelInit->apiOutput( false, "Edit Document", "You don't have permission to add documents" ); }
        if( !\Input::has('documentId') ) { return $this->panelInit->apiOutput( false, "Edit Document", "Unable to load document data" ); }
        $id = \Input::get('documentId');
        $studentDoc = StudentDoc::find( $id );
        if( !$studentDoc ) { return $this->panelInit->apiOutput( false, "Edit Document", "Unable to load document data" ); }
        
        if( !\Input::has('title') ) { return $this->panelInit->apiOutput( false, "Edit Document", "Document Title is missing" ); }
        if( !\Input::has('studentId') ) { return $this->panelInit->apiOutput( false, "Edit Document", "Unable to locate student data" ); }
        $title = trim( \Input::get('title') );
        if( !$title ) { return $this->panelInit->apiOutput( false, "Edit Document", "Document Title is missing" ); }
        $studentId = \Input::get('studentId');
        $notes = \Input::has('notes') ? ( trim( \Input::get('notes') ) ? trim( \Input::get('notes') ) : "" ) : "";

        User::$withoutAppends = true;
        $student = User::where('role', 'student')->where('id', $studentId)->first();
        if( !$student ) { return $this->panelInit->apiOutput( false, "Edit Document", "Unable to locate student data" ); }
        
        if( \Input::hasFile('file') )
        {
            $oldFileName = $studentDoc->file_name;
            if( trim( $oldFileName ) )
            {
                if( file_exists( uploads_config()['uploads_file_path'] . "/student_docs/$oldFileName" ) )
                {
				    unlink( uploads_config()['uploads_file_path'] . "/student_docs/$oldFileName" );
			    }
            }
            $docFileInstance = \Input::file('file');
			if( !$this->panelInit->validate_upload( $docFileInstance ) ) {
				return $this->panelInit->apiOutput(false, "Edit Document", "Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

            $newFileName = $studentId . "_" . uniqid() . "." . $docFileInstance->getClientOriginalExtension();
            $docFileInstance->move(uploads_config()['uploads_file_path'] . '/student_docs/', $newFileName);

        } else $newFileName = $studentDoc->file_name;

        
        $studentDoc->user_id = $studentId;
        $studentDoc->file_title = $title;
        $studentDoc->file_name = $newFileName;
        $studentDoc->file_notes = $notes;
        $studentDoc->save();

        $studentDocs = StudentDoc::where('user_id', $studentId)->get()->toArray();
        $docs = [];
        $toReturn = array();
        foreach( $studentDocs as $index => $oneDocument )
        {
            $file_name = $oneDocument['file_name'];
            if( !file_exists( uploads_config()['uploads_file_path'] . "/student_docs/$file_name" ) ) { continue; }
            $docs[] = $oneDocument;
        }
        $toReturn["status"] = "success";
        $toReturn["title"] = "Edit Document";
        $toReturn["message"] = "Document updated successfully";
        $toReturn["documents"] = $docs;
        return $toReturn;
    }

    public function deleteStudent()
    {
        if( !$this->panelInit->can('students.delStudent') ) { return $this->panelInit->apiOutput( false, "Delete Student", "You don't have permission to delete student" ); }
        if( !\Input::has('studentId') ) { return $this->panelInit->apiOutput( false, "Delete Student", "Unable to load student data" ); }
        $studentId = \Input::get('studentId');
        User::$withoutAppends = true;
        $student = User::where('role', 'student')->where('id', $studentId)->first();
        if( !$student ) { return $this->panelInit->apiOutput( false, "Delete Student", "Unable to locate student data" ); }

        // put code to delete all student data right here
        $student->delete();
        return $this->panelInit->apiOutput( true, "Delete Student", "Student deleted successfully" );
    }

    public function student_attendance()
    {
        $studentId = NULL;
        $studentsList = [];
        $toReturn = array();
        User::$withoutAppends = true;
        $userId = $this->data['users']->id;
        if( !$this->panelInit->can('students.Attendance') ) { return ['students' => [], 'attenadnce' => []]; }
        if( $this->data['users']->role == "parent" )
        {
            if( \Input::has('studentId') && trim( \Input::get('studentId') ) ) { $studentId = \Input::get('studentId'); }
            else
            {
                $studentsIds = User::getStudentsIdsFromParentId( $userId );
                if( count( $studentsIds ) == 0 )
                {
                    $toReturn['students'] = [];
                    $toReturn['attenadnce'] = [];
                    return $toReturn;
                }
                $studentsList = User::select('id', 'fullName as name')->whereIn('id', $studentsIds)->get()->toArray();
                $studentId = $studentsIds[0];
            }
        }
        elseif( $this->data['users']->role == "student" )
        {
            $studentId = $userId;
        }

        if( !$studentId ) { return ['students' => [], 'attenadnce' => []]; }

        $year_1 = \Input::has('current') ? intval( \Input::get('current') ) : intval( date('Y') );
        $year_2 = \Input::has('next') ? intval( \Input::get('next') ) : intval( date('Y') ) + 1;
        if( \Input::has('action') )
        {
            $action = \Input::get('action');
            if( $action == "backward" ) { $year_1 = $year_1 - 1; $year_2 = $year_2 - 1; }
            if( $action == "forward" ) { $year_1 = $year_1 + 1; $year_2 = $year_2 + 1; }
        }

        $apr = generateTimeRange( $year_1 . "-04-01" );
        $may = generateTimeRange( $year_1 . "-05-01" );
        $jun = generateTimeRange( $year_1 . "-06-01" );
        $jul = generateTimeRange( $year_1 . "-07-01" );
        $aug = generateTimeRange( $year_1 . "-08-01" );
        $sep = generateTimeRange( $year_1 . "-09-01" );
        $oct = generateTimeRange( $year_1 . "-10-01" );
        $nov = generateTimeRange( $year_1 . "-11-01" );
        $dec = generateTimeRange( $year_1 . "-12-01" );
        $jan = generateTimeRange( $year_2 . "-01-01" );
        $feb = generateTimeRange( $year_2 . "-02-01" );
        $mar = generateTimeRange( $year_2 . "-03-01" );

        $totalDays = [ $apr, $may, $jun, $jul, $aug, $sep, $oct, $nov, $dec, $jan, $feb, $mar ];

        $start = $apr[0]['date']; $last = end( $mar )['date'];
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

        $attendance = Attendance::select('id', 'status', 'date', 'attNotes as notes', 'studentId as student');
        $attendance = $attendance->where('date', '>=', strtotime( $start . " 00:00:00" ));
        $attendance = $attendance->where('date', '<=', strtotime( $last . " 23:59:59" ));
        $attendance = $attendance->where('studentId', $studentId);
        $attendance = $attendance->get()->toArray();
        $attendanceList = [];
        
        foreach( $attendance as $item )
        {
            $rowId = $item['id'];
            $status = intval( $item['status'] );
            $day = date('Y-m-d', $item['date']);
            $notes = $item['notes'];
            $attendanceList[$studentId]["$day"] = [ 'id' => $rowId, 'student' => $studentId, 'status' => $status, 'date' => $day, 'notes' => $notes ];
        }
        $mixedList = []; $index = 0;
        
        foreach( $totalDays as $timeRange )
        {
            $attenData = [];
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
        $finalList = [];
        foreach( $mixedList as $oneList ) { $finalList[] = $oneList['attendance']; }
        foreach( $finalList as $key => $timeRange )
        {
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
                
                $finalList[$key][$indexed]['isAllowed'] = $isAllowed;
                $finalList[$key][$indexed]['allowanceData'] = $allowanceData;
                $finalList[$key][$indexed]['normal'] = true;
            }
        }
        foreach( $finalList as $monthIndex => $monthData )
        {
            $count = count( $monthData );
            $month = getMonthNumByIndex( $monthIndex );
            $year = $monthIndex <= 8 ? $year_1 : $year_2;
            if( $count == 31 ) continue;
            else
            {
                for( $i = ( $count + 1); $i <= 31; $i++ )
                {
                    $day = $year . "-" . $month . "-" . $i;
                    $finalList[$monthIndex][$day] = [ "id" => "", "status" => "", "date" => $day, "day" => "", "notes" => "", "isExist" => false, "isAllowed" => false, "allowanceData" => [ "type" => "", "name" => "", "comment" => "" ], "normal" => false ];
                }
            }
        }
        $holidays = 0; $weekends = 0; $presents = 0; $absentss = 0;
        foreach( $finalList as $key => $timeRange )
        {
            foreach( $timeRange as $indexed => $singleDay )
            {
                if( $singleDay["status"] == 1 ) { $finalList[$key][$indexed]["name"] = "P"; $presents = $presents + 1; }
                elseif( $singleDay["status"] === 0 ) { $finalList[$key][$indexed]["name"] = "A"; $absentss = $absentss + 1; }
                elseif( $singleDay["isAllowed"] == true )
                {
                    if( $singleDay["allowanceData"]["type"] == "Weekly" ) { $finalList[$key][$indexed]["name"] = "W"; $weekends = $weekends + 1; }
                    elseif( $singleDay["allowanceData"]["type"] == "Holiday" ) { $finalList[$key][$indexed]["name"] = "H"; $holidays = $holidays + 1; }
                    else { $finalList[$key][$indexed]["name"] = ""; }
                } else { $finalList[$key][$indexed]["name"] = ""; }
            }
        }
        $days = []; for( $x = 1; $x <= 31; $x++ ) { $days[] = $x; }
        $attendances = [
            "days" => $days,
            "total" => [ "holidays" => $holidays, "weekends" => $weekends, "presents" => $presents, "absents" => $absentss ],
            "data" => $finalList,
            "current" => $year_1,
            "next" => $year_2
        ];
        $toReturn['students'] = $studentsList;
        $toReturn['attenadnce'] = $attendances;
        return $toReturn;        
    }
}