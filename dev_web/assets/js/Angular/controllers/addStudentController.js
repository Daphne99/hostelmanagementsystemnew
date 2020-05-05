var CuteBrains = angular.module('addStudentController', []);

CuteBrains.controller('addStudentController', function( dataFactory, $rootScope, $scope, $sce, $http ) {
    $scope.views = {};
    $scope.searchSiblingString = "";
    $scope.student = {
        details: {
            personal: {},
            address: {
                corres: { line: "", city: "", state: "", pin: "", country: "", phone: "", Mobile: "", sameAs: false },
                perma: { line: "", city: "", state: "", pin: "", country: "", phone: "", Mobile: "" }
            },
            academic: {},
            vehicles: { comms: { mail: true, sms: true, phone: true } },
            medical: {},
            docs: [ { title: "", file: "", notes: "" } ]
        },
        siblings: {
            is_defined: false,
            parents: { father: {}, mother: {} },
            guardian: { type: "others", name: "", relation: "", phone: "", address: "", username: "", password: "", mail: "", job: "" },
        },
        previous: {}
    };
    $scope.siblings = {};
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
    $scope.selectedSibling = {};
    $scope.isPassShown = false;
    $scope.isReadyPassShown = false;
    $scope.isGaurdPassShown = false;
    $scope.views.import = false;
    $scope.views.reviewImport = false;
    $scope.importReview;
    $scope.importSections;
    $scope.importClasses;
    
    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Create New Student';
    });

    $scope.proceedToFirstStep = function(){
        $scope.changeView('details');
    }

    $scope.loadData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/qstudents/preload').then(function(data) {
            $scope.studentCategory = data.categories;
            $scope.studentType = data.types;
            $scope.classes = data.classes;
            $scope.allSections = data.sections;
            $scope.hostels = data.hostels;
            // $scope.stoppages = data.stoppage;
            $scope.allvehicles = data.vehicles;
            $scope.vehicles = data.vehicles;
            $scope.proceedToFirstStep();
            showHideLoad(true);
        });
    }

    $scope.loadData();

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
        vehicle_id = $scope.student.details.vehicles.transport;
        let stoppages = $scope.vehicles[vehicle_id] !== undefined ? $scope.vehicles[vehicle_id]['stoppages'] : {};
        let finalList = [];
        
        angular.forEach(stoppages, function(vehicle, key) {
            finalList.push( { id: vehicle.stoppage_id, name: vehicle.name } );
        });
        $scope.stoppages = finalList;
    }

    $scope.loadRooms = function(){
        let hostel_id = $scope.student.details.vehicles.hostel;
        let roomList = $scope.hostels[hostel_id].rooms;
        $scope.rooms = roomList;
        $scope.student.details.vehicles.room = "";
    }

    $scope.loadSections = function(){
        class_id = $scope.student.details.academic.class;
        let sections = $scope.allSections[class_id] !== undefined ? $scope.allSections[class_id] : {};
        $scope.sections = sections;
    }

    $scope.addRowDoc = function(){
        let newDoc = { title: "", file: "", notes: "" };
        $scope.student.details.docs.push( newDoc );
    }

    $scope.removeDoc = function(index){
        if( Object.keys( $scope.student.details.docs ).length == 0 )
        {
            $scope.student.details.docs = [{ title: "", file: "", notes: "" }];
        }
        else
        {
            delete $scope.student.details.docs[index];
            var i = 0; var newDate = [];
            $.each($scope.student.details.docs, function (indexInArray, val) {
                if( val !== undefined )
                {
                    newDate[i] = val;
                    i++;
                }
            });
            $scope.student.details.docs = newDate;
        }
    }

    $scope.checkSameAs = function()
    {
        if( $scope.student.details.address.corres.sameAs == true )
        {
            $scope.student.details.address.perma.line = $scope.student.details.address.corres.line;
            $scope.student.details.address.perma.city = $scope.student.details.address.corres.city;
            $scope.student.details.address.perma.state = $scope.student.details.address.corres.state;
            $scope.student.details.address.perma.pin = $scope.student.details.address.corres.pin;
            $scope.student.details.address.perma.country = $scope.student.details.address.corres.country;
            $scope.student.details.address.perma.phone = $scope.student.details.address.corres.phone;
            $scope.student.details.address.perma.Mobile = $scope.student.details.address.corres.Mobile;
        }
        $scope.student.siblings.guardian.address = $scope.student.details.address.corres.line;
        $scope.student.siblings.parents.father.address = $scope.student.details.address.corres.line;
        $scope.student.siblings.parents.mother.address = $scope.student.details.address.corres.line;
    }

    $scope.cloneAddresses = function(){
        if( $scope.student.details.address.corres.sameAs == true )
        {
            $scope.student.details.address.perma.line = $scope.student.details.address.corres.line;
            $scope.student.details.address.perma.city = $scope.student.details.address.corres.city;
            $scope.student.details.address.perma.state = $scope.student.details.address.corres.state;
            $scope.student.details.address.perma.pin = $scope.student.details.address.corres.pin;
            $scope.student.details.address.perma.country = $scope.student.details.address.corres.country;
            $scope.student.details.address.perma.phone = $scope.student.details.address.corres.phone;
            $scope.student.details.address.perma.Mobile = $scope.student.details.address.corres.Mobile;
        }
    }

    $scope.showModal = false;
    $scope.searchSibling = function(){
        $scope.modalTitle = "Search for Siblings";
        $scope.modalClass = "modal-lg";
        $scope.showModal = !$scope.showModal;
    }

    $scope.doSearch = function(){
        let searchText = $('#searchSiblingString').val();
        if( searchText.length < 3 )
        {
            alertify.defaults.transition = 'zoom';
            alertify.defaults.theme.ok = 'btn btn-sm btn-info';
            alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
            alertify.defaults.theme.input = 'form-control';
            alertify.alert(
                "<i class='fa fa-exclamation-triangle text-warning'></i> Hold On",
                $rootScope.phrase.minCharLength3,
                function(){}
            ).set('labels', {ok: 'I see'});
            return;
        }
        else
        {
            dataFactory.httpRequest('index.php/qstudents/findStudent','POST', {} , {'searchText':searchText}).then(function(data) {
                if( data.status )
                {
                    $scope.siblings = data.siblings;
                } else { apiResponse( data ); }
            });
        }
    }

    $scope.togglePassVisibilty = function(){
        if( $scope.student.siblings.guardian.password != "dummyp@sswordText" )
        {
            $scope.isPassShown = !$scope.isPassShown;
        }
    }
    
    $scope.toggleGaurdPassVisibilty = function(){
        $scope.isGaurdPassShown = !$scope.isGaurdPassShown;
    }
    
    $scope.toggleReadyPassVisibilty = function(){
        $scope.isReadyPassShown = !$scope.isReadyPassShown;
    }

    $scope.setAsGuardian = function(parentId) {
        let currentSbling = $scope.siblings[parentId];
        $scope.selectedSibling = currentSbling;
        $scope.student.siblings.is_defined = parentId;
        $scope.student.siblings.parents.father.name = currentSbling.father.name;
        $scope.student.siblings.parents.father.phone = currentSbling.father.mobile;
        $scope.student.siblings.parents.father.job = currentSbling.father.job;
        $scope.student.siblings.parents.father.qualification = currentSbling.father.qualification;
        $scope.student.siblings.parents.father.email = currentSbling.father.email;
        $scope.student.siblings.parents.father.address = currentSbling.father.address;

        $scope.student.siblings.parents.mother.name = currentSbling.mother.name;
        $scope.student.siblings.parents.mother.phone = currentSbling.mother.mobile;
        $scope.student.siblings.parents.mother.job = currentSbling.mother.job;
        $scope.student.siblings.parents.mother.qualification = currentSbling.mother.qualification;
        $scope.student.siblings.parents.mother.email = currentSbling.mother.email;
        $scope.student.siblings.parents.mother.address = currentSbling.mother.address;

        $scope.student.siblings.guardian.name = currentSbling.gaurdian.name;
        $scope.student.siblings.guardian.relation = currentSbling.gaurdian.relation;
        $scope.student.siblings.guardian.phone = currentSbling.gaurdian.mobile;
        $scope.student.siblings.guardian.address = currentSbling.gaurdian.address;
        $scope.student.siblings.guardian.mail = currentSbling.gaurdian.email;
        $scope.student.siblings.guardian.username = currentSbling.gaurdian.username;
        $scope.student.siblings.guardian.job = currentSbling.gaurdian.job;
        $scope.student.siblings.guardian.password = "dummyp@sswordText";

        if( currentSbling.relation == 'Father' || currentSbling.relation == 'father' ) $scope.student.siblings.guardian.type = 'father';
        else if( currentSbling.relation == 'Mother' || currentSbling.relation == 'mother' ) $scope.student.siblings.guardian.type = 'mother';
        else $scope.student.siblings.guardian.type = 'others';

        $scope.showModal = !$scope.showModal;
    }

    $scope.removeGuardian = function(parentId) {
        $scope.student.siblings.is_defined = false;
    }

    $scope.changeGuardianType = function(GuardianType){
        if( GuardianType == 'father' )
        {
            $scope.student.siblings.guardian.name = $scope.student.siblings.parents.father.name;
            $scope.student.siblings.guardian.relation = 'Father';
            $scope.student.siblings.guardian.phone = $scope.student.siblings.parents.father.phone;
            $scope.student.siblings.guardian.username = $scope.student.siblings.parents.father.phone;
            $scope.student.siblings.guardian.mail = $scope.student.siblings.parents.father.email;
            $scope.student.siblings.guardian.job = $scope.student.siblings.parents.father.job;
            $scope.student.siblings.guardian.address = $scope.student.siblings.parents.father.address;
            var username = $scope.student.siblings.guardian.username;
            if( username.length )
            {
                if( username.length >= 4 )
                {
                    var lastFour = username.substr(username.length - 4);
                    $scope.student.siblings.guardian.password = lastFour;
                }
                else
                {
                    //
                }
            }
        }
        else if( GuardianType == 'mother' )
        {
            $scope.student.siblings.guardian.name = $scope.student.siblings.parents.mother.name;
            $scope.student.siblings.guardian.relation = 'Mother';
            $scope.student.siblings.guardian.phone = $scope.student.siblings.parents.mother.phone;
            $scope.student.siblings.guardian.username = $scope.student.siblings.parents.mother.phone;
            $scope.student.siblings.guardian.mail = $scope.student.siblings.parents.mother.email;
            $scope.student.siblings.guardian.job = $scope.student.siblings.parents.mother.job;
            $scope.student.siblings.guardian.address = $scope.student.siblings.parents.mother.address;
            var username = $scope.student.siblings.guardian.username;
            if( username.length )
            {
                if( username.length >= 4 )
                {
                    var lastFour = username.substr(username.length - 4);
                    $scope.student.siblings.guardian.password = lastFour;
                }
                else
                {
                    //
                }
            }
        }
        else if( GuardianType == 'others' )
        {
            $scope.student.siblings.guardian.name = '';
            $scope.student.siblings.guardian.relation = '';
            $scope.student.siblings.guardian.phone = '';
            $scope.student.siblings.guardian.mail = '';
            $scope.student.siblings.guardian.job = '';
        }
    }

    $scope.proceedToSecondStep = function (){
        $scope.changeView('siblings');
    }

    $scope.proceedToFinalStep = function (){
        $scope.changeView('previous');
    }

    $scope.doSave = function (){
        let personal = $scope.student.details.personal;
        let academic = $scope.student.details.academic;
        let corres = $scope.student.details.address.corres;
        let perma = $scope.student.details.address.perma;
        let vehicles = $scope.student.details.vehicles;
        let medical = $scope.student.details.medical;
        let docs = $scope.student.details.docs;
        let guardian = $scope.student.siblings.guardian;
        let father = $scope.student.siblings.parents.father;
        let mother = $scope.student.siblings.parents.mother;
        let previous = $scope.student.previous;

        var model = {
            admissionNo : personal.admissionNo ? personal.admissionNo : '',
            admissionDate : personal.admissionDate ? personal.admissionDate : '',
            firstName : personal.firstName ? personal.firstName : '',
            middleName : personal.middleName ? personal.middleName : '',
            lastName : personal.lastName ? personal.lastName : '',
            dateOfBirth : personal.dateOfBirth ? personal.dateOfBirth : '',
            gender : personal.gender ? personal.gender : '',
            bloodType : personal.bloodType ? personal.bloodType : '',
            birthPlace : personal.birthPlace ? personal.birthPlace : '',
            nationality : personal.nationality ? personal.nationality : '',
            religion : personal.religion ? personal.religion : '',
            studentCategory : personal.studentCategory ? personal.studentCategory : '',
            userPhoto : document.getElementById('userPhoto').files[0],
            studentType : personal.studentType ? personal.studentType : '',
            classId : academic.class ? academic.class : '',
            sectionId : academic.section ? academic.section : '',
            rollNo : academic.rollNo ? academic.rollNo : '',
            bioId : academic.bioId ? academic.bioId : '',
            corresAddressLine : corres.line ? corres.line : '',
            corresAddressCity : corres.city ? corres.city : '',
            corresAddressState : corres.state ? corres.state : '',
            corresAddressPin : corres.pin ? corres.pin : '',
            corresAddressCountry : corres.country ? corres.country : '',
            corresAddressPhone : corres.phone ? corres.phone : '',
            corresAddressMobile : corres.Mobile ? corres.Mobile : '',
            permaAddressLine : perma.line ? perma.line : '',
            permaAddressCity : perma.city ? perma.city : '',
            permaAddressState : perma.state ? perma.state : '',
            permaAddressPin : perma.pin ? perma.pin : '',
            permaAddressCountry : perma.country ? perma.country : '',
            permaAddressPhone : perma.phone ? perma.phone : '',
            permaAddressMobile : perma.Mobile ? perma.Mobile : '',
            stoppage: vehicles.stoppage ? vehicles.stoppage : '',
            transport: vehicles.transport ? vehicles.transport : '',
            hostel: vehicles.hostel ? vehicles.hostel : '',
            room: vehicles.room ? vehicles.room : '',
            mail: vehicles.comms.mail ? true : false,
            sms: vehicles.comms.sms ? true : false,
            phone: vehicles.comms.phone ? true : false,
            inspol: medical.inspol ? medical.inspol : '',
            weight: medical.weight ? medical.weight : '',
            height: medical.height ? medical.height : '',
            disab: medical.disab ? medical.disab : '',
            contact: medical.contact ? medical.contact : '',
            guardianId: $scope.student.siblings.is_defined ? $scope.student.siblings.is_defined : false,
            guardianType: guardian.type ? guardian.type : '',
            guardianName: guardian.name ? guardian.name : '',
            guardianRelation: guardian.relation ? guardian.relation : '',
            guardianPhone: guardian.phone ? guardian.phone : '',
            guardianAddress: guardian.address ? guardian.address : '',
            guardianUsername: guardian.username ? guardian.username : '',
            guardianPassword: guardian.password ? guardian.password : '',
            gaurdianMail: guardian.mail ? guardian.mail : '',
            gaurdianJob: guardian.job ? guardian.job : '',
            fatherName: father.name ? father.name : '',
            fatherPhone: father.phone ? father.phone : '',
            fatherJob: father.job ? father.job : '',
            fatherQualification: father.qualification ? father.qualification : '',
            fatherEmail: father.email ? father.email : '',
            fatherPhoto: document.getElementById('fatherPhoto').files[0],
            fatherAddress: father.address ? father.address : '',
            motherName: mother.name ? mother.name : '',
            motherPhone: mother.phone ? mother.phone : '',
            motherJob: mother.job ? mother.job : '',
            motherQualification: mother.qualification ? mother.qualification : '',
            motherEmail: mother.email ? mother.email : '',
            motherPhoto: document.getElementById('motherPhoto').files[0],
            motherAddress: mother.address ? mother.address : '',
            previousInstitution: previous.institution ? previous.institution : '',
            previousClass: previous.class ? previous.class : '',
            previousYear: previous.year ? previous.year : '',
            previousPercentage: previous.percentage ? previous.percentage : ''
        }
        let filesCount = 0;
        angular.forEach( docs, function (item, key) {
            let fileId = "userDoc_" + key;
            if( document.getElementById(fileId).files[0] )
            {
                model["filesCount_" + filesCount + "_file"] = document.getElementById(fileId).files[0];
                model["filesCount_" + filesCount + "_title"] = $scope.student.details.docs[key].title;
                model["filesCount_" + filesCount + "_notes"] = $scope.student.details.docs[key].notes;
                filesCount++;
            }
        });
        model['filesCount'] = filesCount;
        var configs = {
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) { formData.append(key, value); });
                return formData;
            }
        };
        $scope.editProcessingStatus = true;
        $http.post('index.php/qstudents/completeRegister', model, configs).then(function(data) {
            let xhrResponse = data.data;
            if( xhrResponse.status == 'success' )
            {
                response = apiResponse(xhrResponse, 'edit');
                $scope.editProcessingStatus = false;
                $scope.student = {
                    details: {
                        personal: {},
                        address: {
                            corres: { line: "", city: "", state: "", pin: "", country: "", phone: "", Mobile: "", sameAs: false },
                            perma: { line: "", city: "", state: "", pin: "", country: "", phone: "", Mobile: "" }
                        },
                        academic: {},
                        vehicles: { comms: { mail: true, sms: true, phone: true } },
                        medical: {},
                        docs: [ { title: "", file: "", notes: "" } ]
                    },
                    siblings: {
                        is_defined: false,
                        parents: { father: {}, mother: {} },
                        guardian: { type: "others", name: "", relation: "", phone: "", address: "", username: "", password: "", mail: "", job: "" },
                    },
                    previous: {}
                };
                $('#userPhoto').val('');
                $('#fatherPhoto').val('');
                $('#motherPhoto').val('');
                angular.forEach( docs, function (item, key) {
                    let fileId = "#userDoc_" + key;
                    $(fileId).val("");
                });
                $scope.student.details.docs = [ { title: "", file: "", notes: "" } ];
                $scope.changeView('details');
            }
            else
            {
                $scope.editProcessingStatus = false;
                response = apiResponse(xhrResponse, 'remove');
            }
        },function( error ){
            $scope.editProcessingStatus = false;
            let errorMsg = {
                status: "failed",
                title: "Add student",
                message: "Error occurred while processing your request"
            }
            response = apiResponse(errorMsg, 'remove');
        });
    }

    $scope.saveImported = function(content){
        content = uploadSuccessOrError(content);
        if(content)
        {
            $scope.importReview = content.dataImport;
            $scope.importSections = content.sections;
            $scope.importClasses = content.classes;
            showHideLoad();
            $scope.changeView('reviewImport');
        }
        showHideLoad(true);
    }

    $scope.reviewImportData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/reviewImport','POST',{},{'importReview':$scope.importReview}).then(function(data) {
            content = apiResponse(data);
            if( data.status == "failed" )
            {
                $scope.importReview = content;
                $scope.changeView('reviewImport');
            } else { $scope.changeView('details'); }
            showHideLoad(true);
        });
    }

    $scope.removeImport = function( item, index, importType ){
        if(importType == "revise") { $scope.importReview.revise.splice(index,1); }
        if(importType == "ready") { $scope.importReview.ready.splice(index,1); }
    }

    $scope.fixImport = function( item, index ){
        delete $scope.importReview.revise[index].error;
        let row = $scope.importReview.revise[index];
        $scope.importReview.ready.push( row );
        $scope.importReview.revise.splice(index,1);
    }

    $scope.reviewImportData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/qstudents/processImported','POST',{},{'importReview':$scope.importReview.ready}).then(function(data) {
            content = apiResponse(data);
            if(data.status == "failed")
            {
                //
            }
            else if (data.status == "success")
            {
                $('#excelcsv').val('');
                $scope.changeView('import');
            }
            showHideLoad(true);
        });
    }

    $scope.updateUsernameByMobile = function(){
        $scope.student.siblings.guardian.username = $scope.student.siblings.guardian.phone;
        var username = $scope.student.siblings.guardian.username;
        if( username.length )
        {
            if( username.length >= 4 )
            {
                var lastFour = username.substr(username.length - 4);
                $scope.student.siblings.guardian.password = lastFour;
            }
            else
            {
                //
            }
        }
    }

    $scope.updatePasswordByUsername = function(){
        var username = $scope.student.siblings.guardian.username;
        if( username.length )
        {
            if( username.length >= 4 )
            {
                var lastFour = username.substr(username.length - 4);
                $scope.student.siblings.guardian.password = lastFour;
            }
            else
            {
                //
            }
        }
    }

    $scope.changeView = function(view){
        $scope.views.details = false;
        $scope.views.siblings = false;
        $scope.views.previous = false;
        $scope.views.import = false;
        $scope.views.reviewImport = false;
        $scope.views[view] = true;
    }
});