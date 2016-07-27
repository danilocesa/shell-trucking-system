$(document).ready(function(){
	$(".site-lists").on("click",function(){
		$("#site-picture").attr("src",base_url+"/assets/sites/"+$(this).attr("data-desc"));
	});

});