<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class Messaging extends Model {
	public $timestamps = false;
	protected $table = 'messages_list_grouped';

	public function user()
	{
		return $this->belongsTo(User::class, 'userId', 'id');
	}
}
