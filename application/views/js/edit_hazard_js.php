// **************** Global variables ***********************
	// bounds latlng for current marker
	var latlng = <?php echo $latlng; ?>;
	
	// hazard details
	var hazard_details = [<?php echo $hazard_details_json; ?>];
	
	// type of marker being edited
	var type = '<?php echo $type; ?>';
	
	// id of marker being edited
	var id = <?php echo $id; ?>;
	
	// accepted old marker boundary
	var acceptBounds;
	
	//hold data for ajax
	var data = {};
	
	//marker
	var marker;
	
	// Array to hold hazard polygonal boundary coordinates
	var hazardPolyArr = [];
	var hazardPoly = null;
	
	// Uneditable polygon indicating marker bounds
	var fixedPoly;
	
	// Valid image fileSize
	var validImgTypes = ['.jpg', '.gif', '.png', '.jpeg'];
	
	// Image uploading status
	var siteUploadFin = false;
	var imageUploadFin = false;
	
	// Indicates if image file is selected
	var siteUploadSel = false;
	var imageUploadSel = false;
// **************** Initialize function ***********************
	// Site layout upload button init
	site_upload();
	
	// Marker image upload button init
	upload_image();
	
	google.maps.Polygon.prototype.my_getBounds=function(){
		var bounds = new google.maps.LatLngBounds()
		this.getPath().forEach(function(element,index){bounds.extend(element)});
		return bounds;
	}

	Date.prototype.toMysqlFormat = function () {
    function pad(n) { return n < 10 ? '0' + n : n }
    return this.getFullYear() + "-" + pad(1 + this.getMonth()) + "-" + pad(this.getDate()) + " " + pad(this.getHours()) + ":" + pad(this.getMinutes()) + ":" + pad(this.getSeconds());
	};
	var TimeNow = new Date().toMysqlFormat();

// **************** Maps Other options ***********************	 
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	map.controls[google.maps.ControlPosition.CENTER].push(document.getElementById('center_map'));
	map.controls[google.maps.ControlPosition.LEFT_CENTER].push(document.getElementById('addHazSiteDepo'));
	
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
			case 'CHKMR':// Check for marker ID from Marker Delete module
				if (id == parsedJSON.id && type == parsedJSON.type) {
					// Broadcast message to socket clients
					var myJSON = {
						action: 'RESMR',
						id: parsedJSON.id,
						type: parsedJSON.type
					};
									
					// Broadcast ID
					conn.send(JSON.stringify(myJSON));
				}				
				break;
			default: return;
		}
	} catch (err) {
		console.debug(err);
	}
};
	
