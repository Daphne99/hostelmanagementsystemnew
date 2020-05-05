var CuteBrains = angular.module('noticesController', []);

CuteBrains.controller('noticesController', function( dataFactory, $rootScope, $route, $scope, $location, $routeParams, $sce, $http ) {
    $scope.notices = {};
    $scope.noticeDet = {};
    $scope.pageNumber = 1;
    $scope.totalItems = 0;
    $scope.views = {};
    $scope.isFirstLoaded = false;
    $scope.isLoading = false;
    $scope.selectedAll = false;
    $scope.form = {};
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.selectedThread = 0;
    $scope.options = {};
    $scope.classes = {};
    $scope.sections = {};
    $scope.roles = {};
    $scope.academic = {};
    $scope.filterForm = {};
    $scope.filterForm.type1 = "";
    $scope.filterForm.type2 = "";
    $scope.filterForm.class = [];
    $scope.filterForm.section = [];
    $scope.filterForm.startDate = "";
    $scope.filterForm.endDate = "";
    $scope.updateForm = {};
    $scope.isNoticesFiltered = false;
    $scope.noticesFilteredPageNo = 1;
    $scope.myNoticesPageNumber = 1;
    $scope.isMyNoticesFiltered = false;
    $scope.myNoticesFilteredPageNo = 1;
    $scope.myNotices = false;

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Notices';
    });

    dataFactory.httpRequest('index.php/messaging/preLoad').then(function(data) {
        $scope.options = data.type;
        $scope.classes = data.classes;
        $scope.roles = data.roles;
        $scope.academic = data.academic;
        setTimeout(function(){
            $('select[multiple]').selectpicker('destroy');
            $('select[multiple]').selectpicker();
        }, 300);
    });

    $scope.changeClass = function(){
        $scope.sections = []; let sections = [];
        angular.forEach($scope.filterForm.class, function(value, key) {
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

    $scope.enableDisableClass = function(){
        setTimeout(function(){
            $('select[multiple]').selectpicker('destroy');
            $('select[multiple]').selectpicker();
        }, 300);
    }

    function getResultsPage(pageNumber) {
        showHideLoad();
        $scope.pageNumber = pageNumber;
        dataFactory.httpRequest('index.php/notices/listNotices/' + pageNumber).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isNoticesFiltered = data.filter;
            $scope.notices = data.notices;
            $scope.totalItems = data.totalItems;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
                $('.sent-data').mCustomScrollbar({ theme: 'minimal-dark' });
            }, 300);
            showHideLoad(true);
        });
    }

    $scope.openNotice = function(id, index){
        $scope.isLoading = true;
        dataFactory.httpRequest('index.php/notices/show/'+id).then(function(data) {
            $scope.isLoading = false;
            if( data.status == "success" )
            {
                $scope.isFirstLoaded = true;
                $scope.noticeDet = data.noticeDet;
                $scope.notices[index].isRead = true;
                $scope.noticeDet.newsText = $sce.trustAsHtml( $scope.noticeDet.newsText );
                $scope.noticeDet.targetIndex = index;
            } else if( data.status == "failed" ) { response = apiResponse(data, 'remove'); }
            else { response = apiResponse({ status: "failed", title: "Read Notice", message: "Error occurred while processing your request" }, 'remove'); }
        });
    }
    
    $scope.doFilterNotices = function( pageNumber = 1 ){
        showHideLoad();
        $scope.noticesFilteredPageNo = pageNumber;
        dataFactory.httpRequest('index.php/notices/listNotices/' + $scope.noticesFilteredPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isNoticesFiltered = data.filter;
            $scope.notices = data.notices;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.doFilterMyNotices = function( pageNumber = 1 ){
        $scope.filterForm.myNotices = true;
        $scope.myNoticesFilteredPageNo = pageNumber;
        dataFactory.httpRequest('index.php/notices/listNotices/' + $scope.myNoticesFilteredPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isNoticesFiltered = data.filter;
            $scope.notices = data.notices;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.cancelNoticesFilter = function(){
        if( $scope.views['notices'] == true ) { getResultsPage( $scope.pageNumber ); }
        else if( $scope.views['myNotices'] ) { $scope.getMyResultsPage( $scope.pageNumber ); }
    }

    $scope.getMyResultsPage = function( pageNumber = 1 ){
        showHideLoad();
        $scope.myNoticesFilteredPageNo = pageNumber;
        $scope.filterForm.myNotices = true;
        dataFactory.httpRequest('index.php/notices/listNotices/' + $scope.myNoticesFilteredPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isNoticesFiltered = data.filter;
            $scope.notices = data.notices;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.openGuests = function(){
        $scope.modalTitle = "Notice members List";
        $scope.modalClass = "modal-lg";
        $scope.guestsModal = !$scope.guestsModal;
    }

    $scope.toogleParticipates = function(){
        $scope.isParticipatesShown = !$scope.isParticipatesShown;
    }

    $scope.checkAll = function(){
        $scope.selectedAll = !$scope.selectedAll;
        angular.forEach($scope.notices, function (item) {
            item.selected = $scope.selectedAll;
        });
    }

    $scope.markDelete = function(type){
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            "Are you sure you want to remove selected notices ?",
            function(){
                $scope.form.items = [];
                var len = $scope.notices.length;
                while (len--) { if($scope.notices[len].selected) { $scope.form.items.push($scope.notices[len].id); } }
                dataFactory.httpRequest('index.php/notices/multipleDelete', "POST", {}, $scope.form).then(function(data) {
                    if( data.status == "success" )
                    {
                        $scope.isFirstLoaded = false;
                        response = apiResponse(data,'remove');
                        var len = $scope.notices.length;
                        while( len-- ) { if($scope.notices[len].selected) { $scope.notices.splice(len,1); } }
                    } else if( data.status == "failed" ) { response = apiResponse(data, 'remove'); }
                    else { response = apiResponse({ status: "failed", title: "Read Notice", message: "Error occurred while processing your request" }, 'remove'); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-exclamation-triangle'></i> Yes Remove it", cancel: "Cancel"});
    }

    $scope.changeView = function(view){
        if(view == "notices")
        {
            $scope.form = {}; $scope.filterForm = { type1: "", type2: "", class : [], section : [], startDate: "", endDate: "" };
            getResultsPage(1);
        }
        else if(view == "myNotices")
        {
            $scope.form = {}; $scope.filterForm = { type1: "", type2: "", class : [], section : [], startDate: "", endDate: "", myNotices: true };
            $scope.getMyResultsPage(1);
        }
        $scope.justChangeView(view);
    }

    $scope.justChangeView = function(view)
    {
        $scope.views.notices = false;
        $scope.views.myNotices = false;
        $scope.views[view] = true;
    }

    $scope.editNotice = function( id, index )
    {
        $scope.updateForm.noticId = id;
        $scope.updateForm.targetIndex = index;
        $scope.updateForm.noticeTitle = $scope.noticeDet.newsTitle;
        $scope.updateForm.noticeContent = $scope.noticeDet.desc;
        $scope.updateForm.noticeDate = $scope.noticeDet.date;
        $scope.updateForm.imageStatus = $scope.noticeDet.newsImage == "default.png" ? 'false' : 'true';
        $scope.modalTitle = "Edit Notice";
        $scope.modalClass = "modal-lg";
        $scope.noticeModal = !$scope.noticeModal;
    }

    $scope.removeCurrentImage = function(){
        $scope.updateForm.imageStatus = 'false';
    }

    $scope.saveNotice = function(){
        var index = $scope.updateForm.targetIndex;
        var model = {
            notice_id: $scope.updateForm.noticId,
            index: $scope.updateForm.targetIndex,
            title: $scope.updateForm.noticeTitle,
            description: $scope.updateForm.noticeContent ? $scope.updateForm.noticeContent : '',
            date: $scope.updateForm.noticeDate ? $scope.updateForm.noticeDate : '',
            image: document.getElementById('noticeImage').files[0],
            imageStatus: $scope.updateForm.imageStatus == 'true' ? "true" : "false"
        }
        var configs = {
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) { formData.append(key, value); });
                return formData;
            }
        };
        $scope.isLoading = true;
        $http.post('index.php/notices/updateNotice', model, configs).then(
            function(data) {
                let xhrResponse = data.data;
                $scope.isLoading = false;
                if( xhrResponse.status == 'success' )
                {
                    response = apiResponse(xhrResponse, 'edit');
                    $scope.noticeModal = !$scope.noticeModal;
                    $scope.updateForm = {};
                    let updatedData = xhrResponse.data;
                    $scope.noticeDet.newsTitle = updatedData.title;
                    $scope.noticeDet.newsDate = updatedData.newsDate;
                    $scope.noticeDet.until = updatedData.until;
                    $scope.noticeDet.date = updatedData.date;
                    $scope.noticeDet.newsText = $sce.trustAsHtml( updatedData.desc );
                    $scope.noticeDet.newsImage = updatedData.newsImage;
                    $scope.notices[index].name = updatedData.name;
                    $scope.notices[index].newsImage = updatedData.newsImage;
                } else { response = apiResponse(xhrResponse, 'remove'); }
            },function( error ){
                $scope.isLoading = false;
                let errorMsg = { status: "failed", title: "Update notice", message: "Error occurred while processing your request" }
                response = apiResponse(errorMsg, 'remove');
            }
        );
    }

    $scope.removeNotice = function( id, index )
    {
        alertify.defaults.transition = 'zoom';
        alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
        alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
        alertify.defaults.theme.input = 'form-control';
        alertify.confirm(
            'Confirm deletion',
            "Are you sure you want to remove this notice ?",
            function(){
                let send_data = { notice_id: id };
                dataFactory.httpRequest('index.php/notices/removeNotice', "POST", {}, send_data).then(function(data) {
                    if( data.status == "success" )
                    {
                        response = apiResponse(data,'remove');
                        $scope.notices.splice(index, 1);
                        $scope.isFirstLoaded = false;
                    } else if( data.status == "failed" ) { response = apiResponse(data, 'remove'); }
                    else { response = apiResponse({ status: "failed", title: "Read Notice", message: "Error occurred while processing your request" }, 'remove'); }
                });
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-check'></i> Yes Remove it", cancel: "Cancel"});
    }

    $scope.changeView('notices');

    $scope.pageChanged = function(newPage) {
        if( $scope.isNoticesFiltered == true ) { $scope.doFilterNotices( newPage ); }
        else if( $scope.isMyNoticesFiltered == true ) { $scope.doFilterMyNotices( newPage ); }
        else
        {
            if( $scope.views['notices'] == true ) { getResultsPage( newPage ); }
            else if( $scope.views['myNotices'] ) { $scope.getMyResultsPage( newPage ); }
        }
    };
});