<style type="text/css">
	table.page_header {width: 100%; border: none; background-color: #ff0; border-bottom: solid 1mm #f00; padding: 2mm;}
	table.page_footer {width: 100%; border: none; background-color: #ff0; border-top: solid 1mm #f00; padding: 2mm}
	h1 {color: #000033}
	h2 {color: #000055}
	h3 {color: #000077}
	div.niveau{padding:0px;margin:0px;}
	th{text-align:center;padding:5px;background:#fc0;}
	page{font-family:arial;}
	div#front{background:url(assets/img/front1.jpg);background-repeat:no-repeat;background-position:center center;width:1125px;height:700px;margin-top:50}
	td.text { vertical-align:center; font-size:10px; word-wrap: break-word; white-space: normal; padding-top:10px; width: 160px; }
	td.legend { vertical-align:center; text-align:center; background:#333; border:none; color:#fff; width:2px; border-radius:14px; padding:8px 8px 5px 8px; font-size:14px; height:8px; max-height: 8px;}
	td.pic { width:27px; }
	table p { margin:0px; line-height:14px; }
	table#haz-mapping, table#haz-mapping td { border:solid thin #dcc;}
</style>
<page pageset="old">
	<div id="front">
		<div style="position:relative;margin-top:340px;width:1100px;text-align:center;font-family:helvetica;text-decoration:underline">
			<?php echo $hazards[array_search($hazard_ids[0] ,$hazards_lookup)]['location']; ?> to <?php echo $hazards[array_search($hazard_ids[count($hazard_ids) - 1] ,$hazards_lookup)]['location']; ?>
		</div>
	</div>
</page>

<page backtop="14mm" backbottom="14mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
	<page_header>
		<table class="page_header">
			<tr>
				<td style="width: 100%; text-align: left">
					<img src="assets/img/shell_logo.png" style="width:23px;height:20px"/>
					Journey Management Plan
				</td>
			</tr>
		</table>
	</page_header>
	<page_footer>
		<table class="page_footer">
			<tr>
				<td style="width: 100%; text-align: right">
					Page [[page_cu]]/[[page_nb]]
				</td>
			</tr>
		</table>
	</page_footer>
</page>

	<?php
	if($screens != NULL):
	foreach($screens as $key => $img_src):?>
	<page pageset="old">
		<?php if ($key == 0)
			echo '<bookmark title="Delivery Routes" level="0" ></bookmark>';
		?>
		<h1>Delivery Route</h1>
		<div class="niveau">
			<div style="margin-left:130px;width:520px;border:solid thin #000">
				<img style="height:600px;width:640px;" src="<?php echo substr(substr($img_src, strpos($img_src, 'screens')), 0, strpos(substr($img_src, strpos($img_src, 'screens')), '?ver')); ?>">
			</div>
			<?php if($hazard_ids != NULL): ?>
			<div style="position:absolute;right:0;top:75;border:solid thin #000;width:225px;padding:5px">
				<h5 style="text-align:center">
					LEGEND
					<?php
						if (strpos($img_src, '0.jpg') !== FALSE)
							echo '<br/>Overall Route Map';
						else
							echo '<br/>Segment Map ('.$key.' of '.(count($screens) - 1).')'; // first image is exempted
					?>
				</h5>
				<table>
					<tbody>
						<tr>
							<td class="legend" style="background:#080">A</td>
							<?php if (strpos($img_src, '0.jpg') !== FALSE) {?>
							<td class="pic">
								<img style="width:30px;height:30px" src="assets/img/icons/depots.png">
							</td>
							<td class="text">
								Depot
							</td>
							<?php } else {?>
							<td class="pic"></td>
							<td class="text">
								Start
							</td>
							<?php } ?>
						</tr>
					<?php
						if (strpos($img_src, '0.jpg') === FALSE) {
							// Get IDs of hazards in current map screenshot
							$arrIds = explode(",", $screens_id[$key]);
							// Check if there are markers in screenshot
							if ($arrIds[0] !== '.jpg'){
								foreach ($arrIds as $key1 => $value1)
								{
									// Get hazard details using hazard ID
									$key = array_search($value1 ,$hazards_lookup);
					?>
						<tr>
							<td class="legend" style="background:#fff;color:#000;border:solid thin #000;">
								<?php echo $key1 + 1; ?>
							</td>
							<td class="pic">
								<img style="width:30px;height:30px" src="assets/img/icons/<?php echo $hazards[$key]['hazard_icon']; ?>">
							</td>
							<td class="text">
								<?php echo $hazards[$key]['title']; ?>
							</td>
						</tr>
					<?php 
								}
							}
						}
					?>
						<tr>
							<td class="legend" style="background:#c00">B</td>
							<?php if (strpos($img_src, '0.jpg') !== FALSE) {?>
							<td class="pic">
								<img style="width:30px;height:30px" src="assets/img/icons/shell.png">
							</td>
							<td class="text">
								Site
							</td>
							<?php } else {?>
							<td class="pic"></td>
							<td class="text">
								End
							</td>
							<?php } ?>
						</tr>
					</tbody>
				</table>
			</div>
			<?php endif;?>
			<div style="position:absolute;top:115;left:0;border:solid thin #000;width:100px;height:100px;padding:5px;background:#fff;">
				<img style="width:100px;height:100px" src="assets/img/map_dir.jpg">
			</div>
			</div>
	</page>

	<?php endforeach;
	endif;
	?>
	<?php if ($hazard_ids != null) {
		// Spread hazard mapping to two columns
		$member_count = 8;
		// Preserve array keys
		$chunk_norris = array_chunk($hazard_ids, $member_count, true);
		// Counter of sites
		$siteCtr = 1;
		// Loop through chunks
		foreach ($chunk_norris as $key_chunk => $chunk) {
	?>
		<page pageset="old">
		<h1>Route Hazard Mapping</h1>
	<?php
		// Bookmark the first item in markers array
		if ($key_chunk == 0) {
	?>
		<bookmark title="Route Hazard Mapping" level="0" ></bookmark>	
	<?php } ?>
		<table id="haz-mapping">
			<thead style="font-size:12px">
				<tr>
					<th>Landmark</th>	
					<th>Photograph</th>
					<th>Information</th>	
					<th>Controls</th>
					<?php
						// Don't print right column when items in chunk is less than half of defined member count
						if (count($chunk) > ($member_count/2)) {
					?>
					<th>Landmark</th>	
					<th>Photograph</th>
					<th>Information</th>	
					<th>Controls</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody style="font-size:10px">
		<?php
			// Loop through chunk values
			foreach ($chunk as $key => $value)
			{
				// Break loop when reached middle index
				if ($key == (($key_chunk * $member_count) + ($member_count / 2)))
					break;
				// Print two items per row; print item to left column first, then print item to the right column
				// 2 >> left column; 1 >> right column
				$loop = 2;
		?>
				<tr>
		<?php
				while ($loop > 0) {
					// If column on the right does not exists, break loop
					if ($loop == 1 && !isset($chunk[$key + ($member_count / 2)])){
						break;
					}
				
					// Adjust key and value based on column
					$key1 = $loop == 2 ? $key : $key + ($member_count / 2);
					$value1 = $loop == 2 ? $value : $chunk[$key + ($member_count / 2)];
					
					// Get hazard details key using adjusted value
					$key2 = array_search($value1 ,$hazards_lookup);
		?>
					<td style="text-align:center;width:50px" align="center">
						<?php if (strpos($value1, 'd')) {?>
							<p style="font-size:14px"><strong>DEPOT</strong></p>
						<?php } else if (strpos($value1, 's')) { ?>
							<p style="font-size:14px"><strong>SITE <?php echo $siteCtr++; ?></strong></p>
							<strong><?php echo 'KM '.$distances[$key1].'<br>'; ?></strong>
						<?php } else {?>
							<p style="font-size:14px"><strong><?php echo $key1; ?></strong></p>
							<strong><?php echo 'KM '.$distances[$key1].'<br>'; ?></strong>
						<?php } ?>
						<img style="width:50px;height:50px;" src="assets/img/icons/<?php echo $hazards[$key2]['hazard_icon'];?>">
					</td>
					<td style="vertical-align:top;text-align:center;width:150px">
						<img src="<?php
						// fetch images from respective directories (depots, sites, uploads)
						if (strpos($value1, 'd'))
							echo 'uploads/depots/'.$hazards[$key2]['hazard_image'];
						else if (strpos($value1, 's'))
							echo 'uploads/sites/'.$hazards[$key2]['hazard_image'];
						else if (strpos($value1, 'h'))
							echo 'uploads/'.$hazards[$key2]['hazard_image'];
						?>" style="width:150px;height:100px;">
					</td>
					<td style="vertical-align:top;width:160px" <?php echo (strpos($value1, 'h') === FALSE) ? 'colspan="2"' : ''; ?>>
						<p><strong>Title:</strong> <?php echo $hazards[$key2]['title']; ?></p>
						<p><strong>Location:</strong> <?php echo $hazards[$key2]['location']; ?></p>
						<p><strong>Information:</strong> 
						<?php
							if (!empty($hazards[$key2]['information']))
								echo $hazards[$key2]['information'];
							else
								echo "(Not available)";
						?>
						</p>
						<?php if ($hazards[$key2]['speed_limit'] != null) { ?>
						<p><strong>Speed Limit:</strong> <?php echo $hazards[$key2]['speed_limit']; ?> KPH
						</p>
						<?php } ?>
					</td>
					<?php if (strpos($value1, 'h')) { ?>
					<td style="vertical-align:top;width:100px">
						<p>
						<?php
							if (!empty($hazards[$key2]['controls']))
								echo $hazards[$key2]['controls'];
							else
								echo "(Not available)";
						?>
						</p>
					</td>
					<?php } ?>
		<?php
					$loop--;
				} // while loop
		?>
				</tr>
		<?php } ?>
			</tbody>
		</table>
		</page>
		<?php } ?>
	<?php } ?>
<page pageset="old">
<bookmark title="Directions" level="0" ></bookmark>
<h1>Directions</h1>
<?php
	// Spread directions to two columns
	$member_count = 60;
	$chunk_norris = array_chunk($directions, $member_count, true);
	
	foreach ($chunk_norris as $key_chunk => $chunk)
	{
		echo '<table>';
		echo '<tbody style="font-size:12px">';
		foreach ($chunk as $key => $value)
		{
			if ($key == (($key_chunk * $member_count) + ($member_count / 2)))
				break;
			
			echo '<tr>';
			echo '<td>'.($key + 1).'.</td>';
			echo '<td width="500">';
			echo strip_tags($value);
			echo '</td>';
			if (isset($chunk[$key + ($member_count / 2)])) {
				echo '<td>'.($key + ($member_count / 2) + 1).'.</td>';
				echo '<td width="500">';
				echo strip_tags($chunk[$key + ($member_count / 2)]);
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
?>
</page>
<?php 
if(!empty($sts)) {
	foreach($sts as $stsKey => $value) {
?>
	<page pageset="old">
	<?php if ($stsKey == 0) { ?>
		<bookmark title="Customer Sites" level="0" ></bookmark>
	<?php } ?>
	<h1>Customer Site <?php echo $stsKey + 1; ?></h1>
	<?php
		// Get sitephoto
		$key = array_search($value.'s' ,$hazards_lookup);
	?>	
	<div style="position:absolute;border:solid thin #fff;margin-top:55">
		<img src="uploads/sites/sites_layout/<?php echo $hazards[$key]['site_photo'] ? $hazards[$key]['site_photo'] : 'no-image.jpg'; ?>" style="width:1040px;height:620px;">
	</div>
	</page>
	<?php 
		}
	} ?>
<?php 
$content = ob_get_clean();
//echo $content;
//return;
require_once(APPPATH.'libraries/html2pdf.class.php');
try{
	$html2pdf = new HTML2PDF('L', 'A4', 'en', true, 'UTF-8', array(0, 0, 0, 0));
	// display the full page
    $html2pdf->pdf->SetDisplayMode('fullpage');
	$html2pdf->writeHTML($content);
	$html2pdf->createIndex('Contents', 25, 14, false, true, 2);
	$html2pdf->Output('Journey Management Plan '.date('Y-m-d H:i:s').'.pdf');
}
catch(HTML2PDF_exception $e) {
	echo $e;
	exit;
}
?>