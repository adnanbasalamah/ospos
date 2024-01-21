<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Sale class
 */
class Inventoryoutlet extends CI_Model
{
    public function get_info($customer_id, $item_id = null)
    {
        $FieldArr = [
            'item_qo.item_id', 'item_qo.location_id', 'quantity', 'item_qo.customer_id',
            'items.*','people_cust.*'
        ];
        $this->db->select(implode(',',$FieldArr))->from('item_quantities_outlet AS item_qo');
        $this->db->join('items AS items','item_qo.item_id = item_qo.item_id','LEFT');
        $this->db->join('people AS people_cust','item_qo.customer_id = people_cust.person_id','LEFT');
        $this->db->where('item_qo.customer_id',$customer_id);
        if (!is_null($item_id)){
            $this->db->where('item_qo.item_id',$item_id);
        }
        return $this->db->get();
    }
    /**
     * Get number of rows for the inventory outlet view
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
        $FieldArr = [
            'item_qo.item_id', 'item_qo.location_id', 'quantity', 'item_qo.customer_id',
            'items.*','people_cust.*'
        ];
        $this->db->select(implode(',',$FieldArr))->from('item_quantities_outlet AS item_qo');
        $this->db->join('items AS items','item_qo.item_id = item_qo.item_id','LEFT');
        $this->db->join('people AS people_cust','item_qo.customer_id = people_cust.person_id','LEFT');
        if (isset($filters['customer_id']) && !is_null($filters['customer_id'])){
            $this->db->where('item_qo.customer_id', $filters['customer_id']);
        }
        $this->db->group_by('item_qo.customer_id');
        $this->db->group_by('item_qo.item_id');
        if(!empty($search))
        {

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
        $return_query = $this->db->get();
        return $return_query;
    }

    /**
     * Gets total of rows
     */
    public function get_total_rows($customer_id)
    {
        $this->db->from('item_quantities_outlet');
        $this->db->where('customer_id', $customer_id);
        $this->db->group_by('item_qo.customer_id');
        $this->db->group_by('item_qo.item_id');
        return $this->db->count_all_results();
    }

    /**
     * Checks if sale exists
     */
    public function exists($customer_id)
    {
        $this->db->from('item_quantities_outlet');
        $this->db->where('customer_id', $customer_id);
        $this->db->group_by('item_qo.customer_id');
        $this->db->group_by('item_qo.item_id');
        return ($this->db->get()->num_rows()==1);
    }
    /**
     * Gets sale customer name
     */
    public function get_customer($customer_id)
    {
        return $this->Customer->get_info($customer_id);
    }
}
?>