<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Thin bridge to legacy Salary controller logic for Hrapi JSON endpoints.
 */
class Payroll_service {

    /** @var Salary|null */
    private $salary;

    public function __construct()
    {
        require_once APPPATH . 'controllers/Salary.php';
        $this->salary = new Salary(true);
    }

    public function build_generate_salary_payload($user_id, $campus_id, $month, $year)
    {
        return $this->salary->build_generate_salary_payload($user_id, $campus_id, $month, $year);
    }

    public function storepayroll_from_body($body, $actor_user_id = null, $actor_name = '')
    {
        return $this->salary->storepayroll_from_body($body, $actor_user_id, $actor_name);
    }

    public function fetch_salary_report_data($campus_id, $to_date, $minimum_adjustment_report = false)
    {
        return $this->salary->fetch_salary_report_data($campus_id, $to_date, $minimum_adjustment_report);
    }

    public function insert_expense_from_body($body, $actor_user_id = null, $actor_name = '')
    {
        return $this->salary->insert_expense_from_body($body, $actor_user_id, $actor_name);
    }

    public function remove_contributions_from_body($body)
    {
        return $this->salary->remove_contributions_from_body($body);
    }

    public function add_contribution_expense_from_body($body, $files = array(), $actor_user_id = null, $actor_name = '')
    {
        return $this->salary->add_contribution_expense_from_body($body, $files, $actor_user_id, $actor_name);
    }

    public function delete_expense_from_body($exp_id)
    {
        return $this->salary->delete_expense_from_body($exp_id);
    }

    public function fetch_salary_slip_data($user_id, $month, $year)
    {
        return $this->salary->fetch_salary_slip_data($user_id, $month, $year);
    }
}
