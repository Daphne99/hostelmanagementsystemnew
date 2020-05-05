var CuteBrains = angular.module('dashboardController', []);

CuteBrains.controller('dashboardController', function (dataFactory, $rootScope, $scope, $sce) {
    $scope.current_date = getDate();
    $scope.current_date2 = getDate2();
    $scope.calendarContent = false;
    $scope.fee_list = {};
    $scope.filter = {};
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.wardList = [];
    $scope.studentId = {};
    $scope.attendance = {};
    $scope.calender = {};
    $scope.calendarType = "";
    $scope.calendarContent = false;
    $scope.calendarContentProccess = false;
    $scope.calendarContentFailure = false;
    $scope.calendarContentData = {};
    $scope.calender_month = "";
    $scope.sidePayloads = [];
    $scope.fullMonth = [];

    function changeTimezone(date, ianatz) {
        // suppose the date is 12:00 UTC
        var invdate = new Date(date.toLocaleString('en-US', {
            timeZone: ianatz
        }));

        // then invdate will be 07:00 in Toronto
        // and the diff is 5 hours
        var diff = invdate.getTime() - date.getTime();

        // so 12:00 in Toronto is 17:00 UTC
        return new Date(date.getTime() + diff);
    }

    function getDate() {
        var current = new Date();
        var there = changeTimezone(current, "Asia/Kolkata");

        var date = there.getDate();
        var month = there.getMonth() + 1;
        var year = there.getFullYear();
        var current_date = date + '/' + month + '/' + year;
        return current_date;
    }

    function getDate2() {
        var current = new Date();
        var there = changeTimezone(current, "Asia/Kolkata");

        var month = there.getMonth() + 1;
        var year = there.getFullYear();
        var current_date = month + '/' + year;
        return current_date;
    }

    $scope.$on('$viewContentLoaded', function () {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Home page';
        setTimeout(function () {
            var ele = document.getElementById("mainCalendarElement").getBoundingClientRect();
            $('#birthdayCeleb').css('height', ele.height + 'px');
            $('#sideCalendarElement').css('height', ele.height + 'px');
        }, 400);
    });

    $scope.fetchAllfeeUList = function () {
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/u_fee_titles').then(function (data) {
            $scope.fee_list = data.fee_list;
            if ($scope.fee_list.length) {
                angular.forEach( $scope.fee_list, function (item, fee) {
                    if( fee.paymentTitle == 'Academic Fee Aug_Sept_2019' ) { $scope.filter.current_u_fee = fee.id; }
                });
            }
            showHideLoad(true);
        });
    }
    $scope.fetchAllfeeUList();
    if( $scope.userRole != "admin" && $scope.userRole != 'teacher' )
    {
        $scope.fetchAttendance = function(){
            showHideLoad();
            var send_data = { studentId: $scope.studentId };
            dataFactory.httpRequest('index.php/get_student_attendance', 'GET', send_data).then(function (data) {
                $scope.wardList = data.students;
                $scope.attendance = data.attenadnce;
                showHideLoad(true);
            });
        }

        $scope.moveSession = function( timeStep ){
            var send_data = {
                student: $scope.studentId,
                action: timeStep,
                current: $scope.attendance.current,
                next: $scope.attendance.next
            };
            showHideLoad();
            dataFactory.httpRequest("index.php/get_student_attendance", 'GET', send_data).then(function(data) {
                $scope.wardList = data.students;
                $scope.attendance = data.attenadnce;
                showHideLoad(true);
            });
        }

        $scope.changeWard = function(){
            showHideLoad();
            var send_data = { studentId: $scope.studentId };
            dataFactory.httpRequest('index.php/get_student_attendance', 'GET', send_data).then(function (data) {
                $scope.wardList = data.students;
                $scope.attendance = data.attenadnce;
                showHideLoad(true);
            });
        }

        $scope.fetchAttendance();
    }

    $scope.checkChangeFeetitle = function () {
        var chart = echarts.init(document.getElementById('unpaid-fees-chart'));
        var title = $scope.fee_list.find(x => x.id == $scope.filter.current_u_fee).paymentTitle;

        dataFactory.httpRequest('index.php/invoices/fetch-unpaid-fees-charts/' + title).then(function (data) {
            option = {
                legend: {},
                autoPlay: true,
                tooltip: {},
                dataset: {
                    dimensions: data.dimensions,
                    source: data.source
                },
                xAxis: {
                    type: 'category',
                    axisLabel: {
                        'interval': 0
                    },
                    data: data.classes
                },
                yAxis: {},
                series: [{
                        type: 'bar'
                    },
                    {
                        type: 'bar'
                    },
                    {
                        type: 'bar'
                    },
                    {
                        type: 'bar'
                    }
                ],
                color: ["#0074D9", "#f62d51", "#FFDC00", "#2ECC40"]
            }

            // use configuration item and data specified to show chart
            chart.setOption(option, true), $(function () {
                function resize() {
                    setTimeout(function () {
                        chart.resize()
                    }, 100)
                }
                $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
            });
        });
    }
    $scope.loadBirthday = function(type){
        let send_data = {type: type}
        showHideLoad();
        dataFactory.httpRequest('index.php/dashboard/loadBirthdays', 'POST', {}, send_data).then(function (data) {
            $rootScope.dashboardData.birthday = data.birthday;
            showHideLoad(true);
        });
    }

    $scope.loadCalendar = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/get_my_calender').then(function (data) {
            $scope.calender = data.calendar;
            $scope.fullMonth = data.fullMonth;
            $scope.calender_month = data.month;
            $scope.sidePayloads = data.fullMonth;
            setTimeout(function () {
                var ele = document.getElementById("mainCalendarElement").getBoundingClientRect();
                $('#birthdayCeleb').css('height', ele.height + 'px');
                $('#sideCalendarElement').css('height', ele.height + 'px');
            }, 400);
            showHideLoad(true);
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
            setTimeout(function () {
                var ele = document.getElementById("mainCalendarElement").getBoundingClientRect();
                $('#birthdayCeleb').css('height', ele.height + 'px');
                $('#sideCalendarElement').css('height', ele.height + 'px');
            }, 400);
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
            setTimeout(function () {
                var ele = document.getElementById("mainCalendarElement").getBoundingClientRect();
                $('#birthdayCeleb').css('height', ele.height + 'px');
                $('#sideCalendarElement').css('height', ele.height + 'px');
            }, 400);
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

    setTimeout(function () {
        var ele = document.getElementById("mainCalendarElement").getBoundingClientRect();
        $('#birthdayCeleb').css('height', ele.height + 'px');
        $('#sideCalendarElement').css('height', ele.height + 'px');
    }, 400);
})

