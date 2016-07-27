<div class="clearfix"></div>
<br />
<div class="row col-md-12 data-list">
	<div class="table-header-title col-md-12">
		<img src="<?php echo base_url();?>assets/img/list-bg.PNG" class="pull-left" style="margin-top:4px;margin-right:10px;" /><div class="pull-left header-title">Hazard list</div>
	</div>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
		<thead>
			<tr>
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
				<td><?php echo $row->location; ?></td>
				<td><?php echo $row->title; ?></td>
				<td><?php echo word_limiter($row->information, 5); ?></td>
				<td><?php echo $row->created_date; ?></td>
				<td class="list-actions">
					<a href="<?php echo base_url("google/edit_hazard/".$row->hazard_id);?>" class="view_details" data-tooltip="Edit Details"><img src="<?php echo base_url();?>assets/img/details_view.png" /></a>
					<img src="<?php echo base_url();?>assets/img/recycle_route.png" class="delete_hazard delete_list" data-id="<?php echo $row->hazard_id; ?>" data-tooltip="Delete Route"/>
				</td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
</div>	