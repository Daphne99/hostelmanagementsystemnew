<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
    protected $table = 'hr_role';
    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_id', 'role_name'
    ];


}
