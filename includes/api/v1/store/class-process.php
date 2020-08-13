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
	class MP_Process {

        public static function listen(){
            return rest_ensure_response( 
                MP_Process:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;

            // Get Order ID and status order (shipping/delivered/cancelled)
            $table_ord = MP_ORDERS_TABLE;
            $odid = $_POST['odid'];
            $stage = $_POST['stage'];

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
                || !isset($_POST["stage"])) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["odid"]) 
                || empty($_POST["stage"])) {
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

            // Step 6: Check if stage input is received or pending
            if ($stage === 'received' 
                || $stage === 'pending') {
                return array(
                    "status" => "failed",
                    "message" => "This process is for mover only.",
                );
            }

            // Step 7: Check order status if same in stage input
            $verify_stage = $wpdb->get_row("SELECT `status` FROM $table_ord WHERE  ID = '$odid' and `status` = '$stage'");
            if ($verify_stage) {
                return array(
                    "status" => "failed",
                    "message" => "This order has already been $stage.",
                );
            }

            // Step 8: Check order status if received for shipping
            if ($stage === 'shipping') {
                $verify_shipping = $wpdb->get_row("SELECT `status` FROM $table_ord WHERE  ID = '$odid' and `status` = 'received'");
                if (!$verify_shipping) {
                    return array(
                        "status" => "failed",
                        "message" => "This order is not for shipping.",
                    );
                }
            }

            // Step 9: Check order status if shipping for delivered or cancelled
            if ($stage === 'delivered' 
                || $stage === 'cancelled') {
                $verify_shipping = $wpdb->get_row("SELECT `status` FROM $table_ord WHERE  ID = '$odid' and `status` = 'shipping'");
                if (!$verify_shipping) {
                    return array(
                        "status" => "failed",
                        "message" => "This order is not for complete.",
                    );
                }
            }
            
            // Step 10: Update query
            $result = $wpdb->query("UPDATE $table_ord SET  `status` = '$stage' WHERE ID = '$odid'");
            if ( $result < 1 ) {
                return array(
                    "status"  => "failed",
                    "message" => "An error occured while submiting data to server.",
                );

            }else{
                return array(
                    "status"  => "success",
                    "message" => "Order status change to $stage.",
                );
            }

        }
    }