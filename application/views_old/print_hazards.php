<?php 
if (isset($img)){
	
	$sts = array();
	$hzs = array();
	foreach ($img as $hazard){
		// Hazard
		if (strrpos($hazard, 'h') !== false){
			$hzs[] = str_replace('h', '', $hazard);
		}
		// Site
		else if (strrpos($hazard, 's') !== false){
			$sts[] = str_replace('s', '', $hazard);
		}
	}
	// Perform queries 
	$query = "";
	if (count($hzs))
		$query .= "SELECT CONCAT(hazard_id,'h') id, hazard_id, latitude, longtitude, title, information, location, hazard_image, hazard_icon, '' site_photo, status, COALESCE(start_date,null) start_date, COALESCE(end_date,null) end_date, hazard_control FROM hazard_tb WHERE active=1 and hazard_id IN (".implode(',', $hzs).")";
	if (count($hzs) && count($sts))

		$query .= " UNION ";
	if (count($sts))
		$query .= "SELECT CONCAT(site_id,'s') id, site_id, latitude, longtitude,site_name, null, site_location, site_img, hazard_icon, site_photo, null, null, null, null FROM site_tb WHERE active=1 and site_id IN (".implode(',', $sts).")";
	$result = $this->db->query($query)->result();
	$hazards = array();
	foreach($result as $row){
		$hazards[] = array(
			'site_hazard' =>$row->id,
			'hazard_id' => $row->hazard_id,
			'latitude' => $row->latitude,
	 		'longitude' => $row->longtitude,
	 		'title' => $row->title,
	 		'location' => $row->location,
	 		'information' => $row->information,
	 		'hazard_image' => $row->hazard_image,
	 		'hazard_icon' => $row->hazard_icon,
	 		'site_photo' => $row->site_photo,
	 		'status' => $row->status,
	 		'start_date' => $row->start_date,
	 		'end_date' => $row->end_date,
	 		'controls' => $row->hazard_control
		);
	}
} 

