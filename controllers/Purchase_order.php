<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class purchase_order extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

	}

	public function add_order()
	{
		$data['products'] = $this->db->get('product_names')->result_array();
		$data['vendors'] = $this->db->get('vendors')->result_array();
		$data['users'] = $this->db->join('designations','designations.designation_id = users.designation_id')->get_where('users','status = 1')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('purchase_order/add_purchase_order', $data);
		$this->load->view('inc/footer');
	}

	public function edit_order($order_id)
	{

        $data['products'] = $this->db->get('product_names')->result_array();
        $data['users'] = $this->db->join('designations','designations.designation_id = users.designation_id')->get_where('users','status = 1')->result_array();
        $data['purchase_order'] = $this->db->join('users','users.user_id=purchase_order.purchaser','left')->get_where('purchase_order',array('id'=>$order_id))->row();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('purchase_order/edit_purchase_order', $data);
        $this->load->view('inc/footer');
	}

	public function insert_order()
	{
		$purchase_user_id = $this->input->post('purchase_user_id');
		$vendor_name = $this->input->post('vendor_name');
		$description = $this->input->post('description');
		$vendor_address = $this->input->post('vendor_address');
		$bill_amount = $this->input->post('amount');
        $category_id = $this->input->post('category_id');
        $quantity = $this->input->post('quantity');
        $unit_price = $this->input->post('unit_price');
        $total_price = $this->input->post('total_price');
        $inv_type = $this->input->post('inv_type');

        $this->db->set('description',$description);
        $this->db->set('vendor_id',$this->input->post('my_vendor_id'));
        $this->db->set('vendor_name',$vendor_name);
        $this->db->set('vendor_address',$vendor_address);
        $this->db->set('total_amount',$bill_amount);
        $this->db->set('purchaser',$purchase_user_id);
        $this->db->set('created_by',$this->session->userdata('name'));
        $this->db->set('status','0');

        $this->db->insert('purchase_order');
        $insert_id = $this->db->insert_id();
		$counter = count($category_id);
		
		for($i=0;$i<$counter;$i++)
		{
			$this->db->set('po_id',$insert_id);
			$this->db->set('item_id',$category_id[$i]);
			$this->db->set('quantity',$quantity[$i]);
			$this->db->set('per_item_price',$unit_price[$i]);
			$this->db->set('total_price',$total_price[$i]);
			$this->db->set('inv_type',$inv_type[$i]);
			$this->db->insert('po_products');
		}

		$this->session->set_flashdata('message','Purchase Order Add Successfully.');
		redirect('purchase_order/all_orders');
	}
	
	public function update_order($order_id)
	{
        $purchase_user_id = $this->input->post('purchase_user_id');
        $vendor_name = $this->input->post('vendor_name');
        $description = $this->input->post('description');
        $vendor_address = $this->input->post('vendor_address');
        $bill_amount = $this->input->post('amount');
        $category_id = $this->input->post('category_id');
        $quantity = $this->input->post('quantity');
        $unit_price = $this->input->post('unit_price');
        $total_price = $this->input->post('total_price');

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
                $this->db->set('po_image',$picture);
            }
        }

        $this->db->set('description',$description);
        $this->db->set('vendor_name',$vendor_name);
        $this->db->set('vendor_address',$vendor_address);
        $this->db->set('total_amount',$bill_amount);
        $this->db->set('purchaser',$purchase_user_id);

        $this->db->set('status','0');

        $this->db->where('id',$order_id);
        $this->db->update('purchase_order');

        $insert_id = $order_id;

        $this->db->where('po_id', $order_id);
        $this->db->delete('po_products');

        $counter = count($category_id);

        for($i=0;$i<$counter;$i++)
        {
            $this->db->set('po_id',$insert_id);
            $this->db->set('item_id',$category_id[$i]);
            $this->db->set('quantity',$quantity[$i]);
            $this->db->set('per_item_price',$unit_price[$i]);
            $this->db->set('total_price',$total_price[$i]);
            $this->db->insert('po_products');
        }
        $this->session->set_flashdata('message','Purchase Order Updated Successfully.');
        redirect('purchase_order/all_orders');
	}
	
	public function all_orders()
	{
        $setype = 0;

        if ($this->input->post('setype') === 'Pending')
        {
            $setype='0';
        }
        if ($this->input->post('setype') === 'Approved')
        {
            $setype='1';
        }
        if ($this->input->post('setype') === 'Rejected')
        {
            $setype='2';
        }
        if ($this->input->post('setype') === 'Purchased')
        {
            $setype='3';
        }

        if($this->input->post('from_date') == NULL && $this->input->post('to_date') == NULL)
        {
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }else{
            $from_date = $this->input->post('from_date');
            $to_date = $this->input->post('to_date');
        }

		$this->db->select('*,purchase_order.status as p_status');
		$this->db->from('purchase_order');
		$this->db->join('users','users.user_id=purchase_order.purchaser','left');
        $this->db->where(array('purchase_order.created_at>='=>$from_date." 00:00:00", 'purchase_order.created_at<='=>$to_date." 23:59:59"));
		if ($this->input->post('setype'))
            $this->db->where('purchase_order.status', $setype);

		$data['purchase_orders'] = $this->db->get()->result_array();

        $all_orders = $this->db->where(array('purchase_order.created_at>='=>$from_date." 00:00:00", 'purchase_order.created_at<='=>$to_date." 23:59:59"))->get('purchase_order')->result_array();
        $data['pending'] = 0;
        $data['approved'] = 0;
        $data['rejected'] = 0;
        $data['purchased'] = 0;

        foreach ($all_orders as $exp)
        {
            if ($exp['status'] === '0')
            {
                $data['pending']++;
            }elseif ($exp['status'] === '1')
            {
                $data['approved']++;
            }elseif ($exp['status'] === '2')
            {
                $data['rejected']++;
            }elseif ($exp['status'] === '3')
            {
                $data['purchased']++;
            }
        }
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('purchase_order/all_purchase_orders', $data);
		$this->load->view('inc/footer');
	}

    public function view_order($order_id)
    {

        $data['purchase_order'] = $this->db->join('users','users.user_id=purchase_order.purchaser','left')->join('designations','designations.designation_id = users.designation_id')->get_where('purchase_order',array('id'=>$order_id))->row();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('purchase_order/view_purchase_order', $data);
        $this->load->view('inc/footer');
    }

    public function change_approve_status()
    {

        $this->db->set('status', $this->input->post('status'));
        $this->db->set('approved_by', $this->input->post('last_edit'));
        $this->db->set('approved_date', date("Y-m-d h:i:s"));
        $this->db->where('id', $this->input->post('expense_id'));
        $this->db->update('purchase_order');

        redirect('purchase_order/all_orders');

    }

    public function change_purchased_status()
    {

        $cash = my_pettycash();
//        print_r($cash);
//        exit();

        if ($cash > $this->input->post('purchased_amount')) {
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
            if (!$this->upload->do_upload('picture')) {
                $data = array('msg' => $this->upload->display_errors());
                $picture = '';
            } else {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if ($data['upload_data']['file_name']) {
                    $picture = $data['upload_data']['file_name'];
                }
            }

            $this->db->set('status', "3");
            $this->db->set('purchased_by', $this->input->post('last_edit'));
            $this->db->set('purchased_date', date("Y-m-d h:i:s"));
            $this->db->set('po_image', $picture);
            $this->db->set('purchased_amount', $this->input->post('purchased_amount'));
            $this->db->set('paid_amount', $this->input->post('paid_amount'));
            $this->db->where('id', $this->input->post('expense_id'));
            $this->db->update('purchase_order');

            $user = $this->db->get_where('users',"user_id = '".$this->session->userdata('user_id')."'")->row();

            $this->db->set('campus_id',$user->campus_id);
            $this->db->set('expense_category_id',"108");
            $this->db->set('title',"Purchase against PO-".$this->input->post('expense_id'));
            $this->db->set('date',date('Y-m-d'));
            $this->db->set('amount',$this->input->post('paid_amount'));
            $this->db->set('purpose',"Purchase Order");
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', $picture);
            $this->db->set('approved_status', '1');
            $this->db->set('po_no', $this->input->post('expense_id'));
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->insert('expenses');

            $this->db->set('remaining_amount', 'remaining_amount -' . $this->input->post('paid_amount') . '', false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');



            $this->session->set_flashdata('message', 'Purchase Order Set to Purchased Successfully.');
            redirect('purchase_order/all_orders');
        }
        else
        {

            $this->session->set_flashdata('error', 'Insufficient Balance.');
            redirect('purchase_order/all_orders');
        }

    }

    public function add_payment_details()
    {

        $cash = my_pettycash();
//        print_r($cash);
//        exit();

        if ($cash > $this->input->post('purchased_amount')) {


            $this->db->set('paid_amount', 'paid_amount +' . $this->input->post('paid_amount') . '', false);
            $this->db->where('id', $this->input->post('expense_id'));
            $this->db->update('purchase_order');

            $user = $this->db->get_where('users',"user_id = '".$this->session->userdata('user_id')."'")->row();

            $this->db->set('campus_id',$user->campus_id);
            $this->db->set('expense_category_id',"108");
            $this->db->set('title',"Purchase against PO-".$this->input->post('expense_id'));
            $this->db->set('date',date('Y-m-d'));
            $this->db->set('amount',$this->input->post('paid_amount'));
            $this->db->set('purpose',"Purchase Order");
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', "");
            $this->db->set('approved_status', '1');
            $this->db->set('po_no', $this->input->post('expense_id'));
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->insert('expenses');

            $this->db->set('remaining_amount', 'remaining_amount -' . $this->input->post('paid_amount') . '', false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');

            $this->session->set_flashdata('message', 'Purchase Order Set to Purchased Successfully.');
            redirect('purchase_order/all_orders');
        }
        else
        {

            $this->session->set_flashdata('error', 'Insufficient Balance.');
            redirect('purchase_order/all_orders');
        }

    }

    public function add_grn()
    {

        $purchase_id = $this->input->post("purchase_order");
        $data['purchase_orders'] = $this->db->get_where('purchase_order',"status = 3")->result_array();
        $data['pr_id'] = $purchase_id;
        $data['pr_order'] = $this->db->get_where('purchase_order',"id = '".$purchase_id."'")->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('purchase_order/add_grn', $data);
        $this->load->view('inc/footer');
    }

    public function insert_grn($purchase_order)
    {

        $product_ids = $this->input->post('product_id');
        $product_types = $this->input->post('product_type');
        $quantitys = $this->input->post('qty');
        $unit_prices = $this->input->post('unit_price');
        $campus_id = $this->input->post('campus_id');

        $this->db->set('purchase_order_no',$purchase_order);
        $this->db->set('created_by',$this->session->userdata('name'));
        $this->db->set('campus_id',$campus_id);
        $this->db->insert('grn');
        $insert_id = $this->db->insert_id();

        $counter = count($product_ids);
        $order = $this->db->get_where('purchase_order',"id = '$purchase_order'")->row();
        $this->db->set('employee_id',"1");
        $this->db->set('comment',"Receiving Against PO-".$order->id);
        $this->db->set('payment_type',"Cash");
        $this->db->insert('receivings');
        $rec_id = $this->db->insert_id();

        for($i=0;$i<$counter;$i++) {
            $this->db->set('po_id',$purchase_order);
            $this->db->set('grn_id',$insert_id);
            $this->db->set('product_id',$product_ids[$i]);
            $this->db->set('qty',$quantitys[$i]);
            $this->db->set('price_per',$unit_prices[$i]);
            $this->db->insert('grn_products');
            if ($product_types[$i] == "inventory") {
                $product = $this->db->get_where("product_names", "product_name_id = '$product_ids[$i]'")->row();
                if ($product->inventory_product_id === NULL) {
                    $item_data = array(
                        'name' => $product->product_name,
                        'description' => $product->product_name,
                        'category' => $product->product_name,
                        'cost_price' => $unit_prices[$i],
                        'unit_price' => $unit_prices[$i],
                        'reorder_level' => "20",
                        'receiving_quantity' => "0",
                        'allow_alt_description' => "0",
                        'is_serialized' => "0",
                        'deleted' => "0",
                        'custom1' => '',
                        'custom2' => '',
                        'custom3' => '',
                        'custom4' => '',
                        'custom5' => '',
                        'custom6' => '',
                        'custom7' => '',
                        'custom8' => '',
                        'custom9' => '',
                        'custom10' => ''
                    );
                    if ($this->db->insert('items', $item_data)) {
                        $item_id = $this->db->insert_id();
                        $this->db->set("inventory_product_id", $item_id);
                        $this->db->where("product_name_id", $product->product_name_id);
                        $this->db->update("product_names");
                    }
                } else
                    $item_id = $product->inventory_product_id;

                $this->insert_to_receiving($order->id, $item_id, ($i + 1), $quantitys[$i], $unit_prices[$i], $rec_id);
            }
            else
                $this->insert_to_inventory($campus_id,$purchase_order,$product_ids[$i],$quantitys[$i],$order->po_image,$this->session->userdata('user_id'),$this->session->userdata('name'));
        }

        $this->session->set_flashdata('message','GRN Add Successfully.');
        redirect('purchase_order/all_orders');
    }

    public function insert_to_inventory($campus_id,$po,$product,$qty,$purchase_slip,$user_id,$user_name)
    {
        $room = $this->db->get_where('rooms',"room_name = 'Main Inventory' and campus_id = '$campus_id'")->row()->room_id;
        $this->db->set('campus_id',$campus_id);
        $this->db->set('room_id',$room);
        $this->db->set('subroom_id','');
        $this->db->set('product_name_id',$product);
        $this->db->set('purchase_slip',$purchase_slip);
        $this->db->set('product_quantity',$qty);
        $this->db->set('product_guarantee',"0");
        $this->db->set('product_guarantee_start_date',"0000-00-00");
        $this->db->set('product_guarantee_end_date',"0000-00-00");
        $this->db->set('remarks',"Received in Stock of PO-$po");
        $this->db->set('user_id',$user_id);
        $this->db->set('reponsilble_user_id',$user_id);
        $this->db->set('po_no',$po);
        $this->db->set('status',"1");
        $this->db->set('add_by',$user_name);
        $this->db->set('last_edit',$user_name);
        $this->db->set('clear_by',$user_name);
        $this->db->insert('products');
    }

    public function insert_to_receiving($po_id,$item_id,$key,$quantity,$cost_price,$rec_id)
    {
        $this->db->set('receiving_id',$rec_id);
        $this->db->set('item_id',$item_id);
        $this->db->set('description',"Receiving Against PO-".$po_id);
        $this->db->set('line',$key);
        $this->db->set('quantity_purchased',$quantity);
        $this->db->set('receiving_quantity',$quantity);
        $this->db->set('item_cost_price',$cost_price);
        $this->db->set('item_unit_price',$cost_price);
        $this->db->set('discount_percent',"0.0");
        $this->db->set('item_location',"1");
        $this->db->insert('receivings_items');

        return true;
    }

    public function all_grns()
    {

        $this->db->select('*');
        $this->db->from('grn');
        $data['purchase_orders'] = $this->db->get()->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('purchase_order/all_grn_orders', $data);
        $this->load->view('inc/footer');
    }

    public function view_grn($order_id)
    {

        $data['purchase_order'] = $this->db->get_where('grn',array('id'=>$order_id))->row();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('purchase_order/view_grn_order', $data);
        $this->load->view('inc/footer');
    }

    public function add_vendor()
    {
        $data['vendors'] = $this->db->get('vendors')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('purchase_order/add_vendor', $data);
        $this->load->view('inc/footer');
    }

    public function insert_vendor()
    {
        $vendor_name = $this->input->post('vendor_name');
        $vendor_address = $this->input->post('vendor_address');
        $vendor_phone = $this->input->post('vendor_phone');

        $this->db->set('name',$vendor_name);
        $this->db->set('address',$vendor_address);
        $this->db->set('phone',$vendor_phone);
        $this->db->set('created_by',$this->session->userdata('name'));
        $this->db->insert('vendors');

        $this->session->set_flashdata('message','Vendor Added Successfully.');
        redirect('purchase_order/add_vendor');
    }

}
