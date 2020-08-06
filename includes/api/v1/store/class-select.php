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
	class MP_Select_Order {
        public static function listen(){
            global $wpdb;

            // Step1 : check if datavice plugin is activated
            if (MP_Globals::verifiy_plugins() == false) {
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
      
            $result = $wpdb->get_results("SELECT
                    mp_orders.ID,
                    (SELECT wp_users.display_name FROM wp_users WHERE wp_users.ID = mp_orders.wpid  ) as ordered_by,
                    (SELECT tp_revisions.child_val FROM tp_revisions WHERE ID = tp_stores.title AND tp_revisions.parent_id = tp_stores.ID )as `store_name`,
                    (SELECT tp_revisions.child_val  FROM tp_revisions WHERE tp_revisions.ID = (SELECT tp_products.title FROM tp_products WHERE tp_products.ID = mp_order_items.pdid  ))as `product_name`,
                    mp_order_items.quantity as order_quantity,
                    mp_orders.date_created as order_created
                FROM
                    mp_orders
                    LEFT JOIN mp_order_items ON mp_order_items.odid = mp_orders.ID
                    LEFT JOIN tp_stores ON tp_stores.ID = mp_orders.stid
                WHERE mp_orders.ID = 11 AND mp_orders.`status` = 'pending'");




        }


        public static function catch_post()
        {
			$cur_user = array();			
         
			$cur_user['created_by']    = $_POST['wpid'];
			$cur_user['order_id']      = $_POST['odid'];

            return  $cur_user;
        }
    }           
