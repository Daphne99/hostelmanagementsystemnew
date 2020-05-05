<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models2\Role;

class HrRolesController extends Controller
{
    var $data = array();
	var $panelInit ;
    var $layout = 'dashboard';
	var $roles_perms = [
        "dashboard" => [
            "title" => "Dashboard",
            "roles" => ["absentStudents" => "Absent students", "absentStaff" => "Absent staff", "birthdays" => "Birthday list", "academicCalendar" => "Academic calendar", "myCalendar" => "My attendance (parents only)" , "feeDefaulters" => "Fee Defaulters"]
        ],
        "academicyears" => [
            "title" => "Academic Years",
            "roles" => [ "list" => "List", "addAcademicyear" => "Add academic year", "editAcademicYears" => "Edit academic year", "delAcademicYears" => "Delete academic year" ]
        ],
        "staticPages" => [
            "title" => "Static pages",
            "roles" => [ "list" => "List", "addPage" => "Add page", "editPage" => "Edit page", "delPage" => "Delete page" ]
        ],
        "Administrators" => [
            "title" => "Administrators",
            "roles" => [ "list" => "List", "addAdministrator" => "Add admininstrator", "editAdministrator" => "Edit admininstrator", "delAdministrator" => "Delete admininstrator" ]
        ],
        "employees" => [
            "title" => "Employees",
            "roles" => [ "list" => "List", "addEmployee" => "Add Employee", "editEmployee" => "Edit Employee", "delEmployee" => "Delete Employee" ]
        ],
        "AccountSettings" => [
            "title" => "Account Settings",
            "roles" => [ "myInvoices" => "My Invoices", "ChgProfileData" => "Change profile data", "chgEmailAddress" => "Change e-mail address", "chgPassword" => "Change password", "Messages" => "Messages" ]
        ],
        "dbExport" => [
            "title" => "DB Export",
            "roles" => [ "dbExport" => "DB Export" ]
        ],
        "classes" => [
            "title" => "Classes",
            "roles" => [ "list" => "List", "addClass" => "Add class", "editClass" => "Edit Class", "delClass" => "Delete class" ]
        ],
        "sections" => [
            "title" => "Sections",
            "roles" => [ "list" => "List", "addSection" => "Add section", "editSection" => "Edit section", "delSection" => "Delete section" ]
        ],
        "Subjects" => [
            "title" => "Subjects",
            "roles" => [ "list" => "List", "addSubject" => "Add subject", "editSubject" => "Edit Subject", "delSubject" => "Delete Subject", "mySubjects" => "Show my subjects" ]
        ],
        "adminTasks" => [
            "title" => "Administrative tasks",
            "roles" => [ "globalSettings" => "Global Settings", "activatedModules" => "Activated Modules", "paymentsSettings" => "Payments Settings", "mailSmsSettings" => "Mail/SMS Settings", "vacationSettings" => "Vacation Settings", "mobileSettings" => "Mobile application", "frontendCMS" => "frontend CMS", "bioItegration" => "Biometric Integration", "lookFeel" => "Look & Feel" ]
        ],
        "Expenses" => [
            "title" => "Expenses",
            "roles" => [ "list" => "List", "addExpense" => "Add Expense", "editExpense" => "Edit expense", "delExpense" => "Delete expense", "expCategory" => "Expenses Categories" ]
        ],
        "Incomes" => [
            "title" => "Incomes",
            "roles" => [ "list" => "List", "addIncome" => "Add Income", "editIncome" => "Edit Income", "delIncome" => "Delete Income", "incomeCategory" => "Incomes Categories" ]
        ],
        "Languages" => [
            "title" => "Languages",
            "roles" => [ "list" => "List", "addLanguage" => "Add language", "editLanguage" => "Edit Language", "delLanguage" => "Delete language" ]
        ],
        "Polls" => [
            "title" => "Polls",
            "roles" => [ "list" => "List", "addPoll" => "Add poll", "editPoll" => "Edit poll", "delPoll" => "Delete poll" ]
        ],
        "messaging" => [
            "title" => "Messaging",
            "roles" => [ "list" => "View Messages List", "View" => "Read Message", "addMsg" => "Compose Message", "editMsg" => "Edit Message", "delMsg" => "Delete Message" ]
        ],
        "newsboard" => [
            "title" => "News Board",
            "roles" => [ "list" => "List", "View" => "View", "addNews" => "Add News", "editNews" => "Edit News", "delNews" => "Delete news" ]
        ],
        "events" => [
            "title" => "Events",
            "roles" => [ "list" => "List", "View" => "View", "addEvent" => "Add event", "editEvent" => "Edit event", "delEvent" => "Delete event" ]
        ],
        "frontendCMSpages" => [
            "title" => "frontend CMS pages",
            "roles" => [ "list" => "List", "addPage" => "Add page", "editPage" => "Edit page", "delPage" => "Delete page" ]
        ],
        "mediaCenter" => [
            "title" => "Media Center",
            "roles" => [ "View" => "View", "addAlbum" => "Add album", "editAlbum" => "Edit album", "delAlbum" => "Delete album", "addMedia" => "Add media", "editMedia" => "Edit media", "delMedia" => "Delete media" ]
        ],
        "roles" => [
            "title" => "Permission Roles",
            "roles" => [ "list" => "List", "add_role" => "Add Role", "modify_role" => "Modify Role", "delete_role" => "Delete Role" ]
        ],
        "gradeLevels" => [
            "title" => "Grade levels",
            "roles" => [ "list" => "List", "addLevel" => "Add Grade level", "editGrade" => "Edit grade level", "delGradeLevel" => "Delete grade level" ]
        ],
        "Promotion" => [
            "title" => "Promotion",
            "roles" => [ "promoteStudents" => "Promote students" ]
        ],
        "mobileNotifications" => [
            "title" => "Mobile Notifications",
            "roles" => [ "sendNewNotification" => "Send new notification" ]
        ],
        "mailsms" => [
            "title" => "Mail / SMS",
            "roles" => [ "mailSMSSend" => "Send Mail / SMS", "mailSMSList" => "List Mail / SMS", "mailSMSAdd" => "Add Mail / SMS", "mailSMSDelete" => "Delete Mail / SMS", "mailsmsTemplates" => "Mail / SMS Templates" ]
        ],
        "FeeGroups" => [
            "title" => "Fee Groups",
            "roles" => [ "list" => "List", "addFeeGroup" => "Add fee group", "editFeeGroup" => "Edit fee group", "delFeeGroup" => "Delete fee group" ]
        ],
        "FeeTypes" => [
            "title" => "Fee Types",
            "roles" => [ "list" => "List", "addFeeType" => "Add fee type", "editFeeType" => "Edit fee type", "delFeeType" => "Delete fee type" ]
        ],
        "FeeAllocation" => [
            "title" => "Fee Allocation",
            "roles" => [ "list" => "List", "addFeeAllocation" => "Add fee allocation", "editFeeAllocation" => "Edit fee allocation", "delFeeAllocation" => "Delete fee allocation" ]
        ],
        "FeeDiscount" => [
            "title" => "Fee Discount",
            "roles" => [ "list" => "List", "addFeeDiscount" => "Add fee discount", "editFeeDiscount" => "Edit fee discount", "delFeeDiscount" => "Delete Fee discount", "assignUser" => "Assign to users" ]
        ],
        "Invoices" => [
            "title" => "Invoices",
            "roles" => [ "list" => "List", "View" => "View", "addPayment" => "Add payment", "editPayment" => "Edit payment", "delPayment" => "Delete payment", "collInvoice" => "Collect Invoice", "payRevert" => "Revert", "dueInvoices" => "Due Invoices", "Export" => "Export" ]
        ],
        "Assignments" => [
            "title" => "Assignments",
            "roles" => [ "list" => "List", "AddAssignments" => "Add assignment", "editAssignment" => "Edit Assignment", "delAssignment" => "Delete assignment", "viewAnswers" => "View answers", "applyAssAnswer" => "Apply assignment answer", "Download" => "Download" ]
        ],
        "studyMaterial" => [
            "title" => "Study Material",
            "roles" => [ "list" => "List", "addMaterial" => "Add Material", "editMaterial" => "Edit Material", "delMaterial" => "Delete Material", "Download" => "Download" ]
        ],
        "Homework" => [
            "title" => "Homework",
            "roles" => [ "list" => "List", "View" => "View", "addHomework" => "Add Homework", "editHomework" => "Edit Homework", "delHomework" => "Delete Homework", "Download" => "Download", "Answers" => "Answers" ]
        ],
        "Payroll" => [
            "title" => "Payroll",
            "roles" => [ "makeUsrPayment" => "Make user payment", "delUsrPayment" => "Delate user payment", "userSalary" => "Users Salary", "salaryBase" => "Salary Base", "hourSalary" => "Hourly Base" ]
        ],
        "classSch" => [
            "title" => "Class Timetable",
            "roles" => [ "list" => "View class timetable", "addSch" => "Add Schedule", "editSch" => "Edit Schedule", "delSch" => "Delete schedule" ]
        ],
        "timeTableClassWise" => [
            "title" => "institution timetable (class wise)",
            "roles" => [ "list" => "View timetable", "addSch" => "Add Schedule", "editSch" => "Edit Schedule", "delSch" => "Delete schedule" ]
        ],
        "timeTableTeacherWise" => [
            "title" => "institution timetable (teacher wise)",
            "roles" => [ "list" => "View timetable", "addSch" => "Add Schedule", "editSch" => "Edit Schedule", "delSch" => "Delete schedule" ]
        ],
        "teacherAvailabilityPresence" => [
            "title" => "Teacher Availability & Presence",
            "roles" => [ "showAvailability" => "View Teacher Availability", "showPresence" => "View Teacher Presence" ]
        ],
        "parents" => [
            "title" => "Parents",
            "roles" => [ "list" => "List", "AddParent" => "Add parent", "editParent" => "Edit Parent", "delParent" => "Delete parent", "Approve" => "Approve", "Import" => "Import", "Export" => "Export" ]
        ],
        "teachers" => [
            "title" => "Teachers",
            "roles" => [ "list" => "List", "addTeacher" => "Add teacher", "EditTeacher" => "Edit Teacher", "delTeacher" => "Delete teacher", "Approve" => "Approve", "teacLeaderBoard" => "Teacher leaderboard", "Import" => "Import", "Export" => "Export" ]
        ],
        "students" => [
            "title" => "Students",
            "roles" => [ "list" => "List", "admission" => "Students Admission", "editStudent" => "Edit student", "delStudent" => "Delete student", "listGradStd" => "List Graduate Students", "Approve" => "Approve", "stdLeaderBoard" => "Student leaderboard", "Import" => "Import", "Export" => "Export", "Attendance" => "Attendance", "Marksheet" => "Marksheet", "medHistory" => "Medical History", "std_cat" => "Student Categories", "TrackBus" => "Track Bus" ]
        ],
        "studentType" => [
            "title" => "Fee Group Type",
            "roles" => [ "list" => "List", "add" => "Add", "edit" => "Edit", "del" => "Delete" ]
        ],
        "studentCat" => [
            "title" => "Student Category",
            "roles" => [ "list" => "List", "add" => "Add", "edit" => "Edit", "del" => "Delete" ]
        ],
        "genMarksheet" => [
            "title" => "Generate Marksheet",
            "roles" => [ "list" => "list Report Cards", "view" => "Show Report Card", "create" => "Generate Marksheet", "edit" => "Update Marksheet", "delete" => "Remove Marksheet" ]
        ],
        "examsList" => [
            "title" => "Exams List",
            "roles" => [ "list" => "List", "View" => "View", "addExam" => "Add exam", "editExam" => "Edit Exam", "delExam" => "Delete exam", "examDetailsNot" => "Exam details notifications", "showMarks" => "Show marks", "controlMarksExam" => "Control marks for Exam" ]
        ],
        "schoolTerms" => [
            "title" => "Privacy Policy",
            "roles" => [ "list" => "List", "View" => "View", "add" => "Add", "edit" => "Edit", "del" => "Delete" ]
        ],
        "onlineExams" => [
            "title" => "Online Exams",
            "roles" => [ "list" => "List", "addExam" => "Add exam", "editExam" => "Edit Exam", "delExam" => "Delete exam", "takeExam" => "Take exam", "showMarks" => "Show marks", "QuestionsArch" => "Questions Bank" ]
        ],
        "dashboard" => [
            "title" => "Dashboard",
            "roles" => [ "stats" => "Statistics" ]
        ],
        "wel_office_cat" => [
            "title" => "Office Categories",
            "roles" => [ "list" => "List", "add_cat" => "Add Category", "edit_cat" => "Edit Category", "del_cat" => "Delete Category" ]
        ],
        "visitors" => [
            "title" => "Visitors",
            "roles" => [ "list" => "List", "View" => "View", "add_vis" => "Add Visitor", "edit_vis" => "Edit Visitor", "del_vis" => "Delete visitor", "Download" => "Download", "Export" => "Export" ]
        ],
        "phn_calls" => [
            "title" => "Phone Calls",
            "roles" => [ "list" => "List", "View" => "View", "add_call" => "Add phone call", "edit_call" => "Edit phone Call", "del_call" => "Delete phone Call", "Export" => "Export" ]
        ],
        "postal" => [
            "title" => "Postal",
            "roles" => [ "list" => "List", "add_postal" => "Add postal", "edit_postal" => "Edit postal", "del_postal" => "Delete Postal", "Download" => "Download", "Export" => "Export" ]
        ],
        "con_mess" => [
            "title" => "Contact Messages",
            "roles" => [ "list" => "List", "View" => "View", "del_mess" => "Delete Message", "Export" => "Export" ]
        ],
        "enquiries" => [
            "title" => "Enquiries",
            "roles" => [ "list" => "List", "View" => "View", "add_enq" => "Add Enquiry", "edit_enq" => "Edit Enquiry", "del_enq" => "Delete Enquiry", "Download" => "Download", "Export" => "Export" ]
        ],
        "complaints" => [
            "title" => "Complaints",
            "roles" => [ "list" => "List", "View" => "View", "add_complaint" => "Add Complaint", "edit_complaint" => "Edit Complaint", "del_complaint" => "Delete Complaint", "Download" => "Download", "Export" => "Export" ]
        ],
        "trans_vehicles" => [
            "title" => "Transport vehicles",
            "roles" => [ "list" => "List", "add_vehicle" => "Add Vehicle", "edit_vehicle" => "Edit Vehicle", "del_vehicle" => "Delete vehicle" ]
        ],
        "Transportation" => [
            "title" => "Stoppage",
            "roles" => [ "list" => "List", "addTransport" => "Add stoppage", "editTransport" => "Edit stoppage", "delTrans" => "Delete stoppage", "members" => "Members" ]
        ],
        "quicktransportation" => [
            "title" => "Quick assign transportation",
            "roles" => [ "list" => "List transport", "add" => "Add transport", "edit" => "Edit transport", "del" => "Delete transport", "view" => "View Transport Details", "members" => "View members", "fees" => "Allocate Transportation Fees" ]
        ],
        "Hostels" => [
            "title" => "Manage Hostels",
            "roles" => [ "list" => "List hostels", "add" => "Add Hostel", "edit" => "Edit Hostels", "del" => "Delete Hostels", "view" => "View Hostel Details", "members" => "View members", "fees" => "Allocate Hostel Fees" ]
        ],
        "Hostel" => [
            "title" => "Hostel",
            "roles" => [ "list" => "List", "AddHostel" => "Add Hostel", "EditHostel" => "Edit hostel", "delHostel" => "Delete Hostel", "listSubs" => "List subscribers", "HostelCat" => "Hostel Categories" ]
        ],
        "depart" => [
            "title" => "Departments",
            "roles" => [ "list" => "List", "add_depart" => "Add Departmnet", "edit_depart" => "Edit Department", "del_depart" => "Delete department" ]
        ],
        "desig" => [
            "title" => "Designations",
            "roles" => [ "list" => "List", "add_desig" => "Add Designations", "edit_desig" => "Edit Designations", "del_desig" => "Delete designation" ]
        ],
        "Attendance" => [
            "title" => "Attendance",
            "roles" => [ "takeAttendance" => "Take Attendance", "attReport" => "Attendance Report" ]
        ],
        "myAttendance" => [
            "title" => "My Attendance",
            "roles" => [ "myAttendance" => "My Attendance" ]
        ],
        "staffAttendance" => [
            "title" => "Staff Attendance",
            "roles" => [ "takeAttendance" => "Take Attendance", "attReport" => "Attendance Report" ]
        ],
        "Vacation" => [
            "title" => "Vacation",
            "roles" => [ "reqVacation" => "Request vacation", "appVacation" => "Approve vacation", "myVacation" => "My vacations" ]
        ],
        "iss_ret" => [
            "title" => "Issue/Return",
            "roles" => [ "list" => "List", "issue_item" => "Issue Item", "edit_item" => "Edit Item", "del_item" => "Delete item", "Download" => "Download", "Export" => "Export" ]
        ],
        "items_stock" => [
            "title" => "Items Stock",
            "roles" => [ "list" => "List", "add_item" => "Add item", "edit_item" => "Edit Item", "del_item" => "Delete item", "Download" => "Download", "Export" => "Export" ]
        ],
        "inv_cat" => [
            "title" => "Inventory Categories",
            "roles" => [ "list" => "List", "add_cat" => "Add Category", "edit_cat" => "Edit Category", "del_cat" => "Delete Category" ]
        ],
        "suppliers" => [
            "title" => "Suppliers",
            "roles" => [ "list" => "List", "add_supp" => "Add Supplier", "edit_supp" => "Edit Supplier", "del_supp" => "Delete Supplier", "Export" => "Export" ]
        ],
        "stores" => [
            "title" => "Stores",
            "roles" => [ "list" => "List", "add_store" => "Add store", "edit_store" => "Edit store", "del_store" => "Delete store" ]
        ],
        "items_code" => [
            "title" => "Item Coding",
            "roles" => [ "list" => "List", "add_item" => "Add item", "edit_item" => "Edit Item", "del_item" => "Delete item", "Export" => "Export" ]
        ],
        "Library" => [
            "title" => "Library",
            "roles" => [ "list" => "List", "addBook" => "Add book", "editBook" => "Edit book", "delLibrary" => "Delete library item", "Download" => "Download", "mngSub" => "Manage Subscription", "Export" => "Export", "Import" => "Import" ]
        ],
        "issue_book" => [
            "title" => "Issue Book",
            "roles" => [ "list" => "List", "add_issue" => "Create book issue", "edit_issue" => "Edit book issue", "del_issue" => "Delete book issue", "Export" => "Export", "book_return" => "Return Book" ]
        ],
        "Certificates" => [
            "title" => "Certificates",
            "roles" => [ "list" => "List", "add_cert" => "Add Certificate", "edit_cert" => "Edit certificate", "del_cert" => "Delete Certificate" ]
        ],
        "Reports" => [
            "title" => "Reports",
            "roles" => [ "Reports" => "Reports", "userLog" => "User Log" ]
        ],
        "Disciplines" => [
            "title" => "Disciplines",
            "roles" => [ "list" => "List", "View" => "View", "add" => "Add", "edit" => "Edit", "del" => "Delete", "Download" => "Download" ]
        ],
        "feedbacks" => [
            "title" => "Teacher Feedback",
            "roles" => [ "list" => "List Feedbacks", "add" => "Add Feedback", "edit" => "Edit Feedback", "del" => "Delete Feedback", "View" => "View Feedback", "evaluate" => "Evaluate Teacher" ]
        ],
        "departments" => [
            "title" => "Departments managment",
            "roles" => [ "list" => "View departments", "add_department" => "Add department", "edit_department" => "Edit department", "delete_department" => "Delete department" ]
        ],
        "designations" => [
            "title" => "Designations managment",
            "roles" => [ "list" => "View designations", "add_designation" => "Add designation", "edit_designation" => "Edit designation", "delete_designation" => "Delete designation" ]
        ],
        "branchs" => [
            "title" => "Branchs managment",
            "roles" => [ "list" => "View designations", "add_branch" => "Add Branch", "edit_branch" => "Edit Branch", "delete_branch" => "Delete Branch" ]
        ],
        "employees" => [
            "title" => "Employees managment",
            "roles" => [ "list" => "List employees", "view_employee" => "View employee", "add_employee" => "Add employee", "edit_employee" => "Edit employee", "delete_employee" => "Delete employee", "myProfile" => "My profile" ]
        ],
        "warnings" => [
            "title" => "Warnings managment",
            "roles" => [ "list" => "List warnings", "view_warning" => "View warning", "add_warning" => "Add warning", "edit_warning" => "Edit warning", "delete_warning" => "Delete warning" ]
        ],
        "terminations" => [
            "title" => "Terminations managment",
            "roles" => [ "list" => "List terminations", "view_termination" => "View termination", "add_terminations" => "Add termination", "edit_terminations" => "Edit termination", "delete_terminations" => "Delete termination" ]
        ],
        "promotions" => [
            "title" => "Promotions managment",
            "roles" => [ "list" => "List promotions", "view_promotion" => "View promotion", "add_promotion" => "Add promotion", "edit_promotion" => "Edit promotion", "delete_promotion" => "Delete promotion" ]
        ],
        "payroll_setup" => [
            "title" => "Payroll Setup",
            "roles" => [ "tax_setup" => "View Taxes", "tax_manage" => "Edit Taxes", "late_setup" => "View Late Configration", "late_manage" => "Edit Late Configration" ]
        ],
        "allowance" => [
            "title" => "Manage allowance",
            "roles" => [ "list" => "List allowance", "add" => "Add allowance", "edit" => "Edit allowance", "delete" => "Delete allowance" ]
        ],
        "deduction" => [
            "title" => "Manage deduction",
            "roles" => [ "list" => "List deduction", "add" => "Add deduction", "edit" => "Edit deduction", "delete" => "Delete deduction" ]
        ],
        "paygrade_monthly" => [
            "title" => "Monthly Paygrade",
            "roles" => [ "list" => "List monthly paygrade", "add" => "Add monthly paygrade", "edit" => "Edit monthly paygrade", "delete" => "Delete monthly paygrade" ]
        ],
        "paygrade_hourly" => [
            "title" => "Hourly Paygrade",
            "roles" => [ "list" => "List hourly paygrade", "add" => "Add hourly paygrade", "edit" => "Edit hourly paygrade", "delete" => "Delete hourly paygrade" ]
        ],
        "bonuses" => [
            "title" => "Manage Bonus",
            "roles" => [ "list" => "List Bonus", "add" => "Add Bonus", "edit" => "Edit Bonus", "delete" => "Delete Bonus", "apply" => "Generate Bonus" ]
        ],
        "workhours" => [
            "title" => "Manage Work Hour",
            "roles" => [ "approve" => "Approve Work Hour" ]
        ],
        "payroll_reports" => [
            "title" => "Payroll Reports",
            "roles" => [ "list" => "List Payment History", "personal" => "View My Payroll", "payslip" => "Generate Payslip" ]
        ],
        "salary_sheet" => [
            "title" => "Salary Sheet",
            "roles" => [ "list" => "List Payment Info", "add" => "Generate Salary Sheet", "payslip" => "Generate Payslip", "payment" => "Make Payments" ]
        ],
        "workshifts" => [
            "title" => "Manage Work Shifts",
            "roles" => [ "list" => "List Work Shifts", "add" => "Add Work Shift", "edit" => "Edit Work Shift", "delete" => "Delete Work Shift" ]
        ],
        "attendances" => [
            "title" => "Employee Attendance",
            "roles" => [ "list" => "List Employee Attendance", "take" => "Employee Employee Attendance" ]
        ],
        "attendances_reports" => [
            "title" => "Attendance Reports",
            "roles" => [ "daily" => "Daily Attendance", "monthly" => "Monthly Attendance", "myReport" => "My Attendance Report", "summary" => "Summary Report" ]
        ],
        "holidays" => [
            "title" => "Manage Holiday",
            "roles" => [ "list" => "List Holidays", "add" => "Add Holiday", "edit" => "Edit Holiday", "delete" => "Delete Holiday" ]
        ],
        "public_holidays" => [
            "title" => "Manage Public Holiday",
            "roles" => [ "list" => "List Public Holidays", "add" => "Add Public Holiday", "edit" => "Edit Public Holiday", "delete" => "Delete Public Holiday" ]
        ],
        "weekly_holidays" => [
            "title" => "Manage Weekly Holiday",
            "roles" => [ "list" => "List Weekly Holidays", "add" => "Add Weekly Holiday", "edit" => "Edit Weekly Holiday", "delete" => "Delete Weekly Holiday" ]
        ],
        "leaveType" => [
            "title" => "Manage Leave Types",
            "roles" => [ "list" => "List Leave Types", "add" => "Add Leave Type", "edit" => "Edit Leave Type", "delete" => "Delete Leave Type" ]
        ],
        "earnLeaveConf" => [
            "title" => "Earn Leave",
            "roles" => [ "view" => "View Earn Leave Configuration", "apply" => "Update Earn Leave Configuration" ]
        ],
        "leaves" => [
            "title" => "Leave Application",
            "roles" => [ "list" => "View Requested Application", "action" => "Update Application Status", "apply" => "Apply Application for leave" ]
        ],
        "leaveReports" => [
            "title" => "Leave Application",
            "roles" => [ "leave" => "View Leave Report", "summary" => "View Summary Report", "my" => "My Leave Report" ]
        ],
        "manageStudents" => [
            "title" => "Manage Students",
            "roles" => [ "add" => "Create new student" ]
        ],
        "subjectVideos" => [
            "title" => "Subject videos",
            "roles" => [ "list" => "List subject videos" ]
        ],
    ];
    
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
    
