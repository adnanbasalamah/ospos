<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class purchase_order extends Secure_Controller
{
	public function __construct()
	{
		//error_reporting(E_ERROR | E_WARNING | E_PARSE);
		parent::__construct('purchase_order');

		$this->load->library('purchase_order_lib');		
		$this->load->library('token_lib');
		$this->load->library('barcode_lib');
		$this->load->model("Purchaseorder");
	}

	public function index()
	{
		$this->list(); //_reload();
	}

	public function item_search()
	{
		$suggestions = $this->Item->get_search_suggestions($this->input->get('term'), array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE);
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($this->input->get('term')));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}

	
    public function search()
    {
		error_reporting(0);
		
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');

        $filters = array('sale_type' => 'all',
            'location_id' => 'all',
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date')
        );

        // check if any filter is set in the multiselect dropdown
        //$filledup = array_fill_keys($this->input->get('filters'), TRUE);
        //$filters = array_merge($filters, $filledup);

	
        $sales = $this->Purchaseorder->search($search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->Purchaseorder->get_total_rows($search, $filters);

        $data_rows = array();
		$no=1;
		$i=0;
        foreach($sales->result() as $sale)
        {
			
            $data_rows[$i] = $this->xss_clean(get_purchase_order_data_row($sale));
			$data_rows[$i]['no']=$no;
			$i++;
			$no++;
        }

        if($total_rows > 0)
        {
            $data_rows[] = $this->xss_clean(get_purchase_order_data_last_row($sales));
        }
        $payment_summary = '';
        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
    }


	public function stock_item_search()
	{
		$suggestions = $this->Item->get_stock_search_suggestions($this->input->get('term'), array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE);
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($this->input->get('term')));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}

    public function list(){


        $data['table_headers'] = get_po_manage_table_headers();
		$data['filters'] = [];
		
       $this->load->view('purchase_order/manage', $data);
		
	}

	public function new_po(){
		$data['total'] = $this->purchase_order_lib->get_total();
		$data['cart'] = $this->purchase_order_lib->get_cart();
		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);
		$data['comment'] = $this->purchase_order_lib->get_comment();
		$data['print_after_sale'] = $this->purchase_order_lib->is_print_after_sale();
		$data['payment_options'] = $this->Receiving->get_payment_options();
		$data['mode']='po';
		$this->load->view('purchase_order/po',$data) ; 
		
	}
	
	public function detailpo($po_id){
        $data['table_headers'] = get_purchase_order_detail_table_headers();
        $data['po_id'] = $po_id;
        $data['po_number'] = 'PO'.str_pad($po_id,5,'0',STR_PAD_LEFT);
        $po_info = $this->Purchaseorder->get_purchase_order_info_by_id($po_id);
        $data['po_info_suplier'] = $po_info->supplier_name;
        $data['po_info_comment'] = $po_info->comment;
        $data['po_info_date'] = substr($po_info->po_time,0,10);

        $this->load->view('purchase_order/view_detail_po', $data);
    }
	public function get_detail_po($po_id){
		
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
        $filters = [];
        $purchase_order_items = $this->Purchaseorder->search_detail($po_id,$search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->Purchaseorder->get_detail_found_rows($po_id, $search, $filters);
        $data_rows = array();

		$no=1;
		$i=0;
        foreach($purchase_order_items->result() as $po_item)
        {
			
			$data_rows[$i] = $this->xss_clean(get_purchase_order_items_data_row($po_item));
			$data_rows[$i]['no']=$no;
			$i++;
			$no++;

		}

        if($total_rows > 0)
        {
            $data_rows[] = $this->xss_clean(get_purchase_order_items_data_last_row($purchase_order_items));
        }

        $payment_summary = '';
        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
    }

    public function get_row($row_id)
    {
        $po_info = $this->Purchaseorder->get_info($row_id)->row();
        $data_row = $this->xss_clean(get_purchase_order_data_row($po_info));

        echo json_encode($data_row);
    }

	public function select_supplier()
	{
		$supplier_id = $this->input->post('supplier');
		if($this->Supplier->exists($supplier_id))
		{
			$this->purchase_order_lib->set_supplier($supplier_id);
		}

		$this->_reload();
	}

	public function change_mode()
	{
		$stock_destination = $this->input->post('stock_destination');
		$stock_source = $this->input->post('stock_source');

		if((!$stock_source || $stock_source == $this->purchase_order_lib->get_stock_source()) &&
			(!$stock_destination || $stock_destination == $this->purchase_order_lib->get_stock_destination()))
		{
			$this->purchase_order_lib->clear_reference();
			$mode = $this->input->post('mode');
			$this->purchase_order_lib->set_mode($mode);
		}
		elseif($this->Stock_location->is_allowed_location($stock_source, 'receivings'))
		{
			$this->purchase_order_lib->set_stock_source($stock_source);
			$this->purchase_order_lib->set_stock_destination($stock_destination);
		}

		$this->_reload();
	}
	
	public function set_comment()
	{
		$this->purchase_order_lib->set_comment($this->input->post('comment'));
	}

	public function set_print_after_sale()
	{
		$this->purchase_order_lib->set_print_after_sale($this->input->post('recv_print_after_sale'));
	}
	
	public function set_reference()
	{
		$this->purchase_order_lib->set_reference($this->input->post('recv_reference'));
	}
	
	public function add()
	{
		$data = array();

		$mode = $this->purchase_order_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		$quantity = 1;
		$item_data = $this->Item->get_info($item_id_or_number_or_item_kit_or_receipt);
		$price = !empty($item_data->unit_price) ? $item_data->unit_price : 0;
		$this->token_lib->parse_barcode($quantity, $price, $item_id_or_number_or_item_kit_or_receipt);
		$quantity = ($mode == 'receive' || $mode == 'requisition') ? $quantity : -$quantity;
		$item_location = $this->purchase_order_lib->get_stock_source();
		$discount = $this->config->item('default_receivings_discount');
		$discount_type = $this->config->item('default_receivings_discount_type');
		if($mode == 'return' && $this->Purchaseorder->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->purchase_order_lib->return_entire_po($item_id_or_number_or_item_kit_or_receipt);
		}
		elseif($this->Item_kit->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->purchase_order_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt, $item_location, $discount, $discount_type);
		}
		elseif(!$this->purchase_order_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location, $discount,  $discount_type))
		{
			$data['error'] = $this->lang->line('receivings_unable_to_add_item');
		}
		$this->_reload($data);
	}

	public function edit_item($item_id)
	{
		$data = array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|callback_numeric');

		$description = $this->input->post('description');
		$serialnumber = $this->input->post('serialnumber');
		$price = parse_decimals($this->input->post('price'));
		$quantity = parse_quantity($this->input->post('quantity'));
		$discount_type = $this->input->post('discount_type');
		$discount = $discount_type ? parse_quantity($this->input->post('discount')) : parse_decimals($this->input->post('discount'));

		$po_quantity = $this->input->post('po_quantity');

		if($this->form_validation->run() != FALSE)
		{
			$this->purchase_order_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $discount_type, $price, $po_quantity);
		}
		else
		{
			$data['error']=$this->lang->line('receivings_error_editing_item');
		}

		$this->_reload($data);
	}
	
	public function edit($po_id)
	{
		$data = array();

		$data['suppliers'] = array('' => 'No Supplier');
		foreach($this->Supplier->get_all()->result() as $supplier)
		{
			$data['suppliers'][$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
		}
	
		$data['employees'] = array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $this->xss_clean($employee->first_name . ' '. $employee->last_name);
		}
	
		$po_info = $this->xss_clean($this->Purchaseorder->get_info($po_id)->row_array());
		$data['selected_supplier_name'] = !empty($po_info['supplier_id']) ? $po_info['company_name'] : '';
		$data['selected_supplier_id'] = $po_info['supplier_id'];
		$POStatusOption = arr_purchase_order_status();
		$data['po_status'] = $POStatusOption;
		$data['po_info'] = $po_info;

		

		$this->load->view('purchase_order/form', $data);
	}

	public function delete_item($item_number)
	{
		$this->purchase_order_lib->delete_item($item_number);

		$this->_reload();
	}
	
	public function delete($po_id = -1, $update_inventory = TRUE) 
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$po_ids = $po_id == -1 ? $this->input->post('ids') : array($po_id);
	
		if($this->Purchaseorder->delete_list($po_id, $employee_id, $update_inventory))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('receivings_successfully_deleted') . ' ' .
							count($receiving_ids) . ' ' . $this->lang->line('receivings_one_or_multiple'), 'ids' => $receiving_ids));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('receivings_cannot_be_deleted')));
		}
	}

	public function remove_supplier()
	{
		$this->purchase_order_lib->clear_reference();
		$this->purchase_order_lib->remove_supplier();

		$this->_reload();
	}

	public function complete()
	{
		$data = array();
		
		$data['cart'] = $this->purchase_order_lib->get_cart();
		$data['total'] = $this->purchase_order_lib->get_total();
		$data['transaction_time'] = to_datetime(time());
		$data['mode'] = $this->purchase_order_lib->get_mode();
		$data['comment'] = $this->purchase_order_lib->get_comment();
		$data['reference'] = $this->purchase_order_lib->get_reference();
		$data['payment_type'] = $this->input->post('payment_type');
		$data['show_stock_locations'] = 0;//$this->Stock_location->show_locations('receivings');
		//$data['stock_location'] = $this->purchase_order_lib->get_stock_source();
		if($this->input->post('amount_tendered') != NULL)
		{
			$data['amount_tendered'] = $this->input->post('amount_tendered');
			$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
		}
		
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_info = '';
		$supplier_id = $this->purchase_order_lib->get_supplier();
		if($supplier_id != -1)
		{
			$supplier_info = $this->Supplier->get_info($supplier_id);
			$data['po_status'] = '0';
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) or !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;				
			}
			else
			{
				$data['supplier_location'] = '';
			}
		}
	
		//SAVE receiving to database
		//print_r($data);		
		if (!isset($data['stock_location'])){
			$data['stock_location'] = 0;
		}
		if (!isset($data['po_status'])){
			$data['po_status'] = 0;
		}
		$data['po_id'] = 'PO ' . $this->Purchaseorder->save($data['cart'], $supplier_id, $employee_id, $data['comment'], $data['total'], $data['reference'], $data['payment_type'], $data['stock_location'], $data['po_status']);

		$data = $this->xss_clean($data);

		if($data['po_id'] == 'RECV -1')
		{
			$data['error_message'] = $this->lang->line('receivings_transaction_failed');
		}
		else
		{
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['po_id']);				
		}

		$data['print_after_sale'] = $this->purchase_order_lib->is_print_after_sale();
		//	echo "<pre>";
		//	print_r($data);
		//	die();
		$this->load->view("purchase_order/receipt",$data);

		$this->purchase_order_lib->clear_all();
	}

	public function requisition_complete()
	{
		if($this->purchase_order_lib->get_stock_source() != $this->purchase_order_lib->get_stock_destination()) 
		{
			foreach($this->purchase_order_lib->get_cart() as $item)
			{
				$this->purchase_order_lib->delete_item($item['line']);
				$this->purchase_order_lib->add_item($item['item_id'], $item['quantity'], $this->purchase_order_lib->get_stock_destination(), $item['discount_type']);
				$this->purchase_order_lib->add_item($item['item_id'], -$item['quantity'], $this->purchase_order_lib->get_stock_source(), $item['discount_type']);
			}
			
			$this->complete();
		}
		else 
		{
			$data['error'] = $this->lang->line('receivings_error_requisition');

			$this->_reload($data);	
		}
	}
	
	public function receipt($po_id)
	{
		$po_info = $this->purchase_order->get_info($po_id)->row_array();
		$this->purchase_order_lib->copy_entire_receiving($po_id);
		$data['cart'] = $this->purchase_order_lib->get_cart();
		$data['total'] = $this->purchase_order_lib->get_total();
		$data['mode'] = $this->purchase_order_lib->get_mode();
		$data['transaction_time'] = to_datetime(strtotime($po_info['receiving_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['payment_type'] = $po_info['payment_type'];
		$data['reference'] = $this->purchase_order_lib->get_reference();
		$data['po_id'] = 'PO ' . $po_id;
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['po_id']);
		$employee_info = $this->Employee->get_info($po_info['employee_id']);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_id = $this->purchase_order_lib->get_supplier();
		if($supplier_id != -1)
		{
			$supplier_info = $this->Supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) or !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;				
			}
			else
			{
				$data['supplier_location'] = '';
			}
		}

		$data['print_after_sale'] = FALSE;

		$data = $this->xss_clean($data);
		
		$this->load->view("purchase_order/receipt", $data);

		$this->purchase_order_lib->clear_all();
	}

	private function _reload($data = array())
	{
		$data['cart'] = $this->purchase_order_lib->get_cart();
		$data['modes'] = array('purchase_order' => $this->lang->line('purchase_order'), 'return' => $this->lang->line('receivings_return'));
		$data['mode'] = $this->purchase_order_lib->get_mode();
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('receivings');
		$data['show_stock_locations'] = count($data['stock_locations']) > 1;
		if($data['show_stock_locations']) 
		{
			$data['modes']['requisition'] = $this->lang->line('receivings_requisition');
			$data['stock_source'] = $this->purchase_order_lib->get_stock_source();
			$data['stock_destination'] = $this->purchase_order_lib->get_stock_destination();
		}

		$data['total'] = $this->purchase_order_lib->get_total();
		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);
		$data['comment'] = $this->purchase_order_lib->get_comment();
		$data['reference'] = $this->purchase_order_lib->get_reference();
		$data['payment_options'] = $this->Receiving->get_payment_options();

		$supplier_id = $this->purchase_order_lib->get_supplier();
		$supplier_info = '';
		if($supplier_id != -1)
		{
			$supplier_info = $this->Supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) or !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;				
			}
			else
			{
				$data['supplier_location'] = '';
			}
		}
		
		$data['print_after_sale'] = $this->purchase_order_lib->is_print_after_sale();

		$data = $this->xss_clean($data);
		$this->load->view("purchase_order/po", $data);
	}
	
	public function save($po_id = -1)
	{
		$newdate = $this->input->post('date');
		
		$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);
		$po_time = $date_formatter->format('Y-m-d H:i:s');
		//$po_status=0;

		$po_data = array(
			'po_time' => $po_time,
			'po_status' => $this->input->post('po_status'),
			'supplier_id' => $this->input->post('supplier_id') ? $this->input->post('supplier_id') : NULL,
			'employee_id' => $this->input->post('employee_id'),
		//	'total_order' => $this->purchase_order_lib->get_total(),
			'comment' => $this->input->post('comment'),
			'reference' => $this->input->post('reference') != '' ? $this->input->post('reference') : NULL
		);


		//$this->Inventory->update('RECV '.$receiving_id, ['trans_date' => $po_time]);
		if($this->Purchaseorder->update($po_data, $po_id))
		{

			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('receivings_successfully_updated'), 'id' => $po_id));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('receivings_unsuccessfully_updated'), 'id' => $po_id));
		}
		$this->list();

	}

	public function cancel_receiving()
	{
		$this->purchase_order_lib->clear_all();

		$this->_reload();
	}

	public function saveToReceive()
	{
		/*
		$data = array();
		
		$data['cart'] = $this->receiving_lib->get_cart();
		$data['total'] = $this->receiving_lib->get_total();
		$data['transaction_time'] = to_datetime(time());
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['comment'] = $this->receiving_lib->get_comment();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['payment_type'] = $this->input->post('payment_type');
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['stock_location'] = $this->receiving_lib->get_stock_source();
		if($this->input->post('amount_tendered') != NULL)
		{
			$data['amount_tendered'] = $this->input->post('amount_tendered');
			$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
		}
		
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_info = '';
		$supplier_id = $this->receiving_lib->get_supplier();
		if($supplier_id != -1)
		{
			$supplier_info = $this->Supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) or !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;				
			}
			else
			{
				$data['supplier_location'] = '';
			}
		}

		//SAVE receiving to database
		// save($items, $supplier_id, $employee_id, $comment, $reference, $payment_type, $receiving_id = FALSE)
		$data['receiving_id'] = 'RECV ' . $this->Receiving->save($data['cart'], $supplier_id, $employee_id, $data['comment'], $data['reference'], $data['payment_type'], $data['stock_location']);

		$this->receiving_lib->clear_all();
		*/
	}
	public function purchade_order_print($purchase_order_id)
	{
		$data = $this->_load_purchase_order_data($purchase_order_id);
		$data['cart'] = $this->Purchaseorder->get_purchase_order_items($purchase_order_id)->result();
		$total_order = 0;
		foreach ($data['cart'] as $idx => $cart){
			$qty_item = $cart->quantity_purchased;
			$total_order += $cart->item_cost_price*$qty_item;
		}
		$data['total'] = to_currency($total_order);
		$this->load->view('purchase_order/po_print', $data);
	}

	public function _load_purchase_order_data($purchase_order_id)
	{
		$po_info = $this->Purchaseorder->get_info($purchase_order_id)->row_array();
		$data = array();
		$data['transaction_time'] = to_datetime(strtotime($po_info['delivery_date']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('purchase_order');

		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = 0;//$this->sale_lib->get_totals();
		//$data['subtotal'] = $totals['subtotal'];

		$employee_info = $this->Employee->get_info($po_info['employee_id']);
		$data['employee'] = $employee_info->first_name . ' ' . mb_substr($employee_info->last_name, 0, 1);
		$this->_load_supplier_data($po_info['supplier_id'], $data);
		$data['purchase_order_id_num'] = $purchase_order_id;
		$data['purchase_order_id'] = $purchase_order_id;
		$data['comments'] = $po_info['comment'];
		$data['po_number'] = 'PO ' . str_pad($purchase_order_id,5,'0',STR_PAD_LEFT);
		$data['page_title'] = $this->lang->line('purchase_order');
		$data['transaction_date'] = to_date(strtotime($po_info['po_time']));
		$data['total'] = '-';
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone')
		));
		if($this->config->item('account_number'))
		{
			$data['company_info'] .= "\n" . $this->lang->line('sales_account_number') . ": " . $this->config->item('account_number');
		}
		if($this->config->item('tax_id') != '')
		{
			$data['company_info'] .= "\n" . $this->lang->line('sales_tax_id') . ": " . $this->config->item('tax_id');
		}

		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['po_number']);
		$data['print_after_sale'] = FALSE;
		$data['price_work_orders'] = FALSE;
		return $this->xss_clean($data);
	}

	private function _load_supplier_data($supplier_id, &$data, $stats = FALSE)
	{
		$supplier_info = '';

		if($supplier_id != -1)
		{
			$supplier_info = $this->Supplier->get_info($supplier_id);
			$data['supplier_id'] = $supplier_id;
			if(!empty($supplier_info->company_name))
			{
				$data['supplier'] = $supplier_info->company_name;
			}
			else
			{
				$data['supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
			}
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) || !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city . "\n" . $supplier_info->state;
			}
			else
			{
				$data['supplier_location'] = '';
			}
			$data['supplier_account_number'] = $supplier_info->account_number;

			$data['supplier_info'] = implode("\n", array(
				$data['supplier'],
				$data['supplier_address'],
				$data['supplier_location']
			));

			if($data['supplier_account_number'])
			{
				$data['supplier_info'] .= "\n" . $this->lang->line('sales_account_number') . ": " . $data['supplier_account_number'];
			}
			if($supplier_info->tax_id != '')
			{
				$data['supplier_info'] .= "\n" . $this->lang->line('sales_tax_id') . ": " . $supplier_info->tax_id;
			}
			$data['tax_id'] = $supplier_info->tax_id;
		}

		return $supplier_info;
	}
}

?>
