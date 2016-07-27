<div class="clearfix"></div>
<br />
<div class="col-md-12">
	<a href="<?php echo base_url('google');?>"><button class="btn btn-info btn-sm pull-right"><img src="<?php echo base_url();?>assets/img/plus-white.png" width="12"/> Add Route</button></a>
</div>
<div class="row col-md-12 data-list">
	
	<div class="table-header-title col-md-12">
		<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width: 20px;" /><div class="pull-left header-title">Route list</div>
	</div>
	
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
		<thead>
			<tr>
				<th class="" style="width:100px;">Ship To #</th>
				<th class="th_loc">Location</th>
				<th class="th_title">Title</th>
				<th>Information</th>
				<th>Created By</th>
				<th>Created Date</th>
				<th class="th_act" id="cur">Action</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($routes_list as $row):?>
			<tr class="gradeX">
				<td id="col-left"><?php echo $row->ship_to; ?></td>
				<td><?php echo $row->start." to ".$row->end; ?></td>
				<td><?php echo $row->title; ?></td>
				<td><?php echo word_limiter($row->info, 5); ?></td>
				<td><?php echo user_info($row->created_by,"firstname")." ".user_info($row->created_by,"lastname"); ?></td>
				<td><?php echo $row->created_date; ?></td>
				<td class="list-actions" id="col-right">
					<a href="<?php echo base_url("google/fetch_waypoints/".$row->route_id);?>" class="view_details" data-tooltip="View Details"><img src="<?php echo base_url();?>assets/img/list-blue2.png" /></a> 
					<img src="<?php echo base_url();?>assets/img/recycle_route.png" class="delete_routes" data-id="<?php echo $row->route_id; ?>" data-tooltip="Delete Route"/>
					
				</td>
			</tr>
			<?php endforeach;?>
			
		</tbody>
	</table>
</div>	