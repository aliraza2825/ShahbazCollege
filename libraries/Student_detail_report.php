<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared Student Full Detail Report enrichment (month grid + footers).
 * Used by Studentsapi and Incentiveapi recovery student detail.
 */
class Student_detail_report {

	/** @var CI_Controller */
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function month_list($start, $end)
	{
		$months = array();
		try {
			$startDt = new DateTime(date('Y-m-01', strtotime($start)));
			$endDt = new DateTime(date('Y-m-01', strtotime($end)));
			$endDt->modify('+1 month');
			$period = new DatePeriod($startDt, new DateInterval('P1M'), $endDt);
			foreach ($period as $dt) {
				$months[] = $dt->format('Y-m');
			}
		} catch (Exception $e) {
			$months[] = date('Y-m');
		}
		return $months;
	}

	public function date_range_for_students($student_ids)
	{
		if (!count($student_ids)) {
			return array(
				'startdate' => date('Y-m-01'),
				'enddate' => date('Y-m-d'),
			);
		}
		$this->CI->db->select('MIN(students.registration_date) AS startdate, MAX(dead_line) AS enddate', false);
		$this->CI->db->from('students');
		$this->CI->db->join('payments', 'payments.student_id = students.student_id', 'left');
		$this->CI->db->where_in('students.student_id', array_map('intval', $student_ids));
		$this->CI->db->where('students.status', '1');
		$row = $this->CI->db->get()->row_array();
		return array(
			'startdate' => !empty($row['startdate']) ? $row['startdate'] : date('Y-m-01'),
			'enddate' => !empty($row['enddate']) ? $row['enddate'] : date('Y-m-d'),
		);
	}

	public function enrich($students, $months)
	{
		$out = array();
		$footer_must = array();
		$footer_paid = array();
		foreach ($months as $m) {
			$footer_must[$m] = 0;
			$footer_paid[$m] = 0;
		}
		if (!count($students)) {
			return array('rows' => array(), 'footer_must' => $footer_must, 'footer_paid' => $footer_paid);
		}

		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$pay_by = $this->payments_by_student($ids);
		$remarks_by = $this->latest_remarks_by_student($ids);

		foreach ($students as $s) {
			$sid = (int)$s['student_id'];
			$payments = isset($pay_by[$sid]) ? $pay_by[$sid] : array();

			$due_by_month = array();
			$paid_by_month = array();
			$paid_transactions = array();
			$row_must = 0;
			$row_paid = 0;

			foreach ($payments as $p) {
				$due_ym = substr($p['dead_line'], 0, 7);
				$paid_ym = $this->payment_paid_month($p);
				$amt = (float)$p['amount'];
				$act = (float)$p['actual_amount'];
				$is_paid = (int)$p['paid'] === 1;
				$is_merged = !empty($p['merged_challan']);
				$plan = isset($p['payment_plan']) ? $p['payment_plan'] : (isset($p['payment_comment']) ? $p['payment_comment'] : '');
				$row_must += $amt;

				$paid_elsewhere = $is_paid && $paid_ym && ($is_merged || $paid_ym !== $due_ym);
				$display_paid = 0;
				if ($is_paid && $act > 0 && !$is_merged && $paid_ym === $due_ym) {
					$display_paid = $act;
				}

				if (!$paid_elsewhere) {
					if (!$is_paid) {
						if (!isset($due_by_month[$due_ym])) $due_by_month[$due_ym] = array();
						$due_by_month[$due_ym][] = array(
							'amount' => $amt,
							'actual_amount' => 0,
							'unpaid_style' => true,
							'dead_line' => $p['dead_line'],
							'payment_plan' => $plan,
							'cell_kind' => 'due',
						);
						if (isset($footer_must[$due_ym])) {
							$footer_must[$due_ym] += $amt;
						}
					} elseif ($display_paid > 0) {
						if (!isset($due_by_month[$due_ym])) $due_by_month[$due_ym] = array();
						$due_by_month[$due_ym][] = array(
							'amount' => $amt,
							'actual_amount' => $display_paid,
							'unpaid_style' => false,
							'dead_line' => $p['dead_line'],
							'payment_plan' => $plan,
							'cell_kind' => 'due',
						);
						if (isset($footer_must[$due_ym])) {
							$footer_must[$due_ym] += $amt;
						}
						$row_paid += $display_paid;
						if (isset($footer_paid[$paid_ym])) {
							$footer_paid[$paid_ym] += $display_paid;
						}
					}
				}

				if ($is_paid && $act > 0 && $paid_ym && ($is_merged || $paid_ym !== $due_ym)) {
					$merge_key = $is_merged
						? 'm:' . $p['merged_challan'] . ':' . $paid_ym . ':' . $p['paid_date']
						: 's:' . $p['id'];
					if (!isset($paid_transactions[$merge_key])) {
						$paid_transactions[$merge_key] = array(
							'actual_amount' => $act,
							'payable_amount' => 0,
							'paid_date' => $p['paid_date'],
							'paid_ym' => $paid_ym,
							'installments' => array(),
							'payment_plan' => $plan,
						);
					}
					$paid_transactions[$merge_key]['payable_amount'] += $amt;
					$paid_transactions[$merge_key]['installments'][] = array(
						'dead_line' => $p['dead_line'],
						'due_month' => $due_ym,
						'amount' => $amt,
						'payment_plan' => $plan,
					);
				}
			}

			$paid_month_buckets = array();
			foreach ($paid_transactions as $tx) {
				$ym = $tx['paid_ym'];
				if (!isset($paid_month_buckets[$ym])) {
					$paid_month_buckets[$ym] = array(
						'amount' => 0,
						'actual_amount' => 0,
						'installments' => array(),
						'paid_dates' => array(),
					);
				}
				$paid_month_buckets[$ym]['amount'] += (float)$tx['payable_amount'];
				$paid_month_buckets[$ym]['actual_amount'] += (float)$tx['actual_amount'];
				$paid_month_buckets[$ym]['installments'] = array_merge(
					$paid_month_buckets[$ym]['installments'],
					$tx['installments']
				);
				if (!empty($tx['paid_date']) && $tx['paid_date'] !== '0000-00-00') {
					$paid_month_buckets[$ym]['paid_dates'][$tx['paid_date']] = true;
				}
			}

			foreach ($paid_month_buckets as $ym => $bucket) {
				if (!isset($paid_by_month[$ym])) $paid_by_month[$ym] = array();
				$installment_months = array();
				foreach ($bucket['installments'] as $inst) {
					if (!empty($inst['due_month']) && !in_array($inst['due_month'], $installment_months, true)) {
						$installment_months[] = $inst['due_month'];
					}
				}
				sort($installment_months);
				$paid_dates = array_keys($bucket['paid_dates']);
				sort($paid_dates);
				$paid_by_month[$ym][] = array(
					'amount' => $bucket['amount'],
					'actual_amount' => $bucket['actual_amount'],
					'unpaid_style' => false,
					'paid_date' => count($paid_dates) ? $paid_dates[0] : '',
					'paid_dates' => $paid_dates,
					'installment_months' => $installment_months,
					'installment_details' => $bucket['installments'],
					'cell_kind' => 'payment',
				);
				$row_paid += (float)$bucket['actual_amount'];
				if (isset($footer_paid[$ym])) {
					$footer_paid[$ym] += (float)$bucket['actual_amount'];
				}
				if (isset($footer_must[$ym])) {
					$footer_must[$ym] += (float)$bucket['amount'];
				}
			}

			$month_cells = array();
			foreach ($months as $ym) {
				$cells = array();
				if (isset($due_by_month[$ym])) {
					$cells = array_merge($cells, $due_by_month[$ym]);
				}
				if (isset($paid_by_month[$ym])) {
					$cells = array_merge($cells, $paid_by_month[$ym]);
				}
				$month_cells[$ym] = $cells;
			}

			$s['months'] = $month_cells;
			$s['must_paid'] = $row_must;
			$s['paid_total'] = $row_paid;
			$rem = isset($remarks_by[$sid]) ? $remarks_by[$sid] : null;
			$s['latest_comment'] = $rem ? (string)$rem['comment'] : '';
			$s['latest_comment_date'] = $rem ? (string)$rem['date'] : '';
			$s['latest_comment_paid_on'] = ($rem && !empty($rem['paid_on_date']) && $rem['paid_on_date'] !== '0000-00-00')
				? (string)$rem['paid_on_date']
				: '';
			$out[] = $s;
		}
		return array(
			'rows' => $out,
			'footer_must' => $footer_must,
			'footer_paid' => $footer_paid,
		);
	}

