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
    class TP_List_Orders {
        
        public static function listen(){
            return rest_ensure_response( 
                TP_List_Orders:: list_orders()
            );
        }
        
        public static function list_orders(){
            
            //Initial QA done 2020-08-11 11:03 AM
            // 2nd Initial QA 2020-08-24 5:55 PM - Miguel

            global $wpdb;
            $table_revs = TP_REVISIONS_TABLE;
            $table_categories = TP_CATEGORIES_TABLE;
            $table_store = TP_STORES_TABLE;
            $table_product = TP_PRODUCT_TABLE;

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
                    "message" => "Please contact your administrator. Verification Issues!",
                );
                
            }

            // Step 3: Check if parameters are passed
            if (!isset($_POST["ops_id"]) ) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are not null
            if (empty($_POST["ops_id"]) ) {
				return array(
					"status" => "failed",
					"message" => "Required fields cannot be empty.",
                );
            }

            $operations_id = $_POST["ops_id"];
            
            // Step 5: Check if operation id exists
            $get_operation = $wpdb->get_row("SELECT `ID`
                FROM
                    `mp_operations`
                WHERE
                    `ID` = $operations_id
            ");

            //Return failed status if no rows found
            if (!$get_operation) {
                return array(
					"status" => "failed",
					"message" => "This operation id does not exists.",
                );
            }
             
            // Step 6: Start mysql query
            $orders = $wpdb->get_results("SELECT st.ID, ops.id as operation_id, o.id as order_id,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.title ) AS `store_name`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = p.title ) AS `product_name`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = p.price ) AS `product_price`,
                oi.quantity as quantity
                FROM
                    $table_store st
                INNER JOIN 
                    $table_revs rev ON rev.ID = st.`status` 
                INNER JOIN
                    mp_operations ops ON ops.stid = st.ID
                INNER JOIN
                    mp_orders o	ON o.opid = ops.id
                INNER JOIN
                    mp_order_items oi ON oi.odid = o.id
                INNER JOIN
                    $table_product p ON p.id = oi.pdid
                WHERE 
                    rev.child_val = 1
                AND
                    ops.id = $operations_id");

            // Step 8: Return a success status and message 
            return array(
                "status" => "success",
                "data" => $orders
            );
        }
    }