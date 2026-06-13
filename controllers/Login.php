<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
        $this->load->model('profiles');
    }

	public function index()
    {
	    if(isset($_SESSION['logged_in'])){
			redirect('dashboard');
		}
		else{
			$this->load->view('login/login');
		}
	}
	
	public function check_user()
	{
		$username = $this->input->post('username');
		$password = md5($this->input->post('password'));
		
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('username'=>$username, 'password'=>$password, 'status'=>'1'));
		$query = $this->db->get()->result_array();
		
		if(count($query)>0):
          $this->session->set_userdata(array('user_id'=> $query[0]['user_id'],'name'=> $query[0]['first_name'].' '.$query[0]['last_name'], 'username'=> $username,'designation_id'=> $query[0]['designation_id'], 'role'=> $query[0]['role'],'cnic'=> $query[0]['cnic'], 'type'=> $query[0]['type'], 'user_campus_id'=>$query[0]['campus_id'],'logged_in' => TRUE));

		  //SEND LOGIN SMS
			//$authToken = 'uWFecSsyJhmJySmofndKnqFqikns1NDxJda4';
			$deviceID  = @$this->db->get_where('sms_gateway', array('campus_id'=>$query[0]['campus_id'],'status'=>'active'))->row()->id;

			if($deviceID!=NULL)
            {
                // The data to send to the API
                $campus_detail = $this->db->get_where('campuses', array('campus_id'=>$query[0]['campus_id']))->result_array();

                $this->db->set('number', $query[0]['mobile']);
                $this->db->set('message', 'Dear '.$query[0]['first_name'].' '.$query[0]['last_name'].' You have successfully logged in to '.$campus_detail[0]['campus_name'].' on '.date('d-m-Y H:i:s').'. For further information please contact on '.$campus_detail[0]['phone4'].'');
                $this->db->set('status', '');
                $this->db->set('date', date('Y-m-d H:i:s'));
                $this->db->set('chk', 0);
                $this->db->set('add_by', 'System');
                $this->db->set('device_id', $deviceID);
                $this->db->insert('sms');
            }

			echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
			echo '<script>
			
			function getLocation() {
				if (navigator.geolocation) 
				{
					navigator.geolocation.getCurrentPosition(showPosition);
				} 
				else 
				{ 
					x.innerHTML = "Geolocation is not supported by this browser.";
				}
			}
			
			function showPosition(position) 
			{
				//alert("https://maps.google.com/?q="+position.coords.latitude+","+position.coords.longitude+"");
				
				$.ajax({
						  type: "post",
						  async: false,
						  url: "'.site_url().'/login/location",
						  data: { 
								lat : position.coords.latitude,
								lon : position.coords.longitude
							 },						  
						  success: function(data) {
							  window.location.replace(data);
						  }
						  
				});
			}
			
			getLocation();
			</script>';

			//redirect('dashboard');

		else:
			$this->session->set_flashdata('error', 'Username or password is invalid!');
			redirect('login');
		endif;
	}
	
	public function location()
	{
		$lat = $this->input->post('lat');
		$lon = $this->input->post('lon');
		$datetime = date('Y-m-d H:i:s');
		$url = 'https://maps.google.com/?q='.$lat.','.$lon;
		$user_id = $this->session->userdata('user_id');
		$this->db->set('date',$datetime);
		$this->db->set('user_id',$user_id);
		$this->db->set('url',$url);
		$this->db->insert('locations');
		
		echo site_url().'/dashboard';
	}

    public function forgot_pass()
    {
        if(@$this->input->post('email'))
        {
            $email=$this->input->post('email');
            $que=$this->db->query("select user_id,password,email from users where email='$email'");
            $row=$que->row();
            if ($row != null) {
                $user_email = $row->email;
                $user_id = $row->user_id;
                if ((!strcmp($email, $user_email))) {
                    $pass = $row->password;
                    /*Mail Code*/
                    $to = $user_email;
                    $subject = "Password";
                    $txt = 'Click on this link to reset your password. ' . site_url() . '/login/reset_password/' . $pass . '/' . $user_id . '"';
                    $headers = "From: info@shahbazcollegeofpharmacy.edu.pk" . "\r\n" .
                        "CC: xeroraja@gmail.com";

                    mail($to, $subject, $txt, $headers);

                } else {
                    $this->session->set_flashdata('error', 'Username or Email is invalid!');
                    redirect('login');
                }
            }
            else {
                $this->session->set_flashdata('error', 'Username or Email is Not Found!');
                redirect('login');
            }
            $this->session->set_flashdata('message', 'Reset password Link has been sent to your Registered Email Address');
            redirect('login');
        }

    }
    
    public function reset_password($password,$user_id)
    {
        $data['users'] = $this->getCurrentUser($user_id);

        if ($password == $data['users'][0]['password']) {

            $this->load->view('profile/user_update.php', $data);
        }else
            show_404();
    }
    
    public function getCurrentUser($user_id)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where(array('user_id'=>$user_id));
        $query = $this->db->get()->result_array();
        return $query;
    }
    
    public function update()
    {
        $user_id = $this->input->post('user_id');
        $currentusers = $this->getCurrentUser($user_id);
        if($this->input->post('password') == $this->input->post('r-password'))
        {
            $password = md5($this->input->post('password'));
            $this->db->set('password', $password);
            $this->db->where('user_id', $user_id);
            $this->db->update('users');

            $this->session->set_flashdata('message', 'Your profile update successfully!');
            redirect('login');
        }
        else
        {
            $this->session->set_flashdata('error', 'Your password and retype password didn\'t match!');
            redirect('login/reset_password/'.$currentusers[0]['password'].'/'.$currentusers[0]['user_id']);
        }

    }

}