
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
            $fields_ord_it = MP_ORDER_ITEMS_TABLE_FIELD;                                 
            $table_ord_it = MP_ORDER_ITEMS_TABLE;

            // order table 
            $fields_ord = MP_ORDER_TABLE_FIELD;                                 
            $table_ord = MP_ORDERS_TABLE;
                                           
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD; 

            // tp tables 
            $table_prod = TP_PRODUCT_TABLE;
            $table_store = TP_STORES_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
           
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
            if (!isset($_POST["qty"]) 
                || !isset($_POST["pdid"]) 
                || !isset($_POST["stid"]) 
                || !isset($_POST["opid"])  ) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request Unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["qty"]) 
                || empty($_POST["pdid"]) 
                || empty($_POST["stid"]) 
                || empty($_POST["opid"])  ) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );  
            }

            // Step 5: Check if parameters passed is numeric
            if (!is_numeric($_POST["qty"])  ) {
				return array(
						"status" => "failed",
						"message" => "Required ID is not in valid format.",
                );
            }

            // Step 6: Check if store id and operation id are exists or not
            $verify_store =$wpdb->get_row("SELECT ID FROM $table_store WHERE ID = '{$user["stid"]}' ");
            if (!$verify_store) {
                return array(
                    "status" => "failed",
                    "message" => "No store found.",
                );
            }

            // Step 7: Check if the product is inside the store and the status is active or not
            $verify_status = $wpdb->get_row("SELECT child_val AS status FROM $table_tp_revs WHERE ID = (SELECT status FROM $table_prod WHERE ID = '{$user["pid"]}' AND stid = '{$user["stid"]}')");
            if (!($verify_status->status === '1')) {
                return array(
                    "status" => "failed",
                    "message" => "No product found.",
                );
            }
            $get_data =$wpdb->get_row("SELECT title AS name_id, price AS price_id FROM $table_prod WHERE ID = '{$user["pid"]}' ");
            $child_key = array( 
                'title'     =>$get_data->name_id, 
                'price'     =>$get_data ->price_id, 
                'quantity'  =>$user["qty"], 
                'status'    =>$user["status"]
            );

            // Step 8: Insert Query
            $wpdb->query("START TRANSACTION");
            $wpdb->query("INSERT INTO $table_ord $fields_ord VALUES ('{$user["stid"]}', '{$user["opid"]}', '{$user["uid"]}', '0', '0', '$date') ");
            $order_id = $wpdb->insert_id;

            $wpdb->query("INSERT INTO $table_ord_it $fields_ord_it VALUES ('$order_id', '{$user["pid"]}', '0', '$date') ");
             $order_items_id = $wpdb->insert_id;

            foreach ( $child_key as $key => $child_val) {
                $insert_result = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('{$user["type"]}', '$order_items_id', '$key', '$child_val', '{$user["uid"]}', '$date') ");
                $last_id = $wpdb->insert_id;
            }
            
            $update_result = $wpdb->query("UPDATE $table_ord_it SET `status` = $last_id WHERE ID IN ($order_items_id) ");

            $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$order_id', 'status', 'pending', '{$user["uid"]}', '$date') ");
            $order_revs = $wpdb->insert_id;
            $result = $wpdb->query("UPDATE $table_ord SET status = $order_revs WHERE ID IN ($order_id) ");
            //return $last_id;
            //return $insert_result;
            if ( $order_id < 1 ||$order_items_id < 1 || $insert_result < 1 || $update_result < 1 ) {

                // Step 9: If failed, do mysql rollback (discard the insert queries(no inserted data))
                $wpdb->query("ROLLBACK");
                return array(
                   "status" => "failed",
                   "message" => "An error occured while submitting data to the server."
                );
            }else{

                // Step 10: If no problems found in queries above, do mysql commit (do changes(insert rows))
                $wpdb->query("COMMIT");
                return array(
                        "status" => "success",
                        "message" => "Order added successfully."
                );
            }
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

            // Step 8: Insert Query
            /*$wpdb->query("START TRANSACTION");
    
                $wpdb->query("INSERT INTO $table_ord_it $fields_ord_it VALUES ('0', '{$user["product_id"]}', '{$user["quantity"]}', '0', '$date') ");
                $order_items = $wpdb->insert_id;
                                                                                
                $wpdb->query("INSERT INTO $table_ord $fields_ord VALUES ('{$user["store_id"]}', '{$user["operation_id"]}', '{$user["created_by"]}', '{$user["created_by"]}', '$date', 'pending') ");
                $order = $wpdb->insert_id;

                $result = $wpdb->query("UPDATE $table_ord_it SET odid = $order WHERE ID IN ($order_items) ");

            if ($order_items < 1 || $order < 1 || $result < 1 ) {

                 // Step 9: If failed, do mysql rollback (discard the insert queries(no inserted data))
                 $wpdb->query("ROLLBACK");
                 return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to the server."
                 );
            }else{

                // Step 10: If no problems found in queries above, do mysql commit (do changes(insert rows))
                $wpdb->query("COMMIT");
                return array(
                        "status" => "success",
                        "message" => "Order added successfully."
                );
            }*/
        }
        
        // Catch Post 
        public static function catch_post()
        {
			$cur_user = array();
			
            $cur_user['pid'] = $_POST['pdid'];
            $cur_user['qty']  = $_POST['qty'];
			$cur_user['uid']  = $_POST['wpid'];
			$cur_user['stid'] = $_POST['stid'];
			$cur_user['opid'] = $_POST['opid'];
			$cur_user['type'] = 'order_items';
			$cur_user['status'] = '1';

            return  $cur_user;
        }
    }