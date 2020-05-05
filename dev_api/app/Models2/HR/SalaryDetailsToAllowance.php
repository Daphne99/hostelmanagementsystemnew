<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class SalaryDetailsToAllowance extends Model
{
    protected $table = 'hr_salary_details_to_allowance';
    protected $primaryKey = 'salary_details_to_allowance_id';

    protected $fillable = [
        'salary_details_to_allowance_id', 'salary_details_id','allowance_id','amount_of_allowance'
    ];
}
