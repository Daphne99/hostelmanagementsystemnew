<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models2\User;
use App\Models2\NotificationMobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SendAssignmentNotification extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;
    public $classId;
    public $sectionId;
    public $enableSections;
    public $newAssigmentAdded;
    public $AssignTitle;
    public $assignmentsId;
    public $settings;

    public function __construct($classId, $sectionId, $enableSections, $newAssigmentAdded, $AssignTitle, $assignmentsId, $settings)
    {
        $this->classId = $classId;
        $this->sectionId = $sectionId;
        $this->enableSections = $enableSections;
        $this->newAssigmentAdded = $newAssigmentAdded;
        $this->AssignTitle = $AssignTitle;
        $this->assignmentsId = $assignmentsId;
        $this->settings = $settings;
    }

    public function handle( NotificationMobHistory $notificationMobHistory )
    {
        $classId = $this->classId;
        $sectionId = $this->sectionId;
        $enableSections = $this->enableSections;
        $newAssigmentAdded = $this->newAssigmentAdded;
        $AssignTitle = $this->AssignTitle;
        $assignmentsId = $this->assignmentsId;
        $settings = $this->settings;

        User::$withoutAppends = true;
        $tokens_list = array();
		$user_ids = array();
		$user_list = User::where('role','student')->whereIn('studentClass', $classId);
		if( $enableSections == true) { $user_list = $user_list->whereIn('studentSection', $sectionId); }
		$user_list = $user_list->select('id', 'firebase_token')->get();
		$student_id = array();
		foreach($user_list as $ite) { $student_id[]="%\"id\":".$ite->id."}%" ; }
        
        $user_list_parents = array();
        foreach($student_id as $itp)
        {
			$res = User::where('role','parent') ->where('parentOf','like',$itp) ->select('id', 'firebase_token')->first();
			if($res) { $user_list_parents[] = $res; }
		}

        foreach( $user_list_parents as $value )
        {
            if( $value['firebase_token'] != "" )
            {
                if( is_array( json_decode( $value['firebase_token'] ) ) ) { foreach( json_decode( $value['firebase_token'] ) as $token ) { $tokens_list[] = $token; } }
                else if( $this->isJson( $value['firebase_token'] ) ) { foreach (json_decode($value['firebase_token']) as $token) { $tokens_list[] = $token; } }
                else { $tokens_list[] = $value['firebase_token']; }
			}
			$user_ids[] = $value['id'];
		}

        if( count( $tokens_list ) > 0 )
        {
            $notificationMobHistory->sendPushNotification( $tokens_list, $user_ids, $AssignTitle, $newAssigmentAdded, "assignment", $assignmentsId, $settings );
        }
        else
        {
			$notificationMobHistory->saveNotificationsToDBModel( $tokens_list, $user_ids, $AssignTitle, $newAssigmentAdded, "assignment", $assignmentsId, date('Y-m-d H:i:s') );
		}
    }

    public function failed(Exception $exception)
    {
        \Log::error($exception->getMessage());
    }
}