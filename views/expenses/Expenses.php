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

       // \Tinify\setKey("78Hv7L6t1mZpLbPcHfbzYrG4VR1nYkzm"); //pass your actual API key
        \Tinify\setKey("jcsTy5mwDNZypg4wZnvZQ7THGKVq6gXN"); //pass your actual API key

        $supported_image = array('image/gif', 'image/jpg', 'image/jpeg', 'image/png');

        if (in_array($_FILES['image']['type'], $supported_image)) {

            $src_file_name = $_FILES['image']['name'];

            if (!file_exists(getcwd().'/uploads')) {

                mkdir(getcwd().'/uploads', 0777);
            }

            move_uploaded_file($_FILES['image']['tmp_name'], getcwd().'/uploads/'.$src_file_name);

            //optimize image using TinyPNG
            $source = \Tinify\fromFile(getcwd().'/uploads/'.$src_file_name);
            $source->toFile(getcwd().'/uploads/'.$src_file_name);
            $image=$src_file_name;
            $data = $this->input->post();

        } else {
           $data = $this->input->post();
            $image = '';
        }



		$this->expense->storeExpense($data, $image);

        $exp=$this->db->get_where('expenses','expense_id = "'.$this->input->post('expense_id').'"')->result_array();

        $this->db->set('remaining_amount', 'remaining_amount -'.$this->input->post('amount') .'',false);
        $this->db->where('assign_to', $this->session->userdata('user_id'));
        $this->db->update('petty_cash_college_wise');

		
		$campus = $this->db->get_where('campuses', array('campus_id'=>$data['campus_id']))->row()->campus_name;
		$category = $this->db->get_where('expense_category', array('expense_category_id'=>$data['expense_category_id']))->row()->name;
		
		
		$message = 'Expense Add Alert
		Campus : '.$campus.' 
		Category : '.$category.'
		Title : '.$data['title'].'
		Date : '.$data['date'].'
		Amount : '.$data['amount'].'
		Add By : '.$data['add_by'].'
		';
		
		$this->campusSMS($campus, $message);
		
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
		
		$this->db->select('*');
        $this->db->from('campuses');
		if($this->session->userdata('role') != 'Admin' && $this->session->userdata('role') != 'Accounts' )
		{ 
			
			$this->db->where('campus_id', $this->session->userdata('user_campus_id'));
			
		}
		
		$data['campuses'] = $this->db->get()->result_array();
		
		$this->db->select('*');
        $this->db->from('campuses');
	
		
		$data['allcampuses'] = $this->db->get()->result_array();
		
		
		
		$data['categories'] = $this->expense->getCategories();

        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->where('assign_to', $this->session->userdata('user_id'));
        $query = $this->db->get()->result_array();

		if(count($query)>0){
			
			$data['pettycash']=$query[0]['remaining_amount'];

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
			
		}else{
		
		

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
			$from_date = $this->input->post('from_date');
			$to_date = $this->input->post('to_date');
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
		$data['categories'] = $this->expense->getCategories();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('expenses/category', $data);
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
		$data['categories'] = $this->expense->getCategories();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('expenses/edit_category', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_category($expense_category_id)
	{
		$this->db->set('name', $this->input->post('name'));
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
            ,'council_exam_no'=>@$exam_no))->result_array();
			
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
		$this->db->join('students','students.student_id=payments.custom_student_id','inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->where('campuses.campus_id',$campus_id);
		$this->db->like('payment_comment', 'This fee for next exam # '.$council_exam_no.' '.$class.' Year', 'both');
		$results = $this->db->get()->result_array();
		
		$html='';
		$i=1;
		foreach($results as $result)
		{
			$this->db->select('*');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->where('students.student_id', $result['custom_student_id']);
			$student_data = $this->db->get()->result_array();
			if($result['paid']==1)
			{
				$paid='Submitted';
			}
			else
			{
				$paid='Not Submitted';
			}
			$checkAddedFee = $this->db->get_where('expenses',array('student_id'=>@$student_data[0]['student_id'],'council_exam_no'=>$council_exam_no,'class'=>$this->input->post('campus_class')))->result_array();
			
			if(count($checkAddedFee)>0)
			{
				$html.='<tr class="alert-success">';
			}
			else
			{
				$html.='<tr>';
			}
			$html.='<td>'.@$i.'</td>';
			$html.='<td>'.@$student_data[0]['name'].'</td>';
			$html.='<td>'.@$student_data[0]['first_name'].' '.@$student_data[0]['last_name'].'</td>';
			$html.='<td>'.@$this->db->get_where('contractors', array('contractor_id'=>$student_data[0]['contractor_id']))->row()->name.'</td>';
			$html.='<td>'.@$student_data[0]['cnic'].'</td>';
			$html.='<td>'.@$student_data[0]['roll_no'].'</td>';
			$html.='<td>'.$result['payment_comment'].'</td>';
			$html.='<td>'.$result['amount'].'</td>';
			$html.='<td>'.$result['add_by'].'</td>';
			$html.='<td>'.$paid.'</td>';
			if(count($checkAddedFee)>0)
			{
				$html.='<td><a target="_blank" href="'.base_url().'uploads/'.$checkAddedFee[0]['image'].'" class="btn green"><i class="fa fa-image"></i></a></td>';
			}
			else
			{
				$html.='<td><label class="checkbox-inline"><input type="checkbox" class="student_id" name="student_id" value="'.@$student_data[0]['student_id'].'" /> Submit Council Fee</label></td>';
			}
			$html.='</tr>';
			$i++;
		}
		echo $html;
	}

    public function change_approve_status()
    {

        $this->db->set('approved_status', $this->input->post('status'));
        $this->db->set('last_edit', $this->input->post('last_edit'));
        $this->db->where('expense_id', $this->input->post('expense_id'));
        $this->db->update('expenses');

        if($this->input->post('status')=='2'){

            $exp=$this->db->get_where('expenses','expense_id = "'.$this->input->post('expense_id').'"')->result_array();

            $this->db->set('remaining_amount', 'remaining_amount +'. $exp[0]['amount'] .'',false);
            $this->db->where('assign_to', $exp[0]['add_by_id']);
            $this->db->update('petty_cash_college_wise');
        }

        redirect('expenses/all_expenses/'.$this->input->post('from_date').'/'.$this->input->post('to_date').'/'.$this->input->post('setype'));
		



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
        $this->db->where('last_edit', $this->input->post('last_edit'));
        $this->db->update('expenses');


        $this->all_expenses();

    }
		public function all_expenses_report()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $data['campuses'] = $this->db->where_in( array('campus_id'=>$campus_ids))->get('campuses')->result_array();
        $data['categories'] = $this->db->get('expense_category')->result_array();

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

        if($this->input->post('status')=='1')
        {

            $this->db->set('rev_status', '1');
            $this->db->set('approved_status', '3');
            $this->db->where('expense_id', $this->input->post('expense_id'));
            $this->db->where('last_edit', $this->input->post('last_edit'));
            $this->db->update('expenses');

            $exp=$this->db->get_where('expenses','expense_id = "'.$this->input->post('expense_id').'"')->result_array();
            $this->db->set('remaining_amount', 'remaining_amount +'. $exp[0]['amount'] .'',false);
            $this->db->where('assign_to', $exp[0]['add_by_id']);
            $this->db->update('petty_cash_college_wise');
        }
        elseif ($this->input->post('status')=='2')
        {

            $this->db->set('rev_status', '2');
            $this->db->where('expense_id', $this->input->post('expense_id'));
            $this->db->where('last_edit', $this->input->post('last_edit'));
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

}
