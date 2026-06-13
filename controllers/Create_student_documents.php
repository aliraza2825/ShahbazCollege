<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Create_student_documents extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		//$this->load->library('Email_reader');	
		ini_set('max_input_time', 0);
		ini_set('max_execution_time', 0);
	}

	public function index()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('create_student_documents/index',$data);
		$this->load->view('inc/footer');
	}

	public function getClasses()
	{
		$campus_id = $this->input->post('campus_id');
		$classes = $this->db->get_where('classes',array('campus_id'=>$campus_id))->result_array();
		$html='';
		foreach($classes as $class)
		{
			$html.='<option value="'.$class['class_id'].'">'.$class['name'].'</option>';
		}
		echo $html;
	}

	public function getStudentsCount()
	{
		$class_id = $this->input->post('class_id');
		$students = $this->db->get_where('students',array('class_id'=>$class_id))->result_array();

		$total_students = count($students);
		//echo $total_students;
		$html='';
		//1st
		if($total_students>1 && $total_students<100)
		{
			$html.='<option value="100,1">1-'.$total_students.'</option>';
		}
		elseif($total_students>1 && $total_students>=100)
		{
			$html.='<option value="100-1">1-100</option>';
		}
		else
		{

		}
		//2nd
		if($total_students>100 && $total_students<200)
		{
			$html.='<option value="100,100">100-'.$total_students.'</option>';
		}
		elseif($total_students>100 && $total_students>=200)
		{
			$html.='<option value="100,100">100-200</option>';
		}
		else
		{
			
		}
		//3rd
		if($total_students>200 && $total_students<300)
		{
			$html.='<option value="100,200">200-'.$total_students.'</option>';
		}
		elseif($total_students>200 && $total_students>=300)
		{
			$html.='<option value="100,200">200-300</option>';
		}
		else
		{
			
		}
		//4rd
		if($total_students>300 && $total_students<400)
		{
			$html.='<option value="300,100">300-'.$total_students.'</option>';
		}
		elseif($total_students>300 && $total_students>=400)
		{
			$html.='<option value="300,100">300-400</option>';
		}
		else
		{
			
		}
		echo $html;
	}

	public function generate_documents()
	{
		ini_set('max_input_time', 0);
		ini_set('max_execution_time', 0);
		$class_id =$this->input->post('class_id');
		$students_count = $this->input->post('students_count');

		$this->db->select('*');
		$this->db->from('classes');
		$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
		$this->db->where('classes.class_id',$class_id);
		$data = $this->db->get()->result_array();

		if (!is_dir('students_data')) {
			mkdir('./students_data', 0777, TRUE);
		}

		if (!is_dir('students_data/'.$data[0]['campus_name'])) {
			mkdir('./students_data/'.$data[0]['campus_name'], 0777, TRUE);
		}

		if (!is_dir('students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'])) {
			mkdir('./students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'], 0777, TRUE);
		}

		$this->db->select('*');
		$this->db->from('students');
		$this->db->where('class_id',$class_id);
		$this->db->limit($students_count);
		$students = $this->db->get()->result_array();

		foreach($students as $student)
		{
			if (!is_dir('students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'])) {
				mkdir('./students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'], 0777, TRUE);
			}
			$documents = $this->db->get_where('student_documents',array('student_id'=>$student['student_id']))->result_array();
			foreach($documents as $document)
			{
				//SAVE STUDENT PHOTO
				if($document['type']=='Photo')
				{
					if($document['online_image']!='')
					{
						$image_link = $document['online_image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/photo.'.$extension;
						copy($image_link, $new_link);
					}
					else
					{
						$image_link = base_url().'uploads/'.$document['image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/photo.'.$extension;
						copy($image_link, $new_link);
					}
				}
				//SAVE STUDENT ID CARD
				elseif($document['type']=='ID Card')
				{
					if($document['online_image']!='')
					{
						$image_link = $document['online_image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						if(file_exists('./students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/id-card-front.'.$extension))
						{
							$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/id-card-back.'.$extension;
						}
						else
						{
							$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/id-card-front.'.$extension;
						}
						copy($image_link, $new_link);
					}
					else
					{
						$image_link = base_url().'uploads/'.$document['image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						if(file_exists('./students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/id-card-front.'.$extension))
						{
							$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/id-card-back.'.$extension;
						}
						else
						{
							$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/id-card-front.'.$extension;
						}
						copy($image_link, $new_link);
					}
				}
				//SAVE STUDENT RESULT CARD
				elseif($document['type']=='Result Card')
				{
					if($document['online_image']!='')
					{
						$image_link = $document['online_image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/result-card.'.$extension;
						copy($image_link, $new_link);
					}
					else
					{
						$image_link = base_url().'uploads/'.$document['image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/result-card.'.$extension;
						copy($image_link, $new_link);
					}
				}
				//SAVE STUDENT B-FORM
				elseif($document['type']=='B - FORM')
				{
					if($document['online_image']!='')
					{
						$image_link = $document['online_image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/b-form.'.$extension;
						copy($image_link, $new_link);
					}
					else
					{
						$image_link = base_url().'uploads/'.$document['image'];
						$array = explode('.', $image_link);
						$extension = end($array);
						$new_link = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'].'/'.$student['roll_no'].'/b-form.'.$extension;
						copy($image_link, $new_link);
					}
				}
				else
				{

				}
			}
		}

		// Get real path for our folder
		$rootPath = realpath('./students_data/'.$data[0]['campus_name'].'/'.$data[0]['name']);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open('file.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
	
		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
	
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);
	
				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}
	
		// Zip archive will be created only after closing object
		$zip->close();

		// folder path that contains files and subfolders
		$path = './students_data/'.$data[0]['campus_name'].'/'.$data[0]['name'];
		
		// call the function
		$this->deleteAll($path);

		echo '<a href="'.base_url('file.zip').'" class="btn green"><i class="fa fa-download"></i> Download File</a>';
		exit;
	}

	public function deleteAll($dir, $remove = false) 
	{
		$structure = glob(rtrim($dir, "/").'/*');
		if (is_array($structure)) {
		foreach($structure as $file) {
		if (is_dir($file))
		$this->deleteAll($file,true);
		else if(is_file($file))
		unlink($file);
		}
		}
		if($remove)
		rmdir($dir);
	}
}
