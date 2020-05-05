<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models2\User;
use App\Models2\Main;
use App\Models2\messages_list;
use App\Models2\messages;
use App\Models2\Messaging;
use App\Models2\Section;
use App\Models2\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendMessageNotification;
use Carbon\Carbon;

class MessagingController extends Controller
{
    var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

    public function __construct()
    {
		if(app('request')->header('Authorization') != "" || \Input::has('token')) { $this->middleware('jwt.auth'); }
        else { $this->middleware('authApplication'); }

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) { return \Redirect::to('/'); }
    }

    public function preLoad()
    {
        $classesArray = array();
        if( $this->data['users']->role == "teacher" )
		{
			$class_ids = Main::getClassesIdsByTeacherId($this->data['users']->id);
			$classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->whereIn('id', $class_ids)->get()->toArray();
		} else { $classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->get()->toArray(); }

		$classesArray = array();
		foreach( $classes as $class )
		{
			$class_id = $class['id']; $name = $class['className'];
			$sections = Section::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
			foreach( $sections as $innerKey => $section ) { $sections[$innerKey]['name'] = "($name)" . " " . $section['name']; }
			$subjectsIds = Main::getSubjectIdsByClassId( $class_id );
			$classesArray[ $class_id ] = [ 'id' => $class_id, 'name' => $name, 'sections' => $sections ];
		}
		
		$toReturn['classes'] = $classesArray;
        $toReturn['roles'] = Role::select("id", "role_title as name")->get()->toArray();
        $toReturn['type'] = [ [ "id" => "acad", "name" => "Academic" ],[ "id" => "non", "name" => "Non Academic" ] ];
        $toReturn['academic'] = [ [ "id" => "teach", "name" => "Teachers" ],[ "id" => "non", "name" => "Students" ] ];
        return $toReturn;
    }

    public function listMessages( $page = 1 )
	{
        if( !$this->panelInit->can( array( "messaging.list", "messaging.View", "messaging.editMsg", "messaging.delMsg" ) ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to list messages");
        }
        $toReturn = array();
        $toReturn['status'] = "success";
		User::$withoutAppends = true;
		$messaging = Messaging::where('userId', Auth::guard('web')->user()->id)
            ->with('user')
            ->orderBy('lastMessageDate','DESC');
        if( \Input::has('type1') )
        {
            $userIds = [];
            $type = \Input::get('type1'); $role = \Input::get('type2');
            if( $type == 'non' )
            {
                $user_Ids = User::select('id', 'fullName')->where('role_perm', $role)->pluck('id');
                foreach( $user_Ids as $id ) { if( !in_array( $id, $userIds ) ) { $userIds[] = $id; } }
            }
            else
            {
                if( $role == 'non' )
                {
                    $class_ids = \Input::get('class');
                    $section_ids = \Input::get('section');
                    $students_ids = User::select('id', 'fullName')->where('studentSection', $section_ids)->pluck('id');
                    $user_Ids = User::getParentIdsFromStudentsIds($students_ids);
                }
                else
                {
                    $class_ids = \Input::get('class');
                    $user_Ids = Main::getTeacherIdsByClassIds( $class_ids );
                }
                foreach( $user_Ids as $id ) { if( !in_array( $id, $userIds ) ) { $userIds[] = $id; } }
            }
            if( count( $userIds ) == 0 ) { $messaging = $messaging->Where('id', '0'); }
            else
            {
                $messaging = $messaging->Where(
                    function ( $messaging ) use( $userIds ) {
                    foreach($userIds as $recipient_id ) { $messaging->orwhere('recipients_ids', 'LIKE', '%"' . $recipient_id . '"%'); }
               });
            }
            $toReturn['filter'] = true;
        } else $toReturn['filter'] = false;
        $toReturn['totalItems'] = $messaging->count();
        $messaging = $messaging->take(all_pagination_number())
            ->skip(all_pagination_number() * ($page - 1))
            ->get();
        $toReturn['messages'] = $messaging;
		foreach( $toReturn['messages'] as $key => $value) {
            $messages_ids = json_decode( $value['messages_ids'], true);
            if( json_last_error() != JSON_ERROR_NONE ) $messages_ids = [];
            $recipients_ids = json_decode( $value['recipients_ids'], true);
            if( json_last_error() != JSON_ERROR_NONE ) $recipients_ids = [];
            
            $toReturn['messages'][$key]['messages_ids'] = $messages_ids;
            $toReturn['messages'][$key]['recipients_ids'] = $recipients_ids;
			$toReturn['messages'][$key]['students'] = [];
            $toReturn['messages'][$key]['isGroup'] = count( $recipients_ids ) > 1 ? true : false;
            if( strlen( $toReturn['messages'][$key]['lastMessage'] ) <= 18 )
                $toReturn['messages'][$key]['lastMiniMessage'] = $toReturn['messages'][$key]['lastMessage'];
            else
                $toReturn['messages'][$key]['lastMiniMessage'] = substr( $toReturn['messages'][$key]['lastMessage'], 0, 18 ) . "...";
            if( $toReturn['messages'][$key]['isGroup'] )
            {
                $toReturn['messages'][$key]['sender_receiver_data'] = ['fullName' => 'Group Message'];
                $toReturn['messages'][$key]['students'] = [];
            }
            else
            {
                $toId = $toReturn['messages'][$key]['recipients_ids'][0];
                $toReturn['messages'][$key]['sender_receiver_data'] = User::find($toId);
                if($toReturn['messages'][$key]['sender_receiver_data']) {
                	$toReturn['messages'][$key]['receiver_role'] = $toReturn['messages'][$key]['sender_receiver_data']->role;
                }
                $toId = $toReturn['messages'][$key]['recipients_ids'][0];
                if($toReturn['messages'][$key]['receiver_role'] == 'parent') {
                    $students_ids = User::getStudentsIdsFromParentId($toId);
                    $student_collection = User::whereIn('id', $students_ids)
                      ->select('id', 'fullName')
                      ->get()->toArray();
                    $toReturn['messages'][$key]['students'] = $student_collection;
                } else $toReturn['messages'][$key]['students'] = [];
            }
		}

		foreach( $toReturn['messages'] as $key => $value ) {
            $stamp = $toReturn['messages'][$key]->lastMessageDate;
            $toReturn['messages'][$key]->lastMessageDate = $this->panelInit->unix_to_date($stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
            $toReturn['messages'][$key]->dateSentH = $this->panelInit->unix_to_date( $stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
            $toReturn['messages'][$key]->dateH = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
            $toReturn['messages'][$key]->dateHour = date('h:i A', $stamp);
            $toReturn['messages'][$key]->isMultiple = true;
            $toReturn['messages'][$key]->dateToolTip = date('jS F Y h:i:s A', $stamp);
                
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'messages', null);
		return $toReturn;
	}
    
    public function create()
    {
        if( !$this->panelInit->can( "messaging.addMsg" ) )
        {
            return $this->panelInit->apiOutput(false, 'Compose message', "You don't have permission to compose message");
        }
        $currentUserId = $this->data['users']->id;
        ini_set('max_execution_time', 1000);
		$users_list = array();
		User::$withoutAppends = true;

        if( is_array(\Input::get('toId')) )
        {
			$users_ids = array();
			$toId_list = \Input::get('toId');
			foreach( $toId_list as $key => $id) { $users_ids[] = $id; }
			$users = \User::whereIn('id',$users_ids);
			if( $users->count() == 0 ) { return $this->panelInit->apiOutput(false, 'Compose message', $this->panelInit->language['userisntExist']); }
			$users_array = $users->get();

            foreach( $users_array as $key => $user )
            {
                if($user->role == 'student')
                {
					foreach( User::getRealParentIdsFromStudentId( $user->id ) as $key => $parent_id) { $users_list[] = $parent_id; }
				} else { $users_list[] = $user->id; }
			}
        }
        else
        {
			$users = \User::where('username',\Input::get('toId'))->orWhere('email',\Input::get('toId'));
			if( $users->count() == 0 ) { return $this->panelInit->apiOutput(false, 'Compose message', $this->panelInit->language['userisntExist']); }
			$users_list[] = $users->first()->id;
        }

        $finalList = []; $user_ids = array(); $tokens_list = array(); $messages_ids = []; $recipients_list = [];
        $match_messages_ids = []; $match_recipients_list = []; $toStoreThreadsIds = [];
        foreach( $users_list as $user_id ) { if( $user_id == $currentUserId ) { continue; } else { $finalList[] = $user_id; } }

        foreach( $finalList as $user_id )
        {
			if( $user_id == $currentUserId ) { continue; }
			$messagesList = \messages_list::where('userId', $currentUserId)->where('toId', $user_id);
            
            if( $messagesList->count() == 0 )
            {
				$messagesList = new \messages_list();
				$messagesList->userId = $currentUserId;
				$messagesList->toId = $user_id;
			} else { $messagesList = $messagesList->first(); }
            
            $messagesList->lastMessage = \Input::get('messageText');
			$messagesList->lastMessageDate = time();
			$messagesList->messageStatus = 0;
			if( !is_null( \Input::get('is_enable_reply') ) ) { $messagesList->enable_reply = 1; } else { $messagesList->enable_reply = 0; }
			$messagesList->save();
			$toReturnId = $messagesList->id;

			$messagesList_ = \messages_list::where('userId', $user_id)->where('toId', $currentUserId);
            if( $messagesList_->count() == 0 )
            {
				$messagesList_ = new \messages_list();
				$messagesList_->userId = $user_id;
				$messagesList_->toId = $currentUserId;
            } else { $messagesList_ = $messagesList_->first(); }
            
			$messagesList_->lastMessage = \Input::get('messageText');
			$messagesList_->lastMessageDate = time();
			$messagesList_->messageStatus = 1;
			if( !is_null( \Input::get('is_enable_reply') ) ) { $messagesList_->enable_reply = 1; } else { $messagesList_->enable_reply = 0; }
			$messagesList_->save();
			$toReturnId_ = $messagesList_->id;

			$messages = new \messages();
			$messages->messageId = $toReturnId;
			$messages->userId = $currentUserId;
			$messages->fromId = $currentUserId;
			$messages->toId = $user_id;
			$messages->messageText = \Input::get('messageText');
			$messages->dateSent = time();
            if( !is_null( \Input::get('is_enable_reply') ) ) { $messages->enable_reply = 1; } else { $messages->enable_reply = 0; }
            $messages->save();
            $mids = $messages->id;

			$messages = new \messages();
			$messages->messageId = $toReturnId_;
			$messages->userId = $user_id;
			$messages->fromId = $currentUserId;
			$messages->toId = $user_id;
			$messages->messageText = \Input::get('messageText');
			$messages->dateSent = time();
			if( !is_null( \Input::get('is_enable_reply') ) ) { $messages->enable_reply = 1; } else { $messages->enable_reply = 0; }
            $messages->save();

            $messageing_to_him = Messaging::where('userId', $user_id)->where('recipients_ids', 'LIKE', '"%' . $currentUserId . '%"')->where('messages_ids', 'LIKE', '"%' . $toReturnId_ . '%"');
            if( $messageing_to_him->count() == 0 )
            {
                $messageing_to_him = new Messaging();
                $messageing_to_him->userId = $user_id;
                $messageing_to_him->recipients_ids = json_encode( ["$currentUserId"] );
                $messageing_to_him->messages_ids = json_encode( ["$toReturnId_"] );
            } else { $messageing_to_him = $messageing_to_him->first(); }
            $messageing_to_him->lastMessage = \Input::get('messageText');
            $messageing_to_him->lastMessageDate = time();
            $messageing_to_him->messageStatus = 1;
            $messageing_to_him->enable_reply = 1;
            $messageing_to_him->save();
            
            if( !in_array( "$toReturnId", $messages_ids ) ) { $messages_ids[] = "$toReturnId"; }
            if( !in_array( $toReturnId, $match_messages_ids ) ) { $match_messages_ids[] = $toReturnId; }
            if( !in_array( $user_id, $match_recipients_list ) ) { $match_recipients_list[] = $user_id; }
            if( !in_array( "$user_id", $recipients_list ) ) { $recipients_list[] = "$user_id"; }
            
            if( !in_array( $mids, $toStoreThreadsIds ) ) { $toStoreThreadsIds[] = $mids; }

			$firebase_token = User::find($user_id)->firebase_token;
            if( is_array( json_decode( $firebase_token ) ) ) { foreach (json_decode($firebase_token) as $token) { $tokens_list[] = $token; } } // \Log::debug("Array Message Token: " . $token);
            elseif( $this->isJson( $firebase_token ) ) { foreach( json_decode( $firebase_token ) as $token) { $tokens_list[] = $token; } } // \Log::debug("Json Message Token: " . $token);
            else { $tokens_list[] = $firebase_token; }  // \Log::debug("String Message Token: " . $firebase_token);
			$user_ids[] = $user_id;
        }
        $strg = "";
        foreach( $recipients_list as $key => $recipient )
        {
            if( $key == 0 ) $strg = $strg . (string)"\"$recipient\"";
            else $strg = $strg . "," . (string)"\"$recipient\"";
        }
        $messageing_from_me = Messaging::where('userId', $currentUserId)->where('recipients_ids', 'LIKE', '%[' . $strg . ']%');
        if( $messageing_from_me->count() == 0 )
        {
            $messageing_from_me = new Messaging();
            $messageing_from_me->userId = $currentUserId;
            $messageing_from_me->recipients_ids = json_encode( $recipients_list );
            $messageing_from_me->messages_ids = json_encode( $messages_ids );
        } else { $messageing_from_me = $messageing_from_me->first(); }
        $messageing_from_me->lastMessage = \Input::get('messageText');
        $messageing_from_me->lastMessageDate = time();
        $messageing_from_me->messageStatus = 1;
        $messageing_from_me->enable_reply = 1;
        if( !is_null( \Input::get('is_enable_reply') ) ) { $messageing_from_me->enable_reply = 1; } else { $messageing_from_me->enable_reply = 0; }
        $messageing_from_me->save();
        $threadId = $messageing_from_me->id;
        
        if( count( $toStoreThreadsIds ) > 1 )
        {
            messages::whereIn('id', $toStoreThreadsIds)->update( [ 'is_grouped' => 'yes', 'threadId' => $threadId ] );
        }

		user_log('Messages', 'create');
        $fullName = $this->data['users']->fullName;
        $msgs = $this->panelInit->language['Messages'];
        $newMsg = $this->panelInit->language['newMessageFrom'];
        $fullTitle = $newMsg . " " . $fullName;
        
        // Send Push Notifications
        if( count( $tokens_list ) > 0 ) { $this->panelInit->send_push_notification( $tokens_list, $user_ids, $fullTitle, $msgs, "messages", "" ); }
        else { $this->panelInit->save_notifications_toDB( $tokens_list, $user_ids, $fullTitle, $msgs, "messages", "" ); }

        return $this->panelInit->apiOutput(true, 'Compose message', 'Messages was sent successfully');
        if(count($users_list) == 1 AND isset($toReturnId)){
			return json_encode(array('messageId'=>$toReturnId));
		}else{
			return json_encode(array('messageId'=>"home"));
		}
    }

    public function fetch( $threadId )
    {
        if( !$this->panelInit->can( "messaging.View" ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to read message");
        }
        $currentUserId = $this->data['users']->id;
        User::$withoutAppends = true;
        $toReturn = array();
        $toReturn['status'] = "success";
		$toReturn['user'] = $this->data['users'];
        $thread = Messaging::find( $threadId );
        if( !$thread ) { return json_encode(array("jsTitle"=>$this->panelInit->language['readMessage'],"jsStatus"=>"0","jsMessage"=>$this->panelInit->language['messageNotExist'] )); }
        
        $messages_ids = json_decode( $thread->messages_ids, true);
        if( json_last_error() != JSON_ERROR_NONE ) $messages_ids = [];
        $recipients_ids = json_decode( $thread->recipients_ids, true);
        if( json_last_error() != JSON_ERROR_NONE ) $recipients_ids = [];
        if( count( $messages_ids ) > 1 )
        {
            $toReturn['type'] = "multiple";
            $recipients = User::select('id', 'fullName as name')->whereIn('id', $recipients_ids)->get()->toArray();
            $toReturn['messageDet'] = [
                'id' => $threadId,
                'type' => 'multiple',
                'lastMessageDate' => $thread->lastMessageDate,
                'fullName' => "Group Message",
                'fromId' => $currentUserId,
                'messageText' => $thread->lastMessage,
                'recipients' => $recipients,
                'recipients_ids' => $recipients_ids,
            ];
            $toReturn['messages'] = messages::select(
                "messages.id as id", "messages.fromId as fromId", "messages.messageText as messageText", "messages.dateSent as dateSent",
                "messages.enable_reply as enableReply")->where('threadId', $threadId)->where('is_grouped', 'yes')->groupBy('messageText')->get();            
            foreach( $toReturn['messages'] as $key => $value )
            {
                $stamp = $toReturn['messages'][$key]->dateSent;
                $txt = $toReturn['messages'][$key]->messageText;
                $toReturn['messages'][$key]->fullName = $this->data['users']['fullName'];
                $toReturn['messages'][$key]->dateSentH = $this->panelInit->unix_to_date( $stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
                $toReturn['messages'][$key]->dateH = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
                $toReturn['messages'][$key]->dateHour = date('h:i A', $stamp);
                $toReturn['messages'][$key]->userId = $currentUserId;
                $toReturn['messages'][$key]->isMultiple = true;
                $toReturn['messages'][$key]->dateToolTip = date('jS F Y h:i:s A', $stamp);
                $toReturn['messages'][$key]->styler = strlen( $txt ) <= 30 ? 75 : 100;
            }
            return $toReturn;
        }
        else
        {
            $toReturn['type'] = "single";
            $messageId = $messages_ids[0];
            $toReturn['messageDet'] = \DB::select(\DB::raw("
                SELECT messages_list.id as id,
                messages_list.lastMessageDate as lastMessageDate,
                messages_list.enable_reply as enableReply,
                messages_list.userId as fromId,
                messages_list.toId as toId,users.fullName as fullName,users.id as userId,users.photo as photo from messages_list LEFT JOIN users ON users.id=messages_list.toId where messages_list.id='$messageId' AND userId='".$this->data['users']->id."' order by id DESC"));
            
            if( count($toReturn['messageDet']) > 0 ) { $toReturn['messageDet'] = $toReturn['messageDet'][0]; }
            else { return json_encode(array("jsTitle"=>$this->panelInit->language['readMessage'],"jsStatus"=>"0","jsMessage"=>$this->panelInit->language['messageNotExist'] )); }
    
            $toReturn['messageDet']->type = "single";
            $toReturn['messages'] = \DB::select(\DB::raw("
                SELECT messages.id as id,
                messages.fromId as fromId,
                messages.messageText as messageText,
                messages.dateSent as dateSent,
                messages.is_grouped as isMultiple,
                messages.enable_reply as enableReply,
                users.fullName as fullName,
                users.id as userId,users.photo as photo
                FROM messages LEFT JOIN users ON users.id=messages.fromId
                where messages.userId='".$this->data['users']->id."' AND ( (messages.fromId='".$this->data['users']->id."' OR messages.fromId='".$toReturn['messageDet']->toId."' ) AND (messages.toId='".$this->data['users']->id."' OR messages.toId='".$toReturn['messageDet']->toId."' ) ) order by id DESC limit 20"));
    
            foreach( $toReturn['messages'] as $key => $value )
            {
                $stamp = $toReturn['messages'][$key]->dateSent;
                $txt = $toReturn['messages'][$key]->messageText;
                $isMultiple = $toReturn['messages'][$key]->isMultiple;
                $toReturn['messages'][$key]->dateSentH = $this->panelInit->unix_to_date( $stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
                $toReturn['messages'][$key]->dateH = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
                $toReturn['messages'][$key]->dateHour = date('h:i A', $stamp);
                $toReturn['messages'][$key]->isMultiple = $isMultiple == 'yes' ? true : false;
                $toReturn['messages'][$key]->dateToolTip = date('jS F Y h:i:s A', $stamp);
                $toReturn['messages'][$key]->styler = strlen( $txt ) <= 30 ? 75 : 100;
            }
            updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'messages', $messageId);
            return $toReturn;
        }
    }

    public function showMore( $from, $to, $before = 0 )
    {
        $toReturn['messages'] = \DB::select(\DB::raw("
            SELECT messages.id as id,
            messages.fromId as fromId,
            messages.messageText as messageText,
            messages.dateSent as dateSent,
            messages.enable_reply as enableReply,
            users.fullName as fullName,
            users.id as userId,users.photo as photo
            FROM messages LEFT JOIN users ON users.id=messages.fromId
            where userId='".$this->data['users']->id."' AND ( (fromId='$from' OR fromId='$to' ) AND (toId='$from' OR toId='$to' ) ) AND dateSent < '$before' order by id DESC limit 20"));
		foreach ($toReturn['messages'] as $key => $value) {
			$stamp = $toReturn['messages'][$key]->dateSent;
            $txt = $toReturn['messages'][$key]->messageText;
            $toReturn['messages'][$key]->dateSentH = $this->panelInit->unix_to_date( $stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
            $toReturn['messages'][$key]->dateH = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
            $toReturn['messages'][$key]->dateHour = date('h:i A', $stamp);
            $toReturn['messages'][$key]->dateToolTip = date('jS F Y h:i:s A', $stamp);
            $toReturn['messages'][$key]->styler = strlen( $txt ) <= 30 ? 75 : 100;
		}
		return $toReturn['messages'];
    }

    public function reply()
    {
        if( !$this->panelInit->can( "messaging.View" ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to reply to message");
        }
        $currentUserId = $this->data['users']->id;
        if( !\Input::has('mid') ) return $this->panelInit->apiOutput(false, 'reply message', "Unable to read thread data");
        if( !\Input::has('reply') ) return $this->panelInit->apiOutput(false, 'reply message', "Reply text cannot be empty");
        $mid = \Input::get('mid'); $reply = \Input::get('reply');
        $type = \Input::has('type') ? \Input::get('type') : "single";
        if( $type == "single" )
        {
            if( !\Input::has('toId') ) return $this->panelInit->apiOutput(false, 'reply message', "Unable to read recipient data");
            $toId = \Input::get('toId');

            $messagesList = \messages_list::where('userId',$currentUserId)->where('toId',$toId);
            if( $messagesList->count() == 0 ) {
                $messagesList = new \messages_list();
                $messagesList->userId = $currentUserId;
                $messagesList->toId = $toId;
            } else { $messagesList = $messagesList->first(); }

            $messagesList->lastMessage = $reply;
            $messagesList->lastMessageDate = time();
            $messagesList->messageStatus = 0;
            $messagesList->enable_reply = 1;
            $messagesList->save();
            $myMid = $messagesList->id;
    
            $messagesList_ = \messages_list::where('userId',$toId)->where('toId',$currentUserId);
            if($messagesList_->count() == 0) {
                $messagesList_ = new \messages_list();
                $messagesList_->userId = $toId;
                $messagesList_->toId = $currentUserId;
            } else { $messagesList_ = $messagesList_->first(); }
            $messagesList_->lastMessage = $reply;
            $messagesList_->lastMessageDate = time();
            $messagesList_->messageStatus = 1;
            $messagesList_->enable_reply = 1;
            $messagesList_->save();

            $mid_ = $messagesList_->id;

            $messages = new \messages();
            $messages->messageId = $myMid;
            $messages->userId = $currentUserId;
            $messages->fromId = $currentUserId;
            $messages->toId = $toId;
            $messages->messageText = $reply;
            $messages->dateSent = time();
            $messages->enable_reply = 1;
            $messages->save();

            $messages = new \messages();
            $messages->messageId = $mid_;
            $messages->userId = $toId;
            $messages->fromId = $currentUserId;
            $messages->toId = $toId;
            $messages->messageText = $reply;
            $messages->dateSent = time();
            $messages->enable_reply = 1;
            $messages->save();

            $messageing_from_me = Messaging::where('userId', $currentUserId)->where('recipients_ids', 'LIKE', '"%' . $toId . '%"')->where('messages_ids', 'LIKE', '"%' . $mid . '%"');
            if( $messageing_from_me->count() == 0 )
            {
                $messageing_from_me = new Messaging();
                $messageing_from_me->userId = $currentUserId;
                $messageing_from_me->recipients_ids = json_encode( ["$toId"] );
                $messageing_from_me->messages_ids = json_encode( ["$mid"] );
            } else { $messageing_from_me = $messageing_from_me->first(); }
            $messageing_from_me->lastMessage = $reply;
            $messageing_from_me->lastMessageDate = time();
            $messageing_from_me->messageStatus = 1;
            $messageing_from_me->enable_reply = 1;
            $messageing_from_me->save();

            $messageing_to_him = Messaging::where('userId', $toId)->where('recipients_ids', 'LIKE', '"%' . $currentUserId . '%"')->where('messages_ids', 'LIKE', '"%' . $mid_ . '%"');
            if( $messageing_to_him->count() == 0 )
            {
                $messageing_to_him = new Messaging();
                $messageing_to_him->userId = $toId;
                $messageing_to_him->recipients_ids = json_encode( ["$currentUserId"] );
                $messageing_to_him->messages_ids = json_encode( ["$mid_"] );
            } else { $messageing_to_him = $messageing_to_him->first(); }
            $messageing_to_him->lastMessage = $reply;
            $messageing_to_him->lastMessageDate = time();
            $messageing_to_him->messageStatus = 1;
            $messageing_to_him->enable_reply = 1;
            $messageing_to_him->save();

            // Send Push Notifications
            $tokens_list = array(); $user_ids = array();
            user_log('Messages', 'reply');
            $fullName = $this->data['users']->fullName;
            $msgs = $this->panelInit->language['Messages'];
            $newMsg = $this->panelInit->language['newMessageFrom'];
            $fullTitle = $newMsg . " " . $fullName;

            $to_user = \User::where('id',$toId)->select('id', 'firebase_token')->first();
            if( is_array( json_decode( $to_user->firebase_token ) ) ) { foreach( json_decode( $to_user->firebase_token ) as $token ) { $tokens_list[] = $token; } }
            else { $tokens_list[] = $to_user->firebase_token; }
            $user_ids[] = $to_user->id;
            if( $to_user->firebase_token != "" ) { $this->panelInit->send_push_notification( $tokens_list, $user_ids, $fullTitle, $msgs, "messages", $messagesList_->id ); }
            else { $this->panelInit->save_notifications_toDB( $tokens_list, $user_ids, $fullTitle, $msgs, "messages", $messagesList_->id ); }
            
            $outputData = [
                'type' => 'single',
                'miniMessage' => strlen( $reply ) <= 18 ? $reply : substr( $reply, 0, 18 ) . "...",
                'timeNow' => Carbon::parse( date('Y-m-d h:i:s A', time()) )->diffForHumans()
            ];
            return $this->panelInit->apiOutput(true, 'reply message', "message sent successfully", $outputData);
        }
        else
        {
            $thread = Messaging::find( $mid );
            $thread->lastMessage = $reply;
            $thread->lastMessageDate = time();
            $thread->save();
            $messages_ids = json_decode($thread->messages_ids, true);
            if( json_last_error() != JSON_ERROR_NONE ) $messages_ids = [];
            $recipients_ids = json_decode( $thread->recipients_ids, true );
            if( json_last_error() != JSON_ERROR_NONE ) $recipients_ids = [];
            $mixed = [];
            foreach( $recipients_ids as $key => $recipient_id ) { $mixed[$key]['id'] = $recipient_id; }
            foreach( $messages_ids as $key => $message_id ) { $mixed[$key]['message'] = $message_id; }
            foreach( $mixed as $data )
            {
                $toId = $data['id']; $messageId = $data['message'];
                
                $myMessagesList = messages_list::where('userId',$currentUserId)->where('toId',$toId);
                if( $myMessagesList->count() == 0 ) {
                    $myMessagesList = new messages_list();
                    $myMessagesList->userId = $currentUserId;
                    $myMessagesList->toId = $toId;
                } else { $myMessagesList = $myMessagesList->first(); }
    
                $myMessagesList->lastMessage = $reply;
                $myMessagesList->lastMessageDate = time();
                $myMessagesList->messageStatus = 0;
                $myMessagesList->enable_reply = 1;
                $myMessagesList->save();
                $myStoryId = $myMessagesList->id;
        
                $otherMessagesList = messages_list::where('userId',$toId)->where('toId',$currentUserId);
                if($otherMessagesList->count() == 0) {
                    $otherMessagesList = new messages_list();
                    $otherMessagesList->userId = $toId;
                    $otherMessagesList->toId = $currentUserId;
                } else { $otherMessagesList = $otherMessagesList->first(); }
                $otherMessagesList->lastMessage = $reply;
                $otherMessagesList->lastMessageDate = time();
                $otherMessagesList->messageStatus = 1;
                $otherMessagesList->enable_reply = 1;
                $otherMessagesList->save();
                $otherStoryId = $otherMessagesList->id;
                
                $myMessage = new messages();
                $myMessage->messageId = $myStoryId;
                $myMessage->userId = $currentUserId;
                $myMessage->fromId = $currentUserId;
                $myMessage->toId = $toId;
                $myMessage->messageText = $reply;
                $myMessage->dateSent = time();
                $myMessage->enable_reply = 1;
                $myMessage->is_grouped = 'yes';
                $myMessage->threadId = $mid;
                $myMessage->save();
                $myMid = $myMessage->id;

                $otherMessage = new \messages();
                $otherMessage->messageId = $otherStoryId;
                $otherMessage->userId = $toId;
                $otherMessage->fromId = $currentUserId;
                $otherMessage->toId = $toId;
                $otherMessage->messageText = $reply;
                $otherMessage->dateSent = time();
                $otherMessage->is_grouped = 'no';
                $otherMessage->threadId = 0;
                $otherMessage->enable_reply = 1;
                $otherMessage->save();
            }
            $outputData = [
                'type' => 'multiple',
                'miniMessage' => strlen( $reply ) <= 18 ? $reply : substr( $reply, 0, 18 ) . "...",
                'timeNow' => Carbon::parse( date('Y-m-d h:i:s A', time()) )->diffForHumans()
            ];
            return $this->panelInit->apiOutput(true, 'reply message', "message sent successfully", $outputData);
        }
    }

    public function remove()
    {
        if( !$this->panelInit->can( "messaging.delMsg" ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to delete message");
        }
        $currentUserId = $this->data['users']->id;
        if( \Input::get('items') )
        {
            if( count( \Input::get('items') ) > 0 )
            {
				$arr = \Input::get('items');
                foreach( $arr as $value )
                {
                    $messaging = Messaging::where('id', $value)->first();
                    $messages_ids = json_decode( $messaging->messages_ids, true );
                    if( json_last_error() != JSON_ERROR_NONE ) $messages_ids = [];
                    $messages_list = messages_list::select('id')->where('userId', $currentUserId)->whereIn('id', $messages_ids);
                    $m_ids = $messages_list->pluck('id');
                    messages::where('userId', $currentUserId)->where('fromId', $currentUserId)->whereIn('messageId', $m_ids)->delete();
                    $messages_list->delete();
                    $messaging->delete();
				}
				user_log('Messages', 'delete');
                return $this->panelInit->apiOutput(true,$this->panelInit->language['delMess'],$this->panelInit->language['messDel']);
			} else return $this->panelInit->apiOutput(false, $this->panelInit->language['delMess'], "At least one message should be selected");
		} else return $this->panelInit->apiOutput(false, $this->panelInit->language['delMess'], "At least one message should be selected");
    }

    protected function isJson( $string )
    {
        $decoded = json_decode($string);
        if ( !is_object($decoded) && !is_array($decoded) ) { return false; }
        return( json_last_error() == JSON_ERROR_NONE );
    }

    public function listConvarsations( $page = 1 )
    {
        if( !$this->panelInit->can( array( "messaging.list", "messaging.View", "messaging.editMsg", "messaging.delMsg" ) ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to list messages");
        }
        $toReturn = array();
        $toReturn['status'] = "success";
		User::$withoutAppends = true;
		
		$messaging = messages_list::where('userId', Auth::guard('web')->user()->id)
            ->with('user')
            ->orderBy('lastMessageDate','DESC');
        
        if( \Input::has('type1') )
        {
            $userIds = [];
            $type = \Input::get('type1'); $role = \Input::get('type2');
            if( $type == 'non' )
            {
                $user_Ids = User::select('id', 'fullName')->where('role_perm', $role)->pluck('id');
                foreach( $user_Ids as $id ) { if( !in_array( $id, $userIds ) ) { $userIds[] = $id; } }
            }
            else
            {
                if( $role == 'non' )
                {
                    $class_ids = \Input::get('class');
                    $section_ids = \Input::get('section');
                    $students_ids = User::select('id', 'fullName')->where('studentSection', $section_ids)->pluck('id');
                    $user_Ids = User::getParentIdsFromStudentsIds($students_ids);
                }
                else
                {
                    $class_ids = \Input::get('class');
                    $user_Ids = Main::getTeacherIdsByClassIds( $class_ids );
                }
                foreach( $user_Ids as $id ) { if( !in_array( $id, $userIds ) ) { $userIds[] = $id; } }
            }
            if( count( $userIds ) == 0 ) { $messaging = $messaging->Where('id', '0'); }
            else
            {
                $messaging = $messaging->Where(
                    function ( $messaging ) use( $userIds ) {
                    foreach($userIds as $toId ) { $messaging->orwhere('toId', $toId); }
               });
            }
            $toReturn['filter'] = true;
        }
        elseif( \Input::has('recipient') )
        {
            $messaging->where('toId', \Input::get('recipient'));
            $toReturn['filter'] = true;
        }
        else $toReturn['filter'] = false;
        $toReturn['totalItems'] = $messaging->count();
        $messaging = $messaging->take(all_pagination_number())
            ->skip(all_pagination_number() * ($page - 1))
            ->get();
        $toReturn['messages'] = $messaging;
		foreach( $toReturn['messages'] as $key => $value) {
			$toReturn['messages'][$key]['students'] = [];
            if( strlen( $toReturn['messages'][$key]['lastMessage'] ) <= 18 )
                $toReturn['messages'][$key]['lastMiniMessage'] = $toReturn['messages'][$key]['lastMessage'];
            else
                $toReturn['messages'][$key]['lastMiniMessage'] = substr( $toReturn['messages'][$key]['lastMessage'], 0, 18 ) . "...";
            
            $toId = $toReturn['messages'][$key]['toId'];
            $toReturn['messages'][$key]['sender_receiver_data'] = User::find($toId);
            if($toReturn['messages'][$key]['sender_receiver_data']) {
                $toReturn['messages'][$key]['receiver_role'] = $toReturn['messages'][$key]['sender_receiver_data']->role;
            }
            if($toReturn['messages'][$key]['receiver_role'] == 'parent') {
                $students_ids = User::getStudentsIdsFromParentId($toId);
                $student_collection = User::whereIn('id', $students_ids)
                    ->select('id', 'fullName')
                    ->get()->toArray();
                $toReturn['messages'][$key]['students'] = $student_collection;
            } else $toReturn['messages'][$key]['students'] = [];
		}

		foreach( $toReturn['messages'] as $key => $value ) {
            $stamp = $toReturn['messages'][$key]->lastMessageDate;
            $toReturn['messages'][$key]->lastMessageDate = $this->panelInit->unix_to_date($stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
            $toReturn['messages'][$key]->dateSentH = $this->panelInit->unix_to_date( $stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
            $toReturn['messages'][$key]->dateH = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
            $toReturn['messages'][$key]->dateHour = date('h:i A', $stamp);
            $toReturn['messages'][$key]->isMultiple = true;
            $toReturn['messages'][$key]->dateToolTip = date('jS F Y h:i:s A', $stamp);
                
		}

        updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'messages', null);
		return $toReturn;
    }

    public function fetchConvarsation( $convarsationId )
    {
        if( !$this->panelInit->can( "messaging.View" ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to read message");
        }
        $currentUserId = $this->data['users']->id;
        User::$withoutAppends = true;
        $toReturn = array();
		$toReturn['user'] = $this->data['users'];
        $thread = messages_list::find( $convarsationId );
        if( !$thread ) { return json_encode(array("jsTitle"=>$this->panelInit->language['readMessage'],"jsStatus"=>"0","jsMessage"=>$this->panelInit->language['messageNotExist'] )); }
        
        $toReturn['status'] = "success";
        $toReturn['type'] = "single";
        $messageId = $convarsationId;
        $toReturn['messageDet'] = \DB::select(\DB::raw("
            SELECT messages_list.id as id,
            messages_list.lastMessageDate as lastMessageDate,
            messages_list.enable_reply as enableReply,
            messages_list.userId as fromId,
            messages_list.toId as toId,users.fullName as fullName,users.id as userId,users.photo as photo from messages_list LEFT JOIN users ON users.id=messages_list.toId where messages_list.id='$messageId' AND userId='".$this->data['users']->id."' order by id DESC"));
        
        if( count($toReturn['messageDet']) > 0 ) { $toReturn['messageDet'] = $toReturn['messageDet'][0]; }
        else { return json_encode(array("jsTitle"=>$this->panelInit->language['readMessage'],"jsStatus"=>"0","jsMessage"=>$this->panelInit->language['messageNotExist'] )); }

        $toReturn['messageDet']->type = "single";
        $toReturn['messages'] = \DB::select(\DB::raw("
            SELECT messages.id as id,
            messages.fromId as fromId,
            messages.messageText as messageText,
            messages.dateSent as dateSent,
            messages.is_grouped as isMultiple,
            messages.enable_reply as enableReply,
            users.fullName as fullName,
            users.id as userId,users.photo as photo
            FROM messages LEFT JOIN users ON users.id=messages.fromId
            where messages.userId='".$this->data['users']->id."' AND ( (messages.fromId='".$this->data['users']->id."' OR messages.fromId='".$toReturn['messageDet']->toId."' ) AND (messages.toId='".$this->data['users']->id."' OR messages.toId='".$toReturn['messageDet']->toId."' ) ) order by id DESC limit 20"));

        foreach( $toReturn['messages'] as $key => $value )
        {
            $stamp = $toReturn['messages'][$key]->dateSent;
            $txt = $toReturn['messages'][$key]->messageText;
            $isMultiple = $toReturn['messages'][$key]->isMultiple;
            $toReturn['messages'][$key]->dateSentH = $this->panelInit->unix_to_date( $stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
            $toReturn['messages'][$key]->dateH = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
            $toReturn['messages'][$key]->dateHour = date('h:i A', $stamp);
            $toReturn['messages'][$key]->isMultiple = $isMultiple == 'yes' ? true : false;
            $toReturn['messages'][$key]->dateToolTip = date('jS F Y h:i:s A', $stamp);
            $toReturn['messages'][$key]->styler = strlen( $txt ) <= 30 ? 75 : 100;
        }
        updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'messages', $messageId);
        return $toReturn;
    }

    public function loadMore( $from, $to, $before = 0 )
    {
        $toReturn['messages'] = \DB::select(\DB::raw("
            SELECT messages.id as id,
            messages.fromId as fromId,
            messages.messageText as messageText,
            messages.dateSent as dateSent,
            messages.enable_reply as enableReply,
            users.fullName as fullName,
            users.id as userId,users.photo as photo
            FROM messages LEFT JOIN users ON users.id=messages.fromId
            where userId='".$this->data['users']->id."' AND ( (fromId='$from' OR fromId='$to' ) AND (toId='$from' OR toId='$to' ) ) AND dateSent < '$before' order by id DESC limit 20"));
		foreach ($toReturn['messages'] as $key => $value) {
			$stamp = $toReturn['messages'][$key]->dateSent;
            $txt = $toReturn['messages'][$key]->messageText;
            $toReturn['messages'][$key]->dateSentH = $this->panelInit->unix_to_date( $stamp, $this->panelInit->settingsArray['dateformat']." hr:mn a");
            $toReturn['messages'][$key]->dateH = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
            $toReturn['messages'][$key]->dateHour = date('h:i A', $stamp);
            $toReturn['messages'][$key]->dateToolTip = date('jS F Y h:i:s A', $stamp);
            $toReturn['messages'][$key]->styler = strlen( $txt ) <= 30 ? 75 : 100;
		}
		return $toReturn['messages'];
    }

    public function replyToConvarsation()
    {
        if( !$this->panelInit->can( "messaging.View" ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to reply to message");
        }
        $currentUserId = $this->data['users']->id;
        if( !\Input::has('mid') ) return $this->panelInit->apiOutput(false, 'reply message', "Unable to read thread data");
        if( !\Input::has('reply') ) return $this->panelInit->apiOutput(false, 'reply message', "Reply text cannot be empty");
        $mid = \Input::get('mid'); $reply = \Input::get('reply');
        
        if( !\Input::has('toId') ) return $this->panelInit->apiOutput(false, 'reply message', "Unable to read recipient data");
        $toId = \Input::get('toId');

        $messagesList = \messages_list::where('userId',$currentUserId)->where('toId',$toId);
        if( $messagesList->count() == 0 ) {
            $messagesList = new \messages_list();
            $messagesList->userId = $currentUserId;
            $messagesList->toId = $toId;
        } else { $messagesList = $messagesList->first(); }

        $messagesList->lastMessage = $reply;
        $messagesList->lastMessageDate = time();
        $messagesList->messageStatus = 0;
        $messagesList->enable_reply = 1;
        $messagesList->save();
        $myMid = $messagesList->id;

        $messagesList_ = \messages_list::where('userId',$toId)->where('toId',$currentUserId);
        if($messagesList_->count() == 0) {
            $messagesList_ = new \messages_list();
            $messagesList_->userId = $toId;
            $messagesList_->toId = $currentUserId;
        } else { $messagesList_ = $messagesList_->first(); }
        $messagesList_->lastMessage = $reply;
        $messagesList_->lastMessageDate = time();
        $messagesList_->messageStatus = 1;
        $messagesList_->enable_reply = 1;
        $messagesList_->save();

        $mid_ = $messagesList_->id;

        $messages = new \messages();
        $messages->messageId = $myMid;
        $messages->userId = $currentUserId;
        $messages->fromId = $currentUserId;
        $messages->toId = $toId;
        $messages->messageText = $reply;
        $messages->dateSent = time();
        $messages->enable_reply = 1;
        $messages->save();

        $messages = new \messages();
        $messages->messageId = $mid_;
        $messages->userId = $toId;
        $messages->fromId = $currentUserId;
        $messages->toId = $toId;
        $messages->messageText = $reply;
        $messages->dateSent = time();
        $messages->enable_reply = 1;
        $messages->save();

        // Send Push Notifications
        $tokens_list = array(); $user_ids = array();
        user_log('Messages', 'reply');
        $fullName = $this->data['users']->fullName;
        $msgs = $this->panelInit->language['Messages'];
        $newMsg = $this->panelInit->language['newMessageFrom'];
        $fullTitle = $newMsg . " " . $fullName;

        $to_user = \User::where('id',$toId)->select('id', 'firebase_token')->first();
        if( is_array( json_decode( $to_user->firebase_token ) ) ) { foreach( json_decode( $to_user->firebase_token ) as $token ) { $tokens_list[] = $token; } }
        else { $tokens_list[] = $to_user->firebase_token; }
        $user_ids[] = $to_user->id;
        if( $to_user->firebase_token != "" ) { $this->panelInit->send_push_notification( $tokens_list, $user_ids, $fullTitle, $msgs, "messages", $messagesList_->id ); }
        else { $this->panelInit->save_notifications_toDB( $tokens_list, $user_ids, $fullTitle, $msgs, "messages", $messagesList_->id ); }
        
        $outputData = [
            'type' => 'single',
            'miniMessage' => strlen( $reply ) <= 18 ? $reply : substr( $reply, 0, 18 ) . "...",
            'timeNow' => Carbon::parse( date('Y-m-d h:i:s A', time()) )->diffForHumans()
        ];
        return $this->panelInit->apiOutput(true, 'reply message', "message sent successfully", $outputData);
    }

    public function removeConvarsation()
    {
        if( !$this->panelInit->can( "messaging.delMsg" ) )
        {
            return $this->panelInit->apiOutput(false, 'Access denied', "you don't have permission to delete message");
        }
        $currentUserId = $this->data['users']->id;
        if( \Input::get('items') )
        {
            if( count( \Input::get('items') ) > 0 )
            {
				$arr = \Input::get('items');
                foreach( $arr as $value )
                {
                    messages::where('userId', $currentUserId)->where('fromId', $currentUserId)->where('messageId', $value)->delete();
                    messages_list::where('id', $value)->delete();
				}
				user_log('Messages', 'delete');
                return $this->panelInit->apiOutput(true,$this->panelInit->language['delMess'],$this->panelInit->language['messDel']);
			} else return $this->panelInit->apiOutput(false, $this->panelInit->language['delMess'], "At least one message should be selected");
		} else return $this->panelInit->apiOutput(false, $this->panelInit->language['delMess'], "At least one message should be selected");
    }
}