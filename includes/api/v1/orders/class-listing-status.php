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
            $table_products = TP_PRODUCT_TABLE;
            $table_revs = TP_REVISIONS_TABLE;
            $table_orders = 'mp_orders';
            $table_order_items = 'mp_order_items';
            $status = $_POST['status'];
            
            //Step1 : Check if prerequisites plugin are missing
            $plugin = MP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step2 : Check if wpid and snky is valid
            if (DV_Verification::is_verified() == false) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Verification Issues!",
                );
            }
            
            // Step3 : Sanitize all Request
            if (!isset($_POST["status"])) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step4 : Sanitize all Request
            if (empty($_POST["status"])) {
                return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                );
            }

            //Ensures that `stage` is correct
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
            
            // Step5 : Query
            $result = $wpdb->get_results("SELECT
                mp_ordtem.ID,
                mp_ord.`status` AS STATUS,
                (SELECT child_val FROM tp_revisions WHERE id = ( SELECT title FROM tp_stores WHERE id = mp_ord.stid )) AS store,
                (SELECT child_val FROM tp_revisions WHERE id = ( SELECT title FROM tp_products WHERE id = mp_ordtem.pdid )) AS orders,
                mp_ordtem.quantity AS qty,
                mp_ord.date_created AS date_ordered 
            FROM
                mp_order_items AS mp_ordtem
            INNER JOIN 
                mp_orders AS mp_ord ON mp_ord.ID = mp_ordtem.odid 
            WHERE
                mp_ord.`status` = '$status'
            ");
            
            // Step6 : Check if no result
            if (!$result)
            {
                return array(
                        "status" => "failed",
                        "message" => "No orders found.",
                );
            }
            
            // Step7 : Return Result 
            return array(
                    "status" => "success",
                    "data" => $result
                
            );
            
        }

    }