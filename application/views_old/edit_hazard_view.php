<div class="row">	
	<div class="col-md-12">
		<input id="address" type="textbox" class="form-control" onkeyup="codeAddress()" style="width:400px;margin-top:10px;" placeholder="Manila, Philippines">
		<div id="map" style="width: 100%; height: 600px;">
			<span style="color:Gray;">Loading map...</span>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true" >&times;</button>-->
        <h4 class="modal-title" id="myModalLabel">Information</h4>
      </div>
      <div class="modal-body">
		<form class="form-horizontal" id="add_hazard_form" enctype="multipart/form-data" role="form">
		  <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Location</label>
			<div class="col-sm-10">
			  <input type="email" value="<?php echo $hazard_details->location; ?>" class="form-control" id="location_modal" disabled>
			</div>
		  </div>
		  <div class="form-group">
				<label for="status" class="col-sm-2 control-label">Status</label>
				<div class="col-sm-3">
					<select name="status" id="haz_status"class="form-control input-sm" style="width:120px!important;">
					  <option value="1" <?php echo ($hazard_details->status == 1)? "selected" : "" ; ?> id="perm-status">Permanent</option>
					  <option value="0" <?php echo ($hazard_details->status == 0)? "selected" : "" ; ?> id="temp-status">Temporary</option>	
					</select>
				</div>
				<div class="col-sm-3">
					<label>Start</label><input type="text" id="start_date" name="start_date" class="form-control datetimepicker input-sm" disabled="disabled"  style="width:140px!important;" value="<?php echo $hazard_details->start_date; ?>" />
				</div>	
				<div class="col-sm-3"><label>End</label> 
					<input type="text" id="end_date" name="end_date" class="form-control datetimepicker input-sm" disabled="disabled"  style="width:140px!important;" value="<?php echo $hazard_details->start_date; ?>" />
				</div>
			</div>
		   <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Image</label>
			<div class="col-sm-6">
			   <input type="file" id="_file" name="file_upload" multiple="false">
			   <input type="text" id="file_namename" value="<?php echo $hazard_details->hazard_image; ?>" style="display:none;" />
			</div>
			<div class="col-sm-4"><img id="cur_img" src="<?php echo base_url();?>uploads/<?php echo $hazard_details->hazard_image; ?>" style="width:160px;height:130px;" />
				<img id="thumb_img" src="<?php echo base_url();?>uploads/" style="width:160px;height:130px;" /></div>
		  </div>
		  <div class="form-group">
			<label for="inputEmail3" class="col-sm-2 control-label">Title</label>
			<div class="col-sm-10">
			  <input type="text" class="form-control" id="title_modal" value="<?php echo $hazard_details->title;?>" name="hazard_title" placeholder="Title">
			</div>
		  </div>
		   <div class="form-group">
			<label for="controls" class="col-sm-2 control-label">Controls</label>
			<div class="col-sm-10">
			 <textarea class="form-control" rows="3" id="control_modal" name="hazard_control" value="<?php echo $hazard_details->hazard_control;?>" ></textarea>
			</div>
		  </div>
		  <div class="form-group">
			<label for="inputPassword3" class="col-sm-2 control-label">Information</label>
			<div class="col-sm-10">
			 <textarea class="form-control" rows="3" id="info_modal"><?php echo $hazard_details->information; ?></textarea>
			</div>
		  </div>
		</form>
      </div>
      <div class="modal-footer">
        <a href"<?php echo base_url();?>google/hazard_list"><button type="button" class="btn btn-default" data-dismiss="modal" id="modalclose">Cancel</button></a>
        <button type="submit" class="btn btn-primary" onclick="save_info()" id='_submit'>Save changes</button>
      </div>
      
    </div>
  </div>
</div>