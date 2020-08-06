
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
	class MP_Orders {
        public static function listen(){
            global $wpdb;
           
            // Step1 : check if datavice plugin is activated
            if (MP_Globals::verifiy_datavice_plugin() == false) {
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

            // Step 2: Sanitize and validate all requests
            if (!isset($_POST["qty"]) || !isset($_POST["pdid"]) 
            || !isset($_POST["stid"]) || !isset($_POST["opid"])  ) {
				return array(
						"status" => "failed",
						"message" => "Please contact your administrator. Request Unknown!",
                );
                
            }

            // Step 2: Sanitize and validate all requests
            if (empty($_POST["qty"]) || empty($_POST["pdid"]) 
              || empty($_POST["stid"]) || empty($_POST["opid"])  ) {
                return array(
                        "status" => "failed",
                        "message" => "Please contact your administrator. Request Unknown!",
                );
                  
            }

            // Step 2: Sanitize and validate all requests
            if (!is_numeric($_POST["wpid"]) || !is_numeric($_POST["qty"]) 
            || !is_numeric($_POST["pdid"]) || !is_numeric($_POST["stid"]) 
            || !is_numeric($_POST["opid"])  ) {
				return array(
						"status" => "failed",
						"message" => "Please contact your administrator. Request Unknown!",
                );
                
            }

            $date = MP_Globals:: date_stamp();
            $user = MP_Orders:: catch_post();

            // order items table 
            $order_items_fields = MP_ORDER_ITEMS_TABLE_FIELD;                                 
            $order_items_table = MP_ORDER_ITEMS_TABLE;

            // order table 
            $order_fields = MP_ORDER_TABLE_FIELD;                                 
            $order_table = MP_ORDER_TABLE;


            $wpdb->query("START TRANSACTION");
    
                $wpdb->query("INSERT INTO $order_items_table $order_items_fields VALUES ('0', '{$user["product_id"]}', '{$user["quantity"]}', '0', '$date') ");
                $order_items = $wpdb->insert_id;
                                                                                
                $wpdb->query("INSERT INTO $order_table $order_fields VALUES ('{$user["store_id"]}', '{$user["operation_id"]}', '{$user["created_by"]}', '{$user["created_by"]}', '$date', 'pending'  ) ");
                $order = $wpdb->insert_id;

                $result = $wpdb->query("UPDATE $order_items_table SET odid = $order WHERE ID IN ($order_items) ");


            if ($order_items < 1 || $order < 1 || $result < 1 ) {
 
                 //If failed, do mysql rollback (discard the insert queries(no inserted data))
                 $wpdb->query("ROLLBACK");
                    
                 return rest_ensure_response( 
                     array(
                         "status" => "error",
                         "message" => "An error occured while submitting data to the server."
                     )
                 );
             }
 
            //If no problems found in queries above, do mysql commit (do changes(insert rows))
            $wpdb->query("COMMIT");

            if ($result < 1) {
                return rest_ensure_response( 
                    array(
                        "status" => "failed",
                        "message" => "An error occured while submitting data to the server."
                    )
                );
            }else{
                return rest_ensure_response( 
                    array(
                        "status" => "success",
                        "message" => "Order added successfully."
                    )
                );
            }
            
        }
        
        // Catch Post 
        public static function catch_post()
        {
			$cur_user = array();
			
            $cur_user['product_id']    = $_POST['pdid'];
            $cur_user['quantity']      = $_POST['qty'];
			$cur_user['created_by']    = $_POST['wpid'];
			$cur_user['store_id']      = $_POST['stid'];
			$cur_user['operation_id']      = $_POST['opid'];

            return  $cur_user;
        }
    }