<?php

namespace App\Models2;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models2\User;

class Attendance extends Model {
	public $timestamps = false;
	protected $table = 'attendance';
	// public function user() { User::$withoutAppends = true; return $this->belongsTo( User::class,'studentId', 'id'); }
}