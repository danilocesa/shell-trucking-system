// **************** Setting variables ***********************
	//hold data for ajax
	var data = {};
	
	//poly line variable
	var path = new google.maps.MVCArray(), poly;	
	
	//holds markers
	var points = [];
	
    //geocode services	
	var geocoder = new google.maps.Geocoder();
	
	//infowindow
	var infowindow = new google.maps.InfoWindow();
	
	//direction services
	var service = new google.maps.DirectionsService();
	
	// Directions renderer
	var directionsDisplay = new google.maps.DirectionsRenderer();
	
	// Start and end markers for route
	var start_marker;
	var end_marker;
	
	// Web worker output
	
	// Hazards' ids in route
	var arrHazardsHitId = [];
	
	// Hazards' distances in route
	var arrHazardsHitDistance = [];
	
	// Initialize web worker
	var worker = new Worker('<?php echo base_url();?>assets/js/intermediate_points.js');
	
	// Route vertices
	var allPath = [];
	
	// Route with waypoints vertices
	var allWpPath = [];
	
	// Indeces of waypoints
	var allWpPathIndeces = [];
	
	// Marker arrays
	
	// Array of array of polygon vertices 
 	var allPolyVertices = [];
	
	// Hazard title array
	var hazardTitles = [];
	
	// Hold all google polygons instances
	var allPoly = [];
	
	// Store all google marker instances
	var allMarkers = [];
	
	// Store modified marker ids
	var markersArrayLookup = [];
	
	// Marker cluster manager
	var markerCluster = new MarkerClusterer(map);
	
	// Last openened infowindow
	var lastInfoWindow = null;
