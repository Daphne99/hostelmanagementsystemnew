<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $table = 'hr_interview';
    protected $primaryKey = 'interview_id';

    protected $fillable = [
        'interview_id', 'job_applicant_id','interview_date','interview_time','interview_type','comment'
    ];
}
