<!DOCTYPE HTML>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo $title;?></title>
    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url();?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="<?php echo base_url();?>assets/css/style.css" rel="stylesheet">
	<!-- Normalize CSS-->
	<link href="<?php echo base_url();?>assets/css/normalize.css" rel="stylesheet">
	<!--Responsive CSS-->
  	<link href="<?php echo base_url();?>assets/css/responsive.css" rel="stylesheet">
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
</head>
<body>
	<div id="dvLoading"></div>
	<div class="container">
		<div class="row">
			<div class="hidden-md col-sm-12 col-xs-12 hidden-lg login_logo">
				<img src="<?php echo base_url();?>assets/img/shell_logo.png" class="center-block" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-4" style="float:right;margin-top:50px;">
				<img src="<?php echo base_url();?>assets/img/shell_logo.png" class="center-block login_logo hidden-sm hidden-xs"/>
				<form role="form" id="login_form" method="post">
					<div class="form-group error_vali"><?php echo validation_errors();?><?php echo (@$invalid_cred == TRUE)? "Invalid Username or Password" : NULL ;?></div>
					<div class="form-group error_vali"><?php echo ($this->session->flashdata("logged") == "no")? "Logged out, another computer is logged into this account.": NULL ; ?></div>
					<div class="form-group">
						<label for="email addresss">Email address</label>
						<input type="email" class="form-control" id="" placeholder="Email" name="email" required />
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" id="" placeholder="Password" name="password" required />
					</div>
					<button type="submit" class="btn btn-primary" name="login" >Login</button>
				</form>
			</div>
		</div>
		<div class="" style="position:absolute;bottom:70px;left:0;z-index:-10;">
			<div class="col-md-12">
				<img src="<?php echo base_url();?>assets/img/login_intro.png" width="1150" height="500" />
			</div>
		</div>
	</div>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.validate.min.js"></script>
<script>
	var l = window.location;
  	var base_url = l.protocol + "//" + l.host  + "/" + l.pathname.split('/')[1];
	$(document).ready(function() {
		$('#dvLoading').fadeOut(1000);
		$("#login_form").validate({
			onkeyup: false,
			rules:{
				email:{required: true,email: true},
				password:{required: true,password: true}
			},
			submitHandler: function (form) {
        		$('#dvLoading').fadeIn(300);
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

