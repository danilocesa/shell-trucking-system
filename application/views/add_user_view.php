<div class="row">
	<div class="col-md-4"></div>
	<div class="col-md-4">
		<form role="form" id="login_form" method="post">
			<div class="form-group error_vali"><?php echo validation_errors();?></div>
			<?php echo @$success; ?>
			<div class="form-group">
				<label for="email addresss">Email address</label>
				<input type="email" class="form-control" id="" placeholder="Email" name="email">
			</div>
				<div class="form-group">
				<label for="firstname">Firstname</label>
				<input type="text" class="form-control" id="" placeholder="Firstname" name="fname">
			</div>
			<div class="form-group">
				<label for="lastname">Lastname</label>
				<input type="text" class="form-control" id="" placeholder="Lastname" name="lname">
			</div>
			<div class="form-group">
				<label for="password">Password</label>
				<input type="password" class="form-control" placeholder="Password" name="password">
			</div>
			<div class="form-group">
				<label for="confirm password">Confirm Password</label>
				<input type="password" class="form-control" placeholder="Confirm password" name="cpass">
			</div>
			<button type="submit" class="btn btn-primary" name="Add">Add</button>
		</form>

	</div>
	<div class="col-md-4"></div>
</div>
<script type="text/javascript">
	var l = window.location;
  	var base_url = l.protocol + "//" + l.host  + "/" + l.pathname.split('/')[1];
	$(document).ready(function() {
		$("#login_form").validate({
			onkeyup: false,
			rules:{
				email:{required: true,email: true},
				password:{required: true,password: true}
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