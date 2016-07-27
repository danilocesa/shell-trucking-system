	var id = l.href.substr(l.href.lastIndexOf('/')+1);
	var haz_stat = [];
	var data = {};
	var path = new google.maps.MVCArray(), shiftPressed = false, poly;
	var test_path = [];
	var store_mark = [];
	var points = [];
    var markers = [];	
	var geocoder = new google.maps.Geocoder();
	var infowindow = new google.maps.InfoWindow();
	var placename = [];
	var locationsAdded = 1;
	var clickCounter = 0;
	var start_loca;
	var end_loca;

	//map options
    var latlng = new google.maps.LatLng(14.657134311228834, 121.05623297821063);
    var options = {
		zoom: 13,
		center: new google.maps.LatLng(0, 0),
		mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
	var map = new google.maps.Map(document.getElementById("map"), options);


	//Search Box
	map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('address'));
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



	var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	jax.open('POST',base_url+'/google/fetch_waypoints/'+id);
	jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	jax.send('command=fetch')
	jax.onreadystatechange = function(){ 
		if(jax.readyState==4) {
			if(jax.responseText != null){
				$("#route_dest").html(eval('(' + jax.responseText + ')').start+" to "+eval('(' + jax.responseText + ')').end);
				start_loca = eval('(' + jax.responseText + ')').start;
				end_loca = eval('(' + jax.responseText + ')').end;
				$("#route_title").val(eval('(' + jax.responseText + ')').title);
				$("#route_info").text(eval('(' + jax.responseText + ')').info);
				setroute( eval('(' + jax.responseText + ')') ); 
				sethazard(1);
			}
			else{
				smoke.signal('Unable to process this request.\nPlease try again later.', function(e){}, {
					duration: 9999
				});
			}
		}
	}
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
		path.push(new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
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
		path.push(new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		var infowindow =  new google.maps.InfoWindow({
			content: route_json.location['last_loc'],
			map: map
		});
		infowindow.open(map, end_marker);
		end_marker.setMap(map);
		
		
		//poly line
		
		//start
	
		wp.push( new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
		//midpoints
		for(var i=1;i<route_json.midpoints.length;i++){
			path.push(new google.maps.LatLng(route_json.midpoints[i][0],route_json.midpoints[i][1]));
			wp.push( new google.maps.LatLng(route_json.midpoints[i][0],route_json.midpoints[i][1]));
			
		}
		
		//end
		wp.push( new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		
		poly = new google.maps.Polyline({ 
			path: wp,
			editable: true
			
		});

		poly.setMap(map);

		var bounds = new google.maps.LatLngBounds();
		bounds.extend( new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
		bounds.extend( new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		map.fitBounds(bounds);
	}
	



	function sethazard(){
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				var hazard_json = JSON.parse(jax.responseText);
				if(jax.responseText != null){
					var hazard_marker;
					var hazard_img = {
						url: base_url+'/assets/img/warning.png',
						size: new google.maps.Size(20, 32)
					};
					for(var e = 0; e <= hazard_json.length-1; e++ ){
						//console.log(hazard_json[e].hazard_id);
						hazard_marker = new google.maps.Marker({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
							icon: hazard_img
						});
						hazard_marker.setMap(map);
						haz_stat.push(hazard_marker);
						google.maps.event.addListener(hazard_marker,"click", (function(hazard_marker,e) {
							return function() {
								var contentString = '<div class="content" style="text-align:left;">'+
								'<div class="title">'+
								'<h5 style="color:#0174DF;">Title:</h5><h6>'+ hazard_json[e].title + '</h6></div>'+
								'<div class="information">'+
								'<h5 style="color:#0174DF;">Information:</h5><h6>'+ hazard_json[e].information + '</h6></div>'+
								'<div class="location">'+
								'<h5 style="color:#0174DF;">Location:</h5><h6>'+ hazard_json[e].location + '</h6></div>'+
								'<div class="image">'+
								'<h5 style="color:#0174DF;">Image:</h5>'+
								"<img id='preview_img' src=' " + base_url+'/uploads/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>'+
								'<div>'
								;
								var infowindow =  new google.maps.InfoWindow({
									content: contentString,
									map: map
								});
								infowindow.open(map, hazard_marker);
							}
						})(hazard_marker, e));
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

	function showhazard(){
		setAllMap(null);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_near/'+id);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('trigger=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				var hazard_json = JSON.parse(jax.responseText);
				if(jax.responseText != null){
					var hazard_marker;
					var hazard_img = {
						url: base_url+'/assets/img/warning.png',
						size: new google.maps.Size(20, 32)
					};
					for(var e = 0; e <= hazard_json.length-1; e++ ){
						hazard_marker = new google.maps.Marker({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
							icon: hazard_img
						});
						hazard_marker.setMap(map);
						haz_stat.push(hazard_marker);
						google.maps.event.addListener(hazard_marker,"click", (function(hazard_marker,e) {
							return function() {
								var contentString = '<div class="content" style="text-align:left;">'+
								'<div class="title">'+
								'<h5 style="color:#0174DF;">Title:</h5><h6>'+ hazard_json[e].title + '</h6></div>'+
								'<div class="information">'+
								'<h5 style="color:#0174DF;">Information:</h5><h6>'+ hazard_json[e].information + '</h6></div>'+
								'<div class="location">'+
								'<h5 style="color:#0174DF;">Location:</h5><h6>'+ hazard_json[e].location + '</h6></div>'+
								'<div class="image">'+
								'<h5 style="color:#0174DF;">Image:</h5>'+
								"<img id='preview_img' src=' " + base_url+'/uploads/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>'+
								'<div>'
								;
								var infowindow =  new google.maps.InfoWindow({
									content: contentString,
									map: map
								});
								infowindow.open(map, hazard_marker);
							}
						})(hazard_marker, e));
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
  }
}

function hideMarkers() {
  setAllMap(null);
}

function showMarkers() {
  setAllMap(map);
}
	


function save_directions(){
		console.log(path);
		if(! $("#route_title").val() || ! $("#route_info").val() || path.length === 0){
			 smoke.signal("Please fill up", function(e){}, {
				duration: 9999
			});
		}else{
			var w=[],mp; 
			var endArray = path.getArray().length-1;
			data.start = {'lat': path.getArray()[0].lat(), 'lng':path.getArray()[0].lng()}
			data.end = {'lat': path.getArray()[endArray].lat(), 'lng':path.getArray()[endArray].lng()}
			for(var d=1; d<path.getArray().length-1; d++){
				w[d] = [path.getArray()[d].lat(),path.getArray()[d].lng()];
			}
			data.midpoints = w;
			data.info = {'title':$("#route_title").val(), 'information':$("#route_info").val() }
			data.location = {'start_loc': start_loca, 'last_loc': end_loca}
			var str = JSON.stringify(data);
			var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
			jax.open('POST',base_url+'/google/edit_waypoints/'+id);
			jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			jax.send('trigger=save&mapdata='+str)
			jax.onreadystatechange = function(){ 
				if(jax.readyState==4) {
					if(jax.responseText == 'success'){
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
	
	}