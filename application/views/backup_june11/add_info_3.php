<div class="row ">
	<div id="map" style="width: 100%; height: 600px;">
		<span style="color:Gray;">Loading map...</span>
	</div>
</div>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="clearMarkers()">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Information</h4>
      </div>
      <div class="modal-body">
		<form class="form-horizontal" role="form">
		  <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Location</label>
			<div class="col-sm-10">
			  <input type="email" class="form-control" id="location_modal" disabled>
			</div>
		  </div>
		  <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Title</label>
			<div class="col-sm-10">
			  <input type="email" class="form-control" id="title_modal" placeholder="Title">
			</div>
		  </div>
		  <div class="form-group">
			<label for="inputPassword3" class="col-sm-2 control-label">Information</label>
			<div class="col-sm-10">
			 <textarea class="form-control" rows="3" id="info_modal"></textarea>
			</div>
		  </div>
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" >Close</button>
        <button type="button" class="btn btn-primary" onclick="save_info()">Save changes</button>
      </div>
    </div>
  </div>
</div>

