<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Collegepapers extends CI_Controller {
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
	
	public function add_paper()
	{
		$data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
		$data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();
		//$data['subjects'] = $this->db->get_where('course_subjects',array('status'=>1))->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('collegepapers/add_paper',$data);
		$this->load->view('inc/footer');
	}
	
	public function getSubjects()
	{
		$access = checkUserAccess();
		$subject_ids = @explode(',',$access[0]['assignment_subject_ids']);
		
		$course_id = $this->input->post('course_id');
		$class = $this->input->post('period');
		
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->where(array('course_id'=>$course_id,'status'=>1));
		$this->db->where("(subject_year='$class' OR subject_semester='$class')", NULL, FALSE);
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('course_subject_id', $subject_ids);
		}
		$subjects = $this->db->get()->result_array();
		$html='';
	
		foreach($subjects as $subject)
		{
			$html.='<option value="'.$subject['course_subject_id'].'">'.$subject['subject_name'].'</option>';
		}
		echo $html;
		exit();
	}
	
	public function getChapters()
	{
		$subject_id = $this->input->post('subject_id');


		$chapters = $this->db->where_in('course_subject_id',$subject_id)->get('chapters')->result_array();
		$html='<option value="">Select Chapter</option>';
		foreach($chapters as $chapter)
		{
			$html.='<option value="'.$chapter['chapter_id'].'">'.$chapter['chapter_name'].'</option>';
		}
		echo $html;
		exit();
	}
	
	public function getTopics()
	{
		$chapter_id = $this->input->post('chapter_id');
		$topics = $this->db->where_in('chapter_id',$chapter_id)->get('topics')->result_array();
		$html='';
		$i=1;
		foreach($topics as $topic)
		{
			$html.='<div class="clearfix"></div><label class="checkbox-inline"><input class="topic_id" type="checkbox" id="inlineCheckbox'.$i.'" name="topic_id[]" value="'.$topic['topic_id'].'" checked/> '.$topic['topic_name'].' </label><br />';
			$i++;
		}
		echo $html;
		exit();
	}
	
	public function getPracticals()
	{
		$subject_id = $this->input->post('subject_id');
		$practicals = $this->db->where_in('subject_id',$subject_id)->get_where('practicals',array('status'=>1))->result_array();
		$html='';
		$i=1;
		
		foreach($practicals as $practical)
		{
			$html.='<div class="clearfix"></div><label class="checkbox-inline"><input class="practical_id" type="checkbox" id="inlineCheckbox'.$i.'" name=
			"practical_id[]" value="'.$practical['practical_id'].'" /> '.$practical['practical_name'].' </label><br />';
			$i++;
		}
		echo $html;
		exit();
	}
	
	public function center()
	{
		$this->session->set_userdata(
										array
										(
											'college_paper_topic_id'=>$this->input->post('topic_id'),
											'college_paper_campus_id'=>$this->input->post('campus_id'),
											'college_paper_session'=>$this->input->post('class_session'),
											'college_paper_subject_id'=>$this->input->post('subject_id'),
											'college_paper_chapter_id'=>$this->input->post('chapter_id'),
											'college_paper_section'=>$this->input->post('section'),
											'college_paper_course_id'=>$this->input->post('course_id'),
											'college_paper_class'=>$this->input->post('class'),
											'college_paper_attendence_wise'=>$this->input->post('attendence_wise'),
											'college_paper_mcqs'=>$this->input->post('mcqs'),
											'college_paper_marks_mcq'=>$this->input->post('marks_mcq'),
											'college_paper_short_questions'=>$this->input->post('short_questions'),
											'college_paper_short_question_mcq'=>$this->input->post('short_question_mcq'),
											'college_paper_marks_practical'=>$this->input->post('marks_practical'),
											'college_paper_practical_id'=>$this->input->post('practical_id'),
											'college_paper_short_question_lines'=>$this->input->post('short_question_lines'),
											'college_paper_practical_lines'=>$this->input->post('practical_lines')
										)
									);
		redirect('collegepapers/create_paper');
	}
	
	public function create_paper()
	{
		if($this->session->userdata('college_paper_mcqs')>0)
		{
			$this->db->select();
			$this->db->from('questions');
			$this->db->where_in('topic_id',$this->session->userdata('college_paper_topic_id'));
			$this->db->where_in('type',array('radio','multiple'));
			$this->db->order_by('question_id','RANDOM');
			$this->db->limit($this->session->userdata('college_paper_mcqs'));
			$mcqs = $this->db->get()->result_array();
		}
		
		if($this->session->userdata('college_paper_short_questions')>0)
		{
			$this->db->select();
			$this->db->from('questions');
			$this->db->where_in('topic_id',$this->session->userdata('college_paper_topic_id'));
			$this->db->where_in('type',array('short-question'));
			$this->db->order_by('question_id','RANDOM');
			$this->db->limit($this->session->userdata('college_paper_short_questions'));
			$short_questions = $this->db->get()->result_array();
		}
		
		if(count($this->session->userdata('college_paper_practical_id'))>0)
		{
			$this->db->select();
			$this->db->from('practicals');
			$this->db->where_in('practical_id',$this->session->userdata('college_paper_practical_id'));
			$practicals = $this->db->get()->result_array();
		}
		
		
		$data['campus_name'] = $this->db->get_where('campuses',array('campus_id'=>$this->session->userdata('college_paper_campus_id')))->row()->campus_name;
		$data['campus_logo'] = $this->db->get_where('campuses',array('campus_id'=>$this->session->userdata('college_paper_campus_id')))->row()->logo;
		
		
		$subject_names = $this->db->where_in('course_subject_id',$this->session->userdata('college_paper_subject_id'))->get('course_subjects')->result_array();
		
		$data['subject_name']='';
		foreach($subject_names as $subject)
		{
			$data['subject_name'].=$subject['subject_name'].' ';
		}
		$data['total_marks'] = ($this->session->userdata('college_paper_mcqs')*$this->session->userdata('college_paper_marks_mcq'))+($this->session->userdata('college_paper_short_questions')*$this->session->userdata('college_paper_short_question_mcq'))+(@$this->session->userdata('college_paper_marks_practical')*count($this->session->userdata('college_paper_practical_id')));
		$data['mcqs_marks'] = ($this->session->userdata('college_paper_mcqs')*$this->session->userdata('college_paper_marks_mcq'));
		$data['short_question_marks'] = ($this->session->userdata('college_paper_short_questions')*$this->session->userdata('college_paper_short_question_mcq'));
		$data['mcqs'] = @$mcqs;
		$data['short_questions'] = @$short_questions;
		$data['practicals'] = @$practicals;
		$data['practical_marks'] = @$this->session->userdata('college_paper_marks_practical')*count($this->session->userdata('college_paper_practical_id'));
		
		if($this->session->userdata('college_paper_attendence_wise')==1)
		{
			$campus_code = $this->db->get_where('campuses',array('campus_id'=>$this->session->userdata('college_paper_campus_id')))->row()->campus_code;
			$this->db->select('students.*');
			$this->db->from('students');
			if($this->session->userdata('college_paper_session')!='all')
			{
				$this->db->join('classes','classes.class_id=students.class_id','inner');
			}
			$this->db->join('machine_data','machine_data.teacher_student_id=students.student_id','inner');
			$this->db->join('attendence','machine_data.machine_id=attendence.machine_user_id','inner');
			$this->db->where(array('attendence.campus_code'=>$campus_code,'attendence.time>='=>date('Y-m-d').' 00:00:00','time<='=>date('Y-m-d').' 23:59:00'));
			if($this->session->userdata('college_paper_session')!='all')
			{
				$this->db->where_in('classes.session',$this->session->userdata('college_paper_session'));
			}
			$data['students']=$this->db->get()->result_array();
			
			
		}
		
		
		
		//DATA FOR AJAX CALL TO SAVE PAPER
		$data['campus_id'] = $this->session->userdata('college_paper_campus_id');
		$data['class'] = $this->session->userdata('college_paper_section');
		$data['course_id'] = $this->session->userdata('college_paper_course_id');
		$data['subject_id'] = implode(',',$this->session->userdata('college_paper_subject_id'));
		$data['chapter_id'] = implode(',',$this->session->userdata('college_paper_chapter_id'));
		$data['topic_ids'] = $this->session->userdata('college_paper_topic_id');
		//print_r($data['topic_ids']); exit;
		if($this->session->userdata('college_paper_attendence_wise')==1)
		{
			$data['print_type'] = 'Attendence Wise';
		}
		else
		{
			$data['print_type'] = 'Quantity Wise';
		}
		$data['class'] = $this->session->userdata('college_paper_class');
		
		$this->load->view('collegepapers/display_paper',$data);
	}
	
	public function savePaper(){
		$campus_id = $this->input->post('campus_id');
		$course_id = $this->input->post('course_id');
		//$class_session = $this->input->post('class_session');
		$subject_id = $this->input->post('subject_id');
		$chapter_id = $this->input->post('chapter_id');
		$topic_ids = $this->input->post('topic_ids');
		$class = $this->input->post('cclass');
		$total_marks = $this->input->post('total_marks');
		$add_by = $this->input->post('add_by');
		$mcqs = $this->input->post('mcqs');
		$short_questions = $this->input->post('short_questions');
		$print_type = $this->input->post('print_type');
		$practical_ids = $this->input->post('practical_ids');
		$mcqs_marks = $this->input->post('mcqs_marks');
		$short_questions_marks = $this->input->post('short_questions_marks');
		$practical_marks = $this->input->post('practical_marks');
		
		$this->db->set('campus_id',$campus_id);
		$this->db->set('exam_id',$this->input->post('exam_id'));
		$this->db->set('course_id',$course_id);
		$this->db->set('session',implode(',',$this->session->userdata('college_paper_session')));
		$this->db->set('subject_id',$subject_id);
		$this->db->set('chapter_id',$chapter_id);
		$this->db->set('topic_ids',$topic_ids);
		$this->db->set('class',$class);
		$this->db->set('total_marks',$total_marks);
		$this->db->set('add_by',$add_by);
		$this->db->set('mcqs',$mcqs);
		$this->db->set('short_questions',$short_questions);
		$this->db->set('print_type',$print_type);
		$this->db->set('practicals',$practical_ids);
		$this->db->set('mcqs_marks',$mcqs_marks);
		$this->db->set('short_questions_marks',$short_questions_marks);
		$this->db->set('practical_marks',$practical_marks);
		$this->db->set('date',date('Y-m-d'));
		
		$this->db->insert('collegepapers');
	}
	
	public function all_paper()
	{
		$data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
		$data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();
		
		if($this->input->post('submit')==1)
		{
			$course_id = $this->input->post('course_id');
			$subject_id = $this->input->post('subject_id');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
			
			$this->db->select('collegepapers.*,campuses.campus_name,courses.course_name,course_subjects.subject_name as subject_name');
			$this->db->from('collegepapers');
			$this->db->join('campuses','campuses.campus_id=collegepapers.campus_id','inner');
			$this->db->join('courses','courses.course_id=collegepapers.course_id','inner');
			$this->db->join('course_subjects','course_subjects.course_subject_id=collegepapers.subject_id','inner');
			if($course_id!='')
			{
				$this->db->where('collegepapers.course_id like "%'.$course_id.'%"');
			}
			if($subject_id!='')
			{
				$this->db->where('collegepapers.subject_id like "%'.$subject_id.'%"');
			}
			$this->db->where(array('collegepapers.date>='=>$start_date,'collegepapers.date<='=>$end_date));
			
			$data['papers'] = $this->db->get()->result_array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('collegepapers/all_paper',$data);
		$this->load->view('inc/footer');
	}
	
	public function viewpaper($collegepaper_id)
	{
		$data['collegepaper'] = $this->db->get_where('collegepapers',array('collegepaper_id'=>$collegepaper_id))->result_array();
		
		$this->load->view('collegepapers/view_paper',$data);
	}
	
	public function viewsolvepaper($collegepaper_id)
	{
		$data['collegepaper'] = $this->db->get_where('collegepapers',array('collegepaper_id'=>$collegepaper_id))->result_array();
		
		$this->load->view('collegepapers/view_solve_paper',$data);
	}
	
	public function add_result($paper_id)
	{
		$this->db->select('collegepapers.*');
		$this->db->from('collegepapers');
		$this->db->where('collegepaper_id',$paper_id);
		$data['papers'] = $this->db->get()->result_array();
		
		$data['students'] = $this->db->get_where('students',array('status'=>1,'course_id'=>$data['papers'][0]['course_id']))->result_array();
		
		$this->db->select('collegepaper_results.*,students.*,classes.name as class_name,campuses.campus_name, courses.course_name');
		$this->db->from('collegepaper_results');
		$this->db->join('students','students.student_id=collegepaper_results.student_id','inner');
		$this->db->join('classes','students.class_id=classes.class_id','inner');
		$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
		$this->db->join('courses','courses.course_id=students.course_id','inner');
		$this->db->where('collegepaper_results.collegepaper_id',$paper_id);
		$data['results'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('collegepapers/add_result',$data);
		$this->load->view('inc/footer');
	}
	
	public function insert_result($collegepaper_id)
	{
		$student_ids = $this->input->post('student_ids');
		$obtain_marks = $this->input->post('obtain_marks');
		
		$total = count($student_ids);
		for($i=0;$i<$total;$i++)
		{
			$check = $this->db->get_where('collegepaper_results',array('student_id'=>$student_ids[$i],'collegepaper_id'=>$collegepaper_id))->result_array();
			
			if(count($check)==0)
			{
				$this->db->set('student_id',$student_ids[$i]);
				$this->db->set('obtain_marks',$obtain_marks[$i]);
				$this->db->set('collegepaper_id',$collegepaper_id);
				$this->db->set('add_by',$this->session->userdata('name'));
				$this->db->insert('collegepaper_results');
			}
		}
		
		$this->session->set_flashdata('message','Result Added Successfully!');
		redirect('collegepapers/add_result/'.$collegepaper_id);
	}
	
	public function delete_result($collegepaper_result_id,$collegepaper_id)
	{
		$this->db->where('collegepaper_result_id',$collegepaper_result_id);
		$this->db->delete('collegepaper_results');
		
		$this->session->set_flashdata('message','Result Deleted Successfully!');
		redirect('collegepapers/add_result/'.$collegepaper_id);
	}
	
	public function getCourseDetails()
	{
		$course_id = $this->input->post('course_id');
		$course = $this->db->get_where('courses',array('course_id'=>$course_id))->result_array();
		$html='';
		if($course[0]['course_type']=='Annual')
		{
			$html.='<div class="form-group"><label class="col-md-3 control-label">Select Subject Year <span class="required">*</span></label><div class="col-md-9"><select name="class" class="form-control input-inline input-medium period" ><option value="">Select Subject Year</option>';
			$years = $course[0]['course_duration_year'];
			for($i=1;$i<=$years;$i++)
			{
				$html.='<option value="'.$i.'">'.$i.' Year</option>';
			}
			$html.='</select></div></div>';
			echo $html;
		}
		if($course[0]['course_type']=='Semester')
		{
			$html.='<div class="form-group"><label class="col-md-3 control-label">Select Subject Semester <span class="required">*</span></label><div class="col-md-9"><select name="class" class="form-control input-inline input-medium period" ><option value="">Select Subject Semester</option>';
			$semesters = $course[0]['course_semester'];
			for($i=1;$i<=$semesters;$i++)
			{
				$html.='<option value="'.$i.'">'.$i.' Semester</option>';
			}
			$html.='</select></div></div>';
			echo $html;
		}
	}
	
	public function student_results()
	{
		$data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
		$data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();
		
		$data['students']  = $this->db->get_where('students',array('status'=>1))->result_array();
		
		if($this->input->post('submit')==1)
		{
			$course_id = $this->input->post('course_id');
			$subject_id = $this->input->post('subject_id');
			$student_id = $this->input->post('student_id');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
			
			$this->db->select('collegepapers.*,collegepaper_results.*,classes.name as class_name,campuses.campus_name, courses.course_name,students.roll_no,students.first_name,students.last_name');
			$this->db->from('collegepaper_results');
			$this->db->join('collegepapers','collegepapers.collegepaper_id=collegepaper_results.collegepaper_id','inner');
			$this->db->join('students','students.student_id=collegepaper_results.student_id','inner');
			$this->db->join('classes','students.class_id=classes.class_id','inner');
			$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
			$this->db->join('courses','courses.course_id=students.course_id','inner');
			$this->db->where(array(
							'collegepapers.course_id'=>$course_id,
							'collegepapers.date>='=>$start_date,
							'collegepapers.date<='=>$end_date
							));
			if($subject_id!='')
			{
				$this->db->where('collegepapers.subject_id = "'.$subject_id.'" or collegepapers.subject_id like"%,'.$subject_id.'%" or collegepapers.subject_id like"%'.$subject_id.',%"' );
				
			}
			if($student_id!='')
			{
				$this->db->where('collegepaper_results.student_id',$student_id);
			}
			$data['results'] = $this->db->get()->result_array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('collegepapers/student_results',$data);
		$this->load->view('inc/footer');
	}
	
	public function getCourseStudents()
	{
		$course_id = $this->input->post('course_id');
		$students = $this->db->get_where('students',array('status'=>1,'course_id'=>$course_id))->result_array();
		
		$html='';
		$html.='<option value="">All Students</option>';
		foreach($students as $student)
		{
			$html.='<option value="'.$student['student_id'].'">'.$student['first_name'].' '.$student['last_name'].' ('.$student['roll_no'].')</option>';
		}
		echo $html;
	}
	
	public function getSessions()
	{
		$campus_id = $this->input->post('campus_id');
		$this->db->group_by('session');
		$classes = $this->db->get_where('classes',array('campus_id >'=>'0'))->result_array();
		$html='';
	
		foreach($classes as $class)
		{
			$html.='<option value="'.$class['session'].'">'.$class['session'].'</option>';
		}
		echo $html;
	}
	
	public function test_system()
    {
        $data['exams'] = $this->db
            ->order_by('id', 'DESC')
            ->get('monthly_test_exams')
            ->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('monthly_test/exams', $data);
        $this->load->view('inc/footer');
    }

    public function save_exam()

    {

        $id = $this->input->post('id');

        $data = array(

            'exam_name'   => $this->input->post('exam_name'),
            'expense_category'   => json_encode($this->input->post('expense_category_id')),

            'description' => $this->input->post('description'),

            'status'      => $this->input->post('status'),

            'updated_at'  => date('Y-m-d H:i:s')

        );

        if ($id) {

            $this->db->where('id', $id);

            $this->db->update('monthly_test_exams', $data);

            echo json_encode(array('status' => true, 'message' => 'Exam updated successfully'));

        } else {

            $data['created_at'] = date('Y-m-d H:i:s');

            $this->db->insert('monthly_test_exams', $data);

            echo json_encode(array('status' => true, 'message' => 'Exam added successfully'));

        }

    }

    public function get_exam($id)

    {

        $exam = $this->db

            ->where('id', $id)

            ->get('monthly_test_exams')

            ->row_array();

        echo json_encode($exam);

    }

    public function delete_exam($id)

    {

        $this->db->where('id', $id);

        $this->db->delete('monthly_test_exams');

        echo json_encode(array('status' => true, 'message' => 'Exam deleted successfully'));

    }

    public function get_improvement_rules($exam_id)

    {

        $rules = $this->db

            ->where('exam_id', $exam_id)

            ->order_by('attempt_no', 'ASC')

            ->get('monthly_test_improvement_rules')

            ->result_array();

        echo json_encode($rules);

    }

    public function save_improvement_rule()

    {

        $id = $this->input->post('id');

        $data = array(

            'exam_id'              => $this->input->post('exam_id'),

            'attempt_no'           => $this->input->post('attempt_no'),

            'attempt_name'         => $this->input->post('attempt_name'),

            'min_percentage'       => $this->input->post('min_percentage') ?: 0,

            'max_percentage'       => $this->input->post('max_percentage') ?: NULL,

            'improvement_required' => $this->input->post('improvement_required') ? 1 : 0,

            'status'               => $this->input->post('status'),

            'updated_at'           => date('Y-m-d H:i:s')

        );

        if ($id) {

            $this->db->where('id', $id);

            $this->db->update('monthly_test_improvement_rules', $data);

            echo json_encode(array('status' => true, 'message' => 'Improvement rule updated successfully'));

        } else {

            $data['created_at'] = date('Y-m-d H:i:s');

            $this->db->insert('monthly_test_improvement_rules', $data);

            echo json_encode(array('status' => true, 'message' => 'Improvement rule added successfully'));

        }

    }

    public function get_improvement_rule($id)

    {

        $rule = $this->db

            ->where('id', $id)

            ->get('monthly_test_improvement_rules')

            ->row_array();

        echo json_encode($rule);

    }

    public function delete_improvement_rule($id)

    {

        $this->db->where('id', $id);

        $this->db->delete('monthly_test_improvement_rules');

        echo json_encode(array('status' => true, 'message' => 'Improvement rule deleted successfully'));

    }

    public function get_reward_rules($exam_id)

    {

        $rules = $this->db

            ->where('exam_id', $exam_id)

            ->order_by('improvement_count', 'ASC')

            ->get('monthly_test_reward_rules')

            ->result_array();

        echo json_encode($rules);

    }

    public function save_reward_rule()

    {

        $id = $this->input->post('id');

        $data = array(

            'exam_id'            => $this->input->post('exam_id'),

            'improvement_count'  => $this->input->post('improvement_count'),

            'certificate'        => $this->input->post('certificate') ? 1 : 0,

            'cash_amount'        => $this->input->post('cash_amount') ?: 0,

            'status'             => $this->input->post('status'),

            'updated_at'         => date('Y-m-d H:i:s')

        );

        if ($id) {

            $this->db->where('id', $id);

            $this->db->update('monthly_test_reward_rules', $data);

            echo json_encode(array('status' => true, 'message' => 'Reward rule updated successfully'));

        } else {

            $data['created_at'] = date('Y-m-d H:i:s');

            $this->db->insert('monthly_test_reward_rules', $data);

            echo json_encode(array('status' => true, 'message' => 'Reward rule added successfully'));

        }

    }

    public function get_reward_rule($id)

    {

        $rule = $this->db

            ->where('id', $id)

            ->get('monthly_test_reward_rules')

            ->row_array();

        echo json_encode($rule);

    }

    public function delete_reward_rule($id)

    {

        $this->db->where('id', $id);

        $this->db->delete('monthly_test_reward_rules');

        echo json_encode(array('status' => true, 'message' => 'Reward rule deleted successfully'));

    }
    
    public function improvement_report()
    {
        $exam_id   = $this->input->post('exam_id');
        $class     = $this->input->post('class');
        $course_id = $this->input->post('course_id');
        $campus_id = $this->input->post('campus_id');
        $badge_id  = $this->input->post('badge');
    
        $data['campuses'] = $this->db->get('campuses')->result_array();
    
        $data['exams'] = $this->db
            ->where('status', 1)
            ->order_by('exam_name', 'ASC')
            ->get('monthly_test_exams')
            ->result_array();
    
        $results = array();
    
        if (count($this->input->post()) > 0) {
    
            $badge = array();
    
            if (!empty($badge_id)) {
                $badge = $this->db
                    ->get_where('classes', array('class_id' => $badge_id))
                    ->row_array();
            }
    
            $this->db->select('
                cp.collegepaper_id,
                cp.exam_id,
                cp.class as paper_class,
                cp.date,
                cp.total_marks,
                cp.course_id,
                cp.campus_id,
                cp.session,
    
                mt.exam_name,
    
                cpr.student_id,
                cpr.obtain_marks,
    
                s.first_name,
                s.last_name,
                s.class_id,
    
                cls.name as class_name
            ');
    
            $this->db->from('collegepaper_results cpr');
            $this->db->join('collegepapers cp', 'cp.collegepaper_id = cpr.collegepaper_id', 'inner');
            $this->db->join('monthly_test_exams mt', 'mt.id = cp.exam_id', 'left');
            $this->db->join('students s', 's.student_id = cpr.student_id', 'left');
            $this->db->join('classes cls', 'cls.class_id = s.class_id', 'left');
    
            if (!empty($exam_id)) {
                $this->db->where('cp.exam_id', $exam_id);
            }
    
            if (!empty($class)) {
                $this->db->where('cp.class', $class);
            }
    
            if (!empty($campus_id)) {
                $this->db->where('cp.campus_id', $campus_id);
            }
    
            if (!empty($course_id)) {
                $this->db->where('cp.course_id', $course_id);
            }
    
            if (!empty($badge)) {
                $this->db->where('cp.session', $badge['session']);
            }
    
            $this->db->order_by('cpr.student_id', 'ASC');
            $this->db->order_by('cp.exam_id', 'ASC');
            $this->db->order_by('YEAR(cp.date)', 'ASC', false);
            $this->db->order_by('MONTH(cp.date)', 'ASC', false);
            $this->db->order_by('cp.date', 'ASC');
            $this->db->order_by('cp.collegepaper_id', 'ASC');
    
            $results = $this->db->get()->result_array();
        }
    
        $report = array();
    
        foreach ($results as $row) {
    
            $studentExamKey = $row['student_id'] . '_' . $row['exam_id'];
    
            $monthKey  = date('Y-m', strtotime($row['date']));
            $monthName = date('F Y', strtotime($row['date']));
    
            if (!isset($report[$studentExamKey])) {
                $report[$studentExamKey] = array(
                    'student_id' => $row['student_id'],
                    'student' => trim($row['first_name'] . ' ' . $row['last_name']),
                    'class' => !empty($row['class_name']) ? $row['class_name'] : $row['paper_class'],
                    'exam_id' => $row['exam_id'],
                    'exam_name' => !empty($row['exam_name']) ? $row['exam_name'] : '-',
                    'month_attempts' => array(),
                    'attempts' => array(),
                    'improvement_count' => 0,
                    'reward' => 'Not Eligible',
                    'reward_text' => ''
                );
            }
    
            if (!isset($report[$studentExamKey]['month_attempts'][$monthKey])) {
                $report[$studentExamKey]['month_attempts'][$monthKey] = array(
                    'month_key' => $monthKey,
                    'month_name' => $monthName,
                    'obtain_marks' => 0,
                    'total_marks' => 0,
                    'percentage' => 0,
                    'papers_count' => 0,
                    'papers' => array(),
    
                    // reward/improvement event fields
                    'is_improved' => 0,
                    'improvement_no' => 0,
                    'reward_rule' => array(),
                    'reward_given' => 0,
                    'reward_given_data' => array()
                );
            }
    
            $obtain_marks = (float) $row['obtain_marks'];
            $total_marks  = (float) $row['total_marks'];
    
            $report[$studentExamKey]['month_attempts'][$monthKey]['obtain_marks'] += $obtain_marks;
            $report[$studentExamKey]['month_attempts'][$monthKey]['total_marks'] += $total_marks;
            $report[$studentExamKey]['month_attempts'][$monthKey]['papers_count']++;
    
            $report[$studentExamKey]['month_attempts'][$monthKey]['papers'][] = array(
                'paper_id' => $row['collegepaper_id'],
                'date' => $row['date'],
                'obtain_marks' => $obtain_marks,
                'total_marks' => $total_marks
            );
        }
    
        foreach ($report as $key => $studentReport) {
    
            ksort($report[$key]['month_attempts']);
    
            foreach ($report[$key]['month_attempts'] as $monthKey => $monthData) {
    
                $percentage = 0;
    
                if ($monthData['total_marks'] > 0) {
                    $percentage = round(($monthData['obtain_marks'] / $monthData['total_marks']) * 100, 2);
                }
    
                $monthData['percentage'] = $percentage;
    
                $report[$key]['attempts'][] = $monthData;
            }
        }
    
        /*
         * Har improved month par reward event attach hoga.
         * Last attempt par nahi.
         */
        foreach ($report as $key => $studentReport) {
    
            $attempts = $studentReport['attempts'];
            $improvement_count = 0;
    
            if (isset($report[$key]['attempts'][0])) {
                $report[$key]['attempts'][0]['is_improved'] = 0;
                $report[$key]['attempts'][0]['improvement_no'] = 0;
                $report[$key]['attempts'][0]['reward_rule'] = array();
                $report[$key]['attempts'][0]['reward_given'] = 0;
                $report[$key]['attempts'][0]['reward_given_data'] = array();
            }
    
            for ($i = 1; $i < count($attempts); $i++) {
    
                $report[$key]['attempts'][$i]['is_improved'] = 0;
                $report[$key]['attempts'][$i]['improvement_no'] = 0;
                $report[$key]['attempts'][$i]['reward_rule'] = array();
                $report[$key]['attempts'][$i]['reward_given'] = 0;
                $report[$key]['attempts'][$i]['reward_given_data'] = array();
    
                if ($attempts[$i]['percentage'] > $attempts[$i - 1]['percentage']) {
    
                    $improvement_count++;
                    $improvement_no = $improvement_count;
    
                    $reward_rule = $this->db
                        ->where('exam_id', $studentReport['exam_id'])
                        ->where('improvement_count', $improvement_no)
                        ->where('status', 1)
                        ->get('monthly_test_reward_rules')
                        ->row_array();
    
                    $given_reward = array();
    
                    if ($reward_rule) {
                        $given_reward = $this->db
                            ->where('student_id', $studentReport['student_id'])
                            ->where('exam_id', $studentReport['exam_id'])
                            ->where('month_key', $attempts[$i]['month_key'])
                            ->where('improvement_no', $improvement_no)
                            ->get('monthly_test_rewards_given')
                            ->row_array();
                    }
    
                    $report[$key]['attempts'][$i]['is_improved'] = 1;
                    $report[$key]['attempts'][$i]['improvement_no'] = $improvement_no;
                    $report[$key]['attempts'][$i]['reward_rule'] = $reward_rule;
                    $report[$key]['attempts'][$i]['reward_given'] = !empty($given_reward) ? 1 : 0;
                    $report[$key]['attempts'][$i]['reward_given_data'] = $given_reward;
                }
            }
    
            $report[$key]['improvement_count'] = $improvement_count;
    
            $lastRewardRule = $this->db
                ->where('exam_id', $studentReport['exam_id'])
                ->where('improvement_count <=', $improvement_count)
                ->where('status', 1)
                ->order_by('improvement_count', 'DESC')
                ->get('monthly_test_reward_rules')
                ->row_array();
    
            if ($lastRewardRule) {
                $report[$key]['reward'] = 'Eligible';
    
                $rewardText = array();
    
                if ($lastRewardRule['certificate'] == 1) {
                    $rewardText[] = 'Certificate';
                }
    
                if ($lastRewardRule['cash_amount'] > 0) {
                    $rewardText[] = 'Cash: ' . $lastRewardRule['cash_amount'];
                }
    
                $report[$key]['reward_text'] = !empty($rewardText) ? implode(' + ', $rewardText) : '-';
            }
        }
    
        $data['report'] = array_values($report);
    
        $max_attempts = 0;
    
        foreach ($data['report'] as $r) {
            if (count($r['attempts']) > $max_attempts) {
                $max_attempts = count($r['attempts']);
            }
        }
    
        $data['max_attempts'] = $max_attempts;
    
        $data['selected_exam_id'] = $exam_id;
        $data['selected_class'] = $class;
        $data['selected_course_id'] = $course_id;
        $data['selected_campus_id'] = $campus_id;
        $data['selected_badge'] = $badge_id;
    
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('monthly_test/improvement_report', $data);
        $this->load->view('inc/footer');
    }
    
    public function improvement_month_detail($student_id, $exam_id, $month_key)
    {
        $start_date = date('Y-m-01', strtotime($month_key . '-01'));
        $end_date   = date('Y-m-t', strtotime($month_key . '-01'));
    
        $this->db->select('
            cp.collegepaper_id,
            cp.date,
            cp.exam_id,
            cp.subject_id,
            cp.total_marks,
    
            mt.exam_name,
    
            cpr.obtain_marks,
    
            s.first_name,
            s.last_name,
    
            sub.name as subject_name
        ');
    
        $this->db->from('collegepaper_results cpr');
        $this->db->join('collegepapers cp', 'cp.collegepaper_id = cpr.collegepaper_id', 'inner');
        $this->db->join('monthly_test_exams mt', 'mt.id = cp.exam_id', 'left');
        $this->db->join('students s', 's.student_id = cpr.student_id', 'left');
        $this->db->join('subjects sub', 'sub.subject_id = cp.subject_id', 'left');
    
        $this->db->where('cpr.student_id', $student_id);
        $this->db->where('cp.exam_id', $exam_id);
        $this->db->where('cp.date >=', $start_date);
        $this->db->where('cp.date <=', $end_date);
    
        $this->db->order_by('cp.date', 'ASC');
        $this->db->order_by('cp.collegepaper_id', 'ASC');
    
        $details = $this->db->get()->result_array();
    
        $total_obtain = 0;
        $total_marks = 0;
    
        foreach ($details as $row) {
            $total_obtain += (float) $row['obtain_marks'];
            $total_marks  += (float) $row['total_marks'];
        }
    
        $percentage = 0;
    
        if ($total_marks > 0) {
            $percentage = round(($total_obtain / $total_marks) * 100, 2);
        }
    
        $data['details'] = $details;
        $data['student_id'] = $student_id;
        $data['exam_id'] = $exam_id;
        $data['month_key'] = $month_key;
        $data['month_name'] = date('F Y', strtotime($month_key . '-01'));
        $data['total_obtain'] = $total_obtain;
        $data['total_marks'] = $total_marks;
        $data['percentage'] = $percentage;
    
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('monthly_test/improvement_month_detail', $data);
        $this->load->view('inc/footer');
    }
    
    public function overall_class_performance()
    {
        $exam_id   = $this->input->post('exam_id');
        $class     = $this->input->post('class');
        $course_id = $this->input->post('course_id');
        $campus_id = $this->input->post('campus_id');
        $badge_id  = $this->input->post('badge');
    
        $data['campuses'] = $this->db->get('campuses')->result_array();
    
        $data['exams'] = $this->db
            ->where('status', 1)
            ->order_by('exam_name', 'ASC')
            ->get('monthly_test_exams')
            ->result_array();
    
        $badge = array();
    
        if (!empty($badge_id)) {
            $badge = $this->db
                ->get_where('classes', array('class_id' => $badge_id))
                ->row_array();
        }
    
        $this->db->select('
            cp.collegepaper_id,
            cp.exam_id,
            cp.class as paper_class,
            cp.date,
            cp.total_marks,
            cp.course_id,
            cp.campus_id,
            cp.session,
    
            mt.exam_name,
    
            cpr.student_id,
            cpr.obtain_marks,
    
            s.first_name,
            s.last_name,
            s.class_id,
    
            cls.name as class_name
        ');
    
        $this->db->from('collegepaper_results cpr');
        $this->db->join('collegepapers cp', 'cp.collegepaper_id = cpr.collegepaper_id', 'inner');
        $this->db->join('monthly_test_exams mt', 'mt.id = cp.exam_id', 'left');
        $this->db->join('students s', 's.student_id = cpr.student_id', 'left');
        $this->db->join('classes cls', 'cls.class_id = s.class_id', 'left');
    
        if (!empty($exam_id)) {
            $this->db->where('cp.exam_id', $exam_id);
        }
    
        if (!empty($class)) {
            $this->db->where('cp.class', $class);
        }
    
        if (!empty($campus_id)) {
            $this->db->where('cp.campus_id', $campus_id);
        }
    
        if (!empty($course_id)) {
            $this->db->where('cp.course_id', $course_id);
        }
    
        if (!empty($badge)) {
            $this->db->where('cp.session', $badge['session']);
        }
    
        $this->db->order_by('cp.date', 'ASC');
        $this->db->order_by('cpr.student_id', 'ASC');
    
        $results = $this->db->get()->result_array();
    
        $monthly = array();
    
        foreach ($results as $row) {
    
            $monthKey  = date('Y-m', strtotime($row['date']));
            $monthName = date('F Y', strtotime($row['date']));
    
            $key = $row['exam_id'] . '_' . $row['paper_class'] . '_' . $monthKey;
    
            if (!isset($monthly[$key])) {
                $monthly[$key] = array(
                    'month_key' => $monthKey,
                    'month_name' => $monthName,
                    'class' => !empty($row['class_name']) ? $row['class_name'] : $row['paper_class'],
                    'exam_id' => $row['exam_id'],
                    'exam_name' => !empty($row['exam_name']) ? $row['exam_name'] : '-',
                    'students' => array()
                );
            }
    
            $studentId = $row['student_id'];
    
            if (!isset($monthly[$key]['students'][$studentId])) {
                $monthly[$key]['students'][$studentId] = array(
                    'obtain_marks' => 0,
                    'total_marks' => 0
                );
            }
    
            $monthly[$key]['students'][$studentId]['obtain_marks'] += (float) $row['obtain_marks'];
            $monthly[$key]['students'][$studentId]['total_marks']  += (float) $row['total_marks'];
        }
    
        $report = array();
    
        foreach ($monthly as $key => $monthData) {
    
            $appeared = count($monthData['students']);
            $passed = 0;
            $failed = 0;
            $sumPercentage = 0;
            $highest = 0;
            $lowest = 0;
            $first = true;
    
            foreach ($monthData['students'] as $student) {
    
                $percentage = 0;
    
                if ($student['total_marks'] > 0) {
                    $percentage = round(($student['obtain_marks'] / $student['total_marks']) * 100, 2);
                }
    
                $sumPercentage += $percentage;
    
                if ($percentage >= 50) {
                    $passed++;
                } else {
                    $failed++;
                }
    
                if ($first) {
                    $highest = $percentage;
                    $lowest = $percentage;
                    $first = false;
                } else {
                    if ($percentage > $highest) {
                        $highest = $percentage;
                    }
    
                    if ($percentage < $lowest) {
                        $lowest = $percentage;
                    }
                }
            }
    
            $avg = 0;
    
            if ($appeared > 0) {
                $avg = round($sumPercentage / $appeared, 2);
            }
    
            $report[] = array(
                'month_name' => $monthData['month_name'],
                'class' => $monthData['class'],
                'exam_name' => $monthData['exam_name'],
                'appeared' => $appeared,
                'passed' => $passed,
                'failed' => $failed,
                'avg_marks' => $avg,
                'highest' => $highest,
                'lowest' => $lowest
            );
        }
    
        $data['report'] = $report;
    
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('monthly_test/overall_class_performance', $data);
        $this->load->view('inc/footer');
    }
    
    public function give_monthly_test_reward()
    {
        $student_id = $this->input->post('student_id');
        $exam_id = $this->input->post('exam_id');
        $month_key = $this->input->post('month_key');
        $reward_rule_id = $this->input->post('reward_rule_id');
        $improvement_count = $this->input->post('improvement_count');
        $remarks = $this->input->post('remarks');
        
        $st_detail = $this->db->get_where("students","student_id = ".$student_id)->row();
    
        $reward_rule = $this->db
            ->where('id', $reward_rule_id)
            ->get('monthly_test_reward_rules')
            ->row_array();
        $monthly_test = $this->db
            ->where('id', $reward_rule['exam_id'])
            ->get('monthly_test_exams')
            ->row_array();
    
        if (!$reward_rule) {
            $this->session->set_flashdata('error', 'Reward rule not found.');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }
        
        $this->db->select('*');
            $this->db->from('petty_cash_college_wise');
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $query = $this->db->get()->result_array();
            
        
    
        if(count($query) > 0){
            $pettycash = my_pettycash();
            if($pettycash < $reward_rule['cash_amount']){
                $this->session->set_flashdata('error', "Your Petty Cash is Less then reward Amount.");
                redirect($_SERVER['HTTP_REFERER']);
                return;
            }
        }
        else{
            $this->session->set_flashdata('error', "You don't have Petty Cash.");
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }
        
        
    
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
        if (!$this->upload->do_upload('proof_image')) {

            $image = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }
        
            $exp_id = $monthly_test['expense_category'][count($monthly_test['expense_category'])-1];
        
            $this->db->set('campus_id',$st_detail->study_campus);
			$this->db->set('expense_category_id',$exp_id);
			$this->db->set('title',"Reward Given to Student $st_detail->roll_no for improvement number $improvement_count for the month of $month_key");
			$this->db->set('date', date('Y-m-d'));
			$this->db->set('amount',$reward_rule['cash_amount']);
			$this->db->set('purpose',"Reward Given to Student $st_detail->roll_no for improvement number $improvement_count for the month of $month_key");
			$this->db->set('month_year',date('Y-m'));
			
			$this->db->set('student_id',$student_id);
			$this->db->set('actual_date', date('Y-m-d H:i:s'));
			$this->db->set('image', $image);
			$this->db->set('payment_type', 'cash');
			$this->db->set('class_id', $st_detail->class_id);
			$this->db->set('roll_no', $st_detail->roll_no);
			$this->db->set('add_by', $this->session->userdata('name'));
			$this->db->set('last_edit', $this->session->userdata('name'));
			$this->db->set('add_by_id', $this->session->userdata('user_id'));
			$this->db->set('approved_status', 1);
			$this->db->set('paid_type', 'cash');
			$this->db->insert('expenses');
			$expense_id = $this->db->insert_id();
            
    
            $this->db->set('remaining_amount', 'remaining_amount -'.$reward_rule['cash_amount'] .'',false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');
                
        
    
        $already = $this->db
            ->where('student_id', $student_id)
            ->where('exam_id', $exam_id)
            ->where('month_key', $month_key)
            ->get('monthly_test_rewards_given')
            ->row_array();
    
        $data = array(
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'month_key' => $month_key,
            'improvement_count' => $improvement_count,
            'reward_rule_id' => $reward_rule_id,
            'certificate' => $reward_rule['certificate'],
            'cash_amount' => $reward_rule['cash_amount'],
            'remarks' => $remarks,
            'given_by' => $this->session->userdata('user_id'),
            'given_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'expense_id' => $expense_id
        );
    
        if ($image != '') {
            $data['proof_image'] = $image;
        }
    
        if ($already) {
            $this->db->where('id', $already['id']);
            $this->db->update('monthly_test_rewards_given', $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('monthly_test_rewards_given', $data);
        }
    
        $this->session->set_flashdata('message', 'Reward given successfully.');
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function get_expense_category_chain()
    {
        $selected_categories = $this->input->post('selected_categories');
    
        if (empty($selected_categories)) {
            echo '';
            return;
        }
    
        $html = '';
    
        foreach ($selected_categories as $index => $selected_id) {
    
            if ($index == 0) {
                $this->db->where('sub_of IS NULL', null, false);
            } else {
                $this->db->where('sub_of', $selected_categories[$index - 1]);
            }
    
            $this->db->where('status', 'active');
            $categories = $this->db->get('expense_category')->result_array();
    
            $html .= '<div class="form-group" id="div-'.$index.'">';
            $html .= '<div>';
            $html .= '<select class="form-control Select2 exps" data-count="'.$index.'" name="expense_category_id[]" id="category_id'.$index.'">';
            $html .= '<option value="">Select expense category</option>';
    
            foreach ($categories as $cat) {
                $selected = ($cat['expense_category_id'] == $selected_id) ? 'selected' : '';
    
                $html .= '<option value="'.$cat['expense_category_id'].'" '.$selected.'>';
                $html .= $cat['name'];
                $html .= '</option>';
            }
    
            $html .= '</select>';
            $html .= '</div>';
            $html .= '</div>';
        }
    
        echo $html;
    }
}
