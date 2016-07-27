<!DOCTYPE html>
<html lang="en">
  <head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta http-equiv="cache-control" content="max-age=0" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="expires" content="0" />
  <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
  <meta http-equiv="pragma" content="no-cache" />
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
   <!-- Chosen -->
  <link href="<?php echo base_url();?>assets/css/chosen.min.css" rel="stylesheet"/>
  <!-- OFFLINE -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/css/offline-theme-chrome.css" />
  <link rel="stylesheet" href="<?php echo base_url();?>assets/css/offline-language-english.css" />
	
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
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyAgocfpYU736VIBLB1O2SB4XQoarno70SA&libraries=places,geometry&sensor=false"></script>
  </head>
  <body>
    <div id="dvLoading"></div>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation" style="z-index:10;">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo base_url();?>"><img src="<?php echo base_url();?>assets/img/shell_logo.png"  width="50" height="45"/></a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
			     <?php $active = $this->uri->segment(2); $uri1 = $this->uri->segment(1);?>
            <li class="<?php echo ($uri1 == "routes-list" || $uri1 == "add-route" || $uri1 == "route-details" )? 'active-li': NULL;?>">
              <a href="<?php echo base_url("routes-list");?>" >Route</a>
            </li>
            <li class="<?php echo ($uri1 == "hazards-list" || $uri1 == "add-hazards" || $uri1 == "add-sites" || $uri1 == "add-depots" || $uri1 == "edit-hazard" || $uri1 == "edit-site" || $uri1 == "edit-depot" )? 'active-li': NULL;?>">
              <a href="<?php echo base_url("hazards-list");?>" >Hazard</a>
            </li>
            <li class="<?php echo ($uri1 == "users-list" || $uri1 == "add-user")? 'active-li': NULL;?>">
              <a href="<?php echo base_url("users-list");?>">Users</a>
            </li>
            <li class="<?php echo ($uri1 == "audit-trail" )? 'active-li': NULL;?>">
              <a href="<?php echo base_url("audit-trail");?>">Audit Trail</a>
            </li>
            <li id="user-profile" >
              <a style="cursor: default;" href="#" class="text-right">
                <img src="<?php echo base_url();?>assets/img/user-icon.png" />
                <span><?php echo user_info(get_session("login")['user_id'],"firstname");?></span>
              </a>
            </li>
            <li class="log-btn">
              <a href="<?php echo base_url("logout");?>" class="text-right btn btn-primary" id="logout-btn">Logout</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
	<div class="container">
		<div class="starter-template">
			<script>
			// **************** Web Socket ***********************	 
			var conn = new WebSocket('ws://' + window.location.host + ':8080');
			conn.onopen = function(e) {
				console.log('SUCCESS: Connected to ' + e.currentTarget.URL);
			};
				
			conn.onerror = function(e) {
				console.log('ERROR: Cannot connect to ' + e.currentTarget.URL);
			};
			
			conn.onclose = function(e) {
				console.log('ERROR: Cannot connect to ' + e.currentTarget.URL);
			};
			// ***************************************************
			</script>
			<?php echo $this->load->view($content);?>
		</div>
	</div>


<?php $user_latlng = $this->dan_model->select_where("user_tb",array("user_id"=>get_session("login")['user_id']));?>
  <!-- Modal -->
