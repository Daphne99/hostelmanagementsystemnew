<?php
namespace App\Http\Controllers;

use App\Models2\SchoolTerm;

class GradeLevelsController extends Controller {

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

	public function listAll()
	{

		if(!$this->panelInit->can( array("gradeLevels.list","gradeLevels.addLevel","gradeLevels.editGrade","gradeLevels.delGradeLevel") )){
			exit;
		}

		$toReturn = array();

		$toReturn['terms'] = SchoolTerm::get();
		$toReturn['grades'] = \grade_levels::get();
		return $toReturn;

		return \grade_levels::get();
	}

	public function delete($id){

		if(!$this->panelInit->can( "gradeLevels.delGradeLevel" )){
			exit;
		}

		if ( $postDelete = \grade_levels::where('id', $id)->first() ){
				user_log('Grade levels', 'delete', $postDelete->gradeName);
        $postDelete->delete();
        return $this->panelInit->apiOutput(true,$this->panelInit->language['delGradeLevel'],$this->panelInit->language['gradeDeleted']);
    }else{
        return $this->panelInit->apiOutput(false,$this->panelInit->language['delGradeLevel'],$this->panelInit->language['gradeNotExist']);
    }
	}

	public function create(){

		if(!$this->panelInit->can( "gradeLevels.addLevel" )){
			exit;
		}

		$gradeLevels = new \grade_levels();
		$gradeLevels->gradeName = \Input::get('gradeName');
		$gradeLevels->gradeDescription = \Input::get('gradeDescription');
		$gradeLevels->gradePoints = \Input::get('gradePoints');
		$gradeLevels->gradeFrom = \Input::get('gradeFrom');
		$gradeLevels->gradeTo = \Input::get('gradeTo');
		$gradeLevels->school_term_id = \Input::get('term_id');
		$gradeLevels->save();

		user_log('Grade levels', 'create', $gradeLevels->gradeName);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addLevel'],$this->panelInit->language['gradeCreated'],$gradeLevels->toArray() );
	}

	function fetch($id){

		if(!$this->panelInit->can( "gradeLevels.editGrade" )){
			exit;
		}

		$data = \grade_levels::where('id',$id)->first();
		$data['term_id'] = $data['school_term_id'];

		return $data;
	}

	function edit($id){

		if(!$this->panelInit->can( "gradeLevels.editGrade" )){
			exit;
		}

		$gradeLevels = \grade_levels::find($id);
		$gradeLevels->gradeName = \Input::get('gradeName');
		$gradeLevels->gradeDescription = \Input::get('gradeDescription');
		$gradeLevels->gradePoints = \Input::get('gradePoints');
		$gradeLevels->gradeFrom = \Input::get('gradeFrom');
		$gradeLevels->gradeTo = \Input::get('gradeTo');
		$gradeLevels->school_term_id = \Input::get('term_id');
		$gradeLevels->save();

		user_log('Grade levels', 'edit', $gradeLevels->gradeName);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editGrade'],$this->panelInit->language['gradeUpdated'],$gradeLevels->toArray() );
	}
}
