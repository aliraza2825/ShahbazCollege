<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Contractors extends CI_Controller {
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
		$this->load->model('contractor');
		$this->load->model('student');
		$this->load->model('clas');
	}
	
	public function insert()
	{
		$data = $this->input->post();
		$contractor_id = $this->contractor->storeContractor($data);
		
		//ADD CONTRACT DOCUMENTS
		$count_contract_images = count($_FILES['contractor_documents']['name']);
		for($i=0;$i<$count_contract_images;$i++){
			if(!empty($_FILES['contractor_documents']['name'][$i]))
			{
				$_FILES['file']['name'] = $_FILES['contractor_documents']['name'][$i];
				$_FILES['file']['type'] = $_FILES['contractor_documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['contractor_documents']['tmp_name'][$i];
				$_FILES['file']['error'] = $_FILES['contractor_documents']['error'][$i];
				$_FILES['file']['size'] = $_FILES['contractor_documents']['size'][$i];
				
				$config['upload_path'] = 'contract_images/'; 
				$config['allowed_types'] = 'jpg|jpeg|png|gif';
				$config['max_size'] = '5000';
				$config['file_name'] = $_FILES['contractor_documents']['name'][$i];
				
				$this->load->library('upload',$config); 
				
				if($this->upload->do_upload('file'))
				{
					$uploadData = $this->upload->data();
					$filename = $uploadData['file_name'];
					//ADD IN DATABASE
					$this->db->set('image',$filename);
					$this->db->set('contractor_id',$contractor_id);
					$this->db->insert('contractor_documents');
				}
			}
		}
		
		$this->session->set_flashdata('message', 'Contractor added successfully!');
		redirect('contractors/add_contractor');
	}
	
	public function update($id)
	{
		$data = $this->input->post();
		$this->contractor->updateContractor($data);
		
		//ADD CONTRACT DOCUMENTS
		$count_contract_images = count($_FILES['contractor_documents']['name']);
		for($i=0;$i<$count_contract_images;$i++){
			if(!empty($_FILES['contractor_documents']['name'][$i]))
			{
				$_FILES['file']['name'] = $_FILES['contractor_documents']['name'][$i];
				$_FILES['file']['type'] = $_FILES['contractor_documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['contractor_documents']['tmp_name'][$i];
				$_FILES['file']['error'] = $_FILES['contractor_documents']['error'][$i];
				$_FILES['file']['size'] = $_FILES['contractor_documents']['size'][$i];
				
				$config['upload_path'] = 'contract_images/'; 
				$config['allowed_types'] = 'jpg|jpeg|png|gif';
				$config['max_size'] = '5000';
				$config['file_name'] = $_FILES['contractor_documents']['name'][$i];
				
				$this->load->library('upload',$config); 
				
				if($this->upload->do_upload('file'))
				{
					$uploadData = $this->upload->data();
					$filename = $uploadData['file_name'];
					//ADD IN DATABASE
					$this->db->set('image',$filename);
					$this->db->set('contractor_id',$id);
					$this->db->insert('contractor_documents');
				}
			}
		}
		
		$this->session->set_flashdata('message', 'Contractor updated successfully!');
		redirect('contractors/edit_contractor/'.$id);
	}
	
	public function delete($id)
	{
		$this->contractor->deleteContractor($id);
		$this->session->set_flashdata('message', 'Contractor deleted successfully!');
		redirect('contractors/all_contractors');
	}
	
	public function index()
	{
		$data['contractors'] = $this->clas->getContractor();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/all_contractors', $data);
		$this->load->view('inc/footer');
	}
	
	public function add_contractor()
	{
		$data['count'] = $this->contractor->getContractorCount();
		$data['contractors'] = $this->contractor->getContractors();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/add_contractor', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_contractor($id)
	{
		$data['count'] = $this->contractor->getContractorCount();
		$data['contractor'] = $this->contractor->editContractor($id);
		$data['contractors'] = $this->contractor->getContractors();
		$data['contractor_documents'] = $this->db->get_where('contractor_documents',array('contractor_id'=>$id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/edit_contractor', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_contractors()
	{
		$data['count'] = $this->contractor->getContractorCount();
		$data['contractors'] = $this->contractor->getContractors();
		
    
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/all_contractors', $data);
		$this->load->view('inc/footer');
	}
	
	public function contractor_documents($contractor_id)
	{
		$data['contractor_documents'] = $this->db->get_where('contractor_documents',array('contractor_id'=>$contractor_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/contractor_documents', $data);
		$this->load->view('inc/footer');
	}
	
	public function contractors($contractor_id)
	{
		$data['students'] = $this->clas->getStudents($class_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/contractors', $data);
		$this->load->view('inc/footer');
	}
	
	public function add_extra_fee($contract_id)
	{
		$dead_line = $this->input->post('extra_fee_dead_line');
		$challan_no = $this->getChallanNo();
		
		$this->db->set('amount', $this->input->post('extra_fee'));
		$this->db->set('dead_line', $dead_line);
		$this->db->set('contract_id', $contract_id);
		$this->db->set('payment_plan', 'Custom Plan');
		$this->db->set('payment_comment', $this->input->post('payment_comment'));
		$this->db->set('challan_no', $challan_no);
		$this->db->set('add_by', $this->session->userdata('name'));
		$this->db->insert('payments');
		
		$this->session->set_flashdata('message', 'Extra fee added successfully.');
		redirect(site_url().'/contractors/contract_payments_paid/'.$contract_id);
	}
	
	public function add_extra_consulation_fee($contract_id)	{		$dead_line = $this->input->post('consulation_dead_line_1');		$challan_no = $this->getChallanNo();				$this->db->set('amount', $this->input->post('consulation_fee_1'));		$this->db->set('dead_line', $dead_line);		$this->db->set('contract_id', $contract_id);		$this->db->set('payment_plan', 'consulation fee');		$this->db->set('payment_comment', $this->input->post('payment_comment').'This fee for next Exam # '.$this->input->post('exam_no').' '.$this->input->post('class'));		$this->db->set('challan_no', $challan_no);		$this->db->set('student_id', $this->input->post('student_id'));		$this->db->set('custom_student_id', $this->input->post('student_id'));		$this->db->set('challan_no', $challan_no);		$this->db->set('add_by', $this->session->userdata('name'));		$this->db->insert('payments');				$this->session->set_flashdata('message', 'Extra consultaion fee added successfully.');		redirect(site_url().'/contractors/contract_payments_paid/'.$contract_id);	}
	
	public function getChallanNo()
	{
		$random_number = rand(1000, 999999999);
		$check_challan_no = $this->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
		if(count($check_challan_no)>0)
		{
			$random_number = $this->getChallanNo();
		}
		else
		{
			return $random_number;
		}
	}
	
	public function contract_reset_plan($contract_id)
	{
		$this->db->where('contract_id', $contract_id);
		$this->db->delete('payments');
		$this->session->set_flashdata('message', 'Contractor fee plan has been reset successfully');
		redirect('contractors/all_contracts');
	}
	
	public function create_contract()
	{
		$data['contractors'] = $this->db->get('contractors')->result_array();
		$data['courses'] = $this->db->get('courses')->result_array();
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/create_contract', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_contract($contract_id)
	{
		$data['contractors'] = $this->db->get('contractors')->result_array();
		$data['campuses'] = $this->clas->getCampuses();
		$data['courses'] = $this->db->get('courses')->result_array();
		$data['contract'] = $this->db->get_where('contracts',array('contract_id'=>$contract_id))->result_array();
		$data['contract_documents'] = $this->db->get_where('contract_documents',array('contract_id'=>$contract_id,'document_type'=>'contract_documents'))->result_array();
		$data['other_documents'] = $this->db->get_where('contract_documents',array('contract_id'=>$contract_id,'document_type'=>'other_documents'))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/edit_contract', $data);
		$this->load->view('inc/footer');
	}
	
	public function contract_documents($contract_id)
	{
		$data['contract_documents'] = $this->db->get_where('contract_documents',array('contract_id'=>$contract_id,'document_type'=>'contract_documents'))->result_array();
		$data['other_documents'] = $this->db->get_where('contract_documents',array('contract_id'=>$contract_id,'document_type'=>'other_documents'))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/contract_documents', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete_contractor_documents($contractor_id,$document_id)
	{
		$this->db->where('contractor_document_id',$document_id);
		$this->db->delete('contractor_documents');
		
		$this->session->set_flashdata('message','Document Deleted Successfully.');
		redirect('contractors/contractor_documents/'.$contractor_id);
	}
	
	public function delete_documents($contract_id,$document_id)
	{
		$this->db->where('contract_document_id',$document_id);
		$this->db->delete('contract_documents');
		
		$this->session->set_flashdata('message','Document Deleted Successfully.');
		redirect('contractors/contract_documents/'.$contract_id);
	}
	
	public function all_contracts()
	{	
		$this->db->select('*');
		$this->db->from('contracts');
		$this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
		$this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->join('campuses','contracts.campus_id=campuses.campus_id','LEFT');
		$data['contracts'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/all_contracts',$data);
		$this->load->view('inc/footer');
	}
	
	public function create()
	{
		$data = $this->input->post();
		$contractor_id = $data['contractor_id'];
		$course_id = $data['course_id'];
		$campus_id = $data['campus_id'];
		$contract_name = $data['contract_name'];
		$session = $data['session'];
		$contract_date = $data['contract_date'];
		$total_students = $data['total_students'];
		$per_student_fee = $data['per_student_fee'];
		
		$this->db->set('contractor_id',$contractor_id);
		$this->db->set('course_id',$course_id);
		$this->db->set('campus_id',$campus_id);
		$this->db->set('contract_name',$contract_name);
		$this->db->set('session',$session);
		$this->db->set('contract_date',$contract_date);
		$this->db->set('total_students',$total_students);
		$this->db->set('per_student_fee',$per_student_fee);
		$this->db->insert('contracts');
		
		$contract_id = $this->db->insert_id();
		
		//ADD CONTRACT DOCUMENTS
		$count_contract_images = count($_FILES['contract_documents']['name']);
		for($i=0;$i<$count_contract_images;$i++){
			if(!empty($_FILES['contract_documents']['name'][$i]))
			{
				$_FILES['file']['name'] = $_FILES['contract_documents']['name'][$i];
				$_FILES['file']['type'] = $_FILES['contract_documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['contract_documents']['tmp_name'][$i];
				$_FILES['file']['error'] = $_FILES['contract_documents']['error'][$i];
				$_FILES['file']['size'] = $_FILES['contract_documents']['size'][$i];
				
				$config['upload_path'] = 'contract_images/'; 
				$config['allowed_types'] = 'jpg|jpeg|png|gif';
				$config['max_size'] = '5000';
				$config['file_name'] = $_FILES['contract_documents']['name'][$i];
				
				$this->load->library('upload',$config); 
				
				if($this->upload->do_upload('file'))
				{
					$uploadData = $this->upload->data();
					$filename = $uploadData['file_name'];
					//ADD IN DATABASE
					$this->db->set('document_type','contract_documents');
					$this->db->set('image',$filename);
					$this->db->set('contract_id',$contract_id);
					$this->db->insert('contract_documents');
				}
			}
		}
		
		//ADD OTHER DOCUMENTS
		$count_contract_images = count($_FILES['other_documents']['name']);
		for($i=0;$i<$count_contract_images;$i++){
			if(!empty($_FILES['other_documents']['name'][$i]))
			{
				$_FILES['file']['name'] = $_FILES['other_documents']['name'][$i];
				$_FILES['file']['type'] = $_FILES['other_documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['other_documents']['tmp_name'][$i];
				$_FILES['file']['error'] = $_FILES['other_documents']['error'][$i];
				$_FILES['file']['size'] = $_FILES['other_documents']['size'][$i];
				
				$config['upload_path'] = 'contract_images/'; 
				$config['allowed_types'] = 'jpg|jpeg|png|gif';
				$config['max_size'] = '5000';
				$config['file_name'] = $_FILES['other_documents']['name'][$i];
				
				$this->load->library('upload',$config); 
				
				if($this->upload->do_upload('file'))
				{
					$uploadData = $this->upload->data();
					$filename = $uploadData['file_name'];
					//ADD IN DATABASE
					$this->db->set('document_type','other_documents');
					$this->db->set('image',$filename);
					$this->db->set('contract_id',$contract_id);
					$this->db->insert('contract_documents');
				}
			}
		}
		
		$this->session->set_flashdata('message','Contract Add Successfully.');
		redirect('contractors/create_contract');
	}
	
	
	public function update_contract($contract_id)
	{
		$data = $this->input->post();
		$contractor_id = $data['contractor_id'];
		$course_id = $data['course_id'];
		$campus_id = $data['campus_id'];
		$contract_name = $data['contract_name'];
		$session = $data['session'];
		$contract_date = $data['contract_date'];
		$total_students = $data['total_students'];
		$per_student_fee = $data['per_student_fee'];
		
		$this->db->set('contractor_id',$contractor_id);
		$this->db->set('course_id',$course_id);
		$this->db->set('campus_id',$campus_id);
		$this->db->set('contract_name',$contract_name);
		$this->db->set('session',$session);
		$this->db->set('contract_date',$contract_date);
		$this->db->set('total_students',$total_students);
		$this->db->set('per_student_fee',$per_student_fee);
		$this->db->where('contract_id',$contract_id);
		$this->db->update('contracts');
		
		
		//ADD CONTRACT DOCUMENTS
		$count_contract_images = count($_FILES['contract_documents']['name']);
		for($i=0;$i<$count_contract_images;$i++){
			if(!empty($_FILES['contract_documents']['name'][$i]))
			{
				$_FILES['file']['name'] = $_FILES['contract_documents']['name'][$i];
				$_FILES['file']['type'] = $_FILES['contract_documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['contract_documents']['tmp_name'][$i];
				$_FILES['file']['error'] = $_FILES['contract_documents']['error'][$i];
				$_FILES['file']['size'] = $_FILES['contract_documents']['size'][$i];
				
				$config['upload_path'] = 'contract_images/'; 
				$config['allowed_types'] = 'jpg|jpeg|png|gif';
				$config['max_size'] = '5000';
				$config['file_name'] = $_FILES['contract_documents']['name'][$i];
				
				$this->load->library('upload',$config); 
				
				if($this->upload->do_upload('file'))
				{
					$uploadData = $this->upload->data();
					$filename = $uploadData['file_name'];
					//ADD IN DATABASE
					$this->db->set('document_type','contract_documents');
					$this->db->set('image',$filename);
					$this->db->set('contract_id',$contract_id);
					$this->db->insert('contract_documents');
				}
			}
		}
		
		//ADD OTHER DOCUMENTS
		$count_contract_images = count($_FILES['other_documents']['name']);
		for($i=0;$i<$count_contract_images;$i++){
			if(!empty($_FILES['other_documents']['name'][$i]))
			{
				$_FILES['file']['name'] = $_FILES['other_documents']['name'][$i];
				$_FILES['file']['type'] = $_FILES['other_documents']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['other_documents']['tmp_name'][$i];
				$_FILES['file']['error'] = $_FILES['other_documents']['error'][$i];
				$_FILES['file']['size'] = $_FILES['other_documents']['size'][$i];
				
				$config['upload_path'] = 'contract_images/'; 
				$config['allowed_types'] = 'jpg|jpeg|png|gif';
				$config['max_size'] = '5000';
				$config['file_name'] = $_FILES['other_documents']['name'][$i];
				
				$this->load->library('upload',$config); 
				
				if($this->upload->do_upload('file'))
				{
					$uploadData = $this->upload->data();
					$filename = $uploadData['file_name'];
					//ADD IN DATABASE
					$this->db->set('document_type','other_documents');
					$this->db->set('image',$filename);
					$this->db->set('contract_id',$contract_id);
					$this->db->insert('contract_documents');
				}
			}
		}
		
		$this->session->set_flashdata('message','Contract Updated Successfully.');
		redirect('contractors/edit_contract/'.$contract_id);
	}
	
	public function contract_payments($contract_id)
	{
		$this->load->model('student');
		$plan_created = $this->student->contractor_payment_paid($contract_id);
		$data['contract'] = $this->student->getSingleContract($contract_id);
		if(count($plan_created)>0)
		{
			redirect('contractors/contract_payments_paid/'.$contract_id);
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/contractor_payments', $data);
		$this->load->view('inc/footer');
	}
	
	public function add_contract_payment_plan($id)
	{
		$payment_plan = $this->input->post('payment_plan');
		
		if($payment_plan=='Custom Plan')
		{
			$total_installments = (count($this->input->post())-4)/3;
			
			for($i=1; $i<=$total_installments; $i++)
			{
				$dead_line = $this->input->post('dead_line_'.$i);
				$challan_no = $this->getChallanNo();
				
				$this->db->set('amount', $this->input->post('amount_'.$i));
				$this->db->set('dead_line', $dead_line);
				$this->db->set('contract_id', $id);
				$this->db->set('payment_plan', $payment_plan);
				$this->db->set('payment_comment', $this->input->post('payment_plan_'.$i));
				$this->db->set('challan_no', $challan_no);
				$this->db->set('add_by', $this->session->userdata('name'));
				$this->db->insert('payments');
			}
			
		}
		for($i=1; $i<=1; $i++)
		{
			$dead_line = $this->input->post('consulation_dead_line_'.$i);
			$challan_no = $this->getChallanNo();
			
			$this->db->set('amount', $this->input->post('consulation_fee_'.$i));
			$this->db->set('dead_line', $dead_line);
			$this->db->set('contract_id', $id);
			$this->db->set('payment_plan', 'consulation fee');
			$this->db->set('payment_comment', 'This fee for next exam # '.$this->input->post('consulation_payment_plan_'.$i).' 1st Year.');
			$this->db->set('challan_no', $challan_no);
			$this->db->set('add_by', $this->session->userdata('name'));
			$this->db->insert('payments');
		}
		redirect('contractors/contract_payments_paid/'.$id);
	}
	
	public function contract_payments_paid($contract_id)
	{
		$data['payments'] = $this->student->contractor_payment_paid($contract_id);
		$data['contract'] = $this->student->getSingleContract($contract_id);
		$data['no_of_students'] = $this->student->getContractStudents($contract_id);
		
		$data['total_contract_amount'] = $this->student->getCompleteContractAmount($contract_id);
		//$data['discount'] = $this->student->getStudentDiscount($contractor_id);
		$data['paid_fee'] = $this->student->getContractPaidFee($contract_id);
		$data['remaining_fee'] = $this->student->getContractRemainingFee($contract_id);
		$data['fee_should_pay'] = $this->student->getContractFeeShouldPay($contract_id);
        $data['account_numbers'] = $this->db->get_where('accounts',array('type'=>'1'))->result_array();

        $this->db->select('campuses.*');
        $this->db->from('campus_rules');
        $this->db->join('campuses','campuses.campus_id=campus_rules.campus_id','inner');
        $this->db->where('campus_rules.college_fee',1);
        $data['campuses'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('students');
        $this->db->where('contract_id',$contract_id);
        $data['students'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('contractors/contractor_payments_paid', $data);
		$this->load->view('inc/footer');
	}
	
	public function contract_paid_payment_action($contract_id)
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
		if (!$this->upload->do_upload('scan_challan')) {
			$data = array('msg' => $this->upload->display_errors());
			$scan_challan = '';

		} else { //else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
					$scan_challan = $data['upload_data']['file_name'];
				}
		}
		
		//if not successful, set the error message
		if (!$this->upload->do_upload('fine_application')) {
			$data = array('msg' => $this->upload->display_errors());
			$fine_application = '';

		} else { //else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
					$fine_application = $data['upload_data']['file_name'];
				}
		}
		
		//CHECK TO CHECK AMOUNT
		
		//echo $this->input->post('dead_line');
		$dead_line_date = date_create($this->input->post('dead_line'));
		$payment_paid_date = date_create($this->input->post('paid_date'));
		$diff=date_diff($dead_line_date,$payment_paid_date);
		$difference = $diff->format("%R%a");
		//echo $difference;
		if($difference>0)
		{
			$payment_plan = $this->input->post('payment_plan');
			if($payment_plan=='24 Installments')
			{
				$fine = $difference*10;
			}
			else
			{
				$fine = $difference*50;
			}
		}
		else
		{
			$fine = 0;
		}
		$amount_should_paid = $this->input->post('fee_amount')+$fine;
		
		
		if( $this->input->post('actual_amount') < $amount_should_paid)
		{
			$payment_id = $this->input->post('id');
			$next_payment_id = $this->student->getNextPaymentIdContractor($payment_id, $contract_id);
			
			if($next_payment_id!='' && count($next_payment_id)>0)
			{
				$data = array(
						'scan_challan' => $scan_challan,
						'fine_application' => $fine_application,
						'actual_amount' => $this->input->post('actual_amount'),
						'id' => $this->input->post('id'),
						'paid_date' => $this->input->post('paid_date'),
						'actual_paid_date'	=> date('Y-m-d'),
						'paid' => 1,
						'college_fee' => $this->input->post('college_fee'),
						'last_edit'	=> $this->session->userdata('name')
						);
				$next_payment_id = $next_payment_id[0]['id'];
				$extra_amount = $amount_should_paid - $this->input->post('actual_amount');
				$this->student->addExtraChargesToNextInstallment($next_payment_id, $extra_amount);
				$this->student->saveInstallment($data);	
			}
			else
			{
				$this->session->set_flashdata('error', 'Fee submitted failed');
				redirect('contractors/contract_payments_paid/'.$contract_id);
			}
		}
		else
		{	
		$data = array(
				'scan_challan' => $scan_challan,
				'fine_application' => $fine_application,
				'actual_amount' => $this->input->post('actual_amount'),
				'id' => $this->input->post('id'),
				'paid_date' => $this->input->post('paid_date'),
				'actual_paid_date'	=> date('Y-m-d'),
				'paid' => 1,
				'college_fee' => $this->input->post('college_fee'),
				'last_edit'	=> $this->session->userdata('name')
				);
		$this->student->saveInstallment($data);
		}
		redirect('contractors/contract_payments_paid/'.$contract_id);
	}
	
	public function getContracts()
	{
		$contractor_id = $this->input->post('contractor_id');
		$contracts = $this->db->get_where('contracts',array('contractor_id'=>$contractor_id))->result_array();
		$html = '';
		
		foreach($contracts as $contract)
		{
			$html.='<option value="'.$contract['contract_id'].'">'.$contract['contract_name'].'</option>';
		}
		
		echo $html;
		
	}
}
