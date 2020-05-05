<?php
namespace App\Http\Controllers;

use App\Models2\ClassSchedule;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\Section;
use App\Models2\Subject;
use App\Models2\User;
use App\Models2\HR\WeeklyHoliday;
use Cache;
use Carbon\Carbon;

class ClassScheduleController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';
	var $temp_empty_timetable_parameter = '#';

	public function __construct(){
		if(app('request')->header('Authorization') != "" || \Input::has('token')){
			$this->middleware('jwt.auth');
		}else{
			$this->middleware('authApplication');
		}

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)){
			return \Redirect::to('/');
		}
	}

	public function listAll() {

		if(!$this->panelInit->can( array("classSch.list","classSch.addSch","classSch.editSch","classSch.delSch") )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to access Timetable");
		}

		$toReturn = array();
		$toReturn['classes'] = array();
		if($this->panelInit->settingsArray['enableSections'] == true){

			$toReturn['sections'] = array();
			if($this->data['users']->role == "student"){

				$classesIn = array();
				$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
				foreach ($classes as $value) {
					$toReturn['classes'][$value->id] = $value->className;
					$classesIn[] = $value->id;
				}

				$sections = \DB::table('sections')
							->select('sections.id as id',
							'sections.sectionName as sectionName',
							'sections.classId as classId')
							->where('id',$this->data['users']->studentSection)
							->get();

				foreach ($sections as $key => $section) {
					// $sections[$key]->teacherId = json_decode($sections[$key]->teacherId,true);
					if(isset($toReturn['classes'][$section->classId])){
						$toReturn['sections'][$toReturn['classes'][$section->classId]][] = $section;
					}
				}
			}elseif($this->data['users']->role == "parent"){

				$classesIn = array();
				$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)
					->whereIn('id', Main::getClassesIdsByParentId($this->data['users']->id))
				  ->get();

				foreach ($classes as $value) {
					$toReturn['classes'][$value->id] = $value->className;
					$classesIn[] = $value->id;
				}

				$parentOf = json_decode($this->data['users']->parentOf,true);
				if(!is_array($parentOf)){
					$parentOf = array();
				}
				$ids = array();
				foreach($parentOf as $value){
					$ids[] = $value['id'];
				}
				if(count($ids) > 0){
					$studentArray = \User::where('role','student')->whereIn('id',$ids)->get();
					foreach ($studentArray as $stOne) {
						$sectionsId[] = $stOne->studentSection;
					}

					if(count($sectionsId)){
						$sections = \DB::table('sections')
									->select('sections.id as id',
									'sections.sectionName as sectionName',
									'sections.classId as classId')
									->whereIn('id',$sectionsId)
									->get();

						foreach ($sections as $key => $section) {
							// $sections[$key]->teacherId = json_decode($sections[$key]->teacherId,true);
							if(isset($toReturn['classes'][$section->classId])){
								$toReturn['sections'][$toReturn['classes'][$section->classId]][] = $section;
							}
						}
					}

				}
			}elseif($this->data['users']->role == "teacher"){
				$teacher_id = $this->data['users']->id;
				$class_ids = Main::getClassesIdsByTeacherId($teacher_id);
				$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->whereIn('id', $class_ids)->get();

				$classesIn = array();
				foreach ($classes as $value) {
					$toReturn['classes'][$value->id] = $value->className;
					$classesIn[] = $value->id;
				}

				$toReturn['sections'] = array();
				if(count($classesIn) > 0){
					$sections = Section::whereIn('classId', $classesIn)->get();

					foreach ($sections as $key => $section) {
						$check_timetable = ClassSchedule::where('sectionId', $section->id)->where('teacherId', $this->data['users']->id)->count();

						if($check_timetable) {
							if(isset($toReturn['classes'][$section->classId])){
								$toReturn['sections'][$toReturn['classes'][$section->classId]][] = $section;
							}
						}
					}
				}
			}else{

				$classesIn = array();
				$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
				foreach ($classes as $value) {
					$toReturn['classes'][$value->id] = $value->className;
					$classesIn[] = $value->id;
				}

				$toReturn['sections'] = array();
				if(count($classesIn) > 0){
					$sections = \DB::table('sections')
								->select('sections.id as id',
								'sections.sectionName as sectionName',
								'sections.classId as classId')
								->whereIn('sections.classId',$classesIn)
								->get();

					foreach ($sections as $key => $section) {
						// $sections[$key]->teacherId = json_decode($sections[$key]->teacherId,true);
						if(isset($toReturn['classes'][$section->classId])){
							$toReturn['sections'][$toReturn['classes'][$section->classId]][] = $section;
						}
					}
				}
			}
		}else{
			if($this->data['users']->role == "student"){
				$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('id',$this->data['users']->studentClass)->get()->toArray();
			}elseif($this->data['users']->role == "teacher"){
				$teacher_id = $this->data['users']->id;
				$class_ids = Main::getClassesIdsByTeacherId($teacher_id);
				$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)
					->whereIn('id', $class_ids)
					->get()->toArray();
			}elseif($this->data['users']->role == "parent"){
				if($this->data['users']->parentOf != ""){
					$parentOf = json_decode($this->data['users']->parentOf,true);
					if(!is_array($parentOf)){
						$parentOf = array();
					}
					$ids = array();
					foreach($parentOf as $value){
						$ids[] = $value['id'];
					}
					if(count($ids) > 0){
						$studentArray = \User::where('role','student')->whereIn('id',$ids)->get();
						foreach ($studentArray as $stOne) {
							$classesIds[] = $stOne->studentClass;
						}
						$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->whereIn('id',$classesIds)->get()->toArray();
					}
				}
			}else{
				$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
			}
		}

		$toReturn['subject'] = \subject::get()->toArray();

		$toReturn['days'] = array(
				1=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][1],
				2=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][2],
				3=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][3],
				4=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][4],
				5=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][5],
				6=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][6],
				7=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][7]
			);

		$toReturn['teachers'] = \User::where('role','teacher')->where('activated','1')->select('id','fullName');
		if($this->data['panelInit']->settingsArray['teachersSort'] != ""){
			$toReturn['teachers'] = $toReturn['teachers']->orderByRaw($this->data['panelInit']->settingsArray['teachersSort']);
		}
		$toReturn['teachers'] = $toReturn['teachers']->get();

		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function listTeacherwise($day_number, $page = 1){

		if(!$this->panelInit->can( "timeTableTeacherWise.list" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to access Timetable");
		}

		$paginate = ClassSchedule::where('dayOfWeek', $day_number)->groupBy('teacherId');
		$totalItems = count($paginate->get());

		// filtring teachers
		if(\Input::has('search_value')){
			$searchInput = \Input::get('search_value');
			$teacher_ids = User::where('fullName', 'like', '%' . $searchInput . '%')
				->orWhere('id', $searchInput)
				->orWhere('phoneNo', 'like', '%' . $searchInput . '%')
				->pluck('id');
			$paginate->whereIn('teacherID', $teacher_ids);
		} else {
			$paginate->take(5)->skip(5 * ($page - 1));
		}

		$paginate = $paginate->pluck('teacherId');

		$query = ClassSchedule::where('dayOfWeek', $day_number)
			->whereIn('teacherId', $paginate);

		$break_subject = Subject::where('subjectTitle', 'Break')->first();
		if($break_subject != null) {
			$query = $query->where('subjectId', '!=', $break_subject->id);
		}

		$classes = MClass::pluck('className', 'id');
		$sections = Section::pluck('sectionName', 'id');
		$subjects = Subject::pluck('subjectTitle', 'id');

		$group_data = [];

		if($query->count()) {
			$data = $query->get()->toArray();
			foreach ($data as $key => $item) {
				$data[$key]['class_name'] = '';
				$data[$key]['section_name'] = '';
				$data[$key]['subject_name'] = '';

				if(isset($classes[Main::getClassIdBySectionId($item['sectionId'])])) {
					$data[$key]['class_name'] = $classes[Main::getClassIdBySectionId($item['sectionId'])];
				} else {
					unset($data[$key]);
				}

				if(isset($sections[$item['sectionId']])) {
					$data[$key]['section_name'] = $sections[$item['sectionId']];
				}

				if(isset($subjects[$item['subjectId']])) {
					$data[$key]['subject_name'] = $subjects[$item['subjectId']];
				}

				$data[$key]['startTime'] = implode(":", str_split($item['startTime'], 2));
				$data[$key]['endTime'] = implode(":", str_split($item['endTime'], 2));

				// for orderBy
				$data[$key]['_startTime'] = (integer) $item['startTime'];

				$data[$key]['disabled_status'] = true;
			}
			foreach ($data as $key => $item) {
				if(isset($item['teacherId'])) {
					$group_data[$item['teacherId']][$key] = $item;
				}
			}
		}

		return response()->json([
			'data' => $group_data,
			'totalItems' => $totalItems
		]);
	}

	public function listClasswise($day_number, $page = 1){

		if(!$this->panelInit->can( "timeTableClassWise.list" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to access Timetable");
		}

		$paginate = ClassSchedule::where('dayOfWeek', $day_number)->groupBy('sectionId');
		$totalItems = count($paginate->get());

		// filtring teachers
		if(\Input::has('class_id') || \Input::has('section_id')){
			$class_id = \Input::get('class_id');
			$section_id = \Input::get('section_id');

			if($class_id > 0 && $section_id > 0) {
				$paginate->where('sectionId', $section_id);
			} else if ($class_id > 0) {
				$section_ids = Section::where('classId', $class_id)->pluck('id')->toArray();
				$paginate->whereIn('sectionId', $section_ids);
			}
		} else {
			$paginate->take(4)->skip(4 * ($page - 1));
		}

		$paginate = $paginate->pluck('sectionId');

		$query = ClassSchedule::where('dayOfWeek', $day_number)
			->whereIn('sectionId', $paginate)
			->orderBy('startTime', 'ASC');

		// $i = 1;
		// $query_count = 0;
		// while($query_count == 0) {
		// 	$paginate2 = ClassSchedule::where('dayOfWeek', $day_number)->groupBy('sectionId');
		// 	$totalItems = count($paginate2->get());
		// 	$paginate2 = $paginate2->take(4)->skip(4 * (($page + $i) - 1))->pluck('sectionId');
		// 	$query = ClassSchedule::where('dayOfWeek', $day_number)
		// 		->whereIn('sectionId', $paginate2)
		// 		->orderBy('startTime', 'ASC');
		// 	$query_count = $query->count();
		// 	$i++;
		// }

		// $break_subject = Subject::where('subjectTitle', 'Break')->first();
		// if($break_subject != null) {
		// 	$query = $query->where('subjectId', '!=', $break_subject->id);
		// }

		$classes = MClass::pluck('className', 'id');
		$teachers = User::where('role', 'teacher')->pluck('fullName', 'id');
		$subjects = Subject::pluck('subjectTitle', 'id');
		$sections = Section::pluck('sectionName', 'id');

		$group_data = [];

		if($query->count()) {
			$data = $query->get()->toArray();
			foreach ($data as $key => $item) {
				$data[$key]['class_name'] = '';
				$data[$key]['teacher_name'] = '';
				$data[$key]['subject_name'] = '';
				$data[$key]['section_name'] = '';

				if(isset($classes[Main::getClassIdBySectionId($item['sectionId'])])) {
					$data[$key]['class_name'] = $classes[Main::getClassIdBySectionId($item['sectionId'])];
				}

				if(isset($teachers[$item['teacherId']])) {
					$data[$key]['teacher_name'] = $teachers[$item['teacherId']];
				}

				if(isset($sections[$item['sectionId']])) {
					$data[$key]['section_name'] = $sections[$item['sectionId']];
				}

				if(isset($subjects[$item['subjectId']])) {
					$data[$key]['subject_name'] = $subjects[$item['subjectId']];
				}

				$data[$key]['startTime'] = implode(":", str_split($item['startTime'], 2));
				$data[$key]['endTime'] = implode(":", str_split($item['endTime'], 2));

				// for orderBy
				$data[$key]['_startTime'] = (integer) $item['startTime'];

				$data[$key]['disabled_status'] = true;
			}
			foreach ($data as $key => $item) {
				if(isset($item['sectionId'])) {
					$group_data[$item['sectionId']][$key] = $item;
				}
			}
		}

		return response()->json([
			'status' => 'success',
			'data' => $group_data,
			'totalItems' => $totalItems
		]);
	}

	protected function optimizeAdvTimetableTime($time) {
		$time = str_replace(' ', '', $time);
		$time = str_replace(':', '', $time);

		if(substr($time, -2) == 'PM') {
			$mins = substr($time, -4, 2);
			if(strlen($time) == 5) {
				$hrs = substr($time, -6, 1);
			} else {
				$hrs = substr($time, -6, 2);
			}

			$time = (((integer) $hrs) + 12) . '' . $mins;
		} else if(substr($time, -2) == 'AM') {
			$mins = substr($time, -4, 2);
			if(strlen($time) == 5) {
				$hrs = substr($time, -6, 1);
			} else {
				$hrs = substr($time, -6, 2);
			}

			$time = ((integer) $hrs) . '' . $mins;

			if(strlen($time) == 3) {
				$time = '0'. $time;
			}
		}

		return $time;
	}

	public function advancedTimetableTeacherwiseStore() {
		$timetable = request()->input('timetable');
		$day = request()->input('currentDisplayDay');
		$days_array = [
			1 => 'Sunday',
			2 => 'Monday',
			3 => 'Tuesday',
			4 => 'Wednesday',
			5 => 'Thursday',
			6 => 'Friday',
			7 => 'Saturday'
		];
		$days_array = array_flip($days_array);
		$day_number = $days_array[$day];

		$classes = MClass::pluck('id', 'className');
		$subjects = Subject::pluck('id', 'subjectTitle');

		foreach ($timetable as $key1 => $item) {
			$teacher_id = $item[0];
			$periods = $item[1];

			foreach ($periods as $key2 => $period) {
				if(
					$period['startTime'] == '' ||
					$period['endTime'] == '' ||
					$period['class_name'] == '' ||
					$period['section_name'] == '' ||
					$period['subject_name'] == '' ||
					!isset($classes[$period['class_name']]) ||
					!isset($subjects[$period['subject_name']])
				) {
					return $this->panelInit->apiOutput(false, 'Data is missing', 'Please fill all inputs of periods');
				}

				$start_time = $this->optimizeAdvTimetableTime($period['startTime']);
				$end_time = $this->optimizeAdvTimetableTime($period['endTime']);
				$class_id = $classes[$period['class_name']];
				$section_id = Section::where('classId', $class_id)->where('sectionName', $period['section_name'])->first()->id;

				if(isset($period['id'])) {
					// update current period

					$check_exists_period = ClassSchedule::where([
						'dayOfWeek' => $day_number,
						'startTime' => $start_time,
						'endTime' => $end_time,
						'sectionId' => $section_id
					])->where('id', '!=', $period['id'])
					->count();

					if($check_exists_period) {
						return $this->panelInit->apiOutput(false, 'Period was exists', '');
					} else {
						$current = ClassSchedule::where('dayOfWeek', $day_number)->find($period['id']);
						$current->startTime = $start_time;
						$current->endTime = $end_time;
						// $current->dayOfWeek = $day_number;
						$current->classId = $classes[$period['class_name']];
						$current->sectionId = $section_id;
						$current->subjectId = $subjects[$period['subject_name']];
						$current->save();
					}

				} else {
					// create new period

					$check_exists_period = ClassSchedule::where([
						'dayOfWeek' => $day_number,
						'startTime' => $start_time,
						'endTime' => $end_time,
						'sectionId' => $section_id
					])->count();

					if($check_exists_period) {
						return $this->panelInit->apiOutput(false, 'Period was exists', '');
					} else {
						$current = new ClassSchedule;
						$current->dayOfWeek = $day_number;
						$current->startTime = $start_time;
						$current->endTime = $end_time;
						$current->classId = $classes[$period['class_name']];
						$current->sectionId = $section_id;
						$current->subjectId = $subjects[$period['subject_name']];
						$current->teacherId = $teacher_id;
						$current->save();
					}
				}
			}
		}

		return $this->panelInit->apiOutput(true, 'Success save changes.', '');
	}

	public function advancedTimetableClasswiseStore() {
		$timetable = request()->input('timetable');
		$day = request()->input('currentDisplayDay');

		$days_array = [
			1 => 'Sunday',
			2 => 'Monday',
			3 => 'Tuesday',
			4 => 'Wednesday',
			5 => 'Thursday',
			6 => 'Friday',
			7 => 'Saturday'
		];
		$days_array = array_flip($days_array);
		$day_number = $days_array[$day];

		$subjects = Subject::pluck('id', 'subjectTitle');
		$teachers = User::where('role', 'teacher')->pluck('id', 'fullName');

		foreach ($timetable as $key1 => $item) {
			$section_id = $item[0];
			$periods = $item[1];

			foreach ($periods as $key2 => $period) {
				if(
					$period['startTime'] == '' ||
					$period['endTime'] == '' ||
					$period['teacher_name'] == '' ||
					$period['subject_name'] == '' ||
					!isset($subjects[$period['subject_name']]) ||
					!isset($teachers[$period['teacher_name']])
				) {
					return $this->panelInit->apiOutput(false, 'Data is missing', 'Please fill all inputs of periods');
				}

				$start_time = $this->optimizeAdvTimetableTime($period['startTime']);
				$end_time = $this->optimizeAdvTimetableTime($period['endTime']);
				$classes = MClass::pluck('id', 'className');
				$teacher_id = $teachers[$period['teacher_name']];

				if(isset($period['id'])) {
					// update current period

					$check_exists_period = ClassSchedule::where([
						'dayOfWeek' => $day_number,
						'startTime' => $start_time,
						'endTime' => $end_time,
						'sectionId' => $section_id
					])->where('id', '!=', $period['id'])
					->count();

					if($check_exists_period) {
						return $this->panelInit->apiOutput(false, 'Period was exists', '');
					} else {
						$current = ClassSchedule::where('dayOfWeek', $day_number)->find($period['id']);
						$current->startTime = $start_time;
						$current->endTime = $end_time;
						$current->teacherId = $teacher_id;
						$current->subjectId = $subjects[$period['subject_name']];
						$current->save();
					}
				} else {
					// create new period
					$check_exists_period = ClassSchedule::where([
						'dayOfWeek' => $day_number,
						'startTime' => $start_time,
						'endTime' => $end_time,
						'sectionId' => $section_id
					])->count();

					if($check_exists_period) {
						return $this->panelInit->apiOutput(false, 'Period was exists', '');
					} else {
						$current = new ClassSchedule;
						$current->dayOfWeek = $day_number;
						$current->startTime = $start_time;
						$current->endTime = $end_time;
						$current->classId = $classes[$period['class_name']];
						$current->sectionId = $section_id;
						$current->subjectId = $subjects[$period['subject_name']];
						$current->teacherId = $teacher_id;
						$current->save();
					}
				}
			}
		}

		return $this->panelInit->apiOutput(true, 'Success save changes.', '');
	}

	public function advancedTimetableExcludeTeacher() {
		$day = request()->input('currentDisplayDay');
		$teacher_id = request()->input('teacher_id');
		$days_array = [
			1 => 'Sunday',
			2 => 'Monday',
			3 => 'Tuesday',
			4 => 'Wednesday',
			5 => 'Thursday',
			6 => 'Friday',
			7 => 'Saturday'
		];
		$days_array = array_flip($days_array);
		$day_number = $days_array[$day];

		ClassSchedule::where([
			'dayOfWeek' => $day_number,
			'teacherId' => $teacher_id
		])->delete();

		$teacher_name = User::find($teacher_id)->fullName;

		return $this->panelInit->apiOutput(true, "Teacher $teacher_name was excluded.", '');
	}

	public function advancedTimetableRemovePeriod() {
		$period_id = request()->input('period_id');
		ClassSchedule::where('id', $period_id)->delete();
		return $this->panelInit->apiOutput(true, 'Period was removed.', '');
	}

	function fetch($id){

		if(!$this->panelInit->can( "classSch.list" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to access Timetable");
		}

		$arrayOfDays = array(
			2=>$this->panelInit->language['Monday'],
			3=>$this->panelInit->language['Tuesday'],
			4=>$this->panelInit->language['Wednesday'],
			5=>$this->panelInit->language['Thurusday'],
			6=>$this->panelInit->language['Friday'],
			7=>$this->panelInit->language['Saturday']
		);

		$subjectArray = array();
		$subjectObject = \subject::get();
		foreach ($subjectObject as $subject) {
			$subjectArray[$subject->id] = $subject->subjectTitle;
		}

		$toReturn = array();

		$toReturn['schedule'] = array(
			2=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][2],'data'=>array()),
			3=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][3],'data'=>array()),
			4=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][4],'data'=>array()),
			5=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][5],'data'=>array()),
			6=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][6],'data'=>array()),
			7=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][7],'data'=>array())
		);

		if($this->panelInit->settingsArray['enableSections'] == true){
			if($this->data['users']->role == "teacher"){
					$classSchedule = \class_schedule::where('sectionId',$id)
						// ->where('teacherId',$this->data['users']->id)
						->orderBy('startTime')
						->get();
			}else{
					$classSchedule = \class_schedule::where('sectionId',$id)->orderBy('startTime')->get();
			}

			foreach ($classSchedule as $schedule) {
				if($this->data['users']->role == "teacher"){
					$teacherTmp = \User::where('id',$schedule->teacherId)->select('fullName')->first();
				}else{
					$teacherTmp = \User::where('id',$schedule->teacherId)->select('fullName')->first();
				}

				if($teacherTmp){
					$teacherName = $teacherTmp->fullName;
				}else{
					$teacherName = "none";
				}
				$toReturn['schedule'][$schedule->dayOfWeek]['sub'][] = array(
					'id'=>$schedule->id,
					'teacherId'=>$schedule->teacherId,
					'teacherName' => $teacherName,
					'sectionId'=>$schedule->sectionId,
					'subjectId'=> isset($subjectArray[$schedule->subjectId])?$subjectArray[$schedule->subjectId]:"" ,
					'start'=>wordwrap($schedule->startTime,2,':',true),
					'end'=>wordwrap($schedule->endTime,2,':',true)
				);

			}

			$toReturn['section'] = \sections::where('id',$id)->first();
			$toReturn['class'] = \classes::where('id',$toReturn['section']->classId)->select('className')->first();
		}else{
			$classSchedule = \class_schedule::where('classId',$id)->orderBy('startTime')->get();
			foreach ($classSchedule as $schedule) {
				$toReturn['schedule'][$schedule->dayOfWeek]['sub'][] = array('id'=>$schedule->id,'classId'=>$schedule->classId,'subjectId'=> isset($subjectArray[$schedule->subjectId])?$subjectArray[$schedule->subjectId]:"" ,'start'=>wordwrap($schedule->startTime,2,':',true),'end'=>wordwrap($schedule->endTime,2,':',true) );
			}

			$toReturn['class'] = \classes::where('id',$id)->select('className')->first();
		}

		return $toReturn;
	}

	function fetchForTeacher(){

		if(!$this->panelInit->can( "classSch.list" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to access Timetable");
		}

		$arrayOfDays = array(
			2=>$this->panelInit->language['Monday'],
			3=>$this->panelInit->language['Tuesday'],
			4=>$this->panelInit->language['Wednesday'],
			5=>$this->panelInit->language['Thurusday'],
			6=>$this->panelInit->language['Friday'],
			7=>$this->panelInit->language['Saturday']
		);

		$subjectArray = array();
		$subjectObject = \subject::get();
		foreach ($subjectObject as $subject) {
			$subjectArray[$subject->id] = $subject->subjectTitle;
		}

		$toReturn = array();

		$toReturn['schedule'] = array(
			2=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][2],'data'=>array()),
			3=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][3],'data'=>array()),
			4=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][4],'data'=>array()),
			5=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][5],'data'=>array()),
			6=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][6],'data'=>array()),
			7=>array('dayName'=>$this->panelInit->weekdays[ $this->panelInit->settingsArray['gcalendar'] ][7],'data'=>array())
		);

		if($this->panelInit->settingsArray['enableSections'] == true){
			if($this->data['users']->role == "teacher"){
				$classSchedule = \class_schedule::where('teacherId',$this->data['users']->id)
					->orderBy('startTime')
					->get();
			}

			$classes = MClass::pluck('className', 'id');
			$sections = Section::pluck('sectionName', 'id');

			foreach ($classSchedule as $schedule) {
				if($this->data['users']->role == "teacher"){
					$teacherTmp = \User::where('id',$schedule->teacherId)->select('fullName')->first();
				}

				if($teacherTmp){
					$teacherName = $teacherTmp->fullName;
				}else{
					$teacherName = "none";
				}

				// get class id from section id
				if(Section::where('id', $schedule->sectionId)->count()) {
					$current_class_id = Section::where('id', $schedule->sectionId)->first()->classId;

					$toReturn['schedule'][$schedule->dayOfWeek]['sub'][] = array(
						'id'=>$schedule->id,
						'teacherId'=> $schedule->teacherId,
						'teacherName' => $teacherName,
						'className'=> $classes[$current_class_id],
						'sectionName'=> $sections[$schedule->sectionId],
						'subjectId'=> isset($subjectArray[$schedule->subjectId])?$subjectArray[$schedule->subjectId]:"" ,
						'start'=>wordwrap($schedule->startTime,2,':',true),
						'end'=>wordwrap($schedule->endTime,2,':',true)
					);
				}

			}
		}

		return $toReturn;
	}

	protected function createColumnsArray($end_column, $first_letters = '') {
	  $columns = array();
	  $length = strlen($end_column);
	  $letters = range('A', 'Z');

	  // Iterate over 26 letters.
	  foreach ($letters as $letter) {
	      // Paste the $first_letters before the next.
	      $column = $first_letters . $letter;

	      // Add the column to the final array.
	      $columns[] = $column;

	      // If it was the end column that was added, return the columns.
	      if ($column == $end_column)
	          return $columns;
	  }

	  // Add the column children.
	  foreach ($columns as $column) {
	      // Don't itterate if the $end_column was already set in a previous itteration.
	      // Stop iterating if you've reached the maximum character length.
	      if (!in_array($end_column, $columns) && strlen($column) < $length) {
	          $new_columns = $this->createColumnsArray($end_column, $column);
	          // Merge the new columns which were created with the final columns array.
	          $columns = array_merge($columns, $new_columns);
	      }
	  }

	  return $columns;
	}

	public function import($type){
		if(!$this->panelInit->can( "classSch.addSch" )){
			exit;
		}

		if (\Input::hasFile('excelcsv')) {
			if ( $_FILES['excelcsv']['tmp_name'] ) {

				// init & read excel data
				$temp_filename = $_FILES['excelcsv']['tmp_name'];
				$readExcel = \Excel::load($temp_filename, function($reader) {
					$reader->noHeading();
				})->toArray();


				$periods_collection = [];
				$items = [];
				$empty_values_items = [];
				$temp_days = [];

				// Filter and store cells of excel sheet
				foreach ($readExcel as $i => $row) {
					if(count(array_unique($row)) == 1 && array_unique($row)[0] == null) {
						continue;
					}
					if($i > 1) {
						for ($j = 3; $j < count($row) - 1; $j++) {
							$day = $readExcel[0][$j];
							$time = $readExcel[1][$j];
							$teacher_name = $row[1];
							$collection = $row[$j];

							if(strlen($time) > 3) {
								$periods_collection[] = $time;
							}

							$item = [
								'day' => $day,
								'time' => $time,
								'teacher_name' => $teacher_name,
								'collection' => $collection,
								'row_num' => $i + 1,
								'column_num' => $j + 1,
							];

							if($collection != $this->temp_empty_timetable_parameter) {
								$items[] = $item;
								$temp_days[] = $day;
							} else {
								$empty_values_items[] = $item;
							}
						}
					}
				}

				$temp_days = array_unique($temp_days);
				$temp_teacher_fullnames = [];
				$temp_class_names = [];
				$temp_subject_names = [];
				$warning_cases = [];

				// check cell empty or invalid cell format
				foreach ($items as $key => $collection) {
					$props = explode(',', $collection['collection']);
					$get_cell = $this->createColumnsArray('GG')[$collection['column_num'] - 1] . $collection['row_num'];

					if(empty($collection['collection']) || $collection['collection'] == '') {
						$message = 'Cell cannot be blank, in cell ['. $get_cell . ']';
						return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
					}

					if(count($props) <= 2 || count($props) >= 4) {
						$message = 'Unknow value in cell ['. $get_cell . ']';
						return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
					}

					if(!in_array($collection['teacher_name'], $temp_teacher_fullnames)) {
						$temp_teacher_fullnames[] = $collection['teacher_name'];
					}
					if(!in_array($props[0], $temp_class_names)) {
						$temp_class_names[] = $props[0];
					}
					if(!in_array(end($props), $temp_subject_names)) {
						$temp_subject_names[] = end($props);
					}
				}


				$main_targets = [];
				$check_exists_teachers_with_same_time = [];
				$check_delete_old_timetable = 0;
				$teachers = User::whereIn('fullName', $temp_teacher_fullnames)->pluck('id', 'fullName');
				$classes = MClass::whereIn('className', $temp_class_names)->pluck('id', 'className');
				$sections = Section::get()->toArray();
				$subjects = Subject::whereIn('subjectTitle', $temp_subject_names)->pluck('id', 'subjectTitle');

				// Insert or update teachers periods + validation on cells
				foreach ($items as $key => $collection) {
					$get_cell = $this->createColumnsArray('GG')[$collection['column_num'] - 1] . $collection['row_num'];

					// check cell teacher name
					if(isset(array_change_key_case($teachers->toArray(), CASE_LOWER)[strtolower($collection['teacher_name'])])) {
						$teacher_id = array_change_key_case($teachers->toArray(), CASE_LOWER)[strtolower($collection['teacher_name'])];
					} else {
						$get_cell = $this->createColumnsArray('GG')[$collection['column_num'] - 3] . $collection['row_num'];
						$message = 'Teacher "' . $collection['teacher_name'] . '" worng spelling not found datebase, in cell [' . $get_cell . ']';
						return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
					}

					if($this->getDayId($collection['day']) > 0) {
						$day = $this->getDayId($collection['day']);
					} else {
						$message = 'Day name "' . $collection['day'] . '" not found, im cell [' . $get_cell . ']';
						return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
					}

					if(strlen($collection['time']) < 5) {
						$current_time = $periods_collection[$collection['time'] - 1];
					} else {
						$current_time = $collection['time'];
					}

					$_time = explode('-', $current_time);
					if(is_array($_time) && count($_time) == 2) {
						$start_time = str_replace(':', '', $_time[0]);
						$end_time = str_replace([':', '(Break)', ' '], ['', '', ''], $_time[1]);
					} else {
						$message = 'Invalid time solt (period) format "' . $current_time . '", in cell [' . $get_cell . ']';
						return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
					}

					$props = explode(',', $collection['collection']);

					if(count($props) == 3) {
						$class_id = 0;
						$subject_id = 0;
						$class_name = $props[0];
						$subject_name = end($props);

						if(isset($classes[$class_name])) {
							$class_id = $classes[$class_name];
						} else {
							$message = '"' . $class_name . '" not found in datebase, in cell [' . $get_cell . ']';
							return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
						}
						if(isset($subjects[$subject_name])) {
							$subject_id = $subjects[$subject_name];
						} else {
							$message = 'Subject "' . $subject_name . '" not found in datebase, in cell [' . $get_cell . ']';
							return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
						}

						if($class_id > 0 && $subject_id > 0) {
							$section_item = [];
							$section_name = $props[1];

							foreach ($sections as $key => $section) {
								if($section['classId'] == $class_id && $section['sectionName'] == $section_name) {
									$section_item = $section;
								}
							}

							if(count($section_item) != 0) {
								$section_id = $section_item['id'];

								$exists_repeater = $day .'-'. $start_time .'-'. $end_time .'-'. $section_id;
								if(in_array($exists_repeater, $check_exists_teachers_with_same_time)) {
									$warning_cases[] = '"' . $class_name . '" / Section "'. $section_name .'" cannot assign 2 subjects with same time slot (period), in cell [' . $get_cell . ']';
								}

								$check_exists2 = ClassSchedule::where(['dayOfWeek' => $day, 'startTime' => $start_time, 'endTime' => $end_time, 'sectionId' => $section_id]);
								if($check_exists2->count() > 1) {
									$warning_cases[] = '"'. $class_name .'" / Section "' . $section_name . '" / Day "'. $collection['day'] .'" cannot take more than one subject in same time slot (period), Please repair it from timetable panel';
								}

								if($check_exists2->count()) {
									if($check_exists2->first()->teacherId != $teacher_id) {
										$warning_cases[] = '"'. $class_name .'"" / Section "' . $section_name . '" / Day "'. $collection['day'] .'" cannot take more than one subject in same time slot (period), in cell [' . $get_cell . ']';
									}
								}

								// Check waring cases
								if(count($warning_cases) && request()->input('warnings') != '1') {
									$info_string = "There are some warning cases: \n";
									$info_string .= "---------------------------------------";
									foreach ($warning_cases as $key__ => $case) {
										$info_string .= "\n" . ($key__ + 1) .'- '. $case;
									}
									$info_string .= "\n\nAre you sure to continue import?";
									return $this->panelInit->apiOutput2('asking', $info_string);
								}

								// delete old data
								if(!$check_delete_old_timetable) {
									ClassSchedule::select('id')->delete();
									$check_delete_old_timetable = 1;
								}

								$timetable = ClassSchedule::firstOrNew([
									'dayOfWeek' => $day,
									'startTime' => $start_time,
									'endTime' => $end_time,
									'teacherId' => $teacher_id
								]);
								$timetable->sectionId = $section_id;
								$timetable->subjectId = $subject_id;
								$timetable->save();

								$check_exists_teachers_with_same_time[] = $exists_repeater;
							} else {
								$message = '"' . $class_name . '" / Section "' . $section_name . '" not found in datebase, in cell [' . $get_cell . ']';
								return $this->panelInit->apiOutput(false, 'Incomplete import timetable', $message);
							}
						}
					}
				}

				// Delete teacher empty/old periods
				foreach ($empty_values_items as $key => $collection) {
					$teacher_id = array_change_key_case($teachers->toArray(), CASE_LOWER)[strtolower($collection['teacher_name'])];
					$day = $this->getDayId($collection['day']);
					if(strlen($collection['time']) < 5) {
						$current_time = $periods_collection[$collection['time'] - 1];
					} else {
						$current_time = $collection['time'];
					}

					$_time = explode('-', $current_time);
					$start_time = str_replace(':', '', $_time[0]);
					$end_time = str_replace([':', '(Break)', ' '], ['', '', ''], $_time[1]);

					ClassSchedule::where([
						'dayOfWeek' => $day,
						'startTime' => $start_time,
						'endTime' => $end_time,
						'teacherId' => $teacher_id
					])->delete();
				}

				// Insert Break slots to all sections -----------
				$break_slot = '';
				foreach ($periods_collection as $key => $time) {
					$_time_slot = strtolower($time);
					if(strpos($_time_slot, 'break') && $break_slot == '') {
						$break_slot = str_replace(['(break)', ' ', ':'], ['', '', ''], $_time_slot);
						$break_startTime = explode('-', $break_slot)[0];
						$break_endTime = explode('-', $break_slot)[1];
					}
				}
				if($break_slot != '') {
					$break_subject = Subject::where('subjectTitle', 'Break')->first();
					if($break_subject == null) {
						return $this->panelInit->apiOutput(false, 'Incomplete import timetable', '"Break" subject not found, Please add "Break" subject from subjects module');
					}
					foreach ($temp_days as $key1 => $day_name) {
						foreach ($sections as $key2 => $section) {
							$day = $this->getDayId($day_name);
							$timetable = new ClassSchedule;
							$timetable->dayOfWeek = $day;
							$timetable->startTime = $break_startTime;
							$timetable->endTime = $break_endTime;
							$timetable->teacherId = User::where('role', 'teacher')->first()->id;
							$timetable->sectionId = $section['id'];
							$timetable->subjectId = $break_subject->id;
							$timetable->save();
						}
					}
				}
				// ----------------------------------------------

				user_log('Timetable', 'import');

				return $this->panelInit->apiOutput(true, 'Import timetable', 'Success import timetable');
			}
		} else {
			return $this->panelInit->language['specifyFileToImport'];
			exit;
		}

		exit;
	}

	protected function getDayId($string) {
		$arr = [
			'Monday' => 2,
			'Tuesday' => 3,
			'Wednesday' => 4,
			'Thursday' => 5,
			'Friday' => 6,
			'Saturday' => 7
		];

		if(isset($arr[$string])) {
			return $arr[$string];
		} else {
			return 0;
		}
	}

	public function addSub($class){

		if(!$this->panelInit->can( "classSch.addSch", "timeTableClassWise.addSch", "timeTableTeacherWise.addSch" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to add to Timetable");
		}

		$classSchedule = new \class_schedule();
		if($this->panelInit->settingsArray['enableSections'] == true){
			$classSchedule->sectionId = $class;
		}else{
			$classSchedule->classId = $class;
		}
		$classSchedule->subjectId = \Input::get('subjectId');
		$classSchedule->dayOfWeek = \Input::get('dayOfWeek');
		$classSchedule->teacherId = \Input::get('teacherId');

		$startTime = "";
		if(\Input::get('startTimeHour') < 10){
			$startTime .= "0";
		}
		$startTime .= \Input::get('startTimeHour');
		if(\Input::get('startTimeMin') < 10){
			$startTime .= "0";
		}
		$startTime .= \Input::get('startTimeMin');
		$classSchedule->startTime = $startTime;

		$endTime = "";
		if(\Input::get('endTimeHour') < 10){
			$endTime .= "0";
		}
		$endTime .= \Input::get('endTimeHour');
		if(\Input::get('endTimeMin') < 10){
			$endTime .= "0";
		}
		$endTime .= \Input::get('endTimeMin');
		$classSchedule->endTime = $endTime;
		$classSchedule->save();


		$classSchedule->startTime = wordwrap($classSchedule->startTime,2,':',true);
		$classSchedule->endTime = wordwrap($classSchedule->endTime,2,':',true);
		$classSchedule->subjectId = \subject::where('id',\Input::get('subjectId'))->first()->subjectTitle;

		// User log
		if(MClass::find($class)) {
			$user_log_payload = 'Class: ' . MClass::find($class)->className . ', ';
		} else {
			$user_log_payload = '';
		}
		$user_log_payload .= 'Subject: ' . $classSchedule->subjectId;
		user_log('Timetable', 'create', $user_log_payload);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addSch'],$this->panelInit->language['schCreaSucc'],$classSchedule->toArray() );
	}

	public function delete($class,$id){

		if(!$this->panelInit->can( "classSch.delSch", "timeTableClassWise.delSch", "timeTableTeacherWise.delSch" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to remove from Timetable");
		}

		if ( $postDelete = \class_schedule::where('id', $id)->first() ) {
			user_log('Timetable', 'delete');
      $postDelete->delete();
      return $this->panelInit->apiOutput(true,$this->panelInit->language['delSch'],$this->panelInit->language['schDeleted']);
    }else{
      return $this->panelInit->apiOutput(false,$this->panelInit->language['delSch'],$this->panelInit->language['schNotExist']);
    }
	}

	public function fetchSub($id){

		if(!$this->panelInit->can( "classSch.editSch", "timeTableClassWise.editSch", "timeTableTeacherWise.editSch" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to edit on Timetable");
		}

		$sub = \class_schedule::where('id',$id)->first()->toArray();
		$sub['startTime'] = str_split($sub['startTime'],2);
		$sub['startTimeHour'] = intval($sub['startTime'][0]);
		$sub['startTimeMin'] = intval($sub['startTime'][1]);

		$sub['endTime'] = str_split($sub['endTime'],2);
		$sub['endTimeHour'] = intval($sub['endTime'][0]);
		$sub['endTimeMin'] = intval($sub['endTime'][1]);

		return json_encode($sub);
	}

	public function editSub($id){

		if(!$this->panelInit->can( "classSch.editSch", "timeTableClassWise.editSch", "timeTableTeacherWise.editSch" )){
			return $this->panelInit->apiOutput(false, 'Access Denied', "You don't have permission to edit on Timetable");
		}

		$classSchedule = \class_schedule::find($id);
		$classSchedule->subjectId = \Input::get('subjectId');
		$classSchedule->dayOfWeek = \Input::get('dayOfWeek');
		$classSchedule->teacherId = \Input::get('teacherId');

		$startTime = "";
		if(\Input::get('startTimeHour') < 10){
			$startTime .= "0";
		}
		$startTime .= \Input::get('startTimeHour');
		if(\Input::get('startTimeMin') < 10){
			$startTime .= "0";
		}
		$startTime .= \Input::get('startTimeMin');
		$classSchedule->startTime = $startTime;

		$endTime = "";
		if(\Input::get('endTimeHour') < 10){
			$endTime .= "0";
		}
		$endTime .= \Input::get('endTimeHour');
		if(\Input::get('endTimeMin') < 10){
			$endTime .= "0";
		}
		$endTime .= \Input::get('endTimeMin');
		$classSchedule->endTime = $endTime;
		$classSchedule->save();

		$classSchedule->startTime = wordwrap($classSchedule->startTime,2,':',true);
		$classSchedule->endTime = wordwrap($classSchedule->endTime,2,':',true);
		$classSchedule->subjectId = \subject::where('id',\Input::get('subjectId'))->first()->subjectTitle;

		user_log('Timetable', 'edit', 'Subject: ' . $classSchedule->subjectId);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editSch'],$this->panelInit->language['schModSucc'],$classSchedule->toArray() );
	}

	public function fetchParameters() {
		User::$withoutAppends = true;

		$teachers = User::select('id', 'fullName as name')->where('role', 'teacher')->get();
		$classes = MClass::select('id', 'className as name')->get();
		$sections = Section::select('id', 'classId', 'sectionName as name')->get();
		$subjects = Subject::select('id', 'subjectTitle as name')->get();

		return response()->json([
			'teachers' => $teachers,
			'classes' => $classes,
			'sections' => $sections,
			'subjects' => $subjects,
		]);
	}


	public function teacherPresence($page = 1) {
		$days_array = [
			1 => 'Sunday',
			2 => 'Monday',
			3 => 'Tuesday',
			4 => 'Wednesday',
			5 => 'Thursday',
			6 => 'Friday',
			7 => 'Saturday'
		];

		$main_title = '';

		// get day number from date
		if(!is_null(request()->input('filter_date'))) {
			$date = request()->input('filter_date');
			$current_day = Carbon::createFromFormat('d/m/Y', $date)->format('l');
			$current_day2 = $date;
		} else {
			$current_day = Carbon::now()->format('l');
			$current_day2 = Carbon::now()->format('d/m/Y');
		}
		$days_array = array_flip($days_array);
		$day_number = $days_array[$current_day];
		$main_title = $current_day2 . ' (' . $current_day . ')';

		// Fetch constants -----------
		$teachers = User::where('role', 'teacher')->get()->pluck('teacher_availability_info', 'id')->toArray();
		$subjects = Subject::pluck('subjectTitle', 'id');
		$classes = MClass::select('id', 'className')->get()->toArray();
		$sections = Section::select('id', 'classId', 'sectionName')->get()->toArray();
		$time_slots = [];
		$time_slots_query = ClassSchedule::groupBy('startTime')->orderBy('startTime', 'asc')->get()->toArray();
		foreach ($time_slots_query as $key => $slot) {
			$start_at = implode(':', str_split($slot['startTime'], 2));
			$end_at = implode(':', str_split($slot['endTime'], 2));
			$time_slots[] = $start_at . ' - ' . $end_at;
		}
		// --------------------------

		$data = [];
		$indecator_teacher = [];
		$fill_teachers = [];
		$query = ClassSchedule::where('dayOfWeek', $day_number);

		// filter by time slot, teacher, subject or section --------------------
		if(!is_null(request()->input('filter_timeslot')) && request()->input('filter_timeslot') > 0) {
			$timeslot = request()->input('filter_timeslot');
			$current_value = str_replace(':', '', explode(' - ', $timeslot)[0]);
			$query = $query->where('startTime', $current_value);
			$main_title .= ' - ' . str_replace(' - ', '~', $timeslot);
		}
		if(!is_null(request()->input('filter_class')) && request()->input('filter_class') > 0) {
			$class_id = request()->input('filter_class');
			$section_ids = Section::where('classId', $class_id)->pluck('id');
			$query = $query->whereIn('sectionId', $section_ids);
			$class_key = array_search($class_id, array_column($classes, 'id'));
			$main_title .= ' - ' . $classes[$class_key]['className'];
		}
		if(!is_null(request()->input('filter_section')) && request()->input('filter_section') > 0) {
			$section_id = request()->input('filter_section');
			$query = $query->where('sectionId', $section_id);
			$section_key = array_search($section_id, array_column($sections, 'id'));
			$main_title .= ' - Sec: ' . $sections[$section_key]['sectionName'];
		}
		if(!is_null(request()->input('filter_subject')) && request()->input('filter_subject') > 0) {
			$subject_id = request()->input('filter_subject');
			$query = $query->where('subjectId', $subject_id);
			$main_title .= ' - ' . $subjects[$subject_id];
		}
		// end filter ----------------------------------------------------------

		$presences = $query->get()->toArray();

		foreach ($presences as $key => $item) {
			if(in_array($item['teacherId'], $indecator_teacher)) {
				$temp_key = $fill_teachers[$item['teacherId']];
				if(!in_array($item['sectionId'], $data[$temp_key]['sections'])) {
					$data[$temp_key]['sections'] = array_merge($data[$temp_key]['sections'], [$item['sectionId']]);
				}
				if(!in_array($item['subjectId'], $data[$temp_key]['subjects'])) {
					$data[$temp_key]['subjects'] = array_merge($data[$temp_key]['subjects'], [$item['subjectId']]);
				}
			} else {
				$data[$key] = [
					'dayOfWeek' => $day_number,
					'startTime' => $item['startTime'],
					'endTime' => $item['endTime'],
					'teacherId' => $item['teacherId'],
					'sections' => [$item['sectionId']],
					'subjects' => [$item['subjectId']]
				];
				$indecator_teacher[] = $item['teacherId'];
				$fill_teachers[$item['teacherId']] = $key;
			}
		}

		// optimize data
		foreach ($data as $key => $item) {
			$start_at = implode(':', str_split($item['startTime'], 2));
			$end_at = implode(':', str_split($item['endTime'], 2));
			$data[$key]['time_slot'] = $start_at . ' - ' . $end_at;

			if(isset($teachers[$item['teacherId']])) {
				$data[$key]['teacher_info'] = $teachers[$item['teacherId']];
			} else {
				$data[$key]['teacher_info'] = '<span class="text-warning">undefined</span>';
			}

			$subject_names = [];
			foreach ($data[$key]['subjects'] as $subject_id) {
				$subject_names[] = $subjects[$subject_id];
			}
			$data[$key]['subjects'] = implode(', ', $subject_names);

			$class_and_section_names = [];
			foreach ($data[$key]['sections'] as $section_id) {
				$arr_key = array_search($section_id, array_column($sections, 'id'));
				$section_name = $sections[$arr_key]['sectionName'];
				$class_id = $sections[$arr_key]['classId'];
				$class_key = array_search($class_id, array_column($classes, 'id'));
				$class_name = $classes[$class_key]['className'];
				$class_and_section_names[] = $class_name . '/' . $section_name;
			}
			$data[$key]['classes_and_sections'] = implode(', ', $class_and_section_names);
		}

		return response()->json([
			'presences' => $data,
			'main_title' => $main_title,
			'time_slots' => $time_slots,
			'subjects' => $subjects,
			'classes' => $classes,
			'sections' => $sections
		]);
	}

	public function teacherAvailability($page = 1) {
		$data = [];

		$days_array = [
			1 => 'Sunday',
			2 => 'Monday',
			3 => 'Tuesday',
			4 => 'Wednesday',
			5 => 'Thursday',
			6 => 'Friday',
			7 => 'Saturday',
		];

		// get day number from date
			if(!is_null(request()->input('filter_date'))) {
				$date = request()->input('filter_date');
				$current_day = Carbon::createFromFormat('d/m/Y', $date)->format('l');
				$current_day2 = $date;
			} else {
				$current_day = Carbon::now()->format('l');
				$current_day2 = Carbon::now()->format('d/m/Y');
			}
			$days_array = array_flip($days_array);
			$day_number = $days_array[$current_day];
			$main_title = $current_day2 . ' (' . $current_day . ')';
		// ----------------------------

		// Fetch constants -----------
			$teachers = User::where('role', 'teacher')->get()
				->pluck('teacher_availability_info', 'id')
				->toArray();
			$all_classes = MClass::get()->toArray();
			$sections = Section::select('id', 'classId', 'sectionName')->get()->toArray();
			$subjects = Subject::pluck('subjectTitle', 'id');
			$pluck_classes = MClass::pluck('className', 'id');
			$time_slots = [];
			$time_slots_query = ClassSchedule::groupBy('startTime')
				->select('startTime', 'endTime')
				->orderBy('startTime', 'asc')->get()->toArray();
			foreach ($time_slots_query as $key => $slot) {
				$start_at = implode(':', str_split($slot['startTime'], 2));
				$end_at = implode(':', str_split($slot['endTime'], 2));
				$time_slots[] = $start_at . ' - ' . $end_at;
			}
		// ---------------------------

		$timetable_teacher_ids = ClassSchedule::orderBy('teacherId', 'DESC')
			->groupBy('teacherId')
			// ->take(5)->skip(5 * ($page - 1))
			->pluck('teacherId', 'id');

		$data = [];

		// init data --------------
			foreach ($timetable_teacher_ids as $classScheduleId => $teacher_id) {
				foreach ($time_slots_query as $key => $slot) {
					$check = ClassSchedule::where([
						'dayOfWeek' => $day_number,
						'startTime' => $slot['startTime'],
						'endTime' => $slot['endTime'],
						'teacherId' => $teacher_id
					])
					->remember(60 * 4)
					->count();

					if($check == 0) {
						$teacher_classes = Main::getClassesIdsByTeacherId($teacher_id);
						$teacher_sections = Main::getSectionIdsByTeacherId($teacher_id);
						$teacher_subjects = Main::getSubjectsIdsByTeacherId($teacher_id);

						$data[] = [
							'dayOfWeek' => $day_number,
							'teacher_id' => $teacher_id,
							'startTime' => $slot['startTime'],
							'endTime' => $slot['endTime'],
							'teacher_classes' => $teacher_classes,
							'teacher_sections' => $teacher_sections,
							'teacher_subjects' => $teacher_subjects,
						];
					}
				}
			}
		// end init ---------------

		// get total items --------------------------------------------------
			// if (!Cache::has('total_items_for_teacher_avai_41204568')) {
			// 	$totalItems = 0;
			// 	$timetable_teacher_ids = ClassSchedule::orderBy('teacherId', 'DESC')
			// 		->groupBy('teacherId')
			// 		->pluck('teacherId', 'id');

			// 	foreach ($timetable_teacher_ids as $classScheduleId => $teacher_id) {
			// 		foreach ($time_slots_query as $key => $slot) {
			// 			$check = ClassSchedule::where([
			// 				'dayOfWeek' => $day_number,
			// 				'startTime' => $slot['startTime'],
			// 				'endTime' => $slot['endTime'],
			// 				'teacherId' => $teacher_id
			// 			])->count();

			// 			if($check == 0) {
			// 				$totalItems++;
			// 			}
			// 		}
			// 	}
			// 	Cache::put('total_items_for_teacher_avai_41204568', $totalItems, 120); // minutes
			// } else {
			// 	$totalItems = Cache::get('total_items_for_teacher_avai_41204568');
			// }
		// end get total items ----------------------------------------------

		// filter -----------------------------------------------------------------
			foreach ($data as $key => $item) {
				// filter by time slot, teacher, subject or section --------------------
				if(!is_null(request()->input('filter_timeslot')) && request()->input('filter_timeslot') > 0) {
					$timeslot = request()->input('filter_timeslot');
					$filter_startTime = str_replace(':', '', explode(' - ', $timeslot)[0]);
					$filter_endTime = str_replace(':', '', explode(' - ', $timeslot)[1]);
					if($item['startTime'] == $filter_startTime && $item['endTime'] == $filter_endTime) {
					} else {
						unset($data[$key]);
					}
				}
				if(!is_null(request()->input('filter_class')) && request()->input('filter_class') > 0) {
					$class_id = request()->input('filter_class');
					if(!in_array($class_id, $item['teacher_classes'])) {
						unset($data[$key]);
					}
				}
				if(!is_null(request()->input('filter_section')) && request()->input('filter_section') != '0') {
					$section_ids = json_decode(request()->input('filter_section'));
					foreach ($section_ids as $section_id) {
						if(!in_array($section_id, $item['teacher_sections'])) {
							unset($data[$key]);
						}
					}
				}
				if(!is_null(request()->input('filter_subject')) && request()->input('filter_subject') != '0') {
					$subject_ids = json_decode(request()->input('filter_subject'));
					foreach ($subject_ids as $subject_id) {
						if(!in_array($subject_id, $item['teacher_subjects'])) {
							unset($data[$key]);
						}
					}
				}
				// end filter ----------------------------------------------------------
			}

			if(!is_null(request()->input('filter_timeslot')) && request()->input('filter_timeslot') > 0) {
				$main_title .= ' - ' . str_replace(' - ', '~', $timeslot);
			}
			if(!is_null(request()->input('filter_class')) && request()->input('filter_class') > 0) {
				$main_title .= ' - ' . $pluck_classes[$class_id];
			}
			if(!is_null(request()->input('filter_subject')) && request()->input('filter_subject') > 0) {
				$main_title .= ' - ' . $subjects[$subject_id];
			}
		// end filter ------------------------------------------------------------

		// optimize data ------------------
			foreach ($data as $key => $item) {
				$start_at = implode(':', str_split($item['startTime'], 2));
				$end_at = implode(':', str_split($item['endTime'], 2));
				$time_slot = $start_at . ' - ' . $end_at;

				$data[$key]['time_slot'] = $time_slot;
				if(isset($teachers[$item['teacher_id']])) {
					$data[$key]['teacher_info'] = $teachers[$item['teacher_id']];
				} else {
					$data[$key]['teacher_info'] = '<span class="text-warning">undefined</span>';
				}

				$subject_names = [];
				foreach ($item['teacher_subjects'] as $subject_id) {
					if(isset($subjects[$subject_id])) {
						$subject_names[] = $subjects[$subject_id];
					}
				}
				$data[$key]['subjects'] = implode(', ', $subject_names);

				// $class_names = [];
				// foreach ($item['teacher_classes'] as $class_id) {
				// 	$class_names[] = $pluck_classes[$class_id];
				// }
				// $data[$key]['classes'] = implode(', ', $class_names);

				$class_and_section_names = [];
				foreach ($item['teacher_sections'] as $section_id) {
					$arr_key = array_search($section_id, array_column($sections, 'id'));
					$section_name = $sections[$arr_key]['sectionName'];
					$class_id = $sections[$arr_key]['classId'];
					$class_key = array_search($class_id, array_column($all_classes, 'id'));
					$class_name = $all_classes[$class_key]['className'];
					$class_and_section_names[] = $class_name . '/' . $section_name;
				}
				$data[$key]['classes_and_sections'] = implode(', ', $class_and_section_names);

				// filter un-needed items
				unset($data[$key]['teacher_id']);
				unset($data[$key]['startTime']);
				unset($data[$key]['endTime']);
				unset($data[$key]['teacher_classes']);
				unset($data[$key]['teacher_sections']);
				unset($data[$key]['teacher_subjects']);
			}
		// end optimize data --------------

		return response()->json([
			'availabilities' => $data,
			'main_title' => $main_title,
			'time_slots' => $time_slots,
			'classes' => $all_classes,
			'sections' => $sections,
			'subjects' => $subjects,
			// 'totalItems' => $totalItems
		]);
	}

	public function preLoad()
	{
		User::$withoutAppends = true;
		if( !$this->panelInit->can( array( "timeTableClassWise.list","timeTableClassWise.addSch","timeTableClassWise.editSch","timeTableClassWise.delSch" ) ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "You don't have permission to view timetable" ); }
        $classesArray = array();
        if( $this->data['users']->role == "teacher" )
		{
			$class_ids = Main::getClassesIdsByTeacherId($this->data['users']->id);
			$classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->whereIn('id', $class_ids)->get()->toArray();
			$sectionsIds = Main::getSectionIdsByTeacherId($this->data['users']->id);
		}
		elseif( $this->data['users']->role == "parent" )
		{
			$class_ids = Main::getClassesIdsByParentId($this->data['users']->id);
			$classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->whereIn('id', $class_ids)->get()->toArray();
			$sectionsIds = Main::getSectionIdsByParentId($this->data['users']->id);
		} else { $classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->get()->toArray(); }
		foreach( $classes as $class )
		{
			$class_id = $class['id']; $name = $class['className'];
			$sections = Section::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
			$classesArray[ $class_id ] = [ 'id' => $class_id, 'name' => $name, 'sections' => $sections ];
		}

		if( $this->data['users']->role == "teacher" || $this->data['users']->role == "parent" )
		{
			foreach( $classesArray as $classId => $oneClass )
			{
				foreach( $oneClass['sections'] as $index => $oneSection )
				{
					if( !in_array($oneSection['id'], $sectionsIds) )
					{
						unset( $classesArray[$classId]["sections"][$index] );
					}
				}
			}
		}
		$teachersArray = []; $subjectsArray = [];
		$teachers = User::select('id', 'fullName as name')->where('role', 'teacher')->get();
		$subjects = Subject::select('id', 'subjectTitle as name')->get();
		foreach( $teachers as $teacher ) { $teachersArray[] = ['id' => $teacher->id, 'name' => $teacher->name]; }
		foreach( $subjects as $subject ) { $subjectsArray[] = ['id' => $subject->id, 'name' => $subject->name]; }

        $toReturn['classes'] = $classesArray;
        $toReturn['teachers'] = $teachersArray;
        $toReturn['subjects'] = $subjectsArray;
        return $toReturn;
	}

	public function listSchedules()
	{
		if( !$this->panelInit->can( array( "timeTableClassWise.list","timeTableClassWise.addSch","timeTableClassWise.editSch","timeTableClassWise.delSch" ) ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "You don't have permission to view timetable" ); }
		if( !\Input::has('class_id') ) { return $this->panelInit->apiOutput( false, "Class timetable", "Class is not selected" ); }
		if( !\Input::has('section_id') ) { return $this->panelInit->apiOutput( false, "Class timetable", "Section is not selected" ); }
		$class_id = \Input::get('class_id');
		$section_id = \Input::get('section_id');
		$toReturn = array();
		$toReturn['schedule'] = $this->getScheduleList($class_id, $section_id);
		$toReturn['classId'] = $class_id;
		$toReturn['sectionId'] = $section_id;

		return $toReturn;
	}

	public function storeSchedule()
	{
		if( !$this->panelInit->can( array( "timeTableClassWise.addSch","timeTableClassWise.editSch" ) ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "You don't have permission to save timetable", "isError" ); }
		if( !\Input::has('classId') ) { return $this->panelInit->apiOutput( false, "Class timetable", "Class not defined", "isError" ); }
		if( !\Input::has('sectionId') ) { return $this->panelInit->apiOutput( false, "Class timetable", "Section not defined", "isError" ); }
		if( !\Input::has('schedule') ) { return $this->panelInit->apiOutput( false, "Class timetable", "timetable not defined", "isError" ); }
		$class_id = intval( \Input::get('classId') );
		$section_id = intval( \Input::get('sectionId') );
		$schedule = \Input::get('schedule');
		if( !is_array( $schedule ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "timetable not defined", "isError" ); }
		if( !count( $schedule ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "timetable not defined", "isError" ); }
		$toAdd = [];
		$toEdit = [];
		$existedIds = [];
		foreach( $schedule as $scheduleData )
		{
			if( !$scheduleData['status'] ) continue;
			$dayId = $scheduleData['dayId'];
			$periods = $scheduleData['schedule'];
			foreach( $periods as $onePeriod )
			{
				if( $onePeriod['is_break'] == "yes" )
				{
					if( $onePeriod['startTime'] == '' || $onePeriod['endTime'] == '' )
					{
						return $this->panelInit->apiOutput(false, 'Data is missing', 'Please fill all inputs of periods', "isError");
					}
				}
				elseif( $onePeriod['is_break'] == "no" )
				{
					if(
						$onePeriod['startTime'] == '' || $onePeriod['endTime'] == '' || $onePeriod['subjectId'] == 0 ||
						$onePeriod['teacherId'] == 0 || $onePeriod['subjectName'] == '' || $onePeriod['teacherName'] == ''
					)
					{
						return $this->panelInit->apiOutput(false, 'Data is missing', 'Please fill all inputs of periods', "isError");
					}
				}
				if( $onePeriod['id'] == "NEW" )
				{
					$toAdd[] = [
						'classId'   => $onePeriod['classId'],
						'sectionId' => $onePeriod['sectionId'],
						'subjectId' => $onePeriod['is_break'] == "yes" ? 0 : $onePeriod['subjectId'],
						'dayOfWeek' => $dayId,
						'teacherId' => $onePeriod['is_break'] == "yes" ? 0 : $onePeriod['teacherId'],
						'startTime' => $this->optimizeAdvTimetableTime($onePeriod['startTime']),
						'endTime'   => $this->optimizeAdvTimetableTime($onePeriod['endTime']),
						'is_break'  => $onePeriod['is_break']
					];
				}
				else
				{
					$id = intval( $onePeriod['id'] );
					if( !in_array( $id, $existedIds) ) { $existedIds[] = $id; }
					$toEdit[] = [
						'id'        => $onePeriod['id'],
						'classId'   => $onePeriod['classId'],
						'sectionId' => $onePeriod['sectionId'],
						'subjectId' => $onePeriod['is_break'] == "yes" ? 0 : $onePeriod['subjectId'],
						'dayOfWeek' => $dayId,
						'teacherId' => $onePeriod['is_break'] == "yes" ? 0 : $onePeriod['teacherId'],
						'startTime' => $this->optimizeAdvTimetableTime($onePeriod['startTime']),
						'endTime'   => $this->optimizeAdvTimetableTime($onePeriod['endTime']),
						'is_break'  => $onePeriod['is_break']
					];
				}
			}
		}
		$status = "failed"; $toLastEdit = [];
		if( count( $toAdd ) )
		{
			if( !$this->panelInit->can( array( "timeTableClassWise.addSch" ) ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "You don't have permission to add to timetable", "isError" ); }
			ClassSchedule::insert($toAdd);
			$status = "success";
		}
		if( count( $toEdit ) )
		{
			if( !$this->panelInit->can( array( "timeTableClassWise.editSch" ) ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "You don't have permission to edit on timetable", "isError" ); }
			$toCompareEdit = [];
			$oldSchedule = ClassSchedule::whereIn('id', $existedIds)->get()->toArray();
			foreach( $oldSchedule as $oneSchedule ) { $id = $oneSchedule['id']; $toCompareEdit[$id] = $oneSchedule; }
			foreach( $toEdit as $oneEdit )
			{
				$id = $oneEdit['id'];
				if( isset( $toCompareEdit[$id] ) )
				{
					if(
						$toCompareEdit[$id]['startTime'] != $oneEdit['startTime'] ||
						$toCompareEdit[$id]['endTime']   != $oneEdit['endTime'] ||
						$toCompareEdit[$id]['subjectId'] != $oneEdit['subjectId'] ||
						$toCompareEdit[$id]['teacherId'] != $oneEdit['teacherId']
					)
					{
						$toLastEdit[] = $oneEdit;
					}
				}
			}
		}
		if( count( $toLastEdit ) )
		{
			foreach( $toLastEdit as $oneEdit )
			{
				$id = $oneEdit['id'];
				$oneSchedule = ClassSchedule::find($id);
				if( !$oneSchedule ) $oneSchedule = new ClassSchedule();
				$oneSchedule->classId   = $oneEdit['classId'];
				$oneSchedule->sectionId = $oneEdit['sectionId'];
				$oneSchedule->subjectId = $oneEdit['subjectId'];
				$oneSchedule->dayOfWeek = $oneEdit['dayOfWeek'];
				$oneSchedule->teacherId = $oneEdit['teacherId'];
				$oneSchedule->startTime = $oneEdit['startTime'];
				$oneSchedule->endTime   = $oneEdit['endTime'];
				$oneSchedule->is_break  = $oneEdit['is_break'];
				$oneSchedule->save();
			}
			$status = "success";
		}
		
		
		$toReturn = [
			"status"   => $status,
			"title"    => "Class timetable",
			"message"  => $status == "success" ? "Timetable saved successfully" : "Nothing to save"
		];
		if( $status == "success" ) $toReturn['schedule'] = $this->getScheduleList($class_id, $section_id);
		return $toReturn;
	}

	public function removeSchedule()
	{
		if( !$this->panelInit->can( array( "timeTableClassWise.delSch" ) ) ) { return $this->panelInit->apiOutput( false, "Class timetable", "You don't have permission to remove from timetable", "isError" ); }
		if( !\Input::has('period_id') ) { return $this->panelInit->apiOutput( false, "Class timetable", "Selected period not found", "isError" ); }
		$id = \Input::get('period_id');
		$schedule = ClassSchedule::find($id);
		if( !$schedule ) { return $this->panelInit->apiOutput( false, "Class timetable", "Selected period not found", "isError" ); }
		$schedule->delete();
		return $this->panelInit->apiOutput( true, "Class timetable", "Period deleted successfully", ["timer" => 2000] );
	}

	protected function getScheduleList( $class_id, $section_id )
	{
		User::$withoutAppends = true;
		$schedule = ClassSchedule::where('classId', $class_id)->where('sectionId', $section_id)->get()->toArray();
		$weeklyHolidays = WeeklyHoliday::select('week_holiday_id as id', 'day_name as name', 'status')->where('status', '1')->get();
		foreach( $weeklyHolidays as $week )
		{
			$name = strtolower( $week->name );
			$weeklyDays[] = getDayNum( $name );
		}
		$subjects = []; $classes = []; $sections = []; $teachers = [];
		$classes = MClass::pluck('className', 'id');
		$teachers = User::where('role', 'teacher')->pluck('fullName', 'id');
		$subjects = Subject::pluck('subjectTitle', 'id');
		$sections = Section::pluck('sectionName', 'id');
		$days_array = [
			1 => 'Sunday',
			2 => 'Monday',
			3 => 'Tuesday',
			4 => 'Wednesday',
			5 => 'Thursday',
			6 => 'Friday',
			7 => 'Saturday'
		];
		$userId = $this->data['users']->id;
		foreach( $schedule as $key => $data )
		{
			if( $this->data['users']->role == "teacher" )
			{
				if( floatval($userId) != floatval( $data['teacherId'] ) )
				{
					unset( $schedule[$key] );
					continue;
				}
			}
			$schedule[$key]['dayOfWeek'] = intval( $data['dayOfWeek'] );
			$schedule[$key]['startTime'] = implode(":", str_split($data['startTime'], 2));
			$schedule[$key]['endTime'] = implode(":", str_split($data['endTime'], 2));
			$schedule[$key]['className'] = isset($classes[$data['classId']]) ? $classes[$data['classId']] : "";
			$schedule[$key]['sectionName'] = isset($sections[$data['sectionId']]) ? $sections[$data['sectionId']] : "";
			$schedule[$key]['subjectName'] = isset($subjects[$data['subjectId']]) ? $subjects[$data['subjectId']] : "";
			$schedule[$key]['teacherName'] = isset($teachers[$data['teacherId']]) ? $teachers[$data['teacherId']] : "";
			$schedule[$key]['disabled_status'] = true;
		}
		$weekDayWise = [];
		foreach( $schedule as $data ) { $weekId = $data['dayOfWeek']; $weekDayWise[$weekId][] = $data; }
		$outPutArray = [
			['classId' => $class_id, 'sectionId' => $section_id, 'dayId' => 2, 'name'=> 'Monday'   , 'status' => true, 'schedule' => []],
			['classId' => $class_id, 'sectionId' => $section_id, 'dayId' => 3, 'name'=> 'Tuesday'  , 'status' => true, 'schedule' => []],
			['classId' => $class_id, 'sectionId' => $section_id, 'dayId' => 4, 'name'=> 'Wednesday', 'status' => true, 'schedule' => []],
			['classId' => $class_id, 'sectionId' => $section_id, 'dayId' => 5, 'name'=> 'Thursday' , 'status' => true, 'schedule' => []],
			['classId' => $class_id, 'sectionId' => $section_id, 'dayId' => 6, 'name'=> 'Friday'   , 'status' => true, 'schedule' => []],
			['classId' => $class_id, 'sectionId' => $section_id, 'dayId' => 7, 'name'=> 'Saturday' , 'status' => true, 'schedule' => []],
			['classId' => $class_id, 'sectionId' => $section_id, 'dayId' => 1, 'name'=> 'Sunday'   , 'status' => true, 'schedule' => []]
		];
		foreach( $outPutArray as $index => $day )
		{
			$dayId = $day['dayId'];
			if( isset( $weekDayWise[$dayId] ) ) { $outPutArray[$index]['schedule'] = $weekDayWise[$dayId]; }
			if( in_array( $dayId, $weeklyDays ) ) { $outPutArray[$index]['status'] = false; }
		}
		return $outPutArray;
	}
}