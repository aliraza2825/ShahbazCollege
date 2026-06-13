<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Documents extends CI_Controller {
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
		$this->load->model('clas');
		$this->load->model('student');
		$this->load->model('contractor');
	}
	
	public function diploma_documents()
	{
		if(@$this->input->post('council_exam_no'))
		{
			$this->db->select('campuses.campus_name, punjab_council_roll_number.*, students.class_id, students.roll_no as campus_roll_no, classes.name as class_name, students.mobile, students.emergency_no, contractors.name as contractor_name, students.student_id');
			$this->db->from('punjab_council_roll_number');
			$this->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
			$this->db->join('classes', 'students.class_id=classes.class_id', 'left');
			$this->db->join('contractors', 'students.contractor_id=contractors.contractor_id', 'left');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
			$this->db->where('students.course_id',1);
			$this->db->where('students.status',1);
			$this->db->where(array('punjab_council_roll_number.result_remarks!='=>'', 'punjab_council_roll_number.council_exam_no'=>$this->input->post('council_exam_no'),'punjab_council_roll_number.class'=>'2'));
			$this->db->where("(punjab_council_roll_number.result_remarks='Pass' OR punjab_council_roll_number.result_remarks='Pass*')", NULL, FALSE);
			if($this->input->post('campus_id')!='')
			{
				$this->db->where('campuses.campus_id',$this->input->post('campus_id'));
			}
			$data['roll_numbers'] = $this->db->get()->result_array();
		}
		else
		{
			$data['roll_numbers'] = array();
		}
		
		$this->db->select('council_exam_no, class, date, result_update_date');
		$this->db->from('punjab_council_roll_number');
		$this->db->group_by('council_exam_no');
		$this->db->group_by('class');
		$this->db->where('class','2');
		$data['council_exam_numbers'] = $this->db->get()->result_array();
		
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('documents/diploma_documents', $data);
		$this->load->view('inc/footer');
	}
	
	public function print_diploma($student_id)
	{
		$this->db->select('students.*, campuses.website, campuses.campus_name, campuses.logo,punjab_council_roll_number.computer_no, punjab_council_roll_number.roll_no as result_roll_no, classes.session, punjab_council_roll_number.result_update_date as result_update_date');
		$this->db->from('students');
		$this->db->join('punjab_council_roll_number', 'punjab_council_roll_number.cnic=students.cnic', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		if($student_id == '0'){
			$this->db->where_in('students.student_id', explode(',',$this->input->post('student_ids')));
		}else{
			$this->db->where('students.student_id', $student_id);
		}
		$this->db->where('punjab_council_roll_number.class', '2');
		$where = '(punjab_council_roll_number.result_remarks="Pass" or punjab_council_roll_number.result_remarks = "Pass*")';
		$this->db->where($where);
		
		$data['student']=$this->db->get()->result_array();
		//$this->load->view('punjab_council_roll_number/print_diploma', $data);
		if(count($data['student'])>0) {
			$this->load->view('documents/print_diploma', $data);
		}
		else {
			echo 'Page Not Found';
		}
	}
	
	public function print_noc($student_id)
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		if($student_id == '0'){
			
			$this->db->where_in('students.student_id', explode(',',$this->input->post('student_ids')));
		}else{
			$this->db->where('students.student_id', $student_id);
		}
		$data['student'] = $this->db->get()->result_array();
		
		$this->load->view('documents/print_noc', $data);
	}
	
	public function print_character_certificate($student_id)
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		if($student_id == '0'){
			
			$this->db->where_in('students.student_id', explode(',',$this->input->post('student_ids')));
		}else{
			$this->db->where('students.student_id', $student_id);
		}
		$data['student'] = $this->db->get()->result_array();
		
		$this->load->view('documents/print_character_certificate', $data);
	}
	
	public function print_student_character_certificate($student_id)
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->where('students.student_id', $student_id);
		$data['student'] = $this->db->get()->result_array();
		
		$this->load->view('documents/print_student_character_certificate', $data);
	}
	
	public function students_documents()
	{
		$data['campuses'] = $this->clas->getCampuses();
		if(@$this->input->post('form_submit')==1)
		{
			$data['students'] = $this->student->getStudents();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('documents/all_students', $data);
		$this->load->view('inc/footer');
	}
	
	public function print_admission_form($student_id)
	{
		$student_ids = explode(',',$student_id);

		$this->db->select('students.*,campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.phone1, campuses.phone2, campuses.phone3, campuses.phone4, campuses.phone5, campuses.phone6, campuses.phone7, campuses.logo, campuses.website, classes.session');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->where_in('students.student_id', $student_ids);
		$data['students'] = $this->db->get()->result_array();
		
		$this->load->view('documents/print_admission_form', $data);
	}
	
	public function print_struck_off_notice($student_id)
	{
		$this->db->select('students.*,campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.phone1, campuses.phone2, campuses.phone3, campuses.phone4, campuses.phone5, campuses.phone6, campuses.phone7, campuses.logo, campuses.website, classes.session');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->where('students.student_id', $student_id);
		$data['student'] = $this->db->get()->result_array();
		
		$this->load->view('documents/print_struck_off_notice', $data);
	}
	
	public function council_documents()
	{
		$data['campuses'] = $this->clas->getCampuses();
		if(@$this->input->post('form_submit')==1)
		{
			$data['students'] = $this->student->getStudents();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('documents/all_students_council', $data);
		$this->load->view('inc/footer');
	}
	
	public function print_council_admission_form($student_id)
	{
		$student_ids = explode(',',$student_id);

		$this->db->select('students.*, campuses.campus_name, campuses.address as campus_address, campuses.phone,campuses.campus_id,campuses.stamp,classes.session');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->where_in('students.student_id', $student_ids);
		$data['students'] = $this->db->get()->result_array();
		
		//$data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();
		
		$this->load->view('documents/print_council_admission_form', $data);
	}

	public function print_council_character_certificate($student_id)
	{
		$student_ids = explode(',',$student_id);

		$this->db->select('students.*, campuses.campus_name, campuses.address as campus_address, campuses.phone,campuses.campus_id,campuses.stamp,classes.session');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->where_in('students.student_id', $student_ids);
		$data['students'] = $this->db->get()->result_array();
		
		//$data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();
		
		$this->load->view('documents/print_council_character_certificate', $data);
	}

	public function print_council_admission_letter($student_id)
	{
		$student_ids = explode(',',$student_id);
		$this->db->select('students.*, campuses.campus_name, campuses.address as campus_address, campuses.phone,campuses.campus_id,campuses.stamp,classes.session');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->where_in('students.student_id', $student_ids);
		$data['students'] = $this->db->get()->result_array();
		
		//$data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();
		
		$this->load->view('documents/print_council_admission_letter', $data);
	}
	
	public function print_college_admission_letters()
	{
		$student_ids = $this->input->post('student_ids');
		$student_ids = explode(',',$student_ids);
		//exit();
		foreach($student_ids as $student_id):
			$this->db->select('students.*,campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.phone1, campuses.phone2, campuses.phone3, campuses.phone4, campuses.phone5, campuses.phone6, campuses.phone7, campuses.logo, campuses.website, classes.session,courses.course_name');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
			$this->db->join('courses','courses.course_id=students.course_id','inner');
			$this->db->where('students.student_id', $student_id);
			$data['student'] = $this->db->get()->result_array();

			$this->load->view('documents/print_admission_form', $data);
		endforeach;
	}
	
	public function print_struck_off_letters()
	{
		$student_ids = $this->input->post('student_ids');
		$student_ids = explode(',',$student_ids);
		foreach($student_ids as $student_id):
			$this->db->select('students.*,campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.phone1, campuses.phone2, campuses.phone3, campuses.phone4, campuses.phone5, campuses.phone6, campuses.phone7, campuses.logo, campuses.website, classes.session');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
			$this->db->where('students.student_id', $student_id);
			$data['student'] = $this->db->get()->result_array();
			
			$this->load->view('documents/print_struck_off_notice', $data);
		endforeach;
	}
	
	public function print_council_admission_forms()
	{
		$student_ids = $this->input->post('student_ids');
		$student_ids = explode(',',$student_ids);
		//exit();
		foreach($student_ids as $student_id):
			$this->db->select('students.*, campuses.campus_name,campuses.campus_id,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,campuses.head_stamp, campuses.address as campus_address, campuses.phone');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
			$this->db->where('students.student_id', $student_id);
			$data['student'] = $this->db->get()->result_array();
			
			$data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();
			
			$this->load->view('documents/print_council_admission_form', $data);
		endforeach;
	}
	
	public function print_student_card($student_id)
	{
		$this->db->select('students.*, classes.campus_id, classes.course_id, classes.session');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->where('students.student_id', $student_id);
		$data['students'] = $this->db->get()->result_array();
		
		$this->db->select('*');
		$this->db->from('student_documents');
		$this->db->where(array('student_id'=>$student_id, 'type'=>'Photo'));
		$data['photo'] = $this->db->get()->result_array();
		
		$data['campus'] = $this->db->get_where('campuses', array('campus_id'=>$data['students'][0]['campus_id']))->result_array();
		
		$this->load->view('documents/print_student_card', $data);
	}
	
	public function print_college_student_cards()
	{
		$student_ids = $this->input->post('student_ids');
		$student_ids = explode(',',$student_ids);
		//exit();
		foreach($student_ids as $student_id):
			$this->db->select('students.*, classes.campus_id, classes.course_id, classes.session');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->where('students.student_id', $student_id);
			$data['students'] = $this->db->get()->result_array();
			
			$this->db->select('*');
			$this->db->from('student_documents');
			$this->db->where(array('student_id'=>$student_id, 'type'=>'Photo'));
			$data['photo'] = $this->db->get()->result_array();
			
			$data['campus'] = $this->db->get_where('campuses', array('campus_id'=>$data['students'][0]['campus_id']))->result_array();
			
			$this->load->view('documents/print_student_card', $data);
		endforeach;
	}

	public function print_student_checklist()
	{
		$student_ids = $this->input->post('student_ids');
		$student_ids = explode(',',$student_ids);
		//exit();
		foreach($student_ids as $student_id):
			$this->db->select('students.*, classes.campus_id, classes.course_id, classes.session,campuses.campus_name');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'inner');
			$this->db->where('students.student_id', $student_id);
			$data['student'] = $this->db->get()->result_array();

			$this->load->view('documents/print_student_checklist', $data);
		endforeach;
	}
	
	public function print_student_character_certificates()
	{
		$student_ids = $this->input->post('student_ids');
		$student_ids = explode(',',$student_ids);
		//exit();
		foreach($student_ids as $student_id):
			$this->db->select('*');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
			$this->db->where('students.student_id', $student_id);
			$data['student'] = $this->db->get()->result_array();
			
			$this->load->view('documents/print_student_character_certificate', $data);
		endforeach;
	}

    public function select_campuse_book(){
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('documents/select_campus_book',$data);
        $this->load->view('inc/footer');
    }

    public function get_print_view()
    {
        $data = $this->input->post();
        $this->db->select_max('book');
        $this->db->from('bookcount');
        $this->db->where('campus_code',$this->input->post('campus_id'));
        $count=$this->db->get()->result_array();
        $count=$count[0]['book'];
        $count++;
        $data['compuses'] = $this->db->get_where('campuses',array('campus_code'=>$this->input->post('campus_id')))->row();
        $data['bookno'] = $count;
        $this->load->view('documents/print_recived_pad',$data);
    }

    public function ajax_request_to_store_book_number(){
        $data = $this->input->post();
        $this->db->set('book', $this->input->post('get_data_of_book'));
        $this->db->set('campus_code', $this->input->post('branch_code_get'));
        $this->db->set('created_by', $this->input->post('created_by_code_get'));
        $this->db->set('created_at', date('Y-m-d H:i:s'));
        $this->db->insert('bookcount');
        echo"ok";

    }

    public function recipt_pad_list(){
        $this->db->select('*');
        $this->db->from('bookcount');
        $this->db->join('campuses', 'campuses.campus_code=bookcount.campus_code', 'inner');
        $this->db->join('users', 'users.user_id = bookcount.created_by', 'inner');
        $this->db->order_by("bookcount.id", "desc");
        $data['pad_list'] = $this->db->get()->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('documents/recipt_pad_list',$data);
        $this->load->view('inc/footer');
    }

    public function show_print_recipt($id)
    {
        $this->db->select('*');
        $this->db->from('bookcount');
        $this->db->join('campuses', 'campuses.campus_code = bookcount.campus_code', 'inner');
        $this->db->where('bookcount.id',$id);
        $data['pr_data']=$this->db->get()->result_array();
        $this->load->view('documents/recipt_pad_view',$data);
    }

    public function student_all_document($id)
	{
        $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->where('students.student_id', $id);
		$data['student'] = $this->db->get()->result_array();

        $data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$id, 'type'=>'Photo'))->result_array();
        $data['result_card'] = $this->db->get_where('student_documents', array('student_id'=>$id, 'type'=>'Result Card'))->result_array();
        $data['id_card'] = $this->db->get_where('student_documents', array('student_id'=>$id, 'type'=>'ID Card'))->result_array();

		$this->db->select('teacher_documents.image');
		$this->db->from('teacher_documents');
		$this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
		$this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $data['student'][0]['campus_id']));
		$data['resp']= $this->db->get()->result_array();

        $this->load->view('documents/student_all_document', $data);
    }

	public function privacy_policy(){

        
        $this->load->view('documents/privacy_policy');
    }

    public function print_council_registration_form($student_id)
    {
        $this->db->select('students.*,campuses.campus_id, campuses.campus_name,campuses.stamp,campuses.head_stamp, campuses.address as campus_address, campuses.phone,punjab_council_roll_number.roll_no as roll_no,classes.session,punjab_council_roll_number.computer_no');
        $this->db->from('students');
        $this->db->join('punjab_council_roll_number', 'punjab_council_roll_number.cnic=students.cnic', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->where('students.student_id', $student_id);
        $this->db->where(array('punjab_council_roll_number.result_remarks like'=>'%Pass%','punjab_council_roll_number.class'=>'2'));
        $data['student'] = $this->db->get()->result_array();

        $data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();

        $this->load->view('documents/print_council_diploma_registration_form', $data);
    }
    
    public function print_affidavit($student_id)
    {
        $this->db->select('students.*,campuses.campus_id, campuses.campus_name,campuses.stamp,campuses.head_stamp, campuses.address as campus_address, campuses.phone,punjab_council_roll_number.roll_no as roll_no,classes.session,punjab_council_roll_number.computer_no');
        $this->db->from('students');
        $this->db->join('punjab_council_roll_number', 'punjab_council_roll_number.cnic=students.cnic', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->where('students.student_id', $student_id);
        $this->db->where(array('punjab_council_roll_number.result_remarks like'=>'%Pass%','punjab_council_roll_number.class'=>'2'));
        $data['student'] = $this->db->get()->result_array();

        $data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();

        $this->load->view('documents/print_affidavit', $data);
    }

    public function student_supplementary_document($id,$council_exam_no,$class) {

        $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->where('students.student_id', $id);
        $data['student'] = $this->db->get()->result_array();

        $data['photo'] = $this->db->get_where('student_documents', array('student_id'=>$id, 'type'=>'Photo'))->result_array();
        $data['exam_details']  = $this->db->order_by("id","DESC")->get_where("punjab_council_roll_number","cnic = '".$data['student'][0]['cnic']."'")->row();
        $data['id_card'] = $this->db->get_where('student_documents', array('student_id'=>$id, 'type'=>'ID Card'))->result_array();

		if($class==1)
		{
			$column_name = 'first_year';
		}
		elseif($class==2)
		{
			$column_name = 'second_year';
		}
		$data['checkExam'] = $this->db->get_where('exam_sequence',array($column_name=>$council_exam_no))->result_array();

        $this->load->view('documents/student_supplementary_document', $data);
    }
}
