<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class MarkSheetStudents extends Model {
	public $timestamps = false;
	protected $table = "marksheet_students";

	public function MarkSheet()
	{
		return $this->belongsTo( MarkSheet::class, 'marksheet_id', 'id');
	}

	public function User()
	{
		return $this->belongsTo( User::class, 'student_id', 'id');
	}
}