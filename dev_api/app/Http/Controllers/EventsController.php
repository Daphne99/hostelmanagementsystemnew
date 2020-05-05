<?php
namespace App\Http\Controllers;

use App\Models2\Newsboard;
use App\Models2\Event;
use App\Models2\User;
use App\Models2\Main;
use App\Models2\NotificationMobHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
// use App\Jobs\SendNotification;

class EventsController extends Controller {

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
		if( !isset( $this->data['users']->id ) ) { return \Redirect::to('/'); }

	}

	public function createNewEvent()
	{
		if( !$this->panelInit->can( "events.addEvent" ) ) { return $this->panelInit->apiOutput( false, "Create event", "you don't have permission to create event" ); }
		if( !\Input::has('title') ) { return $this->panelInit->apiOutput( false, "Create event", "event title is missing" ); }
		if( !trim( \Input::get('title') ) ) { return $this->panelInit->apiOutput( false, "Create event", "event title is missing" ); }
		if( !\Input::has('recipients') ) { return $this->panelInit->apiOutput( false, "Create event", "event recipients is missing" ); }
		if( !trim( \Input::get('recipients') ) ) { return $this->panelInit->apiOutput( false, "Create event", "event recipients is missing" ); }
		$recipients = explode(',', \Input::get('recipients'));
		if( !is_array( $recipients ) ) { return $this->panelInit->apiOutput( false, "Create event", "event recipients is missing" ); }
		if( count( $recipients ) <= 0 ) { return $this->panelInit->apiOutput( false, "Create event", "event recipients is missing" ); }
		if( count( $recipients ) > 1000 ) { return $this->panelInit->apiOutput( false, "Create event", "Maximum sending to users is 1000, Please reduce the number and resend to other users" ); }
		
		if( !\Input::has('startDate') ) { return $this->panelInit->apiOutput( false, "Create event", "Start Date is missing" ); }
        $date = formatDate( \Input::get('startDate') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Create event", "Start Date has invalid format" ); }
        if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Create event", "Start Date has invalid format" ); }
		$startDate = strtotime("today", toUnixStamp( $date ));
		
		if( \Input::has('endDate') )
		{
			$date = formatDate( \Input::get('endDate') );
			if( !$date ) { return $this->panelInit->apiOutput( false, "Create event", "End Date has invalid format" ); }
        	if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Create event", "End Date has invalid format" ); }
			$endDate = strtotime("tomorrow", toUnixStamp( $date )) - 1;
		} else $endDate = NULL;

		if( \Input::hasFile('image') )
		{
			$errMsg = "Sorry, This File Type Is Not Permitted For Security Reasons ";
			$fileInstance = \Input::file('image');
			if( !$this->panelInit->validate_upload($fileInstance) ) { return $this->panelInit->apiOutput(false, "Create event", $errMsg); }
			$extenstion = $fileInstance->getClientOriginalExtension();
			$allowedExtenstions = ["jpg", "png", "jpeg"];
			if( !in_array($extenstion, $allowedExtenstions) ) { return $this->panelInit->apiOutput(false, "Create event", $errMsg); }
			$newFileName = uniqid() . "." . $extenstion;
			$fileInstance->move(uploads_config()['uploads_file_path'] . '/events/',$newFileName);
		} else $newFileName = NULL;
		if( $newFileName == NULL ) $newFileName = "default.png";
		$eventName = \Input::get('title');
		$events = new Event();
		$events->eventTitle = \Input::get('title');
		if( \Input::has('description') )
		{
			if( trim( \Input::get('description') ) ) { $description = htmlspecialchars(\Input::get('description'), ENT_QUOTES); $events->eventDescription = $description; } else { $description = NULL; }
		} else $description = NULL;
		$events->eventCreator = $this->data['users']->id;
		$events->eventFor = "custom";
		$events->participants = json_encode( $recipients );
		$events->enentPlace = NULL;
		$events->eventImage = $newFileName;
		$events->fe_active = 1;
		$events->eventDate = $startDate;
		$events->eventEndDate = $endDate;
		$events->created_at = date('Y-m-d H:i:s', time());
		$events->updated_at = date('Y-m-d H:i:s', time());
		$events->save();
		user_log('Events', 'create', $events->eventTitle);
		
		$settings = (array) $this->panelInit->settingsArray;
		// dispatch( new SendNotification( "Events", $eventName, $description, $recipients, $startDate, $endDate, $settings ) );
		return $this->panelInit->apiOutput( true, "Create event", "Event created successfully" );
	}

	public function listEvents( $page = 1 )
	{
		$toReturn = array();
		User::$withoutAppends = true;
		$currentUser = $this->data['users']->id;
		
		$events = Event::select('*');
		if( $this->data['users']->role != "admin" )
		{
			if( \Input::has('myEvents') ) { $events = $events->where('eventCreator', $currentUser); }
			else { $events = $events->where('participants', 'LIKE', '%"' . $currentUser . '"%'); }
		}
		$events = $events->orderBy('eventDate', 'DESC');
		if( \Input::has('startDate') )
		{
			$date = formatDate( \Input::get('startDate') );
			$startDate = strtotime("today", toUnixStamp( $date ));
			$events = $events->where('eventDate', '>=', $startDate);
		}
		if( \Input::has('endDate') )
		{
			$date = formatDate( \Input::get('endDate') );
			$endDate = strtotime("tomorrow", toUnixStamp( $date )) - 1;
			$events = $events->where('eventEndDate', '<=', $endDate);
		}
		
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
			
			if( count( $userIds ) == 0 ) { $events = $events->Where('id', '0'); }
            else
            {
                $events = $events->Where(
                    function ( $events ) use( $userIds ) {
                    foreach($userIds as $recipient_id ) { $events->orwhere('participants', 'LIKE', '%"' . $recipient_id . '"%'); }
               });
            }
            $toReturn['filter'] = true;
        } else $toReturn['filter'] = false;
        $toReturn['totalItems'] = $events->count();
        $events = $events->take(all_pagination_number())
            ->skip(all_pagination_number() * ($page - 1))
            ->get();
		$toReturn['events'] = $events;
		foreach( $toReturn['events'] as $key => $value)
		{
			if( !$value['enentPlace'] ) $toReturn['events'][$key]['enentPlace'] = '';
			$participants = json_decode( $value['participants'], true);
			if( json_last_error() != JSON_ERROR_NONE ) $participants = [];
			$seenMembers = json_decode( $value['eventSeenMembers'], true);
			if( json_last_error() != JSON_ERROR_NONE ) $seenMembers = [];

			$toReturn['events'][$key]['until'] = Carbon::parse( date('Y-m-d h:i:s A', $value['eventDate']) )->diffForHumans();
			$toReturn['events'][$key]['eventDate'] = date('jS M Y', $value['eventDate']);
            $toReturn['events'][$key]['participants'] = $participants;
			$toReturn['events'][$key]['eventSeenMembers'] = $seenMembers;
			$toReturn['events'][$key]['selected'] = false;

			if( strlen( $value['eventTitle'] ) <= 24 )
                $toReturn['events'][$key]['name'] = $value['eventTitle'];
            else
				$toReturn['events'][$key]['name'] = substr( $value['eventTitle'], 0, 24 ) . "...";

			if( !trim( $value['eventImage'] ) || trim( $value['eventImage'] ) == "" ) { $toReturn['events'][$key]['eventImage'] = "default.png"; }
			if( $value['eventEndDate'] )
			{
				$toReturn['events'][$key]['eventEndDate'] = date('jS M Y', $value['eventEndDate']);

			} else $toReturn['events'][$key]['eventEndDate'] = "";
			if( $value->user )
			{
				$toReturn['events'][$key]['creator'] = $value->user->fullName;
			} else $toReturn['events'][$key]['creator'] = "Unknown";
			unset( $toReturn['events'][$key]['user'] );
			$created_at = $value['created_at'];
			$stamp = toUnixStamp( $created_at );
			$toReturn['events'][$key]['creation_date'] = date('d/m/Y', $stamp);
			$toReturn['events'][$key]['creation'] = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
			if( $currentUser == $value['eventCreator'] ) { $toReturn['events'][$key]['isRead'] = true; }
			else
			{
				if( array_key_exists( $currentUser, $seenMembers ) ) { $toReturn['events'][$key]['isRead'] = true; }
				else { $toReturn['events'][$key]['isRead'] = false; }
			}
		}
		return $toReturn;
	}

	public function loadEvent( $image )
	{
		header('Content-Type: image/jpeg');
        $uploads_file_path = uploads_config()['uploads_file_path'];
		$fileName = !trim( $image ) ? "default.png" : ( file_exists( $uploads_file_path . "/events/$image" ) ? $image : "default.png" );
		echo file_get_contents( $uploads_file_path . "/events/$fileName");
	}

	public function readEvent( $id )
	{
		$toReturn = array();
		$toReturn['status'] = "success";
		User::$withoutAppends = true;
		$currentUser = $this->data['users']->id;
		$currentName = $this->data['users']->fullName;
		$role = $this->data['users']->role;
		$event = Event::find( $id );
		if( !$event ) { return $this->panelInit->apiOutput( false, "Read Event", "Unable to read event data" ); }
		$creator = $event->eventCreator;
		$status = ( $role == "admin" || intval( $currentUser ) == intval( $creator ) ) ? true : false;
		$toReturn['eventDet'] = $event;
		$value = (array)$toReturn['eventDet'];

		if( !$event->enentPlace ) $toReturn['eventDet']->enentPlace = '';
		$participants = json_decode( $event->participants, true);
		if( json_last_error() != JSON_ERROR_NONE ) $participants = [];
		$seenMembers = json_decode( $event->eventSeenMembers, true);
		if( json_last_error() != JSON_ERROR_NONE ) $seenMembers = [];
		if( !$status && !in_array( $currentUser, $participants ) ) { return $this->panelInit->apiOutput( false, "Read Event", "you don't have permission to show this event details" ); }
		
		$toReturn['eventDet']->participants = $participants;
		$toReturn['eventDet']->eventSeenMembers = $seenMembers;
		$users = User::select('id', 'fullName as name', 'role')->whereIn('id', $participants)->get()->toArray();
		// 0 for unseen, 1 for seen, 2 for maybe, 3 for accept, 4 for delete invitation
		if( !array_key_exists($currentUser, $seenMembers) )
		{
			$myStatus = [ 'seen' => true, 'status' => 1, 'seenDate' => time(), 'actionDate' => "" ];
			$seenMembers[$currentUser] = $myStatus;
		}
		else
		{
			$scopedStatus = $seenMembers[$currentUser];
			$myStatus = [
				'seen' => $scopedStatus['seen'], 'status' => $scopedStatus['status'], 'seenDate' => $scopedStatus['seenDate'],
				'actionDate' => $scopedStatus['actionDate']
			];
		}
		
		foreach( $users as $key => $user )
		{
			$userId = $user['id'];
			if( $status )
			{
				if( array_key_exists($userId, $seenMembers) )
				{
					$seenData = $seenMembers[$userId]['seenDate'];
					$actionDate = $seenMembers[$userId]['actionDate'];
					$users[$key]['details'] = [
						'isSeen' => $seenMembers[$userId]['seen'],
						'status' => $seenMembers[$userId]['status'],
						'seenDate' => $seenData != "" ? date('Y-m-d h:i:s A', $seenData) : "",
						'actionDate' => $actionDate != "" ? date('Y-m-d h:i:s A', $actionDate) : "",
						'isInvited' => true
					];
				} else { $users[$key]['details'] = [ 'isSeen' => false, 'status' => 0, 'seenDate' => "", 'actionDate' => "", 'isInvited' => true ]; }
			} else $users[$key]['details'] = [];
		}
		if( !in_array( $currentUser, $participants ) )
		{
			$seenData = $myStatus['seenDate'];
			$actionDate = $myStatus['actionDate'];
			$users[] = [
				'id' => $currentUser,
				'name' => $currentName,
				'role' => $role,
				'details' => [
					'isSeen' => $myStatus['seen'],
					'status' => $myStatus['status'],
					'seenDate' => $seenData != "" ? date('Y-m-d h:i:s A', $seenData) : "",
					'actionDate' => $actionDate != "" ? date('Y-m-d h:i:s A', $actionDate) : "",
					'isInvited' => false
				]
			];
		}
		
		if( count( $seenMembers ) != count( $toReturn['eventDet']->eventSeenMembers ) )
		{
			$scopedEvent = Event::select( 'id', 'eventSeenMembers' )->where('id', $id)->first();
			$scopedEvent->eventSeenMembers = json_encode( $seenMembers );
			$scopedEvent->save();
		}
		$toReturn['eventDet']['until'] = Carbon::parse( date('Y-m-d h:i:s A', $event->eventDate) )->diffForHumans();
		$toReturn['eventDet']['eventDate'] = date('jS M Y', $event->eventDate);

		if( $event->eventEndDate )
		{
			$toReturn['eventDet']['eventEndDate'] = date('jS M Y', $event->eventEndDate);
		} else $toReturn['eventDet']['eventEndDate'] = "";
		
		if( $event->user )
		{
			$toReturn['eventDet']['creator'] = $event->user->fullName;
		} else $toReturn['eventDet']['creator'] = "Unknown";
		
		$created_at = $event->created_at;
		$stamp = toUnixStamp( $created_at );
		$toReturn['eventDet']['creation_date'] = date('d/m/Y', $stamp);
		$toReturn['eventDet']['creation'] = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
		
		if( !trim( $event->eventImage ) || trim( $event->eventImage ) == "" ) { $toReturn['eventDet']['eventImage'] = "default.png"; }

		unset( $toReturn['eventDet']['enentPlace'] );
		unset( $toReturn['eventDet']['created_at'] );
		unset( $toReturn['eventDet']['updated_at'] );
		unset( $toReturn['eventDet']['participants'] );
		unset( $toReturn['eventDet']['eventSeenMembers'] );
		unset( $toReturn['eventDet']['fe_active'] );
		unset( $toReturn['eventDet']['user'] );

		$toReturn['eventDet']['eventDescription'] = strip_tags(htmlspecialchars_decode( $toReturn['eventDet']['eventDescription'],ENT_QUOTES));
		$toReturn['eventDet']['members'] = $users;
		$toReturn['eventDet']['user'] = $currentUser;
		$toReturn['eventDet']['status'] = $status;
		return $toReturn;
	}

	public function listAll()
	{
		if(!$this->panelInit->can( array("events.list","events.View","events.addEvent","events.editEvent","events.delEvent") )){
			exit;
		}

		$toReturn = array();
		if($this->data['users']->role == "admin" ){
			$toReturn['events'] = \events::orderby('eventDate','DESC')->get()->toArray();
		}else{
			$toReturn['events'] = \events::where('eventFor',$this->data['users']->role)
			  ->orWhere('eventFor','all')
			  ->orderby('eventDate','DESC')
			  ->get()->toArray();
		}

		foreach ($toReturn['events'] as $key => $item) {
			$toReturn['events'][$key]['eventDescription'] = strip_tags(htmlspecialchars_decode($toReturn['events'][$key]['eventDescription'],ENT_QUOTES));
			$toReturn['events'][$key]['eventDate'] = $this->panelInit->unix_to_date($toReturn['events'][$key]['eventDate']);
		}

		return $toReturn;
	}

	public function listAllEventAndNotices()
	{
		if(!$this->panelInit->can( array("events.list","events.View","events.addEvent","events.editEvent","events.delEvent") )){
			exit;
		}

		$newsboard_query = Newsboard::orderBy('newsDate', 'DESC')
			->select([
				'newsTitle as eventTitle',
				'newsText as eventDescription',
				'newsFor as eventFor',
				'newsDate as eventDate',
				'newsImage as eventImage',
				'fe_active as fe_active',
			]);

		$toReturn = array();
		if($this->data['users']->role == "admin" ){
			$toReturn['events'] = \events::orderby('eventDate','DESC')->get()->toArray();
			$toReturn['notices'] = $newsboard_query->get()->toArray();
		}else{
			$toReturn['events'] = \events::where('eventFor',$this->data['users']->role)
			  ->orWhere('eventFor','all')
			  ->orderby('eventDate','DESC')
			  ->get()->toArray();
			$toReturn['notices'] = $newsboard_query->where('newsFor', $this->data['users']->role)
				->orWhere('newsFor', 'all')
			  ->get()->toArray();
		}

		foreach ($toReturn['events'] as $key => $item) {
			$toReturn['events'][$key]['eventDescription'] = strip_tags(htmlspecialchars_decode($toReturn['events'][$key]['eventDescription'],ENT_QUOTES));
			$toReturn['events'][$key]['eventDate'] = $this->panelInit->unix_to_date($toReturn['events'][$key]['eventDate']);
			$toReturn['events'][$key]['__type'] = 'event';
		}
		foreach ($toReturn['notices'] as $key => $value) {
			$toReturn['notices'][$key]['eventDescription'] = strip_tags(htmlspecialchars_decode($toReturn['notices'][$key]['eventDescription'],ENT_QUOTES));
			$toReturn['notices'][$key]['eventDate'] = $this->panelInit->unix_to_date($toReturn['notices'][$key]['eventDate']);
			$toReturn['notices'][$key]['__type'] = 'notice';
		}

		$toReturn['events'] = array_merge($toReturn['events'], $toReturn['notices']);

		return $toReturn;
	}

	public function listAllUpcoming()
	{
		if(!$this->panelInit->can( array("events.list","events.View","events.addEvent","events.editEvent","events.delEvent") )){
			exit;
		}

		$toReturn = array();
		$today_minus_days = Carbon::now()->subDays(3)->timestamp;
		$today_plus_days = Carbon::now()->addDays(7)->timestamp;

		if($this->data['users']->role == "admin" ){
			$toReturn['events'] = \events::orderby('eventDate','DESC')
				->whereBetween('eventDate', [$today_minus_days, $today_plus_days])
			  ->get()->toArray();
		}else{
			$toReturn['events'] = \events::where('eventFor',$this->data['users']->role)
			  ->orWhere('eventFor','all')
			  ->orderby('eventDate','DESC')
			  ->whereBetween('eventDate', [$today_minus_days, $today_plus_days])
			  ->get()->toArray();
		}

		foreach ($toReturn['events'] as $key => $item) {
			$toReturn['events'][$key]['date_diffForHumans'] = Carbon::createFromTimestamp($toReturn['events'][$key]['eventDate'])->diffForHumans();
			$toReturn['events'][$key]['eventDescription'] = strip_tags(htmlspecialchars_decode($toReturn['events'][$key]['eventDescription'],ENT_QUOTES));
			$toReturn['events'][$key]['eventDate'] = $this->panelInit->unix_to_date($toReturn['events'][$key]['eventDate']);
			$toReturn['events'][$key]['__type'] = 'event';
		}

		return $toReturn;
	}

	public function delete($id)
	{

		if(!$this->panelInit->can( "events.delEvent" )){
			exit;
		}

		if ( $postDelete = \events::where('id', $id)->first() ) {
    		user_log('Events', 'delete', $postDelete->eventTitle);
        	$postDelete->delete();
        	return $this->panelInit->apiOutput(true,$this->panelInit->language['delEvent'],$this->panelInit->language['eventDeleted']);
    	}else{
        	return $this->panelInit->apiOutput(false,$this->panelInit->language['delEvent'],$this->panelInit->language['eventNotEist']);
    	}
	}

	public function create()
	{

		if(!$this->panelInit->can( "events.addEvent" )){
			exit;
		}

		$events = new \events();
		$events->eventTitle = \Input::get('eventTitle');
		$events->eventDescription = htmlspecialchars(\Input::get('eventDescription'),ENT_QUOTES);
		$events->eventFor = \Input::get('eventFor');
		$events->enentPlace = \Input::get('enentPlace');
		$events->eventDate = $this->panelInit->date_to_unix(\Input::get('eventDate'));
		$events->fe_active = \Input::get('fe_active');

		if (\Input::hasFile('eventImage')) {
			$fileInstance = \Input::file('eventImage');

			if(!$this->panelInit->validate_upload($fileInstance)){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addEvent'],"Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

			if($fileInstance->getClientOriginalExtension() != 'jpg' || $fileInstance->getClientOriginalExtension() != 'png' || $fileInstance->getClientOriginalExtension() != 'jpeg'){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addEvent'],"Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

			$newFileName = uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move(uploads_config()['uploads_file_path'] . '/events/',$newFileName);

			$events->eventImage = $newFileName;
		}

		$events->save();

		user_log('Events', 'create', $events->eventTitle);

		//Send Push Notifications
		$tokens_list = array();
		$user_ids = array();
		if($events->eventFor == "all"){
			$user_list = \User::select('id', 'firebase_token')->get();
		}else{
			$user_list = \User::where('role',$events->eventFor)->select('id', 'firebase_token')->get();
		}
		foreach ($user_list as $value) {
			if($value['firebase_token'] != ""){
				if(is_array(json_decode($value['firebase_token']))) {
					foreach (json_decode($value['firebase_token']) as $token) {
						$tokens_list[] = $token;
						// \Log::debug("Event Token: " . $token);
					}
				} else if ($this->isJson($value['firebase_token'])) {
					foreach (json_decode($value['firebase_token']) as $token) {
						$tokens_list[] = $token;
						// \Log::debug("Event Token: " . $token);
					}
				} else {
					$tokens_list[] = $value['firebase_token'];
					// \Log::debug("Event Token: " . $value['firebase_token']);
				}
			}
			$user_ids[] = $value['id'];
		}

		// $tokens_list = array_unique($tokens_list);

		$eventDescription = strip_tags(\Input::get('eventDescription'));
		if(count($tokens_list) > 0){
			$this->panelInit->send_push_notification(
				$tokens_list,
				$user_ids,
				$eventDescription,
				$events->eventTitle,
				"events",
				$events->id
			);
		} else {
			$this->panelInit->save_notifications_toDB(
				$tokens_list,
				$user_ids,
				$eventDescription,
				$events->eventTitle,
				"events",
				$events->id
			);
		}

		$events->eventDescription = strip_tags(htmlspecialchars_decode($events->eventDescription));
		$events->eventDate = $this->panelInit->unix_to_date($events->eventDate);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addEvent'],$this->panelInit->language['eventCreated'],$events->toArray() );
	}

	protected function isJson($string)
	{
    	$decoded = json_decode($string); // decode our JSON string
	    if ( !is_object($decoded) && !is_array($decoded) ) {
	        return false;
	    }
	    return (json_last_error() == JSON_ERROR_NONE);
	}

	function fetch($id)
	{

		if(!$this->panelInit->can( array("events.View","events.editEvent") )){
			exit;
		}

		if(\events::where('id',$id)->count() <= 0) {
			exit;
		}

		$data = \events::where('id',$id)->first()->toArray();
		$data['eventDescription'] = htmlspecialchars_decode($data['eventDescription'],ENT_QUOTES);
		$data['eventDate'] = $this->panelInit->unix_to_date($data['eventDate']);

		// make notification seen
		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'events', $id);

		return json_encode($data);
	}

	function edit($id)
	{

		if(!$this->panelInit->can( "events.editEvent" )){
			exit;
		}

		$events = \events::find($id);
		$events->eventTitle = \Input::get('eventTitle');
		$events->eventDescription = htmlspecialchars(\Input::get('eventDescription'),ENT_QUOTES);
		$events->eventFor = \Input::get('eventFor');
		$events->enentPlace = \Input::get('enentPlace');
		$events->eventDate = $this->panelInit->date_to_unix(\Input::get('eventDate'));
		$events->fe_active = \Input::get('fe_active');

		if (\Input::hasFile('eventImage')) {
			$fileInstance = \Input::file('eventImage');

			if(!$this->panelInit->validate_upload($fileInstance)){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['editEvent'],"Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

			if($fileInstance->getClientOriginalExtension() != 'jpg' || $fileInstance->getClientOriginalExtension() != 'png' || $fileInstance->getClientOriginalExtension() != 'jpeg'){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addEvent'],"Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

			$newFileName = uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move(uploads_config()['uploads_file_path'] . '/events/',$newFileName);

			$events->eventImage = $newFileName;
		}

		$events->save();

		user_log('Events', 'edit', $events->eventTitle);

		$events->eventDescription = strip_tags(htmlspecialchars_decode($events->eventDescription));
		$events->eventDate = $this->panelInit->unix_to_date($events->eventDate);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editEvent'],$this->panelInit->language['eventModified'],$events->toArray() );
	}

	function fe_active($id)
	{

		if(!$this->panelInit->can( "events.editEvent" )){
			exit;
		}

		$events = \events::find($id);

		if($events->fe_active == 1){
			$events->fe_active = 0;
		}else{
			$events->fe_active = 1;
		}

		$events->save();

		user_log('Events', 'update_status', $events->eventTitle);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editEvent'],$this->panelInit->language['eventModified'], array("fe_active"=>$events->fe_active) );
	}
}