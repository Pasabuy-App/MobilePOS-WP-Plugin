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
?>
<?php
	class MP_Cancel_Order_Store {
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

            $store_id = $_POST['stid'];
            $order_id = $_POST['odid'];

            $verify_store =$wpdb->get_row("SELECT ID FROM tp_stores WHERE ID = 1 ");

            if (!$verify_store) {
                return array(
                    "status" => "unknown",
                    "message" => "An error occured while fetching data to server."
                );
            }

            $check_order = $wpdb->get_row("SELECT `status` FROM mp_orders WHERE ID = $order_id");
            if ($check_order == 'cancelled') {
                return array(
                    "status" => "failed",
                    "message" => "This order has already been cancelled."
                );
            }
            
            $result = $wpdb->query("UPDATE mp_orders SET  `status` = 'cancelled' WHERE ID = $order_id AND stid = $store_id");
            
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