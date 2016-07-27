// **************** Setting variables ***********************
	//hold data for ajax
	var data = {};
    //for markers
    var markers = [];
    //geocode services		
	var geocoder = new google.maps.Geocoder();
	//infowindow 
	var infowindow = new google.maps.InfoWindow();
	//marker
	var marker;
	//location
	var loc = [];
	//click counter 
	var clickCounter = 0;
	// upload var
	var _submit = document.getElementById('_submit'), 
	_file = document.getElementById('_file'), 
	_progress = document.getElementById('_progress');
	// Define variable to hold image id of selected icon
	var selectedIconImage = "";
	// New hazard latitude and longitude
	var newHazardLatLng = null;
	// Declare variable to hold location
	var hazard_location = null;
	// Array to hold all markers information
	var allHazards = [];
	// set value for default show hazard
	var showhazard = 1;
	// Site photo
	var _sitephoto = $("#_sitephoto");
	var newmarker;
	// Click mode to distinguish hazard polygonal boundary plotting and placement
	// 1 -> boundary plotting
	// 0 -> placement
	var clickMode = 0;
	// Array to hold hazard polygonal boundary coordinates
	var hazardPolyArr = [];
	var hazardPoly = null;
	// Array to hold all polygons
	var allPoly = [];
// **************** Initialize function ***********************
	site_upload();
	sethazard(1);
	google.maps.Polygon.prototype.my_getBounds=function(){
		var bounds = new google.maps.LatLngBounds()
		this.getPath().forEach(function(element,index){bounds.extend(element)})
		return bounds
	}
	google.maps.event.addListener(map,"zoom_changed",function(e){
		if(map.getZoom() <= 16){
			hideMarkers();
		} else{
			showMarkers();
		}
	});	
// **************** Maps Other options ***********************	 
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('address'));
	map.controls[google.maps.ControlPosition.CENTER].push(document.getElementById('center_map'));
// **************** Maps events ***********************	
	// Done plotting hazard bounds
	$("#ok-hazard-bounds").click(function() {
		$("#dvLoading").fadeIn(300);
		// Check if hazard marker is within polygon bounds
		if (!google.maps.geometry.poly.containsLocation(new google.maps.LatLng(marker.position.lat(), marker.position.lng()), hazardPoly))
		{
			smoke.signal('Cannot continue. Hazard marker is outside polygon bounds.', function(e){ }, {
				duration: 9999
			});
			$("#dvLoading").fadeOut(300);
			return;
		}
		// hide save hazard bounds
		$("#hazardBounds").fadeOut(500);
		geocoder.geocode( { 'latLng': newHazardLatLng}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			$('#myModal').modal('show');
			$("#location_modal").val(results[0].formatted_address.split(",",2).toString());
			marker.setMap(map);
			$('#myModal').on('hide.bs.modal', function (e) {
				clearHazardform();
				loc = [];
				clickCounter = 0;
				clickMode = 0;
				marker.setMap(null);
				hazardPoly.setMap(null);
				hazardPolyArr = [];
				$("#ok-hazard-bounds").attr("disabled","disabled");
				$("#thumb_img").hide();
				$("#sitethumb").hide();
			});	
		} else {	
			smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){}, {
				duration: 9999
			});
		}
		});
		$("#dvLoading").fadeOut(300);
	});
	
	
	// Cancel plotting
	$("#cancel-hazard-bounds").click(function(e) {
		$("#dvLoading").fadeIn(300);
		// Remove polygon and marker
		if (hazardPoly != null)
		{
			hazardPoly.setMap(null);
		}
		if (marker != null)
		{
			marker.setMap(null);
		}
		hazardPolyArr = [];
		clickMode = 0;
		clickCounter = 0;
		// hide save hazard bounds
		$("#hazardBounds").fadeOut(500);
		$("#dvLoading").fadeOut(300);
		$("#ok-hazard-bounds").attr("disabled","disabled");
	});
	
	google.maps.event.addListener(map,"click", function(location) {
		clickCounter += 1;
		if (clickMode == 0) {
			// display save hazard bounds
			$("#hazardBounds").fadeIn(500);
			$("#dvLoading").fadeOut(300);
		}
		else {
			hazardPolyArr.push(location.latLng);
			// Remove polygon
			if (hazardPoly != null)
			{
				hazardPoly.setMap(null);
			}
			hazardPoly = new google.maps.Polygon({
				paths: hazardPolyArr,
				strokeColor: '#FF0000',
				strokeOpacity: 0.8,
				strokeWeight: 3,
				editable: true,
				draggable: true,
				fillColor: '#FF0000',
				fillOpacity: 0.35
			});
			hazardPoly.setMap(map);
			// If points is more than three, enable save button
			if (hazardPoly.getPath().getLength() > 2)
			{
				$("#ok-hazard-bounds").removeAttr("disabled");
			}
			return;
		}
		if(clickCounter == 1 ){
			newHazardLatLng = new google.maps.LatLng(location.latLng.lat(), location.latLng.lng());
			loc.push(location.latLng);
			geocoder.geocode( { 'latLng': location.latLng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					marker = new google.maps.Marker({position: location.latLng, animation: google.maps.Animation.BOUNCE});
					$("#site_region").val(results[0].address_components[results[0].address_components.length-2].short_name);
					$("#location_modal").val(results[0].formatted_address.split(",",2).toString());
					marker.setMap(map);
					clickMode = 1;
					map.setOptions({ draggableCursor: 'crosshair' });
					$('#myModal').on('hide.bs.modal', function (e) {
						clearHazardform();
						loc = [];
						clickCounter = 0;
						marker.setMap(null);
						$("#thumb_img").hide();
						$("#sitethumb").hide();
					});	
				} else {	
					smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){}, {
						duration: 9999
					});
				}
			});
			return false;
		} else{
			smoke.signal('Maximum of one marker only', function(e){}, {
				duration: 9999
			});
		}
	});
	
