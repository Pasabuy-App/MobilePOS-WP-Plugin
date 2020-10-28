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
		//Initializing table name of version one
			$tbl_configs = MP_CONFIGS_TABLE;
			$tbl_inventory = MP_INVENTORY_TABLE;
			$tbl_operations = MP_OPERATIONS_TABLE;
			$tbl_orders = MP_ORDERS_TABLE;
			$tbl_order_items = MP_ORDER_ITEMS_TABLE;
			$tbl_order_items_vars = MP_ORDER_ITEM_VARS_TABLE;
			$tbl_revisions = MP_REVISIONS_TABLE;

		//Initializing table name of version two

			$tbl_roles_v2 = MP_ROLES_v2;
			$tbl_orders_v2 = MP_ORDERS_v2;
			$tbl_orders_items_v2 = MP_ORDERS_ITEMS_v2;
			$tbl_orders_items_vars_v2 = MP_ORDERS_ITEMS_VARS_v2;
			$tbl_access_v2 = MP_ACCESS_v2;
			$tbl_permission_v2 = MP_PERMISSION_v2;
			$tbl_personnel_v2= MP_PERSONNELS_v2;
			$tbl_schedule_v2 = MP_SCHEDULES_v2;
			$tbl_operation_v2 = MP_OPERATIONS_v2;
			$tbl_coupon_v2 = MP_COUPONS_v2;
			$tbl_coupon_usage_v2 = MP_COUPONS_USAGE_v2;
			$tbl_payments_v2 = MP_PAYMENTS_v2;
			$tbl_wallet_v2 = MP_WALLETS_v2;

		// Database creation for version one

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
					$sql .= "`sched_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Schedule id from tp_schedule.',  ";
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

			//Database table creation for order items
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_order_items_vars'" ) != $tbl_order_items_vars) {
				$sql = "CREATE TABLE `".$tbl_order_items_vars."` (";
					$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= "`hash_id` varchar(255) NOT NULL, ";
					$sql .= "`vrid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Variant ID', ";
					$sql .= "`item_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Order item id',  ";
					$sql .= "`date_created` datetime(0) NULL DEFAULT current_timestamp() COMMENT 'The date this order was created.', ";
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

		// End

		//Database table creation version two
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_roles_v2'" ) != $tbl_roles_v2) {
				$sql = "CREATE TABLE `".$tbl_roles_v2."` (";
					$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= "`hsid` varchar(255) NOT NULL, ";
					$sql .= "`title` varchar(100) NOT NULL  COMMENT 'Title of role.', ";
					$sql .= "`info` varchar(200) NOT NULL  COMMENT 'Information of this role',  ";
					$sql .= "`stid` varchar(200) NOT NULL  COMMENT 'Store id of this role',  ";
					$sql .= "`status` enum('active', 'inactive') NOT NULL COMMENT 'Status of this role.',  ";
					$sql .= "`created_by` bigint(20) NOT NULL COMMENT 'The one who creates thos role.',  ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX title ON $tbl_roles_v2 (title);");
				$wpdb->query("CREATE INDEX `status` ON $tbl_roles_v2 (`status`);");
				$wpdb->query("CREATE INDEX `date_created` ON $tbl_roles_v2 (`date_created`);");

			}

			//Database table creation for orders
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_orders_v2'" ) != $tbl_orders_v2) {
				$sql = "CREATE TABLE `".$tbl_orders_v2."` (";
					$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= " `hsid` varchar(255) NOT NULL, ";
					$sql .= " `pubkey` varchar(100) NOT NULL  COMMENT 'Public Key of this order.', ";
					$sql .= " `opid` varchar(200) NOT NULL  COMMENT 'Store operation hsid',  ";
					$sql .= " `stages` enum('pending', 'accepted', 'ongoing', 'preparing', 'shipping', 'completed', 'cancelled') NOT NULL COMMENT 'Stage of this order.',  ";
					$sql .= " `status` enum('active', 'inactive') NOT NULL COMMENT 'Status of this order.',  ";
					$sql .= " `adid` bigint(20) NOT NULL COMMENT 'Address ID of this order.',  ";
					$sql .= " `instructions` varchar(255) NOT NULL COMMENT 'Additional instruction of this order.',  ";
					$sql .= " `delivery_charges` varchar(150) NOT NULL COMMENT 'Method choosen for this order.',  ";
					$sql .= " `psb_fee` double(6, 2) NOT NULL COMMENT 'Method choosen for this order.',  ";
					$sql .= " `order_by` bigint(20) NOT NULL COMMENT 'The one who created this order.',  ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX `pubkey` ON $tbl_orders_v2 (`pubkey`);");
				$wpdb->query("CREATE INDEX `stages` ON $tbl_orders_v2 (`stages`);");

			}

			//Database table creation for orders_items
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_orders_items_v2'" ) != $tbl_orders_items_v2) {
				$sql = "CREATE TABLE `".$tbl_orders_items_v2."` (";
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

				$wpdb->query("CREATE INDEX `hsid` ON $tbl_orders_items_v2 (`hsid`);");

			}

			//Database table creation for orders_items
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_orders_items_vars_v2'" ) != $tbl_orders_items_vars_v2) {
				$sql = "CREATE TABLE `".$tbl_orders_items_vars_v2."` (";
					$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= " `hsid` varchar(255) NOT NULL, ";
					$sql .= " `otid` varchar(150) NOT NULL  COMMENT 'Order item hsid.', ";
					$sql .= " `vrid` varchar(150) NOT NULL  COMMENT 'Variant hsid',  ";
					$sql .= " `created_by` bigint(20) NOT NULL COMMENT 'The one who creates this order items vars.',  ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX `hsid` ON $tbl_orders_items_vars_v2 (`hsid`);");
				$wpdb->query("CREATE INDEX `date_created` ON $tbl_orders_items_vars_v2 (`date_created`);");
			}

			//Database table creation for orders_items
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_access_v2'" ) != $tbl_access_v2) {
				$sql = "CREATE TABLE `".$tbl_access_v2."` (";
					$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= " `hsid` varchar(255) NOT NULL, ";
					$sql .= " `groups` enum('product', 'store', 'category', 'wallet', 'variant', 'document', 'coupon', 'order', 'report', 'dashboard', 'schedule', 'operation', 'personnel', 'role') NOT NULL  COMMENT 'Categories of access.', ";
					$sql .= " `title` varchar(150) NOT NULL  COMMENT 'Title of access to be displayed in app',  ";
					$sql .= " `actions` varchar(150) NOT NULL  COMMENT 'Access key',  ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX `actions` ON $tbl_access_v2 (`actions`);");
				$wpdb->query("CREATE INDEX `hsid` ON $tbl_access_v2 (`hsid`);");
				$data = MP_ACCESS_DATA_v2;

				$wpdb->query("INSERT INTO $tbl_access_v2 (`hsid`, `groups`, `title`, `actions`) VALUES $data ");
				$access_id = $wpdb->insert_id;

			}

			//Database table creation for permission
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_permission_v2'" ) != $tbl_permission_v2) {
				$sql = "CREATE TABLE `".$tbl_permission_v2."` (";
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

				$wpdb->query("CREATE INDEX `access` ON $tbl_permission_v2 (`access`);");
				$wpdb->query("CREATE INDEX `status` ON $tbl_permission_v2 (`status`);");
				$wpdb->query("CREATE INDEX `hsid` ON $tbl_permission_v2 (`hsid`);");

			}

			//Database table creation for permission
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_personnel_v2'" ) != $tbl_personnel_v2) {
				$sql = "CREATE TABLE `".$tbl_personnel_v2."` (";
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

				$wpdb->query("CREATE INDEX `wpid` ON $tbl_personnel_v2 (`wpid`);");
				$wpdb->query("CREATE INDEX `stid` ON $tbl_personnel_v2 (`stid`);");
				$wpdb->query("CREATE INDEX `status` ON $tbl_personnel_v2 (`status`);");
				$wpdb->query("CREATE INDEX `hsid` ON $tbl_personnel_v2 (`hsid`);");

			}

			//Database table creation for mover documents schedule
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_schedule_v2'" ) != $tbl_schedule_v2) {
				$sql = "CREATE TABLE `".$tbl_schedule_v2."` (";
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

				$wpdb->query("CREATE INDEX hsid ON $tbl_schedule_v2 (hsid);");
				$wpdb->query("CREATE INDEX mvid ON $tbl_schedule_v2 (mvid);");
				$wpdb->query("CREATE INDEX types ON $tbl_schedule_v2 (types);");
				$wpdb->query("CREATE INDEX `started` ON $tbl_schedule_v2 (`started`);");
				$wpdb->query("CREATE INDEX ended ON $tbl_schedule_v2 (ended);");
				$wpdb->query("CREATE INDEX date_created ON $tbl_schedule_v2 (date_created);");
			}

			//Database table creation for mover Operations
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_operation_v2'" ) != $tbl_operation_v2) {
				$sql = "CREATE TABLE `".$tbl_operation_v2."` (";
					$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
					$sql .= " `stid` varchar(150) NOT NULL COMMENT 'Mover ID' , ";
					$sql .= " `sdid` varchar(150) NOT NULL , ";
					$sql .= " `created_by` bigint(20) COMMENT 'The one who creates this operation', ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX hsid ON $tbl_operation_v2 (hsid);");
				$wpdb->query("CREATE INDEX stid ON $tbl_operation_v2 (stid);");
				$wpdb->query("CREATE INDEX sdid ON $tbl_operation_v2 (sdid);");
				$wpdb->query("CREATE INDEX date_created ON $tbl_operation_v2 (date_created);");
			}

			//Database table creation for mover Operations
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_coupon_v2'" ) != $tbl_coupon_v2) {
				$sql = "CREATE TABLE `".$tbl_coupon_v2."` (";
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

				$wpdb->query("CREATE INDEX hsid ON $tbl_coupon_v2 (hsid);");
				$wpdb->query("CREATE INDEX pdid ON $tbl_coupon_v2 (pdid);");
				$wpdb->query("CREATE INDEX expiry ON $tbl_coupon_v2 (expiry);");
				$wpdb->query("CREATE INDEX date_created ON $tbl_coupon_v2 (date_created);");
			}


			//Database table creation for mover Operations
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_coupon_usage_v2'" ) != $tbl_coupon_usage_v2) {
				$sql = "CREATE TABLE `".$tbl_coupon_usage_v2."` (";
					$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
					$sql .= " `cpid` varchar(255) NOT NULL COMMENT 'Coupon ID' , ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX hsid ON $tbl_coupon_usage_v2 (hsid);");
				$wpdb->query("CREATE INDEX date_created ON $tbl_coupon_usage_v2 (date_created);");
			}

			//Database table creation for mover Operations
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_payments_v2'" ) != $tbl_payments_v2) {
				$sql = "CREATE TABLE `".$tbl_payments_v2."` (";
					$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
					$sql .= " `odid` varchar(255) NOT NULL COMMENT 'Order ID' , ";
					$sql .= " `method` enum('card', 'cash', 'savings', 'pluss', 'coupon') NOT NULL COMMENT 'Method used for this payment' , ";
					$sql .= " `extra` varchar(255) NOT NULL COMMENT 'Coupon ID' , ";
					$sql .= " `amount` int(20) NOT NULL COMMENT 'Amount of payments' , ";
					$sql .= " `status` enum('active', 'inactive') NOT NULL COMMENT 'Status of this payment transactiom' , ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX hsid ON $tbl_payments_v2 (hsid);");
				$wpdb->query("CREATE INDEX date_created ON $tbl_payments_v2 (date_created);");
			}

			//Database table creation for mover Operations
			if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_wallet_v2'" ) != $tbl_wallet_v2) {
				$sql = "CREATE TABLE `".$tbl_wallet_v2."` (";
					$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
					$sql .= " `hsid` varchar(255) NOT NULL COMMENT 'This column is used for table realtionship' , ";
					$sql .= " `stid` varchar(255) NOT NULL COMMENT 'Store ID' , ";
					$sql .= " `pubkey` varchar(255) NOT NULL COMMENT 'Public key of this wallet' , ";
					$sql .= " `assigned_by` varchar(150) NOT NULL COMMENT 'User ID' , ";
					$sql .= " `status` enum('active', 'inactive') NOT NULL COMMENT 'Status of this payment transactiom' , ";
					$sql .= " `created_by` bigint(20) NOT NULL COMMENT 'Created this wallet' , ";
					$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp(), ";
					$sql .= "PRIMARY KEY (`ID`) ";
					$sql .= ") ENGINE = InnoDB; ";
				$result = $wpdb->get_results($sql);

				$wpdb->query("CREATE INDEX hsid ON $tbl_wallet_v2 (hsid);");
				$wpdb->query("CREATE INDEX pubkey ON $tbl_wallet_v2 (pubkey);");
				$wpdb->query("CREATE INDEX date_created ON $tbl_wallet_v2 (date_created);");
			}
		// End
	}
    add_action( 'activated_plugin', 'mp_dbhook_activate' );