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
            
            $stid = $_POST['stid'];
            $odid = $_POST['odid'];
            
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


            $verify_store =$wpdb->get_row("SELECT ID FROM tp_stores WHERE ID = 1 ");

            if (!$verify_store) {
                return array(
                    "status" => "unknown",
                    "message" => "An error occured while fetching data to server."
                );
            }

            $check_order = $wpdb->get_row("SELECT `status` FROM mp_orders WHERE ID = $odid");
            if ($check_order == 'cancelled') {
                return array(
                    "status" => "failed",
                    "message" => "This order has already been cancelled."
                );
            }
            
            $result = $wpdb->query("UPDATE mp_orders SET  `status` = 'cancelled' WHERE ID = $odid AND stid = $store_id");
            
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