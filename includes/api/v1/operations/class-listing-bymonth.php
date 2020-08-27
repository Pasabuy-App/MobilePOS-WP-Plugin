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
    class MP_Listing_Month {
        
        public static function listen(){
            return rest_ensure_response( 
                MP_Listing_Month:: list_open()
            );
        }
        
        public static function list_open(){

            global $wpdb;
            $table_revs = TP_REVISIONS_TABLE;
            $table_product = TP_PRODUCT_TABLE;
            $table_store = TP_STORES_TABLE; 
            $table_ope = MP_OPERATIONS_TABLE;                             
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

            // Step 3: Convert timezone to user specific timezone
            $date = TP_Globals::get_user_date($_POST['wpid']);

            //Create start date and end date of current month
            $start = date('Y-m-01 H:i:s', strtotime($date));
            $end = date('Y-m-t H:i:s', strtotime($date));

            // Step 4: Start mysql transaction
            $result = $wpdb->get_results("SELECT st.ID, ops.id as operation_id, o.id as order_id,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.title ) AS `store_name`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = p.title ) AS `product_name`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = p.price ) AS `product_price`,
                oi.quantity as quantity,
                o.date_created as date
            FROM
                $table_store st
            INNER JOIN 
                $table_revs rev ON rev.ID = st.`status` 
            INNER JOIN
                $table_ope ops ON ops.stid = st.ID
            INNER JOIN
                $table_ord o	ON o.opid = ops.id
            INNER JOIN
                $table_ord_it oi ON oi.odid = o.id
            INNER JOIN
                $table_product p ON p.id = oi.pdid
            WHERE 
                   rev.child_val = 1
            AND
                o.date_created BETWEEN '$start' AND '$end'");

            // Step 6: Return a success status and message 
            return array(
                "status" => "success",
                "data" => $result
            );
        }
    }