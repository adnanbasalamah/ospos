<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tabular views helper
 */

/*
Basic tabular headers function
*/
function transform_headers_readonly($array)
{
	$result = array();

	foreach($array as $key => $value)
	{
		$result[] = array('field' => $key, 'title' => $value, 'sortable' => $value != '', 'switchable' => !preg_match('(^$|&nbsp)', $value));
	}

	return json_encode($result);
}

/*
Basic tabular headers function
*/
function transform_headers($array, $readonly = FALSE, $editable = TRUE)
{
	$result = array();

	if(!$readonly)
	{
		$array = array_merge(array(array('checkbox' => 'select', 'sortable' => FALSE)), $array);
	}

	if($editable)
	{
		$array[] = array('edit' => '');
	}

	foreach($array as $element)
	{
		reset($element);
		$result[] = array('field' => key($element),
			'title' => current($element),
			'switchable' => isset($element['switchable']) ? $element['switchable'] : !preg_match('(^$|&nbsp)', current($element)),
			'escape' => !preg_match("/(edit|phone_number|email|messages|item_pic)/", key($element)) && !(isset($element['escape']) && !$element['escape']),
			'sortable' => isset($element['sortable']) ? $element['sortable'] : current($element) != '',
			'checkbox' => isset($element['checkbox']) ? $element['checkbox'] : FALSE,
			'class' => isset($element['checkbox']) || preg_match('(^$|&nbsp)', current($element)) ? 'print_hide' : '',
			'sorter' => isset($element['sorter']) ? $element ['sorter'] : '');
	}

	return json_encode($result);
}


/*
Get the header for the sales tabular view
*/
function get_sales_manage_table_headers_original()
{
	$CI =& get_instance();

	$headers = array(
		array('sale_id' => $CI->lang->line('common_id')),
		array('sale_time' => $CI->lang->line('sales_sale_time')),
		array('customer_name' => $CI->lang->line('customers_customer')),
		array('amount_due' => $CI->lang->line('sales_amount_due')),
		array('amount_tendered' => $CI->lang->line('sales_amount_tendered')),
		array('change_due' => $CI->lang->line('sales_change_due')),
		array('payment_type' => $CI->lang->line('sales_payment_type')),
		array('payment_status' => $CI->lang->line('sales_payment_status'), 'escape' => FALSE )

	);

	if($CI->config->item('invoice_enable') == TRUE)
	{
		$headers[] = array('invoice_number' => $CI->lang->line('sales_invoice_number'));
		$headers[] = array('invoice' => '&nbsp', 'sortable' => FALSE, 'escape' => FALSE);
	}

	$headers[] = array('receipt' => '&nbsp', 'sortable' => FALSE, 'escape' => FALSE);

	return transform_headers($headers);
}

function get_sales_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('sale_id' => $CI->lang->line('common_id')),
		array('sale_time' => $CI->lang->line('sales_sale_time')),
	);
	if($CI->config->item('invoice_enable') == TRUE)
	{
		$headers[] = array('invoice_number' => $CI->lang->line('sales_invoice_number'));
	}
	$headers[] = array('customer_name' => $CI->lang->line('customers_customer'));
	$headers[] = array('total_order' => $CI->lang->line('sales_order_total'));
	$headers[] = array('total_paid' => $CI->lang->line('sales_total_paid'));
	$headers[] = array('total_unpaid' => $CI->lang->line('sales_total_unpaid'));
	$headers[] = array('payment_type' => $CI->lang->line('sales_payment_type'));
	$headers[] = array('payment_status' => $CI->lang->line('sales_payment_status'), 'escape' => FALSE );
	if($CI->config->item('invoice_enable') == TRUE)
	{
		$headers[] = array('invoice' => '&nbsp', 'sortable' => FALSE, 'escape' => FALSE);
	}
	$headers[] = array('receipt' => '&nbsp', 'sortable' => FALSE, 'escape' => FALSE);

	return transform_headers($headers);
}

/*
Get the header for the sales order tabular view
*/
function get_sales_order_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('sale_order_id' => $CI->lang->line('common_id')),
		array('sale_time' => $CI->lang->line('sales_sale_time')),
		array('customer_name' => $CI->lang->line('customers_customer')),
		array('company_name' => $CI->lang->line('sales_company_name')),
		array('employee_name' => $CI->lang->line('common_sales')),
		array('delivery_date' => $CI->lang->line('sales_order_delivery_date')),
		array('order_status' => $CI->lang->line('sales_order_status'), 'escape' => FALSE),
		array('total_order' => $CI->lang->line('sales_order_total')),
		array('view_detail' => 'Detail', 'escape' => FALSE),
		array('print_so' => 'Print', 'escape' => FALSE)
	);
	return transform_headers($headers);
}

