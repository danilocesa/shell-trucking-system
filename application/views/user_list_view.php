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
			<th>First Name</th>
			<th>Last Name</th>
			<th>Location</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($users_list as $row):?>
		<tr class="gradeX">
			<td id="col-left"><?php echo $row->email; ?></td>
			<td><?php echo $row->firstname; ?></td>
			<td><?php echo $row->lastname; ?></td>
			<td id="col-right"><?php 
				if($row->location != 0){
					echo @$this->dan_model->select_where("depot_tb",array("depot_id"=>$row->location))->depot_name;
				} else{
					echo "<p style='color:red;'>Not assigned</p>";
				}
				?></td>
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
							<input maxlength="50" type="email" class="form-control input-sm" id="" placeholder="Email" name="email">
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
							<input maxlength="50" type="text" class="form-control input-sm" id="" placeholder="Firstname" name="fname">
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
							<input maxlength="50" type="text" class="form-control input-sm" id="" placeholder="Lastname" name="lname">
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
							<input type="password" class="form-control input-sm" placeholder="Password" id="similarpass" name="password">
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
							<input type="password" class="form-control input-sm" placeholder="Confirm password" name="cpass">
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
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
							<label for="answer" class="pull-right">Answer<label style="color:red;"> *<label></label>
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group ">
							<input maxlength="60" type="text" class="form-control input-sm" placeholder="Answer" name="answer">
						</div>
					</div>		
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group ">
						<label for="questions" class="pull-right">User Level<label style="color:red;"> *<label></label>
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group ">
							<select class="form-control input-sm" name="user_level">
								<option value="">Select option</option>
								<option value="1">Admin access</option>
								<option value="2">Group 1</option>
							</select>	
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
							  <select class="form-control chosen-select input-sm" name="addlocation" id="userAddDepot" style="padding:0!important;border:0!important" >
							  	<option value=""></option>
					            <?php $depots = $this->dan_model->select_table("depot_tb"); 
					            foreach($depots as $row):?>
					              <option value="<?php echo $row->depot_id; ?>" data-latitude="<?php echo $row->center_latitude; ?>" data-longtitude="<?php echo $row->center_longitude;?>" data-id="<?php echo $row->depot_id; ?>"><?php echo $row->depot_name; ?>
					              </option>
					            <?php endforeach;?>
					          </select>
							<!--input type="text" class="form-control input-sm" placeholder="Manila" name="location"-->
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-md-12">
					<div class="col-md-4 hide">
						<div class="form-group ">
							<label for="latlng" class="pull-left">Latitude and Longtitude<label style="color:red;"> *<label></label>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group ">
							<input type="hidden" class="form-control" placeholder="" name="lat" id="latitude_user">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group ">
							<input type="hidden" class="form-control" placeholder="" name="lng" id="longitude_user">
						</div>
					</div>
					<p  style="margin-left:182px;color:red;width:260px;"id="latlng_user"></p>
				</div>
				
				<input type="hidden" name="yourlocation" id="yourlocation" />
					
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default btn-sm" id="" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-sm" id='save_user'>Save</button>
      </div>
      </form>
    </div>
  </div>
</div>