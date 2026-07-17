<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modern POS JSON API for React frontend (pos subdomain)
 * Base: /index.php/posapi/{method}
 */
class Posapi extends CI_Controller {

	private $current_user = null;

	public function __construct()
	{
		parent::__construct();
		$this->_cors();

		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(204);
			exit;
		}

		$method = $this->router->method;
		$public = array('login', 'ping');
		if (!in_array($method, $public)) {
			$this->current_user = $this->_auth_user();
			if (!$this->current_user) {
				$this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
			}
		}
	}

	private function _cors()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		$allowed = array(
			'http://localhost:5173',
			'http://localhost:4173',
			'http://127.0.0.1:5173',
			'https://pos.shahbazcollegeofpharmacy.edu.pk',
			'http://pos.shahbazcollegeofpharmacy.edu.pk',
		);
		if ($origin === '*' || in_array($origin, $allowed)) {
			header('Access-Control-Allow-Origin: ' . ($origin === '*' ? '*' : $origin));
		} elseif (preg_match('/^https?:\\/\\/(localhost|127\\.0\\.0\\.1)(:\\d+)?$/', $origin)) {
			header('Access-Control-Allow-Origin: ' . $origin);
		}
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Pos-Token');
		header('Access-Control-Allow-Credentials: true');
	}

	private function _json($data, $code = 200)
	{
		http_response_code($code);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

	private function _body()
	{
		$raw = file_get_contents('php://input');
		$json = json_decode($raw, true);
		if (is_array($json) && count($json)) {
			return $json;
		}
		return $this->input->post() ? $this->input->post() : array();
	}

	private function _token_from_request()
	{
		$header = isset($_SERVER['HTTP_X_POS_TOKEN']) ? $_SERVER['HTTP_X_POS_TOKEN'] : '';
		if ($header === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
			if (preg_match('/Bearer\\s+(\\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
				$header = $m[1];
			}
		}
		if ($header === '') {
			$header = $this->input->get_request_header('X-Pos-Token', TRUE);
		}
		return $header;
	}

	private function _auth_user()
	{
		$token = $this->_token_from_request();
		if (!$token) {
			return null;
		}
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) {
			return null;
		}
		$user = $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
		return $user ? $user : null;
	}

	private function _pos_access_row($user = null)
	{
		$user = $user ? $user : $this->current_user;
		if (!$user) return null;
		// CodeIgniter Access module (`access` table) — not React-side table
		return $this->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
	}

	private function _is_admin($user = null)
	{
		$user = $user ? $user : $this->current_user;
		return $user && isset($user['role']) && $user['role'] === 'Admin';
	}

	/**
	 * Resolved POS permissions from CI Access module
	 */
	private function _permissions($user = null)
	{
		$user = $user ? $user : $this->current_user;
		$is_admin = $this->_is_admin($user);
		$row = $this->_pos_access_row($user);

		$campus_ids = array();
		if ($is_admin) {
			$all = $this->db->select('campus_id')->get_where('campuses', array('status' => 1))->result_array();
			foreach ($all as $c) {
				$campus_ids[] = (int)$c['campus_id'];
			}
		} elseif ($row && !empty($row['pos_campuses'])) {
			foreach (explode(',', $row['pos_campuses']) as $id) {
				$id = (int)trim($id);
				if ($id > 0) $campus_ids[] = $id;
			}
		} elseif ($user && !empty($user['campus_id'])) {
			// fallback: home campus only if POS access granted
			if ($row && !empty($row['pos'])) {
				$campus_ids[] = (int)$user['campus_id'];
			}
		}

		$campus_ids = array_values(array_unique($campus_ids));

		$has_pos = $is_admin || ($row && !empty($row['pos']));
		$has_inventory = $is_admin || ($row && !empty($row['inventory']));

		$inventory_campus_ids = array();
		if ($is_admin) {
			$inventory_campus_ids = $campus_ids;
		} elseif ($row && !empty($row['inventory_campuses'])) {
			foreach (explode(',', $row['inventory_campuses']) as $id) {
				$id = (int)trim($id);
				if ($id > 0) $inventory_campus_ids[] = $id;
			}
		} elseif ($has_inventory && $user && !empty($user['campus_id'])) {
			$inventory_campus_ids[] = (int)$user['campus_id'];
		}
		$inventory_campus_ids = array_values(array_unique($inventory_campus_ids));

		return array(
			'is_admin' => $is_admin,
			'campus_ids' => $campus_ids,
			'can_sell' => $has_pos,
			'can_manage_categories' => $is_admin || ($row && !empty($row['pos_manage_categories'])),
			'can_manage_bundles' => $is_admin || ($row && !empty($row['pos_manage_bundles'])),
			'can_view_history' => $is_admin || ($row && !empty($row['pos_view_history'])),
			'can_manage_access' => $is_admin, // always via CI /access
			'can_inventory' => $has_inventory,
			'inventory_campus_ids' => $inventory_campus_ids,
		);
	}

	private function _require_perm($key)
	{
		$perms = $this->_permissions();
		if (empty($perms[$key])) {
			$this->_json(array('success' => false, 'message' => 'Permission denied'), 403);
		}
		return $perms;
	}

	private function _require_campus_access($campus_id)
	{
		$perms = $this->_permissions();
		$campus_id = (int)$campus_id;
		if ($campus_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'Campus required'), 422);
		}
		if ($perms['is_admin']) return $perms;
		if (!in_array($campus_id, $perms['campus_ids'], true)) {
			$this->_json(array('success' => false, 'message' => 'No access to this campus'), 403);
		}
		return $perms;
	}

	private function _user_payload($user)
	{
		$this->_ensure_pos_closing_campus_column();
		$campus = $this->db->get_where('campuses', array('campus_id' => $user['campus_id']))->row_array();
		$perms = $this->_permissions($user);

		$closing_campus_id = 0;
		if (isset($user['pos_closing_campus_id'])) {
			$closing_campus_id = (int)$user['pos_closing_campus_id'];
		} else {
			$row = $this->db->select('pos_closing_campus_id')->get_where('users', array('user_id' => $user['user_id']))->row_array();
			$closing_campus_id = $row ? (int)$row['pos_closing_campus_id'] : 0;
		}

		$closing_campus = null;
		if ($closing_campus_id > 0) {
			$closing_campus = $this->db->get_where('campuses', array('campus_id' => $closing_campus_id))->row_array();
		}

		return array(
			'user_id' => $user['user_id'],
			'name' => trim($user['first_name'] . ' ' . $user['last_name']),
			'username' => $user['username'],
			'role' => $user['role'],
			'campus_id' => $user['campus_id'],
			'campus_name' => $campus ? $campus['campus_name'] : '',
			'closing_campus_id' => $closing_campus_id > 0 ? $closing_campus_id : null,
			'closing_campus_name' => $closing_campus ? $closing_campus['campus_name'] : '',
			'permissions' => $perms,
		);
	}

	private function _ensure_pos_closing_campus_column()
	{
		if (!$this->db->table_exists('users')) {
			return;
		}
		if ($this->db->field_exists('pos_closing_campus_id', 'users')) {
			return;
		}
		$this->db->query("ALTER TABLE `users` ADD `pos_closing_campus_id` INT(11) NULL DEFAULT NULL");
	}

	/** Same campuses as /closing/index for this user — from closing_persons */
	private function _closing_campus_rows($user = null)
	{
		$user = $user ? $user : $this->current_user;
		if (!$user) {
			return array();
		}

		$is_admin = $this->_is_admin($user);
		$access = $this->_pos_access_row($user);
		$view_campus_closings = $access && !empty($access['view_campus_closings']) ? (string)$access['view_campus_closings'] : '0';
		$campus_closing_ids = $access && !empty($access['campus_closing_ids']) ? $access['campus_closing_ids'] : '';

		$this->db->select('campuses.campus_id, campuses.campus_name, campuses.roll_no_code, campuses.status, closing_persons.id as closing_person_id');
		$this->db->from('closing_persons');
		$this->db->join('campuses', 'campuses.campus_id = closing_persons.campus_id', 'inner');
		$this->db->where('campuses.status', 1);

		if ($is_admin) {
			$this->db->where('closing_persons.active_status', 1);
		} elseif ($view_campus_closings === '1' && $campus_closing_ids !== '') {
			$ids = array();
			foreach (explode(',', $campus_closing_ids) as $id) {
				$id = (int)trim($id);
				if ($id > 0) $ids[] = $id;
			}
			if (!count($ids)) {
				return array();
			}
			$this->db->where_in('closing_persons.id', $ids);
		} else {
			// Exact Closing::index rule for normal staff
			$this->db->where('closing_persons.user_id', (int)$user['user_id']);
			$this->db->where('closing_persons.active_status', 1);
		}

		$this->db->group_by('closing_persons.campus_id');
		$this->db->order_by('campuses.campus_name', 'ASC');
		return $this->db->get()->result_array();
	}

	private function _is_allowed_closing_campus($campus_id, $user = null)
	{
		$campus_id = (int)$campus_id;
		if ($campus_id <= 0) {
			return false;
		}
		foreach ($this->_closing_campus_rows($user) as $row) {
			if ((int)$row['campus_id'] === $campus_id) {
				return true;
			}
		}
		return false;
	}

	public function ping()
	{
		$this->_json(array('success' => true, 'message' => 'POS API OK', 'time' => date('c')));
	}

	public function login()
	{
		$body = $this->_body();
		$username = isset($body['username']) ? trim($body['username']) : '';
		$password = isset($body['password']) ? md5($body['password']) : '';

		$user = $this->db->get_where('users', array(
			'username' => $username,
			'password' => $password,
			'status' => '1'
		))->row_array();

		if (!$user) {
			$this->_json(array('success' => false, 'message' => 'Invalid username or password'), 401);
		}

		// Non-admin must have POS Access checkbox in CI Access module
		if ($user['role'] !== 'Admin') {
			$acc = $this->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
			if (!$acc || empty($acc['pos'])) {
				$this->_json(array('success' => false, 'message' => 'No POS access. Ask admin to enable POS in Access.'), 403);
			}
		}

		$token = bin2hex(openssl_random_pseudo_bytes(32));
		$expires = date('Y-m-d H:i:s', strtotime('+12 hours'));

		$this->db->where('user_id', $user['user_id']);
		$this->db->delete('pos_api_tokens');

		$this->db->insert('pos_api_tokens', array(
			'user_id' => $user['user_id'],
			'token' => $token,
			'expires_at' => $expires
		));

		$this->_json(array(
			'success' => true,
			'token' => $token,
			'expires_at' => $expires,
			'user' => $this->_user_payload($user),
		));
	}

	public function me()
	{
		$this->_json(array(
			'success' => true,
			'user' => $this->_user_payload($this->current_user),
		));
	}

	public function logout()
	{
		$token = $this->_token_from_request();
		if ($token) {
			$this->db->where('token', $token)->delete('pos_api_tokens');
		}
		$this->_json(array('success' => true));
	}

	public function my_permissions()
	{
		$this->_json(array('success' => true, 'data' => $this->_permissions()));
	}

	private function _pos_image_url($filename)
	{
		$filename = trim((string)$filename);
		if ($filename === '') return null;
		if (preg_match('#^https?://#i', $filename)) return $filename;
		return rtrim(base_url(), '/') . '/pos_images/' . rawurlencode($filename);
	}

	private function _decorate_images(&$rows)
	{
		foreach ($rows as &$row) {
			$row['image_url'] = $this->_pos_image_url(isset($row['image']) ? $row['image'] : '');
		}
	}

	/**
	 * Upload image for category / bundle (multipart field: image)
	 */
	public function upload_image()
	{
		$perms = $this->_permissions();
		if (empty($perms['can_manage_categories']) && empty($perms['can_manage_bundles'])) {
			$this->_json(array('success' => false, 'message' => 'Permission denied'), 403);
		}

		$dir = FCPATH . 'pos_images/';
		if (!is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}

		$filename = '';

		// Multipart file
		if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
			$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
			$allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
			if (!in_array($ext, $allowed)) {
				$this->_json(array('success' => false, 'message' => 'Only jpg, png, gif, webp allowed'), 422);
			}
			if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
				$this->_json(array('success' => false, 'message' => 'Max 5MB image'), 422);
			}
			$filename = 'pos_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
			if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
				$this->_json(array('success' => false, 'message' => 'Upload failed'), 500);
			}
		} else {
			// Base64 fallback: { image_base64, filename }
			$body = $this->_body();
			$b64 = isset($body['image_base64']) ? $body['image_base64'] : '';
			if ($b64 === '') {
				$this->_json(array('success' => false, 'message' => 'No image uploaded'), 422);
			}
			if (preg_match('#^data:image/(\w+);base64,#', $b64, $m)) {
				$ext = strtolower($m[1]);
				if ($ext === 'jpeg') $ext = 'jpg';
				$b64 = substr($b64, strpos($b64, ',') + 1);
			} else {
				$ext = 'jpg';
			}
			$allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
			if (!in_array($ext, $allowed)) {
				$this->_json(array('success' => false, 'message' => 'Invalid image type'), 422);
			}
			$bin = base64_decode($b64);
			if ($bin === false || strlen($bin) < 10) {
				$this->_json(array('success' => false, 'message' => 'Invalid image data'), 422);
			}
			if (strlen($bin) > 5 * 1024 * 1024) {
				$this->_json(array('success' => false, 'message' => 'Max 5MB image'), 422);
			}
			$filename = 'pos_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
			if (file_put_contents($dir . $filename, $bin) === false) {
				$this->_json(array('success' => false, 'message' => 'Upload failed'), 500);
			}
		}

		$this->_json(array(
			'success' => true,
			'filename' => $filename,
			'image_url' => $this->_pos_image_url($filename),
		));
	}

	/* ---------- Catalog ---------- */

	public function categories()
	{
		$campus_id = (int)$this->input->get('campus_id');

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$name = isset($body['name']) ? trim($body['name']) : '';
			$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
			if ($name === '') {
				$this->_json(array('success' => false, 'message' => 'Name required'), 422);
			}
			$this->_require_perm('can_manage_categories');
			$this->_require_campus_access($campus_id);
			$slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
			$this->db->insert('pos_categories', array(
				'campus_id' => $campus_id,
				'name' => $name,
				'slug' => $slug,
				'sort_order' => isset($body['sort_order']) ? (int)$body['sort_order'] : 0,
				'status' => 1,
				'image' => isset($body['image']) ? $body['image'] : null,
			));
			$this->_json(array('success' => true, 'category_id' => $this->db->insert_id()));
		}

		if ($campus_id > 0) {
			$this->_require_campus_access($campus_id);
		}

		$this->db->from('pos_categories');
		$this->db->where('status', 1);
		$this->db->group_start();
		$this->db->where('slug', 'all');
		if ($campus_id > 0) {
			$this->db->or_where('campus_id', $campus_id);
			$this->db->or_where('campus_id IS NULL', null, false);
		}
		$this->db->group_end();
		$this->db->order_by('sort_order', 'ASC');
		$this->db->order_by('category_id', 'ASC');
		$rows = $this->db->get()->result_array();
		$this->_decorate_images($rows);
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function update_category($category_id = 0)
	{
		$this->_require_perm('can_manage_categories');
		$body = $this->_body();
		$category_id = (int)$category_id;
		if (!$category_id) {
			$this->_json(array('success' => false, 'message' => 'Invalid id'), 422);
		}
		$cat = $this->db->get_where('pos_categories', array('category_id' => $category_id))->row_array();
		if ($cat && !empty($cat['campus_id'])) {
			$this->_require_campus_access($cat['campus_id']);
		}
		$data = array();
		if (isset($body['name'])) {
			$data['name'] = trim($body['name']);
			$data['slug'] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']));
		}
		if (isset($body['campus_id'])) {
			$this->_require_campus_access((int)$body['campus_id']);
			$data['campus_id'] = (int)$body['campus_id'];
		}
		if (isset($body['sort_order'])) $data['sort_order'] = (int)$body['sort_order'];
		if (isset($body['status'])) $data['status'] = (int)$body['status'];
		if (isset($body['image'])) $data['image'] = $body['image'];
		if ($data) {
			$this->db->where('category_id', $category_id)->update('pos_categories', $data);
		}
		$this->_json(array('success' => true));
	}

	public function delete_category($category_id = 0)
	{
		$this->_require_perm('can_manage_categories');
		$category_id = (int)$category_id;
		$cat = $this->db->get_where('pos_categories', array('category_id' => $category_id))->row_array();
		if (!$cat) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		if ($cat['slug'] === 'all') {
			$this->_json(array('success' => false, 'message' => 'Cannot delete All'), 422);
		}
		if (!empty($cat['campus_id'])) {
			$this->_require_campus_access($cat['campus_id']);
		}
		$this->db->where('category_id', $category_id)->update('pos_categories', array('status' => 0));
		$this->_json(array('success' => true));
	}

	/**
	 * Direct items inside a category (not via bundle)
	 */
	public function category_items()
	{
		$category_id = (int)$this->input->get('category_id');
		$campus_id = (int)$this->input->get('campus_id');

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$category_id = isset($body['category_id']) ? (int)$body['category_id'] : 0;
			$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
			$product_name_id = isset($body['product_name_id']) ? (int)$body['product_name_id'] : 0;
			$items = isset($body['items']) && is_array($body['items']) ? $body['items'] : null;

			$this->_require_perm('can_manage_categories');
			if ($campus_id) {
				$this->_require_campus_access($campus_id);
			}

			if (!$category_id) {
				$this->_json(array('success' => false, 'message' => 'Category required'), 422);
			}

			if ($items) {
				$added = 0;
				foreach ($items as $item) {
					$pid = (int)$item['product_name_id'];
					if (!$pid) continue;
					$exists = $this->db->get_where('pos_category_items', array(
						'category_id' => $category_id,
						'product_name_id' => $pid,
					))->row_array();
					if ($exists) continue;
					$this->db->insert('pos_category_items', array(
						'category_id' => $category_id,
						'campus_id' => $campus_id ? $campus_id : null,
						'product_name_id' => $pid,
					));
					$added++;
				}
				$this->_json(array('success' => true, 'added' => $added));
			}

			if (!$product_name_id) {
				$this->_json(array('success' => false, 'message' => 'Product required'), 422);
			}
			$exists = $this->db->get_where('pos_category_items', array(
				'category_id' => $category_id,
				'product_name_id' => $product_name_id,
			))->row_array();
			if ($exists) {
				$this->_json(array('success' => true, 'category_item_id' => $exists['category_item_id'], 'message' => 'Already added'));
			}
			$this->db->insert('pos_category_items', array(
				'category_id' => $category_id,
				'campus_id' => $campus_id ? $campus_id : null,
				'product_name_id' => $product_name_id,
			));
			$this->_json(array('success' => true, 'category_item_id' => $this->db->insert_id()));
		}

		if (!$category_id) {
			$this->_json(array('success' => false, 'message' => 'category_id required'), 422);
		}

		$this->db->select('pos_category_items.*, product_names.product_name, MIN(products.sale_amount) as sale_amount, COUNT(products.product_id) as stock, MAX(NULLIF(products.product_image, "")) as product_image, MIN(products.campus_id) as stock_campus_id, MIN(products.room_id) as room_id, MIN(products.subroom_id) as subroom_id');
		$this->db->from('pos_category_items');
		$this->db->join('product_names', 'product_names.product_name_id = pos_category_items.product_name_id', 'inner');
		$this->db->join('products', 'products.product_name_id = pos_category_items.product_name_id AND products.saleable = 1 AND products.sold = 0 AND products.consume = 0 AND products.status = 1', 'left');
		if ($campus_id > 0) {
			$this->db->where('(products.campus_id = ' . (int)$campus_id . ' OR products.campus_id IS NULL)', null, false);
		}
		$this->db->where('pos_category_items.category_id', $category_id);
		$this->db->group_by('pos_category_items.category_item_id');
		$this->db->order_by('pos_category_items.sort_order', 'ASC');
		$rows = $this->db->get()->result_array();

		$base = rtrim(base_url(), '/');
		foreach ($rows as &$row) {
			$img = isset($row['product_image']) ? trim($row['product_image']) : '';
			$row['image_url'] = $img !== '' ? $base . '/inventory_images/' . rawurlencode($img) : null;
			if ($campus_id > 0) {
				$row['campus_id'] = $campus_id;
			}
		}

		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function remove_category_item($category_item_id = 0)
	{
		$this->_require_perm('can_manage_categories');
		$category_item_id = (int)$category_item_id;
		$this->db->where('category_item_id', $category_item_id)->delete('pos_category_items');
		$this->_json(array('success' => true));
	}

	/**
	 * POS catalog: bundles + direct category items for a campus/category
	 */
	public function catalog()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$category_id = (int)$this->input->get('category_id');
		$q = trim((string)$this->input->get('q'));

		// Bundles
		$this->db->select('pos_bundles.*, pos_categories.name as category_name');
		$this->db->from('pos_bundles');
		$this->db->join('pos_categories', 'pos_categories.category_id = pos_bundles.category_id', 'left');
		$this->db->where('pos_bundles.status', 1);
		if ($campus_id > 0) {
			$this->db->group_start();
			$this->db->where('pos_bundles.campus_id', $campus_id);
			$this->db->or_where('pos_bundles.campus_id IS NULL', null, false);
			$this->db->group_end();
		}
		if ($category_id > 0) {
			$this->db->where('pos_bundles.category_id', $category_id);
		}
		if ($q !== '') {
			$this->db->like('pos_bundles.name', $q);
		}
		$this->db->order_by('pos_bundles.bundle_id', 'DESC');
		$bundles = $this->db->get()->result_array();
		foreach ($bundles as &$b) {
			$b['items'] = $this->_bundle_items($b['bundle_id']);
			$b['item_count'] = count($b['items']);
			$b['type'] = 'bundle';
			$b['image_url'] = $this->_pos_image_url(isset($b['image']) ? $b['image'] : '');
		}

		// Direct category items (only when a category selected, or all categories for campus)
		$items = array();
		if ($category_id > 0 || $campus_id > 0) {
			$this->db->select('pos_category_items.category_item_id, pos_category_items.category_id, pos_category_items.product_name_id, product_names.product_name, pos_categories.name as category_name, MIN(products.sale_amount) as sale_amount, COUNT(products.product_id) as stock, MAX(NULLIF(products.product_image, "")) as product_image, MIN(products.campus_id) as campus_id, MIN(products.room_id) as room_id, MIN(products.subroom_id) as subroom_id');
			$this->db->from('pos_category_items');
			$this->db->join('product_names', 'product_names.product_name_id = pos_category_items.product_name_id', 'inner');
			$this->db->join('pos_categories', 'pos_categories.category_id = pos_category_items.category_id', 'left');
			$this->db->join('products', 'products.product_name_id = pos_category_items.product_name_id AND products.saleable = 1 AND products.sold = 0 AND products.consume = 0 AND products.status = 1', 'left');
			if ($campus_id > 0) {
				$this->db->where('products.campus_id', $campus_id);
			}
			if ($category_id > 0) {
				$this->db->where('pos_category_items.category_id', $category_id);
			} elseif ($campus_id > 0) {
				$this->db->group_start();
				$this->db->where('pos_categories.campus_id', $campus_id);
				$this->db->or_where('pos_category_items.campus_id', $campus_id);
				$this->db->group_end();
			}
			if ($q !== '') {
				$this->db->like('product_names.product_name', $q);
			}
			$this->db->group_by('pos_category_items.category_item_id');
			$this->db->having('COUNT(products.product_id) >', 0);
			$items = $this->db->get()->result_array();
			$base = rtrim(base_url(), '/');
			foreach ($items as &$row) {
				$row['type'] = 'item';
				$img = isset($row['product_image']) ? trim($row['product_image']) : '';
				$row['image_url'] = $img !== '' ? $base . '/inventory_images/' . rawurlencode($img) : null;
			}
		}

		$this->_json(array(
			'success' => true,
			'data' => array(
				'bundles' => $bundles,
				'items' => $items,
			)
		));
	}

	public function bundles()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$name = isset($body['name']) ? trim($body['name']) : '';
			$category_id = isset($body['category_id']) ? (int)$body['category_id'] : 0;
			$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
			$price = isset($body['price']) ? (float)$body['price'] : 0;
			$items = isset($body['items']) && is_array($body['items']) ? $body['items'] : array();

			$this->_require_perm('can_manage_bundles');
			$this->_require_campus_access($campus_id);

			if ($name === '' || !$category_id) {
				$this->_json(array('success' => false, 'message' => 'Name and category required'), 422);
			}
			if (!count($items)) {
				$this->_json(array('success' => false, 'message' => 'Add at least one item'), 422);
			}

			$this->db->trans_start();
			$this->db->insert('pos_bundles', array(
				'category_id' => $category_id,
				'campus_id' => $campus_id,
				'name' => $name,
				'description' => isset($body['description']) ? $body['description'] : '',
				'price' => $price,
				'image' => isset($body['image']) ? $body['image'] : null,
				'status' => 1,
			));
			$bundle_id = $this->db->insert_id();
			foreach ($items as $item) {
				$this->db->insert('pos_bundle_items', array(
					'bundle_id' => $bundle_id,
					'product_name_id' => (int)$item['product_name_id'],
					'quantity' => isset($item['quantity']) ? max(1, (int)$item['quantity']) : 1,
				));
			}
			$this->db->trans_complete();

			$this->_json(array('success' => true, 'bundle_id' => $bundle_id));
		}

		$category_id = (int)$this->input->get('category_id');
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('pos_bundles.*, pos_categories.name as category_name');
		$this->db->from('pos_bundles');
		$this->db->join('pos_categories', 'pos_categories.category_id = pos_bundles.category_id', 'left');
		$this->db->where('pos_bundles.status', 1);
		if ($category_id > 0) {
			$this->db->where('pos_bundles.category_id', $category_id);
		}
		if ($campus_id > 0) {
			$this->db->group_start();
			$this->db->where('pos_bundles.campus_id', $campus_id);
			$this->db->or_where('pos_bundles.campus_id IS NULL', null, false);
			$this->db->group_end();
		}
		$this->db->order_by('pos_bundles.bundle_id', 'DESC');
		$bundles = $this->db->get()->result_array();

		foreach ($bundles as &$b) {
			$b['items'] = $this->_bundle_items($b['bundle_id']);
			$b['item_count'] = count($b['items']);
			$b['image_url'] = $this->_pos_image_url(isset($b['image']) ? $b['image'] : '');
		}

		$this->_json(array('success' => true, 'data' => $bundles));
	}

	public function bundle($bundle_id = 0)
	{
		$bundle_id = (int)$bundle_id;
		$bundle = $this->db->get_where('pos_bundles', array('bundle_id' => $bundle_id))->row_array();
		if (!$bundle) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
			$this->_require_perm('can_manage_bundles');
			if (!empty($bundle['campus_id'])) {
				$this->_require_campus_access($bundle['campus_id']);
			}
			$body = $this->_body();
			$data = array();
			if (isset($body['name'])) $data['name'] = trim($body['name']);
			if (isset($body['category_id'])) $data['category_id'] = (int)$body['category_id'];
			if (isset($body['campus_id'])) {
				$this->_require_campus_access((int)$body['campus_id']);
				$data['campus_id'] = (int)$body['campus_id'];
			}
			if (isset($body['price'])) $data['price'] = (float)$body['price'];
			if (isset($body['description'])) $data['description'] = $body['description'];
			if (isset($body['status'])) $data['status'] = (int)$body['status'];
			if (isset($body['image'])) $data['image'] = $body['image'];

			$this->db->trans_start();
			if ($data) {
				$this->db->where('bundle_id', $bundle_id)->update('pos_bundles', $data);
			}
			if (isset($body['items']) && is_array($body['items'])) {
				$this->db->where('bundle_id', $bundle_id)->delete('pos_bundle_items');
				foreach ($body['items'] as $item) {
					$this->db->insert('pos_bundle_items', array(
						'bundle_id' => $bundle_id,
						'product_name_id' => (int)$item['product_name_id'],
						'quantity' => isset($item['quantity']) ? max(1, (int)$item['quantity']) : 1,
					));
				}
			}
			$this->db->trans_complete();
			$this->_json(array('success' => true));
		}

		$bundle['items'] = $this->_bundle_items($bundle_id);
		$bundle['image_url'] = $this->_pos_image_url(isset($bundle['image']) ? $bundle['image'] : '');
		$this->_json(array('success' => true, 'data' => $bundle));
	}

	public function delete_bundle($bundle_id = 0)
	{
		$this->_require_perm('can_manage_bundles');
		$bundle_id = (int)$bundle_id;
		$bundle = $this->db->get_where('pos_bundles', array('bundle_id' => $bundle_id))->row_array();
		if (!$bundle) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		if (!empty($bundle['campus_id'])) {
			$this->_require_campus_access($bundle['campus_id']);
		}
		$this->db->where('bundle_id', $bundle_id)->update('pos_bundles', array('status' => 0));
		$this->_json(array('success' => true));
	}

	private function _bundle_items($bundle_id)
	{
		$this->db->select('pos_bundle_items.*, product_names.product_name');
		$this->db->from('pos_bundle_items');
		$this->db->join('product_names', 'product_names.product_name_id = pos_bundle_items.product_name_id', 'left');
		$this->db->where('pos_bundle_items.bundle_id', $bundle_id);
		return $this->db->get()->result_array();
	}

	public function products()
	{
		$q = trim((string)$this->input->get('q'));
		$campus_id = (int)$this->input->get('campus_id');

		// Full inventory catalog (optional campus filter). Images from products.product_image
		$this->db->select('product_names.product_name_id, product_names.product_name, MIN(products.sale_amount) as sale_amount, COUNT(products.product_id) as stock, MAX(NULLIF(products.product_image, "")) as product_image, MIN(products.campus_id) as campus_id, MIN(products.room_id) as room_id, MIN(products.subroom_id) as subroom_id');
		$this->db->from('product_names');
		$this->db->join('products', 'products.product_name_id = product_names.product_name_id', 'inner');
		$this->db->where(array(
			'products.saleable' => 1,
			'products.sold' => 0,
			'products.consume' => 0,
			'products.status' => 1,
		));
		if ($campus_id > 0) {
			$this->db->where('products.campus_id', $campus_id);
		}
		if ($q !== '') {
			$this->db->like('product_names.product_name', $q);
		}
		$this->db->group_by('product_names.product_name_id');
		$this->db->order_by('product_names.product_name', 'ASC');
		$this->db->limit(500);
		$rows = $this->db->get()->result_array();

		$base = rtrim(base_url(), '/');
		foreach ($rows as &$row) {
			$img = isset($row['product_image']) ? trim($row['product_image']) : '';
			$row['image_url'] = $img !== '' ? $base . '/inventory_images/' . rawurlencode($img) : null;
		}

		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function product_stock()
	{
		$product_name_id = (int)$this->input->get('product_name_id');
		$campus_id = (int)$this->input->get('campus_id');

		$this->db->select('products.campus_id, products.room_id, products.subroom_id, campuses.campus_name, rooms.room_name, subrooms.subroom_name, products.sale_amount, COUNT(products.product_id) as quantity');
		$this->db->from('products');
		$this->db->join('campuses', 'campuses.campus_id = products.campus_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = products.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = products.subroom_id', 'left');
		$this->db->where(array(
			'products.product_name_id' => $product_name_id,
			'products.saleable' => 1,
			'products.sold' => 0,
			'products.consume' => 0,
			'products.status' => 1,
		));
		if ($campus_id > 0) {
			$this->db->where('products.campus_id', $campus_id);
		}
		$this->db->group_by(array('products.campus_id', 'products.room_id', 'products.subroom_id'));
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/**
	 * Global item location search for POS (campus / room / qty).
	 * Available units only; scoped to user's POS campuses (Admin = all).
	 */
	public function stock_search()
	{
		$q = trim((string)$this->input->get('q'));
		if (strlen($q) < 2) {
			$this->_json(array('success' => true, 'data' => array()));
		}

		$perms = $this->_permissions();
		$campus_id = (int)$this->input->get('campus_id');

		$this->db->select('product_names.product_name_id, product_names.product_name,
			COUNT(products.product_id) as stock,
			products.campus_id, products.room_id, products.subroom_id,
			MAX(campuses.campus_name) as campus_name,
			MAX(rooms.room_name) as room_name,
			MAX(subrooms.subroom_name) as subroom_name', false);
		$this->db->from('products');
		$this->db->join('product_names', 'product_names.product_name_id = products.product_name_id', 'inner');
		$this->db->join('campuses', 'campuses.campus_id = products.campus_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = products.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = products.subroom_id', 'left');
		$this->db->where(array(
			'products.status' => 1,
			'products.sold' => 0,
			'products.consume' => 0,
		));
		$this->db->like('product_names.product_name', $q);
		if ($campus_id > 0) {
			if (!$perms['is_admin'] && !in_array($campus_id, $perms['campus_ids'], true)) {
				$this->_json(array('success' => false, 'message' => 'No access to this campus'), 403);
			}
			$this->db->where('products.campus_id', $campus_id);
		} elseif (!$perms['is_admin']) {
			if (!count($perms['campus_ids'])) {
				$this->_json(array('success' => true, 'data' => array()));
			}
			$this->db->where_in('products.campus_id', $perms['campus_ids']);
		}
		$this->db->group_by(array('products.product_name_id', 'products.campus_id', 'products.room_id', 'products.subroom_id'));
		$this->db->order_by('product_names.product_name', 'ASC');
		$this->db->limit(500);
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function campuses()
	{
		$perms = $this->_permissions();
		$ids = array_values(array_unique(array_merge(
			$perms['campus_ids'],
			$perms['inventory_campus_ids']
		)));
		$this->db->from('campuses');
		$this->db->where('status', 1);
		if (!$perms['is_admin'] && count($ids)) {
			$this->db->where_in('campus_id', $ids);
		} elseif (!$perms['is_admin']) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->order_by('campus_name', 'ASC');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/** Campuses that appear on Daily Closing (active closing_persons) */
	public function closing_campuses()
	{
		$rows = $this->_closing_campus_rows();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/** Persist selected campus for stock + sales (sale posts into that campus closing). */
	public function set_closing_campus()
	{
		$this->_ensure_pos_closing_campus_column();
		$body = $this->_body();
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		if ($campus_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'Campus required'), 422);
		}
		// Any accessible campus (POS / inventory) — not limited to closing_persons.
		$perms = $this->_permissions();
		$allowed = array_values(array_unique(array_merge(
			$perms['campus_ids'],
			$perms['inventory_campus_ids']
		)));
		if (!$perms['is_admin'] && !in_array($campus_id, $allowed, true)) {
			$this->_json(array('success' => false, 'message' => 'No access to this campus'), 403);
		}
		$exists = $this->db->get_where('campuses', array('campus_id' => $campus_id, 'status' => 1))->row_array();
		if (!$exists) {
			$this->_json(array('success' => false, 'message' => 'Campus not found'), 404);
		}

		$this->db->where('user_id', $this->current_user['user_id'])->update('users', array(
			'pos_closing_campus_id' => $campus_id,
		));
		$this->current_user['pos_closing_campus_id'] = $campus_id;

		$this->_json(array(
			'success' => true,
			'message' => 'Campus saved',
			'user' => $this->_user_payload($this->current_user),
		));
	}

	public function courses()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->like('campus_ids', (string)$campus_id);
		$rows = $this->db->get_where('courses', array('status' => 1))->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function classes()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$course_id = (int)$this->input->get('course_id');
		$rows = $this->db->get_where('classes', array(
			'campus_id' => $campus_id,
			'course_id' => $course_id,
			'status' => 1
		))->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function students()
	{
		$class_id = (int)$this->input->get('class_id');
		$q = trim((string)$this->input->get('q'));

		$this->db->select('students.student_id, students.first_name, students.last_name, students.roll_no, students.class_id, students.course_id, students.registration_date, students.status, students.mobile, sd.image as photo, sd.online_image');
		$this->db->from('students');
		$this->db->join(
			'(SELECT student_id, image, online_image FROM student_documents WHERE type = "Photo" GROUP BY student_id) sd',
			'sd.student_id = students.student_id',
			'left'
		);
		$this->db->where('students.status', 1);
		if ($class_id > 0) {
			$this->db->where('students.class_id', $class_id);
		}
		if ($q !== '') {
			$this->db->group_start();
			$this->db->like('students.first_name', $q);
			$this->db->or_like('students.last_name', $q);
			$this->db->or_like('students.roll_no', $q);
			$this->db->group_end();
		}
		$this->db->limit(100);
		$rows = $this->db->get()->result_array();

		$base = rtrim(base_url(), '/');
		foreach ($rows as &$row) {
			$online = isset($row['online_image']) ? trim($row['online_image']) : '';
			$photo = isset($row['photo']) ? trim($row['photo']) : '';
			if ($online !== '') {
				$row['image_url'] = $online;
			} elseif ($photo !== '') {
				$row['image_url'] = $base . '/uploads/' . rawurlencode($photo);
			} else {
				$row['image_url'] = null;
			}
		}

		$this->_json(array('success' => true, 'data' => $rows));
	}

	/**
	 * Student detail popup — same core fields as CI /students/search (anyquery_student)
	 */
	public function student_detail($student_id = 0)
	{
		$student_id = (int)$student_id;
		if (!$student_id) {
			$this->_json(array('success' => false, 'message' => 'Invalid student'), 422);
		}

		$this->db->select('students.*, classes.name as class_name, classes.exam_no as student_exam, courses.course_name, courses.course_id');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id = students.class_id', 'left');
		$this->db->join('courses', 'courses.course_id = classes.course_id', 'left');
		$this->db->where('students.student_id', $student_id);
		$student = $this->db->get()->row_array();
		if (!$student) {
			$this->_json(array('success' => false, 'message' => 'Student not found'), 404);
		}

		$base = rtrim(base_url(), '/');

		// Photo
		$photo_row = $this->db->get_where('student_documents', array(
			'student_id' => $student_id,
			'type' => 'Photo',
		))->row_array();
		$image_url = null;
		if ($photo_row) {
			if (!empty($photo_row['online_image'])) {
				$image_url = $photo_row['online_image'];
			} elseif (!empty($photo_row['image'])) {
				$image_url = $base . '/uploads/' . rawurlencode($photo_row['image']);
			}
		}

		// Machine ID
		$machine = $this->db->get_where('machine_data', array(
			'teacher_student_id' => $student_id,
		))->row_array();

		// Reference user
		$reference = null;
		if (!empty($student['reference_user_id'])) {
			$ref = $this->db->get_where('reference_users', array(
				'reference_user_id' => (int)$student['reference_user_id'],
			))->row_array();
			if ($ref) {
				$reference = array(
					'name' => isset($ref['name']) ? $ref['name'] : '',
					'phone' => isset($ref['phone']) ? $ref['phone'] : '',
				);
			}
		}

		// Contractor / contract
		$contractor = null;
		if (!empty($student['contract_id']) && (int)$student['contract_id'] > 0) {
			$contract = $this->db->get_where('contracts', array(
				'contract_id' => (int)$student['contract_id'],
			))->row_array();
			if ($contract) {
				$contractor = array(
					'contract_name' => isset($contract['contract_name']) ? $contract['contract_name'] : '',
					'contract_date' => isset($contract['contract_date']) ? $contract['contract_date'] : '',
				);
			}
		}

		// Shift
		$shift_name = null;
		if (isset($student['shift']) && $student['shift'] !== '' && $student['shift'] !== null) {
			$st_shift = $this->db->get_where('shifts', array('id' => $student['shift']))->row_array();
			$shift_name = $st_shift && isset($st_shift['name']) ? $st_shift['name'] : null;
		}

		// Study type
		$study_type_name = null;
		if (!empty($student['study_type'])) {
			$st_study = $this->db->get_where('study_type', array('name' => $student['study_type']))->row_array();
			$study_type_name = $st_study && isset($st_study['name']) ? $st_study['name'] : $student['study_type'];
		}

		// Documents checklist
		$docs = array(
			'id_card' => false,
			'photo' => false,
			'result_card' => false,
		);
		$doc_rows = $this->db->select('type')->from('student_documents')->where('student_id', $student_id)->get()->result_array();
		foreach ($doc_rows as $d) {
			$t = isset($d['type']) ? $d['type'] : '';
			if ($t === 'ID Card') $docs['id_card'] = true;
			if ($t === 'Photo') $docs['photo'] = true;
			if ($t === 'Result Card') $docs['result_card'] = true;
		}

		// Freeze / deleted status label
		$status_label = 'Active';
		if ((int)$student['status'] === 0) {
			$freeze = $this->db->get_where('freeze_student', array('student_id' => $student_id))->result_array();
			$status_label = count($freeze) > 0 ? 'Freezed' : 'Deleted';
		}

		$this->_json(array(
			'success' => true,
			'data' => array(
				'student_id' => (int)$student['student_id'],
				'roll_no' => $student['roll_no'],
				'first_name' => $student['first_name'],
				'last_name' => $student['last_name'],
				'father_name' => isset($student['father_name']) ? $student['father_name'] : '',
				'cnic' => isset($student['cnic']) ? $student['cnic'] : '',
				'mobile' => isset($student['mobile']) ? $student['mobile'] : '',
				'emergency_no' => isset($student['emergency_no']) ? $student['emergency_no'] : '',
				'class_name' => isset($student['class_name']) ? $student['class_name'] : '',
				'student_exam' => isset($student['student_exam']) ? $student['student_exam'] : '',
				'course_name' => isset($student['course_name']) ? $student['course_name'] : '',
				'section' => isset($student['section']) ? $student['section'] : '',
				'shift' => $shift_name,
				'study_type' => $study_type_name,
				'student_card' => !empty($student['student_card']) ? 1 : 0,
				'machine_id' => $machine && isset($machine['machine_id']) ? $machine['machine_id'] : null,
				'registration_date' => isset($student['registration_date']) ? $student['registration_date'] : '',
				'status' => (int)$student['status'],
				'status_label' => $status_label,
				'image_url' => $image_url,
				'reference' => $reference,
				'contractor' => $contractor,
				'documents' => $docs,
			),
		));
	}

	/**
	 * Price for a single product for a student (applies free_item_rules)
	 */
	public function price()
	{
		$body = $this->_body();
		$product_name_id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$student_id = (int)(isset($body['student_id']) ? $body['student_id'] : 0);
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);

		$this->db->limit(1);
		$where = array(
			'product_name_id' => $product_name_id,
			'saleable' => 1,
			'sold' => 0,
			'consume' => 0,
			'status' => 1,
		);
		if ($campus_id > 0) {
			$where['campus_id'] = $campus_id;
		}
		$product = $this->db->get_where('products', $where)->row_array();
		if (!$product) {
			$this->_json(array('success' => false, 'message' => 'Out of stock'), 404);
		}

		$result = $this->_resolve_price($product_name_id, $product['sale_amount'], $student_id);
		$this->_json(array('success' => true, 'data' => $result));
	}

	private function _resolve_price($product_name_id, $sale_amount, $student_id)
	{
		$out = array(
			'product_name_id' => $product_name_id,
			'unit_price' => (float)$sale_amount,
			'is_free' => false,
			'reason' => null,
		);

		if (!$this->_can_claim_free($product_name_id, $student_id)) {
			if ($student_id) {
				$claim = $this->db->get_where('products', array(
					'student_id' => $student_id,
					'product_name_id' => $product_name_id,
					'sold_amount' => 0
				))->result_array();
				if (count($claim) > 0) {
					$out['reason'] = 'already_claimed';
				}
			}
			return $out;
		}

		$out['unit_price'] = 0;
		$out['is_free'] = true;
		$out['reason'] = 'free_rule';
		return $out;
	}

	/**
	 * Free rule = max 1 piece, one-time per student per product (not already sold free).
	 */
	private function _can_claim_free($product_name_id, $student_id)
	{
		$product_name_id = (int)$product_name_id;
		$student_id = (int)$student_id;
		if (!$product_name_id || !$student_id) {
			return false;
		}

		$claim = $this->db->get_where('products', array(
			'student_id' => $student_id,
			'product_name_id' => $product_name_id,
			'sold_amount' => 0
		))->result_array();
		if (count($claim) > 0) {
			return false;
		}

		$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
		if (!$student || (int)$student['status'] !== 1) {
			return false;
		}

		$class_id = (int)$student['class_id'];
		$this->db->from('free_item_rules');
		$this->db->where("find_in_set($class_id, class_ids)");
		$this->db->where("find_in_set($product_name_id, product_name_ids)");
		$this->db->where('till_date >=', date('Y-m-d'));
		$free_check = $this->db->get()->result_array();

		if (count($free_check) > 0 && $student['registration_date'] >= $free_check[0]['student_admission_date']) {
			return true;
		}
		return false;
	}

	/**
	 * Consume at most 1 free unit for this product within the current cart quote.
	 */
	private function _take_free_unit($product_name_id, $student_id, &$claimed)
	{
		$product_name_id = (int)$product_name_id;
		if (!$product_name_id) return 0;
		if (!empty($claimed[$product_name_id])) return 0;
		if (!$this->_can_claim_free($product_name_id, $student_id)) return 0;
		$claimed[$product_name_id] = true;
		return 1;
	}

	/**
	 * Quote a cart (items + bundles) with free rules applied
	 */
	public function quote()
	{
		$body = $this->_body();
		$quoted = $this->_quote_internal($body);
		$this->_json(array(
			'success' => true,
			'data' => array(
				'lines' => $quoted['lines'],
				'subtotal' => $quoted['subtotal'],
				'tax' => 0,
				'service' => 0,
				'total' => $quoted['total'],
			)
		));
	}

	/**
	 * Checkout: create order + mark inventory sold (same unit-row model as legacy POS)
	 */
	public function checkout()
	{
		$this->_require_perm('can_sell');
		$body = $this->_body();
		$student_id = (int)(isset($body['student_id']) ? $body['student_id'] : 0);
		$purchaser_type = isset($body['purchaser_type']) ? $body['purchaser_type'] : ($student_id ? 'student' : 'other');
		$purchaser_name = isset($body['purchaser_name']) ? $body['purchaser_name'] : '';
		$purchaser_phone = isset($body['purchaser_phone']) ? $body['purchaser_phone'] : '';
		$payment_method = isset($body['payment_method']) ? $body['payment_method'] : 'cash';
		$lines = isset($body['lines']) && is_array($body['lines']) ? $body['lines'] : array();

		if (!count($lines)) {
			$this->_json(array('success' => false, 'message' => 'Cart is empty'), 422);
		}

		// Selected campus required — sale posts into that campus's closing.
		$this->_ensure_pos_closing_campus_column();
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		if ($campus_id <= 0) {
			$fresh = $this->db->select('pos_closing_campus_id')->get_where('users', array(
				'user_id' => $this->current_user['user_id'],
			))->row_array();
			$campus_id = $fresh ? (int)$fresh['pos_closing_campus_id'] : 0;
		}
		if ($campus_id <= 0) {
			$this->_json(array(
				'success' => false,
				'message' => 'Select a campus before creating an invoice',
			), 422);
		}
		$this->_require_campus_access($campus_id);

		// Force all lines onto the selected campus (stock + closing for that campus).
		foreach ($lines as &$line) {
			$line['campus_id'] = $campus_id;
		}
		unset($line);

		if ($student_id) {
			$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
			if ($student) {
				$purchaser_name = trim($student['first_name'] . ' ' . $student['last_name']);
				$purchaser_phone = $student['mobile'];
			}
		}

		// Re-quote server-side
		$_POST = array(); // unused
		$quote_body = array('student_id' => $student_id, 'lines' => $lines);
		$quoted = $this->_quote_internal($quote_body);

		$campus = $this->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();
		$code = $campus && !empty($campus['roll_no_code']) ? $campus['roll_no_code'] : 'POS';
		$invoice_no = $code . '-' . time();

		$this->db->trans_start();

		try {
			$this->db->insert('pos_orders', array(
				'invoice_no' => $invoice_no,
				'campus_id' => $campus_id,
				'student_id' => $student_id,
				'purchaser_type' => $purchaser_type,
				'purchaser_name' => $purchaser_name,
				'purchaser_phone' => $purchaser_phone,
				'subtotal' => $quoted['subtotal'],
				'discount' => 0,
				'tax' => 0,
				'total' => $quoted['total'],
				'payment_method' => $payment_method,
				'payment_status' => 'paid',
				'sold_by' => $this->current_user['user_id'],
			));
			$order_id = $this->db->insert_id();

			foreach ($quoted['lines'] as $line) {
				$this->db->insert('pos_order_items', array(
					'order_id' => $order_id,
					'item_type' => $line['type'],
					'ref_id' => $line['ref_id'],
					'name' => $line['name'],
					'quantity' => $line['quantity'],
					'unit_price' => $line['unit_price'],
					'line_total' => $line['line_total'],
					'is_free' => !empty($line['is_free']) ? 1 : 0,
					'campus_id' => $campus_id,
					'room_id' => $line['room_id'],
					'subroom_id' => $line['subroom_id'],
				));

				if ($line['type'] === 'bundle') {
					$parts = isset($line['parts']) ? $line['parts'] : $this->_expand_bundle_parts($line['ref_id'], $line['quantity'], $student_id);
					foreach ($parts as $part) {
						$base = isset($part['base_price']) ? (float)$part['base_price'] : (float)$part['unit_price'];
						$free_u = isset($part['free_units']) ? (int)$part['free_units'] : 0;
						$ok = $this->_mark_sold_units(
							$part['product_name_id'],
							$part['quantity'],
							$base,
							$invoice_no,
							$student_id,
							$purchaser_name,
							$purchaser_phone,
							$campus_id,
							isset($line['room_id']) ? $line['room_id'] : null,
							isset($line['subroom_id']) ? $line['subroom_id'] : null,
							$free_u
						);
						if (!$ok) {
							$this->db->trans_rollback();
							$this->_json(array('success' => false, 'message' => 'Insufficient stock for a bundle item'), 409);
						}
					}
				} else {
					$base = isset($line['base_price']) ? (float)$line['base_price'] : (float)$line['unit_price'];
					$free_u = isset($line['free_units']) ? (int)$line['free_units'] : (!empty($line['is_free']) ? (int)$line['quantity'] : 0);
					$ok = $this->_mark_sold_units(
						$line['ref_id'],
						$line['quantity'],
						$base,
						$invoice_no,
						$student_id,
						$purchaser_name,
						$purchaser_phone,
						$campus_id,
						$line['room_id'],
						$line['subroom_id'],
						$free_u
					);
					if (!$ok) {
						$this->db->trans_rollback();
						$this->_json(array('success' => false, 'message' => 'Insufficient stock'), 409);
					}
				}
			}

			$this->db->trans_complete();
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$this->_json(array('success' => false, 'message' => 'Checkout failed'), 500);
		}

		if ($this->db->trans_status() === FALSE) {
			$this->_json(array('success' => false, 'message' => 'Checkout failed. Check stock.'), 500);
		}

		$this->_json(array(
			'success' => true,
			'data' => array(
				'order_id' => $order_id,
				'invoice_no' => $invoice_no,
				'total' => $quoted['total'],
				'campus_id' => $campus_id,
				'lines' => $quoted['lines'],
			)
		));
	}

	private function _quote_internal($body)
	{
		$student_id = (int)(isset($body['student_id']) ? $body['student_id'] : 0);
		$lines = isset($body['lines']) && is_array($body['lines']) ? $body['lines'] : array();
		$quoted = array();
		$subtotal = 0;
		$claimed = array(); // product_name_id => free already used in this cart

		foreach ($lines as $line) {
			$type = isset($line['type']) ? $line['type'] : 'item';
			$qty = isset($line['quantity']) ? max(1, (int)$line['quantity']) : 1;

			if ($type === 'bundle') {
				$bundle_id = (int)$line['ref_id'];
				$bundle = $this->db->get_where('pos_bundles', array('bundle_id' => $bundle_id, 'status' => 1))->row_array();
				if (!$bundle) continue;
				$items = $this->_bundle_items($bundle_id);
				$bundle_price = (float)$bundle['price'];
				$free_discount = 0;
				$part_details = array();

				foreach ($items as $bi) {
					$pid = (int)$bi['product_name_id'];
					$per = max(1, (int)$bi['quantity']);
					$total_units = $per * $qty;
					$this->db->limit(1);
					$p = $this->db->get_where('products', array(
						'product_name_id' => $pid,
						'saleable' => 1,
						'sold' => 0,
						'consume' => 0,
						'status' => 1,
					))->row_array();
					$base = $p ? (float)$p['sale_amount'] : 0;
					$free_units = $this->_take_free_unit($pid, $student_id, $claimed);
					$paid_units = max(0, $total_units - $free_units);
					$free_discount += $base * $free_units;

					$part_details[] = array(
						'product_name_id' => $pid,
						'product_name' => isset($bi['product_name']) ? $bi['product_name'] : '',
						'quantity' => $total_units,
						'free_units' => $free_units,
						'paid_units' => $paid_units,
						'base_price' => $base,
						'is_free' => ($free_units > 0 && $paid_units === 0),
						'unit_price' => $base,
					);
				}

				// Discount only 1 free piece value per eligible product — not × bundle qty
				$line_total = max(0, $bundle_price * $qty - $free_discount);
				$quoted[] = array(
					'type' => 'bundle',
					'ref_id' => $bundle_id,
					'name' => $bundle['name'],
					'quantity' => $qty,
					'unit_price' => $qty ? round($line_total / $qty, 2) : 0,
					'line_total' => round($line_total, 2),
					'is_free' => ($line_total <= 0),
					'free_units' => $free_discount > 0 ? 1 : 0,
					'parts' => $part_details,
					'campus_id' => isset($line['campus_id']) ? (int)$line['campus_id'] : null,
					'room_id' => isset($line['room_id']) ? (int)$line['room_id'] : null,
					'subroom_id' => isset($line['subroom_id']) ? (int)$line['subroom_id'] : null,
				);
				$subtotal += $line_total;
			} else {
				$product_name_id = (int)$line['ref_id'];
				$pn = $this->db->get_where('product_names', array('product_name_id' => $product_name_id))->row_array();
				$this->db->limit(1);
				$where = array(
					'product_name_id' => $product_name_id,
					'saleable' => 1,
					'sold' => 0,
					'consume' => 0,
					'status' => 1,
				);
				if (!empty($line['campus_id'])) $where['campus_id'] = (int)$line['campus_id'];
				$p = $this->db->get_where('products', $where)->row_array();
				$base = $p ? (float)$p['sale_amount'] : 0;
				$free_units = $this->_take_free_unit($product_name_id, $student_id, $claimed);
				$paid_units = max(0, $qty - $free_units);
				$line_total = $paid_units * $base;
				$unit = $qty > 0 ? round($line_total / $qty, 2) : $base;

				$quoted[] = array(
					'type' => 'item',
					'ref_id' => $product_name_id,
					'name' => $pn ? $pn['product_name'] : 'Item',
					'quantity' => $qty,
					'unit_price' => $unit,
					'base_price' => $base,
					'line_total' => round($line_total, 2),
					'is_free' => ($free_units > 0 && $paid_units === 0),
					'free_units' => $free_units,
					'paid_units' => $paid_units,
					'campus_id' => isset($line['campus_id']) ? (int)$line['campus_id'] : ($p ? (int)$p['campus_id'] : null),
					'room_id' => isset($line['room_id']) ? (int)$line['room_id'] : ($p ? (int)$p['room_id'] : null),
					'subroom_id' => isset($line['subroom_id']) ? (int)$line['subroom_id'] : ($p ? (int)$p['subroom_id'] : null),
				);
				$subtotal += $line_total;
			}
		}

		return array(
			'lines' => $quoted,
			'subtotal' => round($subtotal, 2),
			'total' => round($subtotal, 2),
		);
	}

	private function _expand_bundle_parts($bundle_id, $qty, $student_id)
	{
		$claimed = array();
		$items = $this->_bundle_items($bundle_id);
		$parts = array();
		foreach ($items as $bi) {
			$pid = (int)$bi['product_name_id'];
			$per = max(1, (int)$bi['quantity']);
			$total_units = $per * $qty;
			$this->db->limit(1);
			$p = $this->db->get_where('products', array(
				'product_name_id' => $pid,
				'saleable' => 1,
				'sold' => 0,
				'consume' => 0,
				'status' => 1,
			))->row_array();
			$base = $p ? (float)$p['sale_amount'] : 0;
			$free_units = $this->_take_free_unit($pid, $student_id, $claimed);
			$parts[] = array(
				'product_name_id' => $pid,
				'quantity' => $total_units,
				'free_units' => $free_units,
				'unit_price' => $base,
				'base_price' => $base,
			);
		}
		return $parts;
	}

	private function _mark_sold_units($product_name_id, $quantity, $unit_price, $invoice_no, $student_id, $purchaser_name, $purchaser_phone, $campus_id = null, $room_id = null, $subroom_id = null, $free_units = 0)
	{
		$free_units = max(0, (int)$free_units);
		for ($i = 0; $i < $quantity; $i++) {
			$this->db->limit(1);
			$where = array(
				'product_name_id' => $product_name_id,
				'sold' => 0,
				'saleable' => 1,
				'consume' => 0,
				'status' => 1,
			);
			if ($campus_id) $where['campus_id'] = $campus_id;
			if ($room_id) $where['room_id'] = $room_id;
			if ($subroom_id) $where['subroom_id'] = $subroom_id;

			$row = $this->db->get_where('products', $where)->row_array();
			if (!$row) {
				return false;
			}

			$sold_amount = ($i < $free_units) ? 0 : $unit_price;

			$this->db->where('product_id', $row['product_id'])->update('products', array(
				'sold' => 1,
				'sold_date' => date('Y-m-d'),
				'sold_amount' => $sold_amount,
				'invoice_no' => $invoice_no,
				'sold_by' => $this->current_user['user_id'],
				'student_id' => $student_id ? $student_id : 0,
				'purchaser_name' => $purchaser_name,
				'purchaser_phone' => $purchaser_phone,
			));
		}
		return true;
	}

	public function orders()
	{
		$this->_require_perm('can_view_history');
		$from = $this->input->get('from_date') ? $this->input->get('from_date') : date('Y-m-d', strtotime('-1 month'));
		$to = $this->input->get('to_date') ? $this->input->get('to_date') : date('Y-m-d');
		$perms = $this->_permissions();

		$this->db->select('pos_orders.*, users.first_name, users.last_name');
		$this->db->from('pos_orders');
		$this->db->join('users', 'users.user_id = pos_orders.sold_by', 'left');
		$this->db->where('DATE(pos_orders.created_at) >=', $from);
		$this->db->where('DATE(pos_orders.created_at) <=', $to);
		if (!$perms['is_admin']) {
			if (count($perms['campus_ids'])) {
				$this->db->where_in('pos_orders.campus_id', $perms['campus_ids']);
			} else {
				$this->db->where('pos_orders.sold_by', $this->current_user['user_id']);
			}
		}
		$this->db->order_by('pos_orders.order_id', 'DESC');
		$this->db->limit(100);
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function order($order_id = 0)
	{
		$order = $this->db->get_where('pos_orders', array('order_id' => (int)$order_id))->row_array();
		if (!$order) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}

		$perms = $this->_permissions();
		$is_seller = !empty($order['sold_by']) && (int)$order['sold_by'] === (int)$this->current_user['user_id'];
		if (!$perms['is_admin'] && empty($perms['can_view_history']) && !$is_seller) {
			$this->_json(array('success' => false, 'message' => 'Permission denied'), 403);
		}
		if (!empty($order['campus_id']) && !$is_seller) {
			$this->_require_campus_access($order['campus_id']);
		}

		$campus = null;
		if (!empty($order['campus_id'])) {
			$campus = $this->db->get_where('campuses', array('campus_id' => (int)$order['campus_id']))->row_array();
		}
		$seller = null;
		if (!empty($order['sold_by'])) {
			$seller = $this->db->get_where('users', array('user_id' => (int)$order['sold_by']))->row_array();
		}
		$student = null;
		if (!empty($order['student_id'])) {
			$student = $this->db->select('student_id, first_name, last_name, roll_no, mobile')
				->from('students')
				->where('student_id', (int)$order['student_id'])
				->get()
				->row_array();
		}

		$items = $this->db->get_where('pos_order_items', array('order_id' => (int)$order_id))->result_array();

		$order['campus_name'] = $campus && isset($campus['campus_name']) ? $campus['campus_name'] : '';
		$order['sold_by_name'] = $seller
			? trim((isset($seller['first_name']) ? $seller['first_name'] : '') . ' ' . (isset($seller['last_name']) ? $seller['last_name'] : ''))
			: '';
		$order['student_roll'] = $student && isset($student['roll_no']) ? $student['roll_no'] : '';

		$this->_json(array(
			'success' => true,
			'data' => array(
				'order' => $order,
				'items' => $items,
			),
		));
	}
}
