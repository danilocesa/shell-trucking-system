// **************** Setting variables ***********************
	var id = l.href.substr(l.href.lastIndexOf('/')+1);
	var haz_stat = [];
	var markers = [];
	//hold data for ajax
	var data = {};
	var infowindow = new google.maps.InfoWindow();
	// Define new RouteBoxer instance
	var routeBoxer = new RouteBoxer();
	// Define covered distance near path; default is km, adjusted to meters
	var distance = 0.5/1000;
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
	var count_li;
	var allPoly = [];
	var hazard_ids = []; 
// **************** Maps Other options ***********************
	google.maps.Polygon.prototype.my_getBounds=function(){
		var bounds = new google.maps.LatLngBounds()
		this.getPath().forEach(function(element,index){bounds.extend(element)})
		return bounds
	}	//var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');

	google.maps.event.addListener(map,"zoom_changed",function(e){
		//alert(map.getZoom());
		if(map.getZoom() <= 16){
			hideMarkers();
		} else{
			showMarkers();
		}
	});	
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	sethazard(0);
	$("#save_way").hide();
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
			strokeColor: '#FF0000',
			path: wp
		});
		poly.setMap(map);
		displayNearbyMarkers(poly.getPath().j, poly.getPath().j[0]);
		var bounds = new google.maps.LatLngBounds();
		bounds.extend( new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
		bounds.extend( new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		map.fitBounds(bounds);
		geocoder.geocode({'latLng': poly.getPath().j[0]}, function(results, status) {
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

		geocoder.geocode({'latLng': poly.getPath().j[poly.getPath().j.length-1]}, function(results, status) {
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
	function editPoly(){
		$("#back_button").attr("onclick","location.reload()");
		$("#dvLoading").fadeIn(300);
		poly.setEditable(true);
		$("#save_way").show();
		$("#ship_to").removeAttr("readonly");
		$("#route_title").removeAttr("readonly");
		$("#route_info").removeAttr("readonly");
		$("#dvLoading").fadeOut(300);
		google.maps.event.addListener(poly.getPath(), 'set_at', function() {
  			displayNearbyMarkers(poly.getPath().j, poly.getPath().j[0]);
		});
	}

	//**** Save route
    function save_directions() {
    	$("#dvLoading").fadeIn(300);
    	var poly_path = poly.getPath().j;
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
		else if(poly_path.length === 0){
			smoke.signal("Create a route", function(e){}, {
				duration: 9999
			});
		} else {
			var w=[],mp; 
			if (poly_path instanceof Array) {
				var endArray = poly_path.length-1;
				data.start = {'lat': poly_path[0].lat(), 'lng':poly_path[0].lng()}
				data.end = {'lat': poly_path[endArray].lat(), 'lng':poly_path[endArray].lng()}
				for(var d=1; d<poly_path.length-1; d++){
					w[d] = [poly_path[d].lat(),poly_path[d].lng()];
				}
			} else {
				var endArray = poly_path.getArray().length-1;
				data.start = {'lat': poly_path.getArray()[0].lat(), 'lng':poly_path.getArray()[0].lng()}
				data.end = {'lat': poly_path.getArray()[endArray].lat(), 'lng':poly_path.getArray()[endArray].lng()}
				for(var d=1; d<poly_path.getArray().length-1; d++){
					w[d] = [poly_path.getArray()[d].lat(),poly_path.getArray()[d].lng()];
				}
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
					var na_json = JSON.parse(jax.responseText);
					if(na_json.resp == 'success'){
						smoke.signal("Route updated, redirecting to the list..", function(e){
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
	function sethazard(stat){
		clearmarkerwithlabel();
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/',true);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					var hazard_json = JSON.parse(jax.responseText);
					var hazard_poly;
					hazard_ids = [];
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
							strokeWeight: 1,
							fillColor: '#FF0000',
							fillOpacity: 0
						});
						hazard_marker = new google.maps.Marker({
							position: hazard_poly.my_getBounds().getCenter(),
							icon:  {
								url: base_url+'/assets/img/icons/'+hazard_json[e].hazard_icon
							},
							title: hazard_json[e].title,
							id: hazard_json[e].hazard_id,
							custom:{information:hazard_json[e].information,location:hazard_json[e].location,latitude:hazard_json[e].latitude,longtitude:hazard_json[e].longtitude,hazard_image:hazard_json[e].hazard_image}
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
						hazard_ids.push(hazard_json[e].hazard_id);
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


	function setAllMap(map) {
	  for (var i = 0; i < haz_stat.length; i++) {
	    haz_stat[i].setMap(map);
	    allHazards[i].setMap(map); 
	    allPoly[i].setMap(map);  
	  }
	  $("#dvLoading").fadeOut(300);
	}

	function hideMarkers() {
		$("#dvLoading").fadeIn(300);
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
		$("#dvLoading").fadeIn(300);
		showhazard == 1;
		if(showhazard == 0){}
		setAllMap(map);
	}
	
	//**** Display nearby markers
	function displayNearbyMarkers(path, first_point){
		// Draw invisible boxes on covered areas
		var boxes = routeBoxer.box(path, distance);
		
		//	Modify order of boxes based on location of first indicator
		//	If center of indicator is covered within the first half of the array, leave current order of elements.
		//	If not, reverse the order.
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
		for(var j = 0; j < allPoly.length; j++){
		// Get polygon
		var paths = allPoly[j].getPath();
		// Center of polygon
		var center = allPoly[j].my_getBounds().getCenter();
		var hit = false;
		for (var k = 0; k < boxpolys.length && !hit; k++) {	
			var NE = boxpolys[k].bounds.getNorthEast();
			var SW = boxpolys[k].bounds.getSouthWest();
			var NW = new google.maps.LatLng(NE.lat(),SW.lng());
			var SE = new google.maps.LatLng(SW.lat(),NE.lng());
			var containsNE = google.maps.geometry.poly.containsLocation(boxpolys[k].bounds.getNorthEast(), allPoly[j]);
			var containsSW = google.maps.geometry.poly.containsLocation(boxpolys[k].bounds.getSouthWest(), allPoly[j]);
			var containsC = google.maps.geometry.poly.containsLocation(boxpolys[k].bounds.getCenter(), allPoly[j]);
			var containsNW = google.maps.geometry.poly.containsLocation(NW, allPoly[j]);
			var containsSE = google.maps.geometry.poly.containsLocation(SE, allPoly[j]);
			if ( containsNE == true || containsSW == true || containsC == true || containsNW == true || containsSE == true ) {
				km_distance = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()), center);
				$("p#reordering-panel").css("visibility", "visible");
				var new_item = '<li class="marker" id="' + j + '">' + haz_stat[j].getTitle() + '(KM ' + km_distance + ')</li>';
				$("ol#marker-list").append(new_item);
				hit = true;
				break;
			}
		}
		// Iterate on polygon edges
		//for (var k=0; k < paths.getLength() && !hit; k++) {
		//	var mylatlng = new google.maps.LatLng(paths.getAt(k).lat(), paths.getAt(k).lng());
		//	for(var i = 0; i < boxpolys.length; i++){					
		//		if(boxpolys[i].getBounds().contains(mylatlng))
		//		{						
		//			km_distance = calcDistance(new google.maps.LatLng(path[0].lat(),path[0].lng()), center);
		//			$("p#reordering-panel").css("visibility", "visible");
		//			var new_item = '<li class="marker" id="' + j + '">' + haz_stat[j].getTitle() + '(KM ' + km_distance + ')</li>';
		//			$("ol#marker-list").append(new_item);
		//			hit = true;
		//			break;
		//		}
		//	}
		//}
		}
		if(typeof count_li != "undefined"){
			$("#nearbyhaz_count").html("(Record count: "+ count_li +")");
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
			allPoly[i].setMap(null); 
		}
		haz_stat = [];
		allHazards = [];
		allPoly = [];

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

	$("#print_details").click(function(){
		$("#dvLoading").fadeIn(300);
		screenshot();
		$("#dvLoading").fadeOut(300);
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

$(document).ready(function(){
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


});