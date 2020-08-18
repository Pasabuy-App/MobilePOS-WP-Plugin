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
                           
            $date = MP_Globals:: date_stamp();     
            $table_ord = MP_ORDERS_TABLE;                                     
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD; 
            $odid = $_POST['odid'];
            $user_id = $_POST['wpid'];
            $status = 'cancelled';
            
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
            if (!isset($_POST['odid'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Validate order using order id and user id
            $check_order = $wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid' AND wpid = '$user_id' ");
            if (!$check_order) {
                return array(
                    "status" => "failed",
                    "message" => "No order found."
                );
            }
            
            // Step 5: Check if order status is pending
            $check_status = $wpdb->get_row("SELECT (Select child_val from $table_mp_revs where id = $table_ord.status) AS status FROM $table_ord where id = '$odid'");
            if ($check_status->status === $status) {
                return array(
                    "status" => "failed",
                    "message" => "This order has already been $status."
                );
            }
            if (!($check_status->status === 'pending')) {
                return array(
                    "status" => "failed",
                    "message" => "This order cannot be $status."
                );
            }
            
            // Step 6: Update order status to cancelled
            $wpdb->query("START TRANSACTION");
            // Insert into table revision (type = orders, order id, key = status, value = status value, customer id and date)
            $insert1 = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'key_type', 'ordering', '$user_id', '$date') ");
            $insert2 = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'cancel_by', 'customer', '$user_id', '$date') ");
            $insert3 = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'status', '$status', '$user_id', '$date') ");
            $order_status = $wpdb->insert_id;
            $result = $wpdb->query("UPDATE $table_ord SET status = '$order_status' WHERE ID IN ($odid) ");

			//$result = $wpdb->query("UPDATE $table_ord SET `status` = '$status' WHERE ID = $odid AND wpid = $user_id "); -> old query
            if ( $insert1 < 1 ||  $insert2 < 1 ||   $insert3 < 1 || $result < 1 ) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status"  => "failed",
                    "message" => "An error occured while submiting data to server."
                );
            } else {
                $wpdb->query("COMMIT");
                return array(
                    "status"  => "success",
                    "message" => "Order has been $status successfully."
                );
            }
		}
	}