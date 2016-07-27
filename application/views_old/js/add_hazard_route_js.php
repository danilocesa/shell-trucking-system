	var id = l.href.substr(l.href.lastIndexOf('/')+1);
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
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        draggableCursor: "crosshair"
    };
	var map = new google.maps.Map(document.getElementById("map"), options);
	 
	var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	jax.open('POST',base_url+'/google/add_hazard/'+id);
	jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	jax.send('command=fetch')
	jax.onreadystatechange = function(){ 
		if(jax.readyState==4) {
			//console.log(jax.responseText);
			if(jax.responseText != null){
				setroute( eval('(' + jax.responseText + ')') ); 
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
		var path = new google.maps.MVCArray(), poly;
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
			path: wp,
			strokeWeight: 5
			
		});
		
		poly.setMap(map);

		var bounds = new google.maps.LatLngBounds();
		bounds.extend( new google.maps.LatLng(route_json.start.lat,route_json.start.lng));
		bounds.extend( new google.maps.LatLng(route_json.end.lat,route_json.end.lng));
		map.fitBounds(bounds); 
		
	}

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

	google.maps.event.addListener(map,"click", function(location) {
		loc.push(location.latLng);
		geocoder.geocode( { 'latLng': location.latLng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
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
	});
	
	$('#modalclose').click(function (e) {
		marker.setMap(null);
	});	

	var _submit = document.getElementById('_submit'), 
	_file = document.getElementById('_file'), 
	_progress = document.getElementById('_progress');

	function save_redirect(){
		smoke.confirm("Are you sure?", function(e){
			if(e){
					smoke.signal("Redirecting to the list..", function(e){
						setTimeout(function(){window.location=base_url+"/google/routes"},500);
					}, {
						duration: 500
					});
				}else{}
			}, {
				ok: "Yes",
				cancel: "No",
				classname: "custom-class",
				reverseButtons: true
		});		
	}


	function save_info(){
		var title = $('#title_modal').val();
		var info = $('#info_modal').val();
		if($('#title_modal').val() == "" || $('#info_modal').val() == "" || $('#file_namename').val() =="" ){
			alert("Please complete the form");
			return false;
		}
		data =  {'location':$("#location_modal").val(),'lat':loc[0].k,'lng':loc[0].A,'title':escape(title),'info':escape(info),'filename':$('#file_namename').val()}
		var str = JSON.stringify(data);
		//console.log(str);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/add_hazard/'+id);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('trigger=save&infodata='+str)
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				console.log(jax.responseText);
				if(jax.responseText == 'success'){
					$('#myModal').modal('hide');
					$("#modalclose").show();
					smoke.confirm("Done adding hazard?", function(e){
						if(e){
							smoke.signal("Redirecting to the list..", function(e){
								setTimeout(function(){window.location=base_url+"/google/routes"},500);
							}, {
								duration: 500
							});
						}else{ 
							window.location.reload(true);
						}
					}, {
						ok: "Yes",
						cancel: "No",
						classname: "custom-class",
						reverseButtons: true
					});
					
				}
				else{ 
					alert("error");	
				}
			}
		}
	}
	$("#thumb_img").hide();
	upload_image();
	function upload_image(){
		var buf = makeid();
		var timestamp = new Date().getTime();
		$(function() {
			$('#_file').uploadify({
				'formData'     : {
					'timestamp' : timestamp,
					'token'     : buf
				},
				'swf'      : base_url+'/assets/js/uploadify.swf',
				'uploader' : base_url+'/google/upload_image',
				'onUploadSuccess' : function(file, data, response) {
				 	var na_json = JSON.parse(data);
				 	if(na_json.resp == 'success'){
				 		$("#thumb_img").show();
				 		$("#thumb_img").attr('src',base_url+"/uploads/"+file.name);
				 		$("#file_namename").val(na_json.filename);
				 	} else {
				 		$('#myModal').modal('hide');
				 		smoke.signal("Invalid file type", function(e){ }, {
							duration: 1500
						});
				 	}
				 	$("#_submit").prop("disabled");
				 	$("#modalclose").hide();
				 	$(".close").hide();
			    },
			    'onUploadComplete' : function(file) {
            		$("#_submit").removeAttr("disabled");
      			},
			    'fileSizeLimit' :'10MB',
			    'multi': false
			});
		});
	}

	function makeid()
	{
	    var text = "";
	    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	    for( var i=0; i < 25; i++ )
	        text += possible.charAt(Math.floor(Math.random() * possible.length));

	    return text;
	}

	sethazard(1);
	function sethazard(stat){
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					var hazard_json = JSON.parse(jax.responseText); 
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
  for (var i = 0; i < markers.length; i++) {
    markers[i].setMap(map);
  }
}

function clearMarkers() {
  setAllMap(null);
}

function showMarkers() {
  setAllMap(map);
}