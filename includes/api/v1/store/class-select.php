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
            $odid = $_POST['odid'];

            //Step 1: Check if prerequisites plugin are missing
            $plugin = MP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Check if wpid and snky is valid
            if (DV_Verification::is_verified() == false) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Verification Issues!",
                );
            }
            
            // Step 3: Sanitize all Request
            if (!isset($_POST["odid"]))  {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Sanitize if all variables is empty
            if (empty($_POST["odid"])) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
            }
            
             // Step 5: Check if order id is valid
             $verify_id =$wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid' ");

             if (!$verify_id) {
                 return array(
                     "status" => "failed",
                     "message" => "No order found with this value.",
                 );
             }
            
             // Step 6: Query
            $result = $wpdb->get_results("SELECT
                ord.ID,
                ( SELECT wp_users.display_name FROM wp_users WHERE wp_users.ID = ord.wpid ) AS ordered_by,
                ( SELECT $table_tp_revs.child_val FROM $table_tp_revs WHERE ID = $table_store.title AND $table_tp_revs.parent_id = $table_store.ID ) AS `store_name`,
                ( SELECT
                    $table_tp_revs.child_val 
                FROM
                    $table_tp_revs 
                WHERE
                    $table_tp_revs.ID = ( SELECT $table_prod.title FROM $table_prod WHERE $table_prod.ID = ord_it.pdid )) AS `product_name`,
                    ord_it.quantity AS order_quantity,
                    ord.date_created AS order_created 
            FROM
                $table_ord AS ord
            LEFT JOIN 
                $table_ord_it AS ord_it ON ord_it.odid = ord.ID
            LEFT JOIN 
                $table_store ON $table_store.ID = ord.stid 
            WHERE 
                ord.ID = '$odid' 
            AND 
                ord.`status` = 'pending'");

             // Step 7: Check if no result
             if (!$result)
             {
                 return array(
                         "status" => "failed",
                         "message" => "No results found.",
                 );
             }
             
             // Step 8: Return Result 
             return array(
                     "status" => "success",
                     "data" => $result
             );
        }
    }           
