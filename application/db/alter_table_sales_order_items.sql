ALTER TABLE `ospos_sales_order_items`
ADD `delivery_status` tinyint(1) NOT NULL DEFAULT '0',
ADD `qty_shipped` double NULL AFTER `delivery_status`,
ADD `qty_delivered` double NULL AFTER `qty_shipped`,
ADD `supplier_id` int NULL AFTER `qty_delivered`;