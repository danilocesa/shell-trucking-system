<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('Asia/Manila');
		$this->check_permission();
	}

	public function check_permission(){
		$this->load->library('user_agent');
		$ip_address = $this->dan_model->select_where("user_session",array("ip_address"=>$this->input->ip_address(),"user_agent"=>$this->agent->agent_string()));
		if(empty($ip_address->user_data)){
			$this->dan_model->delete_where("user_session",array("ip_address"=>$this->input->ip_address(),"user_agent"=>$this->agent->agent_string()));
			$this->session->set_flashdata("logged","no");			
			redirect(base_url());
		}

		if($this->session->userdata('login')["logged_in"] == FALSE){
			redirect(base_url());
		}

	}

//*********************Directions**********************//
	public function index(){
		if(get_user_region(get_session("login")['user_region']) == "Luzon"){
			$luzon_region = array("NCR","CAR","Rehiyong Ilocos","Lambak ng Cagayan","Gitnang Luzon","Calabarzon","MIMAROPA","Kabikulan","Ilocos Region","Cagayan Valley","Central Luzon","CALABARZON","Bicol Region");
			$site_region = $this->dan_model->select_where_in("site_tb","site_region",$luzon_region);
			$depot_region = $this->dan_model->select_where_in("depot_tb","depot_region",$luzon_region);
		} elseif(get_user_region(get_session("login")['user_region']) == "Visayas"){
			$visayas_region = array("Kanlurang Kabisayaan","Gitnang Visayas","Silangang Visayas","Western Visayas","Central Visayas","Eastern Visayas");
			$site_region = $this->dan_model->select_where_in("site_tb","site_region",$visayas_region);
			$depot_region = $this->dan_model->select_where_in("depot_tb","depot_region",$visayas_region);
		} else{
			$mindanao_region = array("Tangway ng Kasambuwangaan","Hilagang Mindanao","Rehiyon ng Davao","Caraga","ARMM","Zamboanga Peninsula","Northern Mindanao","Davao Region","SOCCSKSARGEN");
			$site_region = $this->dan_model->select_where_in("site_tb","site_region",$mindanao_region);
			$depot_region = $this->dan_model->select_where_in("depot_tb","depot_region",$mindanao_region);
		}
		$data['sites'] = $site_region;
		$data['depots'] = $depot_region;
		$data['title'] = "Add routes";
		$data['js_content'] = "js/add_route_js";
		$data['content'] = "simple_view";
		$this->load->view('template/main_view',$data);	
	}

	public function save_points(){
		if($_REQUEST['command']=='save')
		{
			$data = json_decode($_REQUEST['mapdata'],true);
			$new = str_replace("null,","",$data);
			$this->dan_model->inserting("waypoint_tb",
				array(
					"start"=>$data['location']['start_loc'],
					"end"=>$data['location']['last_loc'],
					"midpoints"=>json_encode($data['midpoints']),
					"title"=>$data['info']['title'],
					"info"=>$data['info']['information'],
					"created_date"=>date("Y-m-d H:i:s"),
					"route_json"=>json_encode($data),
					"ship_to"=>$data['info']['ship_to']));
			audit_insert("Created ".$data['info']['title']." route");
			echo json_encode(array("resp"=>"success","route_id"=>$this->db->insert_id()));
		}
	}

	public function edit_route($id){
		if($_REQUEST['command']=='edit'){
			$data = json_decode($_REQUEST['mapdata'],true);
			$new = str_replace("null,","",$data);
			$this->dan_model->update_all_c("waypoint_tb",
				array(
					"start"=>$data['location']['start_loc'],
					"end"=>$data['location']['last_loc'],
					"midpoints"=>json_encode($data['midpoints']),
					"title"=>$data['info']['title'],
					"info"=>$data['info']['information'],
					"created_date"=>date("Y-m-d H:i:s"),
					"route_json"=>json_encode($data),
					"ship_to"=>$data['info']['ship_to']
				),array("route_id"=>$id));	
			audit_insert("Update ".$data['info']['title']." route");
			echo json_encode(array("resp"=>"success"));
		}	
	}
	
	public function routes(){
		if(isset($_REQUEST['command']) == 'delete'){
			$route_info = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$_REQUEST['id']));
			$this->dan_model->delete_where("waypoint_tb",array("route_id"=>$_REQUEST['id']));
			audit_insert("Deleted ".$route_info->title." route");
			die('success');
		}
		$data['routes_list'] = $this->dan_model->select_table("waypoint_tb");
		$data['js_content'] = "js/route_list_js";
		$data['title'] = 'Routes list';
		$data['content'] = 'routes_list_view';
		$this->load->view('template/main_view',$data);	
	}
	public function fetch_waypoints($id){
		if(isset($_REQUEST['command']) == 'fetch')
		{
			$haha = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$id));
			print_r(json_encode($haha));
			exit;
		}
		$data['js_content'] = "js/route_details_js";
		$data['title'] = 'Routes details';
		$data['content'] = 'routes_details_view';		
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
			$this->dan_model->update_all_c("waypoint_tb",array("start"=>$data['location']['start_loc'],"end"=>$data['location']['last_loc'],"midpoints"=>json_encode($data['midpoints']),"title"=>$data['info']['title'],"info"=>$data['info']['information'],"created_date"=>date("Y-m-d H:i:s"),"route_json"=>json_encode($data)),array("route_id"=>$id));
			audit_insert("Update ".$data['info']['title']." route");
			die('success');
		}
		$data['js_content'] = "js/edit_route_js";
		$data['title'] = 'Edit route';
		$data['content'] = 'edit_route_view';		
		$this->load->view("template/main_view",$data);
	}
	
	public function fetch_hazard(){
		if(isset($_REQUEST['command']) == 'fetch')
		{
			$this->db->query("UPDATE hazard_tb SET active = CASE WHEN status = 0 and NOW() >= start_date and NOW() <= end_date THEN 1 WHEN status = 1 THEN 1 ELSE 0 END");
			$wew = $this->db->query("SELECT * from (SELECT CONCAT(a.hazard_id,'h') hazard_id,title, hazard_image, hazard_icon, active, information, location,  center_latitude,center_longitude, status, group_concat(b.latitude SEPARATOR '|') latitude, group_concat(b.longitude SEPARATOR '|') longitude FROM hazard_tb a LEFT JOIN hazard_latlng_tb b ON a.hazard_id = b.hazard_id GROUP BY hazard_id UNION SELECT CONCAT(c.site_id,'s') site_id, site_name, site_img, hazard_icon, active, site_information, site_location, center_latitude,center_longitude, status, group_concat(d.latitude SEPARATOR '|') latitude, group_concat(d.longitude SEPARATOR '|') longitude from site_tb c LEFT JOIN site_latlng_tb d ON c.site_id = d.site_id GROUP BY site_id UNION SELECT CONCAT(e.depot_id,'d')depot_id, depot_name, depot_img, hazard_icon, active, depot_information, depot_location, center_latitude,center_longitude, status, group_concat(f.latitude SEPARATOR '|') latitude, group_concat(f.longitude SEPARATOR '|') longitude from depot_tb e LEFT JOIN depot_latlng_tb f ON e.depot_id = f.depot_id GROUP BY depot_id ) a where active = 1")->result();
			print_r(json_encode($wew));
			exit;
		}
	}
	public function fetch_near(){
		if(isset($_REQUEST['trigger']) == 'fetch')
		{
			print_r(json_encode($this->dan_model->select_result("hazard_tb",array("route_id"=>$this->uri->segment(3)))));
			exit;
		}
	
	}

	public function print_route($id){
		$data['hsz_dst'] = $this->input->post("hzds_dst");
		$data['img'] = $this->input->post('hzds');
		$data['screen_array'] = $this->input->post('screens_id');
		$data['origin'] = $this->input->post("orgn");
		$data['destination'] = $this->input->post("dest");
		$data['directions'] = $this->input->post("directions");
		$this->load->view("print_hazards",$data);	
	}

	public function saveimg(){
		if (isset($_POST['url'])){
			$image = file_get_contents($_POST['url']);
			write_file(FCPATH."screenie/map/image.jpg", $image); //Where to save the image on your server
		}
	}

	public function save_img_google(){
		//if (isset($_POST["image"]) && !empty($_POST["image"])) {
			$dataURL = $this->input->post("image");  
			$replace = str_replace("[removed]", "", $dataURL);
			$data = base64_decode($replace);  
			$fp = fopen(FCPATH.'screenie/'.$_POST['filename'].'.png', 'w');  
			fwrite($fp, $data);  
			fclose($fp);  
		//}
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



//**************************Directions END*************************//	



//************************Hazard**********************************//
	public function add_hazard(){
		$id = $this->uri->segment(3);
		//if($id == NULL){
			if(isset($_REQUEST['command'])=='save'){
				$newcode = preg_replace( "/\r|\n/", " ", $_REQUEST['infodata']);
				$data = json_decode(trim($newcode),true);
				$today = date("Y-m-d H:i:s"); 
				//$info = (empty($data['info']))? 'NULL' : $data['info'];
				//$hazard_control = (empty($data['hazard_control']))? 'NULL' : $data['hazard_control'];
				$date = date('Y-m-d H:i:s', strtotime($data['start_date']));
				$filename = $data['filename'] == "" ? "no-image.jpg" :$data['filename'];
				$start = isset($data['start_date']) && $data['status'] == 0 ? date('Y-m-d H:i:s', strtotime($data['start_date'])): 'null';
				$end = isset($data['end_date']) && $data['status'] == 0 ? date('Y-m-d H:i:s', strtotime($data['end_date'])) : 'null';
				$icon = $data['hazard_icon'] == "" ? "other.png" :$data['hazard_icon'];
				if(get_user_region($data['site_region']) != get_user_region(get_session("login")['user_region'])){
					die("false_region");
				}
				if($data['site_category'] == 1){
					$this->dan_model->inserting("site_tb",array(
						"site_name"=>"{$data['title']}",
						"site_img"=>$filename,
						"site_photo"=>"{$data['site_photo']}",
						"hazard_icon"=>$icon,
						"created_date"=>"{$today}",
						"status"=> "{$data['status']}",
						"site_location"=>"{$data['location']}",
						"site_region" => "{$data['site_region']}",
						"site_information"=>"{$data['info']}",
						"center_latitude" => "{$data['latitude']}",
						"center_longitude" => "{$data['longitude']}",
						"created_by"=> user_info(get_session("login")["user_id"],"firstname"),
						"active"=> 1
						));
						$id = $this->db->insert_id();
						for ($i = 0; $i < count($data['lat']); $i++)
						{
							$this->dan_model->inserting("site_latlng_tb",array(
								"site_id"=>"{$id}",
								"latitude"=>"{$data['lat'][$i]}",
								"longitude" => "{$data['lng'][$i]}"
							));
						}
					audit_insert("Added ".$data['title']." site");
				} elseif($data['site_category'] == 2){
					$this->dan_model->inserting("depot_tb",array(
						"depot_name"=>"{$data['title']}",
						"depot_photo"=>"{$data['site_photo']}",
						"hazard_icon"=>$icon,
						"depot_img"=>$filename,
						"created_date"=>"{$today}",
						"status"=> "{$data['status']}",
						"depot_location"=>"{$data['location']}",
						"depot_region" => "{$data['site_region']}",
						"depot_information"=>"{$data['info']}",
						"center_latitude" => "{$data['latitude']}",
						"center_longitude" => "{$data['longitude']}",
						"created_by"=> user_info(get_session("login")["user_id"],"firstname"),
						"active"=> 1
						));
						$id = $this->db->insert_id();
						for ($i = 0; $i < count($data['lat']); $i++)
						{
							$this->dan_model->inserting("depot_latlng_tb",array(
								"depot_id"=>"{$id}",
								"latitude"=>"{$data['lat'][$i]}",
								"longitude" => "{$data['lng'][$i]}"
							));
						}
					audit_insert("Added ".$data['title']." depot");
				} else{
					$this->dan_model->inserting("hazard_tb",array(
						"location"=>"{$data['location']}",
						"title"=>"{$data['title']}",
						"information"=>"{$data['info']}",
						"hazard_control"=>"{$data['hazard_control']}",
						"created_date"=>"{$today}",
						"hazard_image"=> $filename,
						"status"=> "{$data['status']}",
						"start_date"=> $start,
						"end_date"=> $end,
						"center_latitude" => "{$data['latitude']}",
						"center_longitude" => "{$data['longitude']}",
						"hazard_category"=>"{$data['category']}",
						"site_photo" => "{$data['site_photo']}",
						"hazard_icon"=> $icon
						));
						$id = $this->db->insert_id();
						//$get_id = $this->dan_model->select_where("hazard_tb",array());
						for ($i = 0; $i < count($data['lat']); $i++)
						{
							$this->dan_model->inserting("hazard_latlng_tb",array(
								"hazard_id"=>"{$id}",
								"latitude"=>"{$data['lat'][$i]}",
								"longitude" => "{$data['lng'][$i]}"
							));
						}
					audit_insert("Added ".$data['title']." hazard");
				}
				die('success');
			}
			$data['js_content'] = "js/add_hazard_js";
			
		//} else{
			// if(isset($_REQUEST['command']) == 'fetch')
			// {
			// 	$haha = $this->dan_model->select_where("waypoint_tb",array("route_id"=>$id));
			// 	print_r(json_encode($haha));
			// 	exit;   
			// }
			// if(isset($_REQUEST['trigger'])=='save'){
			// 	$string = preg_replace("/[\r\n]+/", " ", $_REQUEST['infodata']);
			// 	$data = json_decode($string,true);
			// 	$today = date("Y-m-d H:i:s"); 
			// 	$info = (empty($data['info']))? 'NULL' : $data['info']; 
			// 	$this->dan_model->inserting("hazard_tb",array("location"=>"{$data['location']}","latitude"=>"{$data['lat']}","longtitude"=>"{$data['lng']}","title"=>"{$data['title']}","information"=>"{$info}","created_date"=>"{$today}","hazard_image"=>$data['filename'],"route_id"=>$id));
			// 	audit_insert("Added ".$data['title']." hazard");
			// 	die('success');
			// }
			// $data['js_content'] = "js/add_hazard_route_js";
		//}

		$data['title'] = 'Add Hazard';
		$data['content'] = 'add_hazard_view';	
		$this->load->view('template/main_view',$data);
	}

	public function upload_image($dir){
		if($dir == "uploads"){
			$targetFolder = '/shell/uploads'; 
		} elseif($dir == "sites"){
			$targetFolder = '/shell/uploads/sites'; 
		} elseif($dir == "site_layout"){
			$targetFolder = '/shell/uploads/sites/sites_layout'; 
		} else{
			$targetFolder = '/shell/uploads/depots'; 
		}
		

		if (!empty($_FILES) && !empty($_POST['token'])) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
			
			// Validate the file type
			$fileTypes = array('jpg','jpeg','gif','png','PNG',"JPG",'JPEG','GIF'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				echo json_encode(array("resp"=>"success","filename"=>$_FILES['Filedata']['name']));
			} else {
				echo '0';
			}
		}
	}

	public function hazard_list(){
		if(isset($_REQUEST['command']) == 'delete'){
			$hazard_info = $this->dan_model->select_where("hazard_tb",array("hazard_id"=>$_REQUEST['id']));
			$this->dan_model->delete_where("hazard_tb",array("hazard_id"=>$_REQUEST['id']));
			audit_insert("Deleted ".$hazard_info->title." hazard");
			die('success');
		}
		$data['js_content'] = "js/hazard_list_js";
		$data['title'] = 'Hazard List';
		$data['content'] = 'hazard_list_view';
		$data['hazard_list'] = $this->dan_model->selectall_orderby("hazard_tb","hazard_id");
		$data['site_list'] = $this->dan_model->selectall_orderby("site_tb","site_id");
		$data['depot_list'] = $this->dan_model->selectall_orderby("depot_tb","depot_id");
		$this->load->view('template/main_view',$data);
	}

	public function edit_hazard($id){
		if(isset($_REQUEST['command'])=='save'){
			$newcode = preg_replace( "/\r|\n/", " ", $_REQUEST['infodata']);
			$data = json_decode($_REQUEST['infodata'],true);
			$today = date("Y-m-d H:i:s"); 
			$start = isset($data['start_date']) && $data['status'] == 0 ? date('Y-m-d H:i:s', strtotime($data['start_date'])): 'null';
			$end = isset($data['end_date']) && $data['status'] == 0 ? date('Y-m-d H:i:s', strtotime($data['end_date'])) : 'null';
			$info = (empty($data['info']))? 'NULL' : $data['info']; 
			// dump_exit(array(
			// 		"title"=>"{$data['title']}",
			// 		"information"=>"{$info}",
			// 		"hazard_image"=>$data['filename'],
			// 		"status"=>$data['status'],
			// 		"start_date"=> $start,
			// 		"end_date"=>$end,
			// 		"hazard_control"=> $data['hazard_control']
			// 	));
			$this->dan_model->update_all_c("hazard_tb",
				array(
					"title"=>"{$data['title']}",
					"information"=>"{$info}",
					"hazard_image"=>$data['filename'],
					"status"=>$data['status'],
					"start_date"=> $start,
					"end_date"=>$end,
					"hazard_control"=> $data['hazard_control'],
					"last_update_by" => user_info(get_session("login")["user_id"],"firstname")
				),array("hazard_id"=>$id));

			audit_insert("Update ".$data['title']." hazard");
			die('success');
		}
		$data['hazard_details'] = $this->dan_model->select_where("hazard_tb",array("hazard_id"=>$id));
		$data['js_content'] = "js/edit_hazard_js";
		$data['title'] = 'Edit Hazard';
		$data['content'] = 'edit_hazard_view';	
		$this->load->view('template/main_view',$data);
	}

	public function close_hazard($id){
		$check = $this->dan_model->update_all_c("hazard_tb",array("closed_hazard"=>1),array("hazard_id"=>$id));
		echo json_encode(array("resp"=>"success"));
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
	
}

/* End of file google.php */
/* Location: ./application/controllers/google.php */