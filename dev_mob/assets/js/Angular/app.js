if (jQuery) {
    var originalFn = $.fn.data;
    $.fn.data = function() {
        if (arguments[0] !== '$binding')
        return originalFn.apply(this, arguments);
    }
}

var CuteBrains = angular.module('CuteBrains',[
	'ngRoute', 'ngFileUpload', 'ngCookies','ngUpload', 'ui.autocomplete','angularUtils.directives.dirPagination', 'timer',
	// Controllers {
		'dashboardController',
		'accountSettingsController',
		'adminsController',
		'assignmentsController',
		'attendance_reportController',
		'attendanceController',
		'attendanceStatsController',
		'classesController',
		'employeesController',
		'eventsController',
		'examsListController',
		'myMarkSheetController',
		'gradeLevelsController',
		'homeworkController',
		'hostelCatController',
		'hostelController',
		'invoicesController',
		'mainController',
		'mediaController',
		'messagesController',
		'mobileNotifController',
		'newsboardController',
		'parentsController',
		'registeration',
		'sectionsController',
		'staffAttendance_reportController',
		'staffAttendanceController',
		'student_categories',
		'studentsController',
		'subjectsController',
		'teachersController',
		'transport_members',
		'TransportsController',
		'classScheduleController',
		'userProfileController',
		'trackBusesController',
		'markSheetsController',
		'teacherLogsController',
	// }
]).run(function($http,dataFactory,$rootScope,$q,Upload,$timeout,$location,$window) {
    $rootScope.defaultAcademicYear = function() {
        angular.forEach($rootScope.dashboardData.academicYear, function (item) {
            if(item.isDefault == "1"){
                return item.id;
            }
        });
    }
    // console.log($location.$$absUrl);
    $rootScope.mm_select_upload = function(files,errFiles){
        if(files == null || files.length == 0){
            $rootScope.media_manager = !$rootScope.media_manager;
            $rootScope.gallery_return_scope();
        }else{
            $rootScope.files = files;
            $rootScope.errFiles = errFiles;
            $rootScope.mm_files_count = 0;
            if($rootScope.allow_multiple == false){
                files_tmp = files;
                files = [];
                files.push(files_tmp);
            }
            angular.forEach(files, function(file) {
                if(typeof file.uploaded == "undefined"){
                    showHideLoad();

                    file.upload = Upload.upload({
                        url: 'index.php/ml_upload',
                        data: {file: file}
                    });

                    file.upload.then(function (response) {
                        $timeout(function () {
                            $rootScope.mm_files_count ++;
                            file.result = response.data;
                            $rootScope.selected_images.push(response.data.file);
                            if(files.length == $rootScope.mm_files_count){
                                $rootScope.media_manager = !$rootScope.media_manager;
                                $rootScope.gallery_return_scope();
                                showHideLoad(true);
                            }
                        });
                    }, function (response) {
                        if (response.status > 0)
                            $rootScope.errorMsg = response.status + ': ' + response.data;
                    }, function (evt) {
                        file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
                    });
                }

            });
        }
    }

    $rootScope.mm_cancel = function(){
        $rootScope.selected_images = [];
        $rootScope.media_manager = !$rootScope.media_manager;
        $rootScope.modalClass = "";
    }

    $rootScope.mm_upload_selected = function(){
        if(!$rootScope.allow_multiple){
            $('.mm_gallery_image_selected').removeClass('mm_gallery_image_selected');
            $rootScope.selected_images = [];
        }
    }

    $rootScope.mm_open = function(){
        $rootScope.selected_images = [];
        $rootScope.gallery_images = [];
        $rootScope.media_manager = !$rootScope.media_manager;
        if($rootScope.show_gallery == true && $rootScope.gallery_images.length == 0){
            dataFactory.httpRequest('index.php/ml_upload/load').then(function(data) {
                angular.forEach(data, function(file) {
                    $rootScope.gallery_images.push(file);
                    $rootScope.mm_last_id = file.id;
                });
            });
        }
    }

    $rootScope.mm_load_more = function(){
        $('.mm_load_more_loading').show();
        dataFactory.httpRequest('index.php/ml_upload/load/'+$rootScope.mm_last_id).then(function(data) {
            if(data.length < 25){
                $('.mm_load_more').hide();
            }
            angular.forEach(data, function(file) {
                $rootScope.gallery_images.push(file);
                $rootScope.mm_last_id = file.id;
            });
            $('.mm_load_more_loading').hide();
        });
    }

    $rootScope.mm_select_image = function(file,$event){
        var element = angular.element($event.target);
        if( $(element).hasClass('mm_gallery_image_selected') ){
            $(element).removeClass('mm_gallery_image_selected');
            angular.forEach($rootScope.selected_images, function(image, key) {
                if(image.id == file.id){
                    delete $rootScope.selected_images.splice(key,1);
                }
            });
        }else{
            if($rootScope.allow_multiple){
                $rootScope.selected_images.push(file);
            }else{
                $rootScope.mm_files = null;
                $('.mm_gallery_image_selected').removeClass('mm_gallery_image_selected');
                $rootScope.selected_images = [];
                $rootScope.selected_images.push(file);
            }
            $(element).addClass('mm_gallery_image_selected');
        }
    }

    $rootScope.can = function(perm){
        if($rootScope.dashboardData.perms.indexOf(perm) !== -1) {
            return true
        }
        return false;
    }

    $rootScope.online = navigator.onLine;
    $window.addEventListener("offline", function() {
      $rootScope.$apply(function() {
        $rootScope.online = false;
      });
    }, false);
    $window.addEventListener("online", function() {
      $rootScope.$apply(function() {
        $rootScope.online = true;
      });
    }, false);
    $rootScope.$watch('online', function(newStatus) {
    	if(!newStatus) {
    		$('#no_internet').show();
    	} else {
    		$('#no_internet').hide();
    	}
    });

    // update the app
    // if( typeof Android !== 'undefined' )
    // {
    //     try { $rootScope.current_version = Android.getAndroidVersion() }
	// 	catch(err) { $rootScope.current_version = '5.2'; }

    //     $rootScope.$watch('current_version', function(newStatus)
    //     {
	//     	dataFactory.httpRequest('index.php/dashboard/mobile-attrs').then(function(data) {
    //             var app_version = data.app_version;
    //             var server_info = JSON.parse(data.server_info);
    //             if( parseFloat( app_version ) < 5.2 || parseFloat( newStatus ) < 5.2 )
    //             {
    //                 $('#update_app').show();
    //             }
    //             else
    //             {
    //                 $('#update_app').hide();
    //             }
	//     	});
    // 	});
  	// }
});

CuteBrains.config(function($logProvider){
    $logProvider.debugEnabled(false);
});

var appBaseUrl = $('base').attr('href');

CuteBrains.controller('calenderController', function(dataFactory,$scope) {
    showHideLoad(true);
});