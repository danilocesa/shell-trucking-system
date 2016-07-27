<div id="center_map"></div>
<div class="row">
	<?php if($this->uri->segment(3) != NULL){?>
		<div class="col-md-4"><button type="button" class="btn btn-primary" onclick="save_redirect()">Save Hazard</button></div>
	<?php }?>
</div><br />	
<div class="">	
	<div class="">
		<div id="menu_add_route">
			<img src="<?php echo base_url();?>assets/img/toggle_bounds.png" id="hideBounds" width="40" data-tooltip="Show/Hide marker boundary" />
			<img src="<?php echo base_url();?>assets/img/back.png" onclick='window.location = "<?php echo base_url().'hazards-list#tabs-'.($type == 'h' ? '1' : ($type == 's' ? '2' : ($type == 'd' ? '3' : '1'))); ?>"' width="40" data-tooltip="Back" />
		</div>
		<div id="map" style="width: 100%;height:625px;position:absolute!important;left:0;top:54px;">
			<span style="color:Gray;">Loading map...</span>
		</div>
		<div id="hazardBounds">
			<p>Plot the polygonal new boundary for this marker.<br>Click OK when done. Click Retain to keep old boundary.</p>
			<button class="btn btn-default" id="cancel-hazard-bounds">Clear</a>
			<button class="btn btn-primary" id="ok-hazard-bounds" disabled="disabled">OK</a>
			<button class="btn btn-primary" id="accept-hazard-bounds">Retain</a>
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
		<form class="form-horizontal" id="add_hazard_form" enctype="multipart/form-data" role="form">
		  <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Location</label>
			<div class="col-sm-10">
			  <input type="email" value="<?php
				if ($type == 'h')
					echo $hazard_details->location;
				else if ($type == 's')
					echo $hazard_details->site_location;
				else if($type == 'd')
					echo $hazard_details->depot_location;
			?>" class="form-control" id="location_modal" disabled>
			</div>
		  </div>
		  <?php if($type == 'h'):?>
		  <div class="form-group">
				<label for="status" class="col-sm-2 control-label">Status<span style="color:red;"> *<span></label>
				<div class="col-sm-3">
					<select name="status" id="haz_status"class="form-control input-sm" style="width:120px!important;">
					  <option value="1" <?php echo ($hazard_details->status == 1)? "selected" : "" ; ?> id="perm-status">Permanent</option>
					  <option value="0" <?php echo ($hazard_details->status == 0)? "selected" : "" ; ?> id="temp-status">Temporary</option>	
					</select>
				</div>
				<div class="col-sm-3 start_end-date">
					<label>Start<span style="color:red;"> *<span></label>
					<input type="text" id="start_date" name="start_date" class="form-control datetimepicker input-sm" style="width:140px!important;" value="<?php echo $hazard_details->start_date; ?>" />
				</div>	
				<div class="col-sm-3 start_end-date">
					<label>End</label>
					<input type="text" id="end_date" name="end_date" class="form-control datetimepicker input-sm" style="width:140px!important;" value="<?php if($hazard_details->end_date == "1970-01-01 08:00:00"){ echo ""; } else{echo $hazard_details->end_date; }  ?>" />
				</div>
			</div>
			<?php endif;?>
		   <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Image</label>
			<div class="col-sm-6">
			   <input type="file" id="_file" name="file_upload">
			   <div class="help-text pull-left" style="color:red;font-size:11px;">Only GIF, JPG, PNG are supported</div>
			   <input type="hidden" id="file_namename" value="<?php
				if ($type == 'h')
					echo $hazard_details->hazard_image;
				else if ($type == 's')
					echo $hazard_details->site_img;
				else if($type == 'd')
					echo $hazard_details->depot_img;
			   ?>" />
			</div>
			<div class="col-sm-4">
				<img id="thumb_img" src="<?php
				if ($type == 'h')
					echo base_url().'uploads/'.$hazard_details->hazard_image;
				else if ($type == 's')
					echo base_url().'uploads/sites/'.$hazard_details->site_img;
				else if($type == 'd')
					echo base_url().'uploads/depots/'.$hazard_details->depot_img;
				?>" style="width:160px;height:130px;border:solid thin #333;" />
			</div>
			</div>
		  <?php if ($type == 's') { ?>
		  <div class="form-group" id="site-upload">
			<label for="image" class="col-sm-2 control-label">Site Layout</label>
			<div class="col-sm-6">
			   <input type="file" id="_sitephoto" name="site_photo">
			   <div class="help-text pull-left" style="color:red;font-size:11px;">Only GIF, JPG, PNG are supported</div><br/>
			   <input type="hidden" id="site_photoname" value="<?php echo $hazard_details->site_photo; ?>"/>
			</div>
			<div class="col-sm-4">
				<img id="sitethumb" src="<?php echo base_url().'uploads/sites/sites_layout/'.$hazard_details->site_photo;?>" style="width:160px;height:130px;border:solid thin #333;"/>
			</div>
		  </div>
		  <?php } ?>
		  <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Title<span style="color:red;"> *<span></label>
			<div class="col-sm-10">
			  <input type="text" class="form-control" id="title_modal" maxlength="50" value="<?php
				if ($type == 'h')
					echo $hazard_details->title;
				else if ($type == 's')
					echo $hazard_details->site_name;
				else if($type == 'd')
					echo $hazard_details->depot_name;
				?>" name="hazard_title">
			</div>
		  </div>
		  <?php if ($type == 'h') { ?>
		  	<div class="form-group speed-limit">
				<label for="controls" class="col-sm-2 control-label">Speed Limit</label>
				<div class="col-sm-2">
				<select class="form-control input-sm" id="speed_limit" name="speed_limit">
					<?php for ($i = 10; $i <= 80; $i=$i+10):?>
						<option <?php echo $i == $hazard_details->speed_limit ? 'selected="selected"' : '' ?> value="<?php echo $i;?>"><?php echo $i;?> KPH</option>
					<?php endfor;?>
				</select>
			</div>
		  </div>
		  <div class="form-group">
			<label for="controls" class="col-sm-2 control-label">Controls</label>
			<div class="col-sm-10">
			 <textarea class="form-control" rows="3" id="control_modal" name="hazard_control" ><?php echo $hazard_details->hazard_control;?></textarea>
			</div>
		  </div>
		  <?php } ?>
		  <div class="form-group">
			<label for="inputPassword3" class="col-sm-2 control-label">Information</label>
			<div class="col-sm-10">
			 <textarea class="form-control" rows="3" id="info_modal"><?php
				if ($type == 'h')
					echo $hazard_details->information;
				else if ($type == 's')
					echo $hazard_details->site_information;
				else if($type == 'd')
					echo $hazard_details->depot_information;
			 ?></textarea>
			</div>
		  </div>
		</form>
      </div>
	  	<div id="save-marker-progress" style="margin-top:20px;font-size:11px;display:none">
			<div class="text-center">Saving <?php echo ($type == 'h' ? 'hazard' : ($type == 's' ? 'site' : ($type == 'd' ? 'depot' : ''))) ?>...</div>
			<div class="progress progress-striped active">
				<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
			</div>
		</div>
      <div class="modal-footer" style="margin-top:-20px;padding-top:8px;" id="save-marker-btns">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" id="modalclose">Close</button>
        <button type="submit" class="btn btn-primary btn-sm" onclick="saveMarker()">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>