CuteBrains.directive('unpaidFeesChart', function ($parse, $timeout, $rootScope, dataFactory) {
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function (element, attrs) {
            return function (scope, slider, attrs, controller) {
                var chart = echarts.init(document.getElementById('unpaid-fees-chart'));
                var title = 'Academic Fee Aug_Sept_2019';

                dataFactory.httpRequest('index.php/invoices/fetch-unpaid-fees-charts/' + title).then(function (data) {
                    option = {
                        legend: {},
                        autoPlay: true,
                        tooltip: {},
                        dataset: {
                            dimensions: data.dimensions,
                            source: data.source
                        },
                        xAxis: {
                            type: 'category',
                            axisLabel: {
                                'interval': 0
                            },
                            data: data.classes
                        },
                        yAxis: {},
                        series: [{
                                type: 'bar'
                            },
                            {
                                type: 'bar'
                            },
                            {
                                type: 'bar'
                            },
                            {
                                type: 'bar'
                            }
                        ],
                        color: ["#0074D9", "#f62d51", "#FFDC00", "#2ECC40"]
                    }

                    // use configuration item and data specified to show chart
                    chart.setOption(option, true), $(function () {
                        function resize() {
                            setTimeout(function () {
                                chart.resize()
                            }, 100)
                        }
                        $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
                    });
                });

            };
        }
    };
});