function get_sales_order_detail_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('item_id' => $CI->lang->line('common_id')),
		array('item_number' => $CI->lang->line('items_item_number')),
		array('name' => $CI->lang->line('items_item')),
		array('items_quantity' => $CI->lang->line('items_quantity')),
		array('items_unit_price' => $CI->lang->line('items_unit_price')),
		array('subtotal_order' => $CI->lang->line('sales_sub_total')),
	);
	return transform_headers($headers);
}
/*
Get the html data row for the sales
*/
function get_sale_data_row($sale)
{
	$CI =& get_instance();

	$controller_name = $CI->uri->segment(1);
	$arr_payment_status = arr_sale_payment_status();
	$arr_status_color = array_payment_status_color();
	$row = array (
		'sale_id' => $sale->sale_id,
		'sale_time' => to_datetime(strtotime($sale->sale_time))
	);
	if($CI->config->item('invoice_enable'))
	{
		$row['invoice_number'] = $sale->invoice_number;
	}
	$row['customer_name'] = $sale->customer_name;
	$row['total_order'] = to_currency($sale->total_order);
	$row['total_paid'] = to_currency($sale->total_paid);
	$row['total_unpaid'] = to_currency($sale->total_unpaid);
	$row['payment_type'] = $sale->payment_type;
	$row['payment_status'] = '<div class="btn btn-xs btn-block '.$arr_status_color[$sale->payment_status].'">'.$arr_payment_status[$sale->payment_status].'</div>';
	if($CI->config->item('invoice_enable'))
	{
		$row['invoice'] = empty($sale->invoice_number) ? '' : anchor($controller_name."/invoice/$sale->sale_id", '<span class="glyphicon glyphicon-list-alt"></span>',
			array('title'=>$CI->lang->line('sales_show_sales_order_detail'))
		);
	}
	$row['receipt'] = anchor($controller_name."/receipt/$sale->sale_id", '<span class="glyphicon glyphicon-usd"></span>',
		array('title' => $CI->lang->line('sales_show_receipt'))
	);
	$row['edit'] = anchor($controller_name."/edit/$sale->sale_id", '<span class="glyphicon glyphicon-edit"></span>',
		array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $CI->lang->line('common_delete'), 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_update'))
	);
	return $row;
}

function get_sale_order_data_row($sale)
{
	$CI =& get_instance();

	$controller_name = $CI->uri->segment(1);

	$sales_order_status = arr_sales_order_status();
	$so_status_color = array_status_color();

	$row = array (
		'sale_order_id' => $sale->sale_order_id,
		'sale_time' => to_datetime(strtotime($sale->sale_time)),
		'customer_name' => $sale->customer_name,
		'company_name' => $sale->company_name,
		'employee_name' => $sale->employee_name,
		'delivery_date' => $sale->delivery_date,
		'order_status' => '<div class="btn btn-xs btn-block '.$so_status_color[$sale->sale_status].'">'.$sales_order_status[$sale->sale_status].'</div>',
		'total_order' => to_currency($sale->total_order),
	);
	$row['view_detail'] = anchor(
		$controller_name."/detailso/$sale->sale_order_id",
		'<span class="glyphicon glyphicon-list-alt"></span>',
		array('title'=>$CI->lang->line('sales_show_invoice'))
	);
	if ($sale->sale_status == 2){
		$row['print_so'] = anchor(
			$controller_name . "/sales_order_print/$sale->sale_order_id",
			'<span class="glyphicon glyphicon-print"></span>',
			array('title' => $CI->lang->line('delivery_order_print'))
		);
	}else {
		$row['print_so'] = anchor(
			$controller_name . "/sales_order_print/$sale->sale_order_id",
			'<span class="glyphicon glyphicon-print"></span>',
			array('title' => $CI->lang->line('sales_order_print'))
		);
	}
	if($CI->config->item('invoice_enable'))
	{
		$row['invoice_number'] = $sale->invoice_number;
		$row['invoice'] = empty($sale->invoice_number) ? '' : anchor($controller_name."/invoice/$sale->sale_order_id", '<span class="glyphicon glyphicon-list-alt"></span>',
			array('title'=>$CI->lang->line('sales_show_invoice'))
		);
	}

	$row['receipt'] = anchor($controller_name."/receipt/$sale->sale_order_id", '<span class="glyphicon glyphicon-usd"></span>',
		array('title' => $CI->lang->line('sales_show_receipt'))
	);
	$row['edit'] = anchor($controller_name."/edit/$sale->sale_order_id", '<span class="glyphicon glyphicon-edit"></span>',
		array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $CI->lang->line('common_delete'), 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_update'))
	);

	return $row;
}

function get_sale_order_items_data_row($so_item)
{
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);
	$row = array(
		'item_id' => $so_item->item_id,
		'item_number' => $so_item->item_number,
		'name' => $so_item->name,
		'items_quantity' => to_quantity_decimals($so_item->quantity_purchased),
		'items_unit_price' => to_currency($so_item->item_unit_price),
		'subtotal_order' => to_currency($so_item->item_unit_price * $so_item->quantity_purchased),
	);
	return $row;
}

/*
Get the html data last row for the sales
*/
function get_sale_data_last_row_original($sales)
{
	$CI =& get_instance();

	$sum_amount_due = 0;
	$sum_amount_tendered = 0;
	$sum_change_due = 0;

	foreach($sales->result() as $key=>$sale)
	{
		$sum_amount_due += $sale->amount_due;
		$sum_amount_tendered += $sale->amount_tendered;
		$sum_change_due += $sale->change_due;
	}

	return array(
		'sale_id' => '-',
		'sale_time' => $CI->lang->line('sales_total'),
		'amount_due' => to_currency($sum_amount_due),
		'amount_tendered' => to_currency($sum_amount_tendered),
		'change_due' => to_currency($sum_change_due)
	);
}

