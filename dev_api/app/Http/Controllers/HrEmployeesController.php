<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models2\User;
use App\Models2\Role;
use App\Models2\HR\Department;
use App\Models2\HR\Designation;
use App\Models2\HR\Branch;
use App\Models2\HR\Employee;
use App\Models2\HR\Warning;
use App\Models2\HR\Promotion;
use App\Models2\HR\Termination;
use App\Models2\HR\WorkShift;
use App\Models2\HR\PayGrade;
use App\Models2\HR\HourlySalary;
use App\Models2\HR\EmployeeEducationQualification;
use App\Models2\HR\EmployeeExperience;

class HrEmployeesController extends Controller
{
    var $data = array();
	var $panelInit ;
    var $layout = 'dashboard';
    
    public function __construct()
    {
		if( app('request')->header('Authorization') != "" || \Input::has('token') ) $this->middleware('jwt.auth');
		else $this->middleware('authApplication');
        
        $this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) return \Redirect::to('/');
    }

    public function listDepartments( $page )
    {
        if( !$this->panelInit->can( "departments.list" ) )
        {
            return \Redirect::to( \URL::to('/portal#/') );
        }
        $toReturn = array();
        $getDepartments = Department::select('department_id as id', 'department_name as name');
        $getDepartments = $getDepartments->where('deleted', '0');
        $toReturn["departmentsCount"] = $getDepartments->count();
        $getDepartments = $getDepartments->take(all_pagination_number())->skip(all_pagination_number() * ($page - 1) )->get()->toArray();
        $toReturn["departments"] = $getDepartments;
        return $toReturn;
    }

    public function allDepartments()
    {
        if( !$this->panelInit->can( "departments.list" ) )
        {
            $toReturn["departments"] = [];
            return $toReturn;
        }
        $toReturn = array();
        $getDepartments = Department::select('department_id as id', 'department_name as name');
        $getDepartments = $getDepartments->where('deleted', '0');
        $getDepartments = $getDepartments->get()->toArray();
        $toReturn["departments"] = $getDepartments;
        return $toReturn;
    }

    public function createDepartment()
    {
        if( !$this->panelInit->can( "departments.add_department" ) ) { return $this->panelInit->apiOutput( false, "Add department", "You don't have permission to add departments" ); }
        if( !\Input::has('departmentName') ) { return $this->panelInit->apiOutput( false, "Add department", "Department Name is missing" ); }
        $department = new Department();
        $department->department_name = \Input::get('departmentName');
        $department->save();
        user_log('Departments', 'create', $department->department_name);
        return $this->panelInit->apiOutput( true, "Add department", "Department Created Successfully" );
    }

    public function readDepartment()
    {
        if( !$this->panelInit->can( "departments.edit_department" ) ) { return $this->panelInit->apiOutput( false, "Edit department", "You don't have permission to edit departments" ); }
        if( !\Input::has('department_id') ) { return $this->panelInit->apiOutput( false, "Edit department", "Department is not exists" ); }
        $id = \Input::get('department_id');
        $department = Department::where('department_id', $id)->first();
        if( !$department ) { return $this->panelInit->apiOutput( false, "Edit department", "Department is not exists" ); }
        $toReturn = array();
        $output = [ 'id' => $department->department_id, 'departmentName' => $department->department_name ];
        $toReturn['department'] = $output;
        return $toReturn;
    }

    public function updateDepartment()
    {
        if( !$this->panelInit->can( "departments.edit_department" ) ) { return $this->panelInit->apiOutput( false, "Edit department", "You don't have permission to edit departments" ); }
        if( !\Input::has('id') ) { return $this->panelInit->apiOutput( false, "Edit department", "Department is not exists" ); }
        $id = \Input::get('id');
        $department = Department::where('department_id', $id)->first();
        if( !$department ) { return $this->panelInit->apiOutput( false, "Edit department", "Department is not exists" ); }
        if( !\Input::has('departmentName') ) { return $this->panelInit->apiOutput( false, "Edit department", "Department name is missing" ); }
        $department->department_name = \Input::get('departmentName');
        $department->save();
        user_log('Departments', 'edit', $department->department_name);
        return $this->panelInit->apiOutput( true, "Edit department", "Department Updated Successfully" );
    }

    public function deleteDepartment()
    {
        if( !$this->panelInit->can( "departments.delete_department" ) ) { return $this->panelInit->apiOutput( false, "Delete department", "You don't have permission to delete departments" ); }
        if( !\Input::has('department_id') ) { return $this->panelInit->apiOutput( false, "Delete department", "Department is not exists" ); }
        $id = \Input::get('department_id');
        $postDelete = Department::where('department_id', $id)->first();
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete department", "Department is not exists" ); }
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Departments', 'delete', $postDelete->department_name);
        return $this->panelInit->apiOutput( true, "Delete department", "Department Deleted Successfully" );
    }

    public function listDesignations( $page )
    {
        if( !$this->panelInit->can( "designations.list" ) )
        {
            return \Redirect::to( \URL::to('/portal#/') );
        }
        $toReturn = array();
        $getDesignations = Designation::select('designation_id as id', 'designation_name as name');
        $getDesignations = $getDesignations->where('deleted', '0');
        $toReturn["designationsCount"] = $getDesignations->count();
        $getDesignations = $getDesignations->take(all_pagination_number())->skip(all_pagination_number() * ($page - 1) )->get()->toArray();
        $toReturn["designations"] = $getDesignations;
        return $toReturn;
    }

    public function createDesignation()
    {
        if( !$this->panelInit->can( "designations.add_designation" ) ) { return $this->panelInit->apiOutput( false, "Add designation", "You don't have permission to add designations" ); }
        if( !\Input::has('designationName') ) { return $this->panelInit->apiOutput( false, "Add designation", "Designation Name is missing" ); }
        $designation = new Designation();
        $designation->designation_name = \Input::get('designationName');
        $designation->save();
        user_log('Designations', 'create', $designation->designation_name);
        return $this->panelInit->apiOutput( true, "Add designation", "Designation Created Successfully" );
    }

    public function readDesignation()
    {
        if( !$this->panelInit->can( "designations.edit_designation" ) ) { return $this->panelInit->apiOutput( false, "Edit designation", "You don't have permission to edit designations" ); }
        if( !\Input::has('designation_id') ) { return $this->panelInit->apiOutput( false, "Edit designation", "Designation is not exists" ); }
        $id = \Input::get('designation_id');
        $designation = Designation::where('designation_id', $id)->first();
        if( !$designation ) { return $this->panelInit->apiOutput( false, "Edit designation", "Designation is not exists" ); }
        $toReturn = array();
        $output = [ 'id' => $designation->designation_id, 'designationName' => $designation->designation_name ];
        $toReturn['designation'] = $output;
        return $toReturn;
    }

    public function updateDesignation()
    {
        if( !$this->panelInit->can( "designations.edit_designation" ) ) { return $this->panelInit->apiOutput( false, "Edit designation", "You don't have permission to edit designations" ); }
        if( !\Input::has('id') ) { return $this->panelInit->apiOutput( false, "Edit designation", "Designation is not exists" ); }
        $id = \Input::get('id');
        $designation = Designation::where('designation_id', $id)->first();
        if( !$designation ) { return $this->panelInit->apiOutput( false, "Edit designation", "Designation is not exists" ); }
        if( !\Input::has('designationName') ) { return $this->panelInit->apiOutput( false, "Edit designation", "Designation name is missing" ); }
        $designation->designation_name = \Input::get('designationName');
        $designation->save();
        user_log('Designations', 'edit', $designation->designation_name);
        return $this->panelInit->apiOutput( true, "Edit Designation", "Designation Updated Successfully" );
    }

    public function deleteDesignation()
    {
        if( !$this->panelInit->can( "designations.delete_designation" ) ) { return $this->panelInit->apiOutput( false, "Delete designation", "You don't have permission to delete designations" ); }
        if( !\Input::has('designation_id') ) { return $this->panelInit->apiOutput( false, "Delete designation", "Designation is not exists" ); }
        $id = \Input::get('designation_id');
        $postDelete = Designation::where('designation_id', $id)->first();
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete designation", "Designation is not exists" ); }
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Designations', 'delete', $postDelete->designation_name);
        return $this->panelInit->apiOutput( true, "Delete designation", "Designation Deleted Successfully" );
    }

    public function listBranchs( $page )
    {
        if( !$this->panelInit->can( "branchs.list" ) )
        {
            return \Redirect::to( \URL::to('/portal#/') );
        }
        $toReturn = array();
        $getBranchs = Branch::select('branch_id as id', 'branch_name as name');
        $getBranchs = $getBranchs->where('deleted', '0');
        $toReturn["branchsCount"] = $getBranchs->count();
        $getBranchs = $getBranchs->take(all_pagination_number())->skip(all_pagination_number() * ($page - 1) )->get()->toArray();
        $toReturn["branchs"] = $getBranchs;
        return $toReturn;
    }

    public function createBranch()
    {
        if( !$this->panelInit->can( "branchs.add_branch" ) ) { return $this->panelInit->apiOutput( false, "Add branch", "You don't have permission to add branchs" ); }
        if( !\Input::has('branchName') ) { return $this->panelInit->apiOutput( false, "Add branch", "Branch Name is missing" ); }
        $branch = new Branch();
        $branch->branch_name = \Input::get('branchName');
        $branch->save();
        user_log('Branchs', 'create', $branch->branch_name);
        return $this->panelInit->apiOutput( true, "Add branch", "Branch Created Successfully" );
    }

    public function readBranch()
    {
        if( !$this->panelInit->can( "branchs.edit_branch" ) ) { return $this->panelInit->apiOutput( false, "Edit branch", "You don't have permission to edit branchs" ); }
        if( !\Input::has('branch_id') ) { return $this->panelInit->apiOutput( false, "Edit Branch", "Branch is not exists" ); }
        $id = \Input::get('branch_id');
        $branch = Branch::where('branch_id', $id)->first();
        if( !$branch ) { return $this->panelInit->apiOutput( false, "Edit Branch", "Branch is not exists" ); }
        $toReturn = array();
        $output = [ 'id' => $branch->branch_id, 'branchName' => $branch->branch_name ];
        $toReturn['branch'] = $output;
        return $toReturn;
    }

    public function updateBranch()
    {
        if( !$this->panelInit->can( "branchs.edit_branch" ) ) { return $this->panelInit->apiOutput( false, "Edit Branch", "You don't have permission to edit branchs" ); }
        if( !\Input::has('id') ) { return $this->panelInit->apiOutput( false, "Edit Branch", "Branch is not exists" ); }
        $id = \Input::get('id');
        $branch = Branch::where('branch_id', $id)->first();
        if( !$branch ) { return $this->panelInit->apiOutput( false, "Edit Branch", "Branch is not exists" ); }
        if( !\Input::has('branchName') ) { return $this->panelInit->apiOutput( false, "Edit Branch", "Branch name is missing" ); }
        $branch->branch_name = \Input::get('branchName');
        $branch->save();
        user_log('Branchs', 'edit', $branch->branch_name);
        return $this->panelInit->apiOutput( true, "Edit Branch", "Branch Updated Successfully" );
    }

    public function deleteBranch()
    {
        if( !$this->panelInit->can( "branchs.delete_branch" ) ) { return $this->panelInit->apiOutput( false, "Delete Branch", "You don't have permission to delete branchs" ); }
        if( !\Input::has('branch_id') ) { return $this->panelInit->apiOutput( false, "Delete Branch", "Branch is not exists" ); }
        $id = \Input::get('branch_id');
        $postDelete = Branch::where('branch_id', $id)->first();
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete Branch", "Branch is not exists" ); }
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Branchs', 'delete', $postDelete->branch_name);
        return $this->panelInit->apiOutput( true, "Delete Branch", "Branch Deleted Successfully" );
    }

    public function listEmployees( $page )
    {
        if( !$this->panelInit->can( array("employees.list", "employees.myProfile") ) ) { return \Redirect::to( \URL::to('/portal#/') ); }
        if( \Input::has('department') )
        {
            $department_id = \Input::get('department');
            if( $department_id == 0 || $department_id == '0' ) { $department_id = NULL; }
        } else $department_id = NULL;
        if( \Input::has('designation') )
        {
            $designation_id = \Input::get('designation');
            if( $designation_id == 0 || $designation_id == '0' ) { $designation_id = NULL; }
        } else $designation_id = NULL;
        if( \Input::has('designation') )
        {
            $role_id = \Input::get('role');
            if( $role_id == 0 || $role_id == '0' ) { $role_id = NULL; }
        } else $role_id = NULL;

        $role = $this->data['users']->role;
        $userId = $this->data['users']->id;
        $allowanceStatus = false;
        if( $this->panelInit->can( "employees.list" ) )
        {
            $allowanceStatus = "public";
        } elseif( $this->panelInit->can( "employees.myProfile" ) ) { $allowanceStatus = "onlyMe"; }
        
        $getEmployees = Employee::getEmployees( $page, $department_id, $designation_id, $role_id, $role, $userId, $allowanceStatus );
        $toReturn = array();
        $toReturn["employees"] = $getEmployees;
        $toReturn["totalEmployees"] = Employee::where('deleted', '0')->count();
        $toReturn["departments"] = Department::select('department_id as id', 'department_name as name')->where('deleted', '0')->get()->toArray();
        $toReturn["designations"] = Designation::select('designation_id as id', 'designation_name as name')->where('deleted', '0')->get()->toArray();
        $toReturn["roles"] = Role::select('id', 'role_title as name')->get()->toArray();
        $toReturn["supervisors"] = Employee::select('employee_id as id', 'first_name', 'last_name')->get()->toArray();
        $toReturn["branchs"] = Branch::select('branch_id as id', 'branch_name as name')->where('deleted', '0')->get()->toArray();
        $toReturn["shifts"] = WorkShift::select('work_shift_id as id', 'shift_name as name')->where('deleted', '0')->get()->toArray();
        $toReturn["paygrades"] = PayGrade::select('pay_grade_id as id', 'pay_grade_name as name')->where('deleted', '0')->get()->toArray();
        $toReturn["hourlySalaries"] = HourlySalary::select('hourly_salaries_id as id', 'hourly_grade as name')->where('deleted', '0')->get()->toArray();
        return $toReturn;
    }

    public function createEmployee()
    {
        if( !$this->panelInit->can( "employees.add_employee" ) ) { return $this->panelInit->apiOutput( false, "Create Employee", "You don't have permission to create employees" ); }
        if( !\Input::has('role_id') ) return $this->panelInit->apiOutput( false, "Create Employee", "Role is missing" );
        if( !\Input::has('username') ) return $this->panelInit->apiOutput( false, "Create Employee", "Username is missing" );
        if( !\Input::has('password') ) return $this->panelInit->apiOutput( false, "Create Employee", "Password is missing" );
        if( !\Input::has('academic_role') ) return $this->panelInit->apiOutput( false, "Create Employee", "Academic Role is missing" );
        if( !\Input::has('first_name') ) return $this->panelInit->apiOutput( false, "Create Employee", "First Name is missing" );
        if( !\Input::has('finger') ) return $this->panelInit->apiOutput( false, "Create Employee", "Fingerprint No. is missing" );
        if( !\Input::has('department_id') ) return $this->panelInit->apiOutput( false, "Create Employee", "Department Name is missing" );
        if( !\Input::has('designation_id') ) return $this->panelInit->apiOutput( false, "Create Employee", "Designation Name is missing" );
        if( !\Input::has('shift_id') ) return $this->panelInit->apiOutput( false, "Create Employee", "Work Shift is missing" );
        if( !\Input::has('paygrade_id') ) return $this->panelInit->apiOutput( false, "Create Employee", "Monthly Pay Grade is missing" );
        if( !\Input::has('salary_id') ) return $this->panelInit->apiOutput( false, "Create Employee", "Hourly Pay Grade is missing" );
        if( !\Input::has('phone') ) return $this->panelInit->apiOutput( false, "Create Employee", "Phone is missing" );
        if( !\Input::has('birthday') ) return $this->panelInit->apiOutput( false, "Create Employee", "Date Of Birth is missing" );
        if( !\Input::has('joinday') ) return $this->panelInit->apiOutput( false, "Create Employee", "Date Of Joining is missing" );
        if( !\Input::has('status') ) return $this->panelInit->apiOutput( false, "Create Employee", "Account status is missing" );
        if( \Input::has('eduCount') )
        {
            $eduCount = \Input::get('eduCount');
            if( $eduCount > 0 )
            {
                for( $i = 0; $i < $eduCount; $i++ )
                {
                    $institute = "education_" . $i . "_institute";
                    if( !\Input::has($institute) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of educational institute is missing" );
                    if( !trim( \Input::get($institute) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of education institute is missing" );
                    $university = "education_" . $i . "_university";
                    if( !\Input::has($university) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of educational board / university name is missing" );
                    if( !trim( \Input::get($university) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of educational board / university name is missing" );
                    $degree = "education_" . $i . "_degree";
                    if( !\Input::has($degree) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of educational degree is missing" );
                    if( !trim( \Input::get($degree) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of educational degree is missing" );
                    $passingYear = "education_" . $i . "_passingYear";
                    if( !\Input::has($passingYear) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of educational passing year is missing" );
                    if( !trim( \Input::get($passingYear) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of educational passing year is missing" );
                }
            }
        }

        if( \Input::has('proffCount') )
        {
            $proffCount = \Input::get('proffCount');
            if( $proffCount > 0 )
            {
                for( $i = 0; $i < $proffCount; $i++ )
                {
                    $organization = "profession_" . $i . "_organization";
                    if( !\Input::has($organization) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences organization name is missing" );
                    if( !trim( \Input::get($organization) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences organization name is missing" );
                    $designation = "profession_" . $i . "_designation";
                    if( !\Input::has($designation) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences designation name is missing" );
                    if( !trim( \Input::get($designation) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences designation name is missing" );
                    $from = "profession_" . $i . "_from";
                    if( !\Input::has($from) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences start date is missing" );
                    if( !trim( \Input::get($from) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences start date is missing" );
                    $to = "profession_" . $i . "_to";
                    if( !\Input::has($to) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences leave date is missing" );
                    if( !trim( \Input::get($to) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences leave date is missing" );
                    $responsibility = "profession_" . $i . "_responsibility";
                    if( !\Input::has($responsibility) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences responsibility is missing" );
                    if( !trim( \Input::get($responsibility) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences responsibility is missing" );
                    $skill = "profession_" . $i . "_skill";
                    if( !\Input::has($skill) ) return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences skill is missing" );
                    if( !trim( \Input::get($skill) ) )  return $this->panelInit->apiOutput( false, "Create Employee", "One of professional experiences skill is missing" );
                }
            }
        }
        
        $role_id = \Input::get('role_id');
        $username = \Input::get('username');
        $password = \Input::get('password');
        $first_name = \Input::get('first_name');
        $last_name = \Input::has('last_name') ? \Input::get('last_name') : NULL;
        $finger = \Input::get('finger');
        $supervisor_id = \Input::has('supervisor_id') ? ( trim( \Input::get('supervisor_id') ) ? \Input::get('supervisor_id') : NULL ) : NULL;
        $department_id = \Input::get('department_id');
        $designation_id = \Input::get('designation_id');
        $branch_id = \Input::has('branch_id') ? ( trim( \Input::get('branch_id') ) ? \Input::get('branch_id') : NULL ) : NULL;
        $shift_id = \Input::has('shift_id') ? ( trim( \Input::get('shift_id') ) ? \Input::get('shift_id') : NULL ) : NULL;
        $paygrade_id = \Input::has('paygrade_id') ? ( trim( \Input::get('paygrade_id') ) ? \Input::get('paygrade_id') : NULL ) : NULL;
        $salary_id = \Input::has('salary_id') ? ( trim( \Input::get('salary_id') ) ? \Input::get('salary_id') : NULL ) : NULL;
        $email = \Input::has('email') ? \Input::get('email') : NULL;
        $phone = \Input::get('phone');
        $gender = \Input::has('gender') ? strtolower( \Input::get('gender') ) : NULL;
        $religion = \Input::has('religion') ? \Input::get('religion') : NULL;
        $birthday = \Input::get('birthday');
        $joinday = \Input::get('joinday');
        $leaveday = \Input::has('leaveday') ? \Input::get('leaveday') : NULL;
        $status = \Input::get('status');
        $marital = \Input::has('marital') ? \Input::get('marital') : NULL;
        $address = \Input::has('address') ? \Input::get('address') : NULL;
        $emergency = \Input::has('emergency') ? \Input::get('emergency') : NULL;

        $username_validate = User::where('username', $username)->first();
        if( $username_validate ) return $this->panelInit->apiOutput( false, "Create employee", "Username already exists please try different username and try again" );
        if( \Input::has('email') )
        {
            $user_mail_check = User::where('email', $email)->first();
            if( $user_mail_check ) return $this->panelInit->apiOutput( false, "Create employee", "Email already exists please try different email and try again" );
            $employee_mail_check = Employee::where('email', $email)->first();
            if( $employee_mail_check ) return $this->panelInit->apiOutput( false, "Create employee", "Email already exists please try different email and try again" );
        }
        $check_finger = Employee::where('finger_id', $finger)->first();
        if( $check_finger )  return $this->panelInit->apiOutput( false, "Create employee", "Fingerprint no. already exists please try different fingerprint no. and try again" );
        
        $check_role = Role::where('id', $role_id)->first();
        if( !$check_role )  return $this->panelInit->apiOutput( false, "Create employee", "Role not exists please try different role and try again");

        if( $supervisor_id )
        {
            $supervisor_check = Employee::where('employee_id', $supervisor_id)->first();
            if( !$supervisor_check ) return $this->panelInit->apiOutput( false, "Create employee", "Supervisor not exists please try different supervisor and try again");
        }
        $check_department = Department::where('department_id', $department_id)->first();
        if( !$check_department ) return $this->panelInit->apiOutput( false, "Create employee", "Department not exists please try different department and try again");
        
        $check_designation = Designation::where('designation_id', $designation_id)->first();
        if( !$check_designation ) return $this->panelInit->apiOutput( false, "Create employee", "Designation not exists please try different designation and try again");
        
        if( $branch_id )
        {
            $check_branch = Branch::where('branch_id', $branch_id)->first();
            if( !$check_branch ) return $this->panelInit->apiOutput( false, "Create employee", "Branch not exists please try different branch and try again");
        }

        if( $shift_id )
        {
            $check_workShift = WorkShift::where('work_shift_id', $shift_id)->first();
            if( !$check_workShift ) return $this->panelInit->apiOutput( false, "Create employee", "Work shift not exists please try different shift and try again");
        }

        if( $paygrade_id )
        {
            $check_payGrade = PayGrade::where('pay_grade_id', $paygrade_id)->first();
            if( !$check_payGrade ) return $this->panelInit->apiOutput( false, "Create employee", "Monthly pay grade not exists please try different pay grade and try again");
        }

        if( $salary_id )
        {
            $check_hourlySalary = HourlySalary::where('hourly_salaries_id', $salary_id)->first();
            if( !$check_hourlySalary ) return $this->panelInit->apiOutput( false, "Create employee", "Hourly pay grade not exists please try different pay grade and try again");
        }

        if( \Input::hasFile('photo') )
        {
			$fileInstance = \Input::file('photo');
            if( !$this->panelInit->validate_upload( $fileInstance ) )
            {
				return $this->panelInit->apiOutput(false, "Create employee", "Sorry, This File Type Is Not Permitted For Security Reasons");
            }
        }

        $fullName = trim( $first_name );
        if( \Input::has('last_name') ) $fullName = $fullName . ' ' . $last_name;
        $user_gender = $gender != '' ? ( $gender == 'male' ? 'Male' : 'Female' ) : NULL;
        $account_active = $status == "Active" ? "1" : "0";
        $systemUser = new User();
        $systemUser->username = $username;
        if( $email ) { $systemUser->email = $email; }
        $systemUser->fullName = $fullName;
        $systemUser->password = \Hash::make( $password );
        $systemUser->role = \Input::get('academic_role') == "teacher" ? "teacher" : "employee";
        $systemUser->mobileNo = $phone;
        $systemUser->comVia = json_encode( ["mail","sms","phone"] );
        $systemUser->account_active = $account_active;
        $systemUser->role_perm = $role_id;
        $systemUser->birthday = toUnixStamp( $birthday );
        $systemUser->gender = $user_gender;
        $systemUser->photo = NULL;
        $systemUser->save();
        $userId = $systemUser->id;
        
        if( \Input::hasFile('photo') )
        {
			$fileInstance = \Input::file('photo');
			$newPhotoName = "profile_" . $userId . ".jpg";
            $getUser = User::find( $userId );
            if( $getUser )
            {
                $file = $fileInstance->move(uploads_config()['uploads_file_path'] . '/profile/', $newPhotoName);
                $getUser->photo = "profile_" . $userId . ".jpg";
                $getUser->save();
            }
        } else $newPhotoName = "024.png";
        user_log('User', 'create', $systemUser->fullName);
        
        if( toUnixStamp( $birthday ) )
        {
            $db_birthday = date('Y-m-d', toUnixStamp( $birthday ));
        } else $db_birthday = $birthday;
        
        if( toUnixStamp( $joinday ) )
        {
            $db_joinday = date('Y-m-d', toUnixStamp( $joinday ));
        } else $db_joinday = $joinday;

        if( $leaveday )
        {
            if( toUnixStamp( $leaveday ) )
            {
                $db_leaveday = date('Y-m-d', toUnixStamp( $leaveday ));
            } else $db_leaveday = $leaveday;
        } else $db_leaveday = NULL;

        $employeeUser = new Employee();
        $employeeUser->user_id = $userId;
        $employeeUser->finger_id = $finger;
        $employeeUser->department_id = $department_id;
        $employeeUser->designation_id = $designation_id;
        $employeeUser->branch_id = $branch_id ? $branch_id : "";
        $employeeUser->supervisor_id = $supervisor_id ? $supervisor_id : "";
        $employeeUser->work_shift_id = $shift_id ? $shift_id : "";
        $employeeUser->pay_grade_id = $paygrade_id ? $paygrade_id : "";
        $employeeUser->hourly_salaries_id = $salary_id ? $salary_id : "";
        $employeeUser->email = $email;
        $employeeUser->first_name = $first_name;
        $employeeUser->last_name = $last_name;
        $employeeUser->date_of_birth = $db_birthday;
        $employeeUser->date_of_joining = $db_joinday;
        $employeeUser->date_of_leaving = $db_leaveday;
        $employeeUser->gender = $user_gender;
        $employeeUser->religion = $religion;
        $employeeUser->marital_status = $marital;
        $employeeUser->photo = $newPhotoName;
        $employeeUser->address = $address;
        $employeeUser->emergency_contacts = $emergency;
        $employeeUser->phone = $phone;
        $employeeUser->status = $account_active;
        $employeeUser->permanent_status = '1';
        $employeeUser->created_by = \Auth::user()->id;
        $employeeUser->updated_by = \Auth::user()->id;
        $employeeUser->deleted = '0';
        $employeeUser->save();
        $employeeId = $employeeUser->employee_id;
        user_log('Employees', 'create', trim( $employeeUser->first_name . " " . $employeeUser->last_name ) );

        if( \Input::has('eduCount') )
        {
            $eduCount = \Input::get('eduCount');
            if( $eduCount > 0 )
            {
                for( $i = 0; $i < $eduCount; $i++ )
                {
                    $institute = "education_" . $i . "_institute";
                    $university = "education_" . $i . "_university";
                    $degree = "education_" . $i . "_degree";
                    $passingYear = "education_" . $i . "_passingYear";
                    $result = "education_" . $i . "_result";
                    $gpa = "education_" . $i . "_gpa";
                    
                    $dbEducation = new EmployeeEducationQualification();
                    $dbEducation->employee_id = $employeeId;
                    $dbEducation->institute = \Input::get( $institute );
                    $dbEducation->board_university = \Input::get( $university );
                    $dbEducation->degree = \Input::get( $degree );
                    if( \Input::has($result) ) { if( trim( \Input::get($result) ) ) { $dbEducation->result = \Input::get( $result ); } }
                    if( \Input::has($gpa) ) { if( trim( \Input::get($gpa) ) ) { $dbEducation->cgpa = \Input::get( $gpa ); } }
                    $dbEducation->passing_year = date('Y', toUnixStamp( \Input::get( $passingYear ) ));
                    $dbEducation->save();
                }
            }
        }

        if( \Input::has('proffCount') )
        {
            $proffCount = \Input::get('proffCount');
            if( $proffCount > 0 )
            {
                for( $i = 0; $i < $proffCount; $i++ )
                {
                    $organization = "profession_" . $i . "_organization";
                    $designation = "profession_" . $i . "_designation";
                    $from = "profession_" . $i . "_from";
                    $to = "profession_" . $i . "_to";
                    $responsibility = "profession_" . $i . "_responsibility";
                    $skill = "profession_" . $i . "_skill";
                    $profession = new EmployeeExperience();
                    $profession->employee_id = $employeeId;
                    $profession->organization_name = \Input::get( $organization );
                    $profession->designation = \Input::get( $designation );
                    $profession->from_date = date('Y-m-d', toUnixStamp( \Input::get( $from ) ));
                    $profession->to_date = date('Y-m-d', toUnixStamp( \Input::get( $to ) ));
                    $profession->skill = \Input::get( $responsibility );
                    $profession->responsibility = \Input::get( $skill );
                    $profession->save();
                }
            }
        }
        return $this->panelInit->apiOutput( true, "Create employee", "Employee Created Successfully" );
    }

    public function readEmployee()
    {
        if( !$this->panelInit->can( "employees.view_employee" ) ) { return $this->panelInit->apiOutput( false, "View Employee", "You don't have permission to View employees" ); }
        if( !\Input::has('employee_id') ) { return $this->panelInit->apiOutput( false, "View Employee", "Employee is not exists" ); }
        $id = \Input::get('employee_id');
        $employee = Employee::where('employee_id', $id)->first();
        if( !$employee ) { return $this->panelInit->apiOutput( false, "View Employee", "Employee is not exists" ); }
        $user = User::find( $employee->user_id );
        if( !$user ) { return $this->panelInit->apiOutput( false, "View Employee", "Employee is not exists" ); }

        $toReturn = array();
        $statusEmp = intval( $employee->status );
        $employeeStatus = $statusEmp == 1 ? "Active" : ( $statusEmp == 2 ? "Inactive" : ( $statusEmp == 3 ? "Terminate" : "" ) );

        $output['id'] = $id;
        $output['user_id'] = $employee->user_id;
        $output['username'] = $user->username;
        $output['academic_role'] = $user->role == "employee" ? "nonTeacher" : "teacher";
        $output['role_id'] = $user->role_perm;
        $output['finger_id'] = $employee->finger_id;
        $output['department_id'] = $employee->department_id;
        $output['designation_id'] = $employee->designation_id;
        $output['branch_id'] = $employee->branch_id ? $employee->branch_id : '';
        $output['supervisor_id'] = $employee->supervisor_id;
        $output['work_shift_id'] = $employee->work_shift_id;
        $output['pay_grade_id'] = $employee->pay_grade_id;
        $output['hourly_salaries_id'] = $employee->hourly_salaries_id;
        $output['email'] = $employee->email ? $employee->email : '';
        $output['first_name'] = $employee->first_name;
        $output['last_name'] = $employee->last_name ? $employee->last_name : '';
        $output['date_of_birth'] = $employee->date_of_birth;
        $output['date_of_joining'] = $employee->date_of_joining;
        $output['date_of_leaving'] = $employee->date_of_leaving;
        $output['gender'] = $employee->gender ? $employee->gender : '';
        $output['religion'] = $employee->religion ? $employee->religion : '';
        $output['marital_status'] = $employee->marital_status ? $employee->marital_status : '';
        $output['photo'] = $employee->photo;
        $output['address'] = $employee->address ? $employee->address : '';
        $output['emergency_contacts'] = $employee->emergency_contacts ? $employee->emergency_contacts : '';
        $output['phone'] = $employee->phone;
        $output['status'] = $employeeStatus;
        $output['permanent_status'] = $employee->permanent_status;
        
        $education = EmployeeEducationQualification::where('employee_id', $id)->get()->toArray();
        foreach( $education as $key => $edu )
        {
            if( !$edu['result'] ) { $education[$key]['result'] = ''; }
            $education[$key]['gpa'] = $education[$key]['cgpa'];
            if( !$edu['cgpa'] ) { $education[$key]['cgpa'] = ''; $education[$key]['gpa'] = ''; }
            $education[$key]['university'] = $education[$key]['board_university'];
            $education[$key]['passingYear'] = $education[$key]['passing_year'] . "-01-01";
        }
        $experience = EmployeeExperience::where('employee_id', $id)->get()->toArray();

        foreach( $experience as $key => $exp )
        {
            $from = toUnixStamp( $exp['from_date'] );
            $to = toUnixStamp( $exp['to_date'] );
            $experience[$key]['from'] = date('Y-m-d', $from);
            $experience[$key]['to'] = date('Y-m-d', $to);
            $experience[$key]['organization'] = $exp['organization_name'];
            if( $from > $to ) $experience[$key]['duration'] = '';
            else
            {
                $duration_time = $to - $from;
                $experience[$key]['duration'] = humanDuration( $duration_time );
            }
        }
        
        $output['education'] = $education;
        $output['experience'] = $experience;
        $toReturn['employee'] = $output;
        return $toReturn;
    }

    public function updateEmployee()
    {
        if( !$this->panelInit->can( "employees.edit_employee" ) ) { return $this->panelInit->apiOutput( false, "Edit Employee", "You don't have permission to edit employees" ); }
        if( !\Input::has('employee_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Employee not found" );
        if( !\Input::has('user_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Employee not found" );
        if( !\Input::has('role_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Role is missing" );
        if( !\Input::has('username') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Username is missing" );
        if( !\Input::has('academic_role') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Academic Role is missing" );
        if( !\Input::has('first_name') ) return $this->panelInit->apiOutput( false, "Edit Employee", "First Name is missing" );
        if( !\Input::has('finger') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Fingerprint No. is missing" );
        if( !\Input::has('department_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Department Name is missing" );
        if( !\Input::has('designation_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Designation Name is missing" );
        if( !\Input::has('shift_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Work Shift is missing" );
        if( !\Input::has('paygrade_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Monthly Pay Grade is missing" );
        if( !\Input::has('salary_id') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Hourly Pay Grade is missing" );
        if( !\Input::has('phone') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Phone is missing" );
        if( !\Input::has('birthday') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Date Of Birth is missing" );
        if( !\Input::has('joinday') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Date Of Joining is missing" );
        if( !\Input::has('status') ) return $this->panelInit->apiOutput( false, "Edit Employee", "Account status is missing" );
        if( \Input::has('eduCount') )
        {
            $eduCount = \Input::get('eduCount');
            if( $eduCount > 0 )
            {
                for( $i = 0; $i < $eduCount; $i++ )
                {
                    $institute = "education_" . $i . "_institute";
                    if( !\Input::has($institute) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of educational institute is missing" );
                    if( !trim( \Input::get($institute) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of education institute is missing" );
                    $university = "education_" . $i . "_university";
                    if( !\Input::has($university) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of educational board / university name is missing" );
                    if( !trim( \Input::get($university) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of educational board / university name is missing" );
                    $degree = "education_" . $i . "_degree";
                    if( !\Input::has($degree) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of educational degree is missing" );
                    if( !trim( \Input::get($degree) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of educational degree is missing" );
                    $passingYear = "education_" . $i . "_passingYear";
                    if( !\Input::has($passingYear) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of educational passing year is missing" );
                    if( !trim( \Input::get($passingYear) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of educational passing year is missing" );
                }
            }
        }

        if( \Input::has('proffCount') )
        {
            $proffCount = \Input::get('proffCount');
            if( $proffCount > 0 )
            {
                for( $i = 0; $i < $proffCount; $i++ )
                {
                    $organization = "profession_" . $i . "_organization";
                    if( !\Input::has($organization) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences organization name is missing" );
                    if( !trim( \Input::get($organization) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences organization name is missing" );
                    $designation = "profession_" . $i . "_designation";
                    if( !\Input::has($designation) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences designation name is missing" );
                    if( !trim( \Input::get($designation) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences designation name is missing" );
                    $from = "profession_" . $i . "_from";
                    if( !\Input::has($from) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences start date is missing" );
                    if( !trim( \Input::get($from) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences start date is missing" );
                    $to = "profession_" . $i . "_to";
                    if( !\Input::has($to) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences leave date is missing" );
                    if( !trim( \Input::get($to) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences leave date is missing" );
                    $responsibility = "profession_" . $i . "_responsibility";
                    if( !\Input::has($responsibility) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences responsibility is missing" );
                    if( !trim( \Input::get($responsibility) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences responsibility is missing" );
                    $skill = "profession_" . $i . "_skill";
                    if( !\Input::has($skill) ) return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences skill is missing" );
                    if( !trim( \Input::get($skill) ) )  return $this->panelInit->apiOutput( false, "Edit Employee", "One of professional experiences skill is missing" );
                }
            }
        }
        $user_id = \Input::get('user_id');
        $employee_id = \Input::get('employee_id');

        $getUser = User::find( $user_id );
        if( !$getUser ) return $this->panelInit->apiOutput( false, "Edit Employee", "Employee not found" );
        $getEmployee = Employee::find( $employee_id );
        if( !$getEmployee ) return $this->panelInit->apiOutput( false, "Edit Employee", "Employee not found" );
        $currentRole = $getUser->role;
        $currentPhoto = $getUser->photo;
        
        $role_id = \Input::get('role_id');
        $username = \Input::get('username');
        $password = \Input::has('password') ? \Input::get('password') : NULL;
        
        $first_name = \Input::get('first_name');
        $last_name = \Input::has('last_name') ? \Input::get('last_name') : NULL;
        $finger = \Input::get('finger');
        $supervisor_id = \Input::has('supervisor_id') ? ( trim( \Input::get('supervisor_id') ) ? \Input::get('supervisor_id') : NULL ) : NULL;
        $department_id = \Input::get('department_id');
        $designation_id = \Input::get('designation_id');
        $branch_id = \Input::has('branch_id') ? ( trim( \Input::get('branch_id') ) ? \Input::get('branch_id') : NULL ) : NULL;
        $shift_id = \Input::has('shift_id') ? ( trim( \Input::get('shift_id') ) ? \Input::get('shift_id') : NULL ) : NULL;
        $paygrade_id = \Input::has('paygrade_id') ? ( trim( \Input::get('paygrade_id') ) ? \Input::get('paygrade_id') : NULL ) : NULL;
        $salary_id = \Input::has('salary_id') ? ( trim( \Input::get('salary_id') ) ? \Input::get('salary_id') : NULL ) : NULL;
        $email = \Input::has('email') ? \Input::get('email') : NULL;
        $phone = \Input::get('phone');
        $gender = \Input::has('gender') ? strtolower( \Input::get('gender') ) : NULL;
        $religion = \Input::has('religion') ? \Input::get('religion') : NULL;
        $birthday = \Input::get('birthday');
        $joinday = \Input::get('joinday');
        $leaveday = \Input::has('leaveday') ? \Input::get('leaveday') : NULL;
        $status = \Input::get('status');
        $marital = \Input::has('marital') ? \Input::get('marital') : NULL;
        $address = \Input::has('address') ? \Input::get('address') : NULL;
        $emergency = \Input::has('emergency') ? \Input::get('emergency') : NULL;
        
        $username_validate = User::where('username', $username)->where('id', '!=', $user_id)->first();
        if( $username_validate ) return $this->panelInit->apiOutput( false, "Edit Employee", "Username already exists please try different username and try again" );
        if( \Input::has('email') )
        {
            $user_mail_check = User::where('email', $email)->where('id', '!=', $user_id)->first();
            if( $user_mail_check ) return $this->panelInit->apiOutput( false, "Edit Employee", "Email already exists please try different email and try again" );
            $employee_mail_check = Employee::where('email', $email)->where('employee_id', '!=', $employee_id)->first();
            if( $employee_mail_check ) return $this->panelInit->apiOutput( false, "Edit Employee", "Email already exists please try different email and try again" );
        }
        $check_finger = Employee::where('finger_id', $finger)->where('employee_id', '!=', $employee_id)->first();
        if( $check_finger )  return $this->panelInit->apiOutput( false, "Edit Employee", "Fingerprint no. already exists please try different fingerprint no. and try again" );
        
        $check_role = Role::where('id', $role_id)->first();
        if( !$check_role )  return $this->panelInit->apiOutput( false, "Edit Employee", "Role not exists please try different role and try again");

        if( $supervisor_id )
        {
            $supervisor_check = Employee::where('employee_id', $supervisor_id)->first();
            if( !$supervisor_check ) return $this->panelInit->apiOutput( false, "Edit Employee", "Supervisor not exists please try different supervisor and try again");
        }
        $check_department = Department::where('department_id', $department_id)->first();
        if( !$check_department ) return $this->panelInit->apiOutput( false, "Edit Employee", "Department not exists please try different department and try again");
        
        $check_designation = Designation::where('designation_id', $designation_id)->first();
        if( !$check_designation ) return $this->panelInit->apiOutput( false, "Edit Employee", "Designation not exists please try different designation and try again");
        
        if( $branch_id )
        {
            $check_branch = Branch::where('branch_id', $branch_id)->first();
            if( !$check_branch ) return $this->panelInit->apiOutput( false, "Edit Employee", "Branch not exists please try different branch and try again");
        }

        if( $shift_id )
        {
            $check_workShift = WorkShift::where('work_shift_id', $shift_id)->first();
            if( !$check_workShift ) return $this->panelInit->apiOutput( false, "Edit Employee", "Work shift not exists please try different shift and try again");
        }

        if( $paygrade_id )
        {
            $check_payGrade = PayGrade::where('pay_grade_id', $paygrade_id)->first();
            if( !$check_payGrade ) return $this->panelInit->apiOutput( false, "Edit Employee", "Monthly pay grade not exists please try different pay grade and try again");
        }

        if( $salary_id )
        {
            $check_hourlySalary = HourlySalary::where('hourly_salaries_id', $salary_id)->first();
            if( !$check_hourlySalary ) return $this->panelInit->apiOutput( false, "Edit Employee", "Hourly pay grade not exists please try different pay grade and try again");
        }

        if( \Input::hasFile('photo') )
        {
			$fileInstance = \Input::file('photo');
            if( !$this->panelInit->validate_upload( $fileInstance ) )
            {
				return $this->panelInit->apiOutput(false, "Edit Employee", "Sorry, This File Type Is Not Permitted For Security Reasons");
            }
        }

        $fullName = trim( $first_name );
        if( \Input::has('last_name') ) $fullName = $fullName . ' ' . $last_name;
        $user_gender = $gender != '' ? ( $gender == 'male' ? 'Male' : 'Female' ) : NULL;
        $account_active = $status == "Active" ? "1" : "0";

        $getUser = User::find( $user_id );
        $getUser->username = $username;
        $getUser->email = $email;
        $getUser->fullName = $fullName;
        if( $password ) { $getUser->password = \Hash::make( $password ); }
        $getUser->mobileNo = $phone;
        if( $currentRole != "admin" ) { $getUser->role = \Input::get('academic_role') == "teacher" ? "teacher" : "employee"; }
        $getUser->account_active = $account_active;
        $getUser->role_perm = $role_id;
        $getUser->birthday = toUnixStamp( $birthday );
        $getUser->gender = $user_gender;
        if( \Input::hasFile('photo') )
        {
			$fileInstance = \Input::file('photo');
            $newPhotoName = "profile_" . $user_id . ".jpg";
            
            if( file_exists( uploads_config()['uploads_file_path'] . "/profile/$newPhotoName" ) )
            {
                unlink( uploads_config()['uploads_file_path'] . "/profile/$newPhotoName" );
            }

            $file = $fileInstance->move(uploads_config()['uploads_file_path'] . "/profile/$newPhotoName" );
            $getUser->photo = $newPhotoName;
        } else $newPhotoName = $currentPhoto;
        $getUser->save();
        $userId = $user_id;
        user_log('User', 'edit', $getUser->fullName);
        
        if( toUnixStamp( $birthday ) )
        {
            $db_birthday = date('Y-m-d', toUnixStamp( $birthday ));
        } else $db_birthday = $birthday;
        
        if( toUnixStamp( $joinday ) )
        {
            $db_joinday = date('Y-m-d', toUnixStamp( $joinday ));
        } else $db_joinday = $joinday;

        if( $leaveday )
        {
            if( toUnixStamp( $leaveday ) )
            {
                $db_leaveday = date('Y-m-d', toUnixStamp( $leaveday ));
            } else $db_leaveday = $leaveday;
        } else $db_leaveday = NULL;

        $getEmployee = Employee::find( $employee_id );
        $getEmployee->user_id = $userId;
        $getEmployee->finger_id = $finger;
        $getEmployee->department_id = $department_id;
        $getEmployee->designation_id = $designation_id;
        $getEmployee->branch_id = $branch_id ? $branch_id : "";
        $getEmployee->supervisor_id = $supervisor_id ? $supervisor_id : "";
        $getEmployee->work_shift_id = $shift_id ? $shift_id : "";
        $getEmployee->pay_grade_id = $paygrade_id ? $paygrade_id : "";
        $getEmployee->hourly_salaries_id = $salary_id ? $salary_id : "";
        $getEmployee->email = $email;
        $getEmployee->first_name = $first_name;
        $getEmployee->last_name = $last_name;
        $getEmployee->date_of_birth = $db_birthday;
        $getEmployee->date_of_joining = $db_joinday;
        $getEmployee->date_of_leaving = $db_leaveday;
        $getEmployee->gender = $user_gender;
        $getEmployee->religion = $religion;
        $getEmployee->marital_status = $marital;
        $getEmployee->photo = $newPhotoName;
        $getEmployee->address = $address;
        $getEmployee->emergency_contacts = $emergency;
        $getEmployee->phone = $phone;
        $getEmployee->status = $account_active;
        $getEmployee->updated_by = \Auth::user()->id;
        $getEmployee->deleted = '0';
        $getEmployee->save();
        user_log('Employees', 'edit', trim( $getEmployee->first_name . " " . $getEmployee->last_name ) );

        EmployeeEducationQualification::where('employee_id', $employee_id)->delete();
        EmployeeExperience::where('employee_id', $employee_id)->delete();
        if( \Input::has('eduCount') )
        {
            $eduCount = \Input::get('eduCount');
            if( $eduCount > 0 )
            {
                for( $i = 0; $i < $eduCount; $i++ )
                {
                    $institute = "education_" . $i . "_institute";
                    $university = "education_" . $i . "_university";
                    $degree = "education_" . $i . "_degree";
                    $passingYear = "education_" . $i . "_passingYear";
                    $result = "education_" . $i . "_result";
                    $gpa = "education_" . $i . "_gpa";
                    
                    $dbEducation = new EmployeeEducationQualification();
                    $dbEducation->employee_id = $employee_id;
                    $dbEducation->institute = \Input::get( $institute );
                    $dbEducation->board_university = \Input::get( $university );
                    $dbEducation->degree = \Input::get( $degree );
                    if( \Input::has($result) ) { if( trim( \Input::get($result) ) ) { $dbEducation->result = \Input::get( $result ); } }
                    if( \Input::has($gpa) ) { if( trim( \Input::get($gpa) ) ) { $dbEducation->cgpa = \Input::get( $gpa ); } }
                    $dbEducation->passing_year = date('Y', toUnixStamp( \Input::get( $passingYear ) ));
                    $dbEducation->save();
                }
            }
        }

        if( \Input::has('proffCount') )
        {
            $proffCount = \Input::get('proffCount');
            if( $proffCount > 0 )
            {
                for( $i = 0; $i < $proffCount; $i++ )
                {
                    $organization = "profession_" . $i . "_organization";
                    $designation = "profession_" . $i . "_designation";
                    $from = "profession_" . $i . "_from";
                    $to = "profession_" . $i . "_to";
                    $responsibility = "profession_" . $i . "_responsibility";
                    $skill = "profession_" . $i . "_skill";
                    $profession = new EmployeeExperience();
                    $profession->employee_id = $employee_id;
                    $profession->organization_name = \Input::get( $organization );
                    $profession->designation = \Input::get( $designation );
                    $profession->from_date = date('Y-m-d', toUnixStamp( \Input::get( $from ) ));
                    $profession->to_date = date('Y-m-d', toUnixStamp( \Input::get( $to ) ));
                    $profession->skill = \Input::get( $responsibility );
                    $profession->responsibility = \Input::get( $skill );
                    $profession->save();
                }
            }
        }

        return $this->panelInit->apiOutput( true, "Edit employee", "Employee Updated Successfully" );
    }

    public function deleteEmployee()
    {
        if( !$this->panelInit->can( "employees.delete_employee" ) ) { return $this->panelInit->apiOutput( false, "Delete Employee", "You don't have permission to delete employees" ); }
        if( !\Input::has('employee_id') ) { return $this->panelInit->apiOutput( false, "Delete Employee", "Employee is not exists" ); }
        $id = \Input::get('employee_id');
        $postDelete = Employee::where('employee_id', $id)->first();
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete Employee", "Employee is not exists" ); }
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Employees', 'delete', $postDelete->branch_name);
        return $this->panelInit->apiOutput( true, "Delete Employee", "Employee Deleted Successfully" );
    }

    public function loadImage( $img )
    {
        header('Content-Type: image/jpeg');
        $uploads_file_path = uploads_config()['uploads_file_path'];
        return file_get_contents($uploads_file_path . "/profile/$img");
    }

    public function listWarnings( $page )
    {
        if( !$this->panelInit->can( "warnings.list" ) ) { return \Redirect::to( \URL::to('/portal#/') ); }
        $toReturn = array();
        $getWarnings = Warning::where('deleted', '0');
        $toReturn["warningsCount"] = $getWarnings->count();
        $getWarnings = $getWarnings->take(all_pagination_number())->skip(all_pagination_number() * ($page - 1) )->get();
        $output = []; $index = 0;
        foreach( $getWarnings as $key => $warning )
        {
            $firstNameTo = trim( $warning->warningTo->first_name );
            $lastNameTo = trim( $warning->warningTo->last_name );
            $fullNameTo = trim( "$firstNameTo $lastNameTo" );

            $firstNameBy = trim( $warning->warningBy->first_name );
            $lastNameBy = trim( $warning->warningBy->last_name );
            $fullNameBy = trim( "$firstNameBy $lastNameBy" );

            $getWarnings[$key]['by'] = $fullNameBy;
            $getWarnings[$key]['to'] = $fullNameTo;

            $output[$index] = [
                'id' => $warning->warning_id,
                'warning_to' => $warning->warning_to,
                'to' => $fullNameTo,
                'warning_type' => $warning->warning_type,
                'subject' => $warning->subject,
                'warning_by' => $warning->warning_by,
                'by' => $fullNameBy,
                'warning_date' => $warning->warning_date,
                'description' => $warning->description
            ];
            $index++;
        }
        $toReturn["warnings"] = $output;
        $toReturn["employees"] = Employee::select('employee_id as id', 'first_name', 'last_name')->get()->toArray();
        return $toReturn;
    }

    public function createWarning()
    {
        if( !$this->panelInit->can( "warnings.add_warning" ) ) { return $this->panelInit->apiOutput( false, "Add warning", "You don't have permission to add warnings" ); }
        if( !\Input::has('to') ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning To is missing" ); }
        if( !\Input::has('type') ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning Type is missing" ); }
        if( !\Input::has('subject') ) { return $this->panelInit->apiOutput( false, "Add warning", "Subject is missing" ); }
        if( !\Input::has('by') ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning By is missing" ); }
        if( !\Input::has('date') ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning Date is missing" ); }
        $date = formatDate( \Input::get('date') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning Date has invalid format" ); }
        if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning Date has invalid format" ); }
        $checkTo = Employee::find( \Input::get('to') );
        $checkBy = Employee::find( \Input::get('by') );
        if( !$checkTo ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning to employee can't be found" ); }
        if( !$checkBy ) { return $this->panelInit->apiOutput( false, "Add warning", "Warning By employee can't be found" ); }

        $warning = new Warning();
        $warning->warning_to = \Input::get('to');
        $warning->warning_by = \Input::get('by');
        $warning->warning_type = \Input::get('type');
        $warning->subject = \Input::get('subject');
        $warning->warning_date = date('Y-m-d', toUnixStamp( $date ) );
        $warning->description = \Input::has('desc') ? \Input::get('desc') : NULL;
        $warning->save();

        user_log('Warnings', 'create', $warning->subject . ' to ' . $checkTo->first_name . ' ' . $checkTo->last_name );
        return $this->panelInit->apiOutput( true, "Add warning", "Warning Created Successfully" );
    }

    public function readWarning()
    {
        if( !$this->panelInit->can( "warnings.view_warning" ) ) { return $this->panelInit->apiOutput( false, "View warning", "You don't have permission to view warnings" ); }
        if( !\Input::has('warning_id') ) { return $this->panelInit->apiOutput( false, "View warning", "Warning is not exists" ); }
        $id = \Input::get('warning_id');
        $warning = Warning::find( $id );
        if( !$warning ) { return $this->panelInit->apiOutput( false, "View warning", "Warning is not exists" ); }
        $toReturn = array();
        $department = Department::find( $warning->warningTo->department_id );
        $department_name = $department ? $department->department_name : '';
        $output = [
            'warning_id' => $id,
            'subject' => $warning->subject,
            'warning_to' => $warning->warning_to,
            'to' => $warning->warningTo->first_name . " " . $warning->warningTo->last_name,
            'department_id' => $warning->warningTo->department_id,
            'department' => $department_name,
            'description' => $warning->description ? $warning->description : '',
            'warning_by' => $warning->warning_by,
            'by' => $warning->warningBy->first_name . " " . $warning->warningBy->last_name,
            'type' => $warning->warning_type,
            'date' => $warning->warning_date,
            'date_formatted' => date(" d M Y ", toUnixStamp( $warning->warning_date ) ),
            'modelDate' => date("Y/m/d", toUnixStamp( $warning->warning_date ) )
        ];
        $toReturn['warning'] = $output;
        return $toReturn;
    }

    public function updateWarning()
    {
        if( !$this->panelInit->can( "warnings.edit_warning" ) ) { return $this->panelInit->apiOutput( false, "Edit warning", "You don't have permission to edit warnings" ); }
        if( !\Input::has('warning_id') ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning is not exists" ); }
        $id = \Input::get('warning_id');
        $warning = Warning::find( $id );
        if( !$warning ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning is not exists" ); }

        if( !\Input::has('warning_to') ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning To is missing" ); }
        if( !\Input::has('type') ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning Type is missing" ); }
        if( !\Input::has('subject') ) { return $this->panelInit->apiOutput( false, "Edit warning", "Subject is missing" ); }
        if( !\Input::has('warning_by') ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning By is missing" ); }
        if( !\Input::has('modelDate') ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning Date is missing" ); }

        $date = formatDate( \Input::get('modelDate') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning Date has invalid format" ); }
        if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning Date has invalid format" ); }
        $checkTo = Employee::find( \Input::get('warning_to') );
        $checkBy = Employee::find( \Input::get('warning_by') );
        if( !$checkTo ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning to employee can't be found" ); }
        if( !$checkBy ) { return $this->panelInit->apiOutput( false, "Edit warning", "Warning By employee can't be found" ); }
        $warning = Warning::find( $id );
        
        $warning->warning_to = \Input::get('warning_to');
        $warning->warning_by = \Input::get('warning_by');
        $warning->warning_type = \Input::get('type');
        $warning->subject = \Input::get('subject');
        $warning->warning_date = date('Y-m-d', toUnixStamp( $date ) );
        $warning->description = \Input::has('description') ? \Input::get('description') : NULL;
        $warning->save();

        user_log('Warnings', 'update', $warning->subject . ' to ' . $checkTo->first_name . ' ' . $checkTo->last_name );
        return $this->panelInit->apiOutput( true, "Edit warning", "Warning Updated Successfully" );
    }

    public function deleteWarning()
    {
        if( !$this->panelInit->can( "warnings.delete_warning" ) ) { return $this->panelInit->apiOutput( false, "Delete Warning", "You don't have permission to delete warnings" ); }
        if( !\Input::has('warning_id') ) { return $this->panelInit->apiOutput( false, "Delete Warning", "Warning is not exists" ); }
        $id = \Input::get('warning_id');
        $postDelete = Warning::find( $id );
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete Warning", "Warning is not exists" ); }
        $fullName = $postDelete->warningTo->first_name . " " . $postDelete->warningTo->last_name;
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Warnings', 'delete', $postDelete->subject . ' to ' . $fullName );
        return $this->panelInit->apiOutput( true, "Delete Warning", "Warning Deleted Successfully" );
    }

    public function listTerminations( $page )
    {
        if( !$this->panelInit->can( "terminations.list" ) ) { return \Redirect::to( \URL::to('/portal#/') ); }
        $toReturn = array();
        $refreshment = Termination::where('deleted', '0')->where('status', '1')->get();
        foreach( $refreshment as $key => $termination )
        {
            $lastSecond = $termination->termination_date . " 23:59:59";
            if( time() > strtotime( $lastSecond ) )
            {
                $employee = Employee::find( $termination->terminate_to );
                if( $employee )
                {
                    $userId = $employee->user_id;
                    $updateUser = User::find( $userId );
                    if( $updateUser )
                    {
                        $updateUser->account_active = "0";
                        $updateUser->save();
                        //
                        $updateTermination = Termination::find( $termination->termination_id );
                        if( $updateTermination )
                        {
                            $updateTermination->status = 2;
                            $updateTermination->save();
                        }
                    } else continue;
                } else continue;
            }
        }

        $getTerminations = Termination::where('deleted', '0');
        $toReturn["terminationsCount"] = $getTerminations->count();
        $getTerminations = $getTerminations->take(all_pagination_number())->skip(all_pagination_number() * ($page - 1) )->get();
        $output = []; $index = 0;
        foreach( $getTerminations as $key => $termination )
        {
            $firstNameTo = trim( $termination->terminateTo->first_name );
            $lastNameTo = trim( $termination->terminateTo->last_name );
            $fullNameTo = trim( "$firstNameTo $lastNameTo" );

            $firstNameBy = trim( $termination->terminateBy->first_name );
            $lastNameBy = trim( $termination->terminateBy->last_name );
            $fullNameBy = trim( "$firstNameBy $lastNameBy" );

            $getTerminations[$key]['by'] = $fullNameBy;
            $getTerminations[$key]['to'] = $fullNameTo;

            $output[$index] = [
                'id' => $termination->termination_id,
                'terminate_to' => $termination->terminate_to,
                'terminate_by' => $termination->terminate_by,
                'to' => $fullNameTo,
                'by' => $fullNameBy,
                'subject' => $termination->subject,
                'type' => $termination->termination_type,
                'notice_date' => $termination->notice_date,
                'termination_date' => $termination->termination_date,
                'status' => intval( $termination->status ),
                'description' => $termination->description
            ];
            $index++;
        }
        $toReturn["terminations"] = $output;
        $toReturn["employees"] = Employee::select('employee_id as id', 'first_name', 'last_name')->get()->toArray();
        return $toReturn;
    }

    public function createTermination()
    {
        if( !$this->panelInit->can( "terminations.add_terminations" ) ) { return $this->panelInit->apiOutput( false, "Add termination", "You don't have permission to add terminations" ); }
        if( !\Input::has('to') ) { return $this->panelInit->apiOutput( false, "Add termination", "Employee terminated is missing" ); }
        if( !\Input::has('type') ) { return $this->panelInit->apiOutput( false, "Add termination", "Termination Type is missing" ); }
        if( !\Input::has('subject') ) { return $this->panelInit->apiOutput( false, "Add termination", "Subject is missing" ); }
        if( !\Input::has('by') ) { return $this->panelInit->apiOutput( false, "Add termination", "Terminated By is missing" ); }
        if( !\Input::has('noticeDate') ) { return $this->panelInit->apiOutput( false, "Add termination", "Notice Date is missing" ); }
        if( !\Input::has('terminateDate') ) { return $this->panelInit->apiOutput( false, "Add termination", "Termination Date is missing" ); }

        $noticeDate = formatDate( \Input::get('noticeDate') );
        if( !$noticeDate ) { return $this->panelInit->apiOutput( false, "Add termination", "Notice Date has invalid format" ); }
        if( !toUnixStamp( $noticeDate ) ) { return $this->panelInit->apiOutput( false, "Add termination", "Notice Date has invalid format" ); }
        
        $terminateDate = formatDate( \Input::get('terminateDate') );
        if( !$terminateDate ) { return $this->panelInit->apiOutput( false, "Add termination", "Termination Date has invalid format" ); }
        if( !toUnixStamp( $terminateDate ) ) { return $this->panelInit->apiOutput( false, "Add termination", "Termination Date has invalid format" ); }
        
        $checkTo = Employee::find( \Input::get('to') );
        $checkBy = Employee::find( \Input::get('by') );
        if( !$checkTo ) { return $this->panelInit->apiOutput( false, "Add termination", "Employee Terminated can't be found" ); }
        if( !$checkBy ) { return $this->panelInit->apiOutput( false, "Add termination", "Terminated By can't be found" ); }
        
        $lastSecond = $terminateDate . " 23:59:59";
        if( time() > strtotime( $lastSecond ) )
        {
            $terminationStatus = 2;
            $employee = Employee::find( \Input::get('to') );
            $userId = $employee->user_id;
            $updateUser = User::find( $userId );
            $updateUser->account_active = "0";
            $updateUser->save();
        } else $terminationStatus = 1;
        
        $termination = new Termination();
        $termination->terminate_to = \Input::get('to');
        $termination->terminate_by = \Input::get('by');
        $termination->termination_type = \Input::get('type');
        $termination->subject = \Input::get('subject');
        $termination->notice_date = date('Y-m-d', toUnixStamp( $noticeDate ) );
        $termination->termination_date = date('Y-m-d', toUnixStamp( $terminateDate ) );
        $termination->description = \Input::has('desc') ? \Input::get('desc') : NULL;
        $termination->status = $terminationStatus;
        $termination->save();

        user_log('Terminations', 'create', $termination->subject . ' to ' . $checkTo->first_name . ' ' . $checkTo->last_name );
        return $this->panelInit->apiOutput( true, "Add termination", "Termination Created Successfully" );
    }

    public function readTermination()
    {
        if( !$this->panelInit->can( "terminations.view_termination" ) ) { return $this->panelInit->apiOutput( false, "View termination", "You don't have permission to view terminations" ); }
        if( !\Input::has('termination_id') ) { return $this->panelInit->apiOutput( false, "View termination", "Termination is not exists" ); }
        $id = \Input::get('termination_id');
        $termination = Termination::find( $id );
        if( !$termination ) { return $this->panelInit->apiOutput( false, "View termination", "Termination is not exists" ); }
        $toReturn = array();
        $department = Department::find( $termination->terminateTo->department_id );
        $department_name = $department ? $department->department_name : '';
        $output = [
            'termination_id' => $id,
            'subject' => $termination->subject,
            'terminate_to' => $termination->terminate_to,
            'to' => $termination->terminateTo->first_name . " " . $termination->terminateTo->last_name,
            'department_id' => $termination->terminateTo->department_id,
            'department' => $department_name,
            'description' => $termination->description ? $termination->description : '',
            'terminate_by' => $termination->terminate_by,
            'by' => $termination->terminateBy->first_name . " " . $termination->terminateBy->last_name,
            'type' => $termination->termination_type,
            'notice_date' => $termination->notice_date,
            'notice_date_formatted' => date(" d M Y ", toUnixStamp( $termination->notice_date ) ),
            'modelNoticeDate' => date("Y/m/d", toUnixStamp( $termination->notice_date ) ),
            'termination_date' => $termination->termination_date,
            'termination_date_formatted' => date(" d M Y ", toUnixStamp( $termination->termination_date ) ),
            'modelTerminationDate' => date("Y/m/d", toUnixStamp( $termination->termination_date ) ),
            'status' => intval( $termination->status )
        ];
        $toReturn['termination'] = $output;
        return $toReturn;
    }

    public function updateTermination()
    {
        $title = \Input::has('isApproved') ? "Approve termination" : "Edit termination";
        $action = \Input::has('isApproved') ? "approve" : "edit";
        if( !$this->panelInit->can( "terminations.edit_terminations" ) ) { return $this->panelInit->apiOutput( false, $title, "You don't have permission to $action terminations" ); }
        if( !\Input::has('termination_id') ) { return $this->panelInit->apiOutput( false, $title, "Termination is not exists" ); }
        $id = \Input::get('termination_id');
        $termination = Termination::find( $id );
        if( !$termination ) { return $this->panelInit->apiOutput( false, $title, "Termination is not exists" ); }
        if( \Input::has('isApproved') )
        {
            $employee = Employee::find( $termination->terminate_to );
            $userId = $employee->user_id;
            $updateUser = User::find( $userId );
            $updateUser->account_active = "0";
            $updateUser->save();

            $termination->status = 2;
            $termination->save();
            return $this->panelInit->apiOutput( true, $title, "Termination approved successfully" );
        }
        else
        {
            if( !\Input::has('terminate_to') ) { return $this->panelInit->apiOutput( false, $title, "Employee terminated is missing" ); }
            if( !\Input::has('type') ) { return $this->panelInit->apiOutput( false, $title, "Termination Type is missing" ); }
            if( !\Input::has('subject') ) { return $this->panelInit->apiOutput( false, $title, "Subject is missing" ); }
            if( !\Input::has('terminate_by') ) { return $this->panelInit->apiOutput( false, $title, "Terminated By is missing" ); }
            if( !\Input::has('modelNoticeDate') ) { return $this->panelInit->apiOutput( false, $title, "Notice Date is missing" ); }
            if( !\Input::has('modelTerminationDate') ) { return $this->panelInit->apiOutput( false, $title, "Termination Date is missing" ); }

            $noticeDate = formatDate( \Input::get('modelNoticeDate') );
            if( !$noticeDate ) { return $this->panelInit->apiOutput( false, $title, "Notice Date has invalid format" ); }
            if( !toUnixStamp( $noticeDate ) ) { return $this->panelInit->apiOutput( false, $title, "Notice Date has invalid format" ); }
            
            $terminateDate = formatDate( \Input::get('modelTerminationDate') );
            if( !$terminateDate ) { return $this->panelInit->apiOutput( false, $title, "Termination Date has invalid format" ); }
            if( !toUnixStamp( $terminateDate ) ) { return $this->panelInit->apiOutput( false, $title, "Termination Date has invalid format" ); }

            $checkTo = Employee::find( \Input::get('terminate_to') );
            $checkBy = Employee::find( \Input::get('terminate_by') );
            if( !$checkTo ) { return $this->panelInit->apiOutput( false, $title, "Employee Terminated can't be found" ); }
            if( !$checkBy ) { return $this->panelInit->apiOutput( false, $title, "Terminated By can't be found" ); }
            $lastSecond = $terminateDate . " 23:59:59";
            if( time() > strtotime( $lastSecond ) )
            {
                $terminationStatus = 2;
                $employee = Employee::find( \Input::get('terminate_to') );
                $userId = $employee->user_id;
                $updateUser = User::find( $userId );
                $updateUser->account_active = "0";
                $updateUser->save();
            } else $terminationStatus = 1;
            $termination->terminate_to = \Input::get('terminate_to');
            $termination->terminate_by = \Input::get('terminate_by');
            $termination->termination_type = \Input::get('type');
            $termination->subject = \Input::get('subject');
            $termination->notice_date = date('Y-m-d', toUnixStamp( $noticeDate ) );
            $termination->termination_date = date('Y-m-d', toUnixStamp( $terminateDate ) );
            $termination->description = \Input::has('description') ? \Input::get('description') : NULL;
            $termination->status = $terminationStatus;
            $termination->save();
    
            user_log('Terminations', 'edit', $termination->subject . ' to ' . $checkTo->first_name . ' ' . $checkTo->last_name );
            return $this->panelInit->apiOutput( true, $title, "Termination Updated Successfully" );
        }
    }

    public function deleteTermination()
    {
        if( !$this->panelInit->can( "terminations.delete_terminations" ) ) { return $this->panelInit->apiOutput( false, "Delete termination", "You don't have permission to delete terminations" ); }
        if( !\Input::has('termination_id') ) { return $this->panelInit->apiOutput( false, "Delete termination", "Termination is not exists" ); }
        $id = \Input::get('termination_id');
        $postDelete = Termination::find( $id );
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete termination", "Termination is not exists" ); }
        $fullName = $postDelete->terminateTo->first_name . " " . $postDelete->terminateTo->last_name;
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Terminations', 'delete', $postDelete->subject . ' to ' . $fullName );
        return $this->panelInit->apiOutput( true, "Delete Terminations", "Terminations Deleted Successfully" );
    }

    public function listPromotions( $page )
    {
        if( !$this->panelInit->can( "promotions.list" ) ) { return \Redirect::to( \URL::to('/portal#/') ); }

        
        $refreshment = Promotion::where('deleted', '0')->where('status', '1')->get();
        foreach( $refreshment as $key => $promotion )
        {
            $lastSecond = $promotion->promotion_date . " 23:59:59";
            if( time() > strtotime( $lastSecond ) )
            {
                $employee = Employee::find( $promotion->employee_id );
                if( $employee )
                {
                    $getPromotion = Promotion::find( $promotion->promotion_id );
                    if( $getPromotion )
                    {
                        $getPromotion->status = 2;
                        $getPromotion->save();

                        $employee->department_id = $promotion->promoted_department;
                        $employee->designation_id = $promotion->promoted_designation;
                        $employee->pay_grade_id = $promotion->promoted_pay_grade;
                        $employee->save();
                    }

                } else continue;
            }
        }
        
        $getPromotions = Promotion::where('deleted', '0');
        $toReturn["promotionsCount"] = $getPromotions->count();
        $getPromotions = $getPromotions->take(all_pagination_number())->skip(all_pagination_number() * ($page - 1) )->get();
        $output = []; $index = 0;
        foreach( $getPromotions as $key => $promotion )
        {
            $firstNameTo = trim( $promotion->employee->first_name );
            $lastNameTo = trim( $promotion->employee->last_name );
            $fullNameTo = trim( "$firstNameTo $lastNameTo" );

            if( $promotion->promotionBy )
            {
                $firstNameBy = trim( $promotion->promotionBy->first_name );
                $lastNameBy = trim( $promotion->promotionBy->last_name );
                $fullNameBy = trim( "$firstNameBy $lastNameBy" );
            } else $fullNameBy = "";

            $output[$index] = [
                'id' => $promotion->promotion_id,
                'promotion_to' => $promotion->employee_id,
                'promotion_by' => $promotion->promotion_by,
                'to' => $fullNameTo,
                'by' => $fullNameBy,
                'current_dep_id' => $promotion->current_department,
                'current_dep' => $promotion->currentDepartment->department_name,
                'promoted_dep_id' => $promotion->promoted_department,
                'promoted_dep' => $promotion->promotedDepartment->department_name,
                'current_des_id' => $promotion->current_designation,
                'current_des' => $promotion->currentDesignation->designation_name,
                'promoted_des_id' => $promotion->promoted_designation,
                'promoted_des' => $promotion->promotedDesignation->designation_name,
                'current_pay_id' => $promotion->current_pay_grade,
                'current_paygrade' => $promotion->currentPayGrade->pay_grade_name,
                'promoted_pay_id' => $promotion->promoted_pay_grade,
                'promoted_paygrade' => $promotion->promotedPayGrade->pay_grade_name,
                'current_salary' => $promotion->current_salary,
                'new_salary' => $promotion->new_salary,
                'date' => $promotion->promotion_date,
                'status' => intval( $promotion->status ),
                'description' => $promotion->description
            ];
            $index++;
        }

        $toReturn["promotions"] = $output;
        $employees = Employee::select('*')->get();
        $outputEmps = [];
        foreach( $employees as $employee )
        {
            $outputEmps[ $employee->employee_id ] = [
                'id' => $employee->employee_id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'depId' => $employee->department->department_id,
                'department' => $employee->department->department_name,
                'desId' => $employee->designation->designation_id,
                'designation' => $employee->designation->designation_name,
                'paygradeId' => $employee->payGrade->pay_grade_id,
                'paygrade' => $employee->payGrade->pay_grade_name,
                'salary' => $employee->payGrade->gross_salary
            ];
        }
        $toReturn["employees"] = $outputEmps;
        $toReturn["departments"] = Department::select('department_id as id', 'department_name as name')->where('deleted', '0')->get()->toArray();
        $toReturn["designations"] = Designation::select('designation_id as id', 'designation_name as name')->where('deleted', '0')->get()->toArray();
        $paygrades = PayGrade::select('pay_grade_id as id', 'pay_grade_name as name', 'gross_salary as salary')->where('deleted', '0')->get()->toArray();
        $grades = [];
        foreach( $paygrades as $paygrade )
        {
            $grades[ $paygrade['id'] ] = [ 'id' => $paygrade['id'], 'name' => $paygrade['name'], 'salary' => $paygrade['salary'] ];
        }
        $toReturn["paygrades"] = $grades;
        return $toReturn;
    }

    public function createPromotion()
    {
        if( !$this->panelInit->can( "promotions.add_promotion" ) ) { return $this->panelInit->apiOutput( false, "Add promotion", "You don't have permission to add promotions" ); }
        if( !\Input::has('to') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Employee Promoted is missing" ); }
        if( !\Input::has('by') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted By is missing" ); }

        if( !\Input::has('currdepId') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Current Department is missing" ); }
        if( !\Input::has('currdesId') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Current Designation is missing" ); }
        if( !\Input::has('currPayGradeId') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Current Pay Grade is missing" ); }
        if( !\Input::has('currSalary') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Current Salary is missing" ); }

        if( !\Input::has('promotedDep') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted Department is missing" ); }
        if( !\Input::has('promotedDes') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted Designation is missing" ); }
        if( !\Input::has('promotedPayGrade') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted Pay Grade is missing" ); }
        if( !\Input::has('promotedSalary') ) { return $this->panelInit->apiOutput( false, "Add promotion", "New Salary is missing" ); }

        $checkTo = Employee::find( \Input::get('to') );
        $checkBy = Employee::find( \Input::get('by') );
        if( !$checkTo ) { return $this->panelInit->apiOutput( false, "Add promotion", "Employee Promoted can't be found" ); }
        if( !$checkBy ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted By can't be found" ); }
        if( !\Input::has('promotionDate') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promotion Date is missing" ); }
        
        $date = formatDate( \Input::get('promotionDate') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promotion Date has invalid format" ); }
        if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promotion Date has invalid format" ); }

        if( $checkTo->department_id != \Input::get('currdepId') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Current Department is invalid" ); }
        if( $checkTo->designation_id != \Input::get('currdesId') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Current Designation is invalid" ); }
        if( $checkTo->pay_grade_id != \Input::get('currPayGradeId') ) { return $this->panelInit->apiOutput( false, "Add promotion", "Current Pay Grade is invalid" ); }
        $currGrade = PayGrade::find( \Input::get('currPayGradeId') );
        
        $checkDep = Department::find( \Input::get('promotedDep') );
        if( !$checkDep ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted Department is invalid" ); }
        
        $checkDes = Designation::find( \Input::get('promotedDes') );
        if( !$checkDes ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted Designation is invalid" ); }
        
        $checkGrade = PayGrade::find( \Input::get('promotedPayGrade') );
        if( !$checkGrade ) { return $this->panelInit->apiOutput( false, "Add promotion", "Promoted Pay Grade is invalid" ); }
        
        $lastSecond = $date . " 23:59:59";
        if( time() > strtotime( $lastSecond ) )
        {
            $promotionStatus = 2;
            $employee = Employee::find( \Input::get('to') );
            $employee->department_id = \Input::get('promotedDep');
            $employee->designation_id = \Input::get('promotedDes');
            $employee->pay_grade_id = \Input::get('promotedPayGrade');
            $employee->save();
        } else $promotionStatus = 1;

        $promotion = new Promotion();
        $promotion->employee_id = \Input::get('to');
        $promotion->promotion_by = \Input::get('by');
        $promotion->current_department = \Input::get('currdepId');
        $promotion->current_designation = \Input::get('currdesId');
        $promotion->current_pay_grade = \Input::get('currPayGradeId');
        $promotion->current_salary = $currGrade->gross_salary;
        $promotion->promoted_department = \Input::get('promotedDep');
        $promotion->promoted_designation = \Input::get('promotedDes');
        $promotion->promoted_pay_grade = \Input::get('promotedPayGrade');
        $promotion->new_salary = \Input::get('promotedSalary');
        $promotion->promotion_date = date('Y-m-d', toUnixStamp( $date ) );;
        $promotion->description = \Input::has('desc') ? \Input::get('desc') : NULL;;
        $promotion->created_by = \Auth::user()->id;
        $promotion->updated_by = \Auth::user()->id;
        $promotion->status = $promotionStatus;
        $promotion->deleted = '0';
        $promotion->save();

        user_log('Promotions', 'create', 'promotion to ' . $checkTo->first_name . ' ' . $checkTo->last_name );
        return $this->panelInit->apiOutput( true, "Add promotion", "Promotion Created Successfully" );
    }

    public function readPromotion()
    {
        if( !$this->panelInit->can( "promotions.edit_promotion" ) ) { return $this->panelInit->apiOutput( false, "Edit promotion", "You don't have permission to edit promotions" ); }
        if( !\Input::has('promotion_id') ) { return $this->panelInit->apiOutput( false, "Edit promotion", "Promotion is not exists" ); }
        $id = \Input::get('promotion_id');
        $promotion = Promotion::find( $id );
        if( !$promotion ) { return $this->panelInit->apiOutput( false, "Edit promotion", "Termination is not exists" ); }
        $toReturn = array();
        $firstNameTo = trim( $promotion->employee->first_name );
        $lastNameTo = trim( $promotion->employee->last_name );
        $fullNameTo = trim( "$firstNameTo $lastNameTo" );

        $firstNameBy = trim( $promotion->promotionBy->first_name );
        $lastNameBy = trim( $promotion->promotionBy->last_name );
        $fullNameBy = trim( "$firstNameBy $lastNameBy" );

        $output = [
            'promotion_id' => $id,
            'promotion_to' => $promotion->employee_id,
            'promotion_by' => $promotion->promotion_by,
            'to' => $fullNameTo,
            'by' => $fullNameBy,
            'current_dep_id' => $promotion->current_department,
            'current_dep' => $promotion->currentDepartment->department_name,
            'promoted_dep_id' => $promotion->promoted_department,
            'promoted_dep' => $promotion->promotedDepartment->department_name,
            'current_des_id' => $promotion->current_designation,
            'current_des' => $promotion->currentDesignation->designation_name,
            'promoted_des_id' => $promotion->promoted_designation,
            'promoted_des' => $promotion->promotedDesignation->designation_name,
            'current_pay_id' => $promotion->current_pay_grade,
            'current_paygrade' => $promotion->currentPayGrade->pay_grade_name,
            'promoted_pay_id' => $promotion->promoted_pay_grade,
            'promoted_paygrade' => $promotion->promotedPayGrade->pay_grade_name,
            'current_salary' => $promotion->current_salary,
            'new_salary' => $promotion->new_salary,
            'date' => $promotion->promotion_date,
            'status' => intval( $promotion->status ),
            'description' => $promotion->description
        ];
        $toReturn['promotion'] = $output;
        return $toReturn;
    }

    public function updatePromotion()
    {
        $title = \Input::has('isApproved') ? "Approve promotion" : "Edit promotion";
        $action = \Input::has('isApproved') ? "approve" : "edit";
        if( !$this->panelInit->can( "promotions.edit_promotion" ) ) { return $this->panelInit->apiOutput( false, $title, "You don't have permission to $action promotions" ); }
        if( !\Input::has('promotion_id') ) { return $this->panelInit->apiOutput( false, $title, "Promotion is not exists" ); }
        $id = \Input::get('promotion_id');
        $promotion = Promotion::find( $id );
        if( \Input::has('isApproved') )
        {
            $employee = Employee::find( $promotion->employee_id );
            $employee->department_id = $promotion->promoted_department;
            $employee->designation_id = $promotion->promoted_designation;
            $employee->pay_grade_id = $promotion->promoted_pay_grade;
            $employee->save();

            $promotion->status = 2;
            $promotion->save();
            return $this->panelInit->apiOutput( true, $title, "Promotion approved successfully" );
        }
        else
        {
            if( !\Input::has('promotion_to') ) { return $this->panelInit->apiOutput( false, $title, "Employee Promoted is missing" ); }
            if( !\Input::has('promotion_by') ) { return $this->panelInit->apiOutput( false, $title, "Promoted By is missing" ); }

            if( !\Input::has('current_dep_id') ) { return $this->panelInit->apiOutput( false, $title, "Current Department is missing" ); }
            if( !\Input::has('current_des_id') ) { return $this->panelInit->apiOutput( false, $title, "Current Designation is missing" ); }
            if( !\Input::has('current_pay_id') ) { return $this->panelInit->apiOutput( false, $title, "Current Pay Grade is missing" ); }
            if( !\Input::has('current_salary') ) { return $this->panelInit->apiOutput( false, $title, "Current Salary is missing" ); }

            if( !\Input::has('promoted_dep_id') ) { return $this->panelInit->apiOutput( false, $title, "Promoted Department is missing" ); }
            if( !\Input::has('promoted_des_id') ) { return $this->panelInit->apiOutput( false, $title, "Promoted Designation is missing" ); }
            if( !\Input::has('promoted_pay_id') ) { return $this->panelInit->apiOutput( false, $title, "Promoted Pay Grade is missing" ); }
            if( !\Input::has('new_salary') ) { return $this->panelInit->apiOutput( false, $title, "New Salary is missing" ); }

            $checkTo = Employee::find( \Input::get('promotion_to') );
            $checkBy = Employee::find( \Input::get('promotion_by') );
            if( !$checkTo ) { return $this->panelInit->apiOutput( false, $title, "Employee Promoted can't be found" ); }
            if( !$checkBy ) { return $this->panelInit->apiOutput( false, $title, "Promoted By can't be found" ); }
            if( !\Input::has('date') ) { return $this->panelInit->apiOutput( false, $title, "Promotion Date is missing" ); }
            
            $date = formatDate( \Input::get('date') );
            if( !$date ) { return $this->panelInit->apiOutput( false, $title, "Promotion Date has invalid format" ); }
            if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, $title, "Promotion Date has invalid format" ); }

            if( $checkTo->department_id != \Input::get('current_dep_id') ) { return $this->panelInit->apiOutput( false, $title, "Current Department is invalid" ); }
            if( $checkTo->designation_id != \Input::get('current_des_id') ) { return $this->panelInit->apiOutput( false, $title, "Current Designation is invalid" ); }
            if( $checkTo->pay_grade_id != \Input::get('current_pay_id') ) { return $this->panelInit->apiOutput( false, $title, "Current Pay Grade is invalid" ); }
            $currGrade = PayGrade::find( \Input::get('current_pay_id') );
            
            $checkDep = Department::find( \Input::get('promoted_dep_id') );
            if( !$checkDep ) { return $this->panelInit->apiOutput( false, $title, "Promoted Department is invalid" ); }
            
            $checkDes = Designation::find( \Input::get('promoted_des_id') );
            if( !$checkDes ) { return $this->panelInit->apiOutput( false, $title, "Promoted Designation is invalid" ); }
            
            $checkGrade = PayGrade::find( \Input::get('promoted_pay_id') );
            if( !$checkGrade ) { return $this->panelInit->apiOutput( false, $title, "Promoted Pay Grade is invalid" ); }

            $lastSecond = $date . " 23:59:59";
            if( time() > strtotime( $lastSecond ) )
            {
                $promotionStatus = 2;
                $employee = Employee::find( \Input::get('promotion_to') );
                $employee->department_id = \Input::get('promoted_dep_id');
                $employee->designation_id = \Input::get('promoted_des_id');
                $employee->pay_grade_id = \Input::get('promoted_pay_id');
                $employee->save();
            } else $promotionStatus = 1;
            
            $promotion->promotion_by = \Input::get('promotion_by');
            $promotion->promoted_department = \Input::get('promoted_dep_id');
            $promotion->promoted_designation = \Input::get('promoted_des_id');
            $promotion->promoted_pay_grade = \Input::get('promoted_pay_id');
            $promotion->new_salary = \Input::get('new_salary');
            $promotion->promotion_date = date('Y-m-d', toUnixStamp( $date ) );;
            $promotion->description = \Input::has('description') ? \Input::get('description') : NULL;;
            $promotion->updated_by = \Auth::user()->id;
            $promotion->status = $promotionStatus;
            $promotion->deleted = '0';
            $promotion->save();

            user_log('Promotions', 'edit', 'promotion to ' . $checkTo->first_name . ' ' . $checkTo->last_name );
            return $this->panelInit->apiOutput( true, $title, "Promotion Updated Successfully" );
        }
    }

    public function deletePromotion()
    {
        if( !$this->panelInit->can( "promotions.delete_promotion" ) ) { return $this->panelInit->apiOutput( false, "Delete promotion", "You don't have permission to delete promotions" ); }
        if( !\Input::has('promotion_id') ) { return $this->panelInit->apiOutput( false, "Delete promotion", "Promotion is not exists" ); }
        $id = \Input::get('promotion_id');
        $postDelete = Promotion::find( $id );
        if( !$postDelete ) { return $this->panelInit->apiOutput( false, "Delete promotion", "Termination is not exists" ); }
        $fullName = $postDelete->employee->first_name . " " . $postDelete->employee->last_name;
        $postDelete->deleted = 1;
        $postDelete->save();
        user_log('Promotions', 'delete', 'promotion to ' . $fullName );
        return $this->panelInit->apiOutput( true, "Delete promotion", "Promotion Deleted Successfully" );
    }
}