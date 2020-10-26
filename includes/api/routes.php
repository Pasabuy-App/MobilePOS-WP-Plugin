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

    // VERSION ONE
        //Operations Classes
        require plugin_dir_path(__FILE__) . '/v1/operations/class-list-open.php';
        require plugin_dir_path(__FILE__) . '/v1/operations/class-list-month.php';
        require plugin_dir_path(__FILE__) . '/v1/operations/class-list-orders.php';
        require plugin_dir_path(__FILE__) . '/v1/operations/class-list-by-date.php';
        require plugin_dir_path(__FILE__) . '/v1/operations/class-insert.php';
        require plugin_dir_path(__FILE__) . '/v1/operations/class-update.php';
        require plugin_dir_path(__FILE__) . '/v1/operations/class-list-byid.php';

        // order folder
        require plugin_dir_path(__FILE__) . '/v1/orders/class-listing.php';
        require plugin_dir_path(__FILE__) . '/v1/customer/class-orderlist.php';

        // store folder
        require plugin_dir_path(__FILE__) . '/v1/store/class-total-sales-date.php';
        require plugin_dir_path(__FILE__) . '/v1/store/class-total-sales.php';
        require plugin_dir_path(__FILE__) . '/v1/store/class-total-order.php';
        require plugin_dir_path(__FILE__) . '/v1/store/class-process.php';
        require plugin_dir_path(__FILE__) . '/v1/store/class-total-piechart.php';
    // END


    // Coupon
        require plugin_dir_path(__FILE__) . '/v2/coupons/class-insert.php';

    // Wallet
        require plugin_dir_path(__FILE__) . '/v2/wallets/class-insert.php';
        require plugin_dir_path(__FILE__) . '/v2/wallets/class-listing.php';
        require plugin_dir_path(__FILE__) . '/v2/wallets/class-change.php';

    // Schedule
        require plugin_dir_path(__FILE__) . '/v2/schedule/class-insert.php';
            // operation
            require plugin_dir_path(__FILE__) . '/v2/schedule/operation/class-insert.php';

    // Personnels
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-insert.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-listing.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-delete.php';
        require plugin_dir_path(__FILE__) . '/v2/personnel/class-activate.php';

            // Role
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-insert.php';
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-listing.php';
            require plugin_dir_path(__FILE__) . '/v2/personnel/role/class-delete.php';

                // Access
                    require plugin_dir_path(__FILE__) . '/v2/personnel/role/access/class-listing.php';

            // Orders
            require plugin_dir_path(__FILE__) . '/v2/orders/class-insert.php';

    require plugin_dir_path(__FILE__) . '/v2/class-globals.php';
    require plugin_dir_path(__FILE__) . '/v1/class-globals.php';

	// Init check if USocketNet successfully request from wapi.
    function mobilepos_route()
    {
        /*
        *    Version one Routes
        */

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

                register_rest_route( 'mobilepos/v1/store/order', 'total', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Total_Order','listen'),
                ));

                register_rest_route( 'mobilepos/v1/store/total/sales', 'date', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Total_sales_date','listen'),
                ));


                register_rest_route( 'mobilepos/v1/store/total', 'sales', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Total_Sales','listen'),
                ));

                register_rest_route( 'mobilepos/v1/store', 'chart', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Total_Piechart','listen'),
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
                register_rest_route( 'mobilepos/v1/order', 'date', array(
                    'methods' => 'POST',
                    'callback' => array('TP_OrdersByDate','listen'),
                ));

            /*
            * OPERATIONS RESTAPI
            */

                register_rest_route( 'mobilepos/v1/operations/list', 'byid', array(
                    'methods' => 'POST',
                    'callback' => array('MP_List_By_Id_Operations','listen'),
                ));

                register_rest_route( 'mobilepos/v1/operations', 'update', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Update_Operations','listen'),
                ));

                register_rest_route( 'mobilepos/v1/operations', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Operations','listen'),
                ));

                register_rest_route( 'mobilepos/v1/operations/list', 'open', array(
                    'methods' => 'POST',
                    'callback' => array('TP_List_Open','listen'),
                ));

                register_rest_route( 'mobilepos/v1/operations/list', 'orders', array(
                    'methods' => 'POST',
                    'callback' => array('TP_List_Orders','listen'),
                ));

                register_rest_route( 'mobilepos/v1/operations/list', 'month', array(
                    'methods' => 'POST',
                    'callback' => array('TP_List_Month','listen'),
                ));

                register_rest_route( 'mobilepos/v1/operations/list', 'date', array(
                    'methods' => 'POST',
                    'callback' => array('TP_List_Date','listen'),
                ));
        /*
        *    Version two Routes
        */
            /**
             * WALLET REST API'S
             *
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

            /**
             * SCHEDULE REST API'S
             *
            */
                register_rest_route( 'mobilepos/v2/schedule', 'insert', array(
                    'methods' => 'POST',
                    'callback' => array('MP_Insert_Schedule_v2','listen'),
                ));

                    // Operation
                        register_rest_route( 'mobilepos/v2/schedule/operation', 'insert', array(
                            'methods' => 'POST',
                            'callback' => array('HP_Insert_Operation_v2','listen'),
                        ));
    }
    add_action( 'rest_api_init', 'mobilepos_route' );

?>