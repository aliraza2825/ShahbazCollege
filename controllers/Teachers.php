<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Teachers extends CI_Controller {
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
		$this->load->model('teacher');
		$this->load->library('upload');	
		$this->ensure_salary_columns();
	}

	private function ensure_salary_columns()
	{
		if (!$this->db->field_exists('salary_adjustment', 'users')) {
			$this->db->query("ALTER TABLE users ADD salary_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER gross_salary");
		}
		if (!$this->db->field_exists('apply_statutory_rules', 'users')) {
			$this->db->query("ALTER TABLE users ADD apply_statutory_rules TINYINT(1) NOT NULL DEFAULT 1 AFTER salary_adjustment");
		}
	}
	
	public function insert()
	{
		//CHECK TEACHER CNIC IS ALREADY EXIST OR NOT
		$teacherNic = $this->teacher->checkTeacherNIC($this->input->post('cnic'));
		if(count($teacherNic)>0)
		{
			$this->session->set_flashdata('error', 'Teacher with CNIC '.$this->input->post('cnic').' is already added');
			redirect('teachers/add_teacher');
		}
		
		$password = md5($this->input->post('password'));
		$designations = $this->input->post('designation_id');
		
		if($designations!='')
		{
			$designations =  implode(",", $designations);
		}
		
		
		$data = array(
				'first_name'		=> $this->input->post('first_name'),
				'last_name'			=> $this->input->post('last_name'),
				'father_name'		=> $this->input->post('father_name'),
				'gender'			=> $this->input->post('gender'),
				'email'				=> $this->input->post('email'),
				'mobile'			=> $this->input->post('mobile'),
				'cnic'				=> $this->input->post('cnic'),
				'maritual_status'	=> $this->input->post('maritual_status'),
				'blood_group'		=> $this->input->post('blood_group'),
				'date_of_birth'		=> $this->input->post('date_of_birth'),
				'joining_date'		=> $this->input->post('joining_date'),
				'salary'			=> $this->input->post('salary'),
				'salary_adjustment'	=> (float) $this->input->post('salary_adjustment'),
				'apply_statutory_rules' => (int) $this->input->post('apply_statutory_rules'),
				'designation'		=> $this->input->post('designation'),
				'city'				=> $this->input->post('city'),
				'address'			=> $this->input->post('address'),
				'emergency_no'		=> $this->input->post('emergency_no'),
				'note'				=> $this->input->post('note'),
				'username' 			=> $this->input->post('username'),
				'password' 			=> $password,
				'role' 				=> $this->input->post('role'),
				'status' 			=> $this->input->post('status'),
				'campus_id' 		=> $this->input->post('campus_id'),
				'staff_type_id' 	=> $this->input->post('staff_type_id'),
				'department_id' 	=> $this->input->post('department_id'),
				'designation_id' 	=> $designations,
				'type' 				=> $this->input->post('type')
				);
		$user_id = $this->teacher->storeTeacher($data);
		
		//ADD MACHINE ID
		
		$sql = 'SELECT machine_id FROM machine_data WHERE campus_id='.$this->input->post('campus_id').' ORDER BY machine_id DESC LIMIT 1';
		$campus_code = $this->db->get_where('campuses',array('campus_id'=>$this->input->post('campus_id')))->result_array();
					$query = $this->db->query($sql)->result_array();
					$last_machine_id = substr($query[0]['machine_id'], 0, -2);

					$this->db->set('teacher_student_id',$user_id);
					$this->db->set('machine_id',($last_machine_id+1).$campus_code[0]['campus_code']);
					$this->db->set('type','teacher');
					$this->db->set('campus_id',$this->input->post('campus_id'));
					$this->db->insert('machine_data');


//        $data2 = $this->input->post();
//        $this->db->set('machine_id', $this->input->post('attandance_id'));
//        $this->db->set('teacher_student_id', $user_id);
//        $this->db->set('type', $this->input->post('role'));
//        $this->db->set('campus_id', $this->input->post('campus_id'));
//        $this->db->insert('machine_data_face');

        $user_phones = $this->input->post('phones');
        foreach ($user_phones as $ph) {
            $this->db->set('user_id', $user_id);
            $this->db->set('phone', $ph);
            $this->db->insert('users_phones');
        }
        $user = $this->db->get_where('users','user_id = '.$user_id)->result_array();
        $this->updateUsersAccess($user);


		$this->session->set_flashdata('message', 'Teacher added successfully!');
		redirect('teachers/add_teacher');
	}

    public function update($id)
    {
//	    print_r($this->input->post('allowance_id[]'));
//	    exit();
        $this->db->where('user_id', $id);
        $this->db->delete('user_allowances');

        $allowances= $this->db->get('allownces')->result_array();

        $total_salary = $this->input->post('gross_salary');

        $amount = 0;
        $allowance_id =$this->input->post('allowance_id[]');

        foreach ($allowance_id as $al_id){

            $percent=$allowances[$al_id]['percent'];

            $amount= ($this->input->post('gross_salary') * ($percent/100));
            $total_salary = $total_salary-$amount;
            // $user_allowance = $this->input->post();
            $this->db->set('allowance_id', $allowances[$al_id]['id']);
            $this->db->set('user_id', $id);
            $this->db->set('amount', $amount);
            $this->db->set('created_by', $this->session->userdata('user_id'));
            $this->db->set('created_at', date('Y-m-d H:i:s'));
            $this->db->insert('user_allowances');
        }

        if($this->input->post('password')!=''){
            $password = md5($this->input->post('password'));
        }else{
            $password = $this->input->post('hidden_password');
        }
		
		$designations = $this->input->post('designation_id');
		
		if($designations!='')
		{
			$designations =  implode(",", $designations);
		}
		
		
        $data = array(
            'first_name'		=> $this->input->post('first_name'),
            'last_name'			=> $this->input->post('last_name'),
            'father_name'		=> $this->input->post('father_name'),
            'gender'			=> $this->input->post('gender'),
            'email'				=> $this->input->post('email'),
            'mobile'			=> $this->input->post('mobile'),
            'cnic'				=> $this->input->post('cnic'),
            'maritual_status'	=> $this->input->post('maritual_status'),
            'blood_group'		=> $this->input->post('blood_group'),
            'date_of_birth'		=> $this->input->post('date_of_birth'),
            'joining_date'		=> $this->input->post('joining_date'),
            'salary'			=> $total_salary,
            'gross_salary'		=> $this->input->post('gross_salary'),
            'salary_adjustment'	=> (float) $this->input->post('salary_adjustment'),
            'apply_statutory_rules' => (int) $this->input->post('apply_statutory_rules'),
            'designation'		=> $this->input->post('designation'),
            'city'				=> $this->input->post('city'),
            'address'			=> $this->input->post('address'),
            'emergency_no'		=> $this->input->post('emergency_no'),
            'note'				=> $this->input->post('note'),
            'username' 			=> $this->input->post('username'),
            'password' 			=> $password,
            'role' 				=> $this->input->post('role'),
            'status' 			=> $this->input->post('status'),
            'campus_id' 		=> $this->input->post('campus_id'),
            'staff_type_id' 	=> $this->input->post('staff_type_id'),
            'department_id' 	=> $this->input->post('department_id'),
            'designation_id' 	=> $designations,
            'type' 				=> $this->input->post('type')
        );
        
        $this->teacher->updateTeacher($data);

        $this->db->select('teacher_student_id');
        $this->db->from('machine_data_face');
        $this->db->where('teacher_student_id',$id);
        $check=$this->db->get()->row();


        if(@$check->teacher_student_id == $id){
            $data2 = $this->input->post();
            $this->db->set('machine_id', $this->input->post('attandance_id'));
            $this->db->set('teacher_student_id', $id);
            $this->db->set('type', $this->input->post('role'));
            $this->db->set('campus_id', $this->input->post('campus_id'));
            $this->db->where('teacher_student_id', $id);
            $this->db->update('machine_data_face');
        }else{
            $data3 = $this->input->post();
            $this->db->set('machine_id', $this->input->post('attandance_id'));
            $this->db->set('teacher_student_id', $id);
            $this->db->set('type', $this->input->post('role'));
            $this->db->set('campus_id', $this->input->post('campus_id'));
            $this->db->insert('machine_data_face');
        }
        $this->db->where("user_id",$id);
        $this->db->delete("users_phones");
        $user_phones = $this->input->post('phones');
        foreach ($user_phones as $ph) {
            $this->db->set('user_id', $id);
            $this->db->set('phone', $ph);
            $this->db->insert('users_phones');
        }
        
        $user = $this->db->get_where('users','user_id = '.$id)->result_array();
        $this->updateUsersAccess($user);

        $this->session->set_flashdata('message', 'Teacher updated successfully!');
        redirect('teachers/edit_teacher/'.$id);
    }
    
    public function updateUsersAccess($users)
    {
        
        if (empty($users)) return;
    
        $columns = $this->db->list_fields('access_rules');
        $ignore  = ['access_id', 'designation_id', 'created_at', 'updated_at'];
    
        foreach ($users as $user) {
    
            $user_id = $user['user_id'];
            $designation_ids = explode(',', $user['designation_id']);
    
            $rules = $this->db->where_in('designation_id', $designation_ids)
                ->get('access_rules')
                ->result_array();
                
    
            if (empty($rules)) continue;
    
            $final = [];
    
            foreach ($columns as $col) {
    
                if (in_array($col, $ignore)) continue;
    
                $values = array_column($rules, $col);
    
                // only remove NULL (not empty string / 0)
                $values = array_filter($values, function ($v) {
                    return $v !== null;
                });
    
                // ðŸ”´ IMPORTANT: revoke case
                if (empty($values)) {
                    $final[$col] = null;   // access revoke
                    continue;
                }
    
                // BOOLEAN / INT â†’ OR logic
                if (count(array_unique($values)) <= 2 && max($values) <= 1) {
                    $final[$col] = max($values);
                }
                // CSV / STRING
                else {
                    $final[$col] = $this->mergeCsv($values);
                }
            }
    
            // check access row
            $exists = $this->db->select('access_id')
                ->where('user_id', $user_id)
                ->get('access')
                ->row();
                
                
                
    
            if ($exists) {
                $this->db->where('user_id', $user_id)->update('access', $final);
            } else {
                $final['user_id'] = $user_id;
                $this->db->insert('access', $final);
            }
        }
    }
	
	public function delete($id)
	{
		$this->teacher->deleteTeacher($id);
		$this->session->set_flashdata('message', 'Teacher deleted successfully!');
		redirect('teachers/all_teachers');
	}
	
	public function index()
	{
		
	}
	
	public function add_teacher()
	{
		$data['count'] = $this->teacher->getTeachersCount();
		$data['campuses'] = $this->teacher->getCampuses();
		$data['teachers'] = $this->teacher->getTeachers();
		$data['staff_types'] = $this->db->get('staff_type')->result_array();
		$data['departments'] = $this->db->get('departments')->result_array();
		$data['designations'] = $this->db->get('designations')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('teachers/add_teacher', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_teacher($id)
	{
		$data['count'] = $this->teacher->getTeachersCount();
		$data['campuses'] = $this->teacher->getCampuses();
		$data['teachers'] = $this->teacher->editTeacher($id);
		$data['staff_types'] = $this->db->get('staff_type')->result_array();
		$data['departments'] = $this->db->get('departments')->result_array();
		$data['designations'] = $this->db->get('designations')->result_array();
        $data['allowances'] = $this->db->get('allownces')->result_array();

        $this->db->select('*');
        $this->db->from('user_allowances');
        $this->db->where('user_id', $id);
        $data['allowances_check'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('teachers/edit_teacher', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_teachers()
	{
		$data['count'] = $this->teacher->getTeachersCount();
		$data['teachers'] = $this->teacher->getTeachers();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('teachers/all_teachers', $data);
		$this->load->view('inc/footer');
	}
	
	public function upload_documents($id)
	{
		$data['documents'] = $this->teacher->uploadedDocuments($id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('teachers/upload_documents', $data);
		$this->load->view('inc/footer');
	}
	
	public function upload($id)
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'uploads/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('teacher_document')) {
			$data = array('msg' => $this->upload->display_errors());
			$teacher_document = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$teacher_document = $data['upload_data']['file_name'];
			}
		}
		
		$this->teacher->uploadDocument($id, $teacher_document,$this->input->post('type'));
		$this->session->set_flashdata('message', 'Document Uploaded Successfully.');
		redirect('teachers/upload_documents/'.$id);
	}
	
	public function check_attendence($user_id)
	{
		$machine_user_exist = $this->teacher->checkMachineUser($user_id);
		if(count($machine_user_exist)):
			$data['users'] = $this->teacher->getTeacher($user_id);
			$type = 'teacher';
			$machine_id = $this->db->get_where('machine_data', array('teacher_student_id'=>$user_id, 'type'=>$type))->result_array();
			$data['machine_id'] = $machine_id[0]['machine_id'];
			//echo $data['machine_id']; exit;
			$strDateFrom = $this->input->post('from_date');
			$strDateTo = $this->input->post('to_date');
			$data['dates'] = $this->createDateRangeArray($strDateFrom,$strDateTo);
			$this->load->view('inc/header');
			$this->load->view('inc/sidebar');
			$this->load->view('teachers/check_attendence', $data);
			$this->load->view('inc/footer');
		else:
			$this->session->set_flashdata('error', 'Kindly set machine id of this user.');
			redirect('teachers/all_teachers');
		endif;
	}
	
	public function createDateRangeArray($strDateFrom,$strDateTo)
	{
		$aryRange=array();
	
		$iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
		$iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));
	
		if ($iDateTo>=$iDateFrom)
		{
			array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
			while ($iDateFrom<$iDateTo)
			{
				$iDateFrom+=86400; // add 24 hours
				array_push($aryRange,date('Y-m-d',$iDateFrom));
			}
		}
		return $aryRange;
	}
	
	public function check_timing($user_id)
	{
		$user = $this->teacher->getTeacher($user_id);
        if (count($user) <= 0) {
            show_404();
        }

        $staffTypeId = (int) @$user[0]['staff_type_id'];
        if ($staffTypeId <= 0) {
            $this->session->set_flashdata('error', 'Please set staff type first to manage timing.');
            redirect('teachers/all_teachers');
        }

        redirect('staff_type/staff_timing/'.$staffTypeId);
	}
	
	public function update_timing($user_id)
	{
		$user = $this->teacher->getTeacher($user_id);
        if (count($user) <= 0) {
            show_404();
        }
        $staffTypeId = (int) @$user[0]['staff_type_id'];
        if ($staffTypeId <= 0) {
            $this->session->set_flashdata('error', 'Please set staff type first to manage timing.');
            redirect('teachers/all_teachers');
        }

        redirect('staff_type/staff_timing/'.$staffTypeId);
	}
	
	public function contact_for_fee()
	{
		$data['staffs'] = $this->db->get('users')->result_array();
		
		if($this->input->post('form_submit')==1)
		{
			$data['results'] = $this->db->get_where('fees_remarks', array('date>='=>$this->input->post('from_date').' 00:00:00', 'date<='=>$this->input->post('to_date').' 11:59:59', 'add_by'=>$this->input->post('staff')))->result_array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('teachers/check_for_fee', $data);
		$this->load->view('inc/footer');
	}
	
	public function getDesignations()
	{
		$department_id = $this->input->post('department_id');
		$designations = $this->db->get_where('designations',array('department_id'=>$department_id))->result_array();
		
		$html = '';
		foreach($designations as $designation)
		{
			$html .= '<option value="'.$designation['designation_id'].'">'.$designation['designation_name'].'</option>';
		}
		echo $html;
	}

    public function delete_documents($id, $photo_id)
    {
        $this->db->where('id', $photo_id);
		$this->db->delete('teacher_documents');
        $this->session->set_flashdata('message', 'Image deleted successfully');
        redirect('teachers/upload_documents/'.$id);
    }
    
    private function mergeCsv(array $values)
    {
        $merged = [];

        foreach ($values as $v) {
            if ($v !== null && $v !== '') {
                $merged = array_merge($merged, explode(',', $v));
            }
        }

        return implode(',', array_unique($merged));
    }

}
