<!DOCTYPE html>
<html lang="en">
  <head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <link rel="shortcut icon" href="<?php echo base_url();?>assets/img/favicon.ico" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url();?>assets/img/favicon.ico" type="image/x-icon">
  <title><?php echo $title;?></title>

  <!-- Bootstrap core CSS -->
  <link href="<?php echo base_url();?>assets/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Custom styles for this template -->
  <link href="<?php echo base_url();?>assets/css/style.css" rel="stylesheet">
	<!-- Normalize CSS-->
	<link href="<?php echo base_url();?>assets/css/normalize.css" rel="stylesheet">
	<!-- Smoke CSS -->
	<link href="<?php echo base_url();?>assets/css/smoke.css" rel="stylesheet">  
	<!--Datatable CSS-->
	<link href="<?php echo base_url();?>assets/css/demo_table_jui.css" rel="stylesheet">
	<link href="<?php echo base_url();?>assets/css/jquery-ui-1.8.4.custom.css" rel="stylesheet">
  <!--Upload CSS-->
  <link href="<?php echo base_url();?>assets/css/uploadify.css" rel="stylesheet">
  <!--Responsive CSS-->
  <link href="<?php echo base_url();?>assets/css/responsive.css" rel="stylesheet">
  <!--QTIP CSS-->
  <link href="<?php echo base_url();?>assets/css/jquery.qtip.min.css" rel="stylesheet">
  <!--UI Timepicker-->
  <link href="<?php echo base_url();?>assets/css/jquery-ui-timepicker-addon.css" rel="stylesheet">
  <!-- Tour Tip -->
  <link href="<?php echo base_url();?>assets/css/tinytools.tourtip.min.css" rel="stylesheet">
	
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="<?php echo base_url();?>assets/js/html5shiv.js"></script>
      <script src="<?php echo base_url();?>assets/js/respond.min.js"></script>
    <![endif]-->
    <noscript>
      <link href="<?php echo base_url();?>assets/css/noscript.css" rel="stylesheet">
      <div id="content">
      <div class="logo"><img src="<?php echo base_url();?>assets/img/shell_logo.png" width="180" height="150" /></div>
      <p class="info">Please enable your javascript.</p>
      <p class="refresh"><a href="<?php echo current_url();?>">Refresh</a></p>
    </div>
    </noscript>
    <script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?&libraries=places,geometry&sensor=false"></script>
  </head>
  <body>
    <div id="dvLoading"></div>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><img src="<?php echo base_url();?>assets/img/shell_logo.png"  width="50" height="45"/></a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
			     <?php $active = $this->uri->segment(2); $uri1 = $this->uri->segment(1); ?>
            <li class="<?php echo ($active == "routes" || $uri1 == "google" && $active=="")? 'active-li': NULL;?>"><a data-toggle="dropdown" href="#" >Route</a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                <li><a href="<?php echo base_url("google/routes");?>">Route list</a></li>
                <li><a href="<?php echo base_url("google");?>">Add route</a></li>
              </ul>
            </li>
            <li class="<?php echo ($active == "hazard_list" || $active == "add_hazard")? 'active-li': NULL;?>"><a data-toggle="dropdown" href="#" >Hazard</a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                <li><a href="<?php echo base_url("google/hazard_list");?>">Hazard list</a></li>
                <li><a href="<?php echo base_url("google/add_hazard");?>">Add hazard</a></li>
              </ul>
            </li>
            <!--<li><a data-toggle="dropdown" href="#">Info</a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                <li><a href="<?php echo base_url("google/info_list");?>">List</a></li>
                <li><a href="<?php echo base_url("google/add_info");?>">Add info</a></li>
              </ul>
            </li>-->
            <li class="<?php echo ($uri1 == "users-list" || $uri1 == "add-user")? 'active-li': NULL;?>"><a data-toggle="dropdown" href="#">Users</a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                <li><a href="<?php echo base_url("users-list");?>">User list</a></li>
              <!--  <li><a href="<?php #echo base_url("add-user");?>">Add user</a></li>-->
              </ul>
            </li>
            <li><a data-toggle="dropdown" href="#">Sites</a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                <li><a href="<?php echo base_url("sites");?>">List</a></li>
              </ul>
            </li>
            <li><a data-toggle="dropdown" href="#">Audit Trail</a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                <li><a href="<?php echo base_url("audit-trail");?>">List</a></li>
              </ul>
            </li>
            <li id="user-profile"><a href="#" class="text-right"><span><?php echo user_info(get_session("login")['user_id'],"firstname");?></span><img src="<?php echo base_url();?>assets/img/user-icon.png" /></a></li>
            <li class="log-btn"><a href="<?php echo base_url("login/logout");?>" class="text-right btn btn-primary" id="logout-btn">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
	<div class="container">
		<div class="starter-template">
			<?php echo $this->load->view($content);?>
		</div>
	</div>


<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>-->
<!--?key=AIzaSyAgocfpYU736VIBLB1O2SB4XQoarno70SA-->
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/json_code.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/smoke.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.uploadify.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.qtip.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/markerwithlabel.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/RouteBoxer.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/html2canvas.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery-ui-sliderAccess.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/tinytools.tourtip.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/poly-contains.js"></script>
<?php $user_latlng = $this->dan_model->select_where("user_tb",array("user_id"=>get_session("login")['user_id'])); ?>
<script type="text/javascript">
//url for ajax
  var l = window.location;
  var base_url = l.protocol + "//" + l.host  + "/" + l.pathname.split('/')[1];
  var user_lat = <?php echo $user_latlng->latitude;?>;
  var user_lng = <?php echo $user_latlng->longtitude;?>;
  $('.dropdown-toggle').dropdown();
  // **************** Map Options ***********************
  var options = {
    zoom: 18,
    center: new google.maps.LatLng(user_lat, user_lng),
    mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU, position: google.maps.ControlPosition.BOTTOM_LEFT
 },
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    draggableCursor: "https://maps.gstatic.com/mapfiles/openhand_8_8.cur",
    streetViewControl: false,
    zoomControlOptions: {
      style: google.maps.ZoomControlStyle.SMALL
    },
    panControl: false
  };
  // **************** Initialize map ***********************
  if($("#map").length) 
  var map = new google.maps.Map(document.getElementById("map"), options);
<?php
  if($js_content != NULL){
     echo $this->load->view($js_content);
  }
?>
$('[data-tooltip!=""]').qtip({ 
        content: {
            attr: 'data-tooltip'
        },
        style: { 
          padding: 5,
          background: '#A2D959',
          color: 'black',
          textAlign: 'center',
          border: {
              width: 7,
              radius: 5,
              color: '#A2D959'
            },
          name: 'dark'
       }
    });

$(document).ready(function() {
    $('#dvLoading').fadeOut(1000);
    //if($("#map").length == 0){
    //  $('#dvLoading').fadeOut(1000);
    //}
    //else{
    //  google.maps.event.addListenerOnce(map, 'tilesloaded', function(e){
    //   $('#dvLoading').fadeOut(1000);
    // });
    //}

    
    $("#adduserform").validate({
      rules:{
        email:{required: true,email: true},
        password:{required: true,password: true},
        fname:{required:true,minlength:3},
        lname:{required:true,minlength:3},
        cpass:{required:true,equalTo:"#similarto"},
        //location:{required:true},
        lat:{required:true},
        lng:{required:true}
      },
      highlight: function(element) {
          $(element).closest('.form-group').addClass('has-error');
      },
      unhighlight: function(element) {
          $(element).closest('.form-group').removeClass('has-error');
      }
    });
});

</script>
  </body>
</html>
