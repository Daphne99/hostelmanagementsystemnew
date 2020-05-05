<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'hr_notice';
    protected $primaryKey = 'notice_id';

    protected $fillable = [
        'notice_id', 'title','description','status','created_by','updated_by','publish_date','attach_file'
    ];

    public function createdBy(){
        return $this->belongsTo(Employee::class,'created_by');
    }
}