// **************** Maps events ***********************	
	// Done plotting hazard bounds
	$("#ok-hazard-bounds").click(function() {
		$("#dvLoading").fadeIn(300);
		// Check if hazard marker is within polygon bounds
		if (!google.maps.geometry.poly.containsLocation(new google.maps.LatLng(marker.position.lat(), marker.position.lng()), hazardPoly))
		{
			smoke.signal('Cannot continue. Hazard marker is outside polygon bounds.', function(e){ }, {
				duration: 9999
			});
			$("#dvLoading").fadeOut(300);
			return;
		}
		
		acceptBounds = false;
		
		// hide save hazard bounds
		$("#hazardBounds").fadeOut(500);
		$('#myModal').modal('show');

		$('#myModal').on('hide.bs.modal', function (e) {
			$("#hazardBounds").fadeIn(500);
		});
		
		$("#dvLoading").fadeOut(300);
	});
	
	$("#accept-hazard-bounds").click(function() {
		smoke.confirm("Retain old bounds?", function(e){
		if (e){
			$("#dvLoading").fadeIn(300);
		
			acceptBounds = true;
			
			// hide save hazard bounds
			$("#hazardBounds").fadeOut(500);
			$('#myModal').modal('show');
	
			$('#myModal').on('hide.bs.modal', function (e) {
				$("#hazardBounds").fadeIn(500);
			});	
			
			$("#dvLoading").fadeOut(300);
		}
		else {
			return;
		}
		},{ ok: "Yes", cancel: "Cancel", reverseButtons: true });
	});
	
	
	// Cancel plotting
	$("#cancel-hazard-bounds").click(function(e) {
		$("#dvLoading").fadeIn(300);
		// Remove polygon and marker
		if (hazardPoly != null)
		{
			hazardPoly.setMap(null);
		}
		hazardPolyArr = [];
		//clickMode = 0;
		//clickCounter = 0;
		// hide save hazard bounds
		//$("#hazardBounds").fadeOut(500);
		$("#dvLoading").fadeOut(300);
		$("#ok-hazard-bounds").attr("disabled","disabled");
	});
	
	function enableDrawBounds(){
		map.setOptions({ draggableCursor: 'crosshair' });
		fixedPoly.setOptions({ draggableCursor: 'crosshair' });
		
		// display save hazard bounds
		$("#hazardBounds").fadeIn(500);
		$("#dvLoading").fadeOut(300);
		
		google.maps.event.addListener(map,"click", function(location) {
			hazardPolyArr.push(location.latLng);
			// Remove polygon
			if (hazardPoly != null)
			{
				hazardPoly.setMap(null);
			}
			hazardPoly = new google.maps.Polygon({
				paths: hazardPolyArr,
				strokeColor: '#FF0000',
				strokeOpacity: 0.8,
				strokeWeight: 3,
				editable: true,
				draggable: true,
				fillColor: '#FF0000',
				fillOpacity: 0.35
			});
			hazardPoly.setMap(map);
			
			google.maps.event.addListener(hazardPoly.getPath(), 'set_at', function() {
				// complete functions
				console.log('set at');
				hazardPolyArr = hazardPoly.getPath().getArray();
				// If points is more than two, enable save button
				if (hazardPoly.getPath().getLength() > 2)
				{
					$("#ok-hazard-bounds").removeAttr("disabled");
				}
			});
			
			google.maps.event.addListener(hazardPoly.getPath(), 'insert_at', function() {
				console.log('insert at');
				hazardPolyArr = hazardPoly.getPath().getArray();
				// If points is more than two, enable save button
				if (hazardPoly.getPath().getLength() > 2)
				{
					$("#ok-hazard-bounds").removeAttr("disabled");
				}
			});
		
			// If points is more than two, enable save button
			if (hazardPoly.getPath().getLength() > 2)
			{
				$("#ok-hazard-bounds").removeAttr("disabled");
			}
			return;
		});
	}
	
