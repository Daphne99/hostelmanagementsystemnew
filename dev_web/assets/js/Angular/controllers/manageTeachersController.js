var CuteBrains = angular.module('manageTeachersController', []);

CuteBrains.controller('manageTeachersController', function( dataFactory, $rootScope, $scope, $sce, $http, $timeout ) {
    $scope.views = {};
    $scope.form = {};
    $scope.loadingIcon = false;
    $scope.current_page = 1;
    $scope.classes = [];
    $scope.sections = [];
    $scope.subjects = [];
    $scope.teachers = [];
    $scope.teachersRows = [];

    $scope.changeView = function(view){
        if( view == "list" )
        {
            $scope.form.class = ""; $scope.form.section = ""; $scope.form.subject = ""; $scope.form.teacher = "";
        }
        $scope.views.list = false;
        $scope.views[view] = true;
    }

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Teachers';
    });

    $scope.preLoad = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/manage-teachers/preLoad').then(function(data) {
            $scope.classes = data.classes;
            $scope.subjects = data.subjects;
            $scope.teachers = data.teachers;
            $scope.changeView('list');
            setTimeout(function(){
		    	$('select[multiple]').selectpicker('destroy');
		    	$('select[multiple]').selectpicker();
		    }, 300);
            showHideLoad(true);
        });
    }

    $scope.preLoad();

    $scope.changeClass = function(){
        var classId = $scope.form.class;
        $scope.sections = [];
        $scope.sections = $scope.classes[classId].sections;
        setTimeout(function(){
            $('select[multiple]').selectpicker('destroy');
            $('select[multiple]').selectpicker();
        }, 300);
    }

    $scope.doFilter = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest("index.php/manage-teachers/filter", "POST", {}, $scope.form).then(function(data) {
            $scope.loadingIcon = false;
            $scope.teachersRows = data.rows;
        });
    }
});