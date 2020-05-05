<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'hr_leave_type';
    protected $primaryKey = 'leave_type_id';

    protected $fillable = [
        'leave_type_id', 'leave_type_name','num_of_day'
    ];
}
