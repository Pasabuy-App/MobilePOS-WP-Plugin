
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
            $fields_ord_it = MP_ORDER_ITEMS_TABLE_FIELD;                                 
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            $fields_ord = MP_ORDER_TABLE_FIELD;                                 
            $table_ord = MP_ORDERS_TABLE;   
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD; 
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
						"message" => "Please contact your administrator. Request unknown!",
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

            // Step 6: Check if store is exist/active and // TODO : operation is exists/active
            $verify_store = $wpdb->get_row("SELECT ID FROM $table_store WHERE ID = '{$user["stid"]}' ");
            $verify_store_stat = $wpdb->get_row("SELECT child_val AS status FROM tp_revisions WHERE id = (SELECT status FROM tp_stores WHERE ID = '{$user["stid"]}')");
            if (!$verify_store || !($verify_store_stat->status === '1')) {
                return array(
                    "status" => "failed",
                    "message" => "No store found.",
                );
            }

            // Step 7: Check if the product is inside the store and the status is active or not
            $verify_prod = $wpdb->get_row("SELECT status FROM $table_prod WHERE ID = '{$user["pid"]}' AND stid = '{$user["stid"]}'");
            $verify_status = $wpdb->get_row("SELECT child_val AS status FROM $table_tp_revs WHERE ID = (SELECT status FROM $table_prod WHERE ID = '{$user["pid"]}' AND stid = '{$user["stid"]}')");
            if (!$verify_prod || !($verify_status->status === '1')) {
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
            // Insert into table orders (store id, operation id, customer id, user id = 0, status = 0 and date)
            $wpdb->query("INSERT INTO $table_ord $fields_ord VALUES ('{$user["stid"]}', '{$user["opid"]}', '{$user["uid"]}', '0', '0', '$date') ");
            $order_id = $wpdb->insert_id;
            // Insert into table order items (order id, customer id who create = 0, status = 0 and date)
            $wpdb->query("INSERT INTO $table_ord_it $fields_ord_it VALUES ('$order_id', '{$user["pid"]}', '0', '0','$date') ");
            $order_items_id = $wpdb->insert_id;
            $id = array();
            // Loop data array from child key with child value and insert to table revisions (revision type, last id of insert of order items, key, value, cusotmer id and date)
            foreach ( $child_key as $key => $child_val) {
                $insert_result = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('{$user["type"]}', '$order_items_id', '$key', '$child_val', '{$user["uid"]}', '$date') ");
                $id[] = $wpdb->insert_id; // Insert last id to array
            }
            // Insert into table revisions (revision type = orders, last id of insert of order, key = status, value = pending, customer id and date )
            $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$order_id', 'status', 'pending', '{$user["uid"]}', '$date') ");
            $order_status_id = $wpdb->insert_id;
            // Update status of order and quantity
            $update_ord = $wpdb->query("UPDATE $table_ord SET `status` = $order_status_id WHERE ID IN ($order_id) ");
            $update_ordit = $wpdb->query("UPDATE $table_ord_it SET `quantity` = '$id[2]', `status` = '$id[3]' WHERE ID IN ($order_items_id) ");
            
            if ( $order_id < 1 ||$order_items_id < 1 || $insert_result < 1 || $order_status_id < 1|| $update_ord < 1 || $update_ordit < 1 ) {
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