
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
	class MP_Delete_Order {

        public static function listen(){
            return rest_ensure_response( 
                MP_Delete_Order:: list_open()
            );
        }
        
        public static function list_open(){
            
            global $wpdb;

            $date = MP_Globals:: date_stamp();                                
            $table_ord_it = MP_ORDER_ITEMS_TABLE;                             
            $table_ord = MP_ORDERS_TABLE;                         
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD; 
            $wpid =$_POST["wpid"];
            $odid =$_POST["odid"];
            $pid =$_POST["pid"];
           
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
            if (!isset($_POST["odid"])
                || !isset($_POST["pid"])  ) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["odid"]) 
                || empty($_POST["pid"])  ) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );  
            }

            // Step 5: Validate order using order id and user id
            $check_order = $wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid' AND wpid = '$wpid' ");
            $check_prod = $wpdb->get_row("SELECT ID FROM $table_ord_it WHERE odid = '$odid' AND pdid = '$pid' ");
            if (!$check_order || !$check_prod) {
                return array(
                    "status" => "failed",
                    "message" => "No order found."
                );
            }
            
            // Step 6: Check if order status is pending using order id
            $check_status = $wpdb->get_row("SELECT (Select child_val from $table_mp_revs where id = $table_ord.status) AS status FROM $table_ord where id = '$odid'");
            if (!($check_status->status === 'pending')) {
                return array(
                    "status" => "failed",
                    "message" => "This order has already been $check_status->status."
                );
            }

            // Step 7: Check if product status inside order using order id and product id
            $check_status = $wpdb->get_row("SELECT ID,(SELECT child_val AS status FROM $table_mp_revs WHERE ID = $table_ord_it.status) AS status FROM $table_ord_it WHERE odid = '$odid' AND pdid = '$pid'");
            if (!($check_status->status === '1')) {
                return array(
                    "status" => "failed",
                    "message" => "No order found.",
                );
            }
            
            // Step 8: Insert Query and Update
            // Insert into table revisions (revision type = order_items, order id, key = status, value = 0, customer id and date )
            $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('order_items', '$odid', 'status', '0', '$wpid', '$date') ");
            $ordid_stat = $wpdb->insert_id;
            $result = $wpdb->query("UPDATE $table_ord_it SET status = '$ordid_stat' WHERE ID IN ($check_status->ID) "); // Update the status of order items table
        
            if ( $ordid_stat < 1  || $result < 1 ) {
                return array(
                   "status" => "failed",
                   "message" => "An error occured while submitting data to the server."
                );
            }else{
                return array(
                        "status" => "success",
                        "message" => "Order deleted successfully."
                );
            }
        }
    }