<?php
namespace App\Http\Controllers;

use App\Models2\ClassSchedule;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\SubSubject;
use App\Models2\Subject;
use App\Models2\SubjectVideo;
use App\Models2\User;

class SubjectsController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct()
	{
		if( app('request')->header('Authorization') != "" || \Input::has('token') ) { $this->middleware('jwt.auth'); }
		else { $this->middleware('authApplication'); }

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) { return \Redirect::to('/'); }
	}

	public function listAll()
	{
		if(!$this->panelInit->can( array("Subjects.list","Subjects.addSubject","Subjects.editSubject","Subjects.delSubject") )) { exit; }

		$toReturn = array();
		$query = \DB::table('subject');
		$query->where('subjectTitle', '!=', 'Break');

		// get current subjects
		if( $this->data['users']->role == "parent" )
		{
			$students_ids = User::getStudentsIdsFromParentId($this->data['users']->id);
			$classes_ids = Main::getClassesIdsOfStudentsIds($students_ids);
			$subjects_ids = Main::getSubjectsIdsByClassesIds($classes_ids);
			$query = $query->whereIn('subject.id', $subjects_ids);
		}
		elseif( $this->data['users']->role == "teacher" )
		{
			$classes_ids = Main::getClassesIdsByTeacherId($this->data['users']->id);
			$subjects_ids = Main::getSubjectsIdsByClassesIds($classes_ids);
			$query = $query->whereIn('subject.id', $subjects_ids);
		}
		$toReturn['subjects'] = $query->get();

		// get current teachers
		$teachers = \User::where('role','teacher')->select('id','fullName')->get()->toArray();
		foreach ($teachers as $value) { $toReturn['teachers'][$value['id']] = $value; }

		foreach ($toReturn['subjects'] as $key => $subject) {
			// filter particalr subjects -----------
			if($this->data['users']->role == "parent")
			{
				$teacher_ids = ClassSchedule::where('subjectId', $subject->id)
					->whereIn('sectionId', Main::getSectionIdsByParentId($this->data['users']->id))
					->distinct()
					->pluck('teacherId')
					->toArray();
				$toReturn['subjects'][$key]->teacherId = $teacher_ids;
			} else { $toReturn['subjects'][$key]->teacherId = Main::getTeacherIdsBySubjectId($subject->id); }
		}
		return $toReturn;
	}

	public function delete($id)
	{
		if(!$this->panelInit->can( "Subjects.delSubject" )) { exit; }
		if ( $postDelete = \subject::where('id', $id)->first() )
		{
    		user_log('Subjects', 'delete', $postDelete->subjectTitle);
        	$postDelete->delete();
        	return $this->panelInit->apiOutput(true,$this->panelInit->language['delSubject'],$this->panelInit->language['subjectDel']);
		} else { return $this->panelInit->apiOutput(false,$this->panelInit->language['delSubject'],$this->panelInit->language['subjectNotExist']); }
	}

	public function create()
	{
		if(!$this->panelInit->can( "Subjects.addSubject" )) { exit; }
		if( \Input::get('isSub') )
		{
			$subjectName = trim( \Input::get('subjectTitle') );
			$checkForName = SubSubject::where('subjectTitle', $subjectName)->first();
			if( $checkForName ) { return $this->panelInit->apiOutput(false, $this->panelInit->language['addSubject'], "Subject name already exsists please choose different name" ); }
			$subject = new SubSubject;
			$subject->subjectTitle = $subjectName;
			$subject->passGrade = \Input::has('passGrade') ? \Input::get('passGrade') : "00";
			$subject->finalGrade = \Input::has('finalGrade') ? \Input::get('finalGrade') : "00";
			$subject->save();
		}
		else
		{
			$subjectName = trim( \Input::get('subjectTitle') );
			$checkForName = Subject::where('subjectTitle', $subjectName)->first();
			if( $checkForName ) { return $this->panelInit->apiOutput(false, $this->panelInit->language['addSubject'], "Subject name already exsists please choose different name" ); }

			$subject = new \subject();
			$subject->subjectTitle = $subjectName;
			$subject->passGrade = \Input::has('passGrade') ? \Input::get('passGrade') : "00";
			$subject->finalGrade = \Input::has('finalGrade') ? \Input::get('finalGrade') : "00";
			$subject->save();
		}
		user_log('Subjects', 'create', $subject->subjectTitle);
		return $this->panelInit->apiOutput(true,$this->panelInit->language['addSubject'],$this->panelInit->language['subjectCreated'],$subject->toArray() );
	}

	function fetch($id)
	{
		if(!$this->panelInit->can( "Subjects.editSubject" )) { exit; }
		$subject = \subject::where('id',$id)->first()->toArray();
		$subject['teacherId'] = Main::getTeacherIdsBySubjectId($id);
		return $subject;
	}

	function edit($id)
	{
		if(!$this->panelInit->can( "Subjects.editSubject" )) { exit; }
		$subject = \subject::find($id);
		$subject->subjectTitle = \Input::get('subjectTitle');
		if(\Input::get('teacherId')) { $subject->teacherId = json_encode(\Input::get('teacherId')); }
		if( \Input::has('passGrade') ) { $subject->passGrade = \Input::get('passGrade'); }
		if( \Input::has('finalGrade') ) { $subject->finalGrade = \Input::get('finalGrade'); }
		$subject->save();
		user_log('Subjects', 'edit', $subject->subjectTitle);
		return $this->panelInit->apiOutput(true,$this->panelInit->language['editSubject'],$this->panelInit->language['subjectEdited'],$subject->toArray() );
	}
}