// **************** Initialize function ***********************
	fetchMarkers();
	google.maps.Polygon.prototype.my_getBounds=function(){
		var bounds = new google.maps.LatLngBounds()
		this.getPath().forEach(function(element,index){bounds.extend(element)})
		return bounds
	}
	
	// Callback function for web worker
	worker.addEventListener('message', function(e) {
		// Clear the list of hazards
		$("ol#marker-list").html("");
	
		// Stop entertaining the user
		$("#release_route").html("");
		$("#worker-progress").hide();
		
		// IDs of hazards hit
		arrHazardsHitId = [];
		arrHazardsHitDistance = [];
		
		//DEBUG
		/*for (var ej=0; ej<=100; ej++){
			var newmarker = new MarkerWithLabel({
				position: new google.maps.LatLng(e.data.mydata[ej].lat, e.data.mydata[ej].lng),
				map: map
			});
		}*/
		
		// Display hazards
		if (e.data.allHazardsHit.length > 0) {
			// Hide all markers
			hideMarkers();
			markerCluster.removeMarkers(allMarkers);
		
			// Append hazards to list
			for (var i=0; i < e.data.allHazardsHit.length; i++)
			{
				var new_item = '<li class="marker" id="' + e.data.allHazardsHit[i].id + '">' + e.data.allHazardsHit[i].title + ' (KM ' + e.data.allHazardsHit[i].distance + ')</li>';
				$("ol#marker-list").append(new_item);
				arrHazardsHitId.push(e.data.allHazardsHit[i].id);
				arrHazardsHitDistance.push(e.data.allHazardsHit[i].distance);
				
				// Display only markers near on route
				markerCluster.addMarker(allMarkers[markersArrayLookup.indexOf(e.data.allHazardsHit[i].id)]);
			}
			
			markerCluster.redraw();
		} else {
			$("ol#marker-list").html("No markers");
		}
	}, false);
	
	function GetDirectionsWaypoints(polyPath, currIdx){
		var startLoc, endLoc;
		var retries = 0;

		// First waypoint, origin is beginning of route
		if (currIdx == 0){
			startLoc = directionsDisplay.getDirections().routes[0].overview_path[0];
			endLoc = polyPath[currIdx];
		}
		// Last waypoint, origin is last waypoint, destination is end of route
		else if (currIdx == polyPath.length){
			startLoc = polyPath[currIdx - 1];
			endLoc = directionsDisplay.getDirections().routes[0].overview_path[directionsDisplay.getDirections().routes[0].overview_path.length - 1];
		}
		// Mid waypoint, origin is previous waypoint, destination is current
		else {
			startLoc = polyPath[currIdx - 1];
			endLoc = polyPath[currIdx];
		}
		
		// Get directions
		service.route({ origin: startLoc, destination: endLoc, travelMode: google.maps.DirectionsTravelMode.DRIVING}, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				// Pass driving route path to web worker
				for (var y = 0; y < result.routes[0].overview_path.length; y++){
					allWpPath.push(result.routes[0].overview_path[y]);
				}
				
				// Store index of last array element
				if (currIdx < polyPath.length){
					allWpPathIndeces.push(allWpPath.length - 1);
				}
				
				currIdx++;
				
				if (currIdx <= polyPath.length){
					setTimeout(function() { GetDirectionsWaypoints(polyPath, currIdx); }, 600);
				} else {
					DividePolyPath(allWpPath, true);
				}
			} else if (status == "OVER_QUERY_LIMIT") {
				if (retries == MAX_RETRIES) {
					smoke.signal("Error while getting directions! Please check your Internet connection.", function(e){ }, {
						duration: 1000
					});
					return;
				}
				setTimeout(function() { GetDirectionsWaypoints(polyPath, currIdx); }, 600);
				retries++;
			}
		});
	}
	
	function DividePolyPath(polypath, hasWaypoint){	
		// Convert google.maps.LatLng to MyLatLng object 
 		var myPolyPath = [];
		
		// Route is created
		path.push(1);
		
		// Current index of waypoint being searched
		var currIdx = 0;
		
		// If more than or equal to 100 KM, divide to sub routes
		if (parseInt(directionsDisplay.getDirections().routes[0].legs[0].distance.text.replace(" km", "")) >= 100)
		{
			var distanceAcc = 0;
			var desiredDstc = 100; // in km
			var nexDst;
			
			for (var i=0; i < polypath.length; i++) {				
				if (i == 0) {
					nexDst = 0;
					//console.log(i + " " + Math.floor(nexDst / 1000) + " FIRST");
					distanceAcc = nexDst;
					myPolyPath.push(new MyLatLng(polypath[i].lat(), polypath[i].lng()));
					/*var newmarker = new MarkerWithLabel({
						position: polypath[i],
						map: map,
						labelContent: i
					});*/
				} else if (i > 0 && i < polypath.length - 1){
					nexDst = distanceAcc + google.maps.geometry.spherical.computeDistanceBetween(polypath[i], polypath[i-1])
					// If current distance is equal to desired distance, store the current point
					if (Math.floor(nexDst / 1000) == desiredDstc){
						desiredDstc += 100;
						//console.log(i + " " + Math.floor(nexDst / 1000) + " HIT next distance " + desiredDstc);
						myPolyPath.push(new MyLatLng(polypath[i].lat(), polypath[i].lng()));
						/*var newmarker = new MarkerWithLabel({
							position: polypath[i],
							map: map,
							labelContent: i
						});*/
					}
					// If current distance is less than desired distance and next distance is more than current, store the previous point
					else if (Math.floor(distanceAcc / 1000) < desiredDstc && Math.floor(nexDst / 1000) > desiredDstc){
						desiredDstc += 100;				
						//console.log(i + " " + Math.floor(distanceAcc / 1000) + " HIT next distance " + desiredDstc);
						myPolyPath.push(new MyLatLng(polypath[i-1].lat(), polypath[i-1].lng()));
						/*var newmarker = new MarkerWithLabel({
							position: polypath[i-1],
							map: map,
							labelContent: i
						});*/
					} else {
						// Check if current vertex is a custom waypoint
						if (hasWaypoint && directionsDisplay.directions.routes[0].legs[0].via_waypoints[currIdx]) {
							if (i == allWpPathIndeces[currIdx]){
								myPolyPath.push(new MyLatLng(polypath[i].lat(), polypath[i].lng()));
								/*var newmarker = new MarkerWithLabel({
									position: polypath[i],
									map: map
								});*/
								currIdx++;
							}
						}
					}
					
					distanceAcc = nexDst;
				} else if (i == polypath.length - 1) {
					//console.log(i + " " + Math.floor(nexDst / 1000) + " LAST");
					myPolyPath.push(new MyLatLng(polypath[i].lat(), polypath[i].lng()));
					var newmarker = new MarkerWithLabel({
						position: polypath[i],
						map: map,
						labelContent: i
					});
				}
			}
			//console.log(myPolyPath);
			DisplayNearbyMarkers(myPolyPath, true); // get driving path per point
		}
		else
		{
			for (var i=0; i < polypath.length; i++) {
				myPolyPath.push(new MyLatLng(polypath[i].lat(), polypath[i].lng()));
			}
			DisplayNearbyMarkers(myPolyPath, false); // do not get driving path
		}
	}
