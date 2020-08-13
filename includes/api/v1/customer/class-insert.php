
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

	class MP_Insert_Order {

        public static function listen(){
            return rest_ensure_response( 
                MP_Insert_Order:: list_open()
            );
        }
        
        public static function list_open(){
            
            global $wpdb;

            $date = MP_Globals:: date_stamp();
            $user = MP_Insert_Order:: catch_post();

            // order items table 
            $order_items_fields = MP_ORDER_ITEMS_TABLE_FIELD;                                 
            $order_items_table = MP_ORDER_ITEMS_TABLE;

            // order table 
            $order_fields = MP_ORDER_TABLE_FIELD;                                 
            $order_table = MP_ORDERS_TABLE;

            // tp tables 
            $table_prod = TP_PRODUCT_TABLE;
            $table_store = TP_STORES_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
           
            //Step1 : Check if prerequisites plugin are missing
            $plugin = MP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step2 : Check if wpid and snky is valid
            if (DV_Verification::is_verified() == false) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Verification Issues!",
                );
            }

            // Step 2: Sanitize and validate all requests
            if (!isset($_POST["qty"]) 
                || !isset($_POST["pdid"]) 
                || !isset($_POST["stid"]) 
                || !isset($_POST["opid"])  ) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request Unknown!",
                );
            }

            // Step 2: Sanitize and validate all requests
            if (empty($_POST["qty"]) 
                || empty($_POST["pdid"]) 
                || empty($_POST["stid"]) 
                || empty($_POST["opid"])  ) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );  
            }

            // Step 2: Sanitize and validate all requests
            if (!is_numeric($_POST["qty"])  ) {
				return array(
						"status" => "failed",
						"message" => "Required ID is not in valid format.",
                );
            }

            // Step3 : Validate store id and operation id if exists or not
            $verify_store =$wpdb->get_row("SELECT ID FROM $table_store WHERE ID = '{$user["store_id"]}' ");
            if (!$verify_store) {
                return array(
                    "status" => "failed",
                    "message" => "No store found.",
                );
            }

            // Step4 : Check if there's a product inside the store and validate the status if active or not
            $verify_status = $wpdb->get_row("SELECT child_val FROM $table_tp_revs WHERE ID = (SELECT status FROM $table_prod WHERE ID = '{$user["product_id"]}' AND stid = '{$user["store_id"]}')");
            if ($verify_status->$status < 1) {
                return array(
                    "status" => "failed",
                    "message" => "No product found.",
                );
            }
            return 'added';

            // validation of product -> old query
            /* $get_product_status = $wpdb->get_row("SELECT
                    tp_rev.child_val as `status`
                FROM
                    $tp_prod_table tp_prod
                    LEFT JOIN $tp_revs_table tp_rev ON tp_rev.ID = tp_prod.`status` 
                WHERE
                    tp_prod.ID = {$user["product_id"]}");
                    
            if ($get_product_status->status === '0' ) {
                return array(
                    "status" => "failed",
                    "message" => "This product does not exist."
                 );
            }*/

            // Step5 : Insert Query
            $wpdb->query("START TRANSACTION");
    
                $wpdb->query("INSERT INTO $order_items_table $order_items_fields VALUES ('0', '{$user["product_id"]}', '{$user["quantity"]}', '0', '$date') ");
                $order_items = $wpdb->insert_id;
                                                                                
                $wpdb->query("INSERT INTO $order_table $order_fields VALUES ('{$user["store_id"]}', '{$user["operation_id"]}', '{$user["created_by"]}', '{$user["created_by"]}', '$date', 'pending'  ) ");
                $order = $wpdb->insert_id;

                $result = $wpdb->query("UPDATE $order_items_table SET odid = $order WHERE ID IN ($order_items) ");

            if ($order_items < 1 || $order < 1 || $result < 1 ) {

                 // Step6 : If failed, do mysql rollback (discard the insert queries(no inserted data))
                 $wpdb->query("ROLLBACK");
                 return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to the server."
                 );
            }else{

                // Step7 : If no problems found in queries above, do mysql commit (do changes(insert rows))
                $wpdb->query("COMMIT");
                return array(
                        "status" => "success",
                        "message" => "Order added successfully."
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