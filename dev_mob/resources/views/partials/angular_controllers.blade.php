<?php
	if(get_server_info()['server_type'] == 'local') {
		srand(mt_rand(1, 9999));
		$random_num = '?v=' . mt_rand(10000, 99999);
	} else {
		// srand(mt_rand(1, 5));
		$random_num = '?v=001';
	}
?>

<script src="{{URL::asset('assets/js/Angular/controllers/mediaController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/eventsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/classesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/teachersController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/studentsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/invoicesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/subjectsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/messagesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/homeworkController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/examsListController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/dashboardController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/TransportsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/markSheetsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/attendanceController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/trackBusesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/userProfileController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/mobileNotifController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/classScheduleController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/attendance_reportController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/helper_controllers/communications-filter.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/helper_controllers/discipline-filter.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/assignmentsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/teacherLogsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/myMarkSheetController.js' . $random_num )}}" type="text/javascript"></script>

<script src="{{URL::asset('assets/js/Angular/controllers/academicYearController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/accountSettingsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/adminsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/attendanceStatsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/employeesController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/gradeLevelsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/hostelCatController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/hostelController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/mainController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/newsboardController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/parentsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/promotionController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/registerationController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/rolesController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/sectionsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/staffAttendance_reportController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/staffAttendanceController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/student_categoriesController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/transport_membersController.js') }}" type="text/javascript"></script>