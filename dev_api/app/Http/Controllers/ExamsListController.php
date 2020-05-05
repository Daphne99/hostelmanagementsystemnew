<?php
namespace App\Http\Controllers;

use App\Models2\Examterms;
use App\Models2\ExamsList;
use App\Models2\ExamMark;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\SchoolTerm;
use App\Models2\SubSubject;
use App\Models2\Subject;
use App\Models2\User;
use App\Models2\sections;
use App\Models2\class_schedule;
use Illuminate\Support\Facades\Auth;

class ExamsListController extends Controller {

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

	public function preLoadList()
	{
		$toReturn = array();
		$toReturn['terms'] = SchoolTerm::select('id', 'title as name')->get()->toArray();
		$classes = MClass::select('id', 'className as name')->get()->toArray();
		$current_user = \Auth::user()->id;
		$user = \Auth::user();
		$role = $user->role;
		foreach( $classes as $index => $class )
		{
			$class_id = $class['id'];
			$sections = sections::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
			$classes[$index]['sections'] = $sections;
		}
		$toReturn['classes'] = $classes;
		$classesArray = [];
		foreach( $classes as $oneClass ) { $id = $oneClass['id']; $classesArray[$id] = $oneClass; }
		$mainSubjects = []; $secondarySubjects = [];
		$exams_list = ExamsList::where('examAcYear', $this->panelInit->selectAcYear)->get()->toArray();
		$readSchedule = class_schedule::select('subjectId as subject', 'teacherId as teacher')->get()->toArray();
		$finalAllowance = [];
		$subjects = Subject::get();
		$subSubjects = SubSubject::get();
		foreach( $readSchedule as $itemValue )
		{
			$subject_id = $itemValue['subject'];
			$teacher_id = $itemValue['teacher'];
			if( array_key_exists( $subject_id, $finalAllowance ) )
			{
				if( !in_array( $teacher_id, $finalAllowance[$subject_id] ) ) $finalAllowance[$subject_id][] = $teacher_id;
			} else $finalAllowance[$subject_id][] = $teacher_id;
		}
		foreach( $subjects as $subject )
		{
			$subject_id = $subject->id;
			$allowed = json_decode( $subject->teacherId, true );
			if( json_last_error() != JSON_ERROR_NONE ) $allowed = [];
			$mainSubjects[$subject_id] = [
				'id' => $subject_id,
				'name' => $subject->subjectTitle,
				'allowed' => $allowed
			];
		}
		foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }
		foreach($exams_list as $key => $value)
		{
			$exams_list[$key]['examDate'] = $this->panelInit->unix_to_date($exams_list[$key]['examDate']);
			$exams_list[$key]['examEndDate'] = $this->panelInit->unix_to_date($exams_list[$key]['examEndDate']);
			$examClasses = json_decode( $value['examClasses'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examClasses = [];
			$examSchedule = json_decode( $value['examSchedule'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examSchedule = [];
			foreach( $examSchedule as $index => $schedule )
			{
				if(!array_key_exists('subject', $schedule)) continue;
				$stDate = $schedule['stDate'];
				$examSchedule[$index]['date'] = trim($stDate) != "" ? date('d/m/Y', $stDate) : "";
				$subject_id = $schedule['subject'];
				if( array_key_exists('subject_type', $schedule) )
				{
					$subject_type = $schedule['subject_type'];
					if( $subject_type == 'main' )
					{
						if( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						else { continue; }
					}
					elseif( $subject_type == 'secondary' )
					{
						if( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						else { continue; }
					} else { continue; }
				}
				else
				{
					if( array_key_exists( $subject_id, $mainSubjects) )
						{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
					elseif( array_key_exists( $subject_id, $secondarySubjects) )
						{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
					else { continue; }
				}
			}
			foreach( $examSchedule as $index => $schedule )
			{
				if( $role == 'admin' ) $examSchedule[$index]['allowance'] = true;
				else
				{
					$subject_id = $schedule['subject'];
					if( array_key_exists( $subject_id, $finalAllowance ) )
					{
						if( in_array( $current_user, $finalAllowance[$subject_id] ) ) $examSchedule[$index]['allowance'] = true;
						else $examSchedule[$index]['allowance'] = false;
					} else $examSchedule[$index]['allowance'] = false;
				}
			}
			$exams_list[$key]['examClasses'] = $examClasses;
			$exams_list[$key]['examSchedule'] = $examSchedule;
		}
		$newFinalList = [];
		foreach( $exams_list as $listItem )
		{
			$newFinalSchedule = [];
			$examSchedule = $listItem['examSchedule'];
			foreach( $examSchedule as $schedule )
			{
				if(!array_key_exists('subject', $schedule)) continue;
				$subjectId = $schedule['subject'];
				if( !is_numeric($subjectId) ) { $newFinalSchedule[] = [ 'id' => $subjectId, 'name' => $schedule['name'] ];  continue; }
				if( array_key_exists('subject_type', $schedule) )
				{
					$subject_type = $schedule['subject_type'];
					if( $subject_type == "main" )
					{
						if( array_key_exists( $subjectId, $mainSubjects ) )
						{
							$targetSubjectId = "m_" . $subjectId;
							$name = $mainSubjects[$subjectId]['name'];
						} else continue;
					}
					else
					{
						if( array_key_exists( $subjectId, $secondarySubjects ) )
						{
							$targetSubjectId = "s_" . $subjectId;
							$name = $secondarySubjects[$subjectId]['name'];
						} else continue;
					}
				}
				else
				{
					if( array_key_exists( $subjectId, $mainSubjects ) )
					{
						$targetSubjectId = "m_" . $subjectId;
						$name = $mainSubjects[$subjectId]['name'];
					}
					elseif( array_key_exists( $subjectId, $secondarySubjects ) )
					{
						$targetSubjectId = "s_" . $subjectId;
						$name = $secondarySubjects[$subjectId]['name'];
					} else continue;
				}
				$newFinalSchedule[] = [ 'id' => $targetSubjectId, 'name' => $name ];
			}
			$newFinalList[] = [
				'id' => $listItem['id'],
				'name' => $listItem['examTitle'],
				'term' => $listItem['school_term_id'],
				'classes' => $listItem['examClasses'],
				'subjects' => $newFinalSchedule
			];
		}
		foreach( $newFinalList as $keey => $oneExam )
		{
			$newClasses = [];
			$classes = $oneExam['classes'];
			foreach( $classes as $oneClass )
			{
				$oneClass = intval( $oneClass );
				if( isset($classesArray[$oneClass]) )
				{
					if( !in_array($oneClass, $classesArray) ) { $newClasses[] = $classesArray[$oneClass]; }
				}
			}
			$newFinalList[$keey]['classes'] = $newClasses;
		}
		$mixedArray = []; $index = 0;
		foreach( $toReturn['terms'] as $oneTerm )
		{
			$termId = intval( $oneTerm['id'] );
			$mixedArray[$index]['id'] = $termId;
			$mixedArray[$index]['name'] = $oneTerm['name'];
			$mixedArray[$index]['exams'] = [];
			
			foreach( $newFinalList as $oneExam )
			{
				$term_id = intval( $oneExam['term'] );
				if( $term_id == $termId ) { $mixedArray[$index]['exams'][] = $oneExam; }
			}
			$index++;
		}
		
		$toReturn['exams'] = $newFinalList;
		$toReturn['mixedArray'] = $mixedArray;
		$toReturn['school_terms'] = SchoolTerm::get()->toArray();
		$toReturn['userRole'] = $this->data['users']->role;

		User::$withoutAppends = true;
		$toReturn['teachers'] = User::select('id', 'fullName as name')->where('role', 'teacher')->get()->toArray();
		$toReturn['subjects'] = array();
		$subjects = Subject::select('id','subjectTitle')->get()->toArray();
		foreach($subjects as $value) { $toReturn['subjects'][$value['id']] = $value['subjectTitle']; }

		$mixed = [];
		foreach( $subjects as $subject ) { $mixed[] = [ 'id' =>  "m_" . $subject['id'], "realId" => $subject['id'], 'subjectTitle' => $subject['subjectTitle'], 'type' => 'main' ]; }
		foreach( $subSubjects as $subject ) { $mixed[] = [ 'id' => "s_" . $subject->id, "realId" => $subject->id, 'subjectTitle' => $subject->subjectTitle, 'type' => 'secondary' ]; }
		$toReturn['subject_with_subsubject_lists'] = $mixed;
		if( $this->data['users']->role == "teacher" )
		{
			$class_ids = Main::getClassesIdsByTeacherId($this->data['users']->id);
			$getclasses = MClass::where('classAcademicYear',$this->panelInit->selectAcYear)->whereIn('id', $class_ids)->get()->toArray();
		} else { $getclasses = MClass::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray(); }
		$toReturn['getclasses'] = $getclasses;

		foreach( $toReturn['exams'] as $index => $oneExam )
		{
			if( !array_key_exists('classesIds', $oneExam) ) $toReturn['exams'][$index]['classesIds'] = [];
			foreach( $oneExam['classes'] as $examClass )
			{
				$class_id = intval( $examClass['id'] );
				if( !in_array( $class_id, $toReturn['exams'][$index]['classesIds'] ) )
				{
					$toReturn['exams'][$index]['classesIds'][] = $class_id;
				}
			}
		}

		foreach( $toReturn['mixedArray'] as $termIndex => $oneTerm )
		{
			foreach( $oneTerm['exams'] as $index => $oneExam )
			{
				if( !array_key_exists('classesIds', $oneExam) ) $toReturn['mixedArray'][$termIndex]['exams'][$index]['classesIds'] = [];
				foreach( $oneExam['classes'] as $examClass )
				{
					$class_id = intval( $examClass['id'] );
					if( !in_array( $class_id, $toReturn['mixedArray'][$termIndex]['exams'][$index]['classesIds'] ) )
					{
						$toReturn['mixedArray'][$termIndex]['exams'][$index]['classesIds'][] = $class_id;
					}
				}
			}
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'exams', null);
		return $toReturn;
	}

	public function listAll()
	{
		if(!$this->panelInit->can( array("examsList.list","examsList.View","examsList.addExam","examsList.editExam","examsList.delExam","examsList.examDetailsNot","examsList.showMarks","examsList.controlMarksExam") )){
			exit;
		}

		$toReturn['exams'] = array();
		if( $this->data['users']->role == "student" )
		{
			$exams_list = ExamsList::where('examAcYear',$this->panelInit->selectAcYear)
				->where('examClasses','LIKE','%"'.$this->data['users']->studentClass.'"%')->get()->toArray();
		}
		elseif ($this->data['users']->role == "parent")
		{
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if( is_array( $parentOf ) ) { foreach($parentOf as $value) { $studentId[] = $value['id']; } }

			if( count($studentId) > 0)
			{
				$studentDetails = \User::where('role','student')->whereIn('id',$studentId)->select('studentClass');
				if( $studentDetails->count() > 0 )
				{
					$studentDetails = $studentDetails->get()->toArray();
					$exams_list = ExamsList::where('examAcYear',$this->panelInit->selectAcYear)->where(function($query) use ($studentDetails){
						foreach($studentDetails as $value) { $query->orWhere('examClasses','LIKE','%"'.$value['studentClass'].'"%'); }
					})->get()->toArray();
				}
			}
		} else { $exams_list = ExamsList::where('examAcYear',$this->panelInit->selectAcYear)->get()->toArray(); }
		if( $this->data['users']->role == "teacher" )
		{
			$class_ids = Main::getClassesIdsByTeacherId($this->data['users']->id);
			$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->whereIn('id', $class_ids)->get()->toArray();
		} else { $classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray(); }
		$sections = sections::all();
		$toReturn['classes'] = $classes;
		$toReturn['sections'] = $sections;
		$subjects = Subject::get();
		$subSubjects = SubSubject::get();
		$mainSubjects = []; $secondarySubjects = [];
		$readSchedule = class_schedule::select('subjectId as subject', 'teacherId as teacher')->get()->toArray();
		$finalAllowance = [];
		foreach( $readSchedule as $itemValue )
		{
			$subject_id = $itemValue['subject'];
			$teacher_id = $itemValue['teacher'];
			if( array_key_exists( $subject_id, $finalAllowance ) )
			{
				if( !in_array( $teacher_id, $finalAllowance[$subject_id] ) ) $finalAllowance[$subject_id][] = $teacher_id;
			} else $finalAllowance[$subject_id][] = $teacher_id;
		}
		foreach( $subjects as $subject )
		{
			$subject_id = $subject->id;
			$allowed = json_decode( $subject->teacherId, true );
			if( json_last_error() != JSON_ERROR_NONE ) $allowed = [];
			$mainSubjects[$subject_id] = [
				'id' => $subject_id,
				'name' => $subject->subjectTitle,
				'allowed' => $allowed
			];
		}
		foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }

		$mixed = [];
		foreach( $subjects as $subject ) { $mixed[] = [ 'id' => $subject->id, 'subjectTitle' => $subject->subjectTitle, 'type' => 'main' ]; }
		foreach( $subSubjects as $subject ) { $mixed[] = [ 'id' => $subject->id, 'subjectTitle' => $subject->subjectTitle, 'type' => 'secondary' ]; }

		$toReturn['subject_object'] = $subjects;
		$toReturn['sub_subject_object'] = $subSubjects;
		$toReturn['subject_with_subsubject_lists'] = $mixed;
		$toReturn['subjects'] = array();
		$subjects = Subject::select('id','subjectTitle')->get()->toArray();
		foreach($subjects as $value) { $toReturn['subjects'][$value['id']] = $value['subjectTitle']; }
		$current_user = \Auth::user()->id;
		$user = \Auth::user();
		$role = $user->role;

		foreach($exams_list as $key => $value){
			$exams_list[$key]['examDate'] = $this->panelInit->unix_to_date($exams_list[$key]['examDate']);
			$exams_list[$key]['examEndDate'] = $this->panelInit->unix_to_date($exams_list[$key]['examEndDate']);
			$examClasses = json_decode( $value['examClasses'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examClasses = [];
			$examSchedule = json_decode( $value['examSchedule'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examSchedule = [];
			foreach( $examSchedule as $index => $schedule )
			{
				$stDate = $schedule['stDate'];
				$examSchedule[$index]['date'] = date('d/m/Y', $stDate);
				$subject_id = $schedule['subject'];
				if( array_key_exists('subject_type', $schedule) )
				{
					$subject_type = $schedule['subject_type'];
					if( $subject_type == 'main' )
					{
						if( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						else { continue; }
					}
					elseif( $subject_type == 'secondary' )
					{
						if( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						else { continue; }
					} else { continue; }
				}
				else
				{
					if( array_key_exists( $subject_id, $mainSubjects) )
						{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
					elseif( array_key_exists( $subject_id, $secondarySubjects) )
						{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
					else { continue; }
				}
			}
			foreach( $examSchedule as $index => $schedule )
			{
				if( $role == 'admin' ) $examSchedule[$index]['allowance'] = true;
				else
				{
					$subject_id = $schedule['subject'];
					if( array_key_exists( $subject_id, $finalAllowance ) )
					{
						if( in_array( $current_user, $finalAllowance[$subject_id] ) ) $examSchedule[$index]['allowance'] = true;
						else $examSchedule[$index]['allowance'] = false;
					} else $examSchedule[$index]['allowance'] = false;
					// if( in_array( $subject_id, $mainSubjects ) )
					// {
					// 	if( in_array( $current_user, $mainSubjects[$subject_id]['allowed'] ) ) $examSchedule[$index]['allowance'] = true;
					// 	else $examSchedule[$index]['allowance'] = false;
					// }
					// elseif( in_array( $subject_id, $secondarySubjects ) )
					// {
					// 	if( in_array( $current_user, $secondarySubjects[$subject_id]['allowed'] ) ) $examSchedule[$index]['allowance'] = true;
					// 	else $examSchedule[$index]['allowance'] = false;
					// } else $examSchedule[$index]['allowance'] = false;
				}
			}
			$exams_list[$key]['examClasses'] = $examClasses;
			$exams_list[$key]['examSchedule'] = $examSchedule;
		}
		
		$toReturn['exams'] = $exams_list;

		return $toReturn;
	}

	public function filterAll()
	{
		$toReturn['exams'] = array();
		$current_user = \Auth::user()->id;
		$user = \Auth::user();
		$role = $user->role;
		$exams_list = ExamsList::where('examAcYear',$this->panelInit->selectAcYear);
		if( \Input::has('exam') ) { if( \Input::get('exam') != "" ) { $exams_list = $exams_list->where('id', \Input::get('exam')); } }
		if( \Input::has('term') ) { if( \Input::get('term') != "" ) { $exams_list = $exams_list->where('school_term_id', \Input::get('term')); } }
		if( \Input::has('class') )
		{
			if( \Input::get('class') != "" )
			{
				$classId = (string)\Input::get('class');
				$exams_list = $exams_list->where('examClasses', 'LIKE', '%"' . $classId . '"%' );
			}
		}
		$exams_list = $exams_list->get()->toArray();
		$mainSubjects = []; $secondarySubjects = [];
		$readSchedule = class_schedule::select('subjectId as subject', 'teacherId as teacher')->get()->toArray();
		$finalAllowance = [];
		$subjects = Subject::get();
		$subSubjects = SubSubject::get();
		foreach( $readSchedule as $itemValue )
		{
			$subject_id = $itemValue['subject'];
			$teacher_id = $itemValue['teacher'];
			if( array_key_exists( $subject_id, $finalAllowance ) )
			{
				if( !in_array( $teacher_id, $finalAllowance[$subject_id] ) ) $finalAllowance[$subject_id][] = $teacher_id;
			} else $finalAllowance[$subject_id][] = $teacher_id;
		}
		foreach( $subjects as $subject )
		{
			$subject_id = $subject->id;
			$allowed = json_decode( $subject->teacherId, true );
			if( json_last_error() != JSON_ERROR_NONE ) $allowed = [];
			$mainSubjects[$subject_id] = [
				'id' => $subject_id,
				'name' => $subject->subjectTitle,
				'allowed' => $allowed
			];
		}
		foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }

		foreach($exams_list as $key => $value){
			$exams_list[$key]['examDate'] = $this->panelInit->unix_to_date($exams_list[$key]['examDate']);
			$exams_list[$key]['examEndDate'] = $this->panelInit->unix_to_date($exams_list[$key]['examEndDate']);
			$examClasses = json_decode( $value['examClasses'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examClasses = [];
			$examSchedule = json_decode( $value['examSchedule'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examSchedule = [];
			foreach( $examSchedule as $index => $schedule )
			{
				$stDate = $schedule['stDate'];
				$examSchedule[$index]['date'] = trim($stDate) != "" ? date('d/m/Y', $stDate) : "";
				$subject_id = $schedule['subject'];
				if( array_key_exists('subject_type', $schedule) )
				{
					$subject_type = $schedule['subject_type'];
					if( $subject_type == 'main' )
					{
						if( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						else { continue; }
					}
					elseif( $subject_type == 'secondary' )
					{
						if( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						else { continue; }
					} else { continue; }
				}
				else
				{
					if( array_key_exists( $subject_id, $mainSubjects) )
					{
						$examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name'];
						$examSchedule[$index]['subject_type'] = "main";
					}
					elseif( array_key_exists( $subject_id, $secondarySubjects) )
					{
						$examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name'];
						$examSchedule[$index]['subject_type'] = "secondary";
					}
					else { continue; }
				}
			}
			foreach( $examSchedule as $index => $schedule )
			{
				if( $role == 'admin' ) $examSchedule[$index]['allowance'] = true;
				else
				{
					$subject_id = $schedule['subject'];
					if( array_key_exists( $subject_id, $finalAllowance ) )
					{
						if( in_array( $current_user, $finalAllowance[$subject_id] ) ) $examSchedule[$index]['allowance'] = true;
						else $examSchedule[$index]['allowance'] = false;
					} else $examSchedule[$index]['allowance'] = false;
					// if( in_array( $subject_id, $mainSubjects ) )
					// {
					// 	if( in_array( $current_user, $mainSubjects[$subject_id]['allowed'] ) ) $examSchedule[$index]['allowance'] = true;
					// 	else $examSchedule[$index]['allowance'] = false;
					// }
					// elseif( in_array( $subject_id, $secondarySubjects ) )
					// {
					// 	if( in_array( $current_user, $secondarySubjects[$subject_id]['allowed'] ) ) $examSchedule[$index]['allowance'] = true;
					// 	else $examSchedule[$index]['allowance'] = false;
					// } else $examSchedule[$index]['allowance'] = false;
				}
			}
			$retainedSchedule = [];
			foreach( $examSchedule as $index => $schedule )
			{
				if( \Input::has('subject') )
				{
					if( \Input::get('subject') )
					{
						$mixedSubject = \Input::get('subject');
						$parsed = explode('_', $mixedSubject);
						if( count($parsed) < 2 ) { continue; }
						$type = $parsed[0];
						$subjectId = intval( $parsed[1] );
						if( array_key_exists('subject_type', $schedule) )
						{
							$subject_type = $schedule['subject_type'];
							if( $subject_type == "main" && $type == "m" )
							{
								if( intval( $schedule['subject'] ) == intval( $subjectId ) ) { $retainedSchedule[] = $schedule; }
							}
							elseif( $subject_type == "secondary" && $type == "s" )
							{
								if( intval( $schedule['subject'] ) == intval( $subjectId ) ) { $retainedSchedule[] = $schedule; }
							}
						}
					}
				}
			}
			if( !count( $retainedSchedule ) ) $retainedSchedule = $examSchedule;
			foreach( $retainedSchedule as $keey => $oneExam )
			{
				$subId = $oneExam['subject']; $type = $oneExam['subject_type'];
				if( !is_numeric($subId) ) continue;
				$retainedSchedule[$keey]['subject'] = $type == "main" ? "m_" . $subId : "s_" . $subId;
			}
			$exams_list[$key]['examClasses'] = $examClasses;
			$exams_list[$key]['examSchedule'] = $retainedSchedule;
		}
		
		$toReturn['exams'] = $exams_list;
		$markForm = [ 'class' => \Input::get('class'), 'section' => \Input::get('section') ];
		$toReturn['markForm'] = $markForm;
		return $toReturn;
	}

	public function delete($id){

		if(!$this->panelInit->can( "examsList.delExam" )){
			exit;
		}

		if ( $postDelete = ExamsList::where('id', $id)->first() ){
    		user_log('Exams', 'delete', $postDelete->examTitle);
        $postDelete->delete();
        return $this->panelInit->apiOutput(true,$this->panelInit->language['delExam'],$this->panelInit->language['exDeleted']);
    }else{
        return $this->panelInit->apiOutput(false,$this->panelInit->language['delExam'],$this->panelInit->language['exNotExist']);
    }
	}

	public function create()
	{
		if(!$this->panelInit->can( "examsList.addExam" )){
			exit;
		}
		$examDate = \Input::has('examDate') ? ( \Input::get('examDate') ? $this->panelInit->date_to_unix(\Input::get('examDate')) : "" ) : "";
		$examEndDate = \Input::has('examEndDate') ? ( \Input::get('examEndDate') ? $this->panelInit->date_to_unix(\Input::get('examEndDate')) : "" ) : "";
		$examsList = new ExamsList();
		$examsList->examTitle = \Input::get('examTitle');
		$examsList->examDescription = \Input::get('examDescription');
		$examsList->examDate = $examDate;
		$examsList->examEndDate = $examEndDate;
		$examsList->examAcYear = $this->panelInit->selectAcYear;
		$examsList->school_term_id = \Input::get('term_id');
		$examsList->main_pass_marks = \Input::get('main_pass_marks');
		$examsList->main_max_marks = \Input::get('main_max_marks');
		if(\Input::has('examClasses')){
			$examsList->examClasses = json_encode(\Input::get('examClasses'));
		}
		if(\Input::has('examMarksheetColumns')){
			$examsList->examMarksheetColumns = json_encode(\Input::get('examMarksheetColumns'));
		}
		if(\Input::has('examSchedule'))
		{
			$subjects = Subject::get();
			$subSubjects = SubSubject::get();
			$mainSubjects = []; $secondarySubjects = [];
			foreach( $subjects as $subject ) { $subject_id = $subject->id; $mainSubjects[$subject_id] = [ 'id' => $subject_id, 'name' => $subject->subjectTitle ]; }
			foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }

			$examSchedule = \Input::get('examSchedule');
			foreach($examSchedule as $index => $schedule)
			{
				$examSchedule[$index]['stDate'] = trim($schedule['stDate']) != "" ? $this->panelInit->date_to_unix($examSchedule[$index]['stDate'] ) : "";
				if(!array_key_exists('subject', $schedule)) continue;
				$subject_id = $schedule['subject'];
				if(!is_numeric($subject_id)) continue;
				if( array_key_exists('subject_type', $schedule) )
				{
					$subject_type = $schedule['subject_type'];
					if( $subject_type == 'main' )
					{
						if( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						else { continue; }
					}
					elseif( $subject_type == 'secondary' )
					{
						if( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						else { continue; }
					} else { continue; }
				}
				else
				{
					if( array_key_exists( $subject_id, $mainSubjects) )
						{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
					elseif( array_key_exists( $subject_id, $secondarySubjects) )
						{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
					else { continue; }
				}
			}
			$examsList->examSchedule = json_encode( $examSchedule );
		}
		$examsList->save();
		user_log('Exams', 'create', $examsList->examTitle);
		/*
		//Send Push Notifications
		$tokens_list = array();
		$user_ids = array();
		$user_list_students = \User::where('role','student')->whereIn('studentClass',\Input::get('examClasses'))->select('id')->get();
		$student_id = array();
		foreach($user_list_students as $ite) { $student_id[]="%\"id\":".$ite->id."}%" ; }
		$user_list_parents = array();
		foreach( $student_id as $itp )
		{
			$res = \User::where('role','parent')->where('parentOf','like',$itp)->select('id', 'firebase_token')->first();
			if($res) { $user_list_parents[] = $res; }
		}

		foreach ($user_list_parents as $value) {
			if($value['firebase_token'] != ""){
				if(is_array(json_decode($value['firebase_token']))) {
					foreach (json_decode($value['firebase_token']) as $token) {
						$tokens_list[] = $token;
					}
				} else if ($this->isJson($value['firebase_token'])) {
					foreach (json_decode($value['firebase_token']) as $token) {
						$tokens_list[] = $token;
					}
				} else {
					$tokens_list[] = $value['firebase_token'];
				}
			}
			$user_ids[] = $value['id'];
		}

		if(count($tokens_list) > 0){
			$this->panelInit->send_push_notification(
				$tokens_list,
				$user_ids,
				$this->panelInit->language['newExamNotif']." : ".\Input::get('examTitle'),
				$this->panelInit->language['examsList'],"exams",$examsList->id
			);
		} else {
			$this->panelInit->save_notifications_toDB(
				$tokens_list,
				$user_ids,
				$this->panelInit->language['newExamNotif']." : ".\Input::get('examTitle'),
				$this->panelInit->language['examsList'],"exams",$examsList->id
			);
		}*/

		$examsList->examDate = \Input::get('examDate');
		$examsList->examEndDate = \Input::get('examEndDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addExam'],$this->panelInit->language['examCreated'],$examsList->toArray() );
	}

	protected function isJson($string) {
    $decoded = json_decode($string); // decode our JSON string
	    if ( !is_object($decoded) && !is_array($decoded) ) {
	        return false;
	    }
	    return (json_last_error() == JSON_ERROR_NONE);
	}

	function fetch($id){

		if(!$this->panelInit->can( array("examsList.View","examsList.editExam") )){
			exit;
		}
		$exams_list = ExamsList::where('id',$id)->first()->toArray();
		$exams_list['examDate'] = $this->panelInit->unix_to_date($exams_list['examDate']);
		$exams_list['examEndDate'] = $this->panelInit->unix_to_date($exams_list['examEndDate']);
		$exams_list['examClasses'] = json_decode($exams_list['examClasses'],true);
		$exams_list['term_id'] = $exams_list['school_term_id'];
		if(is_array($exams_list['examClasses'])){
			$exams_list['examClassesNames'] = \classes::whereIn('id',$exams_list['examClasses'])->select('className')->get()->toArray();
		}
		$exams_list['examMarksheetColumns'] = json_decode($exams_list['examMarksheetColumns'],true);
		if(!is_array($exams_list['examMarksheetColumns'])){
			$exams_list['examMarksheetColumns'] = array();
		}
		$examSchedule = json_decode($exams_list['examSchedule'],true);

		$subjects = Subject::get();
		$subSubjects = SubSubject::get();
		$mainSubjects = []; $secondarySubjects = [];
		foreach( $subjects as $subject ) { $subject_id = $subject->id; $mainSubjects[$subject_id] = [ 'id' => $subject_id, 'name' => $subject->subjectTitle ]; }
		foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }
		foreach( $examSchedule as $index => $schedule )
		{
			if(!array_key_exists('subject', $schedule)) continue;
			$stDate = $schedule['stDate'];
			$examSchedule[$index]['date'] = trim($stDate) != "" ? date('d/m/Y', $stDate) : "";
			$subject_id = $schedule['subject'];
			if( !is_numeric($subject_id) ) continue;
			if( array_key_exists('subject_type', $schedule) )
			{
				$subject_type = $schedule['subject_type'];
				if( $subject_type == 'main' )
				{
					if( array_key_exists( $subject_id, $mainSubjects) )
						{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
					elseif( array_key_exists( $subject_id, $secondarySubjects) )
						{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
					else { continue; }
				}
				elseif( $subject_type == 'secondary' )
				{
					if( array_key_exists( $subject_id, $secondarySubjects) )
						{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
					elseif( array_key_exists( $subject_id, $mainSubjects) )
						{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
					else { continue; }
				} else { continue; }
			}
			else
			{
				if( array_key_exists( $subject_id, $mainSubjects) )
					{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
				elseif( array_key_exists( $subject_id, $secondarySubjects) )
					{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
				else { continue; }
			}
		}
		$exams_list['examSchedule'] = $examSchedule;
		if( is_array($exams_list['examSchedule']) )
		{
			foreach( $exams_list['examSchedule'] as $key => $value )
			{
				$exams_list['examSchedule'][$key]['stDate'] = $this->panelInit->unix_to_date($exams_list['examSchedule'][$key]['stDate'] );
				if( !array_key_exists( 'teachers', $exams_list['examSchedule'][$key] ) ) $exams_list['examSchedule'][$key]['teachers'] = array();
				if( !array_key_exists( 'start_time', $exams_list['examSchedule'][$key] ) ) $exams_list['examSchedule'][$key]['start_time'] = array();
				if( !array_key_exists( 'end_time', $exams_list['examSchedule'][$key] ) ) $exams_list['examSchedule'][$key]['end_time'] = array();
			}
		} else { $exams_list['examSchedule'] = array(); }

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'exams', $id);

		return $exams_list;
	}

	function edit($id){

		if(!$this->panelInit->can( "examsList.editExam" )){
			exit;
		}
		$examDate = \Input::has('examDate') ? ( \Input::get('examDate') ? $this->panelInit->date_to_unix(\Input::get('examDate')) : "" ) : "";
		$examEndDate = \Input::has('examEndDate') ? ( \Input::get('examEndDate') ? $this->panelInit->date_to_unix(\Input::get('examEndDate')) : "" ) : "";
		
		$examsList = ExamsList::find($id);
		$examsList->examTitle = \Input::get('examTitle');
		$examsList->examDescription = \Input::get('examDescription');
		$examsList->examDate = $examDate;
		$examsList->examEndDate = $examEndDate;
		$examsList->main_pass_marks = \Input::get('main_pass_marks');
		$examsList->main_max_marks = \Input::get('main_max_marks');
		$examsList->school_term_id = \Input::get('term_id');
		if(\Input::has('examClasses')){
			$examsList->examClasses = json_encode(\Input::get('examClasses'));
		}
		if(\Input::has('examMarksheetColumns')){
			$examsList->examMarksheetColumns = json_encode(\Input::get('examMarksheetColumns'));
		}
		if(\Input::has('examSchedule'))
		{
			$subjects = Subject::get();
			$subSubjects = SubSubject::get();
			$mainSubjects = []; $secondarySubjects = [];
			foreach( $subjects as $subject ) { $subject_id = $subject->id; $mainSubjects[$subject_id] = [ 'id' => $subject_id, 'name' => $subject->subjectTitle ]; }
			foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }

			$examSchedule = \Input::get('examSchedule');
			foreach($examSchedule as $index => $schedule)
			{
				$examSchedule[$index]['stDate'] = trim($schedule['stDate']) != "" ? $this->panelInit->date_to_unix($examSchedule[$index]['stDate'] ) : "";
				if(!array_key_exists('subject', $schedule)) continue;
				$subject_id = $schedule['subject'];
				if(!is_numeric($subject_id)) continue;
				if( array_key_exists('subject_type', $schedule) )
				{
					$subject_type = $schedule['subject_type'];
					if( $subject_type == 'main' )
					{
						if( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						else { continue; }
					}
					elseif( $subject_type == 'secondary' )
					{
						if( array_key_exists( $subject_id, $secondarySubjects) )
							{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
						elseif( array_key_exists( $subject_id, $mainSubjects) )
							{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
						else { continue; }
					} else { continue; }
				}
				else
				{
					if( array_key_exists( $subject_id, $mainSubjects) )
						{ $examSchedule[$index]['subject'] = "m_" . $subject_id; $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name']; }
					elseif( array_key_exists( $subject_id, $secondarySubjects) )
						{ $examSchedule[$index]['subject'] = "s_" . $subject_id; $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name']; }
					else { continue; }
				}
			}

			$examsList->examSchedule = json_encode( $examSchedule );
		}
		$examsList->save();

		user_log('Exams', 'edit', $examsList->examTitle);

		$examsList->examDate = \Input::get('examDate');
		$examsList->examEndDate = \Input::get('examEndDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExam'],$this->panelInit->language['examModified'],$examsList->toArray() );
	}

	function fetchMarks()
	{
		if(!$this->panelInit->can( array("examsList.showMarks","examsList.controlMarksExam") )){
			return $this->panelInit->apiOutput(false, "Read Exam", "you don't have permission to show exam marks");
		}

		$toReturn = array();
		$mixedSubject = \Input::get('subjectId');
		$parsed = explode('_', $mixedSubject);
		if( count($parsed) < 2 ) { return $this->panelInit->apiOutput(false, "Read Exam", "Unable to read exam subject data"); }
		$type = $parsed[0];
		$subjectId = intval( end($parsed) );
		if( $type == "m" ) $toReturn['subject'] = Subject::where('id', $subjectId)->first()->toArray();
		elseif( $type == "s" ) $toReturn['subject'] = SubSubject::where('id', $subjectId)->first()->toArray();
		else { return $this->panelInit->apiOutput(false, "Read Exam", "Unable to read exam subject data"); }
		
		$toReturn['exam'] = ExamsList::where('id',\Input::get('exam'))->first();
		$toReturn['class'] = \classes::where('id',\Input::get('classId'))->first()->toArray();
		$schedules = json_decode( $toReturn['exam']->examSchedule, true );
		if( json_last_error() != JSON_ERROR_NONE ) $schedules = [];
		$passMark = "Undefined"; $maxMark = "Undefined";
		$mainSubjects = []; $secondarySubjects = [];
		$subjects = Subject::get();
		$subSubjects = SubSubject::get();
		foreach( $subjects as $subject ) { $subject_id = $subject->id; $mainSubjects[$subject_id] = [ 'id' => $subject_id, 'name' => $subject->subjectTitle ]; }
		foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }
		foreach( $schedules as $schedule )
		{
			if( !array_key_exists( "pass_marks", $schedule) && array_key_exists( "max_marks", $schedule) ) continue;
			$subject_id = $schedule['subject'];
			if( array_key_exists('subject_type', $schedule) )
			{
				$subject_type = $schedule['subject_type'];
				if( $type == "m" && $subject_type == 'main' && $subjectId == intval( $subject_id ) )
				{
					if( isset( $mainSubjects[$subject_id] ) )
					{
						if( array_key_exists( "pass_marks", $schedule) && $passMark == "Undefined" ) { $passMark = floatval( $schedule["pass_marks"] ); }
						if( array_key_exists( "max_marks", $schedule) && $maxMark == "Undefined" ) { $maxMark = floatval( $schedule["max_marks"] ); }
					}
					elseif( isset( $secondarySubjects[$subject_id] ) )
					{
						if( array_key_exists( "pass_marks", $schedule) && $passMark == "Undefined" ) { $passMark = floatval( $schedule["pass_marks"] ); }
						if( array_key_exists( "max_marks", $schedule) && $maxMark == "Undefined" ) { $maxMark = floatval( $schedule["max_marks"] ); }
					}
				}
				elseif( $type == "s" && $subject_type == 'secondary' && $subjectId == intval( $subject_id ) )
				{
					if( isset( $secondarySubjects[$subject_id] ) )
					{
						if( array_key_exists( "pass_marks", $schedule) && $passMark == "Undefined" ) { $passMark = floatval( $schedule["pass_marks"] ); }
						if( array_key_exists( "max_marks", $schedule) && $maxMark == "Undefined" ) { $maxMark = floatval( $schedule["max_marks"] ); }
					}
					elseif( isset( $mainSubjects[$subject_id] ) )
					{
						if( array_key_exists( "pass_marks", $schedule) && $passMark == "Undefined" ) { $passMark = floatval( $schedule["pass_marks"] ); }
						if( array_key_exists( "max_marks", $schedule) && $maxMark == "Undefined" ) { $maxMark = floatval( $schedule["max_marks"] ); }
					}
				}
			}
			else
			{
				if( $type == "m" && isset( $mainSubjects[$subject_id] ) && $subjectId == intval( $subject_id ) )
				{
					if( array_key_exists( "pass_marks", $schedule) && $passMark == "Undefined" ) { $passMark = floatval( $schedule["pass_marks"] ); }
					if( array_key_exists( "max_marks", $schedule) && $maxMark == "Undefined" ) { $maxMark = floatval( $schedule["max_marks"] ); }
				}
				elseif( $type == "s" && isset( $secondarySubjects[$subject_id] ) && $subjectId == intval( $subject_id ) )
				{
					if( array_key_exists( "pass_marks", $schedule) && $passMark == "Undefined" ) { $passMark = floatval( $schedule["pass_marks"] ); }
					if( array_key_exists( "max_marks", $schedule) && $maxMark == "Undefined" ) { $maxMark = floatval( $schedule["max_marks"] ); }
				}
			}
		}
		$toReturn['exam']->main_pass_marks = $passMark;
		$toReturn['exam']->main_max_marks = $maxMark;
		
		if( $toReturn['exam']->term )
		{
			$toReturn['exam']->school_term_name = $toReturn['exam']->term->title;
			unset( $toReturn['exam']->term );
		}

		$toReturn['exam']->examClasses = json_decode($toReturn['exam']->examClasses,true);
		$toReturn['exam']->examMarksheetColumns = json_decode($toReturn['exam']->examMarksheetColumns,true);
		if(!is_array($toReturn['exam']->examMarksheetColumns)){
			$toReturn['exam']->examMarksheetColumns = array();
		}

		$toReturn['students'] = array();
		$studentArray = \User::where('role','student')->where('studentClass',\Input::get('classId'));
		if($this->panelInit->settingsArray['enableSections'] == true){
			$studentArray = $studentArray->where('studentSection',\Input::get('sectionId'));
		}
		if($this->data['panelInit']->settingsArray['studentsSort'] != ""){
			$studentArray = $studentArray->orderByRaw($this->data['panelInit']->settingsArray['studentsSort']);
		}
		$studentArray = $studentArray->get();

		$examMarksArray = array();
		$examMarks = ExamMark::where('examId',\Input::get('exam'))->where('classId',\Input::get('classId'))->where('subjectId',\Input::get('subjectId'))->get();
		foreach ($examMarks as $stMark) {
			$examMarksArray[$stMark->studentId] = $stMark;
		}

		$i = 0;
		foreach ($studentArray as $stOne) {
			$toReturn['students'][$i] = array('id'=>$stOne->id,'name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId,'examMark'=>'','attendanceMark'=>'','markComments'=>'');
			if(isset($examMarksArray[$stOne->id])){
				$toReturn['students'][$i]['examMark'] = json_decode($examMarksArray[$stOne->id]->examMark,true);
				$toReturn['students'][$i]['totalMarks'] = $examMarksArray[$stOne->id]->totalMarks;
				$toReturn['students'][$i]['markComments'] = $examMarksArray[$stOne->id]->markComments;
			}
			$i ++;
		}

		echo json_encode($toReturn);
		exit;
	}

	function saveMarks($exam,$class,$subject){

		if(!$this->panelInit->can( "examsList.controlMarksExam" )){
			exit;
		}

		$studentList = array();
		$studentArray = \User::where('role','student')->where('studentClass',$class)->get();
		foreach ($studentArray as $stOne) {
			$studentList[] = $stOne->id;
		}

		$examMarksList = array();
		$examMarks = ExamMark::where('examId',$exam)->where('classId',$class)->where('subjectId',$subject)->get();
		foreach ($examMarks as $stMark) {
			$examMarksList[$stMark->studentId] = array("examMark"=>$stMark->examMark,"attendanceMark"=>$stMark->attendanceMark,"markComments"=>$stMark->markComments);
		}

		$stMarks = \Input::get('respStudents');
		$examList = ExamsList::find( $exam );
		foreach($stMarks as $key => $value){
			if(!isset($examMarksList[$value['id']])){
				$examMarks = new ExamMark;
				$examMarks->examId = $exam;
				$examMarks->classId = $class;
				$examMarks->subjectId = $subject;
				$examMarks->studentId = $value['id'];
				if(isset($value['examMark'])){
					$examMarks->examMark = json_encode($value['examMark']);
				}
				if(isset($value['totalMarks'])){
					$examMarks->totalMarks = $value['totalMarks'];
				}
				if(isset($value['markComments'])){
					$examMarks->markComments = $value['markComments'];
				}
				$examMarks->school_term_id = $examList->school_term_id;
				$examMarks->save();
			}else{
				$examMarks = ExamMark::where([
					'examId' => $exam,
					// 'school_term_id' => $examList->school_term_id,
					'classId' => $class,
					'subjectId' => $subject,
					'studentId' => $value['id']
				])->first();
				if(isset($value['examMark'])){
					$examMarks->examMark = json_encode($value['examMark']);
				}
				if(isset($value['totalMarks'])){
					$examMarks->totalMarks = $value['totalMarks'];
				}
				if(isset($value['markComments'])){
					$examMarks->markComments = $value['markComments'];
				}
				$examMarks->school_term_id = $examList->school_term_id;
				$examMarks->save();
			}
		}

		user_log('Exams', 'take_marks');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExam'],$this->panelInit->language['examModified'] );
	}

	function notifications($id){

		if(!$this->panelInit->can( "examsList.examDetailsNot" )){
			exit;
		}

		if($this->panelInit->settingsArray['examDetailsNotif'] == "0"){
			return json_encode(array("jsTitle"=>$this->panelInit->language['examDetailsNot'],"jsMessage"=>$this->panelInit->language['adjustExamNot'] ));
		}

		$examsList = ExamsList::where('id',$id)->first()->toArray();
		$examsList['examMarksheetColumns'] = json_decode($examsList['examMarksheetColumns'],true);

		$subjectArray = array();
		$subject = Subject::get();
		foreach ($subject as $value) {
			$subjectArray[$value->id] = $value->subjectTitle;
		}

		$usersArray = array();
		if($this->data['panelInit']->settingsArray['examDetailsNotifTo'] == "parent" || $this->data['panelInit']->settingsArray['examDetailsNotifTo'] == "both"){
			$users = \User::where('role','student')->orWhere('role','parent')->get();
		}else{
			$users = \User::where('role','student')->get();
		}
		foreach ($users as $value) {
			if($value->parentOf == "" AND $value->role == "parent") continue;
			if(!isset($usersArray[$value->id])){
				$usersArray[$value->id] = array();
			}
			if($value->parentOf != ""){
				$value->parentOf = json_decode($value->parentOf);
				if(!is_array($value->parentOf)){
					continue;
				}
				if(count($value->parentOf) > 0){
					$usersArray[$value->id]['parents'] = array();
				}
				foreach ($value->parentOf as $parentOf) {
					$usersArray[$parentOf->id]['parents'][$value->id] = array(
						'id'=>$value->id,
						'username'=>$value->username,
						"email"=>$value->email,
						"fullName"=>$value->fullName,
						"mobileNo"=>$value->mobileNo,
						"firebase_token"=>$value->firebase_token,
						"comVia"=>$value->comVia
					);
				}
			}
			$usersArray[$value->id]['student'] = array(
				'id'=>$value->id,
				'username'=>$value->username,
				"studentRollId"=>$value->studentRollId,
				"mobileNo"=>$value->mobileNo,
				"email"=>$value->email,
				"fullName"=>$value->fullName,
				"firebase_token"=>$value->firebase_token,
				"comVia"=>$value->comVia
			);
		}

		$return['marks'] = array();
		$examMarks = ExamMark::where('examId',$id)->get();
		foreach ($examMarks as $value) {
			if(!isset($return['marks'][$value->studentId])){
				$return['marks'][$value->studentId] = array();
			}
			if(isset($subjectArray[$value->subjectId])){
				$value->examMark = json_decode($value->examMark,true);
				$return['marks'][$value->studentId][ $subjectArray[$value->subjectId] ] = array("examMark"=>$value->examMark,"totalMarks"=>$value->totalMarks,"markComments"=>$value->markComments);
			}
		}

		$mailTemplate = \mailsms_templates::where('templateTitle','Exam Details mini')->first();

		if($this->panelInit->settingsArray['examDetailsNotif'] == "mail" || $this->panelInit->settingsArray['examDetailsNotif'] == "mailsms"){
			$mail = true;
		}
		if($this->panelInit->settingsArray['examDetailsNotif'] == "sms" || $this->panelInit->settingsArray['examDetailsNotif'] == "mailsms"){
			$sms = true;
		}
		$sms = true;

		$MailSmsHandler = new \MailSmsHandler();

		foreach($return['marks'] as $key => $value){
			if(!isset($usersArray[$key])) continue;
			if(isset($mail)){
				$studentTemplate = $mailTemplate->templateMail;
				$examGradesTable = "";
				$totalMarks = 0;

				foreach($value as $keyG => $valueG){
					if( (!is_array($valueG['examMark']) || (is_array($valueG['examMark']) AND count($valueG['examMark']) == 0) ) AND $valueG['totalMarks'] == ""){
						continue;
					}
					$examGradesTable .= $keyG . " => ";

					if(is_array($examsList['examMarksheetColumns'])){
						reset($examsList['examMarksheetColumns']);
						foreach ($examsList['examMarksheetColumns'] as $key_ => $value_) {
							if(isset($valueG['examMark'][$value_['id']])){
								$examGradesTable .= $value_['title']." : ".$valueG['examMark'][$value_['id']]. " - ";
							}
						}
					}

					$totalMarks += $valueG['totalMarks'];
					$examGradesTable .= " - Total Marks : ".$valueG['totalMarks']." - Comments : ".$valueG['markComments']."<br/>";
				}

				if($examGradesTable == ""){
					continue;
				}
				$searchArray = array(
					"{studentName}",
					"{studentRoll}",
					"{studentEmail}","{studentUsername}",
					"{examTitle}","{examDescription}",
					"{examDate}","{schoolTitle}",
					"{examGradesTable}","{totalMarks}"
				);
				$replaceArray = array(
					$usersArray[$key]['student']['fullName'],
					$usersArray[$key]['student']['studentRollId'],
					$usersArray[$key]['student']['email'],
					$usersArray[$key]['student']['username'],
					$examsList['examTitle'],
					$examsList['examDescription'],
					$this->panelInit->unix_to_date($examsList['examDate']),
					$this->panelInit->settingsArray['siteTitle'],
					$examGradesTable,
					$totalMarks,
				);
				$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);

				if (strpos($usersArray[$key]['student']['comVia'], 'mail') !== false) {
					$MailSmsHandler->mail($usersArray[$key]['student']['email'],"Exam grade details",$studentTemplate,$usersArray[$key]['student']['fullName']);
				}
				if(isset($usersArray[$key]['parents'])){
					foreach($usersArray[$key]['parents'] as $keyP => $valueP){
						if (strpos($valueP['comVia'], 'mail') !== false) {
							$MailSmsHandler->mail($valueP['email'],"Exam grade details",$studentTemplate,$usersArray[$key]['student']['fullName']);
						}
					}
				}
			}

			$studentTemplate = $mailTemplate->templateSMS;
			$examGradesTable = "";
			$totalMarks = 0;
			reset($value);
			foreach($value as $keyG => $valueG){
				if( (!is_array($valueG['examMark']) || (is_array($valueG['examMark']) AND count($valueG['examMark']) == 0) ) AND $valueG['totalMarks'] == ""){
						continue;
					}
					$examGradesTable .= $keyG . " => ";

					if(is_array($examsList['examMarksheetColumns'])){
						reset($examsList['examMarksheetColumns']);
						foreach ($examsList['examMarksheetColumns'] as $key_ => $value_) {
							if(isset($valueG['examMark'][$value_['id']])){
								$examGradesTable .= $value_['title']." : ".$valueG['examMark'][$value_['id']]. " - ";
							}
						}
					}

					$totalMarks += $valueG['totalMarks'];
					$examGradesTable .= " - Total Marks : ".$valueG['totalMarks']." - Comments : ".$valueG['markComments']."<br/>";
			}
			if($examGradesTable == ""){
				continue;
			}
			$searchArray = array("{studentName}","{studentRoll}",
				"{studentEmail}","{studentUsername}",
				"{examTitle}","{examDescription}",
				"{examDate}","{schoolTitle}",
				"{examGradesTable}",'{totalMarks}');
			$replaceArray = array($usersArray[$key]['student']['fullName'],$usersArray[$key]['student']['studentRollId'],
				$usersArray[$key]['student']['email'],$usersArray[$key]['student']['username'],
				$examsList['examTitle'],$examsList['examDescription'],
				$this->panelInit->unix_to_date($examsList['examDate']),
				$this->panelInit->settingsArray['siteTitle'],
				$examGradesTable, $totalMarks);
			$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);

			if(isset($sms) AND $usersArray[$key]['student']['mobileNo'] != "" AND strpos($usersArray[$key]['student']['comVia'], 'sms') !== false){
				$MailSmsHandler->sms($usersArray[$key]['student']['mobileNo'],$studentTemplate);
			}

			if(isset($usersArray[$key]['parents'])){
				reset($usersArray[$key]['parents']);
				foreach($usersArray[$key]['parents'] as $keyP => $valueP){
					if(isset($sms) AND trim($valueP['mobileNo']) != "" AND strpos($valueP['comVia'], 'sms') !== false){
						$MailSmsHandler->sms($valueP['mobileNo'],$studentTemplate);
					}

					$firebase_token = $usersArray[$key]['student']['firebase_token'];
					if($firebase_token != ""){
						if(is_array(json_decode($firebase_token))) {
							$token = json_decode($firebase_token);
						} else if ($this->isJson($firebase_token)) {
							$token = json_decode($firebase_token);
						} else {
							$token[] = $firebase_token;
						}
						$this->panelInit->send_push_notification(
							$token,
							$valueP['id'],
							$studentTemplate,
							"Exam grade details","marksheet",$usersArray[$key]['student']['id']
						);
					} else {
						$this->panelInit->save_notifications_toDB(
							$token,
							$valueP['id'],
							$studentTemplate,
							"Exam grade details","marksheet",$usersArray[$key]['student']['id']
						);
					}

				}
			}
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['examDetailsNot'],$this->panelInit->language['examNotSent'] );
	}

	function classSheduleLists($id)
	{
		$classId=json_encode($id,true);
		$sheduleLists=ExamsList::where('examClasses', 'like', '%' . $classId . '%')->get();
		echo json_encode($sheduleLists);
	}

	function subjectSheduleLists($id)
	{
		$data=\Input::get();
		$classId=json_encode($data['classId'],true);
		$subjectId=json_encode($id,true);
		$sheduleLists=ExamsList::where('examClasses', 'like', '%' . $classId . '%')->where('examSchedule', 'like', '%' . $subjectId . '%')->get();
		if($data['term']==0){
			echo json_encode(array('examLists'=>$sheduleLists,'termLists'=>null));
		}
		else{
			$returnDatas=$this->termSelect($data['term'],"ren",$data['classId'],$id);
			echo json_encode(array('examLists'=>$sheduleLists,'termLists'=>$returnDatas));

		}
	}

	function sheduleTerms($exam_id){
		$data=\Input::get();
		ExamsList::where('id',$exam_id)->update(['sheduleTerm'=>$data['sheduleTerm']]);
		$getData=Examterms::where('exam_id',$exam_id)->where('term',$data['term'])->get();
		if(count($getData)==0)
		{
			$examterms=new Examterms();
			$examterms->exam_id=$exam_id;
			$examterms->term=$data['term'];
			$examterms->save();
		}
		$returnDatas=$this->termSelect($data['term'],"ren",$data['classId'],$data['subjectId']);
		echo json_encode($returnDatas);
	}

	function termSelect($id,$argu,$classId,$subjectId){
		$returnDatas= array(); $i=0;
		$termGets=Examterms::where('term',$id)->get();
		$classId=json_encode($classId,true);
		$subjectId=json_encode($subjectId,true);
		foreach($termGets as $item)
		{
			$getExams=ExamsList::where('id',$item['exam_id'])->where('sheduleTerm',1)->where('examClasses', 'like', '%' . $classId . '%')->where('examSchedule', 'like', '%' . $subjectId . '%')->first();

			if($getExams){
				$returnDatas[$i]=$getExams;
				$i++;
			}
		}
		if($argu=="ren"){ return $returnDatas; }
		echo json_encode($returnDatas);
	}

}