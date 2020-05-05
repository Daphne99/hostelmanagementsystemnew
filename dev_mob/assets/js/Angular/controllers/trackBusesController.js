var CuteBrains = angular.module('trackBusesController', []);

CuteBrains.controller('trackBusesController', function(dataFactory,$rootScope,$scope,$sce) {
    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Track buses';
    });

    /* ----- trackBus ------ */

    $scope.gps_iframe_link = 'https://gps.cutebrains.com';

    $scope.trustSrc = function(src) {
		  return $sce.trustAsResourceUrl(src);
		}

    /* ----- trackBus ------ */
});