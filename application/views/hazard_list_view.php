<div class="clearfix"></div>
<br />
<div id="tabs">
	<ul>
	  <li><a href="#tabs-1">Hazards List</a></li>
	  <li><a href="#tabs-2">Sites List</a></li>
	  <li><a href="#tabs-3">Depots List</a></li>
	</ul>
	<div id="tabs-1">
	<div class="col-md-12">
		<a href="<?php echo base_url('add-hazards');?>"><button class="btn btn-info btn-xs pull-right" style="font-size:12px;padding: 4px;margin-top:10px;"><img src="<?php echo base_url();?>assets/img/plus-white.png" width="10" style="margin-bottom: 3px;"/> Add Hazard</button></a>
	</div>
	<div class="row col-md-12 data-list">
		<div class="clearfix"></div>
		<div class="table-header-title col-md-12">
			<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Hazards list</div>
		</div>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" >
			<button id="delete-hazardlist" class="btn btn-danger delete-list">Delete <span class="glyphicon glyphicon-trash"></span></button>
			<thead>
				<tr>
					<th><input type="checkbox" name="hazardAllList" /> All</th>
					<th>Image</th>
					<th class="th_loc">Location</th>
					<th class="th_title">Title</th>
					<th>Created Date</th>
					<th>Updated By</th>
					<th>Status</th>
					<th class="th_act">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($hazard_list as $row):?>
				<tr class="gradeX">
					<td><input type="checkbox" name="selectedHazard[]" data-name="<?php echo $row->title; ?>" data-id="<?php echo $row->hazard_id; ?>" /></td>
					<td id="col-left"><img src="<?php echo base_url("uploads/".$row->hazard_image); ?>" height="80" width="80" /></td>
					<td><?php echo $row->location; ?></td>
					<td style="word-break:break-all"><?php echo strlen($row->title) > 50 ? strip_tags(substr($row->title, 0, 50).'...') : strip_tags($row->title); ?></td>
					<td><?php echo $row->created_date; ?></td>
					<td><?php echo @user_info($row->last_update_by,"firstname")." ".@user_info($row->last_update_by,"lastname");?></td>
					<td><?php if($row->status == 1):?><span class="label label-info">Permanent</span><?php else: ?><span class="label label-warning">Temporary</span><?php endif;?></td>
					<td class="list-actions" id="col-right">
						<span class="glyphicon glyphicon-eye-open view_hazard" data-tooltip="View Details" style="cursor:pointer;" data-id="<?php echo $row->hazard_id;?>"></span>
						<a href="<?php echo base_url("edit-hazard/".$row->hazard_id);?>" class="view_details" data-tooltip="Edit Details"><img src="<?php echo base_url();?>assets/img/list-blue2.png" /></a>&nbsp;<a href="<?php echo base_url("logs/h/".$row->hazard_id);?>" class="view_details" data-tooltip="Logs"><img src="<?php echo base_url();?>assets/img/log_images.png" /></a>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
	</div>
	<div id="tabs-2">
	<div class="col-md-12">
		<a href="<?php echo base_url('add-sites');?>"><button class="btn btn-info btn-xs pull-right" style="font-size:12px;padding: 4px;margin-top:10px;"><img src="<?php echo base_url();?>assets/img/plus-white.png" width="10" style="margin-bottom: 3px;"/> Add Site</button></a>
	</div>
	<div class="row col-md-12 data-list">
		<div class="table-header-title col-md-12">
			<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Sites list</div>
		</div><br />
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="site-list">
			<button id="delete-sitelist" class="btn btn-danger delete-list">Delete <span class="glyphicon glyphicon-trash"></span></button>
			<thead>
				<tr>
					<th><input type="checkbox" name="siteAllList" /> All</th>
					<th>Image</th>
					<th class="th_loc">Location</th>
					<th class="th_title">Title</th>
					<th>Created Date</th>
					<th>Updated By</th>
					<th class="th_act">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($site_list as $row):?>
				<tr class="gradeX">
					<td><input type="checkbox" name="selectedSite[]" data-name="<?php echo $row->site_name; ?>" data-id="<?php echo $row->site_id; ?>" /></td>
					<td id="col-left"><img src="<?php echo base_url("uploads/sites/".$row->site_img); ?>" height="80" width="80" /></td>
					<td><?php echo $row->site_location; ?></td>
					<td style="word-break:break-all"><?php echo strlen($row->site_name) > 50 ? strip_tags(substr($row->site_name, 0, 50).'...') : strip_tags($row->site_name); ?></td>
					<td><?php echo $row->created_date; ?></td>
					<td><?php echo @user_info($row->last_update_by,"firstname")." ".@user_info($row->last_update_by,"lastname");?></td>
					<td class="list-actions" id="col-right">
						<span class="glyphicon glyphicon-eye-open view_site" data-tooltip="View Details" style="cursor:pointer;" data-id="<?php echo $row->site_id;?>"></span>
						<a href="<?php echo base_url("edit-site/".$row->site_id);?>" class="view_details" data-tooltip="Edit Details"><img src="<?php echo base_url();?>assets/img/list-blue2.png" /></a>&nbsp;<a href="<?php echo base_url("logs/s/".$row->site_id);?>" class="view_details" data-tooltip="Logs"><img src="<?php echo base_url();?>assets/img/log_images.png" /></a>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
	</div>
	<div id="tabs-3">
	<?php if(get_session("login")['access_level'] == 1):?>	
	<div class="col-md-12">
		<a href="<?php echo base_url('add-depots');?>"><button class="btn btn-info btn-xs pull-right" style="font-size:12px;padding: 4px;margin-top:10px;"><img src="<?php echo base_url();?>assets/img/plus-white.png" width="10" style="margin-bottom: 3px;"/> Add Depot</button></a>
	</div>
	<?php endif;?>
	<div class="row col-md-12 data-list">
		<div class="table-header-title col-md-12">
			<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Depots list</div>
		</div><br />
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="depot-list">
			<button id="delete-depotlist" class="btn btn-danger delete-list">Delete <span class="glyphicon glyphicon-trash"></span></button>
			<thead>
				<tr>
					<th><input type="checkbox" name="depotAllList" /> All</th>
					<th>Image</th>
					<th class="th_loc">Location</th>
					<th class="th_title">Title</th>
					<th>Created Date</th>
					<th>Updated By</th>
					<th class="th_act">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($depot_list as $row):?>
				<tr class="gradeX">
					<td><input type="checkbox" name="selectedDepot[]" data-name="<?php echo $row->depot_name; ?>" data-id="<?php echo $row->depot_id; ?>" /></td>
					<td id="col-left"><img src="<?php echo base_url("uploads/depots/".$row->depot_img); ?>" height="80" width="80" /></td>
					<td><?php echo $row->depot_location; ?></td>
					<td style="word-break:break-all"><?php echo strlen($row->depot_name) > 50 ? strip_tags(substr($row->depot_name, 0, 50).'...') : strip_tags($row->depot_name); ?></td>
					<td><?php echo $row->created_date; ?></td>
					<td><?php echo @user_info($row->last_update_by,"firstname")." ".@user_info($row->last_update_by,"lastname");?></td>
					<td class="list-actions" id="col-right">
						<span class="glyphicon glyphicon-eye-open view_depot" data-tooltip="View Details" style="cursor:pointer;" data-id="<?php echo $row->depot_id; ?>"></span>
						<a href="<?php echo base_url("edit-depot/".$row->depot_id);?>" class="view_details" data-tooltip="Edit Details"><img src="<?php echo base_url();?>assets/img/list-blue2.png" /></a>&nbsp;<a href="<?php echo base_url("logs/d/".$row->depot_id);?>" class="view_details" data-tooltip="Logs"><img src="<?php echo base_url();?>assets/img/log_images.png" /></a>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>	
