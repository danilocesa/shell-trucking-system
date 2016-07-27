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
// **************** Initialize function ***********************
	upload_image();
	site_upload();
	sethazard(1);
// **************** Maps Other options ***********************	 
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('menu_add_route'));
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('address'));
// **************** Maps events ***********************	
	google.maps.event.addListener(map,"click", function(location) {
		clickCounter += 1;
		if(clickCounter == 1 ){
			newHazardLatLng = new google.maps.LatLng(location.latLng.lat(), location.latLng.lng());
			loc.push(location.latLng);
			geocoder.geocode( { 'latLng': location.latLng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				marker = new google.maps.Marker({position: location.latLng, animation: google.maps.Animation.BOUNCE});
				$('#myModal').modal('show');
				$("#location_modal").val(results[0].formatted_address.split(",",2).toString());
				marker.setMap(map);
				$('#myModal').on('hide.bs.modal', function (e) {
					clearHazardform();
					loc = [];
					clickCounter = 0;
					marker.setMap(null);
				});	
			} else {	
				smoke.signal('Geocode was not successful for the following reason: ' + status, function(e){}, {
					duration: 9999
				});
			}
			});
		} else{
			smoke.signal('Maximum of one marker only', function(e){}, {
				duration: 9999
			});
		}
	});

	//google.maps.event.addListener(map,"dragend",function(e){
	//	sethazard(1);
	//});	
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
		var latsave = loc[0].k;
		var lngsave = loc[0].A;
		data =  {
		'location':$("#location_modal").val(),'lat':loc[0].k,'lng':loc[0].A,
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
		'hazard_control':encodeURIComponent($("#control_modal").val())}
		var str = JSON.stringify(data);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/add_hazard');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=save&infodata='+str)
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText == 'success'){
					newHazardLatLng = new google.maps.LatLng(latsave, lngsave);
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
				}
				else{

					alert("Database Error");
					console.log(jax.responseText);
				}
			}
		}
	}
	//***** Upload image
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
				'uploader' : base_url+'/google/upload_image/uploads',
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
				'uploader' : base_url+'/google/upload_image/none',
				 'onUploadSuccess' : function(file, data, response) {
				 	var na_json = JSON.parse(data);
				 	if(na_json.resp == 'success'){
				 		$("#sitethumb").show();
				 		$("#sitethumb").attr('src',base_url+"/uploads/sites/"+file.name);
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
					var hazard_marker;
					for(var e = 0; e <= hazard_json.length-1; e++ ){
						hazard_marker = new google.maps.Marker({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
							icon: {
								url: base_url+'/assets/img/icons/'+hazard_json[e].hazard_icon
							},
							id: hazard_json[e].hazard_id
						});
						markers.push(hazard_marker);
						newmarker = new MarkerWithLabel({
							position: new google.maps.LatLng(hazard_json[e].latitude,hazard_json[e].longtitude),
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
						}else{
							markers[e].setMap(null);
							newmarker.setMap(null);
						}
						google.maps.event.addListener(newmarker,"click", (function(newmarker,e) {
							return function() {
								if(hazard_json[e].status == 0){
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
									"<div class='button' style='margin-top:10px;'>"+
									'<button type="button" class="btn btn-default btn-xs" id="close_hazard" data-id="'+hazard_json[e].hazard_id+'"style="padding:10px;width:100px;font-size:14px;font-weight:800;">De-Active</button>'+'</div>'+
									'<div>'
									;
								} else{
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
								}
								
								var infowindow =  new google.maps.InfoWindow({
									content: contentString,
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
	}
	//**** Hide markers
	function hideMarkers() {
		setAllMap(null);
	}
	//*** Show markers
	function showMarkers() {
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
		}
		markers = [];
		allHazards = [];

	}

//****************** Others *******************************


$("#thumb_img").hide();
$("#sitethumb").hide();
$("#perm-status").attr("checked",true);
// Initializing datetime pickers
$('.datetimepicker').datetimepicker();
$("#icon-container").hide();
$("#site-upload").hide();


$(document).ready(function(){
	$(document).on("change", "#haz_status", function(){
		if ($(this).children(":selected").attr("id") == "perm-status"){
			// Disable datetimepickers and clear values
			$(".datetimepicker").attr("disabled","disabled").val("").removeClass("picker-active");
		} else{
			// Enable datetimepickers
			$(".datetimepicker").removeAttr("disabled").addClass("picker-active");
		}
	});


	$(document).on("change", "select#marker-category", function(){
		//alert("change");
		// Remove '- select -' option
		$("select#marker-category option[value='']").remove();
		$("#icon-container").show();
		if ($(this).val() != '')
		{
			// Hide all icon list
			$("div.icon-list").hide();
			
			// Display icons under selected category
			$("div#" + $(this).val()).show();
		}
	});


	$(document).on("click", "div.icon", function(){
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
			$(".haz-img").hide();
			$("#site_category").val("1");
			$(".haz-controls").hide();
		} else{
			$(".haz-controls").show();
			$("#site_category").val("0");
			$(".haz-img").show();
			$("#site-upload").hide();
		}
	});

	$(document).on("click","#close_hazard",function(){
		$.ajax({
			url:base_url+"/google/close_hazard/"+$(this).attr("data-id"), 
			type: "post",
			cache: false,
			dataType: "json",
			success:function(result){
				//console.log(result);
				location.reload();
			},
			error:function(){
				alert("error");
			}
		});	
		
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
    	submitHandler: function() { save_info(); }
	});
	setInterval(function(){ sethazard(1); }, 30000);
});	



