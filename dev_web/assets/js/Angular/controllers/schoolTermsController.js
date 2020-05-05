var CuteBrains = angular.module('schoolTermsController', []);

CuteBrains.controller('schoolTermsController', function(dataFactory,$rootScope,$scope) {
	$scope.terms = {};
	$scope.views = {};
  $scope.views.list = true;
  $scope.form = {};

  $scope.$on('$viewContentLoaded', function() {
  	document.title = $('meta[name="site_title"]').attr('content') + ' | School terms';
  });

  $scope.fetchAllTerms = function() {
  	showHideLoad();
    dataFactory.httpRequest('index.php/school-terms/listAll').then(function(data) {
      $scope.terms = data.terms;
      showHideLoad(true);
    });
  }
  $scope.fetchAllTerms();

  $scope.saveAdd = function(){
      showHideLoad();
      dataFactory.httpRequest('index.php/school-terms','POST',{},$scope.form).then(function(data) {
          response = apiResponse(data,'add');
          if(data.status == "success"){
              $scope.terms.push(response);
              $scope.changeView('list');
          }
          showHideLoad(true);
      });
  }

  $scope.remove = function(item,index){
      var confirmRemove = confirm($rootScope.phrase.sureRemove);
      if (confirmRemove == true) {
          showHideLoad();
          dataFactory.httpRequest('index.php/school-terms/delete/'+item.id,'POST').then(function(data) {
              response = apiResponse(data,'remove');
              if(data.status == "success"){
                  $scope.terms.splice(index,1);
              }
              showHideLoad(true);
          });
      }
  }

  $scope.edit = function(id){
      showHideLoad();
      dataFactory.httpRequest('index.php/school-terms/'+id).then(function(data) {
        $scope.form = data;
      	$scope.changeView('edit');
        showHideLoad(true);
      });
  }

  $scope.saveEdit = function(){
      showHideLoad();
      dataFactory.httpRequest('index.php/school-terms/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
          response = apiResponse(data,'edit');
          if(data.status == "success"){
              $scope.terms = apiModifyTable($scope.terms,response.id,response);
              $scope.changeView('list');
          }
          showHideLoad(true);
      });
  }

  $scope.changeView = function(view){
    if(view == "add" || view == "list" || view == "show"){
        $scope.form = {};
    }
    if(view == "list") {
    		$scope.fetchAllTerms();
    }

    $scope.views.list = false;
    $scope.views.add = false;
    $scope.views.edit = false;
    $scope.views[view] = true;
  }
});