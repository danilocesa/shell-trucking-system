	var id = l.href.substr(l.href.lastIndexOf('/')+1);
	//variable
	var data = {};
    var markers = [];	
	var geocoder = new google.maps.Geocoder();
	var infowindow = new google.maps.InfoWindow();
	var placename = [];
	var marker;
	var loc = [];

	
	var _submit = document.getElementById('_submit'), 
	_file = document.getElementById('_file'), 
	_progress = document.getElementById('_progress');

	google.maps.Polygon.prototype.my_getBounds=function(){
		var bounds = new google.maps.LatLngBounds()
		this.getPath().forEach(function(element,index){bounds.extend(element)})
		return bounds
	}

	function save_info(){
		
		data =  {
			'title':encodeURIComponent($('#title_modal').val()),
			'info':encodeURIComponent($('#info_modal').val()),
			'filename':$('#file_namename').val(),
			'status':$("#haz_status").val(),
			'start_date':$("#start_date").val(),
			'end_date':$("#end_date").val(),
			'hazard_control':encodeURIComponent($("#control_modal").val())
		}
		var str = JSON.stringify(data);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/edit_hazard/'+id);
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=save&infodata='+str)
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText == 'success'){
					$('#myModal').modal('hide');
					smoke.signal(jax.responseText+", Redirecting to the list..", function(e){
					setTimeout(function(){window.location=base_url+"/google/hazard_list"},500);
					}, {
						duration: 500
					});
				}
				else{ 
					alert(jax.responseText);
				}
			}
		}
	}
	$("#thumb_img").hide();

	$('#modalclose').click(function (e) {
		window.location = base_url+"/google/hazard_list";
	});	
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
				 		$("#cur_img").hide();
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
					var hazard_poly;
					var hazard_img = {
						url: base_url+'/assets/img/warning.png',
						size: new google.maps.Size(20, 32)
					};
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
							strokeOpacity: 0.5,
							strokeWeight: 1,
							fillColor: '#FF0000',
							fillOpacity: 0.0
						});
						hazard_marker = new google.maps.Marker({
							position: hazard_poly.my_getBounds().getCenter(),
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
								//console.log(base_url+"/uploads/"+hazard_json[e].hazard_image);
								//$("#preview_img").attr("src,base_url+"/uploads/"+hazard_json[e].hazard_image);
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

$('.datetimepicker').datetimepicker();
$("#icon-container").hide();
$(document).ready(function(){
	$('#myModal').modal('show');
	$(document).on("change", "#haz_status", function(){
		if ($(this).children(":selected").attr("id") == "perm-status"){
			// Disable datetimepickers and clear values
			$(".datetimepicker").attr("disabled","disabled").val("").removeClass("picker-active");
		} else{
			// Enable datetimepickers
			$(".datetimepicker").val("");
			$(".datetimepicker").removeAttr("disabled").addClass("picker-active");
		}
	});


	$("#add_hazard_form").validate({
		rules:{
			status:{required: true},
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


});