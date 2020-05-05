var CuteBrains = angular.module('viewAndEditBulkInvoicesController', []);

CuteBrains.controller('viewAndEditBulkInvoicesController', function(dataFactory,$rootScope,$scope) {
	$scope.student_types = {};
	$scope.classes = {};
	$scope.sections = {};
  $scope.views = {};
  $scope.views.init_import = true;
  $scope.filterForm = {};
  $scope.editForm = {};

  $scope.$on('$viewContentLoaded', function() {
  	document.title = $('meta[name="site_title"]').attr('content') + ' | View and edit bulk invoices';
  });

  $scope.getStudentTypes = function() {
  	showHideLoad();
    dataFactory.httpRequest('index.php/student_types/listAll?sort=ASC').then(function(data) {
      $scope.student_types = data.student_types;
      $scope.filterForm.student_type = 0; // default selection for 1st element
      setTimeout(function(){
  			$('select[multiple]').selectpicker('destroy');
  			$('select[multiple]').selectpicker();
  		}, 300)
      showHideLoad(true);
    });
  }
  $scope.getClasses = function() {
  	showHideLoad();
    dataFactory.httpRequest('index.php/classes/listAll').then(function(data) {
      $scope.classes = data.classes;
      setTimeout(function(){
  			$('select.classes[multiple]').selectpicker('destroy');
  			$('select.classes[multiple]').selectpicker();
  		}, 300)
      showHideLoad(true);
    });
  }
  $scope.getSelectedSectionByClassId = function(){
		var class_id = $scope.filterForm.classes;
		if(class_id.length == 1) {
  		showHideLoad();
      dataFactory.httpRequest(`index.php/sections/fetch-with-class/${class_id}`).then(function(data) {
        $scope.sections = data.sections;
        $scope.filterForm.sections = [$scope.sections[0].id];
        setTimeout(function(){
	  			$('select.sections[multiple]').selectpicker('destroy');
	  			$('select.sections[multiple]').selectpicker();
	  		}, 300)
        showHideLoad(true);
    	});
		} else {
			$scope.filterForm.sections = '';
			setTimeout(function(){
  			$('select.sections[multiple]').selectpicker('destroy');
  			$('select.sections[multiple]').selectpicker();
  		}, 300)
		}
  }
  $scope.getStudentTypes();
  $scope.getClasses();

  $scope.viewBulkFee = function() {
  	showHideLoad();
    dataFactory.httpRequest('index.php/invoices-bulk/list-fees', 'POST', {}, $scope.filterForm).then(function(data) {
      apiResponse(data);
      if(data.status != 'failed') {
	      $scope.bulk_fees_titles = data.bulk_fees_titles;
	      $scope.bulk_fees = data.bulk_fees;
	      $scope.editForm = data.bulk_fees;
	      $scope.temp_student_ids = data.student_ids;
	      $scope.bulk_fees_paymentRows = data.bulk_fees_paymentRows;
	      $scope.changeView('edit_fees');
      }
      showHideLoad(true);
    });
  }

  $scope.__checkCurrentPaymentRow = function(rows, row_title){
  	if(rows.find(row => row.title.toLowerCase() == row_title) == undefined) {
  		return false;
  	} else {
  		return true;
  	}
  }

  $scope.bulkEditFees = function(){
  	showHideLoad();
    dataFactory.httpRequest('index.php/invoices-bulk/update-fees', 'POST', {}, {
    	form: $scope.editForm,
    	student_ids: $scope.temp_student_ids
    }).then(function(data) {
      apiResponse(data);
      $scope.filterForm = {};
       setTimeout(function(){
  			$('select[multiple]').selectpicker('destroy');
  			$('select[multiple]').selectpicker();
  		}, 300)
      $scope.changeView('init_import');
      showHideLoad(true);
    });
  }

  $scope.changeView = function(view){
    $scope.views.init_import = false;
    $scope.views.edit_fees = false;
    $scope.views[view] = true;
  }
}).directive('tempFeeDetailsInput', function($compile) {
  return {
    restrict: 'AE', //attribute or element
    scope: {
      currentIndex: '=',
      rowTitle: '='
    },
    template: `<input type="text" class="form-control form-control-sm input-success" ng-model="custom">`,
    replace: true,
    transclude: false,
    controller: function($scope, $element){
      $scope.custom = 0;
    	$scope.$parent.editForm[$scope.currentIndex].paymentRows.push({
    		'title': $scope.rowTitle,
    		'amount': $scope.custom
    	})
    }
  };
});