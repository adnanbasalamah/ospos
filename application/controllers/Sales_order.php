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
        $data['so_number'] = 'SO0000'.$sale_order_id;
        $so_info = $this->Salesorder->get_sales_order_info_by_id($sale_order_id);
        $data['so_info_customer'] = $so_info->company_name;
        $data['so_info_comment'] = $so_info->comment;
        $data['so_info_status'] = strtoupper($arr_order_status[$so_info->sale_status]);
        $data['so_info_date'] = substr($so_info->sale_time,0,10);
        $this->load->view('sales_order/view_detail_so', $data);
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

    public function get_row($row_id)
    {
        $sale_info = $this->Sales_order->get_info($row_id)->row();
        $data_row = $this->xss_clean(get_sale_order_data_row($sale_info));

        echo json_encode($data_row);
    }

    public function search()
    {
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
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

            if($this->Salesorder->delete_list($sale_order_ids, $employee_id))
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
            $row_details[] = [$detail_data->item_id, $detail_data->name, $detail_data->quantity_purchased, $detail_data->quantity_purchased];
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
        $InventoryData =
        $this->Inventory->update('POS '.$sale_order_id, ['trans_date' => $sale_time]);
        if($this->Sale->update($sale_order_id, $sale_data))
        {
            echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
        }
        else
        {
            echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
        }
    }
}