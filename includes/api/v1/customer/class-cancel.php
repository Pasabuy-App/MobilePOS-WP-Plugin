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
	class MP_Cancel_Order {

        public static function listen(){
            return rest_ensure_response( 
                MP_Cancel_Order:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;
                               
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
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }
            
            // Step 3: Check if required parameters are passed
            if (!isset($odid)) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }
             
            $date = MP_Globals:: date_stamp(); 
            $odid = $_POST['odid'];
            $user_id = $_POST['wpid'];
            $status = 'cancelled';

            // Step 4: Validate order using order id and user id
            $check_order = $wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid' AND wpid = '$user_id' ");
            if (!$check_order) {
                return array(
                    "status" => "success",
                    "message" => "No data found."
                );
            }
            
            // Step 5: Check if order status is pending and if the same in cancelled status
            $check_status = $wpdb->get_row("SELECT (Select child_val from $table_mp_revs where id = $table_ord.status) AS status FROM $table_ord where id = '$odid'");
            if ($check_status->status === $status) {
                return array(
                    "status" => "success",
                    "message" => "This order has already been $status."
                );
            }
            if (!($check_status->status === 'pending')) {
                return array(
                    "status" => "success",
                    "message" => "This order cannot be $status."
                );
            }
            
            // Step 6: Start mysql transaction
            $wpdb->query("START TRANSACTION");

                // Insert into table revision (type = orders, order id, key = status, value = status value, customer id and date)
                $insert1 = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'key_type', 'ordering', '$user_id', '$date') ");// Add key_type
                $insert2 = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'cancel_by', 'customer', '$user_id', '$date') ");// Add cancel_by
                $insert3 = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'status', '$status', '$user_id', '$date') "); // Add status
                $order_status = $wpdb->insert_id;
                $result = $wpdb->query("UPDATE $table_ord SET status = '$order_status' WHERE ID IN ($odid) "); // Update order status

            // Step 7: Check if any queries above failed
            if ( $insert1 < 1 ||  $insert2 < 1 ||   $insert3 < 1 || $result < 1 ) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status"  => "failed",
                    "message" => "An error occured while submiting data to server."
                );
            }

            // Step 8: Commit if no errors found
            $wpdb->query("COMMIT");
            return array(
                "status"  => "success",
                "message" => "Order has been $status successfully."
                );
            }
		}
	}