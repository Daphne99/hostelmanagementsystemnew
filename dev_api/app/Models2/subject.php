<?php

namespace App\Models2;
use Watson\Rememberable\Rememberable;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model {
	public $timestamps = false;
	protected $table = 'subject';
	public $appends = ['type'];
	use Rememberable;

	public function gettypeAttribute() { return 'main'; }

	public static function getSubjects( $subjectsIds )
	{
		return self::select('id', 'subjectTitle as name')->remember(60 * 4)->whereIn('id', $subjectsIds)->get()->toArray();
	}
}