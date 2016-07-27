<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google extends CI_Controller {
	public function __construct(){
		parent::__construct();
		date_default_timezone_set('Asia/Manila');
		$this->check_permission();
	}

	public function check_permission(){
		if($this->nativesession->userdata('login')["logged_in"] == FALSE){
			$this->nativesession->ses_destroy();
			redirect(base_url());
		}
	}

//*********************Directions**********************//
	public function index(){
		$data['sites'] = $this->dan_model->select_table("site_tb");
		$data['depots'] = $this->dan_model->select_table("depot_tb");
		$data['title'] = "Add routes";
		$data['js_content'] = "js/add_route_js";
		$data['content'] = "simple_view";
		$this->load->view('template/main_view',$data);	
	}

	public function save_points(){
		if($_REQUEST['command']=='save'){
			$data = json_decode($_REQUEST['mapdata'],true);
			$new = str_replace("null,","",$data);
			$this->dan_model->inserting("waypoint_tb",
				array(
					"start"=>$data['location']['start_loc'],
					"end"=>$data['location']['last_loc'],
					"midpoints"=>json_encode($data['midpoints']),
					"title"=>strip_tags($data['info']['title']),
					"info"=>strip_tags($data['info']['information']),
					"created_date"=>date("Y-m-d H:i:s"),
					"route_json"=>json_encode($data),
					"ship_to"=>strip_tags($data['info']['ship_to']),
					"created_by"=> get_session("login")['user_id']));
			$routeId = $this->dan_model->select_max("waypoint_tb","route_id")->route_id; 
			audit_insert("Created ".strip_tags($data['info']['title'])." route",$routeId."r");
			echo json_encode(array("resp"=>"success","route_id"=>$routeId));
		}
	}

	public function edit_route($id){
		$haha = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$id));
		if(count($haha) == 0){
			echo json_encode(array("notExists"=>1));
		} else{
			if($_REQUEST['command']=='edit'){
				$data = json_decode($_REQUEST['mapdata'],true);
				$new = str_replace("null,","",$data);
				$this->dan_model->update_all_c("waypoint_tb",
					array(
						"start"=>$data['location']['start_loc'],
						"end"=>$data['location']['last_loc'],
						"midpoints"=>json_encode($data['midpoints']),
						"title"=>strip_tags($data['info']['title']),
						"info"=>strip_tags($data['info']['information']),
						"route_json"=>json_encode($data),
						"ship_to"=>strip_tags($data['info']['ship_to']),
						"updated_by"=>get_session("login")['user_id'],
						"updated_date"=>date("Y-m-d H:i:s")
					),array("route_id"=>$id));	
				audit_insert("Updated ".strip_tags($data['info']['title'])." route",$id."r");
				echo json_encode(array("resp"=>"success"));
			}	
		}	
	}
	
	public function routes(){
		if(isset($_REQUEST['command']) == 'delete'){
			$route_info = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$_REQUEST['id']));
			$this->dan_model->delete_where("waypoint_tb",array("route_id"=>$_REQUEST['id']));
			audit_insert("Deleted ".$route_info->title." route",0);
			die('success');
		}
		$this->datatables->select("ship_to,start,title,info,created_by,updated_by")->from("waypoint_tb");
		echo $this->datatables->generate();
		$data['routes_list'] = $this->dan_model->selectall_orderby("waypoint_tb", "route_id");
		$data['js_content'] = "js/route_list_js";
		$data['title'] = 'Routes list';
		$data['content'] = 'routes_list_view';
		$this->load->view('template/main_view',$data);	
	}

	public function routes_listData(){
		$this->datatables->select("ship_to,start,title,info,created_by,updated_by")->from("waypoint_tb");
		echo $this->datatables->generate();
	}
	public function fetch_waypoints($id){
		$haha = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$id));
		if(count($haha) == 0){
			redirect("google/notExist");
		}
		if(isset($_REQUEST['command']) == 'fetch')
		{
			$haha = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$id));
			print_r(json_encode($haha));
			exit;
		}
		$data['js_content'] = "js/route_details_js";
		$data['title'] = 'Routes details';
		$data['content'] = 'routes_details_view';
		$data['route_id'] = $id; 		
		$this->load->view("template/main_view",$data);
	}

	public function edit_waypoints($id){
		if(isset($_REQUEST['command']) == 'fetch')
		{
			$haha = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$id));
			print_r(json_encode($haha));
			exit;
		}
		if(isset($_REQUEST['trigger'])=='save')
		{
			$data = json_decode($_REQUEST['mapdata'],true);
			$new = str_replace("null,","",$data);
			$this->dan_model->update_all_c("waypoint_tb",array("start"=>$data['location']['start_loc'],"end"=>$data['location']['last_loc'],"midpoints"=>json_encode($data['midpoints']),"title"=>strip_tags($data['info']['title']),"info"=>strip_tags($data['info']['information']),"created_date"=>date("Y-m-d H:i:s"),"route_json"=>json_encode($data)),array("route_id"=>$id));
			audit_insert("Updated ".$data['info']['title']." route",0);
			die('success');
		}
		$data['js_content'] = "js/edit_route_js";
		$data['title'] = 'Edit route';
		$data['content'] = 'edit_route_view';		
		$this->load->view("template/main_view",$data);
	}
	
	public function fetch_hazard($uri = NULL){

		if(isset($_REQUEST['command']) == 'fetch')
		{
			$this->db->query("UPDATE hazard_tb SET active = CASE WHEN status = 0 and NOW() >= start_date and NOW() <= end_date THEN 1 WHEN status = 1 THEN 1 ELSE 0 END");
			if($uri == NULL){
				$wew = $this->db->query("SELECT * from (SELECT CONCAT(a.hazard_id,'h') hazard_id,title, hazard_image, hazard_icon, active, information, location,  center_latitude,center_longitude, speed_limit, status, last_update_by, group_concat(b.latitude SEPARATOR '|') latitude, group_concat(b.longitude SEPARATOR '|') longitude FROM hazard_tb a LEFT JOIN hazard_latlng_tb b ON a.hazard_id = b.hazard_id GROUP BY hazard_id UNION SELECT CONCAT(c.site_id,'s') site_id, site_name, site_img, hazard_icon, active, site_information, site_location, center_latitude,center_longitude, last_update_by, speed_limit , status, group_concat(d.latitude SEPARATOR '|') latitude, group_concat(d.longitude SEPARATOR '|') longitude from site_tb c LEFT JOIN site_latlng_tb d ON c.site_id = d.site_id GROUP BY site_id UNION SELECT CONCAT(e.depot_id,'d')depot_id, depot_name, depot_img, hazard_icon, active, depot_information, depot_location, center_latitude,center_longitude , speed_limit, status, last_update_by, group_concat(f.latitude SEPARATOR '|') latitude, group_concat(f.longitude SEPARATOR '|') longitude from depot_tb e LEFT JOIN depot_latlng_tb f ON e.depot_id = f.depot_id GROUP BY depot_id ) a where active = 1 ORDER BY hazard_id")->result();
			}
			if($uri == "h"){
				$wew = $this->db->query("SELECT * from (SELECT CONCAT(a.hazard_id,'h') hazard_id, title, hazard_image, hazard_icon, active, information, location,  center_latitude,center_longitude, last_update_by, speed_limit, status, group_concat(b.latitude SEPARATOR '|') latitude, group_concat(b.longitude SEPARATOR '|') longitude FROM hazard_tb a LEFT JOIN hazard_latlng_tb b ON a.hazard_id = b.hazard_id GROUP BY hazard_id) a where active = 1 ORDER BY hazard_id")->result(); 
			}
			if($uri == "s"){
				$wew = $this->db->query("SELECT * from (SELECT CONCAT(c.site_id,'s') site_id, site_name, site_img, hazard_icon, active, site_information, site_location, center_latitude,center_longitude, status, group_concat(d.latitude SEPARATOR '|') latitude, group_concat(d.longitude SEPARATOR '|') longitude from site_tb c LEFT JOIN site_latlng_tb d ON c.site_id = d.site_id GROUP BY site_id) a where active = 1 ORDER BY site_id")->result(); 
			}
			if($uri == "d"){
				$wew = $this->db->query("SELECT * from (SELECT CONCAT(e.depot_id,'d')depot_id, depot_name, depot_img, hazard_icon, active, depot_information, depot_location, center_latitude,center_longitude, status, group_concat(f.latitude SEPARATOR '|') latitude, group_concat(f.longitude SEPARATOR '|') longitude from depot_tb e LEFT JOIN depot_latlng_tb f ON e.depot_id = f.depot_id GROUP BY depot_id) a where active = 1 ORDER BY depot_id")->result(); 
			}
			
			print_r(json_encode($wew));
			exit;
		}
	}
	public function fetch_near(){
		$allIDS = $this->input->post("hazardsID");
		$sts = array();
		$hzs = array();
		$dps = array();
		foreach ($allIDS as $hazard){
			// Hazard
			if (strrpos($hazard, 'h') !== false){
				$hzs[] = str_replace('h', '', $hazard);
			}
			// Site
			else if (strrpos($hazard, 's') !== false){
				$sts[] = str_replace('s', '', $hazard);
			}
			else{
				$dps[] = str_replace('d', '', $hazard);
			}
		}
		$haz = implode(',',$hzs);
		$sit = implode(',',$sts);
		$dep = implode(',',$dps);
		if(empty($hzs)){
			$query = "SELECT * from (SELECT CONCAT(c.site_id,'s') site_id, site_name, site_img, hazard_icon, active, site_information, site_location, center_latitude,center_longitude, status, group_concat(d.latitude SEPARATOR '|') latitude, group_concat(d.longitude SEPARATOR '|') longitude  FROM site_tb c LEFT JOIN site_latlng_tb d ON c.site_id = d.site_id WHERE c.site_id IN ({$sit}) GROUP BY site_id UNION SELECT CONCAT(e.depot_id,'d')depot_id, depot_name, depot_img, hazard_icon, active, depot_information, depot_location, center_latitude,center_longitude, status, group_concat(f.latitude SEPARATOR '|') latitude, group_concat(f.longitude SEPARATOR '|') longitude FROM depot_tb e LEFT JOIN depot_latlng_tb f ON e.depot_id = f.depot_id WHERE e.depot_id IN ({$dep}) GROUP BY depot_id ) c where active = 1 ORDER BY site_id";
		} else{
			$query = "SELECT * from (SELECT CONCAT(a.hazard_id,'h') hazard_id,title, hazard_image, hazard_icon, active, information, location,  center_latitude,center_longitude, status, group_concat(b.latitude SEPARATOR '|') latitude, group_concat(b.longitude SEPARATOR '|') longitude FROM hazard_tb a LEFT JOIN hazard_latlng_tb b ON a.hazard_id = b.hazard_id WHERE a.hazard_id IN ({$haz}) GROUP BY hazard_id UNION SELECT CONCAT(c.site_id,'s') site_id, site_name, site_img, hazard_icon, active, site_information, site_location, center_latitude,center_longitude, status, group_concat(d.latitude SEPARATOR '|') latitude, group_concat(d.longitude SEPARATOR '|') longitude  FROM site_tb c LEFT JOIN site_latlng_tb d ON c.site_id = d.site_id WHERE c.site_id IN ({$sit}) GROUP BY site_id UNION SELECT CONCAT(e.depot_id,'d')depot_id, depot_name, depot_img, hazard_icon, active, depot_information, depot_location, center_latitude,center_longitude, status, group_concat(f.latitude SEPARATOR '|') latitude, group_concat(f.longitude SEPARATOR '|') longitude FROM depot_tb e LEFT JOIN depot_latlng_tb f ON e.depot_id = f.depot_id WHERE e.depot_id IN ({$dep}) GROUP BY depot_id ) a where active = 1 ORDER BY hazard_id";
		}
		//echo $query;
		$haz = $this->db->query($query)->result();
		echo json_encode($haz);
	
	}

	public function print_route($id){
		if($this->input->post() == ""){
			redirect(base_url("page-not-found"));
		}
		$data['distances'] = $this->input->post("hzds_dst");
		$data['hazard_ids'] = $this->input->post('hzds');
		$data['screens'] = $this->input->post('screens');
		$data['screens_id'] = $this->input->post('screens_id');
		$data['origin'] = $this->input->post("orgn");
		$data['destination'] = $this->input->post("dest");
		$data['directions'] = $this->input->post("directions");
		
		if ($data['hazard_ids'] != NULL){
			$sts = array();
			$hzs = array();
			foreach ($data['hazard_ids'] as $hazard) {
				// Hazard
				if (strrpos($hazard, 'h') !== false){
					$hzs[] = str_replace('h', '', $hazard);
				}
				// Site
				else if (strrpos($hazard, 's') !== false){
					$sts[] = str_replace('s', '', $hazard);
				}
			
				else{
					$dps[] = str_replace('d', '', $hazard);
				}
			}
		}
		// Perform queries 
		$query = "";
		
		if (count($hzs))
			$query .= "SELECT CONCAT(hazard_id,'h') id, hazard_id, center_latitude, center_longitude, title, information, location, hazard_image, hazard_icon, '' site_photo, status, COALESCE(start_date,null) start_date, COALESCE(end_date,null) end_date, hazard_control, speed_limit FROM hazard_tb WHERE active=1 and hazard_id IN (".implode(',', $hzs).")";
		if (count($sts)) {
			if ($query)
				$query .= " UNION ";
			
			$query .= "SELECT CONCAT(site_id,'s') id, site_id hazard_id, center_latitude, center_longitude, site_name title, site_information information, site_location location, site_img hazard_image, hazard_icon, site_photo, status, COALESCE(start_date,null) start_date, COALESCE(end_date,null) end_date, null hazard_control, null speed_limit FROM site_tb WHERE active=1 and site_id IN (".implode(',', $sts).")";
		}
		if (count($dps)) {
			if ($query)
				$query .= " UNION ";
				
			$query .= "SELECT CONCAT(depot_id,'d') id, depot_id hazard_id, center_latitude, center_longitude, depot_name title, depot_information information, depot_location location, depot_img hazard_image, hazard_icon, '' site_photo, status, COALESCE(start_date,null) start_date, COALESCE(end_date,null) end_date, null hazard_control, null speed_limit FROM depot_tb WHERE active=1 and depot_id IN (".implode(',', $dps).")";
		}
		$result = $this->db->query($query)->result();
		$hazards = array();
		$hazards_lookup = array();
		
		foreach($result as $row){
			/*if(@$row->hazard_id != NULL){
				$ids = $row->hazard_id;
				$photo = $row->site_photo;
			}
			if(@$row->site_id != NULL){
				$ids = $row->site_id;
				$photo = $row->site_photo;
			}
			if(@$row->depot_id != NULL){
				$ids = $row->depot_id;
				$photo = $row->depot_photo;
			}*/
			$hazards_lookup[] = $row->id;
			$hazards[] = array(
				'mod_id' =>$row->id,
				'hazard_id' => $row->hazard_id, //$ids,
				'center_latitude' => $row->center_latitude,
				'center_longitude' => $row->center_longitude,
				'title' => $row->title,
				'location' => $row->location,
				'information' => $row->information,
				'hazard_image' => $row->hazard_image,
				'hazard_icon' => $row->hazard_icon,
				'site_photo' => $row->site_photo, //$photo,
				'status' => $row->status,
				'start_date' => $row->start_date,
				'end_date' => $row->end_date,
				'controls' => (@$row->hazard_control != NULL)? $row->hazard_control : "",
				'speed_limit' => $row->speed_limit
			);
		}		
		$data['hazards'] = $hazards;	
		$data['hazards_lookup'] = $hazards_lookup;	
		$data['sts'] = $sts;		
		$this->load->view("print_hazards", $data);
	}

	public function fetch_img(){
		$folderPath = FCPATH.'screens/'.$_REQUEST["route_id"]; 
		if (!file_exists($folderPath)) {
			echo json_encode(array("failed" => 1));
		} else {
			$files = array_slice(scandir($folderPath), 2);
			sort($files, SORT_NUMERIC);
			echo json_encode(array("files" => $files, "id" => $_REQUEST["route_id"]));
		}
	}

	function removeDir($path) {
		// Normalise $path.
		$path = rtrim($path, '/') . '/';
	
		// Remove all child files and directories.
		$items = glob($path . '*');
	
		foreach($items as $item) {
			is_dir($item) ? removeDir($item) : unlink($item);
		}
	
		// Remove directory.
		rmdir($path);
	}

	public function save_img(){
		$data = json_decode($_REQUEST['data'],true);
		extract($data);
		
		$folderPath = FCPATH.'screens/'.$routeId;
		if ($testDir){
			if (!file_exists($folderPath)) {
				// Create directory
				mkdir($folderPath, 0777, true);
			} else {
				// Delete all files
				$this->removeDir($folderPath);
				mkdir($folderPath, 0777, true);
			}
		}
			
		// Without hazard
		if($_REQUEST['type']=='1'){
			$mapURL = 'http://maps.googleapis.com/maps/api/staticmap?&size=640x640&sensor=false&path=weight:5%7Ccolor:blue%7Cenc:';
			$mapURL .= $enc;
			$mapURL .= '&markers=label:A%7C';
			$mapURL .= $startLat;
			$mapURL .= ',';
			$mapURL .= $startLng;
			$mapURL .= '&markers=label:B%7C';
			$mapURL .= $endLat;
			$mapURL .= ',';
			$mapURL .= $endLng;
			$mapURL .= '&key=AIzaSyAgocfpYU736VIBLB1O2SB4XQoarno70SA';
			
			$image = file_get_contents(urldecode($mapURL));
			write_file($folderPath.'/'.$fileName.'.jpg', $image); //Where to save the image on your server
			echo json_encode(array("resp"=>"success","map"=>$mapURL));
		}
		// With hazard
		else if($_REQUEST['type']=='2'){			
			$mapURL = 'http://maps.googleapis.com/maps/api/staticmap?&size=640x640&sensor=false&path=weight:5%7Ccolor:blue%7Cenc:';
			$mapURL .= $enc;
			$mapURL .= '&markers=label:A%7C';
			$mapURL .= $startLat;
			$mapURL .= ',';
			$mapURL .= $startLng;
			$mapURL .= '&markers=label:B%7C';
			$mapURL .= $endLat;
			$mapURL .= ',';
			$mapURL .= $endLng;
			$mapURL .= str_replace('3j', '&', $markers);
			$mapURL .= '&key=AIzaSyAgocfpYU736VIBLB1O2SB4XQoarno70SA';
			
			$image = file_get_contents(urldecode($mapURL));
			write_file($folderPath.'/'.$fileName.'_'.$hazards.'.jpg', $image); //Where to save the image on your server
			echo json_encode(array("resp"=>"success","map"=>$mapURL));
		}
	}

	public function geocode(){
		if (isset($_POST['olat']) && isset($_POST['olng']) && isset($_POST['dlat']) && isset($_POST['dlng'])){	
			// format this string with the appropriate latitude longitude
			$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$_POST['olat'].','.$_POST['olng'].'&sensor=false';
			// make the HTTP request
			$data = @file_get_contents($url);
			// parse the json response
			$jsondata = json_decode($data,true);
			echo '<strong>';
			echo str_replace(', Philippines', '', $jsondata['results'][0]['formatted_address']);
			echo ' to ';
			
			// format this string with the appropriate latitude longitude
			$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$_POST['dlat'].','.$_POST['dlng'].'&sensor=false';
			// make the HTTP request
			$data = @file_get_contents($url);
			// parse the json response
			$jsondata = json_decode($data,true);
			echo str_replace(', Philippines', '', $jsondata['results'][0]['formatted_address']);
			echo '</strong><br>';
			
			if ($_POST['dlat'] != '?' && $_POST['dlat'] != '?')
			{
				// format this string with the appropriate latitude longitude
				$url = 'https://maps.googleapis.com/maps/api/directions/json?origin='.$_POST['olat'].','.$_POST['olng'].'&destination='.$_POST['dlat'].','.$_POST['dlng'].'&sensor=false&mode=driving';
				// make the HTTP request
				$data = @file_get_contents($url);
				// parse the json response
				$jsondata = json_decode($data,true);
				$ctr = 1;
				foreach ($jsondata['routes'][0]['legs'][0]['steps'] as $value)
				{
					echo $ctr.'. '.$value['html_instructions'].'<br>';
					$ctr++;
				}
			}
		}

	}

	public function delete_route($id = NULL){
		if($id == "all"){
			$this->db->truncate("waypoint_tb");
			audit_insert("All route deleted",0);
			echo json_encode(array("empty"=>true));
			exit;
		} else{
			$routes_details = $this->db->where_in("route_id",$this->input->post("checkboxarray"))->get("waypoint_tb")->result();
			foreach($routes_details as $row){
				audit_insert("Deleted ".$row->title." route",0);
				$this->removeDir(FCPATH.'screens/'.$row->route_id);
			}

			$this->db->where_in("route_id",$this->input->post("checkboxarray"))->delete("waypoint_tb");
			echo json_encode(array("deleted"=>true));
			exit;
		}
	}

	public function delete_hazard($id = NULL){
		if($id == "all"){
			$this->db->truncate("hazard_tb");
			audit_insert("All hazard deleted",0);
			echo json_encode(array("empty"=>true));
			exit;
		} else{			
			$routes_details = $this->db->where_in("hazard_id",$this->input->post("checkboxarray"))->get("hazard_tb")->result();		
			foreach($routes_details as $row){			
				audit_insert("Deleted ".$row->title." hazard",0);
			}
			$this->db->where_in("hazard_id",$this->input->post("checkboxarray"))->delete("hazard_tb");
			$this->db->where_in("hazard_id",$this->input->post("checkboxarray"))->delete("hazard_latlng_tb");		
			echo json_encode(array("deleted"=>true));
			exit;
		}
	}

	public function delete_site($id = NULL){
		if($id == "all"){
			$this->db->truncate("site_tb");
			audit_insert("All sites deleted",0);
			echo json_encode(array("empty"=>true));
			exit;
		} else{		
			$routes_details = $this->db->where_in("site_id",$this->input->post("checkboxarray"))->get("site_tb")->result();	
			foreach($routes_details as $row){
				audit_insert("Deleted ".$row->site_name." site",0);
			}
			$this->db->where_in("site_id",$this->input->post("checkboxarray"))->delete("site_tb");
			$this->db->where_in("site_id",$this->input->post("checkboxarray"))->delete("site_latlng_tb");	
			echo json_encode(array("deleted"=>true));
			exit;
		}
	}

	public function delete_depot($id = NULL){
		if($id == "all"){
			$this->db->truncate("depot_tb");
			audit_insert("All depots deleted",0);
			echo json_encode(array("empty"=>true));
			exit;
		} else{		
			$routes_details = $this->db->where_in("depot_id",$this->input->post("checkboxarray"))->get("depot_tb")->result();		
			foreach($routes_details as $row){
				$this->dan_model->update_all_c("user_tb",array("latitude"=>"14.592033300000000000","longtitude"=>"121.006406500000020000","location"=>"0"),array("location"=>$row->depot_id));
				audit_insert("Deleted ".$row->depot_name." depot",0);
			}
			$this->db->where_in("depot_id",$this->input->post("checkboxarray"))->delete("depot_tb");
			$this->db->where_in("depot_id",$this->input->post("checkboxarray"))->delete("depot_latlng_tb");	
			echo json_encode(array("deleted"=>true));
			exit;
		}
	}

