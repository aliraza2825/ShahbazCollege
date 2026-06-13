<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Chapters extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
	}
	
	public function add_chapter()
	{
		$data['courses']=$this->db->get('courses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('chapters/add_chapter', $data);
		$this->load->view('inc/footer');
	}
	
	public function getSubjects()
	{
		$course_id = $this->input->post('course_id');
		$subjects = $this->db->get_where('course_subjects',array('course_id'=>$course_id))->result_array();
		$html='';
		foreach($subjects as $subject)
		{
			$html.='<option value="'.$subject['course_subject_id'].'">'.$subject['subject_name'].'</option>';
		}
		echo $html;
	}

    public function getClasses()
    {
        $course_id = $this->input->post('course_id');
        $campus_id = $this->input->post('campus_id');

        $subjects = $this->db->get_where('classes',array('course_id'=>$course_id,'campus_id'=>$campus_id))->result_array();
        $html='';
        foreach($subjects as $subject)
        {
            $html.='<option value="'.$subject['class_id'].'">'.$subject['name'].'</option>';
        }
        echo $html;
    }
	
	public function insert()
	{
		$data = $this->input->post();
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('chapters');
		
		$this->session->set_flashdata('message','Chapter inserted successfully!');
		redirect('chapters/add_chapter');
	}
	
	public function all_chapters()
	{
		$this->db->select('*');
		$this->db->from('chapters');
		$this->db->join('course_subjects','course_subjects.course_subject_id=chapters.course_subject_id','inner');
		$this->db->join('courses','courses.course_id=chapters.course_id','inner');
		$data['chapters'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('chapters/all_chapters', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_chapter($chapter_id)
	{
		$data['courses']=$this->db->get('courses')->result_array();
		$data['chapter'] = $this->db->get_where('chapters',array('chapter_id'=>$chapter_id))->result_array();
		$data['subjects'] = $this->db->get_where('course_subjects',array('course_id'=>$data['chapter'][0]['course_id']))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('chapters/edit_chapter', $data);
		$this->load->view('inc/footer');
	}
	
	public function update($chapter_id)
	{
		$data = $this->input->post();
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('chapter_id',$chapter_id);
		$this->db->update('chapters');
		
		$this->session->set_flashdata('message','Chapter updated successfully!');
		redirect('chapters/edit_chapter/'.$chapter_id);
	}
	
	public function delete($chapter_id)
	{
		$this->db->where('chapter_id',$chapter_id);
		$this->db->delete('chapters');
		
		$this->session->set_flashdata('message','Chapter deleted successfully!');
		redirect('chapters/all_chapters');
	}
}
