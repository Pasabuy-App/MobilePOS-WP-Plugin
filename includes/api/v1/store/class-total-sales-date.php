<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) )
	{
		exit;
	}

	/**
        * @package tindapress-wp-plugin
        * @version 0.1.0
	*/

    class MP_Total_sales_date {

        public static function listen(){

			//Initial QA done 2020-08-11 11:32 AM
			global $wpdb;

			//Check if prerequisites plugin are missing
            $plugin = TP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            //  Step2 : Validate if user is exist
			if (DV_Verification::is_verified() == false) {
				return rest_ensure_response(
					array(
						"status" => "unknown",
                        "message" => "Please contact your administrator. Verification issues!",
					)
				);
            }

             // Step3 : Sanitize all Request
			if (!isset($_POST["stid"])  ) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
                );

            }

            // Step6 : Sanitize all Request
			if ( empty($_POST['stid']) ) {
				return array(
					"status" => "unknown",
					"message" => "Required fields cannot be empty.",
                );
			}
			$store_id = $_POST['stid'];
            $get_store = $wpdb->get_row("SELECT ID FROM tp_stores  WHERE ID = $store_id  ");

             if ( !$get_store ) {
                return rest_ensure_response(
                    array(
                        "status" => "failed",
                        "message" => "This store does not exists.",
                    )
                );
			}

			$date = date("Y-m-d h:i:s");
			$expected_date  = date('Y-m-d H:i:s', strtotime($date. ' - 1 month'));

			$order_items_table = MP_ORDER_ITEMS_TABLE;
			$order_items = MP_ORDERS_TABLE;
			$product_table = TP_PRODUCT_TABLE;
			$tp_revs_table = TP_REVISIONS_TABLE;

			$store_id = $_POST["stid"];
			$result = $wpdb->get_row("SELECT
				COALESCE(SUM((SELECT (SELECT child_val FROM tp_revisions WHERE ID = p.price AND revs_type = 'products' AND child_key = 'price') FROM tp_products p WHERE ID = moi.pdid ))) as total_sale	,

				AVG((SELECT (SELECT child_val FROM tp_revisions WHERE ID = p.price AND revs_type = 'products' AND child_key = 'price') FROM tp_products p WHERE ID = moi.pdid )) as average_bill,
				COUNT(mo.ID) as total_order
			FROM
				mp_orders mo
			LEFT JOIN mp_order_items moi on moi.odid = mo.ID
			WHERE  MONTH(mo.date_created)  BETWEEN MONTH('2020-10-04 12:17:22') AND MONTH('$date')
			");

			if (!$result) {
				return array(
					"status" => "failed",
					"message" => "No results found.",
				);

			}else {
				return array(
					"status" => "success",
					"data" => $result
				);
			}
        }
    }