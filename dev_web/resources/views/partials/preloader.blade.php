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
		padding-top: 50px;
		background: #F7F7F7;
		opacity: 0.9;
	}
</style>

<div id="no_internet">
  <img src="{{ asset('assets/images/no_internet_connection.png') }}">
</div>
<div class="preloader">
  <svg class="circular" viewBox="25 25 50 50">
      <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
  </svg>
</div>
<div class="preloader" id="overlay" style="opacity:0.9; z-index: 20">
  <svg class="circular" viewBox="25 25 50 50">
      <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
  </svg>
</div>