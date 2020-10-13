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

    class MP_Total_Piechart {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;

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

            $store_id = $_POST['stid'];

            $data = $wpdb->get_row("SELECT (SELECT
                    COUNT(mo.ID) as total_order
                FROM
                    mp_orders mo
                LEFT JOIN mp_order_items moi on moi.odid = mo.ID
                WHERE (SELECT child_val FROM mp_revisions WHERE ID = mo.`status` ) = 'completed' AND  mo.stid  = '$store_id' ) as `total_completed`,

                            (SELECT
                    COUNT(mo.ID) as total_order
                FROM
                    mp_orders mo
                LEFT JOIN mp_order_items moi on moi.odid = mo.ID
                WHERE (SELECT child_val FROM mp_revisions WHERE ID = mo.`status` ) = 'pending' AND  mo.stid  = '$store_id' ) as `total_pending`,

                                (SELECT
                    COUNT(mo.ID) as total_order
                FROM
                    mp_orders mo
                LEFT JOIN mp_order_items moi on moi.odid = mo.ID
                WHERE (SELECT child_val FROM mp_revisions WHERE ID = mo.`status` ) = 'cancelled' AND  mo.stid  = '$store_id' ) as `total_cancelled`");


            return array(
                "status" => "success",
                "data" => $data
            );
        }

    }