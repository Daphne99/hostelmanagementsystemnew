var CuteBrains = angular.module('manageAttendanceController', []);

CuteBrains.controller('manageAttendanceController', function( dataFactory, $rootScope, $scope, $sce, $http, $timeout ) {
    $scope.views = {};
    $scope.checkedTab = "";
    $scope.loadingIcon = false;
    $scope.speicalLoadingIcon = false;
    $scope.workshiftForm = {};
    $scope.workshifts = {};
    $scope.totalWorkshifts = 0;
    $scope.workshiftsPageNumber = 1;
    $scope.isFiltered = false;
    $scope.attendences = {};
    $scope.attendenceForm = {};
    $scope.departments = {};
    $scope.dailyAttendanceForm = {};
    $scope.dailyAttendanceForm.dailyDate = moment().format('DD/MM/YYYY');
    $scope.dailyAttendances = {};
    $scope.monthlyAttendanceForm = {};
    $scope.monthlyAttendanceForm.name = "";
    $scope.monthlyAttendanceForm.monthlyFrom = moment().startOf("month").format('DD/MM/YYYY');
    $scope.monthlyAttendanceForm.monthlyTo = moment().endOf("month").format('DD/MM/YYYY');
    $scope.monthlyAttendances = {};
    $scope.monthlyAttendancesSummary = {};
    $scope.myAttendanceForm = {};
    $scope.myAttendanceForm.name = "self";
    $scope.myAttendanceForm.monthlyFrom = moment().startOf("month").format('DD/MM/YYYY');
    $scope.myAttendanceForm.monthlyTo = moment().endOf("month").format('DD/MM/YYYY');
    $scope.myAttendances = {};
    $scope.myAttendancesSummary = {};
    $scope.employees = {};
    $scope.summaryReportForm = {};
    $scope.summaryReportForm.date = moment().format('MM/YYYY');
    $scope.monthSummaryDetails = {};
    $scope.monthSummaryHeads = {};
    $scope.monthSummaryleaveTypes = {};
    $scope.monthSummaryMonthName = "";
    $scope.monthSummaryYearName = "";
    $scope.server_info = JSON.parse($rootScope.dashboardData.server_info);
    
    $scope.$on('$viewContentLoaded', function() {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Manage Attendance';
        $scope.controllerRouter();
    });

    $scope.loadWorkShiftsDate = function( page ){
        showHideLoad();
        $scope.workshiftsPageNumber = page;
        dataFactory.httpRequest('index.php/manageAttendance/listWorkshifts/' + $scope.workshiftsPageNumber).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.workshifts = data.workShifts;
                $scope.totalWorkshifts = data.workShiftsCount;
                $scope.employees = data.employees;
                $scope.changeView('workShiftsList');
                $scope.checkedTab = "shifts";
            }
        });
    }

    $scope.createWorkShift = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageAttendance/createWorkshift', 'POST', {}, $scope.workshiftForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.workshiftForm = {};
                $scope.loadWorkShiftsDate( $scope.workshiftsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.editWorkShift = function(id){
        showHideLoad();
        let send_data = { workshift_id: id }
        dataFactory.httpRequest('index.php/manageAttendance/viewWorkshift', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.workshiftForm = data.workShift;
                $scope.changeView('workShiftsEdit');
                $scope.checkedTab = "shifts";
            }
        });
    }

    $scope.saveWorkShift = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageAttendance/editWorkshift', 'POST', {}, $scope.workshiftForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.workshiftForm = {};
                $scope.loadWorkShiftsDate( $scope.workshiftsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }
    
    $scope.removeWorkShift = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this work shift ?',
            function(){
                showHideLoad();
                let send_data = { workshift_id: item.id }
                dataFactory.httpRequest('index.php/manageAttendance/removeWorkshift', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.workshifts.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.loadAttendanceView = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listAllDepartments').then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.departments = data.departments;
                $scope.changeView('attendancesList');
                $scope.checkedTab = "attendances";
            }
        });
    }

    $scope.filterAttendance = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageAttendance/filterAttendances', 'POST', {}, $scope.attendenceForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.attendences = data.attendences;
                if( $scope.attendences.length )
                {
                    $scope.isFiltered = true;
                    $timeout(function () { 
                        $.each( $('.attendence-table .inTimePicker'), function (key, valueOfElement) {
                            let value = $scope.attendences[key].inTime;
                            if( value ) $(this).timepicker({ showInputs: false, minuteStep: 1, defaultTime: value });
                            else $(this).timepicker({ showInputs: false, minuteStep: 1 });
                        });
                        $.each( $('.attendence-table .outTimePicker'), function (key, valueOfElement) {
                            let value = $scope.attendences[key].outTime;
                            if( value ) $(this).timepicker({ showInputs: false, minuteStep: 1, defaultTime: value });
                            else $(this).timepicker({ showInputs: false, minuteStep: 1 });
                        });
                    } , 1000);
                }
            }
        });
    }

    $scope.takeAttendance = function(){
        $scope.speicalLoadingIcon = true;
        let send_data = {
            main: $scope.attendenceForm,
            secondary: $scope.attendences
        }
        dataFactory.httpRequest('index.php/manageAttendance/takeAttendance', 'POST', {}, send_data).then(function(data) {
            $scope.speicalLoadingIcon = false;
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else if( data.status == "success" )
            {
                apiResponse(data, 'edit');
            } else {  }
        });
    }

    $scope.jumpTo = function( name, excuse = null ){
        switch( name )
        {
            case 'shifts': {
                if( excuse ) { $scope.loadWorkShiftsDate( $scope.workshiftsPageNumber ); }
                else if( $scope.checkedTab != "shifts" ) { $scope.loadWorkShiftsDate( $scope.workshiftsPageNumber ); }
                break;
            }
            case 'attendances':{
                if( excuse ) { $scope.loadAttendanceView(); }
                else if( $scope.checkedTab != "attendances" ) { $scope.loadAttendanceView(); }
                break;
            }
        }
    }

    $scope.controllerRouter = function(){
        if( $scope.checkedTab == "" )
        {
            if( $rootScope.can('workshifts.list') ) { $scope.jumpTo('shifts'); }
            else if( $rootScope.can('attendances.list') ) { $scope.jumpTo('attendances'); }
        }
    }

    $scope.changeView = function(view){
        $scope.views.workShiftsList = false;
        $scope.views.workShiftsAdd = false;
        $scope.views.workShiftsEdit = false;
        $scope.views.attendancesList = false;
        $scope.views[view] = true;
        if( view == "workShiftsAdd" )
        {
            $('.timepicker').timepicker({ showInputs: false, minuteStep: 1 });
        }
        if( view == "workShiftsEdit" )
        {
            $('#shiftStartTime').timepicker({ showInputs: false, minuteStep: 1, defaultTime: $scope.workshiftForm.start });
            $('#shiftEndTime').timepicker({ showInputs: false, minuteStep: 1, defaultTime: $scope.workshiftForm.end });
            $('#shiftLateTime').timepicker({ showInputs: false, minuteStep: 1, defaultTime: $scope.workshiftForm.late });
        }
    }

    $scope.pageChanged = function(newPage) {
        switch( $scope.checkedTab ){
            case 'shifts': { $scope.loadWorkShiftsDate( newPage ); break; }
        }
    }

    $('#summaryMonth').datepicker({ startView: 1, minViewMode: 1, autoclose: true, format: "mm/yyyy" });
});