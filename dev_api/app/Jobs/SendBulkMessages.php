<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models2\NotificationMobHistory;
use App\Models2\User;
use App\Models2\messages_list;
use App\Models2\messages;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Bus\Queueable;

class SendBulkMessages extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;
    
    public $toId;
    public $messageText;
    public $is_enable_reply;
    public $currentUser;
    public $fullName;
    public $settings;
    
    public function __construct( $toId, $messageText, $is_enable_reply, $currentUser, $fullName, $settings )
    {
        $this->toId = $toId;
        $this->messageText = $messageText;
        $this->is_enable_reply = $is_enable_reply;
        $this->currentUser = $currentUser;
        $this->fullName = $fullName;
        $this->settings = $settings;
    }

    public function handle(NotificationMobHistory $notificationMobHistory)
    {
        $toId = $this->toId;
        $messageText = $this->messageText;
        $is_enable_reply = $this->is_enable_reply;
        $currentUser = $this->currentUser;
        $fullName = $this->fullName;
        $settings = $this->settings;

        $users_list = array();
		$enable_reply_array = [];
		User::$withoutAppends = true;

        if( is_array($toId) )
        {
			$users_ids = array();
			$toId_list = $toId;
			foreach ($toId_list as $key => $id) { $users_ids[] = $id; }
			$users = User::whereIn('id',$users_ids);
			if($users->count() == 0) { echo $this->panelInit->language['userisntExist']; exit; }
			$users_array = $users->get();

            foreach ($users_array as $key => $user)
            {
                if($user->role == 'student') { foreach (User::getRealParentIdsFromStudentId($user->id) as $key => $parent_id) { $users_list[] = $parent_id; } }
                else { $users_list[] = $user->id; }
			}
        }
        else
        {
			$users = User::where('username',$toId)->orWhere('email',$toId);
			if($users->count() == 0) { echo $this->panelInit->language['userisntExist']; exit; }
			$users_list[] = $users->first()->id;
		}

        
        foreach( $users_list as $key => $user_id )
        {
            $tokens_list = array();
            $user_ids = array();
			if($user_id == $currentUser) { continue; }

			$messagesList = messages_list::where('userId',$currentUser)->where('toId',$user_id);
            if($messagesList->count() == 0)
            {
				$messagesList = new messages_list();
				$messagesList->userId = $currentUser;
				$messagesList->toId = $user_id;
            } else { $messagesList = $messagesList->first(); }

            $messagesList->lastMessage = $messageText;
			$messagesList->lastMessageDate = time();
            $messagesList->messageStatus = 0;
            $messagesList->enable_reply = $is_enable_reply;
			$messagesList->save();
			$toReturnId = $messagesList->id;

			$messagesList_ = messages_list::where('userId',$user_id)->where('toId',$currentUser);
            if($messagesList_->count() == 0)
            {
				$messagesList_ = new messages_list();
				$messagesList_->userId = $user_id;
				$messagesList_->toId = $currentUser;
            }
            else
            {
				$messagesList_ = $messagesList_->first();
			}
			$messagesList_->lastMessage = $messageText;
			$messagesList_->lastMessageDate = time();
            $messagesList_->messageStatus = 1;
            $messagesList_->enable_reply = $is_enable_reply;
			$messagesList_->save();
			$toReturnId_ = $messagesList_->id;

			$messages = new messages();
			$messages->messageId = $toReturnId;
			$messages->userId = $currentUser;
			$messages->fromId = $currentUser;
			$messages->toId = $user_id;
			$messages->messageText = $messageText;
            $messages->dateSent = time();
            $messages->enable_reply = $is_enable_reply;
			$messages->save();

			$messages = new messages();
			$messages->messageId = $toReturnId_;
			$messages->userId = $user_id;
			$messages->fromId = $currentUser;
			$messages->toId = $user_id;
			$messages->messageText = $messageText;
			$messages->dateSent = time();
            $messages->enable_reply = $is_enable_reply;
            $messages->save();

			$firebase_token = User::find($user_id)->firebase_token;
			if( is_array( json_decode( $firebase_token ) ) ) { foreach (json_decode($firebase_token) as $token) { $tokens_list[] = $token; } }
            elseif( $this->isJson( $firebase_token ) ) { foreach (json_decode($firebase_token) as $token) { $tokens_list[] = $token; } }
            else { $tokens_list[] = $firebase_token; }
			$user_ids[] = $user_id;
            $notificationMobHistory->sendPushNotification(
                $tokens_list,
                $user_ids,
                "new Message From" . " ". $fullName,
                "messages",
                "messages",
                $toReturnId_,
                $settings
            );
    
            $notificationMobHistory->saveNotificationsToDBModel(
                $tokens_list,
                $user_ids,
                "new Message From" . " ". $fullName,
                "Messages",
                "messages",
                $toReturnId_,
                date('Y-m-d H:i:s')
            );
        }
        
        return json_encode(array('messageId'=>"home"));
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