function get_sale_data_last_row($sales)
{
	$CI =& get_instance();

	$sum_total_order = 0;
	$sum_total_paid = 0;
	$sum_total_unpaid = 0;

	foreach($sales->result() as $key=>$sale)
	{
		$sum_total_order += $sale->total_order;
		$sum_total_paid += $sale->total_paid;
		$sum_total_unpaid += $sale->total_unpaid;
	}

	return array(
		'sale_id' => '-',
		'sale_time' => $CI->lang->line('sales_total'),
		'total_order' => to_currency($sum_total_order),
		'total_paid' => to_currency($sum_total_paid),
		'total_unpaid' => to_currency($sum_total_unpaid)
	);
}

function get_sale_order_data_last_row($sales)
{
	$CI =& get_instance();

	$sum_total_order = 0;

	foreach($sales->result() as $key=>$sale)
	{
		$sum_total_order += $sale->total_order;
	}

	return array(
		'sale_order_id' => '-',
		'sale_time' => $CI->lang->line('sales_total'),
		'total_order' => to_currency($sum_total_order),
	);
}

function get_sale_order_items_data_last_row($so_items){
	$CI =& get_instance();

	$sum_total_order = 0;
	foreach($so_items->result() as $key => $so_item)
	{
		$sum_total_order += $so_item->quantity_purchased * $so_item->item_unit_price;
	}

	return array(
		'item_id' => '-',
		'items_unit_price' => $CI->lang->line('sales_total'),
		'subtotal_order' => to_currency($sum_total_order),
	);
}

/*
Get the sales payments summary
*/
function get_sales_manage_payments_summary($payments)
{
	$CI =& get_instance();

	$table = '<div id="report_summary">';
	$total = 0;

	foreach($payments as $key=>$payment)
	{
		$amount = $payment['payment_amount'];
		$total = bcadd($total, $amount);
		$table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . to_currency($amount) . '</div>';
	}
	$table .= '<div class="summary_row">' . $CI->lang->line('sales_total') . ': ' . to_currency($total) . '</div>';
	$table .= '</div>';

	return $table;
}


/*
Get the header for the people tabular view
*/
function get_people_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('people.person_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number')),
		array('city' => $CI->lang->line('common_phone_number')),
		array('employee_category' => $CI->lang->line('items_category')),
		array('supplier_id' => $CI->lang->line('suppliers_supplier')),
	);

	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = array('messages' => '', 'sortable' => FALSE);
	}

	return transform_headers($headers);
}

/*
Get the html data row for the person
*/
function get_person_data_row($person)
{
	$CI =& get_instance();
	$controller_name = strtolower(get_class($CI));
	$emp_category = arr_employee_category();

	return array (
		'people.person_id' => $person->person_id,
		'last_name' => $person->last_name,
		'first_name' => $person->first_name,
		'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
		'phone_number' => $person->phone_number,
		'employee_category' => $emp_category[$person->employee_category],
		'supplier_id' => $person->company_employee,
		'messages' => empty($person->phone_number) ? '' : anchor("Messages/view/$person->person_id", '<span class="glyphicon glyphicon-phone"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$person->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		)
	);
}


/*
Get the header for the customer tabular view
*/
function get_customer_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('people.person_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number')),
		array('city' => $CI->lang->line('common_city')),
		array('sales' => $CI->lang->line('common_sales')),
		array('company_name' => $CI->lang->line('sales_company_name')),
		array('total' => $CI->lang->line('common_total_spent'), 'sortable' => FALSE)
	);

	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = array('messages' => '', 'sortable' => FALSE);
	}

	return transform_headers($headers);
}

/*
Get the html data row for the customer
*/
function get_customer_data_row($person, $stats, $employee_name = '')
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));
	return array (
		'people.person_id' => $person->person_id,
		'last_name' => $person->last_name,
		'first_name' => $person->first_name,
		'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
		'phone_number' => $person->phone_number,
		'city' => $person->city,
		'employee_category' => $person->employee_category,
		'sales' => $employee_name,
		'company_name' => $person->company_name,
		'total' => to_currency($stats->total),
		'messages' => empty($person->phone_number) ? '' : anchor("Messages/view/$person->person_id", '<span class="glyphicon glyphicon-phone"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$person->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
	));
}


/*
Get the header for the suppliers tabular view
*/
function get_suppliers_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('people.person_id' => $CI->lang->line('common_id')),
		array('company_name' => $CI->lang->line('suppliers_company_name')),
		array('agency_name' => $CI->lang->line('suppliers_agency_name')),
		array('category' => $CI->lang->line('suppliers_category')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number'))
	);

	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = array('messages' => '');
	}

	return transform_headers($headers);
}

