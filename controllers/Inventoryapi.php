<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inventory JSON API for React POS shell
 * Base: /index.php/inventoryapi/{method}
 * Auth: same X-Pos-Token as Posapi; requires access.inventory (or Admin)
 */
class Inventoryapi extends CI_Controller {

	private $current_user = null;
	private $access_row = null;
	private $inventory_campus_ids = null;
	private $pr_campus_ids = null;

	public function __construct()
	{
		parent::__construct();
		$this->_cors();
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(204);
			exit;
		}
		$this->current_user = $this->_auth_user();
		if (!$this->current_user) {
			$this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
		}
		// Prefetch access/campuses with raw SQL so query-builder state stays clean
		$this->_access();
		$this->_inventory_campus_ids();
		$this->_pr_campus_ids();
		if (!$this->_can_inventory()) {
			$this->_json(array('success' => false, 'message' => 'No inventory access'), 403);
		}
	}

	private function _cors()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		$allowed = array(
			'http://localhost:5173', 'http://localhost:4173', 'http://127.0.0.1:5173',
			'https://pos.shahbazcollegeofpharmacy.edu.pk', 'http://pos.shahbazcollegeofpharmacy.edu.pk',
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
		if (is_array($json) && count($json)) return $json;
		return $this->input->post() ? $this->input->post() : array();
	}

	private function _auth_user()
	{
		$token = isset($_SERVER['HTTP_X_POS_TOKEN']) ? $_SERVER['HTTP_X_POS_TOKEN'] : '';
		if ($token === '' && isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\\s+(\\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
			$token = $m[1];
		}
		if ($token === '') $token = $this->input->get_request_header('X-Pos-Token', TRUE);
		if (!$token) return null;
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) return null;
		return $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
	}

	private function _access()
	{
		if ($this->access_row !== null) return $this->access_row;
		$uid = (int)$this->current_user['user_id'];
		$row = $this->db->query('SELECT * FROM access WHERE user_id = ? LIMIT 1', array($uid))->row_array();
		$this->access_row = $row ? $row : array();
		return $this->access_row;
	}

	private function _is_admin()
	{
		return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin';
	}

	private function _can_inventory()
	{
		if ($this->_is_admin()) return true;
		$row = $this->_access();
		return $row && !empty($row['inventory']);
	}

	private function _inventory_campus_ids()
	{
		if ($this->inventory_campus_ids !== null) return $this->inventory_campus_ids;
		$ids = array();
		if ($this->_is_admin()) {
			// Raw query — never use query-builder here (it corrupts in-flight selects)
			foreach ($this->db->query('SELECT campus_id FROM campuses WHERE status = 1')->result_array() as $c) {
				$ids[] = (int)$c['campus_id'];
			}
		} else {
			$row = $this->_access();
			if ($row && !empty($row['inventory_campuses'])) {
				foreach (explode(',', $row['inventory_campuses']) as $id) {
					$id = (int)trim($id);
					if ($id > 0) $ids[] = $id;
				}
			}
		}
		$this->inventory_campus_ids = array_values(array_unique($ids));
		return $this->inventory_campus_ids;
	}

	private function _pr_campus_ids()
	{
		if ($this->pr_campus_ids !== null) return $this->pr_campus_ids;
		if ($this->_is_admin()) {
			$this->pr_campus_ids = $this->_inventory_campus_ids();
			return $this->pr_campus_ids;
		}
		$row = $this->_access();
		$ids = array();
		if ($row && !empty($row['product_request_approval_campuses'])) {
			foreach (explode(',', $row['product_request_approval_campuses']) as $id) {
				$id = (int)trim($id);
				if ($id > 0) $ids[] = $id;
			}
		}
		if (!count($ids)) $ids = $this->_inventory_campus_ids();
		$this->pr_campus_ids = array_values(array_unique($ids));
		return $this->pr_campus_ids;
	}

	/** Permission-only check — does not touch query builder */
	private function _assert_campus_access($campus_id, $use_pr = false)
	{
		$campus_id = (int)$campus_id;
		if ($campus_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'campus_id required'), 422);
		}
		if ($this->_is_admin()) return;
		$allowed = $use_pr ? $this->_pr_campus_ids() : $this->_inventory_campus_ids();
		if (!in_array($campus_id, $allowed, true)) {
			$this->_json(array('success' => false, 'message' => 'No access to this campus'), 403);
		}
	}

	/** Apply campus WHERE on the current SELECT — safe: campus ids are cached */
	private function _apply_campus_filter($column, $campus_id = 0, $use_pr = false)
	{
		$allowed = $use_pr ? $this->_pr_campus_ids() : $this->_inventory_campus_ids();
		$campus_id = (int)$campus_id;
		if ($campus_id > 0) {
			if (!$this->_is_admin() && !in_array($campus_id, $allowed, true)) {
				$this->_json(array('success' => false, 'message' => 'No access to this campus'), 403);
			}
			$this->db->where($column, $campus_id);
		} elseif (!$this->_is_admin()) {
			if (!count($allowed)) {
				$this->db->where('1 = 0', null, false);
			} else {
				$this->db->where_in($column, $allowed);
			}
		}
	}

	private function _img_url($file)
	{
		$file = trim((string)$file);
		if ($file === '') return null;
		return rtrim(base_url(), '/') . '/inventory_images/' . rawurlencode($file);
	}

	/**
	 * Build a products INSERT row matching legacy CI schema (many NOT NULL cols, no created_by).
	 */
	private function _product_insert_row($opts)
	{
		$name = '';
		if ($this->current_user) {
			$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		}
		$saleable = !empty($opts['saleable']) ? 1 : 0;
		$sale_amount = isset($opts['sale_amount']) ? $opts['sale_amount'] : ($saleable ? 0 : '');
		$qr = isset($opts['qr_code']) ? trim((string)$opts['qr_code']) : '';
		if ($qr !== '' && strpos($qr, 'inv_qr-') !== 0) $qr = 'inv_qr-' . $qr;

		return array(
			'campus_id' => (int)$opts['campus_id'],
			'room_id' => !empty($opts['room_id']) ? (int)$opts['room_id'] : 0,
			'subroom_id' => !empty($opts['subroom_id']) ? (int)$opts['subroom_id'] : 0,
			'product_name_id' => (int)$opts['product_name_id'],
			'product_image' => isset($opts['product_image']) ? (string)$opts['product_image'] : '',
			'online_product_image' => '',
			'purchase_slip' => '',
			'online_purchase_slip' => '',
			'product_quantity' => 1,
			'remaining_quantity' => 1,
			'qr_code' => $qr !== '' ? $qr : null,
			'estimated_price' => isset($opts['estimated_price']) ? (int)$opts['estimated_price'] : 0,
			'product_guarantee' => 0,
			'product_guarantee_start_date' => '0000-00-00',
			'product_guarantee_end_date' => '0000-00-00',
			'remarks' => isset($opts['remarks']) ? (string)$opts['remarks'] : '',
			'add_by' => $name !== '' ? $name : 'POS',
			'last_edit' => $name !== '' ? $name : 'POS',
			'clear_by' => '',
			'status' => 1,
			'consumeable' => 0,
			'consume' => 0,
			'consume_reason' => '',
			'saleable' => $saleable,
			'sale_amount' => $sale_amount === '' || $sale_amount === null ? null : (int)round((float)$sale_amount),
			'expire' => 0,
			'returnable' => 0,
			'sold_amount' => '',
			'sold' => 0,
			'purchase_no' => isset($opts['purchase_no']) ? (string)$opts['purchase_no'] : '',
			'user_id' => 0,
			'reponsilble_user_id' => 0,
			'upload_image' => 0,
		);
	}

	private function _purchase_no()
	{
		$query = 'SELECT CAST(SUBSTRING(purchase_no,4,LENGTH(purchase_no)) AS UNSIGNED) as n FROM purchase_requests ORDER BY n DESC LIMIT 1';
		$row = $this->db->query($query)->row_array();
		$n = $row && !empty($row['n']) ? ((int)$row['n'] + 1) : 1;
		return 'PR-' . $n;
	}

	private function _pir_no()
	{
		if (!$this->db->table_exists('require_product_requests')) return 'PIR-1';
		$row = $this->db->query("SELECT request_no FROM require_product_requests ORDER BY request_no DESC LIMIT 1")->row_array();
		if ($row && !empty($row['request_no'])) {
			$n = (int)str_replace('PIR-', '', $row['request_no']) + 1;
			return 'PIR-' . $n;
		}
		return 'PIR-1';
	}

	// ─── Catalog / Products ─────────────────────────────────

	public function stock()
	{
		$q = trim((string)$this->input->get('q'));
		$campus_id = (int)$this->input->get('campus_id');
		$room_id = (int)$this->input->get('room_id');
		$type = trim((string)$this->input->get('type')); // available|consumed|sold|all

		$this->db->select('product_names.product_name_id, product_names.product_name,
			COUNT(products.product_id) as stock,
			MIN(products.sale_amount) as sale_amount,
			MAX(NULLIF(products.product_image, "")) as product_image,
			MIN(products.campus_id) as campus_id, MIN(products.room_id) as room_id, MIN(products.subroom_id) as subroom_id,
			MAX(campuses.campus_name) as campus_name, MAX(rooms.room_name) as room_name, MAX(subrooms.subroom_name) as subroom_name,
			SUM(CASE WHEN products.saleable=1 THEN 1 ELSE 0 END) as saleable_count,
			SUM(CASE WHEN products.consume=1 THEN 1 ELSE 0 END) as consume_count,
			SUM(CASE WHEN products.sold=1 THEN 1 ELSE 0 END) as sold_count', false);
		$this->db->from('products');
		$this->db->join('product_names', 'product_names.product_name_id = products.product_name_id', 'inner');
		$this->db->join('campuses', 'campuses.campus_id = products.campus_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = products.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = products.subroom_id', 'left');
		$this->db->where('products.status', 1);
		$this->_apply_campus_filter('products.campus_id', $campus_id);
		if ($room_id > 0) $this->db->where('products.room_id', $room_id);
		if ($type === 'consumed') {
			$this->db->where(array('products.consume' => 1, 'products.sold' => 0));
		} elseif ($type === 'sold') {
			$this->db->where(array('products.sold' => 1, 'products.consume' => 0));
		} elseif ($type !== 'all') {
			$this->db->where(array('products.sold' => 0, 'products.consume' => 0));
		}
		if ($q !== '') $this->db->like('product_names.product_name', $q);
		$this->db->group_by(array('products.product_name_id', 'products.campus_id', 'products.room_id', 'products.subroom_id'));
		$this->db->order_by('product_names.product_name', 'ASC');
		$this->db->limit(500);
		$rows = $this->db->get()->result_array();
		foreach ($rows as &$row) {
			$row['image_url'] = $this->_img_url(isset($row['product_image']) ? $row['product_image'] : '');
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function stock_units()
	{
		$product_name_id = (int)$this->input->get('product_name_id');
		$campus_id = (int)$this->input->get('campus_id');
		$room_id = (int)$this->input->get('room_id');
		$subroom_id = (int)$this->input->get('subroom_id');
		if (!$product_name_id) $this->_json(array('success' => false, 'message' => 'product_name_id required'), 422);

		$this->db->select('products.*, campuses.campus_name, rooms.room_name, subrooms.subroom_name, product_names.product_name');
		$this->db->from('products');
		$this->db->join('product_names', 'product_names.product_name_id = products.product_name_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id = products.campus_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = products.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = products.subroom_id', 'left');
		$this->db->where('products.product_name_id', $product_name_id);
		$this->db->where('products.status', 1);
		$this->_apply_campus_filter('products.campus_id', $campus_id);
		if ($room_id > 0) $this->db->where('products.room_id', $room_id);
		if ($subroom_id > 0) $this->db->where('products.subroom_id', $subroom_id);
		$this->db->order_by('products.product_id', 'DESC');
		$this->db->limit(200);
		$rows = $this->db->get()->result_array();
		foreach ($rows as &$row) {
			$row['image_url'] = $this->_img_url(isset($row['product_image']) ? $row['product_image'] : '');
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function add_stock()
	{
		$body = $this->_body();
		$product_name_id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		$room_id = (int)(isset($body['room_id']) ? $body['room_id'] : 0);
		$subroom_id = (int)(isset($body['subroom_id']) ? $body['subroom_id'] : 0);
		$qty = max(1, (int)(isset($body['quantity']) ? $body['quantity'] : 1));
		$sale_amount = isset($body['sale_amount']) ? (float)$body['sale_amount'] : 0;
		$saleable = !empty($body['saleable']) ? 1 : 0;
		$qr = isset($body['qr_code']) ? trim($body['qr_code']) : '';
		$image = isset($body['product_image']) ? $body['product_image'] : '';

		if (!$product_name_id || !$campus_id) {
			$this->_json(array('success' => false, 'message' => 'product_name_id and campus_id required'), 422);
		}
		$this->_assert_campus_access($campus_id);

		for ($i = 0; $i < $qty; $i++) {
			$this->db->insert('products', $this->_product_insert_row(array(
				'product_name_id' => $product_name_id,
				'campus_id' => $campus_id,
				'room_id' => $room_id,
				'subroom_id' => $subroom_id,
				'sale_amount' => $sale_amount,
				'saleable' => $saleable,
				'qr_code' => $qr,
				'product_image' => $image,
			)));
		}
		$this->_json(array('success' => true, 'message' => 'Stock added', 'quantity' => $qty));
	}

	public function consume()
	{
		$body = $this->_body();
		$ids = isset($body['product_ids']) && is_array($body['product_ids']) ? $body['product_ids'] : array();
		if (!count($ids) && !empty($body['product_id'])) $ids = array((int)$body['product_id']);
		if (!count($ids)) $this->_json(array('success' => false, 'message' => 'product_ids required'), 422);

		$this->db->where_in('product_id', array_map('intval', $ids));
		$this->db->where(array('sold' => 0, 'consume' => 0, 'status' => 1));
		$this->db->update('products', array('consume' => 1, 'consume_date' => date('Y-m-d H:i:s')));
		$this->_json(array('success' => true, 'updated' => $this->db->affected_rows()));
	}

	// ─── Product names ──────────────────────────────────────

	public function names()
	{
		$q = trim((string)$this->input->get('q'));
		$this->db->from('product_names');
		if ($q !== '') $this->db->like('product_name', $q);
		$this->db->order_by('product_name', 'ASC');
		$this->db->limit(500);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function save_name()
	{
		$body = $this->_body();
		$id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$name = isset($body['product_name']) ? trim($body['product_name']) : '';
		if ($name === '') $this->_json(array('success' => false, 'message' => 'Name required'), 422);
		$data = array(
			'product_name' => $name,
			'sub_of' => isset($body['sub_of']) && $body['sub_of'] ? (int)$body['sub_of'] : null,
		);
		if ($id > 0) {
			$this->db->where('product_name_id', $id)->update('product_names', $data);
		} else {
			$this->db->insert('product_names', $data);
			$id = $this->db->insert_id();
		}
		$this->_json(array('success' => true, 'product_name_id' => $id));
	}

	public function delete_name($id = 0)
	{
		$id = (int)$id;
		if (!$id) $this->_json(array('success' => false, 'message' => 'Invalid id'), 422);
		$used = $this->db->where('product_name_id', $id)->count_all_results('products');
		if ($used > 0) $this->_json(array('success' => false, 'message' => 'Name is used by stock units'), 409);
		$this->db->where('product_name_id', $id)->delete('product_names');
		$this->_json(array('success' => true));
	}

	// ─── Rooms / Subrooms ───────────────────────────────────

	public function rooms()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('rooms.*, campuses.campus_name');
		$this->db->from('rooms');
		$this->db->join('campuses', 'campuses.campus_id = rooms.campus_id', 'left');
		$this->_apply_campus_filter('rooms.campus_id', $campus_id);
		$this->db->order_by('rooms.room_name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function save_room()
	{
		$body = $this->_body();
		$id = (int)(isset($body['room_id']) ? $body['room_id'] : 0);
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		$name = isset($body['room_name']) ? trim($body['room_name']) : '';
		if (!$campus_id || $name === '') $this->_json(array('success' => false, 'message' => 'campus_id and room_name required'), 422);
		$this->_assert_campus_access($campus_id);
		$data = array('campus_id' => $campus_id, 'room_name' => $name);
		if ($id > 0) {
			$this->db->where('room_id', $id)->update('rooms', $data);
		} else {
			$this->db->insert('rooms', $data);
			$id = $this->db->insert_id();
		}
		$this->_json(array('success' => true, 'room_id' => $id));
	}

	public function delete_room($id = 0)
	{
		$id = (int)$id;
		$this->db->where('room_id', $id)->delete('rooms');
		$this->_json(array('success' => true));
	}

	public function subrooms()
	{
		$room_id = (int)$this->input->get('room_id');
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('subrooms.*, rooms.room_name, rooms.campus_id');
		$this->db->from('subrooms');
		$this->db->join('rooms', 'rooms.room_id = subrooms.room_id', 'left');
		if ($room_id > 0) $this->db->where('subrooms.room_id', $room_id);
		$this->_apply_campus_filter('rooms.campus_id', $campus_id);
		$this->db->order_by('subrooms.subroom_name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function save_subroom()
	{
		$body = $this->_body();
		$id = (int)(isset($body['subroom_id']) ? $body['subroom_id'] : 0);
		$room_id = (int)(isset($body['room_id']) ? $body['room_id'] : 0);
		$name = isset($body['subroom_name']) ? trim($body['subroom_name']) : '';
		if (!$room_id || $name === '') $this->_json(array('success' => false, 'message' => 'room_id and subroom_name required'), 422);
		$data = array('room_id' => $room_id, 'subroom_name' => $name);
		if ($id > 0) {
			$this->db->where('subroom_id', $id)->update('subrooms', $data);
		} else {
			$this->db->insert('subrooms', $data);
			$id = $this->db->insert_id();
		}
		$this->_json(array('success' => true, 'subroom_id' => $id));
	}

	public function delete_subroom($id = 0)
	{
		$this->db->where('subroom_id', (int)$id)->delete('subrooms');
		$this->_json(array('success' => true));
	}

	// ─── Vendors ────────────────────────────────────────────

	public function vendors()
	{
		$q = trim((string)$this->input->get('q'));
		$campus_id = (int)$this->input->get('campus_id');
		// Real columns: name, shop_name, phone, address, image, campus_id, product_name_ids, status
		$this->db->select('vendors.*, vendors.name AS vendor_name, vendors.shop_name AS company_name, campuses.campus_name', false);
		$this->db->from('vendors');
		$this->db->join('campuses', 'campuses.campus_id = vendors.campus_id', 'left');
		if ($q !== '') {
			$this->db->group_start();
			$this->db->like('vendors.name', $q);
			$this->db->or_like('vendors.shop_name', $q);
			$this->db->or_like('vendors.phone', $q);
			$this->db->group_end();
		}
		if ($campus_id > 0) $this->db->where('vendors.campus_id', $campus_id);
		$this->db->order_by('vendors.name', 'ASC');
		$rows = $this->db->get()->result_array();
		foreach ($rows as &$row) {
			if (empty($row['vendor_name']) && !empty($row['name'])) $row['vendor_name'] = $row['name'];
			if (empty($row['company_name']) && !empty($row['shop_name'])) $row['company_name'] = $row['shop_name'];
			$row['image_url'] = $this->_img_url(isset($row['image']) ? $row['image'] : '');
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function save_vendor()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : 0);
		$name = isset($body['vendor_name']) ? trim($body['vendor_name']) : (isset($body['name']) ? trim($body['name']) : '');
		if ($name === '') $this->_json(array('success' => false, 'message' => 'vendor name required'), 422);
		$shop = isset($body['company_name']) ? $body['company_name'] : (isset($body['shop_name']) ? $body['shop_name'] : '');
		$data = array(
			'name' => $name,
			'shop_name' => $shop,
			'phone' => isset($body['phone']) ? $body['phone'] : '',
			'address' => isset($body['address']) ? $body['address'] : '',
		);
		if (isset($body['campus_id'])) $data['campus_id'] = (int)$body['campus_id'];
		if (isset($body['status'])) $data['status'] = $body['status'];
		if (isset($body['image'])) $data['image'] = $body['image'];
		if (isset($body['product_name_ids'])) {
			$data['product_name_ids'] = is_array($body['product_name_ids'])
				? implode(',', $body['product_name_ids'])
				: $body['product_name_ids'];
		}
		if ($id > 0) {
			$this->db->where('id', $id)->update('vendors', $data);
		} else {
			$data['created_by'] = $this->current_user['user_id'];
			$data['created_at'] = date('Y-m-d H:i:s');
			if (!isset($data['status'])) $data['status'] = 'active';
			$this->db->insert('vendors', $data);
			$id = $this->db->insert_id();
		}
		$this->_json(array('success' => true, 'id' => $id));
	}

	public function delete_vendor($id = 0)
	{
		$this->db->where('id', (int)$id)->delete('vendors');
		$this->_json(array('success' => true));
	}

	// ─── Purchase requests ──────────────────────────────────

	public function purchase_requests()
	{
		$from = $this->input->get('from_date');
		$to = $this->input->get('to_date');
		$final = $this->input->get('final');
		$campus_id = (int)$this->input->get('campus_id');

		$this->db->select('purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = purchase_requests.subroom_id', 'left');
		if ($from) $this->db->where('purchase_requests.created_at >=', $from . ' 00:00:00');
		if ($to) $this->db->where('purchase_requests.created_at <=', $to . ' 23:59:59');
		if ($final === '0' || $final === '1') {
			$this->db->where('purchase_requests.final', (int)$final);
		}
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->order_by('purchase_requests.purchase_request_id', 'DESC');
		$this->db->limit(500);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function create_purchase_request()
	{
		$body = $this->_body();
		$lines = isset($body['lines']) && is_array($body['lines']) ? $body['lines'] : array();
		$title = isset($body['title']) ? trim($body['title']) : 'Purchase Request';
		if (!count($lines)) $this->_json(array('success' => false, 'message' => 'Add at least one line'), 422);

		$purchase_no = $this->_purchase_no();
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		foreach ($lines as $line) {
			$campus_id = (int)$line['campus_id'];
			$this->_assert_campus_access($campus_id, true);
			$this->db->insert('purchase_requests', array(
				'title' => $title,
				'purchase_no' => $purchase_no,
				'campus_id' => $campus_id,
				'room_id' => isset($line['room_id']) ? (int)$line['room_id'] : 0,
				'subroom_id' => isset($line['subroom_id']) ? (int)$line['subroom_id'] : 0,
				'product_name_id' => (int)$line['product_name_id'],
				'product_quantity' => max(1, (int)$line['quantity']),
				'add_by' => $name,
				'purchased_by' => $this->current_user['user_id'],
				'status' => 0,
				'final' => 0,
				'purchased' => 0,
				'gate_approval' => 0,
				'approval' => 0,
			));
		}
		$this->_json(array('success' => true, 'purchase_no' => $purchase_no));
	}

	public function update_purchase_request_status()
	{
		$body = $this->_body();
		$ids = isset($body['purchase_request_ids']) && is_array($body['purchase_request_ids']) ? $body['purchase_request_ids'] : array();
		$status = isset($body['status']) ? (int)$body['status'] : 1; // 1 = approved
		if (!count($ids)) $this->_json(array('success' => false, 'message' => 'ids required'), 422);
		$this->db->where_in('purchase_request_id', array_map('intval', $ids));
		$this->db->update('purchase_requests', array('status' => $status));
		$this->_json(array('success' => true, 'updated' => $this->db->affected_rows()));
	}

	public function delete_purchase_request($id = 0)
	{
		$this->db->where('purchase_request_id', (int)$id)->delete('purchase_requests');
		$this->_json(array('success' => true));
	}

	/**
	 * Full PR journey: lines + quotes + payments + computed step states.
	 * GET purchase_journey?purchase_no=PR-1
	 */
	public function purchase_journey()
	{
		$purchase_no = trim((string)$this->input->get('purchase_no'));
		if ($purchase_no === '') {
			$this->_json(array('success' => false, 'message' => 'purchase_no required'), 422);
		}

		$this->db->select('purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name, vendors.name AS vendor_name', false);
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = purchase_requests.subroom_id', 'left');
		$this->db->join('vendors', 'vendors.id = purchase_requests.purchase_from', 'left');
		$this->db->where('purchase_requests.purchase_no', $purchase_no);
		$this->_apply_campus_filter('purchase_requests.campus_id', 0, true);
		$this->db->order_by('purchase_requests.purchase_request_id', 'ASC');
		$lines = $this->db->get()->result_array();
		if (!count($lines)) {
			$this->_json(array('success' => false, 'message' => 'Purchase request not found'), 404);
		}

		$this->db->select('purchase_request_prices.*, purchase_request_prices.purchase_request_price_id AS id, purchase_request_prices.approve AS approved, vendors.name AS vendor_name, vendors.shop_name AS company_name, product_names.product_name, purchase_requests.product_quantity, purchase_requests.campus_id', false);
		$this->db->from('purchase_request_prices');
		$this->db->join('vendors', 'vendors.id = purchase_request_prices.vendor_id', 'left');
		$this->db->join('purchase_requests', 'purchase_requests.purchase_request_id = purchase_request_prices.purchase_request_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->where('purchase_requests.purchase_no', $purchase_no);
		$quotes = $this->db->get()->result_array();

		$payments = array();
		if ($this->db->table_exists('payment_aggrements')) {
			$payments = $this->db->get_where('payment_aggrements', array('purchase_no' => $purchase_no))->result_array();
		}

		$all_approved = true;
		$all_vendor = true;
		$all_final = true;
		$all_purchased = true;
		$all_gate = true;
		$all_grn = true;
		foreach ($lines as $line) {
			if ((int)$line['status'] !== 1) $all_approved = false;
			if (empty($line['purchase_from'])) $all_vendor = false;
			if ((int)$line['final'] !== 1) $all_final = false;
			if ((int)$line['purchased'] !== 1) $all_purchased = false;
			if ((int)$line['gate_approval'] !== 1) $all_gate = false;
			if ((int)$line['approval'] !== 1) $all_grn = false;
		}
		$has_quotes = count($quotes) > 0;
		$agreement_done = $all_final && $all_purchased;

		$defs = array(
			array(
				'id' => 'created',
				'pending_label' => 'Purchase Request',
				'done_label' => 'Purchase Request Added',
				'hint' => 'Request created with product lines',
				'done' => true,
			),
			array(
				'id' => 'approve',
				'pending_label' => 'Approve Purchase Request',
				'done_label' => 'Purchase Request Approved',
				'hint' => 'Approve all lines to continue',
				'done' => $all_approved,
			),
			array(
				'id' => 'quote_add',
				'pending_label' => 'Add Quotation',
				'done_label' => 'Quotations Added',
				'hint' => 'Add vendor prices for each line',
				'done' => $has_quotes,
			),
			array(
				'id' => 'quote_select',
				'pending_label' => 'Select Quotation',
				'done_label' => 'Quotation Selected',
				'hint' => 'Approve the best vendor quote per line',
				'done' => $all_vendor,
			),
			array(
				'id' => 'payment',
				'pending_label' => 'Finalise & Payment Agreement',
				'done_label' => 'Payment Agreement Added',
				'hint' => 'Finalise quotation then create payment installments',
				'done' => $agreement_done,
			),
			array(
				'id' => 'gate',
				'pending_label' => 'Gate Approval',
				'done_label' => 'Gate Approved',
				'hint' => 'Approve when goods arrive at campus gate',
				'done' => $all_gate,
			),
			array(
				'id' => 'grn',
				'pending_label' => 'GRN / Enter Stock',
				'done_label' => 'In Inventory',
				'hint' => 'Enter stock into campus / room / subroom',
				'done' => $all_grn,
			),
		);

		$steps = array();
		$current = null;
		$found_current = false;
		foreach ($defs as $def) {
			$state = 'upcoming';
			if ($def['done']) {
				$state = 'done';
			} elseif (!$found_current) {
				$state = 'current';
				$current = $def['id'];
				$found_current = true;
			}
			$steps[] = array(
				'id' => $def['id'],
				'pending_label' => $def['pending_label'],
				'done_label' => $def['done_label'],
				'label' => $def['done'] ? $def['done_label'] : $def['pending_label'],
				'state' => $state,
				'hint' => $def['hint'],
			);
		}
		if (!$found_current) {
			$current = 'complete';
		}

		$title = isset($lines[0]['title']) ? $lines[0]['title'] : $purchase_no;
		$this->_json(array(
			'success' => true,
			'data' => array(
				'purchase_no' => $purchase_no,
				'title' => $title,
				'lines' => $lines,
				'quotes' => $quotes,
				'payments' => $payments,
				'steps' => $steps,
				'current_step' => $current,
				'complete' => $all_grn,
			),
		));
	}

	// ─── Quotes ─────────────────────────────────────────────

	public function quotations()
	{
		$purchase_no = trim((string)$this->input->get('purchase_no'));
		if ($purchase_no === '') $this->_json(array('success' => false, 'message' => 'purchase_no required'), 422);

		$this->db->select('purchase_request_prices.*, purchase_request_prices.purchase_request_price_id AS id, purchase_request_prices.approve AS approved, vendors.name AS vendor_name, vendors.shop_name AS company_name, product_names.product_name, purchase_requests.product_quantity, purchase_requests.campus_id', false);
		$this->db->from('purchase_request_prices');
		$this->db->join('vendors', 'vendors.id = purchase_request_prices.vendor_id', 'left');
		$this->db->join('purchase_requests', 'purchase_requests.purchase_request_id = purchase_request_prices.purchase_request_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->where('purchase_requests.purchase_no', $purchase_no);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function save_quote()
	{
		$body = $this->_body();
		$purchase_request_id = (int)(isset($body['purchase_request_id']) ? $body['purchase_request_id'] : 0);
		$vendor_id = (int)(isset($body['vendor_id']) ? $body['vendor_id'] : 0);
		$price = isset($body['price']) ? (float)$body['price'] : 0;
		if (!$purchase_request_id || !$vendor_id) {
			$this->_json(array('success' => false, 'message' => 'purchase_request_id and vendor_id required'), 422);
		}
		$exists = $this->db->get_where('purchase_request_prices', array(
			'purchase_request_id' => $purchase_request_id,
			'vendor_id' => $vendor_id,
		))->row_array();
		if ($exists) {
			$pk = (int)$exists['purchase_request_price_id'];
			$this->db->where('purchase_request_price_id', $pk)->update('purchase_request_prices', array('price' => $price));
			$id = $pk;
		} else {
			$this->db->insert('purchase_request_prices', array(
				'purchase_request_id' => $purchase_request_id,
				'vendor_id' => $vendor_id,
				'price' => $price,
				'approve' => 0,
			));
			$id = $this->db->insert_id();
		}
		$this->_json(array('success' => true, 'id' => $id));
	}

	public function approve_quote()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : (isset($body['purchase_request_price_id']) ? $body['purchase_request_price_id'] : 0));
		$purchase_request_id = (int)(isset($body['purchase_request_id']) ? $body['purchase_request_id'] : 0);
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);

		$quote = $this->db->get_where('purchase_request_prices', array('purchase_request_price_id' => $id))->row_array();
		if (!$quote) $this->_json(array('success' => false, 'message' => 'Quote not found'), 404);
		if (!$purchase_request_id) $purchase_request_id = (int)$quote['purchase_request_id'];

		$this->db->where('purchase_request_id', $purchase_request_id)->update('purchase_request_prices', array('approve' => 0));
		$this->db->where('purchase_request_price_id', $id)->update('purchase_request_prices', array('approve' => 1));
		$this->db->where('purchase_request_id', $purchase_request_id)->update('purchase_requests', array(
			'purchase_from' => $quote['vendor_id'],
			'purchase_price' => $quote['price'],
			'purchased' => 0,
		));
		$this->_json(array('success' => true));
	}

	public function finalise_quotation()
	{
		$body = $this->_body();
		$purchase_no = isset($body['purchase_no']) ? trim($body['purchase_no']) : '';
		if ($purchase_no === '') $this->_json(array('success' => false, 'message' => 'purchase_no required'), 422);
		$this->db->where('purchase_no', $purchase_no)->update('purchase_requests', array('final' => 1));
		$this->_json(array('success' => true, 'updated' => $this->db->affected_rows()));
	}

	// ─── Purchase orders ────────────────────────────────────

	public function purchase_orders()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('purchase_requests.purchase_no, purchase_requests.title, purchase_requests.campus_id, campuses.campus_name,
			MAX(purchase_requests.purchased) as purchased, COUNT(*) as line_count,
			SUM(purchase_requests.product_quantity * IFNULL(purchase_requests.purchase_price,0)) as total_amount,
			MAX(purchase_requests.created_at) as created_at', false);
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->where('purchase_requests.final', 1);
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->group_by('purchase_requests.purchase_no');
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit(200);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function purchase_order_detail()
	{
		$purchase_no = trim((string)$this->input->get('purchase_no'));
		if ($purchase_no === '') $this->_json(array('success' => false, 'message' => 'purchase_no required'), 422);
		$this->db->select('purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name, vendors.name AS vendor_name, purchase_requests.purchase_from AS vendor_id', false);
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = purchase_requests.subroom_id', 'left');
		$this->db->join('vendors', 'vendors.id = purchase_requests.purchase_from', 'left');
		$this->db->where('purchase_requests.purchase_no', $purchase_no);
		$this->db->where('purchase_requests.final', 1);
		$lines = $this->db->get()->result_array();

		$agreements = array();
		if ($this->db->table_exists('payment_aggrements') && $purchase_no !== '') {
			$agreements = $this->db->get_where('payment_aggrements', array('purchase_no' => $purchase_no))->result_array();
		}
		$this->_json(array('success' => true, 'data' => array('lines' => $lines, 'payments' => $agreements)));
	}

	public function set_purchase_prices()
	{
		$body = $this->_body();
		$items = isset($body['items']) && is_array($body['items']) ? $body['items'] : array();
		foreach ($items as $item) {
			$id = (int)$item['purchase_request_id'];
			$upd = array(
				'purchase_price' => isset($item['purchase_price']) ? (float)$item['purchase_price'] : 0,
			);
			if (isset($item['vendor_id']) && (int)$item['vendor_id'] > 0) {
				$upd['purchase_from'] = (int)$item['vendor_id'];
			}
			$this->db->where('purchase_request_id', $id)->update('purchase_requests', $upd);
		}
		$this->_json(array('success' => true));
	}

	public function payment_agreement()
	{
		$body = $this->_body();
		$purchase_no = isset($body['purchase_no']) ? trim($body['purchase_no']) : '';
		$vendor_id = (int)(isset($body['vendor_id']) ? $body['vendor_id'] : 0);
		$installments = isset($body['installments']) && is_array($body['installments']) ? $body['installments'] : array();
		if ($purchase_no === '' || !count($installments)) {
			$this->_json(array('success' => false, 'message' => 'purchase_no and installments required'), 422);
		}
		$lines = $this->db->get_where('purchase_requests', array('purchase_no' => $purchase_no, 'final' => 1))->result_array();
		if (!count($lines)) $this->_json(array('success' => false, 'message' => 'PO not found'), 404);
		if (!$vendor_id) {
			foreach ($lines as $line) {
				if (!empty($line['purchase_from'])) {
					$vendor_id = (int)$line['purchase_from'];
					break;
				}
			}
		}
		if (!$vendor_id) $this->_json(array('success' => false, 'message' => 'vendor_id required (approve a quote first)'), 422);

		foreach ($installments as $inst) {
			$due = isset($inst['due_date']) ? $inst['due_date'] : (isset($inst['date']) ? $inst['date'] : date('Y-m-d'));
			$this->db->insert('payment_aggrements', array(
				'amount' => (float)$inst['amount'],
				'date' => $due,
				'comment' => isset($inst['comment']) ? $inst['comment'] : '',
				'vendor_id' => $vendor_id,
				'purchase_no' => $purchase_no,
				'paid' => 0,
			));
		}
		$this->db->where(array('purchase_no' => $purchase_no, 'purchase_from' => $vendor_id))
			->update('purchase_requests', array('purchased' => 1));
		$this->_json(array('success' => true));
	}

	// ─── Payments ───────────────────────────────────────────

	public function payments()
	{
		$paid = $this->input->get('paid');
		// Real columns: payment_aggrement_id, amount, date, vendor_id, purchase_no, paid, …
		$this->db->select('payment_aggrements.*, payment_aggrements.payment_aggrement_id AS id, payment_aggrements.date AS due_date, vendors.name AS vendor_name, vendors.shop_name AS shop_name, vendors.phone AS vendor_phone', false);
		$this->db->from('payment_aggrements');
		$this->db->join('vendors', 'vendors.id = payment_aggrements.vendor_id', 'left');
		if ($paid === '0' || $paid === '1') {
			$this->db->where('payment_aggrements.paid', (int)$paid);
		}
		$this->db->order_by('payment_aggrements.date', 'ASC');
		$this->db->limit(300);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function pay_installment()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : (isset($body['payment_aggrement_id']) ? $body['payment_aggrement_id'] : 0));
		// mode: pay (default) | waive — waive = vendor discount / forgiven, no cash out
		$mode = isset($body['mode']) ? trim((string)$body['mode']) : 'pay';
		$note = isset($body['note']) ? trim((string)$body['note']) : '';
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		$row = $this->db->get_where('payment_aggrements', array('payment_aggrement_id' => $id))->row_array();
		if (!$row) {
			$row = $this->db->get_where('payment_aggrements', array('id' => $id))->row_array();
		}
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (!empty($row['paid'])) $this->_json(array('success' => false, 'message' => 'Already settled'), 409);

		$pk_col = isset($row['payment_aggrement_id']) ? 'payment_aggrement_id' : 'id';
		$pk = isset($row['payment_aggrement_id']) ? (int)$row['payment_aggrement_id'] : (int)$row['id'];

		$upd = array('paid' => 1);
		if ($this->db->field_exists('paid_date', 'payment_aggrements')) {
			$upd['paid_date'] = date('Y-m-d');
		}
		if ($this->db->field_exists('paid_by', 'payment_aggrements')) {
			$upd['paid_by'] = $this->current_user['user_id'];
		}

		if ($mode === 'waive') {
			$comment = isset($row['comment']) ? trim((string)$row['comment']) : '';
			$waive_note = $note !== '' ? $note : 'Vendor discount / waived — no cash paid';
			$upd['comment'] = $comment !== '' ? ($comment . ' | ' . $waive_note) : $waive_note;
			if ($this->db->field_exists('paid_type', 'payment_aggrements')) {
				$upd['paid_type'] = 'waive';
			}
			$this->db->where($pk_col, $pk)->update('payment_aggrements', $upd);
			$this->_json(array(
				'success' => true,
				'message' => 'Installment waived (no petty cash / bank cut)',
				'mode' => 'waive',
			));
		}

		// mode=pay: mark settled. Petty-cash / expense side-effects stay in CI Inventory::payment_paid for now.
		if ($this->db->field_exists('paid_type', 'payment_aggrements')) {
			$upd['paid_type'] = 'cash';
		}
		$this->db->where($pk_col, $pk)->update('payment_aggrements', $upd);
		$this->_json(array('success' => true, 'message' => 'Marked paid', 'mode' => 'pay'));
	}

	/** Reduce / correct installment amount before pay (e.g. partial vendor discount). */
	public function update_installment()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : (isset($body['payment_aggrement_id']) ? $body['payment_aggrement_id'] : 0));
		$amount = isset($body['amount']) ? (float)$body['amount'] : -1;
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		if ($amount < 0) $this->_json(array('success' => false, 'message' => 'amount required'), 422);

		$row = $this->db->get_where('payment_aggrements', array('payment_aggrement_id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (!empty($row['paid'])) $this->_json(array('success' => false, 'message' => 'Already settled — cannot edit'), 409);

		$upd = array('amount' => (int)round($amount));
		if (isset($body['comment'])) $upd['comment'] = trim((string)$body['comment']);
		$this->db->where('payment_aggrement_id', $id)->update('payment_aggrements', $upd);
		$this->_json(array('success' => true, 'message' => 'Installment updated', 'amount' => $upd['amount']));
	}

	// ─── Gate / GRN ─────────────────────────────────────────

	public function gate_queue()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name, CONCAT(users.first_name," ",users.last_name) as purchased_by_name', false);
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'inner');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'inner');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = purchase_requests.subroom_id', 'left');
		$this->db->join('users', 'users.user_id = purchase_requests.purchased_by', 'left');
		$this->db->where(array('purchase_requests.purchased' => 1, 'gate_approval' => 0));
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->order_by('purchase_requests.purchase_request_id', 'DESC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function gate_approve()
	{
		$body = $this->_body();
		$ids = isset($body['purchase_request_ids']) && is_array($body['purchase_request_ids']) ? $body['purchase_request_ids'] : array();
		if (!count($ids) && !empty($body['purchase_request_id'])) $ids = array((int)$body['purchase_request_id']);
		if (!count($ids)) $this->_json(array('success' => false, 'message' => 'ids required'), 422);
		$this->db->where_in('purchase_request_id', array_map('intval', $ids));
		$this->db->update('purchase_requests', array('gate_approval' => 1));
		$this->_json(array('success' => true, 'updated' => $this->db->affected_rows()));
	}

	public function grn_queue()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'inner');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'inner');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = purchase_requests.subroom_id', 'left');
		$this->db->where(array('purchase_requests.purchased' => 1, 'gate_approval' => 1, 'approval' => 0));
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function grn_enter()
	{
		$body = $this->_body();
		$ids = array();
		if (!empty($body['purchase_request_ids']) && is_array($body['purchase_request_ids'])) {
			$ids = array_map('intval', $body['purchase_request_ids']);
		} elseif (!empty($body['purchase_request_id'])) {
			$ids = array((int)$body['purchase_request_id']);
		}
		if (!count($ids)) $this->_json(array('success' => false, 'message' => 'purchase_request_id required'), 422);

		$total_qty = 0;
		foreach ($ids as $pr_id) {
			$pr = $this->db->get_where('purchase_requests', array('purchase_request_id' => $pr_id))->row_array();
			if (!$pr) continue;
			if (empty($pr['gate_approval']) || !empty($pr['approval'])) continue;

			$qty = max(1, (int)(isset($body['quantity']) ? $body['quantity'] : $pr['product_quantity']));
			$sale_amount = isset($body['sale_amount']) ? (float)$body['sale_amount'] : (float)(isset($pr['purchase_price']) ? $pr['purchase_price'] : 0);
			$saleable = !empty($body['saleable']) ? 1 : 0;
			$qr = isset($body['qr_code']) ? trim($body['qr_code']) : '';
			$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : (int)$pr['campus_id'];
			$room_id = isset($body['room_id']) && (int)$body['room_id'] > 0 ? (int)$body['room_id'] : (int)$pr['room_id'];
			$subroom_id = isset($body['subroom_id']) ? (int)$body['subroom_id'] : (int)$pr['subroom_id'];

			for ($i = 0; $i < $qty; $i++) {
				$this->db->insert('products', $this->_product_insert_row(array(
					'product_name_id' => $pr['product_name_id'],
					'campus_id' => $campus_id,
					'room_id' => $room_id,
					'subroom_id' => $subroom_id,
					'sale_amount' => $sale_amount,
					'saleable' => $saleable,
					'qr_code' => $qr,
					'purchase_no' => isset($pr['purchase_no']) ? $pr['purchase_no'] : '',
					'estimated_price' => (int)round($sale_amount),
				)));
			}
			$this->db->where('purchase_request_id', $pr_id)->update('purchase_requests', array('approval' => 1));
			$total_qty += $qty;
		}
		$this->_json(array('success' => true, 'quantity' => $total_qty));
	}

	// ─── Issue / GIN ────────────────────────────────────────
	// PK column is require_product_request_id (not id); requester column is user_id

	public function issue_requests()
	{
		if (!$this->db->table_exists('require_product_requests')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('require_product_requests.*, require_product_requests.require_product_request_id AS id, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name, CONCAT(users.first_name," ",users.last_name) AS user_name', false);
		$this->db->from('require_product_requests');
		$this->db->join('campuses', 'campuses.campus_id = require_product_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = require_product_requests.product_name_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = require_product_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = require_product_requests.subroom_id', 'left');
		$this->db->join('users', 'users.user_id = require_product_requests.user_id', 'left');
		$this->_apply_campus_filter('require_product_requests.campus_id', $campus_id);
		$this->db->order_by('require_product_requests.require_product_request_id', 'DESC');
		$this->db->limit(300);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function create_issue_request()
	{
		if (!$this->db->table_exists('require_product_requests')) {
			$this->_json(array('success' => false, 'message' => 'Issue table missing'), 500);
		}
		$body = $this->_body();
		$lines = isset($body['lines']) && is_array($body['lines']) ? $body['lines'] : array();
		if (!count($lines)) $this->_json(array('success' => false, 'message' => 'lines required'), 422);
		$req = $this->_pir_no();
		foreach ($lines as $line) {
			$this->db->insert('require_product_requests', array(
				'request_no' => $req,
				'user_id' => $this->current_user['user_id'],
				'campus_id' => (int)$line['campus_id'],
				'room_id' => isset($line['room_id']) ? (int)$line['room_id'] : 0,
				'subroom_id' => isset($line['subroom_id']) ? (int)$line['subroom_id'] : 0,
				'product_name_id' => (int)$line['product_name_id'],
				'quantity' => max(1, (int)$line['quantity']),
				'comment' => isset($line['comment']) ? $line['comment'] : '',
				'status' => 0,
				'gin' => 0,
				'grn' => 0,
			));
		}
		$this->_json(array('success' => true, 'request_no' => $req));
	}

	public function approve_issue()
	{
		$body = $this->_body();
		$ids = isset($body['ids']) && is_array($body['ids']) ? $body['ids'] : array();
		$status = isset($body['status']) ? (int)$body['status'] : 1;
		if (!count($ids)) $this->_json(array('success' => false, 'message' => 'ids required'), 422);
		$this->db->where_in('require_product_request_id', array_map('intval', $ids));
		$upd = array(
			'status' => $status,
			'approved_by' => $this->current_user['user_id'],
			'approved_at' => date('Y-m-d H:i:s'),
		);
		// optional destination fields from approve form
		if (isset($body['campus_id'])) $upd['campus_id'] = (int)$body['campus_id'];
		if (isset($body['room_id'])) $upd['room_id'] = (int)$body['room_id'];
		if (isset($body['subroom_id'])) $upd['subroom_id'] = (int)$body['subroom_id'];
		$this->db->update('require_product_requests', $upd);
		$this->_json(array('success' => true));
	}

	public function issue_gin()
	{
		$body = $this->_body();
		$ids = array();
		if (!empty($body['ids']) && is_array($body['ids'])) {
			$ids = array_map('intval', $body['ids']);
		} elseif (!empty($body['id'])) {
			$ids = array((int)$body['id']);
		}
		if (!count($ids)) $this->_json(array('success' => false, 'message' => 'id required'), 422);

		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		foreach ($ids as $id) {
			$req = $this->db->get_where('require_product_requests', array('require_product_request_id' => $id))->row_array();
			if (!$req) continue;
			if ((int)$req['status'] !== 1 || !empty($req['gin'])) continue;

			$qty = (int)$req['quantity'];
			$from_campus = (int)(isset($body['from_campus_id']) ? $body['from_campus_id'] : (isset($body['campus_id']) ? $body['campus_id'] : $req['campus_id']));
			$from_room = (int)(isset($body['from_room_id']) ? $body['from_room_id'] : (isset($body['room_id']) ? $body['room_id'] : 0));

			// Match CI: ensure enough units exist at source campus/room before marking GIN
			$this->db->where(array(
				'product_name_id' => $req['product_name_id'],
				'campus_id' => $from_campus,
				'sold' => 0,
				'consume' => 0,
				'status' => 1,
			));
			if ($from_room) $this->db->where('room_id', $from_room);
			$available = $this->db->count_all_results('products');
			if ($available < $qty) {
				$this->_json(array('success' => false, 'message' => 'Insufficient stock at source for request '.$id), 409);
			}

			$this->db->where('require_product_request_id', $id)->update('require_product_requests', array(
				'gin' => 1,
				'gin_campus_id' => $from_campus,
				'gin_room_id' => $from_room,
				'gin_by' => $name,
				'gin_created_at' => date('Y-m-d H:i:s'),
				'gin_comment' => isset($body['gin_comment']) ? $body['gin_comment'] : '',
			));
		}
		$this->_json(array('success' => true));
	}

	public function issue_grn()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : 0);
		$req = $this->db->get_where('require_product_requests', array('require_product_request_id' => $id))->row_array();
		if (!$req) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (empty($req['gin'])) $this->_json(array('success' => false, 'message' => 'GIN not issued yet'), 409);

		$qty = (int)$req['quantity'];
		$from_campus = (int)$req['gin_campus_id'];
		$from_room = (int)$req['gin_room_id'];
		$to_campus = (int)$req['campus_id'];
		$to_room = (int)$req['room_id'];
		$to_sub = (int)$req['subroom_id'];
		// optional override for destination
		if (!empty($body['campus_id'])) $to_campus = (int)$body['campus_id'];
		if (!empty($body['room_id'])) $to_room = (int)$body['room_id'];
		if (isset($body['subroom_id'])) $to_sub = (int)$body['subroom_id'];

		$moved = 0;
		for ($i = 0; $i < $qty; $i++) {
			$this->db->limit(1);
			$where = array(
				'product_name_id' => $req['product_name_id'],
				'campus_id' => $from_campus,
				'sold' => 0,
				'consume' => 0,
				'status' => 1,
			);
			if ($from_room) $where['room_id'] = $from_room;
			$unit = $this->db->get_where('products', $where)->row_array();
			if (!$unit) break;
			$this->db->where('product_id', $unit['product_id'])->update('products', array(
				'campus_id' => $to_campus,
				'room_id' => $to_room ?: null,
				'subroom_id' => $to_sub ?: null,
			));
			$moved++;
		}
		if ($moved < $qty) {
			$this->_json(array('success' => false, 'message' => 'Could not move full quantity from GIN location'), 409);
		}

		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		$this->db->where('require_product_request_id', $id)->update('require_product_requests', array(
			'grn' => 1,
			'grn_by' => $name,
			'grn_created_at' => date('Y-m-d H:i:s'),
			'grn_comment' => isset($body['grn_comment']) ? $body['grn_comment'] : '',
		));
		$this->_json(array('success' => true, 'moved' => $moved));
	}

	// ─── Move ───────────────────────────────────────────────

	public function move_stock()
	{
		$body = $this->_body();
		$product_name_id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$qty = max(1, (int)(isset($body['quantity']) ? $body['quantity'] : 1));
		$from_campus = (int)$body['from_campus_id'];
		$to_campus = (int)$body['to_campus_id'];
		$from_room = (int)(isset($body['from_room_id']) ? $body['from_room_id'] : 0);
		$to_room = (int)(isset($body['to_room_id']) ? $body['to_room_id'] : 0);
		$from_sub = (int)(isset($body['from_subroom_id']) ? $body['from_subroom_id'] : 0);
		$to_sub = (int)(isset($body['to_subroom_id']) ? $body['to_subroom_id'] : 0);

		if (!$product_name_id || !$from_campus || !$to_campus) {
			$this->_json(array('success' => false, 'message' => 'product and campuses required'), 422);
		}

		$moved = 0;
		for ($i = 0; $i < $qty; $i++) {
			$this->db->limit(1);
			$where = array(
				'product_name_id' => $product_name_id,
				'campus_id' => $from_campus,
				'sold' => 0,
				'consume' => 0,
				'status' => 1,
			);
			if ($from_room) $where['room_id'] = $from_room;
			if ($from_sub) $where['subroom_id'] = $from_sub;
			$unit = $this->db->get_where('products', $where)->row_array();
			if (!$unit) break;

			$this->db->where('product_id', $unit['product_id'])->update('products', array(
				'campus_id' => $to_campus,
				'room_id' => $to_room ?: null,
				'subroom_id' => $to_sub ?: null,
			));
			if ($this->db->table_exists('product_history')) {
				$this->db->insert('product_history', array(
					'product_id' => $unit['product_id'],
					'from_campus_id' => $from_campus,
					'to_campus_id' => $to_campus,
					'from_room_id' => $from_room,
					'to_room_id' => $to_room,
					'moved_by' => $this->current_user['user_id'],
					'created_at' => date('Y-m-d H:i:s'),
				));
			}
			$moved++;
		}
		if ($moved < 1) $this->_json(array('success' => false, 'message' => 'No stock to move'), 409);
		$this->_json(array('success' => true, 'moved' => $moved));
	}

	// ─── Returns ────────────────────────────────────────────
	// Columns: return_product_id, product_name_id, purchase_no, return_product_quantity, amount, user_id, reason, status

	public function returns()
	{
		if (!$this->db->table_exists('return_products')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$status = $this->input->get('status');
		$this->db->select('return_products.*, return_products.return_product_id AS id, return_products.return_product_quantity AS quantity, product_names.product_name, CONCAT(users.first_name," ",users.last_name) AS user_name', false);
		$this->db->from('return_products');
		$this->db->join('product_names', 'product_names.product_name_id = return_products.product_name_id', 'left');
		$this->db->join('users', 'users.user_id = return_products.user_id', 'left');
		if ($status !== null && $status !== '') {
			$this->db->where('return_products.status', (int)$status);
		}
		$this->db->order_by('return_products.return_product_id', 'DESC');
		$this->db->limit(200);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function create_return()
	{
		$body = $this->_body();
		$product_name_id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$purchase_no = isset($body['purchase_no']) ? trim($body['purchase_no']) : '';
		$qty = max(1, (int)(isset($body['return_product_quantity']) ? $body['return_product_quantity'] : (isset($body['quantity']) ? $body['quantity'] : 1)));
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		$reason = isset($body['reason']) ? trim($body['reason']) : '';
		if (!$product_name_id || $purchase_no === '') {
			$this->_json(array('success' => false, 'message' => 'product_name_id and purchase_no required'), 422);
		}
		$exists = $this->db->get_where('return_products', array(
			'purchase_no' => $purchase_no,
			'product_name_id' => $product_name_id,
			'status' => 0,
		))->row_array();
		if ($exists) $this->_json(array('success' => false, 'message' => 'Return request already submitted'), 409);

		$this->db->insert('return_products', array(
			'product_name_id' => $product_name_id,
			'purchase_no' => $purchase_no,
			'return_product_quantity' => $qty,
			'amount' => $amount,
			'user_id' => $this->current_user['user_id'],
			'reason' => $reason,
			'status' => 0,
		));
		$this->_json(array('success' => true, 'id' => $this->db->insert_id()));
	}

	public function decide_return()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : (isset($body['return_product_id']) ? $body['return_product_id'] : 0));
		$approve = !empty($body['approve']);
		$row = $this->db->get_where('return_products', array('return_product_id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

		if (!$approve) {
			// CI rejects by deleting the request row
			$this->db->where('return_product_id', $id)->delete('return_products');
			$this->_json(array('success' => true, 'message' => 'Rejected'));
		}

		$this->db->where('return_product_id', $id)->update('return_products', array('status' => 1));

		$qty = (int)$row['return_product_quantity'];
		$deleted = 0;
		for ($i = 0; $i < $qty; $i++) {
			$this->db->limit(1);
			$product = $this->db->get_where('products', array(
				'product_name_id' => $row['product_name_id'],
				'purchase_no' => $row['purchase_no'],
				'consume' => 0,
			))->row_array();
			if (!$product) break;
			$this->db->where('product_id', $product['product_id'])->delete('products');
			$deleted++;
		}
		// Petty-cash side-effect stays in CI Inventory::return_request_update for full parity
		$this->_json(array('success' => true, 'message' => 'Approved', 'deleted' => $deleted));
	}

	// ─── QR ─────────────────────────────────────────────────

	public function qr_list()
	{
		if (!$this->db->table_exists('inventory_qr')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->order_by('id', 'DESC');
		$this->db->limit(200);
		$this->_json(array('success' => true, 'data' => $this->db->get('inventory_qr')->result_array()));
	}

	public function generate_qr()
	{
		$body = $this->_body();
		$count = max(1, min(100, (int)(isset($body['count']) ? $body['count'] : 1)));
		$created = array();
		for ($i = 0; $i < $count; $i++) {
			$code = 'inv_qr-' . time() . '-' . mt_rand(1000, 9999) . '-' . $i;
			if ($this->db->table_exists('inventory_qr')) {
				$this->db->insert('inventory_qr', array(
					'qr_code' => $code,
					'created_by' => $this->current_user['user_id'],
					'created_at' => date('Y-m-d H:i:s'),
					'used' => 0,
				));
			}
			$created[] = $code;
		}
		$this->_json(array('success' => true, 'data' => $created));
	}

	// ─── Rules ──────────────────────────────────────────────

	public function rules()
	{
		$out = array(
			'expense_category' => $this->db->table_exists('default_expense_category_inventory')
				? $this->db->get('default_expense_category_inventory')->result_array() : array(),
			'return_category' => $this->db->table_exists('default_return_category_inventory')
				? $this->db->get('default_return_category_inventory')->result_array() : array(),
			'room_rules' => $this->db->table_exists('default_room_rules')
				? $this->db->get('default_room_rules')->result_array() : array(),
			'expense_categories' => $this->db->get_where('expense_category', 'sub_of IS NULL')->result_array(),
		);
		$this->_json(array('success' => true, 'data' => $out));
	}

	public function save_rules()
	{
		$body = $this->_body();
		if (isset($body['expense_category_id']) && $this->db->table_exists('default_expense_category_inventory')) {
			$this->db->truncate('default_expense_category_inventory');
			$this->db->insert('default_expense_category_inventory', array(
				'expense_category_id' => (int)$body['expense_category_id'],
			));
		}
		if (isset($body['return_category_id']) && $this->db->table_exists('default_return_category_inventory')) {
			$this->db->truncate('default_return_category_inventory');
			$this->db->insert('default_return_category_inventory', array(
				'expense_category_id' => (int)$body['return_category_id'],
			));
		}
		$this->_json(array('success' => true));
	}

	public function campuses()
	{
		$ids = $this->_inventory_campus_ids();
		$this->db->from('campuses');
		$this->db->where('status', 1);
		if (!$this->_is_admin() && count($ids)) {
			$this->db->where_in('campus_id', $ids);
		} elseif (!$this->_is_admin()) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->order_by('campus_name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	/**
	 * Action dashboard — everything that needs attention in one payload.
	 * GET inventoryapi/dashboard?campus_id=
	 */
	public function dashboard()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$limit = 8;

		// Pending PR approval (open, not final)
		$this->db->select('purchase_requests.purchase_request_id, purchase_requests.purchase_no, purchase_requests.title, purchase_requests.product_quantity, purchase_requests.status, campuses.campus_name, product_names.product_name, rooms.room_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->where(array('purchase_requests.final' => 0, 'purchase_requests.status' => 0));
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->order_by('purchase_requests.purchase_request_id', 'DESC');
		$this->db->limit($limit);
		$pending_prs = $this->db->get()->result_array();

		$this->db->from('purchase_requests');
		$this->db->where(array('final' => 0, 'status' => 0));
		$this->_apply_campus_filter('campus_id', $campus_id, true);
		$pending_pr_count = $this->db->count_all_results();

		// Ready for quotes / finalise (approved, not final)
		$this->db->select('purchase_requests.purchase_no, purchase_requests.title, campuses.campus_name, COUNT(*) as line_count,
			SUM(CASE WHEN purchase_request_prices.approve = 1 THEN 1 ELSE 0 END) as approved_quotes', false);
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('purchase_request_prices', 'purchase_request_prices.purchase_request_id = purchase_requests.purchase_request_id', 'left');
		$this->db->where(array('purchase_requests.final' => 0, 'purchase_requests.status' => 1));
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->group_by('purchase_requests.purchase_no');
		$this->db->order_by('purchase_requests.purchase_no', 'DESC');
		$this->db->limit($limit);
		$quote_prs = $this->db->get()->result_array();

		$this->db->from('purchase_requests');
		$this->db->where(array('final' => 0, 'status' => 1));
		$this->_apply_campus_filter('campus_id', $campus_id, true);
		$this->db->select('purchase_no');
		$this->db->group_by('purchase_no');
		$quote_pr_count = count($this->db->get()->result_array());

		// Unpaid payments
		$this->db->select('payment_aggrements.*, payment_aggrements.payment_aggrement_id AS id, payment_aggrements.date AS due_date, vendors.name AS vendor_name', false);
		$this->db->from('payment_aggrements');
		$this->db->join('vendors', 'vendors.id = payment_aggrements.vendor_id', 'left');
		$this->db->where('payment_aggrements.paid', 0);
		$this->db->order_by('payment_aggrements.date', 'ASC');
		$this->db->limit($limit);
		$payments = $this->db->get()->result_array();
		$payment_count = $this->db->where('paid', 0)->count_all_results('payment_aggrements');

		// Gate
		$this->db->select('purchase_requests.purchase_request_id, purchase_requests.purchase_no, purchase_requests.product_quantity, campuses.campus_name, product_names.product_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->where(array('purchase_requests.purchased' => 1, 'purchase_requests.gate_approval' => 0));
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->order_by('purchase_requests.purchase_request_id', 'DESC');
		$this->db->limit($limit);
		$gate = $this->db->get()->result_array();

		$this->db->from('purchase_requests');
		$this->db->where(array('purchased' => 1, 'gate_approval' => 0));
		$this->_apply_campus_filter('campus_id', $campus_id, true);
		$gate_count = $this->db->count_all_results();

		// GRN stock entry
		$this->db->select('purchase_requests.purchase_request_id, purchase_requests.purchase_no, purchase_requests.product_quantity, campuses.campus_name, product_names.product_name, rooms.room_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->where(array('purchase_requests.purchased' => 1, 'purchase_requests.gate_approval' => 1, 'purchase_requests.approval' => 0));
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->order_by('purchase_requests.purchase_request_id', 'DESC');
		$this->db->limit($limit);
		$grn = $this->db->get()->result_array();

		$this->db->from('purchase_requests');
		$this->db->where(array('purchased' => 1, 'gate_approval' => 1, 'approval' => 0));
		$this->_apply_campus_filter('campus_id', $campus_id, true);
		$grn_count = $this->db->count_all_results();

		$issues_pending = array();
		$issues_pending_count = 0;
		$gin_queue = array();
		$gin_count = 0;
		$issue_grn_queue = array();
		$issue_grn_count = 0;
		if ($this->db->table_exists('require_product_requests')) {
			$this->db->select('require_product_requests.*, require_product_requests.require_product_request_id AS id, campuses.campus_name, product_names.product_name', false);
			$this->db->from('require_product_requests');
			$this->db->join('campuses', 'campuses.campus_id = require_product_requests.campus_id', 'left');
			$this->db->join('product_names', 'product_names.product_name_id = require_product_requests.product_name_id', 'left');
			$this->db->where(array('require_product_requests.status' => 0, 'require_product_requests.gin' => 0));
			$this->_apply_campus_filter('require_product_requests.campus_id', $campus_id);
			$this->db->order_by('require_product_requests.require_product_request_id', 'DESC');
			$this->db->limit($limit);
			$issues_pending = $this->db->get()->result_array();

			$this->db->from('require_product_requests');
			$this->db->where(array('status' => 0, 'gin' => 0));
			$this->_apply_campus_filter('campus_id', $campus_id);
			$issues_pending_count = $this->db->count_all_results();

			$this->db->select('require_product_requests.*, require_product_requests.require_product_request_id AS id, campuses.campus_name, product_names.product_name', false);
			$this->db->from('require_product_requests');
			$this->db->join('campuses', 'campuses.campus_id = require_product_requests.campus_id', 'left');
			$this->db->join('product_names', 'product_names.product_name_id = require_product_requests.product_name_id', 'left');
			$this->db->where(array('require_product_requests.status' => 1, 'require_product_requests.gin' => 0));
			$this->_apply_campus_filter('require_product_requests.campus_id', $campus_id);
			$this->db->order_by('require_product_requests.require_product_request_id', 'DESC');
			$this->db->limit($limit);
			$gin_queue = $this->db->get()->result_array();

			$this->db->from('require_product_requests');
			$this->db->where(array('status' => 1, 'gin' => 0));
			$this->_apply_campus_filter('campus_id', $campus_id);
			$gin_count = $this->db->count_all_results();

			$this->db->select('require_product_requests.*, require_product_requests.require_product_request_id AS id, campuses.campus_name, product_names.product_name', false);
			$this->db->from('require_product_requests');
			$this->db->join('campuses', 'campuses.campus_id = require_product_requests.campus_id', 'left');
			$this->db->join('product_names', 'product_names.product_name_id = require_product_requests.product_name_id', 'left');
			$this->db->where(array('require_product_requests.gin' => 1, 'require_product_requests.grn' => 0));
			$this->_apply_campus_filter('require_product_requests.campus_id', $campus_id);
			$this->db->order_by('require_product_requests.require_product_request_id', 'DESC');
			$this->db->limit($limit);
			$issue_grn_queue = $this->db->get()->result_array();

			$this->db->from('require_product_requests');
			$this->db->where(array('gin' => 1, 'grn' => 0));
			$this->_apply_campus_filter('campus_id', $campus_id);
			$issue_grn_count = $this->db->count_all_results();
		}

		$returns = array();
		$returns_count = 0;
		if ($this->db->table_exists('return_products')) {
			$this->db->select('return_products.*, return_products.return_product_id AS id, return_products.return_product_quantity AS quantity, product_names.product_name', false);
			$this->db->from('return_products');
			$this->db->join('product_names', 'product_names.product_name_id = return_products.product_name_id', 'left');
			$this->db->where('return_products.status', 0);
			$this->db->order_by('return_products.return_product_id', 'DESC');
			$this->db->limit($limit);
			$returns = $this->db->get()->result_array();
			$returns_count = $this->db->where('status', 0)->count_all_results('return_products');
		}

		$total_actions = $pending_pr_count + $quote_pr_count + $payment_count + $gate_count + $grn_count
			+ $issues_pending_count + $gin_count + $issue_grn_count + $returns_count;

		$this->_json(array(
			'success' => true,
			'data' => array(
				'total_actions' => $total_actions,
				'counts' => array(
					'pending_prs' => $pending_pr_count,
					'quotes' => $quote_pr_count,
					'payments' => $payment_count,
					'gate' => $gate_count,
					'grn' => $grn_count,
					'issues' => $issues_pending_count,
					'gin' => $gin_count,
					'issue_grn' => $issue_grn_count,
					'returns' => $returns_count,
				),
				'pending_prs' => $pending_prs,
				'quotes' => $quote_prs,
				'payments' => $payments,
				'gate' => $gate,
				'grn' => $grn,
				'issues' => $issues_pending,
				'gin_queue' => $gin_queue,
				'issue_grn' => $issue_grn_queue,
				'returns' => $returns,
			),
		));
	}
}