// **************** Functions ***********************
	//**** Search Box
	function codeAddress() {
		var autocomplete = new google.maps.places.Autocomplete(document.getElementById('address'));
  		autocomplete.bindTo('bounds', map);
  		google.maps.event.addListener(autocomplete, 'place_changed', function() {
  			 var place = autocomplete.getPlace();
  			 if (place.geometry.viewport) {
		      map.fitBounds(place.geometry.viewport);
		    } else {
		      map.setCenter(place.geometry.location);
		      map.setZoom(15); 
		    }
  		});
	}
	
	//**** Save information	
	function saveMarker(){
		// Check form validity
		if (!$("#add_hazard_form").valid())
			return;
		
		// Hide form buttons
		$("#save-marker-progress").show();
		$("#save-marker-btns").hide();
		// Check if there are selected files for marker image or site layout
		if (imageUploadSel || siteUploadSel) {
			if (imageUploadSel) {
				$('#_file').uploadify('upload', '*');
			}
			if (siteUploadSel) {
				$('#_sitephoto').uploadify('upload', '*');
			}
		} else {
			saveMarkerToServer();
		}
		
		return false;
	}
	
	function saveMarkerToServer(){
		var lats = [];
		var lngs = [];
		
		if (acceptBounds){
			for(var i=0; i<latlng.length; i++){
				lats.push(latlng[i].latitude);
				lngs.push(latlng[i].longitude);
			}
		} else {
			var paths = hazardPoly.getPath();
			for (var i=0; i < paths.getLength(); i++) {
				lats.push(paths.getAt(i).lat());
				lngs.push(paths.getAt(i).lng());
			}
		}
		
		data = {
			'lat':				lats,
			'lng':				lngs,
			'accept':			acceptBounds,
			'title':			$('#title_modal').val() ? encodeURIComponent($('#title_modal').val().replace(/<(?:.|\n)*?>/gm, '')) : '',
			'info':				$('#info_modal').val() ? encodeURIComponent($('#info_modal').val().replace(/<(?:.|\n)*?>/gm, '')) : '',
			'filename':			$('#file_namename').val(),
			'site_filename':	$('#site_photoname').val(),
			'status':			$("#haz_status").val(),
			'start_date':		$("#start_date").val(),
			'end_date':			$("#end_date").val(),
			'hazard_control':	$("#control_modal").val() ? encodeURIComponent($("#control_modal").val().replace(/<(?:.|\n)*?>/gm, '')) : '',
			'speed_limit':		$("#speed_limit") != null ? $("#speed_limit").val() : ''
		}
		var str = JSON.stringify(data);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/edit_hazard/' + type + '/' + id);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=save&infodata=' + str)
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				console.log(jax.responseText);
				if(jax.responseText == 'success'){
					/******* SOCKET BROADCAST ******/
					// Sample JS array
					var myJSON = {
						action: 'U',
						id: id,
						type: type,
						name: $('#title_modal').val().replace(/<(?:.|\n)*?>/gm, ''),
						data: {
							hazard_id: 			id + type,
							latitude: 			lats.join('|'),
							longitude:			lngs.join('|'),
							title:				$('#title_modal').val().replace(/<(?:.|\n)*?>/gm, ''),
							information:		$('#info_modal').val().replace(/<(?:.|\n)*?>/gm, ''),
							location:			hazard_details[0].location || hazard_details[0].site_location || hazard_details[0].depot_location,
							hazard_image:		$('#file_namename').val(),
							center_latitude:	hazard_details[0].center_latitude,
							center_longitude:	hazard_details[0].center_longitude,
							hazard_icon:		hazard_details[0].hazard_icon,
							status:				$("#haz_status").val()
						}
					};
					
					// Send as JSON
					conn.send(JSON.stringify(myJSON));
					
					/*******************************/
				
					$('#myModal').modal('hide');
					smoke.signal("Success. Redirecting to the list..", function(e){
						setTimeout(function(){window.location=base_url+"/hazards-list#tabs-" + (type == 'h' ? '1' : type == 's' ? '2' : type == 'd' ? '3' : '')},500);
					}, {
						duration: 500
					});
				}
				else {
					// If failed, show form buttons
					smoke.signal("Error while processing request. Please check your Internet connection and try again.", function(e){ }, {
						duration: 1000
					});
					$("#save-marker-progress").hide();
					$("#save-marker-btns").show();
					console.debug(jax.responseText);
				}
			}
		}
	}
	
	//***** Upload image
	function upload_image(){
		var buf = makeid();
		var timestamp = new Date().getTime();
		// File id of current uploaded file
		var fileId = null;
		
		$(function() {
			var uploader_path;
			if (type == 'h'){
				uploader_path = base_url+'/google/upload_image/uploads';
			}
			else if (type == 's'){
				uploader_path = base_url+'/google/upload_image/sites';
			}
			else if (type == 'd'){
				uploader_path = base_url+'/google/upload_image/depots';
			}
		
			$('#_file').uploadify({
				'formData'     : {
					'timestamp' : timestamp,
					'token'     : buf
				},
				'auto': false,
				'multi': false,
				'buttonText': 'Browse image',
				'swf'      : base_url+'/assets/js/uploadify.swf',
				'uploader' : uploader_path,
				'onUploadSuccess' : function(file, data, response) {
					try {
						var na_json = JSON.parse(data);
					} catch (err) {
						console.debug(data);
						smoke.signal("Unable to upload image. Please select another file.", function(e){ }, {
							duration: 1500
						});
						$("#save-marker-progress").hide();
						$("#save-marker-btns").show();
						$("#dvLoading").fadeOut(300);
						return;
					}
				 	if(na_json.resp == 'success'){
						$("#file_namename").val(na_json.filename);
						imageUploadFin = true;
						// If site, check if site layout upload is finished
						if (type == 's' && siteUploadSel){
							if (siteUploadFin) {
								saveMarkerToServer();
							}
						} else {
							saveMarkerToServer();
						}
				 	} else {
				 		smoke.signal("Invalid image file!", function(e){ }, {
							duration: 1500
						});
				 	}
			    },
				'onCancel': function(file){
					if (fileId) {
						// If current image is cancelled, indicate that no image is selected
						if (fileId == file.id){
							imageUploadSel = false;
							console.log('No image!');
						}
					}
				},
				'onSelect': function(file) {
					// Validate filename
					if (validImgTypes.indexOf(file.type.toLowerCase()) == -1 || file.size > 10000000) {
						smoke.signal("Invalid image file!", function(e){ }, {
							duration: 1500
						});
						// Remove this image
						$('#_file').uploadify('cancel', file.id);
						return;
					} else {
						// Remove previous image
						if (fileId)
							$('#_file').uploadify('cancel', fileId);
						// Store current image id
						fileId = file.id;
						// Indicate that there is an image selected
						imageUploadSel = true;
						console.log('Theres image!');
					}
				},
			    'fileSizeLimit' :'10MB',
				'queueSizeLimit': '2'
			});
		});
	}

	//Create random id
	function makeid()
	{
	    var text = "";
	    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	    for( var i=0; i < 25; i++ )
	        text += possible.charAt(Math.floor(Math.random() * possible.length));

	    return text;
	}

	function site_upload(){
		var buf = makeid();
		var timestamp = new Date().getTime();
		// File id of current uploaded file
		var fileId = null;
		
		$(function() {
			$('#_sitephoto').uploadify({
				'formData'     : {
					'timestamp' : timestamp,
					'token'     : buf
				},
				'auto': false,
				'multi': false,
				'buttonText': 'Browse image',
				'swf'      : base_url+'/assets/js/uploadify.swf',
				'uploader' : base_url+'/google/upload_image/site_layout',
				'onUploadSuccess' : function(file, data, response) {
					try {
						var na_json = JSON.parse(data);
					} catch (err) {
						console.debug(data);
						smoke.signal("Unable to upload image for site. Please select another file.", function(e){ }, {
							duration: 1500
						});
						$("#save-marker-progress").hide();
						$("#save-marker-btns").show();
						$("#dvLoading").fadeOut(300);
						return;
					}
				 	if(na_json.resp == 'success'){
						$("#site_photoname").val(na_json.filename);
						siteUploadFin = true;
						// If site, check if site layout upload is finished
						if (type == 's' && imageUploadSel){
							if (imageUploadFin) {
								saveMarkerToServer();
							}
						} else {
							saveMarkerToServer();
						}
				 	} else {
				 		$('#myModal').modal('hide');
				 		smoke.signal("Invalid image file!", function(e){ }, {
							duration: 1500
						});
				 	}
			    },
				'onCancel': function(file){
					if (fileId) {
						// If current image is cancelled, indicate that no image is selected
						if (fileId == file.id){
							siteUploadSel = false;
							console.log('No site!');
						}
					}
				},
				'onSelect': function(file) {
					// Validate filename
					if (validImgTypes.indexOf(file.type.toLowerCase()) == -1 || file.size > 10000000) {
						smoke.signal("Invalid image file!", function(e){ }, {
							duration: 1500
						});
						// Remove this image
						$('#_sitephoto').uploadify('cancel', file.id);
						return;
					} else {
						// Remove previous image
						if (fileId)
							$('#_sitephoto').uploadify('cancel', fileId);
						// Store current image id
						fileId = file.id;
						// Indicate that there is an image selected
						siteUploadSel = true;
						console.log('Theres site!');
					}
				},
			    'fileSizeLimit' :'10MB',
				'queueSizeLimit': '2'
			});
		});
	}

	function startTour(){
		$.tourTip.start();
		$("#show_hazard").tourTip({
			title: "Show Hazard",
			description: 'This button shows the Hazard(s).',
			next: true
		});
		$("#hide_hazard").tourTip({
			title: "Hide Hazard",
			description: 'This button hides the Hazard(s).',
			next: true
		});
		
		$("#add_hazardAction").tourTip({
			title: "Add Hazard",
			description: 'In order to create new Hazard, point the location of the Hazard by simply clicking on the map. Please specify boundaries for the Hazard by simply plotting a polygon shape around the marker then click "OK" button. The Information page will display, fill up the necessary fields before saving the data.',
			close: true,
		});
	}

