<?php
namespace App\Http\Controllers;

use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\Subject;
use App\Models2\User;

class ClassesController extends Controller {

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
		if(!$this->panelInit->can( array("classes.list","classes.addClass","classes.editClass","classes.delClass") )){
			exit;
		}

		$toReturn = array();

		// $toReturn['dormitory'] =  \dormitories::get()->toArray();
		// $toReturn['subject'] = array();
		// $subjects =  \subject::get();
		// foreach ($subjects as $value) {
		//     $toReturn['subject'][$value->id] = $value->subjectTitle;
		// }

		$classes = \classes::select('classes.id as id', 'classes.className as className');

		if($this->data['users']->role == "teacher"){
			$class_ids = Main::getClassesIdsByTeacherId($this->data['users']->id);
			$classes = $classes->whereIn('id', $class_ids);
		} else if($this->data['users']->role == "parent"){
			$students_ids = User::getStudentsIdsFromParentId($this->data['users']->id);
			$classes_ids = Main::getClassesIdsOfStudentsIds($students_ids);
			$classes = $classes->whereIn('id', $classes_ids);
		}

		if(\Input::has('searchInput') AND isset($classes)){
			$searchInput = \Input::get('searchInput');

			if(is_string($searchInput)) {
				$searchInput = (array) json_decode($searchInput);
			}

			if(is_array($searchInput)){
				if(isset($searchInput['class']) AND $searchInput['class'] != "" ){
					$classes = $classes->where('classes.id', $searchInput['class'][0]);
				}

				if(isset($searchInput['subject']) AND $searchInput['subject'] != ""){
					$subject_id = $searchInput['subject'];
					$class_ids = Main::getClassIdsBySubjectId($subject_id);
					$classes = $classes->whereIn('classes.id', $class_ids);
				}
			}
		}

		$classes = $classes->where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();

		$teachers = \User::where('role','teacher')->select('id','fullName')->get()->toArray();
		$toReturn['teachers'] = array();
		foreach($teachers as $teacherKey => $teacherValue){
			$toReturn['teachers'][$teacherValue['id']] = $teacherValue;
		}

		$toReturn['subject_ids'] = \subject::pluck('id');
		$toReturn['subject_array'] = \subject::get()->pluck('subjectTitle', 'id')->toArray();

		$toReturn['classes'] = array();
		foreach($classes as $key => $class){
			$toReturn['classes'][$key] = $class;
			$toReturn['sections'][$key]['classSections'] = Main::getSectionsByClassId($class['id']);
			$toReturn['classes'][$key]['teachers'] = Main::getTeacherIdsByClassId($class['id']);
			$toReturn['classes'][$key]['subjects'] = Main::getSubjectIdsByClassId($class['id']);
		}

