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
            'dinner_table_id','work_order_number','sale_type','total_order','delivery_date',
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
    public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'sales_order.sale_time', $order = 'desc', $count_only = FALSE)
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
            if($filters['is_valid_receipt'] != FALSE)
            {
                $pieces = explode(' ', $search);
                $this->db->where('so.sale_order_id', $pieces[1]);
            }
            else
            {

            }
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
    public function delete_list($sale_order_ids, $employee_id, $update_inventory = TRUE)
    {
        $result = TRUE;

        foreach($sale_order_ids as $sale_order_id)
        {
            $result &= $this->delete($sale_order_id, $employee_id, $update_inventory);
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
    public function delete($sale_order_id, $employee_id, $update_inventory = TRUE)
    {
        // start a transaction to assure data integrity
        $this->db->trans_start();

        $sale_status = $this->get_sale_status($sale_order_id);
        if($update_inventory && ((int)$sale_status == COMPLETED || (int)$sale_status == PARTIALLY_DELIVERED))
        {
            // defect, not all item deletions will be undone??
            // get array with all the items involved in the sale to update the inventory tracking
            $items = $this->get_sale_order_items($sale_order_id)->result_array();
            foreach($items as $item)
            {
                $cur_item_info = $this->Item->get_info($item['item_id']);

                if($cur_item_info->stock_type == HAS_STOCK)
                {
                    // create query to update inventory tracking
                    $inv_data = array(
                        'trans_date' => date('Y-m-d H:i:s'),
                        'trans_items' => $item['item_id'],
                        'trans_user' => $employee_id,
                        'trans_comment' => 'Deleting sale ' . $sale_order_id,
                        'trans_location' => $item['item_location'],
                        'trans_inventory' => $item['quantity_purchased']
                    );
                    // update inventory
                    $this->Inventory->insert($inv_data);

                    // update quantities
                    $this->Item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased']);
                }
            }
        }
        $this->update_sale_status($sale_order_id, CANCELED);

        // execute transaction
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Gets sale item
     */
    public function get_sale_order_items($sale_order_id)
    {
        $this->db->from('sales_order_items');
        $this->db->where('sale_order_id', $sale_order_id);

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

        // touch payment only if update sale is successful and there is a payments object otherwise the result would be to delete all the payments associated to the sale
        if($success)
        {
            //Run these queries as a transaction, we want to make sure we do all or nothing
            $this->db->trans_start();
            if ($sale_data['sale_status'] == 2){

            }
            $this->db->trans_complete();
            $success &= $this->db->trans_status();
        }
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
}
?>
