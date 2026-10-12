<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Use stored fee rows, never a fee label or original amount supplied by the browser.
function student_payment_amount_error($amount, $rows)
{
    if (!is_scalar($amount) || is_bool($amount) || !is_numeric($amount) || !is_finite((float)$amount)) {
        return 'Enter a valid received amount.';
    }
    $amount = (float)$amount;
    if ($amount < 0 || $amount > 2147483647 || ($amount > 0 && $amount < 1)) {
        return 'Received amount must be at least Rs 1.';
    }
    if ($amount > 0) return null;

    if (!count($rows)) return 'Select a fee before submitting payment.';
    foreach ($rows as $row) {
        if (strtolower(trim((string)$row['payment_comment'])) !== 're-admission fee'
            || (float)$row['amount'] !== 0.0
            || (float)$row['remaining_installment_amount'] !== 0.0
            || (float)$row['extra_amount'] !== 0.0
            || (float)$row['fine_amount'] !== 0.0) {
            return 'Payment cannot be Rs 0. Only a zero-amount Re-Admission Fee with no outstanding dues can be paid at Rs 0.';
        }
    }
    return null;
}

function student_payment_selection_error($rows, $ids, $student_id)
{
    if (!$ids || count($rows) !== count($ids)) return 'One or more selected fees were not found. Refresh and try again.';
    foreach ($rows as $row) {
        if ((int)$row['student_id'] !== (int)$student_id || (int)$row['paid'] !== 0) {
            return 'Select only unpaid fees belonging to this student. Refresh and try again.';
        }
    }
    return null;
}
