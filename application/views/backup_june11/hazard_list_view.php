<div class="clearfix"></div>
<br />
<div id="tabs">
	<ul>
	  <li><a href="#tabs-1">Hazards List</a></li>
	  <li><a href="#tabs-2">Sites List</a></li>
	  <li><a href="#tabs-3">Depots List</a></li>
	</ul>
	<div class="col-md-12">
		<a href="<?php echo base_url('google/add_hazard');?>"><button class="btn btn-info btn-xs pull-right" style="font-size:12px;padding: 4px;"><img src="<?php echo base_url();?>assets/img/plus-white.png" width="10" style="margin-bottom: 3px;"/> Add Hazard</button></a>
	</div>
	<div class="row col-md-12 data-list" id="tabs-1">
		
		<div class="table-header-title col-md-12">
			<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Hazards list</div>
		</div>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" >
			<thead>
				<tr>
					<th>Image</th>
					<th class="th_loc">Location</th>
					<th class="th_title">Title</th>
					<th>Information</th>
					<th>Created Date</th>
					<th class="th_act">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($hazard_list as $row):?>
				<tr class="gradeX">
					<td id="col-left"><img src="<?php echo base_url("uploads/".$row->hazard_image); ?>" height="80" width="80" /></td>
					<td><?php echo $row->location; ?></td>
					<td><?php echo $row->title; ?></td>
					<td><?php echo word_limiter($row->information, 5); ?></td>
					<td><?php echo $row->created_date; ?></td>
					<td class="list-actions" id="col-right">
						<a href="<?php echo base_url("google/edit_hazard/".$row->hazard_id);?>" class="view_details" data-tooltip="Edit Details"><img src="<?php echo base_url();?>assets/img/list-blue2.png" /></a>
						<img src="<?php echo base_url();?>assets/img/recycle_route.png" class="delete_hazard delete_list" data-id="<?php echo $row->hazard_id; ?>" data-tooltip="Delete Route"/>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
	<div class="row col-md-12 data-list" id="tabs-2">
		<div class="table-header-title col-md-12">
			<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Sites list</div>
		</div><br />
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="site-list">
			<thead>
				<tr>
					<th>Image</th>
					<th class="th_loc">Location</th>
					<th class="th_title">Title</th>
					<th>Information</th>
					<th>Created Date</th>
					<th class="th_act hide">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($site_list as $row):?>
				<tr class="gradeX">
					<td id="col-left"><img src="<?php echo base_url("uploads/sites/".$row->site_img); ?>" height="80" width="80" /></td>
					<td><?php echo $row->site_location; ?></td>
					<td><?php echo $row->site_name; ?></td>
					<td><?php echo word_limiter($row->site_information, 5); ?></td>
					<td><?php echo $row->created_date; ?></td>
					<td class="list-actions hide" id="col-right">
						<a href="<?php echo base_url("google/edit_hazard/".$row->site_id);?>" class="view_details" data-tooltip="Edit Details"><img src="<?php echo base_url();?>assets/img/list-blue2.png" /></a>
						<img src="<?php echo base_url();?>assets/img/recycle_route.png" class="delete_hazard delete_list" data-id="<?php echo $row->site_id; ?>" data-tooltip="Delete Route"/>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
	<div class="row col-md-12 data-list" id="tabs-3">
		<div class="table-header-title col-md-12">
			<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Depots list</div>
		</div><br />
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="depot-list">
			<thead>
				<tr>
					<th>Image</th>
					<th class="th_loc">Location</th>
					<th class="th_title">Title</th>
					<th>Information</th>
					<th>Created Date</th>
					<th class="th_act hide">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($depot_list as $row):?>
				<tr class="gradeX">
					<td id="col-left"><img src="<?php echo base_url("uploads/".$row->depot_img); ?>" height="80" width="80" /></td>
					<td><?php echo $row->depot_location; ?></td>
					<td><?php echo $row->depot_name; ?></td>
					<td><?php echo word_limiter($row->depot_information, 5); ?></td>
					<td><?php echo $row->created_date; ?></td>
					<td class="list-actions hide" id="col-right">
						<a href="<?php echo base_url("google/edit_hazard/".$row->depot_id);?>" class="view_details" data-tooltip="Edit Details"><img src="<?php echo base_url();?>assets/img/list-blue2.png" /></a>
						<img src="<?php echo base_url();?>assets/img/recycle_route.png" class="delete_hazard delete_list" data-id="<?php echo $row->depot_id; ?>" data-tooltip="Delete Route"/>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>	