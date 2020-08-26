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
	class MP_Select_Order {

        public static function listen(){
            return rest_ensure_response( 
                MP_Select_Order:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;
            
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            $table_ord = MP_ORDERS_TABLE;
            $table_prod = TP_PRODUCT_TABLE;
            $table_store = TP_STORES_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
            $table_revs = MP_REVISIONS_TABLE;

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
            if (!isset($_POST["odid"]))  {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["odid"])) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }
            
            $odid = $_POST['odid'];
            
            // Step 5: Check if order id is valid
            $verify_id =$wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid' ");
            if (!$verify_id) {
                return array(
                    "status" => "success",
                    "message" => "No data found with this value.",
                );
            }
            
            // Step 6: Start mysql transaction
            $result = $wpdb->get_results("SELECT
                od_it.odid AS order_id, 
                (SELECT wp_users.display_name FROM wp_users WHERE wp_users.ID = ord.wpid ) AS ordered_by,
                (SELECT child_val FROM $table_tp_revs WHERE ID = str.title) AS store_name, 
                (SELECT child_val FROM $table_tp_revs WHERE ID = prd.title) AS product_name,
                od_it.quantity,
                ord.date_created
            FROM
                $table_ord AS ord
            INNER JOIN 
                $table_ord_it AS od_it ON  ord.ID = od_it.odid
            INNER JOIN 
                $table_store AS str ON  ord.stid = str.ID
            INNER JOIN 
                $table_prod AS prd ON od_it.pdid = prd.ID
            INNER JOIN 
                $table_revs AS mprevs ON od_it.quantity = mprevs.ID
            WHERE 
                ord.ID = '$odid' 
            AND 
            (SELECT child_val FROM $table_revs WHERE ID = ord.`status`) = 'pending'");

            // Step 7: Check if no rows found
            if (!$result) {
                return array(
                    "status" => "success",
                    "message" => "No data found.",
                );
            }
             
            // Step 8: Return Result 
            return array(
                "status" => "success",
                "data" => $result
            );
        }
    }           
