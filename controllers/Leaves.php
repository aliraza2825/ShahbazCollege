<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leaves extends CI_Controller {

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

	public function index()
	{
		
	}
	
	public function define_leave()
	{

        $this->db->select('*');
        $this->db->from('tblleavetype');
		$data['leaves'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('leaves/define_leave', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_leave()
	{
		$this->db->set('leavetype', $this->input->post('leavetype'));
		$this->db->set('no_of_leaves', $this->input->post('no_of_leaves'));
		$this->db->set('is_half_allowed', $this->input->post('is_half_allowed'));
		$this->db->set('description', $this->input->post('description'));
		$this->db->insert('tblleavetype');
		
		$this->session->set_flashdata('message', 'Interview Added Successfully.');
		redirect('leaves/define_leave');
	}

    public function update_leave()
    {
        $this->db->set('leavetype', $this->input->post('leavetype'));
        $this->db->set('no_of_leaves', $this->input->post('no_of_leaves'));
        $this->db->set('is_half_allowed', $this->input->post('hallowed'));
        $this->db->set('description', $this->input->post('description'));
        $this->db->where("(tblleavetype.id = '".$this->input->post('leave_id')."')",NULL,FALSE);
        $this->db->update('tblleavetype');

        $this->session->set_flashdata('message', 'Interview Added Successfully.');
        redirect('leaves/define_leave');
    }

    public function assign_leaves()
    {
        $data['staff_types'] = $this->db->get('staff_type')->result_array();
        $data['departments'] = $this->db->get('departments')->result_array();
        $data['leaves'] = $this->db->get('tblleavetype')->result_array();
        $data['users'] = $this->db->get_where('users',array('type'=>'regular'))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('leaves/assign_leaves', $data);
        $this->load->view('inc/footer');
    }

    public function insert_leaves()
    {

        $stafftype=$this->input->post('staff_type_id');
        $delptid=$this->input->post('department_id');
        $user_id=$this->input->post('user_id');
        $Role=$this->input->post('role');
        $leave_type=$this->input->post('leave_type');


            if (!$user_id == "") {


                $found = $this->db->get_where('tblassigned_leaves',array
                    ('user_id'=>$user_id,
                        'leave_type_id' => $leave_type
                    ,'allow_for_year' => date("Y"))
                )->result_array();
                if (sizeof($found>0)){

                    $this->session->set_flashdata('error', 'Leaves Already Assigned to this user.');
                    redirect('leaves/assign_leaves');

                }else {


                    $this->db->select('*');
                    $this->db->from('users');
                    $this->db->where('type', 'regular');
                    $this->db->where('user_id', $user_id);

                }


            } else {


                $this->db->select('*');
                $this->db->from('users');
                $this->db->where('type', 'regular');

                if (!$stafftype == "") {



                    $this->db->where('staff_type_id', $stafftype);


                }
                if (!$delptid == "") {


                    $this->db->where('department_id', $delptid);


                }
                if (!$Role == "") {


                    $this->db->where('Role', $Role);


                }


            }

            $users = $this->db->get()->result_array();

            $noofleaves = $this->db->get_where('tblleavetype', array('id' => $leave_type))->result_array();

            foreach ($users as $user) {

                $this->load->helper('date');

                $yearEnd = date('Y-m-d h:i:s', strtotime('12/31'));

                // Declare timestamps
                $last = new DateTime($yearEnd);
                $now = new DateTime(date('Y-m-d h:i:s', time()));

                // Find difference
                $interval = $last->diff($now);


                $months = (int)$interval->format('%m');


                $calculatedleaves = ($noofleaves[0]['no_of_leaves'] / 12) * $months;


                echo $calculatedleaves;

                $this->db->set('leave_type_id', $leave_type);
                $this->db->set('user_id', $user['user_id']);
                $this->db->set('allow_for_year', date("Y"));
                $this->db->set('no_of_leaves', $calculatedleaves);
                $this->db->set('remaining_leaves', $calculatedleaves);
                $this->db->set('to_date', date("y") . '-12-31');
                $this->db->set('created_by', $this->session->userdata('user_id'));
                $this->db->insert('tblassigned_leaves');


            }

            $this->session->set_flashdata('message', 'Leaves Assigned Successfully.');
            redirect('leaves/assign_leaves');



    }

    public function apply_leave()
    {

        $this->db->select('tblleavetype.leavetype as leavetypename,tblleaves.*');
        $this->db->from('tblleaves');
        $this->db->join('tblleavetype','tblleavetype.id = tblleaves.leavetype','left');
        $data['leaves'] = $this->db->where("(tblleaves.empid=". $this->session->userdata('user_id').")")->get()->result_array();


        $this->db->select('tblleavetype.leavetype as leavetypename,tblleavetype.is_half_allowed as ishalf,tblassigned_leaves.*');
        $this->db->from('tblassigned_leaves');
        $this->db->join('tblleavetype','tblleavetype.id = tblassigned_leaves.leave_type_id','left');
        $this->db->where("(tblassigned_leaves.user_id=". $this->session->userdata('user_id')." )");
        $data['leavestype'] = $this->db->get()->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('leaves/apply_leave', $data);
        $this->load->view('inc/footer');


    }

    public function insert_apply_leave()
    {

        $user_id=$this->session->userdata('user_id');
        $leave_type=$this->input->post('in_leave_id');
        $yeehalf="";
        $leave_assign_id=$this->input->post('leave_assign_id');
        $day_type=$this->input->post('day_type');
        $description=$this->input->post('description');

        $from=$this->input->post('from_date');
        $to=$this->input->post('to_date');


        $now = strtotime($from); // or your date as well
        $your_date = strtotime($to);
        $datediff =  $your_date-$now;

        $val = round($datediff / (60 * 60 * 24))+1;

        if ($day_type=='1'){

            $yeehalf="0";

        }else {
            $val = $day_type * $val;
            $yeehalf="1";

        }


        $this->db->set('leavetype',$leave_type);
        $this->db->set('leave_assign_id',$leave_assign_id);
        $this->db->set('leaves_value',$val);
        $this->db->set('half_taken',$yeehalf);
        $this->db->set('todate',$to);
        $this->db->set('fromdate',$from);
        $this->db->set('description',$description);
        $this->db->set('empid',$user_id);


        $ins = $this->db->insert('tblleaves');
        if ($ins){

            $this->apply_leave();

        }else{

            $this->session->set_flashdata('message', 'error occured.');
            redirect('leaves/apply_leave');

        }




    }

    public function insert_edit_apply_leave()
    {

        $user_id=$this->session->userdata('user_id');
        $leave_type=$this->input->post('upin_leave_id');
        $leave_id=$this->input->post('leave_id');
        $leave_assign_id=$this->input->post('upleave_assign_id');
        $day_type=$this->input->post('upday_type');
        $description=$this->input->post('updescription');




        $from=$this->input->post('upfrom_date');
        $to=$this->input->post('upto_date');


        $now = strtotime($from); // or your date as well
        $your_date = strtotime($to);
        $datediff =  $your_date-$now;


        $val = (round($datediff / (60 * 60 * 24)))+1;



        if ($day_type=='0.5'){

            $val = $day_type * $val;
            $yeehalf="1";





        }else {

            $yeehalf="0";

        }


        $this->db->set('leavetype',$leave_type);
        $this->db->set('leave_assign_id',$leave_assign_id);
        $this->db->set('leaves_value',$val);
        $this->db->set('half_taken',$yeehalf);
        $this->db->set('todate',$to);
        $this->db->set('fromdate',$from);
        $this->db->set('description',$description);
        $this->db->set('empid',$user_id);
        $this->db->where('id',$leave_id);


        $ins = $this->db->update('tblleaves');
        if ($ins){

            $this->apply_leave();

        }else{

            $this->session->set_flashdata('message', 'error occured');
            redirect('leaves/apply_leave');

        }


    }

    public function leave_list()
    {

        $this->db->select('users.*,tblleavetype.leavetype as leavetypename,tblleaves.*,tblassigned_leaves.remaining_leaves as remaining');
        $this->db->from('tblleaves');
        $this->db->join('tblleavetype','tblleavetype.id = tblleaves.leavetype','left');
        $this->db->join('users','tblleaves.empid = users.user_id','left');
        $this->db->join('tblassigned_leaves','tblassigned_leaves.user_id= users.user_id and tblassigned_leaves.leave_type_id = tblleavetype.id','left');
        $this->db->order_by("tblleaves.status", "asc");
        $data['leaves'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('leaves/leaves_approvel', $data);
        $this->load->view('inc/footer');

    }

    public function update_employee_leave()
    {

        $leave_id=$this->input->post('status');

        $this->db->set('status', $this->input->post('status'));
        $this->db->set('updated_by', $this->session->userdata('user_id'));
        $this->db->where("(tblleaves.id ='".$this->input->post('leave_id')."')");
        $this->db->update('tblleaves');

        $this->session->set_flashdata('message', 'Successfully updated Leave Status');
        redirect('leaves/leave_list');

    }

}