// **************** Maps Other options ***********************
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('address'));
	
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
					DirectionChangedEvent();
					break;
				case 'U':
					deleteMarker(parsedJSON.data.hazard_id);
					addMarker(parsedJSON.data);
					actionDesc = 'Updated ' + ((parsedJSON.type == 's') ? 'Site ' : (parsedJSON.type == 'd') ? 'Depot ' : (parsedJSON.type == 'h') ? 'Hazard ' : '') + parsedJSON.name;
					DirectionChangedEvent();
					break;
				case 'D':
					var names = '';
					for (var i=0; i<parsedJSON.data.hazard_id.length; i++){
						deleteMarker(parsedJSON.data.hazard_id[i] + parsedJSON.type);
						names += parsedJSON.data.hazard_name[i] + ' ';
					}
					actionDesc = 'Deleted ' + ((parsedJSON.type == 's') ? 'Site ' : (parsedJSON.type == 'd') ? 'Depot ' : (parsedJSON.type == 'h') ? 'Hazard ' : '') + names;
					DirectionChangedEvent();
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
	google.maps.event.addListener(map,"zoom_changed",function(e){
		/*if(map.getZoom() <= 16){
			hideMarkers();
		} else{
			showMarkers();
		}*/
	});	

// **************** Functions ***********************
	// MyLatLng Class 
 	function MyLatLng(lat, lng){ 
 		this.lat = lat; 
 		this.lng = lng; 
 	} 
	//**** Search location
	function codeAddress() {
		var autocomplete = new google.maps.places.Autocomplete(document.getElementById('address'));
  		autocomplete.bindTo('bounds', map);
  		var marker = new google.maps.Marker({
   			map: map,
    		anchorPoint: new google.maps.Point(0, -29)
  		});
  		
  		google.maps.event.addListener(autocomplete, 'place_changed', function() {
  			marker.setVisible(false);
  			var place = autocomplete.getPlace();
		    if (!place.geometry) {
		      return;
		    }
  			var place = autocomplete.getPlace();
  			if (place.geometry.viewport) {
		      map.fitBounds(place.geometry.viewport);
		    } else {
		      map.setCenter(place.geometry.location);
		      map.setZoom(18); 
		    }
		    marker.setIcon(({
		      url: place.icon,
		      size: new google.maps.Size(71, 71),
		      origin: new google.maps.Point(0, 0),
		      anchor: new google.maps.Point(17, 34),
		      scaledSize: new google.maps.Size(35, 35)
    		}));
		    marker.setPosition(place.geometry.location);
		    marker.setVisible(true);
		    var address = '';
		    if (place.address_components) {
		      address = [
		        (place.address_components[0] && place.address_components[0].short_name || ''),
		        (place.address_components[1] && place.address_components[1].short_name || ''),
		        (place.address_components[2] && place.address_components[2].short_name || '')
		      ].join(' ');
		    }
  		});
	}
	
	//*******Add route
	function addRoute() {
		// Prompt for route overwrite
		if (path.length > 0){
			smoke.confirm("Overwrite this route?", function(e){
			if (e){
				showSaveRouteDialog();
			}
			else {
				return;
			}
			},{ ok: "Yes", cancel: "Cancel", reverseButtons: true });
		} else {
			showSaveRouteDialog();
		}
	}
	
	function deleteRoute(){
		// Prompt for route deletion
		if (path.length > 0){
			smoke.confirm("Delete this route?", function(e){
			if (e){
				// Refresh
				window.location = window.location;
			}
			else {
				return;
			}
			},{ ok: "Yes", cancel: "Cancel", reverseButtons: true });
		} else {
			smoke.signal('No route yet', function(e){}, {
				duration: 9999
			});
		}
	}
	
	function showSaveRouteDialog(){
		$("#dvLoading").fadeIn(300);
		if($("#from_depot option").length == 0){
			$("#opts_sub").attr("disabled");
			smoke.signal('Please add Hazard for Depot and Sites before proceeding to add Routes.', function(e){}, {
				duration: 9999
			});
			$("#dvLoading").fadeOut(300);
			return;
		}
		$('#addRouteModal').modal('show');

		$("#opts_sub").unbind("click").click(function(){
			$("#dvLoading").fadeIn(300);
			$(".menu-push").trigger("click");
			start_marker = new google.maps.LatLng($("#from_depot option:selected").attr("data-latitude"),$("#from_depot option:selected").attr("data-longtitude"));
			end_marker = new google.maps.LatLng($("#to_site option:selected").attr("data-latitude"),$("#to_site option:selected").attr("data-longtitude"));

			directionsDisplay.setOptions({draggable:true});
			directionsDisplay.setMap(map);
			directionsDisplay.setPanel(document.getElementById('paneldirect'));
			geocoder.geocode( { 'latLng': start_marker}, function(results, status) {
				GetLocationInfo(start_marker, results[0].formatted_address.split(",",2).toString(),1);
			});
			geocoder.geocode( { 'latLng': end_marker}, function(results, status) {
				GetLocationInfo(end_marker, results[0].formatted_address.split(",",2).toString(),1);
			});	
			service.route({ origin: start_marker, destination: end_marker, travelMode: google.maps.DirectionsTravelMode.DRIVING, provideRouteAlternatives: false }, function(result, status) { 
    			if (status == google.maps.DirectionsStatus.OK) {
    				directionsDisplay.setDirections(result);		
    			}		        
			});
			
			$('#addRouteModal').modal('hide');
			$("#dvLoading").fadeOut(300);
		});	
		$("#dvLoading").fadeOut(300);
	}

	//*** Get location information
	function GetLocationInfo(latlng, locationName,route) {
		if (latlng != null) {
			var point = { LatLng: latlng, LocationName: locationName };
			points.push(point);
        }
	}
	
	function saveRoute(){
		if(path.length === 0){
			smoke.signal("Create route first", function(e){}, {
				duration: 9999
			});
		}
		else if ($("#release_route").html() != ""){
			smoke.signal("Cannot save route now. Processing nearby hazards is on-going.", function(e){}, {
				duration: 9999
			});
		}
		else {
			// Show save route dialog
			$("#dvLoading").fadeIn(300);
			if($("#from_depot option").length == 0){
				$("#opts_sub").attr("disabled");
				smoke.signal('Please add Hazard for Depot and Sites before proceeding to add Routes.', function(e){}, {
					duration: 9999
				});
				$("#dvLoading").fadeOut(300);
				return;
			}
			$("#route_title").val("").css('border', 'solid thin #ccc');
    		$("#route_info").val("").css('border', 'solid thin #ccc');
    		$("#ship_to").val("").css('border', 'solid thin #ccc');
			$('#saveRouteModal').modal('show');
			$("#dvLoading").fadeOut(300);
		}
	}

    //**** Save route
    function save_directions() {
    	//$("#dvLoading").fadeIn(300);
    	if(!$("#route_title").val() || !$("#ship_to").val() || $.trim($("#route_title").val()) == ''){
    		$("#route_title").css({"border":"1px solid red"});
    		$("#ship_to").css({"border":"1px solid red"});
    		smoke.signal("Fill up the route details", function(e){}, {
				duration: 9999
			});
    	}
		else if(!$("#route_title").val() ){
			$("#route_title").css({"border":"1px solid red"});
			smoke.signal("Route title", function(e){}, {
				duration: 9999
			});
		}
		else if(!$("#ship_to").val()){
			$("#ship_to").css({"border":"1px solid red"});
			smoke.signal("Ship to number", function(e){}, {
				duration: 9999
			});
		}
		else if(path.length === 0){
			smoke.signal("Create a route", function(e){}, {
				duration: 9999
			});
		} else {		
			$("#save-route-progress").show();
			$("#save-route-btns").hide();
			
			var polypath = google.maps.geometry.encoding.decodePath(directionsDisplay.getDirections().routes[0].overview_polyline);
			
			var w=[],mp, dire = directionsDisplay.directions.routes[0].legs[0];
			data.start = {'lat': dire.start_location.lat(), 'lng':dire.start_location.lng()}
		    data.end = {'lat': dire.end_location.lat(), 'lng':dire.end_location.lng()}
		    var wp = dire.via_waypoints
		    for(var i=0;i<wp.length;i++){
		    	 w[i] = [wp[i].lat(),wp[i].lng()];
			}
		    data.midpoints = w;
			data.info = {'title':$("#route_title").val(), 'information':$("#route_info").val(), 'ship_to':$("#ship_to").val() }
			data.location = {'start_loc': points[0].LocationName, 'last_loc': points[points.length-1].LocationName}
			var str = JSON.stringify(data);
			var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
			jax.open('POST',base_url+'/google/save_points');
			jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			jax.send('command=save&mapdata=' + str)
			jax.onreadystatechange = function(){ 
				if(jax.readyState==4) {
					var na_json = JSON.parse(jax.responseText);

					if(na_json.resp == 'success'){
						// Redirect to route list
						window.location = base_url+"/routes-list";
					}
					else {
						// If failed, show form buttons
						smoke.signal("Error while processing request. Please check your Internet connection and try again.", function(e){ }, {
							duration: 1000
						});
						$("#save-route-progress").hide();
						$("#save-route-btns").show();
						console.debug(jax.responseText);
					}
				}
			}
		}
		$("#dvLoading").fadeOut(300);
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
			var polyArr2 = []; // Without google.maps.LatLng
			for (var i=0; i < lats.length; i++)
			{
				polyArr.push(new google.maps.LatLng(lats[i], lngs[i]));
				polyArr2.push(new MyLatLng(lats[i], lngs[i])); 
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
	
				var temp_hazard = "";
	
				//if(json.status == 0){
				//	temp_hazard = "<div class='button' style='margin-top:10px;'>"+
				//	'<button type="button" class="btn btn-default btn-xs" id="close_hazard" data-id="'+json.hazard_id+'"style="padding:10px;width:100px;font-size:14px;font-weight:800;">Deactivate</button>'+'</div>'+
				//	'<div>';
				//}
				//else {
				//	temp_hazard = "";
				//}
				
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
			// Add marker title
			hazardTitles.push(newmarker_title);
			// Add polygon vertices
			allPolyVertices.push(polyArr2);
		} catch (err) {
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
			allPoly.splice(index, 1);
			allMarkers.splice(index, 1);
			hazardTitles.splice(index, 1);
			markersArrayLookup.splice(index, 1);
			allPolyVertices.splice(index, 1);
			
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
		jax.open('POST',base_url+'/google/fetch_hazard/');
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
	
	// Detection of nearby hazards using web workers
	// INPUT: decoded path of directions renderer
	//**** Display nearby markers
	function DisplayNearbyMarkers(polyPath, getRoute){	
		// Entertain the user
		$("#release_route").html("Working...");
		$("#worker-progress").show();
		// Clear list
		$("ol#marker-list").html("");
		
		allPath = [];
		
		if (getRoute){
			GetDirections(polyPath, 0);
		}
		else {
			worker.postMessage({'polypath' : polyPath, 'allPolyVertices' : allPolyVertices, 'hazardIds' : markersArrayLookup, 'hazardTitles' : hazardTitles});
		}
	}
	
	function GetDirections(polyPath, currIdx){
		var retries = 0;
		// Get directions
		service.route({ origin: new google.maps.LatLng(polyPath[currIdx].lat, polyPath[currIdx].lng), destination: new google.maps.LatLng(polyPath[currIdx + 1].lat,polyPath[currIdx + 1].lng), travelMode: google.maps.DirectionsTravelMode.DRIVING}, function(result, status) { 
			if (status == google.maps.DirectionsStatus.OK) {
				// Pass driving route path to web worker
				for (var y = 0; y < result.routes[0].overview_path.length; y++){
					allPath.push(new MyLatLng(result.routes[0].overview_path[y].lat(), result.routes[0].overview_path[y].lng()));
				}
				
				currIdx++;
				
				if (currIdx < polyPath.length - 1){
					setTimeout(function() { GetDirections(polyPath, currIdx); }, 600);
				} else {
					worker.postMessage({'polypath' : allPath, 'allPolyVertices' : allPolyVertices, 'hazardIds' : markersArrayLookup, 'hazardTitles' : hazardTitles});
				}
			} else if (status == "OVER_QUERY_LIMIT") {
				if (retries == MAX_RETRIES) {
					smoke.signal("Error while getting directions! Please check your Internet connection.", function(e){ }, {
						duration: 1000
					});
					return;
				}
				setTimeout(function() { GetDirections(polyPath, currIdx); }, 600);
				retries++;
			}
		});
	}

	function startTour(){
		$.tourTip.start();
		$("#show_hazard").tourTip({
			title: "Show Hazard",
			description: 'In order to view Hazard(s), simply click "Show Hazard" button. You can continue by clicking Next button or close the tour using the "Close" button.',
			next: true
		});
		$("#hide_hazard").tourTip({
			title: "Hide Hazard",
			description: 'By clicking "Hide Hazard" button, you can hide all Hazard(s).',
			next: true
		});
		
		$("#add_route").tourTip({
			title: "Add Route",
			description: 'In order to create new Route, simply click "Add Route". Route Option page will display, fill up the necessary fields before clicking "OK" button.',
			next: true
		});

		$(".add-route-form").tourTip({
			title: "Route details",
			description: 'Route Details serves to be the information regarding to the Route that will be created. Please fill up the required fields before saving the data.',
			position: "left",
			next: true
		});

		$("#nearby_haz").tourTip({
			title: "Nearby Hazard",
			description: 'This shows the Hazard(s) closed to the created Route.',
			position: "left",
			next: true
		});

		$("#delete_route").tourTip({
			title: "Delete Route",
			description: "If you wish to delete the unsaved Route or recreate the Route, simply click this button to delete what you have created.",
			close: true
		});
	}
	
	function DirectionChangedEvent() {
		if (directionsDisplay.directions != null){
			// Check if there's custom waypoint
			if (directionsDisplay.directions.routes[0].legs[0].via_waypoints.length > 0){
				// Clear arrays
				allWpPath = [];
				allWpPathIndeces = [];
				// Get driving directions on each waypoint
				GetDirectionsWaypoints(directionsDisplay.directions.routes[0].legs[0].via_waypoints, 0);
			}
			else {
				// Get vertices of direction renderer 
				DividePolyPath(google.maps.geometry.encoding.decodePath(directionsDisplay.getDirections().routes[0].overview_polyline), false);
			}
		}
	}
	
//DOM Element
$(document).ready(function(){
	// Focus on marker when user clicks hazard in hazard list
	$(document).on("click", ".marker", function(){		
		map.setCenter(allMarkers[markersArrayLookup.indexOf($(this).attr('id'))].getPosition());
		map.setZoom(18);
	});
	
	google.maps.event.addListener(directionsDisplay,"directions_changed", function() {
		DirectionChangedEvent();
 	});
	
	// Display the list of hazards
	$(document).on("click", "button.print", function(){
		var output = "";
		$("ol#marker-list li").each( function () {
			output += $(this).text() + "\n";
		});
	});

	// Remove field border
	$(".form-control").keyup(function(){
		$(this).each(function (item) {
	        $(this).css('border', 'none');
    	});
	});

	// Sliding panel animation
	var lastId = null;
	$('.slider-arrow').click(function(){
        if($(this).hasClass('show-panel')){
			if (lastId != null){
				$("#" + lastId + ", #p" + lastId).animate({
				right: "-=350", height: "50px"
				}, 300, function() {
					// Animation complete.
				});
				
				$("#" + lastId).removeClass('hide-panel').addClass('show-panel');
			}
			$("#panel-holder").css("width",390);
			$("#" + $(this).attr("id") + ", #p" + $(this).attr("id")).animate({
			right: "+=350", height: $(this).parent().css("height")
			}, 500, function() {			
				// Animation complete.
			});			
			$(this).removeClass('show-panel').addClass('hide-panel');
			lastId = $(this).attr("id");
        }
        else {
			$("#" + $(this).attr("id") + ", #p" + $(this).attr("id")).animate({
			right: "-=350", height: "50px"
			}, 500, function() {
				// Animation complete.
				$("#" + lastId).css("display", "block");
				$("#panel-holder").css("width",40);
			});
			$(this).removeClass('hide-panel').addClass('show-panel');
			lastId = null;
        }
    });

    $("#worker-progress").hide();
    $(".route-select").chosen(); 
});