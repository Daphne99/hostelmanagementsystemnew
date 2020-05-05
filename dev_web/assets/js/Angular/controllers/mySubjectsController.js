var CuteBrains = angular.module('mySubjectsController', []);

CuteBrains.controller('mySubjectsController', function(dataFactory,$rootScope,$scope) {
    $scope.subjects = {};
    $scope.teachers = {};
    $scope.class_schedule = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole = $rootScope.dashboardData.role;

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | My Subjects';
    });

    dataFactory.httpRequest('index.php/subjects/listAll').then(function(data) {
        $scope.subjects = data.subjects;
        $scope.teachers = data.teachers;
        $scope.classes = data.classes;
        $scope.class_schedule = data.class_schedule;
        showHideLoad(true);
    });

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show") { $scope.form = {}; }
        $scope.views.list = false;
        $scope.views[view] = true;
    }
});