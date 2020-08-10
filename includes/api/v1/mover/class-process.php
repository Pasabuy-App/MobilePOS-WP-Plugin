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
            $table_order = MP_ORDERS_TABLE;
            $odid = $_POST['odid'];
            $stage = $_POST['stage'];

            // Step1 : check if datavice plugin is activated
            if (MP_Globals::verify_plugins() == false) {
                return rest_ensure_response( 
                    array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Plugin Missing!",
                    )
                );
            }

            // Step2: Validate user
            if ( DV_Verification::is_verified() == false ) {
                return rest_ensure_response( 
                    array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request Unknown!",
                    )
                );
            }
            
            // Step3 : Sanitize all Request
            if (!isset($_POST["odid"]) || !isset($_POST["stage"])) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step4 : Sanitize if all variables is empty
            if (empty($_POST["odid"]) || empty($_POST["stage"])) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
            }

            $verify_store =$wpdb->get_row("SELECT ID FROM $table_order WHERE ID = '$odid' ");

            if (!$verify_store) {
                return array(
                    "status" => "failed",
                    "message" => "No order found with this value!",
                );
            }

            // Check if stage input is for received or pending
            if ($stage == 'received' || $stage == 'pending') {
                return array(
                    "status" => "failed",
                    "message" => "This process is for mover only!",
                );
            }

            // Check the order status if the same in the stage input
            $verify_stage =$wpdb->get_row("SELECT `status` FROM $table_order WHERE  ID = '$odid' and `status` = '$stage'");
            if ($verify_stage) {
                return array(
                    "status" => "failed",
                    "message" => "This order has already been $stage!",
                );
            }

            // Check the order status if received for shipping
            if ($stage == 'shipping') {
                $verify_shipping =$wpdb->get_row("SELECT `status` FROM $table_order WHERE  ID = '$odid' and `status` = 'received'");
                if (!$verify_shipping) {
                    return array(
                        "status" => "failed",
                        "message" => "This order is not for shipping!",
                    );
                }
            }

            // Check the order status if shipping for delivered or cancelled
            if ($stage == 'delivered' || $stage == 'cancelled') {
                $verify_shipping =$wpdb->get_row("SELECT `status` FROM $table_order WHERE  ID = '$odid' and `status` = 'shipping'");
                if (!$verify_shipping) {
                    return array(
                        "status" => "failed",
                        "message" => "This order is not for complete!",
                    );
                }
            }
            
            $result = $wpdb->query("UPDATE mp_orders SET  `status` = '$stage' WHERE ID = '$odid'");
            
            if ( $result < 1 ) {
                return array(
                    "status"  => "failed",
                    "message" => "An error occured while submiting data to server!",
                );

            }else{
                return array(
                    "status"  => "success",
                    "message" => "Order has been $stage successfully!",
                );
            }

        }
    }