/*
Get the html data row for the supplier
*/
function get_supplier_data_row($supplier)
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));

	return array (
		'people.person_id' => $supplier->person_id,
		'company_name' => $supplier->company_name,
		'agency_name' => $supplier->agency_name,
		'category' => $supplier->category,
		'last_name' => $supplier->last_name,
		'first_name' => $supplier->first_name,
		'email' => empty($supplier->email) ? '' : mailto($supplier->email, $supplier->email),
		'phone_number' => $supplier->phone_number,
		'messages' => empty($supplier->phone_number) ? '' : anchor("Messages/view/$supplier->person_id", '<span class="glyphicon glyphicon-phone"></span>',
			array('class'=>"modal-dlg", 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$supplier->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>"modal-dlg", 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update')))
	);
}


/*
Get the header for the items tabular view
*/
function get_items_manage_table_headers()
{
	$CI =& get_instance();

	$definition_names = $CI->Attribute->get_definitions_by_flags(Attribute::SHOW_IN_ITEMS);

	$headers = array(
		array('items.item_id' => $CI->lang->line('common_id')),
		array('item_number' => $CI->lang->line('items_item_number')),
		array('name' => $CI->lang->line('items_name')),
		array('category' => $CI->lang->line('items_category')),
		array('company_name' => $CI->lang->line('suppliers_company_name')),
		array('phone_number' => $CI->lang->line('common_phone_number')),
		array('cost_price' => $CI->lang->line('items_cost_price')),
		array('unit_price' => $CI->lang->line('items_unit_price')),
		array('quantity' => $CI->lang->line('items_quantity'))
	);

	if($CI->config->item('use_destination_based_tax') == '1')
	{
		$headers[] = array('tax_percents' => $CI->lang->line('items_tax_category'), 'sortable' => FALSE);
	}
	else
	{
		$headers[] = array('tax_percents' => $CI->lang->line('items_tax_percents'), 'sortable' => FALSE);

	}

	$headers[] = array('item_pic' => $CI->lang->line('items_image'), 'sortable' => FALSE, 'visible' => FALSE);

	foreach($definition_names as $definition_id => $definition_name)
	{
		$headers[] = array($definition_id => $definition_name, 'sortable' => FALSE);
	}

	$headers[] = array('inventory' => '', 'escape' => FALSE);
	$headers[] = array('stock' => '', 'escape' => FALSE);

	return transform_headers($headers);
}

/*
Get the html data row for the item
*/
function get_item_data_row($item)
{
	$CI =& get_instance();

	if($CI->config->item('use_destination_based_tax') == '1')
	{
		if($item->tax_category_id == NULL)
		{
			$tax_percents = '-';
		}
		else
		{
			$tax_category_info = $CI->Tax_category->get_info($item->tax_category_id);
			$tax_percents = $tax_category_info->tax_category;
		}
	}
	else
	{
		$item_tax_info = $CI->Item_taxes->get_info($item->item_id);
		$tax_percents = '';
		foreach($item_tax_info as $tax_info)
		{
			$tax_percents .= to_tax_decimals($tax_info['percent']) . '%, ';
		}
		// remove ', ' from last item
		$tax_percents = substr($tax_percents, 0, -2);
		$tax_percents = !$tax_percents ? '-' : $tax_percents;
	}

	$controller_name = strtolower(get_class($CI));

	$image = NULL;
	if($item->pic_filename != '')
	{
		$ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
		if($ext == '')
		{
			// legacy
			$images = glob('./uploads/item_pics/' . $item->pic_filename . '.*');
		}
		else
		{
			// preferred
			$images = glob('./uploads/item_pics/' . $item->pic_filename);
		}

		if(sizeof($images) > 0)
		{
			$image .= '<a class="rollover" href="'. base_url($images[0]) .'"><img src="'.site_url('items/pic_thumb/' . pathinfo($images[0], PATHINFO_BASENAME)) . '"></a>';
		}
	}

	if($CI->config->item('multi_pack_enabled') == '1')
	{
		$item->name .= NAME_SEPARATOR . $item->pack_name;
	}

	$definition_names = $CI->Attribute->get_definitions_by_flags(Attribute::SHOW_IN_ITEMS);
	if (!isset($item->phone_number)){
		$item->phone_number = '-';
	}
	$columns = array (
		'items.item_id' => $item->item_id,
		'item_number' => $item->item_number,
		'name' => $item->name,
		'category' => $item->category,
		'company_name' => $item->company_name,
		'phone_number' => $item->phone_number,
		'cost_price' => to_currency($item->cost_price),
		'unit_price' => to_currency($item->unit_price),
		'quantity' => to_quantity_decimals($item->quantity),
		'tax_percents' => !$tax_percents ? '-' : $tax_percents,
		'item_pic' => $image
	);

	$icons = array(
		'inventory' => anchor($controller_name."/inventory/$item->item_id", '<span class="glyphicon glyphicon-pushpin"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_count'))
		),
		'stock' => anchor($controller_name."/count_details/$item->item_id", '<span class="glyphicon glyphicon-list-alt"></span>',
			array('class' => 'modal-dlg', 'title' => $CI->lang->line($controller_name.'_details_count'))
		),
		'edit' => anchor($controller_name."/view/$item->item_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_update'))
		)
	);

	return $columns + expand_attribute_values($definition_names, (array) $item) + $icons;

}


/*
Get the header for the giftcard tabular view
*/
function get_giftcards_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('giftcard_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('giftcard_number' => $CI->lang->line('giftcards_giftcard_number')),
		array('value' => $CI->lang->line('giftcards_card_value'))
	);

	return transform_headers($headers);
}

/*
Get the html data row for the giftcard
*/
function get_giftcard_data_row($giftcard)
{
	$CI =& get_instance();

	$controller_name=strtolower(get_class($CI));

	return array (
		'giftcard_id' => $giftcard->giftcard_id,
		'last_name' => $giftcard->last_name,
		'first_name' => $giftcard->first_name,
		'giftcard_number' => $giftcard->giftcard_number,
		'value' => to_currency($giftcard->value),
		'edit' => anchor($controller_name."/view/$giftcard->giftcard_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		)
	);
}

/*
Get the header for the item kits tabular view
*/
function get_item_kits_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('item_kit_id' => $CI->lang->line('item_kits_kit')),
		array('item_kit_number' => $CI->lang->line('item_kits_item_kit_number')),
		array('name' => $CI->lang->line('item_kits_name')),
		array('description' => $CI->lang->line('item_kits_description')),
		array('total_cost_price' => $CI->lang->line('items_cost_price'), 'sortable' => FALSE),
		array('total_unit_price' => $CI->lang->line('items_unit_price'), 'sortable' => FALSE)
	);

	return transform_headers($headers);
}

