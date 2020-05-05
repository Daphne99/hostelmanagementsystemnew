<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class SubSubject extends Model {
	public $timestamps = false;
	protected $table = 'sub_subjects';
	public $appends = ['type'];

	public function gettypeAttribute() { return 'sub'; }
}