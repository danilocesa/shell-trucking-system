<div class="row">
	<div class="col-md-12"><h5 id="route_dest"></h5>
	
	</div>
</div>
<div class="clearfix"></div>
<div class="row">
	<div class="" style="position: absolute;width:100%;left:0;">
		<div id="menu_add_route">
			<img src="<?php echo base_url();?>assets/img/show_haz.png" onclick="showMarkers()" id="show_hazard" width="40" data-tooltip="Show Hazard" />
			<img src="<?php echo base_url();?>assets/img/hide_haz.png" onclick="hideMarkers()" id="hide_hazard" width="40" data-tooltip="Hide Hazard"/>
			<img src="<?php echo base_url();?>assets/img/edit_route.png" onclick="editPoly()" id="edit_poly" width="40" data-tooltip="Edit Route"/>
			<img src="<?php echo base_url();?>assets/img/print-route.png" id="print_details" width="40" data-tooltip="Print Route" />
			<img src="<?php echo base_url();?>assets/img/back.png" id="back_button" onclick='window.location = "<?php echo base_url();?>";' width="40" data-tooltip="Back" />
		</div>
		<div id="map" style="width: 100%; height: 585px;">
			<span style="color:Gray;">Loading map...</span>
		</div>
	</div>
	<div class="col-md-3" style="position:absolute;right:0;bottom:0;margin-right:30px;padding:0;margin-top:99px;top:0;">
		<div class="add-route-form">
			<form id="save-route-validation">
			<div class="row header_title col-md-12"><h3>Route Details</h3></div>
			<div class="row">
				<div class="col-md-12"><h6 class="text-left">Ship To #:</h6></div>
				<div class="col-md-12">
					<input type="text" class="form-control" rows="10" id="ship_to" readonly />
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="row">
				<div class="col-md-12"><h6 class="text-left">Title</h6></div>
				<div class="col-md-12">
					<input type="text" class="form-control" rows="10" id="route_title" readonly />
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="row">
				<div class="col-md-12"><h6 class="text-left">Information</h6></div>
				<div class="col-md-12">
					<textarea class="form-control" rows="3" id="route_info" style="resize:none;" readonly></textarea>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="row">
				<div class="col-md-12">
					<input type="button" value="Save Route" onclick="save_directions()" id="save_way" class="btn btn-success pull-right btn-sm" />
				</div>
			</div>
			</form>
		</div>	
		<div id="nearby_haz">
			<div class="row nearby_header_title col-md-12"><h3>Nearby Hazard</h3><div id="nearbyhaz_count" style="color:#fff;font-size: 11px;margin-left: 7px;position: absolute;right: 13px;bottom: 10px;"></div></div>
			<div class="text-center hide" id="release_route"><h5>Route not created</h5></div>
			<ol id="marker-list" style="margin-top:32px;">
			</ol>
			<p id="totaldistance" class="pull-left" style="font-size:12px;"></p>
			<p id="reordering-panel" class="pull-right col-md-12" style="visibility:hidden;">
				<button type="button" class="btn btn-primary moveup btn-xs">Move Up</button>
				<button type="button" class="btn btn-primary movedown btn-xs">Move Down</button>
			<!--	<button class="moveup">Move Up</button>
				<button class="">Move Down</button><br>-->
				<!--<button class="print">Print Hazards</button>-->
			</p>
		</div>

	</div>
	
</div><br /><!--
<div class="clearfix"></div>
<div class="row">
	<div class="col-md-12"><h3 class="text-left">Ship To #</h3></div>
	<div class="col-md-12">
		<input type="text" class="form-control" rows="10" id="route_ship" />
	</div>
</div>
<div class="clearfix"></div>
<div class="row">
	<div class="col-md-12"><h3 class="text-left">Title</h3></div>
	<div class="col-md-12">
		<input type="text" class="form-control" rows="10" id="route_title" />
	</div>
</div>
<div class="clearfix"></div>
<div class="row">
	<div class="col-md-12"><h3 class="text-left">Information</h3></div>
	<div class="col-md-12">
		<textarea class="form-control" rows="10" id="route_info"></textarea>
	</div>
</div>
<div class="clearfix"></div>
<br />	

-->


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
			<form action="<?php echo base_url();?>google/print_route/<?php echo $this->uri->segment(3);?>" method="POST" id="print_hazards">
				<input type="hidden" id="orgn" name="orgn" value=""/>
				<input type="hidden" id="dest" name="dest" value=""/>
				<div id="map_path" style="overflow:auto;width:185px;display:none;"></div>
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