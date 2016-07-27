// **************** Setting variables ***********************
	//hold data for ajax
	var data = {};
	//poly line variable
	var path = new google.maps.MVCArray(), poly
;	//clear path
	var test_path = [];
	//holds markers
	var points = [];
	//for markers
    var markers = [];
    //geocode services	
	var geocoder = new google.maps.Geocoder();
	//infowindow
	var infowindow = new google.maps.InfoWindow();
	//hazard status
	var haz_stat = [];
	//direction services
	var service = new google.maps.DirectionsService();
	//var directionsDisplay = new google.maps.DirectionsRenderer();
	//route options
	var direction_opt = false;
	// Define variable to hold selected hazard id, for reordering
	var selectedHazardId = null;
	// Define new RouteBoxer instance
	var routeBoxer = new RouteBoxer();
	// Define covered distance near path; default is km, adjusted to meters
	var distance = 20/1000;
	// Array to hold coordinates of boxes that covers the path
	var boxpolys = null;
	//marker location
	var mark_locat = "";
	//click counter
	var clickCounter = 0;
	//Set add route
	var add_route_init = false;
	//marker with label plugin
	var newmarker;
	var allHazards = [];
	var km_distance;
	//set default value of show hazard
	var showhazard = 1;
	var allPoly = [];
	var count_li;
// **************** Initialize function ***********************
	sethazard(0);
	google.maps.Polygon.prototype.my_getBounds=function(){
		var bounds = new google.maps.LatLngBounds()
		this.getPath().forEach(function(element,index){bounds.extend(element)})
		return bounds
	}
// **************** Maps Other options ***********************
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('address'));
	//map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('help_tour'));
// **************** Maps events ***********************	
	google.maps.event.addListener(map,"mousemove",function(e){
		if(clickCounter >= 2){
			add_route_init = false;
			map.setOptions({draggableCursor: 'url(https://maps.gstatic.com/mapfiles/openhand_8_8.cur), auto'});
		} else {
			add_route_init = true;
		}

	});
	google.maps.event.addListener(map,"zoom_changed",function(e){
		if(map.getZoom() <= 16){
			hideMarkers();
		} else{
			showMarkers();
		}
	});	

