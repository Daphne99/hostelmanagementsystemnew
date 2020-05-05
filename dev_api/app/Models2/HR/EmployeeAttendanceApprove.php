<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceApprove extends Model
{
    protected $table = 'hr_employee_attendance_approve';
    protected $primaryKey = 'employee_attendance_approve_id';

    protected $fillable = [
        'employee_attendance_approve_id','employee_id','finger_print_id','date','in_time','out_time','working_hour','approve_working_hour','created_by','updated_by'
    ];

}
