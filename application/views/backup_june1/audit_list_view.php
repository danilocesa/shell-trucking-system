<div class="clearfix"></div>
<br />
<div class="row col-md-12 data-list">
	<div class="table-header-title col-md-12">
		<img src="<?php echo base_url();?>assets/img/list-bg.PNG" class="pull-left" style="margin-top:4px;margin-right:10px;" /><div class="pull-left header-title">List</div>
	</div>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
		<thead>
			<tr>
				<th>Firstname</th>
				<th>Date</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($audit_list as $row):?>
			<tr class="gradeX">
				<td><?php echo $row->firstname; ?></td>
				<td><?php echo $row->date; ?></td>
				<td><?php echo $row->description; ?></td>
			</tr>
			<?php endforeach;?>
		</tbody>
		</table>
</div>	