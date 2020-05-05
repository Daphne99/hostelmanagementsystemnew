<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models2\NotificationMobHistory;

function all_pagination_number()
{
	return env('APP_TYPE') == 'mob' ? 10 : ( env('APP_TYPE') == 'web' ? 20 : 20 );
}

// make notification seen
function updateSeenNotificationMobHistory($user_id, $type, $payload_id)
{
	$query = NotificationMobHistory::where('user_id', $user_id)->where('type', $type);
	if(!is_null($payload_id)) { $query->where('payload_id', $payload_id); }
	$NotificationMobHistory = $query->pluck('id');
	if( $query->count() ) { NotificationMobHistory::whereIn('id', $NotificationMobHistory)->update(['is_seen' => 1]); }
}

function uploads_config()
{
	return [
		'uploads_provider' => 'https://upload.cutebrains.com/dev',
		'uploads_file_path' => '../dev_uploads',
	];
}

function get_server_info()
{
	// change $server_type to "production" when you deploy to live server
	$server_type = 'local';
	$database_name = 'cute_brains';
	$app_debug = true;
	$debugbar_enable = true;
	return [
		'server_type' => $server_type,
		'database_name' => $database_name,
		'app_debug' => $app_debug,
		'debugbar_enable' => $debugbar_enable,
		'user_ip' => request()->ip(),
	];
}

function user_log($module_name, $action, $payload = null)
{
	if( Auth::check() )
	{
		//
	}
}

function getGrade( $mark )
{
	if( $mark >= 0 && $mark <= 32 ) return "E";
	if( $mark >= 33 && $mark <= 40 ) return "D";
	if( $mark >= 41 && $mark <= 50 ) return "C2";
	if( $mark >= 51 && $mark <= 60 ) return "C1";
	if( $mark >= 61 && $mark <= 70 ) return "B2";
	if( $mark >= 71 && $mark <= 80 ) return "B1";
	if( $mark >= 81 && $mark <= 90 ) return "A2";
	if( $mark >= 91 && $mark <= 100 ) return "A1";
	else return "A1";
}

function isJsonString( $string )
{
	$decoded = json_decode( $string );
	if ( !is_object( $decoded ) && !is_array( $decoded ) ) { return false; }
	return ( json_last_error() == JSON_ERROR_NONE );
}

function toUnixStamp( $date )
{
	return strtotime( $date );
}

function humanDuration( $seconds )
{
	if( $seconds <= 60 ) return '1 minute';
	if( $seconds > 60 && $seconds <= 86400 ) return 'Less than day';
	if( $seconds > 86400 && $seconds <= 604800 ) return 'Less than week';
	if( $seconds > 604800 && $seconds <= 2592000 ) return 'Less than month';
	if( $seconds > 2592000 && $seconds <= 31536000 ) return 'Less than year';
	$years = intval( $seconds / 31536000 );
	return $years == 1 ? "$years year" : "$years years";
}

function formatDate( $dateString )
{
	$output = '';
	$exploded = explode('/', $dateString );
	if( count( $exploded ) )
	{
		foreach( $exploded as $key => $item )
		{
			$output = $key == 0 ? $item : $output . '-' . $item;
		}
	}
	if( $output == '' ) $output = NULL;
	return $output;
}

function formatDate2( $dateString )
{
	$output = '';
	$exploded = explode('/', $dateString );
	if( count( $exploded ) == 3 ) { $output = $exploded[1] . '-' . $exploded[0] . '-' . $exploded[2]; }
	if( $output == '' ) $output = NULL;
	return $output;
}

function getEmployeeAge($date_of_birth) {
	$birthday = new Carbon ($date_of_birth);
	$currentDate = new Carbon ( 'now' );
	$interval = $birthday->diff ( $currentDate );
	return $interval->y;
}

function findMonthToAllDate($month){
	$start_date  = $month.'-01';
	$end_date    = date("Y-m-t", strtotime($start_date));
	$target      = strtotime($start_date);
	$workingDate = [];
	while ($target <= strtotime(date("Y-m-d", strtotime($end_date)))) {
		$temp = [];
		$temp['date'] = date('Y-m-d', $target);
		$temp['day']  = date('d', $target);
		$temp['day_name']  =date('D', $target);
		$workingDate[] = $temp;
		$target += (60 * 60 * 24);
	}
	return $workingDate;
}


function findMonthToStartDateAndEndDate($month){
	$start_date = $month.'-01';
	$end_date   = date("Y-m-t", strtotime($start_date));
	$data = [
		'start_date' =>$start_date,
		'end_date'   =>$end_date,
	];
	return $data;
}

