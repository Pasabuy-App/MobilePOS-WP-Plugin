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
    
    class TP_OrdersByDate {

        public static function listen(){
            return rest_ensure_response( 
                TP_OrdersByDate:: list_open()
            );
        }

        public static function list_open(){

            //Initial QA done 2020-08-10 10:54 am
            // 2nd Initial QA 2020-08-24 5:55 PM - Miguel
            
            global $wpdb;
            // variables for query
            $table_store = TP_STORES_TABLE;
            $table_products = TP_PRODUCT_TABLE;
            $table_revs = TP_REVISIONS_TABLE;
            $table_orders = 'mp_orders';
            $table_order_items = 'mp_order_items';

            return TP_Globals::get_timezone(1);
            return $dt = TP_Globals::convert_date($_POST["wpid"],$_POST["date"]);

            //Check if prerequisites plugin are missing
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
            
            // Step3 : Sanitize all Request
            if (!isset($_POST["stid"]) || !isset($_POST["date"])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step4 : sanitize if all variables is empty
            if (empty($_POST["stid"]) || empty($_POST["date"])) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step5 : Validation of store id
            $stid = $_POST["stid"];
            $get_store = $wpdb->get_row("SELECT ID FROM $table_store  WHERE ID = $stid  ");
                
             if ( !$get_store ) {
                return array(
                    "status" => "failed",
                    "message" => "This store does not exists.",
                );
            }

            // Step6 : Validate date with format
            $valdt= TP_OrdersByDate::validateDate($dt);   
            if ( !$valdt ) {
               return array(
                    "status" => "failed",
                    "message" => "Date is not in valid format!",
               );
           }
        
            // Step7 : Query
           $result = $wpdb->get_results("SELECT
                    mp_ordtem.ID,
                    (select child_val from $table_revs where id = (select title from $table_store where id = mp_ord.stid)) AS store,
                    (select child_val from $table_revs where id = (select title from $table_products where id = mp_ordtem.pdid)) AS product,
                    mp_ordtem.quantity,
                    mp_ord.date_created
                FROM
                    $table_order_items as mp_ordtem
                INNER JOIN 
                    $table_orders as mp_ord ON mp_ord.ID = mp_ordtem.odid
                WHERE
                    mp_ord.stid = '$stid' 
                AND
                    DATE(mp_ord.date_created) = '$dt'
            ");
            
            // Step8 : Check if no result
            if (!$result)
            {
                return array(
                    "status" => "failed",
                    "message" => "No results found!",
                );
            }
            
            // Step9 : Return Result 
            return array(
                "status" => "success",
                "data" => $result
            );
        }
        
        //Function for checking if date is valid and invalid format(2020-01-01).
        //Invalid dates such us 2020-02-31 will return false
        public static function validateDate($date, $format = 'Y-m-d H:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }