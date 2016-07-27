<div class="row">
	<div class="col-md-12"><h4 id="route_dest"></h4></div>
</div>
<div class="clearfix"></div>
<div class="row">
	<div class="col-md-6">
		<button type="button" class="btn btn-default" onclick="hideMarkers()" >Hide all hazard</button>
		<button type="button" class="btn btn-default" onclick="showMarkers()" >Show all hazard</button>
		<button type="button" class="btn btn-default" onclick="showhazard()" >Show hazard</button>
	</div>
	<div class="col-md-6">
		<button type="button" class="btn btn-primary" onclick="save_directions()">Save</button>
	</div>
</div><br />
<div class="clearfix"></div>
<div class="row">
	<div id="map" style="width: 100%; height: 600px;">
		<span style="color:Gray;">Loading map...</span>
	</div>
</div>
<div class="clearfix"></div><br />
<div class="row">
	<div class="col-md-12"><h3>Title</h3></div>
	<div class="col-md-12">
		<input type="text" class="form-control" rows="10" id="route_title" maxlength="50"/>
	</div>
</div>
<div class="clearfix"></div>
<div class="row">
	<div class="col-md-12"><h3>Information</h3></div>
	<div class="col-md-12">
		<textarea class="form-control" rows="10" id="route_info"></textarea>
	</div>
</div>
<div class="clearfix"></div>
<br />	
