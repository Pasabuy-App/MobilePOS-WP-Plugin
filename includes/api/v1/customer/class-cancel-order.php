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
	class MP_Cancel_Order {
        public static function listen(){
			global $wpdb;

            $order_id = $_POST['odid'];

			$check_order = $wpdb->get_row("SELECT `status` FROM mp_orders WHERE ID = $order_id");
            if ($check_order == 'cancelled') {
                return array(
                    "status" => "failed",
                    "message" => "This order has already been cancelled."
                );
			}

			return $result = $wpdb->query("UPDATE mp_orders SET  `status` = 'cancelled' WHERE ID = $order_id ");
            
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
?>