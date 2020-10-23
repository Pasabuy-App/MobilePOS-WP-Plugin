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
		$tbl_roles = MP_ROLES;
		$tbl_orders = MP_ORDERS;
		$tbl_orders_items = MP_ORDERS_ITEMS;
		$tbl_orders_items_vars = MP_ORDERS_ITEMS_VARS;
		$tbl_access = MP_ACCESS;
		$tbl_permission = MP_PERMISSION;

		//Database table creation for revisions
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_roles'" ) != $tbl_roles) {
			$sql = "CREATE TABLE `".$tbl_roles."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hsid` varchar(255) NOT NULL, ";
				$sql .= "`title` varchar(100) NOT NULL  COMMENT 'Title of role.', ";
				$sql .= "`info` varchar(200) NOT NULL  COMMENT 'Information of this role',  ";
				$sql .= "`status` enum('active', 'inactive') NOT NULL COMMENT 'Status of this role.',  ";
				$sql .= "`created_by` bigint(20) NOT NULL COMMENT 'The one who creates thos role.',  ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX title ON $tbl_roles (title);");
			$wpdb->query("CREATE INDEX `status` ON $tbl_roles (`status`);");
			$wpdb->query("CREATE INDEX `date_created` ON $tbl_roles (`date_created`);");

		}

		//Database table creation for orders
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_orders'" ) != $tbl_orders) {
			$sql = "CREATE TABLE `".$tbl_orders."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL, ";
				$sql .= " `pubkey` varchar(100) NOT NULL  COMMENT 'Public Key of this order.', ";
				$sql .= " `opid` varchar(200) NOT NULL  COMMENT 'Store operation hsid',  ";
				$sql .= " `stages` enum('pending', 'accepted', 'ongoing', 'preparing', 'shipping', 'completed', 'cancelled') NOT NULL COMMENT 'Stage of this order.',  ";
				$sql .= " `status` enum('active', 'inactive') NOT NULL COMMENT 'Status of this order.',  ";
				$sql .= " `adid` bigint(20) NOT NULL COMMENT 'Address ID of this order.',  ";
				$sql .= " `method` enum('cash', 'wallet', 'card') NOT NULL COMMENT 'Method choosen for this order.',  ";
				$sql .= " `instructions` varchar(255) NOT NULL COMMENT 'Additional instruction of this order.',  ";
				$sql .= " `order_by` bigint(20) NOT NULL COMMENT 'The one who created this order.',  ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX `pubkey` ON $tbl_roles (`pubkey`);");
			$wpdb->query("CREATE INDEX `stages` ON $tbl_roles (`stages`);");

		}

		//Database table creation for orders_items
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_orders_items'" ) != $tbl_orders_items) {
			$sql = "CREATE TABLE `".$tbl_orders_items."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL, ";
				$sql .= " `odid` varchar(150) NOT NULL  COMMENT 'Order hsid.', ";
				$sql .= " `pdid` varchar(150) NOT NULL  COMMENT 'Product hsid',  ";
				$sql .= " `quantity` int(50) NOT NULL COMMENT 'Quantity hsid.',  ";
				$sql .= " `status` enum('active', 'inactive') NOT NULL COMMENT 'Status of this order items.',  ";
				$sql .= " `created_by` bigint(20) NOT NULL COMMENT 'The one who creates this order items.',  ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX `hsid` ON $tbl_orders_items (`hsid`);");

		}

		//Database table creation for orders_items
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_orders_items_vars'" ) != $tbl_orders_items_vars) {
			$sql = "CREATE TABLE `".$tbl_orders_items_vars."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL, ";
				$sql .= " `otid` varchar(150) NOT NULL  COMMENT 'Order item hsid.', ";
				$sql .= " `vrid` varchar(150) NOT NULL  COMMENT 'Variant hsid',  ";
				$sql .= " `created_by` bigint(20) NOT NULL COMMENT 'The one who creates this order items vars.',  ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX `hsid` ON $tbl_orders_items_vars (`hsid`);");
			$wpdb->query("CREATE INDEX `date_created` ON $tbl_orders_items_vars (`date_created`);");
		}

		//Database table creation for orders_items
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_access'" ) != $tbl_access) {
			$sql = "CREATE TABLE `".$tbl_access."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL, ";
				$sql .= " `groups` enum('product', 'store', 'category', 'variant', 'document', 'coupon', 'order', 'report', 'dashboard', 'schedule', 'operation', 'personnel', 'role') NOT NULL  COMMENT 'Categories of access.', ";
				$sql .= " `title` varchar(150) NOT NULL  COMMENT 'Title of access to be displayed in app',  ";
				$sql .= " `actions` varchar(150) NOT NULL  COMMENT 'Access key',  ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX `actions` ON $tbl_access (`actions`);");
			$wpdb->query("CREATE INDEX `hsid` ON $tbl_access (`hsid`);");
			$data = MP_ACCESS_DATA;

			$wpdb->query("INSERT INTO $tbl_access (`hsid`, `groups`, `title`, `actions`) VALUES $data ");
			$access_id = $wpdb->insert_id;

		}

		//Database table creation for permission
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_permission'" ) != $tbl_permission) {
			$sql = "CREATE TABLE `".$tbl_permission."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL, ";
				$sql .= " `roid` varchar(255) NOT NULL  COMMENT 'Role hsid.', ";
				$sql .= " `access` varchar(255) NOT NULL  COMMENT 'Access of this permission',  ";
				$sql .= " `enabled` enum('false', 'true') NOT NULL  COMMENT 'Status of this permission',  ";
				$sql .= " `status` enum('active', 'inactive') NOT NULL  COMMENT 'Status of this permission',  ";
				$sql .= " `assigned_by` bigint(20) NOT NULL  COMMENT 'Assigned of this permission',  ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX `access` ON $tbl_permission (`access`);");
			$wpdb->query("CREATE INDEX `status` ON $tbl_permission (`status`);");
			$wpdb->query("CREATE INDEX `hsid` ON $tbl_permission (`hsid`);");

		}

	}
    add_action( 'activated_plugin', 'mp_dbhook_activate' );