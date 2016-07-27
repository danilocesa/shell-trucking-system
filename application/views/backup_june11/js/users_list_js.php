oTable = $('#example').dataTable({
	"oLanguage": {
      "sInfo": "",
      "sLengthMenu": "_MENU_",
      "sSearch":"",
      "oPaginate": {
    		"sNext": '>',
    		"sPrevious": '<'
    	}
    },
	"sPaginationType": "full_numbers",
	"aaSorting": [[1, "desc"]],
	"bInfo" : false
});

$(document).on('click', '.delete_info', function() {	
	//$('.delete_info').click(function(){
		var id = $(this).attr("data-id");
		smoke.confirm("Are you sure?", function(e){
			if (e){
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				jax.open('POST',base_url+'/google/info_list');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=delete&id='+id);
				jax.onreadystatechange = function(){ 
					if(jax.readyState==4) {
						if(jax.responseText == 'success'){
							smoke.signal("Information deleted, redirecting to the list..", function(e){
								setTimeout(function(){window.location=base_url+"/google/info_list"},500);
							}, {
								duration: 1500
							});
						}
						else{ 
							smoke.signal(jax.responseText, function(e){
							}, {
								duration: 9999
							});
						}
					}
				}	
			}
		}, {
			ok: "Yes",
			cancel: "Cancel",
			classname: "custom-class",
			reverseButtons: true
		});
	
	//});

});

$(".dataTables_length select").addClass("form-control");
$(".dataTables_filter input").addClass("form-control").attr("placeholder","Search");




$("#add-user").click(function(){
	$("#addUser").modal();
});



$("#save_user").click(function(){
	$.ajax({
        type: "POST",
        url: base_url+'/login/add_user',
        cache:false,
        dataType: "json",
        data: $("#adduserform").serialize(),
        success: function(data){
    		if(!data.err == false){
        		return;
        	}
        	if(data.success == true){
        		$("#addUser").modal("hide");
        		smoke.signal("User added, redirecting to list...", function(e){
					setTimeout(function(){window.location=base_url+"/users-list"},1000);
				}, {
					duration: 1500
				});
        	}
        }
    });
});