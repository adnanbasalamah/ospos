CREATE TABLE `ospos_db`.`ospos_inventory_outlet` (
                                                     `trans_id` int(11) NOT NULL,
                                                     `trans_items` int(11) NOT NULL DEFAULT 0,
                                                     `trans_user` int(11) NOT NULL DEFAULT 0,
                                                     `trans_date` timestamp NOT NULL DEFAULT current_timestamp(),
                                                     `trans_comment` text NOT NULL,
                                                     `trans_location` int(11) NOT NULL,
                                                     `trans_inventory` decimal(15,3) NOT NULL DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `ospos_db`.`ospos_inventory_outlet` ADD PRIMARY KEY (`trans_id`), ADD KEY `trans_items` (`trans_items`), ADD KEY `trans_user` (`trans_user`), ADD KEY `trans_location` (`trans_location`), ADD KEY `trans_date` (`trans_date`);
ALTER TABLE `ospos_db`.`ospos_inventory_outlet` MODIFY `trans_id` int(11) NOT NULL AUTO_INCREMENT;