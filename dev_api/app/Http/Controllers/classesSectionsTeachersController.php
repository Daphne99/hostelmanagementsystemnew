<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models2\Main;
use App\Models2\User;
use App\Models2\MClass;
use App\Models2\Section;

class classesSectionsTeachersController extends Controller
{
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
		if( !isset( $this->data['users']->id ) ) { return \Redirect::to('/'); }
    }
    
    public function preLoad()
    {
        User::$withoutAppends = true;
        if( !$this->panelInit->can( "classes.list" ) ) { return $this->panelInit->apiOutput( false, "Classed List", "you don't have permission to list classes" ); }
        $current_role = $this->data['users']->role;
        $toReturn = array();
        $teachersList = array();
        $teachers = User::select('id', 'fullName as name')->where('role', 'teacher')->get()->toArray();
        foreach($teachers as $teacher) { $teachersList[$teacher['id']] = ['id' => $teacher['id'], 'name' => $teacher['name']]; }
        $toReturn['teachers'] = $teachers;
        $toReturn['teachersList'] = $teachersList;
        $classes = Mclass::where('classAcademicYear', $this->panelInit->selectAcYear);
        if( $current_role == "parent" )
        {
            $parentId = $this->data['users']->id;
            $class_id = Main::getClassesIdsByParentId($parentId);
            $classes = $classes->whereIn('id', $class_id);
        }
        $classes = $classes->get()->toArray();
        $classesArray = array();
        foreach( $classes as $class )
        {
            $sections = []; $sectionsCount = 0;
            if( $this->panelInit->can( "sections.list" ) )
            {
                $getSections = Section::where('classId', $class['id'])->get()->toArray();
                foreach( $getSections as $section )
                {
                    $teachersArray = []; $teachersCount = 0;
                    $teachersIds = json_decode( $section['classTeacherId'], true );
                    if( json_last_error() != JSON_ERROR_NONE ) $teachersIds = [];
                    foreach( $teachersIds as $teacherId )
                    {
                        if( array_key_exists( $teacherId, $teachersList ) )
                        {
                            $teachersArray[] = [ 'id' => $teacherId, 'name' => $teachersList[$teacherId]['name'] ];
                            $teachersCount = $teachersCount + 1;
                            $sectionsCount = $sectionsCount + 1;
                        }
                    }

                    $sections[] = [
                        'id' => $section['id'],
                        'name' => $section['sectionName'],
                        'teachers' => $teachersArray,
                        'count' => $teachersCount == 0 ? 1 : $teachersCount
                    ];
                }
            }
            $classesArray[] = [
                'id' => $class['id'],
                'name' => $class['className'],
                'sections' => $sections,
                'count' => $sectionsCount == 0 ? 1 : $sectionsCount
            ];
        }
        $output = [];
        foreach( $classesArray as $oneClass )
        {
            $index = 0;
            foreach( $oneClass['sections'] as $oneSection )
            {
                $key = 0;
                foreach( $oneSection['teachers'] as $oneTeacher )
                {
                    $output[] = [
                        'classId' => $oneClass['id'],
                        'className' => $oneClass['name'],
                        'classCount' => $oneClass['count'],
                        'firstClass' => $index == 0 && $key == 0 ? true : false,
                        'sectionId' => $oneSection['id'],
                        'sectionName' => $oneSection['name'],
                        'firstSection' => $key == 0 ? true : false,
                        'sectionCount' => $oneSection['count'],
                        'teacherId' => $oneTeacher['id'],
                        'teacherName' => $oneTeacher['name'],
                    ];
                    $key++;
                }
                $index++;
            }
        }
        $toReturn['status'] = "success";
        $toReturn['classes'] = $classesArray;
        return $toReturn;
    }

    public function create()
    {
        User::$withoutAppends = true;
        if( !$this->panelInit->can( "classes.addClass" ) ) { return $this->panelInit->apiOutput( false, "Create class", "You don't have permission to add class" ); }
        if( !$this->panelInit->can( "sections.addSection" ) ) { return $this->panelInit->apiOutput( false, "Create class", "You don't have permission to add class sections" ); }
        if( !\Input::has('classesName') ) { return $this->panelInit->apiOutput( false, "Create class", "Class name is missing" ); }
        if( !trim( \Input::get('classesName') ) ) { return $this->panelInit->apiOutput( false, "Create class", "Class name is missing" ); }
        $className = trim( \Input::get('classesName') );
        if( !\Input::has('sections') ) { return $this->panelInit->apiOutput( false, "Create class", "at least one section must be defined" ); }
        $classTeachers = [];
        foreach( \Input::get('sections') as $section )
        {
            $sectionName = trim( $section['name'] );
            if( !$sectionName ) continue;
            $teachers = $section['teachers'];
            foreach( $teachers as $teacherId )
            {
                $teacherId = (string) "$teacherId";
                if( !in_array($teacherId, $classTeachers) ) { $classTeachers[] = $teacherId; }
            }
        }
        $classSubjects = [];
        $class = new MClass();
        $class->className = $className;
        $class->classTeacher = json_encode( $classTeachers );
        $class->classAcademicYear = $this->panelInit->selectAcYear;
        $class->classSubjects = json_encode( $classSubjects );
        $class->dormitoryId = 0;
        $class->save();
        $classId = $class->id;
        user_log('Classes', 'create', $class->className);
        $sectionIds = [];
        foreach( \Input::get('sections') as $section )
        {
            $sectionName = trim( $section['name'] );
            if( !$sectionName ) continue;
            $teachers = $section['teachers'];
            $classTeacherId = [];
            foreach( $teachers as $oneTeacher ) { $classTeacherId[] = (string) "$oneTeacher"; }

            $section = new Section();
            $section->sectionName = $sectionName;
            $section->sectionTitle = $sectionName;
            $section->classId = $classId;
            $section->classTeacherId = json_encode( $classTeacherId );
            $section->save();
            $sectionId = $section->id;
            if( !in_array( $sectionId, $sectionIds ) ) { $sectionIds[] = $sectionId; }
        }
        $teachersList = array();
        $teachers = User::select('id', 'fullName as name')->where('role', 'teacher')->get()->toArray();
        foreach($teachers as $teacher) { $teachersList[$teacher['id']] = ['id' => $teacher['id'], 'name' => $teacher['name']]; }
        $getSections = Section::whereIn('id', $sectionIds)->get()->toArray();

        $sections = []; $sectionsCount = 0;
        foreach( $getSections as $section )
        {
            $teachersArray = []; $teachersCount = 0;
            $teachersIds = json_decode( $section['classTeacherId'], true );
            if( json_last_error() != JSON_ERROR_NONE ) $teachersIds = [];
            foreach( $teachersIds as $teacherId )
            {
                if( array_key_exists( $teacherId, $teachersList ) )
                {
                    $teachersArray[] = [ 'id' => $teacherId, 'name' => $teachersList[$teacherId]['name'] ];
                    $teachersCount = $teachersCount + 1;
                    $sectionsCount = $sectionsCount + 1;
                }
            }

            $sections[] = [
                'id' => $section['id'],
                'name' => $section['sectionName'],
                'teachers' => $teachersArray,
                'count' => $teachersCount == 0 ? 1 : $teachersCount
            ];
        }
        $output = [
            'id' => $classId,
            'name' => $className,
            'sections' => $sections,
            'count' => $sectionsCount == 0 ? 1 : $sectionsCount
        ];
        return $this->panelInit->apiOutput( true, "Create class", "Class created successfully", $output );
    }

    public function remove()
    {
        if( !$this->panelInit->can( "classes.delClass" ) ) { return $this->panelInit->apiOutput( false, "Remove class", "You don't have permission to remove class" ); }
        if( !\Input::has('class_id') ) { return $this->panelInit->apiOutput( false, "Remove class", "Selected class not found" ); }
        $classId = \Input::get('class_id');
        $class = MClass::find( $classId );
        if( !$class ) { return $this->panelInit->apiOutput( false, "Remove class", "Selected class not found" ); }
        $sections = Section::where('classId', $classId)->delete();
        user_log('Classes', 'delete', $class->className);
        $class->delete();
        return $this->panelInit->apiOutput( true, "Remove class", "Class removed successfully" );
    }

    public function update()
    {
        $sectionIds = [];
        User::$withoutAppends = true;
        if( !$this->panelInit->can( "classes.editClass" ) ) { return $this->panelInit->apiOutput( false, "Update class", "You don't have permission to edit class" ); }
        if( !$this->panelInit->can( "sections.editSection" ) ) { return $this->panelInit->apiOutput( false, "Update class", "You don't have permission to edit class sections" ); }
        if( !\Input::has('class_id') ) { return $this->panelInit->apiOutput( false, "Update class", "Selected class not found" ); }
        $classId = \Input::get('class_id');
        $class = MClass::find( $classId );
        if( !$class ) { return $this->panelInit->apiOutput( false, "Update class", "Selected class not found" ); }
        if( !\Input::has('classesName') ) { return $this->panelInit->apiOutput( false, "Update class", "Class name is missing" ); }
        if( !trim( \Input::get('classesName') ) ) { return $this->panelInit->apiOutput( false, "Update class", "Class name is missing" ); }
        $className = trim( \Input::get('classesName') );
        if( !\Input::has('sections') ) { return $this->panelInit->apiOutput( false, "Update class", "At least one section must be defined" ); }
        $currentSections = Section::where('classId', $classId)->pluck('id')->toArray();
        $sections = \Input::get('sections');
        $classTeachers = [];
        foreach( $sections as $index => $section )
        {
            $sectionName = trim( $section['name'] );
            if( !$sectionName ) continue;
            
            $classTeacherId = [];
            $teachers = $section['teachers'];
            foreach( $teachers as $oneTeacher )
            {
                $teacherId = (string) "$oneTeacher";
                if( !in_array( $teacherId, $classTeacherId ) ) { $classTeacherId[] = $teacherId; }
                if( !in_array( $teacherId, $classTeachers ) ) { $classTeachers[] = $teacherId; }
            }
            $sections[$index]['teachers'] = $classTeacherId;
            
            if( array_key_exists( 'id', $section ) )
            {
                $sectionId = $section['id'];
                $currentSections = array_diff( $currentSections, ["$sectionId"] );
                $getSection = Section::find( $sectionId );
                $getSection->sectionName = $sectionName;
                $getSection->sectionTitle = $sectionName;
                $getSection->classTeacherId = json_encode( $classTeacherId );
                $getSection->save();
            }
            unset( $sections[$index]['typeahead'] );
            unset( $sections[$index]['visibleTeachers'] );
        }
        
        Section::whereIn('id', $currentSections)->delete();
        foreach( $sections as $section )
        {
            $sectionName = trim( $section['name'] );
            if( !$sectionName ) continue;
            if( array_key_exists( 'id', $section ) ) { 
                $sectionId = $section['id'];
                if( !in_array( $sectionId, $sectionIds ) ) { $sectionIds[] = $sectionId; }
                continue;
            }
            else
            {
                $classTeacherId = $section['teachers'];
                $section = new Section();
                $section->sectionName = $sectionName;
                $section->sectionTitle = $sectionName;
                $section->classId = $classId;
                $section->classTeacherId = json_encode( $classTeacherId );
                $section->save();
                $sectionId = $section->id;
                if( !in_array( $sectionId, $sectionIds ) ) { $sectionIds[] = $sectionId; }
            }
        }

        $class->className = $className;
        $class->classTeacher = json_encode( $classTeachers );
        $class->save();

        $teachersList = array();
        $teachers = User::select('id', 'fullName as name')->where('role', 'teacher')->get()->toArray();
        foreach($teachers as $teacher) { $teachersList[$teacher['id']] = ['id' => $teacher['id'], 'name' => $teacher['name']]; }
        $getSections = Section::whereIn('id', $sectionIds)->get()->toArray();

        $sectionsToOutput = []; $sectionsCount = 0;
        foreach( $getSections as $section )
        {
            $teachersArray = []; $teachersCount = 0;
            $teachersIds = json_decode( $section['classTeacherId'], true );
            if( json_last_error() != JSON_ERROR_NONE ) $teachersIds = [];
            foreach( $teachersIds as $teacherId )
            {
                if( array_key_exists( $teacherId, $teachersList ) )
                {
                    $teachersArray[] = [ 'id' => $teacherId, 'name' => $teachersList[$teacherId]['name'] ];
                    $teachersCount = $teachersCount + 1;
                    $sectionsCount = $sectionsCount + 1;
                }
            }

            $sectionsToOutput[] = [
                'id' => $section['id'],
                'name' => $section['sectionName'],
                'teachers' => $teachersArray,
                'count' => $teachersCount == 0 ? 1 : $teachersCount
            ];
        }
        $output = [
            'id' => $classId,
            'name' => $className,
            'sections' => $sectionsToOutput,
            'count' => $sectionsCount == 0 ? 1 : $sectionsCount
        ];
        return $this->panelInit->apiOutput( true, "Update class", "Class updated successfully", $output );
    }
}