ob_start();
?>
<style type="text/css">
	table.page_header {width: 100%; border: none; background-color: #ff0; border-bottom: solid 1mm #f00; padding: 2mm;}
	table.page_footer {width: 100%; border: none; background-color: #ff0; border-top: solid 1mm #f00; padding: 2mm}
	h1 {color: #000033}
	h2 {color: #000055}
	h3 {color: #000077}
	div.niveau{padding:0px;margin:0px;}
	th{text-align:center;padding:5px;background:#fc0;}
	page{font-family:times;}
	div#front{background:url(<?php echo base_url(); ?>assets/img/front1.jpg);background-repeat:no-repeat;background-position:center center;width:1125px;height:700px;}
</style>
<page pageset="old">
	<div id="front">
		<bookmark title="Front" level="0" ></bookmark>
		<div style="position:relative;margin-top:340px;width:1100px;text-align:center;font-family:helvetica;text-decoration:underline">
			Route 26012-12 Location AB to Location Z2
		</div>
	</div>
</page>
			<?php
			if($screen_array != NULL):
			 foreach($screen_array as $img_fn):?>
				<page backtop="14mm" backbottom="14mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
							<page_header>
								<table class="page_header">
									<tr>
										<td style="width: 100%; text-align: left">
											<img src="<?php echo base_url(); ?>assets/img/shell_logo.png" style="width:23px;height:20px"/>
											Journey Management Plan
										</td>
									</tr>
								</table>
							</page_header>
					<bookmark title="Delivery Route" level="0" ></bookmark><h1>Delivery Route</h1>
						<div class="niveau">
							<div style="margin-left:190px;width:520px;border:solid thin #000">
								<img style="height:600px;width:640px;" src="<?php echo base_url(); ?>screenie/<?php echo $img_fn;?>.png">
							</div>
							<?php if($img != NULL): ?>
							<div style="position:absolute;right:0;top:75;border:solid thin #000;width:175px;padding:5px">
								<h5 style="text-align:center">LEGEND</h5>
									<?php 
									$legends = array();
										foreach ($hazards as $hazard){
											$legends[] = $hazard['hazard_icon'];
										}
										foreach (array_unique($legends) as $legend): 
											?>
										<p style="margin-top:-5px;margin-bottom:2px">
											<img style="width:30px;height:30px" src="<?php echo base_url();?>assets/img/icons/<?php echo $legend; ?>">
											<span style="font-size:10px;font-weight:bold;margin-top:-7px"><?php echo ucwords(str_replace('-', ' ', str_replace('.png','',$legend))); ?></span>
										</p>
									<?php 
									endforeach;?>
							</div>
							<?php endif;?>
							<div style="position:absolute;top:120;left:50;border:solid thin #000;width:100px;height:100px;padding:5px;background:#fff;">
								<img style="width:100px;height:100px" src="<?php echo base_url();?>assets/img/map_dir.jpg">
							</div>
							</div>
							</page>
							<page_footer>
					        <table class="page_footer">
					            <tr>
					                <td style="width: 100%; text-align: right">
					                    Page [[page_cu]]/[[page_nb]]
					                </td>
					            </tr>
					        </table>
							</page_footer>
					<?php endforeach;
					endif;
					if ($img != NULL)
					{
					?>	
					<page backtop="14mm" backbottom="14mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
	<page_header>
		<table class="page_header">
			<tr>
				<td style="width: 100%; text-align: left">
					<img src="<?php echo base_url();?>assets/img/icons/shell.png" style="width:23px;height:20px"/>
					Journey Management Plan
				</td>
			</tr>
		</table>
	</page_header>
	<bookmark title="Route Hazard Mapping" level="0" ></bookmark><h1>Route Hazard Mapping</h1>
		<table border=1 style="width:100%">
			<thead>
				<tr>
					<th>Landmark</th>	
					<th>Photograph</th>
					<th>Hazards</th>	
					<th>Controls</th>	
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($hazards as $key => $value){
					?>
					<tr>
						<td style="text-align:center">
							<?php echo 'KM '.$hsz_dst[$value['site_hazard']].'<br>'; ?>
							<img src="<?php echo base_url();?>assets/img/icons/<?php echo $value['hazard_icon'];?>">
						</td>
						<td style="text-align:center">
							<img src="<?php echo base_url();?>uploads/<?php 
							if(strrpos($value['site_hazard'], 's') !== false){
								echo "sites/".$value['site_photo'];
							}else{
								echo $value['hazard_image'];
							}

							?>" style="width:150px;height:100px">
						</td>
						<td style="word-wrap:break-word;white-space:normal;width:390px;vertical-align:top">
							<p>Location: <?php echo $value['location']; ?></p>
							<p><?php echo (isset($value['information']) == NULL)? "No information available" : $value['information'] ; ?></p>
						</td>
						<td style="word-wrap:break-word;white-space:normal;width:390px;vertical-align:top">
							<p><?php echo (isset($value['hazard_control']) == NULL)? " No controls available" : $value['hazard_control'] ;  ?></p>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
</page>
<page_footer>
    <table class="page_footer">
        <tr>
            <td style="width: 100%; text-align: right">
                Page [[page_cu]]/[[page_nb]]
            </td>
        </tr>
    </table>
</page_footer>
<?php 
foreach($sts as $key) { ?>
	<page_header>
		<table class="page_header">
			<tr>
				<td style="width: 100%; text-align: left">
					<img src="<?php echo base_url();?>assets/img/icons/shell.png" style="width:23px;height:20px"/>
					Journey Management Plan
				</td>
			</tr>
		</table>
	</page_header>
	<page>
	<?php foreach($hazards as $new_val):
	if(strrpos($new_val['site_hazard'], 's') !== false):
	?>
	<img src="<?php echo base_url();?>uploads/sites/<?php echo $new_val['site_photo']; ?>" style="width:1100px;height:700px">
	<?php 
	endif;
	endforeach;?>
	</page>
	<page_footer>
    <table class="page_footer">
        <tr>
            <td style="width: 100%; text-align: right">
                Page [[page_cu]]/[[page_nb]]
            </td>
        </tr>
    </table>
	</page_footer>
<?php } ?>

<?php 
}
$content = ob_get_clean();	
require_once(APPPATH.'libraries/html2pdf.class.php');
try{
	$html2pdf = new HTML2PDF('L', 'A4', 'en', true, 'UTF-8', 0);
	$html2pdf->writeHTML($content);
	$html2pdf->Output('Journey Management Plan '.date('Y-m-d H:i:s').'.pdf');
}
catch(HTML2PDF_exception $e) {
	echo $e;
	exit;
}

?>