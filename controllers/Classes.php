<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Classes extends CI_Controller {

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

		$this->load->model('teacher');

	}

	

	public function insert()

	{

		$data = $this->input->post();

		$this->clas->storeClass($data);

		$this->session->set_flashdata('message', 'Class added successfully!');

		redirect('classes/add_class');

	}

	

	public function update($id)

	{

		$data = $this->input->post();

		$this->clas->updateClass($data);

		$this->session->set_flashdata('message', 'Class updated successfully!');

		redirect('classes/edit_class/'.$id);

	}

	

	public function delete($id)

	{

		//DEACTIVE CLASS

	    $this->clas->deleteClass($id);



        $this->db->set('status',0);

        $this->db->where('class_id',$id);

        $this->db->update('students');



        $this->db->select('*');

        $this->db->from('students');

        $this->db->where('class_id',$id);

        $students = $this->db->get()->result_array();



        foreach($students as $student)

        {

            $this->db->set('delete_type','Delete');

            $this->db->set('student_id',$student['student_id']);

            $this->db->set('deleted_by',$this->session->userdata('name'));

            $this->db->set('reason','other');

            $this->db->set('refund_amount',0);

            $this->db->set('reason_detail','Class Delete');

            $this->db->set('image','');

            $this->db->set('approve_by',$this->session->userdata('name'));

            $this->db->set('status',1);

            $this->db->insert('deleted_students');

        }



		$this->session->set_flashdata('message', 'Class deleted successfully!');

		redirect('classes/all_classes');

	}

	

	public function index()

	{

		$data['classes'] = $this->clas->getTeacherClasses();

		

		$this->load->view('inc/header');

		$this->load->view('inc/sidebar');

		$this->load->view('classes/all_classes', $data);

		$this->load->view('inc/footer');

	}

	

	public function add_class()

	{

		$data['classes'] = $this->clas->getClasses();

		$data['campuses'] = $this->clas->getCampuses();

		$data['courses'] = $this->clas->getCourses();

		

		$this->load->view('inc/header');

		$this->load->view('inc/sidebar');

		$this->load->view('classes/add_class', $data);

		$this->load->view('inc/footer');

	}

	

	public function edit_class($id)

	{

		$data['class'] = $this->clas->editClass($id);

		$data['classes'] = $this->clas->getClasses();

		$data['campuses'] = $this->clas->getCampuses();

		$this->db->select('*');
		$this->db->from('courses');
		$this->db->like('campus_ids',$data['class'][0]['campus_id'],'both');
		$data['courses'] = $this->db->get()->result_array();

		
		$data['course_sessions'] = $this->db->get_where('course_sessions',array('course_id'=>$data['class'][0]['course_id']))->result_array();
		

		$this->load->view('inc/header');

		$this->load->view('inc/sidebar');

		$this->load->view('classes/edit_class', $data);

		$this->load->view('inc/footer');

	}

	

	public function all_classes()

	{

		$data['count'] = $this->clas->getClassesCount();

		$data['classes'] = $this->clas->getClasses();

		

    

		$this->load->view('inc/header');

		$this->load->view('inc/sidebar');

		$this->load->view('classes/all_classes', $data);

		$this->load->view('inc/footer');

	}

	

	public function students($class_id)

	{

		$data['students'] = $this->clas->getStudents($class_id);

		

		$this->load->view('inc/header');

		$this->load->view('inc/sidebar');

		$this->load->view('classes/students', $data);

		$this->load->view('inc/footer');

	}

	

	public function attendence($class_id)

	{

		$data['students'] = $this->clas->getStudents($class_id);

		

		if($this->input->post('start_date')=='' && $this->input->post('end_date')=='')

		{

			$data['start_date'] = date("Y-m-d", strtotime(date("Y-m-d")." -1 week"));

			$data['end_date'] = date("Y-m-d");

			

			$data['period'] = new DatePeriod(

				 new DateTime($data['start_date']),

				 new DateInterval('P1D'),

				 new DateTime($data['end_date'])

			);

		}

		else

		{

			$data['start_date'] = $this->input->post('start_date');

			$data['end_date'] = $this->input->post('end_date');

			

			$data['period'] = new DatePeriod(

				 new DateTime($data['start_date']),

				 new DateInterval('P1D'),

				 new DateTime($data['end_date'])

			);

		}

		

		$this->load->view('inc/header');

		$this->load->view('inc/sidebar');

		$this->load->view('classes/attendence', $data);

		$this->load->view('inc/footer');

	}

	public function getCourseSessions()
	{
		$course_id = $this->input->post('course_id');
		$course_sessions = $this->db->get_where('course_sessions',array('course_id'=>$course_id))->result_array();

		$html='';
		foreach($course_sessions as $course_session)
		{
			$html.='<option value="'.$course_session['session_name'].'">'.$course_session['session_name'].'</option>';
		}
		echo $html;
	}

	public function getCampusCourses()
	{
		$campus_id = $this->input->post('campus_id');

		$this->db->select('*');
		$this->db->from('courses');
		$this->db->like('campus_ids',$campus_id,'both');
		$courses = $this->db->get()->result_array();

		$html = '';
		$html.= '<option value="">SELECT COURSE</option>';
		foreach($courses as $course)
		{
			$html.= '<option value="'.$course['course_id'].'">'.$course['course_name'].'</option>';
		}
		echo $html;
	}

	public function getCourseDetails()
	{
		$course_id = $this->input->post('course_id');
		$this->db->get_where('courses',array('course_id'=>$course_id))->result_array();
	}
	
	public function getCourseDetail()
	{
	    $campus_id = $this->input->post('campus_id');
	    $course_id = $this->input->post('course_id');
	    $course_session = $this->input->post('course_session');
	    
	    $sessionDetail = $this->db->get_where('course_sessions',array('course_id'=>$course_id,'session_name'=>$course_session))->result_array();
	    
	    echo json_encode($sessionDetail);
	}

}

