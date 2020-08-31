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
    class TP_List_Month {
        
        public static function listen(){
            return rest_ensure_response( 
                TP_List_Month:: list_month_orders()
            );
        }
        
        public static function list_month_orders(){
            
            //Initial QA done 2020-08-11 11:20 AM
            // 2nd Initial QA 2020-08-24 5:47 PM - Miguel

            global $wpdb;
            $table_revs = TP_REVISIONS_TABLE;
            $table_categories = TP_CATEGORIES_TABLE;
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

            // Step 3: Convert timezone to user specific timezone
            $date = TP_Globals::get_user_date($_POST['wpid']);

            //Create start date and end date of current month
            $start = date('Y-m-01 H:i:s', strtotime($date));
            $end = date('Y-m-t H:i:s', strtotime($date));

            // Step 4: Start mysql query
            $list_month = $wpdb->get_results("SELECT o.date_created as date, st.ID, ops.id as operation_id, o.id as order_id,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.title ) AS `store_name`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = p.title ) AS `product_name`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = p.price ) AS `product_price`,
                oi.quantity as quantity
                FROM
                    tp_stores st
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
                    o.date_created BETWEEN '$start' AND '$end'");

            // Step 6: Return a success status and message 
            return array(
                "status" => "success",
                "data" => $list_month
            );
        }
    }