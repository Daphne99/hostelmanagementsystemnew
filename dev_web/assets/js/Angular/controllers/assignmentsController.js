var CuteBrains = angular.module('assignmentsController', []);

CuteBrains.controller('assignmentsController', function(dataFactory, $rootScope, $scope, Upload, $timeout) {
    $scope.classes = {};
    $scope.subject = {};
    $scope.section = {};
    $scope.assignments = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;
    $scope.progress = "NONE";
    $scope.isUploading = false;
    $scope.isUploaded = false;
    $scope.viewModal = false;

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Assignments';
    });

    dataFactory.httpRequest('index.php/assignments/listAll').then(function(data) {
        $scope.classes = data.classes;
        $scope.subject = data.subject;
        $scope.assignments = data.assignments;
        if(typeof data.assignmentsAnswers != "undefined"){
            $scope.assignmentsAnswers = data.assignmentsAnswers;
        }
        $scope.userRole = data.userRole
        showHideLoad(true);
    });

    $scope.listAnswers = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/assignments/listAnswers/'+id).then(function(data) {
            $scope.answers = data;
            $scope.changeView('answers');
            showHideLoad(true);
        });
    }

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.subject = data.subjects;
            $scope.sections = data.sections;
            $scope.form.subject = data.subjects;
            $scope.form.sections = data.sections;
        });
    }

    $scope.upload = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/assignments/checkUpload','POST',{},{'assignmentId':id}).then(function(data) {
            response = apiResponse(data,'add');
            if(data.canApply && data.canApply == "true"){
                $scope.form.assignmentId = id;
                $scope.changeView('upload');
            }
        });
        showHideLoad(true);
    }

    $scope.isSectionSelected = function(arrayData,valueData){
        return arrayData.indexOf(valueData) > -1;
    }

    $scope.saveAnswer = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            $scope.changeView('list');
            showHideLoad(true);
        }
    }

    $scope.numberSelected = function(item){
        var count = $(item + " :selected").length;
        if(count == 0){
            return true;
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/assignments/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/assignments/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.assignments.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.doAddAssignment = function(){
        var send_data = {
            AssignTitle: $scope.form.AssignTitle,
            AssignDescription: $scope.form.AssignDescription,
            AssignDeadLine: $scope.form.AssignDeadLine,
            AssignFile: document.getElementById('AssignAddFile').files[0],
            classId: $scope.form.classId,
            sectionId: $scope.form.sectionId,
            subjectId: $scope.form.subjectId
        };
        $scope.isUploading = true;
        if( $.trim( $('#AssignAddFile').val() ) ) { if( $scope.viewModal == false ) { $scope.modalClass = "modal-md"; $scope.viewModal = true; } }
        Upload.upload({
            url: "index.php/assignments",
            data: send_data
        }).then(
            function(content){
                $scope.isUploading = false;
                var xhrResponse = content.data;
                setTimeout(function(){
                    if(xhrResponse.status == "success")
                    {
                        $scope.viewModal = false;
                        response = apiResponse(xhrResponse, 'add');
                        $scope.progress = "NONE";
                        $scope.isUploaded = false;
                        $scope.changeView('list');
                        $('#AssignAddFile').val('');
                        $scope.$apply();
                    } else { response = apiResponse(xhrResponse, 'failed'); }
                }, 3000);
            },
            function(response){
                $scope.isUploading = false;
                var content = {status: "failed", title: "Add assignment", message: "Unable to prossess your request please try again"};
                response = apiResponse(content, 'failed');
            },
            function(evt){
                var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                $scope.progress = progressPercentage + '% ';
                if( progressPercentage >= 100 )
                {
                    setTimeout(function() {
                        $scope.progress = "NONE";
                        $scope.isUploaded = true;
                        $scope.$apply();
                        setTimeout(function() {
                            $scope.isUploaded = false;
                            $scope.viewModal = false;
                            $scope.$apply();
                        }, 1800);
                    }, 800);
                }
            }
        );
    }

    $scope.doEditAssignment = function(){
        var send_data = {
            AssignTitle: $scope.form.AssignTitle,
            AssignDescription: $scope.form.AssignDescription,
            AssignDeadLine: $scope.form.AssignDeadLine,
            AssignFile: document.getElementById('AssignEditFile').files[0],
            classId: $scope.form.classId,
            sectionId: $scope.form.sectionId,
            subjectId: $scope.form.subjectId
        };
        $scope.isUploading = true;
        if( $.trim( $('#AssignEditFile').val() ) ) { if( $scope.viewModal == false ) { $scope.modalClass = "modal-md"; $scope.viewModal = true; } }
        Upload.upload({
            url: "index.php/assignments/" + $scope.form.id,
            data: send_data
        }).then(
            function(content){
                $scope.isUploading = false;
                var xhrResponse = content.data;
                setTimeout(function(){
                    if(xhrResponse.status == "success")
                    {
                        $scope.viewModal = false;
                        response = apiResponse(xhrResponse, 'add');
                        $scope.progress = "NONE";
                        $scope.isUploaded = false;
                        $scope.changeView('list');
                        $('#AssignEditFile').val('');
                        $scope.$apply();
                    } else { response = apiResponse(xhrResponse, 'failed'); }
                }, 3000);
            },
            function(response){
                $scope.isUploading = false;
                var content = {status: "failed", title: "Add assignment", message: "Unable to prossess your request please try again"};
                response = apiResponse(content, 'failed');
            },
            function(evt){
                var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                $scope.progress = progressPercentage + '% ';
                if( progressPercentage >= 100 )
                {
                    setTimeout(function() {
                        $scope.progress = "NONE";
                        $scope.isUploaded = true;
                        $scope.$apply();
                        setTimeout(function() {
                            $scope.isUploaded = false;
                            $scope.viewModal = false;
                            $scope.$apply();
                        }, 1800);
                    }, 800);
                }
            }
        );
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show") {
            $scope.form = {};
            $scope.isUploaded = false;
            $scope.isUploading = false;
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.upload = false;
        $scope.views.answers = false;
        $scope.views[view] = true;
    }
});