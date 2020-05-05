<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models2\academic_year;
use App\Models2\MClass;
use App\Models2\Section;
use App\Models2\User;

class promotionController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct( Request $request )
	{
		if(app('request')->header('Authorization') != "" || \Input::has('token')) { $this->middleware('jwt.auth'); }
		else { $this->middleware('authApplication'); }

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) { return \Redirect::to('/'); }
		if(!$this->panelInit->can( array("Promotion.promoteStudents") ))
		{
			if( $request->method() == "GET" ) { return \Redirect::to('/'); }
			else { return $this->panelInit->apiOutput( false, "Promotion", "you don't have permission to access student promotion" ); }
		}
	}

	public function preLoad()
	{
		$years = academic_year::get()->toArray();
		$acYears = array();
		foreach( $years as $index => $year )
		{
			$acYear = $year['id'];
			if( $year['isDefault'] == 1 ) $years[$index]['yearTitle'] = trim( $years[$index]['yearTitle'] ) . " - Default Year";
			$classes = MClass::where('classAcademicYear', $acYear)->get()->toArray();
			$classesArray = array();
			foreach( $classes as $class )
			{
				$class_id = $class['id']; $name = $class['className'];
				$sections = Section::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
				$classesArray[ $class_id ] = [ 'id' => $class_id, 'name' => $name, 'sections' => $sections ];
			}
			$years[$index]['classes'] = $classesArray;
			$acYears[$acYear] = [
				'id' => $acYear,
				'name' => $years[$index]['yearTitle'],
				'isDefault' => $year['isDefault'],
				'classes' => $classesArray
			];
		}
		$toReturn['acYears'] = $acYears;
		return $toReturn;
	}

	public function listStudents()
	{
		$toReturn = array('students'=>array(),"classes"=>array());
		if( \Input::get('selectType') == "selStudents" )
		{
			$studentIds = array();
			$studentList = \Input::get('studentInfo');

			foreach($studentList as $key => $value) { $studentIds[] = $value['id']; }

			$students = \User::whereIn('id', $studentIds)->orderBy('studentSection', 'ASC')->get();
			foreach( $students as $value )
			{
				$toReturn['students'][$value->id] = [
					"id" => $value->id,
					"fullName" => $value->fullName,
					"rollNo" => $value->studentRollId,
					"admissionNo" => $value->admission_number,
					"class" => $value->studentClass,
					"section" => $value->studentSection,
					"acYear" => $value->studentAcademicYear,
					"nextClass" => \Input::has('targetClassId') ? \Input::get('targetClassId') : '',
					"nextSection" => \Input::has('targetSectionId') ? \Input::get('targetSectionId') : '',
					"nextAcYear" => \Input::has('targetAcYear') ? \Input::get('targetAcYear') : ''
				];
			}
		}
		else
		{
			$students = \User::where('studentAcademicYear',\Input::get('acYear'))->where('studentClass',\Input::get('classId'))->where('role','student')->where('activated',1)->orderBy('studentSection', 'ASC');
			if( \Input::has('sectionId') ) { $students = $students->where('studentSection', \Input::get('sectionId')); }
			$students = $students->get();
			foreach( $students as $value )
			{
				$toReturn['students'][$value->id] = [
					"id" => $value->id,
					"fullName" => $value->fullName,
					"rollNo" => $value->studentRollId,
					"admissionNo" => $value->admission_number,
					"class" => $value->studentClass,
					"section" => $value->studentSection,
					"acYear" => $value->studentAcademicYear,
					"nextClass" => \Input::has('targetClassId') ? \Input::get('targetClassId') : '',
					"nextSection" => \Input::has('targetSectionId') ? \Input::get('targetSectionId') : '',
					"nextAcYear" => \Input::has('targetAcYear') ? \Input::get('targetAcYear') : ''
				];
			}
		}

		$DashboardController = new DashboardController();
		$toReturn['classes'] = $DashboardController->classesList(\Input::get('acYear'));

		return $toReturn;
	}

	public function promoteNow()
	{
		$returnResponse = array();
		$promote = \Input::get('promote');
		if( count($promote) > 0 )
		{
			$studentIdList = array();
			$studentDetailsList = array();
			foreach($promote as $value) { $studentIdList[] = $value['id']; }
			$users = \User::whereIn('id',$studentIdList)->get();
			foreach($users as $value)
			{
				if(\student_academic_years::where('studentId',$value->id)->where('academicYearId',$promote[$value->id]['nextAcYear'])->count() > 0 AND \Input::get('promoType') != "graduate")
				{
					$returnResponse[] = array("id"=>$value->id,"fullName"=>$value->fullName,"status"=>"User already been in that academic year before");
				}
				else
				{
					if( \Input::get('promoType') != "graduate" )
					{
						$studentAcademicYears = new \student_academic_years();
						$studentAcademicYears->studentId = $value->id;
						$studentAcademicYears->academicYearId = $promote[$value->id]['nextAcYear'];
						$studentAcademicYears->classId = $promote[$value->id]['nextClass'];
						if($this->panelInit->settingsArray['enableSections'] == true){ $studentAcademicYears->sectionId = $promote[$value->id]['nextSection']; }
						$studentAcademicYears->save();
						$updateArray = array('studentClass'=>$promote[$value->id]['nextClass'],'studentAcademicYear'=>$promote[$value->id]['nextAcYear']);
						if($this->panelInit->settingsArray['enableSections'] == true){ $updateArray['studentSection'] = $promote[$value->id]['nextSection']; }
					}
					else
					{
						$User = \User::where('id',$value->id)->first();
						$updateArray = array( 'studentClass'=>-1, 'studentSection' => 0 );
					}
					\User::where('id',$value->id)->update($updateArray);
					$returnResponse[] = array("id"=>$value->id,"fullName"=>$value->fullName,"status"=>"User promoted successfully");
				}
			}
			user_log('Promotion', 'promote_users');
			return $returnResponse;
		}
	}

	public function searchStudents($student)
	{
		User::$withoutAppends = true;
		$students = User::where('role', 'student')->where( function($students) use ($student) {
			$students->orWhere('fullName','like','%'.$student.'%')->orWhere('username','like', '%'.$student.'%')->orWhere('email','like', '%'.$student.'%');
		});
		$students = $students->get();
		$retArray = array();
		foreach ($students as $student) {
			$retArray[$student->id] = array("id"=>$student->id,"name"=>$student->fullName,"email"=>$student->email, "section" => $student->studentSection);
		}
		return json_encode($retArray);
	}
}