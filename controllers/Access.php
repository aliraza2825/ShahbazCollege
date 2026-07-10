<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Access extends CI_Controller {
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('accesses');
	}
	
	public function index()
	{	
	    
	    if($this->session->userdata('role')!='Admin')
	        redirect()->back();
	    
		$data['users'] = $this->accesses->getUsers();
		$data['classes'] = $this->accesses->getClasses();
		$data['campuses'] = $this->accesses->getCampuses();
		$data['subjects'] = $this->accesses->getTestEngineSubjects();
		$data['assignment_subjects'] = $this->accesses->getAssignmentsSubjects();
		$data['departments'] = $this->accesses->getDepartments();
		$data['online_admission_campus_ids'] = array();
		
		if($this->input->post('campus_user_id'))
		{
			
			$data['access_values'] = $this->db->get_where('access', array('user_id'=>$this->input->post('campus_user_id')))->result_array();
			
			
			$data['cities'] = array(
								'Islamabad',
								'Ahmed Nager Chatha',
								'Ahmadpur East',
								'Ali Khan Abad',
								'Alipur',
								'Arifwala',
								'Attock',
								'Bhera',
								'Bhalwal',
								'Bahawalnagar',
								'Bahawalpur',
								'Bhakkar',
								'Burewala',
								'Chillianwala',
								'Chakwal',
								'Chichawatni',
								'Chiniot',
								'Chishtian',
								'Daska',
								'Darya Khan',
								'Dera Ghazi Khan',
								'Dhaular',
								'Dina',
								'Dinga',
								'Dipalpur',
								'Faisalabad',
								'Fateh Jhang',
								'Ghakhar Mandi',
								'Gojra',
								'Gujranwala',
								'Gujrat',
								'Gujar Khan',
								'Hafizabad',
								'Haroonabad',
								'Hasilpur',
								'Haveli',
								'Lakha',
								'Jalalpur',
								'Jattan',
								'Jampur',
								'Jaranwala',
								'Jhang',
								'Jhelum',
								'Kalabagh',
								'Karor Lal Esan',
								'Kasur',
								'Kamalia',
								'Kamoke',
								'Khanewal',
								'Khanpur',
								'Kharian',
								'Khushab',
								'Kot Adu',
								'Jauharabad',
								'Lahore',
								'Lalamusa',
								'Layyah',
								'Liaquat Pur',
								'Lodhran',
								'Malakwal',
								'Mamoori',
								'Mailsi',
								'Mandi Bahauddin',
								'mian Channu',
								'Mianwali',
								'Multan',
								'Murree',
								'Muridke',
								'Mianwali Bangla',
								'Muzaffargarh',
								'Narowal',
								'Okara',
								'Renala Khurd',
								'Pakpattan',
								'Pattoki',
								'Pir Mahal',
								'Qaimpur',
								'Qila Didar Singh',
								'Rabwah',
								'Raiwind',
								'Rajanpur',
								'Rahim Yar Khan',
								'Rawalpindi',
								'Sadiqabad',
								'Safdarabad',
								'Sahiwal',
								'Sangla Hill',
								'Sarai Alamgir',
								'Sargodha',
								'Shakargarh',
								'Sheikhupura',
								'Sialkot',
								'Sohawa',
								'Soianwala',
								'Siranwali',
								'Talagang',
								'Taxila',
								'Toba Tek Singh',
								'Vehari',
								'Wah Cantonment',
								'Wazirabad',
								'Badin',
								'Bhirkan',
								'Rajo Khanani',
								'Chak',
								'Dadu',
								'Digri',
								'Diplo',
								'Dokri',
								'Ghotki',
								'Haala',
								'Hyderabad',
								'Islamkot',
								'Jacobabad',
								'Jamshoro',
								'Jungshahi',
								'Kandhkot',
								'Kandiaro',
								'Karachi',
								'Kashmore',
								'Keti Bandar',
								'Khairpur',
								'Kotri',
								'Larkana',
								'Matiari',
								'Mehar',
								'Mirpur Khas',
								'Mithani',
								'Mithi',
								'Mehrabpur',
								'Moro',
								'Nagarparkar',
								'Naudero',
								'Naushahro Feroze',
								'Naushara',
								'Nawabshah',
								'Nazimabad',
								'Qambar',
								'Qasimabad',
								'Ranipur',
								'Ratodero',
								'Rohri',
								'Sakrand',
								'Sanghar',
								'Shahbandar',
								'Shahdadkot',
								'Shahdadpur',
								'Shahpur Chakar',
								'Shikarpaur',
								'Sukkur',
								'Tangwani',
								'Tando Adam Khan',
								'Tando Allahyar',
								'Tando Muhammad Khan',
								'Thatta',
								'Umerkot',
								'Warah',
								'Abbottabad',
								'Adezai',
								'Alpuri',
								'Akora Khattak',
								'Ayubia',
								'Banda Daud Shah',
								'Bannu',
								'Batkhela',
								'Battagram',
								'Birote',
								'Chakdara',
								'Charsadda',
								'Chitral',
								'Daggar',
								'Dargai',
								'Darya Khan',
								'Dera Ismail Khan',
								'Doaba',
								'Dir',
								'Drosh',
								'Hangu',
								'Haripur',
								'Karak',
								'Kohat',
								'Kulachi',
								'Lakki Marwat',
								'Latamber',
								'Madyan',
								'Mansehra',
								'Mardan',
								'Mastuj',
								'Mingora',
								'Nowshera',
								'Paharpur',
								'Pabbi',
								'Peshawar',
								'Saidu Sharif',
								'Shorkot',
								'Shewa Adda',
								'Swabi',
								'Swat',
								'Tangi',
								'Tank',
								'Thall',
								'Timergara',
								'Tordher',
								'Awaran',
								'Barkhan',
								'Chagai',
								'Dera Bugti',
								'Gwadar',
								'Harnai',
								'Jafarabad',
								'Jhal Magsi',
								'Kacchi',
								'Kalat',
								'Kech',
								'Kharan',
								'Khuzdar',
								'Killa Abdullah',
								'Killa Saifullah',
								'Kohlu',
								'Lasbela',
								'Lehri',
								'Loralai',
								'Mastung',
								'Musakhel',
								'Nasirabad',
								'Nushki',
								'Panjgur',
								'Pishin valley',
								'Quetta',
								'Sherani',
								'Sibi',
								'Sohbatpur',
								'Washuk',
								'Zhob',
								'Ziarat'		
								);
		
			$this->db->where('user_id!=',$this->input->post('campus_user_id'));
			$all_accesses = $this->db->get('access')->result_array();
			
			$cities= array();
			foreach($all_accesses as $access)
			{
				if($access['cities']!='')
				{
					$user_cities = explode(',',$access['cities']);
					
					foreach($user_cities as $user_city)
					{
						if (($key = array_search($user_city, $data['cities'])) !== false) 
						{
							unset($data['cities'][$key]);
						}
					}
				}
			}
			
			$data['online_applications'] = $this->db->get_where('online_application_access', array('user_id'=>$this->input->post('campus_user_id')))->result_array();
			$data['online_admission_campus_ids'] = array_values(array_unique(array_column($data['online_applications'], 'campus_id')));
			
		}
		
		if($this->input->post('designation_id'))
		{
			
			$data['access_values'] = $this->db->get_where('access_rules', array('designation_id'=>$this->input->post('designation_id')))->result_array();
			
			
			$data['cities'] = array(
								'Islamabad',
								'Ahmed Nager Chatha',
								'Ahmadpur East',
								'Ali Khan Abad',
								'Alipur',
								'Arifwala',
								'Attock',
								'Bhera',
								'Bhalwal',
								'Bahawalnagar',
								'Bahawalpur',
								'Bhakkar',
								'Burewala',
								'Chillianwala',
								'Chakwal',
								'Chichawatni',
								'Chiniot',
								'Chishtian',
								'Daska',
								'Darya Khan',
								'Dera Ghazi Khan',
								'Dhaular',
								'Dina',
								'Dinga',
								'Dipalpur',
								'Faisalabad',
								'Fateh Jhang',
								'Ghakhar Mandi',
								'Gojra',
								'Gujranwala',
								'Gujrat',
								'Gujar Khan',
								'Hafizabad',
								'Haroonabad',
								'Hasilpur',
								'Haveli',
								'Lakha',
								'Jalalpur',
								'Jattan',
								'Jampur',
								'Jaranwala',
								'Jhang',
								'Jhelum',
								'Kalabagh',
								'Karor Lal Esan',
								'Kasur',
								'Kamalia',
								'Kamoke',
								'Khanewal',
								'Khanpur',
								'Kharian',
								'Khushab',
								'Kot Adu',
								'Jauharabad',
								'Lahore',
								'Lalamusa',
								'Layyah',
								'Liaquat Pur',
								'Lodhran',
								'Malakwal',
								'Mamoori',
								'Mailsi',
								'Mandi Bahauddin',
								'mian Channu',
								'Mianwali',
								'Multan',
								'Murree',
								'Muridke',
								'Mianwali Bangla',
								'Muzaffargarh',
								'Narowal',
								'Okara',
								'Renala Khurd',
								'Pakpattan',
								'Pattoki',
								'Pir Mahal',
								'Qaimpur',
								'Qila Didar Singh',
								'Rabwah',
								'Raiwind',
								'Rajanpur',
								'Rahim Yar Khan',
								'Rawalpindi',
								'Sadiqabad',
								'Safdarabad',
								'Sahiwal',
								'Sangla Hill',
								'Sarai Alamgir',
								'Sargodha',
								'Shakargarh',
								'Sheikhupura',
								'Sialkot',
								'Sohawa',
								'Soianwala',
								'Siranwali',
								'Talagang',
								'Taxila',
								'Toba Tek Singh',
								'Vehari',
								'Wah Cantonment',
								'Wazirabad',
								'Badin',
								'Bhirkan',
								'Rajo Khanani',
								'Chak',
								'Dadu',
								'Digri',
								'Diplo',
								'Dokri',
								'Ghotki',
								'Haala',
								'Hyderabad',
								'Islamkot',
								'Jacobabad',
								'Jamshoro',
								'Jungshahi',
								'Kandhkot',
								'Kandiaro',
								'Karachi',
								'Kashmore',
								'Keti Bandar',
								'Khairpur',
								'Kotri',
								'Larkana',
								'Matiari',
								'Mehar',
								'Mirpur Khas',
								'Mithani',
								'Mithi',
								'Mehrabpur',
								'Moro',
								'Nagarparkar',
								'Naudero',
								'Naushahro Feroze',
								'Naushara',
								'Nawabshah',
								'Nazimabad',
								'Qambar',
								'Qasimabad',
								'Ranipur',
								'Ratodero',
								'Rohri',
								'Sakrand',
								'Sanghar',
								'Shahbandar',
								'Shahdadkot',
								'Shahdadpur',
								'Shahpur Chakar',
								'Shikarpaur',
								'Sukkur',
								'Tangwani',
								'Tando Adam Khan',
								'Tando Allahyar',
								'Tando Muhammad Khan',
								'Thatta',
								'Umerkot',
								'Warah',
								'Abbottabad',
								'Adezai',
								'Alpuri',
								'Akora Khattak',
								'Ayubia',
								'Banda Daud Shah',
								'Bannu',
								'Batkhela',
								'Battagram',
								'Birote',
								'Chakdara',
								'Charsadda',
								'Chitral',
								'Daggar',
								'Dargai',
								'Darya Khan',
								'Dera Ismail Khan',
								'Doaba',
								'Dir',
								'Drosh',
								'Hangu',
								'Haripur',
								'Karak',
								'Kohat',
								'Kulachi',
								'Lakki Marwat',
								'Latamber',
								'Madyan',
								'Mansehra',
								'Mardan',
								'Mastuj',
								'Mingora',
								'Nowshera',
								'Paharpur',
								'Pabbi',
								'Peshawar',
								'Saidu Sharif',
								'Shorkot',
								'Shewa Adda',
								'Swabi',
								'Swat',
								'Tangi',
								'Tank',
								'Thall',
								'Timergara',
								'Tordher',
								'Awaran',
								'Barkhan',
								'Chagai',
								'Dera Bugti',
								'Gwadar',
								'Harnai',
								'Jafarabad',
								'Jhal Magsi',
								'Kacchi',
								'Kalat',
								'Kech',
								'Kharan',
								'Khuzdar',
								'Killa Abdullah',
								'Killa Saifullah',
								'Kohlu',
								'Lasbela',
								'Lehri',
								'Loralai',
								'Mastung',
								'Musakhel',
								'Nasirabad',
								'Nushki',
								'Panjgur',
								'Pishin valley',
								'Quetta',
								'Sherani',
								'Sibi',
								'Sohbatpur',
								'Washuk',
								'Zhob',
								'Ziarat'		
								);
		
			$this->db->where('designation_id!=',$this->input->post('designation_id'));
			$all_accesses = $this->db->get('access_rules')->result_array();
			
			$cities= array();
			foreach($all_accesses as $access)
			{
				if($access['cities']!='')
				{
					$user_cities = explode(',',$access['cities']);
					
					foreach($user_cities as $user_city)
					{
						if (($key = array_search($user_city, $data['cities'])) !== false) 
						{
							unset($data['cities'][$key]);
						}
					}
				}
			}
			
			//$data['online_applications'] = $this->db->get_where('online_application_access', array('user_id'=>$this->input->post('campus_user_id')))->result_array();
			
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('access/index', $data);
		$this->load->view('inc/footer');
	}
	
	public function getUsers()
	{
		$campus_id = $this->input->post('campus_id');
		$users = $this->db->get_where('users', array('campus_id'=>$campus_id,'status'=>1))->result_array();
		$html ='';
		
		foreach($users as $user)
		{
			$html.='<option value="'.$user['user_id'].'">'.$user['first_name'].' '.$user['last_name'].'</option>';
		}
		echo $html;
	}
	
	public function getDesignations()
	{
		$department_id = $this->input->post('department_id');
		$designations = $this->db->get_where('designations', array('department_id'=>$department_id))->result_array();
		$html ='';
		
		foreach($designations as $designation)
		{
			$html.='<option value="'.$designation['designation_id'].'">'.$designation['designation_name'].'</option>';
		}
		echo $html;
	}
	
	public function add()
	{	
		$check_access = $this->accesses->check();
		if(count($check_access)>0)
		{
			$this->accesses->updateAccess();
		}
		else
		{
			$this->accesses->addAccess();
		}
		
		$online_admission_campus_ids = $this->input->post('online_admission_campus_ids');
		$user_id = $this->input->post('user_id');
		
		if ($user_id) {
			$this->db->where('user_id', $user_id);
			$this->db->delete('online_application_access');

			if (is_array($online_admission_campus_ids)) {
				foreach ($online_admission_campus_ids as $campus_id) {
					if ($campus_id == '') {
						continue;
					}

					$this->db->insert('online_application_access', array(
						'user_id' => $user_id,
						'campus_id' => $campus_id,
						'city' => '',
						'all_cities' => 1
					));
				}
			}
		}
		
		$this->session->set_flashdata('message', 'Access has been granted successfully');
		
		redirect('access');
	}
	
}