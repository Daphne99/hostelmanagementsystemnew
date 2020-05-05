var CuteBrains = angular.module('composeEventController', []);

CuteBrains.controller('composeEventController', function(dataFactory,$rootScope,$route,$scope,$location,$routeParams) {
    $scope.views = {};
    $scope.form = {};
    $scope.userRole = $rootScope.dashboardData.role;

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Create new event';
    });

    $scope.changeView = function(view){
        if(view == "create") { $scope.form = {}; }
        $scope.views.create = false;
        $scope.views[view] = true;
    }

    $scope.changeView('create');
});