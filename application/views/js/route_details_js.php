// **************** Setting variables ***********************
	// ID of route
	var id = <?php echo $route_id; ?>;

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
	
	//hold data for ajax
	var data = {};
	
	// Infowindow
	var infowindow = new google.maps.InfoWindow();
	
	//geocode services	
	var geocoder = new google.maps.Geocoder();
	
	// Route waypoints
	var wp = [];
	
	// Expected screenshot count
	var screensExpectedCount = 0;
	
	// Last openened infowindow
	var lastInfoWindow = null;
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
				case 'CHKRT': // Check for route ID from Route Delete module
					if (id == parsedJSON.id) {
						// Broadcast message to socket clients
						var myJSON = {
							action: 'RESRT',
							id: parsedJSON.id
						};
										
						// Broadcast ID
						conn.send(JSON.stringify(myJSON));
					}
					return;
				default: return;
			}
			
			smoke.signal(actionDesc, function(e){ }, {
				duration: 3000
			});
		} catch (err) {
			console.debug(err);
		}
	};
 // ************** Initialize functions *****************	
	google.maps.event.addListener(map,"zoom_changed",function(e){
		/*if(map.getZoom() <= 16){
			hideMarkers();
		} else{
			showMarkers();
		}*/
	});	
	
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	
	fetchMarkers(0);
	
	$("#save_way").hide();
	
	// MyLatLng Class 
 	function MyLatLng(lat, lng){ 
 		this.lat = lat; 
 		this.lng = lng; 
 	}
	
