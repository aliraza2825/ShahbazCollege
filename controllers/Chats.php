<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chats extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}
	public function pending()
	{
		$this->db->select('chats.*,students.first_name,students.last_name,students.roll_no');
		$this->db->from('chats');
		$this->db->join('students','students.student_id=chats.student_id','left');
		$this->db->where('chats.chat_status',0);
		$data['chats']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('chats/pending',$data);
		$this->load->view('inc/footer');
	}

	public function completed()
	{
		$this->db->select('chats.*,students.first_name,students.last_name,students.roll_no');
		$this->db->from('chats');
		$this->db->join('students','students.student_id=chats.student_id','left');
		$this->db->where('chats.chat_status',1);
		$data['chats']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('chats/completed',$data);
		$this->load->view('inc/footer');
	}

	public function view_chat($chat_id)
	{
		$this->db->select('chat_history.*,chats.chat_status,chats.question_id');
		$this->db->from('chat_history');
		$this->db->join('chats','chats.chat_id=chat_history.chat_id','inner');
		$this->db->where('chats.chat_id',$chat_id);
		$this->db->order_by('created_at','ASC');
		$data['chats'] = $this->db->get()->result_array();

		//GET STUDENT PICTURE
		if($data['chats'][0]['student_id']!=0)
		{
			$photo = $this->db->get_where('student_documents',array('student_id'=>$data['chats'][0]['student_id'],'type'=>'Photo'))->result_array();
			if(count($photo)>0)
			{
				if($photo[0]['online_image']!='')
				{
					$data['student_photo'] = $photo[0]['online_image'];
				}
				else
				{
					$data['student_photo'] = base_url().'uploads/'.$photo[0]['image'];
				}
			}
			//GET STUDENT INFORMATION
			$data['student_details'] = $this->db->get_where('students',array('student_id'=>$data['chats'][0]['student_id']))->result_array();
		}
		else
		{
			$data['student_photo'] = 'https://i.pinimg.com/474x/0c/3b/3a/0c3b3adb1a7530892e55ef36d3be6cb8.jpg';
			$data['student_details'] = array();
		}

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('chats/view_chat',$data);
		$this->load->view('inc/footer');
	}

	public function solved($chat_id)
	{
		$this->db->set('chat_status',1);
		$this->db->where('chat_id',$chat_id);
		$this->db->update('chats');

		$this->session->set_flashdata('message','Chat has been resolved.');
		redirect('chats/view_chat/'.$chat_id);
	}

	public function replyChat($chat_id)
	{
		$this->db->set('user_id',$this->session->userdata('user_id'));
		$this->db->set('message',$this->input->post('message'));
		$this->db->set('chat_id',$chat_id);
		$this->db->insert('chat_history');

		$this->session->set_flashdata('message','Reply posted successfully.');
		redirect('chats/view_chat/'.$chat_id);
	}
}
