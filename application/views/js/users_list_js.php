// **************** Web Socket ***********************	 
conn.onmessage = function(e) {
	try {
		// Parse message
		var parsedJSON = JSON.parse(e.data);
		console.log(parsedJSON);
		
		switch (parsedJSON.action) {
			case 'CL':
				// Get id and ip of user
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				jax.open('POST',base_url+'/google/get_user_id');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=fetch')
				jax.onreadystatechange = function(){ 
					if(jax.readyState==4) {
						if(jax.responseText != null){
							try {
								// Parse message
								var data = JSON.parse(jax.responseText);
								// If same user id is accessed on different IP, log out the previous client
								// using this user id
								if (data.id == parsedJSON.id){
									window.location.href = "<?php echo base_url('another_user'); ?>";
								}
							} catch (err) {
								console.debug(err);
							}
						}
					}
				}
				break;
		}
	} catch (err) {
		console.debug(err);
	}
};


$('#example').dataTable({
	"oLanguage": {
      "sInfo": "",
      "sLengthMenu": "_MENU_",
      "sSearch":"",
      "oPaginate": {
    		"sNext": '>',
    		"sPrevious": '<'
    	}
    },
    "bProcessing": true,
    "deferRender": true,
	"sPaginationType": "full_numbers",
	"aaSorting": [[1, "desc"]],
	"bInfo" : false
});

$(".dataTables_length select").addClass("form-control");
$(".dataTables_filter input").addClass("form-control").attr("placeholder","Search");

$("#add-user").click(function(){
	$("#addUser").modal();
});

$(document).ready(function(){
	$.validator.addMethod('latitude', function (value) { 
    	return /^-?([0-8]?[0-9]|90)\.[0-9]{1,6}$/.test(value); 
	}, "Latitude should be -90.XXXXXX to 90.XXXXXX");

	$.validator.addMethod('longitude', function (value) { 
    	return /^-?((1?[0-7]?|[0-9]?)[0-9]|180)\.[0-9]{1,6}$/.test(value); 
	}, "Longitude should be -180.XXXXXX to 180.XXXXXX");
	
	$.validator.addMethod("accept", function(value, element, param) {
 		return value.match(new RegExp("." + param + "$"));
	},"Characters only");

	$.validator.setDefaults({ ignore: ":hidden:not(select)" });

	$("#adduserform").validate({
		rules:{
			email:{required: true, email: true},
			password:{required: true, minlength:6},
			fname:{required:true, minlength:3, accept:"[a-zA-Z]+"},
			lname:{required:true, minlength:3, accept:"[a-zA-Z]+"},
			cpass:{required:true, equalTo:"#similarpass"},
			//lat:{required:true, latitude: true},
			//lng:{required:true, longitude: true},
			question:{required:true},
			answer:{required:true},
			addlocation:{required:true},
			user_level:{required:true}

		},
		highlight: function(element) {
		  $(element).closest('.form-group').addClass('has-error');
		},
		unhighlight: function(element) {
		  $(element).closest('.form-group').removeClass('has-error');
		},
		submitHandler: function(form){			
			//var longitudereg  = /^-?((1?[0-7]?|[0-9]?)[0-9]|180)\.[0-9]{1,6}$/;
			//var latlngVal = /^-?([0-8]?[0-9]|90)\.[0-9]{1,6},-?((1?[0-7]?|[0-9]?)[0-9]|180)\.[0-9]{1,6}$/;
			//if(!latitudereg.test($("#latitude_user").val()) || !longitudereg.test($("#longitude_user").val())) { 
			//	$("#latlng_user").html("Latitude and Longitude are not valid. <br /> Latitude should be -90.XXXXXX to 90.XXXXXX and Longitude -180.XXXXXX to 180.XXXXXX");
			//}
			//else{
				$.ajax({
				    type: "POST",
				    url: base_url+'/login/add_user',
				    cache:false,
				    dataType: "json",
				    async: false,
				    data: $("#adduserform").serialize(),
				    beforeSend: function(){
				    	$("#dvLoading").fadeIn(1000);
					},
				    success: function(data){				   
				    	if(data.dupe_email == true){
				    		$("#dupe_email").text("Email address already exists!");
				    	}
				    	if(data.success == true){				   
				    		$("#addUser").modal("hide");
				    		smoke.signal("User added, redirecting to list...", function(e){
								setTimeout(function(){window.location=base_url+"/users-list"},1000);
							}, {
								duration: 1500
							});
				    	}
				    },
				    complete: function(){
				    	$("#dvLoading").fadeOut(1000);
					}
				});				
				return false;
			//}
		}
	});

	$('#addUser').on('hide.bs.modal', function (e) {
  		$("[name='email']").val("");
  		$("[name='fname']").val("");
  		$("[name='lname']").val("");
  		$("[name='password']").val("");
  		$("[name='cpass']").val("");
  		$("[name='location']").val("");
  		$("[name='lat']").val("");
  		$("[name='lng']").val("");
  		$("#latlng_user").html("");
  		$("#dupe_email").text("");

	});

	$("#userAddDepot").change(function(){
	  $("label[for='userAddDepot']").text("");
	  $("#yourlocation").val($("#userAddDepot option:selected").text());
      $("#latitude_user").val($("#userAddDepot option:selected").attr("data-latitude"));
      $("#longitude_user").val($("#userAddDepot option:selected").attr("data-longtitude"));
  });

	$('#addUser').on('hidden.bs.modal', function () {
		$("#adduserform").validate().resetForm();
   		//$('#addUser').removeData();
	});
});
