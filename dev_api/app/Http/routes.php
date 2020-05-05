<?php

include('helper_routes.php');

Route::get('/lastappv','LoginController@lastappv');
Route::get('/login','LoginController@index')->name('login');
Route::post('/login','LoginController@attemp');
Route::get('/logout','LoginController@logout');

Route::get('/forgetpwd','LoginController@forgetpwd');
Route::post('/forgetpwd','LoginController@forgetpwdStepOne');
Route::get('/forgetpwd/{uniqid}','LoginController@forgetpwdStepTwo');
Route::post('/forgetpwd/{uniqid}','LoginController@forgetpwdStepTwo');

Route::get('/register/classes','LoginController@registerClasses');
Route::get('/register/searchStudents/{student}','LoginController@searchStudents');
Route::post('/register/sectionsList','LoginController@sectionsList');
Route::get('/register/searchUsers/{usersType}/{student}','LoginController@searchUsers');
Route::get('/register','LoginController@register');
Route::post('/register','LoginController@registerPost');

Route::get('/terms','LoginController@terms');

// Dashboard
Route::group(array('middleware'=>'web') ,function(){
	Route::get('/','frontendPagesController@index');
	Route::get('/portal','DashboardController@index');
	Route::get('/cms','DashboardController@cms');

	Route::post('auth/register', 'AuthController@register');
	Route::post('auth/token-register', 'AuthController@tokenRegister');
	Route::post('auth/authenticate', 'AuthController@authenticate');
	Route::get('auth/authenticate/user', 'AuthController@getAuthenticatedUser');

	Route::get('/dashboard','DashboardController@dashboardData');
	Route::get('/dashboard/baseUser','DashboardController@baseUser');
	Route::get('/dashboard/mobile-attrs','DashboardController@getMonileAttrs');
	Route::get('/calender','DashboardController@calender');
	Route::get('/uploads/{section}/{image}','DashboardController@image');
    Route::post('/dashboard/changeAcYear','DashboardController@changeAcYear');
    Route::post('/dashboard/classesList','DashboardController@classesList');
    Route::post('/dashboard/subjectList','DashboardController@subjectList');
    Route::post('/dashboard/sectionsSubjectsList','DashboardController@sectionsSubjectsList');
    Route::get('/dashboard/profileImage/{id}','DashboardController@profileImage');
    Route::get('/dashboard/mobnotif','DashboardController@mobNotif');
	Route::get('/dashboard/mobnotif/{id}','DashboardController@mobNotif');
	Route::post('/dashboard/loadBirthdays','DashboardController@changeBirthday');
    Route::post('/dashaboard','DashboardController@dashaboardData');
    Route::post('/ml_upload','DashboardController@ml_upload');
    Route::get('/ml_upload/load','DashboardController@ml_load');
	Route::get('/ml_upload/load/{id}','DashboardController@ml_load');
	Route::get('/get_student_attendance','manageStudentsController@student_attendance');
	Route::get('/get_my_calender','DashboardController@my_calender');
	Route::get('/get_calender_content','DashboardController@calender_content');

	//Languages & phrases
	Route::get('/languages','DashboardController@index');
	Route::get('/languages/listAll','LanguagesController@listAll');
	Route::post('/languages','LanguagesController@create');
	Route::get('/languages/{id}','LanguagesController@fetch');
    Route::post('/languages/delete/{id}','LanguagesController@delete');
    Route::post('/languages/{id}','LanguagesController@edit');

	//Admins
	Route::get('/admins','DashboardController@index');
	Route::get('/admins/listAll','AdminsController@listAll');
	Route::post('/admins','AdminsController@create');
	Route::get('/admins/{id}','AdminsController@fetch');
    Route::post('/admins/account_status/{id}','AdminsController@account_status');
    Route::post('/admins/delete/{id}','AdminsController@delete');
	Route::post('/admins/{id}','AdminsController@edit');
	
	//Administrative
	Route::get('/administrative/settings','administrativeController@loadSettings');
	Route::get('/administrative/load_cities','administrativeController@getCitites');
	Route::post('/administrative/saveSettings','administrativeController@saveSettings');
	
	//Accountants
	Route::get('/employees','DashboardController@index');
	Route::get('/employees/listAll','employeesController@listAll');
	Route::get('/employees/listAll/{page}','employeesController@listAll');
	Route::post('/employees/listAll/{page}','employeesController@listAll');
	Route::post('/employees','employeesController@create');
	Route::get('/employees/fetch-by-department-id/{id}','employeesController@fetchByDepartmentId');
	Route::get('/employees/fetch-all','employeesController@fetchAll');
	Route::get('/employees/{id}','employeesController@fetch');
	Route::post('/employees/account_status/{id}','employeesController@account_status');
	Route::post('/employees/delete/{id}','employeesController@delete');
	Route::post('/employees/{id}','employeesController@edit');

	//Teachers
	Route::get('/teachers','DashboardController@index');
	Route::post('/teachers/import/{type}','TeachersController@import');
    Route::post('/teachers/reviewImport','TeachersController@reviewImport');
	Route::get('/teachers/export','TeachersController@export');
	Route::get('/teachers/exportpdf','TeachersController@exportpdf');
	Route::post('/teachers/upload','TeachersController@uploadFile');
	Route::get('/teachers/waitingApproval','TeachersController@waitingApproval');
	Route::post('/teachers/leaderBoard/{id}','TeachersController@leaderboard');
    Route::post('/teachers/approveOne/{id}','TeachersController@approveOne');
	Route::get('/teachers/profile/{id}','TeachersController@profile');
	Route::get('/teachers/listAll','TeachersController@listAll');
	Route::get('/teachers/listAll/{page}','TeachersController@listAll');
	Route::get('/teachers/get-by-class-id/{class_id}','TeachersController@fetch_teachers_by_class_id');
	Route::post('/teachers/get-by-class-section','TeachersController@fetch_by_class_and_section');
	Route::post('/teachers/get-by-class-subject','TeachersController@fetch_by_class_and_subject');
	Route::post('/teachers/listAll/{page}','TeachersController@listAll');
	Route::post('/teachers','TeachersController@create');
	Route::get('/teachers/{id}','TeachersController@fetch');
    Route::post('/teachers/leaderBoard/delete/{id}','TeachersController@leaderboardRemove');
    Route::post('/teachers/account_status/{id}','TeachersController@account_status');
    Route::post('/teachers/delete/{id}','TeachersController@delete');
    Route::post('/teachers/{id}','TeachersController@edit');

	//Students
	Route::get('/students','DashboardController@index');
	Route::get('/students/fetch-by-class-id/{id}','StudentsController@fetchByClassId');
	Route::get('/students/fetch-by-class-ids','StudentsController@fetchByClassIds');
	Route::post('/students/fetch-by-class-section','StudentsController@fetchByClassIdAndSectionId');
	Route::get('/students/fetch-all','StudentsController@fetchAll');
	Route::get('/students/get-bus-track-info/{student_id}','StudentsController@getInfoBusTrack');
    Route::get('/students/preAdmission','StudentsController@preAdmission');
	Route::post('/students/import/{type}','StudentsController@import');
    Route::post('/students/reviewImport','StudentsController@reviewImport');
	Route::get('/students/export','StudentsController@export');
	Route::get('/students/exportpdf','StudentsController@exportpdf');
	Route::post('/students/upload','StudentsController@uploadFile');
	Route::post('/students/rem_std_docs','StudentsController@rem_std_docs');
    Route::get('/students/waitingApproval','StudentsController@waitingApproval');
	Route::get('/students/gradStdList','StudentsController@gradStdList');
    Route::post('/students/account_status/{id}','StudentsController@account_status');
	Route::post('/students/approveOne/{id}','StudentsController@approveOne');
    Route::get('/students/print/marksheet/{student}/{exam}','StudentsController@marksheetPDF');
    Route::get('/students/marksheet/{id}','StudentsController@marksheet');
    Route::get('/students/medical/{id}','StudentsController@medical');
	Route::post('/students/medical','StudentsController@saveMedical');
	Route::get('/students/attendance/{id}','StudentsController@attendance');
	Route::get('/students/search_parent/{id}','StudentsController@search_parent');
	Route::get('/students/profile/{id}','StudentsController@profile');
	Route::post('/students/leaderBoard/{id}','StudentsController@leaderboard');
    Route::get('/students/listAll','StudentsController@listAll');
	Route::get('/students/listAll/{page}','StudentsController@listAll');
	Route::post('/students/listAll/{page}','StudentsController@listAll');
    Route::post('/students','StudentsController@create');
	Route::get('/students/{id}','StudentsController@fetch');
    Route::post('/students/printbulk/marksheet','StudentsController@marksheetBulkPDF');
    Route::post('/students/leaderBoard/delete/{id}','StudentsController@leaderboardRemove');
    Route::post('/students/acYear/delete/{student}/{id}','StudentsController@acYearRemove');
    Route::post('/students/delete/{id}','StudentsController@delete');
	Route::post('/students/{id}','StudentsController@edit');

	//Quick Add Student
	Route::get('/qstudents/preload','StudentQuickController@load_data');
	Route::post('/qstudents/findStudent','StudentQuickController@sibling_search');
	Route::post('/qstudents/completeRegister','StudentQuickController@register');
	Route::post('/qstudents/updateStudent','StudentQuickController@update');
	Route::post('/qstudents/importExcel','StudentQuickController@excel_import');
	Route::post('/qstudents/processImported','StudentQuickController@imported');

	// Student List
	Route::get('/manage-students/preLoad', 'manageStudentsController@preLoad');
	Route::get('/manage-students/list/{page}', 'manageStudentsController@viewAllStudent');
	Route::get('/manage-students/viewStudent', 'manageStudentsController@readStudent');
	Route::get('/manage-students/viewImage/{id}', 'manageStudentsController@loadImage');
	Route::get('/document/download/{id}', 'manageStudentsController@downloadDocument');
	Route::post('/manage-students/delete-documents', 'manageStudentsController@deleteDocument');
	Route::post('/manage-students/documentsUpload', 'manageStudentsController@createDocument');
	Route::post('/manage-students/documentEdit', 'manageStudentsController@updateDocument');
	Route::post('/manage-students/deleteStudent', 'manageStudentsController@deleteStudent');
	Route::get('/manage-students/parentImage/{image}', 'manageStudentsController@parentImage');

	// teachers List
	Route::get('/manage-teachers/preLoad', 'manageTeachersController@preLoad');
	Route::post('/manage-teachers/filter', 'manageTeachersController@filter');

	//Student Categories
	Route::get('/student_categories','DashboardController@index');
	Route::get('/student_categories/listAll','studentCategoryController@listAll');
	Route::post('/student_categories','studentCategoryController@create');
	Route::get('/student_categories/export/{type}','studentCategoryController@export');
	Route::get('/student_categories/{id}','studentCategoryController@fetch');
	Route::post('/student_categories/delete/{id}','studentCategoryController@delete');
	Route::post('/student_categories/{id}','studentCategoryController@edit');

	//Student Types
	Route::get('/student_types','DashboardController@index');
	Route::get('/student_types/listAll','studentTypeController@listAll');
	Route::post('/student_types','studentTypeController@create');
	Route::get('/student_types/export/{type}','studentTypeController@export');
	Route::get('/student_types/{id}','studentTypeController@fetch');
	Route::post('/student_types/delete/{id}','studentTypeController@delete');
	Route::post('/student_types/{id}','studentTypeController@edit');

	//Parents
	Route::get('/parents/search/{student}','ParentsController@searchStudents');
    Route::post('/parents/import/{type}','ParentsController@import');
	Route::post('/parents/reviewImport','ParentsController@reviewImport');
	Route::get('/parents/fetch-by-class-id/{id}','ParentsController@fetchByClassId');
	Route::get('/parents/fetch-all','ParentsController@fetchAll');
	Route::get('/parents/export','ParentsController@export');
	Route::get('/parents/exportpdf','ParentsController@exportpdf');
	Route::get('/parents','DashboardController@index');
	Route::post('/parents/upload','ParentsController@uploadFile');
	Route::get('/parents/waitingApproval','ParentsController@waitingApproval');
	Route::get('/parents/profile/{id}','ParentsController@profile');
	Route::post('/parents/approveOne/{id}','ParentsController@approveOne');
	Route::get('/parents/listAll','ParentsController@listAll');
	Route::get('/parents/listAll/{page}','ParentsController@listAll');
	Route::post('/parents/listAll/{page}','ParentsController@listAll');
	Route::post('/parents','ParentsController@create');
	Route::get('/parents/{id}','ParentsController@fetch');
    Route::post('/parents/account_status/{id}','ParentsController@account_status');
    Route::post('/parents/delete/{id}','ParentsController@delete');
	Route::post('/parents/{id}','ParentsController@edit');

	// HR System
	Route::get('/manageRoles/listAll/{page}', 'HrRolesController@listAll');
	Route::get('/manageRoles/role', 'HrRolesController@role');
	Route::post('/manageRoles/saveRole', 'HrRolesController@confirmEdit');
	Route::post('/manageRoles/createRole', 'HrRolesController@confirmAdd');
	Route::post('/manageRoles/deleteRole', 'HrRolesController@deleteRole');
	Route::get('/manageRoles/profileImage/{id}','HrEmployeesController@loadImage');
	Route::get('/manageEmployees/listDepartments/{page}', 'HrEmployeesController@listDepartments');
	Route::get('/manageEmployees/listAllDepartments', 'HrEmployeesController@allDepartments');
	Route::post('/manageEmployees/createDepartment', 'HrEmployeesController@createDepartment');
	Route::post('/manageEmployees/viewDepartment', 'HrEmployeesController@readDepartment');
	Route::post('/manageEmployees/editDepartment', 'HrEmployeesController@updateDepartment');
	Route::post('/manageEmployees/removeDepartment', 'HrEmployeesController@deleteDepartment');
	Route::get('/manageEmployees/listDesignations/{page}', 'HrEmployeesController@listDesignations');
	Route::post('/manageEmployees/createDesignation', 'HrEmployeesController@createDesignation');
	Route::post('/manageEmployees/viewDesignation', 'HrEmployeesController@readDesignation');
	Route::post('/manageEmployees/editDesignation', 'HrEmployeesController@updateDesignation');
	Route::post('/manageEmployees/removeDesignation', 'HrEmployeesController@deleteDesignation');
	Route::get('/manageEmployees/listBranchs/{page}', 'HrEmployeesController@listBranchs');
	Route::post('/manageEmployees/createBranch', 'HrEmployeesController@createBranch');
	Route::post('/manageEmployees/viewBranch', 'HrEmployeesController@readBranch');
	Route::post('/manageEmployees/editBranch', 'HrEmployeesController@updateBranch');
	Route::post('/manageEmployees/removeBranch', 'HrEmployeesController@deleteBranch');
	Route::get('/manageEmployees/listEmployees/{page}', 'HrEmployeesController@listEmployees');
	Route::post('/manageEmployees/createEmployee', 'HrEmployeesController@createEmployee');
	Route::post('/manageEmployees/viewEmployee', 'HrEmployeesController@readEmployee');
	Route::post('/manageEmployees/editEmployee', 'HrEmployeesController@updateEmployee');
	Route::post('/manageEmployees/removeEmployee', 'HrEmployeesController@deleteEmployee');
	Route::get('/manageEmployees/listWarnings/{page}', 'HrEmployeesController@listWarnings');
	Route::post('/manageEmployees/createWarning', 'HrEmployeesController@createWarning');
	Route::post('/manageEmployees/viewWarning', 'HrEmployeesController@readWarning');
	Route::post('/manageEmployees/editWarning', 'HrEmployeesController@updateWarning');
	Route::post('/manageEmployees/removeWarning', 'HrEmployeesController@deleteWarning');
	Route::get('/manageEmployees/listTerminations/{page}', 'HrEmployeesController@listTerminations');
	Route::post('/manageEmployees/createTermination', 'HrEmployeesController@createTermination');
	Route::post('/manageEmployees/viewTermination', 'HrEmployeesController@readTermination');
	Route::post('/manageEmployees/editTermination', 'HrEmployeesController@updateTermination');
	Route::post('/manageEmployees/removeTermination', 'HrEmployeesController@deleteTermination');
	Route::get('/manageEmployees/listPromotions/{page}', 'HrEmployeesController@listPromotions');
	Route::post('/manageEmployees/createPromotion', 'HrEmployeesController@createPromotion');
	Route::post('/manageEmployees/viewPromotion', 'HrEmployeesController@readPromotion');
	Route::post('/manageEmployees/editPromotion', 'HrEmployeesController@updatePromotion');
	Route::post('/manageEmployees/removePromotion', 'HrEmployeesController@deletePromotion');
	
	Route::get('/manageAttendance/listWorkshifts/{page}', 'HrAttendanceController@listWorkshifts');
	Route::post('/manageAttendance/createWorkshift', 'HrAttendanceController@createWorkshift');
	Route::post('/manageAttendance/viewWorkshift', 'HrAttendanceController@readWorkshift');
	Route::post('/manageAttendance/editWorkshift', 'HrAttendanceController@updateWorkshift');
	Route::post('/manageAttendance/removeWorkshift', 'HrAttendanceController@deleteWorkshift');
	Route::post('/manageAttendance/filterAttendances', 'HrAttendanceController@filterAttendances');
	Route::post('/manageAttendance/takeAttendance', 'HrAttendanceController@takeAttendance');
	Route::post('/manageAttendance/dailyAttendance', 'HrAttendanceController@dailyAttendance');
	
	//Classes
	Route::get('/classes','DashboardController@index');
	Route::get('/classes/listAll','ClassesController@listAll');
	Route::get('/classes/get-class-teachers-by-class-id/{class_id}','ClassesController@get_class_teachers_by_class_id');
	Route::get('/classes/get-subjects-by-class-id/{class_id}','ClassesController@get_subjects_by_class_id');
	Route::get('/classes/get-class-id-by-class-name/{class_name}','ClassesController@getClassIdByClassName');
	Route::post('/classes','ClassesController@create');
	Route::get('/classes/fetch-all','ClassesController@fetchAll');
	Route::get('/classes/{id}','ClassesController@fetch');
	Route::post('/classes/delete/{id}','ClassesController@delete');
	Route::post('/classes/{id}','ClassesController@edit');

	// Classes and subjects
	Route::post('/classes-subjects/bulk-insert-classes-seubjects','ClassesController@bulkInsertClassesAndSubjects');
	Route::get('/classes-subjects/import/download-sample','ClassesController@downloadImportSample');
	Route::get('/subjects/import/download-sample','ClassesController@downloadSubjectsImportSample');
	Route::post('/classes-subjects/import/{type}','ClassesController@importBulkClassesAndSubjects');

	//Sections
	Route::get('/sections','DashboardController@index');
	Route::get('/sections/listAll','sectionsController@listAll');
	Route::get('/sections/fetch-with-class/{class_id}','sectionsController@get_sections_with_class_id');
	Route::post('/sections','sectionsController@create');
	Route::get('/sections/{id}','sectionsController@fetch');
	Route::post('/sections/delete/{id}','sectionsController@delete');
	Route::post('/sections/{id}','sectionsController@edit');

	//subjects
	Route::get('/subjects','DashboardController@index');
	Route::get('/subjects/listAll','SubjectsController@listAll');
	Route::post('/subjects','SubjectsController@create');
	Route::get('/subjects/{id}','SubjectsController@fetch');
	Route::post('/subjects/delete/{id}','SubjectsController@delete');
	Route::post('/subjects/{id}','SubjectsController@edit');

	// Sub subjects
	Route::get('/sub-subjects','DashboardController@index');
	Route::get('/sub-subjects/listAll','SubSubjectsController@listAll');
	Route::post('/sub-subjects/delete/{id}','SubSubjectsController@delete');

	//NewsBoard
	Route::get('/newsboard','DashboardController@index');
	Route::get('/newsboard/listAll/{page}','NewsBoardController@listAll');
    Route::get('/newsboard/search/{keyword}/{page}','NewsBoardController@search');
	Route::post('/newsboard','NewsBoardController@create');
	Route::get('/newsboard/{id}','NewsBoardController@fetch');
    Route::post('/newsboard/fe_active/{id}','NewsBoardController@fe_active');
    Route::post('/newsboard/delete/{id}','NewsBoardController@delete');
	Route::post('/newsboard/{id}','NewsBoardController@edit');

	//Account Settings & user profile
	Route::get('/accountSettings','DashboardController@index');
	Route::get('/accountSettings/langs','AccountSettingsController@langs');
	Route::get('/accountSettings/data','AccountSettingsController@listAll');
	Route::post('/accountSettings/profile','AccountSettingsController@saveProfile');
	Route::post('/accountSettings/email','AccountSettingsController@saveEmail');
	Route::post('/accountSettings/password','AccountSettingsController@savePassword');
	Route::get('/accountSettings/invoices','AccountSettingsController@invoices');
	Route::get('/accountSettings/invoices/{id}','AccountSettingsController@invoicesDetails');
	Route::get('/accountSettings/get-user-profile-data','AccountSettingsController@getUserProfileData');

	// Class Schedule - Timetable
	Route::get('/classschedule','DashboardController@index');
	Route::post('/classschedule/import/{type}','ClassScheduleController@import');
	Route::post('/classschedule/reviewImport','ClassScheduleController@reviewImport');
	Route::get('/classschedule/listAll','ClassScheduleController@listAll');

	// advanced timetable
	Route::get('/classschedule/list-teacherwise/{day_number}/{page?}','ClassScheduleController@listTeacherwise');
	Route::get('/classschedule/list-classwise/{day_number}/{page?}','ClassScheduleController@listClasswise');
	Route::get('/classschedule/fetch-parameters','ClassScheduleController@fetchParameters');
	Route::post('/classschedule/advanced-timetable/teacherwise/store','ClassScheduleController@advancedTimetableTeacherwiseStore');
	Route::post('/classschedule/advanced-timetable/classwise/store','ClassScheduleController@advancedTimetableClasswiseStore');
	Route::post('/classschedule/advanced-timetable/exclude-teacher','ClassScheduleController@advancedTimetableExcludeTeacher');
	Route::post('/classschedule/advanced-timetable/remove-period','ClassScheduleController@advancedTimetableRemovePeriod');
	// new timetable schedule (classWise)
	Route::get('/classschedule/preLoad','ClassScheduleController@preLoad');
	Route::get('/classschedule/listSchedules','ClassScheduleController@listSchedules');
	Route::post('/classschedule/saveSchedules','ClassScheduleController@storeSchedule');
	Route::post('/classschedule/removeSchedule','ClassScheduleController@removeSchedule');

	Route::get('/classschedule/teacher-presence/{page?}','ClassScheduleController@teacherPresence');
	Route::get('/classschedule/teacher-availability/{page?}','ClassScheduleController@teacherAvailability');

	Route::get('/classschedule/{id}','ClassScheduleController@fetch');
	Route::get('/classschedule-teacher','ClassScheduleController@fetchForTeacher');
	Route::get('/classschedule/sub/{id}','ClassScheduleController@fetchSub');
	Route::post('/classschedule/sub/{id}','ClassScheduleController@editSub');
	Route::post('/classschedule/delete/{class}/{id}','ClassScheduleController@delete');
	Route::post('/classschedule/{id}','ClassScheduleController@addSub');

	//Site Settings
	Route::get('/settings','DashboardController@index');
	Route::post('/siteSettings/test_mail_function','SiteSettingsController@test_mail_function');
	Route::post('/siteSettings/test_sms_function','SiteSettingsController@test_sms_function');
	Route::get('/siteSettings/langs','SiteSettingsController@langs');
	Route::get('/siteSettings/{title}','SiteSettingsController@listAll');
	Route::post('/siteSettings/{title}','SiteSettingsController@save');

	//Attendance
	Route::get('/attendance','DashboardController@index');
	Route::get('/attendance/data','AttendanceController@listAll');
	Route::post('/attendance/list','AttendanceController@listAttendance');
	Route::post('/attendance/get-current-sections-and-subjects','AttendanceController@currentSectionsSubjectsList');
	Route::post('/attendance/report','AttendanceController@reportAttendance');
	Route::post('/attendance','AttendanceController@saveAttendance');
	Route::get('/attendance/stats','AttendanceController@getStats');
	Route::post('/attendance/biometric','AttendanceController@biometric');
	Route::get('/attendance/get-absent-students','AttendanceController@getAbsentStudents');
	Route::get('/attendance/get-absent-staff','AttendanceController@getAbsentStaff');

	// Student Attendance
	Route::get('/studentattendance/preLoadAttendance','studentAttendanceController@preLoad');
	Route::post('/studentattendance/getAttendance','studentAttendanceController@getAttendance');
	Route::post('/studentattendance/registerAttendance','studentAttendanceController@saveAttendance');

	//Grade Levels
	Route::get('/gradeLevels','DashboardController@index');
	Route::get('/gradeLevels/listAll','GradeLevelsController@listAll');
	Route::post('/gradeLevels','GradeLevelsController@create');
	Route::get('/gradeLevels/{id}','GradeLevelsController@fetch');
    Route::post('/gradeLevels/delete/{id}','GradeLevelsController@delete');
	Route::post('/gradeLevels/{id}','GradeLevelsController@edit');

	//Exams List
	Route::get('/examsList','DashboardController@index');
	Route::get('/examsList/listAll','ExamsListController@listAll');
	Route::get('/examsList/preLoad','ExamsListController@preLoadList');
	Route::post('/examsList/filterAll','ExamsListController@filterAll');
	Route::post('/examsList/notify/{id}','ExamsListController@notifications');
	Route::post('/examsList','ExamsListController@create');
	Route::get('/examsList/{id}','ExamsListController@fetch');
	Route::post('/examsList/getMarks','ExamsListController@fetchMarks');
	Route::post('/examsList/{id}','ExamsListController@edit');
	Route::post('/examsList/delete/{id}','ExamsListController@delete');
	Route::post('/examsList/saveMarks/{exam}/{class}/{subject}','ExamsListController@saveMarks');
	Route::post('/examsList/subjectSheduleList/{id}','ExamsListController@subjectSheduleLists');
  	Route::post('/examsList/sheduleTerm/{id}','ExamsListController@sheduleTerms');
  	Route::get('/examsList/termChange/{id}/{argu}/{classId}/{subjectId}','ExamsListController@termSelect');

  	// School terms
	Route::get('/school-terms','DashboardController@index');
	Route::get('/school-terms/listAll','SchoolTermsController@listAll');
	Route::post('/school-terms','SchoolTermsController@create');
	Route::get('/school-terms/fetch-all','SchoolTermsController@fetchAll');
	Route::get('/school-terms/{id}','SchoolTermsController@fetch');
	Route::post('/school-terms/delete/{id}','SchoolTermsController@delete');
	Route::post('/school-terms/{id}','SchoolTermsController@edit');

	//Events
	Route::get('/events','DashboardController@index');
	Route::get('/events/listAll','EventsController@listAll');
	Route::get('/events/listAll-upcoming','EventsController@listAllUpcoming');
	Route::get('/events/events_and_notices/listAll','EventsController@listAllEventAndNotices');
	Route::post('/events','EventsController@create');
	Route::get('/events/{id}','EventsController@fetch');
    Route::post('/events/delete/{id}','EventsController@delete');
    Route::post('/events/fe_active/{id}','EventsController@fe_active');
	Route::post('/events/{id}','EventsController@edit');

	//Assignments
	Route::get('/assignments','DashboardController@index');
	Route::get('/assignments/listAll','AssignmentsController@listAll');
    Route::get('/assignments/listAnswers/{id}','AssignmentsController@listAnswers');
	Route::post('/assignments','AssignmentsController@create');
    Route::get('/assignments/download/{id}','AssignmentsController@download');
    Route::get('/assignments/downloadAnswer/{id}','AssignmentsController@downloadAnswer');
    Route::get('/assignments/{id}','AssignmentsController@fetch');
    Route::post('/assignments/checkUpload','AssignmentsController@checkUpload');
    Route::post('/assignments/delete/{id}','AssignmentsController@delete');
	Route::post('/assignments/upload/{id}','AssignmentsController@upload');
	Route::post('/assignments/{id}','AssignmentsController@edit');
	Route::get('/assignments/image/{dir}/{id}','AssignmentsController@readImage');

	//Homework
	Route::get('/homeworks','DashboardController@index');
	Route::get('/homeworks/preload','homeworksController@preload');
	Route::get('/homeworks/listAll/{page}','homeworksController@listAll');
	Route::get('/homeworks/search/{keyword}/{page}','homeworksController@search');
	Route::post('/homeworks','homeworksController@create');
    Route::get('/homeworks/download/{id}','homeworksController@download');
    Route::get('/homeworks/{id}','homeworksController@fetch');
    Route::get('/homeworks_view/{id}','homeworksController@fetch_view');
    Route::post('/homeworks/delete/{id}','homeworksController@delete');
    Route::post('/homeworks/apply/{id}','homeworksController@apply');
	Route::post('/homeworks/{id}','homeworksController@edit');

	//Messages
	Route::get('/messages','DashboardController@index');
	Route::post('/messages','MessagesController@create');
	Route::get('/messages/listAll/{page}','MessagesController@listMessages');
    Route::get('/messages/searchUser/{user}','MessagesController@searchUser');
	Route::post('/messages/read','MessagesController@read');
	Route::post('/messages/unread','MessagesController@unread');
	Route::post('/messages/delete','MessagesController@delete');
	Route::get('/messages/{id}','MessagesController@fetch');
	Route::post('/messages/{id}','MessagesController@reply');
	Route::get('/messages/before/{from}/{to}/{before}','MessagesController@before');
	Route::get('/messages/peek/{id}','MessagesController@peek');

	// new messaging
	Route::post('/messaging/compose','MessagingController@create');
	Route::get('/messaging/listAll/{page}','MessagingController@listMessages');
	Route::post('/messaging/listAll/{page}','MessagingController@listMessages');
	Route::get('/messaging/show/{id}','MessagingController@fetch');
	Route::get('/messaging/more/{from}/{to}/{before}','MessagingController@showMore');
	Route::post('/messaging/postReply','MessagingController@reply');
	Route::get('/messaging/preLoad','MessagingController@preLoad');
	Route::POST('/messaging/delete','MessagingController@remove');

	// Events
	Route::post('/eventous/createNewEvent', 'EventsController@createNewEvent');
	Route::get('/eventous/listEvents/{page}', 'EventsController@listEvents');
	Route::post('/eventous/listEvents/{page}', 'EventsController@listEvents');
	Route::get('/eventous/eventImage/{image}', 'EventsController@loadEvent');
	Route::get('/eventous/show/{id}', 'EventsController@readEvent');

	// Notices
	Route::post('/notices/createNewNotice', 'NewsBoardController@createNewNotice');
	Route::get('/notices/listNotices/{page}', 'NewsBoardController@listNotices');
	Route::post('/notices/listNotices/{page}', 'NewsBoardController@listNotices');
	Route::get('/notices/noticeImage/{image}', 'NewsBoardController@loadNotice');
	Route::get('/notices/show/{id}', 'NewsBoardController@readNotice');
	Route::post('/notices/updateNotice', 'NewsBoardController@editNotice');
	Route::post('/notices/removeNotice', 'NewsBoardController@removeNotice');
	Route::post('/notices/multipleDelete', 'NewsBoardController@bulkDelete');

	// classes, Sections and Teachers
	Route::get('/classes-sections-teachers/preLoad', 'classesSectionsTeachersController@preLoad');
	Route::post('/classes-sections-teachers/createClass', 'classesSectionsTeachersController@create');
	Route::post('/classes-sections-teachers/removeClass', 'classesSectionsTeachersController@remove');
	Route::post('/classes-sections-teachers/editClass', 'classesSectionsTeachersController@update');
	
	Route::get('/messaging/listAllConversations/{page}','MessagingController@listConvarsations');
	Route::post('/messaging/listAllConversations/{page}','MessagingController@listConvarsations');
	Route::get('/messaging/read/{id}','MessagingController@fetchConvarsation');
	Route::get('/messaging/puller/{from}/{to}/{before}','MessagingController@loadMore');
	Route::post('/messaging/convarsationReply','MessagingController@replyToConvarsation');
	Route::post('/messaging/remove','MessagingController@removeConvarsation');

	//Online Exams
	Route::get('/onlineExams','DashboardController@index');
	Route::get('/onlineExams/listAll','OnlineExamsController@listAll');
	Route::get('/onlineExams/questions','OnlineExamsController@questions');
	Route::get('/onlineExams/questions/{id}','OnlineExamsController@fetchQuestions');
	Route::get('/onlineExams/searchQuestion/{keyword}','OnlineExamsController@searchQuestion');
	Route::post('/onlineExams/questions/{id}','OnlineExamsController@editQuestions');
	Route::post('/onlineExams/questions/delete/{id}','OnlineExamsController@deleteQuestions');
	Route::post('/onlineExams/questions','OnlineExamsController@createQuestions');
    Route::post('/onlineExams/take/{id}','OnlineExamsController@take');
	Route::post('/onlineExams/took/{id}','OnlineExamsController@took');
	Route::get('/onlineExams/marks/{id}','OnlineExamsController@marks');
	Route::get('/onlineExams/export/{id}/{type}','OnlineExamsController@export');
	Route::post('/onlineExams','OnlineExamsController@create');
	Route::get('/onlineExams/{id}','OnlineExamsController@fetch');
    Route::post('/onlineExams/delete/{id}','OnlineExamsController@delete');
	Route::post('/onlineExams/{id}','OnlineExamsController@edit');

	//Transportation
	Route::get('/transports','DashboardController@index');
	Route::get('/transports/listAll','TransportsController@listAll');
	Route::get('/transports/members','TransportsController@members');
	Route::get('/transports/schedule','TransportsController@schedule');
	Route::post('/transports/members','TransportsController@getmembers');
	Route::post('/transports','TransportsController@create');
	Route::get('/transports/{id}','TransportsController@fetch');
    Route::post('/transports/delete/{id}','TransportsController@delete');
	Route::post('/transports/{id}','TransportsController@edit');

	//Transport Vehicles
	Route::get('/transport_vehicles','DashboardController@index');
	Route::get('/transport_vehicles/listAll','transportVehiclessController@listAll');
	Route::post('/transport_vehicles','transportVehiclessController@create');
	Route::get('/transport_vehicles/download/{id}','transportVehiclessController@download');
	Route::get('/transport_vehicles/export/{type}','transportVehiclessController@export');
	Route::get('/transport_vehicles/{id}','transportVehiclessController@fetch');
	Route::post('/transport_vehicles/delete/{id}','transportVehiclessController@delete');
	Route::post('/transport_vehicles/{id}','transportVehiclessController@edit');
	
	//Media
	Route::get('/media','DashboardController@index');
	Route::get('/media/listAll','MediaController@listAlbum');
	Route::get('/media/listAll/{id}','MediaController@listAlbumById');
	Route::get('/media/resize/{file}/{width}/{height}','MediaController@resize');
    Route::get('/media/image/{id}','MediaController@image');
	Route::post('/media/newAlbum','MediaController@newAlbum');
	Route::get('/media/editAlbum/{id}','MediaController@fetchAlbum');
	Route::post('/media/editAlbum/{id}','MediaController@editAlbum');
	Route::post('/media','MediaController@create');
	Route::get('/media/{id}','MediaController@fetch');
    Route::post('/media/album/delete/{id}','MediaController@deleteAlbum');
    Route::post('/media/delete/{id}','MediaController@delete');
    Route::post('/media/{id}','MediaController@edit');

	//invoices
	Route::get('/invoices','DashboardController@index');
	Route::get('/invoices/preLoadInvoice','invoicesController@preLoad');
	Route::post('/invoices/fiterInvoices/{page}','invoicesController@fiterFeeInvoices');
	Route::post('/invoices/importExcelFile','invoicesController@uploadedExcel');
	Route::post('/invoices/createManualFee','invoicesController@createManualFee');
	Route::get('/invoices/listAll','invoicesController@listAll');
	Route::get('/invoices/listAll/{page}','invoicesController@listAll');
	Route::get('/invoices/listAllNotPaid','invoicesController@listAllNotPaid');
	Route::get('/invoices/listAllNotPaid/{page}','invoicesController@listAllNotPaid');
	Route::post('/invoices/listUnPaid/{page}','invoicesController@listAllNotPaid');
	Route::get('/invoices/fetch-payment-methods','invoicesController@fetchPaymentMethods');
	Route::get('/invoices/advanced-listAll/{page}','invoicesController@advancedListAll');
	Route::get('/invoices/current-listAll/{page}','invoicesController@currentListAll');
	Route::get('/invoices/list-paid','invoicesController@listPaid');
	Route::get('/invoices/list-paid/{page}','invoicesController@listPaid');
	Route::post('/invoices/listAll/{page}','invoicesController@listAll');
	Route::post('/invoices/listPaid/{page}','invoicesController@listPaid');
	Route::post('/invoices/paySuccess/{id}','DashboardController@paySuccess');
	Route::get('/invoices/paySuccess','DashboardController@paySuccess');
	Route::get('/invoices/payFailed','DashboardController@payFailed');
	Route::post('/invoices/payFailed','DashboardController@payFailed');
	Route::get('/invoices/searchUsers/{student}','invoicesController@searchStudents');
	Route::get('/invoices/search/{keyword}/{page}','invoicesController@search');
	Route::get('/invoices/failed','invoicesController@paymentFailed');
	Route::get('/invoices/invoice/{id}','invoicesController@invoice');
	Route::get('/invoices/export/{type}','invoicesController@export');
	Route::get('/invoices/details/{id}','invoicesController@PaymentData');
	Route::post('/invoices/import/excel','invoicesController@importExcel');
	Route::get('/invoices/import/download-sample','invoicesController@downloadImportFeeSample');
	Route::post('/invoices','invoicesController@create');
	Route::get('/invoices/fetch-unpaid-fees-charts/{title}','invoicesController@fetchUnpaidFeesForChart');
	Route::get('/invoices/u_fee_titles','invoicesController@fetchUniqueFeeTitles');
	Route::get('/invoices/{id}','invoicesController@fetch');
	Route::post('/invoices/collect/{id}','invoicesController@collect');
	Route::post('/invoices/revert/{id}','invoicesController@revert');
	Route::post('/invoices/delete/{id}','invoicesController@delete');
	Route::post('/invoices/pay/{id}','DashboardController@pay');
	Route::get('/invoices/pay/multi','DashboardController@multiPay');
	Route::post('/invoices/{id}','invoicesController@edit');

	// Bulk invoices
	Route::post('/invoices-bulk/list-fees','invoicesController@listBulkFees');
	Route::post('/invoices-bulk/update-fees','invoicesController@updateBulkFees');

	//Promotion
    Route::get('/promotion','DashboardController@index');
    Route::get('/promotion/preLoad','promotionController@preLoad');
    Route::get('/promotion/search/{student}','promotionController@searchStudents');
	Route::get('/promotion/listData','promotionController@listAll');
	Route::post('/promotion/listStudents','promotionController@listStudents');
	Route::post('/promotion','promotionController@promoteNow');

    //Academic Year
    Route::get('/academicYear','DashboardController@index');
	Route::get('/academic/listAll','academicYearController@listAll');
	Route::post('/academic/active/{id}','academicYearController@active');
	Route::post('/academic','academicYearController@create');
	Route::get('/academic/{id}','academicYearController@fetch');
    Route::post('/academic/delete/{id}','academicYearController@delete');
	Route::post('/academic/{id}','academicYearController@edit');

    //Staff Attendance
	Route::get('/staffAttendance','DashboardController@index');
	Route::post('/sattendance/list','SAttendanceController@listAttendance');
	Route::post('/sattendance','SAttendanceController@saveAttendance');
	Route::post('/sattendance/report','SAttendanceController@reportAttendance');
	
	//Biometric integration
	Route::get('/biometric','biometricController@get_devices');
	Route::post('/biometric','biometricController@sync_devices');

	//Roles Permissions
	Route::get('/roles','DashboardController@index');
	Route::get('/roles/listAll','rolesController@listAll');
	Route::get('/roles/listCustom1','rolesController@listCustom1');
	Route::get('/roles/get-users-by-role-id/{role_id}','rolesController@fetch_users_by_role_id');
	Route::post('/roles','rolesController@create');
	Route::get('/roles/{id}','rolesController@fetch');
	Route::post('/roles/delete/{id}','rolesController@delete');
	Route::post('/roles/{id}','rolesController@edit');

	// Dashboard items
	Route::get('/dashboard/listItems','DashboardController@homeItems');

	// global search
	Route::post('/global-search/get-users-data', 'DashboardController@globalSearch');

	// get marksheet collection of students's parents/teachers
	Route::get('/marksheet-collection/{user_id}', 'MarkSheetController@collection');
	Route::get('/fetchMarks/listAll', 'generateMarksheetController@index');
	Route::get('/marksheetCollection/listAll', 'generateMarksheetController@mySheets');
	Route::post('/marksheetCollection/mySheet', 'generateMarksheetController@readMyCard');
	Route::post('/marksheetCollection/deleteSheet', 'generateMarksheetController@deleteSheet');
	Route::post('/marksheetCollection/deleteStudent', 'generateMarksheetController@deleteStudent');

	Route::get('/get-notification-count-alert', 'DashboardController@getNotificationCountAlertAndData');
	Route::get('/reset-notifications-seen-status', 'DashboardController@resetNotificationsSeenStatus');
	Route::get('/get-list-stoppages', 'DashboardController@getListStoppages');
	Route::get('/get-notifications-redirect-links', function() { return \App\Models2\NotificationMobHistory::getNotificationsRedirectLinks(); });

	Route::post('uploads-config', function() { return uploads_config(); });
});

Route::post('/invoices/success/{id}','invoicesController@paymentSuccess');
Route::get('/frontend/profileImage/{id}','frontendPagesController@profileImage');
Route::any('/{any}', 'frontendPagesController@index')->where('any', '.*');