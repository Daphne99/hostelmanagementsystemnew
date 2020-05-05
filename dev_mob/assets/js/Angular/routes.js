CuteBrains.config(function($routeProvider,$locationProvider) {

    $routeProvider.when('/', { templateUrl : 'assets/templates/home.html', controller  : 'dashboardController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/students', { templateUrl : 'assets/templates/students.html', controller  : 'studentsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/students/admission', { templateUrl : 'assets/templates/students.html', controller  : 'studentsController', methodName: 'admission', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/student/categories', { templateUrl : 'assets/templates/student_categories.html', controller  : 'student_categories', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/students/marksheet', { templateUrl : 'assets/templates/students.html', controller  : 'studentsController', methodName: 'marksheet', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/events', { templateUrl : 'assets/templates/events.html', controller  : 'eventsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/events/:eventId', { templateUrl : 'assets/templates/events.html', controller  : 'eventsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/newsboard', { templateUrl : 'assets/templates/newsboard.html', controller  : 'newsboardController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/newsboard/:newsId', { templateUrl : 'assets/templates/newsboard.html', controller  : 'newsboardController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/subjects', { templateUrl : 'assets/templates/subjects.html', controller  : 'subjectsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/classes', { templateUrl : 'assets/templates/classes.html', controller  : 'classesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/messages', { templateUrl : 'assets/templates/messages.html', controller  : 'messagesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/messages/:messageId', { templateUrl : 'assets/templates/messages.html', controller  : 'messagesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/transports', { templateUrl : 'assets/templates/transportation.html', controller  : 'TransportsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/transport_members', { templateUrl : 'assets/templates/transportation.html', controller  : 'transport_members', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
	.when('/invoice_transport_schedule', { templateUrl : 'assets/templates/invoice_transport_schedule.html', controller  : 'invoice_transport_schedule', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
	.when('/track-buses', { templateUrl : 'assets/templates/track_buses.html', controller  : 'trackBusesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/homework', { templateUrl : 'assets/templates/homework.html', controller  : 'homeworkController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/assignments', { templateUrl : 'assets/templates/assignments.html', controller  : 'assignmentsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/attendanceStats', { templateUrl : 'assets/templates/attendanceStats.html', controller  : 'attendanceStatsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/staffAttendance', { templateUrl : 'assets/templates/staffAttendance.html', controller  : 'staffAttendanceController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/staffAttendance_report', { templateUrl : 'assets/templates/staffAttendance_report.html', controller  : 'staffAttendance_reportController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/pay-fee', { templateUrl : 'assets/templates/invoices.html', controller  : 'invoicesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/pay-fee/current', { templateUrl : 'assets/templates/invoices.html', controller  : 'invoicesController', methodName: 'currentinvoices', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/pay-fee/due', { templateUrl : 'assets/templates/invoices.html', controller  : 'invoicesController', methodName: 'dueinvoices', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/pay-fee/advanced', { templateUrl : 'assets/templates/advanced-invoices.html', controller  : 'invoicesController', methodName: 'advancedinvoices', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/pay-fee/paid', { templateUrl : 'assets/templates/paid-invoices.html', controller  : 'invoicesController', methodName: 'paidinvoices', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/classschedule', { templateUrl : 'assets/templates/classschedule.html', controller  : 'classScheduleController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/classschedule-teacher', { templateUrl : 'assets/templates/classschedule-teacher.html', controller  : 'classScheduleController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/my-marksheet', { templateUrl : 'assets/templates/myMarkSheet.html', controller  : 'myMarkSheetController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
	.when('/mark-sheets', { templateUrl : 'assets/templates/mark_sheets.html', controller  : 'markSheetsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/examsList', { templateUrl : 'assets/templates/examsList.html', controller  : 'examsListController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/media', { templateUrl : 'assets/templates/media.html', controller  : 'mediaController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    
    .when('/global-search', { templateUrl : 'assets/templates/global_search.html', controller  : 'globalSearchController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/admins', { templateUrl : 'assets/templates/admins.html', controller  : 'adminsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/employees', { templateUrl : 'assets/templates/employees.html', controller  : 'employeesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/teachers', { templateUrl : 'assets/templates/teachers.html', controller  : 'teachersController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/parents', { templateUrl : 'assets/templates/stparents.html', controller  : 'parentsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/hostel', { templateUrl : 'assets/templates/hostel.html', controller  : 'hostelController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/hostelCat', { templateUrl : 'assets/templates/hostelCat.html', controller  : 'hostelCatController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/sections', { templateUrl : 'assets/templates/sections.html', controller  : 'sectionsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/account', { templateUrl : 'assets/templates/accountSettings.html', controller  : 'accountSettingsController', methodName: 'profile', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/user-profile', { templateUrl : 'assets/templates/user-profile.html', controller  : 'userProfileController', methodName: 'user_profile', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/account/email', { templateUrl : 'assets/templates/accountSettings.html', controller  : 'accountSettingsController', methodName: 'email', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/account/password', { templateUrl : 'assets/templates/accountSettings.html', controller  : 'accountSettingsController', methodName: 'password', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/account/invoices', { templateUrl : 'assets/templates/accountSettings.html', controller  : 'accountSettingsController', methodName: 'invoices', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/attendance', { templateUrl : 'assets/templates/attendance.html', controller  : 'attendanceController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/attendance_report', { templateUrl : 'assets/templates/attendance_report.html', controller  : 'attendance_reportController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/gradeLevels', { templateUrl : 'assets/templates/gradeLevels.html', controller  : 'gradeLevelsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/teacher-logs', { templateUrl : 'assets/templates/teacher-logs.html', controller  : 'teacherLogsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    
    .when('/calender', { templateUrl : 'assets/templates/calender.html', controller  : 'calenderController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/mailsms', { templateUrl : 'assets/templates/mailsms.html', controller  : 'mailsmsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/mailsmsTemplates', { templateUrl : 'assets/templates/mailsmsTemplates.html', controller  : 'mailsmsTemplatesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/onlineExams', { templateUrl : 'assets/templates/onlineExams.html', controller  : 'onlineExamsController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/mobileNotif', { templateUrl : 'assets/templates/mobileNotif.html', controller  : 'mobileNotifController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/roles', { templateUrl : 'assets/templates/roles.html', controller  : 'rolesController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/academicYear', { templateUrl : 'assets/templates/academicYear.html', controller  : 'academicYearController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .when('/promotion', { templateUrl : 'assets/templates/promotion.html', controller  : 'promotionController', resolve: { essentialData: function(srvLibrary) { return srvLibrary.getEssentials(); } } })
    .otherwise({ redirectTo:'/' });
});

CuteBrains.factory('srvLibrary', ['$http','$rootScope', function($http,$rootScope) {
    var sdo = {
        getEssentials: function() {
            if(typeof($rootScope.dashboardData) == "undefined"){
                var promise = $http({
                    method: 'GET',
                    url: 'index.php/dashboard'
                });
                promise.success(function(data, status, headers, conf) {
                    $rootScope.dashboardData = data;
                    $rootScope.phrase = $rootScope.dashboardData.language;

                    $rootScope.angDateFormat = $rootScope.dashboardData.dateformat;
                    if($rootScope.angDateFormat == ""){
                        $rootScope.angDateFormat = "MM/dd/yyyy";
                    }else{
                        $rootScope.angDateFormat = $rootScope.angDateFormat.replace('d','dd');
                        $rootScope.angDateFormat = $rootScope.angDateFormat.replace('m','MM');
                        $rootScope.angDateFormat = $rootScope.angDateFormat.replace('Y','yyyy');
                    }

                    if($rootScope.dashboardData.gcalendar == "ethiopic"){
                        $rootScope.dashboardData.gcalendar = "ethiopian";
                    }

                    return true;
                });
                return promise;
            }else{
                return true;
            }
        }
    }
    return sdo;
}]);

CuteBrains.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;

            element.bind('change', function(){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);

CuteBrains.service('fileUpload', ['$http', function ($http) {
    this.uploadFileToUrl = function(file, uploadUrl){
        var fd = new FormData();
        angular.forEach(file, function(value, key) {
            fd.append(key, value);
        });
        $http.post(uploadUrl, fd, {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        })
        .success(function(){
        })
        .error(function(){
        });
    }
}]);

CuteBrains.factory('dataFactory', function($http) {
    var myService = {
        httpRequest: function(url,method,params,dataPost,upload) {
            var passParameters = {};
            passParameters.url = url;

            if (typeof method == 'undefined'){
                passParameters.method = 'GET';
            }else{
                passParameters.method = method;
            }

            if (typeof params != 'undefined'){
                passParameters.params = params;
            }

            if (typeof dataPost != 'undefined'){
                passParameters.data = dataPost;
            }

            if (typeof upload != 'undefined'){
                var fd = new FormData();

                angular.forEach(dataPost, function(value, key) {
                    if(typeof value == 'object' && upload.indexOf(key) == -1 ){
                        value = JSON.stringify(value);
                    }
                    fd.append(key, value);
                });

                passParameters.data = fd;

                passParameters.transformRequest = angular.identity;
                passParameters.headers = {'Content-Type': undefined};
            }

            var promise = $http(passParameters).then(function (response) {
                if(typeof response.data == 'string' && response.data != 1){
                    // if(response.data.substr('loginMark')){
                    //     location.reload();
                    //     return;
                    // }
                    $.toast({
                        heading: 'Error',
                        text: response.data,
                        position: 'top-right',
                        loaderBg:'#ff6849',
                        icon: 'error',
                        hideAfter: 3000,
                        stack: 6
                    });
                    return false;
                }
                if(response.data.jsMessage){
                    $.toast({
                        heading: response.data.jsTitle,
                        text: response.data.jsMessage,
                        position: 'top-right',
                        loaderBg:'#ff6849',
                        icon: 'info',
                        hideAfter: 3000,
                        stack: 6
                    });
                }
                return response.data;
            },function(response){
                // if(response.data.substr('loginMark')){
                //     location.reload();
                //     return;
                // }
                $.toast({
                    heading: 'Error',
                    text: 'An error occured while processing your request.',
                    position: 'top-right',
                    loaderBg:'#ff6849',
                    icon: 'error',
                    hideAfter: 3000,
                    stack: 6
                });
            });
            return promise;
        }
    };
    return myService;
});

CuteBrains.directive('datePicker', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                var dateformatVal = jQuery('#dateformatVal').val();
                if(typeof dateformatVal == "undefined"){
                    var dateformatVal = $rootScope.dashboardData.dateformat;
                }
                var dateformat = dateformatVal;
                if(dateformat == ""){
                    dateformat = 'dd-mm-yyyy';
                }else{
                    dateformat = dateformat.replace('d','dd');
                    dateformat = dateformat.replace('m','mm');
                    dateformat = dateformat.replace('Y','yyyy');
                }

                var calendar = jQuery('#gcalendarVal').val();
                if(typeof calendar == "undefined"){
                    calendar = $rootScope.dashboardData.gcalendar;
                }
                calendar = $.calendars.instance(calendar);

                if(typeof attrs.id == "undefined"){
                    $(".datemask").calendarsPicker({calendar: calendar,dateFormat:dateformat,showAnim:''});
                    $(".datemask").attr("autocomplete", "off");
                }else{
                    $("#"+attrs.id).calendarsPicker({calendar: calendar,dateFormat:dateformat,showAnim:''});
                    $("#"+attrs.id).attr("autocomplete", "off");
                }
            };
        }
    };
});

CuteBrains.directive('carouselInit', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('.carousel').carousel()
            };
        }
    };
});

CuteBrains.directive('mobileNumber', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attrs,ngModel) {
            var telInput = $(element);

            if( $("#countryVal").val() != undefined ){
                var countryVal = $("#countryVal").val();
            }else{
                var countryVal = $rootScope.dashboardData.country;
            }

            telInput.intlTelInput({utilsScript: jQuery('#utilsScript').val(),nationalMode: false,initialCountry:countryVal});

            scope.$watch(attrs.ngModel, function(value) {
                telInput.intlTelInput("setNumber",element.val());
            });

            scope.$watch(attrs.ngModel, function(value) {
                if(value == "" || typeof value === "undefined"){
                    ngModel.$setValidity(attrs.ngModel, true);
                    return;
                }
                if (telInput.intlTelInput("isValidNumber")) {
                    ngModel.$setValidity(attrs.ngModel, true);
                } else {
                    ngModel.$setValidity(attrs.ngModel, false);
                }
            });
        }
    };
});

CuteBrains.directive('checklistModel', ['$parse', '$compile', function($parse, $compile) {
    // contains
    function contains(arr, item, comparator) {
        if (angular.isArray(arr)) {
            for (var i = arr.length; i--;) {
                if (comparator(arr[i], item)) {
                    return true;
                }
            }
        }
        return false;
    }

    // add
    function add(arr, item, comparator) {
        arr = angular.isArray(arr) ? arr : [];
        if(!contains(arr, item, comparator)) {
            arr.push(item);
        }
        return arr;
    }

    // remove
    function remove(arr, item, comparator) {
        if (angular.isArray(arr)) {
            for (var i = arr.length; i--;) {
                if (comparator(arr[i], item)) {
                    arr.splice(i, 1);
                    break;
                }
            }
        }
        return arr;
    }

    // http://stackoverflow.com/a/19228302/1458162
    function postLinkFn(scope, elem, attrs) {
        // exclude recursion, but still keep the model
        var checklistModel = attrs.checklistModel;
        attrs.$set("checklistModel", null);
        // compile with `ng-model` pointing to `checked`
        $compile(elem)(scope);
        attrs.$set("checklistModel", checklistModel);

        // getter for original model
        var checklistModelGetter = $parse(checklistModel);
        var checklistChange = $parse(attrs.checklistChange);
        var checklistBeforeChange = $parse(attrs.checklistBeforeChange);
        var ngModelGetter = $parse(attrs.ngModel);



        var comparator = angular.equals;

        if (attrs.hasOwnProperty('checklistComparator')){
            if (attrs.checklistComparator[0] == '.') {
                var comparatorExpression = attrs.checklistComparator.substring(1);
                comparator = function (a, b) {
                    return a[comparatorExpression] === b[comparatorExpression];
                };

            } else {
                comparator = $parse(attrs.checklistComparator)(scope.$parent);
            }
        }

        // watch UI checked change
        var unbindModel = scope.$watch(attrs.ngModel, function(newValue, oldValue) {
            if (newValue === oldValue) {
                return;
            }

            if (checklistBeforeChange && (checklistBeforeChange(scope) === false)) {
                ngModelGetter.assign(scope, contains(checklistModelGetter(scope.$parent), getChecklistValue(), comparator));
                return;
            }

            setValueInChecklistModel(getChecklistValue(), newValue);

            if (checklistChange) {
                checklistChange(scope);
            }
        });

        // watches for value change of checklistValue
        var unbindCheckListValue = scope.$watch(getChecklistValue, function(newValue, oldValue) {
            if( newValue != oldValue && angular.isDefined(oldValue) && scope[attrs.ngModel] === true ) {
                var current = checklistModelGetter(scope.$parent);
                checklistModelGetter.assign(scope.$parent, remove(current, oldValue, comparator));
                checklistModelGetter.assign(scope.$parent, add(current, newValue, comparator));
            }
        }, true);

        var unbindDestroy = scope.$on('$destroy', destroy);

        function destroy() {
            unbindModel();
            unbindCheckListValue();
            unbindDestroy();
        }

        function getChecklistValue() {
            return attrs.checklistValue ? $parse(attrs.checklistValue)(scope.$parent) : attrs.value;
        }

        function setValueInChecklistModel(value, checked) {
            var current = checklistModelGetter(scope.$parent);
            if (angular.isFunction(checklistModelGetter.assign)) {
                if (checked === true) {
                    checklistModelGetter.assign(scope.$parent, add(current, value, comparator));
                } else {
                    checklistModelGetter.assign(scope.$parent, remove(current, value, comparator));
                }
            }

        }

        // declare one function to be used for both $watch functions
        function setChecked(newArr, oldArr) {
            if (checklistBeforeChange && (checklistBeforeChange(scope) === false)) {
                setValueInChecklistModel(getChecklistValue(), ngModelGetter(scope));
                return;
            }
            ngModelGetter.assign(scope, contains(newArr, getChecklistValue(), comparator));
        }

        // watch original model change
        // use the faster $watchCollection method if it's available
        if (angular.isFunction(scope.$parent.$watchCollection)) {
            scope.$parent.$watchCollection(checklistModel, setChecked);
        } else {
            scope.$parent.$watch(checklistModel, setChecked, true);
        }
    }

    return {
        restrict: 'A',
        priority: 1000,
        terminal: true,
        scope: true,
        compile: function(tElement, tAttrs) {

            if (!tAttrs.checklistValue && !tAttrs.value) {
                throw 'You should provide `value` or `checklist-value`.';
            }

            // by default ngModel is 'checked', so we set it if not specified
            if (!tAttrs.ngModel) {
                // local scope var storing individual checkbox model
                tAttrs.$set("ngModel", "checked");
            }

            return postLinkFn;
        }
    };
}]);

CuteBrains.directive('ngEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if(event.which === 13) {
                scope.$apply(function (){
                    scope.$eval(attrs.ngEnter);
                });

                event.preventDefault();
            }
        });
    };
});

CuteBrains.directive('chatBox', function($parse, $timeout){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#chat-box').slimScroll({
                    height: '500px',alwaysVisible: true,start : "bottom"
                });
            };
        }
    };
});

CuteBrains.directive('scrollBox', function($parse, $timeout){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#'+attrs.id).slimScroll({
                    height: attrs.height,alwaysVisible: true,start : "bottom"
                });
            };
        }
    };
});

CuteBrains.directive('invoceDougnuts', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                var doughnutChart = echarts.init(document.getElementById('m-piechart'));
                // specify chart configuration item and data
                option = {
                    tooltip: {
                        trigger: 'item'
                        , formatter: "{a} <br/>{b} : {c} ({d}%)"
                    }
                    , legend: {
                        orient: 'horizontal'
                        , x: 'center'
                        , show: false
                        , y: 'bottom'
                        , data: ['80', '60', '20', '140']
                    }
                    , toolbox: {
                        show: false
                        , feature: {
                            dataView: {
                                show: true
                                , readOnly: false
                            }
                            , magicType: {
                                show: false
                                , type: ['pie', 'funnel']
                                , option: {
                                    funnel: {
                                        x: '25%'
                                        , width: '50%'
                                        , funnelAlign: 'center'
                                        , max: 1548
                                    }
                                }
                            }
                            , restore: {
                                show: true
                            }
                            , saveAsImage: {
                                show: true
                            }
                        }
                    }
                    , color: ["#745af2", "#f62d51"]
                    , calculable: true
                    , series: [
                        {
                            name: 'Invoices'
                            , type: 'pie'
                            , radius: ['70%', '90%']
                            , itemStyle: {
                                normal: {
                                    label: {
                                        show: false
                                    }
                                    , labelLine: {
                                        show: false
                                    }
                                }
                                , emphasis: {
                                    label: {
                                        show: true
                                        , position: 'center'
                                        , textStyle: {
                                            fontSize: '30'
                                            , fontWeight: 'bold'
                                        }
                                    }
                                }
                            }
                            , data: [
                                {
                                    value: $rootScope.dashboardData.stats.invoices, name: 'Invoices'
                                }
                                , {
                                    value: $rootScope.dashboardData.stats.dueInvoices, name: 'Due Invoices'
                                }
                                ]
                            }
                        ]
                };
                // use configuration item and data specified to show chart
                doughnutChart.setOption(option, true), $(function () {
                    function resize() {
                        setTimeout(function () {
                            doughnutChart.resize()
                        }, 100)
                    }
                    $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
                });
            };
        }
    };
});

CuteBrains.directive('colorbox', function() {
    return {
        restrict: 'AC',
        link: function (scope, element, attrs) {
            var itemsVars = {transition:'elastic',title:attrs.title,rel:'gallery',scalePhotos:true};
            if(attrs.youtube){
                itemsVars['iframe'] = true;
                itemsVars['innerWidth'] = 640;
                itemsVars['innerHeight'] = 390;
            }
            if(attrs.vimeo){
                itemsVars['iframe'] = true;
                itemsVars['innerWidth'] = 500;
                itemsVars['innerHeight'] = 409;
            }
            if(!attrs.youtube && !attrs.vimeo){
                itemsVars['height'] = "100%";
            }
            $(element).colorbox(itemsVars);
        }
    };
});

CuteBrains.directive('ckEditor', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function ($scope, element, attrs, ngModel) {

            if($rootScope.dashboardData.wysiwyg_type == "advanced"){
                var ckconfig = {};
                ckconfig.enterMode = CKEDITOR.ENTER_BR;
                ckconfig.shiftEnterMode = CKEDITOR.ENTER_P;
                ckconfig.extraPlugins = 'font,justify';

                var ck = CKEDITOR.replace(element[0],ckconfig);

                ck.on('pasteState', function () {
                    $scope.$apply(function () {
                        ngModel.$setViewValue(ck.getData());
                    });
                });

                ngModel.$render = function (value) {
                    ck.setData(ngModel.$modelValue);
                };
            }else{
                $(element).summernote({height: 300});

                $(element).on('summernote.change', function(we, contents, $editable) {
                    $scope.$apply(function () {
                        ngModel.$setViewValue(contents);
                    });
                });

                ngModel.$render = function (value) {
                    $(element).summernote('code', ngModel.$modelValue);
                };

            }

        }
    };
});

CuteBrains.directive('calendarBox', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                var calendar = $.calendars.instance($rootScope.dashboardData.gcalendar);
                $('#calendar').calendarsPicker({calendar: calendar,showOtherMonths:false,selectOtherMonths:false,onSelect:null,onChangeMonthYear:showOtherCalEvents});

                var todayDate = calendar.today();
                var d = calendar.newDate(todayDate._year, todayDate._month, 1);

                var start = calendar.minDay+"-"+todayDate._month+"-"+todayDate._year;
                var end = d.daysInMonth()+"-"+todayDate._month+"-"+todayDate._year;

                $.get("index.php/calender",{start : start, end : end},function(data) {
                        populateEventsInFullCal(data,$rootScope.dashboardData.gcalendar);
                    }
                );
            };
        }
    };
});

function showOtherCalEvents(year,month,inst) {
    var gc = $.calendars.instance(inst.drawDate._calendar.local['name']);
    var d = gc.newDate(year, month, 1);

    var start = gc.minDay+"-"+month+"-"+year;
    var end = d.daysInMonth()+"-"+month+"-"+year;

    $.get("index.php/calender",{start : start, end : end},function(data) {
            populateEventsInFullCal(data,inst.drawDate._calendar.local['name']);
        }
    );
}

function populateEventsInFullCal(events,cal_name){
    $.each( events, function( key, value ) {
        if($("#"+value.id).length == 0){
            $(".jdd"+value.start).after( "<a href='" + value.url + "' class='fullCalEvent' style='color:" + value.textColor + " !important;background-color:" + value.backgroundColor + " !important' id='" + value.id + "'>" + value.title + "</a>" );
        }
    });
}

CuteBrains.directive('smsCounter', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#messageContentSms').countSms('#sms-counter');
            };
        }
    };
});

CuteBrains.directive('modal', function () {
    return {
        template: '<div class="modal fade my-custom-modal-dir">' +
        '<div class="modal-dialog {{modalClass}}">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h4 class="modal-title {{modalTitleClass}}">{{ modalTitle }}</h4>' +
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
        '</div>' +
        '<div class="modal-body" ng-transclude></div>' +
        '</div>' +
        '</div>' +
        '</div>',
        restrict: 'E',
        transclude: true,
        replace:true,
        scope:true,
        link: function postLink(scope, element, attrs) {
            scope.$watch(attrs.visible, function(value){
                if(value == true)
                $(element).modal('show');
                else
                $(element).modal('hide');
            });

            $(element).on('shown.bs.modal', function(){
                scope.$apply(function(){
                    scope.$parent[attrs.visible] = true;
                });
            });

            $(element).on('hidden.bs.modal', function(){
                scope.$apply(function(){
                    scope.$parent[attrs.visible] = false;
                });
            });
        }
    };
});

CuteBrains.directive('scalendarBox', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#scalendar').fullCalendar({
                    events: "calender",
                    lang: $rootScope.dashboardData.languageUniversal
                });
            };
        }
    };
});

