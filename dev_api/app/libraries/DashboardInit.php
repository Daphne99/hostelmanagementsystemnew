<?php

use App\Models2\Message;
use App\Models2\NotificationMobHistory;

class DashboardInit {
	public $menuElement;
	public $settingsArray = array();
	public $language;

	public $version = "5.2";
	public $nversion = "520";

	public $lowAndVersion = "4.0";
	public $nLowAndVersion = "400";

	public $lowiOsVersion = "2.0";
	public $nLowiOsVersion = "200";

	public $teacherClasses = array();
	public $isRTL;
	public $languageUniversal;
	public $selectAcYear;
	public $defTheme;
	public $baseURL;
	public $customPermissionsDecoded;
	public $calendarsLocale = array("ethiopic"=>"am","gregorian"=>"en_US","islamic"=>"en_US","persian"=>"fa");
	public $perms = array();
	public $cache_duration = 120;

	public function __construct(){
		// must set viaRemember() then check() then check viaRemember() again
		\Auth::viaRemember();
		\Auth::check();

		date_default_timezone_set('Asia/Kolkata');
		$current_role = Auth::check() || \Auth::viaRemember() ? \Auth::user()->role : "";

		// Prepare setting Array ---------------------------------------------------------
			if( !Cache::has('setting_table_pluck') )
			{
				$settings = settings::pluck('fieldValue', 'fieldName')->toArray();
				Cache::put('setting_table_pluck', $settings, $this->cache_duration);
			} else { $settings = Cache::get('setting_table_pluck'); }
			$this->settingsArray = array_merge($settings, ["thisVersion" => $this->version]);
			if(config('app.env') == 'production') { if($this->settingsArray['https_enabled'] == "1") { \URL::forceSchema('https'); } }
		// ------------------------------------------------------------------------------
			
		$this->menuElement = array(
			"dashboard" => array("title"=>"dashboard","icon"=>"mdi mdi-gauge","icon_img"=>"001-analysis.svg","url"=> URL::to('portal#/') ),
			"messaging" => array(
				"title" => "Messaging", "icon" => "mdi mdi-comment-text", "icon_img" => "004-mail.svg",
				"children" => [
					"messages" => [
						"title"=>"View Messages", "url"=>URL::to('portal#/messages'), "icon"=>"mdi mdi-message-text-outline",
						"role_perm"=>array("messaging.list", "messaging.View", "messaging.editMsg", "messaging.delMsg")
					],
					"composer" => [ "title"=>"Compose Message", "url"=>URL::to('portal#/messages/compose'), "icon"=>"mdi mdi-plus", "role_perm"=>array("messaging.addMsg") ]
				]
			),
			"evantous" => array(
				"title" => "Events", "icon" => "mdi mdi-calendar-clock", "icon_img" => "008-organisation.svg",
				"children" => [
					"listEvents" => [
						"title"=>"View Events", "url"=>URL::to('portal#/events'), "icon"=>"mdi mdi-calendar-clock",
						"role_perm"=>array("events.list","events.View","events.editEvent","events.delEvent")
					],
					"sendEvent" => [
						"title"=>"Add Event", "url"=>URL::to('portal#/events/compose'), "icon"=>"mdi mdi-plus", "role_perm"=>array("events.addEvent")
					],
				]
			),
			"notices" => array(
				"title" => "Notice", "icon" => "mdi mdi-bullhorn", "icon_img" => "003-team.svg",
				"children" => [
					"listNotices" => [
						"title"=>"View Notices", "url"=>URL::to('portal#/notices'), "icon"=>"mdi mdi-bullhorn",
						"role_perm"=>array("newsboard.list","newsboard.View","newsboard.editNews","newsboard.delNews")
					],
					"sendNotice" => [
						"title"=>"Add Notice", "url"=>URL::to('portal#/notices/compose'), "icon"=>"mdi mdi-plus",
						"role_perm"=>array("newsboard.addNews")
					],
				]
			),
			"academic" => array("title"=>"Academic","icon"=>"fa fa-graduation-cap","icon_img"=>"002-school.svg",
				"children"=>array(
					"academicyear"=>array("title"=>"Academic Years","icon"=>"mdi mdi-calendar-clock","url"=>URL::to('portal#/academic/academicYear'),"role_perm"=>array("academicyears.list","academicyears.addAcademicyear","academicyears.editAcademicYears","academicyears.delAcademicYears")),
					"enter_classes_subjects"=>array("title"=>"Enter Subject","icon"=>"fa fa-plus","url"=>URL::to('portal#/academic/subjects'),"role_perm"=>array("classes.addClass","Subjects.addSubject") ),
					"classes_sections_teachers" => array(
						"title"=>"Classes, Sections and Teachers", "icon"=>"mdi mdi-kodi", "url"=>URL::to('portal#/academic/classesSectionsAndTeachers'),
						"role_perm"=>array("classes.list","classes.addClass","classes.editClass","classes.delClass","sections.list","sections.addSection","sections.editSection","sections.delSection")
					),
					"homework"=>array("title"=>"Homework","url"=>URL::to('portal#/academic/homework'),"role_perm"=>array("Homework.list","Homework.View","Homework.addHomework","Homework.editHomework","Homework.delHomework","Homework.Download"),"icon"=>"mdi mdi-book" ),
					"assignments"=>array(
						"title"=>"Assignment", "icon"=>"mdi mdi-file-pdf", "url"=>URL::to('portal#/academic/assignments'),
						"role_perm"=>array("Assignments.list","Assignments.Download","Assignments.AddAssignments","Assignments.editAssignment","Assignments.delAssignment","Assignments.viewAnswers","Assignments.applyAssAnswer")
					),
					"mySubjects"=>array("title"=>"My Subjects","url"=>URL::to('portal#/academic/my-subjects'),"role_perm"=>array("subjects.mySubjects"),"icon"=>"fa fa-book" ),
				)
			),
			"attendance"=>array("title"=>"Attendance","icon"=>"mdi mdi-check-all","icon_img"=>"014-attendant-list.svg",
				"children"=>array(
					"takeAttendance" => array( "title" => "Attendance Register", "url" => URL::to('portal#/student-attendance/take'), "role_perm" => array( "Attendance.takeAttendance"), "icon"=>"mdi mdi-check-all" ),
					"reportAttendance" => array( "title" => "", "url" => URL::to('portal#/student-attendance/report'), "role_perm" => array( "Attendance.attReport"), "icon"=>"fa fa-calendar-check-o" )
				)
			),
			"users" => array("title"=>"Students","icon"=>"mdi mdi-account-location","icon_img"=>"003-team.svg",
				"children"=>array(
					"students"=>array("title"=>"View Students","url"=>URL::to("portal#/student"),"icon"=>"mdi mdi-account-multiple-outline","role_perm"=>array("students.list","students.editStudent","students.delStudent","students.listGradStd","students.Approve","students.stdLeaderBoard","students.Import","students.Export","students.Attendance","students.Marksheet","students.medHistory","students.TrackBus")),
					"addStudents"=> array( "title"=>"Create Student", "url"=>URL::to('portal#/student/create'), "icon"=>"mdi mdi-plus", "role_perm"=>array("manageStudents.add") ),
					"studentsPromotions" => array(  "title"=>"Students Promotion", "icon"=>"fa fa-graduation-cap", "url"=>URL::to("portal#/student/promotion"), "role_perm"=>array( "Promotion.promoteStudents" ) ),
					"StudentType"=>array("title"=>"Fee Group Type","url"=>URL::to('portal#/student/types'),"icon"=>"mdi mdi-account-multiple-outline","role_perm"=>array("studentType.list","studentType.add","studentType.edit","studentType.del")),
					"StudentCategory"=>array("title"=>"Student category","url"=>URL::to('portal#/student/categories'),"icon"=>"mdi mdi-account-multiple-outline","role_perm"=>array("studentCat.list","studentCat.add","studentCat.edit","studentCat.del")),
				)
			),
			"teachers" => array(
				"title"=>"View Teachers", "icon"=>"fa fa-users", "icon_img"=>"003-team.svg", "url"=> URL::to('portal#/teacher'),
				"role_perm"=>array("teachers.list","teachers.addTeacher","teachers.EditTeacher","teachers.delTeacher","teachers.Approve","teachers.teacLeaderBoard","teachers.Import","teachers.Export")
			),
			"classSchedule" => array( "title"=>"Timetable", "icon"=>"mdi mdi-timelapse", "icon_img"=>"008-organisation.svg",
				"children"=>array(
					"classScheduleClassTimetable"=>array( "title"=>"Class Timetable", "url"=>URL::to('portal#/institution-timetable'), "icon"=>"mdi mdi-timelapse", "role_perm"=>array("timeTableClassWise.list","timeTableClassWise.addSch","timeTableClassWise.editSch","timeTableClassWise.delSch") ),
					"AdvTimetableClasswise"=>array( "title"=>"Institution timetable (class wise)", "url"=>URL::to('portal#/institution-timetable-classwise'), "role_perm"=>array("timeTableClassWise.list","timeTableClassWise.addSch","timeTableClassWise.editSch","timeTableClassWise.delSch"), "icon"=>"mdi mdi-timelapse" ),
					"AdvTimetableTeacherwise"=>array( "title"=>"Institution timetable (teacher wise)", "url"=>URL::to('portal#/institution-timetable-teacherwise'), "role_perm"=>array("timeTableTeacherWise.list","timeTableTeacherWise.addSch","timeTableTeacherWise.editSch","timeTableTeacherWise.delSch"), "icon"=>"mdi mdi-timelapse" ),
					"teacherAvailability" => array( "title"=>"Teacher availability", "url"=>URL::to('portal#/teacher-availability'), "role_perm"=>array("teacherAvailabilityPresence.showAvailability"), "icon"=>"mdi mdi-av-timer" ),
					"teacherPresence" => array( "title"=>"Teacher presence", "url"=>URL::to('portal#/teacher-presence'), "role_perm"=>array("teacherAvailabilityPresence.showPresence"), "icon"=>"mdi mdi-av-timer" )
				)
			),
			"myTimeTable" =>array( "title"=>"My Timetable", "icon"=>"mdi mdi-timelapse", "icon_img"=>"008-organisation.svg", "url"=> URL::to('portal#/institution-timetable'), "role_perm"=>array("classSch.list") ),
			"accounting" => array( "title"=>"Fees", "icon"=>"fa fa-inr", "icon_img"=>"007-bill.svg" ),
			"examination" => array("title"=>"Exams",'icon'=>"mdi mdi-clipboard-check","icon_img"=>"006-test.svg",
				"children"=>array(
					"gradelevels"=>array("title"=>"Grade view","urle levels","url"=>URL::to('portal#/exams/gradeLevels'),"role_perm"=>array("gradeLevels.list","gradeLevels.addLevel","gradeLevels.editGrade","gradeLevels.delGradeLevel"),"icon"=>"mdi mdi-arrange-send-backward"),
					"examslist"=>array("title"=>"Exam Schedule","url"=>URL::to('portal#/exams/examsList'),"role_perm"=>array("examsList.list","examsList.addExam","examsList.editExam","examsList.delExam","examsList.examDetailsNot","examsList.showMarks","examsList.controlMarksExam"),"icon"=>"mdi mdi-playlist-check" ),
					"onlineexams"=>array("title"=>"Online exams","url"=>URL::to('portal#/exams/onlineExams'),"icon"=>"mdi mdi-account-network","role_perm"=>array("onlineExams.list","onlineExams.addExam","onlineExams.editExam","onlineExams.delExam","onlineExams.takeExam","onlineExams.showMarks","onlineExams.QuestionsArch") ),
					"school_terms"=>array("title"=>"School terms","url"=>URL::to('portal#/exams/school-terms'),"icon"=>"mdi mdi-gamepad","role_perm"=>array("schoolTerms.list","schoolTerms.add","schoolTerms.edit","schoolTerms.del")),
					"report_card" => array( "title"=>"Report card", "url"=>URL::to('portal#/exams/marksheets'),"icon"=>"fa fa-table", "role_perm"=>array("genMarksheet.list", "genMarksheet.view") ),
				)
			),
			"Transportation"=>array(
				"title" => "Transport",
				"icon" => "mdi mdi-bus",
				"icon_img" => "011-bus.svg",
				"children" => [
					"busTracker" => array( "title"=>"Track Bus", "url"=>URL::to("portal#/bus_tracker"), "icon"=>"fa fa-map-marker", "role_perm"=>array("students.TrackBus") )
				]
			),
			"HRManagment" => array(
				"title" => "HR", "icon" => "mdi mdi-account-card-details", "icon_img" => "009-hr.svg",
				"children" => [
					"roles" => array( "title"=>"Manage Roles", "url"=>URL::to('portal#/hr/roles'), "icon"=>"mdi mdi-key-plus", "role_perm"=>array("roles.list") ),
					"employees" => array( "title"=>"Employee Management", "url"=>URL::to('portal#/hr/employees'), "icon"=>"mdi mdi-account-multiple-plus", "role_perm"=>array("departments.list", "designations.list", "branchs.list", "employees.list", "warnings.list", "terminations.list", "promotions.list") ),
					"attendance" => array( "title"=>"Attendance", "url"=>URL::to('portal#/hr/attendance'), "icon"=>"mdi mdi-calendar-clock", "role_perm"=>array("workshifts.list", "attendances_reports.daily", "attendances_reports.monthly", "attendances_reports.myReport", "attendances_reports.summary", "attendances.list") )
				]
			),
			"gallery" =>array(
				"title"=>"Gallery", "icon"=>"mdi mdi-folder-multiple-image", "icon_img"=>"017-gallery.svg", "url"=> URL::to('portal#/media'),
				"role_perm"=>array("mediaCenter.View","mediaCenter.addAlbum","mediaCenter.editAlbum","mediaCenter.delAlbum","mediaCenter.addMedia","mediaCenter.editMedia","mediaCenter.delMedia")
			),
			"reports"=>array("title"=>"Reports","icon"=>"mdi mdi-chart-areaspline","icon_img"=>"012-report.svg",
				"children"=>array(
					"student_attReport"=>array( "title"=>"Student attendance", "icon"=>"fa fa-bar-chart", "role_perm"=>array("Attendance.attReport"), "url"=>URL::to('portal#/student-attendance/report') ),
				)
			),
			"administration"=>array("title"=>"Administration","icon"=>"mdi mdi-account-settings-variant","icon_img"=>"005-professions-and-jobs.svg",
				"children"=>array(
					"settings" => array("title"=>"School Settings","icon"=>"mdi mdi-settings-box","url"=>URL::to('portal#/administrative/settings'),"role_perm"=>array("adminTasks.globalSettings"))
				)
			),
		);

		// custom appear modules with different system roles -----------------------------------
			$paymentFeeArray = [];
			$additional_fees_options = [];
			if( $current_role == "parent" )
			{
				$additional_fees_options = [
					"viewAllFees"=>array( "title"=>"View All Fees", "url"=>URL::to('portal#/payments/all'), "icon"=>"fa fa-list", "role_perm"=>array("Invoices.list","Invoices.View","Invoices.addPayment","Invoices.editPayment","Invoices.delPayment","Invoices.collInvoice","Invoices.payRevert","Invoices.Export") ),
					"viewPaidFee"=>array( "title"=>"View Paid Fees", "url"=>URL::to('portal#/payments/paid'), "icon"=>"fa fa-check-circle", "role_perm"=>array("Invoices.list","Invoices.View","Invoices.addPayment","Invoices.editPayment","Invoices.delPayment","Invoices.collInvoice","Invoices.payRevert","Invoices.Export") ),
				];
			}
			$mainPaymentFeeArray = array();
			$paymentFeeArray = array_merge( $mainPaymentFeeArray, $additional_fees_options );
			$this->menuElement['accounting']["children"] = $paymentFeeArray;
			if( $current_role == "parent" )
			{
				unset( $this->menuElement['academic']['children']['academicyear'] );
				$this->menuElement['users']['title'] = "My Wards";
				$this->menuElement['users']['children']["students"]["title"] = "View Wards";
				
				unset( $this->menuElement['examination']['children']['onlineexams'] );
				$this->menuElement['examination']['children']['examslist']['title'] = "Exam Schedule and Marks";
				unset( $this->menuElement['academic']['children']['sections'] );
				unset( $this->menuElement['reports'] );
				unset( $this->menuElement['administration'] );
				unset($this->menuElement["examination"]["children"]["gradelevels"]);
				$this->menuElement['hostel'] = array();
			}
			
			if( $current_role == "admin" ) { unset($this->menuElement["myTimeTable"]); }
			else
			{
				unset($this->menuElement["classSchedule"]);
				$this->menuElement["myTimeTable"] = [ "title"=>"My Timetable", "icon"=>"mdi mdi-timelapse", "icon_img"=>"008-organisation.svg", "url"=> URL::to('portal#/institution-timetable'), "role_perm"=>array("classSch.list") ];
			}
			$this->menuElement["feedbacks"]["title"] = $current_role == "teacher" ? "My Feedback" : "Teacher Feedback";
			$this->menuElement["attendance"]["children"]["reportAttendance"]["title"] = $current_role == "parent" || $current_role == "student" ? "My Attendance Report" : "Attendance Report";
		// end custom sidebar ---------------------------------------------------------------

		if(!Cache::has('check_setting_table_5025489'))
		{
			$check = \Schema::hasTable('settings');
			Cache::put('check_setting_table_5025489', $check, $this->cache_duration);
		} else { $check = Cache::get('check_setting_table_5025489'); }
		if(!$check) { $this->redirect('install'); }

		if($this->settingsArray['allowTeachersMailSMS'] == "none" AND !Auth::guest() AND \Auth::user()->role == "teacher") { $this->menuElement["smsmail"] = array(); }
		$this->authUser = $this->getAuthUser();

		//Languages
		$defLang = $defLang_ = $this->settingsArray['languageDef'];
		if(isset($this->settingsArray['languageAllow']) AND $this->settingsArray['languageAllow'] == "1" AND isset($this->authUser->defLang) AND $this->authUser->defLang != 0){
			$defLang = $this->authUser->defLang;
		}

		//Theme
		$this->defTheme = $this->settingsArray['layoutColor'];
		if(isset($this->settingsArray['layoutColorUserChange']) AND $this->settingsArray['layoutColorUserChange'] == "1" AND isset($this->authUser->defTheme) AND $this->authUser->defTheme != ""){
			$this->defTheme = $this->authUser->defTheme;
		}

		//Permissions
		if( isset($this->authUser->role_perm) AND $this->authUser->role_perm != "" AND $this->authUser->role_perm != 0 ){
			$roles = \roles::where('def_for',$this->authUser->role)->select('role_permissions');

			if($roles->count() == 0){
				$this->perms = array();
			}else{
				$roles = $roles->first();
			}

			if($this->authUser->role == 'employee')
			{
				$roles = \roles::where('id', $this->authUser->role_perm)->select('role_permissions');
				$roles = $roles->first();
				if( $roles )
				{
					$this->perms = json_decode($roles->role_permissions,true);
				} else $this->perms = array();
			}
			elseif($this->authUser->role != 'admin') {
				$isClassTeacher = \sections::where('classTeacherId',$this->authUser->id)->select('id');
				if($isClassTeacher->count() > 0){
					$roles = \roles::where('role_title','Class Teacher')->select('role_permissions');
					$roles = $roles->first();
				}
				$this->perms = json_decode($roles->role_permissions,true);
			} else { $this->perms = json_decode($roles->role_permissions,true); }
		}

		if (!Cache::has('language_table')) {
			$language = languages::whereIn('id', [$defLang, 1])->get();
			if(count($language) == 0){
				$language = languages::whereIn('id',array($defLang_,1))->get();
			}
			Cache::put('language_table', $language, $this->cache_duration);
		} else {
			$language = Cache::get('language_table');
		}

		foreach ($language as $value) {
			if($value->id == 1){
				$this->language = json_decode($value->languagePhrases,true);
				$this->languageUniversal = "en";
			}else{
				$this->languageUniversal = $value->languageUniversal;
				$this->isRTL = $value->isRTL;
				$phrases = json_decode($value->languagePhrases,true);
				foreach ($phrases as $key => $value){
					$this->language[$key] = $value;
				}
			}
		}

		$this->weekdays = array("ethiopic"=>array(1=>'እሑድ',2=>'ሰኞ',3=>'ማክሰኞ',4=>'ረቡዕ',5=>'ሓሙስ',6=>'ዓርብ',7=>'ቅዳሜ'),
			"gregorian"=>array(1=>$this->language['Sunday'],2=>$this->language['Monday'],3=>$this->language['Tuesday'],4=>$this->language['Wednesday'],5=>$this->language['Thurusday'],6=>$this->language['Friday'],7=>$this->language['Saturday']),
			"islamic"=>array(1=>'Yawm as-sabt',2=>'Yawm al-ahad',3=>'Yawm al-ithnayn',4=>"Yawm ath-thulaathaa'",5=>"Yawm al-arbi'aa'",6=>"Yawm al-khamīs",7=>"Yawm al-jum'a"),
			"persian"=>array(1=>'Shambe',2=>'Yekshambe',3=>'Doshambe',4=>'Seshambe',5=>'Chæharshambe',6=>'Panjshambe',7=>"Jom'e"),
		);

		//Selected academicYear
		if (Session::has('selectAcYear')) { $this->selectAcYear = Session::get('selectAcYear'); }
		else
		{
			$currentAcademicYear = academic_year::where('isDefault','1')->first();
			$this->selectAcYear = $currentAcademicYear->id;
			Session::put('selectAcYear', $this->selectAcYear);
		}

		$this->baseURL = URL::to('/');
		if (strpos($this->baseURL, 'index.php') == false) { $this->baseURL .= "/"; }
	}

