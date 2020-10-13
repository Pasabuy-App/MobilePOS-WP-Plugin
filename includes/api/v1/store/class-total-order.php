<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) )
	{
		exit;
	}

	/**
        * @package mobilepos-wp-plugin
        * @version 0.1.0
    */

    class MP_Total_Order {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function list_open(){

			global $wpdb;
            $table_revs = TP_REVISIONS_TABLE;
            $table_product = TP_PRODUCT_TABLE;
            $table_ord = MP_ORDERS_TABLE;
            $table_ord_it = MP_ORDER_ITEMS_TABLE;

            // Step 1: Check if prerequisites plugin are missing
            $plugin = TP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

			// Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

             // Step 3: Check if required parameters are passed
			if (!isset($_POST["stid"])  ) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
                );

            }

            // Step 4: Check if parameters passed are empty
			if ( empty($_POST['stid']) ) {
				return array(
					"status" => "unknown",
                    "message" => "Required fields cannot be empty.",
                );
			}

			$store_id = $_POST['stid'];

            // Step 5: Check if this store id exists
            $get_store = $wpdb->get_row("SELECT ID FROM tp_stores  WHERE ID = $store_id  ");
             if ( !$get_store ) {
                return rest_ensure_response(
                    array(
                        "status" => "failed",
                        "message" => "This store does not exists.",
                    )
                );
            }

            // Step 6: Start mysql transaction
			$result = $wpdb->get_row("SELECT
                COUNT(mo.ID) as total_order
            FROM
                mp_orders mo
            LEFT JOIN mp_order_items moi on moi.odid = mo.ID
            WHERE (SELECT child_val FROM mp_revisions WHERE ID = mo.`status` ) = 'completed' AND  mo.stid  = '$store_id'");

            // Step 7: Return result
            return array(
				"status" => "success",
				"data" => $result
			);
        }
    }