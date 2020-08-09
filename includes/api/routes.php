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
    require plugin_dir_path(__FILE__) . '/v1/users/class-auth.php'; // Example
    
    require plugin_dir_path(__FILE__) . '/v1/orders/class-insert.php';
    
    require plugin_dir_path(__FILE__) . '/v1/store/class-select.php';
    
    require plugin_dir_path(__FILE__) . '/v1/class-globals.php';

    //Order Class
    require plugin_dir_path(__FILE__) . '/v1/orders/class-listing.php';
    
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
            register_rest_route( 'mobilepos/v1/orders', 'insert', array(
                'methods' => 'POST',
                'callback' => array('MP_Insert_Order','listen'),
            ));
            register_rest_route( 'mobilepos/v1/orders', 'listing', array(
                'methods' => 'POST',
                'callback' => array('TP_OrdersList','listen'),
            ));


    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>