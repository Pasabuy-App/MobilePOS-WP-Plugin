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
	define('MP_PREFIX', 'mp_'); 

	//Configs config
	define('MP_CONFIGS_TABLE', MP_PREFIX.'configs');

	//Inventory config
	define('MP_INVENTORY_TABLE', MP_PREFIX.'inventory');

	//Operations config
	define('MP_OPERATIONS_TABLE', MP_PREFIX.'operations');

	//Orders config
	define('MP_ORDERS_TABLE', MP_PREFIX.'orders');
	define("MP_ORDER_TABLE_FIELD", "(stid, opid, wpid, created_by, status, date_created)");

	//Order Items config
	define('MP_ORDER_ITEMS_TABLE', MP_PREFIX.'order_items');
	define("MP_ORDER_ITEMS_TABLE_FIELD", "(odid, pdid, quantity, status, date_created)");

	//Revisions config
	define('MP_REVISIONS_TABLE', MP_PREFIX.'revisions');
	define("MP_REVISIONS_TABLE_FIELD", "(revs_type, parent_id, child_key, child_val, created_by, date_created)");