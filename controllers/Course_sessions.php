<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Course_sessions extends CI_Controller {
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

	public function add_course_session()
	{
		$data['courses'] = $this->db->get('courses')->result_array();
		
		$this->db->select('*');
		$this->db->from('course_sessions');
		$this->db->join('courses','courses.course_id=course_sessions.course_id','INNER');
		$data['sessions'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('course_sessions/add_course_session', $data);
		$this->load->view('inc/footer');
	}

	public function insert_course_session()
	{
		$course_id = $this->input->post('course_id');
		$session_name = $this->input->post('session_name');
		$dead_line_add_edit_student = $this->input->post('dead_line_add_edit_student');
		$last_date_auto_fee_installment = $this->input->post('last_date_auto_fee_installment');
		$per_student_fee = $this->input->post('per_student_fee');
		$maximum_fee_last_date = $this->input->post('maximum_fee_last_date');
		$minimum_installment_fee = $this->input->post('minimum_installment_fee');
		$maximum_difference_installments = $this->input->post('maximum_difference_installments');
		$council_board_fee = $this->input->post('council_board_fee');
		$first_council_exam_no = $this->input->post('first_council_exam_no');
		$first_time_council_fee = $this->input->post('first_time_council_fee');
		$last_date_council_fee = $this->input->post('last_date_council_fee');
		$re_admission_fee = $this->input->post('re_admission_fee');
		$freeze_fee = $this->input->post('freeze_fee');
		$freeze_last_date = $this->input->post('freeze_last_date');

		$check = $this->db->get_where('course_sessions',array('course_id'=>$course_id,'session_name'=>$session_name))->result_array();

		if(count($check)>0)
		{
			$this->session->set_flashdata('error','Session Already Added');
			redirect('course_sessions/add_course_session');
		}
		else
		{
			$this->db->set('course_id',$course_id);
			$this->db->set('session_name',$session_name);
			$this->db->set('dead_line_add_edit_student',$dead_line_add_edit_student);
			$this->db->set('last_date_auto_fee_installment',$last_date_auto_fee_installment);
			$this->db->set('per_student_fee',$per_student_fee);
			$this->db->set('maximum_fee_last_date',$maximum_fee_last_date);
			$this->db->set('minimum_installment_fee',$minimum_installment_fee);
			$this->db->set('maximum_difference_installments',$maximum_difference_installments);
			$this->db->set('council_board_fee',$council_board_fee);
			$this->db->set('first_council_exam_no',$first_council_exam_no);
			$this->db->set('first_time_council_fee',$first_time_council_fee);
			$this->db->set('last_date_council_fee',$last_date_council_fee);
			$this->db->set('re_admission_fee',$re_admission_fee);
			$this->db->set('freeze_fee',$freeze_fee);
			$this->db->set('freeze_last_date',$freeze_last_date);
			$this->db->insert('course_sessions');
			
			$this->db->set('last_date',$last_date_auto_fee_installment);
        	$this->db->set('total_fee',$per_student_fee);
        	$this->db->set('first_time_council_fee',$first_time_council_fee);
        	$this->db->set('last_date_council_fee',$last_date_council_fee);
        	$this->db->where('course_id',$course_id);
        	$this->db->where('session',$session_name);
        	$this->db->update('fee_rules');

			$this->session->set_flashdata('message','Session Added Successfully');
			redirect('course_sessions/add_course_session');
		}
	}
	
	public function all_course_sessions()
	{
		$this->db->select('*');
		$this->db->from('course_sessions');
		$this->db->join('courses','courses.course_id=course_sessions.course_id','INNER');
		$data['sessions'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('course_sessions/all_course_sessions', $data);
		$this->load->view('inc/footer');
	}

	public function delete_course_session($course_session_id)
	{
		$this->db->where('course_session_id',$course_session_id);
		$this->db->delete('course_sessions');

		$this->session->set_flashdata('message','Session Deleted Successfully');
		redirect('course_sessions/all_course_sessions');
	}

	public function edit_course_session($course_session_id)
	{
		$data['courses'] = $this->db->get('courses')->result_array();
		$data['session'] = $this->db->get_where('course_sessions',array('course_session_id'=>$course_session_id))->result_array();
		$data['exam_sequence'] = $this->db->get_where('exam_sequence',array('course_id'=>$data['session'][0]['course_id'],"class"=>1))->result_array();
// 		print_r($data['exam_sequence']);
// 		exit();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('course_sessions/edit_course_session', $data);
		$this->load->view('inc/footer');
	}

	public function update_course_session($course_session_id)
	{
		$course_id = $this->input->post('course_id');
		$session_name = $this->input->post('session_name');
		$dead_line_add_edit_student = $this->input->post('dead_line_add_edit_student');
		$last_date_auto_fee_installment = $this->input->post('last_date_auto_fee_installment');
		$per_student_fee = $this->input->post('per_student_fee');
		$maximum_fee_last_date = $this->input->post('maximum_fee_last_date');
		$minimum_installment_fee = $this->input->post('minimum_installment_fee');
		$maximum_difference_installments = $this->input->post('maximum_difference_installments');
		$council_board_fee = $this->input->post('council_board_fee');
		$first_council_exam_no = $this->input->post('first_council_exam_no');
		$first_time_council_fee = $this->input->post('first_time_council_fee');
		$last_date_council_fee = $this->input->post('last_date_council_fee');
		$re_admission_fee = $this->input->post('re_admission_fee');
		$freeze_fee = $this->input->post('freeze_fee');
		$freeze_last_date = $this->input->post('freeze_last_date');
		
		//UPDATE COUNCIL EXAM NUMBER IN PAYMENT PLAN OF EVERY STUDENT
		if($first_council_exam_no!=0)
		{
		    $classes = $this->db->get_where('classes',array('course_id'=>$course_id,'session'=>$session_name))->result_array();
		    $myclasses = array();
		    foreach($classes as $class)
		    {
		        array_push($myclasses,$class['class_id']);
		    }
		    if(count($myclasses)>0)
		    {
		        $this->db->select('student_id');
    		    $this->db->from('students');
    		    $this->db->where_in('class_id',$myclasses);
    		    $students = $this->db->get()->result_array();
    		    
    		    foreach($students as $student)
    		    {
    		        $this->db->select('*');
    		        $this->db->from('payments');
    		        $this->db->where(array('student_id'=>$student['student_id'],'payment_plan'=>'consulation fee'));
    		        $this->db->order_by('dead_line','ASC');
    		        $this->db->limit(1);
    		        $payment = $this->db->get()->result_array();
    		        
    		        if(count($payment)>0)
    		        {
    		            $this->db->set('payment_comment','This fee for next exam # '.$first_council_exam_no.' 1st Year');
        		        $this->db->where('id',$payment[0]['id']);
        		        $this->db->update('payments');
    		        }
    		    }
		    }
		}

		$this->db->set('course_id',$course_id);
		$this->db->set('session_name',$session_name);
		$this->db->set('dead_line_add_edit_student',$dead_line_add_edit_student);
		$this->db->set('last_date_auto_fee_installment',$last_date_auto_fee_installment);
		$this->db->set('per_student_fee',$per_student_fee);
		$this->db->set('maximum_fee_last_date',$maximum_fee_last_date);
		$this->db->set('minimum_installment_fee',$minimum_installment_fee);
		$this->db->set('maximum_difference_installments',$maximum_difference_installments);
		$this->db->set('council_board_fee',$council_board_fee);
		$this->db->set('first_council_exam_no',$first_council_exam_no);
		$this->db->set('first_time_council_fee',$first_time_council_fee);
		$this->db->set('last_date_council_fee',$last_date_council_fee);
		$this->db->set('re_admission_fee',$re_admission_fee);
		$this->db->set('freeze_fee',$freeze_fee);
		$this->db->set('freeze_last_date',$freeze_last_date);
		$this->db->where('course_session_id',$course_session_id);
		$this->db->update('course_sessions');
		
		$this->db->set('dead_line_entry',$dead_line_add_edit_student);
		$this->db->set('class_fee',$per_student_fee);
		$this->db->set('minimum_installment_fee',$minimum_installment_fee);
		$this->db->set('maximum_fee_last_date',$maximum_fee_last_date);
		$this->db->set('maximum_difference_installments',$maximum_difference_installments);
		$this->db->set('exam_no',$first_council_exam_no);
		$this->db->set('freeze_fee',$freeze_fee);
		$this->db->set('freeze_last_date',$freeze_last_date);
		$this->db->set('admission_fee',$re_admission_fee);
		$this->db->where('course_id',$course_id);
		$this->db->where('session',$session_name);
		$this->db->update('classes');
		
		$this->db->set('last_date',$last_date_auto_fee_installment);
		$this->db->set('total_fee',$per_student_fee);
		$this->db->set('first_time_council_fee',$first_time_council_fee);
		$this->db->set('last_date_council_fee',$last_date_council_fee);
		$this->db->where('course_id',$course_id);
		$this->db->where('session',$session_name);
		$this->db->update('fee_rules');
		
		

		$this->session->set_flashdata('message','Session Updated Successfully');
		redirect('course_sessions/edit_course_session/'.$course_session_id);
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

    public function getexams()
    {
        $course_id = $this->input->post('course_id');
        
        $this->db->select('*');
        $this->db->from('exam_sequence');
        $this->db->where('course_id',$course_id);
        $this->db->where('class',1);
        $this->db->where('first_year_type','annual');
        $campuses = $this->db->get()->result_array();

        $html='';
        $html.='<option value="">SELECT EXAM</option>';
        foreach($campuses as $campus)
        {
            $html.='<option value="'.$campus['first_year'].'">'.$campus['first_year'].' ( '.$campus['status']. ')' .'</option>';
        }
        echo $html;
    }
    public function getcouncilsequence()
    {
        $course_id = $this->input->post('course_id');
        $exam_sequence_id = $this->input->post('exam_sequence_id');
        $exam_sequence_id = $this->db->get_where('exam_sequence',[
                        'course_id' => $course_id,
                        'first_year' => $exam_sequence_id,
                        'class' => 1
                    ])->row_array();
                    $exam_sequence_id = $exam_sequence_id['id'];
    
        $this->db->select('*');
        $this->db->from('council_sequence');
        $this->db->where('course_id', $course_id);
        $this->db->where('has_fee', 1);
        $this->db->where('action_type', 'fee');
        $this->db->where_in('recurring',['One Time','Each Exam','Every Semester']);
        $sequences = $this->db->get()->result_array();
    
        $html = '';
    
        if (!empty($sequences))
        {
            foreach ($sequences as $fee)
                {
                    
                $html .= '<div style="border:1px solid #ddd; padding:10px; margin-bottom:10px; background:#f9f9f9;">';

                $html .= '<div style="font-weight:bold; color:#2c3e50; margin-bottom:6px;">
                            '.$fee['type_name'].' ( '.$fee['recurring'].' )
                          </div>';

                $rules = $this->db->get_where(
                    'council_sequence_fee_rules',
                    [
                        'sequence_fee_id' => $fee['council_sequence_id'],
                        'exam_sequence_id' => $exam_sequence_id
                    ]
                )->result_array();
                // echo $fee['council_sequence_id']. ' ' .$exam_sequence_id.'<br>';

                if (!empty($rules))
                {
                    $html .= '<table class="table table-condensed table-bordered" style="margin-bottom:0;">';

                    $html .= '<thead>
                                <tr style="background:#eee;">
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Exam Fee</th>
                                    <th>Expense</th>
                                    
                                </tr>
                              </thead>';

                    $html .= '<tbody>';

                    foreach ($rules as $rule)
                    {
                        $html .= '<tr>';

                        $html .= '<td>'.$rule['from_date'].'</td>';
                        $html .= '<td>'.$rule['to_date'].'</td>';
                        $html .= '<td>Rs '.$rule['exam_fee'].'</td>';
                        $html .= '<td>Rs '.$rule['expense_fee'].'</td>';

                        $html .= '</tr>';
                    }

                    $html .= '</tbody></table>';
                }
                else
                {
                    $html .= '<div style="color:#999;">No fee rules added</div>';
                }

                $html .= '</div>';
            }
    }
    else
    {
        $html .= '<div style="color:red;">No sequence found</div>';
    }

    echo $html;
}
}