/*
Get the html data row for the item kit
*/
function get_item_kit_data_row($item_kit)
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));

	return array (
		'item_kit_id' => $item_kit->item_kit_id,
		'item_kit_number' => $item_kit->item_kit_number,
		'name' => $item_kit->name,
		'description' => $item_kit->description,
		'total_cost_price' => to_currency($item_kit->total_cost_price),
		'total_unit_price' => to_currency($item_kit->total_unit_price),
		'edit' => anchor($controller_name."/view/$item_kit->item_kit_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		)
	);
}

function parse_attribute_values($columns, $row) {
	$attribute_values = array();
	foreach($columns as $column) {
		if (array_key_exists($column, $row))
		{
			$attribute_value = explode('|', $row[$column]);
			$attribute_values = array_merge($attribute_values, $attribute_value);
		}
	}
	return $attribute_values;
}

function expand_attribute_values($definition_names, $row)
{
	$values = parse_attribute_values(array('attribute_values', 'attribute_dtvalues', 'attribute_dvalues'), $row);

	$indexed_values = array();
	foreach($values as $attribute_value)
	{
		$exploded_value = explode('_', $attribute_value);
		if(sizeof($exploded_value) > 1)
		{
			$indexed_values[$exploded_value[0]] = $exploded_value[1];
		}
	}

	$attribute_values = array();
	foreach($definition_names as $definition_id => $definition_name)
	{
		if(isset($indexed_values[$definition_id]))
		{
			$attribute_value = $indexed_values[$definition_id];
			$attribute_values["$definition_id"] = $attribute_value;
		}
	}

	return $attribute_values;
}

function get_attribute_definition_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('definition_id' => $CI->lang->line('attributes_definition_id')),
		array('definition_name' => $CI->lang->line('attributes_definition_name')),
		array('definition_type' => $CI->lang->line('attributes_definition_type')),
		array('definition_flags' => $CI->lang->line('attributes_definition_flags')),
		array('definition_group' => $CI->lang->line('attributes_definition_group')),
	);

	return transform_headers($headers);
}

function get_attribute_definition_data_row($attribute)
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));

	if(count($attribute->definition_flags) == 0)
	{
		$definition_flags = $CI->lang->line('common_none_selected_text');
	}
	else if($attribute->definition_type == GROUP)
	{
		$definition_flags = "-";
	}
	else
	{
		$definition_flags = implode(', ', $attribute->definition_flags);
	}

	return array (
		'definition_id' => $attribute->definition_id,
		'definition_name' => $attribute->definition_name,
		'definition_type' => $attribute->definition_type,
		'definition_group' => $attribute->definition_group,
		'definition_flags' => $definition_flags,
		'edit' => anchor("$controller_name/view/$attribute->definition_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		)
	);
}

/*
Get the header for the expense categories tabular view
*/
function get_expense_category_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('expense_category_id' => $CI->lang->line('expenses_categories_category_id')),
		array('category_name' => $CI->lang->line('expenses_categories_name')),
		array('category_description' => $CI->lang->line('expenses_categories_description'))
	);

	return transform_headers($headers);
}

/*
Gets the html data row for the expenses category
*/
function get_expense_category_data_row($expense_category)
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));

	return array (
		'expense_category_id' => $expense_category->expense_category_id,
		'category_name' => $expense_category->category_name,
		'category_description' => $expense_category->category_description,
		'edit' => anchor($controller_name."/view/$expense_category->expense_category_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		)
	);
}


/*
Get the header for the expenses tabular view
*/
function get_expenses_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('expense_id' => $CI->lang->line('expenses_expense_id')),
		array('date' => $CI->lang->line('expenses_date')),
		array('supplier_name' => $CI->lang->line('expenses_supplier_name')),
		array('supplier_tax_code' => $CI->lang->line('expenses_supplier_tax_code')),
		array('amount' => $CI->lang->line('expenses_amount')),
		array('tax_amount' => $CI->lang->line('expenses_tax_amount')),
		array('payment_type' => $CI->lang->line('expenses_payment')),
		array('category_name' => $CI->lang->line('expenses_categories_name')),
		array('description' => $CI->lang->line('expenses_description')),
		array('created_by' => $CI->lang->line('expenses_employee'))
	);

	return transform_headers($headers);
}

/*
Gets the html data row for the expenses
*/
function get_expenses_data_row($expense)
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));

	return array (
		'expense_id' => $expense->expense_id,
		'date' => to_datetime(strtotime($expense->date)),
		'supplier_name' => $expense->supplier_name,
		'supplier_tax_code' => $expense->supplier_tax_code,
		'amount' => to_currency($expense->amount),
		'tax_amount' => to_currency($expense->tax_amount),
		'payment_type' => $expense->payment_type,
		'category_name' => $expense->category_name,
		'description' => $expense->description,
		'created_by' => $expense->first_name.' '. $expense->last_name,
		'edit' => anchor($controller_name."/view/$expense->expense_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		)
	);
}

