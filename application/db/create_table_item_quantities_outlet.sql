CREATE TABLE `ospos_db`.`ospos_item_quantities_outlet` (
                                                           `item_id` int(11) NOT NULL,
                                                           `location_id` int(11) NOT NULL,
                                                           `quantity` decimal(15,3) NOT NULL DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `ospos_db`.`ospos_item_quantities_outlet` ADD PRIMARY KEY (`item_id`,`location_id`), ADD KEY `item_id` (`item_id`), ADD KEY `location_id` (`location_id`);