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
            
            $table_order_items = MP_ORDER_ITEMS_TABLE;
            $table_order = MP_ORDERS_TABLE;
            $table_tp_revs = 'tp_revisions';
            $table_store = 'tp_stores';
            $table_products = 'tp_products';
            $odid = $_POST['odid'];

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
            if (!isset($_POST["odid"]))  {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step4 : Sanitize if all variables is empty
            if (empty($_POST["odid"])) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
            }
            
             // Step5 : Check if order id is valid
             $verify_id =$wpdb->get_row("SELECT ID FROM $table_order WHERE ID = '$odid' ");

             if (!$verify_id) {
                 return array(
                     "status" => "failed",
                     "message" => "No order found with this value.",
                 );
             }
            
             // Step3 : Query
            $result = $wpdb->get_results("SELECT
                ord.ID,
                ( SELECT wp_users.display_name FROM wp_users WHERE wp_users.ID = ord.wpid ) AS ordered_by,
                ( SELECT $table_tp_revs.child_val FROM $table_tp_revs WHERE ID = $table_store.title AND $table_tp_revs.parent_id = $table_store.ID ) AS `store_name`,
                ( SELECT
                    $table_tp_revs.child_val 
                FROM
                    $table_tp_revs 
                WHERE
                    $table_tp_revs.ID = ( SELECT $table_products.title FROM $table_products WHERE $table_products.ID = ord_it.pdid )) AS `product_name`,
                    ord_it.quantity AS order_quantity,
                    ord.date_created AS order_created 
            FROM
                $table_order AS ord
            LEFT JOIN 
                $table_order_items AS ord_it ON ord_it.odid = ord.ID
            LEFT JOIN 
                $table_store ON $table_store.ID = ord.stid 
            WHERE 
                ord.ID = '$odid' 
            AND 
                ord.`status` = 'pending'");

             // Step4 : Check if no result
             if (!$result)
             {
                 return array(
                         "status" => "failed",
                         "message" => "No results found.",
                 );
             }
             
             // Step5 : Return Result 
             return array(
                     "status" => "success",
                     "data" => $result
             );
        }
    }           
