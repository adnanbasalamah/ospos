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

	function search_paid_items_supp(){
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

}
?>
