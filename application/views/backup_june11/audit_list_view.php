<div class="clearfix"></div>
<br />
<div class="row col-md-12 data-list">
	<div class="table-header-title col-md-12">
		<img src="<?php echo base_url();?>assets/img/list-white.PNG" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Audit Trail</div>
	</div>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
		<thead>
			<tr>
				<th>Created By</th>
				<th>Created Date</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($audit_list as $row):?>
			<tr class="gradeX">
				<td id="col-left"><?php echo $row->firstname; ?></td>
				<td><?php echo $row->date; ?></td>
				<td id="col-right"><?php echo $row->description; ?></td>
			</tr>
			<?php endforeach;?>
		</tbody>
		</table>
</div>	