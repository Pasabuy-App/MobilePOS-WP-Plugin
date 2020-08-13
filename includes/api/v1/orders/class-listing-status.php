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
    class MP_OrdersByStatus {

        public static function listen(){
            return rest_ensure_response( 
                MP_OrdersByStatus:: list_open()
            );
        }

        public static function list_open(){
            
            global $wpdb;

            // variables for query
            $table_store = TP_STORES_TABLE;
            $table_prod = TP_PRODUCT_TABLE;
            $table_revs = TP_REVISIONS_TABLE;                               
            $table_ord = MP_ORDERS_TABLE;
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            $status = $_POST['status'];
            
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
                        "message" => "Please contact your administrator. Verification Issues!",
                );
            }
            
            // Step 3: Check if required parameters are passed
            if (!isset($_POST["status"])) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["status"])) {
                return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                );
            }

            // Step 5: Ensures that `stage` is correct
            if ( !($status === 'pending')  
                && !($status === 'received') 
                && !($status === 'delivered') 
                && !($status === 'shipping') 
                && !($status === 'cancelled') ) {
                return array(
                    "status" => "failed",
                    "message" => "Invalid status.",
                );
            }
            
            // Step 6: Select query
            $result = $wpdb->get_results("SELECT
                mp_ordtem.ID,
                mp_ord.`status` AS status,
                (SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT title FROM $table_store  WHERE id = mp_ord.stid )) AS store,
                (SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT title FROM $table_prod  WHERE id = mp_ordtem.pdid )) AS orders,
                mp_ordtem.quantity AS qty,
                mp_ord.date_created AS date_ordered 
            FROM
                $table_ord_it  AS mp_ordtem
            INNER JOIN 
                $table_ord  AS mp_ord ON mp_ord.ID = mp_ordtem.odid 
            WHERE
                mp_ord.`status` = '$status'
            ");
            
            // Step 7: Check if no rows found
            if (!$result)
            {
                return array(
                        "status" => "failed",
                        "message" => "No orders found.",
                );
            }
            
            return array(
                    "status" => "success",
                    "data" => $result
                
            );
            
        }

    }