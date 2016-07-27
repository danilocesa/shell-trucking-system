oTable = $('#example').dataTable({
	"oLanguage": {
      "sInfo": "",
      "sLengthMenu": "_MENU_",
      "sSearch":"",
      "oPaginate": {
    		"sNext": '>',
    		"sPrevious": '<'
    	},
    	
    },
    "bInfo" : false,
	"sPaginationType": "full_numbers",
	"aoColumnDefs": [{
		'bSortable': false, 
		'aTargets': [ -1 ] 
	}]
});

$(document).on('click', '.delete_routes', function() {
	//$('.delete_routes').click(function(){
		var id = $(this).attr("data-id");
		smoke.confirm("Are you sure?", function(e){
			if (e){
				var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
				jax.open('POST',base_url+'/google/routes');
				jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				jax.send('command=delete&id='+id);
				jax.onreadystatechange = function(){ 
					if(jax.readyState==4) {
						if(jax.responseText == 'success'){
							smoke.signal("Routes deleted, redirecting to the list..", function(e){
								setTimeout(function(){window.location=base_url+"/google/routes"},500);
							}, {
								duration: 500
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