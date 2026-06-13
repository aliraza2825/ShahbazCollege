<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Punjab_council_roll_number extends CI_Controller {
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
        $this->load->model('council');
        $this->load->model('clas');
        $this->load->model('student');
        ini_set('max_execution_time', '300');
    }

    public function index()
    {
        $this->db->select('campuses.campus_id, campuses.campus_name, punjab_council_roll_number.*, students.class_id, classes.name as class_name, students.mobile, students.emergency_no, contractors.name as contractor_name');
        $this->db->from('punjab_council_roll_number');
        $this->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
        $this->db->join('classes', 'students.class_id=classes.class_id', 'left');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->db->join('contracts', 'students.contract_id=contracts.contract_id', 'left');
        $this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'left');

        $this->db->where('punjab_council_roll_number.result_remarks', '');
        $data['roll_numbers'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/index', $data);
        $this->load->view('inc/footer');
    }

    public function upload_roll_no()
    {
        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'results/';

        // set the filter image types
        $config['allowed_types'] = 'csv';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        $this->upload->set_allowed_types('csv');

        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('roll_no')) {
            $data = array('msg' => $this->upload->display_errors());
            $file = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $file = $data['upload_data']['file_name'];
            }
        }

        if($file=='')
        {
            $this->session->set_flashdata('error', 'Problem in file uploading');
            redirect($_SERVER['HTTP_REFERER']);
        }
        else
        {
            $file = fopen('/home/shahbazc/public_html/lahore-campus/results/'.$file,"r");
            $row=1;
            while(! feof($file))
            {
                $index=fgetcsv($file);
                $check_record = $this->db->get_where('punjab_council_roll_number', array('council_exam_no'=>$this->input->post('council_exam_no'),'class'=>$this->input->post('class'),'course_id'=>$this->input->post('course_id'),'cnic'=>preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[5])))->result_array();
                if(count($check_record)>0)
                {
                    if($row!=1)
                    {
                        $this->db->set('class', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $this->input->post('class')));
                        $this->db->set('course_id', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $this->input->post('course_id')));
                        $this->db->set('roll_no', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[0]));
                        $this->db->set('computer_no', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[4]));
                        $this->db->set('cnic', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[5]));
                        $this->db->set('name', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[6]));
                        $this->db->set('address', $index[7]);
                        $this->db->set('remarks', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[9]));
                        $this->db->where('id', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $check_record[0]['id']));
                        $this->db->update('punjab_council_roll_number');
                    }
                }
                else
                {
                    if($row!=1 && $index[0] != null)
                    {
                        $this->db->set('council_exam_no', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $this->input->post('council_exam_no')));
                        $this->db->set('course_id', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $this->input->post('course_id')));
                        $this->db->set('class', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $this->input->post('class')));
                        $this->db->set('roll_no', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[0]));
                        $this->db->set('computer_no', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[4]));
                        $this->db->set('cnic', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[5]));
                        $this->db->set('name', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[6]));
                        $this->db->set('address', $index[7]);
                        $this->db->set('remarks', preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[9]));
                        $this->db->insert('punjab_council_roll_number');
                    }
                }
                $row++;
            }

            fclose($file);
            $this->session->set_flashdata('message', 'All Roll Numbers Added Successfully.');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function result()
    {
        if(@$this->input->post('council_exam_no'))
        {
            $this->db->select('campuses.campus_name, punjab_council_roll_number.*, students.class_id, classes.name as class_name, students.mobile, students.emergency_no, contractors.name as contractor_name');
            $this->db->from('punjab_council_roll_number');
            $this->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
            $this->db->join('classes', 'students.class_id=classes.class_id', 'left');
            $this->db->join('contractors', 'students.contractor_id=contractors.contractor_id', 'left');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
            $this->db->where(array('punjab_council_roll_number.council_exam_no'=>$this->input->post('council_exam_no'),'class'=>$this->input->post('council_class_no')));
            $data['roll_numbers'] = $this->db->get()->result_array();
        }
        else
        {
            $data['roll_numbers'] = array();
        }

        $this->db->select('council_exam_no, class, date, result_update_date');
        $this->db->from('punjab_council_roll_number');
        $this->db->group_by('council_exam_no');
        $data['council_exam_numbers'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/result', $data);
        $this->load->view('inc/footer');
    }

    public function upload_result_cards()
    {
        ini_set('max_execution_time', '300');
        $field_name='filefield';
        $path='results/';

        $this->load->library('upload');
        $files = $_FILES;
        $cpt = count($_FILES[$field_name]['name']);//count for number of image files
        $image_name =array();
        for($i=0; $i<$cpt; $i++)
        {
            $_FILES[$field_name]['name']= $files[$field_name]['name'][$i];
            $_FILES[$field_name]['type']= $files[$field_name]['type'][$i];
            $_FILES[$field_name]['tmp_name'] = $files[$field_name]['tmp_name'][$i];
            $_FILES[$field_name]['error']= $files[$field_name]['error'][$i];
            $_FILES[$field_name]['size'] = $files[$field_name]['size'][$i];

            $arr = explode(".", $_FILES[$field_name]['name'], 2);
            $first = $arr[0];

            $roll_count=$this->db->get_where('punjab_council_roll_number',array('roll_no'=>$first, 'class'=>$this->input->post('class'),'course_id'=>$this->input->post('course_id'), 'council_exam_no'=>$this->input->post('council_exam_no')))->result_array();

            if(count($roll_count)>0){

                print_r($roll_count[0]['roll_no']);


                if (!is_dir('results/result_'.$this->input->post('class').'_'.$this->input->post('council_exam_no')))
                {
                    mkdir('results/result_'.$this->input->post('class').'_'.$this->input->post('council_exam_no'), 0777, true);
                }

                $this->upload->initialize($this->set_upload_options('results/result_'.$this->input->post('class').'_'.$this->input->post('council_exam_no')));
                //for initalizing configuration for each image

                $this->upload->do_upload($field_name);

                $data = array('upload_data' => $this->upload->data());

                $this->db->set('result_image','results/result_'.$this->input->post('class').'_'.$this->input->post('council_exam_no').'/'.$data['upload_data']['file_name']);
                $this->db->where('id',$roll_count[0]['id']);
                $this->db->update('punjab_council_roll_number');


            }

        }


        $this->session->set_flashdata('message', 'All Result Added Successfully.');
        redirect('punjab_council_roll_number/result');
    }

    public function set_upload_options($path)
    {
        $config = array();
        $config['upload_path'] = $path;
        $config['allowed_types'] = '*';
        $config['overwrite']     = FALSE;

        return $config;
    }

    public function upload_result()
    {
        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'results/';

        // set the filter image types
        $config['allowed_types'] = 'csv';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        $this->upload->set_allowed_types('csv');

        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('roll_no')) {
            $data = array('msg' => $this->upload->display_errors());
            $file = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $file = $data['upload_data']['file_name'];
            }
        }

        if($file=='')
        {
            $this->session->set_flashdata('error', 'Problem in file uploading');
            redirect('punjab_council_roll_number/result');
        }
        else
        {
            $file = fopen('/home/shahbazc/public_html/lahore-campus/results/'.$file,"r");
            $updated_rows = 0;
            while(! feof($file))
            {
                $index=fgetcsv($file);
                
                $st = $this->db->get_where("punjab_council_roll_number",array('roll_no'=>$index[0], 'class'=>$this->input->post('class'), 'result_remarks'=>'', 'council_exam_no'=>$this->input->post('council_exam_no')))->row();
                if ($st != null) {
                    if ($index[2] == 'Pass' || $index[2] == 'Pass*') {
                        $this->db->set("section", "Second Year");
                        $this->db->where("cnic", $st->cnic);
                        $this->db->where("course_id", $this->input->post('class') ? $this->input->post('class'):1);
                        $this->db->update("students");
                    }
                }

                $this->db->set('result_remarks', $index[2]);
                $this->db->set('result_update_date', date('Y-m-d'));
                $this->db->where(array('roll_no'=>$index[0], 'class'=>$this->input->post('class'), 'council_exam_no'=>$this->input->post('council_exam_no')));
                $this->db->update('punjab_council_roll_number');
                $updated_rows += $this->db->affected_rows();
            }

            fclose($file);
            $this->session->set_flashdata('message', 'All Result Added Successfully. '.$updated_rows.' Results Updated');
            redirect('punjab_council_roll_number/result');
        }
    }

    public function delete_roll_no($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('punjab_council_roll_number');
        $this->session->set_flashdata('message', 'Roll No. Deleted Successfully.');
        redirect('punjab_council_roll_number');
    }

    public function update_cnic()
    {
        $cnic = $this->input->post('cnic');
        $id = $this->input->post('id');

        if(@$this->input->post('council_mistake')==1)
        {
            $council_mistake = 1;
        }
        else
        {
            $council_mistake = 0;
        }

        $this->db->set('cnic', $cnic);
        $this->db->set('council_mistake', $council_mistake);
        $this->db->where('id', $id);
        $this->db->update('punjab_council_roll_number');
    }
    
    public function update_computer_no()
    {
        $computer_no = $this->input->post('computer_no');
        $id = $this->input->post('id');

        $this->db->set('computer_no', $computer_no);
        $this->db->where('id', $id);
        $this->db->update('punjab_council_roll_number');
    }

    public function final_result()
    {
        $data['campuses'] = $this->clas->getCampuses();
        if(@$this->input->post('form_submit')==1)
        {
            $data['students'] = $this->student->getStudents();
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/all_results', $data);
        $this->load->view('inc/footer');
    }

    public function add_manual_remarks()
    {
        $id = $this->input->post('id');
        $remarks = $this->input->post('remarks');
        $next_admission = $this->input->post('next_admission');
        $add_by = $this->session->userdata('name');

        $this->db->where('id', $id);
        $this->db->delete('punjab_council_result_remarks');

        $this->db->set('id', $id);
        $this->db->set('remarks', $remarks);
        $this->db->set('next_admission', $next_admission);
        $this->db->set('add_by', $add_by);
        $this->db->insert('punjab_council_result_remarks');
    }

    public function add_council_fee()
    {
        if(@$this->input->post('add_council_fee')==1)
        {
            $council_exam_no = $this->input->post('council_exam_no');
            $class = $this->input->post('class');
            $first_year = $this->input->post('exam_sequence_first');
            $second_year = $this->input->post('exam_sequence_second');

            $data['seq_supplementary'] = $this->db->get_where('exam_sequence',"id = '$first_year'")->result_array();
            $data['seq_annual'] = $this->db->get_where('exam_sequence',"id = '$second_year'")->result_array();
            $data['results'] = $this->db->get_where('punjab_council_roll_number', array('council_exam_no'=>$council_exam_no, 'class'=>$class))->result_array();
        }

        $this->db->select('council_exam_no, class, date, result_update_date');
        $this->db->from('punjab_council_roll_number');
        $this->db->group_by('council_exam_no');
        $this->db->group_by('class');
        $data['council_exam_numbers'] = $this->db->get()->result_array();

        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $data['sequences'] = $this->db->get('exam_sequence')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/add_council_fee', $data);
        $this->load->view('inc/footer');
    }

    public function council_result_concile()
    {
        if(@$this->input->post('add_council_fee')==1)
        {
            $council_exam_no = $this->input->post('council_exam_no');
            $class = $this->input->post('class');

            $data['results'] = $this->db->get_where('punjab_council_roll_number', array('council_exam_no'=>$council_exam_no, 'class'=>$class))->result_array();
        }

        $this->db->select('council_exam_no, class, date, result_update_date');
        $this->db->from('punjab_council_roll_number');
        $this->db->group_by('council_exam_no');
        $this->db->group_by('class');
        $data['council_exam_numbers'] = $this->db->get()->result_array();

        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        $data['campuses'] = $this->db->get('campuses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/council_result_concile', $data);
        $this->load->view('inc/footer');
    }

    public function test()
    {
        $data = $this->input->post();

        //GET ALL POSTS
        $id= @$data['id'];
        $cnic= @$data['cnic'];
        $students_cnic= $data['students_cnic'];
        $students_result= $data['students_result'];
        $result_remarks= @$data['result_remarks'];

        $fee_for_students = $data['fee_for_students'];
        $fee_for_contractors = $data['fee_for_contractors'];
        $dead_line = $data['dead_line'];
        $class= $data['class'];
        $council_exam_no= $data['council_exam_no'];
        if ($class == 1)
            @$type = $this->db->get_where("exam_sequence","first_year = '$council_exam_no'")->row()->type;
        else
            @$type = $this->db->get_where("exam_sequence","second_year = '$council_exam_no'")->row()->type;
        $first_year = $this->input->post('exam_sequence_first');
        $second_year = $this->input->post('exam_sequence_second');

        $seq_supplementary = $this->db->get_where('exam_sequence',"id = '$first_year'")->result_array();
        $seq_annual = $this->db->get_where('exam_sequence',"id = '$second_year'")->result_array();

        $total_records = count($this->input->post('id'));

        for($i=1; $i<=$total_records; $i++)
        {
            //echo $cnic[$i-1];
            if($cnic[$i-1]!='')
            {
                $student = $this->db->get_where('students', array('cnic'=>$cnic[$i-1]))->result_array();
                if(count($student)>0)
                {
                    //ADD COUNCIL FEE OF THIS STUDENT
                    if($student[0]['contractor_id']==0)
                    {
                        //FEE ADD ACCORDING TO STUDENT

                        if($result_remarks[$i-1]=='Pass' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]=='Pass*' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='' && $result_remarks[$i-1]!='Pass*' && $class==1)
                        {
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            if ($this->input->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['first_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['first_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }
                            $challan_no = $this->getChallanNo();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 1st Year';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $fee_for_students);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('student_id', $student[0]['student_id']);
                                $this->db->set('payment_plan', 'consulation fee');
                                $this->db->set('payment_comment', $custom_comment);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $challan_no);
                                $this->db->set('exam_class', "1");
                                $this->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                                $insert_id = $this->db->insert_id();
    
                                $this->db->set('fee_id', $insert_id);
                                $this->db->set('amount', $fee_for_students);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->where('id', $id[$i-1]);
                                $this->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='' && $result_remarks[$i-1]!='Pass*' && $class==2)
                        {
                            //CUSTOME COMMENT FAIL IN 2nd YEAR
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            if ($this->input->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['second_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['second_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }
                            $challan_no = $this->getChallanNo();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $fee_for_students);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('student_id', $student[0]['student_id']);
                                $this->db->set('payment_plan', 'consulation fee');
                                $this->db->set('payment_comment', $custom_comment);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $challan_no);
                                $this->db->set('exam_class', "2");
                                $this->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                                $insert_id = $this->db->insert_id();
    
                                $this->db->set('fee_id', $insert_id);
                                $this->db->set('amount', $fee_for_students);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->where('id', $id[$i-1]);
                                $this->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==1)
                        {
                            $challan_no = $this->getChallanNo();

                            $next_council_exam_no = $seq_annual[0]['second_year'];
                            $next_council_exam_id = $seq_annual[0]['id'];
                            $custom_comment = 'Pass in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $fee_for_students);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('student_id', $student[0]['student_id']);
                                $this->db->set('payment_plan', 'consulation fee');
                                $this->db->set('payment_comment', $custom_comment);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $challan_no);
                                $this->db->set('exam_class', "2");
                                $this->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                                $insert_id = $this->db->insert_id();
    
                                $this->db->set('fee_id', $insert_id);
                                $this->db->set('amount', $fee_for_students);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->where('id', $id[$i-1]);
                                $this->db->update('punjab_council_roll_number');
                            }
                        }
                        
                        //CREATE DIPLOMA CHARGES
                        
                        if(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==2)
                        {
                            $diploma_fee = $this->input->post('diploma_fee');
                            $comm = 'Extra Fee For Diploma';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'extra fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$comm))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $diploma_fee);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('student_id', $student[0]['student_id']);
                                $this->db->set('payment_plan', 'extra fee');
                                $this->db->set('payment_comment', $comm);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $this->getChallanNo());
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                            }
                        }
                    }
                    elseif($student[0]['contractor_id']!=0)
                    {

                        if($result_remarks[$i-1]=='Pass' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]=='Pass*' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='Pass*'  && $result_remarks[$i-1]!='' && $class==1)
                        {
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            if ($this->input->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['first_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['first_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }

                            $challan_no = $this->getChallanNo();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 1st Year';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $fee_for_contractors);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('contract_id', $student[0]['contract_id']);
                                $this->db->set('payment_plan', 'consulation fee');
                                $this->db->set('payment_comment', $custom_comment);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $challan_no);
                                $this->db->set('exam_class', "1");
                                $this->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                                $insert_id = $this->db->insert_id();
    
                                $this->db->set('fee_id', $insert_id);
                                $this->db->set('amount', $fee_for_contractors);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->where('id', $id[$i-1]);
                                $this->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='Pass*' && $result_remarks[$i-1]!='' && $class==2)
                        {
                            $challan_no = $this->getChallanNo();
                            if ($this->input->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['second_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['second_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($council_exam_no+1).' 2nd Year';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $fee_for_contractors);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('contract_id', $student[0]['contract_id']);
                                $this->db->set('payment_plan', 'consulation fee');
                                $this->db->set('payment_comment', $custom_comment);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $challan_no);
                                $this->db->set('exam_class', "2");
                                $this->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                                $insert_id = $this->db->insert_id();
    
                                $this->db->set('fee_id', $insert_id);
                                $this->db->set('amount', $fee_for_contractors);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->where('id', $id[$i-1]);
                                $this->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==1)
                        {
                            $challan_no = $this->getChallanNo();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $next_council_exam_no = $seq_annual[0]['second_year'];
                            $next_council_exam_id = $seq_annual[0]['id'];
                            $custom_comment = 'Pass in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $fee_for_contractors);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('contract_id', $student[0]['contract_id']);
                                $this->db->set('payment_plan', 'consulation fee');
                                $this->db->set('payment_comment', $custom_comment);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $challan_no);
                                $this->db->set('exam_class', "2");
                                $this->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                                $insert_id = $this->db->insert_id();
    
                                $this->db->set('fee_id', $insert_id);
                                $this->db->set('amount', $fee_for_contractors);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->where('id', $id[$i-1]);
                                $this->db->update('punjab_council_roll_number');
                            }
                        }
                        //CREATE DIPLOMA CHARGES
                        
                        if(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==2)
                        {
                            $diploma_fee = $this->input->post('diploma_fee');
                            $comm = 'Extra Fee For Diploma';
                            
                            $check = $this->db->get_where('payments',array('payment_plan'=>'extra fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$comm))->result_array();

                            if(count($check)==0)
                            {
                                $this->db->set('amount', $diploma_fee);
                                $this->db->set('dead_line', $dead_line);
                                $this->db->set('student_id', $student[0]['student_id']);
                                $this->db->set('payment_plan', 'extra fee');
                                $this->db->set('payment_comment', $comm);
                                $this->db->set('add_by', 'System');
                                $this->db->set('last_edit', 'System');
                                $this->db->set('challan_no', $this->getChallanNo());
                                $this->db->set('custom_student_id', $student[0]['student_id']);
                                $this->db->insert('payments');
                            }
                        }
                    }
                }
                else
                {
                    //echo 'Ni Mila <br/>';
                }
            }
        }

        // COMMENT BELOW LINE FOR EXTRA FEES NOT CREATED
        $this->Create_extra_fee($students_result,$students_cnic,$council_exam_no,$class,$dead_line);
        if (@$this->input->post("coming_from"))
            $this->fee_not_created_for_next_exam();
        else
            $this->add_council_fee();


    }
    
    public function Create_extra_fee(array $students_result,array $students_cnic,$council_exam_no,$class,$dead_line)
    {
        $rule = $this->db->get('council_rules')->row();
        $extra_fee=$rule->total_fee;
        $no_of_exams=$rule->no_of_exams;

        foreach($students_cnic as $key=>$cnic){

            $student = $this->db->join('classes','classes.class_id = students.class_id')->where( array('cnic'=>$cnic))->get('students')->result_array();
            /*
            $this->db->select('sum(counted) as total_count');
            $this->db->from('council_exam_count');
            $this->db->where('student_id = "'.$student[0]['student_id'].'"');
            $counted_from_table=$this->db->get()->result_array();
            */
            if(count($student)>0)
            {
                if($student[0]['contractor_id']==0)
                {
                    $council_fee_count = $this->db->get_where('payments',array('student_id'=>$student[0]['student_id'],'payment_plan'=>'consulation fee'))->result_array();
                    $deleted_council_fee_count = $this->db->get_where('archive_payments',array('student_id'=>$student[0]['student_id'],'payment_plan'=>'consulation fee'))->result_array();
                    
                    $count_council_fees = count($council_fee_count)+count($deleted_council_fee_count);
                    
                    //CHECK FEE ALREADY CREATED OR NOT
                    $comm = 'This Extra Fee Created Against Council '.$this->addOrdinalSuffix((count($council_fee_count))).' Fee.';
                    $check = $this->db->get_where('payments',array('student_id'=>$student[0]['student_id'],'payment_comment'=>$comm))->result_array();
                    
                    //CHECK STUDENT IS PASSED OR NOT IN 2ND YEAR
                    $this->db->select('*');
                    $this->db->from('punjab_council_roll_number');
                    $this->db->where('class',2);
                    $this->db->where('cnic',$cnic);
                    $this->db->like('result_remarks','Pass');
                    $check_pass_status = $this->db->get()->result_array();
                    
                    if(count($check)==0 && count($check_pass_status)==0)
                    {
                        //CREATE EXTRA FEE IF STUDENT COUNCIL FEE COUNT GREATER THAN 4
                        if($count_council_fees>$no_of_exams)
                        {
                            $this->db->set('amount', $extra_fee);
                            $this->db->set('dead_line', date('Y-m-d', strtotime('-1 day', strtotime($dead_line))));
                            $this->db->set('student_id', $student[0]['student_id']);
                            $this->db->set('payment_plan', 'extra fee');
                            $this->db->set('payment_comment', $comm);
                            $this->db->set('add_by', 'System');
                            $this->db->set('last_edit', 'System');
                            $this->db->set('challan_no', $this->getChallanNo());
                            $this->db->set('custom_student_id', $student[0]['student_id']);
                            $this->db->insert('payments');
                        }
                    }
                    
                    /*
                    //FEE ADD ACCORDING TO STUDENT
                    if($students_result[$key]=='Pass' && $class==2){}
                    elseif($students_result[$key]=='Pass*' && $class==2)
                    {

                    }
                    elseif($students_result[$key]!='Pass' && $students_result[$key]!='Pass*' && $class==1)
                    {
                        $created = false;
                        //CUSTOME COMMENT FAIL IN 1st YEAR
                        $comm = 'Extra Fee due to Attached with College for more then 2 Years';
                        $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($council_exam_no+1).' 1st Year';
                        if((($council_exam_no-$student[0]['exam_no'])+2)>$no_of_exams)
                        {
                            $willcreate=((($council_exam_no+1)-($student[0]['exam_no']-1))+1)-$no_of_exams;

                            $willcreate=$willcreate-$counted_from_table[0]['total_count'];
                            if($willcreate > 0){
                                for($x=0;$x<$willcreate;$x++)
                                {
                                    $this->db->set('amount', $extra_fee);
                                    $this->db->set('dead_line', $dead_line);
                                    $this->db->set('student_id', $student[0]['student_id']);
                                    $this->db->set('payment_plan', 'extra fee');
                                    $this->db->set('payment_comment', $comm);
                                    $this->db->set('add_by', 'System');
                                    $this->db->set('last_edit', 'System');
                                    $this->db->set('challan_no', $this->getChallanNo());
                                    $this->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->db->insert('payments');

                                    $this->db->set('counted', '1');
                                    $this->db->set('student_id', $student[0]['student_id']);
                                    $this->db->set('created_by', $this->session->userdata('name'));
                                    $this->db->insert('council_exam_count');
                                    $created = true;
                                }
                            }
                        }
                        if ($created == false)
                        {
                            $this->db->select('count(*) as total_count');
                            $this->db->from('punjab_council_roll_number');
                            $this->db->where("cnic = '$cnic' and class = '1'");
                            $counted_fee=$this->db->get()->row();
                            if ($counted_fee)
                            {
                                if (count($counted_fee) == 3)
                                {
                                    $this->db->set('amount', $extra_fee);
                                    $this->db->set('dead_line', $dead_line);
                                    $this->db->set('student_id', $student[0]['student_id']);
                                    $this->db->set('payment_plan', 'extra fee');
                                    $this->db->set('payment_comment', $comm);
                                    $this->db->set('add_by', 'System');
                                    $this->db->set('last_edit', 'System');
                                    $this->db->set('challan_no', $this->getChallanNo());
                                    $this->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->db->insert('payments');
                                }
                            }
                        }
                    }
                    elseif($students_result[$key]!='Pass' && $students_result[$key]!='Pass*' && $class==2)
                    {


                        //CUSTOME COMMENT FAIL IN 1st YEAR
                        $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($council_exam_no+1).' 2nd Year';

                        $counts=(($council_exam_no+1)-($student[0]['exam_no']-1))+2;



                        if($counts>$no_of_exams)
                        {

                            $willcreate=($counts-$no_of_exams);
                            $comm = 'Extra Fee due to Attached with College for more then 2 Years';

                            $willcreate=$willcreate-$counted_from_table[0]['total_count'];

                            if($willcreate > 0){
                                for($x=0;$x<$willcreate;$x++)
                                {
                                    $this->db->set('amount', $extra_fee);
                                    $this->db->set('dead_line', $dead_line);
                                    $this->db->set('student_id', $student[0]['student_id']);
                                    $this->db->set('payment_plan', 'extra fee');
                                    $this->db->set('payment_comment', $comm);
                                    $this->db->set('add_by', 'System');
                                    $this->db->set('last_edit', 'System');
                                    $this->db->set('challan_no', $this->getChallanNo());
                                    $this->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->db->insert('payments');

                                    $this->db->set('counted', '1');
                                    $this->db->set('student_id', $student[0]['student_id']);
                                    $this->db->set('created_by', $this->session->userdata('name'));
                                    $this->db->insert('council_exam_count');

                                }
                            }


                        }



                    }
                    elseif(($students_result[$key]=='Pass' || $students_result[$key]=='Pass*') && $class==1)
                    {
                        //CUSTOME COMMENT FAIL IN 1st YEAR
                        if($council_exam_no % 2 == 0){
                            $next_council_exam_no = $council_exam_no-1;
                        }
                        else{
                            $next_council_exam_no = $council_exam_no;
                        }
                        $custom_comment = 'Pass in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';

                        if(((($council_exam_no+1)-($student[0]['exam_no']-1))+1)>$no_of_exams)
                        {
                            $willcreate=(((($council_exam_no+1)-($student[0]['exam_no']-1))+1)-$no_of_exams);
                            $comm = 'Extra Fee due to Attached with College for more then 2 Years';
                            $willcreate=$willcreate-$counted_from_table[0]['total_count'];

                            if($willcreate > 0){
                                for($x=0;$x<$willcreate;$x++)
                                {
                                    $this->db->set('amount', $extra_fee);
                                    $this->db->set('dead_line', $dead_line);
                                    $this->db->set('student_id', $student[0]['student_id']);
                                    $this->db->set('payment_plan', 'extra fee');
                                    $this->db->set('payment_comment', $comm);
                                    $this->db->set('add_by', 'System');
                                    $this->db->set('last_edit', 'System');
                                    $this->db->set('challan_no', $this->getChallanNo());
                                    $this->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->db->insert('payments');

                                    $this->db->set('counted', '1');
                                    $this->db->set('student_id', $student[0]['student_id']);
                                    $this->db->set('created_by', $this->session->userdata('name'));
                                    $this->db->insert('council_exam_count');
                                }
                            }
                        }
                    }
                    */
                }
            }
        }
    }

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

    public function appear_in_next_exam()
    {
        //GET CAMPUSES
        $this->db->select('council_exam_no, class, date, result_update_date','result_image');
        $this->db->from('punjab_council_roll_number');
        $this->db->group_by('council_exam_no');
        $data['council_exam_numbers'] = $this->db->get()->result_array();

        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        $data['campuses'] = $this->db->get('campuses')->result_array();

        //FILTER STUDENTS
        if(@$this->input->post('check')==1)
        {
            $campus_id = $this->input->post('campus_id');
            $council_exam_no = $this->input->post('council_exam_no');
            $class = $this->input->post('class');
            if($class==1)
            {
                $class='1st';
            }
            else
            {
                $class='2nd';
            }

            $this->db->select('*');
            $this->db->from('payments');
            $this->db->where('payment_comment  like ("%This fee for next exam # '.$council_exam_no.' '.$class.' Year%")');
            $data['results'] = $this->db->get()->result_array();
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/appear_in_next_exam', $data);
        $this->load->view('inc/footer');
    }

    public function create_excel_sheet()
    {
        /*$class_id = $this->input->post('class_id');
        $result = $this->council->getClassStudents($class_id);*/

        $campus_id = $this->input->post('campus_id');
        $council_exam_no = $this->input->post('council_exam_no');
        $class = $this->input->post('class');
        if($class==1)
        {
            $class='1st';
        }
        else
        {
            $class='2nd';
        }

        $this->db->select('students.student_id,students.roll_no, students.cnic, CONCAT(students.first_name," ", students.last_name, " S/O ", students.father_name) as name, students.address, students.mobile, students.board, 03158042977 as institute, campuses.campus_name, classes.name as class_name, payments.paid, payments.paid_date, payments.actual_amount, payments.payment_comment');
        $this->db->from('payments');
        $this->db->join('students', 'students.student_id=payments.custom_student_id', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->like('payments.payment_comment', 'This fee for next exam # '.$council_exam_no.' '.$class.' Year', 'both');
        $this->db->order_by('payments.paid', 'DESC');
        $this->db->order_by('students.roll_no', 'ASC');

        $results = $this->db->get()->result_array();

        $result = array();
        foreach($results as $res)
        {
            $this->db->select('*');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
            $this->db->where('student_id',$res['student_id']);
            $thisstudent = $this->db->get()->result_array();

            if($thisstudent[0]['campus_id']==$campus_id)
            {
                $checkFeeSubmitByCollegeInCouncil= $this->db->get_where('expenses',array('student_id'=>$res['student_id'],'council_exam_no'=>$council_exam_no,'class'=>$this->input->post('class')))->result_array();
                if(count($checkFeeSubmitByCollegeInCouncil)>0)
                {
                    $value = 'Submitted';
                }
                else
                {
                    $value = 'Not Submitted';
                }
                array_push($res,$value);
                array_push($result, $res);
            }
        }

        // Clear any previous output
        ob_end_clean();
        // I assume you already have your $result
        $num_fields = count($result);
        //Headings
        $heading = array(
            'Sr. #',
            'Student ID',
            'Roll #',
            'CNIC No.',
            'Name & Father Name',
            'Postal Address',
            'Student Mobile Number',
            'Board Name',
            'Institute Contact Number',
            'Campus Name',
            'Class Name',
            'Submit Fee By Student',
            'Paid Date',
            'Amount Paid',
            'Fee Reason',
            'Submit Fee By College in Council',
        );
        // Fetch MySQL result headers
        $headers = array();
        //$headers[] = "[Row]";
        for ($i = 0; $i <= 15; $i++) {
            $headers[] = $heading[$i];
        }

        // Filename with current date
        $filename = "Shahbaz-College-Council-List-of-Students.csv";

        // Open php output stream and write headers
        $fp = fopen('php://output', 'w');
        if ($fp && $result) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename='.$filename);
            header('Pragma: no-cache');
            header('Expires: 0');
            echo "NEXT EXAM STATUS \n\n";
            // Write mysql headers to csv
            fputcsv($fp, $headers);
            $row_tally = 0;
            // Write mysql rows to csv
            foreach($result as $student)
            {
                $row_tally++;
                echo $row_tally.",";
                fputcsv($fp, array_values($student));
            }
            die;
        }
    }

    public function print_diploma($student_id)
    {
        $this->db->select('students.*, campuses.campus_name, campuses.logo,punjab_council_roll_number.computer_no, punjab_council_roll_number.roll_no as result_roll_no, classes.session, punjab_council_roll_number.result_update_date as result_update_date');
        $this->db->from('students');
        $this->db->join('punjab_council_roll_number', 'punjab_council_roll_number.cnic=students.cnic', 'INNER');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
        $this->db->where('students.student_id', $student_id);
        $this->db->where('punjab_council_roll_number.class', '2');
        $this->db->where('punjab_council_roll_number.result_remarks', 'Pass');
        $data['student']=$this->db->get()->result_array();
        //$this->load->view('punjab_council_roll_number/print_diploma', $data);
        if(count($data['student'])>0)
        {
            $this->load->view('punjab_council_roll_number/print_diploma', $data);
        }
        else
        {
            echo 'Page Not Found';
        }
    }

    public function getAllExamNumber()
    {
        $selected_class = $this->input->post('selected_class');

        $this->db->select('*');
        $this->db->where('class',$selected_class);
        $this->db->where('course_id',1);
        $this->db->where('status','Active');
        $this->db->from('exam_sequence');
        $exam_numbers = $this->db->get()->result_array();

        $html='';
        foreach($exam_numbers as $exam_number)
        {
            $html.='<option value="'.$exam_number['first_year'].'">'.$exam_number['first_year'].'</option>';
        }

        echo $html;
    }

    public function status_report()
    {
        $check = @$this->input->post('check');
        if($check==1)
        {
            $data['class'] = $class = $this->input->post('class');
            $data['council_exam_no'] = $council_exam_no = $this->input->post('council_exam_no');

            /*$this->db->select('*');
            $this->db->from('expenses');
            $this->db->join('students','students.student_id=expenses.student_id','inner');*/

        }

        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/status_report',$data);
        $this->load->view('inc/footer');
    }
    
    function addOrdinalSuffix($number) {
        if (!is_numeric($number)) {
            return $number; // Return as is if not a number
        }
        
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
        
        return $number . $suffixes[$number % 10];
    }

    

    public function get_print_of_concel_list_paid(){

        $campus_id = $this->input->post('campus_id');
        $council_exam_no = $this->input->post('council_exam_no');
        $class = $this->input->post('class');
        if($class==1)
        {
            $class='1st';
        }
        else
        {
            $class='2nd';
        }
        $student_ids = $this->input->post('student_ids');
        if ($student_ids == "") {

            $this->db->select('students.student_id,students.roll_no, students.cnic, CONCAT(students.first_name," ", students.last_name, " S/O ", students.father_name) as name, students.address, students.mobile, students.board, 03158042977 as institute, campuses.campus_name, classes.name as class_name, payments.paid, payments.paid_date, payments.actual_amount, payments.payment_comment');
            $this->db->from('payments');
            $this->db->join('students', 'students.student_id=payments.custom_student_id or students.student_id=payments.student_id', 'inner');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
            $this->db->like('payments.payment_comment', 'This fee for next exam # ' . $council_exam_no . ' ' . $class . ' Year', 'both');
            $this->db->where('payments.paid', '1');
            $this->db->order_by('payments.paid', 'DESC');
            $this->db->order_by('students.roll_no', 'ASC');
            $this->db->group_by('students.cnic');
            $results = $this->db->get()->result_array();
        }else{
            $this->db->select('students.student_id,students.roll_no, students.cnic, CONCAT(students.first_name," ", students.last_name, " S/O ", students.father_name) as name, students.address, students.mobile, students.board, 03158042977 as institute, campuses.campus_name, classes.name as class_name, payments.paid, payments.paid_date, payments.actual_amount, payments.payment_comment');
            $this->db->from('payments');
            $this->db->join('students', 'students.student_id=payments.custom_student_id or students.student_id=payments.student_id', 'inner');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
            $this->db->where_in('students.student_id', explode(",",$student_ids));
            $this->db->order_by('payments.paid', 'DESC');
            $this->db->order_by('students.roll_no', 'ASC');
            $this->db->group_by('students.cnic');
            $results = $this->db->get()->result_array();
        }

        $result = array();
        foreach($results as $res)
        {
            $this->db->select('*');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
            $this->db->where('student_id',$res['student_id']);
            $thisstudent = $this->db->get()->result_array();

            if($thisstudent[0]['campus_id']==$campus_id)
            {
                $checkFeeSubmitByCollegeInCouncil= $this->db->get_where('expenses',array('student_id'=>$res['student_id'],'council_exam_no'=>$council_exam_no,'class'=>$this->input->post('class')))->result_array();
                if(count($checkFeeSubmitByCollegeInCouncil)>0)
                {
                    $value = 'Submitted';
                }
                else
                {
                    $value = 'Not Submitted';
                }
                array_push($res,$value);
                array_push($result, $res);

            }
        }

        foreach ($result as $key=>$re)
        {
            $lastCouncilexam = $this->db->order_by("id","DESC")->
            get_where('punjab_council_roll_number',array('cnic'=>@$re['cnic']))->row();
            $result[$key]['last_roll_no'] = $lastCouncilexam->roll_no;
        }
        $volume  = array_column($result, 'last_roll_no');
        array_multisort($volume, SORT_ASC, $result);
        $data['result'] = $result;

        $this->db->select('*');
        $this->db->from('campuses');
        $this->db->where('campus_id', $campus_id);
        $data['campus'] = $this->db->get()->result_array();
        $data['exam_no'] = $council_exam_no;

        if($this->input->post('class')==1)
        {
            $data['type'] = ucfirst($this->db->get_where('exam_sequence',array('first_year'=>$council_exam_no))->row()->type);
        }
        elseif($this->input->post('class')==2)
        {
            $data['type'] = ucfirst($this->db->get_where('exam_sequence',array('second_year'=>$council_exam_no))->row()->type);
        }
        
        /*
        if(strpos($result[0]['payment_comment'], 'Pass')!==false)
        {
            $data['type'] = 'Annual';

        }else
        {
            $data['type'] = 'Supplementary';
        }
        */



        $this->load->view('punjab_council_roll_number/get_print_of_concel_list_paid', $data);
    }

    function upload_roll_no_images()
    {
        if (!is_dir(getcwd().'/rollno_slips')) {
            mkdir(getcwd().'/rollno_slips', 0777);
        }

        $exam_no = $this->input->post('council_exam_no');
        $class = $this->input->post('class');
        $targetDir = "rollno_slips/";
        $allowTypes = array('jpg','png','jpeg','gif');
        $images = array();

        $statusMsg = $errorMsg = $insertValuesSQL = $errorUpload = $errorUploadType = '';
        $fileNames = array_filter($_FILES['files']['name']);
        if(!empty($fileNames)){
            foreach($_FILES['files']['name'] as $key=>$val){
                // File upload path
                $fileName = basename($_FILES['files']['name'][$key]);
                $targetFilePath = $targetDir . $fileName;

                // Check whether file type is valid
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
                if(in_array($fileType, $allowTypes)){
                    // Upload file to server
                    if(move_uploaded_file($_FILES["files"]["tmp_name"][$key], $targetFilePath)){
                        // Image db insert sql
                        array_push($images,$fileName);
                    }else{
                        $errorUpload .= $_FILES['files']['name'][$key].' | ';
                    }
                }else{
                    $errorUploadType .= $_FILES['files']['name'][$key].' | ';
                }
            }
            $errorUpload = !empty($errorUpload)?'Upload Error: '.trim($errorUpload, ' | '):'';
            $errorUploadType = !empty($errorUploadType)?'File Type Error: '.trim($errorUploadType, ' | '):'';
            $errorMsg = !empty($errorUpload)?'<br/>'.$errorUpload.'<br/>'.$errorUploadType:'<br/>'.$errorUploadType;

            if(count($images)>0){
                foreach ($images as $image)
                {
                    $image_name = strtok($image, '.');
                    $this->db->set("slip_image",$image);
                    $this->db->where("council_exam_no = '$exam_no' and class = '$class' and roll_no = '$image_name'");
                    $this->db->update("punjab_council_roll_number");
                }
                $this->session->set_flashdata('message', 'All Roll No Slips Added Successfully.');
                redirect('punjab_council_roll_number');
            }
            else{
                $this->session->set_flashdata('error', 'Images Names are incorrect.');
                redirect('punjab_council_roll_number');
            }
        }else{
            $this->session->set_flashdata('error', 'Something Wrong.');
            redirect('punjab_council_roll_number');
        }
    }

    public function add_exam_sequence($id = NULL)
    {
        if (!is_dir(getcwd().'/exam_sequence_documents')) {
            mkdir(getcwd().'/exam_sequence_documents', 0777);
        }
        if (@$this->input->post('type') != "")
        {
            $this->db->set("type",$this->input->post('type'));
            $this->db->set("first_year",$this->input->post('first_year'));
            $this->db->set("second_year",$this->input->post('second_year'));
            if (@$this->input->post('seq_id')) {

                //load the helper
                $this->load->helper('form');
                //Configure
                //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
                $config['upload_path'] = 'exam_sequence_documents/';
                // set the filter image types
                $config['allowed_types'] = 'csv';
                //load the upload library
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                $this->upload->set_allowed_types('*');
                $data['upload_data'] = '';

                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_roll_no')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_roll_no = $this->input->post('old_first_year_roll_no');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_roll_no = $data['upload_data']['file_name'];
                    }
                }
                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_date_sheet')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_date_sheet = $this->input->post('old_first_year_date_sheet');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_date_sheet = $data['upload_data']['file_name'];
                    }
                }
                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_date_sheet_nts')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_date_sheet_nts = $this->input->post('old_first_year_date_sheet_nts');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_date_sheet_nts = $data['upload_data']['file_name'];
                    }
                }
                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_result')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_result = $this->input->post('old_first_year_result');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_result = $data['upload_data']['file_name'];
                    }
                }
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_roll_no')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_roll_no = $this->input->post('old_second_year_roll_no');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_roll_no = $data['upload_data']['file_name'];
                    }
                }
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_date_sheet')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_date_sheet = $this->input->post('old_second_year_date_sheet');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_date_sheet = $data['upload_data']['file_name'];
                    }
                }
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_date_sheet_nts')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_date_sheet_nts = $this->input->post('old_second_year_date_sheet_nts');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_date_sheet_nts = $data['upload_data']['file_name'];
                    }
                }
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_result')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_result = $this->input->post('old_second_year_result');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_result = $data['upload_data']['file_name'];
                    }
                }

                $this->db->set("first_year_roll_no",$first_year_roll_no);
                $this->db->set("first_year_date_sheet",$first_year_date_sheet);
                $this->db->set("first_year_date_sheet_nts",$first_year_date_sheet_nts);
                $this->db->set("first_year_result",$first_year_result);
                $this->db->set("second_year_roll_no",$second_year_roll_no);
                $this->db->set("second_year_date_sheet",$second_year_date_sheet);
                $this->db->set("second_year_date_sheet_nts",$second_year_date_sheet_nts);
                $this->db->set("second_year_result",$second_year_result);

                $this->db->where("id", $this->input->post('seq_id'));
                $this->db->update("exam_sequence");
            }
            else {
                $this->db->insert("exam_sequence");
            }
        }
        if ($id != NULL)
            $data['seq'] = $this->db->get_where("exam_sequence","id = $id")->row();

        $data['sequences'] = $this->db->get("exam_sequence")->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/add_exam_sequence', $data);
        $this->load->view('inc/footer');
    }

    public function fee_not_created_for_next_exam()
    {
        //GET CAMPUSES
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $this->db->select('council_exam_no, class, date, result_update_date');
        $this->db->from('punjab_council_roll_number');
        $this->db->group_by('council_exam_no');
        $this->db->group_by('class');
        $data['council_exam_numbers'] = $this->db->get()->result_array();
        $council_roll_no = @$this->input->post('council_exam_no');
        $class = @$this->input->post('class');
        $data['sequences'] = $this->db->get('exam_sequence')->result_array();

        //FILTER STUDENTS
        if(@$this->input->post('add_council_fee')==1)
        {
            $this->db->select('*');
            $this->db->from('punjab_council_roll_number');
            $this->db->where('council_exam_no',$council_roll_no);
            $this->db->where('class',$class);
            $conci = $this->db->get()->result_array();
            if (count($conci)>0) {
                $first_year = $this->input->post('exam_sequence_first');
                $second_year = $this->input->post('exam_sequence_second');
                $data['seq_supplementary'] = $this->db->get_where('exam_sequence',"id = '$first_year'")->result_array();
                $data['seq_annual'] = $this->db->get_where('exam_sequence',"id = '$second_year'")->result_array();

                $this->db->select('*,classes.name as name,students.cnic as cnic');
                $this->db->from('students');
                $this->db->join('classes', 'classes.class_id = students.class_id');
                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
                $this->db->join('punjab_council_roll_number', 'punjab_council_roll_number.cnic = students.cnic', 'left');
                $this->db->where('students.status', '1');
                $this->db->where('classes.campus_id', @$this->input->post('campus_id'));
                $this->db->where('classes.exam_no < ' . $council_roll_no);
                $this->db->where("(punjab_council_roll_number.council_exam_no < " . @$this->input->post('council_exam_no') . " or punjab_council_roll_number.council_exam_no = '' or punjab_council_roll_number.council_exam_no is NULL)");
                $this->db->where("(punjab_council_roll_number.class = '" . $this->input->post('class') . "' or punjab_council_roll_number.class = '' or punjab_council_roll_number.class is NULL)");
                $this->db->where("(((punjab_council_roll_number.result_remarks != 'Pass*' and punjab_council_roll_number.result_remarks != 'Pass' and punjab_council_roll_number.result_remarks != '') or punjab_council_roll_number.result_remarks is NULL ) and council_exam_no != '$council_roll_no')");
                $this->db->where("(select count(*) as total from punjab_council_roll_number s where s.cnic = punjab_council_roll_number.cnic and s.class = '$class' and s.council_exam_no = '$council_roll_no' ) = 0");
                if ($class == 1)
                    $this->db->where("(select count(*) as total from punjab_council_roll_number s where s.cnic = punjab_council_roll_number.cnic and s.class = 2 ) = 0");
                else
                    $this->db->where("(select count(*) as total from punjab_council_roll_number s where s.cnic = punjab_council_roll_number.cnic and s.class = 2 and s.result_remarks like '%Pass%' ) = 0");
                $this->db->order_by('punjab_council_roll_number.id', 'DESC');
                $this->db->group_by('students.cnic');

                $data['results'] = $this->db->get()->result_array();
            }else
                $data['results'] = array();

        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('punjab_council_roll_number/council_fee_not_created', $data);
        $this->load->view('inc/footer');
    }

    public function get_covering_letter()
    {
        //$this->db->select('students.*,campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.phone1, campuses.phone2, campuses.phone3, campuses.phone4, campuses.phone5, campuses.phone6, campuses.phone7, campuses.logo, campuses.website, classes.session');
        //$this->db->from('students');
        //$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        //$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        //$this->db->where_in('students.student_id', explode(",",$this->input->post("student_ids")));
        //$data['student'] = $this->db->get()->result_array();

        $data['campus']=$this->db->get_where('campuses',array('campus_id'=>$this->input->post('campus_id')))->result_array();

        $this->load->view('punjab_council_roll_number/print_covering_letter', $data);
    }
    
    public function delete_council_roll_no($id)
    {
        $this->db->where('id',$id);
        $this->db->delete('punjab_council_roll_number');
        
        $this->session->set_flashdata('message', 'Punjab Council Roll no Deleted from system.');
        redirect('punjab_council_roll_number/result');
    }
}
