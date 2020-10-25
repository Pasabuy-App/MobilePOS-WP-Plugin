<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package mobilepos-wp-plugin
     * @version 0.1.0
     * This is where you provide all the constant config.
	*/

	//Defining Global Variables
	// VERSION ONE
		define('MP_PREFIX', 'mp_');

		//Configs config
		define('MP_CONFIGS_TABLE', MP_PREFIX.'configs');

		//Inventory config
		define('MP_INVENTORY_TABLE', MP_PREFIX.'inventory');

		//Operations config
		define('MP_OPERATIONS_TABLE', MP_PREFIX.'operations');

		//Orders config
		define('MP_ORDERS_TABLE', MP_PREFIX.'orders');
		define("MP_ORDER_TABLE_FIELD", "(stid, opid, wpid, created_by, status, method, date_created)");

		//Order Items config
		define('MP_ORDER_ITEMS_TABLE', MP_PREFIX.'order_items');
		define('MP_ORDER_ITEM_VARS_TABLE', MP_PREFIX.'order_item_variant');
		define("MP_ORDER_ITEMS_TABLE_FIELD", "(odid, pdid, quantity, status, date_created)");

		//Revisions config
		define('MP_REVISIONS_TABLE', MP_PREFIX.'revisions');
		define("MP_REVISIONS_TABLE_FIELD", "(revs_type, parent_id, child_key, child_val, created_by, date_created)");

	// END

	// VERSION TWO

		define('MP_PREFIX_v2', 'mp_v2_');

		define('MP_ROLES_v2', MP_PREFIX_v2.'roles');
		define('MP_ROLES_FILED_v2', ' `title`, `info`, `created_by` ');

		define('MP_ORDERS_v2', MP_PREFIX_v2.'orders');
		define('MP_ORDERS_FILED_v2', ' `opid`, `stages`, `adid`, `instructions`, `order_by` ');

		define('MP_ORDERS_ITEMS_v2', MP_PREFIX_v2.'orders_items');
		define('MP_ORDERS_ITEMS_FIELD_v2', ' `odid`, `pdid`, `quantity`, `created_by` ');

		define('MP_ORDERS_ITEMS_VARS_v2', MP_PREFIX_v2.'orders_items_vars');
		define('MP_ORDERS_ITEMS_VARS_FIELD_v2', ' `otid`, `vrid`, `created_by` ');

		define('MP_ACCESS_v2', MP_PREFIX_v2.'access');
		define('MP_ACCESS_FIELD_v2', ' `groups`, `actions` ');
		define('MP_ACCESS_DATA_v2', $access_data);

		define('MP_PERMISSION_v2', MP_PREFIX_v2.'permission');
		define('MP_PERMISSION_FIELD_v2', ' `roid`, `access`, `assigned_by` ');

		define('MP_PERSONNELS_v2', MP_PREFIX_v2.'personnels');
		define('MP_PERSONNELS_FIELD_v2', ' `stid`, `wpid`, `roid`, `pincode`, `assigned_by` ');

		define('MP_SCHEDULES_v2', MP_PREFIX_v2.'schedule');
		define('MP_SCHEDULES_FIELD_v2', ' `stid`, `types`, `started`, `ended` ');

		define('MP_OPERATIONS_v2', MP_PREFIX_v2.'operation');
		define('MP_OPERATIONS_FIELD_v2', ' `stid`, `sdid`, `created_by` ');

		define('MP_COUPONS_v2', MP_PREFIX_v2.'coupons');
		define('MP_COUPONS_FIELD_v2', ' `pdid`, `title`, `info`, `action`, `extra`, `limit`, `expiry`, `created_by` ');

		define('MP_COUPONS_USAGE_v2', MP_PREFIX_v2.'coupons_usage');
		define('MP_COUPONS_USAGE_FIELD_v2', ' `cpid` ');

		define('MP_PAYMENTS_v2', MP_PREFIX.'payments');
		define('MP_PAYMENTS_FIELD_v2', ' `odid`, `method`, `extra`, `amount` ');

		define('MP_WALLETS_v2', MP_PREFIX.'wallets');
		define('MP_WALLETS_FIELD_v2', ' `stid`, `pubkey`, `assigned_by` ');
	// END
