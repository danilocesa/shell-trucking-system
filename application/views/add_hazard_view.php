<div id="center_map"></div>
<div class="row">
	<?php if($this->uri->segment(3) != NULL){?>
		<div class="col-md-4"><button type="button" class="btn btn-primary" onclick="save_redirect()">Save Hazard</button></div>
	<?php }?>
</div><br />	
<div class="">	
	<div class="">
		<div id="menu_add_route">
			<img src="<?php
				if($type == 'h')
					echo base_url().'assets/img/show_haz.png';
				else if($type == 's')
					echo base_url().'assets/img/site_show.png';
				else if($type == 'd')
					echo base_url().'assets/img/depot_show.png';
				?>" onclick="showMarkers()" id="show_hazard" width="40" data-tooltip="Show <?php
				if($type == 'h')
					echo 'Hazards';
				else if($type == 's')
					echo 'Sites';
				else if($type == 'd')
					echo 'Depots';
			?>" />
			<img src="<?php
				if($type == 'h')
					echo base_url().'assets/img/hide_haz.png';
				else if($type == 's')
					echo base_url().'assets/img/site_hide.png';
				else if($type == 'd')
					echo base_url().'assets/img/depot_hide.png';
				?>" onclick="hideMarkers()" id="hide_hazard" width="40" data-tooltip="Hide <?php
				if($type == 'h')
					echo 'Hazards';
				else if($type == 's')
					echo 'Sites';
				else if($type == 'd')
					echo 'Depots';
				?>"/>
			<img src="<?php
				if($type == 'h')
					echo base_url().'assets/img/addHazard.png';
				else if($type == 's')
					echo base_url().'assets/img/add_site.png';
				else if($type == 'd')
					echo base_url().'assets/img/add_depot.png';
				?>" onclick="enableDrawBounds()" id="add_hazardAction" width="40" data-tooltip="Add <?php
				if($type == 'h')
					echo 'Hazards';
				else if($type == 's')
					echo 'Sites';
				else if($type == 'd')
					echo 'Depots';
				?>" />
			<img src="<?php echo base_url();?>assets/img/help-tour.png" onclick="startTour()" width="40" data-tooltip="Help Tour" />
			<img src="<?php echo base_url();?>assets/img/back.png" onclick='window.location = "<?php echo base_url().'hazards-list#tabs-'.($type == 'h' ? '1' : ($type == 's' ? '2' : ($type == 'd' ? '3' : '1'))); ?>"' width="40" data-tooltip="Back" />
		</div>
		<div id="addHazSiteDepo" style="width:60px;cursor:pointer;">
			<a href=" <?php
				if($type == 'h')
					echo base_url().'add-sites';
				else if($type == 's')
					echo base_url().'add-hazards';
				else if($type == 'd')
					echo base_url().'add-hazards';
			?>"><img src="<?php
				if($type == 'h')
					echo base_url().'assets/img/add_site.png';
				else if($type == 's')
					echo base_url().'assets/img/addHazard.png';
				else if($type == 'd')
					echo base_url().'assets/img/addHazard.png';
				?>" width="40" data-tooltip="Add <?php
				if($type == 'h')
					echo 'Sites';
				else if($type == 's')
					echo 'Hazards';
				else if($type == 'd')
					echo 'Hazards';
				?>" style="margin-bottom:5px;" class="addQuickmenu" />
			</a>
			
			<a href=" <?php
				if($type == 'h')
					echo base_url().'add-depots';
				else if($type == 's')
					echo base_url().'add-depots';
				else if($type == 'd')
					echo base_url().'add-sites';
			?>"><img src="<?php
				if($type == 'h')
					echo base_url().'assets/img/add_depot.png';
				else if($type == 's')
					echo base_url().'assets/img/add_depot.png';
				else if($type == 'd')
					echo base_url().'assets/img/add_site.png';
				?>" width="40" data-tooltip="Add <?php
				if($type == 'h')
					echo 'Depots';
				else if($type == 's')
					echo 'Depots';
				else if($type == 'd')
					echo 'Sites';
				?>" style="margin-bottom:5px;" class="addQuickmenu" />
			</a>
		</div>
		<input id="address" type="textbox" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" onkeyup="codeAddress()" placeholder="Manila, Philippines">
		<div id="map" style="width: 100%;height:625px;position:absolute!important;left:0;top:54px;">
			<span style="color:Gray;">Loading map...</span>
		</div>
		<div id="hazardBounds">
			<p>Plot the polygonal boundary for this marker. Click OK when done.</p>
			<button class="btn btn-default" id="cancel-hazard-bounds">Cancel</a>
			<button class="btn btn-primary" id="ok-hazard-bounds" disabled="disabled">OK</a>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="margin-bottom:-10px;padding-bottom:6px;">
        <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true" >&times;</button>-->
        <h4 class="modal-title" id="myModalLabel"><?php echo ($type == 'h' ? 'Hazard' : ($type == 's' ? 'Site' : ($type == 'd' ? 'Depot' : 'Unknown'))) ?> Information</h4>
      </div>
      <div class="modal-body">
		<form class="form-horizontal" id="add_hazard_form" enctype="multipart/form-data" role="form" style="font-size:12px;">
		  <div class="form-group">
			<label for="location" class="col-sm-2 control-label">Location</label>
			<div class="col-sm-10">
			  <input type="email" class="form-control input-xs" id="location_modal" disabled >
			</div>
		  </div>
		  	<input type="hidden" id="site_region" value="" />
			<?php if($this->uri->segment(1) == "add-hazards"):?>
			<div class="form-group">
				<label for="status" class="col-sm-2 control-label">Status</label>
				<div class="col-sm-3">
					<select name="status" id="haz_status"class="form-control input-sm" style="width:120px!important;">
					  <option value="1" id="perm-status">Permanent</option>
					  <option value="0" id="temp-status">Temporary</option>	
					</select>
				</div>
				<div class="col-sm-3 start_end-date">
					<label>Start</label><span style="color:red;"> *<span>
					<input type="text" id="start_date" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled"  style="width:140px!important;display:inline" />
				</div>	
				<div class="col-sm-3 start_end-date"><label>End</label>
					<input type="text" id="end_date" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled"  style="width:140px!important;display:inline" />
				</div>
			</div>
			<?php endif;?> 
		<?php if ($type == 'h') { ?>
		  <div class="form-group icon-category">
		  		<label for="icon" class="col-sm-2 control-label">Category<span style="color:red;"> *<span></label>
		  		<div class="col-sm-3">
			  		<select id="marker-category" name="marker_category" class="form-control input-sm">
						<option value="">- select -</option>
						<option value="road">Road</option>
						<option value="vehicle">Vehicle</option>
						<option value="places">Places</option>
						<option value="misc">Miscellaneous</option>
					</select>
				</div>	
		  </div>
		<?php } ?>
		  <!--input type="hidden" id="site_category" name="site_category" value="" /-->
		  <div class="form-group">
		  		<!--input type="hidden" id="hazard_icon" value=""/-->
		  		<label for="icon" class="col-sm-2 control-label"> </label>
		  		<div class="col-sm-9" id="icon-container">
				  	<div id="blank" class="icon-list" style="display:block"></div>
					<div id="road" class="icon-list">
						<div class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/construction-ongoing.png)" id="construction-ongoing.png" data-tooltip="Construction ongoing"></div>
						<div data-tooltip="Falling rocks" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/falling-rocks.png)" id="falling-rocks.png"></div>
						<div data-tooltip="Loose Chippings" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/loose-chippings.png)" id="loose-chippings.png"></div>
						<div data-tooltip="No U-turn" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/no-uturn.png)" id="no-uturn.png"></div>
						<div data-tooltip="Parking" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/parking.png)" id="parking.png"></div>
						<div data-tooltip="Road accident" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/road-accident.png)" id="road-accident.png"></div>
						<div data-tooltip="Road narrows" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/road-narrows.png)" id="road-narrows.png"></div>
						<div data-tooltip="Road widens" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/road-widens.png)" id="road-widens.png"></div>
						<div data-tooltip="Slippery road" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/slippery-road.png)" id="slippery-road.png"></div>
						<div data-tooltip="Uneven road" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/uneven-road.png)" id="uneven-road.png"></div>
						<div data-tooltip="U-turn" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/uturn.png)" id="uturn.png"></div>
						<div data-tooltip="Overpass" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/overpass.png)" id="overpass.png"></div>
						<div data-tooltip="Public Transport" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/public-transport.png)" id="public-transport.png"></div>
						
						<div data-tooltip="Winding road" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/winding-road.png)" id="winding-road.png"></div>
						<div data-tooltip="Steep decent ahead" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/steep-decent-ahead.png)" id="steep-decent-ahead.png"></div>
						<div data-tooltip="Steep climb ahead" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/steep-climb-ahead.png)" id="steep-climb-ahead.png"></div>
						<div data-tooltip="Sharp curve" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/sharp-curve.png)" id="sharp-curve.png"></div>
						<div data-tooltip="Detour" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/detour.png)" id="detour.png"></div>
						<div data-tooltip="Heavy traffic" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/heavy-traffic.png)" id="heavy-traffic.png"></div>
						<div data-tooltip="Intersection ahead" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/intersection-ahead.png)" id="intersection-ahead.png"></div>
						<div data-tooltip="Merging traffic" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/merging-traffic.png)" id="merging-traffic.png"></div>
						<div data-tooltip="Ped-xing" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/ped-xing.png)" id="ped-xing.png"></div>
						<div data-tooltip="Railroad crossing" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/railroad-crossing.png)" id="railroad-crossing.png"></div>
						<div data-tooltip="Road branching off" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/road-branching-off.png)" id="road-branching-off.png"></div>
						<div data-tooltip="Roundabout ahead" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/roundabout-ahead.png)" id="roundabout-ahead.png"></div>
						<div data-tooltip="Traffic light ahead" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/traffic-light-ahead.png)" id="traffic-light-ahead.png"></div>
						
						<div data-tooltip="Accident prone area" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/accident-prone-area.png)" id="accident-prone-area.png"></div>
						<div data-tooltip="Traffic survey" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/traffic-survey.png)" id="traffic-survey.png"></div>
						<div data-tooltip="Falling debris" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/falling-debris.png)" id="falling-debris.png"></div>
						<div data-tooltip="Road closed" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/road-closed.png)" id="road-closed.png"></div>
						<div data-tooltip="Roadwork ahead" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/roadwork-ahead.png)" id="roadwork-ahead.png"></div>
					</div>
					<div id="vehicle" class="icon-list">
						<div data-tooltip="No overtake" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/no-overtaking.png)" id="no-overtaking.png"></div>
						<div data-tooltip="No parking" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/no-parking.png)" id="no-parking.png"></div>
						<div data-tooltip="No trucks" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/no-trucks.png)" id="no-trucks.png"></div>
						<div data-tooltip="Axle weight limit" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/axle-weight-limit.png)" id="axle-weight-limit.png"></div>
						<div data-tooltip="Length limit" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/length-limit.png)" id="length-limit.png"></div>
						<div data-tooltip="Weight limit" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/weight-limit.png)" id="weight-limit.png"></div>
						<div data-tooltip="Vertical clearance" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/vertical-clearance.png)" id="vertical-clearance.png"></div>
					</div>
					<div id="places" class="icon-list">
						<div data-tooltip="Church" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/church.png)" id="church.png"></div>
						<div data-tooltip="Hospital" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/hospital.png)" id="hospital.png"></div>
						<div data-tooltip="Police station" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/police-station.png)" id="police-station.png"></div>
						<div data-tooltip="School zone" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/school-zone.png)" id="school-zone.png"></div>
						<div data-tooltip="Market" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/market.png)" id="market.png"></div>
						<div data-tooltip="Shopping Center" class="icon" style="background-image:url(<?php echo base_url();?>assets/img/icons/shopping-center.png)" id="shopping-center.png"></div>
					</div>
					<div id="misc" class="icon-list">
						<div data-tooltip="Bridge" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/bridge.png)" id="bridge.png"></div>
						<div data-tooltip="Checkpoint" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/checkpoint.png)" id="checkpoint.png"></div>
						<div data-tooltip="Fire" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/fire.png)" id="fire.png"></div>
						<div data-tooltip="No entry" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/no-entry.png)" id="no-entry.png"></div>
						<div data-tooltip="Tollgate" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/tollgate.png)" id="tollgate.png"></div>
						<div data-tooltip="Traffic enforcer" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/traffic-enforcer.png)" id="traffic-enforcer.png"></div>
						<div data-tooltip="Warning" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/warning.png)" id="warning.png"></div>
						<div data-tooltip="Bicycle crossing" class="icon" title="Title" style="background-image:url(<?php echo base_url();?>assets/img/icons/bicycle-crossing.png)" id="bicycle-crossing.png"></div>
					</div>
				</div>	
		  </div>
		  <div class="form-group haz-img">
			<label for="image" class="col-sm-2 control-label">Image</label>
			<div class="col-sm-7">
			   <input type="file" id="_file" name="file_upload">
			   <div class="help-text pull-left" style="color:red;font-size:11px;">Only an image file (GIF, JPG, PNG) with file size less than or equal to 10 MB is allowed for upload.</div>
			   <input type="hidden" id="file_namename" value=""/>
			</div>
			<div class="col-sm-4"><img id="thumb_img" src="<?php echo base_url();?>uploads/" style="width:160px;height:130px;" /></div>
		  </div>
		  <?php if ($type == 's') { ?>
		  <div class="form-group" id="site-upload">
			<label for="image" class="col-sm-2 control-label">Site Layout</label>
			<div class="col-sm-6">
			   <input type="file" id="_sitephoto" name="site_photo">
			   <div class="help-text pull-left" style="color:red;font-size:11px;">Only GIF, JPG, PNG are supported</div>
			   <input type="hidden" id="site_photoname" value=""/>
			</div>
			<div class="col-sm-4"><img id="sitethumb" src="<?php echo base_url();?>uploads/sites/" style="width:160px;height:130px;" /></div>
		  </div>
		  <?php } ?>
		  <div class="form-group">
			<label for="title" class="col-sm-2 control-label">Title<span style="color:red;"> *<span></label>
			<div class="col-sm-10">
			  <input type="text" class="form-control input-sm" id="title_modal" name="hazard_title" maxlength="50" />
			</div>
		  </div>
		  <?php if ($type == 'h') { ?>
		  <div class="form-group speed-limit">
				<label for="controls" class="col-sm-2 control-label">Speed Limit</label>
				<div class="col-sm-2">
				<select class="form-control input-sm" id="speed_limit" name="speed_limit">
					<?php for ($i = 10; $i <= 80; $i=$i+10):?>
						<option <?php echo ($i == 10) ? 'selected="selected"': '';?>value="<?php echo $i;?>"><?php echo $i;?> KPH</option>
					<?php endfor;?>
				</select>
			</div>
		  </div>
		  <?php } ?>
		  <div class="form-group haz-controls">
			<label for="controls" class="col-sm-2 control-label">Controls</label>
			<div class="col-sm-10">
			 <textarea class="form-control" rows="3" id="control_modal" name="hazard_control" ></textarea>
			</div>
		  </div>
		  <div class="form-group">
			<label for="information" class="col-sm-2 control-label">Information</label>
			<div class="col-sm-10">
			 <textarea class="form-control" rows="3" id="info_modal" name="hazard_info" ></textarea>
			</div>
		  </div>
		
      </div>
	  <div id="save-marker-progress" style="margin-top:20px;font-size:11px;display:none">
			<div class="text-center">Saving <?php echo ($type == 'h' ? 'hazard' : ($type == 's' ? 'site' : ($type == 'd' ? 'depot' : ''))) ?>...</div>
			<div class="progress progress-striped active">
				<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
			</div>
		</div>
      <div class="modal-footer" style="margin-top:-20px;padding-top:8px;" id="save-marker-btns">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" id="modalclose">Close</button>
        <button type="submit" class="btn btn-primary btn-sm" onclick="" id='_submit'>Save</button>
      </div>
      </form>
    </div>
  </div>
</div>