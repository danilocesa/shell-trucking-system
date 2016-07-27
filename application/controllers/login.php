<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('Asia/Manila');
	}
//*********************Login**********************//
	public function index()
	{
		if($this->nativesession->userdata("login")['logged_in'] == TRUE){
			redirect(base_url("routes-list"));
		}

		if(isset($_POST['login'])){
			$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 'required|xss_clean');
			$user_info = $this->dan_model->select_where("user_tb",array("email"=>$this->input->post("email"),"password"=>$this->input->post("password")));
			if ($this->form_validation->run() == TRUE){
				if(count($user_info) > 0){
					$str_len = strlen($user_info->user_id);
					$user_id = $user_info->user_id;
					$con = '%s:7:"user_id";s:'.$str_len.':"'.$user_id.'"%';
					$check_session = $this->db->query("SELECT * FROM user_session WHERE user_data LIKE '$con'")->row();
					//$database_session = $this->db->query("SELECT * FROM user_session where user_data like '%user_id%{$user_info->user_id}%'")->row();
					if(count($check_session) > 0){
						$this->dan_model->delete_where("user_session",array("session_id"=>$check_session->session_id));
					}
					$userdata = array(
						'user_id' => $user_info->user_id,
						'email' => $this->input->post('email'),
						'user_region' =>$user_info->user_region,
						'access_level' => $user_info->access_level,
						'logged_in' => TRUE
					);
					$this->nativesession->set_userdata('login',$userdata);
					// Add audit trail log
					audit_insert("Logged in from IP ".$_SERVER['REMOTE_ADDR'],0);
					redirect(base_url('routes-list?check=true&token='.substr(md5(rand()), 0, 7)));
					
				} else {
					$data['invalid_cred'] = TRUE;
					//echo json_encode(array("resp"=>"invalid"));
				}
			}
		}
		$data['title'] = "Login";
		$this->load->view("login_view",$data);
	}
	
//*************************Login END*****************************//
	public function logout(){
		$this->load->library('user_agent');
		$this->dan_model->delete_where("user_session",array("ip_address"=>$this->input->ip_address(),"user_agent"=>$this->agent->agent_string()));
		$this->nativesession->ses_destroy();
		redirect(base_url());
	}
	

	public function users_list(){
		if($this->nativesession->userdata("login")['logged_in'] == FALSE){
			redirect(base_url());
		}
		$data['users_list'] = $this->dan_model->select_table("user_tb");
		$data['js_content'] = "js/users_list_js";
		$data['title'] = 'Users List';
		$data['content'] = 'user_list_view';
		$this->load->view('template/main_view',$data);	
	}
	

	public function add_user(){
    	$check = $this->dan_model->select_where("user_tb",array("email"=>$this->input->post("email")));
    	if(count($check) >= 1){
    		echo json_encode(array("dupe_email"=>true));
    		exit;
    	} else{
    		$de_depot = $this->dan_model->select_where("depot_tb",array("depot_id"=>$this->input->post("addlocation")));
			$this->dan_model->inserting("user_tb",
				array(
					"email"=>$this->input->post("email"),
					"firstname"=>$this->input->post("fname"),
					"lastname"=>$this->input->post("lname"),
					"password"=>$this->input->post("password"),
					"latitude"=>$de_depot->center_latitude,
					"longtitude"=>$de_depot->center_longitude,
					"location"=>$de_depot->depot_id,
					"question"=>$this->input->post("question"),
					"answer"=>$this->input->post("answer"),
					"access_level" => $this->input->post('user_level')
				));
			audit_insert("Added ".$this->input->post("fname")." ".$this->input->post("lname")." user",0);
			echo json_encode(array("success"=>true));
			exit;
		}
		$data['js_content'] = NULL;
		$data['title'] = 'Add user';
		$data['content'] = 'add_user_view';
		$this->load->view('template/main_view',$data);	
	}

	public function update_user(){
    	$check = $this->dan_model->select_where("user_tb",array("email"=>$this->input->post("email")));
    	if(count($check) >= 1 && $this->input->post("email") != get_session("login")["email"]){
    		echo json_encode(array("dupe_email"=>true));
    		exit;
    	} else{
			$this->dan_model->update_all_c("user_tb",
				array(
					"email"=>$this->input->post("email"),
					"firstname"=>$this->input->post("fname"),
					"lastname"=>$this->input->post("lname"),
					"password"=>$this->input->post("password"),
					"latitude"=>$this->input->post("lat"),
					"longtitude"=>$this->input->post("lng"),
					"location"=>$this->input->post("location"),
					"question"=>$this->input->post("question"),
					"answer"=>$this->input->post("answer")
				),array("user_id"=>get_session("login")["user_id"]));
			audit_insert("Update ".$this->input->post("fname")." ".$this->input->post("lname")." user",0);
			echo json_encode(array("success"=>true));
			exit;
		}
		$data['js_content'] = NULL;
		$data['title'] = 'Add user';
		$data['content'] = 'add_user_view';
		$this->load->view('template/main_view',$data);	
	}

	public function audit_trail(){
		if($this->nativesession->userdata("login")['logged_in'] == FALSE){
			redirect(base_url());
		}
		$data['audit_list'] = $this->dan_model->selectall_orderby("audit_trail_tb","date");
		$data['js_content'] = "js/users_list_js";
		$data['title'] = 'Audit Trail';
		$data['content'] = 'audit_list_view';
		$this->load->view('template/main_view',$data);	
	}

	public function forgot_pass(){
		$check = $this->dan_model->select_where("user_tb",array("email"=>$this->input->post("forgotemail")));		
    	if(count($check) > 0){
    		$this->nativesession->set_userdata('emailforgot',array("emailforgot"=>$this->input->post("forgotemail")));
    		echo json_encode(array("emailExist"=>true));
    		exit;
    	} else{
    		echo json_encode(array("emailNotexist"=>true));
    		exit;
    	}	
	}

	public function check_pass(){
		$check = $this->dan_model->select_where("user_tb",array("email"=>$this->nativesession->userdata("emailforgot")['emailforgot']));	
		if($check->question == $this->input->post("question") && $check->answer == $this->input->post("answer")){
			echo json_encode(array("success"=>true));
		} else{
			echo json_encode(array("err"=>true));
		}
	}

	public function new_password(){
		$check = $this->dan_model->update_all_c("user_tb",array("password"=>$this->input->post("new_pass")),array("email"=>$this->nativesession->userdata("emailforgot")['emailforgot']));	
		$this->nativesession->unset_userdata("forgot");
		echo json_encode(array("success"=>true));

	}

	public function inactive(){
		//$this->session->set_userdata("inactive_user",array("true"));
		$this->load->view("inactive_view");
		$this->nativesession->ses_destroy();
	}
	
	public function another_user(){
		//$this->session->set_userdata("inactive_user",array("true"));
		$this->load->view("another_user_view");
		$this->nativesession->ses_destroy();
	}
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */