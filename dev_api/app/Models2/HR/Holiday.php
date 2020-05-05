<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'hr_holiday';
    protected $primaryKey = 'holiday_id';

    protected $fillable = [
        'holiday_id', 'holiday_name'
    ];
}
