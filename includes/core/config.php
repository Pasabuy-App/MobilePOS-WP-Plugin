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

	define('MP_ROLES', MP_PREFIX.'roles');
	define('MP_ROLES_FILED', ' `title`, `info`, `created_by` ');

	define('MP_ORDERS', MP_PREFIX.'orders');
	define('MP_ORDERS_FILED', ' `opid`, `stages`, `adid`, `method`, `instructions`, `order_by` ');

	define('MP_ORDERS_ITEMS', MP_PREFIX.'orders_items');
	define('MP_ORDERS_ITEMS_FIELD', ' `odid`, `pdid`, `quantity`, `created_by` ');

	define('MP_ORDERS_ITEMS_VARS', MP_PREFIX.'orders_items_vars');
	define('MP_ORDERS_ITEMS_VARS_FIELD', ' `otid`, `vrid`, `created_by` ');
