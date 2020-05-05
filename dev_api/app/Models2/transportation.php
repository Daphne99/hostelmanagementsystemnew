<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class Transportation extends Model {
	public $timestamps = false;
	protected $table = 'transportation';

	public static function getStopageVehicles()
	{
		$stopages = [];
		$table = self::select('id', 'transportTitle as stopage', 'vehicles_list as vehicles')->get()->toArray();
		foreach( $table as $key => $data )
		{
			if( isJsonString( $data['vehicles'] ) )
			{
				$list = json_decode( $data['vehicles'], true );
				$stopages[$data['id']] = [ 'id' => $data['id'], 'name' => $data['stopage'], 'vehicles' => $list ];
			} else continue;
		}
		return $stopages;
	}
}