		return $toReturn;
	}

	public function delete($id){

		if(!$this->panelInit->can( "classes.delClass" )){
			exit;
		}

		if ( $postDelete = \classes::where('id', $id)->first() ) {
  		user_log('Classes', 'delete', $postDelete->className);
      $postDelete->delete();
      return $this->panelInit->apiOutput(true,$this->panelInit->language['delClass'],$this->panelInit->language['classDeleted']);
    }else{
      return $this->panelInit->apiOutput(false,$this->panelInit->language['delClass'],$this->panelInit->language['classNotExist']);
    }
	}

	public function create(){
		if(!$this->panelInit->can( "classes.addClass" )){
			exit;
		}

		$classes = new \classes();
		$classes->className = \Input::get('className');
		$classes->classAcademicYear = $this->panelInit->selectAcYear;
		if(\Input::has('classTeacher')){
			$classes->classTeacher = json_encode(\Input::get('classTeacher'));
		}
		if(\Input::has('classSubjects')){
			$classes->classSubjects = json_encode(\Input::get('classSubjects'));
		}
		if(\Input::has('dormitoryId')){
			$classes->dormitoryId = \Input::get('dormitoryId');
		}
		$classes->save();

		user_log('Classes', 'create', $classes->className);

		if(\Input::has('classTeacher') && \Input::has('classSubjects')){
			$classes->classTeacher = "";
			$teachersList = \User::whereIn('id',\Input::get('classTeacher'))->get();
			foreach ($teachersList as $teacher) {
				$classes->classTeacher .= $teacher->fullName.", ";
			}
			$classes->classSubjects = json_decode($classes->classSubjects);
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addClass'],$this->panelInit->language['classCreated'],$classes->toArray() );
	}

	function fetch($id){

		if(!$this->panelInit->can( "classes.editClass" )){
			exit;
		}

		$classDetail = \classes::where('id',$id)->first()->toArray();
		$classDetail['classTeacher'] = Main::getTeacherIdsByClassId($id);;
		$classDetail['classSubjects'] = Main::getSubjectIdsByClassId($id);
		return $classDetail;
	}

	function edit($id){

		if(!$this->panelInit->can( "classes.editClass" )){
			exit;
		}

		$classes = \classes::find($id);
		$classes->className = \Input::get('className');
		if(\Input::has('classTeacher')){
			$classes->classTeacher = json_encode(\Input::get('classTeacher'));
		}
		if(\Input::has('classSubjects')){
			$classes->classSubjects = json_encode(\Input::get('classSubjects'));
		}
		if(\Input::has('dormitoryId')){
			$classes->dormitoryId = \Input::get('dormitoryId');
		}
		$classes->save();

		user_log('Classes', 'edit', $classes->className);

		if(\Input::has('classTeacher') && \Input::has('classSubjects')){
			$classes->classTeacher = "";
			$teachersList = \User::whereIn('id',\Input::get('classTeacher'))->get();
			foreach ($teachersList as $teacher) {
				$classes->classTeacher .= $teacher->fullName.", ";
			}
			$classes->classSubjects = json_decode($classes->classSubjects);
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editClass'],$this->panelInit->language['classUpdated'],$classes->toArray() );
	}

	public function fetchAll()
	{
		$classes = \classes::leftJoin('dormitories', 'dormitories.id', '=', 'classes.dormitoryId')
					->select(
						'classes.id as id',
						'classes.className as className',
						// 'classes.classTeacher as classTeacher',
						// 'classes.classSubjects as classSubjects',
						'dormitories.id as dormitory',
						'dormitories.dormitory as dormitoryName'
					)->get()->toArray();

		foreach ($classes as $key => $class) {
			$classes[$key]['classTeacher'] = Main::getTeacherIdsByClassId($class->id);
			$classes[$key]['classSubjects'] = Main::getSubjectIdsByClassId($class->id);
		}

		return json_encode([
			"jsData" => $classes,
			"jsStatus" => "1"
		]);
	}


	public function get_class_teachers_by_class_id($class_id)
	{
		User::$withoutAppends = true;

		$classTeacherIds = Main::getTeacherIdsByClassId($class_id);
		$classTeachers = User::where('role', 'teacher')
			->select('id', 'fullName')
		  ->whereIn('id', $classTeacherIds)
		  ->get()
		  ->toArray();

		return json_encode([
			"classTeachers" => $classTeachers
		]);
	}

	public function get_subjects_by_class_id($class_id)
	{
		$subjectsIds = Main::getSubjectIdsByClassId($class_id);
		$subjects = Subject::whereIn('id', $subjectsIds)
		  ->select('id', 'subjectTitle')
		  ->get()
		  ->toArray();

	  return json_encode([
			"subjects" => $subjects,
		]);
	}

	public function getClassIdByClassName($class_name) {
		$class_id = MClass::where('className', $class_name)->first()->id;

		return response()->json([
			'class_id' => $class_id
		]);
	}

	public function bulkInsertClassesAndSubjects() {
		$subjects = request()->input('subjectNames');
		if(is_null($subjects) || empty($subjects)) { return $this->panelInit->apiOutput(false,'Invalid inputs',''); }
		$subjects_array = explode(',', $subjects);
		foreach ($subjects_array as $subject_name)
		{
			$subject = new Subject;
			$subject->subjectTitle = $subject_name;
			$subject->passGrade = 33;
			$subject->finalGrade = 100;
			$subject->save();
		}
		if( \Input::has('classNames') )
		{
			$classes = request()->input('classNames');
			$classes_array = explode(',', $classes);
			if(is_null($classes) || empty($classes)) { return $this->panelInit->apiOutput(false,'Invalid inputs',''); }
			foreach ($classes_array as $class_name) {
				$class = new MClass;
				$class->className = $class_name;
				$class->classAcademicYear = $this->panelInit->selectAcYear;
				$class->save();
			}
		}
		

		// return
		return $this->panelInit->apiOutput(true,'Success insert classes and subjects','');
	}

	public function importBulkClassesAndSubjects($type) {
		if(!$this->panelInit->can("classes.addClass") || !$this->panelInit->can("Subjects.addSubject")){
			exit;
		}

		if (\Input::hasFile('excelcsv')) {
			if ( $_FILES['excelcsv']['tmp_name'] ) {
				$temp_filename = $_FILES['excelcsv']['tmp_name'];
				$readExcel = \Excel::load($temp_filename, function($reader) {
					$reader->noHeading();
				})->toArray();

				// $classes = [];
				$subjects = [];

				foreach ($readExcel as $i => $row) {
					if(count(array_unique($row)) == 1 && array_unique($row)[0] == null) {
						continue;
					}
					if($i > 0) {
						// $classes[] = $row[0];
						$subjects[] = $row[0];
					}
				}

				// if(count($classes)) {
				// 	foreach ($classes as $class_name) {
				// 		$class = new MClass;
				// 		$class->className = $class_name;
				// 		$class->classAcademicYear = $this->panelInit->selectAcYear;
				// 		$class->save();
				// 	}
				// }

				if(count($subjects)) {
					foreach ($subjects as $subject_name) {
						$subject = new Subject;
						$subject->subjectTitle = $subject_name;
						$subject->passGrade = 33;
						$subject->finalGrade = 100;
						$subject->save();
					}
				}

				// user_log('Classes', 'import');
				user_log('Subjects', 'import');

				return $this->panelInit->apiOutput(true, 'Import timetable', 'Success import timetable');
			}
		}else{
			return json_encode(array("jsTitle"=>$this->panelInit->language['Import'],"jsStatus"=>"0","jsMessage"=>$this->panelInit->language['specifyFileToImport'] ));
			exit;
		}
		exit;
	}

	public function downloadImportSample() {
		return response()->download('storage/app/import_bulk_classes_subjects.xlsx');
	}

	public function downloadSubjectsImportSample() { return response()->download('storage/app/import_bulk_subjects.xlsx'); }
}