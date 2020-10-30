<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package mobilepos-wp-plugin
     * @version 0.1.0
     * Data for Mobilepos access.
    */

    $access_data = "
        ( sha2(1 , 256), 'product','Add Products', 'add_products' ),
        ( sha2(2 , 256), 'product','Edit Products', 'edit_products' ),
        ( sha2(3 , 256), 'product','Delete Products', 'delete_products' ),
        ( sha2(4 , 256), 'store','Edit Store', 'edit_store' ),
        ( sha2(5 , 256), 'category','Add Categories', 'add_category' ),
        ( sha2(6 , 256), 'category','Edit Categories', 'edit_category' ),
        ( sha2(7 , 256), 'category','Delete Categories', 'delete_category' ),
        ( sha2(8 , 256), 'variant','Add Variant', 'add_variant' ),
        ( sha2(9 , 256), 'variant','Edit Variant', 'edit_variant' ),
        ( sha2(10 , 256), 'variant','Delete Variant', 'delete_variant' ),
        ( sha2(11 , 256), 'document','Add Document', 'add_document' ),
        ( sha2(12 , 256), 'document','Edit Document', 'edit_document' ),
        ( sha2(13 , 256), 'document','Delete Document', 'delete_document' ),
        ( sha2(14 , 256), 'coupon','Add Coupon', 'add_coupon' ),
        ( sha2(15 , 256), 'coupon','Edit Coupon', 'edit_coupon' ),
        ( sha2(16 , 256), 'coupon','Delete Coupon', 'delete_coupon' ),
        ( sha2(17 , 256), 'order','Add Order', 'add_order' ),
        ( sha2(18 , 256), 'order','Edit Order', 'edit_order' ),
        ( sha2(19 , 256), 'order','Delete Order', 'delete_order' ),
        ( sha2(20 , 256), 'report','Show Report', 'show_report' ),
        ( sha2(21 , 256), 'report','Export Report', 'export_report' ),
        ( sha2(22 , 256), 'dashboard', 'Accept Order', 'accept_order' ),
        ( sha2(23 , 256), 'dashboard', 'Show Dashboard', 'show_dashboard' ),
        ( sha2(24 , 256), 'schedule','Add Shedule', 'add_shedule' ),
        ( sha2(25 , 256), 'schedule','Edit Schedule', 'edit_schedule' ),
        ( sha2(26 , 256), 'schedule','Delete Schedule', 'delete_schedule' ),
        ( sha2(27 , 256), 'operation','Add operation', 'add_operation' ),
        ( sha2(28 , 256), 'operation','Edit Operation', 'edit_operation' ),
        ( sha2(29 , 256), 'operation','Delete Operation', 'delete_operation' ),
        ( sha2(30 , 256), 'personnel','Add Personnel', 'add_personnel' ),
        ( sha2(31 , 256), 'personnel','Edit Personnel', 'edit_personnel' ),
        ( sha2(32 , 256), 'personnel','Delete Personnel', 'delete_personnel' ),
        ( sha2(33 , 256), 'role','Add Role', 'add_role' ),
        ( sha2(34 , 256), 'role','Edit Role', 'edit_role' ),
        ( sha2(35 , 256), 'role','Delete Role', 'delete_role' ),
        ( sha2(36 , 256), 'wallet','Change Wallet', 'change_wallet' ),
        ( sha2(37 , 256), 'wallet','View Wallet', 'view_wallet' ),
        ( sha2(38 , 256), 'wallet','Create Wallet', 'create_wallet' );
        ";