	public function can($perm)
	{
		if( is_array( $perm ) )
		{
			foreach ($perm as $key => $value) { if( in_array( $value, $this->perms ) ) { return true; } }
		} else { if ( in_array( $perm, $this->perms ) ) { return true; } }
		return false;
	}

	public function collectFees($id = ""){
		$feeTypeList = array();
		$updateAllocationArray = array();
		$updateGroupArray = array();
		$invoice_ids = array();

		$fee_allocation = $id == "" ? \fee_allocation::where('feeTypeNextTS','<',time())->where('feeTypeNextTS','!=',0) : \fee_allocation::where('id',$id);
		if( $fee_allocation->count() > 0 )
		{
			$fee_allocation = $fee_allocation->limit(1)->get()->toArray();
			foreach ($fee_allocation as $value)
			{
				$updateAllocationArray[$value['id']] = array();
				if(!isset($feeTypeList[$value['feeType']]))
				{
					$feeType = \fee_type::leftJoin('fee_group','fee_group.id','=','fee_type.feeGroup')->where('fee_type.id',$value['feeType']);
					if($feeType->count() > 0)
					{
						$feeTypeList[$value['feeType']] = $feeType->select('fee_type.id','fee_type.fineAmount','fee_type.feeTitle','fee_type.feeCode','fee_type.feeDescription','fee_type.feeGroup','fee_type.feeAmount','fee_type.feeSchDetails','fee_group.invoice_prefix as invoice_prefix','fee_group.invoice_count as invoice_count')->first()->toArray();
						$feeTypeList[$value['feeType']]['feeSchDetails'] = json_decode($feeTypeList[$value['feeType']]['feeSchDetails'],true);

						$updateGroupArray[$feeTypeList[$value['feeType']]['id']] = array();
						$updateGroupArray[$feeTypeList[$value['feeType']]['id']]['group'] = $feeTypeList[$value['feeType']]['feeGroup'];
						$updateGroupArray[$feeTypeList[$value['feeType']]['id']]['count'] = $feeTypeList[$value['feeType']]['invoice_count'];
					}
				}

				if( !isset(	$feeTypeList[$value['feeType']] ) ) { $updateAllocationArray[$value['id']]['feeTypeNextTS'] = 0; }
				else
				{
					$paymentUsers = $this->getPaymentUsers($value['feeFor'],$value['feeForInfo']);
					$paymentDate = time();
					$compareTimes = array();
					reset($feeTypeList[$value['feeType']]['feeSchDetails']);
					foreach ($feeTypeList[$value['feeType']]['feeSchDetails'] as $key_ => $value_)
					{
						if($id != "")
						{
							if( !isset($dueDate) )
							{
								$paymentDate = time();
								$dueDate = time();
							} else { if($value_['date'] > time()) { $compareTimes[] = $value_['date']; } }
						}
						else
						{

							if($value_['date'] >= time()) { $compareTimes[] = $value_['date']; }
							if($value['feeTypeNextTS'] == $value_['date'])
							{
								$paymentDate = $value_['date'];
								$dueDate = $value_['due'];
							}
						}

					}

					if(count($compareTimes) > 0) { $updateAllocationArray[$value['id']]['feeTypeNextTS'] = min($compareTimes); }
					else { $updateAllocationArray[$value['id']]['feeTypeNextTS'] = 0; }

					$paymentRows = array();
					$paymentRows[] = array("title"=>$feeTypeList[$value['feeType']]['feeTitle'],"amount"=>$feeTypeList[$value['feeType']]['feeAmount']);

					foreach ($paymentUsers as $value_)
					{

						$updateGroupArray[$value['feeType']]['count'] ++ ;
						$payments = new \payments();
						$payments->paymentTitle = $feeTypeList[$value['feeType']]['invoice_prefix'].$updateGroupArray[$value['feeType']]['count'];
						$payments->paymentDescription = $feeTypeList[$value['feeType']]['feeTitle'];
						$payments->paymentStudent = $value_['id'];
						$payments->paymentRows = json_encode($paymentRows);
						$payments->paymentAmount = $feeTypeList[$value['feeType']]['feeAmount'];
						$payments->paymentDiscounted = $feeTypeList[$value['feeType']]['feeAmount'];
						$payments->fine_amount = $feeTypeList[$value['feeType']]['fineAmount'];
						$payments->paymentStatus = "0";
						$payments->paymentDate = $paymentDate;
						$payments->dueDate = $dueDate;
						$payments->paymentUniqid = uniqid();
						$payments->save();

						$invoice_ids[] = $payments->id;
					}

				}
			}

		}

		if(count($updateAllocationArray) > 0){
			foreach ($updateAllocationArray as $key => $value){
				\fee_allocation::where('id',$key)->update($value);
			}
		}

		if(count($updateGroupArray) > 0){
			foreach ($updateGroupArray as $key => $value){
				\fee_group::where('id',$value['group'])->update( array( 'invoice_count' => $value['count']) );
			}
		}

		if(count($invoice_ids) > 0){
			$this->calculate_discounts($invoice_ids);
		}

	}

