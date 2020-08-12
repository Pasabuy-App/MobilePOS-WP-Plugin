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
            
            // Step1 : check if datavice plugin is activated
            $plugin = TP_Globals::verify_prerequisites();
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

            // variables for query
            $table_store = TP_STORES_TABLE;
            $table_products = TP_PRODUCT_TABLE;
            $table_revs = 'tp_revisions';
            $table_orders = MP_ORDERS_TABLE;
            $table_ordes_items = MP_ORDER_ITEMS_TABLE;
        
            // Step3 : Query
           $result = $wpdb->get_results("SELECT
           mp_ordtem.ID,
           (select child_val from $table_revs where id = (select title from $table_store where id = mp_ord.stid)) AS store,
           (select child_val from $table_revs where id = (select title from $table_products where id = mp_ordtem.pdid)) AS orders,
           mp_ordtem.quantity as qty,
           mp_ord.date_created as date_ordered
           FROM
           $table_ordes_items as mp_ordtem
           INNER JOIN $table_orders as mp_ord ON mp_ord.ID = mp_ordtem.odid
            ");
            
            // Step4 : Check if no result
            if (!$result)
            {
                return array(
                        "status" => "failed",
                        "message" => "No order found by this value.",
                );
            }
            
            // Step5 : Return Result 
            return array(
                    "status" => "success",
                    "data" => array($result,
                )
            );
            
        }

    }