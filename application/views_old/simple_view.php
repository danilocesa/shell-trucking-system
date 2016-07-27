<div class="clearfix"></div>
<div class="row" style="padding-top:5px;">
	<div class="" style="position: absolute;width:100%;left:0;top:50px;">
		<div id="menu_add_route">
			<img src="<?php echo base_url();?>assets/img/show_haz.png" onclick="showMarkers()" id="show_hazard" width="40" data-tooltip="Show Hazard" />
			<img src="<?php echo base_url();?>assets/img/hide_haz.png" onclick="hideMarkers()" id="hide_hazard" width="40" data-tooltip="Hide Hazard"/>
			<img src="<?php echo base_url();?>assets/img/add_route.png" onclick="addRoute()" id="add_route" width="40" data-tooltip="Add Route" />
			<img src="<?php echo base_url();?>assets/img/delete_route.png" onclick="ClearPolyLine()" id="delete_route" width="40" data-tooltip="Delete Route" />
			<!--<img src="<?php echo base_url();?>assets/img/print-route.png" onclick="window.print()" id="" width="40" data-tooltip="Print Route" />-->
		</div>
		<input id="address" type="textbox" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" onkeyup="codeAddress()" placeholder="Manila, Philippines">
		<div id="map" style="width: 100%; height: 625px;">
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
						<input type="text" class="form-control" rows="10" name="ship_to" id="ship_to" />
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="row">
					<div class="col-md-12"><h6 class="text-left">Title</h6></div>
					<div class="col-md-12">
						<input type="text" class="form-control" rows="10" name="route_title" id="route_title" />
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="row">
					<div class="col-md-12"><h6 class="text-left">Information</h6></div>
					<div class="col-md-12">
						<textarea class="form-control" rows="3" name="route_info" id="route_info"></textarea>
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
			<div class="row nearby_header_title col-md-12"><h3>Nearby Hazard</h3></div>
			<div class="text-center" id="release_route"><h5>Route not created</h5></div>
			<ol id="marker-list">
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
		  <div class="form-group">
			<div class="col-sm-12">
				<select class="form-control" name="direct_opts" id="direct_opts" >
				  <option value="1">Auto directions</option>
				  <option value="2">Manual directions</option>
				</select>
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