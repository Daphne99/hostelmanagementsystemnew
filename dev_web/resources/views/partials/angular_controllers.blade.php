<?php
	if(get_server_info()['server_type'] == 'local') {
		srand(mt_rand(1, 9999));
		$random_num = '?v=' . mt_rand(10000, 99999);
	} else {
		$random_num = '?v=001';
	}
?>

<script src="{{URL::asset('assets/js/Angular/controllers/dashboardController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/calendarController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/mediaController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/addStudentController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/manageStudentsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/manageTeachersController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/invoicesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/viewAndEditBulkInvoicesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/makeInvoiceController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/invoicesTempController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/messagesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/composeMessageController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/eventsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/composeEventController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/noticesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/addNoticeController.js' . $random_num )}}" type="text/javascript"></script>

<script src="{{URL::asset('assets/js/Angular/controllers/classesSectionsAndTeachersController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/institutionTimetableController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/institutionTimetableTeacherwiseController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/institutionTimetableClasswiseController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/teacherAvailabilityController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/teacherPresenceController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/rolesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/helper_controllers/communications-filter.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/helper_controllers/discipline-filter.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/homeworkController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/studentTypesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/EnterClassesAndSubjectsController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/manageRolesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/manageEmployeesController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/manageAttendanceController.js' . $random_num )}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/studentAttendanceController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/promotionController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/examsListController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/administrativeController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/assignmentsController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/mySubjectsController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/busTrackerController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/myMarkSheetController.js' . $random_num) }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/academicYearController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/accountSettingsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/adminsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/schoolTermsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/gradeLevelsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/mainController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/onlineExamsController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/student_categoriesController.js') }}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/controllers/TransportsController.js') }}" type="text/javascript"></script>