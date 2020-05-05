<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class HolidayDetails extends Model
{
    protected $table = 'hr_holiday_details';
    protected $primaryKey = 'holiday_details_id';

    protected $fillable = [
        'holiday_details_id','holiday_id', 'from_date','to_date','comment'
    ];

    public function holiday(){
        return $this->belongsTo(Holiday::class,'holiday_id','holiday_id');
    }
}
