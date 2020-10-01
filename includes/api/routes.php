<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
        * @package mobilepos-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/
?>

<?php

    //Require the USocketNet class which have the core function of this plguin.

    // customer folder
    require plugin_dir_path(__FILE__) . '/v1/customer/class-insert.php';
    require plugin_dir_path(__FILE__) . '/v1/customer/class-cancel.php';
    require plugin_dir_path(__FILE__) . '/v1/customer/class-update.php';
    require plugin_dir_path(__FILE__) . '/v1/customer/class-delete.php';


    //Operations Classes
    require plugin_dir_path(__FILE__) . '/v1/operations/class-list-open.php';
    require plugin_dir_path(__FILE__) . '/v1/operations/class-list-month.php';
    require plugin_dir_path(__FILE__) . '/v1/operations/class-list-orders.php';
    require plugin_dir_path(__FILE__) . '/v1/operations/class-list-by-date.php';
    require plugin_dir_path(__FILE__) . '/v1/operations/class-insert.php';
    require plugin_dir_path(__FILE__) . '/v1/operations/class-update.php';

    //Orders Classes
    require plugin_dir_path(__FILE__) . '/v1/orders/class-total-sales-date.php';


    // order folder
    require plugin_dir_path(__FILE__) . '/v1/orders/class-listing.php';
    require plugin_dir_path(__FILE__) . '/v1/customer/class-orderlist.php';

    // store folder
    require plugin_dir_path(__FILE__) . '/v1/store/class-process.php';

    require plugin_dir_path(__FILE__) . '/v1/class-globals.php';

	// Init check if USocketNet successfully request from wapi.
    function mobilepos_route()
    {
        // Example
        register_rest_route( 'mobilepos/v1/user', 'auth', array(
            'methods' => 'POST',
            'callback' => array('MP_Authenticate','listen'),
        ));

        /*
         * STORE RESTAPI
        */
            register_rest_route( 'mobilepos/v1/store', 'select', array(
                'methods' => 'POST',
                'callback' => array('MP_Select_Order','listen'),
            ));

            register_rest_route( 'mobilepos/v1/store/order', 'process', array(
                'methods' => 'POST',
                'callback' => array('MP_Process','listen'),
            ));

        /*
         * ORDER RESTAPI
        */

            register_rest_route( 'mobilepos/v1/orders', 'listing', array(
                'methods' => 'POST',
                'callback' => array('MP_Order_Listing','listen'),
            ));

            register_rest_route( 'mobilepos/v1/store/order', 'cancel', array(
                'methods' => 'POST',
                'callback' => array('MP_Cancel_Order_Store','listen'),
            ));

            register_rest_route( 'mobilepos/v1/order', 'bystatus', array(
                'methods' => 'POST',
                'callback' => array('MP_OrdersByStatus','listen'),
            ));

        /*
         * CUSTOMER ORDER RESTAPI
        */
            register_rest_route( 'mobilepos/v1/customer/order', 'list', array(
                'methods' => 'POST',
                'callback' => array('MP_OrderList','listen'),
            ));

            register_rest_route( 'mobilepos/v1/customer/order', 'insert', array(
                'methods' => 'POST',
                'callback' => array('MP_Insert_Order','listen'),
            ));

            register_rest_route( 'mobilepos/v1/customer/order', 'cancel', array(
                'methods' => 'POST',
                'callback' => array('MP_Cancel_Order','listen'),
            ));

            register_rest_route( 'mobilepos/v1/customer/order', 'update', array(
                'methods' => 'POST',
                'callback' => array('MP_Update_Order','listen'),
            ));

            register_rest_route( 'mobilepos/v1/customer/order', 'delete', array(
                'methods' => 'POST',
                'callback' => array('MP_Delete_Order','listen'),
            ));

        /*
         * ORDER RESTAPI
        */
            register_rest_route( 'tindapress/v1/order/total', 'sales', array(
                'methods' => 'POST',
                'callback' => array('TP_Total_sales','listen'),
            ));

            register_rest_route( 'tindapress/v1/order/total', 'monthly', array(
                'methods' => 'POST',
                'callback' => array('TP_Total_sales_date','listen'),
            ));

            register_rest_route( 'tindapress/v1/order', 'date', array(
                'methods' => 'POST',
                'callback' => array('TP_OrdersByDate','listen'),
            ));

        /*
        * OPERATIONS RESTAPI
        */

            register_rest_route( 'tindapress/v1/operations', 'update', array(
                'methods' => 'POST',
                'callback' => array('MP_Update_Operations','listen'),
            ));

            register_rest_route( 'tindapress/v1/operations', 'insert', array(
                'methods' => 'POST',
                'callback' => array('MP_Insert_Operations','listen'),
            ));

            register_rest_route( 'tindapress/v1/operations/list', 'open', array(
                'methods' => 'POST',
                'callback' => array('TP_List_Open','listen'),
            ));

            register_rest_route( 'tindapress/v1/operations/list', 'orders', array(
                'methods' => 'POST',
                'callback' => array('TP_List_Orders','listen'),
            ));

            register_rest_route( 'tindapress/v1/operations/list', 'month', array(
                'methods' => 'POST',
                'callback' => array('TP_List_Month','listen'),
            ));

            register_rest_route( 'tindapress/v1/operations/list', 'date', array(
                'methods' => 'POST',
                'callback' => array('TP_List_Date','listen'),
            ));
    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>