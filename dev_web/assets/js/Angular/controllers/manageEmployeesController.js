var CuteBrains = angular.module('manageEmployeesController', []);

CuteBrains.controller('manageEmployeesController', function( dataFactory, $rootScope, $scope, $sce, $http ) {
    $scope.views = {};
    $scope.checkedTab = "";
    $scope.add_employee = {
        account: { role: "", username: "", password: "", academicRole: "" },
        information: { firstName: "", lastName: "", FingerprintNo: "", supervisor: "", department: "", designation: "", branch: "", shift: "", paygrade: "", salary: "", mail: "", phone: "", gender: "", religion: "", birthday: "", joinday: "", leaveday: "", marital: "", status: "", photo: "", address: "", emergency: "" },
        educational: [],
        professional: []
    };
    $scope.edit_employee = {
        account: { id: "", user_id: "", role: "", username: "", password: "", academicRole: "" },
        information: { firstName: "", lastName: "", FingerprintNo: "", supervisor: "", department: "", designation: "", branch: "", shift: "", paygrade: "", salary: "", mail: "", phone: "", gender: "", religion: "", birthday: "", joinday: "", leaveday: "", marital: "", status: "", photo: "", address: "", emergency: "" },
        educational: [],
        professional: []
    };
    $scope.loadingIcon = false;
    $scope.speicalLoadingIcon = false;
    $scope.depForm = {};
    $scope.departments = {};
    $scope.totalDepartments = 0;
    $scope.departmentsPageNumber = 1;
    $scope.desForm = {};
    $scope.designations = {};
    $scope.totalDesignations = 0;
    $scope.designationsPageNumber = 1;
    $scope.branchForm = {};
    $scope.branchs = {};
    $scope.totalBranchs = 0;
    $scope.branchsPageNumber = 1;
    $scope.employeeForm = {};
    $scope.employees = {};
    $scope.totalEmployees = 0;
    $scope.employeesPageNumber = 1;
    $scope.selectedDepartment = 0;
    $scope.filterByDepartments = {};
    $scope.selectedDesignation = 0;
    $scope.filterByDesignations = {};
    $scope.selectedRole = 0;
    $scope.filterByRoles = {};
    $scope.supervisors = {};
    $scope.filterByBranchs = {};
    $scope.workShifts = {};
    $scope.filterByPaygrade = {};
    $scope.filterBySalaries = {};
    $scope.warningForm = {};
    $scope.warnings = {};
    $scope.totalWarnings = 0;
    $scope.warningsPageNumber = 1;
    $scope.terminationForm = {};
    $scope.terminations = {};
    $scope.totalTerminations = 0;
    $scope.terminationsPageNumber = 1;
    $scope.promotionForm = {};
    $scope.promotions = {};
    $scope.totalPromotions = 0;
    $scope.promotionsPageNumber = 1;
    $scope.payGrades = {};

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Employee Management';
    });

    $scope.loadDepartmentsData = function( page ){
        $scope.departmentsPageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listDepartments/' + $scope.departmentsPageNumber).then(function(data) {
            $scope.departments = data.departments;
            $scope.totalDepartments = data.departmentsCount;
            $scope.changeView('departmentList');
            $scope.checkedTab = "department";
            showHideLoad(true);
        });
    }

    $scope.createDepartment = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/createDepartment', 'POST', {}, $scope.depForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.depForm = {};
                $scope.loadDepartmentsData( $scope.departmentsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.editDepartment = function(id){
        showHideLoad();
        let send_data = { 'department_id': id }
        dataFactory.httpRequest('index.php/manageEmployees/viewDepartment', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.depForm = data.department;
                $scope.changeView('departmentEdit');
                $scope.checkedTab = "department";
            }
        });
    }

    $scope.saveDepartment = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/editDepartment', 'POST', {}, $scope.depForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.depForm = {};
                $scope.loadDepartmentsData( $scope.departmentsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }
    
    $scope.removeDepartment = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this department ?',
            function(){
                showHideLoad();
                let send_data = { department_id: item.id }
                dataFactory.httpRequest('index.php/manageEmployees/removeDepartment', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.departments.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.loadDesignationsData = function( page ){
        $scope.designationsPageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listDesignations/' + $scope.designationsPageNumber).then(function(data) {
            $scope.designations = data.designations;
            $scope.totalDesignations = data.designationsCount;
            $scope.changeView('designationList');
            $scope.checkedTab = "designation";
            showHideLoad(true);
        });
    }

    $scope.createDesignation = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/createDesignation', 'POST', {}, $scope.desForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.desForm = {};
                $scope.loadDesignationsData( $scope.designationsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.editDesignation = function(id){
        showHideLoad();
        let send_data = { 'designation_id': id }
        dataFactory.httpRequest('index.php/manageEmployees/viewDesignation', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.desForm = data.designation;
                $scope.changeView('designationEdit');
                $scope.checkedTab = "designation";
            }
        });
    }

    $scope.saveDesignation = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/editDesignation', 'POST', {}, $scope.desForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.desForm = {};
                $scope.loadDesignationsData( $scope.designationsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }
    
    $scope.removeDesignation = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this designation ?',
            function(){
                showHideLoad();
                let send_data = { designation_id: item.id }
                dataFactory.httpRequest('index.php/manageEmployees/removeDesignation', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.designations.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.loadBranchsData = function( page ){
        $scope.branchsPageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listBranchs/' + $scope.branchsPageNumber).then(function(data) {
            $scope.branchs = data.branchs;
            $scope.totalBranchs = data.branchsCount;
            $scope.changeView('branchList');
            $scope.checkedTab = "branch";
            showHideLoad(true);
        });
    }

    $scope.createBranch = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/createBranch', 'POST', {}, $scope.branchForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.branchForm = {};
                $scope.loadBranchsData( $scope.branchsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.editBranch = function(id){
        showHideLoad();
        let send_data = { 'branch_id': id }
        dataFactory.httpRequest('index.php/manageEmployees/viewBranch', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.branchForm = data.branch;
                $scope.changeView('branchEdit');
                $scope.checkedTab = "branch";
            }
        });
    }

    $scope.saveBranch = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/editBranch', 'POST', {}, $scope.branchForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.branchForm = {};
                $scope.loadBranchsData( $scope.branchsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }
    
    $scope.removeBranch = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this branch ?',
            function(){
                showHideLoad();
                let send_data = { branch_id: item.id }
                dataFactory.httpRequest('index.php/manageEmployees/removeBranch', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.branchs.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.loadEmployeesData = function( page ){
        $scope.employeesPageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listEmployees/' + $scope.employeesPageNumber).then(function(data) {
            $scope.employees = data.employees;
            $scope.totalEmployees = data.employeesCount;
            
            $scope.filterByDepartments = data.departments;
            $scope.filterByDesignations = data.designations;
            $scope.filterByRoles = data.roles;
            $scope.supervisors = data.supervisors;
            $scope.filterByBranchs = data.branchs;
            $scope.workShifts = data.shifts;
            $scope.filterByPaygrade = data.paygrades;
            $scope.filterBySalaries = data.hourlySalaries;
            
            $scope.changeView('employeeList');
            $scope.checkedTab = "employee";
            showHideLoad(true);
        });
    }

    $scope.filterEmployeesData = function(){
        showHideLoad();
        let send_data = {
            department: $scope.selectedDepartment,
            designation: $scope.selectedDesignation,
            role: $scope.selectedRole
        };
        dataFactory.httpRequest('index.php/manageEmployees/listEmployees/' + $scope.employeesPageNumber, 'GET', send_data).then(function(data) {
            $scope.employees = data.employees;
            $scope.totalEmployees = data.employeesCount;
            
            $scope.changeView('employeeList');
            $scope.checkedTab = "employee";
            showHideLoad(true);
        });
    }

    $scope.addEducationQualification = function(){
        let randoName = $scope.randomString( 16 );
        let newEdu = { institute: "", university: "", degree: "", passingYear: "", result: "", gpa: "", name: randoName };
        $scope.add_employee.educational.push( newEdu );
    }

    $scope.addProfessionalExperience = function(){
        let randoName = $scope.randomString( 16 );
        let newProfession = { organization: "", designation: "", from: "", to: "", responsibility: "", skill: "", name: randoName };
        $scope.add_employee.professional.push( newProfession );
    }

    $scope.removeEducationQualification = function(index){
        delete $scope.add_employee.educational[index];
        var i = 0; var newDate = [];
        $.each($scope.add_employee.educational, function (indexInArray, val) {
            if( val !== undefined )
            {
                newDate[i] = val;
                i++;
            }
        });
        $scope.add_employee.educational = newDate;
    }

    $scope.removeProfessionalExperience = function(index){
        delete $scope.add_employee.professional[index];
        var i = 0; var newDate = [];
        $.each($scope.add_employee.professional, function (indexInArray, val) {
            if( val !== undefined )
            {
                newDate[i] = val;
                i++;
            }
        });
        $scope.add_employee.professional = newDate;
    }

    $scope.confirmCreateEmployee = function(){
        let account = $scope.add_employee.account;
        let information = $scope.add_employee.information;
        let educational = $scope.add_employee.educational;
        let professional = $scope.add_employee.professional;
        var model = {
            role_id: account.role ? account.role : '',
            username: account.username ? account.username : '',
            password: account.password ? account.password : '',
            academic_role: account.academicRole ? account.academicRole : '',
            first_name: information.firstName ? information.firstName : '',
            last_name: information.lastName ? information.lastName : '',
            finger: information.FingerprintNo ? information.FingerprintNo : '',
            supervisor_id: information.supervisor ? information.supervisor : '',
            department_id: information.department ? information.department : '',
            designation_id: information.designation ? information.designation : '',
            branch_id: information.branch ? information.branch : '',
            shift_id: information.shift ? information.shift : '',
            paygrade_id: information.paygrade ? information.paygrade : '',
            salary_id: information.salary ? information.salary : '',
            email: information.mail ? information.mail : '',
            phone: information.phone ? information.phone : '',
            gender: information.gender ? information.gender : '',
            religion: information.religion ? information.religion : '',
            birthday: information.birthday ? information.birthday : '',
            joinday: information.joinday ? information.joinday : '',
            leaveday: information.leaveday ? information.leaveday : '',
            status: information.status ? information.status : '',
            marital: information.marital ? information.marital : '',
            photo: document.getElementById('photo').files[0],
            address: information.address ? information.address : '',
            emergency: information.emergency ? information.emergency : ''
        };
        let eduCount = 0;
        angular.forEach( educational, function (item, key) {
            model["education_" + eduCount + "_institute"] = educational[key].institute;
            model["education_" + eduCount + "_university"] = educational[key].university;
            model["education_" + eduCount + "_degree"] = educational[key].degree;
            model["education_" + eduCount + "_passingYear"] = educational[key].passingYear;
            model["education_" + eduCount + "_result"] = educational[key].result;
            model["education_" + eduCount + "_gpa"] = educational[key].gpa;
            eduCount++;
        });
        model['eduCount'] = eduCount;
        let proffCount = 0;
        angular.forEach( professional, function (item, key) {
            model["profession_" + proffCount + "_organization"] = professional[key].organization;
            model["profession_" + proffCount + "_designation"] = professional[key].designation;
            model["profession_" + proffCount + "_from"] = professional[key].from;
            model["profession_" + proffCount + "_to"] = professional[key].to;
            model["profession_" + proffCount + "_responsibility"] = professional[key].responsibility;
            model["profession_" + proffCount + "_skill"] = professional[key].skill;
            proffCount++;
        });
        model['proffCount'] = proffCount;
        var configs = {
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) { formData.append(key, value); });
                return formData;
            }
        };
        $scope.loadingIcon = true;
        $http.post('index.php/manageEmployees/createEmployee', model, configs).then(function(data) {
            let xhrResponse = data.data;
            if( xhrResponse.status == 'success' )
            {
                $scope.loadingIcon = false;
                response = apiResponse(xhrResponse, 'edit');
                $scope.add_employee = {
                    account: { role: "", username: "", password: "", academicRole: "" },
                    information: { firstName: "", lastName: "", FingerprintNo: "", supervisor: "", department: "", designation: "", branch: "", shift: "", paygrade: "", salary: "", mail: "", phone: "", gender: "", religion: "", birthday: "", joinday: "", leaveday: "", marital: "", status: "", photo: "", address: "", emergency: "" },
                    educational: [],
                    professional: []
                };
                $scope.jumpTo( "employee", true );
            }
            else
            {
                $scope.loadingIcon = false;
                response = apiResponse(xhrResponse, 'remove');
            }
        },function( error ){
            $scope.loadingIcon = false;
            let errorMsg = {
                status: "failed",
                title: "Create employee",
                message: "Error occurred while processing your request"
            }
            response = apiResponse(errorMsg, 'remove');
        });
    }

    $scope.editEmployee = function(id){
        showHideLoad();
        let send_data = { employee_id: id }
        dataFactory.httpRequest('index.php/manageEmployees/viewEmployee', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.employeeForm = data.employee;
                $scope.edit_employee.account.role = data.employee.role_id;
                $scope.edit_employee.account.id = data.employee.id;
                $scope.edit_employee.account.user_id = data.employee.user_id;
                $scope.edit_employee.account.username = data.employee.username;
                $scope.edit_employee.account.academicRole = data.employee.academic_role;
    
                $scope.edit_employee.information.firstName = data.employee.first_name;
                $scope.edit_employee.information.lastName = data.employee.last_name;
                $scope.edit_employee.information.FingerprintNo = data.employee.finger_id;
                $scope.edit_employee.information.supervisor = data.employee.supervisor_id;
                $scope.edit_employee.information.department = data.employee.department_id;
                $scope.edit_employee.information.designation = data.employee.designation_id;
                $scope.edit_employee.information.branch = data.employee.branch_id;
                $scope.edit_employee.information.shift = data.employee.work_shift_id;
                $scope.edit_employee.information.paygrade = data.employee.pay_grade_id;
                $scope.edit_employee.information.salary = data.employee.hourly_salaries_id;
                $scope.edit_employee.information.mail = data.employee.email;
                $scope.edit_employee.information.phone = data.employee.phone;
                $scope.edit_employee.information.gender = data.employee.gender;
                $scope.edit_employee.information.religion = data.employee.religion;
                $scope.edit_employee.information.birthday = data.employee.date_of_birth;
                $scope.edit_employee.information.joinday = data.employee.date_of_joining;
                $scope.edit_employee.information.leaveday = data.employee.date_of_leaving;
                $scope.edit_employee.information.marital = data.employee.marital_status;
                $scope.edit_employee.information.status = data.employee.status;
                $scope.edit_employee.information.address = data.employee.address;
                $scope.edit_employee.information.emergency = data.employee.emergency_contacts;
    
                $scope.edit_employee.educational = data.employee.education;
                $scope.edit_employee.professional = data.employee.experience;
    
                $scope.changeView('employeeEdit');
                $scope.checkedTab = "employee";
            }
        });
    }

    $scope.editEducationQualification = function(){
        let randoName = $scope.randomString( 16 );
        let newEdu = { institute: "", university: "", degree: "", passingYear: "", result: "", gpa: "", name: randoName };
        $scope.edit_employee.educational.push( newEdu );
    }
    
    $scope.editProfessionalExperience = function(){
        let randoName = $scope.randomString( 16 );
        let newProfession = { organization: "", designation: "", from: "", to: "", responsibility: "", skill: "", name: randoName };
        $scope.edit_employee.professional.push( newProfession );
    }

    $scope.deleteEducationQualification = function(index){
        delete $scope.edit_employee.educational[index];
        var i = 0; var newDate = [];
        $.each($scope.edit_employee.educational, function (indexInArray, val) {
            if( val !== undefined )
            {
                newDate[i] = val;
                i++;
            }
        });
        $scope.edit_employee.educational = newDate;
    }

    $scope.deleteProfessionalExperience = function(index){
        delete $scope.edit_employee.professional[index];
        var i = 0; var newDate = [];
        $.each($scope.edit_employee.professional, function (indexInArray, val) {
            if( val !== undefined )
            {
                newDate[i] = val;
                i++;
            }
        });
        $scope.edit_employee.professional = newDate;
    }

    $scope.confirmUpdateEmployee = function(){
        let account = $scope.edit_employee.account;
        let information = $scope.edit_employee.information;
        let educational = $scope.edit_employee.educational;
        let professional = $scope.edit_employee.professional;
        var model = {
            employee_id: account.id ? account.id : '',
            user_id: account.user_id ? account.user_id : '',
            role_id: account.role ? account.role : '',
            username: account.username ? account.username : '',
            password: account.password && account.password != "" ? account.password : '',
            academic_role: account.academicRole ? account.academicRole : '',
            first_name: information.firstName ? information.firstName : '',
            last_name: information.lastName ? information.lastName : '',
            finger: information.FingerprintNo ? information.FingerprintNo : '',
            supervisor_id: information.supervisor ? information.supervisor : '',
            department_id: information.department ? information.department : '',
            designation_id: information.designation ? information.designation : '',
            branch_id: information.branch ? information.branch : '',
            shift_id: information.shift ? information.shift : '',
            paygrade_id: information.paygrade ? information.paygrade : '',
            salary_id: information.salary ? information.salary : '',
            email: information.mail ? information.mail : '',
            phone: information.phone ? information.phone : '',
            gender: information.gender ? information.gender : '',
            religion: information.religion ? information.religion : '',
            birthday: information.birthday ? information.birthday : '',
            joinday: information.joinday ? information.joinday : '',
            leaveday: information.leaveday ? information.leaveday : '',
            status: information.status ? information.status : '',
            marital: information.marital ? information.marital : '',
            photo: document.getElementById('photo').files[0],
            address: information.address ? information.address : '',
            emergency: information.emergency ? information.emergency : ''
        };
        let eduCount = 0;
        angular.forEach( educational, function (item, key) {
            model["education_" + eduCount + "_institute"] = educational[key].institute;
            model["education_" + eduCount + "_university"] = educational[key].university;
            model["education_" + eduCount + "_degree"] = educational[key].degree;
            model["education_" + eduCount + "_passingYear"] = educational[key].passingYear;
            model["education_" + eduCount + "_result"] = educational[key].result;
            model["education_" + eduCount + "_gpa"] = educational[key].gpa;
            eduCount++;
        });
        model['eduCount'] = eduCount;
        let proffCount = 0;
        angular.forEach( professional, function (item, key) {
            model["profession_" + proffCount + "_organization"] = professional[key].organization;
            model["profession_" + proffCount + "_designation"] = professional[key].designation;
            model["profession_" + proffCount + "_from"] = professional[key].from;
            model["profession_" + proffCount + "_to"] = professional[key].to;
            model["profession_" + proffCount + "_responsibility"] = professional[key].responsibility;
            model["profession_" + proffCount + "_skill"] = professional[key].skill;
            proffCount++;
        });
        model['proffCount'] = proffCount;
        var configs = {
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) { formData.append(key, value); });
                return formData;
            }
        };
        $scope.loadingIcon = true;
        $http.post('index.php/manageEmployees/editEmployee', model, configs).then(function(data) {
            let xhrResponse = data.data;
            if( xhrResponse.status == 'success' )
            {
                $scope.loadingIcon = false;
                response = apiResponse(xhrResponse, 'edit');
                $scope.edit_employee = {
                    account: { id: "", user_id: "", role: "", username: "", password: "", academicRole: "" },
                    information: { firstName: "", lastName: "", FingerprintNo: "", supervisor: "", department: "", designation: "", branch: "", shift: "", paygrade: "", salary: "", mail: "", phone: "", gender: "", religion: "", birthday: "", joinday: "", leaveday: "", marital: "", status: "", photo: "", address: "", emergency: "" },
                    educational: [],
                    professional: []
                };
                $scope.jumpTo( "employee", true );
            }
            else
            {
                $scope.loadingIcon = false;
                response = apiResponse(xhrResponse, 'remove');
            }
        },function( error ){
            $scope.loadingIcon = false;
            let errorMsg = {
                status: "failed",
                title: "Create employee",
                message: "Error occurred while processing your request"
            }
            response = apiResponse(errorMsg, 'remove');
        });
    }

    $scope.viewEmployee = function( id ){
        showHideLoad();
        let send_data = { employee_id: id }
        dataFactory.httpRequest('index.php/manageEmployees/viewEmployee', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.employeeForm = data.employee;
                $scope.changeView('employeeView');
                $scope.checkedTab = "employee";
            }
        });
    }

    $scope.removeEmployee = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this employee ?',
            function(){
                showHideLoad();
                let send_data = {'employee_id': item.employee_id }
                dataFactory.httpRequest('index.php/manageEmployees/removeEmployee', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.employees.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.loadWarningsData = function( page ){
        $scope.warningsPageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listWarnings/' + $scope.warningsPageNumber).then(function(data) {
            $scope.warnings = data.warnings;
            $scope.totalWarnings = data.warningsCount;
            $scope.supervisors = data.employees;
            $scope.changeView('warningList');
            $scope.checkedTab = "warning";
            showHideLoad(true);
        });
    }

    $scope.createWarning = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/createWarning', 'POST', {}, $scope.warningForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.warningForm = {};
                $scope.loadWarningsData( $scope.warningsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.viewWarning = function( id ){
        showHideLoad();
        let send_data = { warning_id: id }
        dataFactory.httpRequest('index.php/manageEmployees/viewWarning', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.warningForm = data.warning;
                $scope.changeView('warningView');
                $scope.checkedTab = "warning";
            }
        });
    }

    $scope.editWarning = function(id){
        showHideLoad();
        let send_data = { warning_id: id }
        dataFactory.httpRequest('index.php/manageEmployees/viewWarning', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.warningForm = data.warning;
                $scope.changeView('warningEdit');
                $scope.checkedTab = "warning";
            }
        });
    }

    $scope.saveWarning = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/editWarning', 'POST', {}, $scope.warningForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.warningForm = {};
                $scope.loadWarningsData( $scope.warningsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }
    
    $scope.removeWarning = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this warning ?',
            function(){
                showHideLoad();
                let send_data = { warning_id: item.id }
                dataFactory.httpRequest('index.php/manageEmployees/removeWarning', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.warnings.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.loadTerminationData = function( page ){
        $scope.terminationsPageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listTerminations/' + $scope.terminationsPageNumber).then(function(data) {
            $scope.terminations = data.terminations;
            $scope.totalTerminations = data.terminationsCount;
            $scope.supervisors = data.employees;
            $scope.changeView('terminationList');
            $scope.checkedTab = "termination";
            showHideLoad(true);
        });
    }

    $scope.createTermination = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/createTermination', 'POST', {}, $scope.terminationForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.terminationForm = {};
                $scope.loadTerminationData( $scope.terminationsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.viewTermination = function( id ){
        showHideLoad();
        let send_data = { termination_id: id }
        dataFactory.httpRequest('index.php/manageEmployees/viewTermination', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.terminationForm = data.termination;
                $scope.changeView('terminationView');
                $scope.checkedTab = "termination";
            }
        });
    }

    $scope.editTermination = function(id){
        showHideLoad();
        let send_data = { termination_id: id }
        dataFactory.httpRequest('index.php/manageEmployees/viewTermination', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.terminationForm = data.termination;
                $scope.changeView('terminationEdit');
                $scope.checkedTab = "termination";
            }
        });
    }

    $scope.saveTermination = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/editTermination', 'POST', {}, $scope.terminationForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.terminationForm = {};
                $scope.loadTerminationData( $scope.terminationsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }
    
    $scope.removeTermination = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this termination ?',
            function(){
                showHideLoad();
                let send_data = { termination_id: item.id }
                dataFactory.httpRequest('index.php/manageEmployees/removeTermination', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.terminations.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }
    
    $scope.approveTermination = function(id){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Approve Termination',
            "Are you sure you don't want to wait until termination date and approve termination now ?",
            function(){
                $scope.speicalLoadingIcon = true;
                let send_data = { termination_id: id, isApproved: true }
                dataFactory.httpRequest('index.php/manageEmployees/editTermination', 'POST', {}, send_data).then(function(data) {
                    
                    $scope.speicalLoadingIcon = false;
                    if( data.status == "success" )
                    {
                        apiResponse(data, 'edit');
                        $scope.terminationForm = {};
                        $scope.loadTerminationData( $scope.terminationsPageNumber );
                    } else { apiResponse(data, 'remove'); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.loadPromotionData = function( page ){
        $scope.promotionsPageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageEmployees/listPromotions/' + $scope.promotionsPageNumber).then(function(data) {
            $scope.promotions = data.promotions;
            $scope.totalPromotions = data.promotionsCount;
            $scope.supervisors = data.employees;
            $scope.departments = data.departments;
            $scope.designations = data.designations;
            $scope.payGrades = data.paygrades;
            $scope.changeView('promotionList');
            $scope.checkedTab = "promotion";
            showHideLoad(true);
        });
    }

    $scope.createPromotion = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/createPromotion', 'POST', {}, $scope.promotionForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.promotionForm = {};
                $scope.loadPromotionData( $scope.promotionsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.loadEmployeeBasicData = function(){
        let empId = $scope.promotionForm.to;
        $scope.promotionForm.currDep = $scope.supervisors[empId].department;
        $scope.promotionForm.currdepId = $scope.supervisors[empId].depId;
        $scope.promotionForm.currDes = $scope.supervisors[empId].designation;
        $scope.promotionForm.currdesId = $scope.supervisors[empId].desId;
        $scope.promotionForm.currPayGrade = $scope.supervisors[empId].paygrade;
        $scope.promotionForm.currPayGradeId = $scope.supervisors[empId].paygradeId;
        $scope.promotionForm.currSalary = $scope.supervisors[empId].salary;
    }

    $scope.loadPaygradeSalary = function(){
        let gradeId = $scope.promotionForm.promotedPayGrade;
        $scope.promotionForm.promotedSalary = $scope.payGrades[gradeId].salary;
    }

    $scope.loadEditPaygradeSalary = function(){
        let gradeId = $scope.promotionForm.promoted_pay_id;
        $scope.promotionForm.new_salary = $scope.payGrades[gradeId].salary;
    }

    $scope.editPromotion = function(id){
        showHideLoad();
        let send_data = { promotion_id: id }
        dataFactory.httpRequest('index.php/manageEmployees/viewPromotion', 'POST', {}, send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else
            {
                $scope.promotionForm = data.promotion;
                $scope.changeView('promotionEdit');
                $scope.checkedTab = "promotion";
            }
        });
    }

    $scope.savePromotion = function(){
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/manageEmployees/editPromotion', 'POST', {}, $scope.promotionForm).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.promotionForm = {};
                $scope.loadPromotionData( $scope.promotionsPageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }
    
    $scope.removePromotion = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            'Are you sure you want to remove this promotion ?',
            function(){
                showHideLoad();
                let send_data = { promotion_id: item.id }
                dataFactory.httpRequest('index.php/manageEmployees/removePromotion', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.promotions.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }
    
    $scope.approvePromotion = function(id){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Approve Promotion',
            "Are you sure you don't want to wait until promotion date and approve promotion now ?",
            function(){
                $scope.speicalLoadingIcon = true;
                let send_data = { promotion_id: id, isApproved: true }
                dataFactory.httpRequest('index.php/manageEmployees/editPromotion', 'POST', {}, send_data).then(function(data) {
                    $scope.speicalLoadingIcon = false;
                    if( data.status == "success" )
                    {
                        apiResponse(data, 'edit');
                        $scope.promotionForm = {};
                        $scope.loadPromotionData( $scope.promotionsPageNumber );
                    } else { apiResponse(data, 'remove'); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }
    
    $scope.jumpTo = function( name, excuse = null ){
        switch( name )
        {
            case 'employee': {
                if( excuse ) { $scope.loadEmployeesData( $scope.employeesPageNumber ); }
                else if( $scope.checkedTab != "employee" ) { $scope.loadEmployeesData( $scope.employeesPageNumber ); }
                break;
            }
            case 'department': {
                if( excuse ) { $scope.loadDepartmentsData( $scope.departmentsPageNumber ); }
                else if( $scope.checkedTab != "department" ) { $scope.loadDepartmentsData( $scope.departmentsPageNumber ); }
                break;
            }
            case 'designation': {
                if( excuse ) { $scope.loadDesignationsData( $scope.designationsPageNumber ); }
                else if( $scope.checkedTab != "designation" ) { $scope.loadDesignationsData( $scope.designationsPageNumber ); }
                break;
            }
            case 'branch': {
                if( excuse ) { $scope.loadBranchsData( $scope.branchsPageNumber ); }
                else if( $scope.checkedTab != "branch" ) { $scope.loadBranchsData( $scope.branchsPageNumber ); }
                break;
            }
            case 'warning': {
                if( excuse ) { $scope.loadWarningsData( $scope.warningsPageNumber ); }
                else if( $scope.checkedTab != "warning" ) { $scope.loadWarningsData( $scope.warningsPageNumber ); }
                break;
            }
            case 'termination': {
                if( excuse ) { $scope.loadTerminationData( $scope.terminationsPageNumber ); }
                else if( $scope.checkedTab != "termination" ) { $scope.loadTerminationData( $scope.terminationsPageNumber ); }
                break;
            }
            case 'promotion': {
                if( excuse ) { $scope.loadPromotionData( $scope.promotionsPageNumber ); }
                else if( $scope.checkedTab != "promotion" ) { $scope.loadPromotionData( $scope.promotionsPageNumber ); }
                break;
            }
        }
    }

    $scope.changeView = function(view){
        $scope.views.departmentList = false;
        $scope.views.departmentAdd = false;
        $scope.views.departmentEdit = false;
        $scope.views.designationList = false;
        $scope.views.designationAdd = false;
        $scope.views.designationEdit = false;
        $scope.views.branchList = false;
        $scope.views.branchAdd = false;
        $scope.views.branchEdit = false;
        $scope.views.employeeList = false;
        $scope.views.employeeAdd = false;
        $scope.views.employeeEdit = false;
        $scope.views.employeeView = false;
        $scope.views.warningList = false;
        $scope.views.warningAdd = false;
        $scope.views.warningEdit = false;
        $scope.views.warningView = false;
        $scope.views.terminationList = false;
        $scope.views.terminationAdd = false;
        $scope.views.terminationEdit = false;
        $scope.views.terminationView = false;
        $scope.views.promotionList = false;
        $scope.views.promotionAdd = false;
        $scope.views.promotionEdit = false;
        $scope.views[view] = true;
    }

    $scope.randomString = function( length )
	{
		var result           = '';
		var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
   		for ( var i = 0; i < length; i++ ) {
      		result += characters.charAt(Math.floor(Math.random() * charactersLength));
   		}
		return result;
	}

    $scope.pageChanged = function(newPage) {
        switch( $scope.checkedTab ){
            case 'department': { $scope.loadDepartmentsData( newPage ); break; }
            case 'designation': { $scope.loadDesignationsData( newPage ); break; }
            case 'branch': { $scope.loadBranchsData( newPage ); break; }
            case 'employee': { 
                if( loadEmployeesData != 0 || $scope.selectedDesignation != 0 || $scope.selectedRole != 0 )
                    $scope.filterEmployeesData();
                else
                    $scope.loadEmployeesData( newPage );
                break;
            }
            case 'warning': { $scope.loadWarningsData( newPage ); break; }
            case 'termination': { $scope.loadTerminationData( newPage ); break; }
            case 'promotion': { $scope.loadPromotionData( newPage ); break; }
        }
    }

    if( $scope.checkedTab == "" )
    {
        if( $rootScope.can('employees.list') ||  $rootScope.can('employees.myProfile') ) { $scope.jumpTo('employee'); }
        else if( $rootScope.can('departments.list') ) { $scope.jumpTo('department'); }
        else if( $rootScope.can('designations.list') ) { $scope.jumpTo('designation'); }
        else if( $rootScope.can('branchs.list') ) { $scope.jumpTo('branch'); }
        else if( $rootScope.can('warnings.list') ) { $scope.jumpTo('warning'); }
        else if( $rootScope.can('terminations.list') ) { $scope.jumpTo('termination'); }
        else if( $rootScope.can('promotions.list') ) { $scope.jumpTo('promotion'); }
    }
});