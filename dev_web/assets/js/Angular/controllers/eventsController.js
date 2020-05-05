var CuteBrains = angular.module('eventsController', []);

CuteBrains.controller('eventsController', function( dataFactory, $rootScope, $route, $scope, $location, $routeParams ) {
    $scope.events = {};
    $scope.eventDet = {};
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
    $scope.isEventsFiltered = false;
    $scope.eventsFilteredPageNo = 1;
    $scope.myEventsPageNumber = 1;
    $scope.isMyEventsFiltered = false;
    $scope.myEventsFilteredPageNo = 1;
    $scope.myEvents = false;

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Events';
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
        dataFactory.httpRequest('index.php/eventous/listEvents/' + pageNumber).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isEventsFiltered = data.filter;
            $scope.events = data.events;
            $scope.totalItems = data.totalItems;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
                $('.sent-data').mCustomScrollbar({ theme: 'minimal-dark' });
            }, 300);
            showHideLoad(true);
        });
    }

    $scope.openEvent = function(id, index){
        $scope.isLoading = true;
        dataFactory.httpRequest('index.php/eventous/show/'+id).then(function(data) {
            $scope.isLoading = false;
            if( data.status == "success" )
            {
                $scope.isFirstLoaded = true;
                $scope.eventDet = data.eventDet;
                $scope.events[index].isRead = true;
            } else if( data.status == "failed" ) { response = apiResponse(data, 'remove'); }
            else { response = apiResponse({ status: "failed", title: "Read Event", message: "Error occurred while processing your request" }, 'remove'); }
        });
    }
    
    $scope.doFilterEvents = function( pageNumber = 1 ){
        showHideLoad();
        $scope.eventsFilteredPageNo = pageNumber;
        dataFactory.httpRequest('index.php/eventous/listEvents/' + $scope.eventsFilteredPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isEventsFiltered = data.filter;
            $scope.events = data.events;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.doFilterMyEvents = function( pageNumber = 1 ){
        $scope.filterForm.myEvents = true;
        $scope.myEventsFilteredPageNo = pageNumber;
        dataFactory.httpRequest('index.php/eventous/listEvents/' + $scope.myEventsFilteredPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isEventsFiltered = data.filter;
            $scope.events = data.events;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.cancelEventsFilter = function(){
        if( $scope.views['events'] == true ) { getResultsPage( $scope.pageNumber ); }
        else if( $scope.views['myEvents'] ) { $scope.getMyResultsPage( $scope.pageNumber ); }
    }

    $scope.getMyResultsPage = function( pageNumber = 1 ){
        showHideLoad();
        $scope.myEventsFilteredPageNo = pageNumber;
        $scope.filterForm.myEvents = true;
        dataFactory.httpRequest('index.php/eventous/listEvents/' + $scope.myEventsFilteredPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            $scope.isFirstLoaded = false;
            $scope.isEventsFiltered = data.filter;
            $scope.events = data.events;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.openGuests = function(){
        $scope.modalTitle = "Event Guest List";
        $scope.modalClass = "modal-lg";
        $scope.guestsModal = !$scope.guestsModal;
    }

    $scope.toogleParticipates = function(){
        $scope.isParticipatesShown = !$scope.isParticipatesShown;
    }

    $scope.checkAll = function(){
        $scope.selectedAll = !$scope.selectedAll;
        angular.forEach($scope.events, function (item) {
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
            "Are you sure you want to remove your copy from the conversation ? note that this action cannot be undone",
            function(){
                $scope.form.items = [];
                var len = $scope.messages.length
                while (len--) {
                    if($scope.messages[len].selected){
                        $scope.form.items.push($scope.messages[len].id);
                        $scope.messages.splice(len,1);
                    }
                }
                if( type == 'sent' )
                {
                    dataFactory.httpRequest('index.php/messaging/delete',"POST",{},$scope.form).then(function(data) {
                        response = apiResponse(data,'remove');
                    });
                }
                else if( type == 'Conversation' )
                {
                    dataFactory.httpRequest('index.php/messaging/remove',"POST",{},$scope.form).then(function(data) {
                        response = apiResponse(data,'remove');
                    });
                }
            },
            function(){}
        ).set('labels', {ok: "<i class='fa fa-exclamation-triangle'></i> Yes Remove it", cancel: "Cancel"});
    }

    $scope.changeView = function(view){
        if(view == "events")
        {
            $scope.form = {}; $scope.filterForm = { type1: "", type2: "", class : [], section : [], startDate: "", endDate: "" };
            getResultsPage(1);
        }
        else if(view == "myEvents")
        {
            $scope.form = {}; $scope.filterForm = { type1: "", type2: "", class : [], section : [], startDate: "", endDate: "", myEvents: true };
            $scope.getMyResultsPage(1);
        }
        $scope.justChangeView(view);
    }

    $scope.justChangeView = function(view)
    {
        $scope.views.events = false;
        $scope.views.myEvents = false;
        $scope.views[view] = true;
    }

    $scope.changeView('events');

    $scope.pageChanged = function(newPage) {
        if( $scope.isEventsFiltered == true ) { $scope.doFilterEvents( newPage ); }
        else if( $scope.isMyEventsFiltered == true ) { $scope.doFilterMyEvents( newPage ); }
        else
        {
            if( $scope.views['events'] == true ) { getResultsPage( newPage ); }
            else if( $scope.views['myEvents'] ) { $scope.getMyResultsPage( newPage ); }
        }
    };
});