// **************** Functions ***********************
	//**** Search Box
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
	
	//**** Save information
	function save_info(){
		var lats = [];
		var lngs = [];
		var paths = hazardPoly.getPath();
		for (var i=0; i < paths.getLength(); i++) {
			lats.push(paths.getAt(i).lat());
			lngs.push(paths.getAt(i).lng());
		}
		data =  {
		'location':$("#location_modal").val(),
		'lat':lats,
		'lng':lngs,
		'latitude': hazardPoly.my_getBounds().getCenter().lat(),
		'longitude': hazardPoly.my_getBounds().getCenter().lng(),
		'title':encodeURIComponent($('#title_modal').val()),
		'info':encodeURIComponent($('#info_modal').val()),
		'filename':$('#file_namename').val(),
		'status':$("#haz_status").val(),
		'start_date':$("#start_date").val(),
		'end_date':$("#end_date").val(),
		'category':$("#marker-category").val(),
		'site_photo':$("#site_photoname").val(),
		'hazard_icon':$("#hazard_icon").val(),
		'site_category':$("#site_category").val(),
		'site_region': $("#site_region").val(),
		'hazard_control':encodeURIComponent($("#control_modal").val())}
		var str = JSON.stringify(data);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/add_hazard');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=save&infodata='+str)
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText == 'success'){
					//newHazardLatLng = new google.maps.LatLng(latsave, lngsave);
					newmarker = new MarkerWithLabel({
						position: newHazardLatLng,
						map: map,
						labelContent: $('#title_modal').val(),
						title: $('#title_modal').val(),
						labelAnchor: new google.maps.Point(-30, 33),
						labelClass: "hazard-label",
						icon: base_url+"/assets/img/icons/" + selectedIconImage
					});
					newmarker.setMap(map);
					clearHazardform();
					loc = [];
					$('#myModal').modal('hide');
				} else if(jax.responseText == 'false_region'){
					$("#myModal").modal("hide");
					smoke.signal("This location is not covered by the assigned Region on your Account", function(e){						
					}, {
						duration: 8500
					});
				}
				else{

					alert("Database Error");
					console.log(jax.responseText);
				}
			}
		}
		$("#dvLoading").fadeOut(300);
		return false;
	}
	//***** Upload image
	function upload_image(cate){
		var buf = makeid();
		var timestamp = new Date().getTime();
		var uploader_path;
		if(cate == 0){
			uploader_path = base_url+'/google/upload_image/uploads';
		} else if(cate == 1){
			uploader_path = base_url+'/google/upload_image/sites';
		} else{
			uploader_path = base_url+'/google/upload_image/depots';
		}
		$(function() {
			$('#_file').uploadify({
				'formData'     : {
					'timestamp' : timestamp,
					'token'     : buf
				},
				'swf'      : base_url+'/assets/js/uploadify.swf',
				'uploader' : uploader_path,
				 'onUploadSuccess' : function(file, data, response) {
				 	var na_json = JSON.parse(data);
				 	if(na_json.resp == 'success'){
				 		$("#thumb_img").show();
				 		if(cate == 0){
				 			$("#thumb_img").attr('src',base_url+"/uploads/"+file.name);
				 		} else if(cate == 1){
				 			$("#thumb_img").attr('src',base_url+"/uploads/sites/"+file.name);
				 		} else{
				 			$("#thumb_img").attr('src',base_url+"/uploads/depots/"+file.name);
				 		}
				 		
				 		$("#file_namename").val(na_json.filename);
				 	} else {
				 	//	$('#myModal').modal('hide');
				 		smoke.signal("Invalid file type", function(e){ }, {
							duration: 1500
						});
				 	}
			    },
			    'fileSizeLimit' :'10MB',
			    'multi': false
			});
		});
	}

	//Create random id
	function makeid()
	{
	    var text = "";
	    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	    for( var i=0; i < 25; i++ )
	        text += possible.charAt(Math.floor(Math.random() * possible.length));

	    return text;
	}

	function site_upload(){
		var buf = makeid();
		var timestamp = new Date().getTime();
		$(function() {
			$('#_sitephoto').uploadify({
				'formData'     : {
					'timestamp' : timestamp,
					'token'     : buf
				},
				'swf'      : base_url+'/assets/js/uploadify.swf',
				'uploader' : base_url+'/google/upload_image/site_layout',
				 'onUploadSuccess' : function(file, data, response) {
				 	var na_json = JSON.parse(data);
				 	if(na_json.resp == 'success'){
				 		$("#sitethumb").show();
				 		$("#sitethumb").attr('src',base_url+"/uploads/sites/sites_layout/"+file.name);
				 		$("#site_photoname").val(na_json.filename);
				 	} else {
				 		$('#myModal').modal('hide');
				 		smoke.signal("Invalid file type", function(e){ }, {
							duration: 1500
						});
				 	}
			    },
			    'fileSizeLimit' :'10MB',
			    'multi': false
			});
		});
	}
	//***** Set hazard information
	function sethazard(stat){
		clearmarkerwithlabel();
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/fetch_hazard/');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=fetch')
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText != null){
					var hazard_json = JSON.parse(jax.responseText); 
					var hazard_marker = [];
					var hazard_poly = [];
					for(var e = 0; e <= hazard_json.length-1; e++ ){
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
						allPoly.push(hazard_poly);
						hazard_marker = new google.maps.Marker({
							position: new google.maps.LatLng(hazard_json[e].center_latitude, hazard_json[e].center_longitude),
							icon: {
								url: base_url+'/assets/img/icons/'+hazard_json[e].hazard_icon
							},
							id: hazard_json[e].hazard_id
						});
						markers.push(hazard_marker);
						newmarker = new MarkerWithLabel({
							position: new google.maps.LatLng(hazard_json[e].center_latitude, hazard_json[e].center_longitude),
							labelContent: hazard_json[e].title,
							map: map,
							title: hazard_json[e].title,
							labelContent: hazard_json[e].title,
							labelAnchor: new google.maps.Point(-30, 33),
							labelClass: "hazard-label", // the CSS class for the label
							icon: base_url+'/assets/img/icons/' + hazard_json[e].hazard_icon
						});
						allHazards.push(newmarker);
		
						if(stat == 1){
							markers[e].setMap(map);
							newmarker.setMap(map);
							allPoly[e].setMap(map);
						}else{
							markers[e].setMap(null);
							newmarker.setMap(null);
							allPoly[e].setMap(null);
						}
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
								'<div class="image">'+
								'<h5 style="color:#0174DF;">Image:</h5>';
								var image_path;
								var temp_hazard;
								if(hazard_json[e].hazard_id.substr(hazard_json[e].hazard_id.length - 1) == "h"){
									image_path = "<img id='preview_img' src=' " + base_url+'/uploads/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>';
								} else if(hazard_json[e].hazard_id.substr(hazard_json[e].hazard_id.length - 1) == "s"){
									image_path = "<img id='preview_img' src=' " + base_url+'/uploads/sites/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>';
								} else{
									image_path = "<img id='preview_img' src=' " + base_url+'/uploads/depots/'+hazard_json[e].hazard_image + " ' style='width:170px;height:160px;'/>" +'</div>';
								}

								if(hazard_json[e].status == 0){
									temp_hazard = "<div class='button' style='margin-top:10px;'>"+
									'<button type="button" class="btn btn-default btn-xs" id="close_hazard" data-id="'+hazard_json[e].hazard_id+'"style="padding:10px;width:100px;font-size:14px;font-weight:800;">De-Active</button>'+'</div>'+
									'<div>';
								} else { temp_hazard = "" ;}
								var info_deta = contentString.concat(image_path,temp_hazard);
								var infowindow =  new google.maps.InfoWindow({
									content: info_deta,
									map: map
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
	//**** Set all to map
	function setAllMap(map) {
		for (var i = 0; i < markers.length; i++) {
			markers[i].setMap(map);
			allHazards[i].setMap(map); 
		}
		//$("#dvLoading").fadeOut(300);
	}
	//**** Hide markers
	function hideMarkers() {
		//$("#dvLoading").fadeIn(300);
		setAllMap(null);
	}
	//*** Show markers
	function showMarkers() {
		//$("#dvLoading").fadeIn(300);
		setAllMap(map);
	}

	//**** Clear Form
	function clearHazardform(){
		$("#location_modal").val("");
		$("#haz_status").val("");
		$("#_file").val("");
		$("#file_namename").val("");
		$("#title_modal").val("");
		$("#info_modal").val("");
		$("#control_modal").val("");
	}
	//***** Clear marker with label plugin
	function clearmarkerwithlabel(){
		for (var i = 0; i < markers.length; i++) {
			markers[i].setMap(null);
			allHazards[i].setMap(null); 
			newmarker.setMap(null);
			allPoly[i].setMap(null);
		}
		
		markers = [];
		allHazards = [];
		allPoly = [];

	}

	function startTour(){
		$.tourTip.start();
		$("#show_hazard").tourTip({
			title: "Show Hazard",
			description: 'This button shows the Hazard(s).',
			next: true
		});
		$("#hide_hazard").tourTip({
			title: "Hide Hazard",
			description: 'This button hides the Hazard(s).',
			next: true
		});
		
		$("#center_map").tourTip({
			title: "Add Route",
			description: 'In order to create new Hazard, point the location of the Hazard by simply clicking on the map. Please specify boundaries for the Hazard by simply plotting a polygon shape around the marker then click "OK" button. The Information page will display, fill up the necessary fields before saving the data.',
			close: true,
			position: 'top'
		});
	}

//****************** Others *******************************


$("#thumb_img").hide();
$("#sitethumb").hide();
$("#perm-status").attr("checked",true);
// Initializing datetime pickers
$('.datetimepicker').datetimepicker({minDate:new Date()});
$("#icon-container").hide();
$("#site-upload").hide();
$(".haz-img").hide();



$(document).ready(function(){
	$(document).on("change", "#haz_status", function(){
		$("#dvLoading").fadeIn(300);
		if ($(this).children(":selected").attr("id") == "perm-status"){
			// Disable datetimepickers and clear values
			$(".datetimepicker").attr("disabled","disabled").val("").removeClass("picker-active");
		} else{
			// Enable datetimepickers
			$(".datetimepicker").removeAttr("disabled").addClass("picker-active");
		}
		$("#dvLoading").fadeOut(300);
	});


	$(document).on("change", "select#marker-category", function(){
		//alert("change");
		// Remove '- select -' option
		$("#dvLoading").fadeIn(300);
		$("select#marker-category option[value='']").remove();
		$("#icon-container").show();
		if ($(this).val() != '')
		{
			// Hide all icon list
			$("div.icon-list").hide();
			
			// Display icons under selected category
			$("div#" + $(this).val()).show();
		}
		$("#dvLoading").fadeOut(300);
	});


	$(document).on("click", "div.icon", function(){
		$("#dvLoading").fadeIn(300);
		// Revert selected icon to normal icon
		//$("div.icon-selected").removeClass("icon-selected").addClass("icon");
		$("div.icon-selected").removeClass("icon-selected");
		
		// Assign selected class to selected icon
		$(this).addClass("icon-selected");
		
		// Store id for selected icon
		$("#hazard_icon").val($(this).attr("id"));
		
		selectedIconImage = $(this).attr("id");
		if(selectedIconImage ==  "shell.png"){
			$("#site-upload").show();
			$("#site_category").val("1");
			$(".haz-controls").hide();
		} else if(selectedIconImage ==  "depot.png"){
			$("#site_category").val("2");
			$(".haz-controls").hide();
			$(".haz-img").show();
			$("#site-upload").hide();
		} else {
			$(".haz-controls").show();
			$("#site_category").val("0");
			$(".haz-img").show();
			$("#site-upload").hide();
		}


		$(".haz-img").show();
		var cate = $("#site_category").val();
		upload_image(cate);
		$("#dvLoading").fadeOut(300);
	});

	$(document).on("click","#close_hazard",function(){
		$("#dvLoading").fadeIn(300);
		$.ajax({
			url:base_url+"/google/close_hazard/"+$(this).attr("data-id"), 
			type: "post",
			cache: false,
			dataType: "json",
			success:function(result){
				location.reload();
			},
			error:function(){
				alert("error");
			}
		});	
		$("#dvLoading").fadeOut(300);
		
	});		


	$("#add_hazard_form").validate({
		rules:{
			status:{required: true},
			marker_category:{required: true},
			hazard_title:{required: true},
			start_date:{required:true},
			end_date:{required:true}
		},
		highlight: function(element) {
       		$(element).closest('.form-group').addClass('has-error');
    	},
    	unhighlight: function(element) {
        	$(element).closest('.form-group').removeClass('has-error');
    	},
    	submitHandler: function(form) { $("#dvLoading").fadeIn(300); save_info();   return false; }
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



