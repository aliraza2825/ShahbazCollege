<?php
class Account extends CI_Model {

    public function getCampuses()
    {
        $query = $this->db->get('campuses')->result_array();
        return $query;
    }

    public function getCampus($campus_id)
    {
        $query = $this->db->get_where('campuses', array('campus_id'=>$campus_id))->result_array();
        return $query;
    }

    public function getProfitDone($campus_id)
    {
        $this->db->select('*');
        $this->db->from('profit_distribution');
        $this->db->join('users', 'users.user_id=profit_distribution.user_id', 'inner');
        $this->db->where('profit_distribution.campus_id', $campus_id);
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getExpenses($from_date, $till_date, $campus_id)
    {
        $special_categories = $this->db->get_where('campus_partners',array('campus_id'=>$campus_id))->result_array();
        
        if($special_categories[0]['special_expense_ids']!=NULL || $special_categories[0]['special_expense_ids']!='')
        {
            $this->db->select('*');
            $this->db->from('expenses');
            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.actual_date>='=>$from_date, 'expenses.actual_date<='=>$till_date, 'expenses.approved_status'=> '1'));
            if($special_categories[0]['special_expense_ids']!=NULL || $special_categories[0]['special_expense_ids']!=''):
                $special_categories_ids = implode(',',json_decode($special_categories[0]['special_expense_ids']));
                $this->db->where_in('expenses.expense_category_id', $special_categories_ids);
            endif;
            $query = $this->db->get()->result_array();
            return $query;
        }
        else
        {
            $query = array();
            return $query;
        }
        /*$this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.actual_date>='=>$from_date, 'expenses.actual_date<='=>$till_date, 'expenses.approved_status<'=>"2"));
        $query = $this->db->get()->result_array();
        return $query;*/
    }

    public function getPayments($from_date, $till_date, $campus_id)
    {
        $this->db->select('payments.*, classes.name as class_name, campuses.campus_name, students.first_name, students.last_name, students.roll_no');
        $this->db->from('payments');

        $this->db->join('students', 'students.student_id=payments.student_id', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');

        $this->db->where(array('classes.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1,'payments.contract_id'=>0));
        $this->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
        $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end",false);
        $query = $this->db->get()->result_array();

        $this->db->select('payments.*, classes.name as class_name, campuses.campus_name, students.first_name, students.last_name, students.roll_no');
        $this->db->from('payments');

        $this->db->join('students', 'students.student_id=payments.student_id', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');

        $this->db->where(array('classes.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1,'payments.contract_id'=>0));
        $this->db->where('payments.merged_challan is null and payments.actual_amount > 0');
        $query2 = $this->db->get()->result_array();

        $arr=array_merge($query,$query2);

        function date_compare($a, $b)
        {
            $t1 = strtotime($a['dead_line']);
            $t2 = strtotime($b['dead_line']);
            return $t1 - $t2;
        }

        usort($arr, 'date_compare');
        return $arr;

    }

    public function getPaymentsContractors($from_date, $till_date, $campus_id)
    {
        $this->db->select('payments.*, campuses.campus_name, contractors.name, contractors.contractor_id_from_college,contracts.contract_name');
        $this->db->from('payments');

        $this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
        $this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
        $this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');

        $this->db->where(array('campuses.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1,'payments.contract_id > 0'));
        $this->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
        $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end",false);

        $query = $this->db->get()->result_array();


        $this->db->select('payments.*, campuses.campus_name, contractors.name, contractors.contractor_id_from_college,contracts.contract_name');
        $this->db->from('payments');

        $this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
        $this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
        $this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');

        $this->db->where(array('campuses.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1,'payments.contract_id > 0'));
        $this->db->where('payments.merged_challan is null and payments.actual_amount > 0');
        $query2 = $this->db->get()->result_array();

        $arr=array_merge($query,$query2);



        usort($arr, 'date_compare');
        return $arr;
    }

    public function getUsers()
    {
        /*$this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'inner');*/

        $query = $this->db->get_where('users', array('users.status'=>1))->result_array();
        return $query;
    }

}
?>