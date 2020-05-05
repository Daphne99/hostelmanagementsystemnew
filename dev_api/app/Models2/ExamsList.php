<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class ExamsList extends Model {
	public $timestamps = false;
	protected $table = 'exams_list';

	public function term()
	{
		return $this->belongsTo( SchoolTerm::class, 'school_term_id', 'id');
	}
}
