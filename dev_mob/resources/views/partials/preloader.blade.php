<style type="text/css">
	#no_internet {
		display: none;
		position: fixed;
		z-index: 9999;
		top: 0;
		left: 0;
		width: 100%;
		height: 9000px;
		text-align: center;
		padding-top: 200px;
		background: #F7F7F7;
		opacity: 0.95;
	}
	#update_app {
		display: none;
		z-index: 99999;
		position: fixed;
		text-align: center;
		padding-top: 50px;
		background: #FFF;
		width: 100%;
		height: 9000px;
		box-shadow: inset 0 0 30px #DDD;
	}
</style>

<div id="update_app">
  <img src="{{ asset('assets/images/logo-light.png') }}" style="margin-bottom: 80px">
  <div style="width: 70%; margin: 0 auto">
  	<img src="{{ asset('assets/images/Phone_Update-64.png') }}" class="mb-2" width="64px">
  	<div class="mb-3">
	  	<b style="font-weight: 500;">
	  		Update the APP to avail new features like Full Screen Subject Videos and many more.<br>
		  	Click on Update Button to update the APP.
	  	</b>
	  </div>
  </div>
</div>
<div id="no_internet">
  <img src="{{ asset('assets/images/no_internet_connection.png') }}" width="100%">
</div>
<div class="preloader">
    <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
    </svg>
</div>
<div class="preloader" id="overlay" style="z-index: 20; opacity:0.9;">
    <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
    </svg>
</div>