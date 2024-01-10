<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Sale class
 */
class Salesorder extends CI_Model
{

    /**
     * Get number of rows for the takings (sales_order/manage) view
     */
    public function get_found_rows($search, $filters)
    {
        return $this->search($search, $filters, 0, 0, 'sale_time', 'desc', TRUE);
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
        ];
        $this->db->select(implode(',',$FieldArr))->from('sales_order AS so');
        $this->db->join('people AS people_emp','so.employee_id = people_emp.person_id','LEFT');
        $this->db->join('people AS people_cust','so.customer_id = people_cust.person_id','LEFT');
        $this->db->where($where);

        if(!empty($search))
        {
            if($filters['is_valid_receipt'] != FALSE)
            {
                $pieces = explode(' ', $search);
                $this->db->where('sales_order.sale_order_id', $pieces[1]);
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

        $this->update_sale_status($sale_order_id, CANCELED);

        // execute transaction
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Gets sale item
     */
    public function get_sale_items($sale_order_id)
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
}
?>
