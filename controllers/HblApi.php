<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HblApi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hbl_model');
        $this->load->config('hbl');
        date_default_timezone_set('Asia/Karachi');
    }

    private function jsonResponse($data = [], $httpCode = 200)
    {
        $this->output->set_status_header($httpCode);
        $this->output->set_content_type('application/json');

        $pretty = $this->config->item('hbl_return_json_pretty');
        $jsonOptions = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : 0;

        $this->output->set_output(json_encode($data, $jsonOptions));
    }

    private function getJsonInput()
    {
        $raw = $this->input->raw_input_stream;
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $data = $this->input->post(NULL, true);
        }

        return is_array($data) ? $data : [];
    }

    private function getBearerToken()
    {
        $headers = $this->input->request_headers();

        if (!empty($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (!empty($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        } elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } else {
            $authHeader = '';
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function validateToken()
    {
        $token = $this->getBearerToken();
        $expectedToken = trim($this->config->item('hbl_api_token'));

        if (empty($token) || $token !== $expectedToken) {
            $this->jsonResponse([
                'ReturnValue' => '2',
                'message' => 'INVALID_USERNAME_OR_PASSWORD'
            ], 401);
            exit;
        }
    }

    private function sanitizeString($value)
    {
        return trim((string)$value);
    }

    private function todayDate()
    {
        return date('Y-m-d');
    }

    private function nowDateTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Inquiry API
     * Inputs:
     * - p_ConsumerNumber
     * Header:
     * - Authorization: Bearer YOUR_SECRET_HBL_TOKEN
     */
    public function inquiry()
    {
        try {
            $this->validateToken();
            $input = $this->getJsonInput();

            $consumerNumber = isset($input['p_ConsumerNumber']) ? $this->sanitizeString($input['p_ConsumerNumber']) : '';

            if ($consumerNumber === '') {
                return $this->jsonResponse([
                    'ReturnValue' => '3',
                    'message' => 'INCORRECT_CONSUMER_NO'
                ]);
            }

            $bill = $this->Hbl_model->get_bill_by_consumer_no($consumerNumber);

            if (empty($bill)) {
                return $this->jsonResponse([
                    'ReturnValue' => '3',
                    'message' => 'INCORRECT_CONSUMER_NO'
                ]);
            }

            if ((int)$bill['paid'] === 1) {
                return $this->jsonResponse([
                    'ReturnValue' => '4',
                    'message' => 'ALREADY_PAID',
                    'p_CustomerName' => $bill['customer_name'],
                    'p_AmountBeforeDueDate' => (string)$bill['amount'],
                    'p_BillingMonth' => !empty($bill['dead_line']) ? date('Y-m-01', strtotime($bill['dead_line'])) : null,
                    'p_DueDate' => !empty($bill['dead_line']) ? date('Y-m-d', strtotime($bill['dead_line'])) : null,
                    'p_AmountAfterDueDate' => (string)$bill['amount']
                ]);
            }

            // Agar aap due date expire hone par block karna chahte ho to ye use karo:
            // if (!empty($bill['dead_line']) && strtotime($bill['dead_line']) < strtotime(date('Y-m-d'))) {
            //     return $this->jsonResponse([
            //         'ReturnValue' => '5',
            //         'message' => 'BILL_BLOCK'
            //     ]);
            // }

            return $this->jsonResponse([
                'ReturnValue' => '0',
                'message' => 'SUCCESS',
                'p_CustomerName' => $bill['customer_name'],
                'p_AmountBeforeDueDate' => (string)$bill['amount'],
                'p_BillingMonth' => !empty($bill['dead_line']) ? date('Y-m-01', strtotime($bill['dead_line'])) : null,
                'p_DueDate' => !empty($bill['dead_line']) ? date('Y-m-d', strtotime($bill['dead_line'])) : null,
                'p_AmountAfterDueDate' => (string)$bill['amount']
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'ReturnValue' => '1',
                'message' => 'SYSTEM_ERROR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment API
     * Inputs:
     * - p_TransactionId
     * - p_ReferenceNumber
     * - p_ConsumerNumber
     * - p_Amount
     */
    public function payment()
    {
        try {
            $this->validateToken();
            $input = $this->getJsonInput();

            $transactionId   = isset($input['p_TransactionId']) ? $this->sanitizeString($input['p_TransactionId']) : '';
            $referenceNumber = isset($input['p_ReferenceNumber']) ? $this->sanitizeString($input['p_ReferenceNumber']) : '';
            $consumerNumber  = isset($input['p_ConsumerNumber']) ? $this->sanitizeString($input['p_ConsumerNumber']) : '';
            $amount          = isset($input['p_Amount']) ? (float)$input['p_Amount'] : 0;

            if ($transactionId === '' || $referenceNumber === '' || $consumerNumber === '' || $amount <= 0) {
                return $this->jsonResponse([
                    'ReturnValue' => '1',
                    'message' => 'EXCEPTION',
                    'detail' => 'Missing required parameters'
                ], 400);
            }

            $bill = $this->Hbl_model->get_bill_by_consumer_no($consumerNumber);

            if (empty($bill)) {
                return $this->jsonResponse([
                    'ReturnValue' => '3',
                    'message' => 'INCORRECT_CONSUMER_NO'
                ]);
            }

            if ((int)$bill['paid'] === 1) {
                return $this->jsonResponse([
                    'ReturnValue' => '4',
                    'message' => 'ALREADY_PAID'
                ]);
            }

            // duplicate check by HBL transaction id
            $dupTxn = $this->Hbl_model->get_by_transaction_id($transactionId);
            if (!empty($dupTxn)) {
                return $this->jsonResponse([
                    'ReturnValue' => '4',
                    'message' => 'ALREADY_PAID',
                    'detail' => 'Duplicate transaction id'
                ]);
            }

            // duplicate check by HBL reference no
            $dupRef = $this->Hbl_model->get_by_reference_number($referenceNumber);
            if (!empty($dupRef)) {
                return $this->jsonResponse([
                    'ReturnValue' => '4',
                    'message' => 'ALREADY_PAID',
                    'detail' => 'Duplicate reference number'
                ]);
            }

            $dbAmount = (float)$bill['amount'];
            if (round($dbAmount, 2) != round($amount, 2)) {
                return $this->jsonResponse([
                    'ReturnValue' => '5',
                    'message' => 'BILL_BLOCK',
                    'detail' => 'Amount mismatch'
                ]);
            }

            $updateData = [
                'paid'             => 1,
                'paid_date'        => $this->todayDate(),
                'actual_paid_date' => $this->todayDate(),
                'tid_no'           => $transactionId,
                'bank_challan_no'  => $referenceNumber,
                'fee_pay_through'  => 'HBL',
                'bank_details'     => 'HBL API',
                'paid_by'          => 'HBL'
            ];

            $updated = $this->Hbl_model->mark_bill_paid($consumerNumber, $updateData);

            if (!$updated) {
                return $this->jsonResponse([
                    'ReturnValue' => '1',
                    'message' => 'EXCEPTION',
                    'detail' => 'Unable to update payment record'
                ], 500);
            }

            return $this->jsonResponse([
                'ReturnValue' => '0',
                'message' => 'SUCCESS'
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'ReturnValue' => '1',
                'message' => 'EXCEPTION',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse API
     * Inputs:
     * - p_OriginalTransactionId
     * - p_ReverseTransactionId
     */
    public function reverse()
    {
        try {
            $this->validateToken();
            $input = $this->getJsonInput();

            $originalTransactionId = isset($input['p_OriginalTransactionId']) ? $this->sanitizeString($input['p_OriginalTransactionId']) : '';
            $reverseTransactionId  = isset($input['p_ReverseTransactionId']) ? $this->sanitizeString($input['p_ReverseTransactionId']) : '';

            if ($originalTransactionId === '' || $reverseTransactionId === '') {
                return $this->jsonResponse([
                    'ReturnValue' => '1',
                    'message' => 'EXCEPTION',
                    'detail' => 'Missing required parameters'
                ], 400);
            }

            // Agar reverse transaction id already use ho chuki hai
            $alreadyReversed = $this->Hbl_model->reverse_id_exists($reverseTransactionId);
            if ($alreadyReversed) {
                return $this->jsonResponse([
                    'ReturnValue' => '6',
                    'message' => 'ALREADY_REVERSED'
                ]);
            }

            $bill = $this->Hbl_model->get_by_transaction_id($originalTransactionId);

            if (empty($bill)) {
                return $this->jsonResponse([
                    'ReturnValue' => '7',
                    'message' => 'ORIGINAL_TXN_NOT_FOUND'
                ]);
            }

            if ((int)$bill['paid'] !== 1) {
                return $this->jsonResponse([
                    'ReturnValue' => '6',
                    'message' => 'ALREADY_REVERSED'
                ]);
            }

            $this->db->trans_begin();

            $updateData = [
                'paid'             => 0,
                'paid_date'        => NULL,
                'actual_paid_date' => NULL,
                'bank_details'     => 'HBL API REVERSED',
                'paid_by'          => NULL
            ];

            // tid_no aur bank_challan_no clear karne hain ya nahi:
            // Agar history preserve karni hai to tid_no clear na karo.
            // Main yahan bill unpaid kar raha hoon aur reverse log alag table me save kar raha hoon.
            $updated = $this->Hbl_model->mark_bill_unpaid_by_transaction_id($originalTransactionId, $updateData);

            if (!$updated) {
                $this->db->trans_rollback();
                return $this->jsonResponse([
                    'ReturnValue' => '1',
                    'message' => 'EXCEPTION',
                    'detail' => 'Unable to reverse payment'
                ], 500);
            }

            $logData = [
                'original_transaction_id' => $originalTransactionId,
                'reverse_transaction_id'  => $reverseTransactionId,
                'payment_id'              => $bill['id'],
                'created_at'              => $this->nowDateTime()
            ];

            $this->Hbl_model->insert_reverse_log($logData);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return $this->jsonResponse([
                    'ReturnValue' => '1',
                    'message' => 'EXCEPTION'
                ], 500);
            }

            $this->db->trans_commit();

            return $this->jsonResponse([
                'ReturnValue' => '0',
                'message' => 'SUCCESS'
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'ReturnValue' => '1',
                'message' => 'EXCEPTION',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}