//****************** Others *******************************

// Initializing datetime pickers
$('.datetimepicker').datetimepicker({
	minDate:		new Date().toMysqlFormat(), 
	defaultValue: 	new Date().toMysqlFormat(), 
	dateFormat: 	"yy-mm-dd",
    timeFormat:  	"HH:mm:ss"
});

function checkMarker(){
	if (conn.readyState != 1) {
		console.log('Socket server not ready. Retrying to check...');
		setTimeout(checkMarker, 1000);
		return;
	}
	// Check if marker is marked for deletion
	// If so, cancel pending deletion
	
	// Broadcast message to socket clients
	var myJSON = {
		action: 'CHKMR2',
		id: id,
		type: type
	};
					
	// Broadcast ID
	conn.send(JSON.stringify(myJSON));
}

$(document).ready(function(){
	setTimeout(checkMarker, 1000);

	// Display marker
	marker = new google.maps.Marker({position: new google.maps.LatLng(hazard_details[0].center_latitude, hazard_details[0].center_longitude)});
	marker.setMap(map);
	
	// Click of marker toggles boundary visibility
	$(document).on("click", "#hideBounds",function(){
		if (fixedPoly.map)
			fixedPoly.setMap(null);
		else
			fixedPoly.setMap(map);
	}); 

	// Center the map to the marker
	map.setCenter(marker.getPosition());
	
	// Display uneditable polygon
	var fixedPolyArr = [];
	for(var i=0; i<latlng.length; i++){
		fixedPolyArr.push(new google.maps.LatLng(latlng[i].latitude, latlng[i].longitude));
	}
	fixedPoly = new google.maps.Polygon({
		paths: fixedPolyArr,
		strokeColor: '#777',
		strokeOpacity: 0.8,
		strokeWeight: 3,
		editable: false,
		draggable: false,
		clickable: true,
		fillColor: '#aaa',
		fillOpacity: 0.35
	});
	fixedPoly.setMap(map);
	
	// Zoom in to fixed polygon
	map.fitBounds(fixedPoly.my_getBounds());
	
	// Invoke add hazard function
	enableDrawBounds();
	
	$(document).on("change", "#haz_status", function(){
		if ($(this).children(":selected").attr("id") == "perm-status"){
			$(".start_end-date").hide();
			// Disable datetimepickers
			$(".datetimepicker").attr("disabled","disabled").removeClass("picker-active");
		} else if ($(this).children(":selected").attr("id") == "temp-status"){
			$(".start_end-date").show();
			// Enable datetimepickers
			$(".datetimepicker").removeAttr("disabled").addClass("picker-active");
			if($("#start_date").val() == ""){
				$("#start_date").val(new Date().toMysqlFormat());
			}
			if($("#start_date").val() == "0000-00-00 00:00:00"){
				$("#start_date").val(new Date().toMysqlFormat());
			}
			$("#end_date").val("");
		}
	});

	$(document).on("click","#close_hazard",function(){
		$("#dvLoading").fadeIn(300);
		$.ajax({
			url:base_url+"/google/close_hazard/"+$(this).attr("data-id"), 
			type: "post",
			cache: false,
			dataType: "json",
			success:function(result){
				location.reload();
			},
			error:function(xhr){
				console.log(xhr);
			}
		});	
		$("#dvLoading").fadeOut(300);
	});		

	$.validator.addMethod("enddate", function(value, element){
		var startdatevalue = $('#start_date').val();
		return Date.parse(startdatevalue) < Date.parse(value);
	}, 'End Date should be greater than Start Date.');

	$.validator.addMethod("startdate", function(value, element){
		return Date.parse(new Date().toMysqlFormat()) < Date.parse(value);
	}, 'Start Date should be greater than time now.');

	$("#add_hazard_form").validate({
		rules:{
			hazard_title:	{ required: true },
			start_date:		{ required: ($('#haz_status').val() == 0) }
		},
		highlight: function(element) {
       		$(element).closest('.form-group').addClass('has-error');
    	},
    	unhighlight: function(element) {
        	$(element).closest('.form-group').removeClass('has-error');
    	},
    	submitHandler: function(form) { $("#dvLoading").fadeIn(300); saveMarker();   return false; }
	});

	$("#start_date").change(function(){
		$("#start_date").rules("add", {
			startdate:true
		});
	});
	
	$(".start_end-date").hide();
	
	// If marker is temporary, display dates
	if (hazard_details[0].status == 0)
		$(".start_end-date").show();
});	