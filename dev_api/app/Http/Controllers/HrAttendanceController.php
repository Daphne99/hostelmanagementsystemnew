<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use App\Models2\HR\WorkShift;
use App\Models2\HR\EmployeeAttendance;
use App\Models2\HR\Department;
use App\Models2\HR\Employee;
use App\Models2\HR\LeaveApplication;
use App\Models2\HR\LeaveType;
use Carbon\Carbon;

class HrAttendanceController extends Controller
{
    var $data = array();
	var $panelInit ;
    var $layout = 'dashboard';

    public function __construct()
    {
		if( app('request')->header('Authorization') != "" || \Input::has('token') ) $this->middleware('jwt.auth');
		else $this->middleware('authApplication');
        
        $this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) return \Redirect::to('/');
    }

    public function listWorkshifts( $page )
    {
        if( !$this->panelInit->can( "workshifts.list" ) ) { return $this->panelInit->apiOutput( false, "List work shifts", "You don't have permission to list workshifts" ); }
        $toReturn = array();
        $getWorkShifts = WorkShift::select('work_shift_id as id', 'shift_name as name', 'start_time as start', 'end_time as end', 'late_count_time as late');
        $getWorkShifts = $getWorkShifts->where('deleted', '0');
        $toReturn["workShiftsCount"] = $getWorkShifts->count();
        $getWorkShifts = $getWorkShifts->take(all_pagination_number()) ->skip(all_pagination_number() * ($page - 1) )->get()->toArray();
        foreach( $getWorkShifts as $key => $shift )
        {
            $getWorkShifts[$key]['start'] = date('h:i:s a', strtotime( $shift['start'] ));
            $getWorkShifts[$key]['end'] = date('h:i:s a', strtotime( $shift['end'] ));
            $getWorkShifts[$key]['late'] = date('h:i:s a', strtotime( $shift['late'] ));
        }
        $toReturn["workShifts"] = $getWorkShifts;
        $employees = Employee::select('employee_id as id', 'first_name', 'last_name')->where('deleted', '0')->get()->toArray();
        $output = []; $index = 0;
        foreach( $employees as $employee )
        {
            $output[$index] = [ 'id' => $employee['id'], 'name' => $employee['first_name'] . " " . $employee['last_name'] ];
            $index++;
        }
        $toReturn["employees"] = $output;
        return $toReturn;
    }

    public function createWorkshift()
    {
        if( !$this->panelInit->can( "workshifts.add" ) ) { return $this->panelInit->apiOutput( false, "Add work shift", "You don't have permission to add workshifts" ); }
        if( !\Input::has('name') ) { return $this->panelInit->apiOutput( false, "Add work shift", "Shift Name is missing" ); }
        if( !\Input::has('start') ) { return $this->panelInit->apiOutput( false, "Add work shift", "Start Time is missing" ); }
        if( !\Input::has('end') ) { return $this->panelInit->apiOutput( false, "Add work shift", "End Time is missing" ); }
        if( !\Input::has('late') ) { return $this->panelInit->apiOutput( false, "Add work shift", "Late Count Time is missing" ); }
        
        $start = date('H:i:s', strtotime( \Input::get('start') ));
        $end = date('H:i:s', strtotime( \Input::get('end') ));
        $late = date('H:i:s', strtotime( \Input::get('late') ));
        
        $WorkShift = new WorkShift();
        $WorkShift->shift_name = \Input::get('name');
        $WorkShift->start_time = $start;
        $WorkShift->end_time = $end;
        $WorkShift->late_count_time = $late;
        $WorkShift->deleted = 0;
        $WorkShift->save();

        user_log('Work Shifts', 'create', $WorkShift->shift_name);
        return $this->panelInit->apiOutput( true, "Add work shift", "Shift Created Successfully" );
    }

    public function readWorkshift()
    {
        if( !$this->panelInit->can( "workshifts.edit" ) ) { return $this->panelInit->apiOutput( false, "Edit work shift", "You don't have permission to edit workshifts" ); }
        if( !\Input::has('workshift_id') ) { return $this->panelInit->apiOutput( false, "Edit work shift", "Work shift is not exists" ); }
        $id = \Input::get('workshift_id');
        $WorkShift = WorkShift::find($id);
        if( !$WorkShift ) { return $this->panelInit->apiOutput( false, "Edit work shift", "Work shift is not exists" ); }
        $toReturn = array();
        $output = [
            'workshift_id' => $WorkShift->work_shift_id,
            'name' => $WorkShift->shift_name,
            'start' =>  date('h:i A', strtotime( $WorkShift->start_time )),
            'end' =>  date('h:i A', strtotime( $WorkShift->end_time )),
            'late' =>  date('h:i A', strtotime( $WorkShift->late_count_time ))
        ]; 
        $toReturn['workShift'] = $output;
        return $toReturn;
    }

    public function updateWorkshift()
    {
        if( !$this->panelInit->can( "workshifts.edit" ) ) { return $this->panelInit->apiOutput( false, "Edit work shift", "You don't have permission to edit workshifts" ); }
        if( !\Input::has('workshift_id') ) { return $this->panelInit->apiOutput( false, "Edit work shift", "Work shift is not exists" ); }
        $id = \Input::get('workshift_id');
        $WorkShift = WorkShift::find($id);
        if( !$WorkShift ) { return $this->panelInit->apiOutput( false, "Edit work shift", "Work shift is not exists" ); }

        if( !\Input::has('name') ) { return $this->panelInit->apiOutput( false, "Edit work shift", "Shift Name is missing" ); }
        if( !\Input::has('start') ) { return $this->panelInit->apiOutput( false, "Edit work shift", "Start Time is missing" ); }
        if( !\Input::has('end') ) { return $this->panelInit->apiOutput( false, "Edit work shift", "End Time is missing" ); }
        if( !\Input::has('late') ) { return $this->panelInit->apiOutput( false, "Edit work shift", "Late Count Time is missing" ); }
        
        $start = date('H:i:s', strtotime( \Input::get('start') ));
        $end = date('H:i:s', strtotime( \Input::get('end') ));
        $late = date('H:i:s', strtotime( \Input::get('late') ));
        
        $WorkShift->shift_name = \Input::get('name');
        $WorkShift->start_time = $start;
        $WorkShift->end_time = $end;
        $WorkShift->late_count_time = $late;
        $WorkShift->deleted = 0;
        $WorkShift->save();

        user_log('Work Shifts', 'edit', $WorkShift->shift_name);
        return $this->panelInit->apiOutput( true, "Edit work shift", "Shift Updated Successfully" );
    }

    public function deleteWorkshift()
    {
        if( !$this->panelInit->can( "workshifts.delete" ) ) { return $this->panelInit->apiOutput( false, "Delete work shift", "You don't have permission to delete workshifts" ); }
        if( !\Input::has('workshift_id') ) { return $this->panelInit->apiOutput( false, "Delete work shift", "Work shift is not exists" ); }
        $id = \Input::get('workshift_id');
        $postDelete = WorkShift::find($id);
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete work shift", "Work shift is not exists" ); }
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Work Shifts', 'delete', $postDelete->shift_name);
        return $this->panelInit->apiOutput( true, "Delete work shift", "Work shift Deleted Successfully" );
    }

    public function filterAttendances()
    {
        if( !$this->panelInit->can( "attendances.list" ) ) { return $this->panelInit->apiOutput( false, "Employee Attendance", "You don't have permission to list employee attendance" ); }
        if( !\Input::has('department') ) { return $this->panelInit->apiOutput( false, "Employee Attendance", "Department is missing" ); }
        if( !\Input::has('date') ) { return $this->panelInit->apiOutput( false, "Employee Attendance", "Date is missing" ); }
        $date = formatDate( \Input::get('date') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Employee Attendance", "Date has invalid format" ); }
        if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Employee Attendance", "Date has invalid format" ); }
        $department_id = \Input::get('department');
        $department = Department::find( $department_id );
        if( !$department ) { return $this->panelInit->apiOutput( false, "Employee Attendance", "Department is not exists" ); }
        
        $date = date('Y-m-d', toUnixStamp( $date ));
        $db = "select `hr_employee`.`employee_id`, `hr_employee`.`finger_id`, `hr_employee`.`department_id`,
        CONCAT(
            COALESCE( hr_employee.first_name,''),' ',COALESCE(hr_employee.last_name,'')) as fullName,
            (
                SELECT DATE_FORMAT(MIN(hr_view_employee_in_out_data.in_time), '%h:%i %p')
                FROM hr_view_employee_in_out_data WHERE hr_view_employee_in_out_data.date = \"$date\"
                AND hr_view_employee_in_out_data.finger_print_id = hr_employee.finger_id
            ) AS inTime,
            (
                SELECT DATE_FORMAT(MAX(hr_view_employee_in_out_data.out_time), '%h:%i %p')
                FROM hr_view_employee_in_out_data WHERE hr_view_employee_in_out_data.date = \"$date\"
                AND hr_view_employee_in_out_data.finger_print_id = hr_employee.finger_id
            ) AS outTime from `hr_employee`
            where `hr_employee`.`department_id` = $department_id and `hr_employee`.`status` = 1";
        
        $servername = \Config::get('database.connections.mysql.host');
        $username = \Config::get('database.connections.mysql.username');
        $password = \Config::get('database.connections.mysql.password'); 
        $dbname = \Config::get('database.connections.mysql.database');
        $conn = new \mysqli($servername, $username, $password, $dbname);
        if( $conn->connect_error ) { return $this->panelInit->apiOutput( false, "Employee Attendance", "Something went wrong please try again later" ); }
        $result = $conn->query( $db );
        $key = 0; $attendanceData = []; $count = 0;
        while( $v = $result->fetch_assoc() )
        {
            $attendanceData[$key] = $v;
            $key++;
        }
        $conn->close();
        $output = []; $index = 0;
        $toReturn = array();
        $toReturn['attendences'] = $attendanceData;
        return $toReturn;
    }

    public function takeAttendance()
    {
        if( !$this->panelInit->can( "attendances.take" ) ) { return $this->panelInit->apiOutput( false, "Take Attendance", "You don't have permission to take employee attendance" ); }
        if( !\Input::has('main') ) { return $this->panelInit->apiOutput( false, "Take Attendance", "No valid data to be take attendance" ); }
        if( !\Input::has('secondary') ) { return $this->panelInit->apiOutput( false, "Take Attendance", "No valid data to be take attendance" ); }
        $basic = \Input::get('main');
        if( !array_key_exists('department', $basic) ) { return $this->panelInit->apiOutput( false, "Take Attendance", "Department is missing" ); }
        if( !array_key_exists('date', $basic) ) { return $this->panelInit->apiOutput( false, "Take Attendance", "Date is missing" ); }
        $date = formatDate( $basic['date'] );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Take Attendance", "Date has invalid format" ); }
        if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Take Attendance", "Date has invalid format" ); }
        $department_id = $basic['department'];
        $department = Department::find( $department_id );
        if( !$department ) { return $this->panelInit->apiOutput( false, "Take Attendance", "Department is not exists" ); }

        $date = date('Y-m-d', toUnixStamp( $date ));
        $inputs = \Input::get('secondary');
        if( !count( $inputs ) ) { return $this->panelInit->apiOutput( false, "Take Attendance", "No valid data to be take attendance" ); }
        $dataFormat = []; $index = 0;


        try {
            DB::beginTransaction();
                $result = DB::table(DB::raw("(SELECT hr_employee_attendance.*,hr_employee.`department_id`,  DATE_FORMAT(`hr_employee_attendance`.`in_out_time`,'%Y-%m-%d') AS `date` FROM `hr_employee_attendance`
                INNER JOIN `hr_employee` ON `hr_employee`.`finger_id` = hr_employee_attendance.`finger_print_id`
                WHERE department_id = $department_id) as employeeAttendance"))
                ->select('employeeAttendance.employee_attendance_id')
                ->where('employeeAttendance.date',$date)
                ->get();

                $list = [];
                foreach( $result as $object )
                {
                    $list[] = $object->employee_attendance_id;
                }
                DB::table('hr_employee_attendance')->whereIn('employee_attendance_id',array_values($list))->delete();

                foreach( $inputs as $key => $value )
                {
                    if( isset( $value['inTime'] ) && isset( $value['outTime'] ) )
                    {
                        $InData = [
                            'finger_print_id'       => $value['finger_id'],
                            'in_out_time'           => $date. ' ' .date("H:i:s", strtotime( $value['inTime'] )),
                            'created_at'            => Carbon::now(),
                            'updated_at'            => Carbon::now(),
                        ];
                        EmployeeAttendance::insert($InData);
                        $outData = [
                            'finger_print_id'       => $value['finger_id'],
                            'in_out_time'           => $date. ' ' .date("H:i:s", strtotime( $value['outTime'] )),
                            'created_at'            => Carbon::now(),
                            'updated_at'            => Carbon::now(),
                        ];
                        EmployeeAttendance::insert($outData);
                    }
                    elseif( isset( $value['inTime'] ) )
                    {
                        $InData = [
                            'finger_print_id'       => $value['finger_id'],
                            'in_out_time'           => $date. ' ' .date("H:i:s", strtotime( $value['inTime'] )),
                            'created_at'            => Carbon::now(),
                            'updated_at'            => Carbon::now(),
                        ];
                        EmployeeAttendance::insert($InData);
                    }
                }
            DB::commit();
            $bug = 0;
        }
        catch( \Exception $e ) {
            DB::rollback();
            dd( $e );
            $bug = $e->errorInfo[1];
        }
        if( $bug == 0 ) { return $this->panelInit->apiOutput( true, "Take Attendance", "Attendance took Successfully" ); }
        else { return $this->panelInit->apiOutput( false, "Take Attendance", "Something Error Found !, Please try again" ); }
    }

    public function number_of_working_days_date( $from_date, $to_date )
    {
        $holidays  = DB::select(DB::raw('call SP_getHoliday("'. $from_date .'","'.$to_date .'")'));
        $public_holidays = [];
        foreach( $holidays as $holidays )
        {
            $start_date = $holidays->from_date;
            $end_date   = $holidays->to_date;
            while( strtotime( $start_date ) <= strtotime( $end_date ) )
            {
                $public_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime( $start_date ) ));
            }
        }
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        $weeklyHolidayArray = [];
        foreach ($weeklyHolidays as $weeklyHoliday) { $weeklyHolidayArray[]=$weeklyHoliday->day_name; }
        $target = strtotime($from_date);
        $workingDate = [];

        while ( $target <= strtotime(date("Y-m-d", strtotime($to_date))))
        {
            //get weekly  holiday name
            $timestamp  = strtotime(date('Y-m-d', $target));
            $dayName    = date("l", $timestamp);

            if(!in_array(date('Y-m-d', $target),$public_holidays) && !in_array($dayName,$weeklyHolidayArray)) { array_push($workingDate, date('Y-m-d', $target)); }
            if(date('Y-m-d') <= date('Y-m-d', $target)) { break; }
            $target += (60 * 60 * 24);
        }
        return $workingDate;
    }

    public function getEmployeeLeaveRecord( $from_date, $to_date, $employee_id )
    {
        $queryResult = LeaveApplication::select('application_from_date','application_to_date')
                        ->where('status', '2')
                        ->where('application_from_date','>=',$from_date)
                        ->where('application_to_date','<=',$to_date)
                        ->where('employee_id',$employee_id)
                        ->get();
        $leaveRecord = [];
        foreach( $queryResult as $value )
        {
            $start_date = $value->application_from_date;
            $end_date   = $value->application_to_date;
            while( strtotime($start_date) <= strtotime( $end_date ) ) { $leaveRecord[] = $start_date; $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date))); }
        }
        return $leaveRecord;
    }

    public function hasEmployeeAttendance( $attendance, $finger_print_id, $date )
    {
        foreach( $attendance as $key => $val ) { if( ( $val['finger_print_id'] == $finger_print_id && $val['date'] == $date)) { return true; } }
        return false;
    }

    public function ifHoliday( $govtHolidays, $date )
    {
        $govt_holidays = [];
        foreach( $govtHolidays as $holidays )
        {
            $start_date = $holidays->from_date;
            $end_date   = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date))
            {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }
        foreach ($govt_holidays as $val) { if ($val == $date) { return true; } }
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        $timestamp  = strtotime($date);
        $dayName    = date("l", $timestamp);
        foreach( $weeklyHolidays as $v ) { if($v->day_name == $dayName) { return true; } }
        return false;
    }

    public function ifEmployeeWasLeave( $leave, $employee_id, $date )
    {
        $leaveRecord = [];
        $temp        = [];
        foreach( $leave as $value )
        {
            if( $employee_id == $value->employee_id )
            {
                $start_date = $value->application_from_date;
                $end_date   = $value->application_to_date;
                while( strtotime($start_date) <= strtotime($end_date) )
                {
                    $temp['employee_id']        = $employee_id;
                    $temp['date']               = $start_date;
                    $temp['leave_type_name']    = $value->leave_type_name;
                    $leaveRecord[]              = $temp;
                    $start_date                 = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                }
            }
        }
        foreach ($leaveRecord as $val) { if (($val['employee_id'] == $employee_id && $val['date'] == $date)) { return $val['leave_type_name']; } }
        return false;
    }
}