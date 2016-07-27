<div class="clearfix"></div>
<br />
<div class="row col-md-12 data-list">
	<div class="table-header-title col-md-12">
		<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">&nbsp;<?php echo $logTitle; ?> Logs</div>
	</div>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
			<thead>
				<tr>
					<th>Date</th>
					<th>User</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($logsList as $row):?>
				<tr class="gradeX">
					<td style="width:275px;" id="col-left"><?php echo $row->date; ?></td>
					<td style="width:120px;"><?php echo user_info($row->user_id,"firstname")." ".user_info($row->user_id,"lastname"); ?></td>
					<td style="word-break:break-all;width:500px" id="col-right"><?php echo strip_tags($row->description); ?></td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
</div>