/*
Get the html data last row for the expenses
*/
function get_expenses_data_last_row($expense)
{
	$CI =& get_instance();

	$table_data_rows = '';
	$sum_amount_expense = 0;
	$sum_tax_amount_expense = 0;

	foreach($expense->result() as $key=>$expense)
	{
		$sum_amount_expense += $expense->amount;
		$sum_tax_amount_expense += $expense->tax_amount;
	}

	return array(
		'expense_id' => '-',
		'date' => $CI->lang->line('sales_total'),
		'amount' => to_currency($sum_amount_expense),
		'tax_amount' => to_currency($sum_tax_amount_expense)
	);
}

/*
Get the expenses payments summary
*/
function get_expenses_manage_payments_summary($payments, $expenses)
{
	$CI =& get_instance();

	$table = '<div id="report_summary">';

	foreach($payments as $key=>$payment)
	{
		$amount = $payment['amount'];
		$table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . to_currency($amount) . '</div>';
	}

	$table .= '</div>';

	return $table;
}


/*
Get the header for the cashup tabular view
*/
function get_cashups_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('cashup_id' => $CI->lang->line('cashups_id')),
		array('open_date' => $CI->lang->line('cashups_opened_date')),
		array('open_employee_id' => $CI->lang->line('cashups_open_employee')),
		array('open_amount_cash' => $CI->lang->line('cashups_open_amount_cash')),
		array('transfer_amount_cash' => $CI->lang->line('cashups_transfer_amount_cash')),
		array('close_date' => $CI->lang->line('cashups_closed_date')),
		array('close_employee_id' => $CI->lang->line('cashups_close_employee')),
		array('closed_amount_cash' => $CI->lang->line('cashups_closed_amount_cash')),
		array('note' => $CI->lang->line('cashups_note')),
		array('closed_amount_due' => $CI->lang->line('cashups_closed_amount_due')),
		array('closed_amount_card' => $CI->lang->line('cashups_closed_amount_card')),
		array('closed_amount_check' => $CI->lang->line('cashups_closed_amount_check')),
		array('closed_amount_total' => $CI->lang->line('cashups_closed_amount_total'))
	);

	return transform_headers($headers);
}

/*
Gets the html data row for the cashups
*/
function get_cash_up_data_row($cash_up)
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));

	return array (
		'cashup_id' => $cash_up->cashup_id,
		'open_date' => to_datetime(strtotime($cash_up->open_date)),
		'open_employee_id' => $cash_up->open_first_name . ' ' . $cash_up->open_last_name,
		'open_amount_cash' => to_currency($cash_up->open_amount_cash),
		'transfer_amount_cash' => to_currency($cash_up->transfer_amount_cash),
		'close_date' => to_datetime(strtotime($cash_up->close_date)),
		'close_employee_id' => $cash_up->close_first_name . ' ' . $cash_up->close_last_name,
		'closed_amount_cash' => to_currency($cash_up->closed_amount_cash),
		'note' => $cash_up->note ? $CI->lang->line('common_yes') : $CI->lang->line('common_no'),
		'closed_amount_due' => to_currency($cash_up->closed_amount_due),
		'closed_amount_card' => to_currency($cash_up->closed_amount_card),
		'closed_amount_check' => to_currency($cash_up->closed_amount_check),
		'closed_amount_total' => to_currency($cash_up->closed_amount_total),
		'edit' => anchor($controller_name."/view/$cash_up->cashup_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		)
	);
}
function arr_employee_category()
{
	return ['sales' => 'Sales','inventory' => 'Inventory','admin' => 'Admin'];
}

function arr_sales_order_status(){
	return [0 => 'New', 1 => 'Approved', 2 => 'Shipping', 3 => 'Partially Delivered', 4 => 'Complete', 5 => 'Cancel'];
}

function arr_purchase_order_status(){
	return [0 => 'New', 1 => 'Receive', 2 => 'Partially Complete', 3 => 'Complete'];
}

function arr_sale_payment_status(){
	return [0 => 'Unpaid', 1 => 'Partially Paid', 2 => 'Complete'];
}

/*
Get the header for the sales purchase order tabular view
*/
function get_po_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('no' => $CI->lang->line('no')),
		array('po_id' => $CI->lang->line('common_id')),
		array('po_time' => $CI->lang->line('po_time')),
		array('supplier_name' => $CI->lang->line('supplier_name')),
		array('employee_name' => $CI->lang->line('common_sales')),
		array('delivery_date' => $CI->lang->line('sales_order_delivery_date')),
		array('po_status' => $CI->lang->line('sales_order_status')),
		array('total_order' => $CI->lang->line('sales_order_total')),
		array('view_detail' => 'Detail', 'escape' => FALSE),
		array('print_po' => 'Print', 'escape' => FALSE)
	);
	return transform_headers($headers);
}

function get_po_detail_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('item_id' => $CI->lang->line('common_id')),
		array('item_barcode' => $CI->lang->line('items_item_number')),
		array('item_name' => $CI->lang->line('items_item')),
		array('items_quantity' => $CI->lang->line('items_quantity')),
		array('items_unit_price' => $CI->lang->line('items_unit_price')),
		array('subtotal_order' => $CI->lang->line('sales_sub_total')),
	);


	return transform_headers($headers);
}

