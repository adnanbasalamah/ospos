<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Inventory_outlet extends Secure_Controller
{
    public function __construct()
    {
        parent::__construct('inventory_outlet');
        $this->load->model('Inventoryoutlet');
        $this->load->helper('file');
        $this->load->library('sale_lib');
        $this->load->library('email_lib');
        $this->load->library('token_lib');
        $this->load->library('barcode_lib');
    }

    public function index()
    {
        $data['table_headers'] = get_inventory_outlet_table_headers();
        $this->load->view('inventory_outlet/manage', $data);
    }

    public function manage()
    {
        $data['table_headers'] = get_inventory_outlet_table_headers();
        $this->load->view('inventory_outlet/manage', $data);
    }

    public function search()
    {
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
        $filters = array('sale_type' => 'all');
        if (!is_null($this->input->get('customer_id'))){
            $filters['customer_id'] = $this->input->get('customer_id');
        }else{
            $filters['customer_id'] = 0;
        }
        $items_qo = $this->Inventoryoutlet->search($search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->Inventoryoutlet->get_found_rows($search, $filters);

        $data_rows = array();
        foreach($items_qo->result() as $item_qo)
        {
            $data_rows[] = $this->xss_clean(get_inventory_outlet_data_row($item_qo));
        }

        if($total_rows > 0)
        {
            $data_rows[] = $this->xss_clean(get_inventory_outlet_data_last_row($items_qo));
        }
        $payment_summary = '';
        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
    }
}