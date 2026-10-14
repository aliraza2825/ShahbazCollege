<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Read-only aggregation for the course/batch recovery matrix. Amounts are summed in paisa. */
class Batch_recovery_report {
    public function build($students, $payments, $month, $due_date) {
        $amounts = array(); $receipts = array();
        foreach ($students as $s) $amounts[(int)$s['student_id']] = array('due'=>0, 'recovery'=>0);
        foreach ($payments as $p) {
            $sid = (int)$p['student_id'];
            if (!isset($amounts[$sid])) continue;
            if ((int)$p['paid'] === 0 && $p['dead_line'] >= '1000-01-01' && substr($p['dead_line'],0,10) <= $due_date) {
                $amounts[$sid]['due'] += (int)round((float)$p['amount'] * 100);
            }
            // Actual receipt date matches the recovery collection report; old rows may only have paid_date.
            $date = !empty($p['actual_paid_date']) && $p['actual_paid_date'] >= '1000-01-01' ? $p['actual_paid_date'] : $p['paid_date'];
            if ((int)$p['paid'] !== 1 || substr((string)$date,0,7) !== $month) continue;
            $key = $sid . ':' . (!empty($p['merged_challan']) ? 'm:'.$p['merged_challan'] : 's:'.$p['id']);
            $value = (int)round((float)$p['actual_amount'] * 100);
            if (!isset($receipts[$key])) $receipts[$key] = array('student_id'=>$sid, 'amount'=>$value);
            else $receipts[$key]['amount'] = max($receipts[$key]['amount'], $value);
        }
        foreach ($receipts as $r) $amounts[$r['student_id']]['recovery'] += $r['amount'];
        $courses = array(); $batches = array(); $grand = array();
        foreach ($students as $s) {
            $cid = (int)$s['course_id'];
            $batch = trim((string)$s['session']);
            if ($batch === '') $batch = 'Unassigned';
            $batches[$batch] = true;
            if (!isset($courses[$cid])) $courses[$cid] = array('course_id'=>$cid,'course_name'=>$s['course_name'],'students'=>array(),'totals'=>array());
            $a = $amounts[(int)$s['student_id']];
            $courses[$cid]['students'][] = array('student_id'=>(int)$s['student_id'],'roll_no'=>$s['roll_no'], 'name'=>trim($s['first_name'].' '.$s['last_name']), 'phone'=>$s['mobile'], 'batch'=>$batch,'due'=>$a['due']/100,'recovery'=>$a['recovery']/100);
            foreach (array('due','recovery') as $key) {
                if (!isset($courses[$cid]['totals'][$batch][$key])) $courses[$cid]['totals'][$batch][$key]=0;
                if (!isset($grand[$batch][$key])) $grand[$batch][$key]=0;
                $courses[$cid]['totals'][$batch][$key] += $a[$key];
                $grand[$batch][$key] += $a[$key];
            }
        }
        foreach ($courses as &$course) {
            foreach ($course['totals'] as &$total) { $total['due']/=100; $total['recovery']/=100; } unset($total);
        } unset($course);
        foreach ($grand as &$total) { $total['due']/=100; $total['recovery']/=100; } unset($total);
        $batches = array_keys($batches); natcasesort($batches);
        return array('batches'=>array_values($batches),'courses'=>array_values($courses),'totals'=>$grand,'student_count'=>count($students),'month'=>$month,'due_date'=>$due_date);
    }
}
