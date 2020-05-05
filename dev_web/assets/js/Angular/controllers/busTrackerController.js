var CuteBrains = angular.module('busTrackerController', []);

CuteBrains.controller('busTrackerController', function(dataFactory,$rootScope,$scope,$sce,$route,$location) {
    $scope.students = {};
    $scope.studentsTemp = {};
    $scope.totalItemsTemp = {};
    $scope.classes = {};
    $scope.sections = {};
    $scope.transports = {};
    $scope.vehicles = {};
    $scope.hostel = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;
    $scope.medViewMode = true;
    $scope.searchInput = {};
    $scope.current_page = 1;
    $scope.roles = {};
    $scope.student_categories = [];
    $scope.student_types = [];
    $scope.currentUserRole = $rootScope.dashboardData.role;

    $scope.$on('$viewContentLoaded', function() {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Track Bus';
    });

    $scope.changeView = function(view){
        if(view == "list" || view == "show"){
            $scope.form = {};
        }

        $scope.views.list = false;
        $scope.views.bus_track = false;
        $scope.views[view] = true;
    }

    $scope.listUsers = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/listAll/'+pageNumber).then(function(data) {
            $scope.students = data.students ;
            $scope.classes = data.classes ;
            $scope.sections = data.sections ;
            $scope.transports = data.transports ;
            $scope.vehicles = data.transport_vehicles;
            $scope.hostel = data.hostel ;
            $scope.totalItems = data.totalItems
            $scope.userRole = data.userRole;
            $scope.roles = data.roles;
            $scope.student_categories = data.student_categories;
            $scope.student_types = data.student_types;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);
            showHideLoad(true);
        });
    }

    $scope.searchDB = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/listAll/'+pageNumber,'POST',{},{'searchInput':$scope.searchInput}).then(function(data) {
            $scope.students = data.students ;
            $scope.classes = data.classes ;
            $scope.sections = data.sections ;
            $scope.transports = data.transports ;
            $scope.vehicles = data.transport_vehicles;
            $scope.hostel = data.hostel ;
            $scope.totalItems = data.totalItems
            $scope.userRole = data.userRole;
            $scope.student_categories = data.student_categories;
            $scope.student_types = data.student_types;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);
            showHideLoad(true);
        });
    }

    $scope.getResultsPage = function(newpage = ""){
        if(newpage != ""){
            $scope.current_page = newpage;
        }
        if ( !jQuery.isEmptyObject($scope.searchInput) ) {
            $scope.searchDB( $scope.current_page );
        }else{
            $scope.listUsers( $scope.current_page );
        }
        $scope.changeView('list');
    }

    $scope.sortItems = function(sortBy){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/listAll/1','POST',{},{'sortBy':sortBy}).then(function(data) {
            $scope.students = data.students ;
            $scope.classes = data.classes ;
            $scope.sections = data.sections ;
            $scope.transports = data.transports ;
            $scope.vehicles = data.transport_vehicles;
            $scope.hostel = data.hostel ;
            $scope.totalItems = data.totalItems
            $scope.userRole = data.userRole;
            $scope.student_categories = data.student_categories;
            $scope.student_types = data.student_types;
            $rootScope.dashboardData.sort.students = sortBy;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);
            showHideLoad(true);
        });
    }
    
    $scope.getResultsPage();

    $scope.toggleSearch = function(){
        $('.advSearch').toggleClass('col-0 col-3 hidden',1000);
        $('.listContent').toggleClass('col-12 col-9',1000);
    }

    $scope.resetSearch = function(){
        $scope.searchInput = {};
        $scope.getResultsPage(1);
    }
    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalClass = data.modalClass;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        $scope.getResultsPage(newPage);
    };

    $scope.searchSubjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.searchInput.class}).then(function(data) {
            $scope.sections = data.sections;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);
        });
    }

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.studentClass}).then(function(data) {
            $scope.sections = data.sections;
        });
    }

    /* ----- trackBus ------ */

    $scope.busTrackInfo = {};
    $scope.gps_iframe_link = '';

    $scope.trackBus = function(student_id) {
    	showHideLoad();
        dataFactory.httpRequest('index.php/students/get-bus-track-info/'+student_id).then(function(data) {
            $scope.busTrackInfo = data.data[0];
            if($scope.busTrackInfo.transport_vehicle != null) {
                // $scope.gps_iframe_link = 'https://gps.cutebrains.com/?stoppages=' + $scope.busTrackInfo.transport.transportTitle
                $scope.gps_iframe_link = 'https://gps.cutebrains.com/?plate_number=' + $scope.busTrackInfo.transport_vehicle.plate_number + '&stoppages=' + $scope.busTrackInfo.transport.transportTitle
                $scope.changeView('bus_track');
            }
            showHideLoad(true);
        });
    }

    $scope.trustSrc = function(src) {
        return $sce.trustAsResourceUrl(src);
    }

    /* ----- trackBus ------ */
});