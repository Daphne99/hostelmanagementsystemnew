<?php
namespace App\Http\Controllers;

use App\Models2\Newsboard;
use App\Models2\User;
use App\Models2\Main;
use Carbon\Carbon;
// use App\Jobs\SendNotification;
use Illuminate\Support\Facades\Auth;

class NewsBoardController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		if(app('request')->header('Authorization') != "" || \Input::has('token')) { $this->middleware('jwt.auth'); }
		else { $this->middleware('authApplication'); }

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) { return \Redirect::to('/'); }
	}

	public function listAll($page = 1)
	{

		if(!$this->panelInit->can( array("newsboard.list","newsboard.View","newsboard.addNews","newsboard.editNews","newsboard.delNews") )){
			exit;
		}

		$toReturn = array();
		if($this->data['users']->role == "admin" ){
			$toReturn['newsboard'] = \newsboard::orderby('newsDate','DESC')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get()->toArray();
			$toReturn['totalItems'] = \newsboard::count();
		}else{
			 $toReturn['newsboard'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->orderby('newsDate','DESC')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get()->toArray();
			 $toReturn['totalItems'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->count();
		}

		foreach ($toReturn['newsboard'] as $key => $item) {
			$toReturn['newsboard'][$key]['newsText'] = strip_tags(htmlspecialchars_decode($toReturn['newsboard'][$key]['newsText'],ENT_QUOTES));
			$toReturn['newsboard'][$key]['newsDate'] = $this->panelInit->unix_to_date($toReturn['newsboard'][$key]['newsDate']);
		}

		return $toReturn;
	}

	public function search($keyword,$page = 1)
	{
		if(!$this->panelInit->can( array("newsboard.View","newsboard.addNews","newsboard.editNews","newsboard.delNews") )){
			exit;
		}

		$toReturn = array();
		if($this->data['users']->role == "admin" ){
			$toReturn['newsboard'] = \newsboard::where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get()->toArray();
			$toReturn['totalItems'] = \newsboard::where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->count();
		}else{
			 $toReturn['newsboard'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get()->toArray();
			 $toReturn['totalItems'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->count();
		}

		foreach ($toReturn['newsboard'] as $key => $item) {
			$toReturn['newsboard'][$key]['newsText'] = strip_tags(htmlspecialchars_decode($toReturn['newsboard'][$key]['newsText'],ENT_QUOTES));
			$toReturn['newsboard'][$key]['newsDate'] = $this->panelInit->unix_to_date($toReturn['newsboard'][$key]['newsDate']);
		}

		return $toReturn;
	}

	public function delete( $id ){
		if(!$this->panelInit->can( "newsboard.delNews" )) { exit; }
		if ( $postDelete = \newsboard::where('id', $id)->first() ){
			user_log('Newsboard', 'delete', $postDelete->newsTitle);
			$postDelete->delete();
			return $this->panelInit->apiOutput(true,$this->panelInit->language['delNews'],$this->panelInit->language['newsDeleted']);
		} else {
			return $this->panelInit->apiOutput(false,$this->panelInit->language['delNews'],$this->panelInit->language['newsNotEist']);
		}
	}

	public function create(){

		if(!$this->panelInit->can( "newsboard.addNews" )){
			exit;
		}

		$newsboard = new \newsboard();
		$newsboard->newsTitle = \Input::get('newsTitle');
		$newsboard->newsText = htmlspecialchars(\Input::get('newsText'),ENT_QUOTES);
		$newsboard->newsFor = \Input::get('newsFor');
		$newsboard->newsDate = $this->panelInit->date_to_unix(\Input::get('newsDate'));
		$newsboard->fe_active = \Input::get('fe_active');
		$newsboard->creationDate = time();

		if (\Input::hasFile('newsImage')) {
			$fileInstance = \Input::file('newsImage');

			if(!$this->panelInit->validate_upload($fileInstance)){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addNews'],"Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

			$newFileName = uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move(uploads_config()['uploads_file_path'] . '/news/',$newFileName);

			$newsboard->newsImage = $newFileName;
		}

		$newsboard->save();

		user_log('Newsboard', 'create', $newsboard->newsTitle);

		//Send Push Notifications
		$tokens_list = array();
		$user_ids = array();
		if($newsboard->newsFor == "all"){
			$user_list = \User::select('firebase_token', 'id')->get();
		}else{
			$user_list = \User::where('role',$newsboard->newsFor)->select('id', 'firebase_token')->get();
		}
		foreach ($user_list as $value) {
			if($value['firebase_token'] != ""){
				if(is_array(json_decode($value['firebase_token']))) {
					foreach (json_decode($value['firebase_token']) as $token) {
						$tokens_list[] = $token;
					}
				} else {
					$tokens_list[] = $value['firebase_token'];
				}
			}
			$user_ids[] = $value['id'];
		}

		$newsText = strip_tags(\Input::get('newsText'));
		if(count($tokens_list) > 0){
			$this->panelInit->send_push_notification(
				$tokens_list,
				$user_ids,
				$newsText,$newsboard->newsTitle,"newsboard",$newsboard->id
			);
		} else {
			$this->panelInit->save_notifications_toDB(
				$tokens_list,
				$user_ids,
				$newsText,$newsboard->newsTitle,"newsboard",$newsboard->id
			);
		}

		$newsboard->newsText = strip_tags(htmlspecialchars_decode($newsboard->newsText));

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addNews'],$this->panelInit->language['newsCreated'],$newsboard->toArray() );
	}

	function fetch($id){

		if(!$this->panelInit->can( array("newsboard.View","newsboard.editNews") )){
			exit;
		}

		$data = \newsboard::where('id',$id)->first()->toArray();
		$data['newsText'] = htmlspecialchars_decode($data['newsText'],ENT_QUOTES);
		$data['newsDate'] = $this->panelInit->unix_to_date($data['newsDate']);

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'newsboard', $id);

		return json_encode($data);
	}

	function edit($id){

		if(!$this->panelInit->can( "newsboard.editNews" )){
			exit;
		}

		$newsboard = \newsboard::find($id);
		$newsboard->newsTitle = \Input::get('newsTitle');
		$newsboard->newsText = htmlspecialchars(\Input::get('newsText'),ENT_QUOTES);
		$newsboard->newsFor = \Input::get('newsFor');
		$newsboard->newsDate = $this->panelInit->date_to_unix(\Input::get('newsDate'));
		$newsboard->fe_active = \Input::get('fe_active');

		if (\Input::hasFile('newsImage')) {
			$fileInstance = \Input::file('newsImage');

			if(!$this->panelInit->validate_upload($fileInstance)){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['editNews'],"Sorry, This File Type Is Not Permitted For Security Reasons ");
			}

			$newFileName = uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move(uploads_config()['uploads_file_path'] . '/news/',$newFileName);

			$newsboard->newsImage = $newFileName;
		}

		$newsboard->save();

		user_log('Newsboard', 'edit', $newsboard->newsTitle);

		$newsboard->newsText = strip_tags(htmlspecialchars_decode($newsboard->newsText));

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editNews'],$this->panelInit->language['newsModified'],$newsboard->toArray() );
	}

	function fe_active($id){

		if(!$this->panelInit->can( "newsboard.editNews" )){
			exit;
		}

		$newsboard = \newsboard::find($id);

		if($newsboard->fe_active == 1){
			$newsboard->fe_active = 0;
		}else{
			$newsboard->fe_active = 1;
		}

		$newsboard->save();

		user_log('Newsboard', 'update_status', $newsboard->newsTitle);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editNews'],$this->panelInit->language['newsModified'], array("fe_active"=>$newsboard->fe_active) );
	}

	public function createNewNotice()
	{
		if( !$this->panelInit->can( "newsboard.addNews" ) ) { return $this->panelInit->apiOutput( false, "Create notice", "You don't have permission to create notice" ); }
		if( !\Input::has('title') ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice title is missing" ); }
		if( !trim( \Input::get('title') ) ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice title is missing" ); }
		if( !\Input::has('description') ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice content is missing" ); }
		if( !trim( \Input::get('description') ) ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice content is missing" ); }
		if( !\Input::has('recipients') ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice recipients is missing" ); }
		if( !trim( \Input::get('recipients') ) ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice recipients is missing" ); }
		$recipients = explode(',', \Input::get('recipients'));
		if( !is_array( $recipients ) ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice recipients is missing" ); }
		if( count( $recipients ) <= 0 ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice recipients is missing" ); }
		if( count( $recipients ) > 1000 ) { return $this->panelInit->apiOutput( false, "Create notice", "Maximum sending to users is 1000, Please reduce the number and resend to other users" ); }
		
		if( !\Input::has('date') ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice Date is missing" ); }
        $date = formatDate( \Input::get('date') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice Date has invalid format" ); }
		if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Create notice", "Notice Date has invalid format" ); }
		$date = toUnixStamp( $date );

		if( \Input::hasFile('image') )
		{
			$errMsg = "Sorry, This File Type Is Not Permitted For Security Reasons ";
			$fileInstance = \Input::file('image');
			if( !$this->panelInit->validate_upload($fileInstance) ) { return $this->panelInit->apiOutput(false, "Create notice", $errMsg); }
			$extenstion = $fileInstance->getClientOriginalExtension();
			$allowedExtenstions = ["jpg", "png", "jpeg"];
			if( !in_array($extenstion, $allowedExtenstions) ) { return $this->panelInit->apiOutput(false, "Create notice", $errMsg); }
			$newFileName = uniqid() . "." . $extenstion;
			$fileInstance->move(uploads_config()['uploads_file_path'] . '/news/',$newFileName);
		} else $newFileName = NULL;
		if( $newFileName == NULL ) $newFileName = "default.png";
		$noticeName = \Input::get('title');
		$notice = new Newsboard();
		$notice->newsTitle = trim( \Input::get('title') );
		$notice->newsText = htmlspecialchars(\Input::get('description'), ENT_QUOTES);
		$notice->newsCreator = $this->data['users']->id;
		$notice->newsFor = "custom";
		$notice->participants = json_encode( $recipients );
		$notice->newsDate = $date;
		$notice->newsImage = $newFileName;
		$notice->fe_active = 1;
		$notice->creationDate = time();
		$notice->newsSeenMembers = NULL;
		$notice->created_at = date('Y-m-d H:i:s', time());
		$notice->updated_at = date('Y-m-d H:i:s', time());
		$notice->save();
		user_log('Newsboard', 'create', $notice->newsTitle);
		
		$settings = (array) $this->panelInit->settingsArray;
		// dispatch( new SendNotification( "Notices", $noticeName, $description, $recipients, $startDate, $endDate, $settings ) );
		return $this->panelInit->apiOutput( true, "Create notice", "Notice created successfully" );
	}

	public function listNotices( $page = 1 )
	{
		$toReturn = array();
		User::$withoutAppends = true;
		$currentUser = $this->data['users']->id;
		
		$notices = Newsboard::select('*');
		if( $this->data['users']->role != "admin" )
		{
			if( \Input::has('myNotices') ) { $notices = $notices->where('newsCreator', $currentUser); }
			else { $notices = $notices->where('participants', 'LIKE', '%"' . $currentUser . '"%'); }
		}
		$notices = $notices->orderBy('creationDate', 'DESC');
		if( \Input::has('date') )
		{
			$date = formatDate( \Input::get('date') );
			$startDate = strtotime("today", toUnixStamp( $date ));
			$endDate = strtotime("tomorrow", toUnixStamp( $date )) - 1;
			$notices = $notices->where('newsDate', '>=', $startDate);
			$notices = $notices->where('newsDate', '<=', $endDate);
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
			
			if( count( $userIds ) == 0 ) { $notices = $notices->Where('id', '0'); }
            else
            {
                $notices = $notices->Where(
                    function ( $notices ) use( $userIds ) {
                    foreach($userIds as $recipient_id ) { $notices->orwhere('participants', 'LIKE', '%"' . $recipient_id . '"%'); }
               });
            }
            $toReturn['filter'] = true;
		} else $toReturn['filter'] = false;
		
        $toReturn['totalItems'] = $notices->count();
        $notices = $notices->take(all_pagination_number())
            ->skip(all_pagination_number() * ($page - 1))
            ->get();
		$toReturn['notices'] = $notices;
		foreach( $toReturn['notices'] as $key => $value)
		{
			$participants = json_decode( $value['participants'], true);
			if( json_last_error() != JSON_ERROR_NONE ) $participants = [];
			$seenMembers = json_decode( $value['newsSeenMembers'], true);
			if( json_last_error() != JSON_ERROR_NONE ) $seenMembers = [];
			$toReturn['notices'][$key]['until'] = Carbon::parse( date('Y-m-d h:i:s A', $value['newsDate']) )->diffForHumans();
			$toReturn['notices'][$key]['newsDate'] = date('jS M Y', $value['newsDate']);
            $toReturn['notices'][$key]['participants'] = $participants;
			$toReturn['notices'][$key]['newsSeenMembers'] = $seenMembers;
			$toReturn['notices'][$key]['selected'] = false;
			$toReturn['notices'][$key]['newsText'] = strip_tags( htmlspecialchars_decode( $value['newsText'], ENT_QUOTES ) );

			if( strlen( $value['newsTitle'] ) <= 24 )
                $toReturn['notices'][$key]['name'] = $value['newsTitle'];
            else
				$toReturn['notices'][$key]['name'] = substr( $value['newsTitle'], 0, 24 ) . "...";

			if( !trim( $value['newsImage'] ) || trim( $value['newsImage'] ) == "" ) { $toReturn['notices'][$key]['newsImage'] = "default.png"; }
			if( $value->user )
			{
				$toReturn['notices'][$key]['creator'] = $value->user->fullName;
			} else $toReturn['notices'][$key]['creator'] = "Unknown";
			unset( $toReturn['notices'][$key]['user'] );
			$created_at = $value['created_at'];
			$stamp = toUnixStamp( $created_at );
			$toReturn['notices'][$key]['creation_date'] = date('d/m/Y', $stamp);
			$toReturn['notices'][$key]['creation'] = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
			if( $currentUser == $value['newsCreator'] ) { $toReturn['notices'][$key]['isRead'] = true; }
			else
			{
				if( array_key_exists( $currentUser, $seenMembers ) ) { $toReturn['notices'][$key]['isRead'] = true; }
				else { $toReturn['notices'][$key]['isRead'] = false; }
			}
		}
		return $toReturn;
	}

	public function loadNotice( $image )
	{
		header('Content-Type: image/jpeg');
        $uploads_file_path = uploads_config()['uploads_file_path'];
		$fileName = !trim( $image ) ? "default.png" : ( file_exists( $uploads_file_path . "/news/$image" ) ? $image : "default.png" );
		echo file_get_contents( $uploads_file_path . "/news/$fileName");
	}

	public function readNotice( $id )
	{
		$toReturn = array();
		$toReturn['status'] = "success";
		User::$withoutAppends = true;
		$currentUser = $this->data['users']->id;
		$currentName = $this->data['users']->fullName;
		$role = $this->data['users']->role;
		$notice = Newsboard::find( $id );
		if( !$notice ) { return $this->panelInit->apiOutput( false, "Read Notice", "Unable to Read notice data" ); }
		$creator = $notice->newsCreator;
		$status = ( $role == "admin" || intval( $currentUser ) == intval( $creator ) ) ? true : false;
		$toReturn['noticeDet'] = $notice;
		$value = (array)$toReturn['noticeDet'];

		$participants = json_decode( $notice->participants, true);
		if( json_last_error() != JSON_ERROR_NONE ) $participants = [];
		$seenMembers = json_decode( $notice->newsSeenMembers, true);
		if( json_last_error() != JSON_ERROR_NONE ) $seenMembers = [];
		if( !$status && !in_array( $currentUser, $participants ) ) { return $this->panelInit->apiOutput( false, "Read Notice", "you don't have permission to show this notice details" ); }
		
		$toReturn['noticeDet']->participants = $participants;
		$toReturn['noticeDet']->newsSeenMembers = $seenMembers;

		$usersIdsList = [];
		if( $notice->newsFor == "teacher" )
		{
			$users = User::select('id', 'fullName as name', 'role')->where('role', 'teacher')->get()->toArray();
			foreach($users as $user) $usersIdsList[] = $user['id'];
			$getNotice = Newsboard::find( $id );
			$getNotice->participants = json_encode( $usersIdsList );
			$getNotice->newsFor = "custom";
			$getNotice->save();
		}
		elseif( $notice->newsFor == "parent" )
		{
			$users = User::select('id', 'fullName as name', 'role')->where('role', 'parent')->get()->toArray();
			foreach($users as $user) $usersIdsList[] = $user['id'];
			$getNotice = Newsboard::find( $id );
			$getNotice->participants = json_encode( $usersIdsList );
			$getNotice->newsFor = "custom";
			$getNotice->save();
		}
		else
			$users = User::select('id', 'fullName as name', 'role')->whereIn('id', $participants)->get()->toArray();
		
		if( !array_key_exists($currentUser, $seenMembers) )
		{
			$myStatus = [ 'seen' => true, 'seenDate' => time() ];
			$seenMembers[$currentUser] = $myStatus;
		}
		else
		{
			$scopedStatus = $seenMembers[$currentUser];
			$myStatus = [ 'seen' => $scopedStatus['seen'], 'seenDate' => $scopedStatus['seenDate'] ];
		}
		
		foreach( $users as $key => $user )
		{
			$userId = $user['id'];
			if( $status )
			{
				if( array_key_exists($userId, $seenMembers) )
				{
					$seenData = $seenMembers[$userId]['seenDate'];
					$users[$key]['details'] = [
						'isSeen' => $seenMembers[$userId]['seen'],
						'seenDate' => $seenData != "" ? date('Y-m-d h:i:s A', $seenData) : "",
						'isInvited' => true
					];
				} else { $users[$key]['details'] = [ 'isSeen' => false, 'seenDate' => "", 'isInvited' => true ]; }
			} else $users[$key]['details'] = [];
		}
		if( !in_array( $currentUser, $participants ) )
		{
			$seenData = $myStatus['seenDate'];
			$users[] = [
				'id' => $currentUser,
				'name' => $currentName,
				'role' => $role,
				'details' => [
					'isSeen' => $myStatus['seen'],
					'seenDate' => $seenData != "" ? date('Y-m-d h:i:s A', $seenData) : "",
					'isInvited' => false
				]
			];
		}
		
		if( count( $seenMembers ) != count( $toReturn['noticeDet']->newsSeenMembers ) )
		{
			$scopedNotice = Newsboard::select( 'id', 'newsSeenMembers' )->where('id', $id)->first();
			$scopedNotice->newsSeenMembers = json_encode( $seenMembers );
			$scopedNotice->save();
		}
		$toReturn['noticeDet']['date'] = date('d/m/Y', $notice->newsDate);
		$toReturn['noticeDet']['until'] = Carbon::parse( date('Y-m-d h:i:s A', $notice->newsDate) )->diffForHumans();
		$toReturn['noticeDet']['newsDate'] = date('jS M Y', $notice->newsDate);
		
		if( $notice->user )
		{
			$toReturn['noticeDet']['creator'] = $notice->user->fullName;
		} else $toReturn['noticeDet']['creator'] = "Unknown";
		
		$created_at = $notice->created_at;
		$stamp = toUnixStamp( $created_at );
		$toReturn['noticeDet']['creation_date'] = date('d/m/Y', $stamp);
		$toReturn['noticeDet']['creation'] = Carbon::parse( date('Y-m-d h:i:s A', $stamp) )->diffForHumans();
		
		if( !trim( $notice->newsImage ) || trim( $notice->newsImage ) == "" ) { $toReturn['noticeDet']['newsImage'] = "default.png"; }

		unset( $toReturn['noticeDet']['created_at'] );
		unset( $toReturn['noticeDet']['updated_at'] );
		unset( $toReturn['noticeDet']['participants'] );
		unset( $toReturn['noticeDet']['newsSeenMembers'] );
		unset( $toReturn['noticeDet']['fe_active'] );
		unset( $toReturn['noticeDet']['user'] );

		$toReturn['noticeDet']['desc'] = strip_tags( htmlspecialchars_decode( $toReturn['noticeDet']['newsText'], ENT_QUOTES ) );
		$toReturn['noticeDet']['newsText'] = htmlspecialchars_decode( $toReturn['noticeDet']['newsText'], ENT_QUOTES );
		$toReturn['noticeDet']['members'] = $users;
		$toReturn['noticeDet']['user'] = $currentUser;
		$toReturn['noticeDet']['status'] = $status;
		$toReturn['noticeDet']['targetIndex'] = 0;
		return $toReturn;
	}

	public function editNotice()
	{
		if( !$this->panelInit->can( "newsboard.editNews" ) ) { return $this->panelInit->apiOutput( false, "Update notice", "You don't have permission to edit notice" ); }
		if( !\Input::has('notice_id') ) { return $this->panelInit->apiOutput( false, "Update notice", "Selected notice not found" ); }
		$id = \Input::get('notice_id');
		$notice = Newsboard::find( $id );
		if( !$notice ) { return $this->panelInit->apiOutput( false, "Update notice", "Selected notice not found" ); }
		if( !\Input::has('title') ) { return $this->panelInit->apiOutput( false, "Update notice", "Notice title is missing" ); }
		if( !trim( \Input::get('title') ) ) { return $this->panelInit->apiOutput( false, "Update notice", "Notice title is missing" ); }
		$name = trim( \Input::get('title') );
		if( !\Input::has('description') ) { return $this->panelInit->apiOutput( false, "Update notice", "Notice content is missing" ); }
		if( !trim( \Input::get('description') ) ) { return $this->panelInit->apiOutput( false, "Update notice", "Notice content is missing" ); }
		$desc = trim( \Input::get('description') );
		if( !\Input::has('date') ) { return $this->panelInit->apiOutput( false, "Update notice", "Notice Date is missing" ); }
        $date = formatDate( \Input::get('date') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Update notice", "Notice Date has invalid format" ); }
		if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Update notice", "Notice Date has invalid format" ); }
		$date = toUnixStamp( $date );
		$imageStatus = \Input::get('imageStatus');
		$oldFileName = $notice->newsImage;
		if( $imageStatus == "false" && $oldFileName != "default.png" )
		{
			if( file_exists( uploads_config()['uploads_file_path'] . "/news/$oldFileName" ) )
            {
				unlink( uploads_config()['uploads_file_path'] . "/news/$oldFileName" );
			}
		}
		if( \Input::hasFile('image') )
		{
			$errMsg = "Sorry, This File Type Is Not Permitted For Security Reasons ";
			$fileInstance = \Input::file('image');
			if( !$this->panelInit->validate_upload($fileInstance) ) { return $this->panelInit->apiOutput(false, "Update notice", $errMsg); }
			$extenstion = $fileInstance->getClientOriginalExtension();
			$allowedExtenstions = ["jpg", "png", "jpeg"];
			if( !in_array($extenstion, $allowedExtenstions) ) { return $this->panelInit->apiOutput(false, "Update notice", $errMsg); }
			$newFileName = uniqid() . "." . $extenstion;
			if( trim( $oldFileName ) && $oldFileName != "default.png" )
			{
				if( file_exists( uploads_config()['uploads_file_path'] . "/news/$oldFileName" ) )
            	{
                	unlink( uploads_config()['uploads_file_path'] . "/news/$oldFileName" );
            	}
			}
			$fileInstance->move(uploads_config()['uploads_file_path'] . '/news/',$newFileName);
		} else $newFileName = NULL;
		if( $newFileName == NULL ) $newFileName = "default.png";

		$notice->newsTitle = $name;
		$notice->newsText = htmlspecialchars($desc, ENT_QUOTES);
		$notice->newsDate = $date;
		$notice->newsImage = $newFileName;
		$notice->updated_at = time();
		$notice->save();
		user_log('Newsboard', 'edit', $notice->newsTitle);
		$data = [
			'name' => strlen( $name ) <= 24 ? $name : substr( $name, 0, 24 ) . "...",
			'title' => $name,
			'date' => date('d/m/Y', $date),
			'until' => Carbon::parse( date('Y-m-d h:i:s A', $date) )->diffForHumans(),
			'newsDate' => date('jS M Y', $date),
			'desc' => htmlspecialchars_decode( $desc, ENT_QUOTES ),
			'newsImage' => $newFileName
		];
		return $this->panelInit->apiOutput( true, "Create notice", "Notice created successfully", $data );
	}

	public function removeNotice()
	{
		if( !$this->panelInit->can( "newsboard.delNews" ) ) { return $this->panelInit->apiOutput( false, "Delete notice", "You don't have permission to edit notice" ); }
		if( !\Input::has('notice_id') ) { return $this->panelInit->apiOutput( false, "Delete notice", "Selected notice not found" ); }
		$id = \Input::get('notice_id');
		$notice = Newsboard::find( $id );
		if( !$notice ) { return $this->panelInit->apiOutput( false, "Delete notice", "Selected notice not found" ); }
		user_log('Newsboard', 'remove', $notice->newsTitle);
		$notice->delete();
		return $this->panelInit->apiOutput( true, "Delete notice", "Notice deleted successfully" );
	}

	public function bulkDelete()
	{
		if( !$this->panelInit->can( "newsboard.delNews" ) ) { return $this->panelInit->apiOutput( false, "Delete notice", "You don't have permission to edit notice" ); }
		if( !\Input::has('items') ) { return $this->panelInit->apiOutput( false, "Delete notice", "Selected notice not found" ); }
		$notices_ids = \Input::get('items');
		if( !count( $notices_ids ) ) { return $this->panelInit->apiOutput( false, "Delete notice", "Selected notice not found" ); }
		$role = $this->data['users']->role;
		$currentUser = $this->data['users']->id;
		foreach( $notices_ids as $notice_id )
		{
			$notice = Newsboard::find( $notice_id );
			if( !$notice ) continue;
			if( $notice->newsCreator == $currentUser || $role == "admin" ) $notice->delete();
			else
			{
				$newGuests = [];
				$participants = json_decode( $notice->participants, true);
				if( json_last_error() != JSON_ERROR_NONE ) $participants = [];
				foreach( $participants as $guest )
				{
					if( $guest == $currentUser ) continue;
					else $newGuests[] = $guest;
				}
				$notice->participants = json_encode( $newGuests );
				$notice->save();
			}
		}
		return $this->panelInit->apiOutput( true, "Delete notice", "Notice deleted successfully" );
	}
}