<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class PayGradeToAllowance extends Model
{
    protected $table = 'hr_pay_grade_to_allowance';
    protected $primaryKey = 'pay_grade_to_allowance_id';

    protected $fillable = [
        'pay_grade_to_allowance_id', 'pay_grade_id','allowance_id'
    ];
}
