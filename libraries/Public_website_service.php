<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public (unauthenticated) college website reads — resolve campus by domain,
 * then return banners / gallery / downloads / home content scoped to that campus.
 */
class Public_website_service {

	/** @var CI_Controller */
	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	public function normalize_domain($raw)
	{
		$raw = trim((string) $raw);
		if ($raw === '') {
			return '';
		}
		$raw = preg_replace('#^https?://#i', '', $raw);
		$raw = preg_replace('#^www\.#i', '', $raw);
		$raw = explode('/', $raw)[0];
		$raw = explode(':', $raw)[0];
		return strtolower(rtrim($raw, '/'));
	}

	public function resolve_campus($domain)
	{
		$domain = $this->normalize_domain($domain);
		if ($domain === '') {
			return null;
		}

		$rows = $this->ci->db->get_where('campuses', array('status' => 1))->result_array();
		foreach ($rows as $row) {
			$site = $this->normalize_domain(isset($row['website']) ? $row['website'] : '');
			if ($site !== '' && $site === $domain) {
				return $row;
			}
		}

		// Fallback: allow subdomain match (e.g. foo.example.edu.pk → example.edu.pk campus)
		foreach ($rows as $row) {
			$site = $this->normalize_domain(isset($row['website']) ? $row['website'] : '');
			if ($site !== '' && (substr($domain, -strlen($site) - 1) === '.' . $site || strpos($domain, $site) !== false)) {
				return $row;
			}
		}

		return null;
	}

