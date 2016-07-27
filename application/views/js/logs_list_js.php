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


$('#example').dataTable({
	"oLanguage": {
      "sInfo": "",
      "sLengthMenu": "_MENU_",
      "sSearch":"",
      "oPaginate": {
    		"sNext": '>',
    		"sPrevious": '<'
    	}
    },
    "bProcessing": true,
    "deferRender": true,
	"sPaginationType": "full_numbers",
	"aaSorting": [[0, "desc"]],
	"bInfo" : false
});

$(".dataTables_length select").addClass("form-control");
$(".dataTables_filter input").addClass("form-control").attr("placeholder","Search");
