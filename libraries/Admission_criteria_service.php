<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Per-course structured admission criteria (Admin-built + seeded defaults).
 * PHP 5.6 compatible.
 */
class Admission_criteria_service {

	/** @var CI_Controller */
	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ensure_tables();
	}

	public function ensure_tables()
	{
		$this->ci->db->query("CREATE TABLE IF NOT EXISTS admission_criteria_sets (
			id INT NOT NULL AUTO_INCREMENT,
			course_id INT NOT NULL,
			title VARCHAR(255) NOT NULL DEFAULT '',
			governing_body VARCHAR(64) NOT NULL DEFAULT '',
			is_active TINYINT(1) NOT NULL DEFAULT 1,
			soft_fail TINYINT(1) NOT NULL DEFAULT 0,
			created_by VARCHAR(128) NULL,
			updated_by VARCHAR(128) NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY (id),
			KEY course_id (course_id),
			KEY is_active (is_active)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->ci->db->query("CREATE TABLE IF NOT EXISTS admission_criteria_rules (
			id INT NOT NULL AUTO_INCREMENT,
			set_id INT NOT NULL,
			rule_type VARCHAR(40) NOT NULL,
			label VARCHAR(255) NOT NULL DEFAULT '',
			config_json TEXT NULL,
			required TINYINT(1) NOT NULL DEFAULT 1,
			sort_order INT NOT NULL DEFAULT 0,
			path_group VARCHAR(32) NULL,
			PRIMARY KEY (id),
			KEY set_id (set_id),
			KEY sort_order (sort_order)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->ci->db->query("CREATE TABLE IF NOT EXISTS student_admission_eligibility (
			id INT NOT NULL AUTO_INCREMENT,
			student_id INT NULL,
			course_id INT NOT NULL,
			criteria_set_id INT NOT NULL,
			answers_json TEXT NULL,
			result VARCHAR(20) NOT NULL DEFAULT 'fail',
			override_by VARCHAR(128) NULL,
			override_reason VARCHAR(512) NULL,
			evaluated_at DATETIME NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY student_id (student_id),
			KEY course_id (course_id),
			KEY criteria_set_id (criteria_set_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}

	/**
	 * Idempotent seed for known live courses. Skips course if any set already exists.
	 */
	public function seed_defaults($force_course_id = 0)
	{
		$this->ensure_tables();
		$force_course_id = (int)$force_course_id;
		$seeded = 0;
		$skipped = 0;
		$map = $this->default_templates_by_course_id();

		foreach ($map as $course_id => $tpl) {
			$course_id = (int)$course_id;
			if ($force_course_id > 0 && $course_id !== $force_course_id) {
				continue;
			}
			$course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
			if (!$course) {
				$skipped++;
				continue;
			}
			$existing = $this->ci->db->get_where('admission_criteria_sets', array('course_id' => $course_id))->row_array();
			if ($existing && $force_course_id <= 0) {
				$skipped++;
				continue;
			}
			if ($existing && $force_course_id > 0) {
				$this->replace_set_rules((int)$existing['id'], $tpl['rules']);
				$this->ci->db->where('id', (int)$existing['id'])->update('admission_criteria_sets', array(
					'title' => $tpl['title'],
					'governing_body' => $tpl['governing_body'],
					'is_active' => 1,
					'updated_at' => date('Y-m-d H:i:s'),
					'updated_by' => 'seed',
				));
				$seeded++;
				continue;
			}
			$this->create_set_with_rules($course_id, $tpl['title'], $tpl['governing_body'], $tpl['rules'], 'seed');
			$seeded++;
		}

		return array('seeded' => $seeded, 'skipped' => $skipped);
	}

	public function template_keys()
	{
		$out = array();
		foreach ($this->default_templates_by_course_id() as $course_id => $tpl) {
			$out[] = array(
				'course_id' => (int)$course_id,
				'title' => $tpl['title'],
				'governing_body' => $tpl['governing_body'],
			);
		}
		return $out;
	}

	public function apply_template_to_course($course_id, $template_course_id, $actor = 'Admin', $replace = false)
	{
		$course_id = (int)$course_id;
		$template_course_id = (int)$template_course_id;
		$map = $this->default_templates_by_course_id();
		if (!isset($map[$template_course_id])) {
			return array('success' => false, 'message' => 'Unknown template');
		}
		$tpl = $map[$template_course_id];
		$existing = $this->get_active_set($course_id);
		if (!$existing) {
			$existing = $this->ci->db->order_by('id', 'DESC')->get_where('admission_criteria_sets', array('course_id' => $course_id))->row_array();
		}
		if ($existing) {
			if (!$replace) {
				return array('success' => false, 'message' => 'Criteria already exists for this course. Confirm replace.');
			}
			$this->replace_set_rules((int)$existing['id'], $tpl['rules']);
			$this->ci->db->where('id', (int)$existing['id'])->update('admission_criteria_sets', array(
				'title' => $tpl['title'],
				'governing_body' => $tpl['governing_body'],
				'is_active' => 1,
				'updated_at' => date('Y-m-d H:i:s'),
				'updated_by' => $actor,
			));
			return array('success' => true, 'set_id' => (int)$existing['id'], 'message' => 'Template applied');
		}
		$set_id = $this->create_set_with_rules($course_id, $tpl['title'], $tpl['governing_body'], $tpl['rules'], $actor);
		return array('success' => true, 'set_id' => $set_id, 'message' => 'Template applied');
	}

	public function get_active_set($course_id)
	{
		$course_id = (int)$course_id;
		return $this->ci->db
			->order_by('id', 'DESC')
			->get_where('admission_criteria_sets', array('course_id' => $course_id, 'is_active' => 1))
			->row_array();
	}

	public function get_set($set_id)
	{
		return $this->ci->db->get_where('admission_criteria_sets', array('id' => (int)$set_id))->row_array();
	}

	public function get_set_for_course($course_id, $include_inactive = false)
	{
		$course_id = (int)$course_id;
		if ($include_inactive) {
			$set = $this->ci->db->order_by('id', 'DESC')->get_where('admission_criteria_sets', array('course_id' => $course_id))->row_array();
		} else {
			$set = $this->get_active_set($course_id);
		}
		if (!$set) {
			return null;
		}
		$set['rules'] = $this->list_rules((int)$set['id']);
		return $set;
	}

	public function list_sets_summary()
	{
		$this->ci->db->select('admission_criteria_sets.*, courses.course_name');
		$this->ci->db->from('admission_criteria_sets');
		$this->ci->db->join('courses', 'courses.course_id = admission_criteria_sets.course_id', 'left');
		$this->ci->db->order_by('courses.course_name', 'ASC');
		$rows = $this->ci->db->get()->result_array();
		if (!$rows) {
			return array();
		}

		$set_ids = array();
		foreach ($rows as $row) {
			$set_ids[] = (int)$row['id'];
		}

		$rules_by_set = array();
		if ($set_ids) {
			$this->ci->db->where_in('set_id', $set_ids);
			$this->ci->db->order_by('sort_order', 'ASC');
			$this->ci->db->order_by('id', 'ASC');
			$rule_rows = $this->ci->db->get('admission_criteria_rules')->result_array();
			foreach ($rule_rows as $rule_row) {
				$sid = (int)$rule_row['set_id'];
				if (!isset($rules_by_set[$sid])) {
					$rules_by_set[$sid] = array();
				}
				$rules_by_set[$sid][] = $this->normalize_rule_row($rule_row);
			}
		}

		foreach ($rows as &$row) {
			$row['id'] = (int)$row['id'];
			$row['course_id'] = (int)$row['course_id'];
			$row['is_active'] = (int)$row['is_active'];
			$sid = (int)$row['id'];
			$rules = isset($rules_by_set[$sid]) ? $rules_by_set[$sid] : array();
			$row['rule_count'] = count($rules);
			$row['summary'] = $this->build_rules_summary($rules);
		}
		return $rows;
	}

	private function build_rules_summary($rules)
	{
		if (!$rules) {
			return '';
		}

		$qual_labels = array(
			'matric_science' => 'Matric Science',
			'matric_arts' => 'Matric Arts',
			'matric_general' => 'Matric General',
			'matric_any' => 'Matric (any group)',
			'fsc_pre_med' => 'FSc Pre-Medical',
			'fsc_pre_eng' => 'FSc Pre-Engineering',
			'fa' => 'FA',
			'icom' => 'I.COM',
			'ics' => 'ICS',
		);
		$subj_labels = array(
			'physics' => 'Physics',
			'chemistry' => 'Chemistry',
			'biology' => 'Biology',
			'computer_science' => 'Computer Science',
			'electric_wiring' => 'Electric Wiring',
			'poultry' => 'Poultry Farming',
			'math' => 'Mathematics',
		);

		$paths = array();
		$parts = array();
		$qualification = '';
		$min_percent = null;
		$subject_mins = array();
		$subject_avg = null;
		$subject_avg_subjects = array();
		$subjects_all = array();
		$subjects_any = array();
		$reject_arts = false;
		$reject_online = false;
		$age_min = null;
		$age_max = null;
		$gender = 'any';
		$doc_count = 0;

		foreach ($rules as $rule) {
			$pg = trim((string)$rule['path_group']);
			$cfg = isset($rule['config']) && is_array($rule['config']) ? $rule['config'] : array();

			if ($pg !== '') {
				if (!isset($paths[$pg])) {
					$paths[$pg] = array('qualification' => '', 'min_percent' => null, 'needs_fa' => false);
				}
				if ($rule['rule_type'] === 'qualification') {
					$allowed = isset($cfg['allowed']) && is_array($cfg['allowed']) ? $cfg['allowed'] : array();
					if (isset($allowed[0])) {
						$paths[$pg]['qualification'] = (string)$allowed[0];
					}
				}
				if ($rule['rule_type'] === 'min_percent' && isset($cfg['min'])) {
					$paths[$pg]['min_percent'] = (int)$cfg['min'];
				}
				if ($rule['rule_type'] === 'boolean') {
					$paths[$pg]['needs_fa'] = true;
				}
				continue;
			}

			if ($rule['rule_type'] === 'qualification') {
				$allowed = isset($cfg['allowed']) && is_array($cfg['allowed']) ? $cfg['allowed'] : array();
				$blocked = isset($cfg['blocked']) && is_array($cfg['blocked']) ? $cfg['blocked'] : array();
				if (count($allowed) === 1) {
					$qualification = (string)$allowed[0];
				} elseif (in_array('matric_any', $allowed, true)) {
					$qualification = 'matric_any';
				}
				foreach ($blocked as $b) {
					$b = strtolower((string)$b);
					if (strpos($b, 'arts') !== false || strpos($b, 'general') !== false) {
						$reject_arts = true;
					}
					if (strpos($b, 'online') !== false) {
						$reject_online = true;
					}
				}
			}
			if ($rule['rule_type'] === 'min_percent' && isset($cfg['min'])) {
				$min_percent = (int)$cfg['min'];
			}
			if ($rule['rule_type'] === 'subject_min_percent') {
				$mode = isset($cfg['mode']) ? (string)$cfg['mode'] : 'each';
				if ($mode === 'average') {
					$subject_avg = isset($cfg['min']) ? (int)$cfg['min'] : null;
					$subject_avg_subjects = isset($cfg['subjects']) && is_array($cfg['subjects'])
						? array_map('strval', $cfg['subjects'])
						: array();
				} else {
					$reqs = isset($cfg['requirements']) && is_array($cfg['requirements']) ? $cfg['requirements'] : array();
					foreach ($reqs as $req) {
						if (!is_array($req) || !isset($req['subject'])) {
							continue;
						}
						$subject_mins[] = array(
							'subject' => (string)$req['subject'],
							'min' => isset($req['min']) ? (int)$req['min'] : 0,
						);
					}
				}
			}
			if ($rule['rule_type'] === 'required_subjects') {
				$subjects_all = isset($cfg['subjects']) && is_array($cfg['subjects']) ? array_map('strval', $cfg['subjects']) : array();
				$subjects_any = isset($cfg['any_of']) && is_array($cfg['any_of']) ? array_map('strval', $cfg['any_of']) : array();
			}
			if ($rule['rule_type'] === 'age_range') {
				if (isset($cfg['min'])) {
					$age_min = (int)$cfg['min'];
				}
				if (isset($cfg['max'])) {
					$age_max = (int)$cfg['max'];
				}
			}
			if ($rule['rule_type'] === 'gender') {
				$allowed = isset($cfg['allowed']) && is_array($cfg['allowed']) ? array_map('strtolower', array_map('strval', $cfg['allowed'])) : array();
				if (in_array('female', $allowed, true)) {
					$gender = 'female';
				} elseif (in_array('male', $allowed, true)) {
					$gender = 'male';
				}
			}
			if ($rule['rule_type'] === 'document') {
				$doc_count++;
			}
		}

		if ($paths) {
			$path_parts = array();
			$i = 1;
			foreach ($paths as $p) {
				$s = isset($qual_labels[$p['qualification']]) ? $qual_labels[$p['qualification']] : $p['qualification'];
				if ($p['min_percent'] !== null) {
					$s .= ' >= ' . $p['min_percent'] . '%';
				}
				if (!empty($p['needs_fa'])) {
					$s .= ' + FA';
				}
				$path_parts[] = 'Option ' . $i . ': ' . $s;
				$i++;
			}
			$parts[] = 'Any one of: ' . implode('; ', $path_parts);
		} elseif ($qualification !== '') {
			$s = isset($qual_labels[$qualification]) ? $qual_labels[$qualification] : $qualification;
			if ($min_percent !== null) {
				$s .= ' — overall at least ' . $min_percent . '%';
			}
			$parts[] = $s;
		}

		if ($subjects_all) {
			$labels = array();
			foreach ($subjects_all as $v) {
				$labels[] = isset($subj_labels[$v]) ? $subj_labels[$v] : $v;
			}
			$parts[] = 'Subjects (all): ' . implode(', ', $labels);
		}
		if ($subjects_any) {
			$labels = array();
			foreach ($subjects_any as $v) {
				$labels[] = isset($subj_labels[$v]) ? $subj_labels[$v] : $v;
			}
			$parts[] = 'Plus any one of: ' . implode(', ', $labels);
		}
		if ($subject_mins) {
			$sm_parts = array();
			foreach ($subject_mins as $sm) {
				$sub = $sm['subject'];
				$lbl = isset($subj_labels[$sub]) ? $subj_labels[$sub] : $sub;
				$sm_parts[] = $lbl . ' >= ' . $sm['min'] . '%';
			}
			$parts[] = 'Har subject minimum: ' . implode(', ', $sm_parts);
		}
		if ($subject_avg !== null && $subject_avg_subjects) {
			$labels = array();
			foreach ($subject_avg_subjects as $v) {
				$labels[] = isset($subj_labels[$v]) ? $subj_labels[$v] : $v;
			}
			$parts[] = implode(' + ', $labels) . ' average >= ' . $subject_avg . '%';
		}
		if ($reject_arts) {
			$parts[] = 'Arts / General Matric not accepted';
		}
		if ($reject_online) {
			$parts[] = 'Online Matric not accepted';
		}
		if ($age_min !== null || $age_max !== null) {
			$parts[] = 'Age ' . ($age_min !== null ? $age_min : '?') . '-' . ($age_max !== null ? $age_max : '?');
		}
		if ($gender === 'female') {
			$parts[] = 'Female only';
		} elseif ($gender === 'male') {
			$parts[] = 'Male only';
		}
		if ($doc_count > 0) {
			$parts[] = $doc_count . ' document(s) required at admission';
		}

		return $parts ? implode(' · ', $parts) : '';
	}

	public function list_rules($set_id)
	{
		$rows = $this->ci->db
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get_where('admission_criteria_rules', array('set_id' => (int)$set_id))
			->result_array();
		$out = array();
		foreach ($rows as $row) {
			$out[] = $this->normalize_rule_row($row);
		}
		return $out;
	}

	public function create_blank_set($course_id, $title, $governing_body, $actor)
	{
		$course_id = (int)$course_id;
		if ($course_id <= 0) {
			return array('success' => false, 'message' => 'course_id required');
		}
		$existing = $this->ci->db->get_where('admission_criteria_sets', array('course_id' => $course_id))->row_array();
		if ($existing) {
			return array('success' => false, 'message' => 'Criteria set already exists for this course', 'set_id' => (int)$existing['id']);
		}
		$id = $this->create_set_with_rules($course_id, $title !== '' ? $title : 'Admission criteria', $governing_body, array(), $actor);
		return array('success' => true, 'set_id' => $id);
	}

	public function update_set($set_id, $fields, $actor)
	{
		$set_id = (int)$set_id;
		$set = $this->get_set($set_id);
		if (!$set) {
			return array('success' => false, 'message' => 'Set not found');
		}
		$data = array(
			'updated_at' => date('Y-m-d H:i:s'),
			'updated_by' => $actor,
		);
		if (isset($fields['title'])) {
			$data['title'] = trim((string)$fields['title']);
		}
		if (isset($fields['governing_body'])) {
			$data['governing_body'] = trim((string)$fields['governing_body']);
		}
		if (isset($fields['is_active'])) {
			$data['is_active'] = !empty($fields['is_active']) ? 1 : 0;
		}
		if (isset($fields['soft_fail'])) {
			$data['soft_fail'] = !empty($fields['soft_fail']) ? 1 : 0;
		}
		$this->ci->db->where('id', $set_id)->update('admission_criteria_sets', $data);
		return array('success' => true);
	}

	public function save_rules($set_id, $rules, $actor)
	{
		$set_id = (int)$set_id;
		$set = $this->get_set($set_id);
		if (!$set) {
			return array('success' => false, 'message' => 'Set not found');
		}
		if (!is_array($rules)) {
			$rules = array();
		}
		$this->replace_set_rules($set_id, $rules);
		$this->ci->db->where('id', $set_id)->update('admission_criteria_sets', array(
			'updated_at' => date('Y-m-d H:i:s'),
			'updated_by' => $actor,
		));
		return array('success' => true, 'rules' => $this->list_rules($set_id));
	}

	public function delete_set($set_id)
	{
		$set_id = (int)$set_id;
		$this->ci->db->where('set_id', $set_id)->delete('admission_criteria_rules');
		$this->ci->db->where('id', $set_id)->delete('admission_criteria_sets');
		return array('success' => true);
	}

	/**
	 * @param array $answers keyed by rule id (string/int) => value
	 * @return array {result: pass|fail, failures: [], soft_fail: bool, set: ...}
	 */
	public function evaluate($course_id, $answers)
	{
		$course_id = (int)$course_id;
		$set = $this->get_set_for_course($course_id, false);
		if (!$set) {
			return array(
				'result' => 'pass',
				'failures' => array(),
				'has_criteria' => false,
				'soft_fail' => false,
				'set' => null,
			);
		}
		if (!is_array($answers)) {
			$answers = array();
		}
		$rules = isset($set['rules']) ? $set['rules'] : array();
		$global = array();
		$groups = array();
		foreach ($rules as $rule) {
			$pg = isset($rule['path_group']) ? trim((string)$rule['path_group']) : '';
			if ($pg === '') {
				$global[] = $rule;
			} else {
				if (!isset($groups[$pg])) {
					$groups[$pg] = array();
				}
				$groups[$pg][] = $rule;
			}
		}

		$failures = array();
		foreach ($global as $rule) {
			$check = $this->check_rule($rule, $answers);
			if (!$check['ok']) {
				$failures[] = $check;
			}
		}

		if (count($groups) > 0) {
			$any_group_ok = false;
			$group_failures = array();
			foreach ($groups as $gkey => $grules) {
				$gf = array();
				$ok = true;
				foreach ($grules as $rule) {
					$check = $this->check_rule($rule, $answers);
					if (!$check['ok']) {
						$ok = false;
						$gf[] = $check;
					}
				}
				if ($ok) {
					$any_group_ok = true;
					break;
				}
				$group_failures = array_merge($group_failures, $gf);
			}
			if (!$any_group_ok) {
				$failures[] = array(
					'rule_id' => 0,
					'label' => 'Qualification path',
					'message' => 'None of the allowed qualification paths are satisfied',
					'details' => $group_failures,
				);
			}
		}

		$pass = count($failures) === 0;
		return array(
			'result' => $pass ? 'pass' : 'fail',
			'failures' => $failures,
			'has_criteria' => true,
			'soft_fail' => !empty($set['soft_fail']),
			'set' => array(
				'id' => (int)$set['id'],
				'course_id' => (int)$set['course_id'],
				'title' => $set['title'],
				'governing_body' => $set['governing_body'],
				'soft_fail' => (int)$set['soft_fail'],
			),
		);
	}

	/**
	 * Human-readable snapshot of staff answers for later compare / audit.
	 */
	public function answers_snapshot($set_id, $answers)
	{
		$set_id = (int)$set_id;
		if (!is_array($answers)) $answers = array();
		$rules = $this->ci->db
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get_where('admission_criteria_rules', array('set_id' => $set_id))
			->result_array();
		$qual_labels = array();
		foreach ($this->qualification_options() as $o) {
			$qual_labels[$o['value']] = $o['label'];
		}
		$subj_labels = array();
		foreach ($this->subject_options() as $o) {
			$subj_labels[$o['value']] = $o['label'];
		}
		$snapshot = array();
		foreach ($rules as $rule) {
			$rid = (string)$rule['id'];
			if (!array_key_exists($rid, $answers) && !array_key_exists((int)$rid, $answers)) {
				continue;
			}
			$val = array_key_exists($rid, $answers) ? $answers[$rid] : $answers[(int)$rid];
			$display = $this->_format_answer_display($rule, $val, $qual_labels, $subj_labels);
			$cfg = json_decode(isset($rule['config_json']) ? $rule['config_json'] : '{}', true);
			if (!is_array($cfg)) $cfg = array();
			$snapshot[] = array(
				'rule_id' => (int)$rule['id'],
				'rule_type' => $rule['rule_type'],
				'label' => $rule['label'],
				'path_group' => isset($rule['path_group']) ? $rule['path_group'] : null,
				'value' => $val,
				'display' => $display,
			);
		}
		return $snapshot;
	}

	private function _format_answer_display($rule, $val, $qual_labels, $subj_labels)
	{
		$type = isset($rule['rule_type']) ? $rule['rule_type'] : '';
		if ($type === 'document' || $type === 'boolean') {
			$ok = ($val === true || $val === 1 || $val === '1');
			return $ok ? 'Yes' : 'No';
		}
		if ($type === 'qualification_in' || $type === 'qualification') {
			$key = is_string($val) ? $val : (string)$val;
			return isset($qual_labels[$key]) ? $qual_labels[$key] : $key;
		}
		if ($type === 'subject_min_percent' && is_array($val)) {
			$bits = array();
			foreach ($val as $k => $pct) {
				if ($pct === '' || $pct === null) continue;
				$lab = isset($subj_labels[$k]) ? $subj_labels[$k] : $k;
				$bits[] = $lab . ': ' . $pct . '%';
			}
			return implode(', ', $bits);
		}
		if (is_array($val)) {
			return json_encode($val);
		}
		if (is_bool($val)) return $val ? 'Yes' : 'No';
		return (string)$val;
	}

	public function record_eligibility($student_id, $course_id, $set_id, $answers, $result, $override_by, $override_reason)
	{
		if (!is_array($answers)) $answers = array();
		$payload = array(
			'answers' => $answers,
			'snapshot' => $this->answers_snapshot($set_id, $answers),
		);
		$data = array(
			'student_id' => $student_id ? (int)$student_id : null,
			'course_id' => (int)$course_id,
			'criteria_set_id' => (int)$set_id,
			'answers_json' => json_encode($payload),
			'result' => $result,
			'override_by' => $override_by,
			'override_reason' => $override_reason,
			'evaluated_at' => date('Y-m-d H:i:s'),
			'created_at' => date('Y-m-d H:i:s'),
		);
		$this->ci->db->insert('student_admission_eligibility', $data);
		return (int)$this->ci->db->insert_id();
	}

	public function latest_eligibility_for_student($student_id)
	{
		$student_id = (int)$student_id;
		if ($student_id <= 0) return null;
		$row = $this->ci->db
			->order_by('id', 'DESC')
			->limit(1)
			->get_where('student_admission_eligibility', array('student_id' => $student_id))
			->row_array();
		if (!$row) return null;
		$decoded = json_decode(isset($row['answers_json']) ? $row['answers_json'] : '', true);
		$answers = array();
		$snapshot = array();
		if (is_array($decoded)) {
			if (isset($decoded['answers']) && is_array($decoded['answers'])) {
				$answers = $decoded['answers'];
				$snapshot = isset($decoded['snapshot']) && is_array($decoded['snapshot'])
					? $decoded['snapshot'] : array();
			} else {
				$answers = $decoded;
			}
		}
		if (!$snapshot && $answers) {
			$snapshot = $this->answers_snapshot((int)$row['criteria_set_id'], $answers);
		}
		$row['answers'] = $answers;
		$row['snapshot'] = $snapshot;
		unset($row['answers_json']);
		return $row;
	}

	public function qualification_options()
	{
		return array(
			array('value' => 'matric_science', 'label' => 'Matric Science'),
			array('value' => 'matric_arts', 'label' => 'Matric Arts'),
			array('value' => 'matric_general', 'label' => 'Matric General / Arts'),
			array('value' => 'matric_any', 'label' => 'Matric (Any group)'),
			array('value' => 'fsc_pre_med', 'label' => 'FSc Pre-Medical'),
			array('value' => 'fsc_pre_eng', 'label' => 'FSc Pre-Engineering'),
			array('value' => 'ics', 'label' => 'ICS'),
			array('value' => 'icom', 'label' => 'I.COM'),
			array('value' => 'fa', 'label' => 'FA'),
			array('value' => 'online_matric', 'label' => 'Online Matric'),
			array('value' => 'other', 'label' => 'Other'),
		);
	}

	public function subject_options()
	{
		return array(
			array('value' => 'physics', 'label' => 'Physics'),
			array('value' => 'chemistry', 'label' => 'Chemistry'),
			array('value' => 'biology', 'label' => 'Biology'),
			array('value' => 'computer_science', 'label' => 'Computer Science'),
			array('value' => 'electric_wiring', 'label' => 'Electric Wiring'),
			array('value' => 'poultry', 'label' => 'Poultry Farming'),
			array('value' => 'math', 'label' => 'Mathematics'),
		);
	}

	// ── internals ────────────────────────────────────────────────────────────

	private function normalize_rule_row($row)
	{
		$config = array();
		if (!empty($row['config_json'])) {
			$decoded = json_decode($row['config_json'], true);
			if (is_array($decoded)) {
				$config = $decoded;
			}
		}
		return array(
			'id' => (int)$row['id'],
			'set_id' => (int)$row['set_id'],
			'rule_type' => $row['rule_type'],
			'label' => $row['label'],
			'config' => $config,
			'required' => !empty($row['required']) ? 1 : 0,
			'sort_order' => (int)$row['sort_order'],
			'path_group' => isset($row['path_group']) && $row['path_group'] !== null ? $row['path_group'] : '',
		);
	}

	private function create_set_with_rules($course_id, $title, $governing_body, $rules, $actor)
	{
		$now = date('Y-m-d H:i:s');
		$this->ci->db->insert('admission_criteria_sets', array(
			'course_id' => (int)$course_id,
			'title' => $title,
			'governing_body' => $governing_body,
			'is_active' => 1,
			'soft_fail' => 0,
			'created_by' => $actor,
			'updated_by' => $actor,
			'created_at' => $now,
			'updated_at' => $now,
		));
		$set_id = (int)$this->ci->db->insert_id();
		$this->replace_set_rules($set_id, $rules);
		return $set_id;
	}

	private function replace_set_rules($set_id, $rules)
	{
		$set_id = (int)$set_id;
		$this->ci->db->where('set_id', $set_id)->delete('admission_criteria_rules');
		$order = 0;
		foreach ($rules as $rule) {
			if (!is_array($rule)) {
				continue;
			}
			$type = isset($rule['rule_type']) ? $rule['rule_type'] : (isset($rule['type']) ? $rule['type'] : '');
			if ($type === '') {
				continue;
			}
			$config = isset($rule['config']) && is_array($rule['config']) ? $rule['config'] : array();
			$path_group = '';
			if (isset($rule['path_group']) && $rule['path_group'] !== null && $rule['path_group'] !== '') {
				$path_group = (string)$rule['path_group'];
			}
			$sort = isset($rule['sort_order']) ? (int)$rule['sort_order'] : $order;
			$this->ci->db->insert('admission_criteria_rules', array(
				'set_id' => $set_id,
				'rule_type' => $type,
				'label' => isset($rule['label']) ? $rule['label'] : $type,
				'config_json' => json_encode($config),
				'required' => isset($rule['required']) ? (!empty($rule['required']) ? 1 : 0) : 1,
				'sort_order' => $sort,
				'path_group' => $path_group !== '' ? $path_group : null,
			));
			$order++;
		}
	}

	private function answer_for($rule, $answers)
	{
		$id = (string)(int)$rule['id'];
		if (array_key_exists($id, $answers)) {
			return $answers[$id];
		}
		if (array_key_exists((int)$rule['id'], $answers)) {
			return $answers[(int)$rule['id']];
		}
		$key = 'rule_' . $id;
		if (array_key_exists($key, $answers)) {
			return $answers[$key];
		}
		if (isset($rule['config']['field_key']) && array_key_exists($rule['config']['field_key'], $answers)) {
			return $answers[$rule['config']['field_key']];
		}
		return null;
	}

	private function check_rule($rule, $answers)
	{
		$required = !empty($rule['required']);
		$val = $this->answer_for($rule, $answers);
		$type = $rule['rule_type'];
		$label = $rule['label'];
		$config = isset($rule['config']) ? $rule['config'] : array();
		$empty = ($val === null || $val === '' || (is_array($val) && count($val) === 0));

		if ($empty) {
			if ($required) {
				return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => $label . ' is required');
			}
			return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');
		}

		switch ($type) {
			case 'qualification':
				$allowed = isset($config['allowed']) && is_array($config['allowed']) ? $config['allowed'] : array();
				$blocked = isset($config['blocked']) && is_array($config['blocked']) ? $config['blocked'] : array();
				$v = strtolower(trim((string)$val));
				if (count($blocked) && in_array($v, $blocked, true)) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'This qualification is not accepted');
				}
				if (count($allowed) && !in_array($v, $allowed, true)) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Qualification does not match allowed list');
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'group':
				$allowed = isset($config['allowed']) && is_array($config['allowed']) ? $config['allowed'] : array();
				$v = strtolower(trim((string)$val));
				if (count($allowed) && !in_array($v, $allowed, true)) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Group/stream not allowed');
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'min_percent':
				$min = isset($config['min']) ? (float)$config['min'] : 0;
				$num = (float)$val;
				if ($num < $min) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Overall minimum ' . $min . '% required (got ' . $num . '%)');
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'subject_min_percent':
				$mode = isset($config['mode']) ? (string)$config['mode'] : 'each';
				$marks = is_array($val) ? $val : array();
				if ($mode === 'average') {
					$subjects = isset($config['subjects']) && is_array($config['subjects']) ? $config['subjects'] : array();
					$min = isset($config['min']) ? (float)$config['min'] : 0;
					if (count($subjects) === 0) {
						return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');
					}
					$sum = 0;
					$count = 0;
					$missing = array();
					foreach ($subjects as $sub) {
						$sub = strtolower(trim((string)$sub));
						if ($sub === '') {
							continue;
						}
						if (!array_key_exists($sub, $marks) || $marks[$sub] === '' || $marks[$sub] === null) {
							$missing[] = ucfirst(str_replace('_', ' ', $sub));
							continue;
						}
						$sum += (float)$marks[$sub];
						$count++;
					}
					if (count($missing) > 0) {
						return array(
							'ok' => false,
							'rule_id' => (int)$rule['id'],
							'label' => $label,
							'message' => 'Enter marks for: ' . implode(', ', $missing),
						);
					}
					if ($count === 0) {
						return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Subject marks required');
					}
					$avg = round($sum / $count, 1);
					if ($avg < $min) {
						return array(
							'ok' => false,
							'rule_id' => (int)$rule['id'],
							'label' => $label,
							'message' => 'Subject group average minimum ' . $min . '% required (got ' . $avg . '%)',
						);
					}
					return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');
				}
				$reqs = isset($config['requirements']) && is_array($config['requirements']) ? $config['requirements'] : array();
				foreach ($reqs as $req) {
					if (!is_array($req)) {
						continue;
					}
					$sub = isset($req['subject']) ? strtolower(trim((string)$req['subject'])) : '';
					$min = isset($req['min']) ? (float)$req['min'] : 0;
					if ($sub === '') {
						continue;
					}
					$got = null;
					if (array_key_exists($sub, $marks)) {
						$got = (float)$marks[$sub];
					}
					if ($got === null || $got < $min) {
						$sub_label = ucfirst(str_replace('_', ' ', $sub));
						$got_txt = $got === null ? 'missing' : $got . '%';
						return array(
							'ok' => false,
							'rule_id' => (int)$rule['id'],
							'label' => $label,
							'message' => $sub_label . ' minimum ' . $min . '% required (got ' . $got_txt . ')',
						);
					}
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'required_subjects':
				$need = isset($config['subjects']) && is_array($config['subjects']) ? $config['subjects'] : array();
				$match = isset($config['match']) ? $config['match'] : 'all';
				$any_of = isset($config['any_of']) && is_array($config['any_of']) ? $config['any_of'] : array();
				$selected = is_array($val) ? $val : array($val);
				$norm = array();
				foreach ($selected as $s) {
					$norm[] = strtolower(trim((string)$s));
				}
				if ($match === 'all') {
					foreach ($need as $sub) {
						if (!in_array(strtolower($sub), $norm, true)) {
							return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Missing required subject: ' . $sub);
						}
					}
				}
				if (count($any_of) > 0) {
					$hit = false;
					foreach ($any_of as $sub) {
						if (in_array(strtolower($sub), $norm, true)) {
							$hit = true;
							break;
						}
					}
					if (!$hit) {
						return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Select at least one of: ' . implode(', ', $any_of));
					}
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'age_range':
				$min = isset($config['min']) ? (int)$config['min'] : 0;
				$max = isset($config['max']) ? (int)$config['max'] : 200;
				$age = null;
				if (is_array($val) && isset($val['dob'])) {
					$age = $this->age_from_dob($val['dob']);
				} elseif (preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$val)) {
					$age = $this->age_from_dob((string)$val);
				} else {
					$age = (int)$val;
				}
				if ($age === null || $age < $min || $age > $max) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Age must be between ' . $min . ' and ' . $max);
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'gender':
				$allowed = isset($config['allowed']) && is_array($config['allowed']) ? $config['allowed'] : array();
				$v = strtolower(trim((string)$val));
				$norm_allowed = array();
				foreach ($allowed as $a) {
					$norm_allowed[] = strtolower($a);
				}
				if (count($norm_allowed) && !in_array($v, $norm_allowed, true)) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Gender not eligible for this course');
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'document':
				$ok = ($val === true || $val === 1 || $val === '1' || $val === 'yes' || $val === 'Yes');
				if (!$ok) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => $label . ' not verified');
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'boolean':
				$expect = isset($config['expect']) ? $config['expect'] : true;
				$truthy = ($val === true || $val === 1 || $val === '1' || $val === 'yes' || $val === 'Yes');
				if ((bool)$expect !== $truthy) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => $label . ' failed');
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'number':
				$min = isset($config['min']) ? (float)$config['min'] : null;
				$max = isset($config['max']) ? (float)$config['max'] : null;
				$num = (float)$val;
				if ($min !== null && $num < $min) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Value below minimum');
				}
				if ($max !== null && $num > $max) {
					return array('ok' => false, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => 'Value above maximum');
				}
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');

			case 'text':
			default:
				return array('ok' => true, 'rule_id' => (int)$rule['id'], 'label' => $label, 'message' => '');
		}
	}

	private function age_from_dob($dob)
	{
		try {
			$d = new DateTime(substr($dob, 0, 10));
			$now = new DateTime('today');
			return (int)$d->diff($now)->y;
		} catch (Exception $e) {
			return null;
		}
	}

	private function rule($type, $label, $config, $path_group = '', $required = 1)
	{
		return array(
			'rule_type' => $type,
			'label' => $label,
			'config' => $config,
			'path_group' => $path_group,
			'required' => $required,
		);
	}

	private function ppc_docs()
	{
		return array(
			$this->rule('document', 'Matric certificate ×4', array()),
			$this->rule('document', 'Photos ×4', array()),
			$this->rule('document', 'CNIC / B-form ×2', array()),
			$this->rule('document', 'Character certificate', array()),
			$this->rule('document', 'Admission / domicile letter', array()),
		);
	}

	/**
	 * Live course_id map from production courses table.
	 */
	private function default_templates_by_course_id()
	{
		$ppc_pt = array(
			'title' => 'PPC Pharmacy Technician / Assistant',
			'governing_body' => 'PPC',
			'rules' => array_merge(array(
				$this->rule('qualification', 'Matric qualification', array(
					'allowed' => array('matric_science'),
					'blocked' => array('matric_arts', 'matric_general', 'online_matric', 'fa'),
				)),
				$this->rule('required_subjects', 'Science subjects', array(
					'subjects' => array('physics', 'chemistry'),
					'match' => 'all',
					'any_of' => array('biology', 'computer_science', 'electric_wiring', 'poultry'),
				)),
				$this->rule('min_percent', 'Matric percentage', array('min' => 45)),
			), $this->ppc_docs()),
		);

		$pmf = array(
			'title' => 'PMF Paramedical diploma',
			'governing_body' => 'PMF',
			'rules' => array(
				$this->rule('qualification', 'Matric Science', array('allowed' => array('matric_science'))),
				$this->rule('required_subjects', 'PCB subjects', array(
					'subjects' => array('physics', 'chemistry', 'biology'),
					'match' => 'all',
				)),
				$this->rule('min_percent', 'Minimum percentage', array('min' => 45)),
			),
		);

		$lhv_fww = array(
			'title' => 'PNMC LHV / FWW',
			'governing_body' => 'PNMC',
			'rules' => array(
				$this->rule('qualification', 'Qualification path A — Matric Science', array('allowed' => array('matric_science')), 'A'),
				$this->rule('min_percent', 'Percentage (path A)', array('min' => 45), 'A'),
				$this->rule('qualification', 'Qualification path B — FSc Pre-Medical', array('allowed' => array('fsc_pre_med')), 'B'),
				$this->rule('min_percent', 'Percentage (path B)', array('min' => 45), 'B'),
				$this->rule('age_range', 'Age', array('min' => 14, 'max' => 35)),
			),
		);

		$cmw = array(
			'title' => 'PNMC Community Midwifery',
			'governing_body' => 'PNMC',
			'rules' => array(
				$this->rule('qualification', 'Matric (any group)', array('allowed' => array('matric_science', 'matric_arts', 'matric_general', 'matric_any'))),
				$this->rule('min_percent', 'Minimum percentage', array('min' => 40)),
				$this->rule('age_range', 'Age', array('min' => 14, 'max' => 40)),
				$this->rule('gender', 'Gender', array('allowed' => array('female', 'Female'))),
			),
		);

		$cna = array(
			'title' => 'PNMC Certified Nurse Assistant',
			'governing_body' => 'PNMC',
			'rules' => array(
				$this->rule('qualification', 'Path A — FSc Pre-Medical', array('allowed' => array('fsc_pre_med')), 'A'),
				$this->rule('min_percent', 'Percentage (path A)', array('min' => 45), 'A'),
				$this->rule('qualification', 'Path B — Matric Science', array('allowed' => array('matric_science')), 'B'),
				$this->rule('min_percent', 'Percentage (path B)', array('min' => 45), 'B'),
				$this->rule('qualification', 'Path C — Matric Arts', array('allowed' => array('matric_arts', 'matric_general')), 'C'),
				$this->rule('boolean', 'FA completed (path C)', array('expect' => true), 'C'),
				$this->rule('min_percent', 'Percentage (path C)', array('min' => 50), 'C'),
				$this->rule('age_range', 'Age', array('min' => 14, 'max' => 35)),
			),
		);

		$bsn = array(
			'title' => 'PNMC / HEC BSN',
			'governing_body' => 'PNMC',
			'rules' => array(
				$this->rule('qualification', 'FSc Pre-Medical', array('allowed' => array('fsc_pre_med'))),
				$this->rule('min_percent', 'Minimum percentage', array('min' => 50)),
				$this->rule('age_range', 'Age', array('min' => 14, 'max' => 35)),
			),
		);

		$board_matric = array(
			'title' => 'Intermediate — Matric required',
			'governing_body' => 'Board',
			'rules' => array(
				$this->rule('qualification', 'Matric completed', array(
					'allowed' => array('matric_science', 'matric_arts', 'matric_general', 'matric_any'),
				)),
			),
		);

		return array(
			1 => $ppc_pt,
			2 => $ppc_pt,
			5 => $board_matric,
			6 => $board_matric,
			7 => $board_matric,
			8 => $board_matric,
			9 => $board_matric,
			10 => $lhv_fww,
			11 => $cmw,
			12 => $pmf,
			13 => $pmf,
			14 => $pmf,
			15 => $pmf,
			16 => $cna,
			17 => $bsn,
			20 => $lhv_fww,
			// 18 Tuition, 19 9TH, 21 Math — intentionally no seed
		);
	}

	/**
	 * Map a public applicant profile onto a criteria set's rule-id answers.
	 * Document/boolean rules are auto-passed for website preview (verified at admission).
	 */
	public function map_profile_to_answers($set, $profile, $auto_pass_documents = true)
	{
		$answers = array();
		if (!$set || empty($set['rules']) || !is_array($set['rules'])) {
			return $answers;
		}
		if (!is_array($profile)) {
			$profile = array();
		}
		$qualification = isset($profile['qualification']) ? strtolower(trim((string)$profile['qualification'])) : '';
		$overall = isset($profile['overall_percent']) && $profile['overall_percent'] !== ''
			? (float)$profile['overall_percent'] : null;
		$subjects = isset($profile['subjects']) && is_array($profile['subjects']) ? $profile['subjects'] : array();
		$dob = isset($profile['date_of_birth']) ? trim((string)$profile['date_of_birth']) : '';
		$gender = isset($profile['gender']) ? strtolower(trim((string)$profile['gender'])) : '';
		$group = isset($profile['group']) ? strtolower(trim((string)$profile['group'])) : '';
		$flags = isset($profile['flags']) && is_array($profile['flags']) ? $profile['flags'] : array();

		foreach ($set['rules'] as $rule) {
			$rid = (string)$rule['id'];
			$type = isset($rule['rule_type']) ? $rule['rule_type'] : '';
			if ($type === 'qualification') {
				if ($qualification !== '') $answers[$rid] = $qualification;
			} elseif ($type === 'group') {
				if ($group !== '') $answers[$rid] = $group;
				elseif ($qualification !== '') $answers[$rid] = $qualification;
			} elseif ($type === 'min_percent') {
				if ($overall !== null) $answers[$rid] = $overall;
			} elseif ($type === 'subject_min_percent' || $type === 'required_subjects') {
				if (count($subjects)) $answers[$rid] = $subjects;
			} elseif ($type === 'age_range') {
				if ($dob !== '') $answers[$rid] = $dob;
			} elseif ($type === 'gender') {
				if ($gender !== '') $answers[$rid] = $gender;
			} elseif ($type === 'document' || $type === 'boolean') {
				if ($auto_pass_documents) {
					$answers[$rid] = true;
				} elseif (isset($flags[$rid])) {
					$answers[$rid] = !empty($flags[$rid]);
				}
			} elseif ($type === 'number' && $overall !== null) {
				$answers[$rid] = $overall;
			}
		}
		return $answers;
	}

	/**
	 * Evaluate one applicant profile against all active course criteria sets.
	 * Returns eligible / not_eligible course lists for the public website.
	 */
	public function match_courses_for_profile($profile, $course_ids = null)
	{
		$this->ensure_tables();
		$this->seed_defaults();

		$wanted = null;
		if (is_array($course_ids) && count($course_ids)) {
			$wanted = array();
			foreach ($course_ids as $id) {
				$id = (int)$id;
				if ($id > 0) $wanted[$id] = true;
			}
		}

		$this->ci->db->select('admission_criteria_sets.*, courses.course_name, courses.course_type, courses.course_duration_year');
		$this->ci->db->from('admission_criteria_sets');
		$this->ci->db->join('courses', 'courses.course_id = admission_criteria_sets.course_id', 'inner');
		$this->ci->db->where('admission_criteria_sets.is_active', 1);
		if ($this->ci->db->field_exists('status', 'courses')) {
			$this->ci->db->where('courses.status', 1);
		}
		$this->ci->db->order_by('courses.course_name', 'ASC');
		$rows = $this->ci->db->get()->result_array();

		$eligible = array();
		$not_eligible = array();
		$skipped = array();

		foreach ($rows as $row) {
			$course_id = (int)$row['course_id'];
			if ($wanted !== null && empty($wanted[$course_id])) {
				continue;
			}
			$set = $this->get_set_for_course($course_id, false);
			if (!$set || empty($set['rules'])) {
				$skipped[] = array(
					'course_id' => $course_id,
					'course_name' => isset($row['course_name']) ? $row['course_name'] : '',
					'reason' => 'No active criteria',
				);
				continue;
			}
			$answers = $this->map_profile_to_answers($set, $profile, true);
			$eval = $this->evaluate($course_id, $answers);
			$item = array(
				'course_id' => $course_id,
				'course_name' => isset($row['course_name']) ? $row['course_name'] : '',
				'course_type' => isset($row['course_type']) ? $row['course_type'] : '',
				'course_duration_year' => isset($row['course_duration_year']) ? $row['course_duration_year'] : '',
				'criteria_title' => isset($set['title']) ? $set['title'] : '',
				'governing_body' => isset($set['governing_body']) ? $set['governing_body'] : '',
				'result' => $eval['result'],
				'failures' => isset($eval['failures']) ? $eval['failures'] : array(),
			);
			if ($eval['result'] === 'pass') {
				$eligible[] = $item;
			} else {
				$not_eligible[] = $item;
			}
		}

		return array(
			'eligible' => $eligible,
			'not_eligible' => $not_eligible,
			'skipped' => $skipped,
			'note' => 'Document checks are confirmed at the admission desk; this preview uses academic profile only.',
		);
	}
}