<div class="modal fade" id="updateUser" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <h4 class="modal-title" id="myModalLabel">Update Account</h4>
      </div>
      <div class="modal-body">
      <form role="form" id="updateUserform" method="post" style="font-size:12px;">
        <div class="form-group error_vali"></div>
        <p style="color:red;" id="dupe_email"></p>
        <?php echo @$success; ?>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="email addresss" class="pull-right">Email address<label style="color:red;"> *<label></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <input maxlength="50" type="email" class="form-control input-sm" id="" name="email" value="<?php echo $user_latlng->email;?>">
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="firstname" class="pull-right">Firstname<label style="color:red;"> *<label></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <input maxlength="50" type="text" class="form-control input-sm" id="" name="fname" value="<?php echo $user_latlng->firstname; ?>">
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="lastname" class="pull-right">Lastname<label style="color:red;"> *<label></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <input maxlength="50" type="text" class="form-control input-sm" id="" name="lname" value="<?php echo $user_latlng->lastname; ?>">
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="password" class="pull-right">Password<label style="color:red;"> *<label></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <input type="password" class="form-control input-sm" id="similarto" name="password">
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="confirm password" class="pull-right">Confirm Password<label style="color:red;"> *<label></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <input type="password" class="form-control input-sm" name="cpass">
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="questions" class="pull-right">Secret Question<label style="color:red;"> *<label></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <select class="form-control input-sm" name="question">
                <option value="1" <?php echo ($user_latlng->question == "1")? "selected": "" ; ?> >What was your childhood nickname?</option>
                <option value="2" <?php echo ($user_latlng->question == "2")? "selected": "" ; ?> >What is the middle name of your oldest child?</option>
                <option value="3" <?php echo ($user_latlng->question == "3")? "selected": "" ; ?> >What school did you attend for sixth grade?</option>
                <option value="4" <?php echo ($user_latlng->question == "4")? "selected": "" ; ?> >In what city or town was your first job?</option>
                <option value="5" <?php echo ($user_latlng->question == "5")? "selected": "" ; ?> >What is the first name of the boy or girl that you first kissed?</option>
                <option value="6" <?php echo ($user_latlng->question == "6")? "selected": "" ; ?> >In what city or town did your mother and father meet?</option>
                <option value="7" <?php echo ($user_latlng->question == "7")? "selected": "" ; ?> >What is your maternal grandmother's maiden name?</option>
                <option value="8" <?php echo ($user_latlng->question == "8")? "selected": "" ; ?> >What is your oldest cousin's first and last name?</option>
                <option value="9" <?php echo ($user_latlng->question == "9")? "selected": "" ; ?> >What school did you attend for sixth grade?</option>
              </select> 
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="answer" class="pull-right">Answers<label style="color:red;"> *<label></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <input maxlength="60" type="password" class="form-control input-sm" name="answer" value="<?php echo $user_latlng->answer; ?>"></div>
            </div>  
        </div>
        <div class="col-md-12">
          <div class="col-md-4">
            <div class="form-group ">
              <label for="latitude" class="pull-right">Location</label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group ">
              <select class="form-control chosen-select input-sm" name="location" id="userDepot" style="padding:0!important;border:0!important" >
                  <option value=""></option>
            <?php $depots = $this->dan_model->select_table("depot_tb"); foreach($depots as $row):?>
              <option value="<?php echo $row->depot_id; ?>" data-latitude="<?php echo $row->center_latitude; ?>" data-longtitude="<?php echo $row->center_longitude;?>" data-id="<?php echo $row->depot_id; ?>" <?php echo ($row->depot_id == $user_latlng->location)? "selected" : NULL; ?>><?php echo $row->depot_name; ?>
              </option>
            <?php endforeach;?>
            </select>
              <!--input type="text" class="form-control input-sm" placeholder="Manila" name="location" value="<?php echo $user_latlng->location; ?>" -->
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-12">
          <div class="col-md-4">
            <!-- <div class="form-group ">
              <label for="latlng" class="pull-left">Latitude and Longtitude<label style="color:red;"> *<label></label>
            </div> -->
          </div>
          <div class="col-md-4">
            <div class="form-group ">
              <input type="hidden" class="form-control" placeholder="" name="lat" id="latitude_user" value="<?php echo $user_latlng->latitude; ?>">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group ">
              <input type="hidden" class="form-control" placeholder="" name="lng" id="longitude_user" value="<?php echo $user_latlng->longtitude; ?>">
            </div>
          </div>
          <p  style="margin-left:182px;color:red;width:260px;"id="latlng_user"></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-sm" id="clearUpdateUser" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-sm" id='update_user'>Save</button>
      </div>
      </form>
    </div>
  </div>
