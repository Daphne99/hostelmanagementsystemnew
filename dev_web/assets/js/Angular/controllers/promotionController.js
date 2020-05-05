var CuteBrains = angular.module('promotionController', []);

CuteBrains.controller('promotionController', function(dataFactory,$rootScope,$scope) {
    $scope.acYears = {};
    $scope.classes = {};
    $scope.acYearsGroup = [];
    $scope.classesGroup = [];
    $scope.sectionsGroup = [];
    $scope.students = {};
    $scope.views = {};
    // $scope.views.list = true;
    $scope.form = {};
    $scope.sections = {};
    $scope.classesArray = [];
    $scope.form.studentInfo = [];
    $scope.acYearsGroupedArray = [];
    $scope.classesGroupedArray = [];
    $scope.sectionsGroupedArray = [];

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Promotions';
    });

    $scope.initial = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/promotion/preLoad').then(function(data) {
            $scope.acYears = data.acYears;
            $scope.acYearsGroup['from'] = data.acYears;
            $scope.acYearsGroup['to'] = data.acYears;
            $scope.views.list = true;
            showHideLoad(true);
        });
    }
    $scope.initial();

    $scope.changeYears = function(type){
        if( type == 'from' ) { var acYear = $scope.form.acYear; }
        else if( type == 'to' ) { var acYear = $scope.form.targetAcYear; }
        $scope.classesGroup[type] = $scope.acYearsGroup[type][acYear]['classes'];
    }
    
    $scope.changeClasses = function(type){
        if( type == 'from' ) { var acYear = $scope.form.acYear; var classId = $scope.form.classId; }
        else if( type == 'to' ) { var acYear = $scope.form.targetAcYear; var classId = $scope.form.targetClassId; }
        $scope.sectionsGroup[type] = $scope.acYearsGroup[type][acYear]['classes'][classId]['sections'];
    }

    $scope.classesList = function(){
        var acYear = $scope.form.acYear;
        $scope.classes = $scope.acYears[acYear]['classes'];
        return;
        dataFactory.httpRequest('index.php/dashboard/classesList','POST',{},{"academicYear":$scope.form.acYear}).then(function(data) {
            $scope.classes = data.classes;
            $scope.subjects = data.subjects;
        });
    }

    $scope.sectionsList = function(){
        var acYear = $scope.form.acYear;
        var classId = $scope.form.classId;
        $scope.sections = $scope.acYears[acYear]['classes'][classId]['sections'];
    }

    $scope.classesPromoteList = function(key){
        var acYear = $scope.studentsList.students[key].nextAcYear;
        $scope.classesGroupedArray[key] = $scope.acYearsGroupedArray[key][acYear]['classes'];
        return;
        dataFactory.httpRequest('index.php/dashboard/classesList','POST',{},{"academicYear":$scope.studentsList.students[key].acYear}).then(function(data) {
            $scope.classesArray[key] = data;
            $scope.sections = data.sections;
        });
    }

    $scope.sectionsPromoteList = function(key){
        var acYear = $scope.studentsList.students[key].nextAcYear;
        var classId = $scope.studentsList.students[key].nextClass;
        $scope.sectionsGroupedArray[key] = $scope.acYearsGroupedArray[key][acYear]['classes'][classId]['sections'];
    }

    $scope.listStudents = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/promotion/listStudents','POST',{},$scope.form).then(function(data) {
            $scope.promoType = $scope.form.promoType;
            $scope.studentsList = data;
            $scope.sections = data.classes.sections;

            angular.forEach(data.students, function(value, key) {
                $scope.acYearsGroupedArray[key] = $scope.acYears;
                $scope.classesArray[key] = data.classes;
                if( $scope.promoType == 'promote' )
                {
                    $scope.classesPromoteList(key);
                    $scope.sectionsPromoteList(key);
                }
                $scope.studentsList.students[key].nextSection = value.nextSection;
            });

            $scope.changeView('studentPromote');
            showHideLoad(true);
        });
    }

    $scope.removePromoStudent = function(index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            $rootScope.phrase.sureRemove,
            function(){
                for(key in $scope.studentsList.students)
                {
                    if($scope.studentsList.students[key].id == index) { delete $scope.studentsList.students[key]; break; }
                }
            },
            function(){},
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.promoteNow = function(){
        showHideLoad();
        if($scope.promoType == 'graduate'){
            angular.forEach($scope.studentsList.students, function(value, key) {
                $scope.studentsList.students[key]['acYear'] = 0;
            });
        }
        dataFactory.httpRequest('index.php/promotion','POST',{},{'promote':$scope.studentsList.students,'promoType':$scope.promoType}).then(function(data) {
            if(data){
                $scope.studentsPromoted = data;
                $scope.changeView('studentsPromoted');
            }
            showHideLoad(true);
        });
    }

    $scope.linkStudent = function(){
        $scope.modalTitle = $rootScope.phrase.promoteStudents;
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.minCharLength3);
            return;
        }
        dataFactory.httpRequest('index.php/promotion/search/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(student){
        $scope.form.studentInfo.push({"student":student.name,"id": "" + student.id + "" });
    }

    $scope.removeStudent = function(index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            $rootScope.phrase.sureRemove,
            function(){ $scope.form.studentInfo.splice(index,1); },
            function(){},
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
            $scope.form.studentInfo = [];
        }
        $scope.views.list = false;
        $scope.views.studentsPromoted = false;
        $scope.views.studentPromote = false;
        $scope.views[view] = true;
    }
});