//**************************Directions END*************************//	



//************************Hazard**********************************//
	public function add_hazard($type){
			if(isset($_REQUEST['command'])=='save'){
				$newcode = preg_replace( "/\r|\n/", " ", $_REQUEST['infodata']);
				$data = json_decode(trim($newcode),true);
				$today = date("Y-m-d H:i:s"); 
				$date = date('Y-m-d H:i:s', strtotime(@$data['start_date']));
				$filename = $data['filename'] == "" ? "no-image.jpg" :$data['filename'];
				$site_photo = isset($data['site_photo']) ? ($data['site_photo'] == "" ? "no-image.jpg" :$data['site_photo']) : '';
				if(isset($data['end_date'])){
					if($data['end_date'] == '' && $data['status'] == 0){
						$end =  '2100-12-23 11:59:59';
					}
					if($data['end_date'] != '' && $data['status'] == 0){
						$end =  date('Y-m-d H:i:s', strtotime($data['end_date']));
					} 
				}
				else{
					$end = 'null';
				} 
				$start = isset($data['start_date']) && $data['status'] == 0 ? date('Y-m-d H:i:s', strtotime($data['start_date'])): 'null';
				$icon = $data['hazard_icon'] == "" ? "other.png" :$data['hazard_icon'];
				switch ($type) {
					case 's':
						$this->dan_model->inserting("site_tb",array(
						"site_name"=>"{$data['title']}",
						"site_img"=>$filename,
						"site_photo"=>"{$site_photo}",
						"hazard_icon"=>$icon,
						"created_date"=>"{$today}",
						"site_location"=>"{$data['location']}",
						"site_region" => "{$data['site_region']}",
						"site_information"=>"{$data['info']}",
						"center_latitude" => "{$data['latitude']}",
						"center_longitude" => "{$data['longitude']}",
						"created_by"=> get_session("login")["user_id"]
						));
						$id = $this->dan_model->select_max("site_tb","site_id")->site_id;
						for ($i = 0; $i < count($data['lat']); $i++)
						{
							$this->dan_model->inserting("site_latlng_tb",array(
								"site_id"=>"{$id}",
								"latitude"=>"{$data['lat'][$i]}",
								"longitude" => "{$data['lng'][$i]}"
							));
						}
						audit_insert("Added ".$data['title']." site",$id."s");
						die(json_encode(array('resp' => 'success', 'id' => $id)));
						break;
					case 'd':
						$this->dan_model->inserting("depot_tb",array(
						"depot_name"=>"{$data['title']}",
						"hazard_icon"=>$icon,
						"depot_img"=>$filename,
						"created_date"=>"{$today}",
						"depot_location"=>"{$data['location']}",
						"depot_region" => "{$data['site_region']}",
						"depot_information"=>"{$data['info']}",
						"center_latitude" => "{$data['latitude']}",
						"center_longitude" => "{$data['longitude']}",
						"created_by"=> get_session("login")["user_id"]
						));
						$id = $this->dan_model->select_max("depot_tb","depot_id")->depot_id;
						for ($i = 0; $i < count($data['lat']); $i++)
						{
							$this->dan_model->inserting("depot_latlng_tb",array(
								"depot_id"=>"{$id}",
								"latitude"=>"{$data['lat'][$i]}",
								"longitude" => "{$data['lng'][$i]}"
							));
						}
						audit_insert("Added ".$data['title']." depot",$id."d");
						die(json_encode(array('resp' => 'success', 'id' => $id)));
						break;
					case 'h':

						$this->dan_model->inserting("hazard_tb",array(
						"location"=>"{$data['location']}",
						"title"=>"{$data['title']}",
						"information"=>"{$data['info']}",
						"hazard_control"=>"{$data['hazard_control']}",
						"created_date"=>"{$today}",
						"created_by"=>get_session("login")["user_id"],
						"hazard_image"=> $filename,
						"status"=> "{$data['status']}",
						"start_date"=> $start,
						"end_date"=> @$end,
						"center_latitude" => "{$data['latitude']}",
						"center_longitude" => "{$data['longitude']}",
						"hazard_category"=>"{$data['category']}",
						"site_photo" => @$data['site_photo'],
						"hazard_icon"=> $icon,
						"speed_limit"=>"{$data['speed_limit']}",
						));
						$id = $this->dan_model->select_max("hazard_tb","hazard_id")->hazard_id;
						for ($i = 0; $i < count($data['lat']); $i++)
						{
							$this->dan_model->inserting("hazard_latlng_tb",array(
								"hazard_id"=>"{$id}",
								"latitude"=>"{$data['lat'][$i]}",
								"longitude" => "{$data['lng'][$i]}"
							));
						}
						audit_insert("Added ".$data['title']." hazard",$id."h");
						die(json_encode(array('resp' => 'success', 'id' => $id)));
						break;
				}
				//die('success');
			}
		$data['js_content'] = "js/add_hazard_js";
		$data['title'] = 'Add '.($type == 'h' ? 'Hazards' : ($type == 's' ? 'Sites' : ($type == 'd' ? 'Depots' : 'Unknown')));
		$data['type'] = $type;
		$data['content'] = 'add_hazard_view';	
		$this->load->view('template/main_view',$data);
	}

	public function upload_image($dir){
		if($dir == "uploads"){
			$targetFolder = '/shell/uploads'; 
			$targetResize = '/uploads/';
		} elseif($dir == "sites"){
			$targetFolder = '/shell/uploads/sites'; 
			$targetResize = '/uploads/sites/';
		} elseif($dir == "site_layout"){
			$targetFolder = '/shell/uploads/sites/sites_layout'; 
			$targetResize = '/uploads/sites/sites_layout/';
		} else{
			$targetFolder = '/shell/uploads/depots'; 
			$targetResize = '/uploads/depots/';
		}
		

		if (!empty($_FILES) && !empty($_POST['token'])) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
			
			// Validate the file type
			$fileTypes = array('jpg','jpeg','gif','png','PNG',"JPG",'JPEG','GIF'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile, $targetFile);
				rename($targetFile,preg_replace('/\s+/', '_', strtolower($targetFile)));
				$filelast = substr( preg_replace('/\s+/', '_', strtolower($targetFile)), strrpos( preg_replace('/\s+/', '_', strtolower($targetFile)), '/' )+1 );
				if($dir == "site_layout") {
					// Don't resize image
					echo json_encode(array("resp"=>"success","filename"=>$filelast));
				} else {

					$resizeThis = $_SERVER['DOCUMENT_ROOT'].$targetFolder.'/'.$filelast;
					$config['image_library'] = 'gd2';
					$config['source_image']	= $resizeThis;
					$config['width']	= 220;
					$config['height']	= 180;
					$config['create_thumb']	= TRUE;
					$this->load->library('image_lib', $config); 
					$this->image_lib->resize();
					@unlink($resizeThis);
					$temp = explode( '.', $filelast );
					$ext = array_pop( $temp );
					$name = implode( '.', $temp );
					$fileParts['extension'] = strtolower($fileParts['extension']);
					rename(rtrim($targetPath,'/') . '/' .$name."_thumb.".$fileParts['extension'],rtrim($targetPath,'/') . '/' .$name.'.'.$fileParts['extension']);
					if (!$this->image_lib->resize()){
						echo $this->image_lib->display_errors();
					}
					$this->image_lib->clear();
					@unlink($_SERVER['DOCUMENT_ROOT'].$targetFolder.'/'.$name."_thumb.".$fileParts['extension']);
					echo json_encode(array("resp"=>"success","filename"=>$name.'.'.$fileParts['extension']));
				}
			} else {
				echo '0';
			}
		}
	}

	public function hazard_list(){
		if(isset($_REQUEST['command']) == 'delete'){
			$hazard_info = $this->dan_model->select_where("hazard_tb",array("hazard_id"=>$_REQUEST['id']));
			if($hazard_info->hazard_image != "no-image.jpg"){
				unlink(FCPATH."uploads/".$hazard_info->hazard_image);	
			}
			$this->dan_model->delete_where("hazard_tb",array("hazard_id"=>$_REQUEST['id']));
			audit_insert("Deleted ".$hazard_info->title." hazard",0);
			die('success');
		}
		$data['js_content'] = "js/hazard_list_js";
		$data['title'] = 'Hazards List';
		$data['content'] = 'hazard_list_view';
		$data['hazard_list'] = $this->dan_model->selectall_orderby("hazard_tb","hazard_id");
		$data['site_list'] = $this->dan_model->selectall_orderby("site_tb","site_id");
		$data['depot_list'] = $this->dan_model->selectall_orderby("depot_tb","depot_id");
		$this->load->view('template/main_view',$data);
	}

	
	public function edit_hazard($type, $id){
		
		// Query latlng bounds of marker
		switch ($type){
			case 'h':
				$data['latlng'] = json_encode($this->dan_model->select_result("hazard_latlng_tb",array("hazard_id"=>$id)));
				$data['hazard_details'] = $this->dan_model->select_where("hazard_tb",array("hazard_id"=>$id));				
				$data['hazard_details_json'] = json_encode($data['hazard_details']);
				break;
			case 's':
				$data['latlng'] = json_encode($this->dan_model->select_result("site_latlng_tb",array("site_id"=>$id)));
				$data['hazard_details'] = $this->dan_model->select_where("site_tb",array("site_id"=>$id));
				$data['hazard_details_json'] = json_encode($data['hazard_details']);
				break;
			case 'd':
				$data['latlng'] = json_encode($this->dan_model->select_result("depot_latlng_tb",array("depot_id"=>$id)));
				$data['hazard_details'] = $this->dan_model->select_where("depot_tb",array("depot_id"=>$id));
				$data['hazard_details_json'] = json_encode($data['hazard_details']);
				break;
		}
		
		$data['type'] = $type;
		$data['id'] = $id;
		if(count($data['hazard_details']) == 0 ){
			redirect(base_url("page-not-found"));
		}
		if(isset($_REQUEST['command'])=='save'){
			$newcode = preg_replace( "/\r|\n/", " ", $_REQUEST['infodata']);
			$data = json_decode($_REQUEST['infodata'],true);
			$today = date("Y-m-d H:i:s"); 
			$start = isset($data['start_date']) && $data['status'] == 0 ? date('Y-m-d H:i:s', strtotime($data['start_date'])): 'null';
			$end = isset($data['end_date']) && $data['status'] == 0 ? date('Y-m-d H:i:s', strtotime($data['end_date'])) : 'null';
			$info = (empty($data['info']))? '' : $data['info'];
			
			switch ($type){
			case 'h':
				$this->dan_model->update_all_c("hazard_tb",
				array(
					"title"=>"{$data['title']}",
					"information"=>"{$info}",
					"hazard_image"=>$data['filename'],
					"status"=>$data['status'],
					"start_date"=> $start,
					"end_date"=>$end,
					"hazard_control"=> $data['hazard_control'],
					"speed_limit"=> $data['speed_limit'],
					"last_update_by" => get_session("login")["user_id"]
				),array("hazard_id"=>$id));
				
				// If edited bounds
				if ($data['accept'] === FALSE){
					// Delete previous bounds
					$this->dan_model->delete_where("hazard_latlng_tb", array("hazard_id" => $id));
					// Insert new bounds
					for ($i = 0; $i < count($data['lat']); $i++)
					{
						$this->dan_model->inserting("hazard_latlng_tb", array(
							"hazard_id"=>"{$id}",
							"latitude"=>"{$data['lat'][$i]}",
							"longitude" => "{$data['lng'][$i]}"
						));
					}
				}
				audit_insert("Updated ".$data['title']." hazard",$id."h");
				break;
			case 's':
				$this->dan_model->update_all_c("site_tb",
				array(
					"site_name"=>"{$data['title']}",
					"site_information"=>"{$info}",
					"site_img"=>$data['filename'],
					"site_photo"=>$data['site_filename'],
					"status"=>1,
					"start_date"=> $start,
					"end_date"=>$end,
					"last_update_by" => get_session("login")["user_id"]
				),array("site_id"=>$id));
				
				// If edited bounds
				if ($data['accept'] === FALSE){
					// Delete previous bounds
					$this->dan_model->delete_where("site_latlng_tb", array("site_id" => $id));
					// Insert new bounds
					for ($i = 0; $i < count($data['lat']); $i++)
					{
						$this->dan_model->inserting("site_latlng_tb", array(
							"site_id"=>"{$id}",
							"latitude"=>"{$data['lat'][$i]}",
							"longitude" => "{$data['lng'][$i]}"
						));
					}
				}
				audit_insert("Updated ".$data['title']." site",$id."s");
				break;
			case 'd':
				$this->dan_model->update_all_c("depot_tb",
				array(
					"depot_name"=>"{$data['title']}",
					"depot_information"=>"{$info}",
					"depot_img"=>$data['filename'],
					"status"=>1,
					"start_date"=> $start,
					"end_date"=>$end,
					"last_update_by" => get_session("login")["user_id"]
				),array("depot_id"=>$id));
				
				// If edited bounds
				if ($data['accept'] === FALSE){
					// Delete previous bounds
					$this->dan_model->delete_where("depot_latlng_tb", array("depot_id" => $id));
					// Insert new bounds
					for ($i = 0; $i < count($data['lat']); $i++)
					{
						$this->dan_model->inserting("depot_latlng_tb", array(
							"depot_id"=>"{$id}",
							"latitude"=>"{$data['lat'][$i]}",
							"longitude" => "{$data['lng'][$i]}"
						));
					}
				}
				
				audit_insert("Updated ".$data['title']." depot",$id."d");
				break;
			}			
			die('success');
		}
		
		$data['js_content'] = "js/edit_hazard_js";
		$data['title'] = 'Edit '.($type == 'h' ? 'Hazard' : ($type == 's' ? 'Site' : ($type == 'd' ? 'Depot' : 'Unknown')));
		$data['content'] = 'edit_hazard_view';	
		$this->load->view('template/main_view',$data);
	}

	public function close_hazard($id){
		$new_id = substr($id,0, -1);
		if (strrpos($id, 'h') !== false){
			$this->dan_model->update_all_c("hazard_tb",array("active"=>0),array("hazard_id"=>$new_id));
			$name = $this->dan_model->select_where("hazard_tb",array("hazard_id"=>$new_id))->title;
			audit_insert("Deactive ".$name." hazard",$new_id."h");
		}
		elseif (strrpos($id, 's') !== false){
			$this->dan_model->update_all_c("site_tb",array("active"=>0),array("site_id"=>$new_id));
			$name = $this->dan_model->select_where("site_tb",array("site_id"=>$new_id))->site_name;
			audit_insert("Deactive ".$name." site",$new_id."s");
		} else {
			$this->dan_model->update_all_c("depot_tb",array("active"=>0),array("depot_id"=>$new_id));
			$name = $this->dan_model->select_where("depot_tb",array("depot_id"=>$new_id))->depot_name;
			audit_insert("Deactive ".$name." depot",$new_id."d");
		}
		echo json_encode(array("resp"=>"success",'id'=>$new_id));
	}

	public function hazard_details($id){
	 	$det = $this->dan_model->select_where("hazard_tb",array("hazard_id"=>$id));
	 	$created_by = user_info($det->created_by,"firstname")." ".user_info($det->created_by,"lastname");
	 	@$lastUpdate = @user_info($det->last_update_by,"firstname")." ".@user_info($det->last_update_by,"lastname");
	 	echo json_encode(array("det"=>$det,"created_by"=>$created_by,"lastUpdate"=>@$lastUpdate));
	}
	
	public function site_details($id){
	 	$det = $this->dan_model->select_where("site_tb",array("site_id"=>$id));
	 	$created_by = user_info($det->created_by,"firstname")." ".user_info($det->created_by,"lastname");
	 	@$lastUpdate = @user_info($det->last_update_by,"firstname")." ".@user_info($det->last_update_by,"lastname");
	 	echo json_encode(array("det"=>$det,"created_by"=>$created_by,"lastUpdate"=>@$lastUpdate));
	}

	public function depot_details($id){
	 	$det = $this->dan_model->select_where("depot_tb",array("depot_id"=>$id));
	 	$created_by = user_info($det->created_by,"firstname")." ".user_info($det->created_by,"lastname");
	 	@$lastUpdate = @user_info($det->last_update_by,"firstname")." ".@user_info($det->last_update_by,"lastname");
	 	echo json_encode(array("det"=>$det,"created_by"=>$created_by,"lastUpdate"=>@$lastUpdate));
	}
	

