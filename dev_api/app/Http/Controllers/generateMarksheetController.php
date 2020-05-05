<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models2\Examterms;
use App\Models2\ExamsList;
use App\Models2\exam_marks;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\MarkSheet;
use App\Models2\MarkSheetStudents;
use App\Models2\SchoolTerm;
use App\Models2\SubSubject;
use App\Models2\Subject;
use App\Models2\Section;
use App\Models2\User;

use App\Jobs\markSheetGenerator;

use Illuminate\Support\Facades\Auth;
use Session;

class generateMarksheetController extends Controller
{
    var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

    public function __construct()
    {
		if(app('request')->header('Authorization') != "" || \Input::has('token')) $this->middleware('jwt.auth');
		else $this->middleware('authApplication');

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) return \Redirect::to('/');
    }

    public function preLoad()
    {
        $role = \Auth::user()->role;
        if( !$this->panelInit->can( array("genMarksheet.list", "genMarksheet.view", "genMarksheet.create", "genMarksheet.edit", "genMarksheet.delete") )) { exit; }

        if( $role == "parent" ) { exit; }
        $dbTerms = SchoolTerm::select('id', 'title')->get()->toArray();
        $terms = [];
        foreach( $dbTerms as $oneTerm ) { $termId = intval( $oneTerm['id'] ); $terms[$termId] = $oneTerm; }
        $dbClasses = MClass::select('id', 'className as name')->get()->toArray();
		foreach( $dbClasses as $index => $class )
		{
			$class_id = $class['id'];
			$sections = Section::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
			$dbClasses[$index]['sections'] = $sections;
        }
        foreach( $dbClasses as $oneClass ) { $id = $oneClass['id']; $classes[$id] = $oneClass; }
        $examList = ExamsList::select('id', 'examTitle as name', 'examClasses', 'examSchedule', 'school_term_id as term')->where('examAcYear', $this->panelInit->selectAcYear)->get()->toArray();
		$subjects = Subject::get();
        $subSubjects = SubSubject::get();
        $mainSubjects = []; $secondarySubjects = []; $exams_list = [];
		foreach( $subjects as $subject ) { $subject_id = $subject->id; $mainSubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }
        foreach( $subSubjects as $subject ) { $subject_id = $subject->id; $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }
        foreach( $examList as $oneExam ) { $id = intval($oneExam['id']); $exams_list[$id] = $oneExam; }
        foreach( $exams_list as $key => $value )
		{
			$examClasses = json_decode( $value['examClasses'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examClasses = [];
			$examSchedule = json_decode( $value['examSchedule'], true ); if( json_last_error() != JSON_ERROR_NONE ) $examSchedule = [];
			foreach( $examSchedule as $index => $schedule )
			{
                unset( $examSchedule[$index]['date'] );
                unset( $examSchedule[$index]['stDate'] );
                unset( $examSchedule[$index]['end_time'] );
                unset( $examSchedule[$index]['teachers'] );
                unset( $examSchedule[$index]['start_time'] );
                $subject_id = $schedule['subject'];
                if( !is_numeric($subject_id) )
                {
                    $examSchedule[$index]['subject_id'] = $subject_id;
                    continue;
                }
				if( array_key_exists('subject_type', $schedule) )
				{
					$subject_type = $schedule['subject_type'];
					if( $subject_type == 'main' )
					{
                        if( array_key_exists( $subject_id, $mainSubjects) )
                        {
                            $examSchedule[$index]['subject_id'] = "m_" . $subject_id;
                            $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name'];
                        }
                        elseif( array_key_exists( $subject_id, $secondarySubjects) )
                        {
                            $examSchedule[$index]['subject_id'] = "s_" . $subject_id;
                            $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name'];
                        } else { continue; }
					}
					elseif( $subject_type == 'secondary' )
					{
                        if( array_key_exists( $subject_id, $secondarySubjects) )
                        {
                            $examSchedule[$index]['subject_id'] = "s_" . $subject_id;
                            $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name'];
                        }
                        elseif( array_key_exists( $subject_id, $mainSubjects) )
                        {
                            $examSchedule[$index]['subject_id'] = "m_" . $subject_id;
                            $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name'];
                        } else { continue; }
					} else { continue; }
				}
				else
				{
                    if( array_key_exists( $subject_id, $mainSubjects) )
                    {
                        $examSchedule[$index]['subject_id'] = "m_" . $subject_id;
                        $examSchedule[$index]['name'] = $mainSubjects[$subject_id]['name'];
                    }
                    elseif( array_key_exists( $subject_id, $secondarySubjects) )
                    {
                        $examSchedule[$index]['subject_id'] = "s_" . $subject_id;
                        $examSchedule[$index]['name'] = $secondarySubjects[$subject_id]['name'];
                    } else { continue; }
				}
            }
            foreach( $examClasses as $index => $examClass ) { $examClasses[$index] = intval( $examClass ); }
            $exams_list[$key]['examClasses'] = $examClasses;
            $exams_list[$key]['examSchedule'] = $examSchedule;
        }
        foreach( $terms as $key => $oneTerm )
        {
            $terms[$key]['classes'] = $classes;
        }
        foreach( $terms as $key => $oneTerm )
        {
            $termId = intval( $oneTerm['id'] );
            foreach( $oneTerm['classes'] as $classId => $oneClass )
            {
                $class_id = $oneClass['id'];
                foreach( $exams_list as $examId => $oneExam )
                {
                    $examClasses = $oneExam['examClasses'];
                    if( ( $termId == intval( $oneExam['term'] ) ) && in_array( $class_id, $examClasses ) )
                    {
                        $terms[$key]['classes'][$classId]['exams'][$examId] = $oneExam;
                    }
                }

            }
            $terms[$key]['choosen'] = false;
            $terms[$key]['customName'] = $oneTerm['title'];
        }
        $toReturn = array();
        $toReturn['classes'] = $classes;
        $toReturn['terms'] = $terms;
        return $toReturn;
    }

    public function index()
    {
        User::$withoutAppends = true;
        $role = \Auth::user()->role;
        $toReturn = array();
        $terms = SchoolTerm::select('id', 'title')->get()->toArray();
        $output = [];
        foreach( $terms as $key => $term )
        {
            $output[$term['id']] = [
                'id' => $term['id'],
                'title' => $term['title'],
                'choosen' => false
            ];
        }
        $toReturn['terms'] = $output;
        $classes = MClass::select("*");
        $classes = $classes->get()->toArray();
        if( \Input::has('classId') && \Input::get('classId') != 0 )
        {
            $sections = Section::select("*");
            $sections = $sections->where('classId', \Input::get('classId'));
            $sections = $sections->get()->toArray();
            $students = User::getStudentsByClass( \Input::get('classId') );
        }
        elseif( \Input::has('sectionId') && \Input::get('sectionId') != 0 )
        {
            $sections = [];
            $students = User::getStudentsBySection( \Input::get('sectionId') );
        } else { $sections = []; $students = []; }
        $outClass = []; $outSections = []; $outStudents = [];
        foreach( $classes as $class ) { $outClass[] = ['id' => $class['id'], 'name' => $class['className']]; }
        foreach( $sections as $section ) { $outSections[] = ['id' => $section['id'], 'name' => $section['sectionName']]; }
        foreach( $students as $student ) { $outStudents[] = ['id' => $student['id'], 'name' => $student['fullName']]; }
        $toReturn['classes'] = $outClass;
        $toReturn['sections'] = $outSections;
        $toReturn['students'] = $outStudents;

        $outClass = [];
        foreach( $classes as $class ) { $outClass[] = ['id' => $class['id'], 'name' => $class['className']]; }
        $exams = ExamsList::select('*');
        // $exams = $exams->where('examAcYear',$this->panelInit->selectAcYear);
        if( \Input::has('classId') && \Input::get('classId') != 0 ) { $exams = $exams->where('examClasses', 'LIKE', '%"' . \Input::get('classId') . '"%'); }
        $exams = $exams->get()->toArray();
        $finalExams = []; $tSubjectIds = [];
        foreach( $exams as $exam )
        {
            $subjectIds = [];
            $schedules = json_decode( $exam['examSchedule'], true );
            if( json_last_error() != JSON_ERROR_NONE ) $schedules = [];
            foreach( $schedules as $schedule )
            {
                if( !in_array($schedule['subject'], $subjectIds) ) $subjectIds[] = $schedule['subject'];
                if( !in_array($schedule['subject'], $tSubjectIds) ) $tSubjectIds[] = $schedule['subject'];
            }
            $subjects = Subject::select('id', 'subjectTitle as name');
            $subjects = $subjects->whereIn('id', $subjectIds);
            $subjects = $subjects->get()->toArray();
            $finalExams[$exam['school_term_id']][$exam['id']] = [
                'id' => $exam['id'],
                'name' => $exam['examTitle'],
                'pass' => $exam['main_pass_marks'],
                'max' => $exam['main_max_marks'],
                'classes' => json_decode( $exam['examClasses'], true ),
                // 'schedule' => $schedules,
                'subjects' => $subjects
            ];
        }
        $tSubjects = Subject::select('id', 'subjectTitle as name');
        $tSubjects = $tSubjects->whereIn('id', $tSubjectIds);
        $tSubjects = $tSubjects->get()->toArray();
        $outSubjects = [];
        foreach( $tSubjects as $keey => $subject )
        {
            $outSubjects[ $subject['id'] ] = [
                'id' => $subject['id'],
                'name' => $subject['name']
            ];
        }
        $toReturn['classes'] = $outClass;
        $toReturn['exams'] = $finalExams;
        $toReturn['subjects'] = $outSubjects;
        return $toReturn;
    }

    public function create()
    {
        $selections = \Input::get('selections');
        $classId = \Input::get('class'); $sectionId = \Input::get('section');
        $getSelections = json_decode($selections, true);
        $userId = $this->data['users']->id;
        $selectAcYear = $this->panelInit->selectAcYear;
        dispatch( new markSheetGenerator( $userId, $classId, $sectionId, $getSelections, $selectAcYear ) );
        return $this->panelInit->apiOutput(true, 'Generate Sheet', "Sheet generation success");
    }

    public function deleteSheet()
    {
        if( !$this->panelInit->can( array("genMarksheet.delete") )) { return $this->panelInit->apiOutput(false, "Delete Sheet", "You don't have permission to delete report card" ); }
        if( !\Input::has('sheetId') ) { return $this->panelInit->apiOutput(false, "Delete Sheet", "Unable to load sheet data" ); }
        $sheetId = \Input::get('sheetId');
        $sheet = MarkSheet::find( $sheetId );
        if( !$sheet ) { return $this->panelInit->apiOutput(false, "Delete Sheet", "Unable to load sheet data" ); }
        $studentSheets = MarkSheetStudents::where('marksheet_id', $sheetId)->delete();
        $sheet->delete();
        return $this->panelInit->apiOutput(true, "Delete Sheet", "Sheet deleted successfully" );
    }

    public function deleteStudent()
    {
        if( !$this->panelInit->can( array("genMarksheet.delete") )) { return $this->panelInit->apiOutput(false, "Delete Sheet", "You don't have permission to delete report card" ); }
        if( !\Input::has('sheetId') ) { return $this->panelInit->apiOutput(false, "Delete Sheet", "Unable to load sheet data" ); }
        $sheetId = \Input::get('sheetId');
        $sheet = MarkSheetStudents::find( $sheetId );
        if( !$sheet ) { return $this->panelInit->apiOutput(false, "Delete Sheet", "Unable to load sheet data" ); }
        $sheet->delete();
        return $this->panelInit->apiOutput(true, "Delete Sheet", "Sheet deleted successfully" );
    }

    public function mySheets()
    {
        User::$withoutAppends = true;
        $toReturn = array();
        $role = \Auth::user()->role;
        if( $role == "teacher" ) { return $this->panelInit->apiOutput(false, 'Not authorized', 'You are not authorized to show sheets' ); }
        elseif( $role == "admin" )
        {
            if( \Input::has('sheet_id') ) { $mySheets = MarkSheetStudents::where('marksheet_id', \Input::get('sheet_id'))->get(); $type = "marks"; }
            else { $mySheets = MarkSheet::get(); $type = "sheets"; }
        }
        elseif( $role == "parent" )
        {
            $parent_id = $this->data['users']->id;
            $studets = User::getStudentsIdsFromParentId($parent_id);
            if( is_array( $studets ) && count( $studets ) )
            {
                foreach( $studets as $key => $studentId ) { $studets[$key] = intval($studentId); }
            } else $studets = [];
            $mySheets = MarkSheetStudents::whereIn('student_id', $studets)->get();
            if( \Input::has('mySheets') ) { dd($mySheets->toArray()); }
            $sheets = [];
            $type = "marks";
            foreach( $mySheets as $oneSheet )
            {
                $sheets[] = [
                    'id' => $oneSheet->id,
                    'name' => $oneSheet->MarkSheet ? $oneSheet->MarkSheet->sheetName : "",
                    'studentName' => $oneSheet->User ? $oneSheet->User->fullName : "",
                    'sheetId' => $oneSheet->marksheet_id
                ];
            }
            $toReturn['status'] = "success";
            $toReturn['type'] = $type;
            $toReturn['sheets'] = $sheets;
            return $toReturn;
        }
        $sheets = [];
        if( $role == "admin" && !\Input::has('sheet_id') )
        {
            foreach( $mySheets as $oneSheet )
            {
                $sheets[] = [
                    'id' => $oneSheet->id,
                    'name' => $oneSheet->sheetName
                ];
            }
        }
        else
        {
            foreach( $mySheets as $oneSheet )
            {
                $sheets[] = [
                    'id' => $oneSheet->id,
                    'name' => $oneSheet->MarkSheet ? $oneSheet->MarkSheet->sheetName : "",
                    'studentName' => $oneSheet->User ? $oneSheet->User->fullName : "",
                    'sheetId' => $oneSheet->marksheet_id
                ];
            }
        }
        $toReturn['status'] = "success";
        $toReturn['type'] = $type;
        $toReturn['sheets'] = $sheets;
        return $toReturn;
    }

    public function readMyCard()
    {
        User::$withoutAppends = true;
        $sheetId = \Input::get('sheetId');
        $mySheet = MarkSheetStudents::find( $sheetId );
        if( !$mySheet ) { return $this->panelInit->apiOutput(false, 'Report card', 'Unable to read sheet data' ); }
        if( !$mySheet->MarkSheet ) { return $this->panelInit->apiOutput(false, 'Report card', 'Unable to read sheet data' ); }
        $sheet = $mySheet->MarkSheet;
        $basicPayload = json_decode( $sheet->payload, true );
        $studentPayload = json_decode( $mySheet->payload, true );
        $studentId = $mySheet->student_id;
        
        $getStudent = User::find($studentId);
        $class = MClass::find( $getStudent->studentClass )->className;
        $sect = Section::find( $getStudent->studentSection )->sectionName;
        $name = $getStudent->fullName;
        $claSec = $class . " " . $sect;
        $birthDay = $getStudent->birthday || $getStudent->birthday != 0 ? date("F jS, Y", $getStudent->birthday) : "";
        $father = json_decode( $getStudent->father_info, true );
        $fatherName = json_last_error() != JSON_ERROR_NONE ? "" : $father['name'];
        $mother = json_decode( $getStudent->mother_info, true );
        $motherName = json_last_error() != JSON_ERROR_NONE ? "" : $mother['name'];
        $address = $getStudent->address;
        $rollId = $getStudent->studentRollId;
        $admission = $getStudent->admission_number;
        $cbse = '';
        $contact = $getStudent->phoneNo ? $getStudent->phoneNo : $getStudent->mobileNo;

        if( array_key_exists("topRight", $basicPayload) )
        {
            $school = trim( $basicPayload['topRight'][0]['school'] ) ? $basicPayload['topRight'][0]['school'] : "";
            $title = trim( $basicPayload['topRight'][0]['title'] ) ? $basicPayload['topRight'][0]['title'] : "";
        } else { $school = ""; $title = ""; }
        if( array_key_exists("header", $basicPayload) )
        {
            foreach( $basicPayload['header']['rows'] as $index => $row )
            {
                foreach( $row['payload'] as $key => $cell )
                {
                    if( isset($cell['type']) )
                    {
                        $name = $cell['type'] == "totalCell" ? "Total" : ( $cell['type'] == "averageCell" ? "Average" : "grade" );
                        $basicPayload['header']['rows'][$index]['payload'][$key]['name'] =  $name;
                    }
                }
            }
        }
        if( array_key_exists("body", $studentPayload) )
        {
            foreach( $studentPayload['body']['rows'] as $index => $row )
            {
                $newCells = [];
                foreach( $row['cells'] as $termId => $group )
                {
                    foreach( $group as $oneCell )
                    {
                        $newCells[] = $oneCell;
                    }
                }
                $studentPayload['body']['rows'][$index]['cells'] = [];
                $studentPayload['body']['rows'][$index]['cells'] = $newCells;
            }
        }

        if( array_key_exists("footer", $studentPayload) )
        {
            foreach( $studentPayload['footer']['rows'] as $index => $row )
            {
                foreach( $row['cells'] as $cellIndex => $items )
                {
                    if( $items['type'] != "editable" ) continue(2);
                    if( !array_key_exists('value', $items) ) { $studentPayload['footer']['rows'][$index]['cells'][$cellIndex]['value'] = "Promoted"; }
                }
            }
        }
        $topLeft = [];
        $topLeft['name'] = in_array('name', $basicPayload['topLeft']) ? $name : "";
        $topLeft['class'] = in_array('class', $basicPayload['topLeft']) ? $claSec : "";
        $topLeft['dob'] = in_array('dob', $basicPayload['topLeft']) ? $birthDay : "";
        $topLeft['father'] = in_array('father', $basicPayload['topLeft']) ? $fatherName : "";
        $topLeft['mother'] = in_array('mother', $basicPayload['topLeft']) ? $motherName : "";
        $topLeft['address'] = in_array('address', $basicPayload['topLeft']) ? $address : "";
        $topLeft['roll'] = in_array('roll', $basicPayload['topLeft']) ? $rollId : "";
        $topLeft['addmission'] = in_array('addmission', $basicPayload['topLeft']) ? $admission : "";
        $topLeft['contact'] = in_array('contact', $basicPayload['topLeft']) ? $contact : "";
        $toReturn = array();
        $toReturn['status'] = "success";
        $sheetPayload['right'] = ['school' => $school, 'title' => $title];
        $sheetPayload['left'] = $topLeft;
        $sheetPayload['header'] = $basicPayload['header']['rows'];
        $sheetPayload['body'] = $studentPayload['body']['rows'];
        $sheetPayload['footer'] = $studentPayload['footer']['rows'];
        $toReturn['sheet'] = $sheetPayload;
        return $toReturn;
    }

    function getGrade( $grades, $needle )
    {
        foreach( $grades as $gradeItem )
        {
            $gradeType = $gradeItem['type'];
            $role_1 = $gradeItem['role_1'];
            $role_2 = $gradeItem['role_2'];
            $grade = $gradeItem['grade'];
            if( $gradeType == "gt" ) { if( $needle > $role_1 ) { return $grade; } }
            if( $gradeType == "gte" ) { if( $needle >= $role_1 ) { return $grade; } }
            if( $gradeType == "et" ) { if( $needle == $role_1 ) { return $grade; } }
            if( $gradeType == "lte" ) { if( $needle <= $role_1 ) { return $grade; } }
            if( $gradeType == "lt" ) { if( $needle < $role_1 ) { return $grade; } }
            if( $gradeType == "bt" ) { if( $needle >= $role_1 && $needle <= $role_2 ) { return $grade; } }
        }
        return "";
    }
}