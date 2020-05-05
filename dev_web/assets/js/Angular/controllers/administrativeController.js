var CuteBrains = angular.module('administrativeController', []);

CuteBrains.controller('administrativeController', function( dataFactory, $rootScope, $scope, $http ) {
    $scope.views = {};
    $scope.form = {};
    $scope.title = "";
    $scope.target = "";
    $scope.countries = [];
    $scope.currencies = [];
    $scope.timezones = [];
    $scope.loadingIcon = false;
    $scope.states = [];
    $scope.cities = [];

    $scope.$on('$viewContentLoaded', function() {
        var currentUrl = window.location.href;
        var loc = new URL(currentUrl);
        var res = loc.hash.split("/");
        var target = res.pop();
        $scope.title = target == "settings" ? "School Settings" : ( target == "mobile" ? "Application setting" : ( target == "biometric" ? "Biometric setup" : "" ) );
        $scope.target = target;
        document.title = $('meta[name="site_title"]').attr('content') + ' | Administrative - ' + $scope.title;

        $scope.visitTarget( $scope.target );
    });

    $scope.visitTarget = function(target){
        if( target == "settings" ) $scope.loadSettings();
        if( target == "mobile" ) $scope.loadMobile();
        if( target == "biometric" ) $scope.loadBiometric();
    }

    $scope.loadSettings = function(){
        $scope.form = {};
        showHideLoad();
        dataFactory.httpRequest('index.php/administrative/settings').then(function(data) {
            showHideLoad(true);
            if( data.status == "success" )
            {
                $scope.form = data.settings;
                $scope.timezones = data.timezones;
                $scope.countries = data.countries;
                $scope.currencies = data.currencies;
                if( $scope.form.country != "" ){ $scope.loadStates(); }
                if( $scope.form.State != "" ) { $scope.loadCities(); }
                $scope.changeView('settings');
            } else { response = apiResponse(data,'remove'); }
        });
    }

    $scope.loadMobile = function(){
        $scope.form = {};
        showHideLoad();
        dataFactory.httpRequest('index.php/administrative/mobile').then(function(data) {
            showHideLoad(true);
            if( data.status == "success" )
            {
                $scope.form.apikey = data.apikey;
                $scope.changeView('mobile');
            } else { response = apiResponse(data,'remove'); }
        });
    }

    $scope.loadBiometric = function(){
        $scope.form = {};
        showHideLoad();
        dataFactory.httpRequest('index.php/administrative/biometric').then(function(data) {
            showHideLoad(true);
            if( data.status == "success" )
            {
                $scope.form.biometric = data.biometric;
                $scope.changeView('biometric');
            } else { response = apiResponse(data,'remove'); }
        });
    }

    $scope.loadStates = function(){
        var country = $scope.form.country;
        if( country != "" ) { $scope.states = $scope.countries[country].states; }
    }

    $scope.loadCities = function(){
        var code = $scope.form.country;
        var countryId = $scope.countries[code].id;
        let send_data = { state: $scope.form.State, country: countryId }
        showHideLoad();
        dataFactory.httpRequest('index.php/administrative/load_cities', 'GET', send_data).then(function(data) {
            showHideLoad(true);
            if( data.status == "success" )
            {
                $scope.cities = data.cities;
            } else { response = apiResponse(data,'remove'); }
        });
    }

    $scope.changeCurrency = function(){
        var currency_code = $scope.form.currency_code;
        if( currency_code != "" ) { $scope.form.currency_symbol = $scope.currencies[currency_code].symbol; }
    }

    $scope.saveSettings = function(){
        var model = {
            siteTitle: $scope.form.siteTitle,
            footer: $scope.form.footer,
            startDate: $scope.form.startDate,
            estabDate: $scope.form.estabDate,
            schoolMoto: $scope.form.schoolMoto,
            affilBy: $scope.form.affilBy,
            affilNo: $scope.form.affilNo,
            regisNo: $scope.form.regisNo,
            siteLogo: document.getElementById('siteLogo').files[0],
            board: $scope.form.board,
            aboutUs: $scope.form.aboutUs,
            systemEmail: $scope.form.systemEmail,
            country: $scope.form.country,
            state: $scope.form.State,
            city: $scope.form.City,
            district: $scope.form.District,
            address: $scope.form.address,
            pin: $scope.form.pin,
            mobile1: $scope.form.mobile1,
            mobile2: $scope.form.mobile2,
            landLine: $scope.form.landLine,
            faxNo: $scope.form.faxNo,
            timezone: $scope.form.timezone,
            currency_symbol: $scope.form.currency_symbol,
            currency_code: $scope.form.currency_code
        };
        var configs = {
            headers: { 'Content-Type': undefined },
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) { formData.append(key, value); });
                return formData;
            }
        };
        $scope.loadingIcon = true;
        $http.post('index.php/administrative/saveSettings', model, configs).then(function(data) {
            $scope.loadingIcon = false;
            let xhrResponse = data.data;
            if( xhrResponse.status == 'success' )
            {
                response = apiResponse(xhrResponse, 'add');
            } else { response = apiResponse(xhrResponse, 'remove'); }
        },function( error ){
            $scope.loadingIcon = false;
            let errorMsg = {
                status: "failed",
                title: "School Settings",
                message: "Error occurred while processing your request"
            }
            response = apiResponse(errorMsg, 'remove');
        });
    }

    $scope.saveMobile = function(){
        send_data = { apikey: $scope.form.apikey };
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/administrative/saveMobile', 'POST', send_data).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                response = apiResponse(data, 'add');
            } else response = apiResponse(data,'remove');
        });
    }

    $scope.saveBiometric = function(){
        send_data = { biometric: $scope.form.biometric };
        $scope.loadingIcon = true;
        dataFactory.httpRequest('index.php/administrative/saveBiometric', 'POST', send_data).then(function(data) {
            $scope.loadingIcon = false;
            if( data.status == "success" )
            {
                response = apiResponse(data, 'add');
            } else response = apiResponse(data,'remove');
        });
    }

    $scope.changeView = function(view){
        $scope.views.settings = false;
        $scope.views.mobile = false;
        $scope.views.biometric = false;
        $scope.views[view] = true;
    }
});