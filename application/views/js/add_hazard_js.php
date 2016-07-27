// **************** Global variables ***********************
	// type of marker
	var type = '<?php echo $type; ?>';
	
	//hold data for ajax
	var data = {};
	
    //geocode services		
	var geocoder = new google.maps.Geocoder();
	
	//infowindow 
	var infowindow = new google.maps.InfoWindow();
	
	//marker
	var marker;
	
	//click counter 
	var clickCounter = 0;
	
	// New hazard latitude and longitude
	var newHazardLatLng = null;
	
	// Declare variable to hold location
	var hazard_location = null;
	
	// set value for default show hazard
	var showhazard = 1;
	
	// Click mode to distinguish hazard polygonal boundary plotting and placement
	// 1 -> boundary plotting
	// 0 -> placement
	var clickMode = 0;
	
	// Array to hold hazard polygonal boundary coordinates
	var hazardPolyArr = [];
	var hazardPoly = null;
	
	// Marker arrays
	
	// Hold all google polygons instances
	var allPoly = [];
	
	// Store all google marker instances
	var allMarkers = [];
	
	// Store modified marker ids
	var markersArrayLookup = [];
	
	var markerCluster = new MarkerClusterer(map);
	
	// Valid image fileSize
	var validImgTypes = ['.jpg', '.gif', '.png', '.jpeg'];
	
	// Image uploading status
	var siteUploadFin = false;
	var imageUploadFin = false;

	// Indicates if image file is selected
	var siteUploadSel = false;
	var imageUploadSel = false;	
	
	// Last openened infowindow
	var lastInfoWindow = null;
// **************** Initialize function ***********************
	// Site layout upload button init
	site_upload();
	
	// Marker image upload button init
	upload_image();
	
	// Load specific type of markers
	fetchMarkers();
	
	google.maps.Polygon.prototype.my_getBounds=function(){
		var bounds = new google.maps.LatLngBounds()
		this.getPath().forEach(function(element,index){bounds.extend(element)})
		return bounds
	}

	Date.prototype.toMysqlFormat = function () {
		function pad(n) { return n < 10 ? '0' + n : n }
		return this.getFullYear() + "-" + pad(1 + this.getMonth()) + "-" + pad(this.getDate()) + " " + pad(this.getHours()) + ":" + pad(this.getMinutes()) + ":" + pad(this.getSeconds());
	};

// **************** Maps Other options ***********************	 
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('address'));
	map.controls[google.maps.ControlPosition.CENTER].push(document.getElementById('center_map'));
	map.controls[google.maps.ControlPosition.LEFT_CENTER].push(document.getElementById('addHazSiteDepo'));
	
