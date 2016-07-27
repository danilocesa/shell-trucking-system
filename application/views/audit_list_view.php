<div class="clearfix"></div>
<br />
<div class="row col-md-12 data-list">
	<div class="table-header-title col-md-12">
		<img src="<?php echo base_url();?>assets/img/list-white.png" class="pull-left" style="margin-top:5px;margin-right:10px;width:20px;" /><div class="pull-left header-title">Audit Trail</div>
	</div>
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
		<thead>
			<tr>
				<th>User</th>
				<th>Date/Time</th>
				<th>Details</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($audit_list as $row):?>
			<tr class="gradeX">
				<td style="width:275px;" id="col-left"><?php echo $row->firstname; ?></td>
				<td style="width:120px;"><?php echo $row->date; ?></td>
				<td style="word-break:break-all;width:500px" id="col-right"><?php echo strip_tags($row->description); ?></td>
			</tr>
			<?php endforeach;?>
		</tbody>
		</table>
</div>
<script>
// **************** Web Socket ***********************	 
conn.onmessage = function(e) {
	try {
		// Parse message
		var parsedJSON = JSON.parse(e.data);
		console.log(parsedJSON);
		
		switch (parsedJSON.action) {
			case 'CL':
				// Get id and ip of user
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				jax.open('POST',base_url+'/google/get_user_id');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=fetch')
				jax.onreadystatechange = function(){ 
					if(jax.readyState==4) {
						if(jax.responseText != null){
							try {
								// Parse message
								var data = JSON.parse(jax.responseText);
								// If same user id is accessed on different IP, log out the previous client
								// using this user id
								if (data.id == parsedJSON.id){
									window.location.href = "<?php echo base_url('another_user'); ?>";
								}
							} catch (err) {
								console.debug(err);
							}
						}
					}
				}
				break;
		}
	} catch (err) {
		console.debug(err);
	}
};
</script>