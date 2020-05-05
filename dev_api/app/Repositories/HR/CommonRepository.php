<?php

namespace App\Repositories\HR;

use App\Models2\HR\PerformanceCategory;

use Illuminate\Support\Facades\DB;

use App\Models2\HR\TrainingType;

use App\Models2\HR\WorkShift;

use App\Models2\HR\PayGrade;

use App\Models2\HR\Employee;

use App\Models2\HR\Role;


class CommonRepository
{

    public function roleList(){
        $results = Role::get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->role_id] = $value->role_name;
        }
        return $options ;
    }


    public function userList(){
        $results = DB::table('hr_user')->where('status',1)->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->user_id] = $value->user_name;
        }
        return $options ;
    }


    public function departmentList(){
        $results = DB::table('hr_department')->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->department_id] = $value->department_name;
        }
        return $options ;
    }


    public function designationList(){
        $results = DB::table('hr_designation')->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->designation_id] = $value->designation_name;
        }
        return $options ;
    }


    public function branchList(){
        $results = DB::table('hr_branch')->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->branch_id] = $value->branch_name;
        }
        return $options ;
    }


    public function supervisorList(){
        $results = DB::table('hr_employee')->where('status',1)->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->employee_id] = $value->first_name.' '.$value->last_name;
        }
        return $options ;
    }


    public function holidayList(){
        $results = DB::table('hr_holiday')->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->holiday_id] = $value->holiday_name;
        }
        return $options ;
    }


    public function weekList(){
        $results = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'];
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value] = $value;
        }
        return $options ;
    }


    public function leaveTypeList(){
        $results = DB::table('hr_leave_type')->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->leave_type_id] = $value->leave_type_name;
        }
        return $options ;
    }


    public function workShiftList(){
        $results = WorkShift::get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->work_shift_id] = $value->work_shift_name;
        }
        return $options ;
    }


    public function employeeList(){
        $results = Employee::where('status',1)->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->employee_id] = $value->first_name.' '.$value->last_name;
        }
        return $options ;
    }

    public function performanceCategoryList(){
        $results = PerformanceCategory::all();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->performance_category_id] = $value->performance_category_name;
        }
        return $options ;
    }

    public function getEmployeeInfo($id){
        return  Employee::where('user_id',$id)->first();
    }


    public function getEmployeeDetails($id){
        return  Employee::where('employee_id',$id)->first();
    }

    public function trainingTypeList(){
        $results = TrainingType::where('status',1)->get();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->training_type_id] = $value->training_type_name;
        }
        return $options ;
    }

    public function payGradeList(){
        $results = PayGrade::all();
        $options = [''=>'---- Please select ----'];
        foreach ($results as $key => $value) {
            $options [$value->pay_grade_id] = $value->pay_grade_name;
        }
        return $options ;
    }

}
