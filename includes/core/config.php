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

	// VERSION TWO

		define('MP_PREFIX_v2', 'mp_v2_');

		define('MP_CONFIGS_v2', MP_PREFIX_v2.'configs');
		define('MP_CONFIGS_FILED_v2', ' `config_key`, `config_val` ');

		define('MP_ROLES_v2', MP_PREFIX_v2.'roles');
		define('MP_ROLES_FILED_v2', ' `title`, `info`, `stid`, `created_by` ');

		define('MP_ORDERS_v2', MP_PREFIX_v2.'orders');
		define('MP_ORDERS_FILED_v2', ' `opid`, `stages`, `adid`, `instructions`, `order_by`, `expiry` ');

		define('MP_ORDERS_ITEMS_v2', MP_PREFIX_v2.'orders_items');
		define('MP_ORDERS_ITEMS_FIELD_v2', ' `odid`, `pdid`, `remarks`, `quantity`, `created_by` ');

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

		define('MP_PAYMENTS_v2', MP_PREFIX_v2.'payments');
		define('MP_PAYMENTS_FIELD_v2', ' `odid`, `method`, `extra`, `amount` ');

		define('MP_WALLETS_v2', MP_PREFIX_v2.'wallets');
		define('MP_WALLETS_FIELD_v2', ' `stid`, `pubkey`, `assigned_by`, `created_by` ');

		define('MP_INVENTORY_v2', MP_PREFIX_v2.'inventory');
		define('MP_INVENTORY_FIELD_v2', ' `pdid`, `odid`, `types`,`quantity` ');
	// END
