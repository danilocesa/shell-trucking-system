<!DOCTYPE html>
<html lang="en">
  <head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="no-cache">
  <meta http-equiv="Expires" content="-1">
  <meta http-equiv="Cache-Control" content="no-cache">
  <link rel="shortcut icon" href="<?php echo base_url();?>assets/img/favicon.ico" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url();?>assets/img/favicon.ico" type="image/x-icon">
  <title>Not Exists</title>

  <!-- Bootstrap core CSS -->
  <link href="<?php echo base_url();?>assets/css/bootstrap.min.css" rel="stylesheet" />
	
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="<?php echo base_url();?>assets/js/html5shiv.js"></script>
      <script src="<?php echo base_url();?>assets/js/respond.min.js"></script>
    <![endif]-->
    <noscript>
      <link href="<?php echo base_url();?>assets/css/noscript.css" rel="stylesheet">
      <div id="content">
      <div class="logo"><img src="<?php echo base_url();?>assets/img/shell_logo.png" width="180" height="150" /></div>
      <p class="info">Please enable your javascript.</p>
      <p class="refresh"><a href="<?php echo current_url();?>">Refresh</a></p>
    </div>
    </noscript>
    <script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.min.js"></script>
  </head>
  <body>


<?php $user_latlng = $this->dan_model->select_where("user_tb",array("user_id"=>get_session("login")['user_id'])); ?>
  <!-- Modal -->
<div class="modal fade" id="inactiveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Route is not exists</h4>
      </div>
      <!--<div class="modal-body">
        Your session has expired. Please log-in again.
      </div>-->
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.location='<?php echo base_url();?>'">Okay</button>
      </div>  
    </div>
  </div>
</div>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/bootstrap.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $("#inactiveModal").modal();
});  
</script>
</body>
</html>
