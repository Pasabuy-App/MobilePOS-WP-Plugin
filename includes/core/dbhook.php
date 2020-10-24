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
		$tbl_personnel= MP_PERSONNELS;
		$tbl_schedule = MP_SCHEDULES;
		$tbl_operation = MP_OPERATIONS;
		$tbl_coupon = MP_COUPONS;
		$tbl_coupon_usage = MP_COUPONS_USAGE;

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

		//Database table creation for permission
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_personnel'" ) != $tbl_personnel) {
			$sql = "CREATE TABLE `".$tbl_personnel."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL, ";
				$sql .= " `stid` varchar(150) NOT NULL  COMMENT 'Store ID.', ";
				$sql .= " `wpid` bigint(20) NOT NULL  COMMENT 'Wordpres Id',  ";
				$sql .= " `roid` varchar(255) NOT NULL  COMMENT 'Role id of this permission',  ";
				$sql .= " `pincode` varchar(255) NOT NULL  COMMENT 'Pincode of this personnel',  ";
				$sql .= " `activated` enum('true', 'false') NOT NULL  COMMENT 'Status of this personnel',  ";
				$sql .= " `status` enum('active', 'inactive') NOT NULL  COMMENT 'Status of this personnel',  ";
				$sql .= " `assigned_by` bigint(20) NOT NULL  COMMENT 'The one who assigned of this personnel.',  ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX `wpid` ON $tbl_personnel (`wpid`);");
			$wpdb->query("CREATE INDEX `stid` ON $tbl_personnel (`stid`);");
			$wpdb->query("CREATE INDEX `status` ON $tbl_personnel (`status`);");
			$wpdb->query("CREATE INDEX `hsid` ON $tbl_personnel (`hsid`);");

		}

		//Database table creation for mover documents schedule
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_schedule'" ) != $tbl_schedule) {
			$sql = "CREATE TABLE `".$tbl_schedule."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
				$sql .= " `stid` varchar(150) NOT NULL COMMENT 'Store ID' , ";
				$sql .= " `types` enum('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun') NOT NULL , ";
				$sql .= " `started` datetime NOT NULL , ";
				$sql .= " `ended` datetime NOT NULL,  ";
				$sql .= " `activated` enum('false', 'true') NOT NULL COMMENT 'Status of this schedule', ";
				$sql .= " `executed_by` bigint(20) COMMENT 'The one who approve this schedule', ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX hsid ON $tbl_schedule (hsid);");
			$wpdb->query("CREATE INDEX mvid ON $tbl_schedule (mvid);");
			$wpdb->query("CREATE INDEX types ON $tbl_schedule (types);");
			$wpdb->query("CREATE INDEX `started` ON $tbl_schedule (`started`);");
			$wpdb->query("CREATE INDEX ended ON $tbl_schedule (ended);");
			$wpdb->query("CREATE INDEX date_created ON $tbl_schedule (date_created);");
		}

		//Database table creation for mover Operations
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_operation'" ) != $tbl_operation) {
			$sql = "CREATE TABLE `".$tbl_operation."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
				$sql .= " `stid` varchar(150) NOT NULL COMMENT 'Mover ID' , ";
				$sql .= " `sdid` varchar(150) NOT NULL , ";
				$sql .= " `created_by` bigint(20) COMMENT 'The one who creates this operation', ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX hsid ON $tbl_operation (hsid);");
			$wpdb->query("CREATE INDEX stid ON $tbl_operation (stid);");
			$wpdb->query("CREATE INDEX sdid ON $tbl_operation (sdid);");
			$wpdb->query("CREATE INDEX date_created ON $tbl_operation (date_created);");
		}

		//Database table creation for mover Operations
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_coupon'" ) != $tbl_coupon) {
			$sql = "CREATE TABLE `".$tbl_coupon."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
				$sql .= " `pdid` varchar(150) NOT NULL COMMENT 'Product ID' , ";
				$sql .= " `title` varchar(150) NOT NULL , ";
				$sql .= " `info` varchar(150) NOT NULL , ";
				$sql .= " `limit` tinyint(50) NOT NULL , ";
				$sql .= " `extra` varchar(150) NOT NULL , ";
				$sql .= " `action` enum('free_ship','discount','min_spend','less') , ";
				$sql .= " `expiry` datetime  , ";
				$sql .= " `created_by` bigint(20) COMMENT 'The one who creates this counpon', ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX hsid ON $tbl_coupon (hsid);");
			$wpdb->query("CREATE INDEX pdid ON $tbl_coupon (pdid);");
			$wpdb->query("CREATE INDEX expiry ON $tbl_coupon (expiry);");
			$wpdb->query("CREATE INDEX date_created ON $tbl_coupon (date_created);");
		}


		//Database table creation for mover Operations
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_coupon_usage'" ) != $tbl_coupon_usage) {
			$sql = "CREATE TABLE `".$tbl_coupon_usage."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
				$sql .= " `cpid` varchar(255) NOT NULL COMMENT 'Coupon ID' , ";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);

			$wpdb->query("CREATE INDEX hsid ON $tbl_coupon_usage (hsid);");
			$wpdb->query("CREATE INDEX date_created ON $tbl_coupon_usage (date_created);");
		}

	}
    add_action( 'activated_plugin', 'mp_dbhook_activate' );