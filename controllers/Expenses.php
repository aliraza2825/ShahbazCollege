<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Expenses extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('expense');
        $this->load->library('upload');
        require_once("vendor/autoload.php");
    }

    public function insert()
    {

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
        if (!$this->upload->do_upload('image')) {

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
        $data = $this->input->post();
        $this->expense->storeExpense($data, $image);

        //$exp=$this->db->get_where('expenses','expense_id = "'.$this->input->post('expense_id').'"')->result_array();

        if($data['payment_type']=='cash')
        {
            $this->db->set('remaining_amount', 'remaining_amount -'.$this->input->post('amount') .'',false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');
        }

        $this->session->set_flashdata('message', 'Expense added successfully!');
        redirect('expenses/add_expense');
    }

    public function campusSMS($campus_name, $message)
    {
        $campus = $this->db->get_where('campuses', array('campus_name'=>$campus_name))->result_array();
        $numbers = array();
        array_push($numbers, $campus[0]['phone1']);
        array_push($numbers, $campus[0]['phone2']);

        foreach($numbers as $number)
        {
            $this->db->set('number', $number);
            $this->db->set('message', $message);
            $this->db->set('status', '');
            $this->db->set('date', date('Y-m-d H:i:s'));
            $this->db->set('chk', '0');
            $this->db->set('add_by', 'System');
            $this->db->insert('sms');
        }
    }

    public function update($id)
    {
        \Tinify\setKey("jcsTy5mwDNZypg4wZnvZQ7THGKVq6gXN"); //pass your actual API key
        $supported_image = array('image/gif', 'image/jpg', 'image/jpeg', 'image/png');
        if (in_array($_FILES['img']['type'], $supported_image)) {

            $src_file_name = $_FILES['img']['name'];

            if (!file_exists(getcwd().'/uploads')) {

                mkdir(getcwd().'/uploads', 0777);
            }

            move_uploaded_file($_FILES['img']['tmp_name'], getcwd().'/uploads/'.$src_file_name);

            //optimize image using TinyPNG
            $source = \Tinify\fromFile(getcwd().'/uploads/'.$src_file_name);
            $source->toFile(getcwd().'/uploads/'.$src_file_name);
            $image=$src_file_name;
            $data = $this->input->post();

        } else {
            $data = $this->input->post();
            $image = '';
        }

        $this->expense->updateExpense($data, $image);



        $this->session->set_flashdata('message', 'Expense updated successfully!');
        redirect('expenses/edit_expense/'.$id);
    }

    public function delete($id)
    {
        $this->expense->deleteExpense($id);
        $this->session->set_flashdata('message', 'Expense deleted successfully!');
        redirect('expenses/all_expenses');
    }

    public function add_expense()
    {
        $exp_campuses = $this->db->get_where('access', array('user_id'=>$this->session->userdata('user_id')))->row()->expense_campus_ids;

        $this->db->select('*');
        $this->db->from('campuses');
        if($this->session->userdata('role') != 'Admin' && $this->session->userdata('role') != 'Accounts' )
        {
            $this->db->where_in('campus_id', explode(',',$exp_campuses));
        }

        $data['campuses'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('campuses');

        $data['allcampuses'] = $this->db->get()->result_array();
        $data['exp_categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();
        $data['categories'] = $this->expense->getCategories();

        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->where('assign_to', $this->session->userdata('user_id'));
        $query = $this->db->get()->result_array();

        if(count($query)>0){
            $data['pettycash']=my_pettycash();
        }else {
            $data['pettycash']=0;
        }

        $to_date = date('Y-m-d');

        $this->db->select_sum('amount');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->where(array('expenses.date='=>$to_date,
            'expenses.user_id'=>$this->session->userdata('user_id'),
            'expenses.approved_status !='=>'1'));
        $expenses = $this->db->get()->result_array();

        if($data['pettycash']!=0)
            $data['pettycash'] = $data['pettycash']-$expenses[0]['amount'];
        $data['council_ids'] = $this->db->get_where("bank_reconciliation_statement","is_council_fee = 1 and CAST(REPLACE(debit,',','') as SIGNED) > tagged_amount")->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/add_expense', $data);
        $this->load->view('inc/footer');

    }

    public function edit_expense($id)
    {
        $data['campuses'] = $this->expense->getCampus();
        $data['categories'] = $this->expense->getCategories();
        $data['expenses'] = $this->expense->getExpense($id);

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/edit_expense', $data);
        $this->load->view('inc/footer');
    }

    public function all_expenses($newfrom_date = NULL,$newto_date = NULL,$newsetype = NULL)
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $setype='';

        if($newsetype != NULL && $newsetype != '')
        {

            $setype = $newsetype;
            $from_date = $newfrom_date;
            $to_date = $newto_date;

        }
        else{

            if($newto_date != NULL && $newto_date != '')
            {
                $from_date = $newfrom_date;
                $to_date = $newto_date;
            }else{
                $from_date = $this->input->post('from_date');
                $to_date = $this->input->post('to_date');

            }


            if ($this->input->post('setype') === 'Pending')
            {
                $setype='0';
            }
            if ($this->input->post('setype') === 'Approved')
            {
                $setype='1';
            }
            if ($this->input->post('setype') === 'Rejected')
            {
                $setype='2';
            }
            if ($this->input->post('setype') === 'Reversed')
            {
                $setype='3';
            }

        }

        if( $from_date == '' ){
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }

        //$data['expenses'] = $this->db->get_where('expenses', array('date>='=>$from_date, 'date<='=>$to_date))->result_array();
        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));

        if ($setype !== '')
        {
            $this->db->where('expenses.approved_status', $setype);

        }
        if ($access[0]['expense_view_user'] !== '1' && $this->session->userdata('role')!='Admin')
        {
            $this->db->where('expenses.add_by_id', $this->session->userdata('user_id'));

        }

        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('expenses.campus_id', $campus_ids);
        }

        $data['expenses'] = $this->db->get()->result_array();
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['setype'] = $setype;


        //$query = 'SELECT sum(amount) as total_expenses FROM expenses WHERE date>="'.$from_date.'" AND date<="'.$to_date.'"';
        //$data['total_expense'] = $this->db->query($query)->result_array();
        $this->db->select_sum('amount');
        $this->db->from('expenses');
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        $this->db->where(array('date>='=>$from_date, 'date<='=>$to_date));
        $data['total_expense'] = $this->db->get()->result_array();

        $data['pending'] = 0;
        $data['approved'] = 0;
        $data['rejected'] = 0;
        $data['reversed'] = 0;

        foreach ($data['expenses'] as $exp)
        {
            if ($exp['approved_status'] === '0')
            {
                $data['pending']++;


            }elseif ($exp['approved_status'] === '1')
            {
                $data['approved']++;

            }elseif ($exp['approved_status'] === '2')
            {
                $data['rejected']++;

            }elseif ($exp['approved_status'] === '3')
            {
                $data['reversed']++;

            }

        }


        $this->db->select('*');
        $this->db->from('campuses');
        if($this->session->userdata('role') != 'Admin' && $this->session->userdata('role') != 'Accounts' )
        {
            $this->db->where_in('campus_id', $campus_ids);
        }

        $data['campuses'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/all_expenses', $data);
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

    public function category()
    {

        $campus = $this->input->post('campus_id');
        $this->db->order_by('name', 'ASC');
        if ($campus)
            $data['categories'] = $this->db->where("find_in_set($campus, for_campus)")->get_where('expense_category',"sub_of is NULL")->result_array();
        else
            $data['categories'] = $this->db->get_where('expense_category',"sub_of is NULL")->result_array();
        $data['campuses'] = $this->db->get_where('campuses',"status = 1")->result_array();
        $data['my_campus'] = $campus;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/category', $data);
        $this->load->view('inc/footer');
    }

    public function print_categories($campus)
    {

        $this->db->order_by('name', 'ASC');
        $data['categories'] = $this->db->where("find_in_set($campus, for_campus)")->get_where('expense_category',"sub_of is NULL and status = 'active'")->result_array();
        $data['my_campus'] = $campus;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/print_category', $data);
        $this->load->view('inc/footer');
    }

    public function category_report()
    {
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");
        $campus_id = $this->input->post("campus_id");
        $this->db->select('*');
        $this->db->from('campuses');
        $data['campuses'] = $this->db->get()->result_array();
        if ($from_date == NULL){
            $from_date = date("Y-m-d");
            $to_date = date("Y-m-d");
        }
        $this->db->order_by('name', 'ASC');
        $data['categories'] = $this->db->get_where('expense_category',"sub_of is NULL")->result_array();
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['campus_id'] = $campus_id;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/category_report', $data);
        $this->load->view('inc/footer');
    }

    public function category_report_expenses($from_date,$to_date,$exp_id,$campus_id,$type)
    {
        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        if ($type == 'cash')
            $this->db->where("date >= '$from_date' and date <= '$to_date' and expenses.expense_category_id = '$exp_id' and expenses.campus_id = '$campus_id' and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)");
        else
            $this->db->where("date >= '$from_date' and date <= '$to_date' and expenses.expense_category_id = '$exp_id' and expenses.campus_id = '$campus_id' and expense_id IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)");
        $data['expenses'] = $this->db->get()->result_array();
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/all_category_report', $data);
        $this->load->view('inc/footer');
    }

    public function add_category()
    {
        $data = $this->input->post();
        $this->expense->addExpenseCategories($data);
        $this->session->set_flashdata('message', 'Expense Category added successfully!');
        redirect('expenses/category');
    }

    public function edit_expense_category($expense_category_id)
    {
        $data['current_categories'] = $this->expense->getCategory($expense_category_id);
        $data['exp_categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();

        $data['categories'] = $this->expense->getCategories();
        $data['campuses'] = $this->db->get_where('campuses',"status = 1")->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/edit_category', $data);
        $this->load->view('inc/footer');
    }

    public function update_category($expense_category_id)
    {
        $exp_cat = $this->input->post('expense_category_id');
        if (count($exp_cat) > 0) {
            if (count($exp_cat) == 1 && $exp_cat[count($exp_cat) - 1] == "")
            {

            }else {
                if ($exp_cat[count($exp_cat) - 1] == "")
                    $sub_of = $exp_cat[count($exp_cat) - 2];
                else {
                    $sub_of = $exp_cat[count($exp_cat) - 1];
                }
                $already_data = $this->db->select('count(*) as total')->get_where("expenses", "expense_category_id = $sub_of")->row();
                if ($already_data->total > 0) {
                    $this->session->set_flashdata('error', 'This Head already has expenses!');
                    redirect('expenses/edit_expense_category/' . $expense_category_id);
                }
                $this->db->set('has_sub', 1);
                $this->db->where('expense_category_id', $sub_of);
                $this->db->update('expense_category');

                $this->db->set('sub_of', $sub_of);
            }
        }

        $this->db->set('name', $this->input->post('name'));
        $this->db->set('status', $this->input->post('status'));
        $this->db->set('type', $this->input->post('type'));
        $this->db->set('for_campus', implode(",",$this->input->post('campus_ids')));
        $this->db->where('expense_category_id', $expense_category_id);
        $this->db->update('expense_category');
        $this->session->set_flashdata('message', 'Expense Category updated successfully!');
        redirect('expenses/edit_expense_category/'.$expense_category_id);
    }

    public function delete_expense_category($expense_category_id)
    {
        if($expense_category_id!=1 && $expense_category_id!=9)
        {
            $this->db->where('expense_category_id', $expense_category_id);
            $this->db->delete('expense_category');
            $this->session->set_flashdata('message', 'Expense Category deleted successfully!');
        }
        redirect('expenses/category');
    }

    public function getCampusStaff()
    {
        $campus_id = $this->input->post('campus_id');
        $staffs = $this->db->get_where('users', array('campus_id'=>$campus_id, 'status'=>1))->result_array();
        $html='';
        $html.='<option value="">Select User ID</option>';
        foreach($staffs as $staff)
        {
            $html.='<option value="'.$staff['user_id'].'">'.$staff['first_name'].' '.$staff['last_name'].'</option>';
        }
        echo $html;
    }

    public function getStudentsByClass()
    {
        $campus_id = $this->input->post('campus_id');
        $class_id = $this->input->post('class_id');
        $exam_no = $this->input->post('exam_no');


        $this->db->select('*');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
        $this->db->where(array('students.class_id'=>$class_id,'students.status'=>1));
        $students = $this->db->get()->result_array();


        $html='';
        $i=1;
        foreach($students as $student)
        {
            $this->db->select('*');
            $this->db->from('payments');
            $this->db->where(array('payment_plan'=>'consulation fee','student_id'=>$student['student_id']));
            $this->db->order_by('dead_line','ASC');
            $this->db->limit(1);
            $payment_deatils = $this->db->get()->result_array();


            if(@$payment_deatils[0]['paid']==1)
            {
                $paid='Submitted';
            }
            else
            {
                $paid='Not Submitted';
            }
            $checkAddedFee = $this->db->get_where('expenses',array('student_id'=>@$student['student_id']
            ,'council_exam_no'=>@$student['exam_no'],'class'=>1))->result_array();

            if(count($checkAddedFee)>0)
            {
                $html.='<tr class="alert-success">';
            }
            else
            {
                $html.='<tr>';
            }
            $html.='<td>'.@$i.'</td>';
            $html.='<td>'.@$student['name'].'</td>';
            $html.='<td>'.@$student['first_name'].' '.@$student['last_name'].'</td>';
            $html.='<td>'.@$this->db->get_where('contractors', array('contractor_id'=>$student['contractor_id']))->row()->name.'</td>';
            $html.='<td>'.@$student['cnic'].'</td>';
            $html.='<td>'.@$student['roll_no'].'</td>';
            $html.='<td> Council Exam # '.@$student['exam_no'].'</td>';
            $html.='<td>'.@$payment_deatils[0]['amount'].'</td>';
            $html.='<td>'.@$payment_deatils[0]['add_by'].'</td>';
            $html.='<td>'.$paid.'</td>';
            if(count($checkAddedFee)>0)
            {
                $html.='<td><a target="_blank" href="'.base_url().'uploads/'.$checkAddedFee[0]['image'].'" class="btn green"><i class="fa fa-image"></i></a></td>';
            }
            else
            {
                $html.='<td><label class="checkbox-inline"><input type="checkbox" class="student_id" name="student_id" value="'.@$student['student_id'].'" /> Submit Council Fee</label></td>';
            }
            $html.='</tr>';
            $i++;
        }
        echo $html;
    }

    public function getPaidCouncliFeeStudents()
    {
        $campus_id = $this->input->post('campus_id');
        $council_exam_no = $this->input->post('council_exam_no');
        $class = $this->input->post('campus_class');
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
        $this->db->join('students', 'students.student_id=payments.custom_student_id or students.student_id=payments.student_id', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->like('payments.payment_comment', 'This fee for next exam # '.$council_exam_no.' '.$class.' Year', 'both');
        $this->db->where('payments.paid','1');
        $this->db->order_by('payments.paid', 'DESC');
        $this->db->order_by('students.roll_no', 'ASC');

        $results = $this->db->get()->result_array();
        $html = "";
        $i = 1;
        $result = array();
        foreach($results as $result)
        {
            $this->db->select('*');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
            $this->db->where('student_id',$result['student_id']);
            $student_data = $this->db->get()->result_array();

            if($student_data[0]['campus_id']==$campus_id) {
                if (@$result['paid'] == 1) {
                    $paid = 'Submitted';
                } else {
                    $paid = 'Not Submitted in Council';
                }
                $checkAddedFee = $this->db->get_where('expenses', array('student_id' => @$student_data[0]['student_id'], 'council_exam_no' => $council_exam_no, 'class' => $this->input->post('campus_class'), 'approved_status !=' => "2"))->result_array();

                if (count($checkAddedFee) > 0) {
                    $html .= '<tr class="alert-success">';
                } else {
                    $html .= '<tr>';
                }
                $html .= '<td>' . @$i . '</td>';
                $html .= '<td>' . @$student_data[0]['name'] . '</td>';
                $html .= '<td>' . @$student_data[0]['first_name'] . ' ' . @$student_data[0]['last_name'] . '</td>';
                $html .= '<td>' . @$this->db->get_where('contractors', array('contractor_id' => $student_data[0]['contractor_id']))->row()->name . '</td>';
                $html .= '<td>' . @$student_data[0]['cnic'] . '</td>';
                $html .= '<td>' . @$student_data[0]['roll_no'] . '</td>';
                $html .= '<td>' . @$result['payment_comment'] . '</td>';
                $html .= '<td>' . @$result['amount'] . '</td>';
                $html .= '<td>' . @$result['add_by'] . '</td>';
                $html .= '<td>' . $paid . '</td>';
                if (count($checkAddedFee) > 0) {
                    $html .= '<td><a target="_blank" href="' . base_url() . 'uploads/' . @$checkAddedFee[0]['image'] . '" class="btn green"><i class="fa fa-image"></i></a></td>';
                } else {
                    $html .= '<td><label class="checkbox-inline"><input type="checkbox" class="student_id" name="student_id" value="' . @$student_data[0]['student_id'] . '" /> Submit Council Fee</label></td>';
                }
                $html .= '</tr>';
                $i++;
            }

        }
        echo $html;
    }

    public function change_approve_status()
    {
        $exp=$this->db->get_where('expenses','expense_id = "'.$this->input->post('expense_id').'"')->result_array();
        $this->db->set('approved_status', $this->input->post('status'));
        $this->db->set('last_edit', $this->input->post('last_edit'));
        $this->db->where('expense_id', $this->input->post('expense_id'));
        $this->db->update('expenses');
        $expid = $exp[0]['expense_id'];

        if($this->input->post('status')=='2'){
            $this->db->set('expense_id', $exp[0]['expense_id']);
            $this->db->set('amount', $exp[0]['amount']);
            $this->db->set('reverse_by', $this->session->userdata('user_id'));
            $this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('cash_reversal');

            $cos = $this->db->get_where("bank_reconciliation_statement","expense_id = '$expid'")->result_array();
            if (count($cos) > 0){
                $this->db->set('expense_id', null);
                $this->db->where('expense_id', $expid);
                $this->db->update('bank_reconciliation_statement');
            }
        }
        $this->session->set_flashdata('message','Success');
        redirect('expenses/all_expenses/');
    }

    public function singleexpensedetails($campus_id,$expid)
    {
        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
        $this->db->where(array('expenses.expense_category_id' => $expid, 'expenses.campus_id' => $campus_id));
        $this->db->order_by('expenses.expense_id','DESC');
        $this->db->limit(5);



        $expenses = $this->db->get()->result_array();



        $ret="";

        $i = 0;
        foreach ($expenses as $expense){

            $ret.= '
            <tr class="odd gradeX">
                               
                                <td>';
            $ret.= $expense['campus_name'];

            $ret.=" </td>  <td>";

            $ret.= @$expense['name'];

            $ret.="  </td>  <td>";

            $ret.= $expense['title'];

            if($expense['expense_category_id']==1):

                $ret.="<br />
                       Rickshaw Number : ";
                $ret.= $expense['rickshaw_number'];
                $ret.=" <br />
                       Rickshaw Driver No : ";
                $ret.= $expense['driver_phone'];
            endif;

            if($expense['expense_category_id']==13 && $expense['student_id']!=NULL):
                $student_data = $this->db->get_where('students',array('student_id'=>$expense['student_id']))->result_array();
                $ret.="Name : ";

                $ret.= $student_data[0]['first_name'];
                $ret.= $student_data[0]['last_name'];
                $ret.="(";
                $ret.= $student_data[0]['cnic'];
                $ret.=") <br />
                         Class : ";

                $ret.= $expense['class'];
                $ret.="Year <br /> Exam Number : ";
                $ret.= $expense['council_exam_no'];
            endif;
            $ret.="</td><td>";

            $ret.= $expense['purpose'];
            $ret.="</td> <td>";
            $ret.= $expense['amount'];
            $ret.="</td> <td>";
            $ret.= $expense['date'];
            $ret.="</td> <td>";
            if($expense['image']!='' && $expense['online_image']==''){

                $ret.='<a href="'.base_url().'uploads/'.$expense['image'].'"target="_blank">
                                            <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                        </a>';

            }
            elseif($expense['image']!='' && $expense['online_image']!='')
            {
                $ret.='<a href="'.$expense['online_image'].'"target="_blank">
                                            <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                        </a>';
            }


            $ret.='</td>  <td>';
            $ret.= $expense['add_by'];
            $ret.= '</td> 
         
        </tr>';

            $i++;
        }

        echo $ret;


    }

    public function request_reverse()
    {
        $this->db->set('rev_reason', $this->input->post('reason'));
        $this->db->set('rev_status', '0');
        $this->db->where('expense_id', $this->input->post('expense_rev_id'));
        $this->db->where('add_by_id', $this->input->post('last_edit'));
        $this->db->update('expenses');

        //$this->all_expenses();

        $this->session->set_flashdata('message','Reversal Request Submitted Successfully.');
        redirect('expenses/all_expenses');
    }

    public function all_expenses_report()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $data['campuses'] = $this->db->where_in( array('campus_id'=>$campus_ids))->get('campuses')->result_array();
        $data['categories'] = $this->db->get_where('expense_category','has_sub = 0')->result_array();

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');


        if( $from_date == '' ){

            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');


        }else{

            $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
            $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
            $this->db->where_in('expenses.expense_category_id',$this->input->post('categories'));
            $this->db->where_in('expenses.campus_id',$this->input->post('campus_ids'));
            $this->db->group_by('expenses.campus_id,expenses.expense_category_id');
            $data['expenses']=$this->db->get()->result_array();

        }
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/all_expenses_report', $data);
        $this->load->view('inc/footer');
    }

    public function request_reverse_approve()
    {
        if($this->input->post('status')=='1')  {
            if ($this->input->post('pay_through') == 'bank'){
                $this->db->where('expense_id', $this->input->post('expense_id'));
                $this->db->delete('expenses');

                $cos = $this->db->get_where("bank_reconciliation_statement", "expense_id = '" . $this->input->post('expense_id') . "'")->result_array();
                if (count($cos) > 0) {
                    $this->db->set('expense_id', null);
                    $this->db->where('expense_id', $this->input->post('expense_id'));
                    $this->db->update('bank_reconciliation_statement');
                }
            }else {
                $this->db->set('rev_status', '1');
                $this->db->set('approved_status', '3');
                $this->db->set('last_edit', $this->input->post('last_edit'));
                $this->db->where('expense_id', $this->input->post('expense_id'));
                $this->db->update('expenses');
                $exp = $this->db->get_where('expenses', 'expense_id = "' . $this->input->post('expense_id') . '"')->result_array();

                $this->db->set('expense_id', $exp[0]['expense_id']);
                $this->db->set('amount', $exp[0]['amount']);
                $this->db->set('reverse_by', $this->session->userdata('user_id'));
                $this->db->set('created_at', date('Y-m-d H:i:s'));
                $this->db->insert('cash_reversal');

                $cos = $this->db->get_where("bank_reconciliation_statement", "expense_id = '" . $this->input->post('expense_id') . "'")->result_array();
                if (count($cos) > 0) {
                    $this->db->set('expense_id', null);
                    $this->db->where('expense_id', $this->input->post('expense_id'));
                    $this->db->update('bank_reconciliation_statement');
                }
            }
        }
        elseif ($this->input->post('status')=='2') {
            $this->db->set('rev_status', '2');
            $this->db->where('expense_id', $this->input->post('expense_id'));
            $this->db->update('expenses');
        }
        $this->all_expenses();
    }

    public function all_expenses_details($from_date,$to_date,$campus_id,$category_id)
    {
        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
        $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
        $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.expense_category_id'=>$category_id));

        $data['expenses'] = $this->db->get()->result_array();

        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/all_expenses_report_detail', $data);
        $this->load->view('inc/footer');
    }

    public function all_expenses_subhead_report()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $data['campuses'] = $this->db->where_in( array('campus_id'=>$campus_ids))->get('campuses')->result_array();
        $data['categories'] = $this->db->get_where('expense_category',"sub_of IS NULL")->result_array();
        $data['sub_categories'] = $this->db->get('expense_category')->result_array();

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');

        $data['selected_campuses'] = $this->db->where_in('campus_id',$this->input->post('campus_ids'))->get('campuses')->result_array();
        $categories = $this->input->post('categories');

        if( $from_date == '' ){
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }
        else{
            $data['sub_heads'] = $this->db->get_where("expense_category","sub_of = '".$categories."'")->result_array();
            if (count($data['sub_heads']) < 1) {
                $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id');
                $this->db->from('expenses');
                $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
                $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
                $this->db->where(array('expenses.date>=' => $from_date, 'expenses.date<=' => $to_date));
                $this->db->where('expenses.expense_category_id',$categories);
                $this->db->where('expenses.campus_id', $this->input->post('campus_ids'));
                $this->db->group_by('expenses.campus_id,expenses.expense_category_id');
                $data['expenses'] = $this->db->get()->result_array();
            }else{
                $head = $this->db->get_where("expense_category","expense_category_id = '".$categories."'")->row();
                $data['expenses'] = array(['total_amount]'=>'0','campus_id'=>$this->input->post('campus_ids'),'campus_name'=> '','name'=>$head->name,'expense_category_id'=>$categories]);
            }
        }
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/all_expenses_report_sub_head', $data);
        $this->load->view('inc/footer');
    }

    public function getSubExpenses()
    {
        $course_id = $this->input->post('expense_id');
        $count = $this->input->post('count');
        $count += 1;
        $subjects = $this->db->get_where('expense_category', array('sub_of'=>$course_id,'status'=>'active'))->result_array();
        $html='<div class="form-group" id="div-'.$count.'"><label class="col-md-3 control-label">Expense Sub Category <span class="required">*</span></label> <div class="col-md-9">
                    <select class="form-control Select2 exps" name="expense_category_id[]" data-count="'.$count.'" id="category_id'.$count.'" required><option value="">Select SubExpense</option>';
        foreach($subjects as $subject) {
            $html.='<option value="'.$subject['expense_category_id'].'">'.$subject['name'].'</option>';
        }
        $html.="</select></div></div>";
        if (count($subjects) > 0)
            echo $html;
    }

    public function getSubExpensesFree()
    {
        $course_id = $this->input->post('expense_id');
        $count = $this->input->post('count');
        $count += 1;
        $subjects = $this->db->get_where('expense_category', array('sub_of'=>$course_id))->result_array();
        $html='<div class="form-group" id="div-'.$count.'"><label class="col-md-3 control-label">Expense Sub Category <span class="required">*</span></label> <div class="col-md-9">
                    <select class="form-control Select2 exps" name="expense_category_id[]" data-count="'.$count.'" id="category_id'.$count.'"><option value="">Select SubExpense</option>';
        foreach($subjects as $subject) {
            $html.='<option value="'.$subject['expense_category_id'].'">'.$subject['name'].'</option>';
        }
        $html.="</select></div></div>";
        if (count($subjects) > 0)
            echo $html;
    }

    public function test()
    {
        /*$qry = 'SELECT e.* FROM expenses e
        INNER JOIN students s ON s.student_id=e.student_id
        INNER JOIN classes c ON c.class_id=s.class_id
        WHERE c.session LIKE "2020-2022" AND c.course_id=1';
        $expenses = $this->db->query($qry)->result_array();

        echo '<pre>';
        print_r($expenses);
        echo '</pre>';
        foreach($expenses as $expense)
        {
            $this->db->set('council_exam_no',24);
            $this->db->where('expense_id',$expense['expense_id']);
            $this->db->update('expenses');
        }*/

    }

    public function getCouncilExamNo()
    {
        $class_id = $this->input->post('class_id');
        $class = $this->db->get_where('classes',array('class_id'=>$class_id))->result_array();
        echo $class[0]['exam_no'];
    }

    public function getSalaries()
    {
        $expense_id = $this->input->post('expense_id');
        
        $this->db->select('payroll.*,users.first_name,users.last_name');
        $this->db->from('payroll');
        $this->db->join('users','users.user_id=payroll.user_id','inner');
        $this->db->where('payroll.expense_id',$expense_id);
        $salaries = $this->db->get()->result_array();

        $html ='';
        $total_salary = 0;
        foreach($salaries as $salary)
        {
            $html.='<tr>';
            $html.='<td>';
            $html.=$salary['first_name'].' '.$salary['last_name'];
            $html.='</td>';
            $html.='<td>';
            $html.=$salary['earned_salary'];
            $html.='</td>';
            $html.='<td>';
            $html.=$salary['payroll_month'].' '.$salary['payroll_year'];;
            $html.='</td>';
            $html.='</tr>';

            $total_salary+=$salary['earned_salary'];
        }
        $html.='<tr>';
        $html.='<td colspan="3">';
        $html.= 'Total Salary :'.$total_salary;
        $html.='</td>';
        $html.='</tr>';

        echo $html;
        echo '<div class="alert alert-danger">Total Salary : '.$total_salary.'</div>';
        
        //echo $expense_id;
    }

    public function getAllExpenses()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $search_campus = $this->input->post('campus');

        if ($this->input->post('setype') === 'Pending')
        {
            $setype='0';
        }
        elseif ($this->input->post('setype') === 'Approved')
        {
            $setype='1';
        }
        elseif ($this->input->post('setype') === 'Rejected')
        {
            $setype='2';
        }
        elseif ($this->input->post('setype') === 'Reversed')
        {
            $setype='3';
        }
        else
        {
            $setype = '';
        }

        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));

        if ($setype !== '')
        {
            $this->db->where('expenses.approved_status', $setype);
        }
        if ($access[0]['expense_view_user'] !== '1' && $this->session->userdata('role')!='Admin')
        {
            $this->db->where('expenses.add_by_id', $this->session->userdata('user_id'));
        }
        if($this->session->userdata('role')!='Admin' && $search_campus == '')
        {
            $this->db->where_in('expenses.campus_id', $campus_ids);
        }
        if($this->input->post('campus'))
        {
            $this->db->where('expenses.campus_id', $this->input->post('campus'));
        }

        $expenses = $this->db->get()->result_array();

        //GET TOTAL EXPENSE
        $this->db->select_sum('amount');
        $this->db->from('expenses');
        if($this->session->userdata('role')!='Admin' && $search_campus == '')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        if($this->input->post('campus'))
        {
            $this->db->where('expenses.campus_id', $this->input->post('campus'));
        }
        $this->db->where(array('date>='=>$from_date, 'date<='=>$to_date));
        $total_expense = $this->db->get()->result_array();

        $html = '';

        $html.='<div class="alert alert-success"><p>Total expense from '.date('d F, Y',strtotime($from_date)).' to '.date('d F, Y',strtotime($to_date)).' is '.$total_expense[0]['amount'].'</p></div>';

        $html.='<table class="table table-striped table-bordered table-hover" id="sample_2">';
        $html.='<thead>';
        $html.='<tr>';
        $html.='<th class="hidden">hidden</th>';
        $html.='<th>Campus</th>';
        $html.='<th>History</th>';
        $html.='<th>Category</th>';
        $html.='<th>Title</th>';
        $html.='<th>Purpose</th>';
        $html.='<th>Amount</th>';
        $html.='<th>Date</th>';
        $html.='<th>Upload Date</th>';
        $html.='<th>Receipt</th>';
        $html.='<th>Add By</th>';
        $html.='<th>Last Edit</th>';
        $html.='<th>Status</th>';
        $html.='<th>Expense Type</th>';
        $html.='<th>Action</th>';
        $html.='</tr>';
        $html.='</thead>';
        $html.='<tbody>';
        $i=0;
        foreach($expenses as $expense):
        $html.='<tr class="odd gradeX">';
        $html.='<td class="hidden">'.$i.'</td>';
        $html.='<td>'.$expense['campus_name'].'</td>';
        $html.='<td>';
        $html.='<a data-toggle="modal" data-id="'.$i.'" data-campus-id="'.$expense['campus_id'].'" data-category-id="'.$expense['expense_category_id'].'" data-expense-id="'.$expense['expense_id'].'" data-approved-status="'.$expense['approved_status'].'" class="open-exphistDialog btn btn-primary" style="width: 100px" href="#historyexpense">History</a>';
        $html.='</td>';
        $html.='<td>';
        if($expense['expense_category_id']==9):
            $html.=$expense['name'];
            $html.='<br />';
            $user = $this->db->get_where('users', array('user_id'=>$expense['user_id']))->result_array();
            $html.=@$user[0]['first_name'].' '.@$user[0]['last_name'];
        elseif($expense['expense_category_id']==36):
            $payrolls = $this->db->join('users','users.user_id = payroll.user_id')->get_where('payroll', array('expense_id'=>$expense['expense_id']))->result_array();
            foreach($payrolls as $payroll){
                $html.= $payroll['first_name'].' '.$payroll['last_name'].' ( '.$payroll['earned_salary'].' )'.'<br>';
            }
        else:
            $html.=@$expense['name'];
        endif;
        $html.='</td>';
        $html.='<td>';
        $html.=$expense['title'];
        if($expense['expense_category_id']==1):
            $html.='<br />';
            $html.='Rickshaw Number : '.$expense['rickshaw_number'];
            $html.='<br />';
            $html.='Rickshaw Driver No : '.$expense['driver_phone'];
        endif;
        if($expense['expense_category_id']==13 && $expense['student_id']!=NULL):
            $student_data = $this->db->get_where('students',array('student_id'=>$expense['student_id']))->result_array();
            $html.='Name : '.$student_data[0]['first_name'].' '.$student_data[0]['last_name'].' ('.$student_data[0]['cnic'].')';
            $html.='<br />';
            $html.='Class : '.$expense['class'].' Year';
            $html.='<br />';
            $html.='Exam Number : '.$expense['council_exam_no'];
        endif;
        $html.='</td>';
        $html.='<td>'.$expense['purpose'].'</td>';
        $html.='<td>'.$expense['amount'].'</td>';
        $html.='<td>'.$expense['date'].'</td>';
        $html.='<td>'.$expense['actual_date'].'</td>';
        $html.='<td>';
        //if($expense['image']!='' && $expense['online_image']==''):
            if($expense['online_image']=='' && $expense['image']!=''):
                $html.='<a class="btn btn-default" href="'.base_url().'uploads/'.$expense['image'].'" target="_blank"><i class="fa fa-image"></i> Image</a>';
            elseif($expense['online_image']!='' && $expense['image']!=''):
                $bucket_address= 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
                $cloudfront_address= 'https://d10iw6eujrfvyr.cloudfront.net';
                $html.='<a class="btn btn-default" href="'.str_replace($bucket_address,$cloudfront_address,$expense['online_image']).'" target="_blank"><i class="fa fa-image"></i> Image</a>';
            endif;
        //endif;
        $html.='</td>';
        $html.='<td>'.$expense['add_by'].'</td>';
        $html.='<td>'.$expense['last_edit'].'</td>';
        $html.='<td>';
        if ($expense['approved_status'] == '0'):
            $html.='<a data-toggle="modal" data-id="'.$i.'" class="btn btn-primary" style="width: 100px">PENDING</a>';
        elseif($expense['approved_status'] == '2'):
            $html.='<a data-toggle="modal" class="btn red"  style="width: 100px">REJECTED</a>';
        elseif ($expense['approved_status'] == '1'):
            $html.='<a data-toggle="modal" class="btn green" style="width: 100px" >APPROVED</a>';
            
            if ($expense['rev_status'] === NULL || $expense['rev_status'] === ''):
                $html.='<br /> <br />  <a data-toggle="modal" data-id="'.$i.'" data-expense-id="'.$expense['expense_id'].'" data-expense-amount="'.$expense['amount'].'" class="open-expreversal btn btn-primary" style="width: 150px" href="#expensereversal"> Want Reverse?</a>';
            elseif( $expense['rev_status'] === '0'):
                $html .= '<br><br>
                        <a 
                            data-toggle="modal"
                            data-expense_id="'.$expense['expense_id'].'"
                            data-amount="'.$expense['amount'].'"
                            data-reason="'.$expense['rev_reason'].'"
                            data-paid_type="'.$expense['paid_type'].'"
                            class="open-expreversalapproval btn btn-warning"
                            style="width: 150px"
                            href="#expensereversalapproval">
                            Reversal Requested
                        </a>';
            elseif( $expense['rev_status'] === '2'):
                $html.='<br /> <br />  <a data-toggle="modal" data-id="'.$i.'" data-expense-id="'.$expense['expense_id'].'" data-expense-amount="'.$expense['amount'].'" data-paid-type="'.$expense['paid_type'].'" data-rev-reason="'.$expense['rev_reason'].'" class="open-expreversalapproval btn btn-danger" style="width: 150px" href="#expensereversalapproval" >Reversal Rejected</a>';
            else:
                $html.='';
            endif;
        else:
            $html.='<a data-toggle="modal" class="btn yellow" style="width: 100px">Reversed </a>';
        endif;
        $html.='</td>';
        $html.='<td>';
        if ($expense['paid_type'] == 'bank'){
            $ban = $this->db->join("accounts",'accounts.id = bank_reconciliation_statement.account_id')->get_where('bank_reconciliation_statement',"bank_reconciliation_statement.expense_id = '".$expense['expense_id']."' or bank_reconciliation_statement.salary_expense_ids = '".$expense['expense_id']."'")->row();
            $html.= @$ban->account_title.' '.@$ban->account_name;
        }

        $html.=$expense['paid_type'];
        $html.='</td>';
        $html.='<td>';
        $myAccess = checkUserAccess();
        if(@$myAccess[0]['expense_edit']==1 || $this->session->userdata('role')=='Admin'):
            $html.='<a href="'.site_url("/expenses/edit_expense/".$expense["expense_id"]).'" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>';
        endif;
        if(@$myAccess[0]['expense_delete']==1 || $this->session->userdata('role')=='Admin'):
            if($expense['expense_category_id']!=36)
                $html.='<a onclick="return confirm("Are you sure you want to delete this Expense?")" href="'.site_url("/expenses/delete/".$expense['expense_id']).'" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>';
                else
                $html.='<label onclick="#" title="Delete" class="blue">please Delete From Salary report</label>';
        endif;
        $html.='</td>';
        $html.='</tr>';
        $i++;
        endforeach;
        $html.='</tbody>';
        $html.='</table>';

        echo $html;
    }


    public function all_advertising_expenses()
    {
        $data['from_date'] = date('Y-m-d');
        $data['to_date'] = date('Y-m-d');

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/all_advertising_expenses',$data);
        $this->load->view('inc/footer');
    }

    public function getAllAdvertisingExpenses()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);

        $from_date = $this->input->post('from_date').' 00:00:00';
        $to_date = $this->input->post('to_date').' 23:59:59';

        // if ($this->input->post('setype') === 'Pending')
        // {
        //     $setype='0';
        // }
        // elseif ($this->input->post('setype') === 'Approved')
        // {
        //     $setype='1';
        // }
        // elseif ($this->input->post('setype') === 'Rejected')
        // {
        //     $setype='2';
        // }
        // elseif ($this->input->post('setype') === 'Reversed')
        // {
        //     $setype='3';
        // }
        // else
        // {
        //     $setype = '';
        // }

        $this->db->select('*');
        $this->db->from('advertisement_expenses');
        $this->db->join('campuses', 'campuses.campus_id=advertisement_expenses.campus_id', 'left');
        $this->db->join('users', 'users.user_id=advertisement_expenses.created_by', 'left');
        $this->db->where(array('advertisement_expenses.created_at>='=>$from_date, 'advertisement_expenses.created_at<='=>$to_date));

        // if ($setype !== '')
        // {
        //     $this->db->where('expenses.approved_status', $setype);
        // }
        if ($this->session->userdata('role')!='Admin')
        {
            $this->db->where('advertisement_expenses.created_by', $this->session->userdata('user_id'));
        }
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('advertisement_expenses.campus_id', $campus_ids);
        }
        $expenses = $this->db->get()->result_array();

        $html = '';

        $html.='<table class="table table-striped table-bordered table-hover" id="sample_2">';
        $html.='<thead>';
        $html.='<tr>';
        $html.='<th class="hidden">hidden</th>';
        $html.='<th>Sr no.</th>';
        $html.='<th>Date</th>';
        $html.='<th>Time</th>';
        $html.='<th>Type / Rikshaw No.</th>';
        $html.='<th>Flex No.</th>';
        $html.='<th>Location</th>';
        $html.='<th>Image</th>';
        $html.='<th>Add By</th>';
        $html.='</tr>';
        $html.='</thead>';
        $html.='<tbody>';
        $i=1;
        foreach($expenses as $expense):
        $html.='<tr class="odd gradeX">';
        $html.='<td class="hidden">'.$i.'</td>';
        $html.='<td>'.$i.'</td>';
        $html.='<td>'.date('M d, Y',strtotime($expense['created_at'])).'</td>';
        $html.='<td>'.date('h:i:s A',strtotime($expense['created_at'])).'</td>';
        $html.='<td>'.$expense['vehicle_no'].'</td>';
        $html.='<td>'.$expense['flax_sr_no'].'</td>';
        $html.='<td><a target="_blank" href="https://www.google.com/maps/?q='.$expense['latitude'].','.$expense['longitude'].'" class="btn yellow">Check Location</a></td>';
        if($expense['online_image']==''):
            $html.='<td><a target="_blank" href="'.base_url().'uploads/'.$expense['image'].'" class="btn purple">View Image</a></td>';
        else:
            $html.='<td><a target="_blank" href="'.$expense['online_image'].'" class="btn purple">View Image</a></td>';
        endif;
        $html.='<td>'.$expense['first_name'].' '.$expense['last_name'].'</td>';
        $html.='</tr>';
        $i++;
        endforeach;
        $html.='</tbody>';
        $html.='</table>';

        echo $html;
    }
    
    public function getAllReversalExpenses()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);


        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->where(array('expenses.approved_status'=>'1', 'expenses.rev_status'=>'0'));
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        $expenses = $this->db->get()->result_array();

        //GET TOTAL EXPENSE
        $this->db->select_sum('amount');
        $this->db->from('expenses');
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        
        $this->db->where(array('expenses.approved_status'=>'1', 'expenses.rev_status'=>'0'));
        $total_expense = $this->db->get()->result_array();

        $html = '';

        $html.='<div class="alert alert-success"><p>Total expense Reversal Amount is '.$total_expense[0]['amount'].'</p></div>';

        $html.='<table class="table table-striped table-bordered table-hover">';
        $html.='<thead>';
        $html.='<tr>';
        $html.='<th class="hidden">hidden</th>';
        $html.='<th>Campus</th>';
        $html.='<th>Category</th>';
        $html.='<th>Title</th>';
        $html.='<th>Purpose</th>';
        $html.='<th>Amount</th>';
        $html.='<th>Date</th>';
        $html.='<th>Upload Date</th>';
        $html.='<th>Receipt</th>';
        $html.='<th>Add By</th>';
        $html.='<th>Last Edit</th>';
        $html.='<th>Status</th>';
        $html.='<th>Expense Type</th>';
        $html.='<th>Action</th>';
        $html.='</tr>';
        $html.='</thead>';
        $html.='<tbody>';
        $i=0;
        foreach($expenses as $expense):
        $html.='<tr class="odd gradeX">';
        $html.='<td class="hidden">'.$i.'</td>';
        $html.='<td>'.$expense['campus_name'].'</td>';
        $html.='<td>';
        if($expense['expense_category_id']==9):
            $html.=$expense['name'];
            $html.='<br />';
            $user = $this->db->get_where('users', array('user_id'=>$expense['user_id']))->result_array();
            $html.=@$user[0]['first_name'].' '.@$user[0]['last_name'];
        elseif($expense['expense_category_id']==36):
            $payrolls = $this->db->join('users','users.user_id = payroll.user_id')->get_where('payroll', array('expense_id'=>$expense['expense_id']))->result_array();
            foreach($payrolls as $payroll){
                $html.= $payroll['first_name'].' '.$payroll['last_name'].' ( '.$payroll['earned_salary'].' )'.'<br>';
            }
        else:
            $html.=@$expense['name'];
        endif;
        $html.='</td>';
        $html.='<td>';
        $html.=$expense['title'];
        if($expense['expense_category_id']==1):
            $html.='<br />';
            $html.='Rickshaw Number : '.$expense['rickshaw_number'];
            $html.='<br />';
            $html.='Rickshaw Driver No : '.$expense['driver_phone'];
        endif;
        if($expense['expense_category_id']==13 && $expense['student_id']!=NULL):
            $student_data = $this->db->get_where('students',array('student_id'=>$expense['student_id']))->result_array();
            $html.='Name : '.$student_data[0]['first_name'].' '.$student_data[0]['last_name'].' ('.$student_data[0]['cnic'].')';
            $html.='<br />';
            $html.='Class : '.$expense['class'].' Year';
            $html.='<br />';
            $html.='Exam Number : '.$expense['council_exam_no'];
        endif;
        $html.='</td>';
        $html.='<td>'.$expense['purpose'].'</td>';
        $html.='<td>'.$expense['amount'].'</td>';
        $html.='<td>'.$expense['date'].'</td>';
        $html.='<td>'.$expense['actual_date'].'</td>';
        $html.='<td>';
        //if($expense['image']!='' && $expense['online_image']==''):
            if($expense['online_image']=='' && $expense['image']!=''):
                $html.='<a class="btn btn-default" href="'.base_url().'uploads/'.$expense['image'].'" target="_blank"><i class="fa fa-image"></i> Image</a>';
            elseif($expense['online_image']!='' && $expense['image']!=''):
                $bucket_address= 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
                $cloudfront_address= 'https://d10iw6eujrfvyr.cloudfront.net';
                $html.='<a class="btn btn-default" href="'.str_replace($bucket_address,$cloudfront_address,$expense['online_image']).'" target="_blank"><i class="fa fa-image"></i> Image</a>';
            endif;
        //endif;
        $html.='</td>';
        $html.='<td>'.$expense['add_by'].'</td>';
        $html.='<td>'.$expense['last_edit'].'</td>';
        $html.='<td>';
        if ($expense['approved_status'] == '0'):
            $html.='<a data-toggle="modal" data-id="'.$i.'" class="btn btn-primary" style="width: 100px">PENDING</a>';
        elseif($expense['approved_status'] == '2'):
            $html.='<a data-toggle="modal" class="btn red"  style="width: 100px">REJECTED</a>';
        elseif ($expense['approved_status'] == '1'):
            $html.='<a data-toggle="modal" class="btn green" style="width: 100px" >APPROVED</a>';
            
            if ($expense['rev_status'] === NULL || $expense['rev_status'] === ''):
                $html.='<br /> <br />  <a data-toggle="modal" data-id="'.$i.'" data-expense-id="'.$expense['expense_id'].'" data-expense-amount="'.$expense['amount'].'" class="open-expreversal btn btn-primary" style="width: 150px" href="#expensereversal"> Want Reverse?</a>';
            elseif( $expense['rev_status'] === '0'):
                $html .= '<br><br>
                        <a 
                            data-toggle="modal"
                            data-expense_id="'.$expense['expense_id'].'"
                            data-amount="'.$expense['amount'].'"
                            data-reason="'.$expense['rev_reason'].'"
                            data-paid_type="'.$expense['paid_type'].'"
                            class="open-expreversalapproval btn btn-warning"
                            style="width: 150px"
                            href="#expensereversalapproval">
                            Reversal Requested
                        </a>';
            elseif( $expense['rev_status'] === '2'):
                $html.='<br /> <br />  <a data-toggle="modal" data-id="'.$i.'" data-expense-id="'.$expense['expense_id'].'" data-expense-amount="'.$expense['amount'].'" data-paid-type="'.$expense['paid_type'].'" data-rev-reason="'.$expense['rev_reason'].'" class="open-expreversalapproval btn btn-danger" style="width: 150px" href="#expensereversalapproval" >Reversal Rejected</a>';
            else:
                $html.='';
            endif;
        else:
            $html.='<a data-toggle="modal" class="btn yellow" style="width: 100px">Reversed </a>';
        endif;
        $html.='</td>';
        $html.='<td>';
        if ($expense['paid_type'] == 'bank'){
            $ban = $this->db->join("accounts",'accounts.id = bank_reconciliation_statement.account_id')->get_where('bank_reconciliation_statement',"bank_reconciliation_statement.expense_id = '".$expense['expense_id']."' or bank_reconciliation_statement.salary_expense_ids = '".$expense['expense_id']."'")->row();
            $html.= @$ban->account_title.' '.@$ban->account_name;
        }

        $html.=$expense['paid_type'];
        $html.='</td>';
        $html.='<td>';
        $myAccess = checkUserAccess();
        if(@$myAccess[0]['expense_edit']==1 || $this->session->userdata('role')=='Admin'):
            $html.='<a href="'.site_url("/expenses/edit_expense/".$expense["expense_id"]).'" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>';
        endif;
        if(@$myAccess[0]['expense_delete']==1 || $this->session->userdata('role')=='Admin'):
            if($expense['expense_category_id']!=36)
                $html.='<a onclick="return confirm("Are you sure you want to delete this Expense?")" href="'.site_url("/expenses/delete/".$expense['expense_id']).'" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>';
                else
                $html.='<label onclick="#" title="Delete" class="blue">please Delete From Salary report</label>';
        endif;
        $html.='</td>';
        $html.='</tr>';
        $i++;
        endforeach;
        $html.='</tbody>';
        $html.='</table>';

        echo $html;
    }
    
}