</div>	


<!-- Hazard Details Modal -->
<div class="modal fade" id="viewHazards" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Hazard Details</h4>
      </div>
      <div class="modal-body">
		<form class="form-horizontal" id="add_hazard_form" enctype="multipart/form-data" role="form" style="font-size:12px;">
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Created By</label>
				<div class="col-sm-4 ">
					<input type="text" id="createdBy_modal" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
				<label for="location" class="col-sm-2 control-label">Created Date</label>
				<div class="col-sm-4 ">
					<input type="text" id="created_modal" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
			</div>
			<div class="form-group" id="last_update">
				<label for="location" class="col-sm-2 control-label">Last Update By</label>
				<div class="col-sm-4 ">
					<input type="text" id="last_update_by" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
			</div>
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Location</label>
				<div class="col-sm-10">
					<input type="email" class="form-control input-xs" id="location_modal" disabled >
				</div>
			</div>
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Latitude</label>
				<div class="col-sm-4 ">
					<input type="text" id="lat_modal" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
				<label for="location" class="col-sm-2 control-label">Longitude</label>
				<div class="col-sm-4 ">
					<input type="text" id="long_modal" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>
			</div>
			<div class="form-group" id="haz_status">
				<label for="status" class="col-sm-2 control-label">Status</label>
				<div class="col-sm-4">
					<input type="email" class="form-control input-xs" id="stat_modal" disabled >
				</div>
				<div class="col-sm-3 start_end-date">
					<label>Start</label>
					<input type="text" id="start_date" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled"  style="width:140px!important;display:inline" />
				</div>	
				<div class="col-sm-3 start_end-date"><label>End</label>
					<input type="text" id="end_date" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled"  style="width:140px!important;display:inline" />
				</div>
			</div>
			<div class="form-group icon-category">
				<label for="icon" class="col-sm-2 control-label">Category</label>
				<div class="col-sm-3">
					<input type="email" class="form-control input-xs" id="cate_modal" disabled >
				</div>	
			</div>
			<div class="form-group">
				<label for="title" class="col-sm-2 control-label">Title</label>
				<div class="col-sm-10">
					<input type="text" class="form-control input-sm" id="title_modal" name="hazard_title" disabled />
				</div>
			</div>
			<div class="form-group speed-limit">
				<label for="controls" class="col-sm-2 control-label">Speed Limit</label>
				<div class="col-sm-2">
					<input type="text" class="form-control input-sm" id="speed_modal" name="hazard_title" disabled />
				</div>
			</div>
			<div class="form-group haz-controls">
				<label for="controls" class="col-sm-2 control-label">Controls</label>
				<div class="col-sm-10">
					<textarea class="form-control" rows="3" id="control_modal" name="hazard_control" readonly ></textarea>
				</div>
			</div>
			<div class="form-group">
				<label for="information" class="col-sm-2 control-label">Information</label>
				<div class="col-sm-10">
					<textarea class="form-control" rows="3" id="info_modal" name="hazard_info" readonly ></textarea>
				</div>
			</div>

			</div>
			<div class="modal-footer" style="margin-top:-20px;padding-top:8px;">
			<button type="button" class="btn btn-default btn-sm btn-info" data-dismiss="modal" id="modalclose">Close</button>
			</div>
		</form>
      </div>
    </div>
  </div>
