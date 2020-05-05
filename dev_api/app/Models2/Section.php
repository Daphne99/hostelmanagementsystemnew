<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class Section extends Model {
	use Rememberable;

	public $timestamps = false;
	protected $table = 'sections';

	public static function getSectionsUponClass()
	{
		$sections = self::select('id', 'sectionName as name', 'classId')->get()->toArray();
		$output = [];
		foreach( $sections as $section )
		{
			$output[ $section['classId'] ][] = [ 'id' => $section['id'], 'name' => $section['name'] ];
		}
		return $output;
	}
}
