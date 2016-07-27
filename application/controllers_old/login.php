<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

//*********************Login**********************//
	public function index()
	{
		if($this->session->userdata("login")['logged_in'] == TRUE){
			redirect(base_url("google/routes"));
		}
		if(isset($_POST['login'])){
			$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 'required|xss_clean');
			$user_info = $this->dan_model->select_where("user_tb",array("email"=>$this->input->post("email"),"password"=>$this->input->post("password")));
			if ($this->form_validation->run() == TRUE){
				if(count($user_info) > 0){
					$database_session = $this->db->query("SELECT * FROM user_session where user_data like '%user_id%{$user_info->user_id}%'")->row();
					if(count($database_session) > 0){
						$this->dan_model->delete_where("user_session",array("session_id"=>$database_session->session_id));
					}
					$userdata = array(
						'user_id' => $user_info->user_id,
						'email' => $this->input->post('email'),
						'logged_in' => TRUE
					);
					$this->session->set_userdata('login',$userdata);
					redirect(base_url('google/routes'));
					
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
		$this->session->sess_destroy();
		redirect(base_url());
	}
	

	public function users_list(){
		$data['users_list'] = $this->dan_model->select_table("user_tb");
		$data['js_content'] = "js/users_list_js";
		$data['title'] = 'Users List';
		$data['content'] = 'user_list_view';
		$this->load->view('template/main_view',$data);	
	}
	

	public function add_user(){

		$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email');
		$this->form_validation->set_rules('fname', 'Firstname', 'required|min_length[3]');
		$this->form_validation->set_rules('lname', 'Lastname', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_rules('cpass', 'Confirm Password', 'required|matches[password]');
		if ($this->form_validation->run() == TRUE ){
			$this->dan_model->inserting("user_tb",array("email"=>$this->input->post("email"),"firstname"=>$this->input->post("fname"),"lastname"=>$this->input->post("lname"),"password"=>$this->input->post("password"),"location"=>$this->input->post("location"),"latitude"=>$this->input->post("lat"),"longtitude"=>$this->input->post("lng")));
			audit_insert("Added ".$this->input->post("fname")." ".$this->input->post("lname")." user");
			echo json_encode(array("success"=>true));
			exit;
		} else {
			echo json_encode(array("err"=>validation_errors()));
			exit;
		}

		$data['js_content'] = NULL;
		$data['title'] = 'Add user';
		$data['content'] = 'add_user_view';
		$this->load->view('template/main_view',$data);	
	}


	public function audit_trail(){
		$data['audit_list'] = $this->dan_model->selectall_orderby("audit_trail_tb","date");
		$data['js_content'] = "js/users_list_js";
		$data['title'] = 'Audit Trail';
		$data['content'] = 'audit_list_view';
		$this->load->view('template/main_view',$data);	
	}
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */