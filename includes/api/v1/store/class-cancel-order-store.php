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
	class MP_Cancel_Order_Store {

        public static function listen(){
            return rest_ensure_response( 
                MP_Cancel_Order_Store:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;
            
            $table_store = TP_STORES_TABLE;
            $table_ord = MP_ORDERS_TABLE;                                     
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD; 
            $stid = $_POST['stid'];
            $odid = $_POST['odid'];
            $wpid = $_POST['wpid'];
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
            if (!isset($_POST['odid'])
                || !isset($_POST['stid'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Validate order using order id and user id
            $check_order = $wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = $odid  AND stid = $stid");
            if (!$check_order) {
                return array(
                    "status" => "failed",
                    "message" => "No order found."
                );
            }

            // Step 5: Check if order status is not cancelled, received, delivered, shipping
            $check_status = $wpdb->get_row("SELECT child_val AS status FROM $table_mp_revs WHERE ID = (SELECT `status` FROM $table_ord WHERE ID = $odid  AND stid = $stid)");
            if (!($check_status->status === 'pending')) {
                return array(
                    "status" => "failed",
                    "message" => "This order cannot be $status."
                );
            }
            
            // Step 6: Update order status to cancelled
            $wpdb->query("START TRANSACTION");
            $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'status', '$status', '{$user["uid"]}', '$date') ");
            $order_revs = $wpdb->insert_id;
            $result = $wpdb->query("UPDATE $table_ord SET `status` = $order_revs, created_by = '$wpid' WHERE ID IN ($odid) ");

            if ( $order_revs < 1 || $result < 1 ) {
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