    public function listAll( $page )
    {
        if( !$this->panelInit->can( array( "roles.list", "roles.add_role", "roles.modify_role", "roles.delete_role" ) ) )
        {
			exit;
		}
        $toReturn = array();
        $getRoles = Role::select('id', 'role_title', 'role_description');
        $toReturn["rolesCount"] = $getRoles->count();
        $getRoles = $getRoles->take(all_pagination_number())->skip(all_pagination_number() * ($page - 1) )->get()->toArray();
        $toReturn["roles"] = $getRoles;
        $toReturn["roles_perms"] = $this->roles_perms;
        
        $customPerms = [];
        $customPerms['dashboard'] = [
            "title" => "Dashboard", "roles" => [ "dashboard.absentStudents" => "Absent students", "dashboard.absentStaff" => "Absent staff", "dashboard.birthdays" => "Birthday list", "dashboard.academicCalendar" => "Academic calendar", "dashboard.myCalendar" => "My attendance (parents only)" , "dashboard.feeDefaulters" => "Fee Defaulters" ]
        ];
        $customPerms['viewRecords'] = [
            [
                "index" => "visitors",
                "title" => "Visitors",
                "roles" => [ "list" => "List", "View" => "View", "edit_vis" => "Edit Visitor", "del_vis" => "Delete visitor" ]
            ],
            [
                "index" => "phn_calls",
                "title" => "Phone Calls",
                "roles" => [ "list" => "List", "View" => "View", "edit_call" => "Edit phone Call", "del_call" => "Delete phone Call" ]
            ],
            [
                "index" => "enquiries",
                "title" => "Enquiries",
                "roles" => [ "list" => "List", "View" => "View", "edit_enq" => "Edit Enquiry", "del_enq" => "Delete Enquiry" ]
            ]
        ];
        $customPerms['addRecords'] = [
            ["index" => "visitors", "title" => "Visitors", "roles" => [ "add_vis" => "Add Visitor" ] ],
            ["index" => "phn_calls", "title" => "Phone Calls", "roles" => [ "add_call" => "Add phone call" ] ],
            ["index" => "enquiries", "title" => "Enquiries", "roles" => [ "add_enq" => "Add Enquiry" ] ]
        ];
        $customPerms['viewMessages'] = [ "title" => "Messaging", "roles" => [ "list" => "View Messages List", "View" => "Read Message", "editMsg" => "Edit Message", "delMsg" => "Delete Message" ] ];
        $customPerms['composeMessage'] = [ "title" => "Messaging", "roles" => [ "addMsg" => "Compose Message"] ];
        $customPerms['viewEvents'] = [ "title" => "Events", "roles" => [ "list" => "List Events", "View" => "View Event", "editEvent" => "Edit event", "delEvent" => "Delete event" ] ];
        $customPerms['composeEvent'] = [ "title" => "Events", "roles" => [ "addEvent" => "Add event" ] ];
        $customPerms['viewNotices'] = [ "title" => "Notice", "roles" => [ "list" => "List Notices", "View" => "View Notice", "editNews" => "Edit Notice", "delNews" => "Delete Notice" ] ];
        $customPerms['composeNotice'] = [ "title" => "Notice", "roles" => [ "addNews" => "Add Notice" ] ];
        $customPerms['viewMailSms'] = [ "title" => "Mail / SMS", "roles" => [ "mailSMSSend" => "Send Mail / SMS", "mailSMSList" => "List Mail / SMS", "mailSMSDelete" => "Delete Mail / SMS" ] ];
        $customPerms['composeMailSms'] = [ "title" => "Mail / SMS", "roles" => [ "mailSMSAdd" => "Add Mail / SMS" ] ];
        $customPerms['academicyears'] = [ "title" => "Academic Years", "roles" => [ "list" => "List Years", "addAcademicyear" => "Add academic year", "editAcademicYears" => "Edit academic year", "delAcademicYears" => "Delete academic year" ] ];
        $customPerms['subjectAssign'] = [ "title" => "Enter Subject", "roles" => [ "addSubject" => "Add subject", "sampleDownload" => "Download Sample Format", "excelImport" => "Import Excel" ] ];
        $customPerms['classSectionTeacher'] = [
            "title" => "Classes, Sections and Class Teachers",
            "roles" => [
                "classes.list" => "List classes", "classes.addClass" => "Create class", "classes.editClass" => "Edit class", "classes.delClass" => "Delete class",
                "sections.list" => "List sections", "sections.addSection" => "Create section", "sections.editSection" => "Edit section", "sections.delSection" => "Delete section",
                "teachers.list" => "List teachers", "teachers.addTeacher" => "Create teacher", "teachers.EditTeacher" => "Edit teacher", "teachers.delTeacher" => "Delete teacher",
            ]
        ];
        $customPerms['homework'] = [ "title" => "Homework", "roles" => [ "list" => "List homeworks", "View" => "View homework", "addHomework" => "Add Homework", "editHomework" => "Edit Homework", "delHomework" => "Delete Homework", "Download" => "Download", "Answers" => "Answers" ] ];
        $customPerms['Assignments'] = [ "title" => "Assignments", "roles" => [ "list" => "List Assignments", "AddAssignments" => "Add assignment", "editAssignment" => "Edit Assignment", "delAssignment" => "Delete assignment", "viewAnswers" => "View answers", "applyAssAnswer" => "Apply assignment answer", "Download" => "Download" ] ];
        $customPerms['studyMaterial'] = [ "title" => "Materials", "roles" => [ "list" => "List Materials", "addMaterial" => "Add Material", "editMaterial" => "Edit Material", "delMaterial" => "Delete Material", "Download" => "Download" ] ];
        $customPerms['showStudents'] = [ "title" => "Students", "roles" => [ "list" => "List Students", "editStudent" => "Edit student", "delStudent" => "Delete student", "listGradStd" => "List Graduate Students", "Approve" => "Approve", "stdLeaderBoard" => "Student leaderboard", "invoices" => "Show Invoices", "docs" => "Manage documents", "hostel" => "Show hostel details", "transport" => "Show transport details", "Attendance" => "Show Attendance", "Marksheet" => "Show Marksheet", "medHistory" => "Medical History", "std_cat" => "Student Categories", "TrackBus" => "Bus Tracker", "discipline" => "Manage disciplines" ] ];
        $customPerms['studentsAttendance'] = [ "title" => "Students Attendance", "roles" => [ "Attendance.takeAttendance" => "Attendance Register", "Attendance.attReport" => "Attendance Report" ] ];
        $customPerms['manageStudents'] = [ "title" => "Student Admission", "roles" => [ "manageStudents.add" => "Create new student", "students.admission" => "Students Admission", "students.Import" => "Import", "students.Export" => "Export" ] ];
        $customPerms['studentType'] = [ "title" => "Fee Group Type", "roles" => [ "studentType.list" => "List Types", "studentType.add" => "Add Type", "studentType.edit" => "Edit Type", "studentType.del" => "Delete Type" ] ];
        $customPerms['studentCat'] = [ "title" => "Student Category", "roles" => [ "studentCat.list" => "List Type", "studentCat.add" => "Add Type", "studentCat.edit" => "Edit Type", "studentCat.del" => "Delete Type" ] ];
        $customPerms['Promotion'] = [ "title" => "Student Promotion", "roles" => [ "Promotion.promoteStudents" => "Promote students" ] ];
        $customPerms['teachers'] = [ "title" => "Teachers", "roles" => [ "teachers.list" => "View Teachers" ] ];
        $customPerms['timeTable'] = [ "title" => "class timetable", "roles" => [ "timeTableClassWise.list" => "View timetable", "timeTableClassWise.addSch" => "Add Schedule", "timeTableClassWise.editSch" => "Edit Schedule", "timeTableClassWise.delSch" => "Delete schedule" ] ];
        $customPerms['timeTableClassWise'] = [ "title" => "class wise", "roles" => [ "timeTableClassWise.list" => "View timetable", "timeTableClassWise.addSch" => "Add Schedule", "timeTableClassWise.editSch" => "Edit Schedule", "timeTableClassWise.delSch" => "Delete schedule" ] ];
        $customPerms['timeTableTeacherWise'] = [ "title" => "teacher wise", "roles" => [ "timeTableTeacherWise.list" => "View timetable", "timeTableTeacherWise.addSch" => "Add Schedule", "timeTableTeacherWise.editSch" => "Edit Schedule", "timeTableTeacherWise.delSch" => "Delete schedule" ] ];
        $customPerms['teacherAvailability'] = [ "title" => "Teacher Availability", "roles" => [ "teacherAvailabilityPresence.showAvailability" => "View Teacher Availability" ] ];
        $customPerms['teacherPresence'] = [ "title" => "Teacher Presence", "roles" => [ "teacherAvailabilityPresence.showPresence" => "View Teacher Presence" ] ];
        $customPerms['Invoices'] = [ "title" => "Invoices", "roles" => [ "Invoices.list" => "List", "Invoices.View" => "View", "Invoices.addPayment" => "Add payment", "Invoices.editPayment" => "Edit payment", "Invoices.delPayment" => "Delete payment", "Invoices.collInvoice" => "Collect Invoice", "Invoices.payRevert" => "Revert", "Invoices.dueInvoices" => "Due Invoices", "Invoices.Export" => "Export" ] ];
        $customPerms['gradeLevels'] = [ "title" => "Grade levels", "roles" => [ "list" => "List grade levels", "addLevel" => "Add grade level", "editGrade" => "Edit grade level", "delGradeLevel" => "Delete grade level" ] ];
        $customPerms['examsList'] = [ "title" => "Exam Schedule", "roles" => [ "list" => "List Exams", "View" => "View Exam", "addExam" => "Add exam", "editExam" => "Edit Exam", "delExam" => "Delete exam", "examDetailsNot" => "Exam details notifications", "showMarks" => "Show marks", "controlMarksExam" => "Control marks for Exam" ] ];
        $customPerms['onlineExams'] = [ "title" => "Online exams", "roles" => [ "list" => "List Exams", "addExam" => "Add exam", "editExam" => "Edit Exam", "delExam" => "Delete exam", "takeExam" => "Take exam", "showMarks" => "Show marks", "QuestionsArch" => "Questions Bank" ] ];
        $customPerms['schoolTerms'] = [ "title" => "School terms", "roles" => [ "list" => "List Terms", "View" => "View Term", "add" => "Add Term", "edit" => "Edit Term", "del" => "Delete Term" ] ];
        $customPerms['genMarksheet'] = [ "title" => "Report Card", "roles" => [ "list" => "list Report Cards", "view" => "Show Report Card", "create" => "Generate Marksheet", "edit" => "Update Marksheet", "delete" => "Remove Marksheet" ] ];
        $customPerms['transportation'] = [ "title" => "Transpoetation", "roles" => [ "quicktransportation.list" => "List transport", "quicktransportation.add" => "Add transport", "quicktransportation.edit" => "Edit transport", "quicktransportation.del" => "Delete transport", "quicktransportation.view" => "View Transport Details", "quicktransportation.members" => "View & Find members", "quicktransportation.fees" => "Allocate Transportation Fees" ] ];
        $customPerms['transportationMembers'] = [ "title" => "Find Memebers", "roles" => [ "quicktransportation.members" => "View & Find members" ] ];
        $customPerms['hostel'] = [ "title" => "hostels", "roles" => [ "Hostels.list" => "List hostels", "Hostels.add" => "Add Hostel", "Hostels.edit" => "Edit Hostels", "Hostels.del" => "Delete Hostels", "Hostels.view" => "View Hostel Details", "Hostels.members" => "View & Find members", "Hostels.fees" => "Allocate Hostel Fees" ] ];
        $customPerms['hostelMembers'] = [ "title" => "Find Memebers", "roles" => [ "Hostels.members" => "View & Find members" ] ];
        $customPerms['hrRoles'] = [ "title" => "Permissions", "roles" => [ "roles.list" => "List roles", "roles.add_role" => "Add Role", "roles.modify_role" => "Modify Role", "roles.delete_role" => "Delete Role" ] ];
        $customPerms['hrEmployees'] = [
            [ "index" => "departments", "title" => "Departments", "roles" => [ "list" => "List departments", "add_department" => "Add department", "edit_department" => "Edit department", "delete_department" => "Delete department" ] ],
            [ "index" => "designations", "title" => "Designations", "roles" => [ "list" => "List designations", "add_designation" => "Add designation", "edit_designation" => "Edit designation", "delete_designation" => "Delete designation" ] ],
            [ "index" => "branchs", "title" => "Branchs", "roles" => [ "list" => "List designations", "add_branch" => "Add Branch", "edit_branch" => "Edit Branch", "delete_branch" => "Delete Branch" ] ],
            [ "index" => "employees", "title" => "Employees", "roles" => [ "list" => "List employees", "view_employee" => "View employee", "add_employee" => "Add employee", "edit_employee" => "Edit employee", "delete_employee" => "Delete employee", "myProfile" => "My profile" ] ],
            [ "index" => "warnings", "title" => "Warnings", "roles" => [ "list" => "List warnings", "view_warning" => "View warning", "add_warning" => "Add warning", "edit_warning" => "Edit warning", "delete_warning" => "Delete warning" ] ],
            [ "index" => "terminations", "title" => "Terminations", "roles" => [ "list" => "List terminations", "view_termination" => "View termination", "add_terminations" => "Add termination", "edit_terminations" => "Edit termination", "delete_terminations" => "Delete termination" ] ],
            [ "index" => "promotions", "title" => "Promotions", "roles" => [ "list" => "List promotions", "view_promotion" => "View promotion", "add_promotion" => "Add promotion", "edit_promotion" => "Edit promotion", "delete_promotion" => "Delete promotion" ] ],
        ];

        $customPerms['hrLeaves'] = [
            [ "index" => "holidays", "title" => "Setup - Holiday", "roles" => [ "list" => "List Holidays", "add" => "Add Holiday", "edit" => "Edit Holiday", "delete" => "Delete Holiday" ] ],
            [ "index" => "public_holidays", "title" => "Setup - Public Holiday", "roles" => [ "list" => "List Public Holidays", "add" => "Add Public Holiday", "edit" => "Edit Public Holiday", "delete" => "Delete Public Holiday" ] ],
            [ "index" => "weekly_holidays", "title" => "Setup - Weekly Holiday", "roles" => [ "list" => "List Weekly Holidays", "add" => "Add Weekly Holiday", "edit" => "Edit Weekly Holiday", "delete" => "Delete Weekly Holiday" ] ],
            [ "index" => "leaveType", "title" => "Setup - Leave Types", "roles" => [ "list" => "List Leave Types", "add" => "Add Leave Type", "edit" => "Edit Leave Type", "delete" => "Delete Leave Type" ] ],
            [ "index" => "leaves", "title" => "Leave Application - Apply for leave", "roles" => [ "list" => "View Requested Application", "action" => "Update Application Status" ] ],
            [ "index" => "leaves", "title" => "Leave Application - Requested Applications", "roles" => [ "apply" => "Apply Application for leave" ] ],
            [ "index" => "leaveReports", "title" => "Report", "roles" => [ "leave" => "View Leave Report", "summary" => "View Summary Report", "my" => "View My Leave Report" ] ],
        ];

        $customPerms['hrAttendance'] = [
            [ "index" => "workshifts", "title" => "Work Shifts", "roles" => [ "list" => "List Work Shifts", "add" => "Add Work Shift", "edit" => "Edit Work Shift", "delete" => "Delete Work Shift" ] ],
            [ "index" => "attendances_reports", "title" => "Reports", "roles" => [ "daily" => "Daily Attendance", "monthly" => "Monthly Attendance", "myReport" => "My Attendance Report", "summary" => "Summary Report" ] ],
        ];

        $customPerms['hrPayroll'] = [
            [ "index" => "payroll_setup", "title" => "Payroll Setup", "roles" => [ "tax_setup" => "View Taxes", "tax_manage" => "Edit Taxes", "late_setup" => "View Late Configration", "late_manage" => "Edit Late Configration" ] ],
            [ "index" => "allowance", "title" => "Allowance & Deduction - Allowance", "roles" => [ "list" => "List allowance", "add" => "Add allowance", "edit" => "Edit allowance", "delete" => "Delete allowance" ] ],
            [ "index" => "deduction", "title" => "Allowance & Deduction - Deduction", "roles" => [ "list" => "List deduction", "add" => "Add deduction", "edit" => "Edit deduction", "delete" => "Delete deduction" ] ],
            [ "index" => "paygrade_monthly", "title" => "Pay Grade - Monthly", "roles" => [ "list" => "List monthly paygrade", "add" => "Add monthly paygrade", "edit" => "Edit monthly paygrade", "delete" => "Delete monthly paygrade" ] ],
            [ "index" => "paygrade_hourly", "title" => "Pay Grade - Hourly", "roles" => [ "list" => "List hourly paygrade", "add" => "Add hourly paygrade", "edit" => "Edit hourly paygrade", "delete" => "Delete hourly paygrade" ] ],
            [ "index" => "salary_sheet", "title" => "Salary Sheet", "roles" => [ "list" => "List Payment Info", "add" => "Generate Salary Sheet", "payslip" => "Generate Payslip", "payment" => "Make Payments" ] ],
            [ "index" => "payroll_reports", "title" => "Reports", "roles" => [ "list" => "List Payment History", "personal" => "View My Payroll", "payslip" => "Generate Payslip" ] ],
            [ "index" => "workhours", "title" => "Manage Work Hour", "roles" => [ "approve" => "Approve Work Hour" ] ],
            [ "index" => "bonuses", "title" => "Bonus - Settings", "roles" => [ "list" => "List Bonus", "add" => "Add Bonus", "edit" => "Edit Bonus", "delete" => "Delete Bonus" ] ],
            [ "index" => "bonuses", "title" => "Bonus - Generate", "roles" => [ "apply" => "Generate Bonus" ] ],
        ];
        $customPerms['mediaCenter'] = [ "title" => "Gallery", "roles" => [ "mediaCenter.View" => "View", "mediaCenter.addAlbum" => "Add album", "mediaCenter.editAlbum" => "Edit album", "mediaCenter.delAlbum" => "Delete album", "mediaCenter.addMedia" => "Add media", "mediaCenter.editMedia" => "Edit media", "mediaCenter.delMedia" => "Delete media" ] ];
        $customPerms['disciplines'] = [ "title" => "Disciplines", "roles" => [ "Disciplines.list" => "List disciplines", "Disciplines.View" => "View discipline", "Disciplines.add" => "Add discipline", "Disciplines.edit" => "Edit discipline", "Disciplines.del" => "Delete discipline", "Disciplines.Download" => "Download discipline" ] ];

        $customPerms['userLogs'] = [ "title" => "User Logs", "roles" => [ "Reports.Reports" => "View logs, payment and marks reports" ] ];
        $customPerms['stdAttendance'] = [ "title" => "Student attendance", "roles" => [ "Attendance.attReport" => "View Attendance Reports" ] ];
        $customPerms['stfAttendance'] = [ "title" => "Staff attendance", "roles" => [ "staffAttendance.attReport" => "Attendance Report", "attendances_reports.daily" => "Daily Attendance", "attendances_reports.monthly" => "Monthly Attendance", "attendances_reports.myReport" => "My Attendance Report", "attendances_reports.summary" => "Summary Report" ] ];
        
        $customPerms['settings'] = [ "title" => "Settings", "roles" => ["adminTasks.globalSettings" => "School Settings"] ];
        $customPerms['mobile'] = [ "title" => "Mobile Settings", "roles" => ["adminTasks.mobileSettings" => "Mobile application"] ];
        $customPerms['biometric'] = [ "title" => "Biometric Integration", "roles" => ["adminTasks.bioItegration" => "Biometric Integration"] ];
        $customPerms['feedbacks'] = [ "title" => "Teacher feedback", "roles" => ["feedbacks.list" => "List Feedbacks", "feedbacks.add" => "Add Feedback", "feedbacks.edit" => "Edit Feedback", "feedbacks.del" => "Delete Feedback", "feedbacks.View" => "View Feedback", "feedbacks.evaluate" => "Evaluate Teacher"] ];
        $customPerms['subjectVideos'] = [ "title" => "Subject videos", "roles" => ["subjectVideos.list" => "List subject videos"] ];
        $customPerms['mySubjects'] = [ "title" => "My Subjects", "roles" => ["mySubjects" => "Show my subjects"] ];
        $customPerms['busTracker'] = [ "title" => "Bus Tracker", "roles" => ["students.TrackBus" => "Show Bus Tracker"] ];

        $toReturn["customPerms"] = $customPerms;
        return $toReturn;
    }

