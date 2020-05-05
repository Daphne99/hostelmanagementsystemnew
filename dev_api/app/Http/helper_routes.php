<?php

use Hashids\Hashids;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

Route::get('usage', function(){
	$free = shell_exec('free');
	$free = (string)trim($free);
	$free_arr = explode("\n", $free);
	$mem = explode(" ", $free_arr[1]);
	$mem = array_filter($mem);
	$mem = array_merge($mem);
	$memory_usage = $mem[2]/$mem[1]*100;
	$load = sys_getloadavg();

	return view('usage')->with([
		'memory_usage' => $memory_usage,
		'cpu_usage' => $load[0]
	]);
});

Route::get('remove-cache', function(){
	\Cache::flush();
	return redirect('/portal#/');
});

Route::get('clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
	return redirect('/portal#/');
});

Route::get('remove-all-firebasetokens', function(){
	DB::table('users')->update(['firebase_token' => '']);
});