CuteBrains.directive('invoceDougnuts', function ($parse, $timeout, $rootScope) {
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function (element, attrs) {
            return function (scope, slider, attrs, controller) {
                var doughnutChart = echarts.init(document.getElementById('m-piechart'));
                // specify chart configuration item and data
                option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: "{b}: {c}<br/> ({d}%)"
                    },
                    legend: {
                        orient: 'vertcal',
                        x: 'center',
                        show: false,
                        y: 'bottom',
                        data: ['80', '60', '20', '140']
                    },
                    toolbox: {
                        show: false,
                        feature: {
                            dataView: {
                                show: true,
                                readOnly: false
                            },
                            magicType: {
                                show: false,
                                type: ['pie', 'funnel'],
                                option: {
                                    funnel: {
                                        x: '25%',
                                        width: '50%',
                                        funnelAlign: 'center',
                                        max: 1548
                                    }
                                }
                            },
                            restore: {
                                show: true
                            },
                            saveAsImage: {
                                show: true
                            }
                        }
                    },
                    color: ["#745af2", "#f62d51"],
                    calculable: true,
                    series: [{
                        name: 'Invoices',
                        type: 'pie',
                        radius: ['70%', '90%'],
                        itemStyle: {
                            normal: {
                                label: {
                                    show: false
                                },
                                labelLine: {
                                    show: false
                                }
                            },
                            emphasis: {
                                label: {
                                    show: true,
                                    position: 'center',
                                    textStyle: {
                                        fontSize: '17',
                                        fontWeight: 'bold'
                                    }
                                }
                            }
                        },
                        data: [{
                            value: $rootScope.dashboardData.stats.invoices,
                            name: 'Invoices'
                        }, {
                            value: $rootScope.dashboardData.stats.dueInvoices,
                            name: 'Due Invoices'
                        }]
                    }]
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

CuteBrains.directive('absentStudentsChart', function ($parse, $timeout, $rootScope, dataFactory) {
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function (element, attrs) {
            return function (scope, slider, attrs, controller) {
                var chart = echarts.init(document.getElementById('absent-students-chart'));

                dataFactory.httpRequest('index.php/attendance/get-absent-students').then(function (data) {
                    option = {
                        legend: {},
                        autoPlay: true,
                        tooltip: {},
                        dataset: {
                            dimensions: data.dimensions,
                            source: data.source
                        },
                        xAxis: {
                            type: 'category',
                            axisLabel: {
                                'interval': 0
                            },
                            data: data.classes
                        },
                        yAxis: {
                            type: 'value',
                            interval: 1
                        },
                        series: [{
                                type: 'bar'
                            },
                            {
                                type: 'bar'
                            },
                            {
                                type: 'bar'
                            },
                            {
                                type: 'bar'
                            }
                        ],
                        color: ["#0074D9", "#f62d51", "#FFDC00", "#2ECC40"]
                    }

                    // use configuration item and data specified to show chart
                    chart.setOption(option, true), $(function () {
                        function resize() {
                            setTimeout(function () {
                                chart.resize()
                            }, 100)
                        }
                        $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
                    });
                });

            };
        }
    };
});

CuteBrains.directive('absentStaffChart', function ($parse, $timeout, $rootScope, dataFactory) {
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function (element, attrs) {
            return function (scope, slider, attrs, controller) {
                var chart = echarts.init(document.getElementById('absent-staff-chart'));

                dataFactory.httpRequest('index.php/attendance/get-absent-staff').then(function (data) {
                    option = {
                        dataset: {
                            dimensions: data.days,
                        },
                        xAxis: {
                            axisLabel: {
                                'interval': 0
                            },
                            type: 'category',
                            data: data.days
                        },
                        yAxis: {
                            type: 'value',
                            interval: 1
                        },
                        series: [{
                            data: data.source2,
                            type: 'bar'
                        }],
                        color: ["#0074D9", "#f62d51", "#FFDC00", "#2ECC40"]
                    };

                    // use configuration item and data specified to show chart
                    chart.setOption(option, true), $(function () {
                        function resize() {
                            setTimeout(function () {
                                chart.resize()
                            }, 100)
                        }
                        $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
                    });
                });

            };
        }
    };
});