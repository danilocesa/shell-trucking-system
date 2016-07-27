<div class="row">
	<div class="col-md-12"><h5 id="route_dest"></h5>	
	</div>
</div>
<div class="clearfix"></div>
<div class="row">
	<div class="" style="position: absolute;width:100%;left:0;">
		<div id="menu_add_route">
			<img src="<?php echo base_url();?>assets/img/show_markers.png" onclick="showMarkers()" id="show_hazard" width="40" data-tooltip="Show Hazard" />
			<img src="<?php echo base_url();?>assets/img/hide_markers.png" onclick="hideMarkers()" id="hide_hazard" width="40" data-tooltip="Hide Hazard"/>
			<img src="<?php echo base_url();?>assets/img/edit_route.png" onclick="editPoly()" id="edit_poly" width="40" data-tooltip="Edit Route"/>
			<!--img src="<?php echo base_url();?>assets/img/screenshot.png" id="map_screenshot" width="40" data-tooltip="Refresh screenshot" /--> 
			<img src="<?php echo base_url();?>assets/img/print-route.png" id="print_details" width="40" data-tooltip="Print Route" />
			<img src="<?php echo base_url();?>assets/img/back.png" id="back_button" onclick='window.location = "<?php echo base_url();?>";' width="40" data-tooltip="Back" />
		</div>
		<div id="map" style="width: 100%; height: 585px;">
			<span style="color:Gray;">Loading map...</span>
		</div>
	</div>

	<div id="panel-holder" style="position:absolute;right:0;bottom:0;padding:0;margin-top:90px;top:0;overflow:hidden;width:40px;">
		<a href="javascript:void(0);" style="background-image:url(<?php echo base_url();?>assets/img/details_pin.png);top:0px;right:0px;max-height:40px;" id="p1" class="slider-arrow show-panel" data-tooltip="Route Details" ></a>
		<div id="pp1" class="panel">
			<div class="floating">
				<h5>Route Details</h5>
			</div>
			<div class="paddingator">
				<form id="save-route-validation">
					<div class="row">
						<div class="col-md-12"><h6 class="text-left">Ship To #:</h6></div>
						<div class="col-md-12">
							<input type="text" class="form-control" rows="10" id="ship_to" readonly maxlength="50"/>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="row">
						<div class="col-md-12"><h6 class="text-left">Title</h6></div>
						<div class="col-md-12">
							<input type="text" class="form-control" rows="10" id="route_title" readonly maxlength="50"/>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="row">
						<div class="col-md-12"><h6 class="text-left">Information</h6></div>
						<div class="col-md-12">
							<textarea class="form-control" rows="10" id="route_info" style="resize:none;" readonly></textarea>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="row" id="save-route-btns">
						<div class="col-md-12">
							<input type="button" value="Save Route" onclick="save_directions()" id="save_way" class="btn btn-success pull-right btn-sm" />
						</div>
					</div>
					<div id="save-route-progress" style="margin-top:20px;font-size:11px;display:none">
						<div class="text-center">Saving route...<br>You will be redirected to route list when done.</div>
						<p class="screens-progress" style="font-size:11px"></p>
						<div class="progress progress-striped active">
							<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<a href="javascript:void(0);" style="background-image:url(<?php echo base_url();?>assets/img/hazard_pin.png);top:50px;right:0px;max-height:40px;" id="p2" class="slider-arrow show-panel" data-tooltip="Markers on Route" ></a>
		<div id="pp2" class="panel">
			<div class="floating">
				<h5>Markers on Route</h5>
			</div>
			<div class="paddingator">
				<div style="margin-top:-35px">
					<div class="text-center" id="release_route"></div>
					<p id="totaldistance" style="font-size:12px;"></p>
					<div id="worker-progress-haz" class="progress progress-striped active">
						<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
					</div>
					<ol id="marker-list"></ol>
				</div>
			</div>
		</div>
		<a href="javascript:void(0);" style="background-image:url(<?php echo base_url();?>assets/img/screenshot_pin.png);top:100px;right:0px;max-height:40px;" id="p3" class="slider-arrow show-panel" data-tooltip="Screenshots"></a>
		<div id="pp3" class="panel">
			<div class="floating">
				<h5>Screenshots</h5>
			</div>
			<div class="paddingator">
				<div class="text-center" id="release_scr"></div>
				<p class="screens-progress" style="font-size:11px"></p>
				<div id="worker-progress-scr" class="progress progress-striped active">
					<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
				</div>
				<div id="map-shots" style="overflow:auto;"></div>
			</div>
		</div>
		<a href="javascript:void(0);" style="background-image:url(<?php echo base_url();?>assets/img/directions.png);top:150px;right:0px;max-height:40px;" id="p4" class="slider-arrow show-panel" style="top:75px;right:-30px" data-tooltip="Directions"></a>
		<div id="pp4" class="panel">
			<div class="floating">
				<h5>Directions</h5>
			</div>
			<div class="paddingator">
				<div id="paneldirect"></div>	
			</div>
		</div>
	</div>
	
</div><br />

<div class="col-md-6 hide">
		<div class="btn-group">
			<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Settings
		    	<span class="caret"></span>	
			</button>
			<ul class="dropdown-menu" role="menu">
				<!--<li><a href="<?php echo base_url();?>google/add_hazard/<?php echo $this->uri->segment(3);?>">Add hazard</a></li>
			    <li><a href="<?php echo base_url();?>google/edit_waypoints/<?php #echo $this->uri->segment(3); ?>">Edit route</a></li>
			    
			    <li><a href="<?php echo base_url();?>google/download/<?php echo $this->uri->segment(3);?>">Download</a></li>-->
			    <!--<li><a href="<?php echo base_url();?>google/print_route/<?php echo $this->uri->segment(3);?>">Print route</a></li>-->
		 	</ul>
		</div>
	</div>



<!-- Modal -->
<div class="modal fade" id="previewscreenie" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
    	<h4 class="modal-title" id="myModalLabel">Preview</h4>
      </div>
      <div class="modal-body">
			<div style="height:0px;overflow:hidden">
				<canvas id="myCanvas" width="640" height="640"></canvas>
				<img id="canvasImg_orig" src=""/>
			</div>
			<div id="imgloading"><img src="<?php echo base_url();?>assets/img/ajax-loader.gif"></div>
			<div id="map_shots" class="col-md-12" style="position:relative;"></div>
			<form action="<?php echo base_url();?>google/print_route/<?php echo $this->uri->segment(2);?>" method="POST" id="print_hazards">
				<input type="hidden" id="orgn" name="orgn" value=""/>
				<input type="hidden" id="dest" name="dest" value=""/>
				<input type="text" style="display:none;" id="start_pt_loc" />
				<input type="text" style="display:none;" id="end_pt_loc" />
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default" id="" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id='gen_report'>Generate Report</button>
        </form>
      </div>
    </div>
  </div>
</div>	
<div id="divDownloadBack" style="position: fixed;left: 0;top: 0;width: 100%;height: 100%;z-index: 1999;background: rgba(100,100,100,0.5);display:none;"></div>
<div id="divDownload" style="position:fixed;top:50%;left:50%;transform: translate(-50%, -50%);z-index:2000;width:300px;height:140px;background:#fff;padding:10px;border:solid thin #ccc;display:none;">
	<div class="text-center">Generating report</div>
	<p class="screens-progress" style="font-size:11px">&nbsp;</p>
	<div class="progress progress-striped active">
		<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
	</div>
	<button class="btn btn-default" onclick="javascript:location.reload(true);">Cancel</a>
</div>