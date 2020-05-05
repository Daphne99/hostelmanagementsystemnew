<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class Newsboard extends Model {
	public $timestamps = false;
	protected $table = 'newsboard';

	public function user()
	{
		return $this->belongsTo( User::class, 'newsCreator', 'id');
	}
}