</div>
<!--?key=AIzaSyAgocfpYU736VIBLB1O2SB4XQoarno70SA-->
<script src="<?php echo base_url();?>assets/js/jquery-ui.js"></script>
<script src="<?php echo base_url();?>assets/js/bootstrap.min.js"></script>
<script src="<?php echo base_url();?>assets/js/json_code.js"></script>
<script src="<?php echo base_url();?>assets/js/smoke.min.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery.uploadify.min.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery.qtip.min.js"></script>
<script src="<?php echo base_url();?>assets/js/markerwithlabel.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery-ui-sliderAccess.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery-ui-timepicker-addon.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery.validate.min.js"></script>
<script src="<?php echo base_url();?>assets/js/tinytools.tourtip.min.js"></script>
<script src="<?php echo base_url();?>assets/js/chosen.jquery.min.js"></script>
<script src="<?php echo base_url();?>assets/js/markerclusterer.js"></script>
<script src="<?php echo base_url();?>assets/js/offline.min.js"></script>

<script type="text/javascript">
  //url for ajax
  var l = window.location;
  var base_url = l.protocol + "//" + l.host  + "/" + l.pathname.split('/')[1];
  var user_lat = <?php echo $user_latlng->latitude;?>;
  var user_lng = <?php echo $user_latlng->longtitude;?>;
  $('.dropdown-toggle').dropdown();
  var MAX_RETRIES = 5;
  // **************** Map Options ***********************
  var options = {
    zoom: 18,
    center: new google.maps.LatLng(user_lat, user_lng),
    mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU, position: google.maps.ControlPosition.BOTTOM_LEFT
 },
    mapTypeId: google.maps.MapTypeId.ROADMAP,
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
          padding: 3,
          background: '#A2D959',
          color: 'black',
          textAlign: 'center',
          border: {
              width: 7,
              radius: 5,
              color: '#A2D959'
            },
          name: 'dark'
       },
       position: {
          my: "top center", 
          at: "bottom center",
          adjust: {
            screen: true
          },
          viewport: $(window)
       }

    });
$(".slider-arrow").qtip({ 
        content: {
            attr: 'data-tooltip'
        },
        style: { 
          padding: 3,
          background: '#A2D959',
          color: 'black',
          textAlign: 'center',
          border: {
              width: 7,
              radius: 5,
              color: '#A2D959'
            },
          name: 'dark'
       },
       position: {my: "center right", at: "center left"}
});

$(".addQuickmenu").qtip({ 
        content: {
            attr: 'data-tooltip'
        },
        style: { 
          padding: 3,
          background: '#A2D959',
          color: 'black',
          textAlign: 'center',
          border: {
              width: 7,
              radius: 5,
              color: '#A2D959'
            },
          name: 'dark'
       },
       position: {my: "center left", at: "center right"}
});
$(document).ready(function() {
    $('#dvLoading').fadeOut(1000);
    $("#user-profile").click(function(){
      $("#updateUser").modal();

    });
  $.validator.addMethod('latitude', function (value) { 
      return /^-?([0-8]?[0-9]|90)\.[0-9]{1,6}$/.test(value); 
  }, "Latitude should be -90.XXXXXX to 90.XXXXXX");

  $.validator.addMethod('longitude', function (value) { 
      return /^-?((1?[0-7]?|[0-9]?)[0-9]|180)\.[0-9]{1,6}$/.test(value); 
  }, "Longitude should be -180.XXXXXX to 180.XXXXXX");
  $.validator.setDefaults({ ignore: ":hidden:not(select)" });
  $("#updateUserform").validate({
    rules:{
      email:{required: true, email: true},
      password:{required: true, minlength:6},
      fname:{required:true, minlength:3},
      lname:{required:true, minlength:3},
      cpass:{required:true, equalTo:"#similarto"},
      question:{required:true},
      answer:{required:true},
      location:{required:true}
    },
    highlight: function(element) {
      $(element).closest('.form-group').addClass('has-error');
    },
    unhighlight: function(element) {
      $(element).closest('.form-group').removeClass('has-error');
    },
    submitHandler: function(form){
        $.ajax({
            type: "POST",
            url: base_url+'/login/update_user',
            cache:false,
            dataType: "json",
            async: false,
            data: $("#updateUserform").serialize(),
            beforeSend: function(){
              $("#dvLoading").fadeIn(1000);
          },
            success: function(data){           
              if(data.dupe_email == true){
                $("#dupe_email").text("Email address already exists!");
              }
              if(data.success == true){          
                $("#updateUser").modal("hide");
                smoke.signal("Successfully updated, reloading...", function(e){
                setTimeout(function(){location.reload();},1000);
              }, {
                duration: 1500
              });
              }
            },
            complete: function(){
              $("#dvLoading").fadeOut(1000);
          }
        });       
        return false;
    }
  });
  
  //var input = (document.getElementById('pac-input'));
  //var autocomplete = new google.maps.places.Autocomplete(input);
  //google.maps.event.addListener(autocomplete, 'place_changed', function(e) {
  //  $("#latitude_user").val(autocomplete.getPlace().geometry.location.lat());
  //  $("#longitude_user").val(autocomplete.getPlace().geometry.location.lng());
  //});

  $(".chosen-select").chosen(); 


  $("#userDepot").change(function(){
      $("label[for='userDepot']").text("");
      $("#latitude_user").val($("#userDepot option:selected").attr("data-latitude"));
      $("#longitude_user").val($("#userDepot option:selected").attr("data-longtitude"));
  });

  $("#clearUpdateUser").click(function(){


  });

});

