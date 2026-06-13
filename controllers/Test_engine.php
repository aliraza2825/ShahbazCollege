<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_engine extends CI_Controller {

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
	public function courses()
	{
		$data['classes'] = $this->db->get('classes')->result_array();
		$data['courses']=$this->db->get('courses')->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/courses', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_course()
	{
		$data=$this->input->post();
		
		$check = $this->db->get_where('courses', array('course_name'=>$this->input->post('course_name')))->result_array();
		if(count($check)>0)
		{
			$this->session->set_flashdata('error', 'Course already added.');
			redirect('test_engine/courses');
		}
		
		foreach(@$data as $k=>$value){
			if($k=='class_ids')
			{
				$this->db->set(''.$k.'', implode(',',$value));
			}
			else
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		$this->db->insert('courses');
		$this->session->set_flashdata('message', 'Course added successfully');
		redirect('test_engine/courses');
	}
	
	
	
	public function subjects()
	{
		$data['courses']=$this->db->get('courses')->result_array();
		
		if(@$this->input->post('submit')==1)
		{
			$this->db->select('*');
			$this->db->from('course_subjects');
			$this->db->join('courses', 'courses.course_id=course_subjects.course_id', 'inner');
			$this->db->where(array('course_subjects.status'=>1,'courses.course_id'=>$this->input->post('course_id')));
			$data['subjects']=$this->db->get()->result_array();
		}
		else
		{
			$data['subjects']=array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/subjects', $data);
		$this->load->view('inc/footer');
	}
	
	public function subject_all_questions($subject_id)
	{
		if ($subject_id != null) {

            $this->db->select('*');
            $this->db->from('questions');
            $this->db->join('chapters', 'chapters.chapter_id=questions.chapter_id', 'inner');
            $this->db->join('topics', 'topics.topic_id=questions.topic_id', 'inner');
            $this->db->where(array('subject_id'=>$subject_id, 'option_1!='=>''));
            $data['questions'] = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('questions');
            $this->db->join('chapters', 'chapters.chapter_id=questions.chapter_id', 'inner');
            $this->db->join('topics', 'topics.topic_id=questions.topic_id', 'inner');
            $this->db->where(array('subject_id'=>$subject_id, 'type ='=>'short-question'));
            $data['shortquestions'] = $this->db->get()->result_array();
                
            $this->db->select('*');
            $this->db->from('questions');
            $this->db->join('chapters', 'chapters.chapter_id=questions.chapter_id', 'inner');
            $this->db->join('topics', 'topics.topic_id=questions.topic_id', 'inner');
            $this->db->where(array('subject_id'=>$subject_id, 'type ='=>'long-question'));
            $data['longquestions'] = $this->db->get()->result_array();
            
            $this->db->select('*');
            $this->db->from('questions');
            $this->db->join('chapters', 'chapters.chapter_id=questions.chapter_id', 'inner');
            $this->db->join('topics', 'topics.topic_id=questions.topic_id', 'inner');
            $this->db->where(array('subject_id'=>$subject_id, 'type ='=>'word-meaning'));
            $data['wordmeanings'] = $this->db->get()->result_array();
            

            $this->load->view('inc/header');
            $this->load->view('inc/sidebar');
            $this->load->view('test_engine/subject_all_questions', $data);
            $this->load->view('inc/footer');
        }
	}
	
	public function update_question_status()
    {
        $question_id = $this->input->post("id");
        $status      = $this->input->post("status");

        $this->db->set("test_status",$status);
        $this->db->where('question_id ', $question_id);
        $this->db->update('questions');
        echo json_encode(array(["status",1]));
    }
	
	public function add_practical($subject_id)
	{
		$data['courses']=$this->db->get('courses')->result_array();
		
		
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->join('courses', 'courses.course_id=course_subjects.course_id', 'inner');
		$this->db->where('course_subjects.course_subject_id', $subject_id);
		$data['subjects']=$this->db->get()->result_array();
		
		$data['practicals'] = $this->db->get_where('practicals', array('subject_id'=>$subject_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/add_practical', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_practical($subject_id, $practical_id)
	{	
		$data['practicals'] = $this->db->get_where('practicals', array('subject_id'=>$subject_id, 'practical_id'=>$practical_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/edit_practical', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_practical_data($subject_id)
	{
		$this->db->set('data',$this->input->post('data'));
		$this->db->set('practical_name',$this->input->post('practical_name'));
		//$this->db->set('video',$video);
		$this->db->set('subject_id',$subject_id);
		$this->db->set('add_by',$this->input->post('add_by'));
		$this->db->set('last_edit',$this->input->post('last_edit'));
		$this->db->set('status',$this->input->post('status'));
		$this->db->insert('practicals');
		
		$this->session->set_flashdata('message', 'Practical Data Added successfully');
		redirect('test_engine/add_practical/'.$subject_id);
	}
	
	public function update_practical_data($subject_id, $practical_id)
	{
		$this->db->set('data',$this->input->post('data'));
		$this->db->set('practical_name',$this->input->post('practical_name'));
		//$this->db->set('video',$video);
		$this->db->set('subject_id',$subject_id);
		$this->db->set('last_edit',$this->input->post('last_edit'));
		$this->db->set('status',$this->input->post('status'));
		$this->db->where('practical_id', $practical_id);
		$this->db->update('practicals');
		
		$this->session->set_flashdata('message', 'Practical Data Updated successfully');
		redirect('test_engine/edit_practical/'.$subject_id.'/'.$practical_id);
	}
	
	public function delete_practical($practical_id, $subject_id)
	{
		$this->db->where('practical_id', $practical_id);
		$this->db->delete('practicals');
		
		$this->session->set_flashdata('message', 'Practical Deleted successfully');
		redirect('test_engine/add_practical/'.$subject_id);
	}
	
	public function insert_subject()
	{
		$data=$this->input->post();
		
		$check = $this->db->get_where('course_subjects', array('subject_name'=>$this->input->post('subject_name'), 'course_id'=>$this->input->post('course_id')))->result_array();
		if(count($check)>0)
		{
			$this->session->set_flashdata('error', 'Subject already added.');
			redirect('test_engine/subjects');
		}
		
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('course_subjects');
		$this->session->set_flashdata('message', 'Subject added successfully');
		redirect('test_engine/subjects');
	}
	
	public function delete_subject($course_subject_id)
	{
		$this->db->where('course_subject_id', $course_subject_id);
		$this->db->delete('course_subjects');
		$this->session->set_flashdata('message', 'Subject deleted successfully');
		redirect('test_engine/subjects');
	}
	
	public function edit_subject($course_subject_id)
	{
		$data['courses']=$this->db->get('courses')->result_array();
		$data['subject'] = $this->db->get_where('course_subjects', array('course_subject_id'=>$course_subject_id))->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/edit_subject', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_subject($course_subject_id)
	{
		$data= $this->input->post();
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('course_subject_id', $course_subject_id);
		$this->db->update('course_subjects');
		$this->session->set_flashdata('message', 'Subject Updated successfully');
		redirect('test_engine/subjects');
	}
	
	public function topics()
	{
		$data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();
		
		$access = checkUserAccess();
		$subject_ids = @explode(',',$access[0]['test_engine_subject_ids']);
		
		$this->db->select('*');
		$this->db->from('course_subjects');
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('course_subjects.course_subject_id', $subject_ids);
		}
		$data['subjects'] = $this->db->get()->result_array();
		
		if(@$this->input->post('submit')==1)
		{
			$this->db->select('*');
			$this->db->from('topics');
			$this->db->join('chapters', 'topics.chapter_id=chapters.chapter_id', 'inner');
			$this->db->join('course_subjects', 'course_subjects.course_subject_id=chapters.course_subject_id', 'inner');
			if($this->input->post('chapter_id')!= "" && $this->input->post('chapter_id')!= NULL )
				$this->db->where('chapters.chapter_id',$this->input->post('chapter_id'));
			if($this->session->userdata('role')!='Admin'){
				$this->db->where_in('course_subjects.course_subject_id', $subject_ids);
			}
			$data['topics']=$this->db->get()->result_array();
		}
		else
		{
			$data['topics']=array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/topics', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_topic()
	{
		$data=$this->input->post();
		
		$check = $this->db->get_where('topics', array('topic_name'=>$this->input->post('topic_name'), 'course_subject_id'=>$this->input->post('course_subject_id')))->result_array();
		if(count($check)>0)
		{
			$this->session->set_flashdata('error', 'Topic already added.');
			redirect('test_engine/topics');
		}
		
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('topics');
		$this->session->set_flashdata('message', 'Topic added successfully');
		redirect('test_engine/topics');
	}
	
	public function delete_topic($topic_id)
	{
		$this->db->where('topic_id', $topic_id);
		$this->db->delete('topics');
		$this->session->set_flashdata('message', 'Topic deleted successfully');
		redirect('test_engine/topics');
	}
	
	public function edit_topic($topic_id)
	{
		$data['subjects']=$this->db->get('course_subjects')->result_array();
		$data['topic'] = $this->db->get_where('topics', array('topic_id'=>$topic_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/edit_topic', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_topic($topic_id)
	{
		$data= $this->input->post();
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('topic_id', $topic_id);
		$this->db->update('topics');
		$this->session->set_flashdata('message', 'Topic Updated successfully');
		redirect('test_engine/topics');
	}
	
	public function add_question($topic_id)
	{
		$data['topics'] = $this->db->get_where('topics', array('topic_id'=>$topic_id))->result_array();
		
		$this->db->select('*');
		$this->db->from('questions');
		$this->db->where(array('topic_id'=>$topic_id, 'option_1!='=>''));
		$data['questions'] = $this->db->get()->result_array();
		$data['shortquestions'] = $this->db->get_where('questions', array('topic_id'=>$topic_id, 'type'=>'short-question'))->result_array();
		$data['longquestions'] = $this->db->get_where('questions', array('topic_id'=>$topic_id, 'type'=>'long-question'))->result_array();
		$data['wordmeanings'] = $this->db->get_where('questions', array('topic_id'=>$topic_id, 'type'=>'word-meaning'))->result_array();
		$data['videos'] = $this->db->get_where('question_videos', array('topic_id'=>$topic_id))->result_array();
		
		$topicdata = $this->db->get_where('books', array('topic_id'=>$topic_id))->result_array();
		if(count($topicdata)>0)
		{
			$data['topicdata']=$topicdata;
		}
		
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/add_question', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_question($topic_id)
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'recording/';
		
    	// set the filter image types
		$config['allowed_types'] = '*';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('audio')) {
			$data = array('msg' => $this->upload->display_errors());
			$audio = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$audio = $data['upload_data']['file_name'];
			}
		}
		
		$type 		= @$this->input->post('type');
		$difficulty = @$this->input->post('difficulty');
		$question 	= @$this->input->post('question');
		$option_1 	= @$this->input->post('option_1');
		$option_2 	= @$this->input->post('option_2');
		$option_3 	= @$this->input->post('option_3');
		$option_4 	= @$this->input->post('option_4');
		$answer 	= implode(',',@$this->input->post('answer'));
		$explantion	= @$this->input->post('explanation');
		$recording	= @$this->input->post('recording');
		$add_by		= @$this->input->post('add_by');
		$last_edit	= @$this->input->post('last_edit');
		$status		= @$this->input->post('status');
		$audio		= $audio;
		
		$this->db->set(array(
							'type'			=>$type, 
							'difficulty'	=>$difficulty, 
							'question'		=>$question, 
							'option_1'		=>$option_1, 
							'option_2'		=>$option_2, 
							'option_3'		=>$option_3, 
							'option_4'		=>$option_4, 
							'answer'		=>$answer, 
							'explanation'	=>$explantion, 
							'topic_id'		=>$topic_id, 
							'add_by'		=>$add_by, 
							'last_edit'		=>$last_edit, 
							'status'		=>$status,
							'audio'			=>$audio,
							'created_at'	=>date('Y-m-d H:i:s')
							));
		$this->db->insert('questions');
		$insert_id = $this->db->insert_id();
		//echo $recording; exit;
		
		redirect('test_engine/add_question/'.$topic_id);
	}

    public function insert_questions($topic_id)
    {
        $type 		= 'short-question';
        $difficulty = @$this->input->post('difficulty');
        $question 	= @$this->input->post('question');
        $explantion	= @$this->input->post('explanation');
        $add_by		= $this->session->userdata('name');
        $last_edit	= $this->session->userdata('name');
        $status		= 0;

        $counter = count($difficulty);

        for($i=0;$i<$counter;$i++)
        {
            $_FILES['file']['name']       = $_FILES['audio']['name'][$i];
            $_FILES['file']['type']       = $_FILES['audio']['type'][$i];
            $_FILES['file']['tmp_name']   = $_FILES['audio']['tmp_name'][$i];
            $_FILES['file']['error']      = $_FILES['audio']['error'][$i];
            $_FILES['file']['size']       = $_FILES['audio']['size'][$i];

            //load the helper
            $this->load->helper('form');

            //Configure
            //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
            $config['upload_path'] = 'recording/';

            // set the filter image types
            $config['allowed_types'] = '*';

            //load the upload library
            $this->load->library('upload', $config);

            $this->upload->initialize($config);

            $this->upload->set_allowed_types('*');

            $data['upload_data'] = '';

            //if not successful, set the error message
            if (!$this->upload->do_upload('file')) {
                $data = array('msg' => $this->upload->display_errors());
                $audio = '';

            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $audio = $data['upload_data']['file_name'];
                }
            }

            $this->db->set(array(
                'type'			=>$type,
                'difficulty'	=>$difficulty[$i],
                'question'		=>$question[$i],
                'explanation'	=>$explantion[$i],
                'topic_id'		=>$topic_id,
                'add_by'		=>$add_by,
                'audio'			=>$audio,
                'last_edit'		=>$last_edit,
                'status'		=>$status,
				'created_at'	=>date('Y-m-d H:i:s')
            ));
            $this->db->insert('questions');
        }

        redirect('test_engine/add_question/'.$topic_id);
    }
	
	
	public function delete_question($question_id, $topic_id)
	{
		$this->db->where('question_id', $question_id);
		$this->db->delete('questions');
		$this->session->set_flashdata('message', 'Question deleted successfully');
		redirect('test_engine/add_question/'.$topic_id);
	}
	
	public function edit_question($question_id, $topic_id)
	{
		$data['topics'] = $this->db->get_where('topics', array('topic_id'=>$topic_id))->result_array();
		$findat=$data['topics'][0];
        $data['alltopics'] = $this->db->get_where('topics', array('chapter_id'=>$findat['chapter_id']))->result_array();
		$data['question'] = $this->db->get_where('questions', array('question_id'=>$question_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/edit_question', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_question($question_id, $topic_id)
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'recording/';
		
    	// set the filter image types
		$config['allowed_types'] = '*';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('audio')) {
			$data = array('msg' => $this->upload->display_errors());
			$audio = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$audio = $data['upload_data']['file_name'];
			}
		}
		
		$type 		= $this->input->post('type');
		$difficulty = @$this->input->post('difficulty');
		$question 	= $this->input->post('question');
		$option_1 	= $this->input->post('option_1');
		$option_2 	= $this->input->post('option_2');
		$option_3 	= $this->input->post('option_3');
		$option_4 	= $this->input->post('option_4');
		$answer 	= implode(',',$this->input->post('answer'));
		$explantion	= $this->input->post('explanation');
		$last_edit	= $this->input->post('last_edit');
		$topic_id	= $this->input->post('topic');
		$status		= 0;
		if($audio=='')
		{
			$audio=$this->input->post('old_audio');
		}
		
		$this->db->set(array(
							'type'			=>$type, 
							'difficulty'	=>$difficulty,
							'question'		=>$question, 
							'option_1'		=>$option_1, 
							'option_2'		=>$option_2, 
							'option_3'		=>$option_3, 
							'option_4'		=>$option_4, 
							'answer'		=>$answer, 
							'explanation'	=>$explantion, 
							'topic_id'		=>$topic_id,
							'last_edit'		=>$last_edit, 
							'status'		=>$status,
							'audio'			=>$audio
							));
		$this->db->where('question_id', $question_id);
		$this->db->update('questions');
		$this->session->set_flashdata('message', 'Question updated successfully');
		redirect('test_engine/add_question/'.$topic_id);
	}
	
	public function insert_wordmeaning($topic_id)
	{
		$data = $this->input->post();
		$counter = (count($data)-3)/3;
		for($i=1; $i<=$counter; $i++)
		{
			$this->db->set('word',$this->input->post('word_'.$i));
			$this->db->set('meaning_english',$this->input->post('meaning_english_'.$i));
			$this->db->set('meaning_urdu',$this->input->post('meaning_urdu_'.$i));
			$this->db->set('type','word-meaning');
			$this->db->set('topic_id',$topic_id);
			$this->db->set('add_by',$this->input->post('add_by'));
			$this->db->set('last_edit',$this->input->post('last_edit'));
			$this->db->set('status',$this->input->post('status'));
			$this->db->set('created_at',date('Y-m-d H:i:s'));
			$this->db->insert('questions');
		}
		$this->session->set_flashdata('message', 'Word Meaning Added successfully');
		redirect('test_engine/add_question/'.$topic_id);
	}
	
	public function update_wordmeaning($question_id, $topic_id)
	{
		$word 				= $this->input->post('word');
		$meaning_english 	= @$this->input->post('meaning_english');
		$meaning_urdu 		= $this->input->post('meaning_urdu');
		
		$this->db->set(array(
							'word'				=>$word, 
							'meaning_english'	=>$meaning_english,
							'meaning_urdu'		=>$meaning_urdu,
                            'status'            =>0
							));
		$this->db->where('question_id', $question_id);
		$this->db->update('questions');
		$this->session->set_flashdata('message', 'Word Meaning updated successfully');
		redirect('test_engine/add_question/'.$topic_id);
	}
	
	public function insert_topic_data($topic_id)
	{	
		$check_topic = $this->db->get_where('books', array('topic_id'=>$topic_id))->result_array();
		if(count($check_topic)>0)
		{
			$this->db->set('data',$this->input->post('data'));
			$this->db->set('add_by',$this->input->post('add_by'));
			$this->db->set('last_edit',$this->input->post('last_edit'));
			$this->db->set('status',$this->input->post('status'));
			$this->db->set('video',$this->input->post('video'));
			$this->db->where('topic_id',$topic_id);
			$this->db->update('books');
		}
		else
		{
			$this->db->set('data',$this->input->post('data'));
			$this->db->set('topic_id',$topic_id);
			$this->db->set('add_by',$this->input->post('add_by'));
			$this->db->set('last_edit',$this->input->post('last_edit'));
			$this->db->set('status',$this->input->post('status'));
			$this->db->set('video',$this->input->post('video'));
			$this->db->insert('books');
		}
		$this->session->set_flashdata('message', 'Topic Data Added successfully');
		redirect('test_engine/add_question/'.$topic_id);
	}
	
	public function upload()
	{
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/upload');
		$this->load->view('inc/footer');
	}
	
	public function upload_image()
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'uploads/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('image')) {
			$data = array('msg' => $this->upload->display_errors());
			$this->session->set_flashdata('error', 'Error in uploading.');
			redirect('upload');
		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$upload_image = $data['upload_data']['file_name'];
			}
			$this->session->set_flashdata('message', base_url().'uploads/'.$upload_image);
			redirect('test_engine/upload');
		}
	}
	
	public function edit_word_meanings($question_id, $topic_id)
	{
		$data['topics'] = $this->db->get_where('topics', array('topic_id'=>$topic_id))->result_array();
		$data['question'] = $this->db->get_where('questions', array('question_id'=>$question_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('test_engine/edit_word_meanings', $data);
		$this->load->view('inc/footer');
	}
	
	public function book($course_subject_id)
	{
		echo '<html>';
		echo '<head>
		<title>Print</title>
		</head>
		<body>';
		$subjects = $this->db->get_where('course_subjects',array('course_subject_id'=>$course_subject_id))->result_array();
		$topics = $this->db->get_where('topics', array('course_subject_id'=>$course_subject_id))->result_array();
		
		echo '<p style="color:#F00;">Note : All update questions audios &amp; videos are available on college student portal.</p>';
		
		echo '<h1 style="text-align:center;">'.$subjects[0]['subject_name'].'</h1>';
		
		foreach($topics as $topic)
		{
			$mcqs = $this->db->get_where('questions', array('topic_id'=>$topic['topic_id'], 'option_1!='=>'', 'option_2!='=>'', 'option_3!='=>'', 'option_4!='=>''))->result_array();
			$short_questions = $this->db->get_where('questions', array('topic_id'=>$topic['topic_id'], 'type'=>'short-question'))->result_array();
			
			
			echo '<h1 style="text-align:center;">'.$topic['topic_name'].'</h1>';
			echo '<h3>Total MCQs : '.count($mcqs).'</h3>';
			echo '<h3>Total Short Questions : '.count($short_questions).'</h3>';
			echo '<h2>MCQs</h2>';
			$i=1;
			foreach($mcqs as $mcq)
			{
				echo '<h4>Question '.$i.'</h4>';
				echo '<p>'.$mcq['question'].'</p>';
				echo 'A .'.$mcq['option_1'].'<br />';
				echo 'B .'.$mcq['option_2'].'<br />';
				echo 'C .'.$mcq['option_3'].'<br />';
				echo 'D .'.$mcq['option_4'].'<br />';
				$i++;
			}
			echo '<br /><br />';
			echo '<h4>Answers</h4>';
			$i=1;
			foreach($mcqs as $mcq)
			{
				echo $i.'.'.$mcq['answer'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
				$i++;
			}
			echo '<br /><br />';
			echo '<h2>Short Questions<h2>';
			$i=1;
			foreach($short_questions as $short_question)
			{
				echo '<h4>Question '.$i.'</h4>';
				echo '<p>'.$short_question['question'].'</p>';
				echo '<h4>Answer</h4>';
				echo '<p>'.$short_question['explanation'].'</p>';
				$i++;
			}
			
		}
		echo '</body></html>';
	}
	
	public function import($topic_id)
	{
		$file_name = $_FILES['csv']['tmp_name'];
		header("Content-Disposition: attachment; filename=\"$file_name\"");
		header("Cache-Control: cache, must-revalidate");
		header("Pragma: public");
		header('Content-Type: text/xml,  charset=UTF-8; encoding=UTF-8');
		$type = $this->input->post('type');
		
		if($type=='mcqs')
		{
			$file = fopen($_FILES['csv']['tmp_name'],"r");
			$row=1;
			while(! feof($file))
			{
				$index=fgetcsv($file);
				if($row!=1)
				{
					if($index[0]!='')
					{	
						$question 	= $index[1];
						$option_1 	= $index[2];
						$option_2 	= $index[3];
						$option_3 	= $index[4];
						$option_4 	= $index[5];
						$answer 	= $index[6];
						$mcq_type 	= $index[7];
						//CHECK MCQ TYPE
						if($mcq_type==1)
						{
							$type = 'radio';
						}
						else
						{
							$type = 'multiple';
						}
						//CHECK DIFFICULTY LEVEL
						$difficulty_level = $index[8];
						if($difficulty_level==1)
						{
							$difficulty = 'easy';
						}
						elseif($difficulty_level==2)
						{
							$difficulty = 'medium';
						}
						elseif($difficulty_level==3)
						{
							$difficulty = 'hard';
						}
						else
						{
							$difficulty = 'easy';
						}
						
						$this->db->set('type',$type);
						$this->db->set('question',$question);
						$this->db->set('option_1',$option_1);
						$this->db->set('option_2',$option_2);
						$this->db->set('option_3',$option_3);
						$this->db->set('option_4',$option_4);
						$this->db->set('answer',$answer);
						$this->db->set('topic_id',$topic_id);
						$this->db->set('difficulty',$difficulty);
						$this->db->set('status',0);
						$this->db->set('add_by',$this->session->userdata('name'));
						$this->db->set('last_edit',$this->session->userdata('name'));
						$this->db->insert('questions');
					}
				}
				$row++;
			}
			
			fclose($file);
		}
		elseif($type=='short-question' || $type=='long-question')
		{
			$file = fopen($_FILES['csv']['tmp_name'],"r");
			$row=1;
			while(! feof($file))
			{
				$index=fgetcsv($file);
				if($row!=1)
				{
					if($index[0]!='')
					{	
						$question 	= $index[1];
						$answer 	= $index[2];
						$type 		= $type;
						//CHECK DIFFICULTY LEVEL
						$difficulty_level = $index[3];
						if($difficulty_level==1)
						{
							$difficulty = 'easy';
						}
						elseif($difficulty_level==2)
						{
							$difficulty = 'medium';
						}
						elseif($difficulty_level==3)
						{
							$difficulty = 'hard';
						}
						else
						{
							$difficulty = 'easy';
						}
						
						$this->db->set('type',$type);
						$this->db->set('question',$question);
						$this->db->set('explanation',$answer);
						$this->db->set('topic_id',$topic_id);
						$this->db->set('difficulty',$difficulty);
						$this->db->set('status',0);
						$this->db->set('add_by',$this->session->userdata('name'));
						$this->db->set('last_edit',$this->session->userdata('name'));
						$this->db->insert('questions');
					}
				}
				$row++;
			}
			
			fclose($file);
		}
		
		elseif($type=='word-meaning')
		{
			$file = fopen($_FILES['csv']['tmp_name'],"r");
			$row=1;
			while(! feof($file))
			{
				$index=fgetcsv($file);
				if($row!=1)
				{
					if($index[0]!='')
					{	
						$word 	= $index[1];
						$meaning_english 	= $index[2];
						$meaning_urdu = $index[3];
						$type 		= $type;
						
						$this->db->set('type',$type);
						$this->db->set('word',$word);
						$this->db->set('meaning_english',$meaning_english);
						$this->db->set('meaning_urdu',utf8_encode($meaning_urdu));
						$this->db->set('topic_id',$topic_id);
						$this->db->set('status',0);
						$this->db->set('add_by',$this->session->userdata('name'));
						$this->db->set('last_edit',$this->session->userdata('name'));
						$this->db->insert('questions');
					}
				}
				$row++;
			}
			
			fclose($file);
		}
		$this->session->set_userdata('message','Questions Uploaded Successfully.');
		redirect('test_engine/add_question/'.$topic_id);
	}

	public function recheck_question($topic_id)
    {
        $this->db->set('status',0);
        $this->db->where('topic_id',$topic_id);
        $this->db->update('questions');

        $this->session->set_flashdata('message','Topic questions send for re-check');
        redirect('test_engine/topics');
    }
	
	public function getSubjects()
	{
		$access = checkUserAccess();
		$subject_ids = @explode(',',$access[0]['test_engine_subject_ids']);
		
		$course_id = $this->input->post('course_id');
		$class = $this->input->post('period');
		
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->where(array('course_id'=>$course_id,'status'=>1));
		$this->db->where("(subject_year='$class' OR subject_semester='$class')", NULL, FALSE);
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('course_subject_id', $subject_ids);
		}
		$subjects = $this->db->get()->result_array();
		$html='';
		$html.='<option value="">Select Subject</option>';
		foreach($subjects as $subject)
		{
			$html.='<option value="'.$subject['course_subject_id'].'">'.$subject['subject_name'].'</option>';
		}
		echo $html;
		exit();
	}
	
	public function insert_videos($topic_id)
    {
        $data = $this->input->post();
        $counter = (count($data)-2)/2;
        for($i=1; $i<=$counter; $i++)
        {
            $this->db->set('title',$this->input->post('video_'.$i));
            $this->db->set('file',$this->input->post('video_link_'.$i));
            $this->db->set('topic_id',$topic_id);
            $this->db->set('created_by',$this->input->post('add_by'));
            $this->db->insert('question_videos');
        }
        $this->session->set_flashdata('message', 'Videos Added successfully');
        redirect('test_engine/add_question/'.$topic_id);
    }

    public function delete_video($question_id,$topic_id)
    {
        $this->db->where('id', $question_id);
        $this->db->delete('question_videos');
        $this->session->set_flashdata('message', 'Question deleted successfully');
        redirect('test_engine/add_question/'.$topic_id);
    }

    public function edit_video($question_id)
    {

        $data['question'] = $this->db->get_where('question_videos', array('id'=>$question_id))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('test_engine/edit_video', $data);
        $this->load->view('inc/footer');
    }

    public function update_video($question_id,$topic_id)
    {
        $word 				= $this->input->post('title');
        $meaning_english 	= @$this->input->post('file');

        $this->db->set(array(
            'title'			=>$word,
            'file'	        =>$meaning_english
        ));
        $this->db->where('id', $question_id);
        $this->db->update('question_videos');
        $this->session->set_flashdata('message', 'Word Meaning updated successfully');
        redirect('test_engine/add_question/'.$topic_id);
    }
}
