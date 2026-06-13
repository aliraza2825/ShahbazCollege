<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Courses extends CI_Controller {
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
	}
	
	public function add_course()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['classes'] = $this->db->get('classes')->result_array();
		$data['courses']=$this->db->get('courses')->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('courses/courses', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_courses()
	{
		$data['courses']=$this->db->get('courses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('courses/all_courses', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_course()
	{
		$data=$this->input->post();
		
		$check = $this->db->get_where('courses', array('course_name'=>$this->input->post('course_name')))->result_array();
		if(count($check)>0)
		{
			$this->session->set_flashdata('error', 'Course already added.');
			redirect('test_engine/courses');
		}
		
		foreach(@$data as $k=>$value)
		{
			if($k!= 'campus_ids')
			$this->db->set(''.$k.'', $value);
		}
		
		$campus_ids = $this->input->post('campus_ids');
		$campus_ids = implode(',',$campus_ids);
		$this->db->set('campus_ids', $campus_ids);
		$this->db->insert('courses');
		$this->session->set_flashdata('message', 'Course added successfully');
		redirect('courses/add_course');
	}
	
	public function add_syllabus()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['classes'] = $this->db->get('classes')->result_array();
		$data['courses']=$this->db->get('courses')->result_array();
		
		$this->db->select('syllabus.*, courses.course_name, course_subjects.subject_name, topics.topic_name');
		//$this->db->select('syllabus.*, topics.topic_name');
		$this->db->from('syllabus');
		//$this->db->join('classes', 'classes.class_id=syllabus.class_id', 'inner');
		//$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=syllabus.course_id', 'inner');
		$this->db->join('course_subjects', 'course_subjects.course_subject_id=syllabus.subject_id', 'inner');
		$this->db->join('topics', 'topics.topic_id=syllabus.topic_id', 'inner');
		$this->db->group_by('syllabus.from_date');
		$this->db->group_by('syllabus.to_date');
		$this->db->group_by('syllabus.topic_id');
		$data['syllabuss'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('courses/syllabus', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_syllabus()
	{
		$count_topics = count($this->input->post('topic_ids'));
		$count_classes = count($this->input->post('class_ids'));
		//$loop = $count_classes*$count_topics;
		
		$topic_ids = $this->input->post('topic_ids');
		$class_ids = $this->input->post('class_ids');
		$course_id = $this->input->post('course_id');
		$subject_id = $this->input->post('subject_id');
		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');
		$add_by = $this->input->post('add_by');
		$last_edit = $this->input->post('last_edit');
		
		$myloop=0;
		for($i=0; $i<$count_classes; $i++)
		{
			for($a=0; $a<$count_topics; $a++)
			{
				$check_record = $this->db->get_where('syllabus', array('class_id'=>$class_ids[$i], 'course_id'=>$course_id, 'subject_id'=>$subject_id, 'topic_id'=>$topic_ids[$a]))->result_array();
				
				if(count($check_record)>0)
				{
					//$this->session->set_flashdata('error', 'Syllabus Already Exist.');
					//redirect('courses/add_syllabus');
				}
				else
				{
					$this->db->set('class_id', $class_ids[$i]);
					$this->db->set('course_id', $course_id);
					$this->db->set('subject_id', $subject_id);
					$this->db->set('topic_id', $topic_ids[$a]);
					$this->db->set('from_date', $from_date[$a]);
					$this->db->set('to_date', $to_date[$a]);
					$this->db->set('study',0);
					$this->db->set('revision',0);
					$this->db->set('add_by',$add_by);
					$this->db->set('last_edit',$last_edit);
					$this->db->insert('syllabus');	
					
				}
			}
			
		}
		$this->session->set_flashdata('message', 'Syllabus Added Successfully.');
		redirect('courses/add_syllabus');
	}
	
	public function edit_syllabus($topic_id, $from_date, $to_date)
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['classes'] = $this->db->get('classes')->result_array();
		$data['courses']=$this->db->get('courses')->result_array();
		
		$data['syllabuses'] = $this->db->get_where('syllabus', array('topic_id'=>$topic_id, 'from_date'=>$from_date, 'to_date'=>$to_date))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('courses/edit_syllabus', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_syllabus($topic_id, $from_date, $to_date)
	{
		$this->db->where(array('topic_id'=>$topic_id, 'from_date'=>$from_date, 'to_date'=>$to_date));
		$this->db->delete('syllabus');
				
		$count_classes = count($this->input->post('class_ids'));
		
		$class_ids = $this->input->post('class_ids');
		
		$course_id = $this->input->post('course_id');
		$subject_id = $this->input->post('subject_id');
		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');
		$last_edit = $this->input->post('last_edit');
		
		$myloop=0;
		for($i=0; $i<$count_classes; $i++)
		{
				$this->db->set('class_id', $class_ids[$i]);
				$this->db->set('course_id', $course_id);
				$this->db->set('subject_id', $subject_id);
				$this->db->set('topic_id', $topic_id);
				$this->db->set('from_date', $from_date);
				$this->db->set('to_date', $to_date);
				$this->db->set('study',0);
				$this->db->set('revision',0);
				$this->db->set('add_by',$last_edit);
				$this->db->set('last_edit',$last_edit);
				$this->db->insert('syllabus');
		}
		$this->session->set_flashdata('message', 'Syllabus Updated Successfully.');
		redirect('courses/add_syllabus');
	}
	
	public function delete_course($course_id)
	{
		$this->db->where('course_id', $course_id);
		$this->db->delete('courses');
		$this->session->set_flashdata('message', 'Course deleted successfully');
		redirect('courses/all_courses');
	}
	
	public function edit_course($course_id)
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['classes'] = $this->db->get('classes')->result_array();
		$data['course'] = $this->db->get_where('courses', array('course_id'=>$course_id))->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('courses/edit_course', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_course($course_id)
	{
		$data= $this->input->post();
		foreach(@$data as $k=>$value)
		{
			if($k!= 'campus_ids')
			$this->db->set(''.$k.'', $value);
		}
		
		$campus_ids = $this->input->post('campus_ids');
		$campus_ids = implode(',',$campus_ids);
		$this->db->set('campus_ids', $campus_ids);
		
		$this->db->where('course_id', $course_id);
		$this->db->update('courses');
		$this->session->set_flashdata('message', 'Course Updated successfully');
		redirect('courses/edit_course/'.$course_id);
	}
	
	public function getCourseSubjects()
	{
		$course_id = $this->input->post('course_id');
		$subjects = $this->db->get_where('course_subjects', array('course_id'=>$course_id))->result_array();
		$html='';
		$html.='<option value="">SELECT SUBJECT NAME</option>';
		foreach($subjects as $subject)
		{
			$html.='<option value="'.$subject['course_subject_id'].'">'.$subject['subject_name'].'</option>';
		}
		echo $html;
	}
}
