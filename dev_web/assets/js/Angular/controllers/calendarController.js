var CuteBrains = angular.module('calendarController', []);

CuteBrains.controller('calendarController', function (dataFactory, $rootScope, $scope, $sce) {
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.calender = {};
    $scope.calendarType = "";
    $scope.calendarContent = false;
    $scope.calendarContentProccess = false;
    $scope.calendarContentFailure = false;
    $scope.calendarContentData = {};
    $scope.calender_month = "";
    $scope.sidePayloads = [];
    $scope.fullMonth = [];

    $scope.$on('$viewContentLoaded', function () {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Calendar';
    })

    $scope.loadCalendar = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/get_my_calender').then(function (data) {
            showHideLoad(true);
            $scope.calender = data.calendar;
            $scope.fullMonth = data.fullMonth;
            $scope.calender_month = data.month;
            $scope.sidePayloads = data.fullMonth;
        });
    }
    $scope.loadCalendar();

    $('#calendarMonth').datepicker({ startView: 1, minViewMode: 1, autoclose: true, format: "M yyyy", onSelect:function() {
        $scope.changeCalendarMonth();
    }});
    
    $scope.changeCalendarMonth = function(){
        showHideLoad();
        var send_data = { month: $scope.calender_month };
        dataFactory.httpRequest('index.php/get_my_calender', 'GET', send_data).then(function (data) {
            $scope.calender = data.calendar;
            $scope.fullMonth = data.fullMonth;
            $scope.calender_month = data.month;
            $scope.sidePayloads = data.fullMonth;
            showHideLoad(true);
        });
    }

    $scope.loadDayData = function(week, day){
        var day = day + 1;
        var dayData = $scope.calender[week][day];
        if( dayData != false && dayData != undefined )
        {
            $scope.markActive( dayData.dayOfMonth, week, day );
        }
    }

    $scope.markActive = function(day, weekIndex, dayIndex){
        var finalStatus = false;
        angular.forEach($scope.calender, function(oneWeek, weekKey) {
            angular.forEach(oneWeek, function(oneDay, dayKey) {
                if( oneDay != false )
                {
                    if( day == oneDay.dayOfMonth )
                    {
                        $scope.calender[weekKey][dayKey].selected = oneDay.selected == true ? false : true;
                        finalStatus = $scope.calender[weekKey][dayKey].selected;
                    } else $scope.calender[weekKey][dayKey].selected = false;
                }
            });
        });
        if( finalStatus == true )
        {
            // show only day payloads
            $scope.sidePayloads = [];
            var dayData = $scope.calender[weekIndex][dayIndex];
            var dayPayload = {
                day: dayData.fullDate,
                payloads: dayData.payloads
            };
            $scope.sidePayloads.push(dayPayload);
        }
        else
        {
            // show full month payload
            $scope.sidePayloads = [];
            $scope.sidePayloads = $scope.fullMonth;
        }
    }

    $scope.readContent = function(entryType, entryId){
        var type = getEntryType( entryType );
        if( type )
        {
            $scope.modalTitle = entryType + " Details";
            if( type == "exam" || type == "event" || type == "notice") { $scope.modalClass = "modal-lg"; }
            else    { $scope.modalClass = "modal-md"; }
            var send_data = { id: entryId, type: type }
            $scope.calendarContent = true;
            $scope.calendarContentProccess = true;
            $scope.calendarType = type;
            dataFactory.httpRequest('index.php/get_calender_content', 'GET', send_data).then(function (data) {
                $scope.calendarContentProccess = false;
                if( data.status == "success" )
                {
                    $scope.calendarContentFailure = false;
                    $scope.calendarContentData = data.payload;
                    if( type == "event" || type == "notice" )
                    {
                        $scope.calendarContentData.desc = $sce.trustAsHtml(data.payload.desc);
                    }
                } else { $scope.calendarContentFailure = true; }
            });
        }
    }

    $scope.moveMonth = function(action){
        showHideLoad();
        var send_data = { month: $scope.calender_month, action: action };
        dataFactory.httpRequest('index.php/get_my_calender', 'GET', send_data).then(function (data) {
            $scope.calender = data.calendar;
            $scope.fullMonth = data.fullMonth;
            $scope.calender_month = data.month;
            $scope.sidePayloads = data.fullMonth;
            showHideLoad(true);
        });
    }

    function getEntryType( entryType ){
        switch(entryType)
        {
            case 'Event' : { return 'event'; }
            case 'Exam' : { return 'exam'; }
            case 'Homework' : { return 'homework'; }
            case 'Holiday' : { return 'holiday'; }
            case 'Notice' : { return 'notice'; }
            default : { return null; }
        }
    }
});