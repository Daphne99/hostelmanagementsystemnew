var CuteBrains = angular.module('myMarkSheetController', []);

CuteBrains.controller('myMarkSheetController', function(dataFactory, $rootScope, $scope, $sce)
{
    $scope.views = {};
    $scope.views.list = false;
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.sheetdata = {};
    $scope.sheetLink = "";
    $scope.sheetId = "";
    $scope.sheets = {};
    $scope.students = {};
    $scope.type = "";

    $scope.$on('$viewContentLoaded', function() {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Marksheets';
  });

    $scope.getList = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/marksheetCollection/listAll').then(function(data){
            showHideLoad(true);
            if( data.status == "success" )
            {
                $scope.type = data.type;
                if( data.type == "sheets" )
                {
                    $scope.sheets = data.sheets;
                    $scope.changeView('list');
                }
                else if( data.type == "marks" )
                {
                    $scope.students = data.sheets;
                    $scope.changeView('marks');
                }
                else { apiResponse(data, 'error'); }
            } else if ( data.status == "failed" ) { apiResponse(data,'error'); }
            else { apiResponse(data, 'error'); }
        });
    }

    $scope.getList();

    $scope.openSheetMember = function(sheetId){
        showHideLoad();
        let send_data = { sheet_id: sheetId }
        dataFactory.httpRequest('index.php/marksheetCollection/listAll', 'GET', send_data).then(function(data){
            showHideLoad(true);
            if( data.status == "success" )
            {
                $scope.students = data.sheets;
                $scope.type = data.type;
                if( data.type == "sheets" ) { $scope.changeView('list'); }
                else if( data.type == "marks" ) { $scope.changeView('marks'); }
                else { apiResponse(data, 'error'); }
            } else if ( data.status == "failed" ) { apiResponse(data,'error'); }
            else { apiResponse(data, 'error'); }
        });
    }

    $scope.proccessSheet = function(sheetId){
        $scope.sheetId = sheetId;
        showHideLoad();
        let send_data = { sheetId: sheetId };
        dataFactory.httpRequest('index.php/marksheetCollection/mySheet', 'POST', {}, send_data).then(function(data){
            showHideLoad(true);
            if( data.status == "success" )
            {
                $scope.sheetdata = data.sheet;
                $scope.changeView('frame');
            } else { apiResponse(data, 'error'); }
        });
    }

    $scope.deleteEntireSheet = function(sheetId, index)
    {
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            "Confirm deletion",
            "Are you sure you want to remove <b>( " + $scope.sheets[index].name + " )</b> and all students sheets related to this card ? <br /> note this action can't be undone",
            function(){
                showHideLoad();
                var send_data = {sheetId: sheetId};
                dataFactory.httpRequest('index.php/marksheetCollection/deleteSheet', 'POST', {}, send_data).then(function(data) {
                    if(data.status == "success") { $scope.sheets.splice(index,1); response = apiResponse(data,'edit'); }
                    else { response = apiResponse(data,'remove'); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.deleteStudentSheet = function(sheetId, index)
    {
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-info';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-danger';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            "Confirm deletion",
            "Are you sure you want to remove <b>( " + $scope.students[index].name + " for " + $scope.students[index].studentName + " )</b> report card ? <br /> note this action can't be undone",
            function(){
                showHideLoad();
                var send_data = {sheetId: sheetId};
                dataFactory.httpRequest('index.php/marksheetCollection/deleteStudent', 'POST', {}, send_data).then(function(data) {
                    if(data.status == "success") { $scope.students.splice(index,1); response = apiResponse(data,'edit'); }
                    else { response = apiResponse(data,'remove'); }
                    showHideLoad(true);
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes", cancel: "Cancel"});
    }

    $scope.changeView = function(view)
    {
        $scope.views.list = false;
        $scope.views.marks = false;
        $scope.views.frame = false;
        $scope.views[view] = true;
    }
});