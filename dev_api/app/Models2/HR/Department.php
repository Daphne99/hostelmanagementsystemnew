<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'hr_department';
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'department_id', 'department_name'
    ];
}
