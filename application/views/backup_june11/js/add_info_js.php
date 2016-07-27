	//variable
	var data = {};
    var markers = [];	
	var geocoder = new google.maps.Geocoder();
	var infowindow = new google.maps.InfoWindow();
	var placename = [];
	var marker;
	var loc = [];
	
	//map options
    var latlng = new google.maps.LatLng(14.657134311228834, 121.05623297821063);
    var options = {
		zoom: 13,
        center: latlng,
		mapTypeControl: true,
		mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
		navigationControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        draggableCursor: "crosshair"
    };
	var map = new google.maps.Map(document.getElementById("map"), options);
	 
	
	//click marker
	var clickCounter = 0;
	
	google.maps.event.addListener(map,"click", function(location) {
		clickCounter += 1;
		if(clickCounter == 1 ){
			loc.push(location.latLng);
			geocoder.geocode( { 'latLng': location.latLng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				// GetLocationInfo(location.latLng, results[0].formatted_address);
				// locationsAdded++;
				
				marker = new google.maps.Marker({position: location.latLng, animation: google.maps.Animation.BOUNCE});
				$('#myModal').modal('show');
				$("#location_modal").val(results[0].formatted_address);
				marker.setMap(map);
				
			} else {
				smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){
				}, {
					duration: 9999
				});
			}
			});
		} else{
			smoke.signal('Maximum of one marker only', function(e){
			}, {
				duration: 9999
			});
		}
	});
	
	$('#myModal').on('hide.bs.modal', function (e) {
		clickCounter = 0;
		marker.setMap(null);
	});

	
	
	
	function save_info(){
		data =  {'location':$("#location_modal").val(),'lat':loc[0].k,'lng':loc[0].A,'title':encodeURIComponent($('#title_modal').val()),'info':encodeURIComponent($('#info_modal').val())}
		// data.latlng = {}
		// data.info = {}
		var str = JSON.stringify(data);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/index.php/google/add_info');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=save&infodata='+str)
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText == 'success'){
					$('#myModal').modal('hide');
					smoke.signal(jax.responseText+", Redirecting to the list..", function(e){
					setTimeout(function(){window.location="<?php echo base_url('index.php/google/info_list');?>"},500);
					}, {
						duration: 3000
					});
				}
				else{ 
					alert(jax.responseText);
				}
			}
		}
	}

	sethazard(1);
	function sethazard(stat){
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/get_info/');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					console.log(jax.responseText);
					var hazard_json = JSON.parse(jax.responseText); 
					var hazard_marker;
					var hazard_img = {
						url: base_url+'/assets/img/info.png',
						size: new google.maps.Size(20, 32)
					};
					for(var e = 0; e <= hazard_json.length-1; e++ ){
						hazard_marker = new google.maps.Marker({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
							icon: hazard_img
						});
						markers.push(hazard_marker);
						if(stat == 1){
							markers[e].setMap(map);
						}else{
							markers[e].setMap(null);
						}
						google.maps.event.addListener(hazard_marker,"click", (function(hazard_marker,e) {
							return function() {
								var contentString = '<div class="content" style="text-align:left;">'+
								'<div class="title">'+
								'<h5 style="color:#0174DF;">Title:</h5><h6>'+ hazard_json[e].title + '</h6></div>'+
								'<div class="information">'+
								'<h5 style="color:#0174DF;">Information:</h5><h6>'+ hazard_json[e].information + '</h6></div>'+
								'<div class="location">'+
								'<h5 style="color:#0174DF;">Location:</h5><h6>'+ hazard_json[e].location + '</h6></div>'+
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