function get_purchase_order_detail_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('no' => $CI->lang->line('common_no')),
		array('item_id' => $CI->lang->line('common_id')),
		array('item_barcode' => $CI->lang->line('items_item_number')),
		array('item_name' => $CI->lang->line('items_item')),
		array('items_quantity' => $CI->lang->line('items_quantity')),
		array('items_unit_price' => $CI->lang->line('items_unit_price')),
		array('subtotal_order' => $CI->lang->line('sales_sub_total')),
	);
	return transform_headers($headers);
}


function get_purchase_order_data_row($po)
{
	$CI =& get_instance();

	$controller_name = $CI->uri->segment(1);

	$purchase_order_status = arr_purchase_order_status();

	
	$row = array (
		'po_id' => $po->po_id,
		'po_time' => to_datetime(strtotime($po->po_time)),
		'supplier_name' => $po->supplier_name,
		'employee_name' => $po->employee_name,
		'delivery_date' => $po->delivery_date,
		'po_status' => $purchase_order_status[$po->po_status],
		'total_order' => to_currency($po->total_order),
	);
	$row['view_detail'] = anchor(
		$controller_name."/detailpo/$po->po_id",
		'<span class="glyphicon glyphicon-list-alt"></span>',
		array('title'=>$CI->lang->line('sales_show_invoice'))
	);
	$row['print_po'] = anchor(
		$controller_name."/purchade_order_print/$po->po_id",
		'<span class="glyphicon glyphicon-print"></span>',
		array('title'=>$CI->lang->line('po_show_invoice'))
	);
	/*
	if($CI->config->item('invoice_enable'))
	{
		$row['invoice_number'] = $po->invoice_number;
		$row['invoice'] = empty($po->invoice_number) ? '' : anchor($controller_name."/invoice/$po->po_id", '<span class="glyphicon glyphicon-list-alt"></span>',
			array('title'=>$CI->lang->line('sales_show_invoice'))
		);
	}
	*/
	$row['receipt'] = anchor($controller_name."/receipt/$po->po_id", '<span class="glyphicon glyphicon-usd"></span>',
		array('title' => $CI->lang->line('sales_show_receipt'))
	);
	$row['edit'] = anchor($controller_name."/edit/$po->po_id", '<span class="glyphicon glyphicon-edit"></span>',
		array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $CI->lang->line('common_delete'), 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_update'))
	);

	return $row;
}

function get_purchase_order_items_data_row($po_item)
{
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);
	$row = array(
		'item_id' => $po_item->item_id,
		'item_barcode' => $po_item->item_number,
		'item_name' => $po_item->name,
		'items_quantity' => $po_item->quantity_purchased,
		'items_unit_price' => to_currency($po_item->item_unit_price),
		'subtotal_order' => to_currency($po_item->item_unit_price * $po_item->quantity_purchased),
	);
	return $row;
}

function get_purchase_order_data_last_row($pos)
{
	$CI =& get_instance();

	$sum_total_order = 0;

	foreach($pos->result() as $key=>$po)
	{
		$sum_total_order += $po->total_order;
	}

	return array(
		'po_id' => '-',
		'po_time' => $CI->lang->line('total_order'),
		'total_order' => to_currency($sum_total_order),
	);
}

function get_purchase_order_items_data_last_row($po_items){
	$CI =& get_instance();

	$sum_total_order = 0;
	foreach($po_items->result() as $key => $po_item)
	{
		$sum_total_order += $po_item->quantity_purchased * $po_item->item_unit_price;
	}

	return array(
		'item_id' => '-',
		'items_unit_price' => $CI->lang->line('sales_total'),
		'subtotal_order' => to_currency($sum_total_order),
	);
}

function get_sales_order_detail_form_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('item_id' => $CI->lang->line('common_id')),
		array('name' => $CI->lang->line('items_item')),
		array('items_ordered' => $CI->lang->line('items_ordered')),
		array('items_shipped' => $CI->lang->line('items_shipped')),
	);
	return transform_headers($headers);
}
function array_status_color(){
	return [0 => 'btn-danger', 1 => 'btn-info', 2 => 'btn-warning', 3 => 'btn-primary', 4 => 'btn-success', 5 => 'btn-default' ];
}

function array_payment_status_color(){
	return [0 => 'btn-danger', 1 => 'btn-warning', 2 => 'btn-info'];
}

function get_inventory_outlet_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('item_id' => $CI->lang->line('common_id')),
		array('item_number' => $CI->lang->line('items_item_number')),
		array('name' => $CI->lang->line('items_item')),
		array('items_quantity' => $CI->lang->line('items_quantity')),
		array('unit_price' => $CI->lang->line('items_unit_price')),
		array('subtotal' => $CI->lang->line('sales_sub_total')),
		array('view_detail' => 'Inventory', 'escape' => FALSE)
	);
	return transform_headers($headers);
}

function get_inventory_outlet_data_row($item_qo)
{
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);
	$row = array (
		'item_id' => $item_qo->item_id,
		'item_number' => $item_qo->item_number,
		'name' => $item_qo->name,
		'items_quantity' => to_quantity_decimals($item_qo->quantity),
		'unit_price' => to_currency($item_qo->unit_price),
		'subtotal' => to_currency($item_qo->quantity*$item_qo->unit_price),
	);
	$row['edit'] = anchor(
		$controller_name."/inventory/$item_qo->item_id-$item_qo->customer_id",
		'<span class="glyphicon glyphicon-list-alt"></span>',
		array('class' => 'modal-dlg print_hide','title' => $CI->lang->line($controller_name.'_list'))
	);
	return $row;
}