// **************** Functions ***********************
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
		$("#dvLoading").fadeIn(300);
		if(add_route_init == false){
			$("#dvLoading").fadeOut(300);
			return;
		}
		if($("#from_depot option").length == 0){
			$("#opts_sub").attr("disabled");
			smoke.signal('Please add Hazard for Depot and Sites before proceeding to add Routes.', function(e){}, {
				duration: 9999
			});
			$("#dvLoading").fadeOut(300);
			return;
		}
		$('#addRouteModal').modal('show');
		$("#opts_sub").click(function(){
			$("#dvLoading").fadeIn(300);
			var depot = new google.maps.LatLng($("#from_depot option:selected").attr("data-latitude"),$("#from_depot option:selected").attr("data-longtitude"));
			var site = new google.maps.LatLng($("#to_site option:selected").attr("data-latitude"),$("#to_site option:selected").attr("data-longtitude"));
			//Automatic Directions
			if($("#direct_opts").val() == 1){
				//google.maps.event.addListener(map,"click", function(location) {
					//clickCounter += 1;
					//if(clickCounter == 1){
					//	$("#point_notify").text("Destination");
					//}
					//if(clickCounter == 2){
					//	$("#point_notify").hide();
					//}	
					//if(clickCounter <= 2){
						
						//Depot marker
						geocoder.geocode( { 'latLng': depot}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								GetLocationInfo(depot, results[0].formatted_address.split(",",2).toString(),1);
								service.route({ origin: points[0].LatLng, destination: points[points.length-1].LatLng, travelMode: google.maps.DirectionsTravelMode.DRIVING }, function(result, status) { 
			            			if (status == google.maps.DirectionsStatus.OK) {
			              				for(var i = 0, len = result.routes[0].overview_path.length-1; i < len; i++) {
						        			path.push(result.routes[0].overview_path[i]);
						       			}
						       			path.push(result.routes[0].legs[0].end_location);
			            			}
			            			displayNearbyMarkers(path.j, path.j[0]);			        
			        			});
			        			poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5,strokeColor: '#FF0000' });
		       					poly.setPath(path);
		       					test_path.push(poly);
								google.maps.event.addListener(poly.getPath(), 'set_at', function(){
										displayNearbyMarkers(path.j, path.j[0]);
								}); 
		       					
							} else {
								smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){},{
									duration: 9999
								});
							}
						});

						//Site marker
						geocoder.geocode( { 'latLng': site}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								GetLocationInfo(site, results[0].formatted_address.split(",",2).toString(),1);
								service.route({ origin: points[0].LatLng, destination: points[points.length-1].LatLng, travelMode: google.maps.DirectionsTravelMode.DRIVING }, function(result, status) { 
			            			if (status == google.maps.DirectionsStatus.OK) {
			              				for(var i = 0, len = result.routes[0].overview_path.length-1; i < len; i++) {
						        			path.push(result.routes[0].overview_path[i]);
						       			}
						       			path.push(result.routes[0].legs[0].end_location);
			            			}
			            			displayNearbyMarkers(path.j, path.j[0]);
			        			});
			        			poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5,strokeColor: '#FF0000' });
		       					poly.setPath(path);
		       					test_path.push(poly);
								google.maps.event.addListener(poly.getPath(), 'set_at', function(){
									displayNearbyMarkers(path.j, path.j[0]);
								}); 
							} else {
								smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){},{
									duration: 9999
								});
							}
						});


					//}
				//});
			}
			//Manual Directions
			else{
				//google.maps.event.addListener(map,"click", function(location) {
					//clickCounter += 1;
					//if(clickCounter == 1){
					//	$("#start_point").hide();
					//map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('end_point'));
					//}
					//if(clickCounter == 2){
					//	$("#start_point").hide();
					//	$("#end_point").hide();
					//}	
					//if(clickCounter <= 2){

						//Depot marker
						geocoder.geocode( { 'latLng': depot}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								GetLocationInfo(depot, results[0].formatted_address.split(",",2).toString(),0);
								poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5, strokeColor: '#FF0000' });
								poly.setPath(path);
								test_path.push(poly);
								displayNearbyMarkers(path.j, path.j[0]);
								google.maps.event.addListener(poly.getPath(), 'set_at', function(){
									displayNearbyMarkers(path.j, path.j[0]);
								}); 
							} else {
								smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){},{
									duration: 9999
								});
							}
						});

						//Site marker
						geocoder.geocode( { 'latLng': site}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								GetLocationInfo(site, results[0].formatted_address.split(",",2).toString(),0);
								poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5, strokeColor: '#FF0000' });
								poly.setPath(path);
								test_path.push(poly);
								displayNearbyMarkers(path.j, path.j[0]);
								google.maps.event.addListener(poly.getPath(), 'set_at', function(){
									displayNearbyMarkers(path.j, path.j[0]);
								}); 
							} else {
								smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){},{
									duration: 9999
								});
							}
						});
					//} else{

					//}
				//});
			}
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
			if(route != 1){
				path.push(latlng);
			}
			BuildPoints(route);
        }
	}

	//*** Clear markers and path 
	function clearMarkers() {
		for (var i=0; i<markers.length; i++) {
			markers[i].setMap(null);
        }
        markers = [];
		for (var i=0; i<test_path.length; i++) {
			test_path[i].setMap(null);
        }
        test_path = [];
    }
  

    //**** Build points and marker drag
    function BuildPoints(route) {
		clearMarkers();
		var html = "";
        for (var i=0; i<points.length; i++) {
			var marker = new google.maps.Marker({position: points[i].LatLng, title : points[i].LocationName, draggable: true});
			markers.push(marker);
			marker.setMap(map);
			html += "<tr><td>" + points[i].LocationName + "</td>";

			google.maps.event.addListener(marker, 'dragend', function(event) {
				path = [];
				poly.setMap(null);
			 	poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5, strokeColor: '#FF0000' });
			 	path = poly.getPath();
			 	//Marker drag polyline only
			 	if(route != 1){
			 		if($("#waypointsLocations tr").length >= 2){
						html = "";
						$("#waypointsLocations tbody").html(html);
					}
					for (var a = 0; a < markers.length; a++) {
						var wew = markers[a].getPosition();
						path.push(wew);
				    	poly.setPath(path);
				    	test_path.push(poly);
				    	geocoder.geocode( { 'latLng': wew}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
							} else {
								smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){},{
									duration: 9999
								});
							}
						});
						displayNearbyMarkers(path.j,path.j[0]);
						google.maps.event.addListener(poly.getPath(), 'set_at', function(){
							displayNearbyMarkers(path.j,path.j[0]);
						}); 
					}
				}	
				//Marker drag directions only	
			 	else{
			 		service.route({ origin: markers[0].getPosition(), destination: markers[markers.length-1].getPosition(), travelMode: google.maps.DirectionsTravelMode.DRIVING }, function(result, status) { 
            			if (status == google.maps.DirectionsStatus.OK) {
              				for(var i = 0, len = result.routes[0].overview_path.length-1; i < len; i++) {
			        			path.push(result.routes[0].overview_path[i]);
			       			}
			       			path.push(result.routes[0].legs[0].end_location);
			       			poly.setPath(path);
	       					test_path.push(poly);
							displayNearbyMarkers(path.j,path.j[0]);	
							google.maps.event.addListener(poly.getPath(), 'set_at', function(){
								displayNearbyMarkers(path.j,path.j[0]);	
							}); 
            			}
        			});
	       		}	
			});
        }
        
		$("#waypointsLocations tbody").html(html);
		$( "button.delete" ).button({
			icons: { primary: "ui-icon-trash" }
		});
	}

	//*** Clear marker and path
	function ClearPolyLine() {
		$("#dvLoading").fadeIn(300);
		//if(clickCounter >= 2){
			smoke.confirm("Are you sure?", function(e){
			if (e){
				location.reload();
			}
			}, {
				ok: "Yes",
				cancel: "No",
				classname: "custom-class",
				reverseButtons: true
			});
		//} else {
		//	smoke.signal('Please add route', function(e){},{
		//		duration: 9999
		//	});
		//}
		

		//clickCounter = 0;
		//path = [];
    	//points = [];
       // BuildPoints();
       $("#dvLoading").fadeOut(300);
    }

    //**** Save route
    function save_directions() {
    	$("#dvLoading").fadeIn(300);
    	if(!$("#route_title").val() || !$("#route_info").val() || !$("#ship_to").val()){
    		$("#route_title").css({"border":"1px solid red"});
    		$("#route_info").css({"border":"1px solid red"});
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
		else if(!$("#route_info").val()){
			$("#route_info").css({"border":"1px solid red"});
			smoke.signal("Route info", function(e){}, {
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
			var w=[],mp; 
			if (path instanceof Array) {
				var endArray = path.length-1;
				data.start = {'lat': path[0].lat(), 'lng':path[0].lng()}
				data.end = {'lat': path[endArray].lat(), 'lng':path[endArray].lng()}
				for(var d=1; d<path.length-1; d++){
					w[d] = [path[d].lat(),path[d].lng()];
				}
			} else {
				var endArray = path.getArray().length-1;
				data.start = {'lat': path.getArray()[0].lat(), 'lng':path.getArray()[0].lng()}
				data.end = {'lat': path.getArray()[endArray].lat(), 'lng':path.getArray()[endArray].lng()}
				for(var d=1; d<path.getArray().length-1; d++){
					w[d] = [path.getArray()[d].lat(),path.getArray()[d].lng()];
				}
			}
			//points[points.length-1].LocationName
			data.midpoints = w;
			data.info = {'title':$("#route_title").val(), 'information':$("#route_info").val(), 'ship_to':$("#ship_to").val() }
			data.location = {'start_loc': points[0].LocationName, 'last_loc': points[points.length-1].LocationName}
			var str = JSON.stringify(data);
			var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
			jax.open('POST',base_url+'/google/save_points');
			jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			jax.send('command=save&mapdata='+str)
			jax.onreadystatechange = function(){ 
				if(jax.readyState==4) {
					var na_json = JSON.parse(jax.responseText);
					if(na_json.resp == 'success'){
						smoke.signal("Route saved, redirecting to the list..", function(e){
							setTimeout(function(){window.location=base_url+"/google/routes"},500);
						}, {
							duration: 3000
						});
					}
					else{
						smoke.signal(jax.responseText, function(e){}, {
							duration: 9999
						});
					}
				}
			}
		}
		$("#dvLoading").fadeOut(300);
	}

	//*** Set hazard to map
	function sethazard(stat) {
		clearmarkerwithlabel();
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					var hazard_json = JSON.parse(jax.responseText);
					if($('#from_depot option').length == 0){
						smoke.signal('Please add Hazard for Depot and Sites before proceeding to add Routes.', function(e){}, {
							duration: 9999
						});
					}
					var hazard_poly;
					for(var e = 0; e <= hazard_json.length-1; e++ ){
						setAllMap(null);
						// Extract latlngs and draw polygon
						var lats = hazard_json[e].latitude.split("|");
						var lngs = hazard_json[e].longitude.split("|");
						var polyArr = [];
						for (var i=0; i < lats.length; i++)
						{
							polyArr.push(new google.maps.LatLng(lats[i], lngs[i]));
						}
						
						hazard_poly = new google.maps.Polygon({
							paths: polyArr,
							strokeColor: '#FF0000',
							strokeOpacity: 0,
							strokeWeight: 0,
							fillColor: '#FF0000',
							fillOpacity: 0
						});
						hazard_marker = new google.maps.Marker({
							position: hazard_poly.my_getBounds().getCenter(),
							icon: {
								url: base_url+'/assets/img/icons/'+hazard_json[e].hazard_icon
							},
							title: hazard_json[e].title,
						});
						newmarker = new MarkerWithLabel({
							position: hazard_poly.my_getBounds().getCenter(),
							labelContent: hazard_json[e].title,
							map: map,
							title: hazard_json[e].title,
							labelAnchor: new google.maps.Point(-30, 33),
							labelClass: "hazard-label", // the CSS class for the label
							icon: base_url+'/assets/img/icons/' + hazard_json[e].hazard_icon
						});
						if(stat == 1){
							hazard_marker.setMap(map);
							newmarker.setMap(map);
						}else{
							hazard_marker.setMap(null);
							newmarker.setMap(null);
						}
						allHazards.push(newmarker);
						allPoly.push(hazard_poly);
						haz_stat.push(hazard_marker);
						google.maps.event.addListener(newmarker,"click", (function(newmarker,e) {
							return function() {
								var contentString = '<div class="content" style="text-align:left;">'+
								'<div class="title">'+
								'<h5 style="color:#0174DF;">Title:</h5><h6>'+ hazard_json[e].title + '</h6></div>'+
								'<div class="information">'+
								'<h5 style="color:#0174DF;">Information:</h5><h6>'+ hazard_json[e].information + '</h6></div>'+
								'<div class="location">'+
								'<h5 style="color:#0174DF;">Location:</h5><h6>'+ hazard_json[e].location + '</h6></div>'+
								'<div class="latlng">'+
								'<h5 style="color:#0174DF;">Latitude:</h5><h6>'+ hazard_json[e].center_latitude + '</h6>'+
								'<h5 style="color:#0174DF;">Longtitude:</h5><h6>'+ hazard_json[e].center_longitude + '</h6></div>'+
								'<div class="last-update">';
								var last_update;
								if(typeof hazard_json[e].last_update_by === 'undefined'){
								  last_update = '';
								}else{
									last_update = '<h5 style="color:#0174DF;">Last Update By:</h5><h6>'+ hazard_json[e].last_update_by + '</h6></div>'+
									'<div class="image">'+
									'<h5 style="color:#0174DF;">Image:</h5>';
								}
								
								var image_path;
								if(hazard_json[e].hazard_id.substr(hazard_json[e].hazard_id.length - 1) == "h"){
									image_path = "<img id='preview_img' src=' " +base_url+'/uploads/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>'+'<div>';
								} else if(hazard_json[e].hazard_id.substr(hazard_json[e].hazard_id.length - 1) == "s"){
									image_path = "<img id='preview_img' src=' " +base_url+'/uploads/sites/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>'+'<div>';
								} else{
									image_path = "<img id='preview_img' src=' " +base_url+'/uploads/depots/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>'+'<div>';
								}
								var info_deta = contentString.concat(image_path,last_update);
								var infowindow =  new google.maps.InfoWindow({
									content: info_deta,
									map: map,
									title: hazard_json[e].title
								});
								infowindow.open(map, newmarker);
							}
						})(newmarker, e));
						setAllMap(map);
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

	//**** Set to map
	function setAllMap(map) {
		for (var i = 0; i < haz_stat.length; i++) {
			haz_stat[i].setMap(map);
			allHazards[i].setMap(map);
			allPoly[i].setMap(map);			
		}
		//$("#dvLoading").fadeOut(300);

	}

	//*** Hide markers
	function hideMarkers() {
		//$("#dvLoading").fadeIn(300);
		showhazard = 0;
		$.each(allHazards, function(index, value) {
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
	//**** Show markers
	function showMarkers() {
		//$("#dvLoading").fadeIn(300);
		showhazard == 1;
		if(showhazard == 0){
			//$.each(allHazards, function(index, value) {
			//	var newmarker = new MarkerWithLabel({
			//		position: new google.maps.LatLng(value.latitude, value.longitude),
			//		map: map,
			//		labelContent: value.title,
			//		title: value.title,
			//		labelAnchor: new google.maps.Point(-30, 33),
			//		labelClass: "hazard-label", // the CSS class for the label
			//		icon: base_url+'/assets/img/icons/' + value.hazard_icon
			//	});
			//	newmarker.setMap(map);
			//});
		}
	  setAllMap(map);

	}
	//**** Display nearby markers
	function displayNearbyMarkers(path, first_point){	
		$("#dvLoading").fadeIn(300);
		//for(var j = 0; j < allPoly.length; j++){
		//	if (allPoly[j] == null) {
	    //        alert("No Polygon");
	    //    }
	    //    else {
	    //    	for(var k = 0; k < path.length; k++){
	    //    		if (allPoly[j].Contains(new google.maps.LatLng(path[k].lat(),path[k].lng()))) {
		//                console.log(new google.maps.LatLng(path[k].lat(),path[k].lng()) + "is inside the polygon.");
		//            } else {
		//                console.log(new google.maps.LatLng(path[k].lat(),path[k].lng()) + "is outside the polygon.");
		//            }
		//			//if(allPoly[j].containsLatLng(new google.maps.LatLng(path[k].lat(),path[k].lng()))){
		//			//	alert(true);
		//			//}
		//		}
	    //    }
	    //}    

		//console.log(test_path[0].getBounds());
		//for(var j = 0; j < allPoly.length; j++){
		//	for(var k = 0; k < path.length; k++){
		//		if(allPoly[j].containsLatLng(new google.maps.LatLng(path[k].lat(),path[k].lng()))){
		//			alert(true);
		//		}
		//	}
		//}
		//return;
		// Draw invisible boxes on covered areas
		var boxes = routeBoxer.box(path, distance);
		var reverse = true;
		//for(var i = 0; i < Math.ceil(boxes.length / 2); i++)
		//{
		//	if(boxes[i].contains(new google.maps.LatLng(first_point.lat(),first_point.lng())))
		//	{
		//		reverse = false;
		//		break;
		//	}
		//}
		//if (reverse)
		//	boxes.reverse();
		boxes.unshift(boxes[0]);
		if (boxpolys != null)
		{
			for (var i = 0; i < boxpolys.length; i++) {
				boxpolys[i].setMap(null);
			}
		}
		boxpolys = new Array(boxes.length);
		for (var i = 0; i < boxes.length; i++) {
			boxpolys[i] = new google.maps.Rectangle({
			bounds: boxes[i],
			fillOpacity: 0,
			fillColor: 'green',
			strokeOpacity: 0,
			strokeColor: '#000',
			strokeWeight: 1,
			map: map
			});
		}
		$("#release_route").html("");
		$("ol#marker-list").html("");
		var tot_dist = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()),new google.maps.LatLng(path[path.length-1].lat(),path[path.length-1].lng()));
		$("#totaldistance").html("Total distance "+tot_dist+" KM");
		for(var j = 0; j < allPoly.length; j++){
			// Center of polygon
			// May 22
			//var center = allPoly[j].my_getBounds().getCenter();
			//var hit = false;
        	//for(var i = 0; i < boxpolys.length && !hit; i++){
        		//if (allPoly[j].Contains(boxpolys[i].bounds.getNorthEast()) && allPoly[j].Contains(boxpolys[i].bounds.getSouthWest()) && allPoly[j].Contains(boxpolys[i].bounds.getCenter())) {
        			//km_distance = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()), center);
					//$("p#reordering-panel").css("visibility", "visible");
					//var new_item = '<li class="marker" id="' + j + '">' + haz_stat[j].getTitle() + '(KM ' + km_distance + ')</li>';
					//$("ol#marker-list").append(new_item);
					//hit = true;
					//break;
	            //}
			//}

			// Get polygon
			var paths = allPoly[j].getPath();
			// Center of polygon
			var center = allPoly[j].my_getBounds().getCenter();
			var hit = false;
			
			// Iterate on polygon edges
			for (var k=0; k < paths.getLength() && !hit; k++) {
				var mylatlng = new google.maps.LatLng(paths.getAt(k).lat(), paths.getAt(k).lng());
				// Check if one of the polygon edges is within the route box
				
				for(var i = 0; i < boxpolys.length; i++){					
					if(boxpolys[i].getBounds().contains(mylatlng))
					{						
						km_distance = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()), center);
						$("p#reordering-panel").css("visibility", "visible");
						var new_item = '<li class="marker" id="' + j + '">' + haz_stat[j].getTitle() + '(KM ' + km_distance + ')</li>';
						$("ol#marker-list").append(new_item);
						hit = true;
						break;
					}
				}
			}
	    }   
		if(typeof count_li != "undefined"){
			$("#nearbyhaz_count").html("(Record count: "+ count_li +")");
		}
		$("#dvLoading").fadeOut(300);
	}

	function calcDistance(p1, p2){
		return (google.maps.geometry.spherical.computeDistanceBetween(p1, p2) / 1000).toFixed(2);
	}

	function clearmarkerwithlabel(){
		for (var i = 0; i < haz_stat.length; i++) {
			haz_stat[i].setMap(null);
			allHazards[i].setMap(null); 
			newmarker.setMap(null);
		}
		haz_stat = [];
		allHazards = [];
		allPoly = [];

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
//DOM Element
$(document).ready(function(){
		// Add event handler for move up
		$(document).on("click", "button.moveup", function(){
			if (selectedHazardId != null)
			{
				// Element to move
				var $el = $("li#" + selectedHazardId);
				
				// Move element up one step
				if ($el.not(':first-child'))
					$el.prev().before($el);
			}
		});
		
		// Add event handler for move down
		$(document).on("click", "button.movedown", function(){
			if (selectedHazardId != null)
			{
				// Element to move
				var $el = $("li#" + selectedHazardId);
			
				// Move element down one step
				if ($el.not(':last-child'))
					$el.next().after($el);
			}
		});
		
		// Add event handler for marker click
		// Highlight selected marker
		$(document).on("click", "li.marker", function(){
			// Store marker id to selectHazardId
			selectedHazardId = $(this).attr("id");
			
			// Revert selected marker to normal marker
			$("li.selected").removeClass("selected").addClass("marker");
			
			// Assign selected class to selected marker
			$(this).removeClass("marker").addClass("selected");
		});
		
		// Add event handler for print hazards
		// Display the list of hazards
		$(document).on("click", "button.print", function(){
			var output = "";
			$("ol#marker-list li").each( function () {
				output += $(this).text() + "\n";
			});
		});

		$("#close_route").click(function(){
			map.setOptions({draggableCursor: 'url(https://maps.gstatic.com/mapfiles/openhand_8_8.cur), auto'});
		});

		$(".form-control").keyup(function(){
			$(this).each(function (item) {
	            $(this).css('border', 'none');
        	});
		});
	
	if(map.getZoom() <= 16){
		hideMarkers();
	} else{
		setInterval(function(){ 
			if(map.getZoom() <= 16){
				hideMarkers();
			} else{
				sethazard(1); 
			}
		}, 30000);	
	}	

	
});