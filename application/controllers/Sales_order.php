<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Sales_order extends Secure_Controller
{
    public function __construct()
    {

        parent::__construct('sales_order');
        $this->load->model('Salesorder');
        $this->load->helper('file');
        $this->load->library('sale_lib');
        $this->load->library('email_lib');
        $this->load->library('token_lib');
        $this->load->library('barcode_lib');
    }

    public function index()
    {


        $data['table_headers'] = get_sales_order_manage_table_headers();
        $this->load->view('sales_order/manage', $data);
    }

    public function manage()
    {
        $data['table_headers'] = get_sales_order_manage_table_headers();
        $this->load->view('sales_order/manage', $data);
    }

    public function detailso($sale_order_id){
        $arr_order_status = arr_sales_order_status();
        $data['table_headers'] = get_sales_order_detail_table_headers();
        $data['so_id'] = $sale_order_id;
        $so_info = $this->Salesorder->get_sales_order_info_by_id($sale_order_id);
        if ((int)$so_info->sale_status == 2){
            $data['so_number'] = 'DO0000' . $sale_order_id;
            $data['page_title'] = 'DELIVERY ORDER';
        }else {
            $data['so_number'] = 'SO0000' . $sale_order_id;
            $data['page_title'] = 'SALES ORDER';
            if ((int)$so_info->sale_status == 3 || (int)$so_info->sale_status == 4){
                $data['page_title'] = 'SALES ORDER DELIVERED';
            }elseif ((int)$so_info->sale_status == 5){
                $data['page_title'] = 'SALES ORDER CANCELED';
            }
        }
        $data['so_info_customer'] = $so_info->company_name;
        $data['so_info_comment'] = $so_info->comment;
        $data['so_info_status'] = strtoupper($arr_order_status[$so_info->sale_status]);
        $data['so_info_status_int'] = (int)$so_info->sale_status;
        $data['so_info_date'] = substr($so_info->sale_time,0,10);
        $this->load->view('sales_order/view_detail_so', $data);
    }

    public function matrix(){
        $data['table_headers'] = get_sales_order_matrix_table_headers();
        $data['page_title'] = 'SALES ORDER MATRIX';
        $data['filters2'] = arr_sales_order_status();
        $this->load->view('sales_order/sales_order_matrix', $data);
    }

    public function summary(){
        $data['table_headers'] = get_sales_order_summary_table_headers();
        $data['page_title'] = 'LAPORAN PENGHANTARAN';
        $data['filters2'] = arr_sales_order_status();
        $EmpArray = $this->Employee->get_all_employee_data();
        $EmpOption = [];
        foreach($EmpArray as $EmpData){
            $EmpOption[$EmpData->person_id] = $EmpData->first_name.' '.$EmpData->last_name;
        }
        $data['filters3'] = $EmpOption;
        $this->load->view('sales_order/sales_order_summary', $data);
    }
    public function get_detail_so($sale_order_id){
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
        $filters = [];
        $sales_order_items = $this->Salesorder->search_detail($sale_order_id,$search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->Salesorder->get_detail_found_rows($sale_order_id, $search, $filters);
        $data_rows = array();
        foreach($sales_order_items->result() as $so_item)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_items_data_row($so_item));
        }

        if($total_rows > 0)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_items_data_last_row($sales_order_items));
        }
        $payment_summary = '';
        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
    }

    public function get_matrix_so(){
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
        $sales_order_status = $this->input->get('sale_order_status');
        $sales_order_status_select = null;
        if (is_array($sales_order_status) && count($sales_order_status)){
            $sales_order_status_select = $sales_order_status;
        }
        $filters = array(
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
        );
        $sales_order_items = $this->Salesorder->search_detail_matrix($search, $filters, $limit, $offset, $sort, $order,FALSE, $sales_order_status_select);
        $total_rows = $this->Salesorder->get_detail_found_rows_matrix($search, $filters, $sales_order_status_select);
        $data_rows = array();
        foreach($sales_order_items->result() as $so_item)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_matrix_data_row($so_item));
        }

        if($total_rows > 0)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_matrix_data_last_row($sales_order_items));
        }
        $payment_summary = '';
        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
    }

    public function get_row($row_id)
    {
        $sale_info = $this->Salesorder->get_info($row_id)->row();
        $data_row = $this->xss_clean(get_sale_order_data_row($sale_info));

        echo json_encode($data_row);
    }

    public function search()
    {
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        if (empty($sort)){
            $sort = 'sale_order_id';
        }
        $order = $this->input->get('order');
        if (empty($order)){
            $order = 'DESC';
        }
        $filters = array('sale_type' => 'all',
            'location_id' => 'all',
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
        );
        if (!empty($this->input->get('customer_id'))){
           $filters['customer_id'] = $this->input->get('customer_id');
        }

        // check if any filter is set in the multiselect dropdown
        //$filledup = array_fill_keys($this->input->get('filters'), TRUE);
        //$filters = array_merge($filters, $filledup);


        $sales = $this->Salesorder->search($search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->Salesorder->get_found_rows($search, $filters);

        $data_rows = array();
        foreach($sales->result() as $sale)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_data_row($sale));
        }

        if($total_rows > 0)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_data_last_row($sales));
        }
        $payment_summary = '';
        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
    }

    public function delete($sale_order_id = -1, $update_inventory = TRUE)
    {
        $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $has_grant = $this->Employee->has_grant('sales_delete', $employee_id);
        if(!$has_grant)
        {
            echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_not_authorized')));
        }
        else
        {
            $sale_order_ids = $sale_order_id == -1 ? $this->input->post('ids') : array($sale_order_id);

            if($this->Salesorder->delete_list($sale_order_ids))
            {
                echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_deleted') . ' ' .
                    count($sale_order_ids) . ' ' . $this->lang->line('sales_one_or_multiple'), 'ids' => $sale_order_ids));
            }
            else
            {
                echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_deleted')));
            }
        }
    }

    public function edit($sale_order_id)
    {
        $data = array();
        $sale_info = $this->xss_clean($this->Salesorder->get_info($sale_order_id)->row_array());
        $data['selected_customer_id'] = $sale_info['customer_id'];
        $data['selected_customer_name'] = $sale_info['customer_name'];
        $employee_info = $this->Employee->get_info($sale_info['employee_id']);
        $data['selected_employee_id'] = $sale_info['employee_id'];
        $data['selected_employee_name'] = $this->xss_clean($employee_info->first_name . ' ' . $employee_info->last_name);
        $EmpArray = $this->Employee->get_all_employee_data();
        $EmpOption = [];
        foreach($EmpArray as $EmpData){
            $EmpOption[$EmpData->person_id] = $EmpData->first_name.' '.$EmpData->last_name;
        }
        $data['employee_list'] = $EmpArray;
        $data['employee_option'] = $EmpOption;
        $employee_info = $this->Employee->get_info($sale_info['employee_id']);
        $CustList = $this->Customer->get_all()->result();
        $CustOption = [];
        foreach($CustList as $CustData){
            $CustOption[$CustData->person_id] = $CustData->company_name;
        }
        $data['customer_list'] = $CustList;
        $data['customer_option'] = $CustOption;
        $data['sale_info'] = $sale_info;
        $SOStatusOption = arr_sales_order_status();
        $data['status_option'] = $SOStatusOption;
        $DisabledOption = [];
        if ((int)$sale_info['sale_status'] >= 2){
            foreach ($SOStatusOption as $Idx => $OptValue){
                if ((int)$Idx < $sale_info['sale_status']) {
                    $DisabledOption[$Idx] = 'disabled';
                }elseif ((int)$Idx == 5 && $sale_info['sale_status'] != 5){
                    $DisabledOption[$Idx] = 'disabled';
                }
            }
        }
        $data['disabled_status'] = $DisabledOption;
        $details_data = $this->Salesorder->get_sale_order_items($sale_order_id)->result();
        $row_details = [];
        foreach ($details_data as $detail_data){
            if ((int)$sale_info['sale_status'] < 2) {
                $row_details[] = [$detail_data->item_id, $detail_data->name, $detail_data->quantity_purchased, $detail_data->quantity_purchased];
            }elseif ((int)$sale_info['sale_status'] >= 2){
                $row_details[] = [$detail_data->item_id, $detail_data->name, $detail_data->qty_shipped, $detail_data->qty_shipped];
            }
        }
        $data['details_order'] = $row_details;
        $table_headers = get_sales_order_detail_form_table_headers();

        $this->load->view('sales_order/form', $data);
    }

    public function save($sale_order_id = -1)
    {
        $newdate = $this->input->post('date');
        $date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);
        $sale_time = $date_formatter->format('Y-m-d H:i:s');

        $sale_data = array(
            'sale_time' => $sale_time,
            'customer_id' => $this->input->post('customer_id') != '' ? $this->input->post('customer_id') : NULL,
            'employee_id' => $this->input->post('employee_id') != '' ? $this->input->post('employee_id') : NULL,
            'comment' => $this->input->post('comment'),
            'sale_status' => $this->input->post('sale_status') != '' ? $this->input->post('sale_status') : NULL
        );
        $sale_order_data = $this->Salesorder->get_info($sale_order_id)->result();
        $old_status = $sale_order_data[0]->sale_status;
        $status_changed = false;
        if ((int)$sale_order_data[0]->sale_status != (int)$this->input->post('sale_status') && !is_null($this->input->post('sale_status'))
            && !is_null($sale_order_data[0]->sale_status)){
            $status_changed = true;
        }
        $status_so = 'Tidak Berubah';
        if ((int)$this->input->post('sale_status') == 2 && $status_changed) {
            $status_so = 'Berubah';
            $items = $this->input->post('item_id');
            $qty_items = $this->input->post('qty_shipped');
            $data_items = [];
            for ($i = 0;$i < count($items);$i++){
                $so_item_detail = $this->Salesorder->get_sale_order_item_info($sale_order_id,$items[$i])->result();
                $data_items[$items[$i]]['info'] = $this->Item->get_info($items[$i]);
                $data_items[$items[$i]]['info']->item_location = $so_item_detail[0]->item_location;
                $data_items[$items[$i]]['qty'] = $qty_items[$i];
            }
            foreach($data_items as $Item_id => $ItemData){
                $kalkulasistok = 'Kalkulasi Tidak Terjadi';
                if($ItemData['info']->stock_type == HAS_STOCK && (double)$ItemData['qty'] > 0){
                    $kalkulasistok = 'Kalkulasi Terjadi';
                    $item_quantity = $this->Item_quantity->get_item_quantity($Item_id, $ItemData['info']->item_location);
                    $this->Item_quantity->save(array('quantity'	=> $item_quantity->quantity - $ItemData['qty'],
                        'item_id'		=> $Item_id,
                        'location_id'	=> $ItemData['info']->item_location), $Item_id, $ItemData['info']->item_location);
                    // if an items was deleted but later returned it's restored with this rule

                    if($ItemData['qty'] < 0)
                    {
                        $this->Item->undelete($Item_id);
                    }

                    // Inventory Count Details
                    $sale_remarks = 'SO Number : '.$sale_order_id.' Shipped';
                    $inv_data = array(
                        'trans_date'		=> date('Y-m-d H:i:s'),
                        'trans_items'		=> $Item_id,
                        'trans_user'		=> $sale_data['employee_id'],
                        'trans_location'	=> $ItemData['info']->item_location,
                        'trans_comment'		=> $sale_remarks,
                        'trans_inventory'	=> -$ItemData['qty']
                    );
                    $this->Inventory->insert($inv_data);
                    $SODetailData = ['sale_order_id' => $sale_order_id, 'item_id' => $Item_id, 'qty_shipped' => $ItemData['qty']];
                    $this->Salesorder->update_detail($sale_order_id, $Item_id, $SODetailData);
                }
            }
            $sale_data['shipped_date'] = date('Y-m-d H:i:s');
        }elseif (((int)$this->input->post('sale_status') == 3 || (int)$this->input->post('sale_status') == 4) && $status_changed){
            $status_so = 'Berubah';
            $items = $this->input->post('item_id');
            $qty_items = $this->input->post('qty_shipped');
            $data_items = [];
            for ($i = 0;$i < count($items);$i++){
                $so_item_detail = $this->Salesorder->get_sale_order_item_info($sale_order_id,$items[$i])->result();
                $data_items[$items[$i]]['info'] = $this->Item->get_info($items[$i]);
                $data_items[$items[$i]]['info']->item_location = $so_item_detail[0]->item_location;
                $data_items[$items[$i]]['qty'] = $qty_items[$i];
            }
            foreach($data_items as $Item_id => $ItemData){
                $kalkulasistok = 'Kalkulasi Tidak Terjadi';
                if($ItemData['info']->stock_type == HAS_STOCK && (double)$ItemData['qty'] > 0){
                    $kalkulasistok = 'Kalkulasi Terjadi';
                    $item_quantity = $this->Item_quantity->get_item_quantity_outlet($Item_id, $sale_data['customer_id']);
                    $this->Item_quantity->save_outlet(array('quantity'	=> $item_quantity->quantity + $ItemData['qty'],
                        'item_id'		=> $Item_id,
                        'customer_id'	=> $sale_data['customer_id'],
                        'location_id' => 0), $Item_id, $sale_data['customer_id']);
                    // if an items was deleted but later returned it's restored with this rule

                    if($ItemData['qty'] < 0)
                    {
                        $this->Item->undelete($Item_id);
                    }

                    // Inventory Count Details
                    $sale_remarks = 'SO Number : '.$sale_order_id.' Delivered';
                    $inv_data = array(
                        'trans_date'		=> date('Y-m-d H:i:s'),
                        'trans_items'		=> $Item_id,
                        'trans_user'		=> $sale_data['employee_id'],
                        'trans_location'	=> 0,
                        'trans_comment'		=> $sale_remarks,
                        'trans_inventory'	=> +$ItemData['qty'],
                        'customer_id'       => $sale_data['customer_id']
                    );
                    $this->Inventory->insert_outlet($inv_data);
                    $SODetailData = ['sale_order_id' => $sale_order_id, 'item_id' => $Item_id, 'qty_delivered' => $ItemData['qty']];
                    $this->Salesorder->update_detail($sale_order_id, $Item_id, $SODetailData);
                }
            }
            $sale_data['delivery_date'] = date('Y-m-d H:i:s');
        }
        if ($this->Salesorder->update($sale_order_id, $sale_data)) {
            echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_order_successfully_updated'), 'id' => $sale_order_id,
                'old-status' => $old_status, 'new-status' => $this->input->post('sale_status'),
                'status so' => $status_so));
        } else {
            echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_order_id));
        }
    }
    public function sales_order_print($sale_order_id)
    {
        $data = $this->_load_sale_order_data($sale_order_id);
        $data['cart'] = $this->Salesorder->get_sale_order_items($sale_order_id)->result();
        $total_order = 0;
        foreach ($data['cart'] as $idx => $cart){
            if ($data['sale_status'] == 2){
                $qty_item = $cart->qty_shipped;
            }else{
                $qty_item = $cart->quantity_purchased;
                if($data['sale_status'] == 3 || $data['sale_status'] == 4){
                    $qty_item = $cart->qty_delivered;
                }
            }
            $total_order += $cart->item_unit_price*$qty_item;
        }
        $data['total'] = to_currency($total_order);
        $this->load->view('sales_order/sales_order_print', $data);
    }
    public function _load_sale_order_data($sale_order_id)
    {
        //$this->sale_lib->clear_all();
        //$cash_rounding = $this->sale_lib->reset_cash_rounding();
        //$data['cash_rounding'] = $cash_rounding;

        $sale_info = $this->Salesorder->get_info($sale_order_id)->row_array();
        //var_dump($sale_info);
        //$this->sale_lib->copy_entire_sale($sale_order_id);
        $data = array();
        //$data['discount'] = $this->sale_lib->get_discount();
        $data['transaction_time'] = to_datetime(strtotime($sale_info['sale_time']));
        $data['show_stock_locations'] = $this->Stock_location->show_locations('sales');

        // Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
        $totals = 0;//$this->sale_lib->get_totals();
        //$data['subtotal'] = $totals['subtotal'];

        $employee_info = $this->Employee->get_info($sale_info['employee_id']);
        $data['employee'] = $employee_info->first_name . ' ' . mb_substr($employee_info->last_name, 0, 1);
        $this->_load_customer_data($sale_info['customer_id'], $data);
        $data['sale_order_id_num'] = $sale_order_id;
        $data['sale_order_id'] = $sale_order_id;
        $data['comments'] = $sale_info['comment'];
        if ((int)$sale_info['sale_status'] == 2){
            $data['so_number'] = 'DO ' . str_pad($sale_order_id,5,'0',STR_PAD_LEFT);
            $data['page_title'] = $this->lang->line('delivery_order');
            $data['transaction_date'] = to_date(strtotime($sale_info['shipped_date']));
            $data['total'] = '-';
        }else {
            $data['transaction_date'] = to_date(strtotime($sale_info['sale_time']));
            if ((int)$sale_info['sale_status'] == 3 || (int)$sale_info['sale_status'] == 4){
                $data['transaction_date'] = to_date(strtotime($sale_info['delivery_date']));
            }
            $data['so_number'] = 'SO ' . str_pad($sale_order_id,5,'0',STR_PAD_LEFT);
            $data['page_title'] = $this->lang->line('sales_order');
            $data['total'] = to_currency($totals);
        }
        $data['quote_number'] = $sale_info['quote_number'];
        $data['sale_status'] = $sale_info['sale_status'];

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

        $data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['so_number']);
        $data['print_after_sale'] = FALSE;
        $data['price_work_orders'] = FALSE;
        return $this->xss_clean($data);
    }
    private function _load_customer_data($customer_id, &$data, $stats = FALSE)
    {
        $customer_info = '';

        if($customer_id != -1)
        {
            $customer_info = $this->Customer->get_info($customer_id);
            $data['customer_id'] = $customer_id;
            if(!empty($customer_info->company_name))
            {
                $data['customer'] = $customer_info->company_name;
            }
            else
            {
                $data['customer'] = $customer_info->first_name . ' ' . $customer_info->last_name;
            }
            $data['first_name'] = $customer_info->first_name;
            $data['last_name'] = $customer_info->last_name;
            $data['customer_email'] = $customer_info->email;
            $data['customer_address'] = $customer_info->address_1;
            if(!empty($customer_info->zip) || !empty($customer_info->city))
            {
                $data['customer_location'] = $customer_info->zip . ' ' . $customer_info->city . "\n" . $customer_info->state;
            }
            else
            {
                $data['customer_location'] = '';
            }
            $data['customer_account_number'] = $customer_info->account_number;
            $data['customer_discount'] = $customer_info->discount;
            $data['customer_discount_type'] = $customer_info->discount_type;
            $package_id = $this->Customer->get_info($customer_id)->package_id;
            if($package_id != NULL)
            {
                $package_name = $this->Customer_rewards->get_name($package_id);
                $points = $this->Customer->get_info($customer_id)->points;
                $data['customer_rewards']['package_id'] = $package_id;
                $data['customer_rewards']['points'] = empty($points) ? 0 : $points;
                $data['customer_rewards']['package_name'] = $package_name;
            }

            if($stats)
            {
                $cust_stats = $this->Customer->get_stats($customer_id);
                $data['customer_total'] = empty($cust_stats) ? 0 : $cust_stats->total;
            }

            $data['customer_info'] = implode("\n", array(
                $data['customer'],
                $data['customer_address'],
                $data['customer_location']
            ));

            if($data['customer_account_number'])
            {
                $data['customer_info'] .= "\n" . $this->lang->line('sales_account_number') . ": " . $data['customer_account_number'];
            }
            if($customer_info->tax_id != '')
            {
                $data['customer_info'] .= "\n" . $this->lang->line('sales_tax_id') . ": " . $customer_info->tax_id;
            }
            $data['tax_id'] = $customer_info->tax_id;
        }

        return $customer_info;
    }

    public function get_summary_so(){
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
        $sales_order_status = $this->input->get('sale_order_status');
        $employee_ids = $this->input->get('employee_ids');
        $sales_order_status_select = null;
        if (is_array($sales_order_status) && count($sales_order_status)){
            $sales_order_status_select = $sales_order_status;
        }
        $filters = array(
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
            'employee_ids' => $employee_ids
        );
        $sales_order_summary = $this->Salesorder->search_summary_so($search, $filters, $limit, $offset, $sort, $order,FALSE, $sales_order_status_select);
        $total_rows = $this->Salesorder->get_summary_found_rows($search, $filters, $sales_order_status_select);
        $data_rows = array();
        foreach($sales_order_summary->result() as $so_sums)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_summary_data_row($so_sums));
        }

        if($total_rows > 0)
        {
            $data_rows[] = $this->xss_clean(get_sale_order_summary_data_last_row($sales_order_summary));
        }
        $payment_summary = '';
        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
    }
}