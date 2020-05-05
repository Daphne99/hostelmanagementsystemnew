<?php

namespace App\Models2;

use App\Models2\ClassSchedule;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\Section;
use App\Models2\StudentAcademicYear;
use App\Models2\academic_year;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Watson\Rememberable\Rememberable;

class User extends Authenticatable {
	use Rememberable;
	public $timestamps = false;
	public $customPermissionsDecoded = "";
	protected $table = 'users';
	protected $hidden = array('password');
	public $appends = ['sections', 'classes', 'user_log_info', 'teacher_availability_info'];

	//---------------------------------------------------------

	public static $withoutAppends = false;
	protected function getArrayableAppends() {
    if(self::$withoutAppends){
        return [];
    }
    return parent::getArrayableAppends();
	}

	public function mclass() {
		return $this->belongsTo(MClass::class, 'studentClass', 'id');
	}

	public function section() {
		return $this->belongsTo(Section::class, 'studentSection', 'id');
	}

	public function transport() {
		return $this->belongsTo(Transportation::class, 'transport', 'id');
	}

	public function transport_vehicle() {
		return $this->belongsTo(TransportVehicle::class, 'transport_vehicle', 'id');
	}

	//---------------------------------------------------------

	public function getSectionsAttribute() {
		$section_ids = Main::getSectionIdsByTeacherId($this->attributes['id']);
		$data = Section::whereIn('id', $section_ids)->get()->toArray();
		return $data;
	}

	public function getClassesAttribute() {
		$class_ids = Main::getClassesIdsByTeacherId($this->attributes['id']);
		$data = MClass::whereIn('id', $class_ids)->get()->toArray();
		return $data;
	}

	public function getUserLogInfoAttribute() {
		$user_info = '';

		if($this->attributes['id'] > 0) {
			if(isset($this->attributes['username'])) {
				$username = ' / ' . $this->attributes['username'];
			} else {
				$username = '';
			}
			$user_info .= '<b style="font-size: 13px; font-weight: 500">' . $this->attributes['fullName'] . $username . '</b>';
			if(isset($this->attributes['role'])) {
				$user_info .= '<br>';
				$user_info .= '<b style="font-size: 13px">' . 'Role: ' . $this->attributes['role'] . '</b>';
			}
		}

		return $user_info;
	}

	public function getTeacherAvailabilityInfoAttribute() {
		$info = '';

		if($this->attributes['id'] > 0) {
			$info .= '<b style="font-size: 13px; font-weight: 500">' . $this->attributes['fullName'] . '</b><br>';
			if(isset($this->attributes['username'])) {
				$info .= '<b style="font-size: 13px;">' . $this->attributes['username'] . '</b><br>';
			}
			$info .= '<b style="font-size: 13px;">Emp ID: ' . $this->attributes['id'] . '</b>';
		}

		return $info;
	}

	//---------------------------------------------------------

	public static function getRealParentIdsFromStudentId($student_id) {
		$parent_ids = [];
		$query = self::where('role', 'parent')
			->select('id')
		  ->where('parentOf', 'LIKE', '%"id":' . $student_id . '%');

		if($query->count()) {
			$parent_ids = $query->pluck('id');
		}

		return $parent_ids;
	}

	public static function getParentIdsFromStudentId($student_id) {
		$parent_ids = [];
		$query = self::where('role', 'parent')
			->select('id')
		  ->where('parentOf', 'LIKE', '%"id":' . $student_id . '%');

		if($query->count()) {
			$parent_ids = $query->get('id')->toArray();
		}

		return $parent_ids;
	}

	public static function getParentIdsFromStudentsIds($students_ids) {
		$parent_ids = [];
		if( count( $students_ids ) == 0 ) return [];
		$query = self::select('id')
			->where('role', 'parent')
			->Where(function ($query) use( $students_ids ) {
				foreach($students_ids as $student_id ) { $query->orwhere('parentOf', 'LIKE', '%"id":' . $student_id . '%'); }
		   });
		if($query->count()) {
			$parent_ids = $query->pluck('id');
		}

		return $parent_ids;
	}

	public static function getParentIdsFromSectionId($section_id) {
		$students = self::select('id');
		$students = $students->remember(60 * 4);
		$students = $students->where('studentSection', $section_id);
		$students = $students->pluck('id');
		$students_ids = [];
		foreach( $students as $student ) $students_ids[] = $student;
		
		$parent_ids = [];
		if( count( $students_ids ) == 0 ) return [];
		$query = self::select('id')->remember(60 * 4)
			->where('role', 'parent')
			->Where(function ($query) use( $students_ids ) {
				foreach($students_ids as $student_id ) { $query->orwhere('parentOf', 'LIKE', '%"id":' . $student_id . '%'); }
		   });
		if($query->count()) {
			$parent_ids = $query->pluck('id');
		}

		return $parent_ids;
	}

	public static function getStudentsIdsFromParentId($parent_id) {
		$student_ids = [];
		$students_array = json_decode(self::find($parent_id)->parentOf);
		if(count($students_array)) {
			foreach ($students_array as $key => $value) {
				$student_ids[] = $value->id;
			}
		}
		return $student_ids;
	}

	public static function getStudentIdsOfTeacherByTeacherId($teacher_id) {
		$classes_ids = Main::getClassesIdsByTeacherId($teacher_id);
		$student_ids = self::getStudentsIdsFromClassesIds($classes_ids);
		return $student_ids;
	}

