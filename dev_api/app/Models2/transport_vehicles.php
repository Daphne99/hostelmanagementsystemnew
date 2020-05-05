<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

class transport_vehicles extends Model {
	public $timestamps = false;
	protected $table = "transport_vehicles";

	public static function getVehicles()
	{
		$output = [];
		$vehicles = self::select('id', 'plate_number as plate', 'driver_name as name', 'stoppagesList as stoppages')->get()->toArray();
		foreach( $vehicles as $vehicle )
		{
			$output[ $vehicle['id'] ] = [
				'id' => $vehicle['id'],
				'plate' => $vehicle['plate'],
				'name' => $vehicle['name'],
				'stoppages' => json_decode( $vehicle['stoppages'], true )
			];
		}
		return $output;
	}
}