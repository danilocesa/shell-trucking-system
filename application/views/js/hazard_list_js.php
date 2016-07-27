$(document).ready(function() {
	// Check box check
	$(document).on("click", "input[name='selectedHazard[]']", function(){
		if ($(this).is(":checked")){
			checkMarker($(this).attr("data-id"), 'h')
		}
	});
	$(document).on("click", "input[name='selectedSite[]']", function(){
		if ($(this).is(":checked")){
			checkMarker($(this).attr("data-id"), 's')
		}
	});
	$(document).on("click", "input[name='selectedDepot[]']", function(){
		if ($(this).is(":checked")){
			checkMarker($(this).attr("data-id"), 'd')
		}
	});
});

function checkMarker(id, type){
	// Confirm to all clients if this marker is ok to be deleted
	// Broadcast message to socket clients
	var myJSON = {
		action: 'CHKMR',
		id: id,
		type: type
	};
					
	// Broadcast ID
	conn.send(JSON.stringify(myJSON));
}

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
			case 'RESMR': // Route ID check response from Edit Marker module
				// If there is response, notify user that marker is not available for deletion
				smoke.signal("Cannot delete " + (parsedJSON.type == 'h' ? 'hazard' : parsedJSON.type == 's' ? 'site' : parsedJSON.type == 'd' ? 'depot' : '') + " '" + $("input[data-id='" + parsedJSON.id + "'][name='" + (parsedJSON.type == 'h' ? 'selectedHazard' : parsedJSON.type == 's' ? 'selectedSite' : parsedJSON.type == 'd' ? 'selectedDepot' : '') + "[]']").attr("data-name") + "'. Marker currently open.", function(e){ }, {
					duration: 3000
				});
				// Uncheck checkbox
				$("input[data-id='" + parsedJSON.id + "'][name='" + (parsedJSON.type == 'h' ? 'selectedHazard' : parsedJSON.type == 's' ? 'selectedSite' : parsedJSON.type == 'd' ? 'selectedDepot' : '') + "[]']").prop("checked", false);
				break;
			case 'CHKMR2': // Check for marker id from Edit Marker module
				if ($("input[data-id='" + parsedJSON.id + "'][name='" + (parsedJSON.type == 'h' ? 'selectedHazard' : parsedJSON.type == 's' ? 'selectedSite' : parsedJSON.type == 'd' ? 'selectedDepot' : '') + "[]']").is(":checked")) {
					// If there is response, notify user that route is not available for deletion
					smoke.signal("Cannot delete " + (parsedJSON.type == 'h' ? 'hazard' : parsedJSON.type == 's' ? 'site' : parsedJSON.type == 'd' ? 'depot' : '') + " '" + $("input[data-id='" + parsedJSON.id + "'][name='" + (parsedJSON.type == 'h' ? 'selectedHazard' : parsedJSON.type == 's' ? 'selectedSite' : parsedJSON.type == 'd' ? 'selectedDepot' : '') + "[]']").attr("data-name") + "'. Marker currently open.", function(e){ }, {
						duration: 3000
					});
					// Uncheck checkbox
					$("input[data-id='" + parsedJSON.id + "'][name='" + (parsedJSON.type == 'h' ? 'selectedHazard' : parsedJSON.type == 's' ? 'selectedSite' : 	parsedJSON.type == 'd' ? 'selectedDepot' : '') + "[]']").prop("checked", false);
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
    	}
    },
	"sPaginationType": "full_numbers",
	"aoColumnDefs": [{
		'bSortable': false, 
		'aTargets': [ 0, 1, -1 ]  
	}],
	"bProcessing": true,
    "deferRender": true,
	"bInfo" : false,
	"aaSorting": [[ 4, "desc" ]],
	"fnDrawCallback": function() {
		$(".view_hazard").on("click",function(){
			var hazs_id =$(this).attr('data-id');
			$.ajax({
				url: "<?php echo base_url();?>google/hazard_details/"+hazs_id,
				type: "post",
				dataType: "json",
				cache:false,
				success: function(data){
					$("#createdBy_modal").val(data.created_by);
					$("#created_modal").val(data.det.created_date);
					if(data.lastUpdate){
						$("#last_update").show();
						$("#last_update_by").val(data.lastUpdate);
					} else{
						$("#last_update").hide();
					}
					$("#location_modal").val(data.det.location);
					$("#lat_modal").val(data.det.center_latitude);
					$("#long_modal").val(data.det.center_longitude);
					if(data.det.status == 1){
						$("#stat_modal").val("Permanent");
						$(".start_end-date").hide();
					} else{
						$("#stat_modal").val("Temporary");
						$(".start_end-date").show();
						$("#start_date").val(data.det.start_date);
						if(data.det.end_date == "2100-12-23 11:59:59"){
							$("#end_date").val("");
						} else if(data.det.end_date == "1970-01-01 08:00:00"){
							$("#end_date").val("");
						}
						 else{
							$("#end_date").val(data.det.end_date);
						}
					}
					$("#cate_modal").val(toTitleCase(data.det.hazard_category));
					$("#title_modal").val(data.det.title);
					$("#speed_modal").val(data.det.speed_limit+" kph");
					$("#control_modal").text(data.det.hazard_control);
					$("#info_modal").text(data.det.information);
				},
				error: function(xhr, err){
					console.log(xhr);
				}
			});
		   $("#viewHazards").modal();
		});
	}
});
oSites = $('#site-list').dataTable({
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
	"aoColumnDefs": [{
		'bSortable': false, 
		'aTargets': [ 0, 1, -1 ]  
	}],
	"bInfo" : false,
	"aaSorting": [[ 4, "desc" ]],
	"fnDrawCallback": function() {
		$(".view_site").click(function(){
			var site_id =$(this).attr('data-id');
			$.ajax({
				url: "<?php echo base_url();?>google/site_details/"+site_id,
				type: "post",
				dataType: "json",
				cache:false,
				success: function(data){
					console.log(data);
					$("#site_created").val(data.det.created_date);
					$("#site_createdby").val(data.created_by);
					if(data.lastUpdate){
						$("#site_last_update").show();
						$("#last_update_site").val(data.lastUpdate);
					} else{
						$("#site_last_update").hide();
					}
					$("#site_location").val(data.det.site_location);
					$("#site_lat").val(data.det.center_latitude);
					$("#site_long").val(data.det.center_longitude);
					$("#site_title").val(data.det.site_name);
					$("#site_info").text(data.det.site_information);

				},
				error: function(xhr, err){
					console.log(xhr);
				}
			});
		   $("#viewSite").modal();
		});
	}
});
oDepot = $('#depot-list').dataTable({
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
	"aoColumnDefs": [{
		'bSortable': false, 
		'aTargets': [ 0, 1, -1 ]  
	}],
	"bInfo" : false,
	"aaSorting": [[ 4, "desc" ]],
	"fnDrawCallback": function() {
		$(".view_depot").click(function(){
			var depot_id =$(this).attr('data-id');
			$.ajax({
				url: "<?php echo base_url();?>google/depot_details/"+depot_id,
				type: "post",
				dataType: "json",
				cache:false,
				success: function(data){
					console.log(data);
					$("#depot_created").val(data.det.created_date);
					$("#depot_createdby").val(data.created_by);
					if(data.lastUpdate){
						$("#depot_last_update").show();
						$("#last_update_depot").val(data.lastUpdate);
					} else{
						$("#depot_last_update").hide();
					}
					$("#depot_location").val(data.det.depot_location);
					$("#depot_lat").val(data.det.center_latitude);
					$("#depot_long").val(data.det.center_longitude);
					$("#depot_title").val(data.det.depot_name);
					$("#depot_info").text(data.det.depot_information);

				},
				error: function(xhr, err){
					console.log(xhr);
				}
			});
		   $("#viewDepot").modal();
		});
	}
});

