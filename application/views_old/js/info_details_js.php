	var id = l.href.substr(l.href.lastIndexOf('/')+1);
	var info_marker;
	//map options
    var latlng = new google.maps.LatLng(14.657134311228834, 121.05623297821063);
    var options = {
		zoom: 13,
        center: latlng,
		mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
	var map = new google.maps.Map(document.getElementById("map"), options);
    var markers = [];
	var infowindow = new google.maps.InfoWindow();


	var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	jax.open('POST',base_url+'/google/fetch_info/'+id);
	jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	jax.send('command=fetch')
	jax.onreadystatechange = function(){ 
		if(jax.readyState==4) {
			console.log(jax.responseText);
			var json_parse = JSON.parse(jax.responseText);
			if(jax.responseText != null){
				$("#info_title").val(eval('(' + jax.responseText + ')').title);
				$("#info_details").text(eval('(' + jax.responseText + ')').information);
				var info_img = {
					url: base_url+'/assets/img/info.png',
					size: new google.maps.Size(20, 32)
				};
				info_marker = new google.maps.Marker({
					position: new google.maps.LatLng(eval('(' + jax.responseText + ')').latitude,eval('(' + jax.responseText + ')').longtitude),
					icon: info_img
				});
				var contentString = '<div class="content" style="text-align:left;">'+
				'<div class="title">'+
				'<h5 style="color:#0174DF;">Title:</h5><h6>'+ json_parse.title + '</h6></div>'+
				'<div class="information">'+
				'<h5 style="color:#0174DF;">Information:</h5><h6>'+ json_parse.information + '</h6></div>'+
				'<div class="location">'+
				'<h5 style="color:#0174DF;">Location:</h5><h6>'+ json_parse.location + '</h6></div>'+
				'<div>'
				;
				var infowindow =  new google.maps.InfoWindow({
					content: contentString,
					map: map
				});
				infowindow.open(map, info_marker);
				info_marker.setMap(map);
			}
			else{
				smoke.signal('Unable to process this request.\nPlease try again later.', function(e){}, {
					duration: 9999
				});
			}
			google.maps.event.addListener(info_marker,"click", function(location) {
				infowindow.open(map, info_marker);
			});
		}
	}
	
	



	