//************************Hazard END******************************//



//*************************Information****************************//
	public function add_info(){
		if(isset($_REQUEST['command'])=='save'){
			$string = preg_replace("/[\r\n]+/", " ", $_REQUEST['infodata']);
			$data = json_decode($string,true);
			$today = date("Y-m-d H:i:s"); 
			$info = (empty($data['info']))? 'NULL' : $data['info']; 
			$this->dan_model->inserting("info_tb",array("location"=>$data['location'],"latitude"=>$data['lat'],"longtitude"=>$data['lng'],"title"=>$data['title'],"information"=>$info,"created_date"=>$today));
			// $this->db->query("insert into info_tb (location,latitude,longtitude,title,information,created_date) value ('{$data['location']}','{$data['lat']}','{$data['lng']}','{$data['title']}','{$info}','{$today}')");
			die('success');
		}
		$data['js_content'] = "js/add_info_js";
		$data['title'] = 'Add Information';
		$data['content'] = 'add_info';
		$this->load->view('template/main_view',$data);
		// $this->load->view('add_info');
	}
	
	public function info_list(){
		if(isset($_REQUEST['command']) == 'delete'){
			$this->dan_model->delete_where("info_tb",array("info_id"=>$_REQUEST['id']));
			die('success');
		}
		$data['info_list'] = $this->db->query("select * from info_tb")->result();
		$data['js_content'] = "js/info_list_js";
		$data['title'] = 'Information List';
		$data['content'] = 'info_list_view';
		$this->load->view('template/main_view',$data);
	}

	public function fetch_info($id){
		if(isset($_REQUEST['command']) == 'fetch')
		{
			$haha = $this->dan_model->select_where("info_tb",array("info_id"=>$id));
			print_r(json_encode($haha));
			exit;   
		}
		$data['js_content'] = "js/info_details_js";
		$data['title'] = 'Information Details';
		$data['content'] = 'info_details_view';		
		$this->load->view("template/main_view",$data);
	}

	public function get_info(){
		print_r(json_encode($this->dan_model->select_table("info_tb")));
		exit;
	}