function dateConvertFormtoDB($date){
	if( !empty($date) ) { return date("Y-m-d",strtotime(str_replace('/','-',$date))); }
}

function generateTimeRange( $query )
{
	$startDate = date('Y-m-01', strtotime($query));
	$endDate = date('Y-m-t', strtotime($query));
	$from = Carbon::parse($startDate);
	$to = Carbon::parse($endDate);
	$dates = [];
	for($d = $from; $d->lte($to); $d->addDay()) {
		$dates[] = [
			'dayOfMonth' => $d->format('d'),
			'date' => $d->format('Y-m-d'),
			'day' => $d->format('D'),
			'visDate' => $d->format('jS M Y'),
		];
	}
	// F 	=> February
	// D	=> Sun
	// dd( $from->subMonth()->format('Y-m-d') );
	return $dates;
}

function getMonthNumByIndex( $index )
{
	if( $index == 0 ) return "04";
	if( $index == 1 ) return "05";
	if( $index == 2 ) return "06";
	if( $index == 3 ) return "07";
	if( $index == 4 ) return "08";
	if( $index == 5 ) return "09";
	if( $index == 6 ) return "10";
	if( $index == 7 ) return "11";
	if( $index == 8 ) return "12";
	if( $index == 9 ) return "01";
	if( $index == 10 ) return "02";
	if( $index == 11 ) return "03";
}

function getDatesBetweenDate( $start, $end )
{
	$startDate = date('Y-m-d', strtotime($start));
	$endDate = date('Y-m-d', strtotime($end));
	$from = Carbon::parse($startDate);
	$to = Carbon::parse($endDate);
	$dates = [];
	for($d = $from; $d->lte($to); $d->addDay()) {
		$dates[] = [
			'dayOfMonth' => $d->format('d'),
			'date' => $d->format('Y-m-d'),
			'day' => $d->format('D')
		];
	}
	return $dates;
}

function getDayName( $name )
{
	if( $name == 'saturday' ) return 'Sat';
	elseif( $name == 'sunday' ) return 'Sun';
	elseif( $name == 'monday' ) return 'Mon';
	elseif( $name == 'tuesday' ) return 'Tue';
	elseif( $name == 'wednesday' ) return 'Wed';
	elseif( $name == 'thursday' ) return 'Thu';
	elseif( $name == 'friday' ) return 'Fri';
	else return 'Sun';
}

function getDayNum( $name )
{
	if( $name == 'sunday' ) return 1;
	elseif( $name == 'monday' ) return 2;
	elseif( $name == 'tuesday' ) return 3;
	elseif( $name == 'wednesday' ) return 4;
	elseif( $name == 'thursday' ) return 5;
	elseif( $name == 'friday' ) return 6;
	elseif( $name == 'saturday' ) return 7;
	else return 1;
}

function stampModifier( $stamp )
{
	$thoseDate = date('Y-m-d', $stamp);
	$stampToModify = strtotime( $thoseDate );
	$stampToModify = $stampToModify - 12600;
	$modified = strtotime( date('Y-m-d h:i:s A', $stampToModify) );
	$finalStamp = $modified + 12600;
	return strtotime( date('Y-m-d h:i:s A', $finalStamp) );
}

function monthView( $year, $month = null )
{
	$res = $year >= 1970;
    if ($res)
    {
        // this line gets and sets same timezone, don't ask why :)
        date_default_timezone_set(date_default_timezone_get());
        $dt = strtotime("-1 day", strtotime("$year-01-01 00:00:00"));
        $res = array();
        $week = array_fill(1, 7, false);
        $last_month = 1;
        $w = 1;
        do {
            $dt = strtotime('+1 day', $dt);
            $dta = getdate($dt);
            $wday = $dta['wday'] == 0 ? 7 : $dta['wday'];
            if (($dta['mon'] != $last_month) || ($wday == 1))
            {
                if ($week[1] || $week[7]) $res[$last_month][] = $week;
                $week = array_fill(1, 7, false);
                $last_month = $dta['mon'];
            }
            $week[$wday] = $dta['mday'];
        }
      while ($dta['year'] == $year);
	}
	$currMonth = intval( date('m') );
	if( !$month ) return $res[$currMonth];
	else
	{
		if( $month >= 1 && $month <= 12 ) return $res[$month];
	}
}

function like_match($pattern, $subject)
{
    $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
    return (bool) preg_match("/^{$pattern}$/i", $subject);
}