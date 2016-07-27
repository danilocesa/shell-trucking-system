<h1>Information List</h1>
<div class="row">
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
	<thead>
		<tr>
			<th>Location</th>
			<th>Title</th>
			<th>Information</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($info_list as $row):?>
		<tr class="gradeX">
			<td><?php echo $row->location; ?></td>
			<td><?php echo word_limiter($row->title, 5); ?></td>
			<td><?php echo word_limiter($row->information, 5); ?></td>
			<td><a href="<?php echo base_url("google/fetch_info/".$row->info_id);?>"><button type="button" class="btn btn-info">Details</button></a> <button type="button" class="btn btn-danger delete_info" data-id="<?php echo $row->info_id; ?>">Delete</button></td>
		</tr>
		<?php endforeach;?>
	</tbody>
	</table>
</div>