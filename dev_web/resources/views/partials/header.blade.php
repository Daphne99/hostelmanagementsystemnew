<style type="text/css">
	.navbar-nav .nav-item .nav-link {
		height: 43px !important;
		line-height: 26px !important;
    }
    .profile-pic { border-radius: 6px !important; }
    .text-themecolor { color: #009efb !important; }
    .topbar { background: linear-gradient(178deg, #009efb 10%, black 73%) !important; }
    .card-block { background-color: #f2f7f8 !important; }
    .containerOfUserDropDown{ left: -500% !important; top: 100% !important; }
</style>

<header class="topbar <?php if($panelInit->settingsArray['leftmenuScroller'] == "e"){ echo "topbarSticky"; } ?> no-print">
    <nav class="navbar top-navbar navbar-toggleable-sm navbar-light" style="height: 40px; min-height: unset; box-shadow: 1px 1px 5px #EEE !important;">
      <div class="navbar-header" style="
      	line-height: 39px; position: absolute;
    		left: 50%;
  			transform: translateX(-50%);
  			box-shadow: none;
  			background: unset;">
          <a class="navbar-brand" href="/portal#/" style="color: #FFF; font-weight: 400">
              <?php
              $panelInit->settingsArray['siteLogo'] = "siteName";
              if($panelInit->settingsArray['siteLogo'] == "siteName"){
                  ?>
                  <span>
                      <span class="dark-logo" ng-show="$root.dashboardData.baseUser.defTheme.indexOf('dark') == -1"><?php echo $panelInit->settingsArray['siteTitle']; ?></span>
                      <span class="light-logo" ng-show="$root.dashboardData.baseUser.defTheme.indexOf('dark') !== -1"><?php echo $panelInit->settingsArray['siteTitle']; ?></span>
                  </span>
                  <?php
              }elseif($panelInit->settingsArray['siteLogo'] == "text"){
                  ?>
                  <span>
                      <span class="dark-logo" ng-show="$root.dashboardData.baseUser.defTheme.indexOf('dark') == -1"><?php echo $panelInit->settingsArray['siteLogoAdditional']; ?></span>
                      <span class="light-logo" ng-show="$root.dashboardData.baseUser.defTheme.indexOf('dark') !== -1"><?php echo $panelInit->settingsArray['siteLogoAdditional']; ?></span>
                  </span>
                  <?php
              }elseif($panelInit->settingsArray['siteLogo'] == "image"){
                  ?>
                  <span>
                      <img src="<?php echo URL::asset('assets/images/logo-dark.png'); ?>" alt="homepage" class="dark-logo" />
                      <img src="<?php echo URL::asset('assets/images/logo-light.png'); ?>" class="light-logo" alt="homepage" />
                  </span>
                  <?php
              }
              ?>
              <img class="icon-logo" src="{{ asset('favicon.ico') }}" width="26px">
          </a>
      </div>
      <div class="navbar-collapse">
          <!-- ============================================================== -->
          <!-- toggle and nav items -->
          <!-- ============================================================== -->
          <ul class="navbar-nav mr-auto mt-md-0 ">
              <!-- This is  -->
              {{-- <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li> --}}
              {{-- <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="icon-arrow-left-circle"></i></a> </li> --}}
          </ul>
          <!-- ============================================================== -->
          <!-- User profile and search -->
          <!-- ============================================================== -->
          <ul class="navbar-nav my-lg-0">
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true"  aria-expanded="false">
                  	<img src="{{URL::to('/dashboard/profileImage/'.$users['id'])}}" alt="user" class="profile-pic" style="background-color: #FFF; margin-top: -3px" />
                  </a>
                  <div class="dropdown-menu dropdown-menu-right containerOfUserDropDown">
                      <ul class="dropdown-user">
                          <li>
                              <div class="dw-user-box">
                                  <div class="u-img">
                                  	<img src="{{URL::to('/dashboard/profileImage/'.$users['id'])}}" alt="user">
                                  </div>
                                  <div class="u-text">
                                      <h4>{{$users['fullName']}}</h4>
                                      <p class="text-muted">{{$users['email']}}</p></div>
                              </div>
                          </li>
                          <li role="separator" class="divider"></li>
                          <?php if($panelInit->can('AccountSettings.myInvoices')){ ?><a href="index.php/portal#/account/invoices" class="dropdown-item"><i class="fa fa-files-o pr-3"></i> <?php echo $panelInit->language['myInvoices']; ?></a><?php } ?>
                          <?php if($panelInit->can('AccountSettings.Messages')){ ?><a href="index.php/portal#/messages" class="dropdown-item"><i class="fa fa-comments-o pr-3"></i> <?php echo $panelInit->language['Messages']; ?></a><?php } ?>
                          <?php if($panelInit->can( array("AccountSettings.ChgProfileData","AccountSettings.chgEmailAddress","AccountSettings.chgPassword") )) { ?><div class="dropdown-divider"></div> <a href="portal#/account" class="dropdown-item"><i class="fa fa-cogs pr-3"></i> <?php echo $panelInit->language['AccountSettings']; ?></a><?php } ?>
                          <div class="dropdown-divider"></div>
                          <a href="{{URL::to('/logout')}}" class="dropdown-item logoutBtn">
                          	<i class="fa fa-power-off pr-3"></i> <?php echo $panelInit->language['logout']; ?>
                          </a>
                      </ul>
                  </div>
              </li>

              {{-- <li class="nav-item dropdown" style="width:45px;">
                  <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="/portal#/mobileNotif" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  	<i class="fa fa-bell"></i>
                  </a>
              </li> --}}

              <li class="nav-item dropdown" style="width:45px;">
                  <a href="{{URL::to('/logout')}}" class="nav-link text-muted waves-effect waves-dark logoutBtn">
                  	<i class="fa fa-sign-out"></i>
                  </a>
              </li>
              {{-- <li class="nav-item dropdown">
                  <a class="right-side-toggle text-muted nav-link " href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-view-grid"></i></a>
              </li> --}}

          </ul>
      </div>
    </nav>
</header>

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$('.logoutBtn').click(function(e){
				e.preventDefault();
                alertify.defaults.transition = 'zoom';
                alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
                alertify.defaults.theme.cancel = 'btn btn-sm btn-secondary';
                alertify.defaults.theme.input = 'form-control';
                alertify.confirm(
                    'Confirm Logout',
                    'Are you sure to logout ?',
                    function(){ window.location.href = "{{ URL::to('/logout') }}"; },
                    function(){}
                ).set('labels', {ok: '<i class="fa fa-power-off"></i> Logout', cancel: 'Cancel'});
			});
		});
	</script>
@append