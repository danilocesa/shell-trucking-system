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
			<div class="col-md-4 pull-right" style="margin-top:50px;">
				<img src="<?php echo base_url();?>assets/img/shell_logo.png" class="center-block login_logo hidden-sm hidden-xs"/>
				<form role="form" id="login_form" method="post" style="font-size:12px;">
					<div class="form-group error_vali"><?php echo validation_errors();?><?php echo (@$invalid_cred == TRUE)? "Invalid Username or Password" : NULL ;?></div>
					<div class="form-group error_vali"><?php echo ($this->session->flashdata("logged") == "no")? "Logged out, another computer is logged into this account.": NULL ; ?></div>
					<div class="form-group error_vali"><?php echo ($this->session->flashdata("sesExpire") == "yes")? "Your session has expired. Please log-in again.": NULL ; ?></div>
					<div class="form-group">
						<label for="email addresss">Email address</label>
						<input type="email" class="form-control" id="" placeholder="Email" name="email" required />
					</div>
					<div class="clearfix"></div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" id="" placeholder="Password" name="password" required />
					</div>
					<div class="clearfix"></div>
					<div class="form-group">
						<p style="color:#01A9DB;display:inline;cursor:pointer;" id="forgotFadein" >Forgot Password</p>
						<button type="submit" class="btn btn-primary btn-sm" name="login" style="float:right;">Login</button>
					</div>
					
				</form>
				<!-- Email forgot -->
				<form role="form" id="forgot_form" method="post" style="font-size:12px;">
					<div class="form-group error_vali" id="forgot_error"></div>
					<div class="form-group">
						<label for="email addresss">Email address</label>
						<input type="email" class="form-control" id="forgotemail" placeholder="Email" name="forgotemail" required />
					</div>
					<div class="clearfix"></div>
					<div class="form-group">
						<p style="color:#01A9DB;display:inline;cursor:pointer;" class="loginFadein" >Login</p>
						<button type="submit" class="btn btn-primary btn-sm forgotsubmit" name="forgotsubmit" style="float:right;">Submit</button>
					</div>
				</form>
				<!-- Question form ---->
				<form role="form" id="question_form" method="post" style="font-size:12px;">
					<div class="form-group error_vali" id="question_error"></div>
					<div class="form-group">
						<label for="question">Secret Question</label>
						<select class="form-control input-sm" name="question" id="quest-opt">
							<option value="">Select option</option>
							<option value="1">What was your childhood nickname?</option>
							<option value="2">What is the middle name of your oldest child?</option>
							<option value="3">What school did you attend for sixth grade?</option>
							<option value="4">In what city or town was your first job?</option>
							<option value="5">What is the first name of the boy or girl that you first kissed?</option>
							<option value="6">In what city or town did your mother and father meet?</option>
							<option value="7">What is your maternal grandmother's maiden name?</option>
							<option value="8">What is your oldest cousin's first and last name?</option>
							<option value="9">What school did you attend for sixth grade?</option>
						</select>	
					</div>
					<div class="clearfix"></div>
					<div class="form-group">
						<label for="answer">Answer</label>
						<input type="password" class="form-control" id="quest-ans" placeholder="Answer" name="answer" required />
					</div>
					<div class="clearfix"></div>
					<div class="form-group">
						<p style="color:#01A9DB;display:inline;cursor:pointer;" class="loginFadein" >Login</p>
						<button type="submit" class="btn btn-primary btn-sm forgotsubmit" name="forgotsubmit" style="float:right;">Submit</button>
					</div>
				</form>
				<!-- New Password -->
				<form role="form" id="password_form" method="post" style="font-size:12px;">
					<div class="form-group" id="success_pass" style="color:green;"></div>
					<div class="form-group">
						<label for="answer">New Password</label>
						<input type="password" class="form-control" id="similarto" placeholder="Password" name="new_pass" required />
					</div>
					<div class="clearfix"></div>
					<div class="form-group">
						<label for="answer">Confirm Password</label>
						<input type="password" class="form-control" id="conf-pass" placeholder="Confirm Password" name="conf_pass" required />
					</div>
					<div class="clearfix"></div>
					<div id="changeprogress" style="margin-top:20px;font-size:11px;display:none">
						<div class="progress progress-striped active">
							<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
						</div>
					</div>
					<div class="form-group">
						<p style="color:#01A9DB;display:inline;cursor:pointer;" class="loginFadein" >Login</p>
						<button type="submit" class="btn btn-primary btn-sm forgotsubmit" name="forgotsubmit" style="float:right;">Submit</button>
					</div>
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
			rules:{
				email:{required: true, email: true},
				password:{required: true}
			},
			highlight: function(element) {
           		$(element).closest('.form-group').addClass('has-error');
        	},
        	unhighlight: function(element) {
            	$(element).closest('.form-group').removeClass('has-error');
        	}
		});
		$("#forgot_form").validate({
			rules:{
				forgotemail:{required: true, email: true},
				question:{required: true}
			},
			highlight: function(element) {
           		$(element).closest('.form-group').addClass('has-error');
        	},
        	unhighlight: function(element) {
            	$(element).closest('.form-group').removeClass('has-error');
        	},
        	submitHandler: function (form) {
        		$.ajax({
        			type: "POST",
        			url: base_url+"/login/forgot_pass/",
        			cache:false,
        			async:false,
        			dataType: "json",
        			data: $("#forgot_form").serialize(),
        			beforeSend: function(){
        				$('#dvLoading').fadeIn(300);			
        			},
        			success: function(data){
        				if(data.emailNotexist == true){
        					$("#forgot_error").html("<p>Email doesn't exist</p>");
        				}
        				if(data.emailExist == true){
        					$("#forgot_form").fadeOut(400);	
        					$("#question_form").show(1500);
        				}
        			},
        			complete: function(){
        				$('#dvLoading').fadeOut(1000);
        			},
        			error: function(xhr, stat, str){
        				console.log(xhr);
        				console.log(stat);
        				console.log(str);
        			}
        		});
        		return false;
        	}
		});	
		$("#question_form").validate({
			rules:{
				question:{required: true},
				answer:{required: true}
			},
			highlight: function(element) {
           		$(element).closest('.form-group').addClass('has-error');
        	},
        	unhighlight: function(element) {
            	$(element).closest('.form-group').removeClass('has-error');
        	},
        	submitHandler: function (form) {
        		$.ajax({
        			type: "POST",
        			url: base_url+"/login/check_pass/",
        			cache:false,
        			async:false,
        			dataType: "json",
        			data: $("#question_form").serialize(),
        			beforeSend: function(){
        				$('#dvLoading').fadeIn(300);			
        			},
        			success: function(data){
        				if(data.err == true){
        					$("#question_error").html("<p>Incorrect Question or Answer</p>");
        				}
        				if(data.success == true){
        					$("#question_form").fadeOut(400);	
        					$("#password_form").show(1500);
        				}
        			},
        			complete: function(){
        				$('#dvLoading').fadeOut(1000);
        			},
        			error: function(xhr, stat, str){
        				console.log(xhr);
        				console.log(stat);
        				console.log(str);
        			}
        		});
        		return false;
        	}
		});
		$("#password_form").validate({
			rules:{
				new_pass:{required: true, minlength:6},
				conf_pass:{required: true, equalTo:"#similarto"}
			},
			highlight: function(element) {
           		$(element).closest('.form-group').addClass('has-error');
        	},
        	unhighlight: function(element) {
            	$(element).closest('.form-group').removeClass('has-error');
        	},
        	submitHandler: function (form) {
        		$.ajax({
        			type: "POST",
        			url: base_url+"/login/new_password/",
        			cache:false,
        			async:false,
        			dataType: "json",
        			data: $("#password_form").serialize(),
        			beforeSend: function(){
        				$("#changeprogress").show();			
        			},
        			success: function(data){
        				if(data.success == true){
        					$("#success_pass").html("<p>Password successfully changed</p>");
        				}
        			},
        			complete: function(){
        				$("#changeprogress").hide();
        				$(".forgotsubmit").hide();
        			},
        			error: function(xhr, stat, str){
        				console.log(xhr);
        				console.log(stat);
        				console.log(str);
        			}
        		});
        		return false;
        	}
		});
		$("#forgotFadein").click(function(){
			$(".error_vali").html("");
			$("#login_form").fadeOut(400);
			$("#forgot_form").show(1500);

		});
		$(".loginFadein").click(function(){
			$("#forgotemail").val("");
			$("#quest-opt").val("");
			$("#quest-ans").val("");
			$("#similarto").val("");
			$("#conf-pass").val("");
			$("#question_error").html("");
			$("#forgot_error").html("");
			$("#success_pass").html("");
			$("#forgot_form").fadeOut(400);
			$("#question_form").fadeOut(400);
			$("#password_form").fadeOut(400);
			$("#login_form").show(1500);
		});
		$("#forgot_form").hide();
		$("#question_form").hide();
		$("#password_form").hide();
	});
</script>

</body>
</html>