function get_inventory_outlet_data_last_row($items_qo){
	$CI =& get_instance();
	$total_inventory = 0;
	foreach($items_qo->result() as $key => $item_qo)
	{
		//print_r($item_qo);
		$total_inventory = $total_inventory + ($item_qo->quantity * $item_qo->unit_price);
	}

	return array(
		'item_id' => '-',
		'unit_price' => $CI->lang->line('sales_total'),
		'subtotal' => to_currency($total_inventory),
	);
}

function get_sales_order_matrix_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('item_id' => $CI->lang->line('common_id')),
		array('item_number' => $CI->lang->line('items_item_number')),
		array('name' => $CI->lang->line('items_item')),
		array('total_qty' => $CI->lang->line('items_quantity')),
		array('items_unit_price' => $CI->lang->line('items_unit_price')),
		array('subtotal' => $CI->lang->line('sales_sub_total')),
		array('company_order' => $CI->lang->line('sales_company_name')),
	);
	return transform_headers($headers);
}

function get_sale_order_matrix_data_row($item_so){
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);
	$row = array (
		'item_id' => $item_so->item_id,
		'item_number' => $item_so->item_number,
		'name' => $item_so->name,
		'total_qty' => to_quantity_decimals($item_so->total_qty),
		'items_unit_price' => to_currency($item_so->item_unit_price),
		'subtotal' => to_currency($item_so->subtotal),
		'company_order' => $item_so->company_list,
	);
	return $row;
}

function get_sale_order_matrix_data_last_row($items_so){
	$CI =& get_instance();
	$total_order = 0;
	foreach($items_so->result() as $key => $item_so)
	{
		$total_order += $item_so->subtotal;
	}

	return array(
		'item_id' => '-',
		'items_unit_price' => $CI->lang->line('sales_total'),
		'subtotal' => to_currency($total_order),
	);
}

function get_payment_paid_items_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('item_id' => $CI->lang->line('common_id')),
		array('item_number' => $CI->lang->line('items_item_number')),
		array('name' => $CI->lang->line('items_item')),
		array('total_qty' => $CI->lang->line('items_quantity')),
		array('min_price' => $CI->lang->line('items_min_cost_price')),
		array('max_price' => $CI->lang->line('items_max_cost_price')),
		array('total_payment' => $CI->lang->line('sales_payments_total')),
		array('supplier_name' => $CI->lang->line('suppliers_supplier')),
		array('related_invoices' => 'Related '.$CI->lang->line('sales_invoice'), 'escape' => FALSE),
	);
	return transform_headers($headers, TRUE, FALSE);
}

function get_paid_sale_item_data_row($paid_items){
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);
	$row = array (
		'item_id' => $paid_items->item_id,
		'item_number' => $paid_items->item_number,
		'name' => $paid_items->name,
		'total_qty' => to_quantity_decimals($paid_items->total_qty),
		'min_price' => to_currency($paid_items->min_price),
		'max_price' => to_currency($paid_items->max_price),
		'total_payment' => to_currency($paid_items->total_payment),
		'supplier_name' => $paid_items->supplier_name,
		'related_invoices' => $paid_items->related_invoices
	);
	return $row;
}
function get_paid_sale_item_data_last_row($paid_items){
	$CI =& get_instance();
	$total_payment = 0;
	foreach($paid_items->result() as $key => $paid_item)
	{
		$total_payment += $paid_item->total_payment;
	}

	return array(
		'item_id' => '-',
		'max_price' => $CI->lang->line('sales_total'),
		'total_payment' => to_currency($total_payment),
	);
}

function get_sales_order_summary_table_headers(){
	$CI =& get_instance();

	$headers = array(
		array('location' => $CI->lang->line('common_location')),
		array('outlet' => $CI->lang->line('customers_mesra_name')),
		array('sum_total_order' => $CI->lang->line('sales_order_total')),
		array('total_qty' => $CI->lang->line('items_quantity'))
	);
	return transform_headers($headers, TRUE, FALSE);
}

function get_sale_order_summary_data_row($sum_data){
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);
	$qty_count = !empty($sum_data->total_qty_delivered) ? $sum_data->total_qty_delivered : $sum_data->total_qty_order;
	$row = array (
		'location' => $sum_data->location,
		'outlet' => $sum_data->outlet,
		'sum_total_order' => to_currency($sum_data->sum_total_order),
		'total_qty' => to_quantity_decimals($qty_count),
	);
	return $row;
}

function get_sale_order_summary_data_last_row($sum_datas){
	$CI =& get_instance();
	$total_order = 0;
	$total_qty = 0;
	foreach($sum_datas->result() as $key => $sum_data)
	{
		$qty_count = !empty($sum_data->total_qty_delivered) ? $sum_data->total_qty_delivered : $sum_data->total_qty_order;
		$total_order += $sum_data->sum_total_order;
		$total_qty += $qty_count;
	}

	return array(
		'outlet' => $CI->lang->line('sales_total'),
		'sum_total_order' => to_currency($total_order),
		'total_qty' => to_quantity_decimals($total_qty),
	);
}
?>
