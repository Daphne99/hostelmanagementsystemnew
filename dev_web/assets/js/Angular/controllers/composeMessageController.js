var CuteBrains = angular.module('composeMessageController', []);

CuteBrains.controller('composeMessageController', function(dataFactory,$rootScope,$route,$scope,$location,$routeParams) {
    $scope.messages = {};
    $scope.message = {};
    $scope.messageDet = {};
    $scope.totalItems = 0;
    $scope.views = {};
    $scope.selectedAll = false;
    $scope.repeatCheck = true;
    $scope.form = {};
    $scope.messageBefore;
    $scope.messageAfter;
    $scope.userRole = $rootScope.dashboardData.role;
    var routeData = $route.current;
    var currentMessageRefreshId;
    var messageId;

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        getResultsPage(newPage);
    };

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Compose Message';
    });


    function getResultsPage(pageNumber) {
        showHideLoad();
        dataFactory.httpRequest('index.php/messages/listAll/'+pageNumber).then(function(data) {
            $scope.messages = data.messages;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "create"){
            $scope.form = {};
            clearInterval(currentMessageRefreshId);
            getResultsPage(1);
        }
        $scope.views.create = false;
        $scope.views[view] = true;
    }

    $scope.changeView('create');
});