	public function payment_paid_month($payment)
	{
		$d = '';
		if (!empty($payment['paid_date']) && $payment['paid_date'] !== '0000-00-00') {
			$d = $payment['paid_date'];
		} elseif (!empty($payment['actual_paid_date']) && $payment['actual_paid_date'] !== '0000-00-00') {
			$d = $payment['actual_paid_date'];
		}
		return $d ? substr($d, 0, 7) : null;
	}

	public function payments_by_student($ids)
	{
		$map = array();
		if (!count($ids)) return $map;
		$rows = $this->CI->db->where_in('student_id', $ids)->get('payments')->result_array();
		foreach ($rows as $r) {
			$sid = (int)$r['student_id'];
			if (!isset($map[$sid])) $map[$sid] = array();
			$map[$sid][] = $r;
		}
		return $map;
	}

	/** Latest fees_remarks row per student (across all payment fee_ids). */
	public function latest_remarks_by_student($student_ids)
	{
		$map = array();
		if (!count($student_ids) || !$this->CI->db->table_exists('fees_remarks')) {
			return $map;
		}

		$ids = array_values(array_unique(array_map('intval', $student_ids)));
		$ids = array_filter($ids, function ($id) { return $id > 0; });
		if (!count($ids)) return $map;

		$rows = $this->CI->db->query(
			'SELECT fr.comment, fr.date, fr.paid_on_date, fr.add_by, p.student_id
			FROM fees_remarks fr
			INNER JOIN payments p ON p.id = fr.fee_id
			INNER JOIN (
				SELECT p2.student_id, MAX(fr2.fee_remarks_id) AS max_id
				FROM fees_remarks fr2
				INNER JOIN payments p2 ON p2.id = fr2.fee_id
				WHERE p2.student_id IN (' . implode(',', $ids) . ')
				GROUP BY p2.student_id
			) t ON fr.fee_remarks_id = t.max_id AND p.student_id = t.student_id'
		)->result_array();

		foreach ($rows as $row) {
			$map[(int)$row['student_id']] = $row;
		}
		return $map;
	}
}