$(".dataTables_length select").addClass("form-control");
$(".dataTables_filter input").addClass("form-control").attr("placeholder","Search");

$( "#tabs" ).tabs();


$(':checkbox[name="hazardAllList"]').click (function () {
	var chk = $(this).prop('checked');
   	$('input', oTable.fnGetNodes()).prop('checked',chk);
});

$("input[name='selectedHazard[]']").click(function(){
	$("input[name='hazardAllList']").removeAttr("checked");
});

$("#delete-hazardlist").click(function(){
	var delete_stat = false;
	var checkNames = [];
	var checkArray = [];
	var rowcollection =  oTable.$("input[name='selectedHazard[]']", {"page": "all"});
	rowcollection.each(function(index,elem){
		if($(elem).is(':checked')){
			checkArray.push($(elem).attr('data-id'));
			checkNames.push($(elem).attr('data-name'));
		}
	});
	if(checkArray.length === 0){
		smoke.signal("Please select hazard to delete", function(e){
		}, {
			duration: 1500
		});
		return false;
	}
	smoke.confirm("Are you sure you want to delete?", function(e){
		if (e){
			//All checked
			if($("input[name='hazardAllList']").is(":checked")){
				$.ajax({
					cache:false,
					async:false,
					dataType: "json",
					url: base_url+"/google/delete_hazard/all",
					type: "post",
					beforeSend: function(){
						$("#dvLoading").fadeIn(300);
					},
					success: function(data){
						if(data.empty == true){
							smoke.signal("All hazards deleted, reloading the list..", function(e){
								setTimeout(function(){window.location=base_url+"/hazards-list"},500);
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
					url: base_url+"/google/delete_hazard/",
					type: "post",
					data: {checkboxarray: checkArray },
					beforeSend: function(){
						$("#dvLoading").fadeIn(300);
					},
					success: function(data){
						if(data.deleted == true){
							smoke.signal("Hazard(s) deleted, reloading the list..", function(e){
								setTimeout(function(){window.location=base_url+"/hazards-list"},500);
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
			/******* SOCKET BROADCAST ******/
			// Sample JS array
			var myJSON = {
				action: 'D',
				id: '',
				type: 'h',
				data: {
					hazard_id: checkArray,
					hazard_name: checkNames
				}
			};
			
			// Send as JSON
			conn.send(JSON.stringify(myJSON));
			
			/*******************************/
		}
	}, {
	ok: "Yes",
	cancel: "Cancel",
	classname: "custom-class",
	reverseButtons: true
});
});

$(':checkbox[name="siteAllList"]').click (function () {
	var chk = $(this).prop('checked');
   	$('input', oSites.fnGetNodes()).prop('checked',chk);
});

$("input[name='selectedSite[]']").click(function(){
	$("input[name='siteAllList']").removeAttr("checked");
});

$("#delete-sitelist").click(function(){
	var delete_stat = false;
	var checkNames = [];
	var checkArray = [];
	var rowcollection =  oSites.$("input[name='selectedSite[]']", {"page": "all"});
	rowcollection.each(function(index,elem){
		if($(elem).is(':checked')){
			checkArray.push($(elem).attr('data-id'));
			checkNames.push($(elem).attr('data-name'));
		}
	});
	if(checkArray.length === 0){
		smoke.signal("Please select site to delete", function(e){
		}, {
			duration: 1500
		});
		return false;
	}
	smoke.confirm("Are you sure you want to delete?", function(e){
	if (e){
	//All checked
	if($("input[name='siteAllList']").is(":checked")){
		$.ajax({
			cache:false,
			async:false,
			dataType: "json",
			url: base_url+"/google/delete_site/all",
			type: "post",
			beforeSend: function(){
				$("#dvLoading").fadeIn(300);
			},
			success: function(data){
				if(data.empty == true){
					smoke.signal("All sites deleted, reloading the list..", function(e){
						setTimeout(function(){window.location=base_url+"/hazards-list#tabs-2"; location.reload();},500);
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
			url: base_url+"/google/delete_site/",
			type: "post",
			data: {checkboxarray: checkArray },
			beforeSend: function(){
				$("#dvLoading").fadeIn(300);
			},
			success: function(data){
				if(data.deleted == true){
					smoke.signal("Site(s) deleted, reloading the list..", function(e){
						setTimeout(function(){ window.location=base_url+"/hazards-list#tabs-2"; location.reload();},500);
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
	/******* SOCKET BROADCAST ******/
	// Sample JS array
	var myJSON = {
		action: 'D',
		id: '',
		type: 's',
		data: {
			hazard_id: checkArray,
			hazard_name: checkNames
		}
	};
	
	// Send as JSON
	conn.send(JSON.stringify(myJSON));
	
	/*******************************/
}}, {
	ok: "Yes",
	cancel: "Cancel",
	classname: "custom-class",
	reverseButtons: true
});
});

$(':checkbox[name="depotAllList"]').click (function () {
	var chk = $(this).prop('checked');
   	$('input', oDepot.fnGetNodes()).prop('checked',chk);
});

$("input[name='selectedDepot[]']").click(function(){
	$("input[name='depotAllList']").removeAttr("checked");
});

$("#delete-depotlist").click(function(){
	var delete_stat = false;
	var checkNames = [];
	var checkArray = [];
	var rowcollection =  oDepot.$("input[name='selectedDepot[]']", {"page": "all"});
	rowcollection.each(function(index,elem){
		if($(elem).is(':checked')){
			checkArray.push($(elem).attr('data-id'));
			checkNames.push($(elem).attr('data-name'));
		}
	});
	if(checkArray.length === 0){
		smoke.signal("Please select depot to delete", function(e){
		}, {
			duration: 1500
		});
		return false;
	}
	smoke.confirm("Are you sure you want to delete?", function(e){
	if (e){
	//All checked
	if($("input[name='depotAllList']").is(":checked")){
		$.ajax({
			cache:false,
			async:false,
			dataType: "json",
			url: base_url+"/google/delete_depot/all",
			type: "post",
			beforeSend: function(){
				$("#dvLoading").fadeIn(300);
			},
			success: function(data){
				if(data.empty == true){
					smoke.signal("All depot deleted, reloading the list..", function(e){
						setTimeout(function(){window.location=base_url+"/hazards-list#tabs-3"; location.reload();},500);
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
			url: base_url+"/google/delete_depot/",
			type: "post",
			data: {checkboxarray: checkArray },
			beforeSend: function(){
				$("#dvLoading").fadeIn(300);
			},
			success: function(data){
				if(data.deleted == true){
					smoke.signal("Depot(s) deleted, reloading the list..", function(e){
						setTimeout(function(){ window.location=base_url+"/hazards-list#tabs-3"; location.reload();},500);
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
	/******* SOCKET BROADCAST ******/
	// Sample JS array
	var myJSON = {
		action: 'D',
		id: '',
		type: 'd',
		data: {
			hazard_id: checkArray,
			hazard_name: checkNames
		}
	};
	
	// Send as JSON
	conn.send(JSON.stringify(myJSON));
	
	/*******************************/
}}, {
	ok: "Yes",
	cancel: "Cancel",
	classname: "custom-class",
	reverseButtons: true
});

});


function toTitleCase(str)
{
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}