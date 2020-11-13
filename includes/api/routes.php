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

    // Coupon
        require plugin_dir_path(__FILE__) . '/v2/coupons/class-insert.php';

    // Wallet
        require plugin_dir_path(__FILE__) . '/v2/wallets/class-insert.php';
        require plugin_dir_path(__FILE__) . '/v2/wallets/class-listing.php';
        require plugin_dir_path(__FILE__) . '/v2/wallets/class-change.php';
        require plugin_dir_path(__FILE__) . '/v2/wallets/class-transaction.php';

    // Schedule
        require plugin_dir_path(__FILE__) . '/v2/schedule/class-insert.php';
        require plugin_dir_path(__FILE__) . '/v2/schedule/class-listing.php';
            // operation
            require plugin_dir_path(__FILE__) . '/v2/schedule/operation/class-insert.php';
            require plugin_dir_path(__FILE__) . '/v2/schedule/operation/class-listing.php';
            require plugin_dir_path(__FILE__) . '/v2/schedule/operation/class-verify.php';

    // Personnels
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-insert.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-listing.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-delete.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-approve.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-assign-store.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-update.php';

            // Role
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-insert.php';
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-listing.php';
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-delete.php';
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-update.php';

                // Access
                    require plugin_dir_path(__FILE__) . '/v2/personnel/role/access/class-listing.php';

    // Orders
        require plugin_dir_path(__FILE__) . '/v2/orders/class-insert.php';
        require plugin_dir_path(__FILE__) . '/v2/orders/class-listing.php';
        require plugin_dir_path(__FILE__) . '/v2/orders/class-update.php';

    require plugin_dir_path(__FILE__) . '/v2/class-globals.php';

	// Init check if USocketNet successfully request from wapi.
    function mobilepos_route()
    {

        /*
        *    Version two Routes
        */
            /**
             * WALLET REST API'S
            */
                register_rest_route( 'mobilepos/v2/wallets', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Wallet_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/wallets', 'list', array(
                    'methods' => 'POST',
                    'callback' => array('HP_Listing_Wallet_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/wallets', 'change', array(
                    'methods' => 'POST',
                    'callback' => array('HP_Wallet_Change_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/wallets', 'info', array(
                    'methods' => 'POST',
                    'callback' => array('HP_Wallet_Info_v2','listen'),
                ));

            /**
             * COUPON REST API'S
             *
            */
                register_rest_route( 'mobilepos/v2/coupon', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Coupons_v2','listen'),
                ));

            /**
             * PERSONNEL REST API'S
            */

                register_rest_route( 'mobilepos/v2/personnels', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Personnel_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/personnels', 'list', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Listing_Personnel_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/personnels', 'delete', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Delete_Personnel_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/personnels', 'activate', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Activate_Personnel_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/personnels/store', 'assigned', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Listing_Personnels_Store_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/personnels', 'update', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Update_Personnel_v2','listen'),
                ));


                // ROLE
                    register_rest_route( 'mobilepos/v2/personnels/role', 'insert', array(
                        'methods' => 'POST',
                        'callback' => array('MP_Insert_Role_v2','listen'),
                    ));

                    register_rest_route( 'mobilepos/v2/personnels/role', 'list', array(
                        'methods' => 'POST',
                        'callback' => array('MP_Listing_Role_v2','listen'),
                    ));

                    register_rest_route( 'mobilepos/v2/personnels/role', 'delete', array(
                        'methods' => 'POST',
                        'callback' => array('MP_Delete_Role_v2','listen'),
                    ));

                    register_rest_route( 'mobilepos/v2/personnels/role', 'update', array(
                        'methods' => 'POST',
                        'callback' => array('MP_Update_Role_v2','listen'),
                    ));

                    //  Access

                        register_rest_route( 'mobilepos/v2/personnels/role/access', 'list', array(
                            'methods' => 'POST',
                            'callback' => array('MP_Listing_Access_v2','listen'),
                        ));
            /**
             * ORDER REST API'S
             *
            */
                register_rest_route( 'mobilepos/v2/orders', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Order_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/orders', 'list', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Listing_Order_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/orders', 'update', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Update_Order_v2','listen'),
                ));

            /**
             * SCHEDULE REST API'S
            */
                register_rest_route( 'mobilepos/v2/schedule', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Schedule_v2','listen'),
                ));

                register_rest_route( 'mobilepos/v2/schedule', 'list', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Listing_Schedule_v2','listen'),
                ));

                    // Operation
                        register_rest_route( 'mobilepos/v2/schedule/operation', 'insert', array(
                            'methods' => 'POST',
                            'callback' => array('MP_Insert_Operation_v2','listen'),
                        ));

                        register_rest_route( 'mobilepos/v2/schedule/operation', 'list', array(
                            'methods' => 'POST',
                            'callback' => array('MP_Operation_Listing_v2','listen'),
                        ));

                        register_rest_route( 'mobilepos/v2/schedule/operation', 'Verify', array(
                            'methods' => 'POST',
                            'callback' => array('MP_Verify_Operation_v2','listen'),
                        ));
    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>