//*************************Information END*****************************//
	public function sites(){
		$data['js_content'] = "js/site_list_js";
		$data['title'] = 'Site details';
		$data['content'] = 'site_details_view';		
		$this->load->view("template/main_view",$data);
	}

	//public function inactive(){
	//	//$this->check_permission();
	//	$this->load->view("inactive_view");
	//	$this->session->sess_destroy();
	//}

	public function notExist(){
		$this->load->view("notExists_view");
	}
	
	public function checkInactive(){
		// Check if inactive for 30 minutes (1800 seconds)
		if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 1800) {
		    echo "des";
		}
	}

	public function activeMe(){
		$_SESSION['LAST_ACTIVITY'] = time();
		echo $_SESSION['LAST_ACTIVITY'];
	}
	
	public function get_user_id(){
		if($_REQUEST['command'] == 'fetch'){
			echo json_encode(array("id" => get_session("login")['user_id'], "ip" => $_SERVER['REMOTE_ADDR']));
		}
	}

	public function logs($type){
		$id = $this->uri->segment(3);
		switch ($type) {
			case 'd':
				$data['logTitle'] =$this->dan_model->select_where("depot_tb",array("depot_id"=>$id))->depot_name." (depot)";
				break;
			case 's':
				$data['logTitle'] =$this->dan_model->select_where("site_tb",array("site_id"=>$id))->site_name." (site)";
				break;
			case 'h':
				$data['logTitle'] = $this->dan_model->select_where("hazard_tb",array("hazard_id"=>$id))->title." (hazard)";
				break;	
			case 'r':
				$data['logTitle'] = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$id))->title." (route)";
				break;		
			default:
				$data['logTitle'] = " ";
				break;
		}
		$data['logsList'] = $this->dan_model->select_result("audit_trail_tb",array("ids"=>$id.$type));
		$data['title'] = 'Logs Details';
		$data['js_content'] = "js/logs_list_js";
		$data['content'] = 'logs_list_view';		
		$this->load->view("template/main_view",$data);
	}
	
}

/* End of file google.php */
/* Location: ./application/controllers/google.php */