CuteBrains.directive('tooltip', function(){
    return {
        restrict: 'A',
        link: function(scope, element, attrs){
            $(element).hover(function(){
                $(element).tooltip('show');
            }, function(){
                $(element).tooltip('hide');
            });
        }
    };
});

CuteBrains.directive('showtab',function () {
    return {
        link: function (scope, element, attrs) {
            element.click(function(e) {
                e.preventDefault();
                $(element).tab('show');
            });
        }
    };
});

CuteBrains.directive('tabheads',function () {
    return {
        link: function (scope, element, attrs) {
            $(element).children().first().addClass('active');
        }
    };
});

CuteBrains.directive('tabcontent',function () {
    return {
        link: function (scope, element, attrs) {
            $(element).children().first().addClass('active');
        }
    };
});

CuteBrains.directive('parseStyle', function($interpolate) {
    return function(scope, elem) {
        console.log(elem.html());
        var exp = $interpolate(elem.html()),
            watchFunc = function () { return exp(scope); };

        scope.$watch(watchFunc, function (html) {
            elem.html(html);
        });
    };
});

CuteBrains.filter('object2Array', function() {
    return function(input) {
        var out = [];
        for(i in input){
            out.push(input[i]);
        }
        return out;
    }
});

function uploadSuccessOrError(response){
    if(typeof response == 'string' && response != 1){
        $.toast({
            heading: 'School Application',
            text: response,
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'error',
            hideAfter: 3000,
            stack: 6
        });
        return false;
    }
    if(response.jsMessage){
        $.toast({
            heading: response.jsTitle,
            text: response.jsMessage,
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'info',
            hideAfter: 3000,
            stack: 6
        });
    }
    if(response.jsStatus){
        if(response.jsStatus == "0"){
            return false;
        }
    }
    return response;
}

