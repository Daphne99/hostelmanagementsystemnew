var CuteBrains = angular.module('userProfileController', []);

CuteBrains.controller('userProfileController', function(dataFactory,$rootScope,$route,$scope,$location,$routeParams) {
    $scope.profile_data = {};
    $scope.andriod_version = 0;

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | My profile';
    });

    $scope.getProfileData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/accountSettings/get-user-profile-data').then(function(data) {
            $scope.profile_data = data.profile_data;
            showHideLoad(true);
        });
    }
    $scope.getProfileData();

    $scope.readAndriodVersion = function() {
    	if(typeof Android !== 'undefined') {
    		try {
				  $scope.andriod_version = Android.getAndroidVersion()
				}
				catch(err) {
				  $scope.andriod_version = '5.1';
				}
    	}
    }
    $scope.readAndriodVersion();

});