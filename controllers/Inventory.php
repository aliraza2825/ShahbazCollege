<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Inventory extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('clas');
	}

	public function add_vendor()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['product_names'] = $this->db->get('product_names')->result_array();


		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_vendor', $data);
		$this->load->view('inc/footer');
	}

	public function all_vendors()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['product_names'] = $this->db->get('product_names')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_vendors', $data);
		$this->load->view('inc/footer');
	}

	public function edit_vendor($vendor_id)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['vendor'] = $this->db->get_where('vendors',array('id'=>$vendor_id))->result_array();
		$data['product_names'] = $this->db->get('product_names')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_vendor', $data);
		$this->load->view('inc/footer');
	}

	public function insert_vendor()
	{
		$fields = $this->input->post();

		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';
		
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
			$fields['image'] = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$fields['image'] = $data['upload_data']['file_name'];
			}
		}

		foreach(@$fields as $k=>$value){
			if($k!='product_name_ids')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		$product_name_ids = $this->input->post('product_name_ids');
		$product_name_ids = implode(',',$product_name_ids);

		$this->db->set('product_name_ids',$product_name_ids);

		$this->db->set('created_by',$this->session->userdata('user_id'));
		$this->db->set('created_at',date('Y-m-d H:i:s'));

		$this->db->insert('vendors');

		$this->session->set_flashdata('message','Vendor Added Successfully!');
		redirect('inventory/add_vendor');
	}

	public function update_vendor($vendor_id)
	{
		$fields = $this->input->post();

		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';
		
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
			$fields['image'] = $fields['old_image'];

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$fields['image'] = $data['upload_data']['file_name'];
			}
		}

		foreach(@$fields as $k=>$value){
			if($k!='old_image' && $k!='product_name_ids')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		$product_name_ids = $this->input->post('product_name_ids');
		$product_name_ids = implode(',',$product_name_ids);

		$this->db->set('product_name_ids',$product_name_ids);

		$this->db->where('id',$vendor_id);
		$this->db->update('vendors');

		$this->session->set_flashdata('message','Vendor Updated Successfully!');
		redirect('inventory/edit_vendor/'.$vendor_id);
	}

	public function delete_vendor($vendor_id)
	{
		$this->db->where('id',$vendor_id);
		$this->db->delete('vendors');

		$this->session->set_flashdata('message','Vendor Deleted Successfully!');
		redirect('inventory/all_vendors');
	}

	public function add_room()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_room', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_room($room_id)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['room'] = $this->db->get_where('rooms',array('room_id'=>$room_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_room', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_room()
	{
		$campus_id = $this->input->post('campus_id');
		$room_name = $this->input->post('room_name');
		$room_no = $this->input->post('room_no');
		$type = $this->input->post('type');
		
		$counter = count($room_name);
		
		for($i=0;$i<$counter;$i++)
		{
			$this->db->set('campus_id',$campus_id);
			$this->db->set('room_name',$room_name[$i]);
			$this->db->set('room_no',$room_no[$i]);
			$this->db->set('type',$type[$i]);
			$this->db->insert('rooms');
		}
		$this->session->set_flashdata('message','Rooms Add Successfully.');
		redirect('inventory/add_room');
	}
	
	public function update_room($room_id)
	{
		$campus_id = $this->input->post('campus_id');
		$room_name = $this->input->post('room_name');
		$room_no = $this->input->post('room_no');
		$type = $this->input->post('type');
		
	
		
		$this->db->set('campus_id',$campus_id);
		$this->db->set('room_name',$room_name);
		$this->db->set('room_no',$room_no);
		$this->db->set('type',$type);
		$this->db->where('room_id',$room_id);
		
		$this->db->update('rooms');
		
		$this->session->set_flashdata('message','Rooms Updated Successfully.');
		redirect('inventory/edit_room/'.$room_id);
	}
	
	public function all_rooms()
	{	
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);

		$this->db->select('*');
		$this->db->from('rooms');
		$this->db->join('campuses','campuses.campus_id=rooms.campus_id','INNER');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('rooms.campus_id', $campus_ids);
		}
		$data['rooms'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_rooms', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete_room($room_id)
	{
		$this->db->where('room_id',$room_id);
		$this->db->delete('rooms');
		$this->session->set_flashdata('message','Rooms Deleted Successfully.');
		redirect('inventory/all_rooms');
	}
	
	public function add_subroom()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_subroom', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_subroom()
	{
		$room_id = $this->input->post('room_id');
		$subroom_name = $this->input->post('subroom_name');
		
		$this->db->set('room_id',$room_id);
		$this->db->set('subroom_name',$subroom_name);
		$this->db->insert('subrooms');
		
		$this->session->set_flashdata('message','Sub-Rooms Add Successfully.');
		redirect('inventory/add_subroom');
	}
	
	public function all_subrooms()
	{	
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);

		$this->db->select('*');
		$this->db->from('subrooms');
		$this->db->join('rooms','rooms.room_id=subrooms.room_id','INNER');
		$this->db->join('campuses','campuses.campus_id=rooms.campus_id','INNER');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$data['subrooms'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_subrooms', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_subroom($subroom_id)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$this->db->select('*');
		$this->db->from('subrooms');
		$this->db->join('rooms','rooms.room_id=subrooms.room_id','INNER');
		$this->db->join('campuses','campuses.campus_id=rooms.campus_id','INNER');
		$this->db->where('subrooms.subroom_id',$subroom_id);
		$data['subroom'] = $this->db->get()->result_array();
		$data['rooms'] = $this->db->get_where('rooms',array('campus_id'=>$data['subroom'][0]['campus_id']))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_subroom', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_subroom($subroom_id)
	{
		$room_id = $this->input->post('room_id');
		$subroom_name = $this->input->post('subroom_name');
		
		$this->db->set('room_id',$room_id);
		$this->db->set('subroom_name',$subroom_name);
		$this->db->where('subroom_id',$subroom_id);
		$this->db->update('subrooms');
		
		$this->session->set_flashdata('message','Sub-Rooms Updated Successfully.');
		redirect('inventory/edit_subroom/'.$subroom_id);
	}
	
	public function delete_subroom($subroom_id)
	{
		$this->db->where('subroom_id',$subroom_id);
		$this->db->delete('subrooms');
		$this->session->set_flashdata('message','Sub-Rooms Deleted Successfully.');
		redirect('inventory/all_subrooms');
	}
	
	public function add_product_name()
	{
		$data['product_names'] = $this->db->select('product_names.*,expense_category.name')
            ->join('expense_category','expense_category.expense_category_id = product_names.expense_category_id','left')
            ->get_where('product_names','product_names.sub_of is NULL')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_product_name', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_product_name()
	{
		$product_name = $this->input->post('product_name');
		$type = $this->input->post('type');
		$head_category = $this->input->post('head_category');

		
		$this->db->set('product_name',$product_name);
		$this->db->set('type',$type);
        if($head_category != "" && $head_category != null)
        {
            $this->db->set('sub_of', $head_category);
            $this->db->insert('product_names');

            $this->db->set('has_sub',"1");
            $this->db->where('product_name_id',$head_category);
            $this->db->update('product_names');
        }
        else
        {
            $this->db->insert('product_names');
        }
		
		$this->session->set_flashdata('message','Product Name Inserted Successfully.');
		redirect('inventory/add_product_name');
	}
	
	public function update_product_name($product_name_id)
	{
		$product_name = $this->input->post('product_name');
		$type = $this->input->post('type');
        $exp_cat = $this->input->post('head_product_id');
        if (count($exp_cat) > 0) {
            if (count($exp_cat) == 1 && $exp_cat[count($exp_cat) - 1] == "")
            {

            }else {
                if ($exp_cat[count($exp_cat) - 1] == "")
                    $sub_of = $exp_cat[count($exp_cat) - 2];
                else {
                    $sub_of = $exp_cat[count($exp_cat) - 1];
                }
                $already_data = $this->db->select('count(*) as total')->get_where("products", "product_name_id = $sub_of")->row();
                if ($already_data->total > 0) {
                    $this->session->set_flashdata('error', 'This Head already has Items!');
                    redirect('inventory/edit_product_name/' . $product_name_id);
                }
                $this->db->set('has_sub', 1);
                $this->db->where('product_name_id', $sub_of);
                $this->db->update('product_names');

                $this->db->set('sub_of', $sub_of);
            }
        }
		
		$this->db->set('product_name',$product_name);
		$this->db->set('type',$type);
		$this->db->where('product_name_id',$product_name_id);
		$this->db->update('product_names');
		
		$this->session->set_flashdata('message','Product Name updated Successfully.');
		redirect('inventory/add_product_name');
	}
	
	public function delete_product_name($product_name_id)
	{
		$this->db->where('product_name_id',$product_name_id);
		$this->db->delete('product_names');
		
		$this->session->set_flashdata('message','Product Name deleted Successfully.');
		redirect('inventory/add_product_name');
	}
	
	public function edit_product_name($product_name_id)
	{
		$data['product_name'] = $this->db->select('expense_category.name,product_names.*')->
                                join('expense_category','expense_category.expense_category_id = product_names.expense_category_id','left')->
                                get_where('product_names',array('product_name_id'=>$product_name_id))->result_array();
        $data['product_names'] = $this->db->get_where('product_names', "sub_of is NULL")->result_array();

        $data['exp_category'] = $this->db->get('expense_category')->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_product_name', $data);
		$this->load->view('inc/footer');
	}
	
	public function add_document_name()
	{
		$data['documents_names'] = $this->db->get('document_names')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_document_name', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_document_name()
	{
		$document_name = $this->input->post('document_name');
		$this->db->set('document_name',$document_name);
		$this->db->insert('document_names');
		
		$this->session->set_flashdata('message','Document Name Inserted Successfully.');
		redirect('inventory/add_document_name');
	}
	
	public function edit_document_name($document_name_id)
	{
		$data['document_name'] = $this->db->get_where('document_names',array('document_name_id'=>$document_name_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_document_name', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_document_name($document_name_id)
	{
		$document_name = $this->input->post('document_name');
		$this->db->set('document_name',$document_name);
		$this->db->where('document_name_id',$document_name_id);
		$this->db->update('document_names');
		
		$this->session->set_flashdata('message','Document Name updated Successfully.');
		redirect('inventory/add_document_name');
	}
	
	public function delete_document_name($document_name_id)
	{
		$this->db->where('document_name_id',$document_name_id);
		$this->db->delete('document_names');
		
		$this->session->set_flashdata('message','Document Name deleted Successfully.');
		redirect('inventory/add_document_name');
	}
	
	public function add_document()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['users'] = $this->db->get_where('users',array('status'=>1))->result_array();
		$data['document_names'] = $this->db->get('document_names')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_document', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_document()
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('picture')) {
			$data = array('msg' => $this->upload->display_errors());
			$picture = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$picture = $data['upload_data']['file_name'];
			}
		}
		
		//INSERT PRODUCT IN DATABASE
		$campus_id = $this->input->post('campus_id');
		$room_id = $this->input->post('room_id');
		$subroom_id = $this->input->post('subroom_id');
		$document_name_id = $this->input->post('document_name_id');
		$picture = $picture;
		$document_quantity = $this->input->post('document_quantity');
		$remarks = $this->input->post('remarks');
		$user_id = $this->input->post('user_id');
		$reponsilble_user_id = $this->input->post('reponsilble_user_id');
		$add_by = $this->session->userdata('name');
		$last_edit = '';
		$clear_by = '';
		
		$this->db->set('campus_id',$campus_id);
		$this->db->set('room_id',$room_id);
		$this->db->set('subroom_id',$subroom_id);
		$this->db->set('document_name_id',$document_name_id);
		$this->db->set('picture',$picture);
		$this->db->set('document_quantity',$document_quantity);
		$this->db->set('remarks',$remarks);
		$this->db->set('user_id',$user_id);
		$this->db->set('reponsilble_user_id',$reponsilble_user_id);
		$this->db->set('add_by',$add_by);
		$this->db->set('last_edit',$last_edit);
		$this->db->set('clear_by',$clear_by);
		$this->db->insert('documents');
		
		$this->session->set_flashdata('message','Your Document added successfully.');
		redirect('inventory/add_document');
	}
	
	public function add_product()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['users'] = $this->db->get_where('users',array('status'=>1))->result_array();
		$data['product_names'] = $this->db->get('product_names')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_product', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_product($product_id)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();


		$data['products'] = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();
		$data['product_names'] = $this->db->get('product_names')->result_array();
		//$data['campuses'] = $this->clas->getCampuses();
		$data['rooms'] = $this->db->get_where('rooms',array('campus_id'=>$data['products'][0]['campus_id']))->result_array();
		$data['subrooms'] = $this->db->get_where('subrooms',array('room_id'=>$data['products'][0]['room_id']))->result_array();
		$data['users'] = $this->db->get_where('users',array('status'=>1))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_product', $data);
		$this->load->view('inc/footer');
	}

	public function edit_bulk_products($product_id)
	{
		$data['products'] = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();
		$data['product_names'] = $this->db->get('product_names')->result_array();
		$data['campuses'] = $this->clas->getCampuses();
		$data['rooms'] = $this->db->get_where('rooms',array('campus_id'=>$data['products'][0]['campus_id']))->result_array();
		$data['subrooms'] = $this->db->get_where('subrooms',array('room_id'=>$data['products'][0]['room_id']))->result_array();
		$data['users'] = $this->db->get_where('users',array('status'=>1))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_bulk_products', $data);
		$this->load->view('inc/footer');
	}

    public function move_product($product_id)
    {
        $data['products'] = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();
        $data['product_names'] = $this->db->get('product_names')->result_array();
        $data['campuses'] = $this->clas->getCampuses();
        $data['rooms'] = $this->db->get_where('rooms',array('campus_id'=>$data['products'][0]['campus_id']))->result_array();
        $data['subrooms'] = $this->db->get_where('subrooms',array('room_id'=>$data['products'][0]['room_id']))->result_array();
        $data['users'] = $this->db->get_where('users',array('status'=>1))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('inventory/move_product', $data);
        $this->load->view('inc/footer');
    }

	public function getRooms()
	{
		$campus_id = $this->input->post('campus_id');
		$this->session->set_userdata('product_campus_id',$campus_id);
		$rooms = $this->db->get_where('rooms',array('campus_id'=>$campus_id))->result_array();
		
		$html='';
		$html.='<option value="">Select Room</option>';
		$html.='<option value="0">Personal Use</option>';
		foreach($rooms as $room)
		{
		    if($room['room_id']==$this->session->userdata('product_room_id'))
		    {
		        $html.='<option value="'.$room['room_id'].'" selected>'.$room['room_name'].'</option>';
		    }
		    else
		    {
		        $html.='<option value="'.$room['room_id'].'">'.$room['room_name'].'</option>';
		    }
		}
		echo $html;
	}
	
	public function getSubrooms()
	{
		$room_id = $this->input->post('room_id');
		$this->session->set_userdata('product_room_id',$room_id);
		$subrooms = $this->db->get_where('subrooms',array('room_id'=>$room_id))->result_array();
		
		$html='';
		$html.='<option value="">Select Subroom</option>';
		foreach($subrooms as $subroom)
		{
		    if($subroom['subroom_id']==$this->session->userdata('product_subroom_id'))
		    {
		        $html.='<option value="'.$subroom['subroom_id'].'" selected>'.$subroom['subroom_name'].'</option>';
		    }
		    else
		    {
		        $html.='<option value="'.$subroom['subroom_id'].'">'.$subroom['subroom_name'].'</option>';
		    }
		}
		echo $html;
	}
	
	public function getSubroomProducts()
	{
		$room_id = $this->input->post('room_id');
		$subroom_id = $this->input->post('subroom_id');
		
		$this->session->set_userdata('product_subroom_id',$subroom_id);
		
		//GET PRODUCTS IN SELECTED ROOM AND SUBROOM
		$this->db->select('*');
		$this->db->from('products');
		$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
		$this->db->where(array('products.room_id'=>$room_id,'products.subroom_id'=>$subroom_id,'products.saleable'=>1));
		$this->db->group_by('products.product_name_id');
		$products = $this->db->get()->result_array();
		
		$html='';
		$html.='<option value="">Select Product</option>';
		foreach($products as $product)
		{
			$html.='<option value="'.$product['product_name_id'].'">'.$product['product_name'].'</option>';
		}
		echo $html;
	}
	
	public function insert_product()
	{
		//CHECK PRODUCT QR CODE
		$qr_code = 'inv_qr-'.$this->input->post('qr_code');
		$product_name_id = $this->input->post('product_name_id');

		$this->db->group_by('product_name_id');
		$products = $this->db->get_where('products',array('qr_code'=>$qr_code))->result_array();
		foreach($products as $product)
		{
			if($product['product_name_id']!=$product_name_id)
			{
				$product_name = $this->db->get_where('product_names',array('product_name_id'=>$product['product_name_id']))->row()->product_name;
				$campus_name = $this->db->get_where('campuses',array('campus_id'=>$product['campus_id']))->row()->campus_name;
				$room_name = $this->db->get_where('rooms',array('room_id'=>$product['room_id']))->row()->room_name;
				$subroom_name = $this->db->get_where('subrooms',array('subroom_id'=>$product['subroom_id']))->row()->subroom_name;
				$this->session->set_flashdata('error','This QR Code is invalid. Kindly Choose Correct QR Code. Entered QR Code is attach to '.$campus_name.'/'.$room_name.'/'.$subroom_name.'/'.$product_name);
				redirect('inventory/add_product');
			}
		}
		
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('purchase_slip')) {
			$data = array('msg' => $this->upload->display_errors());
			$purchase_slip = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$purchase_slip = $data['upload_data']['file_name'];
			}
		}

		if (!$this->upload->do_upload('product_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$product_image = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$product_image = $data['upload_data']['file_name'];
			}
		}

		//GET VARIABLES
		$campus_id = $this->input->post('campus_id');
		$room_id = $this->input->post('room_id');
		$subroom_id = $this->input->post('subroom_id');
		$product_name_id = $this->input->post('product_name_id');
		$qr_code = 'inv_qr-'.$this->input->post('qr_code');
		$purchase_slip = $purchase_slip;
		$product_quantity = $this->input->post('product_quantity');
		$product_guarantee = $this->input->post('product_guarantee');
		$estimated_price = $this->input->post('estimated_price');
		$consumeable = $this->input->post('consumeable');
		$saleable = $this->input->post('saleable');
		$expire = $this->input->post('expire');
		

		if($product_guarantee==0)
		{
			$product_guarantee_start_date = '0000-00-00';
			$product_guarantee_end_date = '0000-00-00';
		}
		else
		{
			$product_guarantee_start_date = $this->input->post('product_guarantee_start_date');
			$product_guarantee_end_date = $this->input->post('product_guarantee_end_date');
		}
		if($saleable==1)
		{
			$sale_amount = $this->input->post('sale_amount');
			$returnable = $this->input->post('returnable');
		}
		else
		{
			$sale_amount = '';
			$returnable = 0;
		}
		if($expire==1)
		{
			$expire_date = $this->input->post('expire_date');
		}
		else
		{
			$expire_date = NULL;
		}
		$remarks = $this->input->post('remarks');
		$add_by = $this->session->userdata('name');
		$last_edit = $this->session->userdata('name');
		$clear_by = '';
		
		//INSERT PRODUCT IN DATABASE

		for($i=1;$i<=$product_quantity;$i++)
		{
			$this->db->set('campus_id',$campus_id);
			$this->db->set('room_id',$room_id);
			$this->db->set('subroom_id',$subroom_id);
			$this->db->set('product_name_id',$product_name_id);
			$this->db->set('qr_code',$qr_code);
			$this->db->set('estimated_price',$estimated_price);
			$this->db->set('product_image',$product_image);
			$this->db->set('purchase_slip',$purchase_slip);
			$this->db->set('product_quantity',1);
			$this->db->set('remaining_quantity',1);
			$this->db->set('product_guarantee',$product_guarantee);
			$this->db->set('product_guarantee_start_date',$product_guarantee_start_date);
			$this->db->set('product_guarantee_end_date',$product_guarantee_end_date);
			$this->db->set('remarks',$remarks);
			$this->db->set('consumeable',$consumeable);
			$this->db->set('saleable',$saleable);
			$this->db->set('sale_amount',$sale_amount);
			$this->db->set('returnable',$returnable);
			$this->db->set('expire',$expire);
			$this->db->set('expire_date',$expire_date);
			$this->db->set('status',1);
			$this->db->set('add_by',$add_by);
			$this->db->set('last_edit',$last_edit);
			$this->db->set('clear_by',$clear_by);
			$this->db->insert('products');
		}
		
		$this->session->set_flashdata('message','Your Product added successfully.');
		redirect('inventory/add_product');
	}
	
	public function update_product($product_id)
	{
		//GET PRODUCT DETAILS
		//$product = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();
		
		//CHECK REQUEST ALREADY SUBMITTED
		//$check = $this->db->get_where('update_product_requests',array('product_id'=>$product_id))->result_array();
		//if(count($check)>0)
		//{
		//	$this->session->set_flashdata('error','Product Request Already Submitted.');
		//	redirect('inventory/edit_product/'.$product_id);
		//}
		//else
		//{
			
		//}
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';

		// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';

		//load the upload library
		$this->load->library('upload', $config);

		$this->upload->initialize($config);

		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';

		//if not successful, set the error message
		if (!$this->upload->do_upload('purchase_slip')) {
			$data = array('msg' => $this->upload->display_errors());
			$purchase_slip = $this->input->post('old_purchase_slip');

		}
		else
		{
			//else, set the success message
			$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$purchase_slip = $data['upload_data']['file_name'];
			}
		}

		//if not successful, set the error message
		if (!$this->upload->do_upload('product_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$product_image = $this->input->post('old_product_image');

		}
		else
		{
			//else, set the success message
			$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$product_image = $data['upload_data']['file_name'];
			}
		}


		//GET VARIABLES
		$product_guarantee = $this->input->post('product_guarantee');
		$estimated_price = $this->input->post('estimated_price');
		$saleable = $this->input->post('saleable');
		$expire = $this->input->post('expire');
		$consumeable = $this->input->post('consumeable');
		

		if($product_guarantee==0)
		{
			$product_guarantee_start_date = '0000-00-00';
			$product_guarantee_end_date = '0000-00-00';
		}
		else
		{
			$product_guarantee_start_date = $this->input->post('product_guarantee_start_date');
			$product_guarantee_end_date = $this->input->post('product_guarantee_end_date');
		}
		if($saleable==1)
		{
			$sale_amount = $this->input->post('sale_amount');
			$returnable = $this->input->post('returnable');
		}
		else
		{
			$sale_amount = '';
			$returnable = 0;
		}
		if($expire==1)
		{
			$expire_date = $this->input->post('expire_date');
		}
		else
		{
			$expire_date = NULL;
		}
		$remarks = $this->input->post('remarks');
		$last_edit = $this->session->userdata('name');
		
		//UPDATE PRODUCT IN DATABASE

		$this->db->set('estimated_price',$estimated_price);
		$this->db->set('product_image',$product_image);
		$this->db->set('purchase_slip',$purchase_slip);
		$this->db->set('product_guarantee',$product_guarantee);
		$this->db->set('product_guarantee_start_date',$product_guarantee_start_date);
		$this->db->set('product_guarantee_end_date',$product_guarantee_end_date);
		$this->db->set('remarks',$remarks);
		$this->db->set('consumeable',$consumeable);
		$this->db->set('saleable',$saleable);
		$this->db->set('sale_amount',$sale_amount);
		$this->db->set('returnable',$returnable);
		$this->db->set('expire',$expire);
		$this->db->set('expire_date',$expire_date);
		$this->db->set('last_edit',$last_edit);
		$this->db->where('product_id',$product_id);
		$this->db->update('products');
		
		$this->session->set_flashdata('message','Your Product Updated Successfully.');
		redirect('inventory/edit_product/'.$product_id);
	}

	public function update_bulk_products($product_id)
	{
		//GET PRODUCT DETAILS
		$myProducts = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();
		
		//GET VARIABLES
		$product_guarantee = $this->input->post('product_guarantee');
		$estimated_price = $this->input->post('estimated_price');
		$saleable = $this->input->post('saleable');
		$expire = $this->input->post('expire');
		$consumeable = $this->input->post('consumeable');
		

		if($product_guarantee==0)
		{
			$product_guarantee_start_date = '0000-00-00';
			$product_guarantee_end_date = '0000-00-00';
		}
		else
		{
			$product_guarantee_start_date = $this->input->post('product_guarantee_start_date');
			$product_guarantee_end_date = $this->input->post('product_guarantee_end_date');
		}
		if($saleable==1)
		{
			$sale_amount = $this->input->post('sale_amount');
			$returnable = $this->input->post('returnable');
		}
		else
		{
			$sale_amount = '';
			$returnable = 0;
		}
		if($expire==1)
		{
			$expire_date = $this->input->post('expire_date');
		}
		else
		{
			$expire_date = NULL;
		}
		$remarks = $this->input->post('remarks');
		$last_edit = $this->session->userdata('name');
		
		//UPDATE PRODUCT IN DATABASE

		$this->db->set('estimated_price',$estimated_price);
		$this->db->set('product_guarantee',$product_guarantee);
		$this->db->set('product_guarantee_start_date',$product_guarantee_start_date);
		$this->db->set('product_guarantee_end_date',$product_guarantee_end_date);
		$this->db->set('remarks',$remarks);
		$this->db->set('consumeable',$consumeable);
		$this->db->set('saleable',$saleable);
		$this->db->set('sale_amount',$sale_amount);
		$this->db->set('returnable',$returnable);
		$this->db->set('expire',$expire);
		$this->db->set('expire_date',$expire_date);
		$this->db->set('last_edit',$last_edit);
		$this->db->where(array('campus_id'=>$myProducts[0]['campus_id'],'room_id'=>$myProducts[0]['room_id'],'subroom_id'=>$myProducts[0]['subroom_id'],'product_name_id'=>$myProducts[0]['product_name_id']));
		$this->db->update('products');
		
		$this->session->set_flashdata('message','Your Products Updated Successfully.');
		redirect('inventory/edit_product/'.$product_id);
	}

    public function update_move_product($product_id)
    {
        //GET PRODUCT DETAILS
        $product = $this->db->get_where('products',array('product_id' => $product_id))->result_array();

            //UPDATE PRODUCT IN DATABASE
            $campus_id = $this->input->post('campus_id');
            $room_id = $this->input->post('room_id');
            $subroom_id = $this->input->post('subroom_id');
            $product_name_id = $this->input->post('product_name_id');
            $product_quantity = $this->input->post('product_quantity');
            $product_guarantee = $this->input->post('product_guarantee');
            $purchase_slip = $this->input->post('purchase_slip');
            if($product_guarantee==0)
            {
                $product_guarantee_start_date = '0000-00-00';
                $product_guarantee_end_date = '0000-00-00';
            }
            else
            {
                $product_guarantee_start_date = $this->input->post('product_guarantee_start_date');
                $product_guarantee_end_date = $this->input->post('product_guarantee_end_date');
            }
            $remarks = $this->input->post('remarks');
            $add_by = $product[0]['add_by'];
            $last_edit = $this->session->userdata('name');
            $clear_by = $product[0]['clear_by'];
            $status = $product[0]['status'];

            $this->db->set('campus_id',$campus_id);
            $this->db->set('room_id',$room_id);
            $this->db->set('subroom_id',$subroom_id);
            $this->db->set('product_name_id',$product_name_id);
            $this->db->set('product_quantity',$product_quantity);
            $this->db->set('product_guarantee',$product_guarantee);
            $this->db->set('product_guarantee_start_date',$product_guarantee_start_date);
            $this->db->set('product_guarantee_end_date',$product_guarantee_end_date);
            $this->db->set('remarks',$remarks);
            $this->db->set('purchase_slip',$purchase_slip);
            $this->db->set('date',$product[0]['date']);
            $this->db->set('add_by',$add_by);
            $this->db->set('last_edit',$last_edit);
            $this->db->set('clear_by',$clear_by);
            $this->db->set('status',$status);
            $this->db->set('po_no',$this->input->post('po_no'));
            if ($this->input->post('product_quantity') == $this->input->post('total_quantity')) {
                $this->db->where('product_id', $product_id);
                $this->db->update('products');
            }
            else {
                $this->db->insert('products');

                $this->db->set('product_quantity',$this->input->post('total_quantity')-$this->input->post('product_quantity'));
                $this->db->where('product_id', $product_id);
                $this->db->update('products');
            }

            $this->session->set_flashdata('message','Your Product Moved Successfully.');
            redirect('inventory/all_products');

    }

	public function all_products()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();


		$data['product_names'] = $this->db->get('product_names')->result_array();
		
		if(@$this->input->post('submit')==1)
		{
			if($this->input->post('campus_id')=='')
			{
				if($this->input->post('type') == 'consumed')
				{
					$array = array(
						'products.status'=>'1',
						'products.consume'=>'1',
						'products.sold'=>0
					);
				}
				elseif($this->input->post('type') == 'sold')
				{
					$array = array(
						'products.status'=>'1',
						'products.consume'=>'0',
						'products.sold'=>1
					);
				}
				else
				{
					$array = array(
						'products.status'=>'1',
					);
				}
			}
			elseif($this->input->post('campus_id')!='' && $this->input->post('room_id')=='')
			{
				$campus_id = $this->input->post('campus_id');
				if($this->input->post('type') == 'consumed')
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.status'=>'1',
						'products.consume'=>'1',
						'products.sold'=>0
					);
				}
				elseif($this->input->post('type') == 'sold')
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.status'=>'1',
						'products.consume'=>'0',
						'products.sold'=>1
					);
				}
				else
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.status'=>'1',
					);
				}
			}
			elseif($this->input->post('room_id')!='' && $this->input->post('campus_id')!='')
			{
				$campus_id = $this->input->post('campus_id');
				$room_id = $this->input->post('room_id');
				if($this->input->post('type') == 'consumed')
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.room_id'=>$room_id,
						'products.status'=>'1',
						'products.consume'=>'1',
						'products.sold'=>0
					);
				}
				elseif($this->input->post('type') == 'sold')
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.room_id'=>$room_id,
						'products.status'=>'1',
						'products.consume'=>'0',
						'products.sold'=>1
					);
				}
				else
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.room_id'=>$room_id,
						'products.status'=>'1',
					);
				}
			}
			elseif($this->input->post('room_id')!='' && $this->input->post('subroom_id')!='')
			{
				$campus_id = $this->input->post('campus_id');
				$room_id = $this->input->post('room_id');
				$subroom_id = $this->input->post('subroom_id');
				if($this->input->post('type') == 'consumed')
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.room_id'=>$room_id,
						'products.subroom_id'=>$subroom_id,
						'products.status'=>'1',
						'products.consume'=>'1',
						'products.sold'=>0
					);
				}
				elseif($this->input->post('type') == 'sold')
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.room_id'=>$room_id,
						'products.subroom_id'=>$subroom_id,
						'products.status'=>'1',
						'products.consume'=>'0',
						'products.sold'=>1
					);
				}
				else
				{
					$array = array(
						'products.campus_id'=>$campus_id,
						'products.room_id'=>$room_id,
						'products.subroom_id'=>$subroom_id,
						'products.status'=>'1',
					);
				}
			}
			else
			{
				
			}
            if ($this->input->post('type') == 'group' || $this->input->post('type') == 'consumed' || $this->input->post('type') == 'sold')
                $this->db->select('products.*,rooms.*,subrooms.*,campuses.campus_name,product_names.*,sum(remaining_quantity) as remaining_quantity');
            else
                $this->db->select('products.*,rooms.*,subrooms.*,campuses.campus_name,product_names.*');

			$this->db->from('products');
			$this->db->join('campuses','campuses.campus_id=products.campus_id','inner');
			$this->db->join('product_names','products.product_name_id=product_names.product_name_id','left');
			$this->db->join('rooms','products.room_id=rooms.room_id','left');
			$this->db->join('subrooms','products.subroom_id=subrooms.subroom_id','left');
			$this->db->where($array);
			if ($this->input->post('type') == 'group' || $this->input->post('type') == 'consumed' || $this->input->post('type') == 'sold')
			{
				$this->db->group_by("products.product_name_id");
				$this->db->group_by("products.room_id");
				$this->db->group_by("products.subroom_id");
				$this->db->group_by("products.consume");
				$this->db->group_by("products.sold");
			}
			    
			$data['products'] = $this->db->get()->result_array();
		}
		elseif($this->input->post('productwise_submit')==1)
		{
			$campus_id = $this->input->post('campus_id');
			$product_name_id = $this->input->post('product_name_id');

			if($campus_id=='')
			{
				$array = array('products.product_name_id'=>$product_name_id,'products.status'=>'1');
			}
			else
			{
				$array = array('products.product_name_id'=>$product_name_id,'products.campus_id'=>$campus_id,'products.status'=>'1');
			}

			$this->db->select('products.*,rooms.*,subrooms.*,campuses.campus_name,product_names.*,sum(remaining_quantity) as remaining_quantity');
			$this->db->from('products');
			$this->db->join('campuses','campuses.campus_id=products.campus_id','inner');
			$this->db->join('product_names','products.product_name_id=product_names.product_name_id','left');
			$this->db->join('rooms','products.room_id=rooms.room_id','left');
			$this->db->join('subrooms','products.subroom_id=subrooms.subroom_id','left');
			$this->db->where($array);
			$this->db->group_by("products.product_name_id");
			$this->db->group_by("products.room_id");
			$this->db->group_by("products.subroom_id");
			$this->db->group_by("products.consume");
			$this->db->group_by("products.sold");
			    
			$data['products'] = $this->db->get()->result_array();
		}
		else
		{
			$data['products'] = array();
		}

		
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_products', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete_product($product_id)
	{
		$this->db->where('product_id',$product_id);
		$this->db->delete('products');
		
		$this->session->set_flashdata('message', 'Product Deleted Successfully.');
		redirect('inventory/all_products');
	}
	
	public function consume_product($product_id)
	{
		$this->db->set('consume',1);
		$this->db->set('consume_date',date('Y-m-d'));
		$this->db->where('product_id',$product_id);
		$this->db->update('products');
		
		$this->session->set_flashdata('message', 'Product Consumed Successfully.');
		redirect('inventory/all_products');
	}

	public function consume_products()
	{
		$product_id = $this->input->post('product_id');
		$consume_reason = $this->input->post('consume_reason');
		$quantity = $this->input->post('quantity');

		//GET THE PRODUCT
		$productDetails = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();

		//CHECK PRODUCT MOVE IN SAME CAMPUS
		for($i=1;$i<=$quantity;$i++)
		{
			$this->db->limit(1);
			$productDetail = $this->db->get_where('products',array('product_name_id'=>$productDetails[0]['product_name_id'],'campus_id'=>$productDetails[0]['campus_id'],'room_id'=>$productDetails[0]['room_id'],'subroom_id'=>$productDetails[0]['subroom_id'],'consume'=>0))->result_array();

			$this->db->set('consume_reason',$consume_reason);
			$this->db->set('consume',1);
			$this->db->set('consume_date',date('Y-m-d'));
			$this->db->where('product_id',$productDetail[0]['product_id']);
			$this->db->update('products');
		}
		$this->session->set_flashdata('message','Product Consumed Successfully.');
		redirect('inventory/all_products');
	}
	
	public function all_documents()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		if(@$this->input->post('submit')==1)
		{
			if($this->input->post('campus_id')=='')
			{
				$array =array(
						'documents.status'=>'1'
						);
			}
			elseif($this->input->post('room_id')!='')
			{
				$campus_id = $this->input->post('campus_id');
				$room_id = $this->input->post('room_id');
				$array = array(
					'documents.campus_id'=>$campus_id,
					'documents.room_id'=>$room_id,
					'documents.status'=>'1'
					);
				
			}
			elseif($this->input->post('room_id')!='' && $this->input->post('subroom_id')!='')
			{
				$campus_id = $this->input->post('campus_id');
				$room_id = $this->input->post('room_id');
				$subroom_id = $this->input->post('subroom_id');
				$array = array(
					'documents.campus_id'=>$campus_id,
					'documents.room_id'=>$room_id,
					'documents.subroom_id'=>$subroom_id,
					'documents.status'=>'1'
					);
				
			}
			else
			{
				$campus_id = $this->input->post('campus_id');
				$array = array(
					'documents.campus_id'=>$campus_id,
					'documents.status'=>'1'
					);
			}
			$this->db->select('*');
			$this->db->from('documents');
			$this->db->join('campuses','campuses.campus_id=documents.campus_id','inner');
			$this->db->join('document_names','documents.document_name_id=document_names.document_name_id','left');
			$this->db->where($array);
			$data['documents'] = $this->db->get()->result_array();
		}
		else
		{
			$data['documents'] = array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_documents', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete_document($document_id)
	{
		$this->db->where('document_id',$document_id);
		$this->db->delete('documents');
		
		$this->session->set_flashdata('message', 'Document Deleted Successfully.');
		redirect('inventory/all_documents');
	}

    public function sale_item_details()
    {
        $product_id = $this->input->post('item_id');
        //GET PRODUCT DETAILS
        $product = $this->db->get_where('products',array('product_id' => $product_id))->result_array();

        $purchaser_name = $this->input->post('purchaser_name');
        $purchaser_contact = $this->input->post('purchaser_contact');
        $sale_amount = $this->input->post('sale_amount');
        $sale_qty = $this->input->post('sale_qty');
        $total_qty = $this->input->post('total_qty');
        $last_edit = $this->input->post('last_edit');

        $this->db->set('product_id',$product_id);
        $this->db->set('quantity',$sale_qty);
        $this->db->set('purchaser_name',$purchaser_name);
        $this->db->set('purchaser_contact',$purchaser_contact);
        $this->db->set('sale_amount',$sale_amount);
        $this->db->set('sold_by',$this->session->userdata("name"));
        $this->db->insert("asset_sales");
        $sale_id = $this->db->insert_id();

        $this->db->set('po_no',$this->input->post('po_no'));
        $this->db->set('campus_id',$product[0]['campus_id']);
        $this->db->set('room_id',$product[0]['room_id']);
        $this->db->set('subroom_id',$product[0]['subroom_id']);
        $this->db->set('product_name_id',$product[0]['product_name_id']);
        $this->db->set('product_quantity',$sale_qty);
        $this->db->set('product_guarantee',$product[0]['product_guarantee']);
        $this->db->set('product_guarantee_start_date',$product[0]['product_guarantee_start_date']);
        $this->db->set('product_guarantee_end_date',$product[0]['product_guarantee_end_date']);
        $this->db->set('remarks',$product[0]['remarks']);
        $this->db->set('user_id',$product[0]['user_id']);
        $this->db->set('purchase_slip',$product[0]['purchase_slip']);
        $this->db->set('reponsilble_user_id',$product[0]['reponsilble_user_id']);
        $this->db->set('date',$product[0]['date']);
        $this->db->set('add_by',$product[0]['add_by']);
        $this->db->set('last_edit',$last_edit);
        $this->db->set('clear_by',$product[0]['clear_by']);
        $this->db->set('status',$product[0]['status']);
        $this->db->set('po_no',$product[0]['po_no']);
        if ($sale_qty == $total_qty) {
            $this->db->set('sale_id',$sale_id);
            $this->db->set('status', "2");
            $this->db->where('product_id', $product_id);
            $this->db->update('products');
        }
        else {
            $this->db->set('status', "2");
            $this->db->set('sale_id',$sale_id);
            $this->db->insert('products');

            $this->db->set('product_quantity',$total_qty-$sale_qty);
            $this->db->where('product_id', $product_id);
            $this->db->update('products');
        }

        $this->session->set_flashdata('message','Your Product has been Successfully Sold.');
        redirect('inventory/all_products');
    }

    public function generate_qrs()
    {
        /*$data['qrs']=$this->db->order_by("id","DESC")->get('inventory_qr')->row();
        if ($data['qrs'] == NULL)
            $data['qrs'] = 1;
        else
            $data['qrs'] = $data['qrs']->number+1;

        $this->load->view('inventory/generate_qrs', $data);*/
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/generate_qrcodes');
		$this->load->view('inc/footer');
    }

	public function qr_code_generator()
	{
		$data['type'] = $this->input->post('type');

		if($data['type']=="new")
		{
			$data['quantity'] = $this->input->post('new_quantity');

			if(is_numeric($data['quantity']) && $data['quantity']>0)
			{
				$this->db->select('*');
				$this->db->from('inventory_qr');
				$this->db->order_by('id','DESC');
				$this->db->limit(1);
				$data['last_printed_qr'] = $this->db->get()->row()->number;

				$this->load->view('inventory/generate_qrs', $data);
			}
			else
			{
				$this->session->set_flashdata('error','Kindly Enter Valid Quantity.');
				redirect('inventory/generate_qrs');
			}
		}
		elseif($data['type']=="old")
		{
			$data['qr_number'] = $this->input->post('qr_number');
			$data['quantity'] = $this->input->post('quantity');

			if(file_exists(FCPATH.'qr/inv_qr-'.$data['qr_number'].'.png'))
			{
				$this->load->view('inventory/generate_qrs', $data);
			}
			else
			{
				$this->session->set_flashdata('error','Entered QR Code is not exist. Kindly write correct QR Code.');
				redirect('inventory/generate_qrs');
			}
		}
		elseif($data['type']=="custom")
		{
			$data['from_number'] = $this->input->post('from_number');
			$data['to_number'] = $this->input->post('to_number');

			if(is_numeric($data['from_number']) && is_numeric($data['to_number']) && $data['to_number']>$data['from_number'])
			{
				$this->load->view('inventory/generate_qrs', $data);
			}
			else
			{
				$this->session->set_flashdata('error','Enter Valid Number in fields.');
				redirect('inventory/generate_qrs');
			}
		}
		elseif($data['type']=="check")
		{
			$data['from_number'] = $this->input->post('from_number');
			$data['to_number'] = $this->input->post('to_number');

			if(is_numeric($data['from_number']) && is_numeric($data['to_number']) && $data['to_number']>$data['from_number'])
			{
				redirect('inventory/check_product?from='.$data['from_number'].'&to='.$data['to_number']);
			}
			else
			{
				$this->session->set_flashdata('error','Enter Valid Number in fields.');
				redirect('inventory/generate_qrs');
			}
		}
	}

	public function check_product()
	{
		$from = $this->input->get('from');
		$to = $this->input->get('to');

		$qr_codes = array();
		for($i=$from;$i<=$to;$i++)
		{
			array_push($qr_codes,'inv_qr-'.$i);
		}

		$this->db->select('*');
		$this->db->from('products');
		$this->db->where_in('qr_code',$qr_codes);
		$this->db->get()->result_array();
	}

    public function getSubExpensesFree()
    {
        $course_id = $this->input->post('expense_id');
        $count = $this->input->post('count');
        $count += 1;
        $subjects = $this->db->get_where('product_names', array('sub_of'=>$course_id))->result_array();
        $html='<div class="form-group" id="div-'.$count.'"><label class="col-md-3 control-label">Expense Sub Category <span class="required">*</span></label> <div class="col-md-9">
                    <select class="form-control Select2 exps" name="head_product_id[]" data-count="'.$count.'" id="category_id'.$count.'"><option value="">Select SubProduct</option>';
        foreach($subjects as $subject) {
            $html.='<option value="'.$subject['product_name_id'].'">'.$subject['product_name'].'</option>';
        }
        $html.="</select></div></div>";
        if (count($subjects) > 0)
            echo $html;
    }

	public function add_purchase_request()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();
		
		$data['product_names'] = $this->db->get('product_names')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_purchase_request',$data);
		$this->load->view('inc/footer');
	}

	public function insert_purchase_request()
	{
		$data = $this->input->post();
		$count = count($data['campus_ids']);
		$purchase_no = $this->getPruchaseNo();

		for($i=0;$i<$count;$i++)
		{
			$this->db->set('title',$data['title']);
			$this->db->set('purchase_no',$purchase_no);
			$this->db->set('campus_id',$data['campus_ids'][$i]);
			$this->db->set('room_id',$data['room_ids'][$i]);
			$this->db->set('subroom_id',$data['subroom_ids'][$i]);
			$this->db->set('product_name_id',$data['product_name_ids'][$i]);
			$this->db->set('product_quantity',$data['quantity'][$i]);
			$this->db->set('add_by',$this->session->userdata('name'));
			$this->db->set('purchased_by',$this->session->userdata('user_id'));
			$this->db->insert('purchase_requests');
		}

		$this->session->set_flashdata('message','Purchase Request Submitted Successfully.');
		redirect('inventory/add_purchase_request');
	}

	public function getPruchaseNo()
	{
		// $this->db->order_by('purchase_no','DESC');
		// $this->db->limit(1);
		// $checkLastReqNo = $this->db->get('purchase_requests')->result_array();

		$query = 'SELECT CAST(SUBSTRING(purchase_no,4,LENGTH(purchase_no)) AS INT) as purchase_no FROM purchase_requests ORDER BY purchase_no DESC LIMIT 1';
		$checkLastReqNo = $this->db->query($query)->result_array();

		if(count($checkLastReqNo)>0)
		{
			$req = str_replace('PR-','',$checkLastReqNo[0]['purchase_no']);
			$no = $req+1;
			$reqNo = 'PR-'.$no;
		}
		else
		{
			$reqNo = 'PR-1';
		}
		return $reqNo;
	}

	public function all_purchase_requests()
	{
		$access = checkUserAccess();
		$product_request_approval_campuses = @explode(',',$access[0]['product_request_approval_campuses']);
		if($this->input->post('from_date') && $this->input->post('to_date'))
		{
			$data['from_date'] = $this->input->post('from_date');
			$data['to_date'] = $this->input->post('to_date');
		}
		else
		{
			$data['from_date'] = date('Y-m-d', strtotime("-1 month", strtotime(date('Y-m-d'))));
			$data['to_date'] = date('Y-m-d');
		}

		$from_date = $data['from_date'].' 00:00:00';
		$to_date = $data['to_date'].' 23:59:59';

		$this->db->select('purchase_requests.*,campuses.campus_name,product_names.product_name,rooms.room_name,subrooms.subroom_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses','campuses.campus_id=purchase_requests.campus_id','inner');
		$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
		$this->db->join('rooms','rooms.room_id=purchase_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=purchase_requests.subroom_id','left');
		$this->db->where(array('purchase_requests.created_at>'=>$from_date,'purchase_requests.created_at<'=>$to_date,'final'=>0));
		$this->db->group_by('purchase_no');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('purchase_requests.campus_id',$product_request_approval_campuses);
		}
		$data['purchase_requests']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_purchase_requests',$data);
		$this->load->view('inc/footer');
	}

	public function edit_purchase_request($purchase_no)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['product_names'] = $this->db->get('product_names')->result_array();
		$data['purchase_request'] = $this->db->get_where('purchase_requests',array('purchase_no'=>$purchase_no))->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/edit_purchase_request',$data);
		$this->load->view('inc/footer');
	}

	public function update_purchase_request($purchase_no)
	{
		$purchase_request_id = $this->input->post('purchase_request_id');
		$quantity = $this->input->post('quantity');
		$campus_id = $this->input->post('campus_id');
		$product_name_id = $this->input->post('product_name_id');
		$status = $this->input->post('status');

		$counter = count($purchase_request_id);

		for($i=0;$i<$counter;$i++)
		{
			$this->db->set('campus_id',$campus_id[$i]);
			$this->db->set('product_name_id',$product_name_id[$i]);
			$this->db->set('product_quantity',$quantity[$i]);
			$this->db->set('status',$status);
			$this->db->set('approve_by',$this->session->userdata('name'));
			$this->db->set('approved_at',date('Y-m-d H:i:s'));
			$this->db->where('purchase_request_id',$purchase_request_id[$i]);
			$this->db->update('purchase_requests');
		}

		$this->session->set_flashdata('message','Purchase Request Updated Successfully.');
		redirect('inventory/all_purchase_requests');
	}

	public function add_qoutation($purchase_no)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['product_names'] = $this->db->get('product_names')->result_array();
		$data['purchase_request'] = $this->db->get_where('purchase_requests',array('purchase_no'=>$purchase_no))->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_qoutation',$data);
		$this->load->view('inc/footer');
	}

	//MY CODE START HERE
	public function add_product_issue_request()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['users'] = $this->db->get_where('users',array('status'=>1))->result_array();
		$data['product_names'] = $this->db->get('product_names')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_product_issue_request',$data);
		$this->load->view('inc/footer');
	}

	public function insert_product_issue_request()
	{
		$request_no = $this->getRequestNo();
		$data = $this->input->post();
		$counter = count($data['campus_id']);

		for($i=0;$i<$counter;$i++)
		{
			$this->db->set('request_no',$request_no);
			$this->db->set('user_id',$this->session->userdata('user_id'));
			$this->db->set('campus_id',$data['campus_id'][$i]);
			$this->db->set('room_id',$data['room_id'][$i]);
			$this->db->set('subroom_id',$data['subroom_id'][$i]);
			$this->db->set('product_name_id',$data['product_name_id'][$i]);
			$this->db->set('quantity',$data['quantity'][$i]);
			$this->db->set('comment',$data['comment'][$i]);
			$this->db->insert('require_product_requests');
		}

		$this->session->set_flashdata('message','Product Request Added Successfully.');
		redirect('inventory/add_product_issue_request');
	}

	public function getRequestNo()
	{
		$this->db->order_by('request_no','DESC');
		$this->db->limit(1);
		$checkLastReqNo = $this->db->get('require_product_requests')->result_array();
		if(count($checkLastReqNo)>0)
		{
			$req = str_replace('PIR-','',$checkLastReqNo[0]['request_no']);
			$no = $req+1;
			$reqNo = 'PIR-'.$no;
		}
		else
		{
			$reqNo = 'PIR-1';
		}
		return $reqNo;
	}

	public function all_product_issue_requests()
	{
		if($this->input->post('from_date') && $this->input->post('to_date'))
		{
			$data['from_date'] = $this->input->post('from_date');
			$data['to_date'] = $this->input->post('to_date');
		}
		else
		{
			$data['from_date'] = date("Y-m-d", strtotime("-1 month", strtotime(date('Y-m-d'))));
			$data['to_date'] = date('Y-m-d');
		}

		$this->db->select('require_product_requests.*,campuses.campus_name,campuses.campus_id,rooms.room_id,rooms.room_name,product_names.product_name,product_names.product_name_id,CONCAT(users.first_name," ",users.last_name) user_name,subrooms.subroom_name');
		$this->db->from('require_product_requests');
		$this->db->join('campuses','campuses.campus_id=require_product_requests.campus_id','left');
		$this->db->join('rooms','rooms.room_id=require_product_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=require_product_requests.subroom_id','left');
		$this->db->join('product_names','product_names.product_name_id=require_product_requests.product_name_id','left');
		$this->db->join('users','users.user_id=require_product_requests.user_id','left');
		if($this->input->post('status')!='')
		{
			$this->db->where(array('require_product_requests.created_at>='=>$data['from_date'].' 00:00:00','require_product_requests.created_at<='=>$data['to_date'].' 11:59:59','require_product_requests.status'=>$this->input->post('status'),'gin'=>0));
		}
		else
		{
			$this->db->where(array('require_product_requests.created_at>'=>$data['from_date'].' 00:00:00','require_product_requests.created_at<'=>$data['to_date'].' 11:59:59','gin'=>0));
		}
		$data['requests'] = $this->db->get()->result_array();

		$this->db->where(array('require_product_requests.created_at>'=>$data['from_date'].' 00:00:00','require_product_requests.created_at<'=>$data['to_date'].' 11:59:59','gin'=>0));
		$requests = $this->db->get('require_product_requests')->result_array();
		$approved = 0;
		$pending = 0;
		$rejected = 0;
		foreach($requests as $request)
		{
			if($request['status']=='1')
			{
				$approved++;
			}
			if($request['status']=='0')
			{
				$pending++;
			}
			if($request['status']=='2')
			{
				$rejected++;
			}
		}
		$data['approved'] = $approved;
		$data['pending'] = $pending;
		$data['rejected'] = $rejected;

		$data['campuses'] = $this->clas->getCampuses();
		$data['product_names'] = $this->db->get('product_names')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_product_issue_requests',$data);
		$this->load->view('inc/footer');
	}

	public function approve_product_issue_requests($require_product_request_id)
	{
		$data=$this->input->post();
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->set('approved_by',$this->session->userdata('user_id'));
		$this->db->set('approved_at',date('Y-m-d H:i:s'));
		$this->db->where('require_product_request_id',$require_product_request_id);
		$this->db->update('require_product_requests');
		redirect('inventory/all_product_issue_requests');
	}

	public function move()
	{
		$product_id = $this->input->post('product_id');
		$campus_id = $this->input->post('campus_id');
		$room_id = $this->input->post('room_id');
		$subroom_id = $this->input->post('subroom_id');
		$quantity = $this->input->post('quantity');

		//GET THE PRODUCT
		$productDetails = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();

		//CHECK PRODUCT MOVE IN SAME CAMPUS
		if($campus_id==$productDetails[0]['campus_id'])
		{
			for($i=1;$i<=$quantity;$i++)
			{
				$this->db->limit(1);
				$productDetail = $this->db->get_where('products',array('product_name_id'=>$productDetails[0]['product_name_id'],'campus_id'=>$productDetails[0]['campus_id'],'room_id'=>$productDetails[0]['room_id'],'subroom_id'=>$productDetails[0]['subroom_id']))->result_array();

				$this->db->set('product_id',$productDetail[0]['product_id']);
				$this->db->set('campus_id',$productDetail[0]['campus_id']);
				$this->db->set('room_id',$productDetail[0]['room_id']);
				$this->db->set('subroom_id',$productDetail[0]['subroom_id']);
				$this->db->set('product_name_id',$productDetail[0]['product_name_id']);
				$this->db->set('added_by',$this->session->userdata('name'));
				$this->db->insert('product_history');

				$this->db->set('campus_id',$campus_id);
				$this->db->set('room_id',$room_id);
				$this->db->set('subroom_id',$subroom_id);
				$this->db->where('product_id',$productDetail[0]['product_id']);
				$this->db->update('products');
			}
			$this->session->set_flashdata('message','Product Moved Successfully.');
		}
		else
		{
			$this->session->set_flashdata('error','Error in moving item.');
		}
		redirect('inventory/all_products');
	}

	public function getProductHistory()
	{
		$product_id = $this->input->post('product_id');

		//GET PRODUCT
		$this->db->select('product_history.*,campuses.campus_name,rooms.room_name,subrooms.subroom_name');
		$this->db->from('product_history');
		$this->db->join('campuses','campuses.campus_id=product_history.campus_id','left');
		$this->db->join('rooms','rooms.room_id=product_history.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=product_history.subroom_id','left');
		$this->db->where('product_history.product_id',$product_id);
		$this->db->order_by('created_at','ASC');
		$products = $this->db->get()->result_array();

		$html='';
		foreach($products as $product)
		{
			$html.='<tr>';
				$html.='<td>';
					$html.=$product['campus_name'];
				$html.='</td>';
				$html.='<td>';
					$html.=$product['room_name'];
				$html.='</td>';
				$html.='<td>';
					$html.=$product['subroom_name'];
				$html.='</td>';
				$html.='<td>';
					$html.=$product['added_by'];
				$html.='</td>';
				$html.='<td>';
					$html.=date('F d, Y h:i:s A',strtotime($product['created_at']));
				$html.='</td>';
			$html.='</tr>';
		}
		echo $html;
	}

	public function manage_gin()
	{
		if($this->input->post('from_date') && $this->input->post('to_date'))
		{
			$data['from_date'] = $this->input->post('from_date');
			$data['to_date'] = $this->input->post('to_date');
		}
		else
		{
			$data['from_date'] = date("Y-m-d", strtotime("-1 month", strtotime(date('Y-m-d'))));
			$data['to_date'] = date('Y-m-d');
		}

		$this->db->select('require_product_requests.*,campuses.campus_name,campuses.campus_id,rooms.room_id,rooms.room_name,product_names.product_name,product_names.product_name_id,CONCAT(users.first_name," ",users.last_name) user_name,subrooms.subroom_name');
		$this->db->from('require_product_requests');
		$this->db->join('campuses','campuses.campus_id=require_product_requests.campus_id','left');
		$this->db->join('rooms','rooms.room_id=require_product_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=require_product_requests.subroom_id','left');
		$this->db->join('product_names','product_names.product_name_id=require_product_requests.product_name_id','left');
		$this->db->join('users','users.user_id=require_product_requests.user_id','left');
		$this->db->where(array('require_product_requests.approved_at>='=>$data['from_date'].' 00:00:00','require_product_requests.approved_at<='=>$data['to_date'].' 11:59:59','require_product_requests.status'=>1,'require_product_requests.gin'=>0));
		$data['requests'] = $this->db->get()->result_array();

		$data['campuses'] = $this->clas->getCampuses();
		$data['product_names'] = $this->db->get('product_names')->result_array();


		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/manage_gin',$data);
		$this->load->view('inc/footer');
	}

	public function issue_gin($require_product_request_id)
	{
		$campus_id = $this->input->post('campus_id');
		$room_id = $this->input->post('room_id');

		$requestDetail = $this->db->get_where('require_product_requests',array('require_product_request_id'=>$require_product_request_id))->result_array();

		$checkProductQuantity = $this->db->get_where('products',array('campus_id'=>$campus_id,'room_id'=>$room_id,'product_name_id'=>$requestDetail[0]['product_name_id']))->result_array();

		if(count($checkProductQuantity)>=$requestDetail[0]['quantity'])
		{
			$this->db->set('gin_campus_id',$campus_id);
			$this->db->set('gin_room_id',$room_id);
			$this->db->set('gin_comment',$this->input->post('gin_comment'));
			$this->db->set('gin',1);
			$this->db->set('gin_by',$this->session->userdata('name'));
			$this->db->set('gin_created_at',date('Y-m-d H:i:s'));
			$this->db->where('require_product_request_id',$require_product_request_id);
			$this->db->update('require_product_requests');
			$this->session->set_flashdata('message','GIN issue successfully.');
			redirect('inventory/manage_gin');
		}
		else
		{
			$this->session->set_flashdata('error','GIN issue failed. Required Product / Quantity is not available in selected campus or room.');
			redirect('inventory/manage_gin');
		}
	}

	public function searchProduct()
	{
		$campus_id = $this->input->post('campus_id');
		$product_name_id = $this->input->post('product_name_id');

		$productDetail = $this->db->get_where('product_names',array('product_name_id'=>$product_name_id))->result_array();
		if(@$productDetail[0]['has_sub']==1)
		{
			$subProducts = $this->db->get_where('product_names',array('sub_of'=>$productDetail[0]['product_name_id']))->result_array();
			$product_name_ids = array();
			foreach($subProducts as $subProduct)
			{
				array_push($product_name_ids,$subProduct['product_name_id']);
			}
		}
		else
		{
			$product_name_ids = array();
			array_push($product_name_ids,$product_name_id);
		}

		$this->db->select('products.*,campuses.campus_name,rooms.room_name,subrooms.subroom_name,product_names.product_name,count(products.product_id) as product_quantity');
		$this->db->from('products');
		$this->db->join('campuses','campuses.campus_id=products.campus_id','inner');
		$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
		$this->db->join('rooms','rooms.room_id=products.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
		if($campus_id=='')
		{
			$this->db->where_in('products.product_name_id',$product_name_ids);
			//$this->db->where(array('products.product_name_id'=>$product_name_id));
		}
		else
		{
			$this->db->where_in('products.product_name_id',$product_name_ids);
			$this->db->where(array('products.campus_id'=>$campus_id));
		}
		
		$this->db->group_by('products.room_id','products.subroom_id');
		$products = $this->db->get()->result_array();

		$html ='';
		

		foreach($products as $product)
		{
			$html .='<tr>';
			$html.='<td>'.$product['campus_name'].'</td>';
			$html.='<td>'.$product['product_name'].'</td>';
			$html.='<td>'.$product['room_name'].'</td>';
			$html.='<td>'.$product['subroom_name'].'</td>';
			$html.='<td>'.$product['product_quantity'].'</td>';
			$html .='</tr>';
		}
		echo $html;
		exit();
	}

	public function manage_grn()
	{
		if($this->input->post('from_date') && $this->input->post('to_date'))
		{
			$data['from_date'] = $this->input->post('from_date');
			$data['to_date'] = $this->input->post('to_date');
		}
		else
		{
			$data['from_date'] = date("Y-m-d", strtotime("-1 month", strtotime(date('Y-m-d'))));
			$data['to_date'] = date('Y-m-d');
		}

		$this->db->select('require_product_requests.*,campuses.campus_name,campuses.campus_id,rooms.room_id,rooms.room_name,product_names.product_name,product_names.product_name_id,CONCAT(users.first_name," ",users.last_name) user_name,subrooms.subroom_name');
		$this->db->from('require_product_requests');
		$this->db->join('campuses','campuses.campus_id=require_product_requests.campus_id','left');
		$this->db->join('rooms','rooms.room_id=require_product_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=require_product_requests.subroom_id','left');
		$this->db->join('product_names','product_names.product_name_id=require_product_requests.product_name_id','left');
		$this->db->join('users','users.user_id=require_product_requests.user_id','left');
		$this->db->where(array('require_product_requests.gin_created_at>='=>$data['from_date'].' 00:00:00','require_product_requests.gin_created_at<='=>$data['to_date'].' 11:59:59','require_product_requests.status'=>1,'require_product_requests.gin'=>1,'require_product_requests.grn'=>0));
		$data['requests'] = $this->db->get()->result_array();


		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/manage_grn',$data);
		$this->load->view('inc/footer');
	}

	public function issue_grn($require_product_request_id)
	{
		//MOVE PRODUCT
		$requestDetail = $this->db->get_where('require_product_requests',array('require_product_request_id'=>$require_product_request_id))->result_array();

		$checkProductQuantity = $this->db->get_where('products',array('campus_id'=>$requestDetail[0]['gin_campus_id'],'room_id'=>$requestDetail[0]['gin_room_id'],'product_name_id'=>$requestDetail[0]['product_name_id']))->result_array();

		if(count($checkProductQuantity)>=$requestDetail[0]['quantity'])
		{
			for($i=1;$i<=$requestDetail[0]['quantity'];$i++)
			{
				$product_id = $this->db->get_where('products',array('product_name_id'=>$requestDetail[0]['product_name_id'],'campus_id'=>$requestDetail[0]['gin_campus_id'],'room_id'=>$requestDetail[0]['gin_room_id']))->row()->product_id;

				$this->db->set('campus_id',$requestDetail[0]['campus_id']);
				$this->db->set('room_id',$requestDetail[0]['room_id']);
				$this->db->where('product_id',$product_id);
				$this->db->update('products');
			}

			$this->db->set('grn_comment',$this->input->post('grn_comment'));
			$this->db->set('grn',1);
			$this->db->set('grn_by',$this->session->userdata('name'));
			$this->db->set('grn_created_at',date('Y-m-d H:i:s'));
			$this->db->where('require_product_request_id',$require_product_request_id);
			$this->db->update('require_product_requests');
			$this->session->set_flashdata('message','Product Received Successfully successfully.');
			redirect('inventory/manage_grn');
		}
		else
		{
			$this->session->set_flashdata('error','Something went wrong.');
			redirect('inventory/manage_grn');
		}
	}

	public function getProductConsumeHistory()
	{
		$product_id = $this->input->post('product_id');
		$myProduct = $this->db->get_where('products',array('product_id'=>$product_id))->result_array();

		//GET PRODUCT
		$this->db->select('products.*,campuses.campus_name,rooms.room_name,subrooms.subroom_name');
		$this->db->from('products');
		$this->db->join('campuses','campuses.campus_id=products.campus_id','left');
		$this->db->join('rooms','rooms.room_id=products.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
		$this->db->where(array('products.product_name_id'=>$myProduct[0]['product_name_id'],'products.campus_id'=>$myProduct[0]['campus_id'],'consume'=>1));
		$this->db->limit(5);
		$this->db->order_by('consume_date','DESC');
		$products = $this->db->get()->result_array();

		$html='';
		foreach($products as $product)
		{
			$html.='<tr>';
				$html.='<td>';
					$html.=$product['campus_name'];
				$html.='</td>';
				$html.='<td>';
					$html.=$product['room_name'];
				$html.='</td>';
				$html.='<td>';
					$html.=$product['subroom_name'];
				$html.='</td>';
				$html.='<td>';
					$html.=date('F d, Y',strtotime($product['consume_date']));
				$html.='</td>';
				$html.='<td>';
					$html.=$product['consume_reason'];
				$html.='</td>';
			$html.='</tr>';
		}
		echo $html;
	}

	public function getVendors()
	{
		$product_name_id = $this->input->post('product_name_id');
		$campus_id = $this->input->post('campus_id');

		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);

		//CHECK IT IS MAIN CATEGORY OR NOT
		$main = $this->db->get_where('product_names',array('product_name_id'=>$product_name_id))->result_array();
		if($main[0]['sub_of']==NULL)
		{
			$product_names = $this->db->get_where('product_names',array('sub_of'=>$main[0]['product_name_id']))->result_array();
			
			$product_name_ids = array();

			foreach($product_names as $product_name)
			{
				array_push($product_name_ids,$product_name['product_name_id']);
			}

			$this->db->select('vendors.*,campuses.campus_name as campus_name, CONCAT(users.first_name," ",users.last_name) as user_name');
			$this->db->from('vendors');
			$this->db->join('campuses','campuses.campus_id=vendors.campus_id','left');
			$this->db->join('users','vendors.created_by=users.user_id','left');
			foreach($product_name_ids as $single_product_name_id)
			{
				$this->db->or_where("find_in_set($single_product_name_id, vendors.product_name_ids)");
			}
			if($this->session->userdata('role')!='Admin')
			{
				//$this->db->where_in('vendors.campus_id', $campus_ids);
			}
			if($campus_id!='')
			{
				$this->db->where('vendors.campus_id',$campus_id);
			}
			$vendors = $this->db->get()->result_array();
		}
		else
		{
			$this->db->select('vendors.*,campuses.campus_name as campus_name, CONCAT(users.first_name," ",users.last_name) as user_name');
			$this->db->from('vendors');
			$this->db->join('campuses','campuses.campus_id=vendors.campus_id','left');
			$this->db->join('users','vendors.created_by=users.user_id','left');
			if($campus_id!='')
			{
				$this->db->where('vendors.campus_id',$campus_id);
			}
			$this->db->where("find_in_set($product_name_id, vendors.product_name_ids)");
			if($this->session->userdata('role')!='Admin')
			{
				//$this->db->where_in('vendors.campus_id', $campus_ids);
			}
			$vendors = $this->db->get()->result_array();
		}

		if(count($vendors)>0)
		{
			$html='';
			$html.='<table class="table table-striped table-bordered table-hover" id="sample_2"><thead><tr><th class="hidden">hidden</th><th>Campus Name</th><th>Vendor Name</th><th>Shop Name</th><th> Phone</th><th>Address</th><th>Products Provided</th><th>Image</th><th>Add By</th><th>Action</th></tr></thead><tbody>';
			$i=1;
			foreach($vendors as $vendor)
			{
				$html.='<tr class="odd gradeX">';
				$html.='<td class="hidden">'.$i.'</td>';
				$html.='<td>'.$vendor['campus_name'].'</td>';
				$html.='<td>'.$vendor['name'].'</td>';
				$html.='<td>'.$vendor['shop_name'].'</td>';
				$html.='<td>'.$vendor['phone'].'</td>';
				$html.='<td>'.$vendor['address'].'</td>';
				$product_ids = explode(',',$vendor['product_name_ids']);
				$this->db->where_in('product_name_id',$product_ids);
				$product_names = $this->db->get('product_names')->result_array();
				$html.='<td>';
				foreach($product_names as $product_name){$html.='<button class="btn grey">'.$product_name['product_name'].'</button>';}
				$html.='</td>';
				if($vendor['image']!=''):
					$html.='<td><a class="btn btn-primary" target="_blank" href="'.base_url().'inventory_images/'.$vendor['image'].'"><i class="fa fa-image"></i> Image</a></td>';
				else:
					$html.='<td></td>';
				endif;
				$html.='<td>'.$vendor['user_name'].'</td>';
				$html.='<td>';
				if($access[0]['edit_vendor']==1 || $this->session->userdata('role')=='Admin'):
					$html.='<a href="'.site_url().'/inventory/edit_vendor/'.$vendor['id'].'" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>';
				endif;
				if($access[0]['delete_vendor']==1 || $this->session->userdata('role')=='Admin'):
					$html.='<a onclick="return confirm(\'Are you sure you want to delete this Vendor?\')" href="'.site_url().'/inventory/delete_vendor/'.$vendor['id'].'" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>';
				endif;
				$html.='</td>';
				$html.='</tr>';
				$i++;
			}
			$html.='</tbody></table>';
		}
		else
		{
			$html='There is no vendor added Againt this Product.';
		}

		echo $html;
	}

	public function searchVendor()
	{
		$campus_id = $this->input->post('campus_id');
		$product_name_id = $this->input->post('product_name_id');
		$quantity = $this->input->post('quantity');
		$purchase_request_id = $this->input->post('purchase_request_id');

		$product_name = $this->db->get_where('product_names',array('product_name_id'=>$product_name_id))->row()->product_name;
		$campus_name = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->campus_name;

		$this->db->select('*');
		$this->db->from('vendors');
		$this->db->where("find_in_set($product_name_id, product_name_ids)");
		$vendors = $this->db->get()->result_array();

		$html='';
		if(count($vendors)>0)
		{
			foreach($vendors as $vendor)
			{
				$already = $this->db->get_where('purchase_request_prices',array('vendor_id'=>$vendor['id'],'purchase_request_id'=>$purchase_request_id))->result_array();
				if(count($already)>0)
				{
					$price = $already[0]['price'];
					$approved = $already[0]['approve'];
					if($approved==1)
					{
						$approve_status = 'readonly';
						$success_bar = 'success';
					}
					else
					{
						$approve_status = '';
						$success_bar = '';
					}
				}
				else
				{
					$price = '';
					$approve_status = '';
					$success_bar = '';
				}
				$html.='<tr class="'.$success_bar.'">';
				$html.='<td>'.$campus_name.'</td>';
				$html.='<td>'.$vendor['name'].'</td>';
				$html.='<td>'.$vendor['phone'].'</td>';
				$html.='<td>'.$vendor['address'].'</td>';
				$html.='<td>'.$product_name.'</td>';
				$html.='<td>'.$quantity.'</td>';
				$html.='<td><input type="hidden" name="vendor_id[]" value="'.$vendor['id'].'" /><input class="form-control" type="number" name="amount[]" min="0" value="'.$price.'" placeholder="Enter Price" '.$approve_status.' /></td>';
				if($price!='' && $price>0 && $this->session->userdata('role')=='Admin')
				{
					$html.='<td><a class="btn green" href="'.site_url().'/inventory/approveVendor/'.$already[0]['purchase_request_price_id'].'/'.$purchase_request_id.'">Click To Approve</a></td>';
				}
				else
				{
					$html.='<td></td>';
				}
				$html.='</tr>';
				$html.='<input type="hidden" name="purchase_request_id" value="'.$purchase_request_id.'" />';
			}
		}
		else
		{
			$html.='<tr>';
			$html.='<td colspan="8">Sorry there is no vendor added against this product ('.$product_name.')</td>';
			$html.='</tr>';
			$html.='<input type="hidden" name="purchase_request_id" value="'.$purchase_request_id.'" />';
		}

		echo $html;
	}


	public function insert_qoute()
	{
		$vendor_id = $this->input->post('vendor_id');
		$price = $this->input->post('price');
		$purchase_request_id = $this->input->post('purchase_request_id');

		//CHECK VENDOR REQUEST ALREADY EXIST
		$checker = $this->db->get_where('purchase_request_prices',array('vendor_id'=>$vendor_id,'purchase_request_id'=>$purchase_request_id))->result_array();
		
		if(count($checker)>0)
		{
			$this->db->set('vendor_id',$vendor_id);
			$this->db->set('price',$price);
			$this->db->set('purchase_request_id',$purchase_request_id);
			$this->db->where(array('purchase_request_price_id'=>$checker[0]['purchase_request_price_id']));
			$this->db->update('purchase_request_prices');
			echo 'New Price Qoute Updated Successfully.';
		}
		else
		{
			$this->db->set('vendor_id',$vendor_id);
			$this->db->set('price',$price);
			$this->db->set('purchase_request_id',$purchase_request_id);
			$this->db->insert('purchase_request_prices');
			echo 'Price Qoute Updated Successfully.';
		}
	}

	public function insertprice()
	{
		$vendor_id = $this->input->post('vendor_id');
		$amount = $this->input->post('amount');
		$purchase_request_id = $this->input->post('purchase_request_id');
		$counter = count($vendor_id);

		//DELETE PREVIOUS REQUESTS
		$this->db->where('purchase_request_id',$purchase_request_id);
		$this->db->delete('purchase_request_prices');

		for($i=0;$i<$counter;$i++)
		{
			$this->db->set('vendor_id',$vendor_id[$i]);
			$this->db->set('price',$amount[$i]);
			$this->db->set('purchase_request_id',$purchase_request_id);
			$this->db->insert('purchase_request_prices');
		}
		$this->session->set_flashdata('message','Success');
		redirect('inventory/all_purchase_requests');
	}

	public function approveVendor($purchase_request_price_id,$purchase_request_id)
	{
		$this->db->set('approve',0);
		$this->db->where('purchase_request_id',$purchase_request_id);
		$this->db->update('purchase_request_prices');

		$this->db->set('approve',1);
		$this->db->where('purchase_request_price_id',$purchase_request_price_id);
		$this->db->update('purchase_request_prices');
		$this->session->set_flashdata('message','Purchase Request Approved.');
		redirect('inventory/all_purchase_requests');
	}

	public function getPrice()
	{
		$vendor_id = $this->input->post('vendor_id');
		$purchase_request_id = $this->input->post('purchase_request_id');
		$price = $this->db->get_where('purchase_request_prices',array('vendor_id'=>$vendor_id,'purchase_request_id'=>$purchase_request_id))->result_array();
		if(count($price)>0)
		{
			echo $price[0]['price'];
		}
		else
		{
			echo '';
		}
	}

	public function selectVendor()
	{
		$purchase_request_price_id = $this->input->post('purchase_request_price_id');
		$purchase_request_prices = $this->db->get_where('purchase_request_prices',array('purchase_request_price_id'=>$purchase_request_price_id))->result_array();
		$purchase_request_id = $purchase_request_prices[0]['purchase_request_id'];

		$this->db->set('approve',0);
		$this->db->where('purchase_request_id',$purchase_request_id);
		$this->db->update('purchase_request_prices');

		$this->db->set('approve',1);
		$this->db->where('purchase_request_price_id',$purchase_request_price_id);
		$this->db->update('purchase_request_prices');

		$this->db->set('purchased',0);
		$this->db->set('purchase_price',$purchase_request_prices[0]['price']);
		$this->db->set('purchase_from',$purchase_request_prices[0]['vendor_id']);
		$this->db->where('purchase_request_id',$purchase_request_id);
		$this->db->update('purchase_requests');

		echo 'Selection Updated.';
	}

	public function finalise_qoutation($purchase_no)
	{
		$requests = $this->db->get_where('purchase_requests',array('purchase_no'=>$purchase_no))->result_array();

		foreach($requests as $request)
		{
			$check = $this->db->get_where('purchase_request_prices',array('purchase_request_id'=>$request['purchase_request_id'],'approve'=>1))->result_array();
			if(count($check)<1)
			{
				$this->session->set_flashdata('error','Error! Kindly select every product final prices.');
				redirect('inventory/add_qoutation/'.$purchase_no);
			}
		}
		$this->db->set('final',1);
		$this->db->set('final_approve_at',date('Y-m-d H:i:s'));
		$this->db->set('qoutation_approve_by',$this->session->userdata('name'));
		$this->db->where('purchase_no',$purchase_no);
		$this->db->update('purchase_requests');

		$this->session->set_flashdata('message','Purchase number '.$purchase_no.' has been approved.');
		redirect('inventory/all_purchase_requests');
	}

	public function purchase_orders()
	{
		$access = checkUserAccess();
		$product_request_approval_campuses = @explode(',',$access[0]['purchase_campuses']);
		if($this->input->post('from_date') && $this->input->post('to_date'))
		{
			$data['from_date'] = $this->input->post('from_date');
			$data['to_date'] = $this->input->post('to_date');
		}
		else
		{
			$data['from_date'] = date('Y-m-d', strtotime("-1 month", strtotime(date('Y-m-d'))));
			$data['to_date'] = date('Y-m-d');
		}

		$from_date = $data['from_date'].' 00:00:00';
		$to_date = $data['to_date'].' 23:59:59';

		$this->db->select('purchase_requests.*,campuses.campus_name,product_names.product_name,rooms.room_name,subrooms.subroom_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses','campuses.campus_id=purchase_requests.campus_id','inner');
		$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
		$this->db->join('rooms','rooms.room_id=purchase_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=purchase_requests.subroom_id','left');
		$this->db->where(array('purchase_requests.final_approve_at>'=>$from_date,'purchase_requests.final_approve_at<'=>$to_date,'final'=>1));
		$this->db->group_by('purchase_no');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('purchase_requests.campus_id',$product_request_approval_campuses);
		}
		$data['purchase_requests']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/purchase_orders',$data);
		$this->load->view('inc/footer');
	}

	public function getCampusRooms()
	{
		$campus_id = $this->input->post('campus_id');
		$rooms = $this->db->get_where('rooms',array('campus_id'=>$campus_id))->result_array();

		$html='';
		foreach($rooms as $room)
		{
			$html.='<option value="'.$room['room_id'].'">'.$room['room_name'].'</option>';
		}
		echo $html;
	}

	public function getCampusSubrooms()
	{
		$room_id = $this->input->post('room_id');
		$subrooms = $this->db->get_where('subrooms',array('room_id'=>$room_id))->result_array();

		$html='';
		foreach($subrooms as $subroom)
		{
			$html.='<option value="'.$subroom['subroom_id'].'">'.$subroom['subroom_name'].'</option>';
		}
		echo $html;
	}

	public function view_purchase_order($purchase_no)
	{
		$this->db->select('*,sum(purchase_request_prices.price) as total');
		$this->db->from('purchase_requests');
		$this->db->join('purchase_request_prices','purchase_request_prices.purchase_request_id=purchase_requests.purchase_request_id','inner');
		$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
		$this->db->join('vendors','purchase_request_prices.vendor_id=vendors.id','inner');
		$this->db->group_by('vendors.id');
		$this->db->where(array('purchase_requests.purchase_no'=>$purchase_no,'purchase_requests.product_quantity>'=>0,'purchase_request_prices.approve'=>1));
		$data['purchase_orders'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/view_purchase_order',$data);
		$this->load->view('inc/footer');
	}

	public function insert_finalise_prices()
	{
		$purchase_request_ids = $this->input->post('purchase_request_id');
		$purchase_prices = $this->input->post('purchase_price');
		$purchase_from = $this->input->post('purchase_from');

		$total_entries = count($purchase_request_ids);
		for($i=0;$i<$total_entries;$i++)
		{
			if($purchase_prices[$i]!='')
			{
				$this->db->set('purchased',0);
				$this->db->set('purchased_by',$this->session->userdata('user_id'));
				$this->db->set('purchase_price',$purchase_prices[$i]);
				$this->db->set('purchase_from',$purchase_from[$i]);
				$this->db->where('purchase_request_id',$purchase_request_ids[$i]);
				$this->db->update('purchase_requests');
			}
		}
		
		$this->session->set_flashdata('message','Purchased Successfully.');
		redirect('inventory/purchase_orders');
	}

	public function insert_payment_aggrement()
	{
		$data = $this->input->post();
		$installment = $this->input->post('installment');
		$date = $this->input->post('date');
		$comment = $this->input->post('comment');
		$vendor_id = $this->input->post('vendor_id');
		$purchase_no = $this->input->post('purchase_no');
		$total_amount = $this->input->post('total_amount');

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
        if (!$this->upload->do_upload('aggrement_image')) {
            $image = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }

		//CHECK AGGREMENT PRICE
		$amount=0;
		for($i=0;$i<count($installment);$i++)
		{
			$amount+=$installment[$i];
		}
		if($amount==$total_amount)
		{
			for($i=0;$i<count($installment);$i++)
			{
				$this->db->set('amount',$installment[$i]);
				$this->db->set('date',$date[$i]);
				$this->db->set('comment',$comment[$i]);
				$this->db->set('vendor_id',$vendor_id);
				$this->db->set('purchase_no',$purchase_no);
				$this->db->set('image',$image);
				$this->db->insert('payment_aggrements');
			}
			$this->db->set('purchased',1);
			$this->db->where(array('purchase_no'=>$purchase_no,'purchase_from'=>$vendor_id));
			$this->db->update('purchase_requests');

			$this->session->set_flashdata('message','Payment Aggrement Created Successfully.');
			redirect('inventory/view_purchase_order/'.$purchase_no);
		}
		else
		{
			$this->session->set_flashdata('error','Kindly Enter Complete Installments of '.$total_amount);
			redirect('inventory/view_purchase_order/'.$purchase_no);
		}
	}

	public function delete_purchase_request($purchase_request_id)
	{
		$this->db->where('purchase_request_id',$purchase_request_id);
		$this->db->delete('purchase_requests');

		$this->db->where('purchase_request_id',$purchase_request_id);
		$this->db->delete('purchase_request_prices');

		$this->session->set_flashdata('message','Product Deleted Successfully from Purchase Order.');
		redirect('inventory/purchase_orders');
	}

	public function grn_gate_approval()
	{
		$access = checkUserAccess();
		$product_request_approval_campuses = @explode(',',$access[0]['product_request_approval_campuses']);
		$this->db->select('purchase_requests.*,campuses.campus_name,product_names.product_name,rooms.room_name,subrooms.subroom_name,CONCAT(users.first_name," ",users.last_name) as purchased_by');
		$this->db->from('purchase_requests');
		$this->db->join('campuses','campuses.campus_id=purchase_requests.campus_id','inner');
		$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
		$this->db->join('rooms','rooms.room_id=purchase_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=purchase_requests.subroom_id','left');
		$this->db->join('users','users.user_id=purchase_requests.purchased_by','left');
		$this->db->where(array('purchase_requests.purchased'=>1,'gate_approval'=>0));
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('purchase_requests.campus_id',$product_request_approval_campuses);
		}
		$data['purchase_orders']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/grn_gate_approval',$data);
		$this->load->view('inc/footer');
	}

	public function grn_gate_entry()
	{
		$gate_approval = $this->input->post('gate_approval');
		$purchase_request_id = $this->input->post('purchase_request_id');

		$this->db->set('gate_approval',$gate_approval);
		$this->db->where('purchase_request_id',$purchase_request_id);
		$this->db->update('purchase_requests');

		$this->session->set_flashdata('message','Product Received Successfully.');
		redirect('inventory/grn_gate_approval');
	}

	public function grn_approval()
	{
		$access = checkUserAccess();
		$product_request_approval_campuses = @explode(',',$access[0]['product_request_approval_campuses']);
		$this->db->select('purchase_requests.*,campuses.campus_name,product_names.product_name,rooms.room_name,subrooms.subroom_name,CONCAT(users.first_name," ",users.last_name) as purchased_by');
		$this->db->from('purchase_requests');
		$this->db->join('campuses','campuses.campus_id=purchase_requests.campus_id','inner');
		$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
		$this->db->join('rooms','rooms.room_id=purchase_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=purchase_requests.subroom_id','left');
		$this->db->join('users','users.user_id=purchase_requests.purchased_by','left');
		$this->db->where(array('purchase_requests.purchased'=>1,'gate_approval'=>1,'approval'=>0));
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('purchase_requests.campus_id',$product_request_approval_campuses);
		}
		$data['purchase_orders']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/grn_approval',$data);
		$this->load->view('inc/footer');
	}

	public function add_purchased_product($purchase_request_id)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$data['purchase_request'] = $this->db->get_where('purchase_requests',array('purchase_request_id'=>$purchase_request_id))->result_array();

		$data['rooms'] = $this->db->get_where('rooms',array('campus_id'=>$data['purchase_request'][0]['campus_id']))->result_array();

		$data['subrooms'] = $this->db->get_where('subrooms',array('room_id'=>$data['purchase_request'][0]['room_id']))->result_array();

		$data['exp_categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/add_purchased_product',$data);
		$this->load->view('inc/footer');
	}

	public function insert_purchased_product()
	{
		//CHECK DEFAULT EXEPENSE CATEGORY
		$default_expense_category = $this->db->get('default_expense_category_inventory')->result_array();
		if(count($default_expense_category)<1)
		{
			$this->session->set_flashdata('error','Kindly Choose Default Expense Category in Inventory Rules.');
			redirect('inventory/add_purchased_product/'.$this->input->post('purchase_request_id'));
		}
		
		//CHECK PRODUCT QR CODE
		$qr_code = 'inv_qr-'.$this->input->post('qr_code');
		$product_name_id = $this->input->post('product_name_id');

		$this->db->group_by('product_name_id');
		$products = $this->db->get_where('products',array('qr_code'=>$qr_code))->result_array();
		foreach($products as $product)
		{
			if($product['product_name_id']!=$product_name_id)
			{
				$product_name = $this->db->get_where('product_names',array('product_name_id'=>$product['product_name_id']))->row()->product_name;
				$campus_name = $this->db->get_where('campuses',array('campus_id'=>$product['campus_id']))->row()->campus_name;
				$room_name = $this->db->get_where('rooms',array('room_id'=>$product['room_id']))->row()->room_name;
				$subroom_name = $this->db->get_where('subrooms',array('subroom_id'=>$product['subroom_id']))->row()->subroom_name;
				$this->session->set_flashdata('error','This QR Code is invalid. Kindly Choose Correct QR Code. Entered QR Code is attach to '.$campus_name.'/'.$room_name.'/'.$subroom_name.'/'.$product_name);
				redirect('inventory/add_purchased_product/'.$this->input->post('purchase_request_id'));
			}
		}
		
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';
		
		// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
	
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
	
		//if not successful, set the error message
		if (!$this->upload->do_upload('purchase_slip')) {
			$data = array('msg' => $this->upload->display_errors());
			$purchase_slip = '';

		} 
		else 
		{ 
			//else, set the success message
			$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$purchase_slip = $data['upload_data']['file_name'];
			}
		}

		if (!$this->upload->do_upload('product_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$product_image = '';

		} 
		else 
		{ 
			//else, set the success message
			$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$product_image = $data['upload_data']['file_name'];
			}
		}

		//GET VARIABLES
		$campus_id = $this->input->post('campus_id');
		$room_id = $this->input->post('room_id');
		$subroom_id = $this->input->post('subroom_id');
		$product_name_id = $this->input->post('product_name_id');
		$qr_code = 'inv_qr-'.$this->input->post('qr_code');
		$purchase_slip = $purchase_slip;
		$product_quantity = $this->input->post('product_quantity');
		$product_guarantee = $this->input->post('product_guarantee');
		$estimated_price = $this->input->post('estimated_price');
		$consumeable = $this->input->post('consumeable');
		$saleable = $this->input->post('saleable');
		$expire = $this->input->post('expire');
		$purchase_no = $this->input->post('purchase_no');
		

		if($product_guarantee==0)
		{
			$product_guarantee_start_date = '0000-00-00';
			$product_guarantee_end_date = '0000-00-00';
		}
		else
		{
			$product_guarantee_start_date = $this->input->post('product_guarantee_start_date');
			$product_guarantee_end_date = $this->input->post('product_guarantee_end_date');
		}
		if($saleable==1)
		{
			$sale_amount = $this->input->post('sale_amount');
			$returnable = $this->input->post('returnable');
		}
		else
		{
			$sale_amount = '';
			$returnable = 0;
		}
		if($expire==1)
		{
			$expire_date = $this->input->post('expire_date');
		}
		else
		{
			$expire_date = NULL;
		}
		$remarks = $this->input->post('remarks');
		$add_by = $this->session->userdata('name');
		$last_edit = $this->session->userdata('name');
		$clear_by = '';
		
		//INSERT PRODUCT IN DATABASE

		for($i=1;$i<=$product_quantity;$i++)
		{
			$this->db->set('campus_id',$campus_id);
			$this->db->set('room_id',$room_id);
			$this->db->set('subroom_id',$subroom_id);
			$this->db->set('product_name_id',$product_name_id);
			$this->db->set('qr_code',$qr_code);
			$this->db->set('estimated_price',$estimated_price);
			$this->db->set('product_image',$product_image);
			$this->db->set('purchase_slip',$purchase_slip);
			$this->db->set('product_quantity',1);
			$this->db->set('remaining_quantity',1);
			$this->db->set('product_guarantee',$product_guarantee);
			$this->db->set('product_guarantee_start_date',$product_guarantee_start_date);
			$this->db->set('product_guarantee_end_date',$product_guarantee_end_date);
			$this->db->set('remarks',$remarks);
			$this->db->set('consumeable',$consumeable);
			$this->db->set('saleable',$saleable);
			$this->db->set('sale_amount',$sale_amount);
			$this->db->set('returnable',$returnable);
			$this->db->set('expire',$expire);
			$this->db->set('expire_date',$expire_date);
			$this->db->set('status',1);
			$this->db->set('add_by',$add_by);
			$this->db->set('last_edit',$last_edit);
			$this->db->set('clear_by',$clear_by);
			$this->db->set('purchase_no',$purchase_no);
			$this->db->insert('products');
		}

		//ADD ENTRY IN EXPENSE
		/*
		$purchaser = $this->db->get_where('users',array('user_id'=>$this->input->post('purchased_by')))->result_array();
		$purchaser_name = $purchaser[0]['first_name'].' '.$purchaser[0]['last_name'];
		$product_name = $this->db->get_where('product_names',array('product_name_id'=>$product_name_id))->row()->product_name;
		$campus_name = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->campus_name;
		$purpose = 'Purchase '.$product_name.' (quantity = '.$product_quantity.') in campus '.$campus_name.'';
		$this->db->set('date',date('Y-m-d'));
		$this->db->set('actual_date',date('Y-m-d H:i:s'));
		$this->db->set('Title','Purchase Order');
		$this->db->set('amount',$this->input->post('total_price'));
		$this->db->set('add_by',$purchaser_name);
		$this->db->set('add_by_id',$this->input->post('purchased_by'));
		$this->db->set('last_edit',$purchaser_name);
		$this->db->set('expense_category_id',$default_expense_category[0]['expense_category_id']);
		$this->db->set('campus_id',$campus_id);
		$this->db->set('purpose',$purpose);
		$this->db->set('approved_status',1);
		$this->db->set('payment_type',$this->input->post('paid_type'));
		$this->db->set('paid_type',$this->input->post('paid_type'));
		$this->db->set('purchase_request_id',$this->input->post('purchase_request_id'));
		$this->db->insert('expenses');

		if($this->input->post('paid_type')=='cash')
		{
			$this->db->set('remaining_amount', 'remaining_amount -'.$this->input->post('total_price') .'',false);
			$this->db->where('assign_to', $this->input->post('purchased_by'));
			$this->db->update('petty_cash_college_wise');
		}
		*/

		//UPDATE PURCHASE REQUEST STATUS
		$this->db->set('approval',1);
		$this->db->where('purchase_request_id',$this->input->post('purchase_request_id'));
		$this->db->update('purchase_requests');
		
		$this->session->set_flashdata('message','Your Product added successfully.');
		redirect('inventory/grn_approval');
	}

	public function create_invoices($purchase_no)
	{
		$this->db->select('vendors.*,sum(purchase_price) as total_bill,purchase_requests.purchase_from');
		$this->db->from('purchase_requests');
		$this->db->join('vendors','vendors.id=purchase_requests.purchase_from','inner');
		$this->db->group_by('purchase_requests.purchase_from');
		$this->db->where('purchase_requests.purchase_no',$purchase_no);
		$data['invoices'] = $this->db->get()->result_array();

		// echo '<pre>';
		// print_r($data);
		// echo '</pre>';

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/create_invoices',$data);
		$this->load->view('inc/footer');
	}

	public function payments()
	{
		$till_date = $this->input->post('till_date');
		if($till_date)
		{
			$data['till_date'] = $till_date;
		}
		else
		{
			$data['till_date'] = $till_date = date('Y-m-d');
		}
		
		//GET TOTAL AMOUNT OF UNPAID PAYMENTS TILL DATE
		$this->db->select_sum('amount');
		$this->db->from('payment_aggrements');
		$this->db->where('paid',0);
		$this->db->where('date<=',$till_date);
		$data['unpaid_payments_till_date'] = $this->db->get()->result_array();

		//GET TOTAL AMOUNT OF PAID PAYMENTS TILL DATE
		$this->db->select_sum('amount');
		$this->db->from('payment_aggrements');
		$this->db->where('paid',1);
		$this->db->where('date<=',$till_date);
		$data['paid_payments_till_date'] = $this->db->get()->result_array();

		//GET UNPAID PAYMENTS TILL DATE
		$this->db->select('payment_aggrements.*,vendors.name as vendor_name,vendors.phone as vendor_phone,vendors.address as vendor_address,vendors.shop_name as shop_name');
		$this->db->from('payment_aggrements');
		$this->db->join('vendors','payment_aggrements.vendor_id=vendors.id','left');
		$this->db->where('paid',0);
		$this->db->where('date<=',$till_date);
		$data['unpaid_payments'] = $this->db->get()->result_array();

		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/payments',$data);
		$this->load->view('inc/footer');
	}

	public function delete_product_purchase_request($product_name_id,$purchase_no)
	{
		$this->db->where(array('product_name_id'=>$product_name_id,'purchase_no'=>$purchase_no));
		$this->db->delete('purchase_requests');

		$this->session->set_flashdata('message','Product Remove Successfully.');
		redirect('inventory/purchase_orders');
	}

	public function payment_paid()
	{
		$paid_type = $this->input->post('paid_type');
		$payment_aggrement_id = $this->input->post('payment_aggrement_id');
		$campus_id = $this->input->post('campus_id');
		$date = $this->input->post('date');
		if($paid_type=='bank')
		{
			$transaction_id = $this->input->post('transaction_id');
			if($transaction_id=='')
			{
				$this->session->set_flashdata('error','Error! You did not select any bank transaction for this expense.');
				redirect('inventory/payments');
			}
		}
		else
		{
			$transaction_id = '';
			//CHECK PETTY CASH OF PURCHASER BEFORE EVERYTHING 
			$petty_cash_id = $this->db->get_where('petty_cash_college_wise',array('assign_to'=>$this->session->userdata('user_id')))->row()->id;
			//echo $petty_cash_id;
			$user_petty_cash =  pettycash_statement($petty_cash_id);
			$payment_aggrement = $this->db->get_where('payment_aggrements',array('payment_aggrement_id'=>$payment_aggrement_id))->result_array();
			if($user_petty_cash<$payment_aggrement[0]['amount'])
			{
				$this->session->set_flashdata('error','Error! Kindly add cash in your .');
				redirect('inventory/payments');
			}
		}

		//CHECK DEFAULT EXEPENSE CATEGORY
		$default_expense_category = $this->db->get('default_expense_category_inventory')->result_array();
		if(count($default_expense_category)<1)
		{
			$this->session->set_flashdata('error','Kindly Choose Default Expense Category in Inventory Rules.');
			redirect('inventory/payments');
		}
		//ADD EXPENSE
		$payment_aggrement = $this->db->get_where('payment_aggrements',array('payment_aggrement_id'=>$payment_aggrement_id))->result_array();
		$purchase_no = $payment_aggrement[0]['purchase_no'];
		$purchaser = $this->db->get_where('users',array('user_id'=>$this->session->userdata('user_id')))->result_array();
		$purchaser_name = $purchaser[0]['first_name'].' '.$purchaser[0]['last_name'];
		$campus_name = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->campus_name;
		$purpose = 'Payment Against Purchase Request No.'.$purchase_no;

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
            $image = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }


		$this->db->set('date',$date);
		$this->db->set('actual_date',date('Y-m-d H:i:s'));
		$this->db->set('Title','Purchase Order');
		$this->db->set('image', $image);
		$this->db->set('amount',$payment_aggrement[0]['amount']);
		$this->db->set('add_by',$purchaser_name);
		$this->db->set('add_by_id',$this->session->userdata('user_id'));
		$this->db->set('last_edit',$purchaser_name);
		$this->db->set('expense_category_id',$default_expense_category[0]['expense_category_id']);
		$this->db->set('campus_id',$campus_id);
		$this->db->set('purpose',$purpose);
		$this->db->set('approved_status',1);
		$this->db->set('payment_type',$paid_type);
		$this->db->set('paid_type',$paid_type);
		$this->db->set('purchase_no',$purchase_no);
		$this->db->insert('expenses');
		$expense_id = $this->db->insert_id();

		if($this->input->post('paid_type')=='cash')
		{
			$this->db->set('remaining_amount', 'remaining_amount -'.$payment_aggrement[0]['amount'] .'',false);
			$this->db->where('assign_to', $this->session->userdata('user_id'));
			$this->db->update('petty_cash_college_wise');
		}
		else
		{
			$this->db->set('expense_id',$expense_id);
			$this->db->where('id',$transaction_id);
			$this->db->update('bank_reconciliation_statement');
		}

		//UPDATE PAYMENT AGGREMENT
		$this->db->set('paid',1);
		$this->db->where('payment_aggrement_id',$payment_aggrement_id);
		$this->db->update('payment_aggrements');

		$this->session->set_flashdata('message','Payment Paid Successfully');
		redirect('inventory/payments');

	}

	public function getTransaction()
	{
		$expense_date = $this->input->post('expense_date');
		$expense_amount=number_format($this->input->post('expense_amount'), 2);


		$this->db->group_by('description');
		$transactions = $this->db->get_where('bank_reconciliation_statement',array('trans_date'=>$expense_date,'debit!='=>'','payment_id'=>NULL,'related_to'=>0,'bank_transfer_id'=>NULL,'expense_id'=>NULL,'statement_id'=>NULL,'closing_id'=>NULL,'is_council_fee'=>NULL,'paypro_id'=>NULL,'salary_expense_ids'=>NULL,'cash_deposit_id'=>NULL,'tagged_amount'=>0,'profit_distribution_id'=>NULL,'loan_id'=>NULL,'reversal_payroll_id'=>NULL,'reversal_payroll_expense_id'=>NULL,'reversal_payroll_trans_id'=>NULL))->result_array();

		foreach($transactions as $transaction)
		{
			if(strpos($transaction['debit'],'.')!='')
			{
				$amount = $transaction['debit'];
			}
			else
			{
				$amount = number_format($transaction['debit'],2);
			}

			if($amount==$expense_amount)
			{
				echo '<input type="radio" name="transaction_id" value="'.$transaction['id'].'" required /> <strong>Debit Amount ('.$amount.')</strong> '.$transaction['description'].'<br />';
			}
		}
	}

	public function all_purchase_orders()
	{
		$access = checkUserAccess();
		$product_request_approval_campuses = @explode(',',$access[0]['purchase_campuses']);
		if($this->input->post('from_date') && $this->input->post('to_date'))
		{
			$data['from_date'] = $this->input->post('from_date');
			$data['to_date'] = $this->input->post('to_date');
		}
		else
		{
			$data['from_date'] = date('Y-m-d', strtotime("-1 month", strtotime(date('Y-m-d'))));
			$data['to_date'] = date('Y-m-d');
		}

		$from_date = $data['from_date'].' 00:00:00';
		$to_date = $data['to_date'].' 23:59:59';

		$this->db->select('purchase_requests.*,campuses.campus_name,product_names.product_name,rooms.room_name,subrooms.subroom_name');
		$this->db->from('purchase_requests');
		$this->db->join('campuses','campuses.campus_id=purchase_requests.campus_id','inner');
		$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
		$this->db->join('rooms','rooms.room_id=purchase_requests.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=purchase_requests.subroom_id','left');
		$this->db->where(array('purchase_requests.final_approve_at>'=>$from_date,'purchase_requests.final_approve_at<'=>$to_date));
		$this->db->group_by('purchase_no');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('purchase_requests.campus_id',$product_request_approval_campuses);
		}
		$data['purchase_requests']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/all_purchase_orders',$data);
		$this->load->view('inc/footer');
	}

	public function return_product($purchase_no,$product_name_id)
	{
		$this->db->select('purchase_requests.product_name_id,product_names.product_name,purchase_requests.product_quantity,purchase_requests.purchase_no');
		$this->db->from('purchase_requests');
		$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
		$this->db->where(array('purchase_requests.purchase_no'=>$purchase_no,'purchase_requests.product_name_id'=>$product_name_id));
		$data['purchase_order'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/return_product',$data);
		$this->load->view('inc/footer');
	}

	public function return_product_action()
	{
		$product_name_id = $this->input->post('product_name_id');
		$purchase_no = $this->input->post('purchase_no');
		$return_product_quantity = $this->input->post('return_product_quantity');
		$amount = $this->input->post('amount');
		$user_id = $this->session->userdata('user_id');
		$reason = $this->input->post('reason');

		$check = $this->db->get_where('return_products',array('purchase_no'=>$purchase_no,'product_name_id'=>$product_name_id,'status'=>0))->result_array();

		if(count($check)>0)
		{
			$this->session->set_flashdata('error','Product Return Request Already Submitted.');
		}
		else
		{
			$this->db->set('product_name_id',$product_name_id);
			$this->db->set('purchase_no',$purchase_no);
			$this->db->set('return_product_quantity',$return_product_quantity);
			$this->db->set('amount',$amount);
			$this->db->set('user_id',$user_id);
			$this->db->set('reason',$reason);

			$this->db->insert('return_products');
			
			$this->session->set_flashdata('message','Product Return Request Submit Sucessfully.');
		}
		redirect('inventory/all_purchase_orders');

	}

	public function return_requests()
	{
		$this->db->select('return_products.purchase_no,return_products.amount,product_names.product_name,return_products.return_product_quantity,products.estimated_price,return_products.user_id,return_products.return_product_id');
		$this->db->from('return_products');
		$this->db->join('purchase_requests','purchase_requests.purchase_no=return_products.purchase_no','inner');
		$this->db->join('products','purchase_requests.purchase_no=products.purchase_no','inner');
		$this->db->join('product_names','purchase_requests.product_name_id=product_names.product_name_id','inner');
		$this->db->group_by('return_products.purchase_no');
		$this->db->where(array('return_products.status'=>0));
		$data['return_requests'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('inventory/return_requests',$data);
		$this->load->view('inc/footer');
	}

	public function return_request_update($return_product_id,$status)
	{
		if($status==1)
		{
			$this->db->set('status',$status);
			$this->db->where('return_product_id',$return_product_id);
			$this->db->update('return_products');

			$product_return = $this->db->get_where('return_products',array('return_product_id'=>$return_product_id))->result_array();

			$product_name = $this->db->get_where('product_names',array('product_name_id'=>$product_return[0]['product_name_id']))->row()->product_name;

			$reason = 'Payment for Return Product '.$product_name.' (Quantity = '.$product_return[0]['return_product_quantity'].')  Against Prchase Order '.$product_return[0]['purchase_no'];

			//GET PETTY CASH ID
			$user_id = $this->session->userdata('user_id');
    		$cash = $this->db->get_where("petty_cash_college_wise","assign_to = '$user_id'")->result_array();

			if(count($cash)<1)
			{
				$this->session->set_flashdata('error','Your Petty Cash Account is not exist. Kindly open your petty cash to complete this process');
			}
			else
			{
				$this->db->set('amount_given',$product_return[0]['amount']);
				$this->db->set('debit_credit','D');
				$this->db->set('transaction_by','system');
				$this->db->set('to_pettycash_id',$cash[0]['id']);
				$this->db->set('transaction_pettycash_account',$cash[0]['id']);
				$this->db->set('trans_status','completed');
				$this->db->set('status','1');
				$this->db->set('reason',$reason);
				$this->db->insert('petty_cash_history');

				$loop = $product_return[0]['return_product_quantity'];

				for($i=1;$i<=$loop;$i++)
				{
					$this->db->limit(1);
					$product = $this->db->get_where('products',array('product_name_id'=>$product_return[0]['product_name_id'],'purchase_no'=>$product_return[0]['purchase_no'],'consume'=>0))->result_array();

					$this->db->where('product_id',$product[0]['product_id']);
					$this->db->delete('products');
				}


				$this->session->set_flashdata('message','Return Request Status Approved. Petty Cash Updated. '.$product_name.' (Quantity = '.$product_return[0]['return_product_quantity'].') Deleted Successfully.');
			}
		}
		else
		{
			$this->db->where('return_product_id',$return_product_id);
			$this->db->delete('return_products');

			$this->session->set_flashdata('message','Return Request Status Rejected.');
		}
		redirect('inventory/return_requests');
	}

	public function getProductQR()
	{
		$product_name_id = $this->input->post('product_name_id');

		$this->db->limit(1);
		$product = $this->db->get_where('products',array('product_name_id'=>$product_name_id))->result_array();
		if(count($product)>0)
		{
			$qr_code = str_replace('inv_qr-','',$product[0]['qr_code']);
		}
		else
		{
			$query = 'SELECT CAST(SUBSTRING(qr_code,8,LENGTH(qr_code)) AS INT) as qr_code FROM products ORDER BY qr_code DESC LIMIT 1';
			$number = $this->db->query($query)->result_array();
			$qr_code = $number[0]['qr_code']+1;
		}
		echo $qr_code;
	}

}