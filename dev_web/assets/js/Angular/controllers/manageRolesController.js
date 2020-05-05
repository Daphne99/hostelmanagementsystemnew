var CuteBrains = angular.module('manageRolesController', []);

CuteBrains.controller('manageRolesController', function( dataFactory, $rootScope, $scope, $sce, $http ) {
    $scope.views = {};
    $scope.pageNumber = 1;
    $scope.totalItems = 0;
    $scope.form = {};
    $scope.form.role_title = "";
    $scope.form.role_description = "";
    $scope.form.def_for = "";
    $scope.form.role_permissions = [];
    $scope.fullPerm = [];
    $scope.customPerms = {};
    $scope.isAll = false;
    $scope.edittingInProgress = false;
    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Manage Roles';
    });

    $scope.load_data = function( page ){
        $scope.pageNumber = page;
        showHideLoad();
        dataFactory.httpRequest('index.php/manageRoles/listAll/' + $scope.pageNumber).then(function(data) {
            $scope.roles = data.roles;
            $scope.totalItems = data.rolesCount;
            $scope.roles_perms = data.roles_perms;
            $scope.customPerms = data.customPerms;
            let innerFullPerm = []; let index = 0;
            $.each($scope.roles_perms, function (key, roles) { 
                $.each(roles.roles, function (inner, role) { 
                    innerFullPerm[index] = key + "." + inner;
                    index++;
                });
            });
            $scope.fullPerm = innerFullPerm;
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.load_data( 1 );
    
    $scope.edit = function(id){
        showHideLoad();
        let send_data = { 'role_id': id }
        dataFactory.httpRequest('index.php/manageRoles/role', 'GET', send_data).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            if( $scope.form.role_permissions.length == $scope.fullPerm.length ) $scope.isAll = true;
            else $scope.isAll = false;
            showHideLoad(true);
        });
    }

    $scope.checkAll = function (){
        let fullPerm = [];
        angular.forEach( $scope.roles_perms, function (data, key){
            angular.forEach( data.roles, function (role, inner){
                var roleName = key + "." + inner;
                if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); }
            });
        });
        angular.forEach( $scope.customPerms, function (data, key) {
            if( key == "viewRecords" || key == "addRecords" || key == "hrEmployees" || key == "hrLeaves" || key == "hrAttendance" || key == "hrPayroll" )
                { angular.forEach( data, function (rows, innerKey) { angular.forEach( rows.roles, function (role, roleKey) { var roleName = rows.index + "." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }); }
            else if( key == "viewMessages" || key == "composeMessage" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "messaging." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "viewEvents" || key == "composeEvent" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "events." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "viewNotices" || key == "composeNotice" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "newsboard." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "viewMailSms" || key == "composeMailSms" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "mailsms." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "academicyears" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "academicyears." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "subjectAssign" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "Subjects." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "homework" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "Homework." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "Assignments" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "Assignments." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "studyMaterial" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "studyMaterial." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "mySubjects" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "subjects." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "showStudents" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "students." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "gradeLevels" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "gradeLevels." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "examsList" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "examsList." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "onlineExams" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "onlineExams." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "schoolTerms" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "schoolTerms." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else if( key == "genMarksheet" ) { angular.forEach( data.roles, function (role, roleKey) { var roleName = "genMarksheet." + roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
            else { angular.forEach( data.roles, function (role, roleKey) { var roleName = roleKey; if( !$scope.in_array(roleName, fullPerm) ) { fullPerm.push(roleName); } }); }
        });
        if( $scope.form.role_permissions.length == fullPerm.length )
        {
            $scope.form.role_permissions = [];
            $scope.isAll = false;
        }
        else
        {
            $scope.form.role_permissions = fullPerm;
            $scope.isAll = true;
        }
    }

    $scope.confirmEditRole = function(){
        $scope.edittingInProgress = true;
        dataFactory.httpRequest('index.php/manageRoles/saveRole', 'POST', {}, $scope.form).then(function(data) {
            $scope.edittingInProgress = false;
            if( data.status == "success" )
            {
                $scope.form = {};
                $scope.form.role_title = "";
                $scope.form.role_description = "";
                $scope.form.def_for = "";
                $scope.form.role_permissions = [];
                apiResponse(data, 'edit');
                $scope.changeView('list');
                setTimeout(function(){ location.reload(); }, 3000);
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.confirmSaveRole = function(){
        $scope.edittingInProgress = true;
        dataFactory.httpRequest('index.php/manageRoles/createRole', 'POST', {}, $scope.form).then(function(data) {
            $scope.edittingInProgress = false;
            if( data.status == "success" )
            {
                $scope.form = {};
                $scope.form.role_title = "";
                $scope.form.role_description = "";
                $scope.form.def_for = "";
                $scope.form.role_permissions = [];
                apiResponse(data, 'edit');
                $scope.load_data( $scope.pageNumber );
            } else { apiResponse(data, 'remove'); }
        });
    }

    $scope.pageChanged = function(newPage) {
        $scope.load_data( newPage );
    }
    
    $scope.remove = function(item,index){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            $rootScope.phrase.sureRemove,
            function(){
                showHideLoad();
                let send_data = { role_id: item.id }
                dataFactory.httpRequest('index.php/manageRoles/deleteRole', 'POST', {}, send_data).then(function(data) {
                    response = apiResponse(data,'remove');
                    if(data.status == "success") { $scope.roles.splice(index,1); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.changeView = function(view){
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