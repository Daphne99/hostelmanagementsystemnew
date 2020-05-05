var CuteBrains = angular.module('classesSectionsAndTeachersController', []);

CuteBrains.controller('classesSectionsAndTeachersController', function( dataFactory, $rootScope, $scope, $timeout) {
    $scope.views = {};
    $scope.views.edit = false;
    $scope.form = {};
    $scope.teachers = [];
    $scope.teachersList = {};
    $scope.classes = [];
    $scope.isLoading = false;
    $scope.selectedItem = 0;

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | classes, Sections and Teachers';
    });
    showHideLoad();
    dataFactory.httpRequest('index.php/classes-sections-teachers/preLoad').then(function(data) {
        showHideLoad(true);
        if( data.status == "success" )
        {
            $scope.teachers = data.teachers;
            $scope.teachersList = data.teachersList;
            $scope.classes = data.classes;
            $scope.changeView('list');
        } else if( data.status == "failed" ) {
            response = apiResponse(data, 'remove');
            var base_url = window.location.origin;
    		window.location.href = base_url + "/" + 'index.php/portal#/';
        }
    });

    $scope.plusSection = function(){
        let length = $scope.form.sections.length;
        let oneSection = {name: '', typeahead: '', teachers: [], visibleTeachers: []};
        $scope.form.sections.push(oneSection);
        $timeout(function () {
            let teacherName = '.teacherName-' + length;
            $( teacherName ).typeahead({
                source: $scope.teachers,
                autoSelect: true,
                afterSelect: function(args)
                {
                    $scope.addTeacherToSection(args, length);
                }
            });
        }, 250);
    }

    $scope.addTeacherToSection = function(args, index)
    {
        var teacherName = '.teacherName-' + index;
        var teacherId = args.id;
        if( !$scope.in_array( teacherId, $scope.form.sections[index].teachers ) ) 
        {
            $scope.form.sections[index].teachers.push(teacherId);
            $scope.form.sections[index].visibleTeachers.push(args);
        }
        $( teacherName ).val("");
        $scope.$apply();
    }

    $scope.removeTeacher = function(section, teacher){
        let teachers = $scope.form.sections[section].teachers;
        let visibleTeachers = $scope.form.sections[section].visibleTeachers;
        $scope.form.sections[section].teachers = [];
        $scope.form.sections[section].visibleTeachers = [];
        angular.forEach( teachers, function (item, key) { if( key != teacher ) { $scope.form.sections[section].teachers.push(item); } });
        angular.forEach( visibleTeachers, function (item, key) { if( key != teacher ) { $scope.form.sections[section].visibleTeachers.push(item); } });
    }

    $scope.removeSection = function(section){
        let sectionIndex = $scope.form.sections;
        $scope.form.sections = [];
        angular.forEach( sectionIndex, function (item, key) { if( key != section ) { $scope.form.sections.push(item); } });
        if( $scope.views.edit == false ) { $scope.$apply(); }
    }

    $scope.createClass = function(){
        $scope.isLoading = true;
        dataFactory.httpRequest('index.php/classes-sections-teachers/createClass', 'POST', {}, $scope.form ).then(function(data) {
            $scope.isLoading = false;
            if( data.status == "success" )
            {
                response = apiResponse(data, 'edit');
                $scope.classes.push( data.data );
                $scope.changeView('list');
            } else { response = apiResponse(data, 'remove'); }
        });
    }

    $scope.editClass = function(classId, index){
        $scope.selectedItem = index;
        var classObj = $scope.classes[index];
        var sections = classObj.sections;
        $scope.form = {
            class_id: classId,
            classesName: classObj.name,
            sections: []
        };
        angular.forEach( sections, function (item, key) {
            let teachers = item.teachers;
            let teachersIds = [];
            var visibleTeachers = [];
            angular.forEach( teachers, function (teacherOne, index) {
                if( !$scope.in_array( teacherOne.id, teachersIds ) ) {
                    teachersIds.push(teacherOne.id);
                    visibleTeachers.push({id: teacherOne.id, name: teacherOne.name});
                }
            });
            let oneSection = {id:item.id, name: item.name, typeahead: '', teachers: teachersIds, visibleTeachers: visibleTeachers};
            $scope.form.sections.push(oneSection);
        });
        $timeout(function () {
            
            angular.forEach( $scope.form.sections, function (item, key) {
                let teacherName = '.teacherName-' + key;
                $( teacherName ).typeahead({
                    source: $scope.teachers,
                    autoSelect: true,
                    afterSelect: function(args)
                    {
                        $scope.addTeacherToSection(args, key);
                    }
                });
            });
        }, 250);
        $scope.changeView('edit');
    }

    $scope.removeClass = function(classId, index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this class ?',
            function(){
                showHideLoad();
                let send_data = { class_id: classId }
                dataFactory.httpRequest('index.php/classes-sections-teachers/removeClass', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.classes.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.saveClass = function(){
        $scope.isLoading = true;
        dataFactory.httpRequest('index.php/classes-sections-teachers/editClass', 'POST', {}, $scope.form ).then(function(data) {
            $scope.isLoading = false;
            if( data.status == "success" )
            {
                response = apiResponse(data, 'edit');
                $scope.classes[$scope.selectedItem] = data.data;
                $scope.changeView('list');
            } else { response = apiResponse(data, 'remove'); }
        });
    }

    $scope.changeView = function(view){
        if( view == "add" )
        {
            $scope.form = {};
            $scope.form.sections = [];
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }

    $scope.in_array = function( needle, haystack )
    {
        for( var key in haystack ) { if( needle === haystack[key] ) return true; }
        return false;
    }
});