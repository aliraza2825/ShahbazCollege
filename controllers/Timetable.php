<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Timetable extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

	}
	
	public function insert()
	{

		$course_id = $this->input->post('course_id');
        $name = $this->input->post('name');
		$days = $this->input->post('days');

        $this->db->set('course_id', $course_id);
        $this->db->set('name', $name);
        $this->db->set('days', implode(",", $days));
        $this->db->set('created_by', $this->session->userdata('user_id'));
        $this->db->insert('study_type');


		$this->session->set_flashdata('message', 'Study Type successfully!');
		redirect('timetable/studytype');
	}

    public function update()
    {
        $course_id = $this->input->post('course_id');
        $name = $this->input->post('name');
        $days = $this->input->post('days');

        $this->db->set('course_id', $course_id);
        $this->db->set('name', $name);
        $this->db->set('days', implode(",", $days));


        $this->db->where('id',$this->input->post('id'));
        $this->db->update('study_type');


        $this->session->set_flashdata('message', 'Study Type updated successfully!');
        redirect('timetable/studytype');
    }

	public function index()
	{
		
	}

    public function delete($id)
    {
        $this->db->where('id',$id);
        $this->db->delete('study_type');

        $this->session->set_flashdata('message','Study Type Deleted Successfully');
        redirect('timetable/studytype');
    }
	
	public function studytype()
	{
        $this->db->select('*');
        $this->db->from('study_type');
        $this->db->join('users','users.user_id = study_type.created_by','left');
        $this->db->join('courses','courses.course_id = study_type.course_id','left');
		$data['studytype'] = $this->db->get()->result_array();

        $data['courses'] = $this->db->get('courses')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('timetable/add_studytype', $data);
		$this->load->view('inc/footer');
	}

    public function managestudytype()
	{
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('timetable/studytype', $data);
		$this->load->view('inc/footer');
	}

	public function shifts()
    {

        $data['studytype'] = $this->db->select('shifts.*,users.first_name,users.last_name,shifts.campus_id as shift_campus,study_type.name as study_type_name,courses.course_id as shift_course,courses.course_name')->join('users','users.user_id = shifts.created_by','left')->join('study_type','study_type.id=shifts.study_type_id','left')->join('courses','study_type.course_id=courses.course_id','left')->get('shifts')->result_array();
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $data['courses'] = $this->db->get('courses')->result_array();
        $data['study_types'] = $this->db->get('study_type')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('timetable/add_shift', $data);
        $this->load->view('inc/footer');
    }

    public function insert_shift()
    {

        $name = $this->input->post('name');
        $start_date = $this->input->post('start_time');
        $end_date = $this->input->post('end_time');
        $campus_id = implode(',',$this->input->post('campus_ids'));
        $study_type_id = $this->input->post('study_type_id');

        $this->db->set('name', $name);
        $this->db->set('start_time', $start_date);
        $this->db->set('end_time', $end_date);
        $this->db->set('campus_id', $campus_id);
        $this->db->set('study_type_id', $study_type_id);
        $this->db->set('created_by', $this->session->userdata('user_id'));
        $this->db->insert('shifts');


        $this->session->set_flashdata('message', 'Shift Added successfully!');
        redirect('timetable/shifts');
    }

    public function update_shift()
    {

        $name = $this->input->post('name');
        $start_date = $this->input->post('start_time');
        $end_date = $this->input->post('end_time');
        $campus_id = implode(',',$this->input->post('campus_ids'));
        $study_type_id = $this->input->post('study_type_id');

        $this->db->set('name', $name);
        $this->db->set('start_time', $start_date);
        $this->db->set('end_time', $end_date);
        $this->db->set('created_by', $this->session->userdata('user_id'));
        $this->db->set('campus_id', $campus_id);
        $this->db->set('study_type_id', $study_type_id);
        $this->db->where('id',$this->input->post('id'));
        $this->db->update('shifts');

        $this->session->set_flashdata('message', 'Shift updated successfully!');
        redirect('timetable/shifts');
    }

    public function delete_shift($id)
    {
        $this->db->where('id',$id);
        $this->db->delete('shifts');

        $this->session->set_flashdata('message', 'Shift Deleted successfully!');
        redirect('timetable/shifts');
    }
    
	public function timetable()
    {

        $data['timetable'] = $this->db->join('users','users.user_id = shifts.created_by','left')->get('shifts')->result_array();
        //$data['shifts'] = $this->db->get('shifts')->result_array();
        $data['studytype'] = $this->db->get('study_type')->result_array();
        $data['rooms'] = $this->db->where('type = "0"')->get('rooms')->result_array();
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $data['courses'] = $this->db->get('courses')->result_array();
        $data['teachers'] = $this->db->join('campuses','campuses.campus_id = users.campus_id')->get_where('users','users.status = "1" and users.department_id = "13"')->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('timetable/add_timetable', $data);
        $this->load->view('inc/footer');

    }

    public function edit_timetable($id)
    {

        $data['timetable'] = $this->db->join('users','users.user_id = shifts.created_by','left')->get('shifts')->result_array();
        
        //$data['shifts'] = $this->db->get('shifts')->result_array();
        $data['studytype'] = $this->db->get('study_type')->result_array();
        $data['rooms'] = $this->db->where('type = "0"')->get('rooms')->result_array();
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $data['courses'] = $this->db->get('courses')->result_array();
        $data['teachers'] = $this->db->join('campuses','campuses.campus_id = users.campus_id')->get_where('users','users.status = "1" and users.department_id = "13"')->result_array();
        //EXTRA ADDED
        $data['lecture'] = $this->db->get_where('lectures',array('id'=>$id))->result_array();
        $data['shifts'] = $this->db->where('find_in_set('.$data['lecture'][0]['campus'].', campus_id)')->get('shifts')->result_array();
        $data['sessions'] = $this->db->get_where('course_sessions',array('course_id'=>$data['lecture'][0]['course']))->result_array();

        $class = $data['lecture'][0]['class'];
        $course_id = $data['lecture'][0]['course'];
        $this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->where(array('course_id'=>$course_id,'status'=>1));
		$this->db->where("(subject_year='$class' OR subject_semester='$class')", NULL, FALSE);
		$data['subjects'] = $this->db->get()->result_array();

		$study_types = $this->db->where('id = "'.$data['lecture'][0]['studytype'].'"')->get('study_type')->result_array();
		$data['days'] = explode(',', $study_types[0]['days']);

        $data['rooms'] = $this->db->where('type = "0" and campus_id = "'.$data['lecture'][0]['campus'].'"')->get('rooms')->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('timetable/edit_timetable', $data);
        $this->load->view('inc/footer');

    }
    
	public function inserttimetable()
    {

        $lecture = $this->input->post('lecture_name');
        $course = $this->input->post('course');
        $class = $this->input->post('class');
        $campus = $this->input->post('campus');
        $sessions = $this->input->post('session');
        $subjects = $this->input->post('subject');
        $shift = $this->input->post('shift');
        $studytype = $this->input->post('studytype');
        $room = $this->input->post('room');
        $teacher = $this->input->post('teacher');
        $start_date = $this->input->post('start_time');
        $end_date = $this->input->post('end_time');
		$days = $this->input->post('days');
        $s_teacher = $this->input->post('s_teacher');


        if ($sessions!='')
        {

            $sessions=implode(',',$sessions);
        }

        if ($subjects!='')
        {

            $subjects=implode(',',$subjects);
        }
		 if ($days!='')
        {
            $days=implode(',',$days);
        }

        $this->db->set('lecture_name', $lecture);
        $this->db->set('course', $course);
        $this->db->set('class', $class);
        $this->db->set('session', $sessions);
        $this->db->set('campus', $campus);
        $this->db->set('subjects', $subjects);
        $this->db->set('shift', $shift);
        $this->db->set('studytype', $studytype);
        $this->db->set('room', $room);
        $this->db->set('teacher',$teacher);
        $this->db->set('start_date',$start_date);
        $this->db->set('end_date',$end_date);
		$this->db->set('days',$days);
        $this->db->set('second_teacher',$s_teacher);
        $this->db->set('created_by', $this->session->userdata('user_id'));
        $this->db->insert('lectures');


        $this->session->set_flashdata('message', 'Shift Added successfully!');
        redirect('timetable/view_timetable');
    }

    public function updatetimetable($id)
    {
        $lecture = $this->input->post('lecture_name');
        $course = $this->input->post('course');
        $class = $this->input->post('class');
        $campus = $this->input->post('campus');
        $sessions = $this->input->post('session');
        $subjects = $this->input->post('subject');
        $shift = $this->input->post('shift');
        $studytype = $this->input->post('studytype');
        $room = $this->input->post('room');
        $teacher = $this->input->post('teacher');
        $start_date = $this->input->post('start_time');
        $end_date = $this->input->post('end_time');
		$days = $this->input->post('days');
        $s_teacher = $this->input->post('s_teacher');


        if ($sessions!='')
        {

            $sessions=implode(',',$sessions);
        }

        if ($subjects!='')
        {

            $subjects=implode(',',$subjects);
        }
		 if ($days!='')
        {
            $days=implode(',',$days);
        }

        $this->db->set('lecture_name', $lecture);
        $this->db->set('course', $course);
        $this->db->set('class', $class);
        $this->db->set('session', $sessions);
        $this->db->set('campus', $campus);
        $this->db->set('subjects', $subjects);
        $this->db->set('shift', $shift);
        $this->db->set('studytype', $studytype);
        $this->db->set('room', $room);
        $this->db->set('teacher',$teacher);
        $this->db->set('start_date',$start_date);
        $this->db->set('end_date',$end_date);
		 $this->db->set('days',$days);
        $this->db->set('second_teacher',$s_teacher);
        $this->db->set('created_by', $this->session->userdata('user_id'));
        $this->db->where('id',$id);
        $this->db->update('lectures');


        $this->session->set_flashdata('message', 'Shift Updated successfully!');
        redirect('timetable/edit_timetable/'.$id);
    }

	public function getCampusrooms()
	{
		$campus_id = $this->input->post('campus_id');
		$rooms = $this->db->where('type = "0" and campus_id = "'.$campus_id.'"')->get('rooms')->result_array();
		
		$html='';
		
		foreach($rooms as $subject)
		{
			$html.='<option value="'.$subject['room_id'].'">'.$subject['room_name'].'</option>';
		}
		echo $html;
	}
	
	public function getStudyDays()
	{
		$campus_id = $this->input->post('type_id');
		$rooms = $this->db->where('id = "'.$campus_id.'"')->get('study_type')->result_array();
		
		$rooms = explode(',', $rooms[0]['days']);
		

		$html='';
		
		foreach($rooms as $subject)
		{
			$html.='<option value="'.$subject.'">'.$subject.'</option>';
		}
		echo $html;
	}
	
	public function view_timetable($shift = null,$study_type = null,$campus_id = null)
    {
        /*
        if ($shift != null)
        {
            $data['lectures'] = $this->db
                ->join('courses','courses.course_id = lectures.course','left')
                ->join('campuses','campuses.campus_id = lectures.campus','left')
                ->join('users','users.user_id = lectures.teacher','left')
                ->join('rooms','rooms.room_id = lectures.room','left')
                ->where('lectures.shift',$shift)
                ->where('lectures.studytype',$study_type)
                ->where('lectures.campus',$campus_id)
                ->get('lectures')->result_array();
        }else
        {
            $data['lectures'] = $this->db
                ->join('courses','courses.course_id = lectures.course','left')
                ->join('campuses','campuses.campus_id = lectures.campus','left')
                ->join('users','users.user_id = lectures.teacher','left')
                ->join('rooms','rooms.room_id = lectures.room','left')
                ->get('lectures')->result_array();
        }
        */
        if (@$this->input->post('form_submit') == "1" && @$this->input->post('campus_id_search') != "")
        {
            $data['groups'] = $this->db->join('courses', 'courses.course_id = lectures.course', 'left')
                ->join('campuses', 'campuses.campus_id = lectures.campus', 'left')
                ->join('users', 'users.user_id = lectures.teacher', 'left')
                ->join('rooms', 'rooms.room_id = lectures.room', 'left')
                ->where('lectures.campus',$this->input->post('campus_id_search'))
                ->order_by('campuses.campus_id', "asc")
                ->group_by(array("lectures.shift", "lectures.studytype", "lectures.campus"))
                ->get('lectures')->result_array();
            
            if(@$this->input->post('campus_id_search')=='all')
            {
                $data['lectures'] = $this->db
                ->join('courses','courses.course_id = lectures.course','left')
                ->join('campuses','campuses.campus_id = lectures.campus','left')
                ->join('users','users.user_id = lectures.teacher','left')
                ->join('rooms','rooms.room_id = lectures.room','left')
                ->get('lectures')->result_array();
            }
            else
            {
                $data['lectures'] = $this->db
                ->join('courses','courses.course_id = lectures.course','left')
                ->join('campuses','campuses.campus_id = lectures.campus','left')
                ->join('users','users.user_id = lectures.teacher','left')
                ->join('rooms','rooms.room_id = lectures.room','left')
                ->where('campuses.campus_id',$this->input->post('campus_id_search'))
                ->get('lectures')->result_array();
            }
        }
        else
        {
            $data['groups'] = $this->db->join('courses', 'courses.course_id = lectures.course', 'left')
                ->join('campuses', 'campuses.campus_id = lectures.campus', 'left')
                ->join('users', 'users.user_id = lectures.teacher', 'left')
                ->join('rooms', 'rooms.room_id = lectures.room', 'left')
                ->where('campuses.campus_id',$this->input->post('campus_id_search'))
                ->order_by('campuses.campus_id', "asc")
                ->group_by(array("lectures.shift", "lectures.studytype", "lectures.campus"))
                ->get('lectures')->result_array();
                
            $data['lectures'] = $this->db
                ->join('courses','courses.course_id = lectures.course','left')
                ->join('campuses','campuses.campus_id = lectures.campus','left')
                ->join('users','users.user_id = lectures.teacher','left')
                ->join('rooms','rooms.room_id = lectures.room','left')
                ->get('lectures')->result_array();
        }
        
        

        $data['campuses'] = $this->db->get('campuses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('timetable/view_timetable', $data);
        $this->load->view('inc/footer');
    }


    public function delete_table($id)
    {

        $this->db->where('id', $id);
        $this->db->delete('lectures');

        redirect('timetable/view_timetable');

    }

    public function session_wise_students($lecture_id)
    {
        $this->db->select('lectures.*,shifts.name as shift_name,study_type.name as study_type_name,campuses.campus_name');
        $this->db->from('lectures');
        $this->db->join('shifts','shifts.id=lectures.shift','INNER');
        $this->db->join('study_type','study_type.id=lectures.studytype','INNER');
        $this->db->join('campuses','campuses.campus_id=lectures.campus','INNER');
        $this->db->where('lectures.id',$lecture_id);
        $data['lecture'] = $this->db->get()->result_array();
        
        $study_session = explode(',',$data['lecture'][0]['session']);
        
        // print_r($data['lecture']);
        // exit();
        //$study_type = str_replace("%20"," ",$study_type);

        $this->db->select('students.*, classes.name as class_name,classes.admission_fee as freeze_fee, courses.course_name,campuses.campus_name');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'left');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
        $this->db->where(array('students.status'=>'1'));
        $this->db->where(array('students.shift'=> $data['lecture'][0]['shift']));
        $this->db->where(array('students.study_type'=> $data['lecture'][0]['studytype']));
        $this->db->where(array('students.study_campus'=> $data['lecture'][0]['campus']));
        $this->db->where_in('students.study_session',$study_session);
        $data['students'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('timetable/all_students', $data);
        $this->load->view('inc/footer');
    }

    public function getSessions()
    {
        $course_id = $this->input->post('course_id');
        $sessions = $this->db->get_where('course_sessions',array('course_id'=>$course_id))->result_array();
        $html = '';

        foreach($sessions as $session)
        {
            $html.='<option value="'.$session['session_name'].'">'.$session['session_name'].'</option>';
        }
        echo $html;
    }

    public function today_lecture($lecture_id)
    {
        $this->db->select('lectures.id as lecture_id, lectures.session, lectures.studytype, lectures.room, lectures.teacher, lectures.subjects, lectures.shift, lectures.class, lectures.lecture_name, lectures.start_date, lectures.end_date, session_syllabus.date, session_syllabus.topic_ids, session_syllabus.practical_ids, session_syllabus.is_quiz, session_syllabus.is_half, session_syllabus.id as session_syllabus_id, courses.course_name, campuses.campus_name, course_subjects.subject_name');
		$this->db->from('session_syllabus');
		$this->db->join('lectures','session_syllabus.lecture_id=lectures.id','inner');
		$this->db->join('courses','courses.course_id=lectures.course','inner');
		$this->db->join('campuses','campuses.campus_id=lectures.campus','inner');
		$this->db->join('course_subjects','course_subjects.course_subject_id=session_syllabus.subject_id','inner');
		$this->db->where(array('lectures.id'=>$lecture_id,'session_syllabus.date<='=>date('Y-m-d')));
		$this->db->group_by('session_syllabus.date');
		$this->db->order_by('session_syllabus.date', 'DESC');
		$data['my_lectures'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('timetable/today_lecture', $data);
        $this->load->view('inc/footer');
    }

    public function all_lectures($lecture_id)
    {
        $this->db->select('lectures.id as lecture_id, lectures.session, lectures.studytype, lectures.room, lectures.teacher, lectures.subjects, lectures.shift, lectures.class, lectures.lecture_name, lectures.start_date, lectures.end_date, session_syllabus.date, session_syllabus.topic_ids, session_syllabus.practical_ids, session_syllabus.is_quiz, session_syllabus.is_half, session_syllabus.id as session_syllabus_id, courses.course_name, campuses.campus_name, course_subjects.subject_name');
		$this->db->from('session_syllabus');
		$this->db->join('lectures','session_syllabus.lecture_id=lectures.id','inner');
		$this->db->join('courses','courses.course_id=lectures.course','inner');
		$this->db->join('campuses','campuses.campus_id=lectures.campus','inner');
		$this->db->join('course_subjects','course_subjects.course_subject_id=session_syllabus.subject_id','inner');
		$this->db->where(array('lectures.id'=>$lecture_id));
		$this->db->group_by('session_syllabus.date');
		$data['my_lectures'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('timetable/all_lectures', $data);
        $this->load->view('inc/footer');
    }

    public function getShifts()
    {
        $campus_id = $this->input->post('campus_id');
        $study_type = $this->input->post('study_type');
        $this->db->where("find_in_set($campus_id, campus_id)");
        if($study_type)
            $this->db->where("study_type_id",$study_type);
        $shifts = $this->db->get('shifts')->result_array();

        $html ='';
        $html.='<option value="">SELECT SHIFT</option>';
        foreach($shifts as $shift)
        {
            $html.='<option value="'.$shift['id'].'">'.$shift['name'].'</option>';
        }

        echo $html;
    }
    
    public function getCourseStudyTypes()
    {
        $course_id = $this->input->post('course_id');
        $this->db->where('course_id',$course_id);
        $study_types = $this->db->get('study_type')->result_array();

        $html ='';
        $html.='<option value="">SELECT STUDY TYPE</option>';
        foreach($study_types as $study_type)
        {
            $html.='<option value="'.$study_type['id'].'">'.$study_type['name'].'</option>';
        }

        echo $html;
    }
    
    public function mark_student_lecture_attendance()
    {
        $student_id = $this->input->post('student_id');
        $lecture_id = $this->input->post('lecture_id');
        
        $this->db->set('student_id',$student_id);
        $this->db->set('lecture_id',$lecture_id);
        $this->db->set('add_by',$this->session->userdata('name'));
        $this->db->set('date',date('Y-m-d'));
        $this->db->insert('lecture_wise_attendance');
    }
    
    public function unmark_student_lecture_attendance()
    {
        $student_id = $this->input->post('student_id');
        $lecture_id = $this->input->post('lecture_id');
        
        $this->db->where('student_id',$student_id);
        $this->db->where('lecture_id',$lecture_id);
        $this->db->where('date',date('Y-m-d'));
        $this->db->delete('lecture_wise_attendance');
    }

}
