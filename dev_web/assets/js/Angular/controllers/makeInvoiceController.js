var CuteBrains = angular.module('makeInvoiceController', []);

CuteBrains.controller('makeInvoiceController', function( dataFactory, $scope, $http, $sce, $rootScope, $route ) {
    $scope.loadingIcon = false;
    $scope.searchClasses = {};
    $scope.searchSections = {};
    $scope.payment_methods = {};
    $scope.pageNumber = 1;
    $scope.filterForm = { class: "", section: "", mode: "", status: "", from: "", to: "", text: "" };
    $scope.invoices = {};
    $scope.student_types = {};
    $scope.paidInvoices = {};
    $scope.students = {};
    $scope.types = {};
    $scope.classes = {};
    $scope.sections = [];
    $scope.views = {};
    $scope.form = {};
    $scope.importForm = {};
    $scope.importForm.review_import = true;
    $scope.invoice = {};
    $scope.payDetails = {};
    $scope.searchInput = {};
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.server_info = JSON.parse($rootScope.dashboardData.server_info);
    $scope.cur_page = 1;
    $scope.isUploading = false;
    $scope.isAllocation = false;

    // payment fee type
    $scope.$on('$viewContentLoaded', function() {
        document.title = $('meta[name="site_title"]').attr('content') + ' | Add New Invoice';
        $scope.preLoadInvoicesData();
    });

    $scope.preLoadInvoicesData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/preLoadInvoice').then(function(data) {
            $scope.classes = data.upClasses;
            $scope.types = data.types;
            $scope.changeView('form');
            setTimeout(function(){
    			$('select[multiple]').selectpicker('destroy');
    			$('select[multiple]').selectpicker();
    		}, 300);
            showHideLoad(true);
        });
    }

    $scope.changeClasses = function(){
        $scope.sections = []; let sections = [];
        angular.forEach($scope.importForm.classes, function(value, key) {
            let innerSections = $scope.classes[value].sections;
            angular.forEach(innerSections, function(section, index) {
                sections.push( section );
            });
        });
        $scope.sections = sections;
        setTimeout(function(){
            $('select[multiple]').selectpicker('destroy');
            $('select[multiple]').selectpicker();
        }, 300);
    }

    $scope.addPaymentRow = function(){
        if( typeof( $scope.importForm.details ) == "undefined") { $scope.importForm.details = []; }
        $scope.importForm.details.push({'title':'','amount':''});
    }

    $scope.recalcTotalAmount = function(){
        $scope.importForm.amount = 0;
        angular.forEach($scope.importForm.details, function(value, key) {
            if( value.amount != "" && $.isNumeric( value.amount ) )
            {
                $scope.importForm.amount = parseInt($scope.importForm.amount) + parseInt(value.amount);
            }
        });
    }

    $scope.removeRow = function(row,index){
        $scope.importForm.details.splice(index,1);
        $scope.recalcTotalAmount();
    }

    $scope.doCreateInvoice = function(){
        $scope.isAllocation = true;
        let send_data = {
            student_types: $scope.importForm.type,
            classes: $scope.importForm.classes,
            sections: $scope.importForm.sections,
            feeTitle: $scope.importForm.feeTitle,
            fine: $scope.importForm.fine,
            feeDate: $scope.importForm.feeDate,
            dueDate: $scope.importForm.dueDate,
            details: $scope.importForm.details,
            amount: $scope.importForm.amount,
            description: $scope.importForm.description
        };
        dataFactory.httpRequest('index.php/invoices/createManualFee', 'POST', {}, send_data).then(function(data) {
            $scope.isAllocation = false;
            if( data.status == "failed" ) { apiResponse(data, 'remove'); }
            else if( data.status == "success" )
            {
                apiResponse(data, 'edit');
                $scope.form = {};
                $scope.importForm = {};
                $scope.importForm.review_import = true;
                $scope.preLoadInvoicesData();
            }
            else { apiResponse(data, 'remove'); }
        });
    }

    $scope.changeView = function(view){
        $scope.views.form = false;
        $scope.views.review_import = false;
        $scope.views[view] = true;
    }

    // Import Fees -------------------------------------
    $scope.doUploadImportedFees = function(){
        var model = {
            student_types: $scope.importForm.type,
            classes: $scope.importForm.classes,
            sections: $scope.importForm.sections,
            review_import: $scope.importForm.review_import,
            excelcsv: document.getElementById('excelcsv').files[0]
        }
        var configs = {
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) { formData.append(key, value); });
                return formData;
            }
        };
        $scope.isUploading = true;
        $http.post('index.php/invoices/importExcelFile', model, configs).then(function(data) {
            $scope.isUploading = false;
            let xhrResponse = data.data;
            if( xhrResponse.preview_items == true )
            {
                $scope.importedItemsPreview = xhrResponse.previewItems;
                $scope.changeView('review_import');
            }
            else if ( xhrResponse.status == 'failed' )
            {
                response = apiResponse(xhrResponse, 'remove');
                $scope.changeView('form');
            }
            else if ( xhrResponse.status == 'success' )
            {
                response = apiResponse(xhrResponse, 'edit');
                $scope.changeView('form');
                $scope.importForm = {};
                $scope.importForm.review_import = false;
            }
        });
    }

    $scope.publishImportedFees = function() {
        $scope.importForm.reviewImportStatus = false;
        $scope.importForm.review_import = false;
    	$scope.changeView('form');
    	setTimeout(function(){
    		$('#publishImportFees').trigger('click');
    	}, 200)
    }
    // end import fees ---------------------------------
});