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
    // Personnels

        // Role
        require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-insert.php';

        // Orders
        require plugin_dir_path(__FILE__) . '/v2/orders/class-insert.php';

    require plugin_dir_path(__FILE__) . '/v2/class-globals.php';

	// Init check if USocketNet successfully request from wapi.
    function mobilepos_route()
    {
        /**
         * PERSONNEL REST API'S
        */

            // ROLE
                register_rest_route( 'mobilepos/v2/personnels/role', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Role','listen'),
                ));
        /**
         * ORDER REST API'S
         *
        */
            register_rest_route( 'mobilepos/v2/orders', 'insert', array(
                'methods' => 'POST',
                'callback' => array('MP_Insert_Order','listen'),
            ));


    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>