    public function role()
    {
        if( !$this->panelInit->can( "roles.modify_role" ) ) { return $this->panelInit->apiOutput( false, "Modify Role", "You don't have permission to modify rules" ); }
        if( ! \Input::has('role_id') ) { return $this->panelInit->apiOutput( false, "Modify Role", $this->panelInit->language['role_notexist'] ); }
        $id = \Input::get('role_id');
        $roles = Role::select( 'id', 'role_title', 'role_description', 'def_for', 'role_permissions')->where( 'id', $id )->first()->toArray();
		$roles['role_permissions'] = json_decode($roles['role_permissions'],true);
		return $roles;
    }

    public function confirmAdd()
    {
        if( !$this->panelInit->can( "roles.add_role" ) ) { return $this->panelInit->apiOutput( false, "Add Role", "You don't have permission to add rules" ); }
        if( !\Input::has('role_title') ) { return $this->panelInit->apiOutput( false, "Add Role", "Role Name is missing" ); }
        if( !\Input::has('def_for') ) { return $this->panelInit->apiOutput( false, "Add Role", "Role Defaults is missing" ); }
        if( !\Input::has('role_permissions') ) { return $this->panelInit->apiOutput( false, "Add Role", "Pages Permissions is missing" ); }
        if( count( \Input::get('role_permissions') ) == 0 ) { return $this->panelInit->apiOutput( false, "Add Role", "At least one pages permissions must be selected" ); }

        $role = new Role();
        $role->role_title = \Input::get('role_title');
        if( !\Input::has('role_description') ) { $role->role_description = \Input::get('role_description'); }
        $role->def_for = \Input::get('def_for');
        $role->role_permissions = json_encode( \Input::get('role_permissions') );
        $role->save();
        user_log('Roles', 'create', $role->role_title);
        return $this->panelInit->apiOutput( true, "Modify Role", "Role Created Successfully" );
    }
    
