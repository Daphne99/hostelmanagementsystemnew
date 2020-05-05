var CuteBrains = angular.module('manageStudentsController', []);

CuteBrains.controller('manageStudentsController', function( dataFactory, $rootScope, $scope, $sce, $http, Upload, $timeout ) {
    $scope.views = {};
    $scope.views.list = false;
    $scope.siblings = {};
    $scope.selectedSibling = {};
    $scope.siblingsCount = "NONE";
    $scope.sameAs = false;
    $scope.form = {};
    $scope.mySiblings = {};
    $scope.searchSiblingString = "";
    $scope.searchSiblingStatus = false;
    $scope.editProcessingStatus = false;
    $scope.form.class = "";
    $scope.form.section = "";
    $scope.form.type = "";
    $scope.form.category = "";
    $scope.filteredForm = {};
    $scope.tabs = {};
    $scope.types = {};
    $scope.classes = {};
    $scope.sections = {};
    $scope.categories = {};
    $scope.students = [];
    $scope.totalItems = 0;
    $scope.studentsPage = 1;
    $scope.filterStudentsPage = 1;
    $scope.loadingIcon = false;
    $scope.speicalLoadingIcon = false;
    $scope.sendingReply = false;
    $scope.studentInfo = {};
    $scope.isFiltered = false;
    $scope.server_info = JSON.parse($rootScope.dashboardData.server_info);
    $scope.jury = [];
    $scope.complained_by = [];
    $scope.complained_against = [];
    $scope.responsible_officials = [];
    $scope.complainItemKey = 0;
    $scope.complainId = 0;
    $scope.comments = [];
    $scope.commentReply = "";
    $scope.documents = {};
    $scope.documents.list = true;
    $scope.uploadModal = false;
    $scope.isUploading = false;
    $scope.isUploaded = false;
    $scope.docForm = {};
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.formTabs = {};
    $scope.editForm = {};
    $scope.bloodTypes = [{id:"O+",name:"O+"},{id:"A+",name:"A+"},{id:"B+",name:"B+"},{id:"AB+",name:"AB+"},{id:"O-",name:"O-"},{id:"A-",name:"A-"},{id:"B-",name:"B-"},{id:"AB-",name:"AB-"}];
    $scope.studentCategory = {};
    $scope.studentType = {};
    $scope.classes = {};
    $scope.allSections = {};
    $scope.sections = {};
    $scope.stoppages = {};
    $scope.allvehicles = {};
    $scope.vehicles = {};
    $scope.hostels = {};
    $scope.rooms = {};
    $scope.isPassShown = false;
    
    $scope.$on('$viewContentLoaded', function() {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Students';
    });

    $scope.preLoad = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/manage-students/preLoad').then(function(data) {
            $scope.types = data.types;
            $scope.classes = data.classes;
            $scope.categories = data.categories;
            showHideLoad(true);
        });
    }

    $scope.loadData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/qstudents/preload').then(function(data) {
            $scope.studentCategory = data.categories;
            $scope.studentType = data.types;
            // $scope.classes = data.classes;
            $scope.allSections = data.sections;
            $scope.hostels = data.hostels;
            $scope.allvehicles = data.vehicles;
            $scope.vehicles = data.vehicles;
            showHideLoad(true);
        });
    }

    $scope.listStudent = function( pageNumber ){
        $scope.studentsPage = pageNumber;
        showHideLoad();
        dataFactory.httpRequest("index.php/manage-students/list/" + $scope.studentsPage).then(function(data) {
            $scope.students = data.students;
            $scope.totalItems = data.totalItems;
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.loadData();
    $scope.preLoad();

    $scope.changeClass = function(){
        var currentClass = $scope.form.class;
        $scope.sections = $scope.classes[currentClass].sections;
    }

    $scope.changeView = function(view){
        if( view == "info" ) { $scope.changeTab('profile'); }
        if( view == "edit" ) { $scope.changeFormTab('details'); }
        $scope.views.list = false;
        $scope.views.info = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }

    $scope.changeTab = function(view){
        $scope.tabs.profile = false;
        $scope.tabs.parents = false;
        $scope.tabs.attendance = false;
        $scope.tabs.marksheet = false;
        $scope.tabs.invoice = false;
        $scope.tabs.document = false;
        $scope.tabs.transportaion = false;
        $scope.tabs.hostel = false;
        $scope.tabs.library = false;
        $scope.tabs.discipline = false;
        $scope.tabs.records = false;
        $scope.tabs[view] = true;
    }

    $scope.listStudent(1);

    $scope.pageChanged = function( pageNo ){
        if( $scope.isFiltered = false )
            $scope.listStudent(pageNo);
        else
            $scope.filterForm(pageNo);
    }

    $scope.filterForm = function( pageNumber ){
        $scope.loadingIcon = true;
        // var searchText = $scope.searchText == undefined || $scope.searchText == "" || $scope.searchText == "undefined" ? "" : $scope.searchText;
        $scope.filteredForm = $scope.form;
        // $scope.filteredForm.search = searchText;
        $scope.filterStudentsPage = pageNumber;
        dataFactory.httpRequest("index.php/manage-students/list/" + $scope.filterStudentsPage, 'GET', $scope.filteredForm).then(function(data) {
            $scope.students = data.students;
            $scope.totalItems = data.totalItems;
            if( data.totalItems > 0 ) { $scope.isFiltered = true; }
            $scope.changeView('list');
            $scope.loadingIcon = false;
        });
    }

    $scope.cancelFilter = function(){
        $scope.speicalLoadingIcon = true;
        $scope.searchText = "";
        $scope.form.class = "";
        $scope.form.section = "";
        $scope.form.type = "";
        $scope.form.category = "";
        dataFactory.httpRequest("index.php/manage-students/list/" + $scope.studentsPage).then(function(data) {
            $scope.students = data.students;
            $scope.totalItems = data.totalItems;
            $scope.changeView('list');
            $scope.speicalLoadingIcon = false;
            $scope.isFiltered = false;
        });
    }

    $scope.openStudent = function(studentId){
        showHideLoad();
        var send_data = { student: studentId };
        dataFactory.httpRequest("index.php/manage-students/viewStudent", 'GET', send_data).then(function(data) {
            $scope.studentInfo = data.studentInfo;
            $scope.changeTab('profile');
            $scope.changeView('info');
            $scope.prepareForm();
            showHideLoad(true);
        });
    }

    $scope.editStudent = function(item){
        var studentId = $scope.students[item].id;
        var send_data = { student: studentId };
        showHideLoad();
        dataFactory.httpRequest("index.php/manage-students/viewStudent", 'GET', send_data).then(function(data) {
            $scope.studentInfo = data.studentInfo;
            $scope.changeView('edit');
            $scope.changeFormTab('details');
            $scope.prepareForm();
            showHideLoad(true);
        });
    }

    $scope.prepareForm = function(){
        $scope.mySiblings = $scope.studentInfo.parents.sibling;

        $scope.editForm.studentId = $scope.studentInfo.main.id;
        $scope.editForm.admissionNo = $scope.studentInfo.main.adm;
        $scope.editForm.admissionDate = $scope.studentInfo.main.adm_date;
        $('#admissionDate').val( $scope.studentInfo.main.adm_date );
        $scope.editForm.firstName = $scope.studentInfo.profile.first;
        $scope.editForm.middleName = $scope.studentInfo.profile.middle;
        $scope.editForm.lastName = $scope.studentInfo.profile.last;
        $scope.editForm.dateOfBirth = $scope.studentInfo.main.dob;
        $('#dateOfBirth').val( $scope.studentInfo.main.dob );
        $scope.editForm.gender = $scope.studentInfo.main.gender;
        $scope.editForm.bloodType = $scope.studentInfo.medical.blood_group;
        $scope.editForm.birthPlace = $scope.studentInfo.main.birthPlace;
        $scope.editForm.nationality = $scope.studentInfo.main.nationality;
        $scope.editForm.religion = $scope.studentInfo.profile.religion;
        $scope.editForm.studentCategory = $scope.studentInfo.main.std_category;
        $scope.editForm.studentType = $scope.studentInfo.main.std_type;
        $scope.editForm.corresAddressLine = $scope.studentInfo.corres.line;
        $scope.editForm.corresAddressCity = $scope.studentInfo.corres.city;
        $scope.editForm.corresAddressState = $scope.studentInfo.corres.state;
        $scope.editForm.corresAddressPin = $scope.studentInfo.corres.pin;
        $scope.editForm.corresAddressCountry = $scope.studentInfo.corres.country;
        $scope.editForm.corresAddressPhone = $scope.studentInfo.corres.phone;
        $scope.editForm.corresAddressMobile = $scope.studentInfo.corres.Mobile;
        $scope.editForm.permaAddressLine = $scope.studentInfo.perma.line;
        $scope.editForm.permaAddressCity = $scope.studentInfo.perma.city;
        $scope.editForm.permaAddressState = $scope.studentInfo.perma.state;
        $scope.editForm.permaAddressPin = $scope.studentInfo.perma.pin;
        $scope.editForm.permaAddressCountry = $scope.studentInfo.perma.country;
        $scope.editForm.permaAddressPhone = $scope.studentInfo.perma.phone;
        $scope.editForm.permaAddressMobile = $scope.studentInfo.perma.Mobile;
        $scope.editForm.classId = $scope.studentInfo.main.classId;
        $scope.loadSections();
        $scope.editForm.sectionId = $scope.studentInfo.main.sectionId;
        $scope.editForm.rollNo = $scope.studentInfo.main.roll;
        $scope.editForm.bioId = $scope.studentInfo.main.bio;
        $scope.editForm.transport = $scope.studentInfo.assigns.transport;
        $scope.loadStoppages();
        $scope.editForm.stoppage = $scope.studentInfo.assigns.stoppage;
        $scope.editForm.hostel = $scope.studentInfo.assigns.hostel;
        $scope.loadRooms();
        $scope.editForm.room = $scope.studentInfo.assigns.room;
        $scope.editForm.mail = $scope.studentInfo.assigns.mail;
        $scope.editForm.sms = $scope.studentInfo.assigns.sms;
        $scope.editForm.phone = $scope.studentInfo.assigns.phone;

        $scope.editForm.inspol = $scope.studentInfo.medical.inspol;
        $scope.editForm.weight = $scope.studentInfo.medical.weight;
        $scope.editForm.height = $scope.studentInfo.medical.height;
        $scope.editForm.disab = $scope.studentInfo.medical.disab;
        $scope.editForm.contact = $scope.studentInfo.medical.contact;
        
        $scope.editForm.requestUnlink = "NO";
        
        $scope.editForm.fatherName = $scope.studentInfo.parents.father.name;
        $scope.editForm.fatherPhone = $scope.studentInfo.parents.father.phone;
        $scope.editForm.fatherJob = $scope.studentInfo.parents.father.job;
        $scope.editForm.fatherQualification = $scope.studentInfo.parents.father.qualification;
        $scope.editForm.fatherEmail = $scope.studentInfo.parents.father.email;
        $scope.editForm.fatherAddress = $scope.studentInfo.parents.father.address;

        $scope.editForm.motherName = $scope.studentInfo.parents.mother.name;
        $scope.editForm.motherPhone = $scope.studentInfo.parents.mother.phone;
        $scope.editForm.motherJob = $scope.studentInfo.parents.mother.job;
        $scope.editForm.motherQualification = $scope.studentInfo.parents.mother.qualification;
        $scope.editForm.motherEmail = $scope.studentInfo.parents.mother.email;
        $scope.editForm.motherAddress = $scope.studentInfo.parents.mother.address;

        $scope.editForm.guardianId = $scope.studentInfo.parents.gaurdian.id;
        $scope.editForm.guardianType = $scope.studentInfo.parents.gaurdian.relationId;
        $scope.editForm.guardianName = $scope.studentInfo.parents.gaurdian.name;
        $scope.editForm.guardianRelation = $scope.studentInfo.parents.gaurdian.relation;
        $scope.editForm.guardianPhone = $scope.studentInfo.parents.gaurdian.phone;
        $scope.editForm.gaurdianMail = $scope.studentInfo.parents.gaurdian.email;
        $scope.editForm.guardianJob = $scope.studentInfo.parents.gaurdian.job;
        $scope.editForm.guardianUsername = $scope.studentInfo.parents.gaurdian.username;
        $scope.editForm.guardianPassword = "dummyp@sswordText";
        $scope.editForm.guardianAddress = $scope.studentInfo.parents.gaurdian.address;

        // previous part
        $scope.editForm.previousInstitution = $scope.studentInfo.previous.institution;
        $scope.editForm.previousClass = $scope.studentInfo.previous.class;
        $scope.editForm.previousYear = $scope.studentInfo.previous.year;
        $scope.editForm.previousPercentage = $scope.studentInfo.previous.percentage;
    };

    $scope.deleteStudent = function(item){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            "Confirm Delete student",
            "Are you sure you want to remove this student ? Please note all the student data will be removed and this action can't be undone",
            function(){
                var studentId = $scope.students[item].id;
                var send_data = { studentId: studentId };
                showHideLoad();
                dataFactory.httpRequest("index.php/manage-students/deleteStudent", "POST", {}, send_data).then(function(data){
                    showHideLoad(true);
                    if( data.status == "success" )
                    {
                        apiResponse(data, "success");
                        $scope.students.splice(item,1);
                    } else { apiResponse(data, "error"); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.moveSession = function( timeStep ){
        var studentId = $scope.studentInfo.main.id;
        var send_data = {
            student: studentId,
            action: timeStep,
            current: $scope.studentInfo.attendances.current,
            next: $scope.studentInfo.attendances.next
        };
        showHideLoad();
        dataFactory.httpRequest("index.php/manage-students/viewStudent", 'GET', send_data).then(function(data) {
            $scope.studentInfo.attendances = data.attendances;
            showHideLoad(true);
        });
    }

    $scope.disciplineUsers = function(item){
        $scope.jury = $scope.studentInfo.disciplines[item].jury;
        $scope.complained_by = $scope.studentInfo.disciplines[item].complained_by;
        $scope.complained_against = $scope.studentInfo.disciplines[item].complained_against;
        $scope.responsible_officials = $scope.studentInfo.disciplines[item].responsible_officials;
        $scope.modalTitle = "Discipline parties";
        $scope.modalClass = "modal-lg";
        $scope.partiesModal = !$scope.partiesModal;
    }

    $scope.disciplineComments = function(item){
        $scope.complainItemKey = item;
        $scope.complainId = $scope.studentInfo.disciplines[item].id;
        $scope.comments =  $scope.studentInfo.disciplines[item].comments;
        $scope.modalTitle = "Discipline comments";
        $scope.modalClass = "modal-lg";
        $scope.commentsModal = !$scope.commentsModal;
    }

    $scope.disciplineSolved = function(item){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm status change',
            'Are you sure you want to set this discipline as solved ?',
            function(){
                showHideLoad();
                $scope.complainItemKey = item;
                $scope.complainId = $scope.studentInfo.disciplines[item].id;
                var send_data = { complainId: $scope.complainId, action: 'solved' };
                dataFactory.httpRequest("index.php/manage-students/change-status", "POST", {}, send_data).then(function(data) {
                    showHideLoad(true);
                    if( data.status == "success" )
                    {
                        apiResponse(data, "success");
                        $scope.studentInfo.disciplines[item].status_id = data.complain_status;
                    } else { apiResponse(data, "error"); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.disciplinePending = function(item){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm status change',
            'Are you sure you want to set this discipline as pending ?',
            function(){
                showHideLoad();
                $scope.complainItemKey = item;
                $scope.complainId = $scope.studentInfo.disciplines[item].id;
                var send_data = { complainId: $scope.complainId, action: 'pending' };
                dataFactory.httpRequest("index.php/manage-students/change-status", "POST", {}, send_data).then(function(data) {
                    showHideLoad(true);
                    if( data.status == "success" )
                    {
                        apiResponse(data, "success");
                        $scope.studentInfo.disciplines[item].status_id = data.complain_status;
                    } else { apiResponse(data, "error"); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.disciplineDelete = function(item){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            "Confirm deletation",
            "Are you sure you want to remove this discipline ? this action can't be undone",
            function(){
                showHideLoad();
                $scope.complainItemKey = item;
                $scope.complainId = $scope.studentInfo.disciplines[item].id;
                var send_data = { complainId: $scope.complainId };
                dataFactory.httpRequest("index.php/manage-students/delete-discipline", "POST", {}, send_data).then(function(data) {
                    showHideLoad(true);
                    if( data.status == "success" )
                    {
                        apiResponse(data, "success");
                        $scope.studentInfo.disciplines.splice(item,1);
                    } else { apiResponse(data, "error"); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.typeComment = function(){
        $scope.commentReply = $.trim( $('#commentReply').val() );
    }

    $scope.replyComment = function(){
        if( $scope.commentReply.trim() && $scope.complainId != 0 )
        {
            $scope.sendingReply = true;
            var item = $scope.complainItemKey;
            var send_data = { complainId: $scope.complainId, comment: $scope.commentReply.trim() };
            dataFactory.httpRequest("index.php/manage-students/post-comment", "POST", {}, send_data).then(function(data) {
                $scope.sendingReply = false;
                if( data.status == "success" )
                {
                    apiResponse(data, "success");
                    $scope.commentReply = "";
                    $('#commentReply').val("");
                    $scope.comments = data.comments;
                    $scope.studentInfo.disciplines[item].comments = data.comments;
                } else { apiResponse(data, "error"); }
            });
        } else return false;
    }

    $scope.removeDoc = function(item){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            "Confirm deletation",
            "Are you sure you want to remove this discipline ? this action can't be undone",
            function(){
                var documentId = $scope.studentInfo.documents[item].id;
                var send_data = { documentId: documentId };
                showHideLoad();
                dataFactory.httpRequest("index.php/manage-students/delete-documents", "POST", {}, send_data).then(function(data){
                    showHideLoad(true);
                    if( data.status == "success" )
                    {
                        apiResponse(data, "success");
                        $scope.studentInfo.documents.splice(item,1);
                    } else { apiResponse(data, "error"); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.changeAction = function(view){
        if( view == "add" || view == "list" )
        {
            $scope.docForm = {};
            $scope.docForm.title = "";
            $scope.docForm.notes = "";
            $scope.docForm.file = "";
        }
        $scope.documents.add = false;
        $scope.documents.list = false;
        $scope.documents.edit = false;
        $scope.documents[view] = true;

        if( view == "add" ) { $("#docFile").val(""); }
        if( view == "edit" ) { $("#editDocFile").val(""); }
    }

    $scope.changeFormTab = function(view){
        $scope.formTabs.details = false;
        $scope.formTabs.siblings = false;
        $scope.formTabs.previous = false;
        $scope.formTabs[view] = true;
    }

    $scope.uploadDocument = function(){
        var send_data = {
            title: $scope.docForm.title,
            notes: $scope.docForm.notes,
            file: document.getElementById("docFile").files[0],
            studentId: $scope.studentInfo.main.id
        };
        if( !$.trim( $('#docFile').val() ) )
        {
            var data = { status: "failed", title: "Add Document", message: "No File found please select one"};
            apiResponse(data, "missing");
            return false;
        }
        $scope.isUploading = true;
        Upload.upload({
            url: "index.php/manage-students/documentsUpload",
            data: send_data
        }).then(
            function(content){
                $scope.isUploading = false;
                var xhrResponse = content.data;
                setTimeout(function(){
                    if(xhrResponse.status == "success")
                    {
                        $scope.uploadModal = false;
                        response = apiResponse(xhrResponse, 'add');
                        $scope.progress = "NONE";
                        $scope.isUploaded = false;
                        $scope.studentInfo.documents = xhrResponse.documents;
                        $scope.changeAction('list');
                        $('#docFile').val('');
                        $scope.$apply();
                    } else { response = apiResponse(xhrResponse, 'failed'); }
                }, 3000);
            },
            function(response){
                $scope.isUploading = false;
                var content = {status: "failed", title: "Add Document", message: "Unable to prossess your request please try again"};
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
                            $scope.uploadModal = false;
                            $scope.$apply();
                        }, 1800);
                    }, 800);
                }
            }
        );
    }

    $scope.editDoc = function(item){
        $scope.docForm = {};
        $scope.docForm.id = $scope.studentInfo.documents[item].id;
        $scope.docForm.title = $scope.studentInfo.documents[item].file_title;
        $scope.docForm.notes = $scope.studentInfo.documents[item].file_notes;
        $scope.docForm.user_id = $scope.studentInfo.main.id;
        $scope.changeAction('edit');
    }

    $scope.updateDocument = function(){
        if( $.trim( $('#editDocFile').val() ) )
        {
            var send_data = {
                documentId: $scope.docForm.id,
                title: $scope.docForm.title,
                notes: $scope.docForm.notes,
                file: document.getElementById("editDocFile").files[0],
                studentId: $scope.docForm.user_id
            }
            $scope.isUploading = true;
            Upload.upload({
                url: "index.php/manage-students/documentEdit",
                data: send_data
            }).then(
                function(content){
                    $scope.isUploading = false;
                    var xhrResponse = content.data;
                    setTimeout(function(){
                        if(xhrResponse.status == "success")
                        {
                            $scope.uploadModal = false;
                            response = apiResponse(xhrResponse, 'add');
                            $scope.progress = "NONE";
                            $scope.isUploaded = false;
                            $scope.studentInfo.documents = xhrResponse.documents;
                            $scope.changeAction('list');
                            $('#editDocFile').val('');
                            $scope.$apply();
                        } else { response = apiResponse(xhrResponse, 'failed'); }
                    }, 3000);
                },
                function(response){
                    $scope.isUploading = false;
                    var content = {status: "failed", title: "Edit Document", message: "Unable to prossess your request please try again"};
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
                                $scope.uploadModal = false;
                                $scope.$apply();
                            }, 1800);
                        }, 800);
                    }
                }
            );
        }
        else
        {
            var send_data = {
                documentId: $scope.docForm.id,
                title: $scope.docForm.title,
                notes: $scope.docForm.notes,
                studentId: $scope.docForm.user_id
            }
            $scope.isUploading = true;
            dataFactory.httpRequest("index.php/manage-students/documentEdit", "POST", {}, send_data).then(function(data) {
                $scope.isUploading = false;
                if( data.status == "success" )
                {
                    apiResponse(data, "success");
                    $scope.studentInfo.documents = data.documents;
                    $scope.changeAction('list');
                } else { apiResponse(data, "error"); }
            });
        }
    }

    $scope.checkSameAs = function()
    {
        if( $scope.sameAs == true )
        {
            $scope.editForm.permaAddressLine = $scope.editForm.corresAddressLine;
            $scope.editForm.permaAddressCity = $scope.editForm.corresAddressCity;
            $scope.editForm.permaAddressState = $scope.editForm.corresAddressState;
            $scope.editForm.permaAddressPin = $scope.editForm.corresAddressPin;
            $scope.editForm.permaAddressCountry = $scope.editForm.corresAddressCountry;
            $scope.editForm.permaAddressPhone = $scope.editForm.corresAddressPhone;
            $scope.editForm.permaAddressMobile = $scope.editForm.corresAddressMobile;
        }
        $scope.editForm.guardianAddress = $scope.editForm.corresAddressLine;
        $scope.editForm.fatherAddress = $scope.editForm.corresAddressLine;
        $scope.editForm.motherAddress = $scope.editForm.corresAddressLine;
    }

    $scope.cloneAddresses = function(){
        if( $scope.sameAs == true )
        {
            $scope.editForm.permaAddressLine = $scope.editForm.corresAddressLine;
            $scope.editForm.permaAddressCity = $scope.editForm.corresAddressCity;
            $scope.editForm.permaAddressState = $scope.editForm.corresAddressState;
            $scope.editForm.permaAddressPin = $scope.editForm.corresAddressPin;
            $scope.editForm.permaAddressCountry = $scope.editForm.corresAddressCountry;
            $scope.editForm.permaAddressPhone = $scope.editForm.corresAddressPhone;
            $scope.editForm.permaAddressMobile = $scope.editForm.corresAddressMobile;
        }
    }

    $scope.loadVehicles = function(){
        stoppage_id = $scope.student.details.vehicles.stoppage;
        let vehicles = $scope.stoppages[stoppage_id] !== undefined ? $scope.stoppages[stoppage_id]['vehicles'] : {};
        let finalList = [];
        
        angular.forEach(vehicles, function(vehicle_id, key) {
            let vehicleArray = $scope.allvehicles[vehicle_id] !== undefined ? $scope.allvehicles[vehicle_id] : [];
            if( Object.keys(vehicleArray).length != 0 ) finalList.push( vehicleArray );
        });
        $scope.vehicles = finalList;
    }

    $scope.loadStoppages = function(){
        vehicle_id = $scope.editForm.transport;
        let stoppages = $scope.vehicles[vehicle_id] !== undefined ? $scope.vehicles[vehicle_id]['stoppages'] : {};
        let finalList = [];
        
        angular.forEach(stoppages, function(vehicle, key) {
            finalList.push( { id: vehicle.stoppage_id, name: vehicle.name } );
        });
        $scope.stoppages = finalList;
    }

    $scope.loadRooms = function(){
        let hostel_id = $scope.editForm.hostel;
        if( roomList = $scope.hostels[hostel_id] !== undefined )
        {
            let roomList = $scope.hostels[hostel_id].rooms;
            $scope.rooms = roomList;
        } else { $scope.rooms = {}; }
    }

    $scope.loadSections = function(){
        class_id = $scope.editForm.classId;
        let sections = $scope.allSections[class_id] !== undefined ? $scope.allSections[class_id] : {};
        $scope.sections = sections;
    }

    $scope.togglePassVisibilty = function(){
        if( $scope.editForm.guardianPassword != "dummyp@sswordText" ) { $scope.isPassShown = !$scope.isPassShown; }
    }

    $scope.changeGuardianType = function(GuardianType){
        if( GuardianType == 'father' )
        {
            $scope.editForm.guardianName = $scope.editForm.fatherName;
            $scope.editForm.guardianRelation = "Father";
            $scope.editForm.guardianPhone = $scope.editForm.fatherPhone;
            $scope.editForm.guardianUsername = $scope.editForm.fatherPhone;
            $scope.editForm.gaurdianMail = $scope.editForm.fatherEmail;
            $scope.editForm.guardianJob = $scope.editForm.fatherJob;
            $scope.editForm.guardianAddress = $scope.editForm.fatherAddress;

            $scope.updatePasswordByUsername();
        }
        else if( GuardianType == 'mother' )
        {
            $scope.editForm.guardianName = $scope.editForm.motherName;
            $scope.editForm.guardianRelation = "Mother";
            $scope.editForm.guardianPhone = $scope.editForm.motherPhone;
            $scope.editForm.guardianUsername = $scope.editForm.motherPhone;
            $scope.editForm.gaurdianMail = $scope.editForm.motherEmail;
            $scope.editForm.guardianJob = $scope.editForm.motherJob;
            $scope.editForm.guardianAddress = $scope.editForm.motherAddress;

            $scope.updatePasswordByUsername();
        }
        else if( GuardianType == 'others' )
        {
            $scope.editForm.guardianName = "";
            $scope.editForm.guardianRelation = "";
            $scope.editForm.guardianPhone = "";
            $scope.editForm.gaurdianMail = "";
            scope.editForm.guardianJob = "";
        }
    }

    $scope.updateUsernameByMobile = function(){
        $scope.editForm.guardianUsername = $scope.editForm.guardianPhone;
        var username = $scope.editForm.guardianUsername;
        if( username.length )
        {
            if( username.length >= 4 )
            {
                var lastFour = username.substr(username.length - 4);
                $scope.editForm.guardianPassword = lastFour;
            }
            else
            {
                //
            }
        }
    }

    $scope.updatePasswordByUsername = function(){
        var username = $scope.editForm.guardianUsername;
        if( username.length )
        {
            if( username.length >= 4 )
            {
                var lastFour = username.substr(username.length - 4);
                $scope.editForm.guardianPassword = lastFour;
            }
            else
            {
                //
            }
        }
    }

    $scope.unlinkSibling = function()
    {
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            "<i class='fa fa-unlink'></i> Unlink Siblings",
            "By unlinking from the current siblings all father, mother and Guardian info will be erased from this student <br /> <b>Please note</b> that is action can't be undone <br /><br /> Proceed with Siblings unlinking ?",
            function(){
                $scope.mySiblings = {};
                $scope.editForm.requestUnlink = "YES";
        
                $scope.editForm.fatherName = "";
                $scope.editForm.fatherPhone = "";
                $scope.editForm.fatherJob = "";
                $scope.editForm.fatherQualification = "";
                $scope.editForm.fatherEmail = "";
                $scope.editForm.fatherAddress = "";

                $scope.editForm.motherName = "";
                $scope.editForm.motherPhone = "";
                $scope.editForm.motherJob = "";
                $scope.editForm.motherQualification = "";
                $scope.editForm.motherEmail = "";
                $scope.editForm.motherAddress = "";

                $scope.editForm.guardianId = 0;
                $scope.editForm.guardianType = "";
                $scope.editForm.guardianName = "";
                $scope.editForm.guardianRelation = "";
                $scope.editForm.guardianPhone = "";
                $scope.editForm.gaurdianMail = "";
                $scope.editForm.guardianJob = "";
                $scope.editForm.guardianUsername = "";
                $scope.editForm.guardianPassword = "";
                $scope.editForm.guardianAddress = "";
                $scope.$apply();
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }
    
    $scope.showModal = false;
    $scope.searchSibling = function(){
        $scope.modalTitle = "Search for Siblings";
        $scope.modalClass = "modal-lg";
        $scope.showModal = !$scope.showModal;
    }

    $scope.doSearch = function(){
        let searchText = $('#searchSiblingString').val();
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        if( searchText.length < 3 )
        {
            alertify.alert(
                "<i class='fa fa-exclamation-triangle text-warning'></i> Hold On",
                $rootScope.phrase.minCharLength3,
                function(){}
            ).set('labels', {ok: 'I see'});
            return;
        }
        else if( searchText == $scope.studentInfo.main.adm )
        {
            alertify.alert(
                "<i class='fa fa-exclamation-triangle text-warning'></i> Hold On",
                "Unable to choose same admission number of current student",
                function(){}
            ).set('labels', {ok: 'I see'});
        }
        else
        {
            $scope.searchSiblingStatus = true;
            dataFactory.httpRequest('index.php/qstudents/findStudent', 'POST', {}, {searchText: searchText, studentId: $scope.studentInfo.main.id}).then(function(data) {
                $scope.searchSiblingStatus = false;
                if( data.status )
                {
                    $scope.siblings = data.siblings;
                    $scope.siblingsCount = data.siblingsCount;
                } else { apiResponse( data ); }
            });
        }
    }

    $scope.setAsGuardian = function(parentId) {
        let currentSbling = $scope.siblings[parentId];
        $scope.selectedSibling = currentSbling;
        
        $scope.editForm.fatherName = currentSbling.father.name;
        $scope.editForm.fatherPhone = currentSbling.father.mobile;
        $scope.editForm.fatherJob = currentSbling.father.job;
        $scope.editForm.fatherQualification = currentSbling.father.qualification;
        $scope.editForm.fatherEmail = currentSbling.father.email;
        $scope.editForm.fatherAddress = currentSbling.father.address;

        $scope.editForm.motherName = currentSbling.mother.name;
        $scope.editForm.motherPhone = currentSbling.mother.mobile;
        $scope.editForm.motherJob = currentSbling.mother.job;
        $scope.editForm.motherQualification = currentSbling.mother.qualification;
        $scope.editForm.motherEmail = currentSbling.mother.email;
        $scope.editForm.motherAddress = currentSbling.mother.address;
        
        $scope.editForm.guardianId = parentId;
        $scope.editForm.guardianName = currentSbling.gaurdian.name;
        $scope.editForm.guardianRelation = currentSbling.gaurdian.relation;
        $scope.editForm.guardianPhone = currentSbling.gaurdian.mobile;
        $scope.editForm.guardianAddress = currentSbling.gaurdian.address;
        $scope.editForm.gaurdianMail = currentSbling.gaurdian.email;
        $scope.editForm.guardianUsername = currentSbling.gaurdian.username;
        $scope.editForm.guardianJob = currentSbling.gaurdian.job;
        $scope.editForm.guardianPassword = "dummyp@sswordText";

        if( currentSbling.relation == 'Father' || currentSbling.relation == 'father' ) $scope.editForm.guardianType = 'father';
        else if( currentSbling.relation == 'Mother' || currentSbling.relation == 'mother' ) $scope.editForm.guardianType = 'mother';
        else $scope.editForm.guardianType = 'others';

        $scope.showModal = !$scope.showModal;
    }

    $scope.removeGuardian = function(parentId) {
        $scope.editForm.guardianId = 0;
    }

    $scope.doEditStudent = function(){
        var configs = {
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) { formData.append(key, value); });
                return formData;
            }
        };
        var model = $scope.editForm;
        model['userPhoto'] = document.getElementById('userPhoto').files[0];
        model['fatherPhoto'] = document.getElementById('fatherPhoto').files[0];
        model['motherPhoto'] = document.getElementById('motherPhoto').files[0];
        
        $scope.editProcessingStatus = true;
        $http.post('index.php/qstudents/updateStudent', model, configs).then(function(data) {
            $scope.editProcessingStatus = false;
            var xhrResponse = data.data;
            if( xhrResponse.status == 'success' )
            {
                response = apiResponse(xhrResponse, 'edit');
                $('#userPhoto').val('');
                $('#fatherPhoto').val('');
                $('#motherPhoto').val('');
                $scope.studentInfo.main = xhrResponse.studentInfo.main;
                $scope.studentInfo.profile = xhrResponse.studentInfo.profile;
                $scope.studentInfo.parents = xhrResponse.studentInfo.parents;
                $scope.studentInfo.previous = xhrResponse.studentInfo.parents;
                $scope.studentInfo.medical = xhrResponse.studentInfo.parents;
                $scope.studentInfo.corres = xhrResponse.studentInfo.parents;
                $scope.studentInfo.perma = xhrResponse.studentInfo.parents;
                $scope.studentInfo.assigns = xhrResponse.studentInfo.parents;
                $scope.prepareForm();
                $scope.changeView('info');
            } else { response = apiResponse(xhrResponse, 'remove'); }

        },function( error ){
            $scope.editProcessingStatus = false;
            var errorMsg = { status: "failed", title: "Update Student", message: "Error occurred while processing your request" }
            response = apiResponse(errorMsg, 'remove');
        });
    }
});