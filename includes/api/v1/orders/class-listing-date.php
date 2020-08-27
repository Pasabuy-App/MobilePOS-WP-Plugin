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
    class MP_OrdersByDate {

        public static function listen(){
            return rest_ensure_response( 
                MP_OrdersByDate:: list_open()
            );
		}

		public static function list_open(){
            
            global $wpdb;

            // variables for query
            $table_store = TP_STORES_TABLE;
            $table_products = TP_PRODUCT_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;                               
            $table_orders = MP_ORDERS_TABLE;
			$table_order_items = MP_ORDER_ITEMS_TABLE;
			
            //Step 1: Check if prerequisites plugin are missing
            $plugin = MP_Globals::verify_prerequisites();
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
            if (!isset($_POST["stid"]) || !isset($_POST["date"])) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if required parameters are empty
            if (empty($_POST["stid"]) || empty($_POST["date"])) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
			}
			
            // Step 5: Validate date with format
            $dt = TP_Globals::convert_date($_POST["wpid"],$_POST["date"]);
            $valdt= MP_OrdersByDate::validateDate($dt);   
            if ( !$valdt) {
               return array(
                       "status" => "failed",
                       "message" => "Date is not in valid format!",
               );
            }

            // Step 6: Validation store using store id
            $stid = $_POST["stid"];
            $get_store = $wpdb->get_row("SELECT ID FROM $table_store  WHERE ID = $stid  ");
             if ( !$get_store ) {
                return array(
                        "status" => "success",
                        "message" => "This store does not exists.",
                );
			} 

			// Step 7: Start mysql transaction
			$sql = "SELECT
				mp_ordtem.ID,
				(select child_val from $table_tp_revs where id = (select title from $table_store where id = mp_ord.stid)) AS store,
				(select child_val from $table_tp_revs where id = (select title from $table_products where id = mp_ordtem.pdid)) AS product,
				mp_ordtem.quantity,
				mp_ord.date_created
			FROM
				$table_order_items as mp_ordtem
			INNER JOIN 
				$table_orders as mp_ord ON mp_ord.ID = mp_ordtem.odid
			WHERE
				mp_ord.stid = '$stid' 
			AND
				DATE(mp_ord.date_created) = '$dt' ";

			$result = $wpdb->get_results($sql);
            
            // Step 8: Check if no rows found
            if (!$result) {
                return array(
                    "status" => "success",
                    "message" => "No results found!",
                );
            }
            
            // Step 9: Return result 
            return array(
                "status" => "success",
                "data" => $result
            );

		}
        
        //Function for checking if date is valid and invalid format(2020-01-01).
        //Invalid dates such us 2020-02-31 will return false
        public static function validateDate($date, $format = 'Y-m-d H:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }

	}