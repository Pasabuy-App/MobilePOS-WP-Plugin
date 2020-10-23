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
    // Schedule
        require plugin_dir_path(__FILE__) . '/v2/schedule/class-insert.php';
            // operation
            require plugin_dir_path(__FILE__) . '/v2/schedule/operation/class-insert.php';

    // Personnels
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-insert.php';

            // Role
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-insert.php';

                // Access
                    require plugin_dir_path(__FILE__) . '/v2/personnel/role/access/class-listing.php';


            // Orders
            require plugin_dir_path(__FILE__) . '/v2/orders/class-insert.php';

    require plugin_dir_path(__FILE__) . '/v2/class-globals.php';

	// Init check if USocketNet successfully request from wapi.
    function mobilepos_route()
    {
        /**
         * PERSONNEL REST API'S
        */

            register_rest_route( 'mobilepos/v2/personnels', 'insert', array(
                'methods' => 'POST',
                'callback' => array('MP_Insert_Personnel','listen'),
            ));

            // ROLE
                register_rest_route( 'mobilepos/v2/personnels/role', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Role','listen'),
                ));

                //  Access

                    register_rest_route( 'mobilepos/v2/personnels/role/access', 'list', array(
                        'methods' => 'POST',
                        'callback' => array('MP_Listing_Access','listen'),
                    ));
        /**
         * ORDER REST API'S
         *
        */
            register_rest_route( 'mobilepos/v2/orders', 'insert', array(
                'methods' => 'POST',
                'callback' => array('MP_Insert_Order','listen'),
            ));

        /**
         * SCHEDULE REST API'S
         *
        */
            register_rest_route( 'mobilepos/v2/schedule', 'insert', array(
                'methods' => 'POST',
                'callback' => array('MP_Insert_Schedule','listen'),
            ));

                // Operation
                    register_rest_route( 'mobilepos/v2/schedule/operation', 'insert', array(
                        'methods' => 'POST',
                        'callback' => array('HP_Insert_Operation','listen'),
                    ));
    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>