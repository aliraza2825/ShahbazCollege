<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Meta WhatsApp Cloud API webhook.
 * URL: /index.php/whatsapp/webhook
 */
class Whatsapp extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('whatsapp', true);
	}

	public function webhook()
	{
		$method = strtoupper($this->input->method());
		if ($method === 'GET') {
			$this->_verify_webhook();
		}
		if ($method === 'POST') {
			$this->_handle_webhook();
		}
		http_response_code(405);
		exit;
	}

	private function _verify_token()
	{
		$token = $this->config->item('verify_token', 'whatsapp');
		return is_string($token) && $token !== '' ? $token : 'ShahbazWA2026Verify';
	}

	private function _verify_webhook()
	{
		$mode = (string) $this->input->get('hub_mode');
		$token = (string) $this->input->get('hub_verify_token');
		$challenge = $this->input->get('hub_challenge');

		if ($mode === 'subscribe' && hash_equals($this->_verify_token(), $token) && $challenge !== null && $challenge !== '') {
			http_response_code(200);
			header('Content-Type: text/plain; charset=utf-8');
			echo $challenge;
			exit;
		}

		http_response_code(403);
		exit;
	}

	private function _handle_webhook()
	{
		$raw = file_get_contents('php://input');
		if ($raw !== false && $raw !== '') {
			@file_put_contents(
				APPPATH . 'logs/whatsapp_webhook.log',
				date('Y-m-d H:i:s') . ' ' . $raw . PHP_EOL,
				FILE_APPEND | LOCK_EX
			);
		}

		http_response_code(200);
		header('Content-Type: text/plain; charset=utf-8');
		echo 'EVENT_RECEIVED';
		exit;
	}
}