function successOrError(data){
    if(data.jsStatus){
        if(data.jsStatus == "0"){
            return false;
        }
    }
    return data;
}

//New Functions Implementation
function apiResponse(response, status){
    if(response.status)
    {
        var payload = {};
        if(typeof status !== 'undefined')
        {
            if( status == "add" ) payload.icon = "success";
            else if( status == "success" ) payload.icon = "success";
            else if ( status == "edit" ) payload.icon = "success";
            else if ( status == "remove" ) payload.icon = "info";
            else if ( status == "error" ) payload.icon = "error";
            else if ( status == "missing" ) payload.icon = "warning";
            else if ( status == "failed" ) payload.icon = "error";
        }
        else
        {
            if( response.status == "success" ) payload.icon = "success";
            else if ( response.status == "failed" ) payload.icon = "error";
        }
        if( typeof response.title !== 'undefined' ) payload.title = response.title;
        if( typeof response.message !== 'undefined' ) payload.html = response.message;
        if( typeof response.timer !== 'undefined' ) payload.timer = response.timer; else { payload.timer = 4500; }
        if( typeof payload.title !== 'undefined' || typeof payload.message !== 'undefined' || typeof payload.icon !== 'undefined' )
        {
            swal.fire( payload );
        } else return response;
    } else return response;
}

function apiModifyTable(originalData,id,response){
    angular.forEach(originalData, function (item,key) {
        if(item.id == id){
            originalData[key] = response;
        }
    });
    return originalData;
}