<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $table = 'hr_designation';
    protected $primaryKey = 'designation_id';

    protected $fillable = [
        'designation_id', 'designation_name'
    ];


}
