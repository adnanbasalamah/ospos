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
        $this->load->view('sales/manage', $data);
    }

    public function get_row($row_id)
    {
        $sale_info = $this->Sale->get_info($row_id)->row();
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
}