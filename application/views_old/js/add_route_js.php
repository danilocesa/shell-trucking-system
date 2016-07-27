// **************** Setting variables ***********************
	//hold data for ajax
	var data = {};
	//poly line variable
	var path = new google.maps.MVCArray(), poly;
	//clear path
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
	var distance = 10/1000;
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
// **************** Initialize function ***********************
	sethazard();
// **************** Maps Other options ***********************
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('address'));
// **************** Maps events ***********************	
	google.maps.event.addListener(map,"mousemove",function(e){
		if(clickCounter >= 2){
			add_route_init = false;
			map.setOptions({draggableCursor: 'url(https://maps.gstatic.com/mapfiles/openhand_8_8.cur), auto'});
		} else {
			add_route_init = true;
		}

	});
	//google.maps.event.addListener(map,"dragend",function(e){
	//	sethazard();
	//});	

// **************** Functions ***********************
	//**** Search location
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
	
	//*******Add route
	function addRoute() {
		if(add_route_init == false){
			return;
		}
		if(clickCounter < 2){
			map.setOptions({draggableCursor: 'url(https://storage.googleapis.com/support-kms-prod/SNP_2752125_en_v0), auto'});
		}
		$('#addRouteModal').modal('show');
		$("#opts_sub").click(function(){
			//Automatic Directions
			if($("#direct_opts").val() == 1){
				google.maps.event.addListener(map,"click", function(location) {
					clickCounter += 1;
					if(clickCounter <= 2){	
						geocoder.geocode( { 'latLng': location.latLng}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								GetLocationInfo(location.latLng, results[0].formatted_address.split(",",2).toString(),1);
								service.route({ origin: points[0].LatLng, destination: points[points.length-1].LatLng, travelMode: google.maps.DirectionsTravelMode.DRIVING }, function(result, status) { 
			            			if (status == google.maps.DirectionsStatus.OK) {
			              				for(var i = 0, len = result.routes[0].overview_path.length-1; i < len; i++) {
						        			path.push(result.routes[0].overview_path[i]);
						       			}
						       			path.push(result.routes[0].legs[0].end_location);
			            			}
			            			console.log(path.j);
			            			displayNearbyMarkers(path.j);
			            			google.maps.event.addListener(poly.getPath(), 'set_at', function(){
										displayNearbyMarkers(path.j);
									}); 
			        			});
			        			poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5 });
		       					poly.setPath(path);
		       					test_path.push(poly);
		       					
							} else {
								smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){},{
									duration: 9999
								});
							}
						});
					}
				});
			}
			//Manual Directions
			else{
				google.maps.event.addListener(map,"click", function(location) {
					clickCounter += 1;
					if(clickCounter <= 2){
						sethazard();
						geocoder.geocode( { 'latLng': location.latLng}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								GetLocationInfo(location.latLng, results[0].formatted_address.split(",",2).toString(),0);
								poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5 });
								poly.setPath(path);
								test_path.push(poly);
								displayNearbyMarkers(path.j);
								google.maps.event.addListener(poly.getPath(), 'set_at', function(){
									displayNearbyMarkers(path.j);
								}); 
							} else {
								smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){},{
									duration: 9999
								});
							}
						});
					} else{

					}
				});
				//google.maps.event.addListener(poly, 'polylinecomplete', function() {
				//	alert(1);
				//});
			}
			$('#addRouteModal').modal('hide');
		});	
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
			 	poly = new google.maps.Polyline({ map: map, editable: true, strokeWeight: 5 });
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
					}
					displayNearbyMarkers(path.j);
					google.maps.event.addListener(poly.getPath(), 'set_at', function(){
						displayNearbyMarkers(path.j);
					}); 
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
            			}
            			displayNearbyMarkers(path.j);
            			google.maps.event.addListener(poly.getPath(), 'set_at', function(){
							displayNearbyMarkers(path.j);
						}); 
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
		if(clickCounter >= 2){
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
		} else {
			smoke.signal('Please add route', function(e){},{
				duration: 9999
			});
		}
		

		//clickCounter = 0;
		//path = [];
    	//points = [];
       // BuildPoints();
    }

    //**** Save route
    function save_directions() {
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
	}

	//*** Set hazard to map
	function sethazard() {
		clearmarkerwithlabel();
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					var hazard_json = JSON.parse(jax.responseText); 
					for(var e = 0; e <= hazard_json.length-1; e++ ){
						hazard_marker = new google.maps.Marker({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
							icon: {
								url: base_url+'/assets/img/icons/'+hazard_json[e].hazard_icon
							},
							title: hazard_json[e].title,
						});
						newmarker = new MarkerWithLabel({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
							labelContent: hazard_json[e].title,
							map: map,
							title: hazard_json[e].title,
							labelAnchor: new google.maps.Point(-30, 33),
							labelClass: "hazard-label", // the CSS class for the label
							icon: base_url+'/assets/img/icons/' + hazard_json[e].hazard_icon
						});
						allHazards.push(newmarker);
						hazard_marker.setMap(map);
						newmarker.setMap(map);
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
								'<h5 style="color:#0174DF;">Latitude:</h5><h6>'+ hazard_json[e].latitude + '</h6>'+
								'<h5 style="color:#0174DF;">Longtitude:</h5><h6>'+ hazard_json[e].longtitude + '</h6></div>'+
								'<div class="last-update">'+
								'<h5 style="color:#0174DF;">Last Update By:</h5><h6>'+ hazard_json[e].last_update_by + '</h6></div>'+
								'<div class="image">'+
								'<h5 style="color:#0174DF;">Image:</h5>'+
								"<img id='preview_img' src=' " + base_url+'/uploads/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>'+
								'<div>'
								;
								var infowindow =  new google.maps.InfoWindow({
									content: contentString,
									map: map,
									title: hazard_json[e].title
								});
								infowindow.open(map, newmarker);
							}
						})(newmarker, e));
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
		}

	}

	//*** Hide markers
	function hideMarkers() {
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
	function displayNearbyMarkers(path){
		// Draw invisible boxes on covered areas
		var boxes = routeBoxer.box(path, distance);
		boxes.unshift(boxes[0]);
		boxpolys = new Array(boxes.length);
		for (var i = 0; i < boxes.length; i++) {
			boxpolys[i] = new google.maps.Rectangle({
			bounds: boxes[i],
			fillOpacity: 0.0,
			fillColor: '#fff',
			strokeOpacity: 0,
			strokeColor: '#0f0',
			strokeWeight: 1,
			map: map
			});
		}
		$("#release_route").html("");
		$("ol#marker-list").html("");
		var tot_dist = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()),new google.maps.LatLng(path[path.length-1].lat(),path[path.length-1].lng()));
		$("#totaldistance").html("Total distance "+tot_dist+" KM");
		for(var i = 0; i < boxpolys.length; i++){
			for(var j = 0; j < haz_stat.length; j++){
				if(boxpolys[i].getBounds().contains(new google.maps.LatLng(haz_stat[j].getPosition().lat(), haz_stat[j].getPosition().lng()))){
					var haz_latlng = new google.maps.LatLng(haz_stat[j].getPosition().lat(), haz_stat[j].getPosition().lng());
					//geocoder.geocode( {'latLng':haz_latlng}, function(results, status) {
						//if (status == google.maps.GeocoderStatus.OK) {
							km_distance = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()),haz_latlng);
							//mark_locat = results[0].formatted_address.split(",",2);
							$("p#reordering-panel").css("visibility", "visible");
							var new_item = '<li class="marker" id="' + j + '">' + haz_stat[j].getTitle()+'(KM '+km_distance+')</li>';
							$("ol#marker-list").append(new_item);				
						//}
					//});
					//haz_stat[j].setMap(map);
					continue;
				}
			}

		}

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
			alert(output);
		});

		$("#close_route").click(function(){
			map.setOptions({draggableCursor: 'url(https://maps.gstatic.com/mapfiles/openhand_8_8.cur), auto'});
		});

		$(".form-control").keyup(function(){
			$(this).each(function (item) {
	            $(this).css('border', 'none');
        	});
		});
	setInterval(function(){ sethazard(); }, 30000);	
	
});