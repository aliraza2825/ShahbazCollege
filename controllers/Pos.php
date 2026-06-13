<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pos extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('clas');
	}
	public function index()
	{
		$data['campuses'] = $this->clas->getCampuses();

		$data['students'] = $this->db->get_where('students',array('status'=>1))->result_array();

		//GET SALEABLE PRODUCTS
		$this->db->select('*');
		$this->db->from('product_names');
		$this->db->join('products','products.product_name_id=product_names.product_name_id','inner');
		$this->db->where(array('products.saleable'=>1,'products.sold'=>0));
		$this->db->group_by('product_names.product_name_id');
		$data['product_names'] = $this->db->get()->result_array();

		//GET ONE MONTH SALE HISTORY

		$data['from_date'] = $from_date = date("Y-m-d", strtotime("-1 month", strtotime(date('Y-m-d'))));
		$data['to_date'] = $to_date = date('Y-m-d');

		$this->db->select('*');
		$this->db->from('products');
		$this->db->join('users','users.user_id=products.sold_by','inner');
		$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
		$this->db->join('campuses','campuses.campus_id=products.campus_id','left');
		$this->db->join('rooms','rooms.room_id=products.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
		if($this->session->userdata('role')=='Admin')
		{
			$this->db->where(array('sold'=>1,'sold_date>='=>$from_date,'sold_date<='=>$to_date));
		}
		else
		{
			$this->db->where(array('products.sold_by'=>$this->session->userdata('user_id'),'sold'=>1,'sold_date>='=>$from_date,'sold_date<='=>$to_date));
		}
		$this->db->order_by('invoice_no','DESC');
		$this->db->group_by('invoice_no');
		$data['sold_products'] = $this->db->get()->result_array();
		
		$this->db->select('campuses.*');
		$this->db->from('campuses');
		$this->db->join('users','users.campus_id=campuses.campus_id','inner');
		$this->db->where('users.user_id',$this->session->userdata('user_id'));
		$staff = $this->db->get()->result_array();

		$data['campus_name'] = $staff[0]['campus_name'];
		$data['campus_code'] = $staff[0]['roll_no_code'];
		$data['campus_address'] = $staff[0]['address'];
		$data['campus_phone'] = $staff[0]['phone'];
		
		$this->db->select('campuses.*');
        $this->db->from('campus_rules');
        $this->db->join('campuses','campuses.campus_id=campus_rules.campus_id','inner');
        $this->db->join('closing_persons','campuses.campus_id=closing_persons.campus_id','inner');
        $this->db->where('campus_rules.college_fee',1);
        $this->db->where('closing_persons.active_status',1);
        $data['fee_campuses'] = $this->db->get()->result_array();
        
        $data['account_numbers'] = $this->db->get_where('accounts',array('type'=>'1'))->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('pos/pos',$data);
		$this->load->view('inc/footer');
	}

	public function checkProductAvailblity()
	{
		$campus_id = $this->input->post('campus_id');
		$room_id = $this->input->post('room_id');
		$subroom_id = $this->input->post('subroom_id');
		$product_name_id = $this->input->post('product_name_id');
		$quantity = $this->input->post('quantity');

		$products = $this->db->get_where('products',array('campus_id'=>$campus_id,'room_id'=>$room_id,'subroom_id'=>$subroom_id,'product_name_id'=>$product_name_id,'saleable'=>1,'sold'=>0,'consume'=>0,'status'=>1))->result_array();

		if(count($products)>0)
		{
			if(count($products)<$quantity)
			{
				echo 'outofstock';
			}
			else
			{
				echo 'success';
			}
		}
		else
		{
			echo 'failed';
		}
	}

	public function invoice()
	{
		$datapost = $this->input->post();
		//GET CUSTOMER DETAILS
		$type = $this->input->post('type');
		if($type=='other')
		{
			$data['name'] = $this->input->post('name');
			$data['phone'] = $this->input->post('phone');
			$data['address'] = '';
			$data['student_id'] = '0';
		}
		else
		{
			$student = $this->db->get_where('students',array('student_id'=>$this->input->post('student_id')))->result_array();
			$data['name'] = $student[0]['first_name'].' '.$student[0]['last_name'];
			$data['phone'] = $student[0]['mobile'];
			$data['address'] = $student[0]['address'];
			$data['student_id'] = $purchaser_student_id = $student[0]['student_id'];
		}
		//GET STAFF OR ACCOUNTANT DEATILS
		$this->db->select('campuses.*');
		$this->db->from('campuses');
		$this->db->join('users','users.campus_id=campuses.campus_id','inner');
		$this->db->where('users.user_id',$this->session->userdata('user_id'));
		$staff = $this->db->get()->result_array();

		$data['campus_name'] = $staff[0]['campus_name'];
		$data['campus_code'] = $staff[0]['roll_no_code'];
		$data['campus_address'] = $staff[0]['address'];
		$data['campus_phone'] = $staff[0]['phone'];

		//GET PRODUCT DEATILS
		$products =array();

		$counter = count($this->input->post('campus_id'));

		for($i=0;$i<$counter;$i++)
		{
			//GET PRODUCT DETAILS
			$this->db->select('products.*,product_names.product_name');
			$this->db->from('products');
			$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
			$this->db->where(array('products.product_name_id'=>$datapost['product_name_id'][$i],'products.campus_id'=>$datapost['campus_id'][$i],'products.room_id'=>$datapost['room_id'][$i],'products.subroom_id'=>$datapost['subroom_id'][$i]));
			$this->db->limit(1);
			$product = $this->db->get()->result_array();

			//CREATING SALE AMOUNT
			if(@$purchaser_student_id!='')
			{
				//CHECK STUDENT ALREADY CLAIM FREE PRODUCT
				$claim = $this->db->get_where('products',array('student_id'=>$purchaser_student_id,'product_name_id'=>$product[0]['product_name_id'],'sold_amount'=>0))->result_array();

				if(count($claim)>0)
				{
					$sale_amount = $product[0]['sale_amount'];
				}
				else
				{
					$student_details = $this->db->get_where('students',array('student_id'=>$purchaser_student_id))->result_array();
					if($student_details[0]['status']==1)
					{
						$student_class_id = $student_details[0]['class_id'];
						$product_name_id = $product[0]['product_name_id'];
						$this->db->select('*');
						$this->db->from('free_item_rules');
						$this->db->where("find_in_set($student_class_id, class_ids)");
						$this->db->where("find_in_set($product_name_id, product_name_ids)");
						$free_check = $this->db->get()->result_array();
						if(count($free_check)>0 && $student_details[0]['registration_date']>=$free_check[0]['student_admission_date'])
						{
							$sale_amount = 0;
						}
						else
						{
							$sale_amount = $product[0]['sale_amount'];
						}
					}
					else
					{
						$sale_amount = $product[0]['sale_amount'];
					}
				}
			}
			else
			{
				$sale_amount = $product[0]['sale_amount'];
			}

			//CREATING ARRAY
			$products[$i]['product_id'] = $product[0]['product_id'];
			$products[$i]['product_name'] = $product[0]['product_name'];
			$products[$i]['product_unit_price'] = $sale_amount;
			$products[$i]['product_quantity'] = $datapost['quantity'][$i];
			$products[$i]['campus_id'] = $datapost['campus_id'][$i];
			$products[$i]['room_id'] = $datapost['room_id'][$i];
			$products[$i]['subroom_id'] = $datapost['subroom_id'][$i];
			$products[$i]['product_name_id'] = $datapost['product_name_id'][$i];
		}

		// echo '<pre>';
		// print_r($products);
		// echo '</pre>';

		$data['products'] = $products;
		
		$this->db->select('campuses.*');
        $this->db->from('campus_rules');
        $this->db->join('campuses','campuses.campus_id=campus_rules.campus_id','inner');
        $this->db->join('closing_persons','campuses.campus_id=closing_persons.campus_id','inner');
        $this->db->where('campus_rules.college_fee',1);
        $this->db->where('closing_persons.active_status',1);
        $data['campuses'] = $this->db->get()->result_array();
        
        $data['account_numbers'] = $this->db->get_where('accounts',array('type'=>'1'))->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('pos/invoice',$data);
		$this->load->view('inc/footer');
	}

	public function searchProduct()
	{
		$campus_id = $this->input->post('campus_id');
		$product_name_id = $this->input->post('product_name_id');

		$this->db->select('products.*,campuses.campus_name,rooms.room_name,subrooms.subroom_name,product_names.product_name,count(products.product_id) as product_quantity');
		$this->db->from('products');
		$this->db->join('campuses','campuses.campus_id=products.campus_id','inner');
		$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
		$this->db->join('rooms','rooms.room_id=products.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
		if($campus_id=='')
		{
			$this->db->where(array('products.product_name_id'=>$product_name_id,'products.saleable'=>1,'products.sold'=>0,'products.consume'=>0));
		}
		else
		{
			$this->db->where(array('products.campus_id'=>$campus_id,'products.product_name_id'=>$product_name_id,'products.saleable'=>1,'products.sold'=>0,'products.consume'=>0));
		}
		$this->db->group_by('products.room_id','products.subroom_id');
		$products = $this->db->get()->result_array();

		$html ='';
		

		foreach($products as $product)
		{
			$html .='<tr>';
			$html.='<td>'.$product['campus_name'].'</td>';
			$html.='<td>'.$product['room_name'].'</td>';
			$html.='<td>'.$product['subroom_name'].'</td>';
			$html.='<td>'.$product['product_name'].'</td>';
			$html.='<td>'.$product['product_quantity'].'</td>';
			$html.='<td>'.$product['sale_amount'].'</td>';
			$html .='</tr>';
		}
		echo $html;
		exit();
	}

	public function submit_invoice()
	{
		$invoice = $this->input->post('invoice');
		$student_id = $this->input->post('student_id');
		$purchaser_name = $this->input->post('purchaser_name');
		$purchaser_phone = $this->input->post('purchaser_phone');
		$data = $this->input->post('data');
		$products = json_decode($data,true);
		

		foreach($products as $product)
		{
			$counter = $product['product_quantity'];

			for($i=1;$i<=$counter;$i++)
			{
				$this->db->limit(1);
				$selectedProduct = $this->db->get_where('products',array('campus_id'=>$product['campus_id'],'room_id'=>$product['room_id'],'subroom_id'=>$product['subroom_id'],'product_name_id'=>$product['product_name_id'],'sold'=>0,'saleable'=>1))->result_array();
				
				$this->db->set('sold',1);
				$this->db->set('sold_date',date('Y-m-d'));
				$this->db->set('sold_amount',$product['product_unit_price']);
				$this->db->set('invoice_no',$invoice);
				$this->db->set('sold_by',$this->session->userdata('user_id'));
				$this->db->set('student_id',$student_id);
				$this->db->set('purchaser_name',$purchaser_name);
				$this->db->set('purchaser_phone',$purchaser_phone);
				$this->db->where('product_id',$selectedProduct[0]['product_id']);

				$this->db->update('products');
			}
		}

		$this->session->set_flashdata('message','Invoice Saved Successfully');
		redirect('pos');
	}

	public function getSoldProducts()
	{
		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');

		$this->db->select('*');
		$this->db->from('products');
		$this->db->join('users','users.user_id=products.sold_by','inner');
		$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
		$this->db->join('campuses','campuses.campus_id=products.campus_id','left');
		$this->db->join('rooms','rooms.room_id=products.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
		if($this->session->userdata('role')=='Admin')
		{
			$this->db->where(array('sold'=>1,'sold_date>='=>$from_date,'sold_date<='=>$to_date));
		}
		else
		{
			$this->db->where(array('products.sold_by'=>$this->session->userdata('user_id'),'sold'=>1,'sold_date>='=>$from_date,'sold_date<='=>$to_date));
		}
		$this->db->order_by('invoice_no','DESC');
		$this->db->group_by('invoice_no');
		$sold_products = $this->db->get()->result_array();

		$html = '';
		foreach($sold_products as $sold_product)
		{
			$this->db->select('*');
			$this->db->from('products');
			$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
			$this->db->where('invoice_no',$sold_product['invoice_no']);
			$products = $this->db->get()->result_array();

			$html.='<tr>';
			$html.='<td>'.$sold_product['invoice_no'].'</td>';
			$html.='<td>'.$sold_product['sold_date'].'</td>';
			$product_names = '';
			$sale_amount = 0;
            $return_amount = 0;
			foreach($products as $product)
			{
				if($product['return_status']==0)
				{
					$product_names .= $product['product_name'].' (Rs '.$product['sold_amount'].')<br />';
					$sale_amount += $product['sold_amount'];
				}
				else
				{
					$product_names .= $product['product_name'].' (Rs '.$product['sold_amount'].') <span class="label label-sm label-danger">Returned</span><br />';
					$return_amount += $product['sold_amount'];
				}
				//$product_names .= $product['product_name'].'(Rs '.$product['sold_amount'].')<br />';
				//$invoice_total += $product['sold_amount'];
			}
			$html.='<td>'.$product_names.'</td>';
			$html.='<td>'.$sold_product['campus_name'].'<br />'.$sold_product['room_name'].'<br />'.$sold_product['subroom_name'].'</td>';
			$html.='<td> Rs '.$sale_amount.'</td>';
			$html.='<td> Rs '.$return_amount.'</td>';
			if($sold_product['student_id']==0)
			{
				$html.='<td> Name : '.$sold_product['purchaser_name'].'<br />Phone : '.$sold_product['purchaser_phone'].'</td>';
			}
			else
			{
				$html.='<td> Student Name : '.$sold_product['purchaser_name'].'<br />Student Phone : '.$sold_product['purchaser_phone'].'</td>';
			}
			$html.='<td>'.$sold_product['first_name'].' '.$sold_product['last_name'].'</td>';
			$html.='<td><a data-toggle="modal" href="#view_order" class="btn red view_invoice" data-invoice-no="'.$sold_product['invoice_no'].'"><i class="fa fa-sign-out"></i> Return Product?</a></td>';
			$html.='</tr>';
		}
		echo $html;
	}

	public function getProductPrice()
	{
		$product_name_id = $this->input->post('product_name_id');
		$purchaser_student_id = $this->input->post('purchaser_student_id');
		$this->db->limit(1);
		$myProduct = $this->db->get_where('products',array('product_name_id'=>$product_name_id,'saleable'=>1,'sold'=>0,'consume'=>0,'status'=>1))->result_array();

		//CHECK FREE PRODUCT
		if($purchaser_student_id!='' && $product_name_id!='')
		{
			//CHECK STUDENT ALREADY CLAIM FREE PRODUCT
			$claim = $this->db->get_where('products',array('student_id'=>$purchaser_student_id,'product_name_id'=>$product_name_id,'sold_amount'=>0))->result_array();

			if(count($claim)>0)
			{
				echo $myProduct[0]['sale_amount'];
			}
			else
			{
				$student_details = $this->db->get_where('students',array('student_id'=>$purchaser_student_id))->result_array();
				if($student_details[0]['status']==1)
				{
					$student_class_id = $student_details[0]['class_id'];
					$this->db->select('*');
					$this->db->from('free_item_rules');
					$this->db->where("find_in_set($student_class_id, class_ids)");
					$this->db->where("find_in_set($product_name_id, product_name_ids)");
					$this->db->where('till_date>=',date('Y-m-d'));
					$free_check = $this->db->get()->result_array();
					if(count($free_check)>0 && $student_details[0]['registration_date']>=$free_check[0]['student_admission_date'])
					{
						echo '0';
					}
					else
					{
						echo $myProduct[0]['sale_amount'];
					}
				}
				else
				{
					echo $myProduct[0]['sale_amount'];
				}
			}
		}
		else
		{
			echo $myProduct[0]['sale_amount'];
		}
	}

	public function getCourses()
	{
		$campus_id = $this->input->post('campus_id');

		$courses = $this->db->get_where('courses', array('campus_ids like'=>'%'.$campus_id.'%'))->result_array();
        $html='';
        $html.='<option value="">SELECT COURSE</option>';
        foreach($courses as $course)
        {
            $html.= '<option value="'.$course['course_id'].'">'.$course['course_name'].'</option>';
        }
        echo $html;
        exit;
	}

	public function getClasses()
	{
		$campus_id = $this->input->post('campus_id');
		$course_id = $this->input->post('course_id');

		$classes = $this->db->get_where('classes',array('campus_id'=>$campus_id,'course_id'=>$course_id,'status'=>1))->result_array();
		$html='';
        $html.='<option value="">SELECT CLASS</option>';
        foreach($classes as $class)
        {
            $html.= '<option value="'.$class['class_id'].'">'.$class['name'].'</option>';
        }
        echo $html;
        exit;
	}

	public function getStudents()
	{
		$campus_id = $this->input->post('campus_id');
		$course_id = $this->input->post('course_id');
		$class_id = $this->input->post('class_id');

		$students = $this->db->get_where('students',array('class_id'=>$class_id,'course_id'=>$course_id,'status'=>1))->result_array();
		$html='';
        $html.='<option value="">SELECT STUDENT</option>';
        foreach($students as $student)
        {
            $html.= '<option value="'.$student['student_id'].'">'.$student['first_name'].' '.$student['last_name'].' ('.$student['roll_no'].')</option>';
        }
        echo $html;
        exit;
	}

	public function free_products()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('pos/free_products', $data);
		$this->load->view('inc/footer');
	}

	public function getOrderDetails()
	{
		$invoice_no = $this->input->post('invoice_no');
		$this->db->select('products.*,product_names.*,count(products.product_id) as quantity, sum(products.sold_amount) as total_sold_price');
		$this->db->from('products');
		$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
		$this->db->where(array('products.invoice_no'=>$invoice_no,'products.return_status'=>0));
		$this->db->group_by('products.product_name_id');
		$products = $this->db->get()->result_array();

		$html = '';
		$i=1;
		foreach($products as $product)
		{
			$html.='<tr>';
			$html.='<td>'.$i.'</td>';
			$html.='<td>'.$product['product_name'].'</td>';
			$html.='<td>'.$product['quantity'].'</td>';
			$html.='<td>'.$product['total_sold_price'].'</td>';
			$html.='<td>
						<input type="number" class="form-control input-small" name="return_quantity[]" min="0" max="'.$product['quantity'].'" value="0" required />
						<input type="hidden" name="product_name_id[]" value="'.$product['product_name_id'].'" />
					</td>';
			$html.='</tr>';
			$i++;
		}
		echo $html;
	}

	public function returnItems()
	{
		$return_quantity = $this->input->post('return_quantity');
		$product_name_id = $this->input->post('product_name_id');
		$invoice_no = $this->input->post('invoice_no');
		
		$counter = count($return_quantity);

		for($i=0; $i<$counter; $i++)
		{
			if($return_quantity[$i]!=0)
			{
				for($a=1;$a<=$return_quantity[$i];$a++)
				{
					$this->db->select('products.*,product_names.product_name');
					$this->db->from('products');
					$this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
					$this->db->limit(1);
					$this->db->where(array('products.invoice_no'=>$invoice_no,'products.product_name_id'=>$product_name_id[$i],'products.return_status'=>0));
					$product = $this->db->get()->result_array();

					$myPettyCash =  my_pettycash();

					if($myPettyCash>=$product[0]['sold_amount'])
					{
						//UPDATE PRODUCT RETURN STATUS
						$this->db->set('return_status',1);
						$this->db->set('return_by',$this->session->userdata('user_id'));
						$this->db->where('product_id',$product[0]['product_id']);
						$this->db->update('products');

						//ADD EXPENSE AGAINST RETURN PRODUCT
						$purpose = 'Product ('.$product[0]['product_name'].') Return To '.$product[0]['purchaser_name'].' '.$product[0]['purchaser_phone'].' Purchased on '.$product[0]['sold_date'].'';
						$expense_category_id = $this->db->get_where('default_return_category_inventory',array('id'=>1))->row()->expense_category_id;
						$this->db->set('date',date('Y-m-d'));
						$this->db->set('actual_date',date('Y-m-d H:i:s'));
						$this->db->set('purpose',$purpose);
						$this->db->set('title','Product Return');
						$this->db->set('amount',$product[0]['sold_amount']);
						$this->db->set('add_by',$this->session->userdata('name'));
						$this->db->set('add_by_id',$this->session->userdata('user_id'));
						$this->db->set('last_edit',$this->session->userdata('name'));
						$this->db->set('expense_category_id',$expense_category_id);
						$this->db->set('campus_id',$product[0]['campus_id']);
						$this->db->set('payment_type','cash');
						$this->db->set('paid_type','cash');
						$this->db->set('approved_status',1);
						$this->db->insert('expenses');

						//ADD PRODUCT AGAIN
						$this->db->set('campus_id',$product[0]['campus_id']);
						$this->db->set('room_id',$product[0]['room_id']);
						$this->db->set('subroom_id',$product[0]['subroom_id']);
						$this->db->set('product_name_id',$product[0]['product_name_id']);
						$this->db->set('product_image',$product[0]['product_image']);
						$this->db->set('online_product_image',$product[0]['online_product_image']);
						$this->db->set('purchase_slip',$product[0]['purchase_slip']);
						$this->db->set('online_purchase_slip',$product[0]['online_purchase_slip']);
						$this->db->set('product_quantity',$product[0]['product_quantity']);
						$this->db->set('remaining_quantity',$product[0]['remaining_quantity']);
						$this->db->set('qr_code',$product[0]['qr_code']);
						$this->db->set('estimated_price',$product[0]['estimated_price']);
						$this->db->set('product_guarantee',$product[0]['product_guarantee']);
						$this->db->set('product_guarantee_start_date',$product[0]['product_guarantee_start_date']);
						$this->db->set('product_guarantee_end_date',$product[0]['product_guarantee_end_date']);
						$this->db->set('remarks',$product[0]['remarks']);
						$this->db->set('date',$product[0]['date']);
						$this->db->set('add_by',$product[0]['add_by']);
						$this->db->set('last_edit',$product[0]['last_edit']);
						$this->db->set('clear_by',$product[0]['clear_by']);
						$this->db->set('status',$product[0]['status']);
						$this->db->set('consumeable',$product[0]['consumeable']);
						$this->db->set('consume',$product[0]['consume']);
						$this->db->set('consume_reason',$product[0]['consume_reason']);
						$this->db->set('consume_date',$product[0]['consume_date']);
						$this->db->set('sale_quantity',$product[0]['sale_quantity']);
						$this->db->set('move_quantity',$product[0]['move_quantity']);
						$this->db->set('upload_image',$product[0]['upload_image']);
						$this->db->set('delete_image',$product[0]['delete_image']);
						$this->db->set('po_no',$product[0]['po_no']);
						$this->db->set('grn_no',$product[0]['grn_no']);
						$this->db->set('sale_id',$product[0]['sale_id']);
						$this->db->set('user_id',$product[0]['user_id']);
						$this->db->set('reponsilble_user_id',$product[0]['reponsilble_user_id']);
						$this->db->set('saleable',$product[0]['saleable']);
						$this->db->set('sale_amount',$product[0]['sale_amount']);
						$this->db->set('expire',$product[0]['expire']);
						$this->db->set('expire_date',$product[0]['expire_date']);
						$this->db->set('returnable',$product[0]['returnable']);
						$this->db->set('sold_amount','');
						$this->db->set('sold',0);
						$this->db->set('invoice_no','');
						$this->db->set('sold_date','');
						$this->db->set('sold_by',0);
						$this->db->set('student_id',0);
						$this->db->set('purchaser_name','');
						$this->db->set('purchaser_phone','');
						$this->db->set('purchase_no','');
						$this->db->set('return_status',0);
						$this->db->set('return_by',0);
						$this->db->insert('products');
					}
					else
					{
						$this->session->set_flashdata('error','There is not enough amount in your Petty Cash.');
						redirect('pos');
					}
				}
			}
		}
		$this->session->set_flashdata('message','Product Returned Successfully.');
		redirect('pos');
	}
}
