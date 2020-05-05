var CuteBrains = angular.module('studentAttendanceController', []);

CuteBrains.controller('studentAttendanceController', function( dataFactory, $rootScope, $scope, $sce, $http, $timeout ) {
    $scope.loadingIcon = false;
    $scope.specialLoading = false;
    $scope.fetchingData = false;
    $scope.views = {};
    $scope.targetUrl = "";
    $scope.attendanceType = "";
    $scope.attendanceParseTitle = "";
    $scope.classes = {};
    $scope.sections = {};
    $scope.form = {};
    $scope.calendarForm = {};
    $scope.attendanceForm = {};
    $scope.attendance = {};
    $scope.tableHeaders = {};
    $scope.monthName = "";
    $scope.fromDate = "";
    $scope.toDate = "";
    $scope.highlighDays = {};
    $scope.temp = {}
    $scope.temp.selectedDays = {};
    $scope.temp.pressedSelectedDays = {};
    $scope.userRole = $rootScope.dashboardData.role;

    $scope.$on('$viewContentLoaded', function() {
        var targetUrl = window.location.href.split("/").pop();
        $scope.targetUrl = targetUrl;
        if(targetUrl == 'take')
        {
            $scope.attendanceType = 'Take Student Attendance';
            $scope.attendanceParseTitle = 'Attendance Register';
        }
        else if(targetUrl == 'report')
        {
            $scope.attendanceType = 'Student Attendance Repoart';
            $scope.attendanceParseTitle = 'Student Attendance Report';
        }
        document.title = $('meta[name="site_title"]').attr('content') + ' | ' + $scope.attendanceType;
    });

    $scope.preLoad = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/studentattendance/preLoadAttendance').then(function(data) {
            $scope.classes = data.classes;
            $scope.form.class_id = "";
            $scope.form.section_id = "";
            if($scope.targetUrl == 'take') { $scope.changeView('take'); }
            else if( $scope.targetUrl == 'report' ) { $scope.changeView('report'); }
            
            showHideLoad(true);
        });
    }

    $scope.preLoad();

    $scope.changeClasses = function(){
        let classIndex = $scope.form.class_id;
        if( $scope.form.class_id != "" )
        {
            let sectionList = $scope.classes[classIndex].sections; $scope.sections = sectionList;
        }
    }

    $scope.filterTakeAttendance = function() {
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/studentattendance/getAttendance', 'POST', {}, $scope.form).then(function(data) {
            $scope.loadingIcon = false;
            if ( data.status == 'failed' ) { response = apiResponse(data, 'remove'); }
            else if ( data.status == 'success' )
            {
                if( data.isInDate == true )
                {
                    $scope.temp.pressedSelectedDays = {};
                    var pressedSelectedDays = [];
                    pressedSelectedDays.push( data.currentDate );
                    $scope.temp.pressedSelectedDays = pressedSelectedDays;
                    $scope.temp.selectedDays = {};
                    $scope.temp.selectedDays = pressedSelectedDays;
                }
                setTimeout(function(){
                    $('.tooltipTriggerer').tooltip({ boundary: 'window', html: true });
                    $('select[multiple]').selectpicker('destroy');
                    $('select[multiple]').selectpicker();
                }, 300);
                $scope.monthName = data.month;
                $scope.tableHeaders = data.headers;
                $scope.attendance = data.attendance;
                $scope.highlighDays = data.highlighter;
                $scope.calendarForm.class_id = data.class_id;
                $scope.calendarForm.section_id = data.section_id;
                $scope.calendarForm.month = data.modalMonth;
                $('.tooltipTriggerer').tooltip('dispose');
            } else { response = apiResponse(data, 'remove'); }
        });
    }

    $scope.moveMonth = function(type) {
        $scope.calendarForm.timeStep = type;
        $scope.fetchingData = true;
        dataFactory.httpRequest('index.php/studentattendance/getAttendance', 'POST', {}, $scope.calendarForm).then(function(data) {
            $scope.fetchingData = false;
            if ( data.status == 'failed' ) { response = apiResponse(data, 'remove'); }
            else if ( data.status == 'success' )
            {
                if( data.isInDate == true )
                {
                    $scope.temp.pressedSelectedDays = {};
                    var pressedSelectedDays = [];
                    pressedSelectedDays.push( data.currentDate );
                    $scope.temp.pressedSelectedDays = pressedSelectedDays;
                    $scope.temp.selectedDays = {};
                    $scope.temp.selectedDays = pressedSelectedDays;
                }
                setTimeout(function(){
                    $('.tooltipTriggerer').tooltip({ boundary: 'window', html: true });
                    $('select[multiple]').selectpicker('destroy');
                    $('select[multiple]').selectpicker();
                }, 300);
                $scope.monthName = data.month;
                $scope.tableHeaders = data.headers;
                $scope.attendance = data.attendance;
                $scope.highlighDays = data.highlighter;
                $scope.calendarForm.class_id = data.class_id;
                $scope.calendarForm.section_id = data.section_id;
                $scope.calendarForm.month = data.modalMonth;
                $scope.form.month = data.modalMonth;
                $('#takeMonth').val( data.modalMonth );
                $('.tooltipTriggerer').tooltip('dispose');
            } else { response = apiResponse(data, 'remove'); }
        });
    }

    $scope.openAttendance = function(row , date){
        let dataRow = $scope.attendance[row];
        let studentId = dataRow.id;
        let studentName = dataRow.name;
        let studentAdmision = dataRow.num;
        let attendance = dataRow.attendance[date];
        $scope.attendanceForm.row = row;
        $scope.attendanceForm.studentId = studentId;
        $scope.attendanceForm.studentName = studentName;
        $scope.attendanceForm.studentAdmision = studentAdmision;
        $scope.attendanceForm.isAllowed = attendance.isAllowed;
        $scope.attendanceForm.allowanceData = attendance.allowanceData;
        $scope.attendanceForm.date = attendance.date;
        $scope.attendanceForm.day = attendance.day;
        $scope.attendanceForm.status = attendance.status;
        $scope.attendanceForm.notes = attendance.notes;
        $scope.modalTitle = "Attendance For ( " + attendance.day + " " + attendance.date + " ) " ;
        $scope.modalClass = "modal-md";
        $scope.viewModal = !$scope.viewModal;
    }

    $scope.registerAttendance = function(){
        $scope.specialLoading = true;
        dataFactory.httpRequest('index.php/studentattendance/registerAttendance', 'POST', {}, $scope.attendanceForm).then(function(data) {
            $scope.specialLoading = false;
            if ( data.status == 'failed' ) { response = apiResponse(data, 'remove'); }
            else if ( data.status == 'success' )
            {
                let row = $scope.attendanceForm.row;
                let date = $scope.attendanceForm.date;
                $scope.attendance[row].attendance[date].notes = $scope.attendanceForm.notes;
                $scope.attendance[row].attendance[date].status = $scope.attendanceForm.status;
                $('.tooltipTriggerer').tooltip('dispose');
                setTimeout(function(){
                    $('.tooltipTriggerer').tooltip({ boundary: 'window', html: true });
                    $('select[multiple]').selectpicker('destroy');
                    $('select[multiple]').selectpicker();
                }, 300);
                response = apiResponse(data, 'edit');
                $scope.viewModal = !$scope.viewModal;
            } else { response = apiResponse(data, 'remove'); }
        });
    }

    $scope.closeModal = function(){
        $scope.viewModal = !$scope.viewModal;
    }

    $scope.changeView = function(view){
        $scope.views.take = false;
        $scope.views.report = false;
        $scope.views[view] = true;
        if( view == "take" )
        {
            setTimeout(function(){
                $('#takeMonth').datepicker({ startView: 1, minViewMode: 1, autoclose: true, format: "mm/yyyy" });
            }, 300);
        }
    }

    $scope.filterAttendanceReport = function() {
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/studentattendance/getAttendance', 'POST', {}, $scope.form).then(function(data) {
            $scope.loadingIcon = false;
            if ( data.status == 'failed' ) { response = apiResponse(data, 'remove'); }
            else if ( data.status == 'success' )
            {
                if( data.isInDate == true )
                {
                    $scope.temp.pressedSelectedDays = {};
                    var pressedSelectedDays = [];
                    pressedSelectedDays.push( data.currentDate );
                    $scope.temp.pressedSelectedDays = pressedSelectedDays;
                    $scope.temp.selectedDays = {};
                    $scope.temp.selectedDays = pressedSelectedDays;
                }
                setTimeout(function(){
                    $('.tooltipTriggerer').tooltip({ boundary: 'window', html: true });
                    $('select[multiple]').selectpicker('destroy');
                    $('select[multiple]').selectpicker();
                }, 300);
                $scope.fromDate = data.fromDate;
                $scope.toDate = data.toDate;
                $scope.tableHeaders = data.headers;
                $scope.attendance = data.attendance;
                $scope.highlighDays = data.highlighter;
                $scope.calendarForm.class_id = data.class_id;
                $scope.calendarForm.section_id = data.section_id;
                $('.tooltipTriggerer').tooltip('dispose');
            } else { response = apiResponse(data, 'remove'); }
        });
    }

    $scope.highlight = function(){
        $scope.temp.pressedSelectedDays = $scope.temp.selectedDays;
    }

    $('#takeMonth').datepicker({ startView: 1, minViewMode: 1, autoclose: true, format: "mm/yyyy" });
    $scope.in_array = function( needle, haystack )
    {
        for( var key in haystack ) { if( needle === haystack[key] ) return true; }
        return false;
    }
});