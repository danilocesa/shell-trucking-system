// **************** Setting variables ***********************
	var id = l.href.substr(l.href.lastIndexOf('/')+1);
	var haz_stat = [];
	var markers = [];
	var infowindow = new google.maps.InfoWindow();
	// Define new RouteBoxer instance
	var routeBoxer = new RouteBoxer();
	// Define covered distance near path; default is km, adjusted to meters
	var distance = 10/1000;
	// Array to hold coordinates of boxes that covers the path
	var boxpolys = null;
	//marker location
	var mark_locat = "";
	 //geocode services	
	var geocoder = new google.maps.Geocoder();
	// Define variable to hold selected hazard id, for reordering
	var selectedHazardId = null;
	var path = new google.maps.MVCArray(), poly;
	//marker with label plugin
	var newmarker;
	var allHazards = [];
	var km_distance;
	//set default value of show hazard
	var showhazard = 1;
// **************** Maps Other options ***********************
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	sethazard();
	//google.maps.event.addListener(map,"dragend",function(e){
	//	sethazard();
	//});	

	//var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	//jax.open('POST',base_url+'/google/fetch_waypoints/'+id);
	//jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	//jax.send('command=fetch')
	//jax.onreadystatechange = function(){ 
	//	if(jax.readyState==4) {
	//		if(jax.responseText != null){
	//			$("#route_dest").html(eval('(' + jax.responseText + ')').start+" to "+eval('(' + jax.responseText + ')').end);
	//			$("#route_title").val(eval('(' + jax.responseText + ')').title);
	//			$("#route_info").text(eval('(' + jax.responseText + ')').info);
	//			$("#ship_to").val(eval('(' + jax.responseText + ')').ship_to);
	//			setroute( eval('(' + jax.responseText + ')') ); 
	//		
	//		}
	//		else{
	//			smoke.signal('Unable to process this request.\nPlease try again later.', function(e){}, {
	//				duration: 9999
	//			});
	//		}
	//	}
	//}

	function setroute(os)
	{
		var route_json = JSON.parse(os.route_json);
		var wp = [];
		
			
		//start image
		var image_start = {
			url: base_url+'/assets/img/start_image.png',
			size: new google.maps.Size(20, 32)
		};
		
		var start_marker = new google.maps.Marker({
			position: new google.maps.LatLng(route_json.start.lat,route_json.start.lng),
			icon: image_start
		});
		var infowindow =  new google.maps.InfoWindow({
			content: route_json.location['start_loc'],
			map: map
		});
		infowindow.open(map, start_marker);
		start_marker.setMap(map);


		//end image
		var image_end = {
			url: base_url+'/assets/img/end_image.png',
			size: new google.maps.Size(20, 32)
		};
		var end_marker = new google.maps.Marker({
			position: new google.maps.LatLng(route_json.end.lat,route_json.end.lng),
			icon: image_end
		});
		var infowindow =  new google.maps.InfoWindow({
			content: route_json.location['last_loc'],
			map: map
		});
		infowindow.open(map, end_marker);
		end_marker.setMap(map);
		
		//start
	
		wp.push( new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
		//midpoints
		for(var i=1;i<route_json.midpoints.length;i++){
			wp.push( new google.maps.LatLng(route_json.midpoints[i][0],route_json.midpoints[i][1]));
		}
		
		//end
		wp.push( new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		poly = new google.maps.Polyline({ 
			path: wp
		});
		poly.setMap(map);
		displayNearbyMarkers(poly.getPath().j);
		var bounds = new google.maps.LatLngBounds();
		bounds.extend( new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
		bounds.extend( new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		map.fitBounds(bounds);
		geocoder.geocode({'latLng': poly.getPath().j[0]}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					$("#start_pt_loc").val(results[0].formatted_address);
				} else {
					alert('No results found');
				}
			} else {
				alert('Geocoder failed due to: ' + status);
			}
		});

		geocoder.geocode({'latLng': poly.getPath().j[poly.getPath().j.length-1]}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					$("#end_pt_loc").val(results[0].formatted_address);
				} else {
					alert('No results found');
				}
			} else {
				alert('Geocoder failed due to: ' + status);
			}
		});

		$.ajax({
			url: base_url+'/google/geocode/', 
			async: true, 
			type: "POST", 
			data: {olat: poly.getPath().j[0].lat(), olng: poly.getPath().j[0].lng(), dlat: poly.getPath().j[poly.getPath().j.length-1].lat(), dlng: poly.getPath().j[poly.getPath().j.length-1].lng()},
			success:function(result,status,xhr){
				$("#map_path").append('<p style="font-size:12px">' + result + '</p>');
			},
			error:function(xhr,status,error){
				console.log(xhr + ' ' + status + ' ' + error);
			},
		});

		
	}
	

	function sethazard(){
		clearmarkerwithlabel();
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/',true);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					var hazard_json = JSON.parse(jax.responseText);
					for(var e = 0; e <= hazard_json.length-1; e++ ){
						hazard_marker = new google.maps.Marker({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
							icon:  {
								url: base_url+'/assets/img/icons/'+hazard_json[e].hazard_icon
							},
							title: hazard_json[e].title,
							id: hazard_json[e].hazard_id
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


	function setAllMap(map) {
	  for (var i = 0; i < haz_stat.length; i++) {
	    haz_stat[i].setMap(map);
	    allHazards[i].setMap(map); 
	  }
	}

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

	function showMarkers() {
		showhazard == 1;
		if(showhazard == 0){}
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
							km_distance = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()),haz_latlng);
							//mark_locat = results[0].formatted_address.split(",",2);
							$("p#reordering-panel").css("visibility", "visible");
							var new_item = '<li class="marker" id="' + haz_stat[j].id + '" alt="' + km_distance + '">' + haz_stat[j].getTitle()+'(KM '+km_distance+')</li>';
							$("ol#marker-list").append(new_item);	
					//haz_stat[j].setMap(map);
					continue;
				}
			}
		}
	}

	//**** Compute Distance
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


