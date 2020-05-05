<style type="text/css">
	.top-menu > .row { margin-top: 15px; margin-left: 15px; }
	.top-menu .menu-collection { display: inline-table; }
	.top-menu .btn-group > a { padding: 10px 11px; border-radius: 0; border-bottom: 0; }
	.top-menu .btn-group > a > span { display: block; margin-top: 5px; font-size: 12px; }
	.top-menu .btn-group > a > img { width: 28px; }
	.top-menu .btn-group > a .mdi { font-size: 22px; display: block; }
	.top-menu .btn-group > a .fa { font-size: 20px; margin: 6px auto; display: block; }
	#topMenu { position: fixed; top: 45px; display: block; transition: top 0.3s; z-index: 99; margin-bottom: 50px; width: 100%; text-align: center; background: rgba(255,255,255,0.9); padding: 0px; margin-top: -20px; padding-top: 0; border-bottom: 1px solid #EEE; box-shadow: 1px 1px 15px #DDD; }
	.topMainMenuItem { width: 62px !important; }
	.mainMenuTitileSpan { font-size: x-small !important; font-weight: 500 !important; text-overflow: ellipsis; width: 48px; white-space: nowrap; overflow: hidden; text-align: center !important; }
</style>
<div class="top-menu" id="topMenu">
	<div class="row mr-0 ml-0">
		<div class="col-12 pr-0 pl-0">
			<div class="btn-group menu-collection" role="group">
				<?php
					// remove empty cards from upper menu
					foreach($panelInit->menuElement as $key => $value)
					{
						$initial = 0;
						if( $key == "dashboard" ) { $initial++; }
						elseif( $key == "gallery" )
						{
							if( array_key_exists('role_perm', $value) )
							{
								$galleryRolePerm = $value['role_perm'];
								if( count( $galleryRolePerm ) != 0 )
								{
									foreach( $galleryRolePerm as $perm )
									{
										if( $panelInit->can( $perm ) ) { $initial++; $seconary++; }
									}
								}
							}
						}
						elseif( $key == "disciplines" )
						{
							if( array_key_exists('role_perm', $value) )
							{
								$disciplinesRolePerm = $value['role_perm'];
								if( count( $disciplinesRolePerm ) != 0 )
								{
									foreach( $disciplinesRolePerm as $perm )
									{
										if( $panelInit->can( $perm ) ) { $initial++; $seconary++; }
									}
								}
							}
						}
						elseif( $key == "feedbacks" )
						{
							if( array_key_exists('role_perm', $value) )
							{
								$feedbacksRolePerm = $value['role_perm'];
								if( count( $feedbacksRolePerm ) != 0 )
								{
									foreach( $feedbacksRolePerm as $perm )
									{
										if( $panelInit->can( $perm ) ) { $initial++; $seconary++; }
									}
								}
							}
						}
						elseif( $key == "subjectVideos" )
						{
							if( array_key_exists('role_perm', $value) )
							{
								$subjectVideosRolePerm = $value['role_perm'];
								if( count( $subjectVideosRolePerm ) != 0 )
								{
									foreach( $subjectVideosRolePerm as $perm )
									{
										if( $panelInit->can( $perm ) ) { $initial++; $seconary++; }
									}
								}
							}
						}
						elseif( $key == "myTimeTable" )
						{
							if( array_key_exists('role_perm', $value) )
							{
								$myTimeTableRolePerm = $value['role_perm'];
								if( count( $myTimeTableRolePerm ) != 0 )
								{
									foreach( $myTimeTableRolePerm as $perm )
									{
										if( $panelInit->can( $perm ) ) { $initial++; $seconary++; }
									}
								}
							}
						}
						elseif( $key == "teachers" )
						{
							if( array_key_exists('role_perm', $value) )
							{
								$teachersRolePerm = $value['role_perm'];
								if( count( $teachersRolePerm ) != 0 )
								{
									foreach( $teachersRolePerm as $perm )
									{
										if( $panelInit->can( $perm ) ) { $initial++; $seconary++; }
									}
								}
							}
						}
						if( !is_array( $value ) ) continue;
						if( !count( $value ) ) continue;
						if( isset($value['children']) )
						{
							foreach ($value['children'] as $key2 => $item)
							{
								$seconary = 0;
								if( is_array( $item ) )
								{
									if( array_key_exists('role_perm', $item) )
									{
										if( count( $item['role_perm'] ) != 0 )
										{
											foreach( $item['role_perm'] as $perm )
											{
												if( $panelInit->can( $perm ) ) { $initial++; $seconary++; }
											}
										}
									}
								}
								if( $seconary == 0 ) { unset( $panelInit->menuElement[$key]['children'][$key2] ); }
								if( !is_array( $item ) )
								{
									unset( $panelInit->menuElement[$key]['children'][$key2] );
									continue;
								}
								elseif( count( $item ) == 0 )
								{
									unset( $panelInit->menuElement[$key]['children'][$key2] );
									continue;
								}
							}
						}
						if( $initial == 0 ) { unset( $panelInit->menuElement[$key] ); }
					}
				?>
				@foreach($panelInit->menuElement as $key => $value)
				<?php
					if( !is_array( $value ) ) continue;
					if( !count( $value ) ) continue;
					$inner_urls = [];
					if(isset($value['children'])) {
						foreach ($value['children'] as $key2 => $item) {
							if( !is_array( $item ) )
							{
								unset( $panelInit->menuElement[$key]['children'][$key2] );
								continue;
							}
							elseif( count( $item ) == 0 )
							{
								unset( $panelInit->menuElement[$key]['children'][$key2] );
								continue;
							}
							elseif(isset($item['url'])) {
								$inner_urls[] = $item['url'];
							}
						}
					}
				?>
				<a
				  	data-title="{{ $value['title'] }}"
				  	data-children="{{ isset($value['children']) ? json_encode($value['children']) : '' }}"
				  	data-inner-urls="{{ json_encode($inner_urls) }}"
				  	class="pr-1 pl-1 btn btn-secondary btn-md text-dark topMainMenuItem"
				  	@if(isset($value['url']))
				  		href="{{ URL::to($value['url']) }}">
				  	@else
				  		>
				  	@endif
				  		{{-- <i class="{{ $value['icon'] }}"></i>  --}}
				  		<img src="{{ asset('assets/images/top-menu/' . $value['icon_img']) }}">
				  		<span title="{{ $value['title'] }}" class="mainMenuTitileSpan">{{ $value['title'] }}</span>
				</a>
				@endforeach
			</div>
			<span style="display: none;" class="mr-1 ml-1 arrow-indicator text-primary">
				<i class="fa fa-arrow-right"></i>
			</span>
			<div class="back-to-menu btn-group" style="display: none; margin-right: 15px" role="group">
				<a class="btn btn-secondary btn-md text-danger" style="padding-right: 16px; padding-left: 16px">
					<i class="mdi mdi-arrow-left-bold-hexagon-outline"></i> <span>Back</span>
				</a>
			</div>
			<div class="sub-menu btn-group" style="display: none;" role="group"></div>
		</div>
	</div>
