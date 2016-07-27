<div class="clearfix"></div>
<br />
<div class="col-md-12">
	<a href="#"><button class="btn btn-info btn-sm pull-right" id="add-user"><img src="<?php echo base_url();?>assets/img/plus-white.png" width="12" data-tooltip="Add User"/> Add User</button></a>
</div>
<div class="row col-md-12 data-list">
	<div class="table-header-title col-md-12">
		<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" />
		<div class="pull-left header-title">Users list</div>
	</div>
	<!--<div class="pull-left header-title" id="add-user"><img src="<?php echo base_url();?>assets/img/add_user.jpg" width="45" style="margin-top:35px;margin-bottom:10px;cursor:pointer" data-tooltip="Add user"/></div>-->
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
	<thead>
		<tr>
			<th class="th_loc">Email</th>
			<th>Firstname</th>
			<th>Lastname</th>
			<th>Location</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($users_list as $row):?>
		<tr class="gradeX">
			<td id="col-left"><?php echo $row->email; ?></td>
			<td><?php echo $row->firstname; ?></td>
			<td><?php echo $row->lastname; ?></td>
			<td id="col-right"><?php echo $row->location; ?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
	</table>
</div>	



<!-- Modal -->
<div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
    	<h4 class="modal-title" id="myModalLabel">Create New User</h4>
      </div>
      <div class="modal-body">
			<form role="form" id="adduserform" method="post" style="font-size:12px;">
				<div class="form-group error_vali"></div>
				<?php echo @$success; ?>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
							<label for="email addresss" class="pull-right">Email address</label>
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group ">
							<input type="email" class="form-control input-sm" id="" placeholder="Email" name="email">
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
							<label for="firstname" class="pull-right">Firstname</label>
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group ">
							<input type="text" class="form-control input-sm" id="" placeholder="Firstname" name="fname">
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
							<label for="lastname" class="pull-right">Lastname</label>
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group ">
							<input type="text" class="form-control input-sm" id="" placeholder="Lastname" name="lname">
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
							<label for="password" class="pull-right">Password</label>
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group ">
							<input type="password" class="form-control input-sm" placeholder="Password" id="similarto" name="password">
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
							<label for="confirm password" class="pull-right">Confirm Password</label>
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group ">
							<input type="password" class="form-control input-sm" placeholder="Confirm password" name="cpass">
						</div>
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
							<input type="text" class="form-control input-sm" placeholder="Manila" name="location">
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
							<label for="latlng" class="pull-left">Latitude and Longtitude</label>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group ">
							<input type="text" class="form-control" placeholder="" name="lat">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group ">
							<input type="text" class="form-control" placeholder="" name="lng">
						</div>
					</div>
				</div>
				
				
					
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default btn-sm" id="" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-sm" id='save_user'>Save</button>
      </div>
      </form>
    </div>
  </div>
</div>