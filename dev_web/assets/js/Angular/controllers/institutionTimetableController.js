var CuteBrains = angular.module('institutionTimetableController', []);

CuteBrains.controller('institutionTimetableController', function(dataFactory, $scope) {
	$scope.isLoading = false;
	$scope.loadingIcon = false;
	$scope.classes = {};
	$scope.sections = {};
	$scope.teachers = [];
	$scope.subjects = [];
	$scope.classId = 0;
	$scope.sectionId = 0;
	$scope.form = {};
	$scope.timeTable = [];

	$scope.$on('$viewContentLoaded', function() {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Classes Timetable';
	});

    $scope.preLoad = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/classschedule/preLoad').then(function(data) {
			$scope.classes = data.classes;
			$scope.teachers = data.teachers;
			$scope.subjects = data.subjects;
            $scope.form.class_id = "";
            $scope.form.section_id = "";
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
	
	$scope.getTimetable = function(){
		$scope.loadingIcon = true;
		dataFactory.httpRequest('index.php/classschedule/listSchedules', 'GET', $scope.form).then(function(data) {
			$scope.classId = data.classId;
			$scope.sectionId = data.sectionId;
			$scope.timeTable = data.schedule;
			$scope.loadingIcon = false;
			setTimeout(function(){ pickupTime(); teachersAhead(); subjectsAhead(); }, 500);
        });
	}

	function pickupTime(){
		$('input.pickup-time').ptTimeSelect({
			onClose: function(item){
				var value = $(item).val();
				var time_type = $(item).data('time-type');
				var day_key = $(item).data('day-row');
				var period_key = $(item).data('period-row');
				angular.forEach($scope.timeTable, function(day, dayKey){
					angular.forEach(day.schedule, function(period, periodKey){
						if( $.trim( value ) )
						{
							if(time_type == 'startTime' && day_key == dayKey && period_key == periodKey) $scope.timeTable[dayKey]['schedule'][periodKey].startTime = value;
							else if(time_type == 'endTime' && day_key == dayKey && period_key == periodKey) $scope.timeTable[dayKey]['schedule'][periodKey].endTime = value;
						}
					});
				});
			}
		});
	}

	function teachersAhead(){
		angular.forEach($scope.timeTable, function(day, dayKey){
			angular.forEach(day.schedule, function(period, periodKey){
				let teacherName = '.teacher_' + dayKey + '_' + periodKey;
				$( teacherName ).typeahead({
					source: $scope.teachers,
					autoSelect: true,
					afterSelect: function(args){
						$scope.timeTable[dayKey]['schedule'][periodKey].teacherId = args.id;
						$scope.timeTable[dayKey]['schedule'][periodKey].teacherName = args.name;
					}
				});
			});
		});
	}

	function subjectsAhead(){
		angular.forEach($scope.timeTable, function(day, dayKey){
			angular.forEach(day.schedule, function(period, periodKey){
				let subjectName = '.subject_' + dayKey + '_' + periodKey;
				$( subjectName ).typeahead({
					source: $scope.subjects,
					autoSelect: true,
					afterSelect: function(args){
						$scope.timeTable[dayKey]['schedule'][periodKey].subjectId = args.id;
						$scope.timeTable[dayKey]['schedule'][periodKey].subjectName = args.name;
					}
				});
			});
		});
	}

	$scope.updatePeriod = function(dayKey, periodKey){
		current_status = $scope.timeTable[dayKey]['schedule'][periodKey].disabled_status;
		$scope.timeTable[dayKey]['schedule'][periodKey].disabled_status = !current_status;
	}

	$scope.removePeriod = function(periodId, dayKey, periodKey){
		alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
		alertify.defaults.theme.input = 'form-control';
		alertify.confirm(
			'Confirm deletion',
			'Are you sure to remove this period ?',
			function(){
				showHideLoad();
				$('[tooltip]').tooltip('hide');
				if( periodId == "NEW" ) { $scope.deletePeriod(dayKey, periodKey); showHideLoad(true); }
				else
				{
					let send_data = {period_id: periodId};
					dataFactory.httpRequest('index.php/classschedule/removeSchedule', 'POST', {}, send_data).then(function(data) {
						showHideLoad(true);
						if( data.status == "failed" )
						{
							var msgType = data.data ? ( data.data == "isError" ? "error" : "remove" ) : "remove";
							apiResponse(data, msgType);
						}
						else if( data.status == "success" ) { $scope.deletePeriod(dayKey, periodKey); }
						else { apiResponse(data, 'remove'); }
					});
				}
			},
			function(){},
		);
	}

	$scope.deletePeriod = function(dayKey, periodKey){
		let newPeriods = [];
		$('[tooltip]').tooltip('hide');
		delete $scope.timeTable[dayKey]['schedule'][periodKey];
		angular.forEach($scope.timeTable[dayKey]['schedule'], function(schedule, scheduleKey){
			if( scheduleKey != periodKey )
			{
				newPeriods.push({
					id: schedule.id,
					classId: schedule.classId,
					sectionId: schedule.sectionId,
					subjectId: schedule.subjectId,
					dayOfWeek: schedule.dayOfWeek,
					teacherId: schedule.teacherId,
					startTime: schedule.startTime,
					endTime: schedule.endTime,
					is_break: schedule.is_break,
					className: schedule.className,
					sectionName: schedule.sectionName,
					subjectName: schedule.subjectName,
					teacherName: schedule.teacherName,
					disabled_status: schedule.disabled_status
				});
			}
		});
		$scope.timeTable[dayKey]['schedule'] = [];
		$scope.timeTable[dayKey]['schedule'] = newPeriods;
		setTimeout(function(){ pickupTime(); teachersAhead(); subjectsAhead(); }, 250);
		$scope.$apply();
	}

	$scope.addNewPeriod = function(dayKey){
		var newPeriod = {
			id: "NEW",
			classId: $scope.timeTable[dayKey]['classId'],
			sectionId: $scope.timeTable[dayKey]['sectionId'],
			subjectId: 0,
			dayOfWeek: $scope.timeTable[dayKey]['dayId'],
			teacherId: 0,
			startTime: "",
			endTime: "",
			is_break: "no",
			className: "",
			sectionName: "",
			subjectName: "",
			teacherName: "",
			disabled_status: false
		}
		$scope.timeTable[dayKey]['schedule'].push(newPeriod);
		setTimeout(function(){ pickupTime(); teachersAhead(); subjectsAhead(); }, 250);
	}

	$scope.addBreak = function(dayKey){
		var newPeriod = {
			id: "NEW",
			classId: $scope.timeTable[dayKey]['classId'],
			sectionId: $scope.timeTable[dayKey]['sectionId'],
			subjectId: 0,
			dayOfWeek: $scope.timeTable[dayKey]['dayId'],
			teacherId: 0,
			startTime: "",
			endTime: "",
			is_break: "yes",
			className: "",
			sectionName: "",
			subjectName: "",
			teacherName: "",
			disabled_status: false
		}
		$scope.timeTable[dayKey]['schedule'].push(newPeriod);
		setTimeout(function(){ pickupTime() }, 250);
	}

	$scope.saveTimeTable = function(){
		$scope.isLoading =  true;
		let send_data = {
			classId: $scope.classId,
			sectionId: $scope.sectionId,
			schedule: $scope.timeTable
		};
		dataFactory.httpRequest('index.php/classschedule/saveSchedules', 'POST', {}, send_data).then(function(data) {
			$scope.isLoading = false;
			if( data.status == "failed" )
			{
				var msgType = data.data ? ( data.data == "isError" ? "error" : "remove" ) : "remove";
				apiResponse(data, msgType);
			}
			else if( data.status == "success" )
			{
				$scope.timeTable = [];
				$scope.timeTable = data.schedule;
				apiResponse(data, "success");
				setTimeout(function(){ pickupTime(); teachersAhead(); subjectsAhead(); }, 500);
			}
			else { apiResponse(data, 'remove'); }
        });
	}
});