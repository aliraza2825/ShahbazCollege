<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Uploads extends CI_Controller {

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
	}
	public function inventory_images()
	{
		$this->db->limit(1);
		$products = $this->db->get_where('products',array('purchase_slip!='=>'','upload_image'=>0))->result_array();
		foreach($products as $product)
		{
			$image_type = explode('.',$product['purchase_slip']);
			$extension = end($image_type);
			
			if($extension=='jpg' || $extension=='jpeg' || $extension=='png' || $extension=='PNG' || $extension=='JPG' || $extension=='JPEG')
			{
				$API_KEY = '24dc95aabfb5fa9c0e53d8fcaec95e25';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key='.$API_KEY);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				//curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
				//$extension = pathinfo($image['name'],PATHINFO_EXTENSION);
				//$file_name = ($name)? $name.'.'.$extension : $image['name'] ;
				$data = array('image' => base64_encode(file_get_contents(base_url().'inventory_images/'.$product['purchase_slip'])));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$result = curl_exec($ch);
				echo base_url().'inventory_images/'.$product['purchase_slip'];
				
				$coming_data = json_decode($result);
				
				if($coming_data->success==1)
				{
					//echo $coming_data->data->image->url;
					$this->db->set('online_purchase_slip',$coming_data->data->image->url);
					$this->db->set('upload_image',1);
					$this->db->where('product_id',$product['product_id']);
					$this->db->update('products');
					//echo FCPATH;
					unlink(FCPATH.'inventory_images/'.$product['purchase_slip']);
				}
				
				if (curl_errno($ch)) {
					return 'Error:' . curl_error($ch);
				}else{
					return json_decode($result, true);
				}
				curl_close($ch);
			}
			else
			{
				$this->db->set('upload_image',1);
				$this->db->where('product_id',$product['product_id']);
				$this->db->update('products');
			}
		}
	}
	
	public function challan_images()
	{
		$this->db->limit(1);
		$payments = $this->db->get_where('payments',array('scan_challan!='=>'','upload_image'=>0))->result_array();
		foreach($payments as $payment)
		{
			$image_type = explode('.',$payment['scan_challan']);
			$extension = end($image_type);
			
			if($extension=='jpg' || $extension=='jpeg' || $extension=='png' || $extension=='PNG' || $extension=='JPG' || $extension=='JPEG')
			{
				$API_KEY = '24dc95aabfb5fa9c0e53d8fcaec95e25';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key='.$API_KEY);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				//curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
				//$extension = pathinfo($image['name'],PATHINFO_EXTENSION);
				//$file_name = ($name)? $name.'.'.$extension : $image['name'] ;
				$data = array('image' => base64_encode(file_get_contents(base_url().'uploads/'.$payment['scan_challan'])));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$result = curl_exec($ch);
				echo base_url().'uploads/'.$payment['scan_challan'];
				
				$coming_data = json_decode($result);
				
				if($coming_data->success==1)
				{
					//echo $coming_data->data->image->url;
					$this->db->set('online_scan_challan',$coming_data->data->image->url);
					$this->db->set('upload_image',1);
					$this->db->where('id',$payment['id']);
					$this->db->update('payments');
					//echo FCPATH;
					unlink(FCPATH.'uploads/'.$payment['scan_challan']);
				}
				if (curl_errno($ch)) {
					return 'Error:' . curl_error($ch);
				}else{
					return json_decode($result, true);
				}
				curl_close($ch);
			}
			else
			{
				//$this->db->set('online_scan_challan',$coming_data->data->image->url);
				$this->db->set('upload_image',1);
				$this->db->where('id',$payment['id']);
				$this->db->update('payments');
			}
		}
	}
	
	public function expense_images()
	{
		$this->db->limit(1);
		$expenses = $this->db->get_where('expenses',array('image!='=>'','upload_image'=>0))->result_array();
		foreach($expenses as $expense)
		{
			$image_type = explode('.',$expense['image']);
			$extension = end($image_type);
			
			if($extension=='jpg' || $extension=='jpeg' || $extension=='png' || $extension=='PNG' || $extension=='JPG' || $extension=='JPEG')
			{
				$API_KEY = '24dc95aabfb5fa9c0e53d8fcaec95e25';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key='.$API_KEY);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				//curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
				//$extension = pathinfo($image['name'],PATHINFO_EXTENSION);
				//$file_name = ($name)? $name.'.'.$extension : $image['name'] ;
				$data = array('image' => base64_encode(file_get_contents(base_url().'uploads/'.$expense['image'])));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$result = curl_exec($ch);
				echo base_url().'uploads/'.$expense['image'];
				
				$coming_data = json_decode($result);
				
				if($coming_data->success==1)
				{
					//echo $coming_data->data->image->url;
					$this->db->set('online_image',$coming_data->data->image->url);
					$this->db->set('upload_image',1);
					$this->db->where('expense_id',$expense['expense_id']);
					$this->db->update('expenses');
					//echo FCPATH;
					unlink(FCPATH.'uploads/'.$expense['image']);
				}
				if (curl_errno($ch)) {
					return 'Error:' . curl_error($ch);
				}else{
					return json_decode($result, true);
				}
				curl_close($ch);
			}
			else
			{
				//$this->db->set('online_scan_challan',$coming_data->data->image->url);
				$this->db->set('upload_image',1);
				$this->db->where('expense_id',$expense['expense_id']);
				$this->db->update('expenses');
			}
		}
	}
	
	public function student_documents_images()
	{
		$this->db->limit(1);
		$student_documents = $this->db->get_where('student_documents',array('image!='=>'','upload_image'=>0))->result_array();
		foreach($student_documents as $student_document)
		{
			$image_type = explode('.',$student_document['image']);
			$extension = end($image_type);
			
			if($extension=='jpg' || $extension=='jpeg' || $extension=='png' || $extension=='PNG' || $extension=='JPG' || $extension=='JPEG')
			{
				$API_KEY = '24dc95aabfb5fa9c0e53d8fcaec95e25';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key='.$API_KEY);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				//curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
				//$extension = pathinfo($image['name'],PATHINFO_EXTENSION);
				//$file_name = ($name)? $name.'.'.$extension : $image['name'] ;
				$data = array('image' => base64_encode(file_get_contents(base_url().'uploads/'.$student_document['image'])));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$result = curl_exec($ch);
				echo base_url().'uploads/'.$student_document['image'];
				
				$coming_data = json_decode($result);
				
				if($coming_data->success==1)
				{
					//echo $coming_data->data->image->url;
					$this->db->set('online_image',$coming_data->data->image->url);
					$this->db->set('upload_image',1);
					$this->db->where('id',$student_document['id']);
					$this->db->update('student_documents');
					//echo FCPATH;
					unlink(FCPATH.'uploads/'.$student_document['image']);
				}
				if (curl_errno($ch)) {
					return 'Error:' . curl_error($ch);
				}else{
					return json_decode($result, true);
				}
				curl_close($ch);
			}
			else
			{
				//$this->db->set('online_scan_challan',$coming_data->data->image->url);
				$this->db->set('upload_image',1);
				$this->db->where('id',$student_document['id']);
				$this->db->update('student_documents');
			}
		}
	}
}
