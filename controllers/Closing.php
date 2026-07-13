<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Closing extends CI_Controller {

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
        $this->load->model('account');
        $this->ensure_closing_constraints();
    }

    private function ensure_closing_constraints()
    {
        if (!$this->db->table_exists('closing_perday')) {
            return;
        }

        $this->ensure_products_closing_id_column();

        $indexes = $this->db->query("SHOW INDEX FROM closing_perday WHERE Key_name = 'uniq_campus_closing_date'")->result_array();
        if (!empty($indexes)) {
            return;
        }

        $duplicate = $this->db->query(
            "SELECT campus_id FROM closing_perday
             GROUP BY campus_id, for_day, for_month, for_year
             HAVING COUNT(*) > 1
             LIMIT 1"
        )->row_array();

        if (empty($duplicate)) {
            $this->db->query(
                "ALTER TABLE closing_perday
                 ADD UNIQUE KEY uniq_campus_closing_date (campus_id, for_day, for_month, for_year)"
            );
        }
    }

    /** Bookstore POS rows need closing_id so post-close sales roll to the next day */
    private function ensure_products_closing_id_column()
    {
        if (!$this->db->table_exists('products')) {
            return;
        }
        if ($this->db->field_exists('closing_id', 'products')) {
            return;
        }
        $this->db->query("ALTER TABLE `products` ADD `closing_id` VARCHAR(50) NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `products` ADD INDEX `idx_products_closing_id` (`closing_id`)");
    }

    private function apply_pos_unclosed_where()
    {
        $this->db->group_start();
        $this->db->where('products.closing_id IS NULL', null, false);
        $this->db->or_where('products.closing_id', '');
        $this->db->or_where('products.closing_id', '0');
        $this->db->group_end();
    }

    private function get_pos_sales_rows($campus, $sold_date, $only_unclosed = true)
    {
        $this->db->select('products.*, product_names.product_name, CONCAT(users.first_name, " ", users.last_name) as sold_by_name', false);
        $this->db->from('products');
        $this->db->join('product_names', 'product_names.product_name_id = products.product_name_id', 'left');
        $this->db->join('users', 'users.user_id = products.sold_by', 'left');
        $this->db->where('products.sold', 1);
        $this->db->where('products.campus_id', $campus);
        $this->db->where('products.sold_date', $sold_date);
        if ($only_unclosed) {
            $this->apply_pos_unclosed_where();
        }
        $this->db->order_by('products.invoice_no', 'DESC');
        return $this->db->get()->result_array();
    }

    private function get_pos_sales_sum($campus, $sold_date, $only_unclosed = true)
    {
        $this->db->select('sum(products.sold_amount) as total');
        $this->db->from('products');
        $this->db->where('products.sold', 1);
        $this->db->where('products.campus_id', $campus);
        $this->db->where('products.sold_date', $sold_date);
        if ($only_unclosed) {
            $this->apply_pos_unclosed_where();
        }
        $row = $this->db->get()->result_array();
        return (float)@$row[0]['total'];
    }

    private function get_pos_sales_by_closing($campus, $campus_closing_id)
    {
        $this->db->select('products.*, product_names.product_name, CONCAT(users.first_name, " ", users.last_name) as sold_by_name', false);
        $this->db->from('products');
        $this->db->join('product_names', 'product_names.product_name_id = products.product_name_id', 'left');
        $this->db->join('users', 'users.user_id = products.sold_by', 'left');
        $this->db->where('products.sold', 1);
        $this->db->where('products.campus_id', $campus);
        $this->db->where('products.closing_id', $campus_closing_id);
        $this->db->order_by('products.invoice_no', 'DESC');
        return $this->db->get()->result_array();
    }

    private function get_pos_sales_sum_by_closing($campus, $campus_closing_id)
    {
        $this->db->select('sum(products.sold_amount) as total');
        $this->db->from('products');
        $this->db->where('products.sold', 1);
        $this->db->where('products.campus_id', $campus);
        $this->db->where('products.closing_id', $campus_closing_id);
        $row = $this->db->get()->result_array();
        return (float)@$row[0]['total'];
    }

    private function get_closing_for_date($campus_id, $day, $month, $year, $lock = false)
    {
        $sql = "SELECT * FROM closing_perday
                WHERE campus_id = ?
                AND for_day = ?
                AND for_month = ?
                AND for_year = ?";

        if ($lock) {
            $sql .= " FOR UPDATE";
        }

        return $this->db->query($sql, array($campus_id, $day, $month, $year))->row_array();
    }

    public function index()
    {
        $data['campuses'] = $this->account->getCampuses();

        if ($this->input->post('to_date') == '')
            $today = date('Y-m-d');
        else
            $today=$this->input->post('to_date');

        $access = checkUserAccess();
        $acc = $access[0]['view_campus_closings'];
        $cam_closings = $access[0]['campus_closing_ids'];

        $this->db->select('*,closing_persons.campus_id as campus_id');
        $this->db->from('closing_persons');
        $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id','left');
        $this->db->join('users','users.user_id = closing_persons.user_id','left');

        if(@$this->session->userdata('role') != 'Admin'){
            if ($acc == "1")
                $this->db->where_in('closing_persons.id',explode(",",$cam_closings));
            else
                $this->db->where('closing_persons.user_id = "'.$this->session->userdata('user_id').'" and active_status = 1');
        }
        else {
            $this->db->where('closing_persons.active_status = "1"');
        }

        $dataclose = $this->db->get()->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
                (select for_day from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		        (select for_month from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		        MAX(for_year) as year from closing_perday 
		        left join campuses on campuses.campus_id = closing_perday.campus_id 
		        where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
		        GROUP by closing_perday.campus_id';
        $data['campusclosings'] = $this->db->query($sq)->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
		(select for_day   from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		(select for_month from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		MAX(for_year) as year from closing_perday 
		left join campuses on campuses.campus_id = closing_perday.campus_id
		where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
        GROUP by closing_perday.campus_id';
        $data['campusclosingverified'] = $this->db->query($sq)->result_array();

        if(@$this->session->userdata('role') != 'Admin') {
            if ($acc == "1") {
                foreach ($data['campusclosings'] as $key => $cam) {
                    if (in_array($cam['campus_id'],explode(",",$cam_closings))) {
                        unset($data['campusclosings'][$key]);
                    }
                }
            }
            else {
                foreach ($data['campusclosings'] as $key => $cam) {
                    if ($cam['campus_id'] != $this->session->userdata('user_campus_id')) {
                        unset($data['campusclosings'][$key]);
                    }
                }
            }
        }

        $data['campusclosings'] = array_values($data['campusclosings']);

        foreach ($dataclose as $key=>$closing) {
            $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."'and for_year = '".date('Y', strtotime($today))."'";
            $closed = $this->db->query($sq)->result_array();

            if (count($closed)>0) {
//                $this->db->select('*');
//                $this->db->from('payments');
//                $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'"');
//                $query = $this->db->get()->result_array();
//
//                $value = array_sum(array_column($query,'actual_amount'));

                $dataclose[$key]['closing_amount'] = $closed[0]['closed_amount'];
                $dataclose[$key]['closed_status'] = '1';
                $dataclose[$key]['closing_id'] = $closed[0]['id'];
                $dataclose[$key]['transaction_no'] = $closed[0]['transaction_no'];
                $dataclose[$key]['close_type'] = $closed[0]['close_type'];
                $dataclose[$key]['closed_by'] = $closed[0]['closed_by'];
                $dataclose[$key]['checked_by'] = $closed[0]['checked_by'];
                $dataclose[$key]['account_id'] = $closed[0]['account_id'];
                $dataclose[$key]['partialy_closed_image'] = $closed[0]['partialy_closed_image'];
            }
            else {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                $query = $this->db->get()->result_array();

                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan is null');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->where('payments.paid = 1');
                $query2 = $this->db->get()->result_array();

                $yesterday = date('Y-m-d', strtotime($today. ' - 1 days'));
                $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($yesterday))."' and for_day = '".date('d', strtotime($yesterday))."'and for_year = '".date('Y', strtotime($yesterday))."'";
                $yest_closed = $this->db->query($sq)->result_array();

                if (count($yest_closed)>0)  {

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','left');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                    $query3 = $this->db->get()->result_array();

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','left');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan is null');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->where('payments.paid = 1');
                    $query4 = $this->db->get()->result_array();
                    $final = array_merge($query3, $query4,$query, $query2);
                }
                else {
                    $final = array_merge($query, $query2);
                }
                $value = array_sum(array_column($final,'actual_amount'));

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$today 00:00:00' and asset_sales.sold_date <= '$today 23:59:59' and products.campus_id = '".$closing['campus_id']."'");
                $asset_sales_sum_today = $this->db->get()->result_array();

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id = '".$closing['campus_id']."' and closing_id IS NULL");
                $asset_sales_sum_yesterday = $this->db->get()->result_array();

                $asset_sale_amount = $asset_sales_sum_today[0]['total'] + $asset_sales_sum_yesterday[0]['total'];

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$today 00:00:00' and sales.sale_time <= '$today 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum = $this->db->get()->result_array();

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum_yesterday = $this->db->get()->result_array();

                $sale_amount = $sales_sum[0]['total'] + $sales_sum_yesterday[0]['total'];

                $this->db->select('sum(amount_paid) as total');
                $this->db->from('loan_plan');
                $this->db->where("paid_date = '$today' and campus_id = '".$closing['campus_id']."' and paid_at = 'cash'");
                $loan_today = $this->db->get()->result_array();
                $loan_today = $loan_today[0]['total'];
                if (count($yest_closed)>0) {
                    $this->db->select('sum(amount_paid) as total');
                    $this->db->from('loan_plan');
                    $this->db->where("paid_date = '$yesterday' and campus_id = '" . $closing['campus_id'] . "' and paid_at = 'cash' and closing_id IS NULL");
                    $loan_yesterday = $this->db->get()->result_array();
                    $loan_today += $loan_yesterday[0]['total'];
                }

                // Bookstore POS (products.sold) — unclosed only; orphans from closed yesterday roll forward
                $pos_sale_amount = $this->get_pos_sales_sum($closing['campus_id'], $today, true);
                if (count($yest_closed) > 0) {
                    $pos_sale_amount += $this->get_pos_sales_sum($closing['campus_id'], $yesterday, true);
                }

                $dataclose[$key]['closing_amount'] = $value+$sale_amount+$asset_sale_amount+$loan_today+$pos_sale_amount;
                $dataclose[$key]['closed_status'] = '0';
                $dataclose[$key]['closing_id'] = '';
                $dataclose[$key]['close_type'] = '0';
                $dataclose[$key]['transaction_no'] = '';
                $dataclose[$key]['closed_by'] = '';
                $dataclose[$key]['checked_by'] = '';
                $dataclose[$key]['account_id'] = '';
                $dataclose[$key]['partialy_closed_image'] = @$closed[0]['partialy_closed_image'];
            }
        }

        $data['closings']=$dataclose;
        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where('type = "1"');
        $data['accounts'] = $this->db->get()->result_array();

        $data['selected_date'] = $today;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('closings/closingsheet', $data);
        $this->load->view('inc/footer');
    }

    public function edit_closing_person()
    {

        $campus_id = $this->input->post('campus_id');
        $user = $this->input->post('closing_id');
        $active_status = $this->input->post('active_status');
        $created_by = $this->session->userdata('name');

        $this->db->set('campus_id',$campus_id);
        $this->db->set('active_status',$active_status);
        $this->db->set('created_at',date('Y-m-d H:i:s'));
        $this->db->set('created_by',$created_by);
        $this->db->where('id',$user);
        $this->db->update('closing_persons');

        $this->session->set_flashdata('message', 'Closing Person Updated successfully');

        redirect(site_url().'/closing/closing_person');
    }

    public function dailyclosingview($closing_id)
    {
        $check_record = $this->db->get_where('closing_perday', array('id'=>$closing_id))->result_array();
        $today='2021-'.$check_record[0]['for_month'].'-'.$check_record[0]['for_day'];


        $qry = "SELECT campus_name,campuses.campus_id, 
		
		
		( select count(*) as total_entries from payments where submitted_fee_campus_id =  campuses.campus_id and paid = 1 and `actual_paid_date` = '".$today."') total_entries, 
		( select SUM(amount+fine_amount) total_paid from payments where submitted_fee_campus_id =  campuses.campus_id and paid = 1 and `actual_paid_date` = '".$today."') total_paid,
        ( select SUM(amount+fine_amount) as college_paid from payments where `fee_pay_through` = 'college' and submitted_fee_campus_id =  campuses.campus_id and paid = 1 and `actual_paid_date` = '".$today."') college_paid, 
        ( select SUM(amount+fine_amount) as bank_paid from payments where `fee_pay_through` = 'bank' and submitted_fee_campus_id =  campuses.campus_id and paid = 1 and `actual_paid_date` = '".$today."') bank_paid,
        ( select SUM(amount) from expenses where campus_id = campuses.campus_id and date = '".$today."' and approved_status = '1') as expensetoday ,
		( select (remaining_exp_amount) as required from closing_perday where campus_id = campuses.campus_id and for_month = '".date('m', strtotime($today))."' 
		  and for_day = '".(date('d', strtotime($today))-1 )."') as expensereceivable ,
        ( select count(*) from closing_perday where campus_id = campuses.campus_id and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."') as closed_status,
		( select closed_by from closing_perday where campus_id = campuses.campus_id and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."') as closed_by,
		( select campus_closing_id from closing_perday where campus_id = campuses.campus_id and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."') as closing_id,
        ( select concat(first_name,last_name) as name from petty_cash_college_wise Inner join users on users.user_id = petty_cash_college_wise.assign_to where petty_cash_college_wise.campus_id = campuses.campus_id LIMIT 1 )  as paid_by,
        ( select sum(amount_given) as reverse_amount from petty_cash_history where campus_id = campuses.campus_id and debit_credit = 'C' and DATE(created_at) <= '".$today."' and status != '1' )  as reverse_cash,
        ( select reverse_cash from closing_perday where campus_id = campuses.campus_id and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."' )  as reversed_cash
        
		FROM `campuses` ";



        $dataclose = $this->db->query($qry)->result_array();
        $data['closings']=array();


        for($i = 0; $i<(count($dataclose)-1); $i++)
        {

            if($dataclose[$i]['campus_id'] == $check_record[0]['campus_id']){

                if($dataclose[$i]['paid_by'] == ''){

                }
                else
                {

                    if($dataclose[$i]['college_paid'] == '')
                    {
                        $dataclose[$i]['college_paid'] = '0';
                    }
                    if($dataclose[$i]['bank_paid'] == '')
                    {
                        $dataclose[$i]['bank_paid'] = '0';
                    }
                    if($dataclose[$i]['total_paid'] == '')
                    {
                        $dataclose[$i]['total_paid'] = '0';
                    }
                    if($dataclose[$i]['expensetoday'] == '')
                    {
                        $dataclose[$i]['expensetoday'] = '0';
                    }
                    array_push($data['closings'],$dataclose[$i]);

                }


            }

        }

        $sq = 'select campus_name,(select for_day from closing_perday where created_at = (SELECT MAX(created_at) FROM closing_perday where campus_id = campuses.campus_id)) as day,MAX(for_month) as month from closing_perday left join campuses on campuses.campus_id = closing_perday.campus_id GROUP by closing_perday.campus_id';
        $data['campusclosings'] = $this->db->query($sq)->result_array();


        $data['selected_date'] = $today;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('closings/closingsheet', $data);
        $this->load->view('inc/footer');
    }

    public function advance()
    {
        $data['teachers'] = $this->account->getUsers();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/all_users', $data);
        $this->load->view('inc/footer');
    }

    public function viewclosing($date,$campus,$total_amount,$status){

        $sq = "select * from closing_perday where campus_id = '".$campus."' and for_month = '".date('m', strtotime($date))."' and for_day = '".date('d', strtotime($date))."'and for_year = '".date('Y', strtotime($date))."'";
        $closed = $this->db->query($sq)->result_array();
        $data['closed'] =$closed;

        if (count($closed)>0) {
            $this->db->select('*');
            $this->db->from('payments');
            $this->db->join('students','students.student_id = payments.student_id','left');
            $this->db->join('contracts','contracts.contract_id = payments.contract_id','left');
            $this->db->join('courses','courses.course_id=students.course_id','left');
            $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'"');
            $final = $this->db->get()->result_array();

            $this->db->select('*,asset_sales.created_at as sale_date');
            $this->db->from('asset_sales');
            $this->db->join('products','products.sale_id=asset_sales.id','inner');
            $this->db->join('product_names','product_names.product_name_id  = products.product_name_id','left');
            $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'" and products.campus_id ="'.$campus.'"');
            $data['asset_sales'] = $this->db->get()->result_array();

            $this->db->select('sum(asset_sales.sale_amount)  as total');
            $this->db->from('asset_sales');
            $this->db->join('products','products.sale_id=asset_sales.id','inner');
            $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'" and products.campus_id = "'.$campus.'"');
            $data['asset_sales_sum'] = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('sales');
            $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
            $this->db->join('people','people.person_id  = sales.customer_id','inner');
            $this->db->where('sales.closing_id = "'.$closed[0]['campus_closing_id'].'" and sales.campus_id ="'.$campus.'"');
            $data['sales'] = $this->db->get()->result_array();

            $this->db->select('sum(sales_payments.payment_amount)  as total');
            $this->db->from('sales');
            $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
            $this->db->where('sales.closing_id = "'.$closed[0]['campus_closing_id'].'" and sales.campus_id ="'.$campus.'"');
            $data['sales_sum'] = $this->db->get()->result_array();

            $data['pos_sales'] = $this->get_pos_sales_by_closing($campus, $closed[0]['campus_closing_id']);
            $data['pos_sales_sum'] = array(array('total' => $this->get_pos_sales_sum_by_closing($campus, $closed[0]['campus_closing_id'])));

            $this->db->select('*,loan_plan.id as id');
            $this->db->from('loan_plan');
            $this->db->join('loans','loans.id = loan_plan.loan_id');
            $this->db->join('users','users.user_id = loans.user_id');
            $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'"');
            $loan_today = $this->db->get()->result_array();

        }
        else {
            $this->db->select('*');
            $this->db->from('payments');
            $this->db->join('students','students.student_id = payments.student_id','left');
            $this->db->join('contracts','contracts.contract_id = payments.contract_id','left');
            $this->db->join('courses','courses.course_id=students.course_id','left');
            $this->db->where('submitted_fee_campus_id', $campus);
            $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
            $this->db->where('fee_pay_through = "college"');
            $this->db->where('actual_paid_date = "'.$date.'"');
            $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
            $query = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('payments');
            $this->db->join('students','students.student_id = payments.student_id','left');
            $this->db->join('contracts','contracts.contract_id = payments.contract_id','left');
            $this->db->join('courses','courses.course_id=students.course_id','left');
            $this->db->where('submitted_fee_campus_id', $campus);
            $this->db->where('merged_challan is null');
            $this->db->where('fee_pay_through = "college"');
            $this->db->where('actual_paid_date = "'.$date.'"');
            $this->db->where('payments.paid = 1');
            $query2 = $this->db->get()->result_array();

            $this->db->select('*,asset_sales.sold_date as sale_date');
            $this->db->from('asset_sales');
            $this->db->join('products','products.product_id = asset_sales.product_id','inner');
            $this->db->join('product_names','product_names.product_name_id  = products.product_name_id','left');
            $this->db->where("asset_sales.sold_date >= '$date 00:00:00' and asset_sales.sold_date <= '$date 23:59:59' and products.campus_id ='$campus'");
            $data['asset_sales'] = $this->db->get()->result_array();

            $this->db->select('sum(asset_sales.sale_amount) as total');
            $this->db->from('asset_sales');
            $this->db->join('products','products.product_id = asset_sales.product_id','inner');
            $this->db->join('product_names','product_names.product_name_id  = products.product_name_id','left');
            $this->db->where("asset_sales.sold_date >= '$date 00:00:00' and asset_sales.sold_date <= '$date 23:59:59' and products.campus_id ='$campus'");
            $data['asset_sales_sum'] = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('sales');
            $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
            $this->db->join('people','people.person_id  = sales.customer_id','inner');
            $this->db->where("sales.sale_time >= '$date 00:00:00' and sales.sale_time <= '$date 23:59:59' and sales.campus_id ='$campus'");
            $data['sales'] = $this->db->get()->result_array();

            $this->db->select('sum(sales_payments.payment_amount) as total');
            $this->db->from('sales');
            $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
            $this->db->join('people','people.person_id  = sales.customer_id','inner');
            $this->db->where("sales.sale_time >= '$date 00:00:00' and sales.sale_time <= '$date 23:59:59' and sales.campus_id ='$campus'");
            $data['sales_sum'] = $this->db->get()->result_array();

            $data['pos_sales'] = $this->get_pos_sales_rows($campus, $date, true);
            $data['pos_sales_sum'] = array(array('total' => $this->get_pos_sales_sum($campus, $date, true)));

            $yesterday = date('Y-m-d', strtotime($date. ' - 1 days'));
            $sq = "select * from closing_perday where campus_id = '".$campus."' and for_month = '".date('m', strtotime($yesterday))."' and for_day = '".date('d', strtotime($yesterday))."'and for_year = '".date('Y', strtotime($yesterday))."'";
            $yest_closed = $this->db->query($sq)->result_array();

            if (count($yest_closed)>0)
            {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->join('students','students.student_id = payments.student_id','left');
                $this->db->join('contracts','contracts.contract_id = payments.contract_id','left');
                $this->db->join('courses','courses.course_id=students.course_id','left');
                $this->db->where('submitted_fee_campus_id', $campus);
                $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$yesterday.'"');
                $this->db->where('closing_id IS NULL');
                $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                $query3 = $this->db->get()->result_array();

                $this->db->select('*');
                $this->db->from('payments');
                $this->db->join('students','students.student_id = payments.student_id','left');
                $this->db->join('contracts','contracts.contract_id = payments.contract_id','left');
                $this->db->join('courses','courses.course_id=students.course_id','left');
                $this->db->where('submitted_fee_campus_id', $campus);
                $this->db->where('merged_challan is null');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$yesterday.'"');
                $this->db->where('closing_id IS NULL');
                $this->db->where('payments.paid = 1');
                $query4 = $this->db->get()->result_array();

                $final = array_merge($query3, $query4,$query, $query2);

                $this->db->select('*,asset_sales.created_at as sale_date');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->join('product_names','product_names.product_name_id  = products.product_name_id','left');
                $this->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id ='$campus'");
                $data['asset_sales2'] = $this->db->get()->result_array();
                $data['asset_sales'] = array_merge($data['asset_sales'], $data['asset_sales2']);

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->join('product_names','product_names.product_name_id  = products.product_name_id','left');
                $this->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id ='$campus'");
                $data['asset_sales_sum2'] = $this->db->get()->result_array();
                $data['asset_sales_sum'][0]['total'] = $data['asset_sales_sum'][0]['total']+$data['asset_sales_sum2'][0]['total'];

                $this->db->select('*');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id ='$campus'");
                $data['sales2'] = $this->db->get()->result_array();
                $data['sales'] = array_merge($data['sales'], $data['sales2']);

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id ='$campus'");
                $data['sales_sum2'] = $this->db->get()->result_array();
                $data['sales_sum'][0]['total'] = $data['sales_sum'][0]['total']+$data['sales_sum2'][0]['total'];

                // Yesterday already closed — carry unstamped bookstore POS sales (sold after close / never included)
                $data['pos_sales2'] = $this->get_pos_sales_rows($campus, $yesterday, true);
                $data['pos_sales'] = array_merge($data['pos_sales'], $data['pos_sales2']);
                $data['pos_sales_sum'][0]['total'] = (float)$data['pos_sales_sum'][0]['total']
                    + $this->get_pos_sales_sum($campus, $yesterday, true);

            }
            else {
                $final = array_merge($query, $query2);
            }

            $this->db->select('*,loan_plan.id as id,loans.id as loan_id');
            $this->db->from('loan_plan');
            $this->db->join('loans','loans.id = loan_plan.loan_id');
            $this->db->join('users','users.user_id = loans.user_id');
            $this->db->where("loan_plan.paid_date = '$date' and loan_plan.campus_id = '".$campus."' and paid_at = 'cash'");
            $loan_today = $this->db->get()->result_array();

            if (count($yest_closed)>0) {
                $this->db->select('*,loan_plan.id as id,loans.id as loan_id');
                $this->db->from('loan_plan');
                $this->db->join('loans','loans.id = loan_plan.loan_id');
                $this->db->join('users','users.user_id = loans.user_id');
                $this->db->where("loan_plan.paid_date = '$yesterday' and loan_plan.campus_id = '" . $campus . "' and loan_plan.closing_id IS NULL and paid_at = 'cash'");
                $loan_yesterday = $this->db->get()->result_array();
                $loan_today = array_merge($loan_today, $loan_yesterday);
            }
        }


        if (!isset($data['pos_sales'])) {
            $data['pos_sales'] = array();
        }
        if (!isset($data['pos_sales_sum'][0]['total'])) {
            $data['pos_sales_sum'] = array(array('total' => 0));
        }
        if (!isset($data['sales_sum'][0]['total'])) {
            $data['sales_sum'] = array(array('total' => 0));
        }
        if (!isset($data['asset_sales_sum'][0]['total'])) {
            $data['asset_sales_sum'] = array(array('total' => 0));
        }

        $total_amount = $total_amount - $data['sales_sum'][0]['total'] - $data['asset_sales_sum'][0]['total'] - $data['pos_sales_sum'][0]['total'];
        $data['payments'] =$final;
        $this->db->select('*');
        $this->db->from('closing_persons');
        $this->db->join('campuses','campuses.campus_id=closing_persons.campus_id','inner');
        $this->db->where("closing_persons.active_status = '1'");
        $this->db->group_by("closing_persons.campus_id");
        $data['campuses'] = $this->db->get()->result_array();

        $data['day'] = date('d', strtotime($date));
        $data['month'] = date('m', strtotime($date));
        $data['year'] = date('Y', strtotime($date));
        $data['campus_id'] = $campus;
        $data['closing_status'] = (count($closed) > 0) ? '1' : $status;
        $data['loans'] = $loan_today;
        $data['total_amount'] = $total_amount;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('closings/closingdetails', $data);
        $this->load->view('inc/footer');
    }

    public function campus_profit($campus_id)
    {

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/campus_profit');
        $this->load->view('inc/footer');

    }

    public function closing_person()
    {

        $data['campuses'] = $this->account->getCampuses();

        $this->db->select('*');
        $this->db->from('closing_persons');
        $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id','left');
        $this->db->join('users','users.user_id = closing_persons.user_id','left');
//        $this->db->where ('petty_cash_college_wise.petty_status',$petystatus);
        $data['Persons'] = $this->db->get()->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('closings/add_closing_person', $data);
        $this->load->view('inc/footer');


    }

    public function add_closing_person()
    {
        $campus_id = $this->input->post('campus_id');
        $user = $this->input->post('user_id');
        $created_by = $this->session->userdata('name');

        $this->db->set('campus_id',$campus_id);
        $this->db->set('user_id',$user);
        $this->db->set('created_at',date('Y-m-d H:i:s'));
        $this->db->set('created_by',$created_by);
        $this->db->insert('closing_persons');

        $this->session->set_flashdata('message', 'Closing Person Added successfully');

        redirect(site_url().'/closing/closing_person');
    }

    public function closenow()
    {
        $month=$this->input->post('month');
        $day=$this->input->post('day');
        $year=$this->input->post('year');
        $feeids=$this->input->post('feeids');
        $cam_id=$this->input->post('campus_id');
        $total_amount=$this->input->post('receivable_amount');
        $close_type=$this->input->post('close_type');
        $sale_ids=$this->input->post('sale_ids');
        $loan_ids=$this->input->post('loan_ids');
        $closingDate = sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);

        $this->db->trans_start();

        $existingClosing = $this->get_closing_for_date($cam_id, $day, $month, $year, true);
        if ($existingClosing) {
            $this->db->trans_complete();
            $this->session->set_flashdata('error', 'Closing already exists for this campus on this date.');
            redirect(
                'closing/viewclosing/'.$closingDate.'/'.$cam_id.'/'.$existingClosing['closed_amount'].'/1',
                'refresh'
            );
            return;
        }

        $sqlclosingid="SELECT concat( LEFT(`campus_closing_id`,3),MAX(CAST(SUBSTRING(`campus_closing_id`, 4, length(`campus_closing_id`)-2) AS UNSIGNED))+1) as closing_id FROM `closing_perday` WHERE `campus_id` = '".$cam_id."'";
        $closingid=$this->db->query($sqlclosingid)->row();

        $this->db->set('closing_id',$closingid->closing_id);
        $this->db->where_in('id',@explode(',',$feeids));
        $this->db->update('payments');

        $this->db->set('closing_id',$closingid->closing_id);
        $this->db->where_in('id',@explode(',',$sale_ids));
        $this->db->update('asset_sales');

        // Stamp bookstore POS units included in this closing (today + yesterday orphans if yesterday was closed)
        $this->ensure_products_closing_id_column();
        $yesterday = date('Y-m-d', strtotime($closingDate . ' -1 days'));
        $yest_closed = $this->get_closing_for_date(
            $cam_id,
            date('d', strtotime($yesterday)),
            date('m', strtotime($yesterday)),
            date('Y', strtotime($yesterday))
        );
        $pos_dates = array($closingDate);
        if (!empty($yest_closed)) {
            $pos_dates[] = $yesterday;
        }
        $this->db->set('closing_id', $closingid->closing_id);
        $this->db->where('sold', 1);
        $this->db->where('campus_id', $cam_id);
        $this->db->where_in('sold_date', $pos_dates);
        $this->apply_pos_unclosed_where();
        $this->db->update('products');

        $this->db->select('*');
        $this->db->where('closing_id = "'.$closingid->closing_id.'" and merged_challan is NOT NULL');
        $allpayments=$this->db->get('payments')->result_array();

        foreach($allpayments as $payment)
        {
            $this->db->set('closing_id','0');
            $this->db->where('merged_challan = "'.$payment['merged_challan'].'" and closing_id is NULL');
            $this->db->update('payments');
        }

        $this->db->set('campus_id',$cam_id);
        $this->db->set('for_day',$day);
        $this->db->set('for_month',$month);
        $this->db->set('for_year',$year);
        $this->db->set('campus_closing_id',$closingid->closing_id);
        $this->db->set('closed_amount',$total_amount);
        $this->db->set('receivable_amount',$total_amount);
        $this->db->set('close_type',$close_type);
        $this->db->set('closed_by',$this->session->userdata('name'));
        $this->db->insert('closing_perday');
        $insertedClosingId = $this->db->insert_id();

        $user = $this->session->userdata('user_id');
        $this->db->set('closing_id',$closingid->closing_id);
        $this->db->where_in('id',@explode(',',$loan_ids));
        $this->db->update('loan_plan');

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Closing could not be saved. Please try again.');
            redirect('closing/viewclosing/'.$closingDate.'/'.$cam_id.'/'.$total_amount.'/0', 'refresh');
            return;
        }

        $this->db->trans_complete();

        if ($close_type == '3'){
            $user_phones = $this->db->get_where("users_phones","user_id = '$user'")->row();

            if ($user_phones == NULL)
                $number = "03168042977";
            else
                $number = $user_phones->phone;
            $bill_url = $this->generate_paypro($total_amount,$this->session->userdata('name'),$number,$user,$insertedClosingId);
            redirect($bill_url, 'refresh');
        }else
            redirect('closing/index', 'refresh');
    }

    public function add_closing_details()
    {
        $closingid=$this->input->post('closingid');
        $transno=$this->input->post('trans_id');
        $account_id=$this->input->post('account_id');

        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $new_name                   = time()."closing.jpg";
        $config['file_name']        = $new_name;
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
            $data = array('msg' => $this->upload->display_errors());
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

        $this->db->set('account_id',$account_id);
        $this->db->set('transaction_no',$transno);
        if ($image!='')
            $this->db->set('partialy_closed_image',$image);
        $this->db->where('id',$closingid);
        $this->db->update('closing_perday');

        redirect('closing/index', 'refresh');
    }

    public function accountsclosing()
    {
        $data['campuses'] = $this->db->select('campuses.*')->join("campuses" , "campuses.campus_id = closing_persons.campus_id")->group_by("closing_persons.campus_id")->get("closing_persons")->result_array();
        $access = checkUserAccess();

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');

        if( $from_date == '' ){

            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }
        $this->db->select('*,closing_perday.id as id');
        $this->db->from('closing_perday');
        $this->db->join('campuses','campuses.campus_id = closing_perday.campus_id','left');
        $this->db->join('bank_reconciliation_statement','bank_reconciliation_statement.closing_id = closing_perday.id','left');
        if ($this->input->post('expense_campus_ids') != ''){
            $this->db->where('closing_perday.campus_id', ($this->input->post('expense_campus_ids')));
        }
        if ($this->input->post('tag_type') == '0'){
            $this->db->where('(closing_perday.close_type = 1 and bank_reconciliation_statement.closing_id IS NULL)');
        }
        $this->db->where('STR_TO_DATE(CONCAT(closing_perday.for_year,"-",closing_perday.for_month,"-",closing_perday.for_day), "%Y-%m-%d") >= ', $from_date);
        $this->db->where('STR_TO_DATE(CONCAT(closing_perday.for_year,"-",closing_perday.for_month,"-",closing_perday.for_day), "%Y-%m-%d") <= ', $to_date);

        $this->db->order_by('closing_perday.id', 'DESC');
        $data['closings'] = $this->db->get()->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
			(select for_day from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
			(select for_month from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
			MAX(for_year) as year from closing_perday 
			left join campuses on campuses.campus_id = closing_perday.campus_id
			 where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
			 GROUP by closing_perday.campus_id';
        $data['campusclosings'] = $this->db->query($sq)->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
		(select for_day from closing_perday where campus_id = campuses.campus_id and checked_by = "1" 
		order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		(select for_month from closing_perday where campus_id = campuses.campus_id and checked_by = "1" 
		order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		(select for_year from closing_perday where campus_id = campuses.campus_id and checked_by = "1" 
		order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as year from closing_perday 
		left join campuses on campuses.campus_id = closing_perday.campus_id 
		where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
		GROUP by closing_perday.campus_id';
        $data['campusclosingverified'] = $this->db->query($sq)->result_array();

        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where('type = "1"');
        $data['accounts'] = $this->db->get()->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('closings/accountsclosingsheet', $data);
        $this->load->view('inc/footer');
    }

    public function verify_closing_now()
    {

        $id = $this->input->post('closingid');
        $amount = $this->input->post('amount');
        $success = false;
        $message = 'Closing not found or already verified.';

        $this->db->select('*');
        $this->db->from('closing_perday');
        $this->db->where('id = '.$id.' and checked_by is null');
        $check_record = $this->db->get()->result_array();

        if(count($check_record) > 0){
            if ($check_record[0]['close_type'] == '2')
            {
                $check_closing_account = $this->db->get_where('college_closing_rules', array('campus_id'=>$check_record[0]['campus_id']))->result_array();
                $campus = $this->db->get_where('campuses', array('campus_id'=>$check_record[0]['campus_id']))->row();


                $this->db->set('amount', 'amount +'. $amount .'',false);
                $this->db->where('id', $check_closing_account[0]['account_id']);
                $this->db->update('accounts');

                $this->db->set('closed_amount', $amount);
                $this->db->where('id', $id);
                $this->db->set('created_at',date('Y-m-d H:i:s'));
                $this->db->update('closing_perday');

                $this->db->set('daily_closing_id',$id);
                $this->db->set('to_account_id',$check_closing_account[0]['account_id']);
                $this->db->set('amount',$amount);
                $this->db->set('debit_credit','D');
                $this->db->set('transaction_by',$this->session->userdata('name'));
                $this->db->set('transaction_account_id',$check_closing_account[0]['account_id']);
                $this->db->set('reason','Funds Receive from Closing '.$check_record[0]['campus_closing_id'].' '.$campus->campus_name.' '.$check_record[0]['for_day'].'-'.$check_record[0]['for_month'].'-'.$check_record[0]['for_year']);
                $this->db->set('created_at',date('Y-m-d H:i:s'));
                $this->db->insert('transactions_history');
                $this->db->set('checked_by','1');
                $this->db->where('id',$id);
                $this->db->update('closing_perday');

            }else{
                $this->db->set('checked_by','1');
                $this->db->where('id',$id);
                $this->db->set('created_at',date('Y-m-d H:i:s'));
                $this->db->update('closing_perday');

            }

            $success = true;
            $message = 'Closing verified successfully.';

        }

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => $success,
                    'message' => $message,
                    'closing_id' => $id
                )));
            return;
        }

        if ($success) {
            $this->session->set_userdata('message', $message);
        } else {
            $this->session->set_userdata('error', $message);
        }

        redirect('closing/accountsclosing');

    }

    public function transfer_fee($fee_id)
    {

        $campus_id=$this->input->post('course_id');
        $check_closing_fee = $this->db->get_where('payments', array('id'=>$fee_id))->row();

        if($check_closing_fee->merged_challan == NULL || $check_closing_fee->merged_challan == "")
        {
            $this->db->set('submitted_fee_campus_id',$campus_id);
            $this->db->where('id',$fee_id);
            $this->db->update('payments');
        }else
        {
            $this->db->set('submitted_fee_campus_id',$campus_id);
            $this->db->where('merged_challan',$check_closing_fee->merged_challan);
            $this->db->update('payments');
        }

        redirect('closing/index', 'refresh');

    }

    public function updatenow()
    {
        $closingid = $this->input->post("closing_id");
        $close_type = $this->input->post("close_type");
        $total_amount = $this->db->get_where("closing_perday","id = $closingid")->row()->receivable_amount;

        $this->db->set('close_type',$close_type);
        if ($close_type == "2") {
            $this->db->set('partialy_closed_image', NULL);
            $this->db->set('transaction_no', NULL);
        }

        $this->db->where_in('id',$closingid);
        $this->db->update('closing_perday');

        $user = $this->session->userdata('user_id');

        if ($close_type == '3'){
            $user_phones = $this->db->get_where("users_phones","user_id = '$user'")->row();

            if ($user_phones == NULL)
                $number = "03174999862";
            else
                $number = $user_phones->phone;
            $bill_url = $this->generate_paypro($total_amount,$this->session->userdata('name'),$number,$user,$closingid);
            redirect($bill_url, 'refresh');
        }else
            redirect('closing/index', 'refresh');

        redirect('closing/index', 'refresh');
    }

    public function find_transactions()
    {

        $from_date = date("Y-m-d",strtotime($this->input->post('from_date')));
        $to_date = date("Y-m-d",strtotime($this->input->post('to_date')));
        $bank_trans_id = $this->input->post('bank_trans_id');
        $bail_amount = $this->input->post('amount');
        $closing = $this->db->get_where("closing_perday","id = '$bank_trans_id'")->row();

        $this->db->select('*,bank_reconciliation_statement.id as tidx');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
        $this->db->where("bank_reconciliation_statement.trans_date >='$from_date' and bank_reconciliation_statement.trans_date<='$to_date'and account_id = '$closing->account_id' and bank_reconciliation_statement.closing_id IS NULL and CONVERT(replace(bank_reconciliation_statement.credit,',',''), SIGNED) = '".$bail_amount."'");
        $this->db->group_by("bank_reconciliation_statement.description");
        $entries=$this->db->get()->result_array();


        $html = '';

        $i=0;
        foreach($entries as $closing_rule):

            $f=$i+1;
            $html.=" <tr>
                <td >
                    $f
                     <input class='form-check-input' type='radio' name='tag_id'  value='{$closing_rule['tidx']}' required>
                    
                </td>
                <td>
                    
                </td>
                <td>
                    {$closing_rule['trans_date']}
                </td>
                <td>
                    {$closing_rule['description']}
                </td>
                <td>
                    {$closing_rule['debit']}
                </td>
                <td>
                    {$closing_rule['credit']}
                </td>
                <td>
                    {$closing_rule['balance']}
                </td>

            </tr>";

            $i++;
        endforeach;

        echo $html;


    }

    public function tag_bank_trans() {
        $tag_id=$this->input->post('tag_id');
        $trans_id=$this->input->post('bank_trans_id');
        $bank_cls_data = $this->db->get_where("bank_reconciliation_statement","id = '$tag_id'")->row();
        $closing = $this->db->get_where("closing_perday","id = '$trans_id'")->row();

        //echo intval(str_replace(',','',$bank_cls_data->credit));
        //echo '<br />';
        //echo str_replace(',', '',number_format($closing->closed_amount));
        //exit;

        if(intval(str_replace(',', '',$bank_cls_data->credit)) < intval(str_replace(',', '',number_format($closing->closed_amount))))
        {
            $this->db->set('campus_id',$closing->campus_id);
            $this->db->set('expense_category_id',"122");
            $this->db->set('title',"Expense against Closing ".$closing->campus_closing_id);
            $this->db->set('date',date('Y-m-d'));
            $this->db->set('amount',intval(str_replace(',', '',number_format($closing->closed_amount)))-intval(str_replace(',', '',number_format($bank_cls_data->credit))));
            $this->db->set('purpose',"Expense due to Cash short in Closing");
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', "");
            $this->db->set('paid_type', "bank");
            $this->db->set('approved_status', '1');
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->insert('expenses');
        }
        $this->db->set('closing_id',$trans_id);
        $this->db->where('id',$tag_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->set('checked_by','1');
        $this->db->where('id',$trans_id);
        $this->db->set('created_at',date('Y-m-d H:i:s'));
        $this->db->update('closing_perday');

        redirect(site_url().'/closing/accountsclosing');
    }

    public function print_bank_challan($closing_id,$campus_id,$amount) {

        $data['campus'] = $this->db->get_where('campuses',"campus_id = '$campus_id'")->row();
        $data['closing_id'] = $closing_id;
        $data['amount'] = $amount;
        $this->load->view('closings/print_challan', $data);
    }

    public function generate_paypro($amount,$name,$mobile,$student_id,$closing_id)
    {
        $order_no = $this->getPayproID();
        $date = date('d-m-Y');
        $last_date = date('d-m-Y', strtotime($date. ' + 10 days'));
        $total_order = array();
        $merchant = array("MerchantId"=>"SCOP","MerchantPassword"=>"Live@shahbaz21");
        $order = array("OrderNumber"=>"$order_no","OrderAmount"=>"$amount"
        ,"OrderDueDate"=>"$last_date","OrderAmountWithinDueDate"=>"$amount"
        ,"OrderAmountAfterDueDate"=>"$amount"
        ,"OrderType"=>"Service","OrderTypeId"=>"Service"
        ,"IssueDate"=>"$date","OrderExpireAfterSeconds"=>"0"
        ,"CustomerName"=>"$name","CustomerMobile"=>"$mobile"
        ,"CustomerEmail"=>"","CustomerAddress"=>""
        );
        array_push($total_order,$merchant);
        array_push($total_order,$order);
        $headers = array(
            'Content-Type:application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.paypro.com.pk/cpay/co?");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($total_order));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
//        $response = json_decode($result);
        $response = array_values(json_decode($result, true));
        if ($response[0]['Status'] == "00") {
            $ipg_main_list = $response[1]['IPGList'];
            $application_id = "";
            $ipg_list = $ipg_main_list;
            $transaction_status = $response[1]['TransactionStatus'];
            $order_amount = $amount;
            $description = $response[1]['TransactionStatus'];
            $BAF_charge = $response[1]['BAFCharge'];
            $one_link_charge = $response[1]['1LinkCharge'];
            $created_on = date('Y-m-d');
            $consumer_code = $response[1]['ConsumerCode'];
            $click2pay = $response[1]['Click2Pay'];
            $connect_pay_id = $response[1]['ConnectPayId'];
            $order_type = $response[1]['FetchOrderType'];
            $connect_pay_fee = $response[1]['ConnectPayFee'];
            $bill_url = $response[1]['BillUrl'];
            $order_number = $response[1]['OrderNumber'];
            $is_fee_applied = $response[1]['IsFeeApplied'];

            $this->db->set(array(
                'application_id' => $application_id,
                'student_id' => $student_id,
                'ipg_list' => json_encode($ipg_list),
                'transaction_status' => $transaction_status,
                'order_amount' => $order_amount,
                'description' => $description,
                'BAF_charge' => $BAF_charge,
                'one_link_charge' => $one_link_charge,
                'created_on' => $created_on,
                'consumer_code' => $consumer_code,
                'click2pay' => $click2pay,
                'connect_pay_id' => $connect_pay_id,
                'order_type' => $order_type,
                'connect_pay_fee' => $connect_pay_fee,
                'bill_url' => $bill_url,
                'order_number' => $order_number,
                'is_fee_applied' => $is_fee_applied,
                'type' => 'closing',
                'closing_id' => $closing_id
            ));

            $this->db->insert('students_payments');
            $insert_id = $this->db->insert_id();
            return $click2pay;
        }else
        {
            print_r($result);
            exit();
        }
//        $results=$this->db->get_where("students_payments","payment_id = '$insert_id'")->result_array();

    }

    public function getPayproID()
    {
        $random_number = rand(1000, 999999999);
        $check_challan_no = $this->db->get_where('students_payments', array('order_number'=>$random_number))->result_array();
        if(count($check_challan_no)>0)   {
            $random_number = $this->getPayproID();
        }
        else {
            return $random_number;
        }
    }

    public function deleteClosing($id)
    {
        $this->db->where('id',$id);
        $this->db->delete('closing_perday');
        $this->session->flashdata('message','Closing Deleted Successfully');
        redirect('closing/accountsclosing');
    }

}





