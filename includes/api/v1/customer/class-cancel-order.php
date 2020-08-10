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
            global $wpdb;
            
            // Step1 : check if datavice plugin is activated
            if (MP_Globals::verify_plugins() == false) {
                return rest_ensure_response( 
                    array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Plugin Missing!",
                    )
                );
            }

            // Step1: Validate user
            if ( DV_Verification::is_verified() == false ) {
                return rest_ensure_response( 
                    array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request Unknown!",
                    )
                );
            }

            if (!isset($_POST['odid'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Missing parammiters"
                );
            }

            $order_id = $_POST['odid'];
            $user_id = $_POST['wpid'];

            // validate if order is not cancelled, received, delivered, shipping
			$check_order = $wpdb->get_row("SELECT `status` FROM mp_orders WHERE ID = $order_id");

            if ($check_order->status === 'cancelled' || $check_order->status === 'received' ||  $check_order->status === 'delivered' || $check_order->status === 'shipping'  ) {
                return array(
                    "status" => "failed",
                    "message" => "This order is cannot be cancelled."
                );
            }
            
            // update order to cancelled
			$result = $wpdb->query("UPDATE mp_orders SET  `status` = 'cancelled' WHERE ID = $order_id AND wpid = $user_id ");
            
            if ( $result < 1 ) {
                return array(
                    "status"  => "failed",
                    "message" => "An error occured while submiting data to server."
                );

            }else{
                return array(
                    "status"  => "success",
                    "message" => "Order has been cancelled successfully."
                );
            }
		}
	}