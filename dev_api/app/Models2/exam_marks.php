<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class exam_marks extends Model {
	public $timestamps = false;
	protected $table = 'exam_marks';

	public function getExam()
	{
		return $this->belongsTo( exams_list::class, 'examId', 'id');
	}
}