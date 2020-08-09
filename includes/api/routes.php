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
    
    require plugin_dir_path(__FILE__) . '/v1/orders/class-order.php';
    
    require plugin_dir_path(__FILE__) . '/v1/store/class-select.php';
    
    require plugin_dir_path(__FILE__) . '/v1/class-globals.php';
    
	// Init check if USocketNet successfully request from wapi.
    function mobilepos_route()
    {
        // Example
        register_rest_route( 'mobilepos/v1/user', 'auth', array(
            'methods' => 'POST',
<<<<<<< HEAD
            'callback' => array('MP_Authenticate','listen'),
        ));       
=======
            'callback' => array('DV_Authenticate','initialize'),
        ));   
        
        /*
         * PRODUCT RESTAPI
        */
            register_rest_route( 'mobilepos/v1/orders', 'order', array(
                'methods' => 'POST',
                'callback' => array('MP_Orders','listen'),
            ));

        /*
         * STORE RESTAPI
        */
            register_rest_route( 'mobilepos/v1/store', 'select', array(
                'methods' => 'POST',
                'callback' => array('MP_Select_Order','listen'),
            ));
>>>>>>> eaa98e7e4809fb6d8fb5b1407abc3ebed8103169
    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>