</div>

@section('scripts')
	<script type="text/javascript">
		$('.menu-collection').delegate('a', 'click', function() {
			var current_title = $(this).data('title');
			var children = $(this).data('children');

			$('.sub-menu').html('').hide();
			$('.back-to-menu').hide();
			$('.arrow-indicator').hide();

			if(Object.keys(children).length > 0) {
				$('.menu-collection a').each(function(){
					if($(this).data('title') == current_title) {
						$(this).show()
					} else {
						$(this).hide()
					}
				});

				$.each(children, function(index, item) {
					if(item.url != 'undefined') {
						$('.sub-menu').append(`<a class="btn btn-secondary btn-md text-info" href="`+ item.url +`">`
							+ `<i class="`+ item.icon +`"></i>`
							+ `<span>` + item.title + `</span>` +
						`</a>`);
					} else {
						$('.sub-menu').append(`<a class="btn btn-secondary btn-md text-info">`
							+ `<i class="`+ item.icon +`"></i>`
							+ `<span>` + item.title + `</span>` +
						`</a>`);
					}
				});
				setTimeout(function(){
					$('.sub-menu').show(200);
					$('.back-to-menu').show();
					$('.arrow-indicator').show();
				}, 100);
			}
		})
		$('.sub-menu').delegate('a', 'click', function(){
			$('.sub-menu a').removeClass('active');
			$(this).addClass('active');
		})
		$('.back-to-menu').delegate('a', 'click', function(){
			$('.menu-collection a').show();
			$('.sub-menu').html('').hide();
			$('.back-to-menu').hide()
			$('.arrow-indicator').hide()
		})
		var prevScrollpos = window.pageYOffset;
		window.onscroll = function() {
			var currentScrollPos = window.pageYOffset;
			if(currentScrollPos > 10) {
			  if (prevScrollpos > currentScrollPos) {
			    // document.getElementById("topMenu").style.top = "45px";
			  } else {
			    // document.getElementById("topMenu").style.top = "-150px";
			  }
			}
		  prevScrollpos = currentScrollPos;
		}
		$(document).ready(function(){
      var url = window.location;
      var element = $('#topMenu a').each(function(){
      	var urls = $(this).data('inner-urls');
      	if($.inArray(url.href, urls) >= 0) {
      		$(this).trigger('click');
      		setTimeout(function(){
      			$('.sub-menu a[href="'+ url.href +'"]').addClass('active')
      		}, 200)
      	}
      });
		})
	</script>
@append