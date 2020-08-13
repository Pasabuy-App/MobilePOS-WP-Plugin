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
            $check_order = $wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = $odid  AND wpid = $user_id");
            if (!$check_order) {
                return array(
                    "status" => "failed",
                    "message" => "No order found."
                );
            }
            
            // Step 5: Check if order status is not cancelled, received, delivered, shipping
            $check_status = $wpdb->get_row("SELECT child_val AS status FROM $table_mp_revs INNER JOIN mp_orders ON mp_revisions.parent_id = mp_orders.ID 
            where mp_revisions.parent_id = '$odid' order by mp_revisions.id desc limit 1");
            if (!($check_status->status === 'pending')) {
                return array(
                    "status" => "failed",
                    "message" => "This order cannot be $status."
                );
            }
            
            // Step 6: Update order status to cancelled
            $wpdb->query("START TRANSACTION");
            $insert = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'status', '$status', '$user_id', '$date') ");
            $result = $wpdb->query("UPDATE $table_ord SET created_by = '$user_id' WHERE ID IN ($odid) ");

			//$result = $wpdb->query("UPDATE $table_ord SET `status` = '$status' WHERE ID = $odid AND wpid = $user_id "); -> old query
            if ( $insert < 1 || $result < 1 ) {
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