</div>

<!-- Site Details Modal -->
<div class="modal fade" id="viewSite" tabindex="-1" role="dialog" aria-labelledby="siteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="siteModalLabel">Site Details</h4>
      </div>
      <div class="modal-body">
		<form class="form-horizontal" id="add_hazard_form" enctype="multipart/form-data" role="form" style="font-size:12px;">
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Created Date</label>
				<div class="col-sm-4 ">
					<input type="text" id="site_created" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
				<label for="location" class="col-sm-2 control-label">Created By</label>
				<div class="col-sm-4 ">
					<input type="text" id="site_createdby" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>
			</div>
			<div class="form-group" id="site_last_update">
				<label for="location" class="col-sm-2 control-label">Last Update By</label>
				<div class="col-sm-4 ">
					<input type="text" id="last_update_site" name="" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
			</div>
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Location</label>
				<div class="col-sm-10">
					<input type="email" class="form-control input-xs" id="site_location" disabled >
				</div>
			</div>
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Latitude</label>
				<div class="col-sm-4 ">
					<input type="text" id="site_lat" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
				<label for="location" class="col-sm-2 control-label">Longitude</label>
				<div class="col-sm-4 ">
					<input type="text" id="site_long" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>
			</div>
			<div class="form-group">
				<label for="title" class="col-sm-2 control-label">Title</label>
				<div class="col-sm-10">
					<input type="text" class="form-control input-sm" id="site_title" name="hazard_title" disabled />
				</div>
			</div>
			<div class="form-group">
				<label for="information" class="col-sm-2 control-label">Information</label>
				<div class="col-sm-10">
					<textarea class="form-control" rows="3" id="site_info" name="hazard_info" readonly ></textarea>
				</div>
			</div>

			</div>
			<div class="modal-footer" style="margin-top:-20px;padding-top:8px;">
			<button type="button" class="btn btn-default btn-sm btn-info" data-dismiss="modal" id="modalclose">Close</button>
			</div>
		</form>
      </div>
    </div>
  </div>
</div>


<!-- Depot Details Modal -->
<div class="modal fade" id="viewDepot" tabindex="-1" role="dialog" aria-labelledby="siteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="siteModalLabel">Depot Details</h4>
      </div>
      <div class="modal-body">
		<form class="form-horizontal" id="add_hazard_form" enctype="multipart/form-data" role="form" style="font-size:12px;">
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Created Date</label>
				<div class="col-sm-4 ">
					<input type="text" id="depot_created" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
				<label for="location" class="col-sm-2 control-label">Created By</label>
				<div class="col-sm-4 ">
					<input type="text" id="depot_createdby" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>
			</div>
			<div class="form-group" id="depot_last_update">
				<label for="location" class="col-sm-2 control-label">Last Update By</label>
				<div class="col-sm-4 ">
					<input type="text" id="last_update_depot" name="" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
			</div>
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Location</label>
				<div class="col-sm-10">
					<input type="email" class="form-control input-xs" id="depot_location" disabled >
				</div>
			</div>
			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Latitude</label>
				<div class="col-sm-4 ">
					<input type="text" id="depot_lat" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>	
				<label for="location" class="col-sm-2 control-label">Longitude</label>
				<div class="col-sm-4 ">
					<input type="text" id="depot_long" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled" />
				</div>
			</div>
			<div class="form-group">
				<label for="title" class="col-sm-2 control-label">Title</label>
				<div class="col-sm-10">
					<input type="text" class="form-control input-sm" id="depot_title" name="hazard_title" disabled />
				</div>
			</div>
			<div class="form-group">
				<label for="information" class="col-sm-2 control-label">Information</label>
				<div class="col-sm-10">
					<textarea class="form-control" rows="3" id="depot_info" name="hazard_info" readonly ></textarea>
				</div>
			</div>

			</div>
			<div class="modal-footer" style="margin-top:-20px;padding-top:8px;">
			<button type="button" class="btn btn-default btn-sm btn-info" data-dismiss="modal" id="modalclose">Close</button>
			</div>
		</form>
      </div>
    </div>
  </div>
</div>