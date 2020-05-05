<script src="{{URL::asset('assets/plugins/jquery/jQuery-2.1.4.min.js')}}"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="{{URL::asset('assets/plugins/bootstrap/js/tether.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/bootstrap/js/popper.min.js')}}" crossorigin="anonymous"></script>
<script src="{{URL::asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>

<!-- slimscrollbar scrollbar JavaScript -->
<script src="{{URL::asset('assets/js/jquery.slimscroll.js')}}"></script>
<!--Wave Effects -->
<script src="{{URL::asset('assets/js/waves.js')}}"></script>
<!--Menu sidebar -->
<script src="{{URL::asset('assets/js/sidebarmenu.js')}}"></script>
<!--stickey kit -->
<script src="{{URL::asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js')}}"></script>
<!--Custom JavaScript -->
<script src="{{URL::asset('assets/js/custom.js')}}"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<script src="{{URL::asset('assets/plugins/alertify/alertify.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/sweetalert/sweetalert2.9.min.js')}}"></script>

<!-- ============================================================== -->
<!-- Style switcher -->
<!-- ============================================================== -->
<script src="{{URL::asset('assets/js/CuteBrains.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/intlTelInput.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/ckeditor/ckeditor.js')}}"></script>
<script src="{{URL::asset('assets/js/summernote.js')}}"></script>
<script src="{{URL::asset('assets/plugins/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{URL::asset('assets/js/jquery.colorbox-min.js')}}"></script>
<script src="{{URL::asset('assets/js/moment.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/humanize-duration/humanize-duration.js')}}"></script>
<script src="{{URL::asset('assets/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/typeahead/typeahead.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/scrollbar/scrollbar.js')}}"></script>

<script type="text/javascript" src="{{URL::asset('assets/plugins/global-calendars/jquery.plugin.min.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('assets/plugins/global-calendars/jquery.calendars.all.js')}}"></script>
<?php if($panelInit->settingsArray['gcalendar'] != "gregorian"){ ?>
    <?php
    $gcalendar = $panelInit->settingsArray['gcalendar'];
    if($gcalendar == "ethiopic"){
        $gcalendar = "ethiopian";
    }

    ?>
    <script type="text/javascript" src="{{URL::asset('assets/plugins/global-calendars/jquery.calendars.'.$gcalendar.'.min.js')}}"></script>
<?php } ?>

<?php
	if(get_server_info()['server_type'] == 'local') {
		srand(mt_rand(1, 9999));
		$random_num = '?v=' . mt_rand(10000, 99999);
	} else {
		$random_num = '?v=001';
	}
?>

<script src="{{URL::asset('assets/js/Angular/angular.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/ng-file-upload/ng-file-upload-shim.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/ng-file-upload/ng-file-upload.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/AngularModules.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/js/Angular/app.js' . $random_num )}}"></script>
<script src="{{URL::asset('assets/js/Angular/routes.js?v='. mt_rand(10000, 99999))}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/plugins/ui-slider/ui-bootstrap-tpls.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('assets/plugins/ui-slider/rzslider.js')}}" type="text/javascript"></script>
@include('partials.angular_controllers')
<?php if( isset($panelInit->settingsArray['gTrackId']) AND $panelInit->settingsArray['gTrackId'] != "" ): ?>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '<?php echo $panelInit->settingsArray['gTrackId']; ?>', 'auto');
        ga('send', 'pageview');
    </script>
<?php endif; ?>

<script src="{{URL::asset('assets/plugins/jQuery.ptTimeSelect-0.8/src/jquery.ptTimeSelect.js')}}"></script>

<script type="text/javascript">
	$(document).ready(function(){
		if (typeof Android !== 'undefined') {
			Android.setToken("{{ \App\Models2\User::getUserAuhtToken() }}");
		}
	});
</script>

<!-- Multi bootstrap select Include the plugin's CSS and JS: -->
<script src="{{URL::asset('assets/plugins/bootstrap-select/bootstrap-select.min.js')}}"></script>