// **************** Web Socket ***********************	 
	conn.onmessage = function(e) {
		try {
			// Parse message
			var parsedJSON = JSON.parse(e.data);
			var actionDesc;
			console.log(parsedJSON);
			
			switch (parsedJSON.action) {
				case 'C':
					addMarker(parsedJSON.data);
					actionDesc = 'Created ' + ((parsedJSON.type == 's') ? 'Site ' : (parsedJSON.type == 'd') ? 'Depot ' : (parsedJSON.type == 'h') ? 'Hazard ' : '') + parsedJSON.name;
					break;
				case 'U':
					deleteMarker(parsedJSON.data.hazard_id);
					addMarker(parsedJSON.data);
					actionDesc = 'Updated ' + ((parsedJSON.type == 's') ? 'Site ' : (parsedJSON.type == 'd') ? 'Depot ' : (parsedJSON.type == 'h') ? 'Hazard ' : '') + parsedJSON.name;
					break;
				case 'D':
					var names = '';
					for (var i=0; i<parsedJSON.data.hazard_id.length; i++){
						deleteMarker(parsedJSON.data.hazard_id[i] + parsedJSON.type);
						names += parsedJSON.data.hazard_name[i] + ' ';
					}
					actionDesc = 'Deleted ' + ((parsedJSON.type == 's') ? 'Site ' : (parsedJSON.type == 'd') ? 'Depot ' : (parsedJSON.type == 'h') ? 'Hazard ' : '') + names;
					break;
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
			
			smoke.signal(actionDesc, function(e){ }, {
				duration: 1000
			});
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
		// hide save hazard bounds
		$("#hazardBounds").fadeOut(500);
		geocoder.geocode( { 'latLng': newHazardLatLng}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			$('#myModal').modal('show');
			$("#location_modal").val(results[0].formatted_address.split(",",2).toString());
			marker.setMap(map);
			$('#myModal').on('hide.bs.modal', function (e) {
				clearHazardform();
				//loc = [];
				clickCounter = 0;
				clickMode = 0;
				marker.setMap(null);
				hazardPoly.setMap(null);
				hazardPolyArr = [];
				$("#ok-hazard-bounds").attr("disabled","disabled");
				$("#thumb_img").hide();
				$("#sitethumb").hide();
			});	
		} else {	
			smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){}, {
				duration: 9999
			});
		}
		});
		$("#dvLoading").fadeOut(300);
	});
	
	
	// Cancel plotting
	$("#cancel-hazard-bounds").click(function(e) {
		$("#dvLoading").fadeIn(300);
		// Remove polygon and marker
		if (hazardPoly != null)
		{
			hazardPoly.setMap(null);
		}
		if (marker != null)
		{
			marker.setMap(null);
		}
		hazardPolyArr = [];
		clickMode = 0;
		clickCounter = 0;
		// hide save hazard bounds
		$("#hazardBounds").fadeOut(500);
		$("#dvLoading").fadeOut(300);
		$("#ok-hazard-bounds").attr("disabled","disabled");
	});
	
	function enableDrawBounds() {
		map.setOptions({ draggableCursor: 'crosshair' });
	
		google.maps.event.addListener(map,"click", function(location) {
		clickCounter += 1;
		if (clickMode == 0) {
			// display save hazard bounds
			$("#hazardBounds").fadeIn(500);
			$("#dvLoading").fadeOut(300);
		}
		else {
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

		}
		if(clickCounter == 1 ){
			newHazardLatLng = new google.maps.LatLng(location.latLng.lat(), location.latLng.lng());
			
			// Check if marker is above water
			
			geocoder.geocode( { 'latLng': location.latLng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					marker = new google.maps.Marker({position: location.latLng, animation: google.maps.Animation.BOUNCE});
					$("#site_region").val(results[0].address_components[results[0].address_components.length-2].short_name);
					$("#location_modal").val(results[0].formatted_address.split(",",2).toString());
					marker.setMap(map);
					clickMode = 1;
					$('#myModal').on('hide.bs.modal', function (e) {
						clearHazardform();
						//loc = [];
						clickCounter = 0;
						marker.setMap(null);
						$("#thumb_img").hide();
						$("#sitethumb").hide();
					});	
				} else {	
					smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){}, {
						duration: 9999
					});
				}
			});
			return false;
		}
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
	
	//**** Save marker information
	function saveMarker(){
		// Check if there is selected icon for hazard
		if (type == 'h'){
			if ($("div.icon-selected").attr('id') == null){
				smoke.signal("Please select an icon for hazard.", function(e){ }, {
					duration: 9999
				});
				$("#dvLoading").fadeOut(300);
				return;
			}
		}
		
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
		var paths = hazardPoly.getPath();
		for (var i=0; i < paths.getLength(); i++) {
			lats.push(paths.getAt(i).lat());
			lngs.push(paths.getAt(i).lng());
		}
		
		data =  {
			'location':				$("#location_modal").val(),
			'lat':					lats,
			'lng':					lngs,
			'latitude':				hazardPoly.my_getBounds().getCenter().lat(),
			'longitude':			hazardPoly.my_getBounds().getCenter().lng(),
			'title':				encodeURIComponent($('#title_modal').val().replace(/<(?:.|\n)*?>/gm, '')),
			'info':					encodeURIComponent($('#info_modal').val().replace(/<(?:.|\n)*?>/gm, '')),
			'filename':				$('#file_namename').val(),
			'status':				$("#haz_status").val(),
			'start_date':			$("#start_date").val(),
			'end_date':				$("#end_date").val(),
			'category':				$("#marker-category").val(),
			'site_photo':			$("#site_photoname").val(),
			'hazard_icon':			(type == 'h') ? $("div.icon-selected").attr('id') : (type == 'd') ? 'depot.png' : (type == 's') ? 'site.png' : '',
			'site_region': 			$("#site_region").val(),
			'hazard_control':		encodeURIComponent($("#control_modal").val().replace(/<(?:.|\n)*?>/gm, '')),
			'speed_limit':			$("#speed_limit") != null ? $("#speed_limit").val() : ''
		}
		
		var str = JSON.stringify(data);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/add_hazard/' + type);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=save&infodata='+str)
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				console.log(jax.responseText);
				var data = JSON.parse(jax.responseText);
				if(data.resp == 'success'){
					/******* SOCKET BROADCAST ******/
					// Sample JS array
					var myJSON = {
						action: 'C',
						id: data.id,
						name: $('#title_modal').val().replace(/<(?:.|\n)*?>/gm, ''),
						type: type,
						data: {
							hazard_id: 			data.id + type,
							latitude: 			lats.join('|'),
							longitude:			lngs.join('|'),
							title:				$('#title_modal').val().replace(/<(?:.|\n)*?>/gm, ''),
							information:		$('#info_modal').val().replace(/<(?:.|\n)*?>/gm, ''),
							location:			$("#location_modal").val(),
							hazard_image:		$('#file_namename').val(),
							center_latitude:	hazardPoly.my_getBounds().getCenter().lat(),
							center_longitude:	hazardPoly.my_getBounds().getCenter().lng(),
							hazard_icon:		(type == 'h') ? $("div.icon-selected").attr('id') : (type == 'd') ? 'depot.png' : (type == 's') ? 'site.png' : '',
							status:				$("#haz_status").val()
						}
					};
					
					// Send as JSON
					conn.send(JSON.stringify(myJSON));
					
					/*******************************/
				
					//fetchMarkers(1);
					clearHazardform();
					//loc = [];
					$('#myModal').modal('hide');
					$("#save-marker-progress").hide();
					$("#save-marker-btns").show();
					map.setOptions({ draggableCursor: 'crosshair' });
					
					// Reset upload variables
					imageUploadFin = false;
					siteUploadFin = false;
					
					imageUploadSel = false;
					siteUploadSel = false;
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
	
	// Add marker to array and map
	function addMarker(json){
		try {
			var hazard_poly;
			var newmarker;
			
			var marker_id = json.hazard_id || json.depot_id || json.site_id; // null coalescing
			var newmarker_title = json.title || json.site_name || json.depot_name;
			var newmarker_information = json.information || json.site_information || json.depot_information;
			var newmarker_location = json.location || json.site_location || json.depot_location;
			var image_path = ((marker_id.indexOf('s') > -1) ? 'sites/' : (marker_id.indexOf('d') > -1) ? 'depots/' : '') + (json.hazard_image || json.site_img || json.depot_img || 'no-image.jpg');
			
			// Extract latlngs and draw polygon
			var lats = json.latitude.split("|");
			var lngs = json.longitude.split("|");
			var polyArr = [];
			for (var i=0; i < lats.length; i++)
			{
				polyArr.push(new google.maps.LatLng(lats[i], lngs[i]));
			}
				
			// Draw polygon
			hazard_poly = new google.maps.Polygon({
				paths: polyArr,
				strokeColor: '#FF0000',
				strokeOpacity: 0,
				strokeWeight: 0,
				fillColor: '#FF0000',
				fillOpacity: 0,
				map: map
			});
	
			newmarker = new MarkerWithLabel({
				position: new google.maps.LatLng(json.center_latitude, json.center_longitude),
				labelContent: newmarker_title,
				title: newmarker_title,
				labelContent: newmarker_title,
				labelAnchor: new google.maps.Point(-30, 33),
				labelClass: "hazard-label", // the CSS class for the label
				icon: base_url+'/assets/img/icons/' + json.hazard_icon,
				information: newmarker_information,
				location: newmarker_location,
				map: map
			});
			
			google.maps.event.addListener(newmarker,"click", (function() {
				var contentString = '<div class="content" style="text-align:left;">'+
				'<div class="title">'+
				'<h5 style="color:#0174DF;">Title:</h5><h6>'+ newmarker.title + '</h6></div>'+
				'<div class="information">'+
				'<h5 style="color:#0174DF;">Information:</h5><h6>'+ newmarker.information + '</h6></div>'+
				'<div class="location">'+
				'<h5 style="color:#0174DF;">Location:</h5><h6>'+ newmarker.location + '</h6></div>'+
				'<div class="latlng">'+
				'<h5 style="color:#0174DF;">Latitude:</h5><h6>'+ json.center_latitude + '</h6>'+
				'<h5 style="color:#0174DF;">Longtitude:</h5><h6>'+ json.center_longitude + '</h6></div>'+
				'<div class="image">'+
				'<h5 style="color:#0174DF;">Image:</h5>';
	
				var temp_hazard;
				if(typeof json.hazard_id != "undefined"){
					if(json.hazard_id.substr(json.hazard_id.length -1) == 'h'){
						if(json.status == 0){
							temp_hazard = "<div class='button' style='margin-top:10px;'>"+
							'<button type="button" class="btn btn-warning btn-sm close_hazard" data-id="'+json.hazard_id+'"style="padding:10px;width:100px;font-size:14px;font-weight:800;">Deactivate</button>'+'</div>'+
							'<div>';
						}
						else {
							temp_hazard = "";
						}
					}
					else {
						temp_hazard = "";
					}
				} 	
				else {
					temp_hazard = "";
				}
				if(json.status == 0){
					
				}
				else {
					temp_hazard = "";
				}
				
				var info_deta = contentString.concat("<img src=' " + base_url + '/uploads/' + image_path + " ' style='width:170px;height:160px;'/>" + "</div>", temp_hazard);
				
				var infowindow =  new google.maps.InfoWindow({
					content: info_deta,
					map: map
				});
				
				infowindow.open(map, newmarker);
				
				// Close last opened infowindow
				if (lastInfoWindow){
					lastInfoWindow.close();
					lastInfoWindow = infowindow;
				} else {
					lastInfoWindow = infowindow;
				}
			}));
			
			// Add to markers array
			allMarkers.push(newmarker);
			// Add to polygons array
			allPoly.push(hazard_poly);
			// Store marker id
			markersArrayLookup.push(marker_id);
			// Add marker to cluster
			markerCluster.addMarker(newmarker);
		}
		catch (err) {
			console.debug("Marker not added: " + (json.title || json.site_name || json.depot_name) + " " + err.message);
		}
	}
	
	// Delete marker from array and map
	function deleteMarker(hazard_id){
		// Get index of marker
		var index = markersArrayLookup.indexOf(hazard_id);
		if (index > -1){
			// Remove marker and polygon from map
			allPoly[index].setMap(null);
			allMarkers[index].setMap(null);
			markerCluster.removeMarker(allMarkers[index]);
			
			// Remove from markers array
			markersArrayLookup.splice(index, 1);
			allPoly.splice(index, 1);
			allMarkers.splice(index, 1);
			
			// Redraw clusterer
			markerCluster.redraw();
		}
		else {
			console.log('Index of marker not found.');
		}
	}
	
	// Fetch markers from db
	function fetchMarkers() {
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/' + type);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					var hazard_json = JSON.parse(jax.responseText);
					for(var e = 0; e < hazard_json.length; e++){
						addMarker(hazard_json[e]);
					}
				}
				else{
					smoke.signal('Unable to process this request.\nPlease try again later.', function(e){}, {
						duration: 9999
					});
				}
			}
		}
	}
	
	//**** Set all to map
	function setAllMap(map) {
		for (var i = 0; i < allMarkers.length; i++) {
			allMarkers[i].setMap(map); 
		}
	}
	
	//**** Hide markers
	function hideMarkers() {
		setAllMap(null);
	}
	
	//*** Show markers
	function showMarkers() {
		setAllMap(map);
	}

	//**** Clear Form
	function clearHazardform(){
		$("#start_date").val("");
		$("#end_date").val("");
		$("#location_modal").val("");
		$("#haz_status").val(1);
		$("#_file").val("");
		$("#file_namename").val("");
		$("#title_modal").val("");
		$("#info_modal").val("");
		$("#control_modal").val("");
		$("#marker-category").val("");
		$("#icon-container").hide();
		$(".start_end-date").hide();
		$("div.icon-selected").removeClass("icon-selected");
		$("#speed_limit option").removeAttr('selected');
		$("#speed_limit option:first").attr('selected','selected');
		/*<?php if($this->session->userdata("hazard_cate") == "hazards"):?>
			$("#icon-container").css("display","none");
			//$(".haz-controls").css("display","none");
		<?php endif;?>*/

		//$("#site-upload").css("display","none");
	}

	function startTour(){
		$.tourTip.start();
	}