    public function confirmEdit()
    {
        if( !$this->panelInit->can( "roles.modify_role" ) ) { return $this->panelInit->apiOutput( false, "Modify Role", "You don't have permission to modify rules" ); }
        if( !\Input::has('id') ) { return $this->panelInit->apiOutput( false, "Modify Role", $this->panelInit->language['role_notexist'] ); }
        if( !\Input::has('role_title') ) { return $this->panelInit->apiOutput( false, "Modify Role", "Role Name is missing" ); }
        if( !\Input::has('def_for') ) { return $this->panelInit->apiOutput( false, "Modify Role", "Role Defaults is missing" ); }
        if( !\Input::has('role_permissions') ) { return $this->panelInit->apiOutput( false, "Modify Role", "Pages Permissions is missing" ); }
        
        if( count( \Input::get('role_permissions') ) == 0 ) { return $this->panelInit->apiOutput( false, "Modify Role", "At least one pages permissions must be selected" ); }
        $roles = Role::find( \Input::get('id') );
        if( !$roles ) { return $this->panelInit->apiOutput( false, "Modify Role", $this->panelInit->language['role_notexist'] ); }
        $roles->role_title = \Input::get('role_title');
        if( !\Input::has('role_description') ) { $roles->role_description = \Input::get('role_description'); }
        $roles->def_for = \Input::get('def_for');
        $roles->role_permissions = json_encode( \Input::get('role_permissions') );
        $roles->save();
        user_log('Roles', 'edit', $roles->role_title);
        return $this->panelInit->apiOutput( true, "Modify Role", "Role Updated Successfully" );
    }

    public function deleteRole()
    {
        if( !$this->panelInit->can( "roles.delete_role" ) ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['delete_role'], "You don't have permission to delete rules" ); }
        if( !\Input::has('role_id') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['delete_role'], $this->panelInit->language['role_notexist'] ); }
        $id = \Input::has('role_id');
        $postDelete = Role::where('id', $id)->first();
        if ( $postDelete )
        {
            user_log('Roles', 'delete', $postDelete->role_title);
            $postDelete->delete();
            return $this->panelInit->apiOutput(true, $this->panelInit->language['delete_role'], $this->panelInit->language['role_deleted']);
        }
        else
        {
            return $this->panelInit->apiOutput( true, $this->panelInit->language['delete_role'], $this->panelInit->language['role_notexist'] );
        }
    }
}