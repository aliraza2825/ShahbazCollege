<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Schedule extends CI_Controller {
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
    }

    public function add_syllabus()
    {
        $data['campuses'] = $this->clas->getCampuses();
        $data['courses']=$this->db->get('courses')->result_array();
        $data['studytype']=$this->db->get('study_type')->result_array();



        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('schedule/add_syllabus',$data);
        $this->load->view('inc/footer');
    }

    public function all_syllabus()
    {
        $data['campuses'] = $this->clas->getCampuses();
        $data['courses']=$this->db->get('courses')->result_array();
        $data['studytype']=$this->db->get('study_type')->result_array();

        if(@$this->input->post('submit')==1)
        {

            $course_id = $this->input->post('course_id');
            $subject_id = $this->input->post('subject_id');
            $studytype = $this->input->post('studytype');
            //$revision = $this->input->post('revision');

            $this->db->select('syllabus.*,courses.course_name,
			  course_subjects.subject_name,study_type.name as study_type');
            $this->db->from('syllabus');
            $this->db->join('courses', 'courses.course_id=syllabus.course_id', 'inner');
            $this->db->join('study_type', 'study_type.id=syllabus.studytype', 'inner');
            $this->db->join('course_subjects', 'course_subjects.course_subject_id=syllabus.subject_id', 'inner');
            $this->db->where('syllabus.practical_id is NULL');
            if($course_id!='')
            {
                $this->db->where('syllabus.course_id',$course_id);
            }
            if($subject_id!='')
            {
                $this->db->where('syllabus.subject_id',$subject_id);
            }
            if($studytype!='')
            {
                $this->db->where('syllabus.studytype',$studytype);
            }
            // if($revision!='')
            // {
            //     $this->db->where('syllabus.revision',$revision);
            // }
            $this->db->where('(syllabus.require_lectures like "1" or syllabus.require_lectures like "1-%")');
            $this->db->order_by("syllabus.syllabus_id", "asc");
            $this->db->group_by('syllabus.unique_syllabus_id');
            $data['syllabuss'] = $this->db->get()->result_array();

        }




        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('schedule/all_syllabus',$data);
        $this->load->view('inc/footer');
    }

    public function insert_syllabus()
    {
        $count_topics = count($this->input->post('topic_ids'));
        $count_practicals = count($this->input->post('practical_ids'));
        $max_syllabus_id = $this->db->select_max('unique_syllabus_id')->get('syllabus')->result_array();

        if (count($max_syllabus_id) > 0)
        {
            $max_syllabus_id = $max_syllabus_id[0]['unique_syllabus_id'];
            $max_syllabus_id++;
        }
        else
        {
            $max_syllabus_id = 1;
        }

        $topic_ids = $this->input->post('topic_ids');
        $practical_ids = $this->input->post('practical_ids');
        $days = $this->input->post('days');
        $practical_days = $this->input->post('practical_days');
        $course_id = $this->input->post('course_id');
        $subject_id = $this->input->post('subject_id');
        $add_by = $this->input->post('add_by');
        $last_edit = $this->input->post('last_edit');
        $revision = $this->input->post('revision');
        $studytype = $this->input->post('studytype');
        $syllabus_name = $this->input->post('syllabus_name');

        $myloop=0;

        for($a=0; $a<$count_topics; $a++)
        {
            $this->db->set('unique_syllabus_id', $max_syllabus_id);
            $this->db->set('syllabus_name', $syllabus_name);
            $this->db->set('course_id', $course_id);
            $this->db->set('subject_id', $subject_id);
            $this->db->set('topic_id', $topic_ids[$a]);
            $this->db->set('require_lectures', $days[$a]);
            $this->db->set('revision',$revision);
            $this->db->set('studytype',$studytype);
            $this->db->set('add_by',$add_by);
            $this->db->set('last_edit',$last_edit);
            $this->db->insert('syllabus');

        }

        for($a=0; $a<count($practical_days); $a++)
        {

            $this->db->set('unique_syllabus_id', $max_syllabus_id);
            $this->db->set('syllabus_name', $syllabus_name);
            $this->db->set('course_id', $course_id);
            $this->db->set('subject_id', $subject_id);
            $this->db->set('practical_id', $practical_ids[$a]);
            $this->db->set('require_lectures', $practical_days[$a]);
            $this->db->set('revision',$revision);
            $this->db->set('studytype',$studytype);
            $this->db->set('add_by',$add_by);
            $this->db->set('last_edit',$last_edit);
            $this->db->insert('syllabus');


        }

        $this->session->set_flashdata('message', 'Syllabus Added Successfully.');
        redirect('schedule/add_syllabus');
    }

    public function getPracticals()
    {
        $subject_id = $this->input->post('subject_id');

        //GET PRACTICALS
        $practicals = $this->db->get_where('practicals', array('subject_id'=>$subject_id))->result_array();
        $html='';
        $html.='<table class="table table-striped table-bordered table-hover"><thead><tr><th>Practical</th><th>Required Lectures</th></tr></thead>';
        $html.='';
        foreach($practicals as $practical)
        {
            $html.='<tr>';
            $html.='<td>'.$practical['practical_name'].'</td>';
            $html.='<td> <div class="input-group input-medium">
                        <input type="text" name="practical_days[]" class="form-control" value="" required>
                           
                    </div></td>';
            $html.='<input name="practical_ids[]" type="hidden" value="'.$practical['practical_id'].'" />';
            $html.='</tr>';
        }
        $html.='</table>';

        echo $html;
    }

    public function getTopics()
    {
        $chapter_id = $this->input->post('chapter_id');

        //GET TOPICS
        $topics = $this->db->order_by('topics.chapter_id','ASC')->join('chapters','chapters.chapter_id = topics.chapter_id')->get_where('topics', array('topics.course_subject_id'=>$chapter_id))->result_array();
        $html='';
        $html.='<table class="table table-striped table-bordered table-hover"><thead><tr><th>Sr</th><th>Chapter</th><th>Topic</th><th>Required Lectures + Lecture Number</th></tr></thead>';
        $html.='';
        foreach($topics as $key=>$topic)
        {
            $html.='<tr>';
            $html.='<td>'.($key+1).'</td>';
            $html.='<td>'.$topic['chapter_name'].'</td>';
            $html.='<td>'.$topic['topic_name'].'</td>';
            $html.='<td>
                    <div class="input-group input-medium">
                        <input type="text" name="days[]" class="form-control" value="" required>
                           
                    </div>
			</td>';
            $html.='<input name="topic_ids[]" type="hidden" value="'.$topic['topic_id'].'" />';
            $html.='</tr>';
        }
        $html.='</table>';


        echo $html;
    }

    public function getChapters()
    {
        $subject_id = $this->input->post('subject_id');
        $chapters = $this->db->get_where('chapters',array('course_subject_id'=>$subject_id))->result_array();
        $html='';
        $html.='<option value="">SELECT CHAPTER</option>';
        foreach($chapters as $chapter)
        {
            $html.='<option value="'.$chapter['chapter_id'].'">'.$chapter['chapter_name'].'</option>';
        }
        echo $html;
    }

    public function generate_table()
    {

        $subject = $this->input->post('subject');
        $study_type = $this->input->post('study_type');
        $revision = $this->input->post('syllabus_type');
        $lecture_id = $this->input->post('lecture_id');

        $lecture =$this->db->get_where('lectures','id = "'.$lecture_id.'"')->result_array();


        $lecture_max =$this->db->select('syllabus_id, require_lectures as max_lecture')->order_by('syllabus_id','DESC')
            ->limit('1')
            ->get_where('syllabus','unique_syllabus_id = "'.$revision.'" and practical_id IS NULL')
            ->result_array();

        $lecture_max = $lecture_max[0]['max_lecture'];

        $days=explode(",",$lecture[0]['days']);

        $start_date = date('Y-m-d',strtotime($this->input->post('start_date')));
        $quizgaps = $this->input->post('test_after');

        $months_and_dates = array();
        $i=0;
        $x=0;

        $subject_name =$this->db->get_where('course_subjects','course_subject_id = "'.$subject.'"')->result_array();
        $subject_name=$subject_name[0]['subject_name'];

        // loop over 365 days and look for tuesdays or wednesdays not in the excluded list
        foreach(range(0,366) as $day)
        {
            $internal_date = date('Y-m-d', strtotime("{$start_date} + {$day} days"));
            $this_day = date('Y-m-d', strtotime($internal_date));

            if ($i < $lecture_max)
            {
                foreach ($days as $tday)
                {

                    $holidays =$this->db->get_where('holidays','date = "'.$this_day.'"')->result_array();
                    if (($this->isTuesday($internal_date, $tday)) && count($holidays) < 1)
                    {

                        $months_and_dates[$x]['date'] = $this_day;
                        $months_and_dates[$x]['day'] = strtoupper($tday);
                        $months_and_dates[$x]['name'] = strtoupper($subject_name);
                        $months_and_dates[$x]['lecture_id'] = $lecture[0]['id'];
                        if($x%$quizgaps==0 && $x > 1){
                            $x++;

                        }
                        else
                        {
                            $i++;
                            $x++;
                        }
                    }

                }
            }
            else
            {
                $months_and_dates[$x]['date'] = $this_day;
                $months_and_dates[$x]['day'] = strtoupper($tday);
                $months_and_dates[$x]['name'] = strtoupper($subject_name);
                $months_and_dates[$x]['lecture_id'] = $lecture[0]['id'];
                break;
            }

        }


        $this->db->group_by('session');
        $sessions = $this->db->get_where('classes',array('session !='=>''))->result_array();


        $data['lectures']=$months_and_dates;
        $data['subject']=$subject;
        $data['study_type']=$study_type;
        $data['revision']=$revision;
        $data['sessions']=$sessions;
        $data['start_date']=$start_date;
        $data['lecture_id']=$lecture_id;
        $data['lecture']=$lecture[0];
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('schedule/lectures_generated_view',$data);
        $this->load->view('inc/footer');


    }

    function isTuesday($date,$day) {

        if ($day == 'monday')
        {

            $day = '1';
        }
        elseif ($day == 'tuesday')
        {

            $day = '2';
        }
        elseif ($day == 'wednesday')
        {

            $day = '3';
        }
        elseif ($day == 'thursday')
        {

            $day = '4';
        }elseif ($day == 'friday')
        {

            $day = '5';
        }elseif ($day == 'saturday')
        {

            $day = '6';
        }elseif ($day == 'sunday')
        {

            $day = '0';
        }

        return date('w', strtotime($date)) === $day;
    }

    public function deleteSyllabus()
    {
        $unique_syllabus_id = $this->input->post('unique_syllabus_id');
        $this->db->where('syllabus.unique_syllabus_id',$unique_syllabus_id);

        $this->db->delete('syllabus');

        $this->session->set_flashdata('message', 'Syllabus Deleted Successfully.');
        redirect('schedule/all_syllabus');


    }

    public function insert_session_syllabus()
    {

        $sessions=$this->input->post('sessions');
        $subject_id=$this->input->post('subject_id');
        $start_date=$this->input->post('start_date');
        $lecture_id=$this->input->post('lecture_id');
        $unique_syllabus_id=$this->input->post('unique_syllabus_id');

        $days=$this->input->post('day');
        $dates=$this->input->post('date');
        $topics=$this->input->post('topic_ids');
        $practicals=$this->input->post('practical_ids');

        $half_quiz_topics = "";
        $half_quiz_practicals = "";
        $quizpracticals="";
        $quiztopics="";

        $fcount = 0;
        for($x=0;$x<sizeof($days);$x++)
        {
            if(($practicals[$x]=="" || $practicals[$x]=="0" || $practicals[$x]==NULL) && $topics[$x]=="" )
            {
                $fcount++;
            }

        }

        $fcount = $fcount/2;

        $quiz_counts = 0;
        for($x=0;$x<sizeof($days);$x++)
        {

            if(($practicals[$x]=="" || $practicals[$x]=="0" || $practicals[$x]==NULL) && $topics[$x]=="" )
            {

                if ($quiz_counts == $fcount)
                {
                    $tops = $half_quiz_topics;
                    $pracs = $half_quiz_practicals;
                    $this->db->set('is_quiz', '1');
                    $this->db->set('is_half', '1');
                    $quizpracticals = "";
                    $quiztopics = "";
                }
                else {
                    $tops = $quiztopics;
                    $pracs = $quizpracticals;
                    $this->db->set('is_quiz', '1');
                    $quizpracticals = "";
                    $quiztopics = "";
                }

                $quiz_counts++;

            }

            else
            {
                if($topics[$x] != '' && $topics[$x] != NULL ) {
                    $quiztopics .= $topics[$x] . ",";
                    $half_quiz_topics .= $topics[$x] . ",";
                }

                if($practicals[$x] != '' && $practicals[$x] != NULL ) {
                    $quizpracticals .= $practicals[$x] . ",";
                    $half_quiz_practicals .= $practicals[$x] . ",";
                }

                $tops=$topics[$x];
                $pracs=$practicals[$x];
                $this->db->set('is_quiz','0');

            }

            if($tops == '' && $pracs == ''){

            }

            else
            {
                $this->db->set('sessions',$sessions);
                $this->db->set('subject_id',$subject_id);
                $this->db->set('day',$days[$x]);
                $this->db->set('date',$dates[$x]);
                $this->db->set('topic_ids',$tops);
                $this->db->set('practical_ids',$pracs);
                $this->db->set('start_date',$start_date);
                $this->db->set('lecture_id',$lecture_id);
                $this->db->set('syllabus_id',$unique_syllabus_id);
                $this->db->set('created_by',$this->session->userdata('name'));
                $this->db->insert('session_syllabus');

            }

        }

        $this->session->set_flashdata('message', 'Syllabus Created Successfully.');
        redirect('schedule/all_syllabus');

    }

    public function session_syllabus()
    {
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
        $data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();
        $data['shifts'] = $this->db->get('shifts')->result_array();

        $this->db->select('session_syllabus.*,courses.course_name,
			  course_subjects.subject_name, lectures.lecture_name,study_type.name as study_type,syllabus.revision, shifts.name as shift_name,campuses.campus_name as campus_name');
        $this->db->from('session_syllabus');
        $this->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
        $this->db->join('courses', 'courses.course_id=course_subjects.course_id', 'inner');
        $this->db->join('lectures', 'lectures.id=session_syllabus.lecture_id', 'inner');
        $this->db->join('campuses', 'lectures.campus=campuses.campus_id', 'inner');
        $this->db->join('syllabus', 'syllabus.unique_syllabus_id=session_syllabus.syllabus_id', 'inner');
        $this->db->join('study_type', 'syllabus.studytype=study_type.id', 'inner');
        $this->db->join('shifts', 'shifts.id=lectures.shift', 'inner');
        $this->db->group_by('session_syllabus.subject_id');
        $this->db->group_by('syllabus.unique_syllabus_id');

        if($this->input->post('course_id'))
        {
            $this->db->where('courses.course_id',$this->input->post('course_id'));
        }

        if($this->input->post('shift_id'))
        {
            $this->db->where('shifts.id',$this->input->post('shift_id'));
        }

        if($this->input->post('campus_id'))
        {
            $this->db->where('campuses.campus_id',$this->input->post('campus_id'));
        }

        $data['syllabuss'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('schedule/session_syllabus',$data);
        $this->load->view('inc/footer');
    }

    public function view_session_syllabus($subject,$lecture)
    {


        $this->db->select('session_syllabus.*,course_subjects.subject_name');
        $this->db->from('session_syllabus');
        $this->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
        $this->db->where('session_syllabus.subject_id = "'.$subject.'" and session_syllabus.lecture_id = "'.$lecture.'"');


        $data['lectures'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('schedule/syllabus_generated_view',$data);
        $this->load->view('inc/footer');
    }

    public function view_merged_session_syllabus($lecture)
    {

        $this->db->select('session_syllabus.*,course_subjects.subject_name');
        $this->db->from('session_syllabus');
        $this->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
        $this->db->where('session_syllabus.lecture_id = "'.$lecture.'"');
        $this->db->group_by('session_syllabus.date');
        $data['lectures'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('schedule/syllabus_merge_generated_view',$data);
        $this->load->view('inc/footer');

    }

    public function delete_session_syllabus($subject,$lecture,$syllabus_id)
    {
        $this->db->where('subject_id',$subject);
        $this->db->where('lecture_id',$lecture);
        $this->db->where('syllabus_id',$syllabus_id);

        $this->db->delete('session_syllabus');
        $this->session->set_flashdata('message', 'Syllabus Deleted Successfully.');
        redirect('schedule/session_syllabus');
    }

    public function view_syllabus($subject,$unique_syllabus_id)
    {
        $syllabus = $this->db->get_where('syllabus',array('unique_syllabus_id'=>$unique_syllabus_id))->result_array();

        $data['subject']=$subject;
        $data['syllabus']=$syllabus;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('schedule/syllabus_view',$data);
        $this->load->view('inc/footer');


    }

    public function Subject_Syllabuses()
    {

        $subject_id = $this->input->post('subject_id');
        $studytype = $this->input->post('study_type');


        $this->db->select('syllabus.*,courses.course_name,
			  course_subjects.subject_name,study_type.name as study_type');
        $this->db->from('syllabus');
        $this->db->join('courses', 'courses.course_id=syllabus.course_id', 'inner');
        $this->db->join('study_type', 'study_type.id=syllabus.studytype', 'inner');
        $this->db->join('course_subjects', 'course_subjects.course_subject_id=syllabus.subject_id', 'inner');
        $this->db->where('syllabus.practical_id is NULL');

        if($subject_id!='')
        {
            $this->db->where('syllabus.subject_id',$subject_id);
        }
        if($studytype!='')
        {
            $this->db->where('syllabus.studytype',$studytype);
        }

        $this->db->where('(syllabus.require_lectures like "1" or syllabus.require_lectures like "1-%")');
        $this->db->order_by("syllabus.syllabus_id", "asc");
        $this->db->group_by('syllabus.unique_syllabus_id');

        $syllabuses = $this->db->get()->result_array();

        $html = '<select name="syllabus_type" class="form-control" id="syllabus_type" required>
                              <option value="">Select Syllabus Type</option>';
        $revisioncount = 1;

        foreach ($syllabuses as $data)
        {
            $sysCheck = $this->db->get_where('session_syllabus',array('syllabus_id'=>$data['unique_syllabus_id']))->result_array();

            if ($data['revision'] == 0)
            {
                if(count($sysCheck)==0)
                {
                    $html .= "<option value='".$data['unique_syllabus_id']."'>Regular  - ".$data['syllabus_name']."</option>";
                }
            }
            else
            {
                if(count($sysCheck)==0)
                {
                    $html .= "<option value='".$data['unique_syllabus_id']."'>Revision $revisioncount - ".$data['syllabus_name']."</option>";
                }
                $revisioncount++;
            }

        }
        $html .= "</select>";

        echo $html;
    }

    public function getLastDate()
    {
        $subject_id = $this->input->post('subject_id');
        $lecture_id = $this->input->post('lecture_id');

        $this->db->select('*');
        $this->db->from('session_syllabus');
        $this->db->where(array('subject_id'=>$subject_id,'lecture_id'=> $lecture_id));
        $this->db->order_by('date','DESC');
        $this->db->limit(1);
        $lastLecture = $this->db->get()->result_array();

        if(count($lastLecture)>0)
        {
            echo date('Y-m-d',strtotime("+5 day", strtotime($lastLecture[0]['date'])));
            //echo $lastLecture[0]['date'];
        }
        else
        {
            echo '';
        }
    }
	
	public function assign_zoom()
    {
        $lecture_id = $this->input->post('lecture_id');
        $zoom_id = $this->input->post('zoom_id');
        $zoom_password = $this->input->post('zoom_password');

        $this->db->set('zoom_id',$zoom_id);
        $this->db->set('zoom_password',$zoom_password);
        $this->db->where('id',$lecture_id);
        $this->db->update('lectures');

        $this->session->set_flashdata('message', 'Syllabus Added Successfully.');
        redirect('schedule/add_syllabus');
    }
    
    public function lecture_attendance_students()
{
    $lecture_id = $this->input->post('lecture_id');
    $date       = $this->input->post('date');

    $this->db->select('
        lecture_wise_attendance.*,
        students.roll_no,
        students.first_name,
        students.last_name,
        students.cnic,
        students.mobile,
        students.emergency_no,
    ');
    $this->db->from('lecture_wise_attendance');
    $this->db->join('students', 'students.student_id = lecture_wise_attendance.student_id', 'left');
    $this->db->where('lecture_wise_attendance.lecture_id', $lecture_id);
    $this->db->where('lecture_wise_attendance.date', $date);
    $this->db->order_by('students.roll_no', 'ASC');

    $students = $this->db->get()->result_array();

    echo '<h4>Attendance Date: '.date('F d, Y', strtotime($date)).'</h4>';

    if(count($students) == 0)
    {
        echo '<div class="alert alert-warning">No student attended this lecture.</div>';
        return;
    }

    echo '<table class="table table-bordered table-striped">';
    echo '<thead>
            <tr>
                <th>Sr</th>
                <th>Roll No</th>
                <th>Student Name</th>
                <th>CNIC</th>
                <th>Mobile</th>
                <th>Added By</th>
                <th>Marked At</th>
            </tr>
          </thead>';
    echo '<tbody>';

    $i = 1;
    foreach($students as $student)
    {
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>'.$student['roll_no'].'</td>';
        echo '<td>'.$student['first_name'].' '.$student['last_name'].'</td>';
        echo '<td>'.$student['cnic'].'</td>';
        echo '<td>'.$student['mobile'].'<br>'.$student['emergency_no'].'</td>';
        echo '<td>'.$student['add_by'].'</td>';
        echo '<td>'.date('F d, Y h:i A', strtotime($student['updated_at'])).'</td>';
        echo '</tr>';

        $i++;
    }

    echo '</tbody>';
    echo '</table>';
}

public function save_absent_student_info()
{
    $student_id = $this->input->post('student_id');
    $lecture_id = $this->input->post('lecture_id');
    $info       = $this->input->post('info');

    if(empty($student_id) || empty($lecture_id) || empty($info)){
        echo json_encode(array('status' => 'error'));
        return;
    }

    $data = array(
        'student_id' => $student_id,
        'lecture_id' => $lecture_id,
        'info'       => $info,
        'add_by'     => $this->session->userdata('username')
    );

    $this->db->insert('lecture_absent_student_logs', $data);

    echo json_encode(array('status' => 'success'));
}
}