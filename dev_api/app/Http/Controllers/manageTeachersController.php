<?php

namespace App\Http\Controllers;

use App\Models2\User;
use App\Models2\Main;
use App\Models2\MClass;
use App\Models2\Section;
use App\Models2\Subject;
use App\Models2\ClassSchedule;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;

class manageTeachersController extends Controller
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
        User::$withoutAppends = true;
        $toReturn = array();
        $classes = MClass::select('id', 'className as name')->get()->toArray();
        $classesArray = [];
        foreach( $classes as $index => $class )
		{
			$class_id = $class['id'];
			$sections = Section::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
            $classesArray[$class_id] = $class;
            $classesArray[$class_id]['sections'] = $sections;
        }
        $subjects = Subject::select('id', 'subjectTitle as name')->get()->toArray();
        $teachers = User::select('id', 'fullName as name')->where('role', 'teacher')->get()->toArray();
        $toReturn['classes'] = $classesArray;
        $toReturn['subjects'] = $subjects;
        $toReturn['teachers'] = $teachers;
        return $toReturn;
    }

    public function filter()
    {
        User::$withoutAppends = true;
        $classId = trim(\Input::get('class')) ? \Input::get('class') : NULL;
        $sectionIds = is_array(\Input::get('section')) ? \Input::get('section') : [];
        $subjectId = trim(\Input::get('subject')) ? \Input::get('subject') : NULL;
        $teacherId = trim(\Input::get('teacher')) ? \Input::get('teacher') : NULL;
        
        $schedule = ClassSchedule::select('*');
        if( $classId != NULL ) $schedule = $schedule->where('classId', $classId);
        if( count( $sectionIds ) > 0 ) $schedule = $schedule->whereIn('sectionId', $sectionIds);
        if( $subjectId != NULL ) $schedule = $schedule->where('subjectId', $subjectId);
        if( $teacherId != NULL ) $schedule = $schedule->where('teacherId', $teacherId);
        $schedule = $schedule->orderBy('classId', 'ASC');
        $schedule = $schedule->orderBy('sectionId', 'ASC');
        $schedule = $schedule->get()->toArray();
        
        $tempOutPut = [];
        foreach( $schedule as $oneSchedule )
        {
            $class = $oneSchedule['classId'];
            $section = $oneSchedule['sectionId'];
            $subject = $oneSchedule['subjectId'];
            $teacher = $oneSchedule['teacherId'];
            $tempOutPut[$class][$section][$teacher][$subject] = $subject;
        }
        $classes = MClass::select('id', 'className as name')->get()->toArray();
        $sections = Section::select('id', 'sectionName as name')->get()->toArray();
        $subjects = Subject::select('id', 'subjectTitle as name')->get()->toArray();
        $teachers = User::select('id', 'fullName as name')->where('role', 'teacher')->get()->toArray();

        $classesArray = []; $sectionsArray = []; $subjectsArray = []; $teachersArray = [];
        foreach( $classes as $oneClass ) { $id = $oneClass['id']; $classesArray[$id] = $oneClass; }
        foreach( $sections as $oneSection ) { $id = $oneSection['id']; $sectionsArray[$id] = $oneSection; }
        foreach( $subjects as $oneSubject ) { $id = $oneSubject['id']; $subjectsArray[$id] = $oneSubject; }
        foreach( $teachers as $oneTeacher ) { $id = $oneTeacher['id']; $teachersArray[$id] = $oneTeacher; }
        
        $output = [];
        foreach( $tempOutPut as $classId => $oneClass )
        {
            if( !array_key_exists($classId, $output) ) 
            {
                $output[$classId] = [
                    'id' => $classId,
                    'name' => array_key_exists($classId, $classesArray) ? $classesArray[$classId]['name'] : "",
                    'sections' => []
                ];
            }
            foreach( $oneClass as $sectionId => $oneSection )
            {
                if( !array_key_exists($sectionId, $output[$classId]['sections']) ) 
                {
                    $output[$classId]['sections'][$sectionId] = [
                        'id' => $sectionId,
                        'name' => array_key_exists($sectionId, $sectionsArray) ? $sectionsArray[$sectionId]['name'] : "",
                        'teachers' => []
                    ];
                }

                foreach( $oneSection as $teacherId => $oneTeacher )
                {
                    if( !array_key_exists($teacherId, $output[$classId]['sections'][$sectionId]['teachers']) ) 
                    {
                        $output[$classId]['sections'][$sectionId]['teachers'][$teacherId] = [
                            'id' => $teacherId,
                            'name' => array_key_exists($teacherId, $teachersArray) ? $teachersArray[$teacherId]['name'] : "",
                            'subjects' => []
                        ];
                    }
                    foreach( $oneTeacher as $subjectId => $oneSubject )
                    {
                        if( !array_key_exists($subjectId, $output[$classId]['sections'][$sectionId]['teachers'][$teacherId]['subjects']) ) 
                        {
                            $output[$classId]['sections'][$sectionId]['teachers'][$teacherId]['subjects'][$subjectId] = [
                                'id' => $subjectId,
                                'name' => array_key_exists($subjectId, $subjectsArray) ? $subjectsArray[$subjectId]['name'] : ""
                            ];
                        }
                    }
                }
            }
        }

        $rows = [];
        foreach( $output as $classId => $oneClass )
        {
            $classKey = count( $rows ); $classSpan = 0;
            foreach( $oneClass['sections'] as $sectionId => $oneSection )
            {
                $sectionKey = count( $rows ); $sectionSpan = 0;
                foreach( $oneSection['teachers'] as $teacherId => $oneTeacher )
                {
                    $teacherKey = count( $rows ); $teacherSpan = 0;
                    foreach( $oneTeacher['subjects'] as $subjectsId => $oneSubject )
                    {
                        $subjectKey = count( $rows );
                        $classSpan = $classSpan + 1;
                        $sectionSpan = $sectionSpan + 1;
                        $teacherSpan = $teacherSpan + 1;
                        $subjectSpan = 1;

                        if( !array_key_exists($subjectKey, $rows) ) { $rows[$subjectKey]['subject']['span'] = $subjectSpan; $rows[$subjectKey]['subject']['name'] = $oneSubject['name']; }
                        elseif( !array_key_exists('subject', $rows[$subjectKey]) ) { $rows[$subjectKey]['subject']['span'] = $subjectSpan; $rows[$subjectKey]['subject']['name'] = $oneSubject['name']; }
                        elseif( !array_key_exists('span', $rows[$subjectKey]['subject']) ) { $rows[$subjectKey]['subject']['span'] = $subjectSpan; $rows[$subjectKey]['subject']['name'] = $oneSubject['name']; }
                        else { $rows[$subjectKey]['subject']['span'] = $subjectSpan; $rows[$subjectKey]['subject']['name'] = $oneSubject['name']; }
                    }
                    if( !array_key_exists($teacherKey, $rows) ) { $rows[$teacherKey]['teacher']['span'] = $teacherSpan; $rows[$teacherKey]['teacher']['name'] = $oneTeacher['name']; $rows[$teacherKey]['teacher']['id'] = $oneTeacher['id']; }
                    elseif( !array_key_exists('teacher', $rows[$teacherKey]) ) { $rows[$teacherKey]['teacher']['span'] = $teacherSpan; $rows[$teacherKey]['teacher']['name'] = $oneTeacher['name']; $rows[$teacherKey]['teacher']['id'] = $oneTeacher['id']; }
                    elseif( !array_key_exists('span', $rows[$teacherKey]['teacher']) ) { $rows[$teacherKey]['teacher']['span'] = $teacherSpan; $rows[$teacherKey]['teacher']['name'] = $oneTeacher['name']; $rows[$teacherKey]['teacher']['id'] = $oneTeacher['id']; }
                    else { $rows[$teacherKey]['teacher']['span'] = $teacherSpan; $rows[$teacherKey]['teacher']['name'] = $oneTeacher['name']; $rows[$teacherKey]['teacher']['id'] = $oneTeacher['id']; }
                }
                
                if( !array_key_exists($sectionKey, $rows) ) { $rows[$sectionKey]['section']['span'] = $sectionSpan; $rows[$sectionKey]['section']['name'] = $oneSection['name']; }
                elseif( !array_key_exists('section', $rows[$sectionKey]) ) { $rows[$sectionKey]['section']['span'] = $sectionSpan; $rows[$sectionKey]['section']['name'] = $oneSection['name']; }
                elseif( !array_key_exists('span', $rows[$sectionKey]['section']) ) { $rows[$sectionKey]['section']['span'] = $sectionSpan; $rows[$sectionKey]['section']['name'] = $oneSection['name']; }
                else { $rows[$sectionKey]['section']['span'] = $sectionSpan; $rows[$sectionKey]['section']['name'] = $oneSection['name']; }
                
            }
            if( !array_key_exists($classKey, $rows) ) { $rows[$classKey]['class']['span'] = $classSpan; }
            elseif( !array_key_exists('class', $rows[$classKey]) ) { $rows[$classKey]['class']['span'] = $classSpan; }
            elseif( !array_key_exists('span', $rows[$classKey]['class']) ) { $rows[$classKey]['class']['span'] = $classSpan; }
            else $rows[$classKey]['class']['span'] = $classSpan;
            $rows[$classKey]['class']['name'] = $oneClass['name'];
        }
        foreach( $rows as $rowKey => $oneRow )
        {
            if( !array_key_exists('teacher', $oneRow) ) { $rows[$rowKey]['teacher']['span'] = 0; }
            if( !array_key_exists('section', $oneRow) ) { $rows[$rowKey]['section']['span'] = 0; }
            if( !array_key_exists('class', $oneRow) ) { $rows[$rowKey]['class']['span'] = 0; }
            if( array_key_exists('teacher', $oneRow ) )
            {
                //
            }
        }
        $toReturn = array();
        $toReturn['rows'] = $rows;
        return $toReturn;
    }
}