	public static function getTeachersIdsWhoseTeachingForStudentsIds($students_ids) {
		$classesSectionsIds = self::whereIn('id', $students_ids)->select('id', 'studentClass', 'studentSection')->get()->toArray();
		$teacher_ids = [];

		foreach ($classesSectionsIds as $key => $collection) {
			$query = ClassSchedule::where([
				// 'classId' => $collection['studentClass'],
				'sectionId' => $collection['studentSection']
			]);
			if($query->count()) {
				$teacher_ids = array_merge($teacher_ids, $query->pluck('teacherId')->toArray());
			}
		}

		return $teacher_ids;
	}

	public static function getTeachersIdsForStudentsIds($students_ids) {
		$classesSectionsIds = self::whereIn('id', $students_ids)->select('id', 'studentSection')->get()->toArray();
		$teacher_ids = [];

		foreach ($classesSectionsIds as $key => $collection) {
			$query = ClassSchedule::where([
				// 'classId' => $collection['studentClass'],
				'sectionId' => $collection['studentSection']
			]);
			if($query->count()) {
				$schedules = $query->get()->toArray();
				foreach( $schedules as $data )
				{
					if( !$data['teacherId'] ) continue;
					if( !in_array( $data['teacherId'], $teacher_ids) )
					{
						$teacher_ids[] = $data['teacherId'];
					}
				}
			}
		}

		return $teacher_ids;
	}

	public static function getStudentsIdsFromClassesIds($classes_ids) {
		$ids = [];
		$default_academic_year_id = academic_year::where('isDefault','1')->first()->id;

		foreach ($classes_ids as $key => $class_id) {
			$query = StudentAcademicYear::where('academicYearId', $default_academic_year_id)
			  ->where('classId', $class_id);

			if($query->count()) {
				$student_ids = $query->pluck('studentId')->toArray();
				$ids = array_merge($ids, $student_ids);
			}
		}

		return array_unique($ids);
	}

	public static function getStudentsIdsFromSectionsIds($section_ids) {
		$ids = [];
		$default_academic_year_id = academic_year::where('isDefault','1')->first()->id;

		foreach ($section_ids as $key => $section_id) {
			$query = StudentAcademicYear::where('academicYearId', $default_academic_year_id)
			  ->where('sectionId', $section_id);

			if($query->count()) {
				$student_ids = $query->pluck('studentId')->toArray();
				$ids = array_merge($ids, $student_ids);
			}
		}

		return array_unique($ids);
	}

	public static function getBusTrackInfo($student_id) {
		$info = [];

		$info = self::with('transport', 'transport_vehicle')
		  ->where('role', 'student')
		  ->where('id', $student_id)
		  ->get();

		return $info;
	}

	public static function getUserAuhtToken() {
		$current_user = Auth::guard('web')->user();

		$format = self::find($current_user->id);
		$token = JWTAuth::fromUser($format);

		return $token;
	}

	public static function get_teachers_names_by_ids($teachers_ids)
	{
		$teachersIds = self::whereIn('id', $teachers_ids)->select('id', 'fullName')->get()->toArray();
		$teachers = []; $index = 0;
		foreach( $teachersIds as $teacher ) { $teachers[$index] = ['id' => $teacher['id'], 'name' => $teacher['fullName']]; $index++; }
		return $teachers;
	}

	public static function get_teachers_ids_by_parent_id($parent_id)
	{
		$students_ids = self::getStudentsIdsFromParentId($parent_id);
		$classes = self::select('id', 'fullName', 'username', 'studentClass', 'studentSection');
		$classes = $classes->whereIn('id', $students_ids);
		$classes = $classes->distinct('id')->get()->toArray();
		$teacher_ids = [];
		foreach ($classes as $key => $collection) {
			$query = ClassSchedule::where([
				'sectionId' => $collection['studentSection']
			]);
			if($query->count()) {
				$teacher_ids[] = $query->first()->teacherId;
			}
		}

		return $teacher_ids;
	}

	public static function getStudentsByClass($class_id)
	{
		$students = self::select('id', 'fullName');
		$students = $students->remember(60 * 4);
		$students = $students->where('studentClass', $class_id);
		$students = $students->get()->toArray();
		return $students;
	}

	public static function getStudentsBySection($section_id)
	{
		$students = self::select('id', 'fullName');
		$students = $students->remember(60 * 4);
		$students = $students->where('studentSection', $section_id);
		$students = $students->get()->toArray();
		return $students;
	}

	public static function getStudentIdsBySectionId($section_id)
	{
		$students = self::select('id');
		$students = $students->remember(60 * 4);
		$students = $students->where('studentSection', $section_id);
		$students = $students->pluck('id');
		$list = [];
		foreach( $students as $student ) $list[] = $student;
		return $list;
	}

	public static function getStudentIdsByTextQuery( $keyword )
	{
		$students = self::select('id');
		$students = $students->where('fullName','LIKE',"%$keyword%");
		$students = $students->orWhere('admission_number','LIKE',"%$keyword%");
		$students = $students->orWhere('mobileNo','LIKE',"%$keyword%");
		$students = $students->orWhere('phoneNo','LIKE',"%$keyword%");
		$students = $students->pluck('id');
		$list = [];
		foreach( $students as $student ) $list[] = $student;
		return $list;
	}
}