	function calculate_discounts($invoices_list)
	{
		$available_discount = \fee_discount::where('discount_status','1')->get();
		$section_enabeld = $this->settingsArray['enableSections'];
		$userIds = array();

		$invoices = new \payments();
		$invoices = is_array($invoices_list) ? $invoices = $invoices->whereIn('id',$invoices_list) : $invoices->where('id',$invoices_list);
		$invoices = $invoices->get();
		foreach( $invoices as $key => $invoice) { $userIds[] = $invoice->paymentStudent; }

		//get users list
		$users = array();
		$users_list = \User::whereIn('id',$userIds)->select('studentClass','studentSection','id')->get()->toArray();
		foreach ($users_list as $key => $value) { $users[ $value['id'] ] = $value; }

		reset($invoices);
		foreach($invoices as $key => $invoice)
		{
			reset($available_discount);
			$can_use_list = array();
			foreach ($available_discount as $key => $discount)
			{
				if($section_enabeld == true && isset($users[ $invoice->paymentStudent ]) )
				{
					if (strpos($discount->discount_assignment, 'cl-'.$users[ $invoice->paymentStudent ]['studentClass']."-".$users[ $invoice->paymentStudent ]['studentSection']) !== false)
					{
						$can_use_list[ $discount->id ] = $discount;
					}
				}
				if($section_enabeld == false && isset($users[ $invoice->paymentStudent ]))
				{
					if (strpos($discount->discount_assignment, 'cl-'.$users[ $invoice->paymentStudent ]['studentClass']) !== false)
					{
						$can_use_list[ $discount->id ] = $discount;
					}
				}

				if(strpos($discount->discount_assignment, 'inv-'.$invoice->id) !== false) { $can_use_list[ $discount->id ] = $discount; }
				if(strpos($discount->discount_assignment, 'std-'.$invoice->paymentStudent) !== false) { $can_use_list[ $discount->id ] = $discount; }
			}

			$apply_discount = array();

			//Get max one
			if(count($can_use_list) >= 1)
			{
				$fee_discount = array();
				foreach( $can_use_list as $key => $can_use_one )
				{
					$tmp_discount_calculation = $this->calculate_discount_value($can_use_one, $invoice->paymentAmount);
					if(count($fee_discount) == 0) { $fee_discount = $tmp_discount_calculation; }
					else
					{
						if($tmp_discount_calculation['discount_value'] > $fee_discount['discount_value']) { $fee_discount = $tmp_discount_calculation; }
					}
				}

			}

			if(count($can_use_list) == 0)
			{
				\payments::where('id',$invoice->id)->update( array('paymentDiscount'=>0,'paymentDiscounted'=>$invoice->paymentAmount,'discount_id'=>0) );
			}
			else
			{
				if($fee_discount['discount_value'] > $invoice->paymentDiscount)
				{
					$paymentDiscounted = $invoice->paymentAmount - $fee_discount['discount_value'];
					$update_invoice = array('paymentDiscount'=>$fee_discount['discount_value'],'paymentDiscounted'=>$paymentDiscounted,'discount_id'=>$fee_discount['discount_id']);
					if($paymentDiscounted == 0) { $update_invoice['paymentStatus'] = 1; }
					\payments::where('id',$invoice->id)->update( $update_invoice );
				}
			}

		}
	}

