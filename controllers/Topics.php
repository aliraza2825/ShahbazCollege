<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Topics extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
	}
	
	public function add_topic()
	{
		$data['courses']=$this->db->get('courses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('topics/add_topic', $data);
		$this->load->view('inc/footer');
	}
	
	public function getSubjects()
	{
		$course_id = $this->input->post('course_id');
		$subjects = $this->db->get_where('course_subjects',array('course_id'=>$course_id))->result_array();
		$html='';
		$html.='<option value="">Select Subject</option>';
		foreach($subjects as $subject)
		{
			$html.='<option value="'.$subject['course_subject_id'].'">'.$subject['subject_name'].'</option>';
		}
		echo $html;
	}
	
	public function getChapters()
	{
		$course_subject_id = $this->input->post('course_subject_id');
		$chapters = $this->db->get_where('chapters',array('course_subject_id'=>$course_subject_id))->result_array();
		$html='';
		foreach($chapters as $chapter)
		{
			$html.='<option value="'.$chapter['chapter_id'].'">'.$chapter['chapter_name'].'</option>';
		}
		echo $html;
	}
	
	public function insert()
	{
		$data=$this->input->post();
		
		$check = $this->db->get_where('topics', array('topic_name'=>$this->input->post('topic_name'), 'course_subject_id'=>$this->input->post('course_subject_id'),'chapter_id'=>$this->input->post('chapter_id'),'course_id'=>$this->input->post('course_id')))->result_array();
		if(count($check)>0)
		{
			$this->session->set_flashdata('error', 'Topic already added.');
			redirect('topics/add_topic');
		}
		else
		{
			foreach(@$data as $k=>$value){
				$this->db->set(''.$k.'', $value);
			}
			$this->db->insert('topics');
			$this->session->set_flashdata('message', 'Topic added successfully');
			redirect('topics/add_topic');
		}
	}
	
	public function all_topics()
	{
		$this->db->select('*');
		$this->db->from('topics');
		$this->db->join('chapters','chapters.chapter_id=topics.chapter_id','left');
		$this->db->join('course_subjects','course_subjects.course_subject_id=chapters.course_subject_id','left');
		$this->db->join('courses','courses.course_id=chapters.course_id','left');
		$data['topics'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('topics/all_topics', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_topic($topic_id)
	{
		$data['courses']=$this->db->get('courses')->result_array();
		$data['current_topic'] = $this->db->get_where('topics',array('topic_id'=>$topic_id))->result_array();
		$data['subjects'] = $this->db->get_where('course_subjects',array('course_id'=>@$data['current_topic'][0]['course_id']))->result_array();
		$data['chapters'] = $this->db->get_where('chapters',array('course_subject_id'=>@$data['current_topic'][0]['course_subject_id']))->result_array();
		$data['topics'] = $this->db->get_where('topics',array('topic_id'=>@$data['current_topic'][0]['topic_id']))->result_array();
		
		
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('topics/edit_topic', $data);
		$this->load->view('inc/footer');
	}
	
	public function update($topic_id)
	{
		$data=$this->input->post();
		foreach(@$data as $k=>$value)
		{
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('topic_id',$topic_id);
		$this->db->update('topics');
		$this->session->set_flashdata('message', 'Topic updated successfully');
		redirect('topics/edit_topic/'.$topic_id);
	}
	
	public function delete($topic_id)
	{
		$this->db->where('topic_id',$topic_id);
		$this->db->delete('topics');
		
		$this->session->set_flashdata('message','Topic deleted successfully!');
		redirect('topics/all_topics');
	}
}
