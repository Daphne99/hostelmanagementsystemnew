<?php

namespace App\Jobs;

use Exception;
use App\Jobs\Job;
use App\Models2\User;
use App\Models2\Event;
use App\Models2\NotificationMobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SendNotification extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;
    public $type;
    public $name;
    public $desc;
    public $recipients;
    public $date;
    public $dueDate;
    public $settings;

    public function __construct( $type, $name, $desc, $recipients, $date, $dueDate, $settings )
    {
        $this->type = $type;
        $this->name = $name;
        $this->desc = $desc;
        $this->recipients = $recipients;
        $this->date = $date;
        $this->dueDate = $dueDate;
        $this->settings = $settings;
    }

    public function handle( NotificationMobHistory $notificationMobHistory )
    {
        User::$withoutAppends = true;

        $type = $this->type;
        $name = $this->name;
        $desc = $this->desc;
        $recipients = $this->recipients;
        $date = $this->date;
        $dueDate = $this->dueDate;
        $settings = (array)$this->settings;
		$user_ids = array();
        $tokens_list = array();
        if( $type == "Events" )
        {
            $user_list = User::select('id', 'firebase_token', 'role')->whereIn('id', $recipients)->get()->toArray();
            foreach( $user_list as $value )
            {
                $id = $value['id'];
                if( $value['role'] == "student" )
                {
                    $parents = User::select('id', 'firebase_token', 'role')->where('parentOf','like','%"'.$id.'"%')->orWhere('parentOf','like','%:'.$id.'}%')->first();
                    if(!$parents) continue;
                    if( $parents->firebase_token != "" )
                    {
                        if( is_array( json_decode( $parents->firebase_token ) ) )
                        {
                            foreach( json_decode( $parents->firebase_token ) as $token ) { $tokens_list[] = $token; }
                        }
                        elseif( $this->isJson( $parents->firebase_token ) )
                        {
                            foreach (json_decode($parents->firebase_token) as $token) { $tokens_list[] = $token; }
                        } else { $tokens_list[] = $parents->firebase_token; }
                    }
                    $user_ids[] = $parents->id;
                }
                else
                {
                    $fireBaseToken = $value['firebase_token'];
                    if( $fireBaseToken != "" )
                    {
                        if( is_array( json_decode( $fireBaseToken ) ) )
                        {
                            foreach (json_decode( $fireBaseToken ) as $token ) { $tokens_list[] = $token; }
                        }
                        elseif( $this->isJson( $fireBaseToken ) ) {
                            foreach (json_decode($fireBaseToken) as $token) { $tokens_list[] = $token; }
                        } else { $tokens_list[] = $fireBaseToken; }
                    }
                    $user_ids[] = $id;
                }
            }
            $starting = date( 'jS M Y', $date);
            if( $date && $dueDate )
            {
                $ending = date( 'jS M Y', $dueDate);
                if( $starting == $ending ) { $message = 'A new Event: ' . $name . " will be held during " . $starting; }
                else { $message = 'A new Event: ' . $name . " will be held on " . $starting . " - " . $ending; }
            } else { $message = 'A new Event: ' . $name . " will be held on " . $starting; }
            
            //Send Push Notifications
            $notificationMobHistory->sendPushNotification(
                $tokens_list,
                $user_ids,
                $message,
                "New event",
                "",
                "",
                $settings
            );
            echo 'Send firebase event notification for ' . $name . PHP_EOL;

            $notificationMobHistory->saveNotificationsToDBModel(
                $tokens_list,
                $user_ids,
                $message,
                "New event",
                "",
                "",
                date('Y-m-d')
            );
            echo '['. date('Y-m-d') .'] Save notification to DB for event : ' . $name . PHP_EOL;
        }
    }

    public function isJson( $string )
    {
	    $decoded = json_decode( $string );
	    if ( !is_object( $decoded ) && !is_array( $decoded ) ) { return false; }
	    return (json_last_error() == JSON_ERROR_NONE);
	}

    public function failed( \Exception $exception )
    {
        \Log::error($exception->getMessage());
    }
}