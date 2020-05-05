<?php
namespace App\Http\Controllers;

use App\Models2\ClassSchedule;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\SubSubject;
use App\Models2\Subject;
use App\Models2\User;

class SubSubjectsController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

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
		if(!$this->panelInit->can( array("Subjects.list","Subjects.addSubject","Subjects.editSubject","Subjects.delSubject") )){
			exit;
		}

		$toReturn = array();
		$toReturn['sub_subjects'] = SubSubject::get();

		return $toReturn;
	}

	public function delete($id){
		if(!$this->panelInit->can( "Subjects.delSubject" )){
			exit;
		}

		if ( $postDelete = SubSubject::where('id', $id)->first() ) {
    		user_log('Subjects', 'delete', $postDelete->subjectTitle);
        $postDelete->delete();
        return $this->panelInit->apiOutput(true,$this->panelInit->language['delSubject'],$this->panelInit->language['subjectDel']);
    }else{
        return $this->panelInit->apiOutput(false,$this->panelInit->language['delSubject'],$this->panelInit->language['subjectNotExist']);
    }
	}

	function edit($id){

		if(!$this->panelInit->can( "Subjects.editSubject" )){
			exit;
		}

		$subject = \subject::find($id);
		$subject->subjectTitle = \Input::get('subjectTitle');
		if(\Input::get('teacherId')) {
			$subject->teacherId = json_encode(\Input::get('teacherId'));
		}
		$subject->passGrade = \Input::get('passGrade');
		$subject->finalGrade = \Input::get('finalGrade');
		$subject->save();

		user_log('Subjects', 'edit', $subject->subjectTitle);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editSubject'],$this->panelInit->language['subjectEdited'],$subject->toArray() );
	}
}
