<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Sale class
 */
class Salesorder extends CI_Model
{
    public function get_info($sale_order_id)
    {
        $FieldArr = [
            'sale_time','customer_id','employee_id','comment',
            'invoice_number','quote_number','sale_order_id','sale_status',
            'dinner_table_id','work_order_number','sale_type','total_order','delivery_date', 'shipped_date',
            'CONCAT(CONCAT(people_cust.first_name," "), people_cust.last_name) AS customer_name',
            'CONCAT(CONCAT(people_emp.first_name," "), people_emp.last_name) AS employee_name',
            '(SELECT company_name FROM ospos_customers WHERE ospos_customers.person_id = so.customer_id) AS company_name'
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order AS so');
        $this->db->join('people AS people_emp','so.employee_id = people_emp.person_id','LEFT');
        $this->db->join('people AS people_cust','so.customer_id = people_cust.person_id','LEFT');
        $this->db->where('sale_order_id',$sale_order_id);

        return $this->db->get();
    }
    /**
     * Get number of rows for the takings (sales_order/manage) view
     */
    public function get_found_rows($search, $filters)
    {
        return $this->search($search, $filters, 0, 0, 'sale_time', 'desc', TRUE);
    }

    function get_detail_found_rows($sale_order_id, $search, $filters){
        return $this->search_detail($sale_order_id, $search, $filters, 0, 0, 'item_id', 'desc', TRUE);
    }

    /**
     * Get the sales data for the takings (sales/manage) view
     */
    public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'sales_order.sale_id', $order = 'desc', $count_only = FALSE)
    {


        // Pick up only non-suspended records
        $where = '';

        if(empty($this->config->item('date_or_time_format')))
        {
            $where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']);
        }
        else
        {
            $where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date']));
        }
        $FieldArr = [
            'sale_time','customer_id','employee_id','comment',
            'invoice_number','quote_number','sale_order_id','sale_status',
            'dinner_table_id','work_order_number','sale_type','total_order','delivery_date',
            'CONCAT(CONCAT(people_cust.first_name," "), people_cust.last_name) AS customer_name',
            'CONCAT(CONCAT(people_emp.first_name," "), people_emp.last_name) AS employee_name',
            '(SELECT company_name FROM ospos_customers WHERE ospos_customers.person_id = so.customer_id) AS company_name'
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order AS so');
        $this->db->join('people AS people_emp','so.employee_id = people_emp.person_id','LEFT');
        $this->db->join('people AS people_cust','so.customer_id = people_cust.person_id','LEFT');
        $this->db->where($where);
        if (isset($filters['customer_id']) && !empty($filters['customer_id'])){
            $this->db->where('so.customer_id', $filters['customer_id']);
        }
        if(!empty($search))
        {
            $where = '(CONCAT(CONCAT(people_emp.first_name," "), people_emp.last_name)) LIKE "%'.$search.'%"';
            $this->db->where($where);
        }

        // get_found_rows case
        if($count_only == TRUE)
        {
            return $this->db->get()->num_rows();
        }

        //$this->db->group_by('sales_order.sale_order_id');

        // order by sale time by default
        $this->db->order_by($sort, $order);

        if($rows > 0)
        {
            $this->db->limit($rows, $limit_from);
        }

        return $this->db->get();
    }

    /**
     * Gets total of rows
     */
    public function get_total_rows()
    {
        $this->db->from('sales_order');

        return $this->db->count_all_results();
    }

    /**
     * Checks if sale exists
     */
    public function exists($sale_order_id)
    {
        $this->db->from('sales_order');
        $this->db->where('sale_order_id', $sale_order_id);

        return ($this->db->get()->num_rows()==1);
    }

    /**
     * Deletes list of sales
     */
    public function delete_list($sale_order_ids)
    {
        $result = TRUE;
        foreach($sale_order_ids as $sale_order_id)
        {
            $result &= $this->delete($sale_order_id);
        }

        return $result;
    }

    /**
     * Restores list of sales
     */
    public function restore_list($sale_order_ids, $employee_id, $update_inventory = TRUE)
    {
        foreach($sale_order_ids as $sale_order_id)
        {
            $this->update_sale_status($sale_order_id, SUSPENDED);
        }

        return TRUE;
    }

    /**
     * Delete sale.  Hard deletes are not supported for sales transactions.
     * When a sale is "deleted" it is simply changed to a status of canceled.
     * However, if applicable the inventory still needs to be updated
     */
    public function delete($sale_order_id)
    {
        // start a transaction to assure data integrity
        $this->db->trans_start();

        $sale_status = $this->get_sale_status($sale_order_id);
        if((int)$sale_status == 2 || (int)$sale_status == 3 || (int)$sale_status == 4)
        {

        }elseif ((int)$sale_status == 0 || (int)$sale_status == 1){
            $this->update_sale_status($sale_order_id, 5);
        }elseif ((int)$sale_status == 5){
            $this->db->where('sale_order_id', $sale_order_id);
            $data_delete = $this->db->delete('sales_order_items');
            $this->db->where('sale_order_id', $sale_order_id);
            $data_delete = $this->db->delete('sales_order');
        }

        // execute transaction
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Gets sale item
     */
    public function get_sale_order_items($sale_order_id)
    {
        $this->db->from('sales_order_items as so_items');
        $this->db->join('items AS items','so_items.item_id = items.item_id','LEFT');
        $this->db->where('sale_order_id', $sale_order_id);

        return $this->db->get();
    }

    public function get_sale_order_item_info($sale_order_id, $item_id)
    {
        $this->db->from('sales_order_items as so_items');
        $this->db->join('items AS items','so_items.item_id = items.item_id','LEFT');
        $this->db->where('sale_order_id', $sale_order_id);
        $this->db->where('so_items.item_id', $item_id);

        return $this->db->get();
    }

    /**

    /**
     * Gets sale customer name
     */
    public function get_customer($sale_order_id)
    {
        $this->db->from('sales_order');
        $this->db->where('sale_order_id', $sale_order_id);

        return $this->Customer->get_info($this->db->get()->row()->customer_id);
    }

    /**
     * Gets sale employee name
     */
    public function get_employee($sale_order_id)
    {
        $this->db->from('sales_order');
        $this->db->where('sale_order_id', $sale_order_id);

        return $this->Employee->get_info($this->db->get()->row()->employee_id);
    }

    public function get_sale_status($sale_order_id)
    {
        $this->db->from('sales_order');
        $this->db->where('sale_order_id', $sale_order_id);

        return $this->db->get()->row()->sale_status;
    }

    public function update_sale_status($sale_order_id, $sale_status)
    {
        $this->db->where('sale_order_id', $sale_order_id);
        $this->db->update('sales_order', array('sale_status'=>$sale_status));
    }

    public function update($sale_order_id, $sale_data)
    {
        $this->db->where('sale_order_id', $sale_order_id);
        $success = $this->db->update('sales_order', $sale_data);
        return $success;
    }

    public function update_detail($sale_order_id, $item_id, $sale_detail_data)
    {
        $this->db->where('sale_order_id', $sale_order_id);
        $this->db->where('item_id', $item_id);
        $success = $this->db->update('sales_order_items', $sale_detail_data);
        return $success;
    }

    /**
     * Gets the quote_number for the selected sale
     */
    public function get_comment($sale_order_id)
    {
        $this->db->from('sales_order');
        $this->db->where('sale_order_id', $sale_order_id);

        $row = $this->db->get()->row();

        if($row != NULL)
        {
            return $row->comment;
        }

        return NULL;
    }
    public function get_sales_order_info_by_id($sale_order_id)
    {
        $FieldArr = [
            'sale_time','so.customer_id','so.employee_id','comment',
            'invoice_number','quote_number','sale_order_id','sale_status',
            'dinner_table_id','work_order_number','sale_type','total_order','delivery_date',
            'CONCAT(CONCAT(people_cust.first_name," "), people_cust.last_name) AS customer_name',
            'CONCAT(CONCAT(people_emp.first_name," "), people_emp.last_name) AS employee_name',
            'cust_so.company_name'
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order AS so');
        $this->db->join('people AS people_emp','so.employee_id = people_emp.person_id','LEFT');
        $this->db->join('people AS people_cust','so.customer_id = people_cust.person_id','LEFT');
        $this->db->join('customers AS cust_so','so.customer_id = cust_so.person_id','LEFT');
        $this->db->where('sale_order_id', $sale_order_id);

        $row = $this->db->get()->row();

        if($row != NULL)
        {
            return $row;
        }

        return NULL;
    }

    function search_detail($sale_order_id, $search, $filters, $rows = 0, $limit_from = 0, $sort = 'sales_order_items.line', $order = 'desc', $count_only = FALSE){
        $FieldArr = [
            'sale_order_id', 'so_item.item_id', 'so_item.description', 'serialnumber', 'line', 'quantity_purchased',
            'item_cost_price', 'item_unit_price', 'discount', 'discount_type', 'item_location', 'print_option',
            'items.name','items.category','items.supplier_id','items.item_number','items.description',
            'items.cost_price','items.unit_price','items.reorder_level','items.receiving_quantity',
            'items.item_id','items.pic_filename','items.allow_alt_description','items.is_serialized',
            'items.stock_type','items.item_type','items.deleted','items.tax_category_id','items.qty_per_pack',
            'items.pack_name','items.low_sell_item_id','items.hsn_code'
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order_items AS so_item');
        $this->db->join('items AS items', 'so_item.item_id = items.item_id', 'left');
        $this->db->join('suppliers AS suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $this->db->where('sale_order_id', $sale_order_id);
        if(!empty($search))
        {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('item_number', $search);
            $this->db->or_like('items.item_id', $search);
            $this->db->or_like('company_name', $search);
            $this->db->or_like('items.category', $search);
            $this->db->group_end();
        }
        // get_found_rows case
        if($count_only == TRUE)
        {
            return $this->db->get()->num_rows();
        }
        // order by sale time by default
        $this->db->order_by($sort, $order);
        if($rows > 0)
        {
            $this->db->limit($rows, $limit_from);
        }
        /*$this->db->get();
        print $this->db->last_query();*/
        return $this->db->get();
    }
    function search_detail_matrix($search, $filters, $rows = 0, $limit_from = 0, $sort = 'sales_order_items.item_id', $order = 'asc', $count_only = FALSE, $sales_order_status = null){
        $where = '';
        if (!is_null($sales_order_status) && count($sales_order_status)){
            $sales_order_status_value = implode(',',$sales_order_status);
            $where = '(sale_status IN ('.$sales_order_status_value.')) AND ';
        }
        if(empty($this->config->item('date_or_time_format')))
        {
            $where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']);
        }
        else
        {
            $where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date']));
        }
        $FieldArr = [
            'so_item.item_id', 'SUM(quantity_purchased) AS total_qty',
            'GROUP_CONCAT(DISTINCT(customers.company_name)  
            ORDER BY customers.company_name ASC SEPARATOR ", ") AS company_order',
            'GROUP_CONCAT(DISTINCT(SELECT CONCAT(first_name," ",last_name) FROM 
            ospos_people AS people WHERE people.person_id = sales_order.customer_id) 
            ORDER BY customers.company_name ASC SEPARATOR ", ") AS company_list',
            'items.name','items.category','items.item_number',
            'item_cost_price','item_unit_price',
            'SUM(so_item.item_unit_price*so_item.quantity_purchased) AS subtotal'
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order_items AS so_item');
        $this->db->join('sales_order AS sales_order', 'so_item.sale_order_id = sales_order.sale_order_id', 'left');
        $this->db->join('items AS items', 'so_item.item_id = items.item_id', 'left');
        $this->db->join('customers AS customers', 'sales_order.customer_id = customers.person_id', 'left');
        $this->db->where($where);
        $this->db->group_by('so_item.item_id');
        if($count_only == TRUE)
        {
            return $this->db->get()->num_rows();
        }
        // order by sale time by default
        $this->db->order_by($sort, $order);
        if($rows > 0 && $limit_from != -1)
        {
            $this->db->limit($rows, $limit_from);
        }
        $return_dt = $this->db->get();
        //print $this->db->last_query();
        return $return_dt;
    }
    function get_detail_found_rows_matrix($search, $filters, $sales_order_status){
        return $this->search_detail_matrix($search, $filters, 0, 0, 'item_id', 'desc', TRUE, $sales_order_status);
    }

    function search_summary_so($search, $filters, $rows = 0, $limit_from = 0, $sort = 'people.city,people.last_name', $order = 'asc', $count_only = FALSE, $sales_order_status = null){
        $where = '';
        if (!is_null($sales_order_status) && count($sales_order_status)){
            $sales_order_status_value = implode(',',$sales_order_status);
            $where = '(sale_status IN ('.$sales_order_status_value.')) AND ';
        }
        if (isset($filters['employee_ids']) && $filters['employee_ids'] != -1){
            $employee_value = implode(',',$filters['employee_ids']);
            $where .= '(employee_id IN ('.$employee_value.')) AND ';
        }
        if(empty($this->config->item('date_or_time_format')))
        {
            $where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']);
        }
        else
        {
            $where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date']));
        }
        $FieldArr = [
            'SUM(sales_order.total_order) AS sum_total_order',
            'CONCAT(people.first_name," ",people.last_name) AS outlet',
            'people.city AS location',
            'SUM((SELECT SUM(quantity_purchased) FROM ospos_sales_order_items WHERE sale_order_id = 
            sales_order.sale_order_id GROUP BY sale_order_id)) AS total_qty_order',
            'SUM((SELECT SUM(qty_delivered) FROM ospos_sales_order_items WHERE sale_order_id = 
            sales_order.sale_order_id GROUP BY sale_order_id)) AS total_qty_delivered',
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order AS sales_order');
        $this->db->join('people AS people', 'sales_order.customer_id = people.person_id', 'left');
        $this->db->where($where);
        $this->db->group_by('sales_order.customer_id');
        if($count_only == TRUE)
        {
            return $this->db->get()->num_rows();
        }
        // order by sale time by default
        $this->db->order_by($sort, $order);
        if($rows > 0 && $limit_from != -1)
        {
            $this->db->limit($rows, $limit_from);
        }
        $return_dt = $this->db->get();
        //print $this->db->last_query();
        return $return_dt;
    }
    function get_summary_found_rows($search, $filters, $sales_order_status){
        return $this->search_summary_so($search, $filters, 0, 0, 'people.city,people.last_name', 'asc', TRUE, $sales_order_status);
    }
    function search_summary_by_item_supplier($search, $filters, $rows = 0, $limit_from = 0, $sort = 'people.city,people.last_name', $order = 'asc', $count_only = FALSE, $supplier_id = null){
        $where = 'sale_status = 4 AND ';
        if (!empty($supplier_id)){
            $where .= '(so_items.supplier_id = '.$supplier_id.') AND ';
        }
        if(empty($this->config->item('date_or_time_format')))
        {
            $where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']);
        }
        else
        {
            $where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date']));
        }
        $FieldArr = [
            'items.item_id,items.item_number, items.name, so_items.supplier_id',
            'so.sale_time','SUM(so_items.qty_delivered) AS qty_order',
            '(SELECT company_name FROM ospos_suppliers WHERE ospos_suppliers.person_id = so_items.supplier_id) AS supplier_name',
            'MIN(item_cost_price) AS min_cost_price, MAX(item_cost_price) AS max_cost_price',
            'MIN(item_unit_price) AS min_unit_price, MAX(item_unit_price) AS max_unit_price'
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order_items AS so_items');
        $this->db->join('sales_order AS so', 'so_items.sale_order_id = so.sale_order_id', 'left');
        $this->db->join('items AS items','so_items.item_id = items.item_id', 'LEFT');
        $this->db->where($where);
        $this->db->group_by('so_items.supplier_id');
        $this->db->group_by('so_items.item_id');
        if($count_only == TRUE)
        {
            return $this->db->get()->num_rows();
        }
        // order by sale time by default
        $this->db->order_by($sort, $order);
        if($rows > 0)
        {
            $this->db->limit($rows, $limit_from);
        }
        $return_dt = $this->db->get();
        //print $this->db->last_query();
        return $return_dt;
    }
}
?>
