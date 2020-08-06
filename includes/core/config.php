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
?>
<?php

	//Defining Global Variables
	define('MP_PREFIX', 'mp_'); 

	define('MP_ORDER_TABLE', MP_PREFIX.'orders');
	define("MP_ORDER_TABLE_FIELD", "(stid, opid, wpid, created_by, date_created, `status`)");

	define('MP_ORDER_ITEMS_TABLE', MP_PREFIX.'order_items');
	define("MP_ORDER_ITEMS_TABLE_FIELD", "(odid, pdid, quantity, order_rev_id, date_created)");


?>