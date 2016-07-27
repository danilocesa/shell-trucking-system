var check = <?php echo (isset($_GET['check']) && $_GET['check'] == 'true') ? 'true' : 'false' ?>;
var token = '<?php echo isset($_GET['token']) ? $_GET['token'] : '' ?>';

function checkLogin(){
	if (conn.readyState != 1) {
		console.log('Socket server not ready. Retrying to check...');
		setTimeout(checkLogin, 1000);
		return;
	}

	if (check) {
		// Get id and ip of newly logged in user
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
						
						// Broadcast message to socket clients
						var myJSON = {
							action: 'CL',
							id: data.id,
							ip: data.ip,
							token: token
						};
						
						// Send as JSON
						conn.send(JSON.stringify(myJSON));
					} catch (err) {
						console.debug(err);
					}
				}
			}
		}
	}
}

$(document).ready(function() {
	setTimeout(checkLogin, 1000);
	
	// Check box check
	$(document).on("click", "input[name='selected[]']", function(){
		// Confirm to all clients if this route is ok to be deleted
		if ($(this).is(":checked")){
			// Broadcast message to socket clients
			var myJSON = {
				action: 'CHKRT',
				id: $(this).attr("data-id")
			};
							
			// Broadcast ID
			conn.send(JSON.stringify(myJSON));
		}
	});
});

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
									// If accessing on the same IP, log out the previous session
									if (data.ip == parsedJSON.ip){
										if (check){
											if (token == parsedJSON.token){
												console.log('Checking...');
												// this client is currently checking, don't bother
											} else {
												window.location.href = "<?php echo base_url('another_user'); ?>";
											}
										} else {
											window.location.href = "<?php echo base_url('another_user'); ?>";
										}
									} else {
										window.location.href = "<?php echo base_url('another_user'); ?>";
									}
								}
							} catch (err) {
								console.debug(err);
							}
						}
					}
				}
				break;
			case 'RESRT': // Route ID check response from Route Details module
				// If there is response, notify user that route is not available for deletion
				smoke.signal("Cannot delete route '" + $("input[data-id='" + parsedJSON.id + "']").attr("data-title") + "'. Route currently open.", function(e){ }, {
					duration: 3000
				});
				// Uncheck checkbox
				$("input[data-id='" + parsedJSON.id + "']").prop("checked", false);
				break;
			case 'CHKRT2': // Check for route id from Route Details module
				if ($("input[data-id='" + parsedJSON.id + "']").is(":checked")) {
					// If there is response, notify user that route is not available for deletion
					smoke.signal("Cannot delete route '" + $("input[data-id='" + parsedJSON.id + "']").attr("data-title") + "'. Route currently open.", function(e){ }, {
						duration: 3000
					});
					// Uncheck checkbox
					$("input[data-id='" + parsedJSON.id + "']").prop("checked", false);
				}
				break;
			default: return;
		}
	} catch (err) {
		console.debug(err);
	}
};

oTable = $('#example').dataTable({
	"oLanguage": {
      "sInfo": "",
      "sLengthMenu": "_MENU_",
      "sSearch":"",
      "oPaginate": {
    		"sNext": '>',
    		"sPrevious": '<'
    	},
    },
    "bInfo" : false,
    "sPaginationType": "full_numbers",
    "aoColumnDefs": [{
		'bSortable': false, 
		'aTargets': [ 0,-1 ] 
	}],
	"aaSorting": [[5, "desc"]],
	"bProcessing": true,
	"bServerSide": true,
	"sAjaxSource": "<?php site_url(); ?>google/routes_listData",
	"sServerMethod": "POST",
	'fnServerData': function(sSource, aoData, fnCallback){
    	$.ajax({
	        'dataType': 'json',
	        'type'    : 'POST',
	        'url'     : sSource,
	        'data'    : aoData,
	        'success' : fnCallback
	    });
    }
});

$(document).on('click', '.delete_routes', function() {
		var id = $(this).attr("data-id");
		smoke.confirm("Are you sure you want to delete?", function(e){
			if (e){
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				jax.open('POST',base_url+'/google/routes');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=delete&id='+id);
				jax.onreadystatechange = function(){ 
					if(jax.readyState==4) {
						if(jax.responseText == 'success'){
							smoke.signal("Routes deleted, redirecting to the list..", function(e){
								setTimeout(function(){window.location=base_url+"/routes-list"},500);
							}, {
								duration: 500
							});
						}
						else{ 
							smoke.signal(jax.responseText, function(e){
							}, {
								duration: 9999
							});
						}
					}
				}	
			}
		}, {
			ok: "Yes",
			cancel: "Cancel",
			classname: "custom-class",
			reverseButtons: true
		});
	
	//});
	
});


$(".dataTables_length select").addClass("form-control");
$(".dataTables_filter input").addClass("form-control").attr("placeholder","Search");


$(':checkbox[name=checkallList]').click (function () {
	var chk = $(this).prop('checked');
   	$('input', oTable.fnGetNodes()).prop('checked',chk);
});

$("input[name='selected[]']").click(function(){
	$("input[name='checkallList']").removeAttr("checked");
});

$(".delete-list").click(function(){
	var delete_stat = false;
	var checkArray = [];
	var rowcollection =  oTable.$("input[name='selected[]']", {"page": "all"});
	rowcollection.each(function(index,elem){
		if($(elem).is(':checked')){
			checkArray.push($(elem).attr('data-id'));
		}
	});
	if(checkArray.length === 0){
		smoke.signal("Please select route to delete", function(e){
		}, {
			duration: 1500
		});
		return false;
	}
	smoke.confirm("Are you sure you want to delete?", function(e){
	if (e){
	//All checked
	if($("input[name='checkallList']").is(":checked")){
		$.ajax({
			cache:false,
			async:false,
			dataType: "json",
			url: base_url+"/google/delete_route/all",
			type: "post",
			beforeSend: function(){
				$("#dvLoading").fadeIn(300);
			},
			success: function(data){
				if(data.empty == true){
					smoke.signal("All routes deleted, reloading the list..", function(e){
						setTimeout(function(){window.location=base_url+"/routes-list"},500);
					}, {
						duration: 500
					});
				}
				
			},
			complete: function(){
				$("#dvLoading").fadeOut(1000);
			},
			error: function(xhr, stat, str){
				console.log(xhr);
				console.log(stat);
				console.log(str);
			}

		});
	} 
	//Each one
	else{
		$.ajax({
			cache:false,
			async:false,
			dataType: "json",
			url: base_url+"/google/delete_route/",
			type: "post",
			data: {checkboxarray: checkArray },
			beforeSend: function(){
				$("#dvLoading").fadeIn(300);
			},
			success: function(data){
				if(data.deleted == true){
					smoke.signal("Routes deleted, reloading the list..", function(e){
						setTimeout(function(){window.location=base_url+"/routes-list"},500);
					}, {
						duration: 500
					});
				}
			},
			complete: function(){
				$("#dvLoading").fadeOut(1000);
			},
			error: function(xhr, stat, str){
				console.log(xhr);
				console.log(stat);
				console.log(str);
			}
		});	
	}
}}, {
	ok: "Yes",
	cancel: "Cancel",
	classname: "custom-class",
	reverseButtons: true
});
});