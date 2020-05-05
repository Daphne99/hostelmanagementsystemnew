<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'hr_branch';
    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'branch_id', 'branch_name'
    ];
}
