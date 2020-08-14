
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
	class MP_Update_Order {

        public static function listen(){
            return rest_ensure_response( 
                MP_Update_Order:: list_open()
            );
        }
        
        public static function list_open(){
            
            global $wpdb;

            $date = MP_Globals:: date_stamp();
            $wpid =$_POST["wpid"];
            $odid =$_POST["odid"];
            $qty =$_POST["qty"];

            // order items table                                 
            $table_ord_it = MP_ORDER_ITEMS_TABLE;

            // order table                               
            $table_ord = MP_ORDERS_TABLE;
                                           
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD; 
           
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
            if (!isset($_POST["qty"]) 
                || !isset($_POST["odid"])  ) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["qty"]) 
                || empty($_POST["odid"])  ) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );  
            }

            // Step 5: Check if parameters passed is numeric
            if (!is_numeric($_POST["qty"])  ) {
				return array(
						"status" => "failed",
						"message" => "Required ID is not in valid format.",
                );
            }

            // Step 6: Check if order is exist
            $verify_odid =$wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid'");
            if (!$verify_odid) {
                return array(
                    "status" => "failed",
                    "message" => "No order found.",
                );
            }

            // Step 6: Check if order is exist
            $verify_odid =$wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid'");
            if (!$verify_odid) {
                return array(
                    "status" => "failed",
                    "message" => "No order found.",
                );
            }

            // TODO : Check the orders status if pending and order items status if active or not
            $verify_odiditem = $wpdb->get_row("SELECT ID FROM $table_ord_it WHERE odid = '$verify_odid->ID' ");

            // Step 8: Insert Query
            // Insert into table revisions (revision type = orders, last id of insert of order, key = status, value = pending, customer id and date )
            $result = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('order_items', '$verify_odiditem->ID', 'quantity', '$qty', '$wpid', '$date') ");
        
            if ( $result < 1 ) {
                return array(
                   "status" => "failed",
                   "message" => "An error occured while submitting data to the server."
                );
            }else{
                return array(
                        "status" => "success",
                        "message" => "Order updated successfully."
                );
            }
        }
    }