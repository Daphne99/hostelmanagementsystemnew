var CuteBrains = angular.module('transport_members', []);

CuteBrains.controller('transport_members', function(dataFactory,$scope,$rootScope) {
    $scope.transports = {};
    $scope.transportsList = {};
    $scope.views = {};
    $scope.views.members = false;
    $scope.form = {};
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.loadingIcon = false;
    $scope.selctedVehicles = "";
    $scope.selctedStoppages = "";
    $scope.filteredVehicles = [];
    $scope.filteredStoppages = [];
    $scope.filteredUsers = {};

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Transport members';
    });

    $scope.load_data = function(){
        dataFactory.httpRequest('index.php/transports/members').then(function(data) {
            $scope.transports = data.transports;
            showHideLoad(true);
        });
    }
    // $scope.load_data();
    $scope.load_mixed_data = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/transprtations/initFindMembers').then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else if( data.status == "success" )
            {
                $scope.filteredVehicles = data.fullList;
                $scope.selctedVehicles = "";
                $scope.filteredStoppages = [];
                $scope.selctedStoppages = "";
                // $scope.filteredUsers = data.members;
                $scope.changeView('filteredMembers');
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.load_mixed_data();

    $scope.search_users = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/transports/members','POST',{},$scope.form).then(function(data) {
            $scope.transport_members = data
            showHideLoad(true);
        });
    }

    $scope.loadStoppages = function(){
        let vehicle_id = $scope.selctedVehicles;
        let stoppageList = $scope.filteredVehicles[vehicle_id].stoppages;
        $scope.filteredStoppages = stoppageList;
        $scope.selctedStoppages = "";
    }

    $scope.filterMembers = function(){
        $scope.loadingIcon = true;
        let send_data = { vehicle_id: $scope.selctedVehicles, stoppage_id: $scope.selctedStoppages };
        dataFactory.httpRequest('index.php/transprtations/filterMembers', 'POST', {}, send_data).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else if( data.status == "success" ) { $scope.filteredUsers = data.members; } 
            else { apiResponse(data, 'remove'); }
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.listSubs = false;
        $scope.views.filteredMembers = false;
        $scope.views[view] = true;
    }
});