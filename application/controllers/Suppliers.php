<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Persons.php");

class Suppliers extends Persons
{
	public function __construct()
	{
		parent::__construct('suppliers');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_suppliers_manage_table_headers());

		$this->load->view('people/manage', $data);
	}

	/*
	Gets one row for a supplier manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_supplier_data_row($this->Supplier->get_info($row_id)));
		$data_row['category'] = $this->Supplier->get_category_name($data_row['category']);

		echo json_encode($data_row);
	}
	
	/*
	Returns Supplier table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$suppliers = $this->Supplier->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Supplier->get_found_rows($search);

		$data_rows = array();
		foreach($suppliers->result() as $supplier)
		{
			$row = $this->xss_clean(get_supplier_data_row($supplier));
			$row['category'] = $this->Supplier->get_category_name($row['category']);
			$data_rows[] = $row;
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Supplier->get_search_suggestions($this->input->get('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Supplier->get_search_suggestions($this->input->post('term'), FALSE));

		echo json_encode($suggestions);
	}
	
	/*
	Loads the supplier edit form
	*/
	public function view($supplier_id = -1)
	{
		$info = $this->Supplier->get_info($supplier_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $info;
		$data['categories'] = $this->Supplier->get_categories();

		$this->load->view("suppliers/form", $data);
	}
	
	/*
	Inserts/updates a supplier
	*/
	public function save($supplier_id = -1)
	{
		$first_name = $this->xss_clean($this->input->post('first_name'));
		$last_name = $this->xss_clean($this->input->post('last_name'));
		$email = $this->xss_clean(strtolower($this->input->post('email')));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->input->post('gender'),
			'email' => $email,
			'phone_number' => $this->input->post('phone_number'),
			'address_1' => $this->input->post('address_1'),
			'address_2' => $this->input->post('address_2'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
			'zip' => $this->input->post('zip'),
			'country' => $this->input->post('country'),
			'comments' => $this->input->post('comments')
		);

		$supplier_data = array(
			'company_name' => $this->input->post('company_name'),
			'agency_name' => $this->input->post('agency_name'),
			'category' => $this->input->post('category'),
			'account_number' => $this->input->post('account_number') == '' ? NULL : $this->input->post('account_number'),
			'tax_id' => $this->input->post('tax_id')
		);

		if($this->Supplier->save_supplier($person_data, $supplier_data, $supplier_id))
		{
			$supplier_data = $this->xss_clean($supplier_data);

			//New supplier
			if($supplier_id == -1)
			{
				echo json_encode(array('success' => TRUE,
								'message' => $this->lang->line('suppliers_successful_adding') . ' ' . $supplier_data['company_name'],
								'id' => $supplier_data['person_id']));
			}
			else //Existing supplier
			{
				echo json_encode(array('success' => TRUE,
								'message' => $this->lang->line('suppliers_successful_updating') . ' ' . $supplier_data['company_name'],
								'id' => $supplier_id));
			}
		}
		else//failure
		{
			$supplier_data = $this->xss_clean($supplier_data);

			echo json_encode(array('success' => FALSE,
							'message' => $this->lang->line('suppliers_error_adding_updating') . ' ' . 	$supplier_data['company_name'],
							'id' => -1));
		}
	}
	
	/*
	This deletes suppliers from the suppliers table
	*/
	public function delete()
	{
		$suppliers_to_delete = $this->xss_clean($this->input->post('ids'));

		if($this->Supplier->delete_list($suppliers_to_delete))
		{
			echo json_encode(array('success' => TRUE,'message' => $this->lang->line('suppliers_successful_deleted').' '.
							count($suppliers_to_delete).' '.$this->lang->line('suppliers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE,'message' => $this->lang->line('suppliers_cannot_be_deleted')));
		}
	}

	public function payment(){
		$data = [];
		$data['page_title'] = 'TABEL PEMBAYARAN';
		$data['table_headers'] = get_payment_paid_items_table_headers();
		$this->load->view("suppliers/payment_supplier", $data);
	}

	public function payment_voucher_table(){
		$data = [];
		$data['page_title'] = 'PAYMENT VOUCHER';
		$data['table_headers'] = get_payment_voucher_table_headers();
		$this->load->view("suppliers/payment_voucher_table", $data);
	}

	public function print_pv($voucher_id){
		$data['table_headers'] = get_payment_voucher_detail_table_headers();
		$data['voucher_id'] = $voucher_id;
		$voucher_info = $this->Supplier->get_payment_voucher_info($voucher_id);
		$this->Supplier->update_status_pv($voucher_id);
		$data['voucher_number'] = $voucher_info->voucher_number;
		$data['page_title'] = 'PAYMENT VOUCHER';
		$data['pv_info_supplier'] = $voucher_info->company_name;
		$data['pv_custom_supplier'] = $voucher_info->custom_supplier;
		$PaymentContact = $voucher_info->upto_contact;
		if (empty($voucher_info->upto_contact)){
			$PaymentContact = $voucher_info->first_name .' '.$voucher_info->last_name;
		}
		$data['pv_contact'] = $PaymentContact;
		$data['pv_info_notes'] = $voucher_info->payment_notes;
		$data['pv_info_account_number'] = !empty($voucher_info->pv_account_number) ? $voucher_info->pv_account_number : $voucher_info->account_number;
		$data['pv_info_date'] = substr($voucher_info->payment_date,0,10);
		$this->load->view('suppliers/print_pv', $data);
	}

	public function get_detail_pv($voucher_id){
		$voucher_info = $this->Supplier->get_payment_voucher_info($voucher_id);
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');
		$filters = [];
		$pv_items = $this->Supplier->search_pv_detail($voucher_id,$search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Supplier->get_detail_pv_found_rows($voucher_id, $search, $filters);
		$data_rows = array();
		$counter = 1;
		foreach($pv_items->result() as $pv_item)
		{
			$data_rows[] = $this->xss_clean(get_pv_items_data_row($pv_item, $voucher_info->voucher_number, $counter));
			$counter++;
		}

		if($total_rows > 0)
		{
			$data_rows[] = $this->xss_clean(get_pv_items_data_row_last_row($pv_items));
		}
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	public function payment_voucher()
	{
		$Month = ['JAN','FEB','MAC','APR','MEI','JUN','JUL','OGOS','SEPT','OCT','NOV','DIS'];
		$PaymentCash = 0;
		$ExpenseData = [];
		if (!empty($_GET['supplier_id'])){
			$SupplierId = $_GET['supplier_id'];
			$SupplierData = $this->Supplier->get_info($SupplierId);
		}else if(!empty($_GET['expense_id'])){
			$PaymentCash = 1;
			$ExpenseId = $_GET['expense_id'];
			$EmployeeContact = '';
			if (is_array($ExpenseId)){
				$ExpenseData = $this->Expense->get_multiple_info($ExpenseId)->result();
				$ArrEmployee = [];
				foreach ($ExpenseId as $Idexpense){
					$DataEmployee = $this->Expense->get_employee($Idexpense);
					if (!in_array($DataEmployee->person_id, $ArrEmployee)){
						$ArrEmployee[] = $DataEmployee->person_id;
						if ($EmployeeContact != '') {
							$EmployeeContact .= ', '.$DataEmployee->first_name . ' ' . $DataEmployee->last_name;
						}else{
							$EmployeeContact .= $DataEmployee->first_name . ' ' . $DataEmployee->last_name;
						}
					}
				}
				$SupplierId = $ExpenseData[0]->supplier_id;
				$SupplierData = $this->Supplier->get_info($SupplierId);
			}else{
				$ExpenseData = $this->Expense->get_info($ExpenseId)->result();
				$DataEmployee = $this->Expense->get_employee($ExpenseId);
				$EmployeeContact .= $DataEmployee->first_name . ' ' . $DataEmployee->last_name;
				$SupplierId = $ExpenseData->supplier_id;
				$SupplierData = $this->Supplier->get_info($SupplierId);
			}
		}
		$StartDate = $_GET['start_date'];
		$StartMonth = date('n', strtotime($StartDate));
		$EndDate = $_GET['end_date'];
		$EndMonth = date('n', strtotime($EndDate));
		if (!$PaymentCash) {
			if ($StartMonth == $EndMonth) {
				$PVNotes = 'JUALAN TUNAI ' . date('j', strtotime($StartDate)) . ' - ' . date('j', strtotime($EndDate)) . 'HB ' . $Month[$EndMonth];
			} else {
				$PVNotes = 'JUALAN TUNAI ' . date('j', strtotime($StartDate)) . 'HB ' . $Month[$StartMonth] . ' - ' . date('j', strtotime($EndDate)) . 'HB ' . $Month[$EndMonth];
			}
		}else{
			$PVNotes = '';
		}
		$PaymentValue = $_GET['payment'];
		$PvArr = ['online', 'cash'];
		$data = [];
		$data['page_title'] = 'PAYMENT VOUCHER';
		$data['payment_date'] = date('Y-m-d H:i:s');
		$data['payment_type_selected'] = $PaymentCash;
		if (!$PaymentCash) {
			$data['selected_supplier_id'] = $SupplierId;
			$data['selected_supplier_name'] = $SupplierData->company_name;
			$data['supplier_contact'] = $SupplierData->first_name . ' ' . $SupplierData->last_name;
			$data['account_number'] = $SupplierData->account_number;
		}else{
			$data['account_number'] = 'CASH';
			if (!empty($SupplierId)) {
				$data['selected_supplier_id'] = $SupplierId;
				$data['selected_supplier_name'] = $SupplierData->company_name;
				$data['supplier_contact'] = $SupplierData->first_name . ' ' . $SupplierData->last_name;
				$data['account_number'] = $SupplierData->account_number;
			}
			$data['selected_employee_id'] = $ArrEmployee[0];
			$data['employee_contact'] = $EmployeeContact;
			$data['expense_data'] = $ExpenseData;
		}
		$pv_last_number = $this->Supplier->get_pv_last_id();
		if (empty($pv_last_number)){
			$pv_last_number = 93;
		}else{
			$pv_last_number = $pv_last_number + 93;
		}
		$data['voucher_number'] = 'PV'.str_pad($pv_last_number,4,'0',STR_PAD_LEFT);
		$data['voucher_notes'] = $PVNotes;
		$data['voucher_value'] = $PaymentValue;
		$data['pv_type_option'] = $PvArr;
		$this->load->view("suppliers/voucher_form", $data);
	}

	public function voucher_save($payment_voucher_id = null){
		$voucher_type = $this->xss_clean($this->input->post('voucher_type'));
		if (!$voucher_type){
			$supplier_id = $this->xss_clean($this->input->post('supplier_id'));
		}
		$custom_supplier = $this->xss_clean($this->input->post('custom_supplier'));
		$voucher_number = $this->xss_clean($this->input->post('voucher_number'));
		$payment_notes = $this->xss_clean(nl2br($this->input->post('voucher_notes')));
		$voucher_value = $this->xss_clean($this->input->post('voucher_value'));
		$account_number = $this->xss_clean($this->input->post('account_number'));
		$upto_contact = $this->xss_clean($this->input->post('voucher_up_to'));
		$pv_item = $this->xss_clean($this->input->post('pv_item'));
		$pv_value = $this->xss_clean($this->input->post('pv_value'));
		$newdate = $this->input->post('date');
		$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);
		$payment_date = $date_formatter->format('Y-m-d H:i:s');
		$login_user = $this->Employee->get_logged_in_employee_info();
		$user_id = $login_user->person_id;
		$PaymentVoucher = [
			'voucher_number' => $voucher_number,
			'supplier_id' => $supplier_id,
			'custom_supplier' => $custom_supplier,
			'upto_contact' => $upto_contact,
			'payment_date' => $payment_date,
			'payment_notes' => $payment_notes,
			'created' => time(),
			'user_id' => $user_id,
			'voucher_type' => $voucher_type,
			'account_number' => $account_number
		];
		if (!empty($pv_item)){
			$PaymentVoucherDetail = [];
			for ($i = 0;$i < count($pv_item);$i++){
				$DetailData = [
					'voucher_item' => $pv_item[$i],
					'voucher_value' => !empty($pv_value[$i]) ? $pv_value[$i] : 0
				];
				$PaymentVoucherDetail[] = $DetailData;
			}
		}else {
			$PaymentVoucherDetail = [
				'voucher_item' => $payment_notes,
				'voucher_value' => $voucher_value
			];
		}
		$VoucherId = $this->Supplier->save_voucher($PaymentVoucher, $PaymentVoucherDetail, $payment_voucher_id);
		if ($VoucherId != -1) {
			echo json_encode(array('success' => TRUE,'message' => $this->lang->line('payment_voucher_successful_updating')));
		}else{
			echo json_encode(array('success' => FALSE,'message' => $this->lang->line('payment_voucher_error_adding_updating')));
		}
	}
	public function search_paid_items_supp(){
		$search  = $this->input->get('search');
		$limit   = $this->input->get('limit');
		$offset  = $this->input->get('offset');
		$sort    = $this->input->get('sort');
		$order   = $this->input->get('order');
		$filters = $this->input->get('filters');
		$supplier_id = $this->input->get('supplier_id');
		$filters = array(
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
		);

		$items_paid = $this->Sale->get_paid_sales_by_items($search, $filters, $limit, $offset, $sort, $order, FALSE, $supplier_id);
		$total_rows = $this->Sale->get_paid_sales_by_items_found_rows($search, $filters);

		$data_rows = array();
		$supplier_id_grup = 0;
		$counter = 0;
		foreach($items_paid->result() as $item_paid)
		{
			if ($counter == 0){
				$supplier_id_grup = $item_paid->supplier_id;
				$subtotal = 0;
				$subtotal_margin = 0;
			}else{
				if ($item_paid->supplier_id != $supplier_id_grup){
					$subtotal_data = new stdClass();
					$subtotal_data->total_payment = $subtotal;
					$subtotal_data->total_margin = $subtotal_margin;
					$data_rows[] = $this->xss_clean(get_paid_sale_item_subtotal_row($subtotal_data));
					$supplier_id_grup = $item_paid->supplier_id;
					$subtotal = 0;
					$subtotal_margin = 0;
				}
			}
			$data_rows[] = $this->xss_clean(get_paid_sale_item_data_row($item_paid));
			$subtotal += $item_paid->total_payment;
			$subtotal_margin += $item_paid->total_margin;
			$counter++;
			if ($counter >= $total_rows){
				$subtotal_data = new stdClass();
				$subtotal_data->total_payment = $subtotal;
				$subtotal_data->total_margin = $subtotal_margin;
				$data_rows[] = $this->xss_clean(get_paid_sale_item_subtotal_row($subtotal_data));
			}
		}
		if($total_rows > 0)
		{
			$data_rows[] = $this->xss_clean(get_paid_sale_item_data_last_row($items_paid));
		}
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	public function search_voucher(){
		$search  = $this->input->get('search');
		$limit   = $this->input->get('limit');
		$offset  = $this->input->get('offset');
		$sort    = $this->input->get('sort');
		$order   = $this->input->get('order');
		$supplier_id = $this->input->get('supplier_id');
		$filters = array(
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
		);
		$payment_data = $this->Supplier->search_payment_voucher($search, $filters, $limit, $offset, $sort, $order, FALSE, $supplier_id);
		$total_rows = $this->Supplier->search_payment_voucher_found_row($search, $filters);
		$data_rows = array();
		foreach($payment_data->result() as $payment)
		{
			$data_rows[] = $this->xss_clean(get_payment_voucher_data_row($payment));
		}
		if($total_rows > 0)
		{
			$data_rows[] = $this->xss_clean(get_payment_voucher_data_last_row($payment_data));
		}
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	public function update_status_paid($voucher_id){
		if (!empty($voucher_id)){
			$return_data = $this->Supplier->update_status_pv($voucher_id,2);
			if ($return_data){
				echo json_encode(array('status' => 'Update Status BERHASIL'));
			}else{
				echo json_encode(array('status' => 'Update Status GAGAL'));
			}
		}else{
			echo json_encode(array('status' => 'Update Status GAGAL'));
		}
	}
	public function delete_voucher(){
		$selected_pv = $this->xss_clean($this->input->post('pv_id'));
		if (!empty($selected_pv)){
			$UpdatedStatus = [];
			for ($i = 0;$i < count($selected_pv);$i++){
				$ReturnUpdate = $this->Supplier->update_status_pv($selected_pv[$i], 3);
				if ($ReturnUpdate){
					$UpdatedStatus[] = $ReturnUpdate;
				}
			}
			if (count($UpdatedStatus)){
				echo json_encode(array('status' => 'Update Status BERHASIL'));
			}else{
				echo json_encode(array('status' => 'Update Status GAGAL'));
			}
		}else{
			echo json_encode(array('status' => 'Update Status GAGAL, Tidak ada PV yang dipilih..!!!'));
		}
	}
}
?>
