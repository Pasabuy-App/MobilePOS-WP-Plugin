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
            
            // $verify_store =$wpdb->get_row("SELECT ID FROM tp_stores WHERE ID = 1 ");
            // if (!$verify_store) {
            //     return array(
            //         "status" => "sucess",
            //         "message" => "An error occured while fetching data to server."
            //     );
            // }

            $result = $wpdb->query("UPDATE mp_orders SET  `status` = 'cancelled' WHERE ID = 1 AND stid = 1");
            
            if ( $result < 1 ) {

            }else{
                return array(
                    "status"  => "success",
                    "message" => "Order has been cancelled successfully."
                );
            }
        }
    }