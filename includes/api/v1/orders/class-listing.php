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
    class TP_OrdersList {

        public static function listen(){
            return rest_ensure_response( 
                TP_OrdersList:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;

            // variables for query
            $table_store = TP_STORES_TABLE;
            $table_prod = TP_PRODUCT_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
            $table_ord = MP_ORDERS_TABLE;
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            
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
        
            // Step 3: Query
            $result = $wpdb->get_results("SELECT
                mp_ordtem.ID,
                (SELECT child_val FROM $table_tp_revs WHERE id = (SELECT title FROM $table_store WHERE id = mp_ord.stid)) AS store,
                (SELECT child_val FROM $table_tp_revs WHERE id = (SELECT title FROM $table_prod WHERE id = mp_ordtem.pdid)) AS orders,
                mp_ordtem.quantity as qty,
                mp_ord.date_created as date_ordered
            FROM
                $table_ord_it as mp_ordtem
            INNER JOIN 
                $table_ord as mp_ord ON mp_ord.ID = mp_ordtem.odid
            "); 
            
            // Step 4: Check if no rows found
            if (!$result)
            {
                return array(
                        "status" => "failed",
                        "message" => "No order found by this value.",
                );
            }
            
            return array(
                    "status" => "success",
                    "data" => array($result,
                )
            );
            
        }

    }