	function calculate_discount_value($fee_discount,$original)
	{
		$to_return = array();
		if($fee_discount['discount_type'] == "percentage")
		{
			$to_return['discount_id'] = $fee_discount['id'];
			$to_return['discount_value'] = ($original * $fee_discount['discount_value']) / 100;
			$to_return['after_discount'] = $original - ($original * $fee_discount['discount_value']) / 100;
		}

		if($fee_discount['discount_type'] == "fixed")
		{
			$to_return['discount_value'] = 0;
			if($fee_discount['discount_value'] >= $original) { $to_return['discount_value'] = $original; }
			else { $to_return['discount_value'] = $fee_discount['discount_value']; }
			$to_return['after_discount'] = $original - $to_return['discount_value'];
			$to_return['discount_id'] = $fee_discount['id'];

		}

		return $to_return;
	}

	public function dueInvoicesNotif(){
		$dueInvoices = \payments::where('dueDate','<', time() )->where('dueNotified','0')->where('paymentStatus','!=','1');
		if($dueInvoices->count() > 0)
		{
			if($this->settingsArray['dueInvoicesNotif'] == "mail" || $this->settingsArray['dueInvoicesNotif'] == "mailsms") { $mail = true; }
			if($this->settingsArray['dueInvoicesNotif'] == "sms" || $this->settingsArray['dueInvoicesNotif'] == "mailsms") { $sms = true; }
			if($this->settingsArray['dueInvoicesNotifTo'] == "student" || $this->settingsArray['dueInvoicesNotifTo'] == "both") { $students = true; }
			if($this->settingsArray['dueInvoicesNotifTo'] == "parent" || $this->settingsArray['dueInvoicesNotifTo'] == "both") { $parents = true; }

			if(isset($mail) || isset($sms))
			{
				$mailsms_template = \mailsms_templates::where('templateTitle','Due Invoice');
				$usersIds = array();
				$usersIdsFlat = array();
				$studentsArray = array();
				$parentsArray = array();
				$updateInvoices = array();

				//Get Due Invoices
				$dueInvoices = $dueInvoices->limit(5)->get()->toArray();
				foreach ($dueInvoices as $value)
				{
					$usersIds[ $value['id'] ] = $value['paymentStudent'];
					$usersIdsFlat[] = $value['paymentStudent'];
					$updateInvoices[] = $value['id'];
				}

				if( $mailsms_template->count() > 0 )
				{
					$mailsms_template = $mailsms_template->first()->toArray();
					//Get users information
					$usersList = \User::whereIn('id',$usersIdsFlat);
					if(isset($parents))
					{
						$usersList = $usersList->orWhere(function ($query) use ($usersIdsFlat) {
							foreach ($usersIdsFlat as $value) { $query = $query->orWhere('parentOf', 'like', '%"'.$value.'"%'); }
						});
					}

					$usersList = $usersList->select('id','role','fullName','email','mobileNo','comVia','parentOf')->get()->toArray();
					foreach ($usersList as $value)
					{
						if($value['role'] == "parent")
						{
							$value['parentOf'] = json_decode($value['parentOf'],true);
							if(is_array($value['parentOf']))
							{
								foreach ($value['parentOf'] as $value2)
								{
									if(!isset($parentsArray[ $value2['id'] ])) { $parentsArray[ $value2['id'] ] = array(); }
									$parentsArray[ $value2['id'] ][] = array("id"=>$value['id'],"role"=>$value['role'],"email"=>$value['email'],"mobileNo"=>$value['mobileNo'],"comVia"=>$value['comVia'],"fullName"=>$value['fullName']);
								}
							}
						} else { $studentsArray[ $value['id'] ] = $value; }
					}
					//Start the sending operation
					reset($dueInvoices);
					$MailSmsHandler = new \MailSmsHandler();
					foreach ($dueInvoices as $value)
					{
						if(!isset($studentsArray[$value['paymentStudent']])) { continue; }
						if(isset($students))
						{
							if(isset($mail) AND strpos($studentsArray[$value['paymentStudent']]['comVia'], 'mail') !== false)
							{
								$temp_mailsms_template = $mailsms_template;
								$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
								$replaceArray = array($studentsArray[$value['paymentStudent']]['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
								$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateMail']);
								$MailSmsHandler->mail($studentsArray[$value['paymentStudent']]['email'],$this->language['Invoices'],$sendTemplate,"",$this->settingsArray);
							}

							if(isset($sms) AND strpos($studentsArray[$value['paymentStudent']]['comVia'], 'sms') !== false AND strlen($studentsArray[$value['paymentStudent']]['mobileNo']) > 5)
							{
								$temp_mailsms_template = $mailsms_template;
								$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
								$replaceArray = array($studentsArray[$value['paymentStudent']]['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
								$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateSMS']);
								$MailSmsHandler->sms($studentsArray[$value['paymentStudent']]['mobileNo'],$sendTemplate,$this->settingsArray);
							}

						}
						if(isset($parents) AND isset($parentsArray[$value['paymentStudent']]) )
						{
							foreach ($parentsArray[$value['paymentStudent']] as $parent)
							{
								if(isset($mail) AND strpos($parent['comVia'], 'mail') !== false)
								{
									$temp_mailsms_template = $mailsms_template;
									$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
									$replaceArray = array($parent['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
									$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateMail']);
									$MailSmsHandler->mail($parent['email'],$this->language['Invoices'],$sendTemplate,"",$this->settingsArray);
								}

								if(isset($sms) AND strpos($parent['comVia'], 'sms') !== false AND strlen($parent['mobileNo']) > 5)
								{
									$temp_mailsms_template = $mailsms_template;
									$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
									$replaceArray = array($parent['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
									$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateSMS']);
									$MailSmsHandler->sms($parent['mobileNo'],$sendTemplate,$this->settingsArray);
								}
							}

						}
					}
				}
				if(count($updateInvoices) > 0) { \payments::whereIn('id', $updateInvoices )->update( array('dueNotified'=>'1') ); }
			}
		}

	}

	public function real_notifications($data)
	{
		//Send to firebase
		$Firebase = new \Firebase();
		if(isset($this->settingsArray['firebase_apikey']) AND $this->settingsArray['firebase_apikey'] != "") {
			$Firebase->api_key($this->settingsArray['firebase_apikey']);
		} else { return; }

		$Firebase->title = $data['data_title'] ;
		$Firebase->body = strip_tags($data['data_message']);

		$addData = array();
		if(isset($data['payload_where'])) { $addData['where'] = $data['payload_where']; }
		if(isset($data['payload_id'])) { $addData['id'] = $data['payload_id']; }
		$addData['sound'] = 'default';
		if(count($addData) > 0) { $Firebase->data = $addData; }

		$info = $Firebase->send($data['firebase_token']);
		\Log::info($info);
	}

	public function validate_upload($file){
		$allowed_mime_type = array("text/plain", "text/html", "text/css", "text/javascript", "text/markdown","text/pdf","text/richtext","text/calendar",
			"image/gif", "image/png", "image/jpeg", "image/bmp", "image/webp", "image/vnd.microsoft.icon","image/svg+xml","image/psd","image/pjpeg","image/x-icon",
			"audio/midi", "audio/mpeg", "audio/webm", "audio/ogg", "audio/wav","audio/mpeg3", "audio/x-mpeg-3", "audio/m4a","audio/x-wav","audio/3gpp","audio/3gpp2","audio/x-realaudio",
			"video/webm", "video/ogg","video/mpeg", "video/x-mpeg","video/mp4","video/x-m4v","video/quicktime","video/x-ms-asf","video/x-ms-wmv","application/x-troff-msvideo", "video/avi", "video/msvideo", "video/x-msvideo","video/3gpp","video/3gpp2","video/x-flv","video/divx","video/x-matroska",
			"application/pkcs12", "application/vnd.mspowerpoint","application/msword", "application/xhtml+xml", "application/xml", "application/pdf","application/x-pdf","application/vnd.openxmlformats-officedocument.wordprocessingml.document",'application/rtf',"application/mspowerpoint", "application/powerpoint", "application/vnd.ms-powerpoint", "application/x-mspowerpoint","application/vnd.openxmlformats-officedocument.presentationml.presentation","application/mspowerpoint","application/vnd.ms-powerpoint","application/vnd.openxmlformats-officedocument.presentationml.slideshow","application/vnd.oasis.opendocument.text","application/excel","application/vnd.ms-excel","application/x-excel","application/x-msexcel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.ms-write","application/vnd.ms-access","application/vnd.ms-project",
			"application/x-rar-compressed","application/zip", "application/x-zip-compressed", "multipart/x-zip","application/x-7z-compressed","application/rar","application/x-7z-compressed",
		);
		$banned_extensions = array("php","php3","php4","php5","cgi","sh","bash","bin","pl","htaccess","htpasswd","ksh");
		$uploaded_mime_type = $file->getMimeType();
		$uploaded_extension = $file->getClientOriginalExtension();

		if( in_array( $uploaded_extension , $banned_extensions)  ) { return false; }
		if( in_array( $uploaded_mime_type , $allowed_mime_type)  ) { return true; }

		return false;
	}

	public function redirect($to)
	{
		if($to == "install") { $toTitle = "Installation"; }
		if($to == "upgrade") { $toTitle = "Upgrade"; }
		echo "<html><head>
			<title>$toTitle</title>
			<meta http-equiv='refresh' content='2; URL=".\URL::to('/'.$to)."'>
			<meta name='keywords' content='automatic redirection'>
		</head>
		<body> If your browser doesn't automatically go to the $toTitle within a few seconds,
		you may want to go to <a href='".\URL::to('/'.$to)."'>the destination</a> manually.
		</body></html>";
		die();
	}

	public function getPaymentUsers($feeFor,$feeForInfo)
	{
		$students = array();
		if($feeFor == "all" AND isset($this->selectAcYear))
		{
			$classesList = array();
			$classes = classes::where('classAcademicYear',$this->selectAcYear)->get()->toArray();
			foreach ($classes as $value) { $classesList[] = $value['id']; }
			$students = array();
			if(count($classesList) > 0) { $students = User::where('role','student')->whereIn('studentClass',$classesList)->where('activated','1')->select('id')->get()->toArray(); }
		}

		if($feeFor == "class")
		{
			$feeForInfo = json_decode($feeForInfo,true);
			if(is_array($feeForInfo) AND isset($feeForInfo['class']))
			{
				$students = User::where('role','student')->where('activated','1')->where('studentClass',$feeForInfo['class']);
				if( isset($feeForInfo['section']) AND is_array($feeForInfo['section']) ) { $students = $students->whereIn('studentSection',$feeForInfo['section']); }
				if(isset($feeForInfo['student_type']) && $feeForInfo['student_type'] != 0) { $students = $students->where('studentType', $feeForInfo['student_type']); }
				$students = $students->select('id')->get()->toArray();
			}
		}

		if($feeFor == "student")
		{
			$feeForInfo = json_decode($feeForInfo,true);
			if(is_array($feeForInfo) && count($feeForInfo) > 0)
			{
				$ids = array();
				foreach ($feeForInfo as $value) { if(isset($value['id'])) { $ids[] = $value['id']; } }
				if(count($ids) > 0) { $students = User::where('role','student')->where('activated','1')->whereIn('id',$ids)->select('id')->get()->toArray(); }
			}
		}

		return $students;
	}

	public function hasThePerm($perm)
	{
		if(\Auth::user() AND \Auth::user()->role == "admin" AND \Auth::user()->customPermissionsType == "custom" AND is_array(\Auth::user()->customPermissionsAsJson()) AND !in_array($perm,\Auth::user()->customPermissionsAsJson())) { return false; }
		else { return true; }
	}

	public function getAuthUser()
	{
		if(app('request')->header('Authorization') != "" || \Input::has('token'))
		{
			try { return \JWTAuth::parseToken()->authenticate(); }
			catch(exception $e){  }
		} else { return \Auth::guard('web')->user(); }
	}

	public function isLoggedInUser()
	{
		//
	}

	public function customPermissionsType()
	{
		if($this->customPermissionsDecoded == "") { $this->customPermissionsDecoded = json_decode($this->customPermissions); }
		return $this->customPermissionsDecoded;
	}

	public function mobNotifyUser($userType,$userIds,$notifData)
	{
		$mobNotifications = new \mob_notifications();
		if($userType == "users")
		{
			$mobNotifications->notifTo = "users";
			if(!is_array($userIds)) { $userIds = array($userIds); }
			$userIdsList = array();
			foreach ($userIds as $value) { $userIdsList[] = array('id'=>$value); }
			$mobNotifications->notifToIds = json_encode($userIdsList);
		}
		elseif( $userType == "class" ) { $mobNotifications->notifTo = "students"; $mobNotifications->notifToIds = $userIds; }
		elseif($userType == "role") { $mobNotifications->notifTo = $userIds; $mobNotifications->notifToIds = ""; }

		$mobNotifications->notifData = htmlspecialchars($notifData,ENT_QUOTES);
		$mobNotifications->notifDate = time();
		$mobNotifications->notifSender = "Automated";
		$mobNotifications->save();

		//Get users List
		$token_list = array();
		if($userType == "users")
		{
			if(!is_array($userIds)) { $userIds = array($userIds); }
			$userIdsList = array();
			foreach ($userIds as $value) { $userIdsList[] = array('id'=>$value); }
			$token_list = \User::whereIn('id',$userIdsList)->select('firebase_token')->get();
		}
		elseif($userType == "class") { $token_list = \User::whereIn('role','student')->select('firebase_token')->get(); }
		elseif($userType == "role") { $token_list = \User::whereIn('role',$userIds)->select('firebase_token')->get(); }

		$notif_data = array('data_title'=>'','data_message'=>'','notifUrl'=>'','payload_where'=>'','payload_id'=>'','firebase_token'=>array());
		foreach ($token_list as $value) { $notif_data['firebase_token'][] = $value['firebase_token']; }
		$this->send_push_notification($notif_data);
	}

	public function save_notifications_toDB($target_tokens, $user_ids, $message, $title="", $payload_location = "", $payload_id = "")
	{
		$notificationMobHistory = new NotificationMobHistory;
		$notificationMobHistory->saveNotificationsToDBModel( $target_tokens, $user_ids, $message, $title, $payload_location, $payload_id );
	}

	public function send_push_notification($target_tokens, $user_ids, $message, $title="", $payload_location = "", $payload_id = "")
	{
		$notificationMobHistory = new NotificationMobHistory;
		$notificationMobHistory->saveNotificationsToDBModel( $target_tokens, $user_ids, $message, $title, $payload_location, $payload_id );
		$notificationMobHistory->sendPushNotification( $target_tokens, $user_ids, $message, $title, $payload_location, $payload_id, $this->settingsArray );
	}

	public static function globalXssClean()
	{
		$sanitized = static::arrayStripTags(Input::get());
		Input::merge($sanitized);
	}

	public static function arrayStripTags($array)
	{
	    $result = array();
		foreach ($array as $key => $value)
		{
	        $key = strip_tags($key);
	        if (is_array($value)) { $result[$key] = static::arrayStripTags($value); }
			else { $result[$key] = trim(strip_tags($value)); }
	    }
	    return $result;
	}

	public function viewop($layout,$view,&$data,$div="")
	{
		$data['content'] = View::make($view, $data);
		return view($layout, $data);
	}

	function sanitize_output($buffer)
	{
		$search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s','/\s\s+/');
		$replace = array('>','<',' ',' ');
		$buffer = preg_replace($search, $replace, $buffer);
		return $buffer;
	}

	public static function breadcrumb($breadcrumb){
		echo "<ol class='breadcrumb'> <li><a class='aj' href='".URL::to('/dashboard')."'><i class='fa fa-dashboard'></i> Home</a></li>";
		$i = 0;
		foreach ($breadcrumb as $key => $value)
		{
			$i++;
			if($i == count($breadcrumb)) { echo "<li class='active'>".$key."</li>"; }
			else { echo "<li class='bcItem'><a class='aj' href='$value' title='$key'>$key</a></li>"; }
		}
		echo "</ol>";
	}

	public function truncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = false) {
		if( $considerHtml )
		{
			if (strlen ( preg_replace ( '/<.*?>/', '', $text ) ) <= $length) { return $text; }
			preg_match_all ( '/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER );
			$total_length = strlen ( $ending );
			$open_tags = array ( );
			$truncate = '';
			foreach ( $lines as $line_matchings )
			{
				if(! empty ( $line_matchings [1] ))
				{
					if (preg_match ( '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings [1] )) { /* do nothing */ }
					else if (preg_match ( '/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings [1], $tag_matchings )) { $pos = array_search ( $tag_matchings [1], $open_tags ); if ($pos !== false) { unset ( $open_tags [$pos] ); } }
					else if (preg_match ( '/^<\s*([^\s>!]+).*?>$/s', $line_matchings [1], $tag_matchings )) { array_unshift ( $open_tags, strtolower ( $tag_matchings [1] ) ); }
					$truncate .= $line_matchings [1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen ( preg_replace ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings [2] ) );
				if ($total_length + $content_length > $length)
				{
					$left = $length - $total_length;
					$entities_length = 0;
					if (preg_match_all ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings [2], $entities, PREG_OFFSET_CAPTURE ))
					{
						foreach ( $entities [0] as $entity )
						{
							if ($entity [1] + 1 - $entities_length <= $left) { $left --; $entities_length += strlen ( $entity [0] ); }
							else { /* no more characters left */ break; }
						}
					}
					$truncate .= substr ( $line_matchings [2], 0, $left + $entities_length );
					break;
				} else { $truncate .= $line_matchings [2]; $total_length += $content_length; }
				if ($total_length >= $length) { break; }
			}
		}
		else
		{
			if (strlen ( $text ) <= $length) { return $text; }
			else { $truncate = substr ( $text, 0, $length - strlen ( $ending ) ); }
		}
		if (! $exact)
		{
			$spacepos = strrpos ( $truncate, ' ' );
			if (isset ( $spacepos )) { $truncate = substr ( $truncate, 0, $spacepos ); }
		}
		$truncate .= $ending;
		if ($considerHtml) { foreach ( $open_tags as $tag ) { $truncate .= '</' . $tag . '>'; } }
		return $truncate;
	}

	public function apiOutput($success,$title=null,$messages = null,$data=null)
	{
		$returnArray = array("status"=>"");
		if($title != null) { $returnArray['title'] = $title; }
		if($messages != null) { $returnArray['message'] = $messages; }
		if($data != null)
		{
			if( is_array( $data ) )
			{
				if( array_key_exists( "timer", $data ) ) { $returnArray['timer'] = $data["timer"]; unset( $data["timer"] ); }
			}
			if( is_array( $data ) && count( $data ) ) $returnArray['data'] = $data;
			else $returnArray['data'] = $data;
		}
		$returnArray['status'] = $success ? 'success' : 'failed';
		return $returnArray;
	}

	public function apiOutput2($status,$title=null,$messages = null,$data=null)
	{
		$returnArray = array("status"=>"");
		if($title != null) { $returnArray['title'] = $title; }
		if($messages != null) { $returnArray['message'] = $messages; }
		if($data != null) { $returnArray['data'] = $data; }
		$returnArray['status'] = $status;
		return $returnArray;
	}

	public function get_default_perm($role)
	{
		$roles = \roles::where('def_for',$role)->select('id');
		if($roles->count() == 0) { return 0; }
		$roles = $roles->first();
		return $roles->id;
	}

	public function date_to_unix($time,$format="")
	{
		if(!isset($this->settingsArray['timezone'])) { $this->settingsArray['timezone'] = "Europe/London"; }
		if($format == "") { $format = $this->settingsArray['dateformat']; }
		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) )
		{
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","i",$format);
			return $this->greg_to_unix($time,$format);
		}
		else
		{
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","m",$format);
			return $this->intlToTimestamp($time,$format);
		}
	}

	public function unix_to_date($timestamp,$format="")
	{
		if($format == "") { $format = $this->settingsArray['dateformat']; }
		if(!isset($this->settingsArray['timezone'])) { $this->settingsArray['timezone'] = "Europe/London"; }

		//Adjust date offset
		if(isset($this->settingsArray['calendarOffset']) AND $this->settingsArray['calendarOffset'] != "" AND $this->settingsArray['calendarOffset'] != "0" )
		{
			$timestamp += ( intval($this->settingsArray['calendarOffset']) * 86400 );
		}

		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) )
		{
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","i",$format);
			return $this->unix_to_greg($timestamp,$format);
		}
		else
		{
			//Intl Date manipulation
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","m",$format);
			return $this->timestampToIntl($timestamp,$format);
		}
	}

	public function date_ranges($start,$end="")
	{
		if(!isset($this->settingsArray['timezone'])) { $this->settingsArray['timezone'] = "Europe/London"; }
		if(isset($this->settingsArray['calendarOffset']) AND $this->settingsArray['calendarOffset'] != "" AND $this->settingsArray['calendarOffset'] != "0" )
		{
			$start += ( intval($this->settingsArray['calendarOffset']) * 86400 );
			$end += ( intval($this->settingsArray['calendarOffset']) * 86400 );
		}

		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) )
		{
			return $this->gregTsDow($start,$end);
		} else { return $this->intlTsDow($start,$end); }
	}

	function todayDow()
	{
		$time = time();
		if(isset($this->settingsArray['calendarOffset']) AND $this->settingsArray['calendarOffset'] != "" AND $this->settingsArray['calendarOffset'] != "0" )
		{
			$time += ( intval($this->settingsArray['calendarOffset']) * 86400 );
		}

		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) )
		{
			return $this->unix_to_date($time,'w') + 1;
		} else { return $this->unix_to_date($time,'e') + 1 ; }
	}

	public function greg_to_unix($time,$format)
	{
		$dd = DateTime::createFromFormat($format, $time, new DateTimeZone($this->settingsArray['timezone']));
		if (strpos($format, 'h:i') == false) { $dd->setTime(0,0,0); }
		return $dd->getTimestamp();
	}

	public function unix_to_greg($timestamp, $format)
	{
		if($timestamp == "") { $timestamp = time(); }
		$date = new DateTime("@".$timestamp);
		$date->setTimezone(new DateTimeZone($this->settingsArray['timezone']));
		return $date->format($format);
	}

	public function intlToTimestamp($date,$format="")
	{
		if($format == "") { $format = $this->settingsArray['dateformat']; }

		$format = str_replace('m','MM',$format);
		$format = str_replace('d','dd',$format);
		$format = str_replace('Y','yyyy',$format);
		if($this->settingsArray['gcalendar'] == "gregorian") { $intl_locale = 'en_Us'; $intl_calendar = \IntlDateFormatter::GREGORIAN; }
		else { $intl_locale = 'en_Us@calendar='.$this->settingsArray['gcalendar']; $intl_calendar = \IntlDateFormatter::TRADITIONAL; }
		$intlDateFormatter = new \IntlDateFormatter( $intl_locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, $this->settingsArray['timezone'], $intl_calendar, $format );
		$intlDateFormatter->setLenient(false);
		return $intlDateFormatter->parse($date);
	}

	public function timestampToIntl($timestamp,$format="")
	{
		if($format == "") { $format = $this->settingsArray['dateformat']; }
		$format = str_replace('m','MM',$format);
		$format = str_replace('d','dd',$format);
		$format = str_replace('Y','yyyy',$format);

		if($this->settingsArray['gcalendar'] == "gregorian") { $intl_locale = 'en_Us'; $intl_calendar = \IntlDateFormatter::GREGORIAN; }
		else { $intl_locale = 'en_Us@calendar='.$this->settingsArray['gcalendar']; $intl_calendar = \IntlDateFormatter::TRADITIONAL; }

		$DateTime = new \DateTime("@".$timestamp);
		$IntlDateFormatter = new \IntlDateFormatter( $intl_locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, $this->settingsArray['timezone'], $intl_calendar, $format );
		return $IntlDateFormatter->format($timestamp);
	}

	public function gregTsDow($start,$end="")
	{
		$return = array();
		$format = $this->settingsArray['dateformat'];
		if(!isset($this->settingsArray['timezone'])) { $this->settingsArray['timezone'] = "Europe/London"; }

		if($end == "") {
			$dd = DateTime::createFromFormat($format, $start, new DateTimeZone($this->settingsArray['timezone']));
			$return[] = array("dow"=>$dd->format('N'),"date"=>$start,"timestamp"=>$dd->getTimestamp() );
		}
		else
		{
			$tmpDate = DateTime::createFromFormat($format, $start, new DateTimeZone($this->settingsArray['timezone']));
			$tmpDate->setTime(0,0,0);
			$tmpEndDate = DateTime::createFromFormat($format, $end, new DateTimeZone($this->settingsArray['timezone']));
			$tmpEndDate->setTime(0,0,0);
			$outArray = array();
			do {
				$return[] = array("dow"=>$tmpDate->format('N'),"date"=>$tmpDate->format($format),"timestamp"=>$tmpDate->getTimestamp() );
			} while ($tmpDate->modify('+1 day') <= $tmpEndDate);
		}

		return $return;
	}

	public function intlTsDow($start,$end="")
	{
		$return = array();
		$format = $this->settingsArray['dateformat'];
		$format = str_replace('m','MM',$format);
		$format = str_replace('d','dd',$format);
		$format = str_replace('Y','yyyy',$format);
		if(!isset($this->settingsArray['timezone'])) { $this->settingsArray['timezone'] = "Europe/London"; }

		$intl_locale = 'en_Us@calendar='.$this->settingsArray['gcalendar'];
		$intl_calendar = \IntlDateFormatter::TRADITIONAL;
		$intlDateFormatter = new \IntlDateFormatter( $intl_locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, $this->settingsArray['timezone'], $intl_calendar, $format );
		$intlDateFormatter->setLenient(false);
		$timestamp = $intlDateFormatter->parse($start);
		$firstTime = true;

		if($end == "")
		{
			$DateTime = new \DateTime("@".$timestamp);
			$IntlDateFormatter = new \IntlDateFormatter( $intl_locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, $this->settingsArray['timezone'], $intl_calendar, "e" );
			$return[] = array("dow"=>$IntlDateFormatter->format($DateTime),"date"=>$start,"timestamp"=>$timestamp);
		}
		else
		{
			do {
				if(!isset($firstTime)) { $start = $this->timestampToIntl($timestamp); }
				else { $end = $this->intlToTimestamp($end); }

				unset($firstTime);
				$DateTime = new \DateTime("@".$timestamp);
				$IntlDateFormatter = new \IntlDateFormatter( $intl_locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, $this->settingsArray['timezone'], $intl_calendar, "e" );
				$return[] = array("dow"=>$IntlDateFormatter->format($DateTime),"date"=>$start,"timestamp"=>$timestamp);
				//Next timestamp
				$timestamp = $timestamp + 86400;
			} while($timestamp <= $end);
		}
		return $return;
	}
}