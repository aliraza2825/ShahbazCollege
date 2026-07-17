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

	/** Display name for audit trails (approve / payment / gate / GRN). */
	private function _actor_name()
	{
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		return $name !== '' ? $name : 'POS';
	}

	/**
	 * Ensure who/when columns exist for journey approval steps.
	 * payment_aggrements + purchase_requests audit fields.
	 */
	private function _ensure_journey_audit_columns()
	{
		if ($this->db->table_exists('payment_aggrements')) {
			$pa_cols = array(
				'created_by' => "ALTER TABLE `payment_aggrements` ADD `created_by` VARCHAR(255) NULL DEFAULT NULL",
				'created_at' => "ALTER TABLE `payment_aggrements` ADD `created_at` DATETIME NULL DEFAULT NULL",
				'paid_by' => "ALTER TABLE `payment_aggrements` ADD `paid_by` VARCHAR(255) NULL DEFAULT NULL",
				'paid_at' => "ALTER TABLE `payment_aggrements` ADD `paid_at` DATETIME NULL DEFAULT NULL",
				'paid_type' => "ALTER TABLE `payment_aggrements` ADD `paid_type` VARCHAR(32) NULL DEFAULT NULL",
			);
			foreach ($pa_cols as $col => $sql) {
				if (!$this->db->field_exists($col, 'payment_aggrements')) {
					$this->db->query($sql);
				}
			}
		}
		$pr_cols = array(
			'quote_select_by' => "ALTER TABLE `purchase_requests` ADD `quote_select_by` VARCHAR(255) NULL DEFAULT NULL",
			'quote_select_at' => "ALTER TABLE `purchase_requests` ADD `quote_select_at` DATETIME NULL DEFAULT NULL",
			'payment_agree_by' => "ALTER TABLE `purchase_requests` ADD `payment_agree_by` VARCHAR(255) NULL DEFAULT NULL",
			'payment_agree_at' => "ALTER TABLE `purchase_requests` ADD `payment_agree_at` DATETIME NULL DEFAULT NULL",
			'gate_approve_by' => "ALTER TABLE `purchase_requests` ADD `gate_approve_by` VARCHAR(255) NULL DEFAULT NULL",
			'gate_approve_at' => "ALTER TABLE `purchase_requests` ADD `gate_approve_at` DATETIME NULL DEFAULT NULL",
			'gate_received_qty' => "ALTER TABLE `purchase_requests` ADD `gate_received_qty` INT(11) NOT NULL DEFAULT 0",
			'grn_by' => "ALTER TABLE `purchase_requests` ADD `grn_by` VARCHAR(255) NULL DEFAULT NULL",
			'grn_at' => "ALTER TABLE `purchase_requests` ADD `grn_at` DATETIME NULL DEFAULT NULL",
			'project_id' => "ALTER TABLE `purchase_requests` ADD `project_id` INT(11) NULL DEFAULT NULL",
		);
		foreach ($pr_cols as $col => $sql) {
			if (!$this->db->field_exists($col, 'purchase_requests')) {
				$this->db->query($sql);
			}
		}
		$this->_ensure_gate_receive_table();
	}

	/** Log of partial gate receives (multi-day entries by security). */
	private function _ensure_gate_receive_table()
	{
		if ($this->db->table_exists('purchase_gate_receives')) return;
		$this->db->query("CREATE TABLE `purchase_gate_receives` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`purchase_request_id` INT(11) NOT NULL,
			`purchase_no` VARCHAR(255) NOT NULL,
			`quantity` INT(11) NOT NULL DEFAULT 0,
			`received_by` VARCHAR(255) NULL DEFAULT NULL,
			`received_at` DATETIME NULL DEFAULT NULL,
			`comment` VARCHAR(255) NULL DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `purchase_request_id` (`purchase_request_id`),
			KEY `purchase_no` (`purchase_no`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
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
		$consumeable = !empty($opts['consumeable']) ? 1 : 0;
		$returnable = $saleable && !empty($opts['returnable']) ? 1 : 0;
		$expire = !empty($opts['expire']) ? 1 : 0;
		$guarantee = !empty($opts['product_guarantee']) ? 1 : 0;
		$sale_amount = $saleable
			? (isset($opts['sale_amount']) ? $opts['sale_amount'] : 0)
			: '';
		$qr = isset($opts['qr_code']) ? trim((string)$opts['qr_code']) : '';
		if ($qr !== '' && strpos($qr, 'inv_qr-') !== 0) $qr = 'inv_qr-' . $qr;

		$row = array(
			'campus_id' => (int)$opts['campus_id'],
			'room_id' => !empty($opts['room_id']) ? (int)$opts['room_id'] : 0,
			'subroom_id' => !empty($opts['subroom_id']) ? (int)$opts['subroom_id'] : 0,
			'product_name_id' => (int)$opts['product_name_id'],
			'product_image' => isset($opts['product_image']) ? (string)$opts['product_image'] : '',
			'online_product_image' => '',
			'purchase_slip' => isset($opts['purchase_slip']) ? (string)$opts['purchase_slip'] : '',
			'online_purchase_slip' => '',
			'product_quantity' => 1,
			'remaining_quantity' => 1,
			'qr_code' => $qr !== '' ? $qr : null,
			'estimated_price' => isset($opts['estimated_price']) ? (int)$opts['estimated_price'] : 0,
			'product_guarantee' => $guarantee,
			'product_guarantee_start_date' => $guarantee && !empty($opts['product_guarantee_start_date'])
				? $opts['product_guarantee_start_date']
				: '0000-00-00',
			'product_guarantee_end_date' => $guarantee && !empty($opts['product_guarantee_end_date'])
				? $opts['product_guarantee_end_date']
				: '0000-00-00',
			'remarks' => isset($opts['remarks']) ? (string)$opts['remarks'] : '',
			'add_by' => $name !== '' ? $name : 'POS',
			'last_edit' => $name !== '' ? $name : 'POS',
			'clear_by' => '',
			'status' => 1,
			'consumeable' => $consumeable,
			'consume' => 0,
			'consume_reason' => '',
			'saleable' => $saleable,
			'sale_amount' => $sale_amount === '' || $sale_amount === null ? null : (int)round((float)$sale_amount),
			'expire' => $expire,
			'returnable' => $returnable,
			'sold_amount' => '',
			'sold' => 0,
			'purchase_no' => isset($opts['purchase_no']) ? (string)$opts['purchase_no'] : '',
			'user_id' => 0,
			'reponsilble_user_id' => 0,
			'upload_image' => 0,
		);
		if ($this->db->field_exists('expire_date', 'products')) {
			$row['expire_date'] = $expire && !empty($opts['expire_date']) ? $opts['expire_date'] : null;
		}
		return $row;
	}

	/** Next / existing QR number for a product name (legacy getProductQR). */
	private function _next_product_qr($product_name_id = 0)
	{
		$product_name_id = (int)$product_name_id;
		if ($product_name_id > 0) {
			$this->db->limit(1);
			$product = $this->db->get_where('products', array('product_name_id' => $product_name_id))->row_array();
			if ($product && !empty($product['qr_code'])) {
				return str_replace('inv_qr-', '', $product['qr_code']);
			}
		}
		$query = 'SELECT CAST(SUBSTRING(qr_code,8,LENGTH(qr_code)) AS UNSIGNED) as qr_code FROM products WHERE qr_code LIKE "inv_qr-%" ORDER BY qr_code DESC LIMIT 1';
		$number = $this->db->query($query)->row_array();
		$n = $number && isset($number['qr_code']) ? ((int)$number['qr_code'] + 1) : 1;
		return (string)$n;
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
		$subroom_id = (int)$this->input->get('subroom_id');
		// kind: saleable|consumable|returnable|all  (legacy status: available|consumed|sold)
		$type = trim((string)$this->input->get('type'));

		$this->db->select('product_names.product_name_id, product_names.product_name,
			COUNT(products.product_id) as stock,
			MIN(products.product_id) as product_id,
			MIN(products.sale_amount) as sale_amount,
			MAX(NULLIF(products.product_image, "")) as product_image,
			MIN(products.campus_id) as campus_id, MIN(products.room_id) as room_id, MIN(products.subroom_id) as subroom_id,
			MAX(campuses.campus_name) as campus_name, MAX(rooms.room_name) as room_name, MAX(subrooms.subroom_name) as subroom_name,
			SUM(CASE WHEN products.saleable=1 THEN 1 ELSE 0 END) as saleable_count,
			SUM(CASE WHEN products.consumeable=1 THEN 1 ELSE 0 END) as consumeable_count,
			SUM(CASE WHEN products.returnable=1 THEN 1 ELSE 0 END) as returnable_count,
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
		if ($subroom_id > 0) $this->db->where('products.subroom_id', $subroom_id);

		if ($type === 'consumed') {
			$this->db->where(array('products.consume' => 1, 'products.sold' => 0));
		} elseif ($type === 'sold') {
			$this->db->where(array('products.sold' => 1, 'products.consume' => 0));
		} else {
			// Kind filters (saleable|consumable|returnable|all|available) = in-stock units only.
			$this->db->where(array('products.sold' => 0, 'products.consume' => 0));
			if ($type === 'saleable') {
				$this->db->where('products.saleable', 1);
			} elseif ($type === 'consumable' || $type === 'consumeable') {
				$this->db->where('products.consumeable', 1);
			} elseif ($type === 'returnable') {
				$this->db->where('products.returnable', 1);
			}
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
		$type = trim((string)$this->input->get('type'));
		if ($type === 'consumed') {
			$this->db->where(array('products.consume' => 1, 'products.sold' => 0));
		} elseif ($type === 'sold') {
			$this->db->where(array('products.sold' => 1, 'products.consume' => 0));
		} elseif ($type === '' || $type === 'available' || $type === 'all' || $type === 'saleable' || $type === 'consumable' || $type === 'consumeable' || $type === 'returnable') {
			$this->db->where(array('products.sold' => 0, 'products.consume' => 0));
		}
		$this->db->order_by('products.product_id', 'DESC');
		$this->db->limit(200);
		$rows = $this->db->get()->result_array();
		foreach ($rows as &$row) {
			$row['image_url'] = $this->_img_url(isset($row['product_image']) ? $row['product_image'] : '');
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function next_qr()
	{
		$product_name_id = (int)$this->input->get('product_name_id');
		$code = $this->_next_product_qr($product_name_id);
		$this->_json(array('success' => true, 'qr_code' => $code));
	}

	/** Upload product_image / purchase_slip into inventory_images/ */
	public function upload_file()
	{
		$dir = FCPATH . 'inventory_images/';
		if (!is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}

		$filename = '';
		if (!empty($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
			$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
			$allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf');
			if (!in_array($ext, $allowed)) {
				$this->_json(array('success' => false, 'message' => 'Only jpg, png, gif, webp, pdf allowed'), 422);
			}
			if ($_FILES['file']['size'] > 8 * 1024 * 1024) {
				$this->_json(array('success' => false, 'message' => 'Max 8MB file'), 422);
			}
			$filename = 'inv_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
			if (!move_uploaded_file($_FILES['file']['tmp_name'], $dir . $filename)) {
				$this->_json(array('success' => false, 'message' => 'Upload failed'), 500);
			}
		} else {
			$body = $this->_body();
			$b64 = isset($body['file_base64']) ? $body['file_base64'] : '';
			if ($b64 === '') {
				$this->_json(array('success' => false, 'message' => 'No file uploaded'), 422);
			}
			$ext = 'jpg';
			if (preg_match('#^data:([\w/+.-]+);base64,#', $b64, $m)) {
				$mime = strtolower($m[1]);
				if (strpos($mime, 'png') !== false) $ext = 'png';
				elseif (strpos($mime, 'gif') !== false) $ext = 'gif';
				elseif (strpos($mime, 'webp') !== false) $ext = 'webp';
				elseif (strpos($mime, 'pdf') !== false) $ext = 'pdf';
				$b64 = substr($b64, strpos($b64, ',') + 1);
			}
			$bin = base64_decode($b64);
			if ($bin === false || strlen($bin) < 10) {
				$this->_json(array('success' => false, 'message' => 'Invalid file data'), 422);
			}
			$filename = 'inv_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
			if (file_put_contents($dir . $filename, $bin) === false) {
				$this->_json(array('success' => false, 'message' => 'Upload failed'), 500);
			}
		}

		$this->_json(array(
			'success' => true,
			'filename' => $filename,
			'url' => $this->_img_url($filename),
		));
	}

	public function add_stock()
	{
		$body = $this->_body();
		$product_name_id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		$room_id = (int)(isset($body['room_id']) ? $body['room_id'] : 0);
		$subroom_id = (int)(isset($body['subroom_id']) ? $body['subroom_id'] : 0);
		$qty = max(1, (int)(isset($body['quantity']) ? $body['quantity'] : 1));
		$estimated_price = isset($body['estimated_price']) ? (int)$body['estimated_price'] : 0;
		$saleable = !empty($body['saleable']) ? 1 : 0;
		$consumeable = !empty($body['consumeable']) || !empty($body['consumable']) ? 1 : 0;
		$returnable = $saleable && !empty($body['returnable']) ? 1 : 0;
		$sale_amount = $saleable && isset($body['sale_amount']) ? (float)$body['sale_amount'] : 0;
		$expire = !empty($body['expire']) ? 1 : 0;
		$expire_date = $expire && !empty($body['expire_date']) ? trim($body['expire_date']) : null;
		$guarantee = !empty($body['product_guarantee']) ? 1 : 0;
		$g_start = $guarantee && !empty($body['product_guarantee_start_date'])
			? trim($body['product_guarantee_start_date']) : '0000-00-00';
		$g_end = $guarantee && !empty($body['product_guarantee_end_date'])
			? trim($body['product_guarantee_end_date']) : '0000-00-00';
		$remarks = isset($body['remarks']) ? trim($body['remarks']) : '';
		$qr = isset($body['qr_code']) ? trim($body['qr_code']) : '';
		$image = isset($body['product_image']) ? trim($body['product_image']) : '';
		$slip = isset($body['purchase_slip']) ? trim($body['purchase_slip']) : '';

		if (!$product_name_id || !$campus_id) {
			$this->_json(array('success' => false, 'message' => 'Product name and campus required'), 422);
		}
		if ($room_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'Room is required'), 422);
		}
		if ($estimated_price < 1) {
			$this->_json(array('success' => false, 'message' => 'Estimated purchased price is required (1 unit)'), 422);
		}
		if ($saleable && $sale_amount < 1) {
			$this->_json(array('success' => false, 'message' => 'Sale amount is required for saleable items'), 422);
		}
		if ($expire && !$expire_date) {
			$this->_json(array('success' => false, 'message' => 'Expire date is required'), 422);
		}
		$this->_assert_campus_access($campus_id);

		if ($qr === '') {
			$qr = $this->_next_product_qr($product_name_id);
		}

		for ($i = 0; $i < $qty; $i++) {
			$this->db->insert('products', $this->_product_insert_row(array(
				'product_name_id' => $product_name_id,
				'campus_id' => $campus_id,
				'room_id' => $room_id,
				'subroom_id' => $subroom_id,
				'estimated_price' => $estimated_price,
				'sale_amount' => $sale_amount,
				'saleable' => $saleable,
				'consumeable' => $consumeable,
				'returnable' => $returnable,
				'expire' => $expire,
				'expire_date' => $expire_date,
				'product_guarantee' => $guarantee,
				'product_guarantee_start_date' => $g_start,
				'product_guarantee_end_date' => $g_end,
				'remarks' => $remarks,
				'qr_code' => $qr,
				'product_image' => $image,
				'purchase_slip' => $slip,
			)));
		}
		$this->_json(array('success' => true, 'message' => 'Stock added', 'quantity' => $qty, 'qr_code' => $qr));
	}

	public function consume()
	{
		$body = $this->_body();
		$reason = isset($body['consume_reason']) ? trim((string)$body['consume_reason']) : '';
		$qty = isset($body['quantity']) ? max(1, (int)$body['quantity']) : 0;
		$seed_id = (int)(isset($body['product_id']) ? $body['product_id'] : 0);

		// Legacy modal: product_id + quantity (+ reason) at same location
		if ($seed_id > 0 && $qty > 0) {
			$seed = $this->db->get_where('products', array('product_id' => $seed_id))->row_array();
			if (!$seed) $this->_json(array('success' => false, 'message' => 'Product not found'), 404);
			$this->_assert_campus_access((int)$seed['campus_id']);
			$updated = 0;
			for ($i = 0; $i < $qty; $i++) {
				$this->db->limit(1);
				$unit = $this->db->get_where('products', array(
					'product_name_id' => $seed['product_name_id'],
					'campus_id' => $seed['campus_id'],
					'room_id' => $seed['room_id'],
					'subroom_id' => $seed['subroom_id'],
					'sold' => 0,
					'consume' => 0,
					'status' => 1,
					'consumeable' => 1,
				))->row_array();
				if (!$unit) break;
				$upd = array(
					'consume' => 1,
					'consume_date' => date('Y-m-d'),
				);
				if ($this->db->field_exists('consume_reason', 'products')) {
					$upd['consume_reason'] = $reason;
				}
				$this->db->where('product_id', $unit['product_id'])->update('products', $upd);
				$updated++;
			}
			if ($updated < 1) {
				$this->_json(array('success' => false, 'message' => 'No consumable stock at this location'), 422);
			}
			$this->_json(array('success' => true, 'updated' => $updated));
		}

		$ids = isset($body['product_ids']) && is_array($body['product_ids']) ? $body['product_ids'] : array();
		if (!count($ids) && $seed_id > 0) $ids = array($seed_id);
		if (!count($ids)) $this->_json(array('success' => false, 'message' => 'product_ids required'), 422);

		$upd = array('consume' => 1, 'consume_date' => date('Y-m-d'));
		if ($this->db->field_exists('consume_reason', 'products') && $reason !== '') {
			$upd['consume_reason'] = $reason;
		}
		$this->db->where_in('product_id', array_map('intval', $ids));
		// Only consumable units can be marked consumed (legacy Inventory rule).
		$this->db->where(array('sold' => 0, 'consume' => 0, 'status' => 1, 'consumeable' => 1));
		$this->db->update('products', $upd);
		$updated = $this->db->affected_rows();
		if ($updated < 1) {
			$this->_json(array('success' => false, 'message' => 'Only consumable units can be consumed'), 422);
		}
		$this->_json(array('success' => true, 'updated' => $updated));
	}

	/** Single product row for edit form */
	public function product_detail()
	{
		$id = (int)$this->input->get('product_id');
		if (!$id) $this->_json(array('success' => false, 'message' => 'product_id required'), 422);
		$this->db->select('products.*, campuses.campus_name, rooms.room_name, subrooms.subroom_name, product_names.product_name');
		$this->db->from('products');
		$this->db->join('product_names', 'product_names.product_name_id = products.product_name_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id = products.campus_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = products.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = products.subroom_id', 'left');
		$this->db->where('products.product_id', $id);
		$row = $this->db->get()->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->_assert_campus_access((int)$row['campus_id']);
		$row['image_url'] = $this->_img_url(isset($row['product_image']) ? $row['product_image'] : '');
		$row['slip_url'] = $this->_img_url(isset($row['purchase_slip']) ? $row['purchase_slip'] : '');
		$this->_json(array('success' => true, 'data' => $row));
	}

	/**
	 * Update product fields (legacy edit_product / edit_bulk_products).
	 * bulk=1 updates all units at same name+campus+room+subroom.
	 */
	public function update_product()
	{
		$body = $this->_body();
		$id = (int)(isset($body['product_id']) ? $body['product_id'] : 0);
		if (!$id) $this->_json(array('success' => false, 'message' => 'product_id required'), 422);
		$seed = $this->db->get_where('products', array('product_id' => $id))->row_array();
		if (!$seed) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->_assert_campus_access((int)$seed['campus_id']);

		$guarantee = !empty($body['product_guarantee']) ? 1 : 0;
		$saleable = !empty($body['saleable']) ? 1 : 0;
		$expire = !empty($body['expire']) ? 1 : 0;
		$consumeable = !empty($body['consumeable']) ? 1 : 0;
		$returnable = $saleable ? (!empty($body['returnable']) ? 1 : 0) : 0;
		$sale_amount = $saleable ? (int)(isset($body['sale_amount']) ? $body['sale_amount'] : 0) : 0;
		$estimated_price = (int)(isset($body['estimated_price']) ? $body['estimated_price'] : $seed['estimated_price']);
		$remarks = isset($body['remarks']) ? trim((string)$body['remarks']) : (string)$seed['remarks'];

		$data = array(
			'estimated_price' => $estimated_price,
			'product_guarantee' => $guarantee,
			'product_guarantee_start_date' => $guarantee ? (isset($body['product_guarantee_start_date']) ? $body['product_guarantee_start_date'] : $seed['product_guarantee_start_date']) : '0000-00-00',
			'product_guarantee_end_date' => $guarantee ? (isset($body['product_guarantee_end_date']) ? $body['product_guarantee_end_date'] : $seed['product_guarantee_end_date']) : '0000-00-00',
			'remarks' => $remarks,
			'consumeable' => $consumeable,
			'saleable' => $saleable,
			'sale_amount' => $sale_amount,
			'returnable' => $returnable,
			'expire' => $expire,
			'expire_date' => $expire ? (isset($body['expire_date']) ? $body['expire_date'] : $seed['expire_date']) : null,
			'last_edit' => $this->_actor_name(),
		);

		$bulk = !empty($body['bulk']);
		// Images only on single-unit edit (legacy)
		if (!$bulk) {
			if (isset($body['product_image']) && $body['product_image'] !== '') {
				$data['product_image'] = $body['product_image'];
			}
			if (isset($body['purchase_slip']) && $body['purchase_slip'] !== '') {
				$data['purchase_slip'] = $body['purchase_slip'];
			}
		}

		if ($bulk) {
			$this->db->where(array(
				'campus_id' => $seed['campus_id'],
				'room_id' => $seed['room_id'],
				'subroom_id' => $seed['subroom_id'],
				'product_name_id' => $seed['product_name_id'],
			))->update('products', $data);
		} else {
			$this->db->where('product_id', $id)->update('products', $data);
		}
		$this->_json(array('success' => true, 'updated' => $this->db->affected_rows(), 'bulk' => $bulk ? 1 : 0));
	}

	/** Move history for a unit (legacy getProductHistory) */
	public function move_history()
	{
		$id = (int)$this->input->get('product_id');
		if (!$id) $this->_json(array('success' => false, 'message' => 'product_id required'), 422);
		if (!$this->db->table_exists('product_history')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->select('product_history.*, campuses.campus_name, rooms.room_name, subrooms.subroom_name');
		$this->db->from('product_history');
		$this->db->join('campuses', 'campuses.campus_id = product_history.campus_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = product_history.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = product_history.subroom_id', 'left');
		$this->db->where('product_history.product_id', $id);
		$this->db->order_by('product_history.created_at', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	/** Recent consume history for same product name + campus (legacy getProductConsumeHistory) */
	public function consume_history()
	{
		$id = (int)$this->input->get('product_id');
		if (!$id) $this->_json(array('success' => false, 'message' => 'product_id required'), 422);
		$seed = $this->db->get_where('products', array('product_id' => $id))->row_array();
		if (!$seed) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->db->select('products.product_id, products.consume_date, products.consume_reason,
			campuses.campus_name, rooms.room_name, subrooms.subroom_name');
		$this->db->from('products');
		$this->db->join('campuses', 'campuses.campus_id = products.campus_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = products.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = products.subroom_id', 'left');
		$this->db->where(array(
			'products.product_name_id' => $seed['product_name_id'],
			'products.campus_id' => $seed['campus_id'],
			'products.consume' => 1,
		));
		$this->db->order_by('products.consume_date', 'DESC');
		$this->db->limit(20);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	// ─── Product names ──────────────────────────────────────

	public function names()
	{
		// Full catalogue for tree UI (legacy add_product_name). Optional q filters by name.
		$q = trim((string)$this->input->get('q'));
		$this->db->select('product_names.*,
			(SELECT COUNT(*) FROM products p
			 WHERE p.product_name_id = product_names.product_name_id
			   AND p.consume = 0 AND p.sold = 0) AS stock_count', false);
		$this->db->from('product_names');
		if ($q !== '') $this->db->like('product_name', $q);
		$this->db->order_by('product_names.product_name', 'ASC');
		$this->db->limit(5000);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function save_name()
	{
		$body = $this->_body();
		$id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$name = isset($body['product_name']) ? trim($body['product_name']) : '';
		if ($name === '') $this->_json(array('success' => false, 'message' => 'Name required'), 422);

		$sub_of = null;
		if (isset($body['sub_of']) && $body['sub_of'] !== '' && $body['sub_of'] !== null) {
			$sub_of = (int)$body['sub_of'];
			if ($sub_of <= 0) $sub_of = null;
		}
		$type = isset($body['type']) ? (int)$body['type'] : null; // 0 Inventory, 1 Asset

		$data = array('product_name' => $name);
		if ($sub_of !== null) $data['sub_of'] = $sub_of;
		elseif (array_key_exists('sub_of', $body) && ($body['sub_of'] === null || $body['sub_of'] === '')) {
			$data['sub_of'] = null;
		}
		if ($type !== null && ($type === 0 || $type === 1)) {
			$data['type'] = $type;
		}

		if ($id > 0) {
			// Don't re-parent under itself
			if ($sub_of !== null && $sub_of === $id) {
				$this->_json(array('success' => false, 'message' => 'Cannot set product as its own parent'), 422);
			}
			$this->db->where('product_name_id', $id)->update('product_names', $data);
		} else {
			if (!isset($data['has_sub']) && $this->db->field_exists('has_sub', 'product_names')) {
				$data['has_sub'] = 0;
			}
			if (!isset($data['type'])) $data['type'] = 0;
			$this->db->insert('product_names', $data);
			$id = (int)$this->db->insert_id();
		}

		// Legacy: parent gets has_sub=1 when a child is attached
		if ($sub_of && $this->db->field_exists('has_sub', 'product_names')) {
			$this->db->where('product_name_id', $sub_of)->update('product_names', array('has_sub' => 1));
		}

		$this->_json(array('success' => true, 'product_name_id' => $id));
	}

	public function delete_name($id = 0)
	{
		$id = (int)$id;
		if (!$id) $this->_json(array('success' => false, 'message' => 'Invalid id'), 422);
		$used = $this->db->where('product_name_id', $id)->count_all_results('products');
		if ($used > 0) $this->_json(array('success' => false, 'message' => 'Name is used by stock units'), 409);
		$kids = $this->db->where('sub_of', $id)->count_all_results('product_names');
		if ($kids > 0) {
			$this->_json(array('success' => false, 'message' => 'Delete sub-products first'), 409);
		}
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
			$ids = array();
			if (!empty($row['product_name_ids'])) {
				foreach (explode(',', $row['product_name_ids']) as $pid) {
					$pid = (int)trim($pid);
					if ($pid > 0) $ids[] = $pid;
				}
			}
			$row['product_name_id_list'] = $ids;
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function save_vendor()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : 0);
		$name = isset($body['vendor_name']) ? trim($body['vendor_name']) : (isset($body['name']) ? trim($body['name']) : '');
		$shop = isset($body['company_name']) ? trim((string)$body['company_name']) : (isset($body['shop_name']) ? trim((string)$body['shop_name']) : '');
		$phone = isset($body['phone']) ? trim((string)$body['phone']) : '';
		$address = isset($body['address']) ? trim((string)$body['address']) : '';
		$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
		$status = isset($body['status']) ? trim((string)$body['status']) : 'active';
		if ($status !== 'inactive') $status = 'active';

		$product_ids = array();
		if (isset($body['product_name_ids'])) {
			$raw = $body['product_name_ids'];
			if (is_array($raw)) {
				foreach ($raw as $pid) {
					$pid = (int)$pid;
					if ($pid > 0) $product_ids[] = $pid;
				}
			} else {
				foreach (explode(',', (string)$raw) as $pid) {
					$pid = (int)trim($pid);
					if ($pid > 0) $product_ids[] = $pid;
				}
			}
		}

		// Legacy required fields (inventory/add_vendor)
		if ($campus_id <= 0) $this->_json(array('success' => false, 'message' => 'Campus is required'), 422);
		if (!count($product_ids)) $this->_json(array('success' => false, 'message' => 'Select at least one vendor product'), 422);
		if ($name === '') $this->_json(array('success' => false, 'message' => 'Vendor name is required'), 422);
		if ($shop === '') $this->_json(array('success' => false, 'message' => 'Shop name is required'), 422);
		if ($phone === '') $this->_json(array('success' => false, 'message' => 'Phone is required'), 422);
		if ($address === '') $this->_json(array('success' => false, 'message' => 'Address is required'), 422);

		$this->_assert_campus_access($campus_id);

		$data = array(
			'name' => $name,
			'shop_name' => $shop,
			'phone' => $phone,
			'address' => $address,
			'campus_id' => $campus_id,
			'status' => $status,
			'product_name_ids' => implode(',', $product_ids),
		);
		if (isset($body['image'])) $data['image'] = trim((string)$body['image']);

		if ($id > 0) {
			$this->db->where('id', $id)->update('vendors', $data);
		} else {
			if (!isset($data['image'])) $data['image'] = '';
			$data['created_by'] = $this->current_user['user_id'];
			$data['created_at'] = date('Y-m-d H:i:s');
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
		$project_id = (int)$this->input->get('project_id');

		$this->_ensure_journey_audit_columns();
		$select = 'purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name';
		if ($this->db->table_exists('construction_projects') && $this->db->field_exists('project_id', 'purchase_requests')) {
			$select .= ', construction_projects.project_name';
		}
		$this->db->select($select, false);
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = purchase_requests.subroom_id', 'left');
		if ($this->db->table_exists('construction_projects') && $this->db->field_exists('project_id', 'purchase_requests')) {
			$this->db->join('construction_projects', 'construction_projects.id = purchase_requests.project_id', 'left');
		}
		if ($from) $this->db->where('purchase_requests.created_at >=', $from . ' 00:00:00');
		if ($to) $this->db->where('purchase_requests.created_at <=', $to . ' 23:59:59');
		if ($final === '0' || $final === '1') {
			$this->db->where('purchase_requests.final', (int)$final);
		}
		if ($project_id > 0 && $this->db->field_exists('project_id', 'purchase_requests')) {
			$this->db->where('purchase_requests.project_id', $project_id);
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

		$this->_ensure_journey_audit_columns();
		$purchase_no = $this->_purchase_no();
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		// Optional construction project (header or per-line)
		$header_project_id = isset($body['project_id']) ? (int)$body['project_id'] : 0;
		foreach ($lines as $line) {
			$campus_id = (int)$line['campus_id'];
			$this->_assert_campus_access($campus_id, true);
			$row = array(
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
			);
			if ($this->db->field_exists('gate_received_qty', 'purchase_requests')) {
				$row['gate_received_qty'] = 0;
			}
			$project_id = isset($line['project_id']) ? (int)$line['project_id'] : $header_project_id;
			if ($project_id > 0 && $this->db->field_exists('project_id', 'purchase_requests')) {
				$row['project_id'] = $project_id;
			}
			$this->db->insert('purchase_requests', $row);
		}
		$this->_json(array('success' => true, 'purchase_no' => $purchase_no));
	}

	public function update_purchase_request_status()
	{
		$body = $this->_body();
		$ids = isset($body['purchase_request_ids']) && is_array($body['purchase_request_ids']) ? $body['purchase_request_ids'] : array();
		$purchase_no = isset($body['purchase_no']) ? trim($body['purchase_no']) : '';
		// 0 = pending, 1 = approved, 2 = rejected (legacy Inventory)
		$status = isset($body['status']) ? (int)$body['status'] : 1;
		if (!in_array($status, array(0, 1, 2), true)) {
			$this->_json(array('success' => false, 'message' => 'Invalid status'), 422);
		}
		if (!count($ids) && $purchase_no === '') {
			$this->_json(array('success' => false, 'message' => 'ids or purchase_no required'), 422);
		}
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		if ($name === '') $name = 'POS';
		$now = date('Y-m-d H:i:s');
		$update = array('status' => $status);
		// Legacy columns: approve_by + approved_at (set on approve or reject)
		if ($status === 1 || $status === 2) {
			if ($this->db->field_exists('approve_by', 'purchase_requests')) {
				$update['approve_by'] = $name;
			}
			if ($this->db->field_exists('approved_at', 'purchase_requests')) {
				$update['approved_at'] = $now;
			}
		}
		if (count($ids)) {
			$this->db->where_in('purchase_request_id', array_map('intval', $ids));
		} else {
			$this->db->where('purchase_no', $purchase_no);
		}
		// Only pending / not-yet-final lines can be approved or rejected
		$this->db->where('final', 0);
		$this->db->update('purchase_requests', $update);
		$this->_json(array(
			'success' => true,
			'updated' => $this->db->affected_rows(),
			'status' => $status,
			'approve_by' => isset($update['approve_by']) ? $update['approve_by'] : null,
			'approved_at' => isset($update['approved_at']) ? $update['approved_at'] : null,
			'message' => $status === 2 ? 'Purchase request rejected' : ($status === 1 ? 'Purchase request approved' : 'Status updated'),
		));
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

		$this->_ensure_quote_meta_columns();
		$this->_ensure_journey_audit_columns();

		$this->db->select('purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name, vendors.name AS vendor_name, vendors.shop_name AS vendor_shop, vendors.phone AS vendor_phone, vendors.address AS vendor_address', false);
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
		$this->db->order_by('purchase_request_prices.vendor_id', 'ASC');
		$quotes = $this->db->get()->result_array();

		$payments = array();
		if ($this->db->table_exists('payment_aggrements')) {
			$payments = $this->db->get_where('payment_aggrements', array('purchase_no' => $purchase_no))->result_array();
		}

		$all_approved = true;
		$all_rejected = count($lines) > 0;
		$all_vendor = true;
		$all_final = true;
		$all_purchased = true;
		$all_gate = true;
		$all_grn = true;
		foreach ($lines as $line) {
			$st = (int)$line['status'];
			if ($st !== 1) $all_approved = false;
			if ($st !== 2) $all_rejected = false;
			if (empty($line['purchase_from'])) $all_vendor = false;
			if ((int)$line['final'] !== 1) $all_final = false;
			if ((int)$line['purchased'] !== 1) $all_purchased = false;
			if ((int)$line['gate_approval'] !== 1) $all_gate = false;
			if ((int)$line['approval'] !== 1) $all_grn = false;
		}
		$has_quotes = count($quotes) > 0;
		$agreement_done = $all_final && $all_purchased;

		$payments_total = count($payments);
		$payments_unpaid = 0;
		$payments_paid = 0;
		foreach ($payments as $p) {
			if (!empty($p['paid'])) $payments_paid++;
			else $payments_unpaid++;
		}
		// Settled only when agreement exists and every installment is paid/waived
		$all_payments_settled = $agreement_done && $payments_total > 0 && $payments_unpaid === 0;
		$payments_pending = $agreement_done && $payments_unpaid > 0;

		$payment_done_label = 'Finalise & Payment Agreement';
		$payment_hint = 'Finalise quotation then create payment installments';
		if ($all_payments_settled) {
			$payment_done_label = 'Payments Complete';
			$payment_hint = 'All installments paid or waived';
		} elseif ($payments_pending) {
			$payment_done_label = 'Payments Pending (' . $payments_unpaid . ')';
			$payment_hint = $payments_unpaid . ' installment(s) still unpaid — mark paid when cash goes out';
		} elseif ($agreement_done) {
			$payment_done_label = 'Payment Agreement Added';
			$payment_hint = 'Agreement saved — add/pay installments';
		}

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
				'done_label' => $all_rejected ? 'Purchase Request Rejected' : 'Purchase Request Approved',
				'hint' => $all_rejected
					? 'This purchase request was rejected'
					: 'Approve or reject this purchase request',
				'done' => $all_approved || $all_rejected,
			),
			array(
				'id' => 'quote_add',
				'pending_label' => 'Add Quotation',
				'done_label' => 'Quotations Added',
				'hint' => 'Add vendor prices for each item',
				'done' => $has_quotes,
			),
			array(
				'id' => 'quote_select',
				'pending_label' => 'Select Quotation',
				'done_label' => 'Quotation Selected',
				'hint' => 'Select one vendor quotation from the list',
				'done' => $all_vendor,
			),
			array(
				'id' => 'payment',
				'pending_label' => 'Finalise & Payment Agreement',
				'done_label' => $payment_done_label,
				'hint' => $payment_hint,
				// Agreement unlocks gate/GRN; unpaid installments keep payment step in "attention"
				'done' => $agreement_done,
				'attention' => $payments_pending ? true : false,
			),
			array(
				'id' => 'gate',
				'pending_label' => 'Gate Approval',
				'done_label' => 'Gate Approved',
				'hint' => 'Security selects which items arrived (partial qty OK over multiple days)',
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
			$attention = !empty($def['attention']);
			if ($all_rejected && $def['id'] !== 'created' && $def['id'] !== 'approve') {
				// Rejected PR stops the journey — later steps stay locked.
				$state = 'upcoming';
			} elseif ($def['done']) {
				// Agreement done but installments unpaid → attention (not green "all good")
				$state = $attention ? 'attention' : 'done';
			} elseif (!$found_current) {
				$state = 'current';
				$current = $def['id'];
				$found_current = true;
			}
			$steps[] = array(
				'id' => $def['id'],
				'pending_label' => $def['pending_label'],
				'done_label' => $def['done_label'],
				'label' => ($def['done'] || $attention) ? $def['done_label'] : $def['pending_label'],
				'state' => $state,
				'hint' => $def['hint'],
				'attention' => $attention,
			);
		}
		if ($all_rejected) {
			$current = 'approve';
			$found_current = true;
		} elseif ($payments_pending && $all_grn) {
			// Stock done but cash still owed — keep focus on payments
			$current = 'payment';
			$found_current = true;
		} elseif (!$found_current) {
			$current = $all_payments_settled ? 'complete' : 'payment';
		}

		$title = isset($lines[0]['title']) ? $lines[0]['title'] : $purchase_no;
		$L = $lines[0];
		$pay_vendor = null;
		foreach ($lines as $line) {
			if (!empty($line['purchase_from'])) {
				$pay_vendor = array(
					'id' => (int)$line['purchase_from'],
					'vendor_name' => isset($line['vendor_name']) ? $line['vendor_name'] : '',
					'shop_name' => isset($line['vendor_shop']) ? $line['vendor_shop'] : '',
					'phone' => isset($line['vendor_phone']) ? $line['vendor_phone'] : '',
					'address' => isset($line['vendor_address']) ? $line['vendor_address'] : '',
				);
				break;
			}
		}
		// Fallback: payment agreement vendor
		if (!$pay_vendor && count($payments)) {
			$p0 = $payments[0];
			$vid = isset($p0['vendor_id']) ? (int)$p0['vendor_id'] : 0;
			if ($vid) {
				$vrow = $this->db->get_where('vendors', array('id' => $vid))->row_array();
				if ($vrow) {
					$pay_vendor = array(
						'id' => $vid,
						'vendor_name' => isset($vrow['name']) ? $vrow['name'] : '',
						'shop_name' => isset($vrow['shop_name']) ? $vrow['shop_name'] : '',
						'phone' => isset($vrow['phone']) ? $vrow['phone'] : '',
						'address' => isset($vrow['address']) ? $vrow['address'] : '',
					);
				}
			}
		}
		$gate_receives = array();
		if ($this->db->table_exists('purchase_gate_receives')) {
			$this->db->select('purchase_gate_receives.*, product_names.product_name', false);
			$this->db->from('purchase_gate_receives');
			$this->db->join('purchase_requests', 'purchase_requests.purchase_request_id = purchase_gate_receives.purchase_request_id', 'left');
			$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
			$this->db->where('purchase_gate_receives.purchase_no', $purchase_no);
			$this->db->order_by('purchase_gate_receives.received_at', 'DESC');
			$this->db->order_by('purchase_gate_receives.id', 'DESC');
			$gate_receives = $this->db->get()->result_array();
		}

		// Enrich lines with remaining gate qty for UI
		foreach ($lines as &$line) {
			$ordered = (int)$line['product_quantity'];
			$got = isset($line['gate_received_qty']) ? (int)$line['gate_received_qty'] : 0;
			if ($got < 0) $got = 0;
			if ((int)$line['gate_approval'] === 1 && $got < $ordered) $got = $ordered;
			$line['gate_received_qty'] = $got;
			$line['gate_remaining_qty'] = max(0, $ordered - $got);
		}
		unset($line);

		$this->_json(array(
			'success' => true,
			'data' => array(
				'purchase_no' => $purchase_no,
				'title' => $title,
				'lines' => $lines,
				'quotes' => $quotes,
				'payments' => $payments,
				'gate_receives' => $gate_receives,
				'steps' => $steps,
				'current_step' => $current,
				// Fully complete only when stock entered AND all installments settled
				'complete' => ($all_grn && $all_payments_settled),
				'stock_complete' => $all_grn,
				'payments_pending' => $payments_unpaid,
				'payments_paid' => $payments_paid,
				'payments_total' => $payments_total,
				'all_payments_settled' => $all_payments_settled,
				'rejected' => $all_rejected,
				'vendor' => $pay_vendor,
				'add_by' => isset($L['add_by']) ? $L['add_by'] : '',
				'created_at' => isset($L['created_at']) ? $L['created_at'] : '',
				'approve_by' => isset($L['approve_by']) ? $L['approve_by'] : '',
				'approved_at' => isset($L['approved_at']) ? $L['approved_at'] : '',
				'quote_select_by' => isset($L['quote_select_by']) ? $L['quote_select_by'] : '',
				'quote_select_at' => isset($L['quote_select_at']) ? $L['quote_select_at'] : '',
				'qoutation_approve_by' => isset($L['qoutation_approve_by']) ? $L['qoutation_approve_by'] : '',
				'final_approve_at' => isset($L['final_approve_at']) ? $L['final_approve_at'] : '',
				'payment_agree_by' => isset($L['payment_agree_by']) ? $L['payment_agree_by'] : '',
				'payment_agree_at' => isset($L['payment_agree_at']) ? $L['payment_agree_at'] : '',
				'gate_approve_by' => isset($L['gate_approve_by']) ? $L['gate_approve_by'] : '',
				'gate_approve_at' => isset($L['gate_approve_at']) ? $L['gate_approve_at'] : '',
				'grn_by' => isset($L['grn_by']) ? $L['grn_by'] : '',
				'grn_at' => isset($L['grn_at']) ? $L['grn_at'] : '',
			),
		));
	}

	// ─── Quotes ─────────────────────────────────────────────

	private function _ensure_quote_meta_columns()
	{
		if (!$this->db->field_exists('created_by', 'purchase_request_prices')) {
			$this->db->query("ALTER TABLE `purchase_request_prices` ADD `created_by` VARCHAR(255) NULL DEFAULT NULL");
		}
		if (!$this->db->field_exists('created_at', 'purchase_request_prices')) {
			$this->db->query("ALTER TABLE `purchase_request_prices` ADD `created_at` DATETIME NULL DEFAULT NULL");
		}
	}

	public function quotations()
	{
		$this->_ensure_quote_meta_columns();
		$purchase_no = trim((string)$this->input->get('purchase_no'));
		$campus_id = (int)$this->input->get('campus_id');
		// selected: '' = all, '1' = approved/selected quotes, '0' = not selected
		$selected = $this->input->get('selected');
		$q = trim((string)$this->input->get('q'));

		$this->db->select('purchase_request_prices.*, purchase_request_prices.purchase_request_price_id AS id, purchase_request_prices.approve AS approved, vendors.name AS vendor_name, vendors.shop_name AS company_name, product_names.product_name, purchase_requests.product_quantity, purchase_requests.campus_id, purchase_requests.purchase_no, purchase_requests.title, purchase_requests.final, purchase_requests.status, purchase_requests.purchase_from, campuses.campus_name', false);
		$this->db->from('purchase_request_prices');
		$this->db->join('vendors', 'vendors.id = purchase_request_prices.vendor_id', 'left');
		$this->db->join('purchase_requests', 'purchase_requests.purchase_request_id = purchase_request_prices.purchase_request_id', 'left');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'left');
		if ($purchase_no !== '') {
			$this->db->where('purchase_requests.purchase_no', $purchase_no);
		} else {
			$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		}
		if ($selected === '1' || $selected === '0') {
			$this->db->where('purchase_request_prices.approve', (int)$selected);
		}
		if ($q !== '') {
			$this->db->group_start();
			$this->db->like('purchase_requests.purchase_no', $q);
			$this->db->or_like('purchase_requests.title', $q);
			$this->db->or_like('vendors.name', $q);
			$this->db->or_like('vendors.shop_name', $q);
			$this->db->or_like('product_names.product_name', $q);
			$this->db->group_end();
		}
		$this->db->order_by('purchase_requests.purchase_no', 'DESC');
		$this->db->order_by('purchase_request_prices.vendor_id', 'ASC');
		$this->db->order_by('purchase_request_prices.purchase_request_price_id', 'ASC');
		$this->db->limit(2000);
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function save_quote()
	{
		$this->_ensure_quote_meta_columns();
		$body = $this->_body();
		$purchase_request_id = (int)(isset($body['purchase_request_id']) ? $body['purchase_request_id'] : 0);
		$vendor_id = (int)(isset($body['vendor_id']) ? $body['vendor_id'] : 0);
		$price = isset($body['price']) ? (float)$body['price'] : 0;
		if (!$purchase_request_id || !$vendor_id) {
			$this->_json(array('success' => false, 'message' => 'purchase_request_id and vendor_id required'), 422);
		}
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		if ($name === '') $name = 'POS';
		$now = date('Y-m-d H:i:s');

		$exists = $this->db->get_where('purchase_request_prices', array(
			'purchase_request_id' => $purchase_request_id,
			'vendor_id' => $vendor_id,
		))->row_array();
		if ($exists) {
			$pk = (int)$exists['purchase_request_price_id'];
			$update = array('price' => $price, 'created_by' => $name, 'created_at' => $now);
			$this->db->where('purchase_request_price_id', $pk)->update('purchase_request_prices', $update);
			$id = $pk;
		} else {
			$this->db->insert('purchase_request_prices', array(
				'purchase_request_id' => $purchase_request_id,
				'vendor_id' => $vendor_id,
				'price' => $price,
				'approve' => 0,
				'created_by' => $name,
				'created_at' => $now,
			));
			$id = $this->db->insert_id();
		}
		$this->_json(array('success' => true, 'id' => $id, 'created_by' => $name, 'created_at' => $now));
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

		$this->_ensure_journey_audit_columns();
		$name = $this->_actor_name();
		$now = date('Y-m-d H:i:s');
		$this->db->where('purchase_request_id', $purchase_request_id)->update('purchase_request_prices', array('approve' => 0));
		$this->db->where('purchase_request_price_id', $id)->update('purchase_request_prices', array('approve' => 1));
		$pr_upd = array(
			'purchase_from' => $quote['vendor_id'],
			'purchase_price' => $quote['price'],
			'purchased' => 0,
			'quote_select_by' => $name,
			'quote_select_at' => $now,
		);
		$this->db->where('purchase_request_id', $purchase_request_id)->update('purchase_requests', $pr_upd);
		$this->_json(array('success' => true, 'quote_select_by' => $name, 'quote_select_at' => $now));
	}

	/**
	 * Select one vendor's full quotation for a PR (all items).
	 * POST { purchase_no, vendor_id }
	 */
	public function approve_vendor_quotation()
	{
		$body = $this->_body();
		$purchase_no = isset($body['purchase_no']) ? trim($body['purchase_no']) : '';
		$vendor_id = (int)(isset($body['vendor_id']) ? $body['vendor_id'] : 0);
		if ($purchase_no === '' || !$vendor_id) {
			$this->_json(array('success' => false, 'message' => 'purchase_no and vendor_id required'), 422);
		}

		$lines = $this->db->get_where('purchase_requests', array('purchase_no' => $purchase_no))->result_array();
		if (!count($lines)) {
			$this->_json(array('success' => false, 'message' => 'Purchase request not found'), 404);
		}

		$this->_ensure_journey_audit_columns();
		$name = $this->_actor_name();
		$now = date('Y-m-d H:i:s');
		$selected = 0;
		foreach ($lines as $line) {
			$pr_id = (int)$line['purchase_request_id'];
			$quote = $this->db->get_where('purchase_request_prices', array(
				'purchase_request_id' => $pr_id,
				'vendor_id' => $vendor_id,
			))->row_array();
			if (!$quote) {
				$this->_json(array(
					'success' => false,
					'message' => 'This vendor has no quotation for every item. Add prices for all items first.',
				), 422);
			}
			$this->db->where('purchase_request_id', $pr_id)->update('purchase_request_prices', array('approve' => 0));
			$this->db->where('purchase_request_price_id', $quote['purchase_request_price_id'])
				->update('purchase_request_prices', array('approve' => 1));
			$this->db->where('purchase_request_id', $pr_id)->update('purchase_requests', array(
				'purchase_from' => $vendor_id,
				'purchase_price' => $quote['price'],
				'purchased' => 0,
				'quote_select_by' => $name,
				'quote_select_at' => $now,
			));
			$selected++;
		}

		$this->_json(array(
			'success' => true,
			'message' => 'Quotation selected',
			'selected_lines' => $selected,
			'vendor_id' => $vendor_id,
			'quote_select_by' => $name,
			'quote_select_at' => $now,
		));
	}

	public function finalise_quotation()
	{
		$body = $this->_body();
		$purchase_no = isset($body['purchase_no']) ? trim($body['purchase_no']) : '';
		if ($purchase_no === '') $this->_json(array('success' => false, 'message' => 'purchase_no required'), 422);
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		if ($name === '') $name = 'POS';
		$now = date('Y-m-d H:i:s');
		$update = array('final' => 1);
		if ($this->db->field_exists('qoutation_approve_by', 'purchase_requests')) {
			$update['qoutation_approve_by'] = $name;
		}
		if ($this->db->field_exists('final_approve_at', 'purchase_requests')) {
			$update['final_approve_at'] = $now;
		}
		$this->db->where('purchase_no', $purchase_no)->update('purchase_requests', $update);
		$this->_json(array(
			'success' => true,
			'updated' => $this->db->affected_rows(),
			'qoutation_approve_by' => $name,
			'final_approve_at' => $now,
		));
	}

	// ─── Purchase orders ────────────────────────────────────

	public function purchase_orders()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('purchase_requests.purchase_no, purchase_requests.title, purchase_requests.campus_id, campuses.campus_name,
			MAX(purchase_requests.purchased) as purchased, COUNT(*) as line_count,
			SUM(purchase_requests.product_quantity * IFNULL(purchase_requests.purchase_price,0)) as total_amount,
			MAX(purchase_requests.created_at) as created_at,
			MAX(purchase_requests.approve_by) as approve_by,
			MAX(purchase_requests.approved_at) as approved_at,
			MAX(purchase_requests.qoutation_approve_by) as qoutation_approve_by,
			MAX(purchase_requests.final_approve_at) as final_approve_at', false);
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
		// append=true → add more installments on existing agreement (re-agreement / extra amounts)
		$append = !empty($body['append']);
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

		$this->_ensure_journey_audit_columns();
		$name = $this->_actor_name();
		$now = date('Y-m-d H:i:s');
		$added = 0;

		foreach ($installments as $inst) {
			$amount = isset($inst['amount']) ? (float)$inst['amount'] : 0;
			$due = isset($inst['due_date']) ? trim((string)$inst['due_date']) : (isset($inst['date']) ? trim((string)$inst['date']) : '');
			if ($amount <= 0 || $due === '') continue;
			$row = array(
				'amount' => $amount,
				'date' => $due,
				'comment' => isset($inst['comment']) ? $inst['comment'] : '',
				'vendor_id' => $vendor_id,
				'purchase_no' => $purchase_no,
				'image' => '',
				'paid' => 0,
				'created_by' => $name,
				'created_at' => $now,
			);
			$this->db->insert('payment_aggrements', $row);
			$added++;
		}
		if (!$added) {
			$this->_json(array('success' => false, 'message' => 'Each installment needs amount and due date'), 422);
		}

		$pr_upd = array(
			'purchased' => 1,
			'payment_agree_by' => $name,
			'payment_agree_at' => $now,
		);
		$this->db->where(array('purchase_no' => $purchase_no, 'purchase_from' => $vendor_id))
			->update('purchase_requests', $pr_upd);
		$this->_json(array(
			'success' => true,
			'added' => $added,
			'append' => $append ? 1 : 0,
			'payment_agree_by' => $name,
			'payment_agree_at' => $now,
			'message' => $append ? 'Extra installments added' : 'Payment agreement saved',
		));
	}

	// ─── Payments ───────────────────────────────────────────

	public function payments()
	{
		$paid = $this->input->get('paid');
		// Real columns: payment_aggrement_id, amount, date, vendor_id, purchase_no, paid, …
		$this->db->select('payment_aggrements.*, payment_aggrements.payment_aggrement_id AS id, payment_aggrements.date AS due_date, vendors.name AS vendor_name, vendors.shop_name AS shop_name, vendors.phone AS vendor_phone,
			(SELECT pr.title FROM purchase_requests pr WHERE pr.purchase_no = payment_aggrements.purchase_no LIMIT 1) AS title', false);
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
		// paid_type: cash (default) | bank — same as legacy Inventory::payment_paid
		$paid_type = isset($body['paid_type']) ? trim((string)$body['paid_type']) : 'cash';
		if ($paid_type !== 'bank') $paid_type = 'cash';
		$transaction_id = isset($body['transaction_id']) ? (int)$body['transaction_id'] : 0;
		$expense_date = isset($body['date']) ? trim((string)$body['date']) : date('Y-m-d');
		if ($expense_date === '') $expense_date = date('Y-m-d');

		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		$row = $this->db->get_where('payment_aggrements', array('payment_aggrement_id' => $id))->row_array();
		if (!$row) {
			$row = $this->db->get_where('payment_aggrements', array('id' => $id))->row_array();
		}
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (!empty($row['paid'])) $this->_json(array('success' => false, 'message' => 'Already settled'), 409);

		$pk_col = isset($row['payment_aggrement_id']) ? 'payment_aggrement_id' : 'id';
		$pk = isset($row['payment_aggrement_id']) ? (int)$row['payment_aggrement_id'] : (int)$row['id'];
		$amount = (float)$row['amount'];
		$purchase_no = isset($row['purchase_no']) ? $row['purchase_no'] : '';

		$this->_ensure_journey_audit_columns();
		$name = $this->_actor_name();
		$user_id = (int)$this->current_user['user_id'];
		$now = date('Y-m-d H:i:s');
		$upd = array(
			'paid' => 1,
			'paid_by' => $name,
			'paid_at' => $now,
		);

		if ($mode === 'waive') {
			$comment = isset($row['comment']) ? trim((string)$row['comment']) : '';
			$waive_note = $note !== '' ? $note : 'Vendor discount / waived — no cash paid';
			$upd['comment'] = $comment !== '' ? ($comment . ' | ' . $waive_note) : $waive_note;
			$upd['paid_type'] = 'waive';
			$this->db->where($pk_col, $pk)->update('payment_aggrements', $upd);
			$this->_json(array(
				'success' => true,
				'message' => 'Installment waived (no petty cash / bank cut)',
				'mode' => 'waive',
				'paid_by' => $name,
				'paid_at' => $now,
			));
		}

		// ── mode=pay: expense + petty cash / bank (legacy Inventory::payment_paid) ──
		$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
		if (!$campus_id && $purchase_no !== '') {
			$pr = $this->db->get_where('purchase_requests', array('purchase_no' => $purchase_no))->row_array();
			if ($pr) $campus_id = (int)$pr['campus_id'];
		}
		if (!$campus_id) {
			$this->_json(array('success' => false, 'message' => 'campus_id required for expense'), 422);
		}

		if (!$this->db->table_exists('default_expense_category_inventory')) {
			$this->_json(array('success' => false, 'message' => 'Default expense category table missing'), 500);
		}
		$default_expense_category = $this->db->get('default_expense_category_inventory')->result_array();
		if (!count($default_expense_category)) {
			$this->_json(array(
				'success' => false,
				'message' => 'Kindly choose Default Expense Category in Inventory Rules.',
			), 422);
		}
		$expense_category_id = (int)$default_expense_category[0]['expense_category_id'];

		if ($paid_type === 'bank') {
			if (!$transaction_id) {
				$this->_json(array(
					'success' => false,
					'message' => 'Select a bank transaction for this payment.',
				), 422);
			}
		} else {
			$petty = $this->db->get_where('petty_cash_college_wise', array(
				'assign_to' => $user_id,
				'petty_status' => 1,
			))->row_array();
			if (!$petty) {
				$petty = $this->db->get_where('petty_cash_college_wise', array(
					'assign_to' => $user_id,
				))->row_array();
			}
			if (!$petty) {
				$this->_json(array(
					'success' => false,
					'message' => 'No petty cash account assigned to you. Cannot pay from cash.',
				), 422);
			}
			$user_petty_cash = function_exists('pettycash_statement')
				? (float)pettycash_statement($petty['id'])
				: (float)$petty['remaining_amount'];
			if ($user_petty_cash < $amount) {
				$this->_json(array(
					'success' => false,
					'message' => 'Insufficient petty cash. Balance: ' . number_format($user_petty_cash, 0) . ', needed: ' . number_format($amount, 0) . '. Kindly add cash first.',
				), 422);
			}
		}

		$purpose = 'Payment Against Purchase Request No.' . $purchase_no;
		$expense = array(
			'date' => $expense_date,
			'actual_date' => $now,
			'title' => 'Purchase Order',
			'image' => '',
			'amount' => $amount,
			'add_by' => $name,
			'add_by_id' => $user_id,
			'last_edit' => $name,
			'expense_category_id' => $expense_category_id,
			'campus_id' => $campus_id,
			'purpose' => $purpose,
			'approved_status' => 1,
			'payment_type' => $paid_type,
			'paid_type' => $paid_type,
			'purchase_no' => $purchase_no,
			'month_year' => '',
			'class' => 0,
			'council_exam_no' => '',
			'clear_by' => '',
			'upload_image' => 0,
		);
		$this->db->insert('expenses', $expense);
		$expense_id = (int)$this->db->insert_id();
		if (!$expense_id) {
			$this->_json(array('success' => false, 'message' => 'Failed to create expense'), 500);
		}

		if ($paid_type === 'cash') {
			$this->db->set('remaining_amount', 'remaining_amount - ' . $amount, false);
			$this->db->where('assign_to', $user_id);
			$this->db->update('petty_cash_college_wise');
		} else {
			$this->db->where('id', $transaction_id)
				->update('bank_reconciliation_statement', array('expense_id' => $expense_id));
		}

		$upd['paid_type'] = $paid_type;
		$this->db->where($pk_col, $pk)->update('payment_aggrements', $upd);
		$this->_json(array(
			'success' => true,
			'message' => $paid_type === 'cash'
				? 'Paid — expense created and petty cash reduced'
				: 'Paid — expense created and linked to bank transaction',
			'mode' => 'pay',
			'paid_type' => $paid_type,
			'expense_id' => $expense_id,
			'paid_by' => $name,
			'paid_at' => $now,
		));
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

	/**
	 * Undo a mistaken waive — reopens installment as unpaid (no expense/petty-cash involved).
	 * Cash/bank paid installments cannot be undone here.
	 */
	public function unwaive_installment()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : (isset($body['payment_aggrement_id']) ? $body['payment_aggrement_id'] : 0));
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);

		$this->_ensure_journey_audit_columns();
		$row = $this->db->get_where('payment_aggrements', array('payment_aggrement_id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (empty($row['paid'])) {
			$this->_json(array('success' => false, 'message' => 'Installment is already unpaid'), 409);
		}

		$paid_type = isset($row['paid_type']) ? strtolower(trim((string)$row['paid_type'])) : '';
		$comment = isset($row['comment']) ? (string)$row['comment'] : '';
		$is_waive = ($paid_type === 'waive') || (bool)preg_match('/waiv|discount/i', $comment);
		if (!$is_waive) {
			$this->_json(array(
				'success' => false,
				'message' => 'Only waived installments can be reopened. Cash/bank paid entries need expense reversal.',
			), 422);
		}

		// Strip common waive suffixes from comment
		$clean = preg_replace('/\s*\|\s*(Vendor discount\s*\/\s*)?waived[^\|]*$/i', '', $comment);
		$clean = preg_replace('/^\s*(Vendor discount\s*\/\s*)?waived[^\|]*$/i', '', trim((string)$clean));
		$clean = trim((string)$clean);

		$name = $this->_actor_name();
		$now = date('Y-m-d H:i:s');
		$note = 'Waive undone by ' . $name . ' on ' . $now;
		$upd = array(
			'paid' => 0,
			'paid_type' => null,
			'paid_by' => null,
			'paid_at' => null,
			'comment' => $clean !== '' ? ($clean . ' | ' . $note) : $note,
		);
		$this->db->where('payment_aggrement_id', $id)->update('payment_aggrements', $upd);
		$this->_json(array(
			'success' => true,
			'message' => 'Waive undone — installment is unpaid again',
			'id' => $id,
		));
	}

	// ─── Gate / GRN ─────────────────────────────────────────

	public function gate_queue()
	{
		$this->_ensure_journey_audit_columns();
		$campus_id = (int)$this->input->get('campus_id');
		$this->db->select('purchase_requests.*, campuses.campus_name, product_names.product_name, rooms.room_name, subrooms.subroom_name, CONCAT(users.first_name," ",users.last_name) as purchased_by_name, vendors.name AS vendor_name', false);
		$this->db->from('purchase_requests');
		$this->db->join('campuses', 'campuses.campus_id = purchase_requests.campus_id', 'inner');
		$this->db->join('product_names', 'product_names.product_name_id = purchase_requests.product_name_id', 'inner');
		$this->db->join('rooms', 'rooms.room_id = purchase_requests.room_id', 'left');
		$this->db->join('subrooms', 'subrooms.subroom_id = purchase_requests.subroom_id', 'left');
		$this->db->join('users', 'users.user_id = purchase_requests.purchased_by', 'left');
		$this->db->join('vendors', 'vendors.id = purchase_requests.purchase_from', 'left');
		$this->db->where(array('purchase_requests.purchased' => 1, 'gate_approval' => 0));
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$this->db->order_by('purchase_requests.purchase_request_id', 'DESC');
		$rows = $this->db->get()->result_array();
		foreach ($rows as &$r) {
			$ordered = (int)$r['product_quantity'];
			$got = isset($r['gate_received_qty']) ? (int)$r['gate_received_qty'] : 0;
			if ($got < 0) $got = 0;
			$r['gate_received_qty'] = $got;
			$r['gate_remaining_qty'] = max(0, $ordered - $got);
		}
		unset($r);
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/**
	 * Partial / multi-day gate receive.
	 * Body:
	 *   items: [{ purchase_request_id, quantity }]  — preferred
	 *   OR purchase_request_ids: [id…] — receive remaining qty for each (legacy)
	 *   comment?: string
	 */
	public function gate_approve()
	{
		$body = $this->_body();
		$this->_ensure_journey_audit_columns();
		$name = $this->_actor_name();
		$now = date('Y-m-d H:i:s');
		$comment = isset($body['comment']) ? trim((string)$body['comment']) : '';

		$items = array();
		if (isset($body['items']) && is_array($body['items']) && count($body['items'])) {
			foreach ($body['items'] as $it) {
				$pr_id = (int)(isset($it['purchase_request_id']) ? $it['purchase_request_id'] : 0);
				$qty = (int)(isset($it['quantity']) ? $it['quantity'] : 0);
				if ($pr_id > 0 && $qty > 0) {
					$items[] = array('purchase_request_id' => $pr_id, 'quantity' => $qty);
				}
			}
		} else {
			// Legacy: full remaining for selected ids
			$ids = isset($body['purchase_request_ids']) && is_array($body['purchase_request_ids'])
				? $body['purchase_request_ids'] : array();
			if (!count($ids) && !empty($body['purchase_request_id'])) {
				$ids = array((int)$body['purchase_request_id']);
			}
			foreach ($ids as $raw_id) {
				$pr_id = (int)$raw_id;
				if (!$pr_id) continue;
				$pr = $this->db->get_where('purchase_requests', array('purchase_request_id' => $pr_id))->row_array();
				if (!$pr || empty($pr['purchased']) || !empty($pr['gate_approval'])) continue;
				$ordered = (int)$pr['product_quantity'];
				$got = isset($pr['gate_received_qty']) ? (int)$pr['gate_received_qty'] : 0;
				$remain = max(0, $ordered - $got);
				if ($remain > 0) {
					$items[] = array('purchase_request_id' => $pr_id, 'quantity' => $remain);
				}
			}
		}

		if (!count($items)) {
			$this->_json(array(
				'success' => false,
				'message' => 'Select items and enter quantity that arrived today',
			), 422);
		}

		$updated = 0;
		$completed = 0;
		$entries = array();
		foreach ($items as $it) {
			$pr_id = (int)$it['purchase_request_id'];
			$qty = (int)$it['quantity'];
			$pr = $this->db->get_where('purchase_requests', array('purchase_request_id' => $pr_id))->row_array();
			if (!$pr) {
				$this->_json(array('success' => false, 'message' => 'Line not found: ' . $pr_id), 404);
			}
			if (empty($pr['purchased'])) {
				$this->_json(array('success' => false, 'message' => 'Payment agreement not done yet'), 422);
			}
			if (!empty($pr['gate_approval'])) {
				$this->_json(array(
					'success' => false,
					'message' => 'Already fully received at gate (line ' . $pr_id . ')',
				), 409);
			}
			$ordered = (int)$pr['product_quantity'];
			$got = isset($pr['gate_received_qty']) ? (int)$pr['gate_received_qty'] : 0;
			if ($got < 0) $got = 0;
			$remain = max(0, $ordered - $got);
			if ($qty > $remain) {
				$this->_json(array(
					'success' => false,
					'message' => 'Qty too high for ' . (isset($pr['purchase_no']) ? $pr['purchase_no'] : 'item')
						. ' — remaining ' . $remain,
				), 422);
			}
			$new_got = $got + $qty;
			$fully = $new_got >= $ordered ? 1 : 0;
			$this->db->where('purchase_request_id', $pr_id)->update('purchase_requests', array(
				'gate_received_qty' => $new_got,
				'gate_approval' => $fully,
				'gate_approve_by' => $name,
				'gate_approve_at' => $now,
			));
			$this->db->insert('purchase_gate_receives', array(
				'purchase_request_id' => $pr_id,
				'purchase_no' => isset($pr['purchase_no']) ? $pr['purchase_no'] : '',
				'quantity' => $qty,
				'received_by' => $name,
				'received_at' => $now,
				'comment' => $comment,
			));
			$updated++;
			if ($fully) $completed++;
			$entries[] = array(
				'purchase_request_id' => $pr_id,
				'quantity' => $qty,
				'gate_received_qty' => $new_got,
				'gate_approval' => $fully,
			);
		}

		$this->_json(array(
			'success' => true,
			'updated' => $updated,
			'completed_lines' => $completed,
			'received_by' => $name,
			'received_at' => $now,
			'entries' => $entries,
			'message' => $completed === $updated && $updated > 0
				? 'Gate entry saved — selected lines fully received'
				: 'Gate entry saved — more can arrive later',
		));
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

		$this->_ensure_journey_audit_columns();
		$name = $this->_actor_name();
		$now = date('Y-m-d H:i:s');
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
			$this->db->where('purchase_request_id', $pr_id)->update('purchase_requests', array(
				'approval' => 1,
				'grn_by' => $name,
				'grn_at' => $now,
			));
			$total_qty += $qty;
		}
		$this->_json(array(
			'success' => true,
			'quantity' => $total_qty,
			'grn_by' => $name,
			'grn_at' => $now,
		));
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

	/**
	 * Move stock units.
	 * Legacy modal: { product_id, campus_id, room_id, subroom_id?, quantity } — same campus only.
	 * Panel: { product_name_id, from_campus_id, to_campus_id, from_room_id, to_room_id, … }.
	 * History uses legacy product_history columns (from-location snapshot).
	 */
	public function move_stock()
	{
		$body = $this->_body();
		$qty = max(1, (int)(isset($body['quantity']) ? $body['quantity'] : 1));
		$seed_id = (int)(isset($body['product_id']) ? $body['product_id'] : 0);

		// Products modal: { product_id, campus_id, room_id, subroom_id?, quantity }
		if ($seed_id > 0) {
			$seed = $this->db->get_where('products', array('product_id' => $seed_id))->row_array();
			if (!$seed) $this->_json(array('success' => false, 'message' => 'Product not found'), 404);
			$to_campus = (int)(isset($body['campus_id']) ? $body['campus_id'] : $seed['campus_id']);
			$to_room = (int)(isset($body['room_id']) ? $body['room_id'] : 0);
			$to_sub = (int)(isset($body['subroom_id']) ? $body['subroom_id'] : 0);
			if (!$to_campus) $this->_json(array('success' => false, 'message' => 'Campus is required'), 422);
			if (!$to_room) $this->_json(array('success' => false, 'message' => 'Room is required'), 422);
			$this->_assert_campus_access((int)$seed['campus_id']);
			$this->_assert_campus_access($to_campus);

			$moved = $this->_move_units_from_location(
				(int)$seed['product_name_id'],
				(int)$seed['campus_id'],
				(int)$seed['room_id'],
				(int)$seed['subroom_id'],
				$to_campus,
				$to_room,
				$to_sub,
				$qty
			);
			if ($moved < 1) $this->_json(array('success' => false, 'message' => 'No stock to move'), 409);
			$this->_json(array('success' => true, 'moved' => $moved));
		}

		$product_name_id = (int)(isset($body['product_name_id']) ? $body['product_name_id'] : 0);
		$from_campus = (int)(isset($body['from_campus_id']) ? $body['from_campus_id'] : 0);
		$to_campus = (int)(isset($body['to_campus_id']) ? $body['to_campus_id'] : 0);
		$from_room = (int)(isset($body['from_room_id']) ? $body['from_room_id'] : 0);
		$to_room = (int)(isset($body['to_room_id']) ? $body['to_room_id'] : 0);
		$from_sub = (int)(isset($body['from_subroom_id']) ? $body['from_subroom_id'] : 0);
		$to_sub = (int)(isset($body['to_subroom_id']) ? $body['to_subroom_id'] : 0);

		if (!$product_name_id || !$from_campus || !$to_campus) {
			$this->_json(array('success' => false, 'message' => 'product and campuses required'), 422);
		}
		if (!$to_room) $this->_json(array('success' => false, 'message' => 'Destination room required'), 422);
		$this->_assert_campus_access($from_campus);
		$this->_assert_campus_access($to_campus);

		$moved = $this->_move_units_from_location(
			$product_name_id,
			$from_campus,
			$from_room,
			$from_sub,
			$to_campus,
			$to_room,
			$to_sub,
			$qty
		);
		if ($moved < 1) $this->_json(array('success' => false, 'message' => 'No stock to move'), 409);
		$this->_json(array('success' => true, 'moved' => $moved));
	}

	/**
	 * Move N available units; write legacy product_history (from-location) before each update.
	 * Pass exact from_room / from_sub from the seed row when moving a card/group.
	 */
	private function _move_units_from_location($product_name_id, $from_campus, $from_room, $from_sub, $to_campus, $to_room, $to_sub, $qty)
	{
		$moved = 0;
		for ($i = 0; $i < $qty; $i++) {
			$this->db->from('products');
			$this->db->where(array(
				'product_name_id' => (int)$product_name_id,
				'campus_id' => (int)$from_campus,
				'sold' => 0,
				'consume' => 0,
				'status' => 1,
			));
			// Exact location match (0 / NULL treated the same, like legacy)
			if ($from_room > 0) {
				$this->db->where('room_id', (int)$from_room);
			} else {
				$this->db->group_start();
				$this->db->where('room_id', 0);
				$this->db->or_where('room_id IS NULL', null, false);
				$this->db->group_end();
			}
			if ($from_sub > 0) {
				$this->db->where('subroom_id', (int)$from_sub);
			} else {
				$this->db->group_start();
				$this->db->where('subroom_id', 0);
				$this->db->or_where('subroom_id IS NULL', null, false);
				$this->db->group_end();
			}
			$this->db->limit(1);
			$unit = $this->db->get()->row_array();
			if (!$unit) break;

			if ($this->db->table_exists('product_history')) {
				$this->db->insert('product_history', array(
					'product_id' => $unit['product_id'],
					'campus_id' => $unit['campus_id'],
					'room_id' => $unit['room_id'] ? $unit['room_id'] : 0,
					'subroom_id' => $unit['subroom_id'] ? $unit['subroom_id'] : 0,
					'product_name_id' => $unit['product_name_id'],
					'added_by' => $this->_actor_name(),
				));
			}
			$this->db->where('product_id', $unit['product_id'])->update('products', array(
				'campus_id' => (int)$to_campus,
				'room_id' => $to_room ? (int)$to_room : null,
				'subroom_id' => $to_sub ? (int)$to_sub : null,
			));
			$moved++;
		}
		return $moved;
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
		$this->db->select('payment_aggrements.*, payment_aggrements.payment_aggrement_id AS id, payment_aggrements.date AS due_date, vendors.name AS vendor_name,
			(SELECT pr.title FROM purchase_requests pr WHERE pr.purchase_no = payment_aggrements.purchase_no LIMIT 1) AS title', false);
		$this->db->from('payment_aggrements');
		$this->db->join('vendors', 'vendors.id = payment_aggrements.vendor_id', 'left');
		$this->db->where('payment_aggrements.paid', 0);
		$this->db->order_by('payment_aggrements.date', 'ASC');
		$this->db->limit($limit);
		$payments = $this->db->get()->result_array();
		$payment_count = $this->db->where('paid', 0)->count_all_results('payment_aggrements');

		// Gate
		$this->db->select('purchase_requests.purchase_request_id, purchase_requests.purchase_no, purchase_requests.title, purchase_requests.product_quantity, campuses.campus_name, product_names.product_name');
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
		$this->db->select('purchase_requests.purchase_request_id, purchase_requests.purchase_no, purchase_requests.title, purchase_requests.product_quantity, campuses.campus_name, product_names.product_name, rooms.room_name');
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

		$stats = $this->_inventory_stats($campus_id);

		$this->_json(array(
			'success' => true,
			'data' => array(
				'total_actions' => $total_actions,
				'stats' => $stats,
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

	/**
	 * Inventory KPIs for dashboard (campus-scoped when provided).
	 */
	private function _inventory_stats($campus_id = 0)
	{
		$campus_id = (int)$campus_id;
		$stats = array(
			'total_items' => 0,
			'saleable' => 0,
			'consumable' => 0,
			'returnable' => 0,
			'stock_value' => 0,
			'purchase_total' => 0,
			'sales_total' => 0,
			'sold_units' => 0,
			'consumed_units' => 0,
			'unpaid_amount' => 0,
			'paid_amount' => 0,
		);

		// In-stock units (available)
		$this->db->select("COUNT(*) AS total_items,
			SUM(CASE WHEN saleable = 1 THEN 1 ELSE 0 END) AS saleable,
			SUM(CASE WHEN consumeable = 1 THEN 1 ELSE 0 END) AS consumable,
			SUM(CASE WHEN returnable = 1 THEN 1 ELSE 0 END) AS returnable,
			SUM(IFNULL(estimated_price, 0)) AS stock_value", false);
		$this->db->from('products');
		$this->db->where(array('sold' => 0, 'consume' => 0));
		if ($this->db->field_exists('status', 'products')) {
			$this->db->where('status', 1);
		}
		$this->_apply_campus_filter('campus_id', $campus_id);
		$row = $this->db->get()->row_array();
		if ($row) {
			$stats['total_items'] = (int)$row['total_items'];
			$stats['saleable'] = (int)$row['saleable'];
			$stats['consumable'] = (int)$row['consumable'];
			$stats['returnable'] = (int)$row['returnable'];
			$stats['stock_value'] = (float)$row['stock_value'];
		}

		// Sold units + sales amount
		$this->db->select("COUNT(*) AS sold_units, SUM(IFNULL(CAST(sold_amount AS DECIMAL(14,2)), 0)) AS sales_total", false);
		$this->db->from('products');
		$this->db->where('sold', 1);
		$this->_apply_campus_filter('campus_id', $campus_id);
		$sold = $this->db->get()->row_array();
		if ($sold) {
			$stats['sold_units'] = (int)$sold['sold_units'];
			$stats['sales_total'] = (float)$sold['sales_total'];
		}

		// Consumed units
		$this->db->from('products');
		$this->db->where('consume', 1);
		$this->_apply_campus_filter('campus_id', $campus_id);
		$stats['consumed_units'] = (int)$this->db->count_all_results();

		// Purchase total from finalized PO lines (qty × purchase_price)
		$this->db->select('SUM(purchase_requests.product_quantity * IFNULL(purchase_requests.purchase_price, 0)) AS purchase_total', false);
		$this->db->from('purchase_requests');
		$this->db->where('purchase_requests.final', 1);
		$this->_apply_campus_filter('purchase_requests.campus_id', $campus_id, true);
		$pr = $this->db->get()->row_array();
		if ($pr) $stats['purchase_total'] = (float)$pr['purchase_total'];

		// Payment agreement paid / unpaid totals (scoped by PR campus)
		if ($this->db->table_exists('payment_aggrements')) {
			$this->db->select("SUM(CASE WHEN pa.paid = 1 THEN pa.amount ELSE 0 END) AS paid_amount,
				SUM(CASE WHEN pa.paid = 0 THEN pa.amount ELSE 0 END) AS unpaid_amount", false);
			$this->db->from('payment_aggrements pa');
			$campus_sql = $this->_campus_sql_in('pr.campus_id', $campus_id, true);
			if ($campus_sql !== '') {
				$this->db->where("pa.purchase_no IN (
					SELECT DISTINCT pr.purchase_no FROM purchase_requests pr WHERE 1=1
					" . $campus_sql . "
				)", null, false);
			}
			$pay = $this->db->get()->row_array();
			if ($pay) {
				$stats['paid_amount'] = (float)$pay['paid_amount'];
				$stats['unpaid_amount'] = (float)$pay['unpaid_amount'];
			}
		}

		return $stats;
	}

	/** Build SQL fragment: AND column IN (...) for campus access, or AND column = N */
	private function _campus_sql_in($column, $campus_id = 0, $use_pr = false)
	{
		$campus_id = (int)$campus_id;
		if ($campus_id > 0) {
			return ' AND ' . $column . ' = ' . $campus_id;
		}
		if ($this->_is_admin()) return '';
		$allowed = $use_pr ? $this->_pr_campus_ids() : $this->_inventory_campus_ids();
		if (!count($allowed)) return ' AND 1=0';
		$ids = array();
		foreach ($allowed as $id) $ids[] = (int)$id;
		return ' AND ' . $column . ' IN (' . implode(',', $ids) . ')';
	}
}
