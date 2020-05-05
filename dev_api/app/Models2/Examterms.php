<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class Examterms extends Model {
	public $timestamps = true;
	protected $table = 'examterms';
	
	public function examslists()
	{
		return $this->hasMany('App\Models2\ExamsList','id','exam_id');
	}
}