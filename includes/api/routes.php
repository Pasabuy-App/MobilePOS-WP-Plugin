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
    require plugin_dir_path(__FILE__) . '/v1/customer/class-cancel-order.php';
    require plugin_dir_path(__FILE__) . '/v1/customer/class-update.php';
    
    // order folder
    require plugin_dir_path(__FILE__) . '/v1/orders/class-cancel-order-store.php';
    require plugin_dir_path(__FILE__) . '/v1/orders/class-listing.php';
    require plugin_dir_path(__FILE__) . '/v1/orders/class-process.php';
    require plugin_dir_path(__FILE__) . '/v1/orders/class-listing-status.php';
    
    // store folder
    require plugin_dir_path(__FILE__) . '/v1/store/class-select.php';
    
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

        /*
         * ORDER RESTAPI
        */
           
            register_rest_route( 'mobilepos/v1/orders', 'listing', array(
                'methods' => 'POST',
                'callback' => array('TP_OrdersList','listen'),
            ));

            register_rest_route( 'mobilepos/v1/orders', 'cancel/store', array(
                'methods' => 'POST',
                'callback' => array('MP_Cancel_Order_Store','listen'),
            ));
            
            register_rest_route( 'mobilepos/v1/order', 'process', array(
                'methods' => 'POST',
                'callback' => array('MP_Process','listen'),
            ));

            register_rest_route( 'mobilepos/v1/order', 'bystatus', array(
                'methods' => 'POST',
                'callback' => array('MP_OrdersByStatus','listen'),
            ));
        
        /*
         * CUSTOMER ORDER RESTAPI
        */
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


    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>