//****************** Others *******************************
$("#thumb_img").hide();
$("#sitethumb").hide();
$("#perm-status").attr("checked",true);
// Initializing datetime pickers
$('.datetimepicker').datetimepicker({
	minDate:		new Date().toMysqlFormat(), 
	defaultValue: 	new Date().toMysqlFormat(), 
	dateFormat: 	"yy-mm-dd",
    timeFormat:  	"HH:mm:ss"
});
$("#icon-container").hide();
//$("#site-upload").hide();

$(document).ready(function(){
	var sepCat;
	<?php if($this->uri->segment(1) == "add-hazards"){ ?>
		sepCat = "Hazard";
	<?php } elseif($this->uri->segment(1) == "add-sites") {?>
		sepCat = "Site";
	<?php } else {?>
		sepCat = "Depot";
	<?php }?>
	$("#show_hazard").tourTip({
			title: "Show "+sepCat+"(s)",
			description: 'This button shows the '+sepCat+'(s).',
			next: true
		});
		$("#hide_hazard").tourTip({
			title: "Hide "+sepCat+"(s)",
			description: 'This button hides the '+sepCat+'(s).',
			next: true
		});
		
		$("#add_hazardAction").tourTip({
			title: "Add "+sepCat+"(s)",
			description: 'In order to create new '+sepCat+', point the location of the '+sepCat+' by simply clicking on the map. Please specify boundaries for the '+sepCat+' by simply plotting a polygon shape around the marker then click "OK" button. The Information page will display, fill up the necessary fields before saving the data.',
			close: true,
		});
	$(document).on("change", "#haz_status", function(){
		if ($(this).children(":selected").attr("id") == "perm-status"){
			$(".start_end-date").hide();
			// Disable datetimepickers and clear values
			$(".datetimepicker").attr("disabled","disabled").val("").removeClass("picker-active");
		} else if ($(this).children(":selected").attr("id") == "temp-status"){
			$(".start_end-date").show();
			// Enable datetimepickers
			$(".datetimepicker").removeAttr("disabled").addClass("picker-active");
			$("#start_date").val(new Date().toMysqlFormat());
		}
	});


	$(document).on("change", "select#marker-category", function(){
		// Remove '- select -' option
		$("select#marker-category option[value='']").remove();
		$("#icon-container").show();
		
		if ($(this).val() != '')
		{
			// Hide all icon list
			$("div.icon-list").hide();
			
			// Display icons under selected category
			$("div#" + $(this).val()).show();
		}	
	});


	$(document).on("click", "div.icon", function(){
		$("div.icon-selected").removeClass("icon-selected");
		
		// Assign selected class to selected icon
		$(this).addClass("icon-selected");
	});

	$(document).on("click",".close_hazard",function(){
		var id = $(this).attr('data-id');
		smoke.confirm("Are you sure?", function(e){
			if (e){
				$.ajax({
					url: "<?php echo base_url();?>google/close_hazard/"+id,
					dataType: "json",
					cache: false,
					async: false,
					success: function(data){
						location.reload();
					},
					error: function(xhr){
						console.log(xhr);
					}
				});
			}else{

			}
		}, {
			ok: "Yes",
			cancel: "No",
			classname: "custom-class",
			reverseButtons: true
		});
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
			marker_category:{required: true},
			hazard_title:{required: true},
			start_date:{required:true}
		},
		highlight: function(element) {
       		$(element).closest('.form-group').addClass('has-error');
    	},
    	unhighlight: function(element) {
        	$(element).closest('.form-group').removeClass('has-error');
    	},
    	submitHandler: function(form) { saveMarker();   return false; }
	});

	$("#end_date").change(function(){
		if($(this).val() != ''){
			$("#end_date").rules("add", {
				enddate:true
			});
		} else {
			 $("#end_date").rules("remove");
		}
	});

	$("#start_date").change(function(){
		$("#start_date").rules("add", {
			startdate:true
		});
	});
	
	if(map.getZoom() <= 10){
		hideMarkers();
	}

	$(".start_end-date").hide();
});	