function chckIn(){
	$.ajax({
		url: base_url+'/google/checkInactive',
		cache: false,
		async: true,
		success: function(data){  
			console.log(data);        
			if(data == "des"){
				window.location.href = "<?php echo base_url('inactive'); ?>";
			}
		},
		error: function(xhr){
			console.log(xhr);
		}
	}); 
}

// Check for user inactivity on window events
$(window).bind('click mouseup mousedown keydown keypress keyup submit change scroll resize dblclick', function(){ userEvtCtr++; });

var timeOutID;
var userEvtCtr = 0;

setInterval(function(){
	// Check if there is a user event
	if (userEvtCtr > 0){
		// Update last activity time
		$.ajax({
			url: base_url+'/google/activeMe',
			cache: false,
			async: true,
			success:function(data){
				// Cancel previous timeout check
				clearTimeout(timeOutID);
				// Update timeout for next user inactivity check
				timeOutID = setTimeout(chckIn, 1805000);
			},
			error: function(xhr){
				console.log(xhr);
			}
		});
		userEvtCtr = 0;
	}
}, 5000);

setTimeout(function(){
    var iframes = document.getElementsByTagName('iframe');
    for (var i = 0; i < iframes.length; i++) {
        iframes[i].parentNode.removeChild(iframes[i]);
    }

}, 3000);

Offline.options = {
	checks: {xhr: {url: "<?php echo base_url();?>assets/img/favicon.ico"}},
	checkOnLoad: true,
	interceptRequests: true,
	requests: true
};

Offline.on("confirmed-down", function() {
	$("#dvLoading").fadeIn(300);
	document.onkeydown = function (e) {
        return false;
	}
});

Offline.on("confirmed-up", function() {
	$("#dvLoading").fadeOut(300);
	document.onkeydown = function (e) {
        return true;
	}
	
	if (conn.readyState != 1) {
		console.log('Reestablishing web socket...');
		// **************** Web Socket ***********************	 
		conn = new WebSocket('ws://' + window.location.host + ':8080');
		conn.onopen = function(e) {
			console.log('SUCCESS: Connected to ' + e.currentTarget.URL);
		};
			
		conn.onerror = function(e) {
			console.log('ERROR: Cannot connect to ' + e.currentTarget.URL);
		};
		
		conn.onclose = function(e) {
			console.log('ERROR: Cannot connect to ' + e.currentTarget.URL);
		};
		// ***************************************************
	}
});
</script>
</body>
</html>