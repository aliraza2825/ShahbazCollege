<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('pos_api_token_from_request')) {
	function pos_api_token_from_request($input = null)
	{
		$token = isset($_SERVER['HTTP_X_POS_TOKEN']) ? $_SERVER['HTTP_X_POS_TOKEN'] : '';
		if ($token === '' && isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
			$token = $m[1];
		}
		if (($token === '' || $token === null) && $input) {
			$token = $input->get_request_header('X-Pos-Token', TRUE);
		}
		if (($token === '' || $token === null) && $input) {
			$token = $input->get('pos_token');
		}
		return $token ? $token : null;
	}
}

if (!function_exists('pos_user_is_admin')) {
	function pos_user_is_admin($user)
	{
		return is_array($user) && isset($user['role']) && $user['role'] === 'Admin';
	}
}

if (!function_exists('pos_user_can_use_spa')) {
	/** Non-admin staff are routed to the new portal from the legacy login. */
	function pos_user_can_use_spa($user)
	{
		return is_array($user) && isset($user['role']) && $user['role'] !== 'Admin';
	}
}

if (!function_exists('pos_spa_login_denied_message')) {
	function pos_spa_login_denied_message()
	{
		return 'Could not open the new system. Please try again or contact support.';
	}
}

if (!function_exists('pos_app_base_url')) {
	function pos_app_base_url()
	{
		$base = getenv('POS_APP_URL');
		if (!$base) {
			$base = 'https://pos.shahbazcollegeofpharmacy.edu.pk';
		}
		return rtrim($base, '/');
	}
}

if (!function_exists('pos_issue_sso_login_url')) {
	/**
	 * Issue SPA token and return login URL.
	 * $non_admin_only=true: legacy login sends only staff to the new portal (Admin stays on old).
	 */
	function pos_issue_sso_login_url($db, $user_id, $non_admin_only = false)
	{
		$user = $db->get_where('users', array('user_id' => (int)$user_id, 'status' => '1'))->row_array();
		if (!$user) {
			return null;
		}
		if ($non_admin_only && pos_user_is_admin($user)) {
			return null;
		}
		$token = bin2hex(openssl_random_pseudo_bytes(32));
		$expires = date('Y-m-d H:i:s', strtotime('+12 hours'));
		$db->where('user_id', $user['user_id'])->delete('pos_api_tokens');
		$db->insert('pos_api_tokens', array(
			'user_id' => $user['user_id'],
			'token' => $token,
			'expires_at' => $expires,
		));
		return pos_app_base_url() . '/login?sso=' . rawurlencode($token);
	}
}

if (!function_exists('pos_issue_legacy_entry_url')) {
	/** One-time link for Admin signing in on new portal → old dashboard session. */
	function pos_issue_legacy_entry_url($db, $user_id)
	{
		$user = $db->get_where('users', array('user_id' => (int)$user_id, 'status' => '1'))->row_array();
		if (!pos_user_is_admin($user)) {
			return null;
		}
		$token = bin2hex(openssl_random_pseudo_bytes(32));
		$expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
		$db->where('user_id', $user['user_id'])->delete('pos_api_tokens');
		$db->insert('pos_api_tokens', array(
			'user_id' => $user['user_id'],
			'token' => $token,
			'expires_at' => $expires,
		));
		return site_url('login/pos_entry?sso=' . rawurlencode($token));
	}
}

if (!function_exists('pos_legacy_controller_is_exempt')) {
	/**
	 * Controllers that must stay reachable without Admin (APIs, login, IPN, public).
	 */
	function pos_legacy_controller_is_exempt($class)
	{
		$class = strtolower((string) $class);
		if ($class === '') {
			return true;
		}
		$exact = array(
			'login',
			'paypro',
			'whatsapp',
			'webhooks',
			'api',
			'adminapi',
			'collegeapi',
			'publicwebsiteapi',
			'mobileappapi',
			'welcome',
			'cron',
		);
		if (in_array($class, $exact, true)) {
			return true;
		}
		// Posapi, Studentsapi, Dashboardapi, …
		if (substr($class, -3) === 'api') {
			return true;
		}
		return false;
	}
}

if (!function_exists('pos_force_non_admin_to_spa')) {
	/**
	 * Non-Admin staff must not use legacy CI UI (bookmarked URLs).
	 * Destroys legacy session and sends them to the new POS portal.
	 * Returns true if a redirect was issued.
	 */
	function pos_force_non_admin_to_spa($ci = null)
	{
		if ($ci === null) {
			$ci =& get_instance();
		}
		if (!isset($ci->session) || !$ci->session->userdata('logged_in')) {
			return false;
		}
		if ($ci->session->userdata('role') === 'Admin') {
			return false;
		}

		$class = '';
		if (isset($ci->router)) {
			$class = $ci->router->fetch_class();
		}
		if (pos_legacy_controller_is_exempt($class)) {
			return false;
		}

		$user_id = (int) $ci->session->userdata('user_id');
		$sso = null;
		if ($user_id > 0 && isset($ci->db)) {
			$sso = pos_issue_sso_login_url($ci->db, $user_id, true);
		}
		$ci->session->sess_destroy();
		if ($sso) {
			redirect($sso);
			return true;
		}
		redirect(pos_app_base_url() . '/login');
		return true;
	}
}

if (!function_exists('pos_clear_legacy_session_for_spa_handoff')) {
	/** After issuing SPA SSO for staff, drop CI session so bookmarks cannot reopen legacy. */
	function pos_clear_legacy_session_for_spa_handoff($ci = null)
	{
		if ($ci === null) {
			$ci =& get_instance();
		}
		if (isset($ci->session)) {
			$ci->session->sess_destroy();
		}
	}
}

if (!function_exists('pos_api_auth_user')) {
	/** Resolve active user from POS API token (Admin and staff). */
	function pos_api_auth_user($db, $input = null)
	{
		$token = pos_api_token_from_request($input);
		if (!$token) {
			return null;
		}
		$row = $db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) {
			return null;
		}
		$user = $db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
		return $user ? $user : null;
	}
}
