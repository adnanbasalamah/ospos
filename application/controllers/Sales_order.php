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
}