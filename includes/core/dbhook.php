<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package mobilepos-wp-plugin
     * @version 0.1.0
     * Here is where you add hook to WP to create our custom database if not found.
	*/

	function mp_dbhook_activate() {

		global $wpdb;

		//Initializing table name
		$tbl_configs = MP_CONFIGS_TABLE;
		$tbl_inventory = MP_INVENTORY_TABLE;
		$tbl_operations = MP_OPERATIONS_TABLE;
		$tbl_orders = MP_ORDERS_TABLE;
		$tbl_order_items = MP_ORDER_ITEMS_TABLE;
		$tbl_revisions = MP_REVISIONS_TABLE;

		//Database table creation for config
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_configs'" ) != $tbl_configs) {
			$sql = "CREATE TABLE `".$tbl_configs."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL, ";
				$sql .= "`config_desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Config Description', ";
				$sql .= "`config_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Config KEY', ";
				$sql .= "`config_value` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Config VALUES', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for inventory
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_inventory'" ) != $tbl_inventory) {
			$sql = "CREATE TABLE `".$tbl_inventory."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL, ";
				$sql .= "`pdid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Product ID.', ";
				$sql .= "`stid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Store ID.',  ";
				$sql .= "`wpid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID.', ";
				$sql .= "`odid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Order ID.', ";
				$sql .= "`quantity` bigint(20) NOT NULL DEFAULT 0 COMMENT 'No of items.', ";
				$sql .= "`date_created` datetime(0) NULL DEFAULT NULL COMMENT 'The date this inventory was created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for operations
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_operations'" ) != $tbl_operations) {
			$sql = "CREATE TABLE `".$tbl_operations."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL, ";
				$sql .= "`date_open` datetime(0) NULL DEFAULT NULL COMMENT 'Date and time of opening.', ";
				$sql .= "`date_close` datetime(0) NULL DEFAULT NULL COMMENT 'Date and time of closing.', ";
				$sql .= "`open_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who encoded this.', ";
				$sql .= "`close_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who encoded this',  ";
				$sql .= "`stid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Store id which this operation belongs to.',  ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for orders
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_orders'" ) != $tbl_orders) {
			$sql = "CREATE TABLE `".$tbl_orders."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL, ";
				$sql .= "`stid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Store id which this order belongs to', ";
				$sql .= "`opid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Operation id which this order belongs to',  ";
				$sql .= "`wpid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id',  ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who created this order',  ";
				$sql .= "`status` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Status revision id (stage)',  ";
				$sql .= "`method` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Payment method.',  ";
				$sql .= "`date_created` datetime(0) NULL DEFAULT NULL COMMENT 'The date this inventory was created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for order items
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_order_items'" ) != $tbl_order_items) {
			$sql = "CREATE TABLE `".$tbl_order_items."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL, ";
				$sql .= "`odid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Order id which this item belongs to', ";
				$sql .= "`pdid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Product id of this item',  ";
				$sql .= "`quantity` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Quantity revision id', ";
				$sql .= "`status` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Status revision id 1 or 0',  ";
				$sql .= "`date_created` datetime(0) NULL DEFAULT NULL COMMENT 'The date this order was created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for revisions
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_revisions'" ) != $tbl_revisions) {
			$sql = "CREATE TABLE `".$tbl_revisions."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL, ";
				$sql .= "`revs_type` enum('none','configs','orders','order_items', 'operations') NOT NULL DEFAULT 'none' COMMENT 'Target table', ";
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent id of this revision',  ";
				$sql .= "`child_key` varchar(20) NOT NULL DEFAULT 0 COMMENT 'Column name on the table',  ";
				$sql .= "`child_val` varchar(50) NOT NULL DEFAULT 0 COMMENT 'Value of the row key',  ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who created this revision',  ";
				$sql .= "`date_created` datetime(0) NULL DEFAULT NULL COMMENT 'The date this revision was created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

	}
    add_action( 'activated_plugin', 'mp_dbhook_activate' );