<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assignments extends CI_Controller {

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
		//$this->load->library('Email_reader');	
	}
	public function add_assignment()
	{
		if($this->session->userdata('role')!='Admin')
		{
			//$access = checkUserAccess();
			//$subject_ids = @explode(',',$access[0]['assignment_subject_ids']);
			//$this->db->where_in('course_subject_id',$subject_ids);
		}

        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
        $data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();
		
		//$data['subjects'] = $this->db->get('course_subjects')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('assignments/add_assignment',$data);
		$this->load->view('inc/footer');
	}

    public function center()
    {
        $this->session->set_userdata(
            array
            (
                'assignment_topic_id'=>$this->input->post('topic_id'),
                'assignment_subject_id'=>$this->input->post('subject_id'),
                'assignment_chapter_id'=>$this->input->post('chapter_id'),
                'assignment_course_id'=>$this->input->post('course_id'),
                'assignment_class'=>$this->input->post('class'),
                'assignment_mcqs'=>$this->input->post('mcqs'),
                'assignment_marks_mcq'=>$this->input->post('marks_mcq'),
                'assignment_short_questions'=>$this->input->post('short_questions'),
                'assignment_short_question_mcq'=>$this->input->post('short_question_mcq'),
                'assignment_marks_practical'=>$this->input->post('marks_practical'),
                'assignment_practical_id'=>$this->input->post('practical_id'),
                'assignment_start_date'=>$this->input->post('start_date'),
                'assignment_end_date'=>$this->input->post('end_date')
            )
        );
        redirect('assignments/create_assignment');
    }

    public function create_assignment()
    {
        if($this->session->userdata('assignment_mcqs')>0)
        {
            $this->db->select();
            $this->db->from('questions');
            $this->db->where_in('topic_id',$this->session->userdata('assignment_topic_id'));
            $this->db->where_in('type',array('radio','multiple'));
            $this->db->order_by('question_id','RANDOM');
            $this->db->limit($this->session->userdata('assignment_mcqs'));
            $mcqs = $this->db->get()->result_array();
        }

        if($this->session->userdata('assignment_short_questions')>0)
        {
            $this->db->select();
            $this->db->from('questions');
            $this->db->where_in('topic_id',$this->session->userdata('assignment_topic_id'));
            $this->db->where_in('type',array('short-question'));
            $this->db->order_by('question_id','RANDOM');
            $this->db->limit($this->session->userdata('assignment_short_questions'));
            $short_questions = $this->db->get()->result_array();
        }

        if(@count(@$this->session->userdata('assignment_practical_id'))>0)
        {
            $this->db->select();
            $this->db->from('practicals');
            $this->db->where_in('practical_id',$this->session->userdata('assignment_practical_id'));
            $practicals = $this->db->get()->result_array();
        }


        //$data['campus_name'] = $this->db->get_where('campuses',array('campus_id'=>$this->session->userdata('college_paper_campus_id')))->row()->campus_name;
        //$data['campus_logo'] = $this->db->get_where('campuses',array('campus_id'=>$this->session->userdata('college_paper_campus_id')))->row()->logo;


        $data['subject_name'] = $this->db->get_where('course_subjects',array('course_subject_id'=>$this->session->userdata('assignment_subject_id')))->row()->subject_name;
        $data['total_marks'] = ($this->session->userdata('assignment_mcqs')*$this->session->userdata('assignment_marks_mcq'))+($this->session->userdata('assignment_short_questions')*$this->session->userdata('assignment_short_question_mcq'))+(@$this->session->userdata('assignment_marks_practical')*@count(@$this->session->userdata('assignment_practical_id')));
        $data['mcqs_marks'] = ($this->session->userdata('assignment_mcqs')*$this->session->userdata('assignment_marks_mcq'));
        $data['short_question_marks'] = ($this->session->userdata('assignment_short_questions')*$this->session->userdata('assignment_short_question_mcq'));
        $data['mcqs'] = @$mcqs;
        $data['short_questions'] = @$short_questions;
        $data['practicals'] = @$practicals;
        $data['practical_marks'] = @$this->session->userdata('assignment_marks_practical')*@count(@$this->session->userdata('assignment_practical_id'));

        //DATA FOR AJAX CALL TO SAVE PAPER
        $data['course_id'] = $this->session->userdata('assignment_course_id');
        $data['subject_id'] = $this->session->userdata('assignment_subject_id');
        $data['chapter_id'] = $this->session->userdata('assignment_chapter_id');
        $data['topic_ids'] = $this->session->userdata('assignment_topic_id');
        $data['class'] = $this->session->userdata('assignment_class');

        $this->load->view('assignments/display_assignment',$data);
    }

    public function saveAssignment()
    {
        $course_id = $this->input->post('course_id');
        $subject_id = $this->input->post('subject_id');
        $chapter_id = $this->input->post('chapter_id');
        $topic_ids = $this->input->post('topic_ids');
        $class = $this->input->post('cclass');
        $total_marks = $this->input->post('total_marks');
        $add_by = $this->input->post('add_by');
        $mcqs = $this->input->post('mcqs');
        $short_questions = $this->input->post('short_questions');
        $practical_ids = $this->input->post('practical_ids');
        $mcqs_marks = $this->input->post('mcqs_marks');
        $short_questions_marks = $this->input->post('short_questions_marks');
        $practical_marks = $this->input->post('practical_marks');

        $this->db->set('course_id',$course_id);
        $this->db->set('subject_id',$subject_id);
        $this->db->set('chapter_id',$chapter_id);
        $this->db->set('topic_ids',$topic_ids);
        $this->db->set('class',$class);
        $this->db->set('total_marks',$total_marks);
        $this->db->set('add_by',$add_by);
        $this->db->set('mcqs',$mcqs);
        $this->db->set('short_questions',$short_questions);
        $this->db->set('practicals',$practical_ids);
        $this->db->set('mcqs_marks',$mcqs_marks);
        $this->db->set('short_questions_marks',$short_questions_marks);
        $this->db->set('practical_marks',$practical_marks);
        $this->db->set('start_date',$this->session->userdata('assignment_start_date'));
        $this->db->set('end_date',$this->session->userdata('assignment_end_date'));
        $this->db->set('date',date('Y-m-d'));

        $this->db->insert('assignments');
    }

    public function all_assignments()
    {
        $data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();

        if($this->input->post('submit')==1)
        {
            $course_id = $this->input->post('course_id');
            $subject_id = $this->input->post('subject_id');
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');

            $this->db->select('assignments.*,courses.course_name,course_subjects.subject_name as subject_name');
            $this->db->from('assignments');
            $this->db->join('courses','courses.course_id=assignments.course_id','inner');
            $this->db->join('course_subjects','course_subjects.course_subject_id=assignments.subject_id','inner');
            if($course_id!='')
            {
                $this->db->where('assignments.course_id',$course_id);
            }
            if($subject_id!='')
            {
                $this->db->where('assignments.subject_id',$subject_id);
            }
            $this->db->where(array('assignments.start_date>='=>$start_date,'assignments.end_date<='=>$end_date));

            $data['assignments'] = $this->db->get()->result_array();
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('assignments/all_assignments',$data);
        $this->load->view('inc/footer');
    }

    public function viewassignment($assignment_id)
    {
        $data['assignments'] = $this->db->get_where('assignments',array('assignment_id'=>$assignment_id))->result_array();

        $this->load->view('assignments/view_assignment',$data);
    }

    public function viewsolveassignment($assignment_id)
    {
        $data['assignments'] = $this->db->get_where('assignments',array('assignment_id'=>$assignment_id))->result_array();

        $this->load->view('assignments/view_solve_assignment',$data);
    }

    public function uncheck_assignments()
    {
        $data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();

        if($this->input->post('submit')==1)
        {
            $course_id = $this->input->post('course_id');
            $subject_id = $this->input->post('subject_id');
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');

            $this->db->select('assignments.*,courses.course_name,course_subjects.subject_name as subject_name,students.*');
            $this->db->from('assignments');
            $this->db->join('courses','courses.course_id=assignments.course_id','inner');
            $this->db->join('course_subjects','course_subjects.course_subject_id=assignments.subject_id','inner');
            $this->db->join('assignment_results','assignment_results.assignment_id=assignments.assignment_id','inner');
            $this->db->join('students','students.student_id=assignment_results.student_id','inner');
            $this->db->where('assignment_results.checked',0);
            if($course_id!='')
            {
                $this->db->where('assignments.course_id',$course_id);
            }
            if($subject_id!='')
            {
                $this->db->where('assignments.subject_id',$subject_id);
            }
            $this->db->where(array('assignments.start_date>='=>$start_date,'assignments.end_date<='=>$end_date));

            $data['assignments'] = $this->db->get()->result_array();
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('assignments/uncheck_assignments',$data);
        $this->load->view('inc/footer');
    }

    public function check_assignments()
    {
        $data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();

        if($this->input->post('submit')==1)
        {
            $course_id = $this->input->post('course_id');
            $subject_id = $this->input->post('subject_id');
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');

            $this->db->select('assignments.*,courses.course_name,course_subjects.subject_name as subject_name,students.*');
            $this->db->from('assignments');
            $this->db->join('courses','courses.course_id=assignments.course_id','inner');
            $this->db->join('course_subjects','course_subjects.course_subject_id=assignments.subject_id','inner');
            $this->db->join('assignment_results','assignment_results.assignment_id=assignments.assignment_id','inner');
            $this->db->join('students','students.student_id=assignment_results.student_id','inner');
            $this->db->where('assignment_results.checked',1);
            if($course_id!='')
            {
                $this->db->where('assignments.course_id',$course_id);
            }
            if($subject_id!='')
            {
                $this->db->where('assignments.subject_id',$subject_id);
            }
            $this->db->where(array('assignments.start_date>='=>$start_date,'assignments.end_date<='=>$end_date));

            $data['assignments'] = $this->db->get()->result_array();
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('assignments/check_assignments',$data);
        $this->load->view('inc/footer');
    }

    public function solvedassignment($assignment_id,$student_id)
    {
        $this->db->select('*');
        $this->db->from('assignment_results');
        $this->db->join('assignments','assignments.assignment_id=assignment_results.assignment_id','inner');
        $this->db->where(array('assignment_results.student_id'=>$student_id,'assignment_results.assignment_id'=>$assignment_id));
        $data['assignment'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('assignments/student_assignment',$data);
        $this->load->view('inc/footer');
    }

    public function donechecking()
    {
        $obtain_mcqs_marks              =   json_encode($this->input->post('obtain_mcqs_marks'));
        $obtain_short_questions_marks   =   json_encode($this->input->post('obtain_short_questions_marks'));
        $obtain_practical_marks         =   json_encode($this->input->post('obtain_practical_marks'));
        $student_id                     =   $this->input->post('student_id');
        $assignment_id                  =   $this->input->post('assignment_id');

        $this->db->set('obtain_mcqs_marks',$obtain_mcqs_marks);
        $this->db->set('obtain_short_questions_marks',$obtain_short_questions_marks);
        $this->db->set('obtain_practical_marks',$obtain_practical_marks);
        $this->db->set('check_by',$this->session->userdata('name'));
        $this->db->set('checked',1);
        $this->db->where(array('student_id'=>$student_id,'assignment_id'=>$assignment_id));
        $this->db->update('assignment_results');

        $this->session->set_flashdata('message','Assignment Checked Successfully');
        redirect('assignments/solvedassignment/'.$assignment_id.'/'.$student_id);
    }

    public function deleteassignment($assignment_id)
    {
        $this->db->where('assignment_id',$assignment_id);
        $this->db->delete('assignments');
        $this->db->where('assignment_id',$assignment_id);
        $this->db->delete('assignment_results');
        $this->session->set_flashdata('message','Assignment Deleted Successfully');
        redirect('assignments/all_assignments');
    }
}
