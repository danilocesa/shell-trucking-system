<div id="point_notify" class="route_click" style="display:none;">Starting point</div>
<div id="help_tour" style="cursor:pointer;display:none;"><img src="<?php echo base_url();?>assets/img/help-tour.png" onclick="startTour()" width="40" data-tooltip="Help Tour" /></div>
<div class="clearfix"></div>
<div class="row" style="overflow:hidden;">
	<div class="" style="position: absolute;width:100%;left:0;">
		<div id="menu_add_route">
			<img src="<?php echo base_url();?>assets/img/show_markers.png" onclick="showMarkers()" id="show_hazard" width="40" data-tooltip="Show Markers" />
			<img src="<?php echo base_url();?>assets/img/hide_markers.png" onclick="hideMarkers()" id="hide_hazard" width="40" data-tooltip="Hide Markers"/>
			<img src="<?php echo base_url();?>assets/img/add_route.png" onclick="addRoute()" id="add_route" width="40" data-tooltip="Add Route" />
			<img src="<?php echo base_url();?>assets/img/delete_route.png" onclick="deleteRoute()" id="delete_route" width="40" data-tooltip="Delete Route" />
			<img src="<?php echo base_url();?>assets/img/save_route.png" onclick="saveRoute()" id="save_route" width="40" data-tooltip="Save Route"/>
			<img src="<?php echo base_url();?>assets/img/help-tour.png" onclick="startTour()" width="40" data-tooltip="Help Tour" />
			<img src="<?php echo base_url();?>assets/img/back.png" onclick='window.location = "<?php echo base_url();?>";' width="40" data-tooltip="Back" />
			<!--<img src="<?php echo base_url();?>assets/img/print-route.png" onclick="window.print()" id="" width="40" data-tooltip="Print Route" />-->
		</div>
		<input id="address" type="textbox" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" onkeyup="codeAddress()" placeholder="Manila, Philippines">
		<div id="map" style="width: 100%; height: 625px;">
			<span style="color:Gray;">Loading map...</span>
		</div>
		<div id="panel-holder" style="position:absolute;right:0;bottom:0;padding:0;margin-top:45px;top:0;overflow:hidden;width:40px;">
		<a href="javascript:void(0);" style="background-image:url(<?php echo base_url();?>assets/img/hazard_pin.png);top:0px;right:0px;max-height:40px" id="p2" class="slider-arrow show-panel" data-tooltip="Markers on Route" ></a>
		<div id="pp2" class="panel">
			<div class="floating">
				<h5>Markers on Route</h5>
			</div>
			<div class="paddingator">
				<div class="text-center" id="release_route"></div>
				<div id="worker-progress" class="progress progress-striped active">
					<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
				</div>
				<ol id="marker-list" style="margin-top:-40px;"></ol>
			</div>
		</div>
		<a href="javascript:void(0);" style="background-image:url(<?php echo base_url();?>assets/img/directions.png);top:50px;right:0px;max-height:40px" id="p4" class="slider-arrow show-panel" style="top:75px;right:-30px" data-tooltip="Directions"></a>
		<div id="pp4" class="panel">
			<div class="floating">
				<h5>Directions</h5>
			</div>
			<div class="paddingator">
				<div id="paneldirect"></div>	
			</div>
		</div>
	</div>
	
</div>
<div class="clearfix"></div>

<!-- Modal -->
<div class="modal fade bs-example-modal-sm" id="addRouteModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true" >&times;</button>-->
        <h4 class="modal-title" id="myModalLabel">Route Option</h4>
      </div>
      <div class="modal-body">
		  <div class="form-group row hide">
			<div class="col-sm-12">
				<label class="pull-left">Directions:</label>
				<select class="form-control" name="direct_opts" id="direct_opts" >
				  <option value="1">Auto directions</option>
				  <option value="2">Manual directions</option>
				</select>
			</div>
		  </div>
		  <div class="row"> 
			<div class="form-group">
				<div class="col-sm-12">
					<label class="pull-left">Depot:</label>
					<select class="form-control route-select" name="from_depot" id="from_depot" style="padding:0!important;border:0!important" >
						<?php foreach($depots as $row):?>
							<option value="" data-latitude="<?php echo $row->center_latitude; ?>" data-longtitude="<?php echo $row->center_longitude;?>" data-id="<?php echo $row->depot_id; ?>"><?php echo $row->depot_name; ?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>
			<div class="form-group">	
				<div class="col-sm-12">
					<label class="pull-left">Sites:</label>
					<select class="form-control route-select" name="to_site" id="to_site" style="padding:0!important;border:0!important" >
						<?php foreach($sites as $row):?>
							<option value="" data-latitude="<?php echo $row->center_latitude; ?>" data-longtitude="<?php echo $row->center_longitude;?>" data-id="<?php echo $row->site_id;?>"><?php echo $row->site_name; ?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>	
		</div>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default" id="close_route" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id='opts_sub'>Okay</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade bs-example-modal-sm" id="saveRouteModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true" >&times;</button>-->
        <h4 class="modal-title" id="myModalLabel">Route Details</h4>
      </div>
      <div class="modal-body">
			<form id="save-route-validation">
				<div class="row">
					<div class="col-md-12"><h6 class="text-left">Ship To #:<span style="color:red;"> *</span></h6></div>
					<div class="col-md-12">
						<input type="text" class="form-control" rows="10" name="ship_to" id="ship_to" maxlength="50"/>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="row">
					<div class="col-md-12"><h6 class="text-left">Title<span style="color:red;"> *</span></h6></div>
					<div class="col-md-12">
						<input type="text" class="form-control" rows="10" name="route_title" id="route_title" maxlength="50"/>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="row">
					<div class="col-md-12"><h6 class="text-left">Information</h6></div>
					<div class="col-md-12">
						<textarea class="form-control" rows="10" name="route_info" id="route_info" style="resize:none;"></textarea>
					</div>
				</div>
			</form>
			<div id="save-route-progress" style="margin-top:20px;font-size:11px;display:none">
				<div class="text-center">Saving route...<br>You will be redirected to route list when done.</div>
				<p class="screens-progress" style="font-size:11px"></p>
				<div class="progress progress-striped active">
					<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
				</div>
			</div>
      </div>
      <div class="modal-footer" id="save-route-btns">
        <button type="button" class="btn btn-default" id="close_route" data-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-primary" onclick="save_directions()">Save route</button>
      </div>
    </div>
  </div>
</div>