// Google map screenshot
function screenshot(){
	// Update background image version
	updateBackground();
	html2canvas(document.getElementById("map"), {
		onrendered: function(canvas) {
			document.getElementById("canvasImg_orig").src = canvas.toDataURL()
			var mycanvas = document.getElementById('myCanvas');
			if (mycanvas.getContext){
				var image = document.getElementById("canvasImg_orig");
				image.onload = function () {
					var context = mycanvas.getContext('2d');
					context.drawImage(image, $("#map").width()/2 - 320, $("#map").height()/2 - 320, 640, 640, 0, 0, 640, 640);
					$("#map_shots").append('<img class="map_shot_item" src="'+document.getElementById('myCanvas').toDataURL()+'" style="width:475px;height:450px;">');
					context.clearRect(0, 0, canvas.width, canvas.height);
				};
			}
		},
		useCORS: true,
		allowTaint: false
	});


}

// function updateBackground()
// Updates background image of map_canvas div
function updateBackground(){
	// Download image to server
	$.ajax({
		url:base_url+"/google/saveimg", 
		type: "POST", 
		dataType: "text", 
		async:false,
		cache:false,
		data: "url=" + encodeURIComponent('http://maps.googleapis.com/maps/api/staticmap?center=' + map.getCenter().lat() + ',' + map.getCenter().lng() + '&zoom=' + map.getZoom() + '&size=640x640&sensor=false'),
		success:function(result,status,xhr){
			console.log(result);
			$("#imgloading").show(); 
		},
		error:function(xhr,status,error){
			console.log(status);
		},
		beforeSend: function () { 
			$("#previewscreenie").modal(); 
			$("#gen_report").attr("disabled",true);
			
		},
		complete:function(){
			var d = new Date();
			$("#map_canvas").css('background-image','url(screenie/map/image.jpg?ver='+d.getTime()+')');
		}
	})
	.done(function(data){
			$("#imgloading").hide();
			$("#gen_report").removeAttr("disabled",true);
	});

	$("#map_canvas").css('background-position','center');
	$("#map_canvas").css('background-repeat','no-repeat');
	
	
}

// Add event handler for generating report
$("form#print_hazards").submit(function(){
	// Upload screenshots to server and include list of images
	var output = "";
	var ctr = 0;
	$("div#map_shots img").each( function () {
		// Upload image to server
		$.ajax({
			type: "POST",
			async:false,
			cache:false,
			url: base_url+"/google/save_img_google",
			data: {image: $(this).attr('src'), filename: ctr},
			success:function(data, textStatus, jqXHR){
				console.log('Screenshot saved');
				console.log(data);
			},
			error:function(jqXHR, textStatus, errorThrown){
				console.log('Screenshot error ' + errorThrown);
			}
		});
		output += '<input type="hidden" name="screens_id[]" value="' + ctr + '"/>';
		ctr++;
	});
	$(this).append(output);
	
	// Include list of hazards to the form
	output = "";
	$("ol#marker-list li").each( function () {
		output += '<input type="hidden" name="hzds[]" value="' + $(this).attr("id") + '"/>';
		output += '<input type="hidden" name="hzds_dst[' + $(this).attr("id") + ']" value="' + $(this).attr("alt") + '"/>';
	});
	$(this).append(output);

	// Include list of directions
	output = "";
	$("#map_path p").each( function () {
		output += '<input type="hidden" name="directions[]" value="' + $(this).html() + '"/>';
	});
	$(this).append(output);
	
	// Get origin and destination location
	$("#map_path").append('<input type="hidden" id="orgn" name="orgn" value="' + $("#start_pt_loc").val() + '"/>');
	$("#map_path").append('<input type="hidden" id="dest" name="dest" value="' + $("#end_pt_loc").val() + '"/>');
});

jQuery(window).load(function () {
	var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	jax.open('POST',base_url+'/google/fetch_waypoints/'+id);
	jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	jax.send('command=fetch')
	jax.onreadystatechange = function(){ 
		if(jax.readyState==4) {
			if(jax.responseText != null){
				$("#route_dest").html(eval('(' + jax.responseText + ')').start+" to "+eval('(' + jax.responseText + ')').end);
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

	$("#print_details").click(function(){
		screenshot();
	});
	setInterval(function(){ sethazard(); }, 30000);

});