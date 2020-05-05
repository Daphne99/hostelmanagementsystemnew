var CuteBrains = angular.module('EnterClassesAndSubjectsController', []);

CuteBrains.controller('EnterClassesAndSubjectsController', function(dataFactory,$rootScope,$scope) {
	$scope.form = {};
	$scope.ImportModalStatus = false;

	$scope.$on('$viewContentLoaded', function() {
  	document.title = $('meta[name="site_title"]').attr('content') + ' | Bulk classes and subjects';
  	showHideLoad(true);
  });

  $scope.saveAdd = function(){
    showHideLoad();
    dataFactory.httpRequest('index.php/classes-subjects/bulk-insert-classes-seubjects','POST',{},$scope.form).then(function(data) {
      response = apiResponse(data,'add');
      if(data.status == "success"){
      	$scope.form = {};
      }
      showHideLoad(true);
    });
	}

	$scope.importExcel = function(){
		$scope.modalTitle = 'Import classes and subjects';
		$scope.ImportModalStatus = true;
	}
	$scope.saveImported = function(content){
      content = uploadSuccessOrError(content);
      if(content){
          $scope.ImportModalStatus = false;
          if(content.status == 'success') {
            var data = {status: 'success', title: content.title, message: content.message};
            response = apiResponse(data, 'add');
          } else if(content.status == 'failed') {
          	$.toast({
                heading: content.title,
                text: content.message,
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'error',
                hideAfter: 10000,
                stack: 6
            });
          }
          showHideLoad();
          // $scope.changeView('reviewImport');
      }
      showHideLoad(true);
    }
});