	public function asset_base()
	{
		$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
		$scheme = $https ? 'https' : 'http';
		// Controllers live under /lahore-campus/ on production; FCPATH is that root.
		$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
		$basePath = rtrim(str_replace('\\', '/', dirname($script)), '/');
		if (substr($basePath, -10) === '/index.php') {
			$basePath = dirname($basePath);
		}
		if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
			$basePath = '';
		}
		return $scheme . '://' . $host . $basePath;
	}

	private function asset_url($folder, $file)
	{
		$file = ltrim((string) $file, '/');
		if ($file === '') {
			return '';
		}
		if (preg_match('#^https?://#i', $file)) {
			return $file;
		}
		return rtrim($this->asset_base(), '/') . '/' . trim($folder, '/') . '/' . $file;
	}

	private function campus_in_csv($campus_id, $csv)
	{
		$ids = array_filter(array_map('intval', explode(',', (string) $csv)));
		return in_array((int) $campus_id, $ids, true);
	}

	public function bootstrap($domain)
	{
		$campus = $this->resolve_campus($domain);
		if (!$campus) {
			return array('success' => false, 'message' => 'Campus not found for domain', 'domain' => $this->normalize_domain($domain));
		}
		$campus_id = (int) $campus['campus_id'];

		return array(
			'success' => true,
			'data' => array(
				'campus' => $this->public_campus($campus),
				'asset_base' => $this->asset_base(),
				'slider' => $this->slider_for_campus($campus_id),
				'news' => $this->news_for_campus($campus_id),
				'home' => $this->home_for_campus($campus_id),
				'events' => $this->events_for_campus($campus_id),
				'event_images' => $this->event_images_for_campus($campus_id),
				'videos' => $this->videos_for_campus($campus_id, false),
				'apply_videos' => $this->videos_for_campus($campus_id, true),
				'downloads' => $this->downloads_for_campus($campus_id),
				'courses' => $this->courses_for_campus($campus_id),
				'faqs' => $this->faqs(),
				'zoom' => $this->zoom(),
			),
		);
	}

	public function public_campus($campus)
	{
		return array(
			'campus_id' => (int) $campus['campus_id'],
			'campus_name' => isset($campus['campus_name']) ? $campus['campus_name'] : '',
			'campus_code' => isset($campus['campus_code']) ? $campus['campus_code'] : '',
			'website' => isset($campus['website']) ? $campus['website'] : '',
			'address' => isset($campus['address']) ? $campus['address'] : '',
			'phone' => isset($campus['phone']) ? $campus['phone'] : '',
			'phone1' => isset($campus['phone1']) ? $campus['phone1'] : '',
			'phone2' => isset($campus['phone2']) ? $campus['phone2'] : '',
			'email' => isset($campus['email']) ? $campus['email'] : '',
			'logo' => !empty($campus['logo']) ? $this->asset_url('uploads', $campus['logo']) : '',
		);
	}

	public function slider_for_campus($campus_id)
	{
		$out = array();
		foreach ($this->ci->db->order_by('id', 'DESC')->get('slider_images')->result_array() as $row) {
			if (!$this->campus_in_csv($campus_id, isset($row['campus_ids']) ? $row['campus_ids'] : '')) {
				continue;
			}
			$out[] = array(
				'id' => (int) $row['id'],
				'image' => $this->asset_url('slider_images', $row['image']),
			);
		}
		return $out;
	}

	public function news_for_campus($campus_id)
	{
		$out = array();
		$this->ci->db->where('campus_ids IS NOT NULL', null, false);
		$this->ci->db->where('campus_ids !=', '');
		foreach ($this->ci->db->order_by('news_id', 'DESC')->get('news_updates')->result_array() as $row) {
			if (!$this->campus_in_csv($campus_id, $row['campus_ids'])) {
				continue;
			}
			$out[] = array(
				'news_id' => (int) $row['news_id'],
				'news' => $row['news'],
			);
		}
		return $out;
	}

	public function home_for_campus($campus_id)
	{
		$row = $this->ci->db->get_where('website_content', array('campus_id' => (int) $campus_id))->row_array();
		if (!$row) {
			return null;
		}
		return array(
			'point_1' => $row['point_1'],
			'point_1_explanation' => $row['point_1_explanation'],
			'point_2' => $row['point_2'],
			'point_2_explanation' => $row['point_2_explanation'],
			'point_3' => $row['point_3'],
			'point_3_explanation' => $row['point_3_explanation'],
			'point_4' => $row['point_4'],
			'point_4_explanation' => $row['point_4_explanation'],
			'point_center_image' => $this->asset_url('uploads', $row['point_center_image']),
			'home_left_image' => $this->asset_url('uploads', $row['home_left_image']),
			'home_right_image' => $this->asset_url('uploads', $row['home_right_image']),
			'home_left_paragraph' => $row['home_left_paragraph'],
			'home_right_heading' => $row['home_right_heading'],
			'home_right_paragraph' => $row['home_right_paragraph'],
		);
	}

	public function events_for_campus($campus_id)
	{
		$out = array();
		foreach ($this->ci->db->order_by('event_id', 'DESC')->get('events')->result_array() as $row) {
			if (isset($row['show_on_website']) && (int) $row['show_on_website'] === 0) {
				continue;
			}
			if (!$this->campus_in_csv($campus_id, isset($row['campus_ids']) ? $row['campus_ids'] : '')) {
				continue;
			}
			$out[] = array(
				'event_id' => (int) $row['event_id'],
				'name' => $row['name'],
			);
		}
		return $out;
	}

	public function event_images_for_campus($campus_id)
	{
		$out = array();
		$this->ci->db->select('event_images.*, events.name as event_name');
		$this->ci->db->from('event_images');
		$this->ci->db->join('events', 'events.event_id = event_images.event_id', 'left');
		$this->ci->db->where('event_images.campus_id', (int) $campus_id);
		$this->ci->db->order_by('event_images.image_id', 'DESC');
		foreach ($this->ci->db->get()->result_array() as $row) {
			if (isset($row['show_on_website']) && (int) $row['show_on_website'] === 0) {
				continue;
			}
			$out[] = array(
				'image_id' => (int) $row['image_id'],
				'title' => $row['title'],
				'url' => $this->asset_url('event_images', $row['url']),
				'event_id' => (int) $row['event_id'],
				'event_name' => isset($row['event_name']) ? $row['event_name'] : '',
			);
		}
		return $out;
	}

	public function downloads_for_campus($campus_id)
	{
		$out = array();
		foreach ($this->ci->db->order_by('download_id', 'DESC')->get('downloads')->result_array() as $row) {
			if (!$this->campus_in_csv($campus_id, isset($row['campus_ids']) ? $row['campus_ids'] : '')) {
				continue;
			}
			$out[] = array(
				'download_id' => (int) $row['download_id'],
				'title' => $row['title'],
				'url' => $this->asset_url('downloads', $row['document']),
			);
		}
		return $out;
	}

	public function videos_for_campus($campus_id, $for_apply_now = false)
	{
		$out = array();
		foreach ($this->ci->db->order_by('video_id', 'DESC')->get_where('videos', array('campus_id' => (int) $campus_id))->result_array() as $row) {
			if ($for_apply_now) {
				if (empty($row['for_apply_now'])) {
					continue;
				}
			} elseif (isset($row['show_on_website']) && (int) $row['show_on_website'] === 0) {
				continue;
			}
			$out[] = array(
				'video_id' => (int) $row['video_id'],
				'title' => $row['title'],
				'url' => $row['url'],
			);
		}
		return $out;
	}

	public function courses_for_campus($campus_id)
	{
		$out = array();
		if (!$this->ci->db->table_exists('courses')) {
			return $out;
		}
		// Active courses whose campus_ids CSV includes this campus (same as slider/news).
		if ($this->ci->db->field_exists('status', 'courses')) {
			$this->ci->db->where('status', 1);
		}
		$rows = $this->ci->db->order_by('course_name', 'ASC')->get('courses')->result_array();
		foreach ($rows as $row) {
			if (!$this->campus_in_csv($campus_id, isset($row['campus_ids']) ? $row['campus_ids'] : '')) {
				continue;
			}
			$out[] = array(
				'course_id' => (int) $row['course_id'],
				'course_name' => $row['course_name'],
				'course_type' => isset($row['course_type']) ? $row['course_type'] : '',
				'course_duration_year' => isset($row['course_duration_year']) ? $row['course_duration_year'] : '',
			);
		}
		return $out;
	}

	public function faqs()
	{
		if (!$this->ci->db->table_exists('faqs')) {
			return array();
		}
		$out = array();
		foreach ($this->ci->db->order_by('faq_id', 'DESC')->get('faqs')->result_array() as $row) {
			$out[] = array(
				'faq_id' => (int) $row['faq_id'],
				'question' => $row['question'],
				'slug' => $row['slug'],
				'answer' => $row['answer'],
			);
		}
		return $out;
	}

	public function zoom()
	{
		if (!$this->ci->db->table_exists('zoom')) {
			return array();
		}
		$out = array();
		foreach ($this->ci->db->order_by('zoom_id', 'ASC')->get('zoom')->result_array() as $row) {
			$out[] = array(
				'zoom_id' => (int) $row['zoom_id'],
				'title' => isset($row['title']) ? $row['title'] : '',
				'personal_meeting_id' => isset($row['personal_meeting_id']) ? $row['personal_meeting_id'] : '',
				'admission_timing' => isset($row['admission_timing']) ? $row['admission_timing'] : '',
				'image' => !empty($row['image']) ? $this->asset_url('zoom_images', $row['image']) : '',
			);
		}
		return $out;
	}

	public function submit_apply($body, $domain)
	{
		$campus = $this->resolve_campus($domain);
		if (!$campus) {
			return array('success' => false, 'message' => 'Campus not found');
		}

		$name = trim(isset($body['name']) ? $body['name'] : '');
		$father_name = trim(isset($body['father_name']) ? $body['father_name'] : '');
		$cnic = trim(isset($body['cnic']) ? $body['cnic'] : '');
		$gender = trim(isset($body['gender']) ? $body['gender'] : '');
		$date_of_birth = trim(isset($body['date_of_birth']) ? $body['date_of_birth'] : '');
		$education = trim(isset($body['education']) ? $body['education'] : '');
		$address = trim(isset($body['address']) ? $body['address'] : '');
		$city = trim(isset($body['city']) ? $body['city'] : '');
		$mobile = preg_replace('/^\+92/', '0', trim(isset($body['mobile']) ? $body['mobile'] : ''));
		$emergency_no = preg_replace('/^\+92/', '0', trim(isset($body['emergency_no']) ? $body['emergency_no'] : $mobile));

		if ($name === '' || $mobile === '') {
			return array('success' => false, 'message' => 'Name and mobile are required');
		}

		$dup = $this->ci->db->get_where('apply_now', array('mobile' => $mobile))->row_array();
		if ($dup) {
			return array('success' => false, 'message' => 'You already applied with this mobile number');
		}

		$website = 'https://www.' . $this->normalize_domain(isset($campus['website']) ? $campus['website'] : $domain);
		$row = array(
			'name' => $name,
			'father_name' => $father_name,
			'cnic' => $cnic !== '' ? $cnic : 'N/A',
			'gender' => $gender,
			'date_of_birth' => $date_of_birth !== '' ? $date_of_birth : '0000-00-00',
			'education' => $education,
			'address' => $address,
			'city' => $city,
			'mobile' => $mobile,
			'emergency_no' => $emergency_no !== '' ? $emergency_no : $mobile,
			'website' => $website,
			'fb_ad_name' => '',
		);
		if ($this->ci->db->field_exists('date', 'apply_now')) {
			$row['date'] = date('Y-m-d H:i:s');
		}
		if ($this->ci->db->field_exists('status', 'apply_now')) {
			$row['status'] = 0;
		}
		if ($this->ci->db->field_exists('comment', 'apply_now')) {
			$row['comment'] = '';
		}
		if ($this->ci->db->field_exists('last_edit', 'apply_now')) {
			$row['last_edit'] = '';
		}
		$this->ci->db->insert('apply_now', $row);

		return array('success' => true, 'message' => 'Application submitted successfully', 'apply_now_id' => $this->ci->db->insert_id());
	}

	public function submit_career($body, $domain)
	{
		$campus = $this->resolve_campus($domain);
		if (!$campus) {
			return array('success' => false, 'message' => 'Campus not found');
		}

		$name = trim(isset($body['name']) ? $body['name'] : '');
		$cell_number = preg_replace('/^\+92/', '0', trim(isset($body['cell_number']) ? $body['cell_number'] : ''));
		if ($name === '' || $cell_number === '') {
			return array('success' => false, 'message' => 'Name and cell number are required');
		}

		$row = array(
			'campus_id' => (int) $campus['campus_id'],
			'name' => $name,
			'address' => trim(isset($body['address']) ? $body['address'] : ''),
			'qualification' => trim(isset($body['qualification']) ? $body['qualification'] : ''),
			'other_current_job' => trim(isset($body['other_current_job']) ? $body['other_current_job'] : ''),
			'previous_experience' => trim(isset($body['previous_experience']) ? $body['previous_experience'] : ''),
			'gender' => trim(isset($body['gender']) ? $body['gender'] : ''),
			'marital_status' => trim(isset($body['marital_status']) ? $body['marital_status'] : ''),
			'job_post_wanted' => trim(isset($body['job_post_wanted']) ? $body['job_post_wanted'] : ''),
			'cell_number' => $cell_number,
		);

		// Optional email / message columns if present
		if ($this->ci->db->field_exists('email', 'interview') && isset($body['email'])) {
			$row['email'] = trim($body['email']);
		}
		if ($this->ci->db->field_exists('message', 'interview') && isset($body['message'])) {
			$row['message'] = trim($body['message']);
		}

		$this->ci->db->insert('interview', $row);
		$interview_id = $this->ci->db->insert_id();

		if (!empty($_FILES['cv']['name']) && is_uploaded_file($_FILES['cv']['tmp_name'])) {
			$this->ci->load->library('upload');
			$path = rtrim(FCPATH, '/') . '/uploads/';
			if (!is_dir($path)) {
				@mkdir($path, 0777, true);
			}
			$this->ci->upload->initialize(array(
				'upload_path' => $path,
				'allowed_types' => '*',
				'encrypt_name' => true,
			));
			if ($this->ci->upload->do_upload('cv')) {
				$data = $this->ci->upload->data();
				$cvPath = 'uploads/' . $data['file_name'];
				if ($this->ci->db->field_exists('cv', 'interview')) {
					$this->ci->db->where('interview_id', $interview_id)->update('interview', array('cv' => $cvPath));
				} elseif ($this->ci->db->field_exists('cv_file', 'interview')) {
					$this->ci->db->where('interview_id', $interview_id)->update('interview', array('cv_file' => $cvPath));
				}
			}
		}

		return array('success' => true, 'message' => 'Application submitted successfully', 'interview_id' => $interview_id);
	}
}
