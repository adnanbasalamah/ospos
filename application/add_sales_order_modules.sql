INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
('module_sales_order', 'module_sales_order_desc', 75, 'sales_order');
INSERT INTO `ospos_permissions` (`permission_id`, `module_id`, `location_id`) VALUES
('sales_order', 'sales_order', NULL);