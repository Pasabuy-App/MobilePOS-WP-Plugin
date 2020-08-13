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
                    "message" => "Please contact your administrator. Request Unknown!",
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
            $check_status = $wpdb->get_row("SELECT `status` FROM $table_ord WHERE ID = $odid  AND wpid = $user_id");
            if (!($check_status->status === 'pending')) {
                return array(
                    "status" => "failed",
                    "message" => "This order cannot be $status."
                );
            }
            
            // Step 6: Update order status to cancelled
			$result = $wpdb->query("UPDATE $table_ord SET `status` = '$status' WHERE ID = $odid AND wpid = $user_id ");
            if ( $result < 1 ) {
                return array(
                    "status"  => "failed",
                    "message" => "An error occured while submiting data to server."
                );
            }

            return array(
                "status"  => "success",
                "message" => "Order has been $status successfully."
            );
		}
	}