// **************** Maps Other options ***********************
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
		$("#worker-progress-haz").hide();
		
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
			$("ol#marker-list").html("No nearby hazards");
		}
		
		// Enable printing of report
		$("#print_details").unbind("click");
		$("#print_details").click(function(){
			smoke.confirm("Are you sure?", function(e){
				if (e){
					// Entertain the user
					$("#divDownload").fadeIn(300);
					$("#divDownloadBack").fadeIn(300);
					$("#map-shots").html("");
					$("#release_scr").html("Processing screenshots...");
					document.onkeydown = function (e) {
						return false;
					}
					
					// Download maps
					DownloadOverallMap(id, google.maps.geometry.encoding.decodePath(directionsDisplay.getDirections().routes[0].overview_polyline));
				}
			},{ ok: "Yes", cancel: "Cancel", reverseButtons: true });
		});
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
	
	function DirectionChangedEvent(){
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

	function setroute(os)
	{
		var route_json = JSON.parse(os.route_json);
		start_marker = new google.maps.LatLng(route_json.start.lat,route_json.start.lng);
		end_marker = new google.maps.LatLng(route_json.end.lat,route_json.end.lng)
		var mid_wp = route_json.midpoints.filter(function(n){ return n != undefined });
		directionsDisplay.setMap(map);
		directionsDisplay.setPanel(document.getElementById('paneldirect'));
		for(var i=0;i<mid_wp.length;i++){
   			wp[i] = {'location': new google.maps.LatLng(mid_wp[i][0], mid_wp[i][1]), 'stopover': false}
        }
		service.route({ origin: start_marker, destination: end_marker , travelMode: google.maps.DirectionsTravelMode.DRIVING, waypoints: wp }, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				directionsDisplay.setDirections(result);
				google.maps.event.addListener(directionsDisplay, "directions_changed", DirectionChangedEvent());
			}

		});
		
		var bounds = new google.maps.LatLngBounds();
		bounds.extend( new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
		bounds.extend( new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		map.fitBounds(bounds);
		geocoder.geocode({'latLng': new google.maps.LatLng(route_json.start.lat,route_json.start.lng)}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					$("#start_pt_loc").val(results[0].formatted_address.split(",",2).toString());
				} else {
					alert('No results found');
				}
			} else {
				alert('Geocoder failed due to: ' + status);
			}
		});

		geocoder.geocode({'latLng': new google.maps.LatLng(route_json.end.lat,route_json.end.lng)}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					$("#end_pt_loc").val(results[0].formatted_address.split(",",2).toString());
				} else {
					alert('No results found');
				}
			} else {
				alert('Geocoder failed due to: ' + status);
			}
		});
	}
	
	function editPoly(){
		$("#back_button").attr("onclick","location.reload()");
		$("#dvLoading").fadeIn(300);
		directionsDisplay.setMap(null);
		directionsDisplay = new google.maps.DirectionsRenderer({draggable:true});
		directionsDisplay.setMap(map);
		$("#paneldirect").html("");
		directionsDisplay.setPanel(document.getElementById('paneldirect'));
		service.route({ origin: start_marker, destination: end_marker , travelMode: google.maps.DirectionsTravelMode.DRIVING, waypoints: wp }, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				directionsDisplay.setDirections(result);
				google.maps.event.addListener(directionsDisplay, "directions_changed", function(){
					DirectionChangedEvent();
				});
			}
		});

		$("#save_way").show();
		$("#ship_to").removeAttr("readonly");
		$("#route_title").removeAttr("readonly");
		$("#route_info").removeAttr("readonly");
		$("#dvLoading").fadeOut(300);
		if ($("#p1").css('right') == '0px'){
			$("#p1").click();
		}
	}

	//**** Save route
    function save_directions() {
    	//$("#dvLoading").fadeIn(300);
    	if(!$("#route_title").val() || !$("#ship_to").val()){
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
			
			//var polypath = google.maps.geometry.encoding.decodePath(directionsDisplay.getDirections().routes[0].overview_polyline);
			
			var w=[],mp, dire = directionsDisplay.directions.routes[0].legs[0];
			data.start = {'lat': dire.start_location.lat(), 'lng':dire.start_location.lng()}
		    data.end = {'lat': dire.end_location.lat(), 'lng':dire.end_location.lng()}
		    var wp = dire.via_waypoints
		    for(var i=0;i<wp.length;i++){
		    	 w[i] = [wp[i].lat(),wp[i].lng()];
			}
			data.midpoints = w;
			data.info = {'title':$("#route_title").val(), 'information':$("#route_info").val(), 'ship_to':$("#ship_to").val() }
			data.location = {'start_loc': $("#start_pt_loc").val(), 'last_loc': $("#end_pt_loc").val()}
			var str = JSON.stringify(data);
			var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
			jax.open('POST',base_url+'/google/edit_route/'+id);
			jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			jax.send('command=edit&mapdata='+str)
			jax.onreadystatechange = function(){ 
				if(jax.readyState==4) {
					try {
						var na_json = JSON.parse(jax.responseText);
					} catch (err) {
						console.log(err);
						console.log(jax.responseText);
						smoke.signal('Error saving route.', function(e){ }, {
							duration: 1000
						});
						return;
					}
					if(na_json.notExists == 1){
						window.location= '<?php echo base_url();?>google/notExist';
					} else{
						if(na_json.resp == 'success'){
							$("#map-shots").html("");
							// Entertain the user
							$("#release_scr").html("Working...");
							$("#worker-progress-scr").show();
			
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
		}
		$("#dvLoading").fadeOut(300);
	}
	
	function DownloadOverallMap(id, polypath){
		var retries = 0;
		$('.screens-progress').html('Processing overall map...');
		screensExpectedCount++;
		
		// Download overall map
		var data2 = {};
		data2.enc = directionsDisplay.getDirections().routes[0].overview_polyline;
		data2.startLat = polypath[0].lat();
		data2.startLng = polypath[0].lng();
		data2.endLat = polypath[polypath.length-1].lat();
		data2.endLng = polypath[polypath.length-1].lng();
		data2.routeId = id;
		data2.fileName = 0;
		data2.testDir = true;
		var str = JSON.stringify(data2);
		var jaxtone = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jaxtone.open('POST', base_url + '/google/save_img');
		jaxtone.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jaxtone.onreadystatechange = function(){ 
			if(jaxtone.readyState==4) {
				try {
					var result = JSON.parse(jaxtone.responseText);
				} catch(err) {
					console.log(err);
					console.log(jax.responseText);
					smoke.signal('Error downloading overall map.', function(e){ }, {
						duration: 1000
					});
					return;
				}
				if (result.resp == 'success'){
					$('.screens-progress').html('Downloaded overall map.');
					DownloadSegmentsMap(id);
				} else {
					if (retries == MAX_RETRIES) {
						smoke.signal("Error while downloading screenshots! Please check your Internet connection.", function(e){ }, {
							duration: 1000
						});
						return;
					}
					console.log('Retrying...');
					setTimeout(function() { DownloadOverallMap(id, polypath); }, 600);
					retries++;
				}
			}
		}
		jaxtone.send('type=1&data=' + str);
	}
	
	function DownloadSegmentsMap(id){
		$('.screens-progress').html('Downloading segments maps...');
	
		// Segment screenshots
		// Every 10 KM, create screenshot
		// If more than or equal to 10 KM, divide to segment and show hazards
		var distanceAcc = 0;
		var desiredDstc = 10; // in km
		var nexDst;
		var pointsBound = new google.maps.LatLngBounds();
		var pointsAcc = [];
		var prevIdx = 0;
		var ctr = 1;
		
		// GetScreenshot parameters array
		var getScreensParams = []
		
		if (parseInt(directionsDisplay.getDirections().routes[0].legs[0].distance.text.replace(/[^0-9.]/g, '')) >= 100)
		{
			for (var i=0; i < allPath.length; i++) {
				pointsBound.extend(new google.maps.LatLng(allPath[i].lat, allPath[i].lng));
				pointsAcc.push(new google.maps.LatLng(allPath[i].lat, allPath[i].lng));
				
				if (i > 0 && i < allPath.length - 1){
					nexDst = distanceAcc + google.maps.geometry.spherical.computeDistanceBetween(new google.maps.LatLng(allPath[i].lat,allPath[i].lng), new google.maps.LatLng(allPath[i-1].lat,allPath[i-1].lng));
					// If current distance is equal to desired distance, store the current point
					if (Math.floor(nexDst / 1000) == desiredDstc){
						//console.log("SAKTO " + i);
						desiredDstc += 10;
						screensExpectedCount++; 
						//GetScreenshot(pointsBound, pointsAcc, allPath[prevIdx], allPath[i], ctr, id, false, false, true);
						getScreensParams.push({
							bounds: 		pointsBound,
							encVisiblePts: 	pointsAcc,
							startPt: 		allPath[prevIdx],
							endPt: 			allPath[i],
							fn: 			ctr,
							routeId: 		id,
							timeOut:		true
						});
						ctr++;
						prevIdx = i;
						pointsBound = new google.maps.LatLngBounds();
						pointsAcc = [];
						pointsBound.extend(new google.maps.LatLng(allPath[prevIdx].lat, allPath[prevIdx].lng));
						pointsAcc.push(new google.maps.LatLng(allPath[prevIdx].lat, allPath[prevIdx].lng));
					}
					// If current distance is less than desired distance and next distance is more than current, store the previous point
					else if (Math.floor(distanceAcc / 1000) < desiredDstc && Math.floor(nexDst / 1000) > desiredDstc){
						//console.log("SOBRA " + i);
						desiredDstc += 10;
						screensExpectedCount++; 
						//GetScreenshot(pointsBound, pointsAcc.slice(0, pointsAcc.length - 1), allPath[prevIdx], allPath[i-1], ctr, id, false, false, true);
						getScreensParams.push({
							bounds: 		pointsBound,
							encVisiblePts: 	pointsAcc.slice(0, pointsAcc.length - 1),
							startPt: 		allPath[prevIdx],
							endPt: 			allPath[i-1],
							fn: 			ctr,
							routeId: 		id,
							timeOut:		true
						});
						ctr++;
						prevIdx = i-1;
						pointsBound = new google.maps.LatLngBounds();
						pointsAcc = [];
						pointsBound.extend(new google.maps.LatLng(allPath[prevIdx].lat, allPath[prevIdx].lng));
						pointsAcc.push(new google.maps.LatLng(allPath[prevIdx].lat, allPath[prevIdx].lng));
					}
					distanceAcc = nexDst;
				} else if (i == allPath.length - 1) {
					screensExpectedCount++; 
					//GetScreenshot(pointsBound, pointsAcc, allPath[prevIdx], allPath[i], ctr, id, redirect, reload, true);
					getScreensParams.push({
						bounds: 		pointsBound,
						encVisiblePts: 	pointsAcc,
						startPt: 		allPath[prevIdx],
						endPt: 			allPath[i],
						fn: 			ctr,
						routeId: 		id,
						timeOut:		true
					});
					ctr++;
					pointsBound = null;
					pointsAcc = null;
				}
			}
		} else {
			var myPath = google.maps.geometry.encoding.decodePath(directionsDisplay.getDirections().routes[0].overview_polyline);
			for (var i=0; i < myPath.length; i++) {
				pointsBound.extend(myPath[i]);
				pointsAcc.push(myPath[i]);
				
				if (i > 0 && i < myPath.length - 1){
					nexDst = distanceAcc + google.maps.geometry.spherical.computeDistanceBetween(myPath[i], myPath[i-1]);
					// If current distance is equal to desired distance, store the current point
					if (Math.floor(nexDst / 1000) == desiredDstc){
						desiredDstc += 10;
						screensExpectedCount++; 
						//GetScreenshot(pointsBound, pointsAcc, new MyLatLng(myPath[prevIdx].lat(), myPath[prevIdx].lng()), new MyLatLng(myPath[i].lat(), myPath[i].lng()), ctr, id, false, false, true);
						getScreensParams.push({
							bounds: 		pointsBound,
							encVisiblePts: 	pointsAcc,
							startPt: 		new MyLatLng(myPath[prevIdx].lat(), myPath[prevIdx].lng()),
							endPt: 			new MyLatLng(myPath[i].lat(), myPath[i].lng()),
							fn: 			ctr,
							routeId: 		id,
							timeOut:		true
						});
						ctr++;
						prevIdx = i;
						pointsBound = new google.maps.LatLngBounds();
						pointsAcc = [];
						pointsBound.extend(myPath[prevIdx]);
						pointsAcc.push(myPath[prevIdx]);
					}
					// If current distance is less than desired distance and next distance is more than current, store the previous point
					else if (Math.floor(distanceAcc / 1000) < desiredDstc && Math.floor(nexDst / 1000) > desiredDstc){
						desiredDstc += 10;
						screensExpectedCount++; 
						//GetScreenshot(pointsBound, pointsAcc.slice(0, pointsAcc.length - 1), new MyLatLng(myPath[prevIdx].lat(), myPath[prevIdx].lng()), new MyLatLng(myPath[i-1].lat(), myPath[i-1].lng()), ctr, id, false, false, true);
						getScreensParams.push({
							bounds: 		pointsBound,
							encVisiblePts: 	pointsAcc.slice(0, pointsAcc.length - 1),
							startPt: 		new MyLatLng(myPath[prevIdx].lat(), myPath[prevIdx].lng()),
							endPt: 			new MyLatLng(myPath[i-1].lat(), myPath[i-1].lng()),
							fn: 			ctr,
							routeId: 		id,
							timeOut:		true
						});
						ctr++;
						prevIdx = i-1;
						pointsBound = new google.maps.LatLngBounds();
						pointsAcc = [];
						pointsBound.extend(myPath[prevIdx]);
						pointsAcc.push(myPath[prevIdx]);
					}
					distanceAcc = nexDst;
				} else if (i == myPath.length - 1) {
					screensExpectedCount++; 
					//GetScreenshot(pointsBound, pointsAcc, new MyLatLng(myPath[prevIdx].lat(), myPath[prevIdx].lng()), new MyLatLng(myPath[i].lat(), myPath[i].lng()), ctr, id, redirect, reload, true);
					getScreensParams.push({
						bounds: 		pointsBound,
						encVisiblePts: 	pointsAcc,
						startPt: 		new MyLatLng(myPath[prevIdx].lat(), myPath[prevIdx].lng()),
						endPt: 			new MyLatLng(myPath[i].lat(), myPath[i].lng()),
						fn: 			ctr,
						routeId: 		id,
						timeOut:		true
					});
					ctr++;
					pointsBound = null;
					pointsAcc = null;
				}
			}
		}
		
		GetScreenshot(getScreensParams, 0);
	}
	
	function GetScreenshot(params, currIdx){
		var retries = 0;
		$('.screens-progress').html('Processing segment screenshot: ' +(currIdx + 1) + ' of ' + params.length);
		
		/*var rectangle = new google.maps.Rectangle({
			strokeColor: '#FF0000',
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: '#FF0000',
			fillOpacity: 0.35,
			map: map,
			bounds: bounds
		});*/
		
		var item = '';
		var hitIds = '';
		
		// Add hazard markers
		// Exclude first and last hazard hit (depot and site)
		for (var i = 1, ctr = 1; i < arrHazardsHitId.length; i++)
		{
			// Get index of hazard marker
			var idx = markersArrayLookup.indexOf(arrHazardsHitId[i]);
			if (idx != -1)
			{
				// Check if marker polygon bounds visible on current map view
				var hzPolyPts = allPoly[idx].getPaths().getArray()[0].getArray();
				for (var w = 0; w < hzPolyPts.length; w++){
					if (params[currIdx].bounds.contains(hzPolyPts[w]))
					{
						hitIds += markersArrayLookup[idx] + '_';
						item += '3jmarkers=label:' + ctr++ + '%7C' + allMarkers[idx].getPosition().lat() + ',' + allMarkers[idx].getPosition().lng();
						break;
					}	
				}
			}
		}
		
		var data2 = {};
		data2.enc = google.maps.geometry.encoding.encodePath(params[currIdx].encVisiblePts);
		data2.startLat = params[currIdx].startPt.lat;
		data2.startLng = params[currIdx].startPt.lng;
		data2.endLat = params[currIdx].endPt.lat;
		data2.endLng = params[currIdx].endPt.lng;
		data2.routeId = params[currIdx].routeId;
		data2.fileName = params[currIdx].fn;
		data2.hazards = hitIds;
		data2.testDir = false;
		data2.markers = item;
		var str = JSON.stringify(data2);
		var jaxtone = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jaxtone.open('POST', base_url + '/google/save_img', true);
		jaxtone.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jaxtone.onreadystatechange = function(){ 
			if(jaxtone.readyState==4) {
				try {
					var result = JSON.parse(jaxtone.responseText);
				} catch(err) {
					console.log(err);
					console.log(jax.responseText);
					smoke.signal('Error saving segment map.', function(e){ }, {
						duration: 1000
					});
					return;
				}
				if (result.resp == 'success'){
					console.log('OK... ' + result.map);
					
					currIdx++;
						
					if (currIdx < params.length){
						setTimeout(function() { GetScreenshot(params, currIdx); }, 6000);
					} else {
						GetScreensFromFolder(true);
					}
				} else {
					if (retries == MAX_RETRIES) {
						smoke.signal("Error while downloading screenshots! Please check your Internet connection.", function(e){ }, {
							duration: 1000
						});
						return;
					}
					console.log('Retrying... ');
					setTimeout(function() { GetScreenshot(params, currIdx); }, 6000);
					retries++;
				}
			}
		}
		jaxtone.send('type=2&data=' + str);
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
				//if(json.hazard_id.substr(json.hazard_id.length -1) == 'h'){
				//	if(json.status == 0){
				//		temp_hazard = "<div class='button' style='margin-top:10px;'>"+
				//		'<button type="button" class="btn btn-default btn-xs" id="close_hazard" data-id="'+json.hazard_id+'"style="padding:10px;width:100px;font-size:14px;font-weight:800;">Deactivate</button>'+'</div>'+
				//		'<div>';
				//	}
				//	else {
				//		temp_hazard = "";
				//	}
				//} else{
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

	function setAllMap(map) {
		for (var i = 0; i < allMarkers.length; i++) {
			allMarkers[i].setMap(map); 
			allPoly[i].setMap(map);  
		}
		$("#dvLoading").fadeOut(300);
	}

	function hideMarkers() {
		$("#dvLoading").fadeIn(300);
		$.each(markersArrayLookup, function(index, value) {
			var newmarker = new MarkerWithLabel({
				position: new google.maps.LatLng(value.latitude, value.longitude),
				map: map,
				labelContent: value.title,
				title: value.title,
				labelAnchor: new google.maps.Point(-30, 33),
				labelClass: "hazard-label", // the CSS class for the label
				icon: base_url+'/assets/img/icons/' + value.hazard_icon
			});
			newmarker.setMap(null);
		});
		setAllMap(null);
	}

	function showMarkers() {
		$("#dvLoading").fadeIn(300);
		setAllMap(map);
	}
	
	// Detection of nearby hazards using web workers
	// INPUT: decoded path of directions renderer
	//**** Display nearby markers
	function DisplayNearbyMarkers(polyPath, getRoute){	
		// Entertain the user
		$("#release_route").html("Checking for hazards...");
		$("#worker-progress-haz").show();
		// Clear list
		$("ol#marker-list").html("");
		$("#totaldistance").html("");
		
		allPath = [];
		
		//console.log("getRoute " + getRoute);
		
		// Pass data to worker, initialization mode
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
				
				//console.log(allPath.length);
				
				currIdx++;
				//console.log(currIdx);
				if (currIdx < polyPath.length - 1){
					setTimeout(function() { GetDirections(polyPath, currIdx); }, 600);
				} else {
					//console.log("TAPOS!!!");
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

	// Retrieves the list of images for current route
	// and displays to the screenshots panel
	function GetScreensFromFolder(submitForm) {
		$("#map-shots").html("");
		$('.screens-progress').html('&nbsp;');
		// Entertain the user
		$("#release_scr").html("Processing screenshots...");
		$("#worker-progress-scr").show();
		
		// Get screenshots	
		var jaxtone = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jaxtone.open('POST',base_url+'/google/fetch_img/');
		jaxtone.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jaxtone.send('route_id=' + id)
		jaxtone.onreadystatechange = function(){
			if(jaxtone.readyState==4) {
				//console.log(jaxtone.responseText);
				var result = JSON.parse(jaxtone.responseText)
				if (result.failed){
					$("#map-shots").html('Screenshots unavailable.');
				} else {
					if (screensExpectedCount != 0){
						console.log('filename count: ' + result.files.length);
						console.log('expected count: ' + screensExpectedCount);
						if (screensExpectedCount != result.files.length){
							console.log('fetching filenames again...');
							setTimeout(function() { GetScreensFromFolder(submitForm); }, 1000);
							return;
						}
					}
					
					var d = new Date();
					
					if (result.files.length > 0) {
						for (var i = 0; i < result.files.length; i++){
							$("#map-shots").append('<div class="map-screenshot"><img class="map-screenshot-view" src="' + base_url + '/screens/' + result.id + '/' + result.files[i] + '?ver=' + d.getTime() + '" width="200" alt="' + result.files[i].split('_').slice(1).join(',').replace(',.jpg','') + '"/></div>');
						}
						
						if (submitForm)
							SubmitReportDetails();
					} else {
						$("#map-shots").html('Screenshots unavailable.');
					}
				}
				
				// Stop entertaining the user
				$("#release_scr").html("");
				$("#worker-progress-scr").hide();
			}
		}
	}
	
	function SubmitReportDetails(){
		$('.screens-progress').html('Screenshots processed.');
	
		// Save URL of map screenshots and visible markers ids
		var output = "";
		$("div#map-shots img").each( function () {
			output += '<input type="hidden" name="screens[]" value="' + $(this).attr('src') + '"/>';
			output += '<input type="hidden" name="screens_id[]" value="' + $(this).attr('alt') + '"/>';
		});
		$("#print_hazards").append(output);
		
		// Include list of hazards to the form
		output = "";
		$(arrHazardsHitId).each( function (key, value) {
			output += '<input type="hidden" name="hzds[]" value="' + value + '"/>';
			output += '<input type="hidden" name="hzds_dst[]" value="' + arrHazardsHitDistance[key] + '"/>';
		});
		$("#print_hazards").append(output);
		
		// Pass encrypted route
		output = "";
		$(directionsDisplay.getDirections().routes[0].legs[0].steps).each( function (key, value) {
			output += '<input type="hidden" name="directions[]" value="' + value.instructions + '"/>';
		});
		$("#print_hazards").append(output);
		
		// Get origin and destination location
		$("#map_path").append('<input type="hidden" id="orgn" name="orgn" value="' + $("#start_pt_loc").val() + '"/>');
		$("#map_path").append('<input type="hidden" id="dest" name="dest" value="' + $("#end_pt_loc").val() + '"/>');
		
		$("#print_hazards").submit();
	}
	
	function checkRoute(){
		if (conn.readyState != 1) {
			console.log('Socket server not ready. Retrying to check...');
			setTimeout(checkRoute, 1000);
			return;
		}
		// Check if route is marked for deletion
		// If so, cancel pending deletion
		
		// Broadcast message to socket clients
		var myJSON = {
			action: 'CHKRT2',
			id: id
		};
						
		// Broadcast ID
		conn.send(JSON.stringify(myJSON));
	}

$(document).ready(function() {
	setTimeout(checkRoute, 1000);
	
	// Focus on marker when user clicks hazard in hazard list
	$(document).on("click", ".marker", function(){		
		map.setCenter(allMarkers[markersArrayLookup.indexOf($(this).attr('id'))].getPosition());
		map.setZoom(18);
	});
	
	var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	jax.open('POST',base_url+'/google/fetch_waypoints/'+id);
	jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	jax.send('command=fetch')
	jax.onreadystatechange = function(){ 
		if(jax.readyState==4) {
			if(jax.responseText != null){
				$("#route_dest").html("<b>"+eval('(' + jax.responseText + ')').start+"</b> to <b>"+eval('(' + jax.responseText + ')').end+"</b>");
				$("#route_title").val(eval('(' + jax.responseText + ')').title);
				$("#route_info").text(eval('(' + jax.responseText + ')').info);
				$("#ship_to").val(eval('(' + jax.responseText + ')').ship_to);
				setroute( eval('(' + jax.responseText + ')') ); 
			
			}
			else{
				smoke.signal('Unable to process this request.\nPlease try again later.', function(e){}, {
					duration: 9999
				});
			}
		}
	}
	
	GetScreensFromFolder(false);

	$("#print_details").click(function(){
		$("#dvLoading").fadeIn(300);
		smoke.alert("Cannot print report at this moment. Please wait for nearby hazards and screenshots processing to be finished.", function(e){}, {ok : 'OK'});
		$("#dvLoading").fadeOut(300);
	});	
	
	// Viewing screenshot
	$(document).on("click", ".map-screenshot img", function(){
		window.open($(this).attr("src"));
	});
	
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
});