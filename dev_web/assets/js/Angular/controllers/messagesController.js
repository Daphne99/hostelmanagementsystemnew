var CuteBrains = angular.module('messagesController', []);

CuteBrains.controller('messagesController', function(dataFactory,$rootScope,$route,$scope,$location,$routeParams) {
    $scope.messages = {};
    $scope.message = {};
    $scope.messageDet = {};
    $scope.pageNumber = 1;
    $scope.totalItems = 0;
    $scope.views = {};
    $scope.isFirstLoaded = false;
    $scope.isLoading = false;
    $scope.selectedAll = false;
    $scope.repeatCheck = true;
    $scope.isParticipatesShown = false;
    $scope.form = {};
    $scope.messageBefore;
    $scope.messageAfter;
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.selectedThread = 0;
    $scope.selectedConversation = 0;
    $scope.messageId = 0;
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
    $scope.isSentFiltered = false;
    $scope.sentFilterPageNo = 1;

    $scope.converastionPageNumber = 1;
    $scope.isInboxFiltered = false;
    $scope.inboxFilterPageNo = 1;
    var currentMessageRefreshId;
    var currentConversationRefreshId;

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        if( $scope.isSentFiltered == true ) { doFilterSent( newPage ); }
        if( $scope.isInboxFiltered == true ) { doFilterInbox( newPage ); }
        else { getResultsPage(newPage); }
    };

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

    $scope.doFilterSent = function( pageNumber = 1 ){
        showHideLoad();
        $scope.sentFilterPageNo = pageNumber;
        dataFactory.httpRequest('index.php/messaging/listAll/' + $scope.sentFilterPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { responseData = apiResponse(data, 'remove'); }
            else if( data.status == "success" )
            {
                $scope.isFirstLoaded = false;
                $scope.isSentFiltered = data.filter;
                $scope.messages = data.messages;
                $scope.totalItems = data.totalItems;
            } else { responseData = apiResponse(data, 'remove'); }
        });
    }

    $scope.cancelSentFilter = function(){
        getResultsPage( $scope.pageNumber );
    }

    $scope.toogleParticipates = function(){
        $scope.isParticipatesShown = !$scope.isParticipatesShown;
    }

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Messages';
    });

    var currentMessagePull = function(){
        dataFactory.httpRequest('index.php/messaging/more/'+$scope.messageDet.fromId+'/'+$scope.messageDet.toId+'/'+$scope.messageAfter).then(function(data) {
            angular.forEach(data, function (item) {
                $scope.message.push(item);
                var newH = parseInt($("#chat-box").prop('scrollHeight')) + 100;
                $("#chat-box").slimScroll({ scrollTo: newH+'px' });
            });
            if($scope.message[$scope.message.length - 1]){
                $scope.messageAfter = $scope.message[$scope.message.length - 1].dateSent;
            }
        });
    };

    var currentConversationPull = function(){
        dataFactory.httpRequest('index.php/messaging/puller/'+$scope.messageDet.fromId+'/'+$scope.messageDet.toId+'/'+$scope.messageAfter).then(function(data) {
            angular.forEach(data, function (item) {
                $scope.message.push(item);
                var newH = parseInt($("#chat-box").prop('scrollHeight')) + 100;
                $("#chat-box").slimScroll({ scrollTo: newH+'px' });
            });
            if($scope.message[$scope.message.length - 1]){
                $scope.messageAfter = $scope.message[$scope.message.length - 1].dateSent;
            }
        });
    }

    $scope.openMessage = function(id, index, skip = null){
        if( $rootScope.can('messaging.View') )
        {
            $scope.form = {};
            if( skip == null ) { $scope.isLoading = true; }
            $scope.selectedThread = index;
            clearInterval( currentMessageRefreshId );
            clearInterval( currentConversationRefreshId );
            dataFactory.httpRequest('index.php/messaging/show/'+id).then(function(data) {
                // data = successOrError( data );
                if( data )
                {
                    $scope.messageId = id;
                    $scope.message = data.messages.reverse();
                    $scope.messageDet = data.messageDet;
                    if( data.type == "single" )
                    {
                        if($scope.message[0]) { $scope.messageBefore = $scope.message[0].dateSent; }
                        if($scope.message[$scope.message.length - 1]) { $scope.messageAfter = $scope.message[$scope.message.length - 1].dateSent; }
                        currentMessageRefreshId = setInterval(currentMessagePull, 600000);
                    }
                    else
                    {
                        //
                    }
                    $("#chat-box").slimScroll({ scrollTo: $("#chat-box").prop('scrollHeight')+'px' });
                    $scope.isFirstLoaded = true;
                    $scope.isLoading = false;
                }
            });
        }
    }

    $scope.loadOld = function(){
        dataFactory.httpRequest('index.php/messaging/more/'+ $scope.messageDet.fromId+'/' + $scope.messageDet.toId + '/' + $scope.messageBefore).then(function(data) {
            angular.forEach(data, function (item) {
                $scope.message.splice(0, 0,item);
            });
            if(data.length == 0){
                $('#loadOld').hide();
            }
            $scope.messageBefore = $scope.message[0].dateSent;
        });
    }

    $scope.replyMessage = function(){
        if($scope.form.reply != "" && typeof $scope.form.reply != "undefined"){
            $scope.form.disable = true;
            $scope.form.mid = $scope.messageDet.id;
            $scope.form.toId = $scope.messageDet.toId;
            $scope.form.type = $scope.messageDet.type;
            dataFactory.httpRequest('index.php/messaging/postReply', 'POST', {}, $scope.form).then(function(response) {
                if( response.status == "failed" ) { responseData = apiResponse(response, 'remove'); }
                else if( response.status == "success" )
                {
                    $scope.form = {};
                    $("#chat-box").slimScroll({ scrollTo: $("#chat-box").prop('scrollHeight')+'px' });
                    if( response.data.type == 'single' )
                    {
                        $scope.openMessage( $scope.messageId, $scope.selectedThread, 'true' );
                        $scope.messages[$scope.selectedThread]['lastMiniMessage'] = response.data.miniMessage;
                        $scope.messages[$scope.selectedThread]['moments'] = response.data.timeNow;
                    }
                    else if( response.data.type == 'multiple' )
                    {
                        $scope.openMessage( $scope.messageId, $scope.selectedThread, 'true' );
                        $scope.messages[$scope.selectedThread]['lastMiniMessage'] = response.data.miniMessage;
                        $scope.messages[$scope.selectedThread]['moments'] = response.data.timeNow;
                    }
                } else { responseData = apiResponse(response, 'remove'); }
            });
        }
    }

    function getResultsPage(pageNumber) {
        showHideLoad();
        $scope.pageNumber = pageNumber;
        dataFactory.httpRequest('index.php/messaging/listAll/'+pageNumber).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { responseData = apiResponse(data, 'remove'); }
            else if( data.status == "success" )
            {
                $scope.isFirstLoaded = false;
                $scope.isSentFiltered = data.filter;
                $scope.messages = data.messages;
                $scope.totalItems = data.totalItems;
                setTimeout(function(){
                    $('select[multiple]').selectpicker('destroy');
                    $('select[multiple]').selectpicker();
                    $('.sent-data').mCustomScrollbar({ theme: 'minimal-dark' });
                }, 300);
            } else { responseData = apiResponse(data, 'remove'); }
        });
    }

    $scope.checkAll = function(){
        $scope.selectedAll = !$scope.selectedAll;
        angular.forEach($scope.messages, function (item) {
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

    function getConversationPage (pageNumber = 1){
        showHideLoad();
        $scope.converastionPageNumber = pageNumber;
        dataFactory.httpRequest('index.php/messaging/listAllConversations/' + pageNumber).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { responseData = apiResponse(data, 'remove'); }
            else if( data.status == "success" )
            {
                $scope.isFirstLoaded = false;
                $scope.isInboxFiltered = data.filter;
                $scope.messages = data.messages;
                $scope.totalItems = data.totalItems;
                setTimeout(function(){
                    $('select[multiple]').selectpicker('destroy');
                    $('select[multiple]').selectpicker();
                    $('.sent-data').mCustomScrollbar({ theme: 'minimal-dark' });
                }, 300);
            } else { responseData = apiResponse(data, 'remove'); }
        });
    }

    $scope.doFilterInbox = function( pageNumber = 1 ){
        showHideLoad();
        $scope.inboxFilterPageNo = pageNumber;
        dataFactory.httpRequest('index.php/messaging/listAllConversations/' + $scope.inboxFilterPageNo, 'POST', {}, $scope.filterForm).then(function(data) {
            showHideLoad(true);
            if( data.status == "failed" ) { responseData = apiResponse(data, 'remove'); }
            else if( data.status == "success" )
            {
                $scope.isFirstLoaded = false;
                $scope.isInboxFiltered = data.filter;
                $scope.messages = data.messages;
                $scope.totalItems = data.totalItems;
                $scope.justChangeView("inbox");
                setTimeout(function(){
                    $('select[multiple]').selectpicker('destroy');
                    $('select[multiple]').selectpicker();
                    $('.sent-data').mCustomScrollbar({ theme: 'minimal-dark' });
                }, 300);
            } else { responseData = apiResponse(data, 'remove'); }
        });
    }

    $scope.cancelInboxFilter = function(){
        getConversationPage( $scope.converastionPageNumber );
    }

    $scope.readConversation = function(id, index, skip = null){
        $scope.form = {};
        if( skip == null ) { $scope.isLoading = true; }
        $scope.selectedConversation = index;
        clearInterval( currentMessageRefreshId );
        clearInterval( currentConversationRefreshId );
        dataFactory.httpRequest('index.php/messaging/read/'+id).then(function(data) {
            // data = successOrError( data );
            if( data )
            {
                if( data.status == "failed" ) { responseData = apiResponse(data, 'remove'); }
                else if( data.status == "success" )
                {
                    $scope.messageId = id;
                    $scope.message = data.messages.reverse();
                    $scope.messageDet = data.messageDet;
                    if($scope.message[0]) { $scope.messageBefore = $scope.message[0].dateSent; }
                    if($scope.message[$scope.message.length - 1]) { $scope.messageAfter = $scope.message[$scope.message.length - 1].dateSent; }
                    currentConversationRefreshId = setInterval(currentConversationPull, 600000);
                    $("#chat-box").slimScroll({ scrollTo: $("#chat-box").prop('scrollHeight')+'px' });
                    $scope.isFirstLoaded = true;
                    $scope.isLoading = false;
                } else { responseData = apiResponse(data, 'remove'); }
            }
        });
    }

    $scope.replyConvarsation = function(){
        if($scope.form.reply != "" && typeof $scope.form.reply != "undefined"){
            $scope.form.disable = true;
            $scope.form.mid = $scope.messageDet.id;
            $scope.form.toId = $scope.messageDet.toId;
            dataFactory.httpRequest('index.php/messaging/convarsationReply', 'POST', {}, $scope.form).then(function(response) {
                if( response.status == "failed" ) { responseData = apiResponse(response, 'remove'); }
                else if( response.status == "success" )
                {
                    $scope.form = {};
                    $("#chat-box").slimScroll({ scrollTo: $("#chat-box").prop('scrollHeight')+'px' });
                    $scope.readConversation( $scope.messageId, $scope.selectedConversation, 'true' );
                    $scope.messages[$scope.selectedConversation]['lastMiniMessage'] = response.data.miniMessage;
                    $scope.messages[$scope.selectedConversation]['moments'] = response.data.timeNow;
                } else { responseData = apiResponse(response, 'remove'); }
            });
        }
    }

    $scope.openThisConversation = function (recipientId){
        if( $rootScope.can('messaging.View') )
        {
            showHideLoad();
            let send_data = {
                recipient: recipientId
            }
            dataFactory.httpRequest('index.php/messaging/listAllConversations/' + 1, 'POST', {}, send_data).then(function(data) {
                showHideLoad(true);
                if( data.status == "failed" ) { responseData = apiResponse(data, 'remove'); }
                else if( data.status == "success" )
                {
                    $scope.isFirstLoaded = false;
                    $scope.isInboxFiltered = data.filter;
                    $scope.messages = data.messages;
                    $scope.totalItems = data.totalItems;
                    $scope.readConversation( $scope.messages[0].id, 0 );
                    $scope.justChangeView("inbox");
                    setTimeout(function(){
                        $('select[multiple]').selectpicker('destroy');
                        $('select[multiple]').selectpicker();
                        $('.sent-data').mCustomScrollbar({ theme: 'minimal-dark' });
                    }, 300);
                } else { responseData = apiResponse(data, 'remove'); }
                
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "sent" || view == "inbox") { $scope.form = {}; $scope.filterForm = { type1: "", type2: "", class : [], section : [] }; }
        if(view == "sent")
        {
            clearInterval( currentMessageRefreshId );
            clearInterval( currentConversationRefreshId );
            getResultsPage(1);
        }
        else if(view == "inbox")
        {
            clearInterval( currentMessageRefreshId );
            clearInterval( currentConversationRefreshId );
            getConversationPage(1);
        }
        $scope.justChangeView(view);
    }

    $scope.justChangeView = function(view)
    {
        $scope.views.inbox = false;
        $scope.views.sent = false;
        $scope.views[view] = true;
    }

    $scope.changeView('sent');
});