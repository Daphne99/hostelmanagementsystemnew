<?php

namespace App\Models2\HR;
use App\Models2\Role;
use App\Models2\HR\User;
use App\Models2\HR\Branch;
use App\Models2\HR\Department;
use App\Models2\HR\Designation;
use App\Models2\HR\PayGrade;
use App\Models2\HR\HourlySalary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use carbon\carbon;

class Employee extends Model
{
    protected $table = 'hr_employee';
    protected $primaryKey = 'employee_id';
    protected $fillable = [
        'employee_id','user_id','finger_id','department_id','designation_id','branch_id','supervisor_id','work_shift_id','email','first_name',
        'last_name','date_of_birth','date_of_joining','date_of_leaving','gender','marital_status',
        'photo','address','emergency_contacts','phone','status','created_by','updated_by','religion','pay_grade_id','hourly_salaries_id'
    ];

    public static function roles()
    {
        $output = [];
        $roles = Role::all();
        foreach( $roles as $role )
        {
            $output[ $role->id ] = [ 'id' => $role->id, 'name' => $role->role_title ];
        }
        return $output;
    }

    public static function getEmployees( $page, $department_id, $designation_id, $role_id, $role, $userId, $allowanceStatus )
    {
        $employees = Employee::select('*');
        if( $department_id ) { $employees = $employees->where('department_id', $department_id); }
        if( $designation_id ) { $employees = $employees->where('designation_id', $designation_id); }
        // if( $role != "admin" ) { $employees = $employees->where('user_id', $userId); }
        if( $allowanceStatus != false )
        {
            if( $allowanceStatus == "onlyMe" ) { $employees = $employees->where('user_id', $userId); }
            elseif( $allowanceStatus == "public" ) { /* Nothing to do here  */ }
            else { $employees = $employees->where('employee_id', 0); }
        } else { $employees = $employees->where('employee_id', 0); }
        $employees = $employees->where('deleted', '0')
                            ->take(all_pagination_number())
                            ->skip(all_pagination_number() * ($page - 1) )
                            ->get();
        $output = []; $index = 0;
        $perms = self::roles();
        foreach( $employees as $employee )
        {
            if( !$employee->user ) continue;
            $userData = $employee->user;
            $roleId = $userData->role_perm;
            $academic = $userData->role;
            if( $role_id ) { if( $roleId != $role_id ) continue; }
            
            $output[$index]['employee_id'] = $employee->employee_id;
            $output[$index]['user_id'] = $employee->user_id;
            $output[$index]['finger_id'] = $employee->finger_id;
            $output[$index]['department'] = $employee->department ? $employee->department->department_name : '';
            $output[$index]['designation'] = $employee->designation ? $employee->designation->designation_name : '';
            $output[$index]['branch'] = $employee->branch ? $employee->branch->branch_name : '';
            $output[$index]['has_supervisor'] = $employee->supervisor_id ? true : false;
            if( $employee->supervisor_id )
            {
                if( !$employee->supervisor ) $output[$index]['supervisor'] = '';
                else
                {
                    $output[$index]['supervisor'] = $employee->supervisor->first_name . ' ' . $employee->supervisor->last_name;
                }

            } else $output[$index]['supervisor'] = '';
            $output[$index]['has_payGrade'] = $employee->pay_grade_id ? true : false;
            $output[$index]['has_hourlySalaries'] = $employee->hourly_salaries_id ? true : false;
            if( $employee->payGrade )
            {
                $output[$index]['paygrade_name'] = !$employee->payGrade ? '' : $employee->payGrade->pay_grade_name;
            } else $output[$index]['paygrade_name'] = '';
            if( $employee->hourlySalaries )
            {
                $output[$index]['hourly_name'] = !$employee->hourlySalaries ? '' : $employee->hourlySalaries->hourly_grade;
            } else $output[$index]['hourly_name'] = '';

            $output[$index]['email'] = $employee->email ? $employee->email : '';
            $firstName = $employee->first_name ? $employee->first_name . ' ' : '';
            $lastName = $employee->last_name ? $employee->last_name . ' ' : '';
            $firstName = trim( $firstName ); $lastName = trim( $lastName );
            $fullName = "$firstName $lastName"; $fullName = trim( $fullName );
            $output[$index]['fullName'] = $fullName;
            $output[$index]['birthday'] = $employee->date_of_birth ? $employee->date_of_birth : '';
            $output[$index]['gender'] = $employee->gender ? $employee->gender : 'Unknown';
            $output[$index]['religion'] = $employee->religion ? $employee->religion : 'Unknown';
            $output[$index]['academic_role'] = $academic == "teacher" ? "Teaching Staff" : "Non Teaching Staff";
            $output[$index]['marital_status'] = $employee->marital_status ? $employee->marital_status : 'Unknown';
            $output[$index]['date_of_joining'] = $employee->date_of_joining;
            $output[$index]['date_of_leaving'] = $employee->date_of_leaving;
            $output[$index]['joining_date'] = $employee->date_of_joining ? Carbon::parse( $employee->date_of_joining )->diffForHumans() : '';
            $output[$index]['leaving_date'] = $employee->date_of_leaving ? Carbon::parse( $employee->date_of_leaving )->diffForHumans() : '';
            $output[$index]['address'] = $employee->address ? $employee->address : '';
            $output[$index]['emergency_contacts'] = $employee->emergency_contacts ? $employee->emergency_contacts : '';
            $output[$index]['phone'] = $employee->phone ? $employee->phone : '';
            $output[$index]['profile_pic'] =  $employee->photo ? $employee->photo : '024.png';
            $output[$index]['role'] = array_key_exists( $roleId, $perms ) ? $perms[ $roleId ]['name'] : '';
            $output[$index]['status'] = $employee->status;
            $output[$index]['permanent_status'] = $employee->permanent_status == '1' || $employee->permanent_status == 1 ? 'Permanent' : 'Probation Period';
            $index++;
        }
        return $output;
        // Carbon::parse('01/01/2020')->diffForHumans();
    }

    public function user() { return $this->belongsTo(\User::class,'user_id', 'id'); }
    public function department() { return $this->belongsTo(Department::class,'department_id'); }
    public function designation() { return $this->belongsTo(Designation::class,'designation_id'); }
    public function branch() { return $this->belongsTo(Branch::class,'branch_id'); }
    public function get_created() { return $this->belongsTo(\User::class, 'created_by', 'id'); }
    public function get_updated() { return $this->belongsTo(\User::class, 'updated_by', 'id'); }
    public function payGrade() { return $this->belongsTo(PayGrade::class,'pay_grade_id'); }
    public function supervisor() { return $this->belongsTo(self::class,'supervisor_id', 'employee_id'); }
    public function role() { return $this->belongsTo(Role::class,'role_id'); }
    public function hourlySalaries() { return $this->belongsTo(HourlySalary::class,'hourly_salaries_id'); }
}