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
                self:: list_open()
            );
        }

        // Catch Post
        public static function catch_post()
        {
            $cur_user = array();
            $data = $_POST['data'];
            $cur_user['items']  = $data['items'];
            $cur_user['method']  = $_POST['method'];
			$cur_user['uid']  = $_POST['wpid'];
			$cur_user['stid'] = $_POST['stid'];
			// $cur_user['opid'] = $_POST['opid'];
			$cur_user['address_id'] = $_POST['addid'];
			$cur_user['type'] = 'order_items';
			$cur_user['status'] = '1';

            return  $cur_user;
        }


        public static function list_open(){
            global $wpdb;

            $fields_ord_it = MP_ORDER_ITEMS_TABLE_FIELD;
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            $fields_ord = MP_ORDER_TABLE_FIELD;
            $table_ord = MP_ORDERS_TABLE;
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD;
            $table_prod = TP_PRODUCT_TABLE;
            $table_store = TP_STORES_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
            $time = time();

            //Step 1: Check if prerequisites plugin are missing
            $plugin = MP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Validate user
            // if (DV_Verification::is_verified() == false) {
            //     return array(
                //     "status" => "unknown",
                //     "message" => "Please contact your administrator. Verification issues!",
                // );
            // }

            $user = self:: catch_post();

            // return $notify_store = MP_Globals::call_usn_notify();

            // if ($notify_store['status'] == "505" || $notify_store['status'] == "404"  ) {
            //     return array(
            //         "status" => $notify_store['status'],
            //         "message" => $notify_store['message']
            //     );
            // }

            $date = MP_Globals:: date_stamp();

            // $check_address = $wpdb->get_row("SELECT * FROM dv_address WHERE ID = '{$user["address_id"]}' AND wpid = '{$user["uid"]}'");

            // if (!$check_address) {
            //     return array(
			// 		"status" => "failed",
			// 		"message" => "This Address does not exits.",
            //     );
            // }

            // Step 6: Check if store is exist/active and
            // TODO : operation is exists/active
            $verify_store = $wpdb->get_row("SELECT ID FROM $table_store WHERE ID = '{$user["stid"]}' ");
            $verify_store_stat = $wpdb->get_row("SELECT child_val AS status FROM tp_revisions WHERE id = (SELECT status FROM tp_stores WHERE ID = '{$user["stid"]}')");
            if (!$verify_store || !($verify_store_stat->status === '1')) {
                return array(
                    "status" => "failed",
                    "message" => "This store does not exists.",
                );
            }

            foreach ($user['items'] as $key => $value) {

                // Step 7: Check if the product is inside the store and the status is active or not
                $rify_prod = $wpdb->get_row("SELECT `status` FROM $table_prod WHERE ID = '{$value["pdid"]}' AND stid = '{$user["stid"]}'");
                $verify_status = $wpdb->get_row("SELECT child_val AS `status` FROM $table_tp_revs WHERE ID = (SELECT `status` FROM $table_prod WHERE ID = '{$value["pdid"]}' AND stid = '{$user["stid"]}')");
                if (empty($verify_status)) {
                    return array(
                        "status" => "failed",
                        "message" => "This product does not exists.",
                    );
                }

                if (!($verify_status->status == "1")) {
                    return array(
                        "status" => "failed",
                        "message" => "This product does not exists.",
                    );
                }
            }

            isset($_POST['msg']) ? $remarks = trim($_POST['msg']) : $remarks = NULL  ; // set message is null

            // Step 8: Start mysql transaction
            $wpdb->query("START TRANSACTION");

                $sched = $wpdb->get_results("SELECT * FROM tp_schedule WHERE stid = '{$user["stid"]}' ");

                foreach ($sched as $key => $value) {
                    if(date('D', $time) == ucfirst($value->type)){
                        $smp = $value->type;
                    }
                }

                $check_operation = $wpdb->get_row("SELECT
                    ID,
                    DATE(date_open) as date_open,
                    TIME(date_open) as time_open,
                    DATE(date_close) as date_close,
                    TIME(date_close) as time_close,
                    (SELECT `type` FROM tp_schedule WHERE ID = sched_id ) as `days`
                FROM mp_operations
                HAVING  `days` = '$smp' AND date_close is null  ");

                if (empty($check_operation)) {

                    $insert_rev = $wpdb->query("INSERT INTO mp_revisions (revs_type, parent_id, child_key, child_val, created_by, date_created) VALUES ('operations', '0', 'open_by', '{$user["uid"]}', '{$user["uid"]}', '$date') ");
                    $open_by = $wpdb->insert_id;

                    $insert_operation = $wpdb->query("INSERT INTO mp_operations (date_open, open_by ) VALUES ('$date', '$open_by' )");
                    $operation_id = $wpdb->insert_id;

                    $hash_rev = MP_Globals::update_hash_id_hash($open_by, 'mp_revisions', 'hash_id');

                    $wpdb->query("UPDATE mp_revisions SET parent_id = '$operation_id' WHERE ID = '$insert_operation' ");

                    $hash_ope = MP_Globals::update_hash_id_hash($insert_operation, 'mp_operations', 'hash_id');

                }else{
                    $operation_id = $check_operation->ID;
                }

                // Check to mo_orders if the stid, opid, wpid and date is same, if same then insert into mp_order_items, if not, add another row in mp_orders
                $check_order =$wpdb->get_row("SELECT * FROM $table_ord WHERE stid = '{$user["stid"]}' AND opid = '$operation_id' AND wpid = '{$user["uid"]}' AND date_created = '$date' ");

                if($check_order){ // pag may laman get order id
                    $order_id = $check_order->ID;
                }else{

                    // pag wala insert into mp_orders
                    // Insert into table orders (store id, operation id, customer id, user id = 0, status = 0 and date)
                    $wpdb->query("INSERT INTO $table_ord $fields_ord VALUES ('{$user["stid"]}', '$operation_id', '{$user["uid"]}', '0', '0', '0', '$date') ");
                    $order_id = $wpdb->insert_id;

                    $update_order_key = MP_Globals::update_hash_id_hash($order_id, $table_ord, "hash_id");

                    if(!$update_order_key){
                        return array(
                            "status" => "failed",
                            "message" => "An error occured while submitting data to the server."
                        );
                    }
                }

                $id = array();
                $child_key = array();

                foreach ($user['items'] as $key => $value) {

                    $get_data =$wpdb->get_row("SELECT title AS name_id, price AS price_id FROM $table_prod WHERE ID = '{$value['pdid']}' ");

                    $wpdb->query("INSERT INTO $table_ord_it $fields_ord_it VALUES ('$order_id', '{$value['pdid']}', '0', '0','$date') ");
                    $order_items_id  = $wpdb->insert_id;

                    $insert_result = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('{$user["type"]}', '$order_items_id', 'title', '$get_data->name_id', '{$user["uid"]}', '$date') ");
                    $title = $wpdb->insert_id; // Insert last id to array

                    $insert_result = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('{$user["type"]}', '$order_items_id', 'price', '$get_data->price_id', '{$user["uid"]}', '$date') ");
                    $price = $wpdb->insert_id; // Insert last id to array

                    $insert_result = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('{$user["type"]}', '$order_items_id', 'quantity', '{$value['qty']}', '{$user["uid"]}', '$date') ");
                    $quantity = $wpdb->insert_id; // Insert last id to array

                    $insert_result = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('{$user["type"]}', '$order_items_id', 'status', '{$user['status']}', '{$user["uid"]}', '$date') ");
                    $status = $wpdb->insert_id; // Insert last id to array

                    $update_order = $wpdb->query("UPDATE $table_ord_it SET quantity = '$quantity', `status` = '$status' WHERE ID = $order_items_id ");

                    if(isset($value['variants'])){
                        foreach ($value['variants'] as $key => $value) {
                            $insert_variants = $wpdb->query("INSERT INTO mp_order_item_variant (vrid, item_id) VALUES ('$value', '$order_items_id') ");
                            $insert_variants_id = $wpdb->insert_id;
                            $insert_variants_hash = MP_Globals::update_hash_id_hash($insert_variants_id, 'mp_order_item_variant', "hash_id");

                            if ($insert_variants < 1) {
                                $wpdb->query("ROLLBACK");
                                return array(
                                    "status" => "failed",
                                    "message" => "An error occured while submitting data to the server."
                                );
                            }
                        }
                    }
                }


                if ( !empty($remarks) ) { // if remarks is not empty, insert to mp revisions
                    $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$order_id', 'remarks', '$remarks', '{$user["uid"]}', '$date' ) ");
                }

                // Insert into table revisions (revision type = orders, last id of insert of order, key = status, value = pending, customer id and date )
                $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$order_id', 'status', 'pending', '{$user["uid"]}', '$date') ");
                $order_status_id = $wpdb->insert_id;

                $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$order_id', 'address', '{$user["address_id"]}', '{$user["uid"]}', '$date') ");
                $order_address = $wpdb->insert_id;

                $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$order_id', 'method', '{$user["method"]}', '{$user["uid"]}', '$date') ");
                $order_method_id = $wpdb->insert_id;

                // Update status of order and quantity
                $update_ord = $wpdb->query("UPDATE $table_ord SET `status` = $order_status_id, method = $order_method_id WHERE ID IN ($order_id) ");

            // Step 9: Check if any queries above failed
            if ( $order_id < 1 ||$order_items_id < 1 || $insert_result < 1 || $order_status_id < 1|| $update_ord < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to the server."
                );
            }

            // Step 10: Commit if no errors found
            // $message_user = MP_Globals::call_usn_message($wpid, 'Your order has been received.', 'store-accepted');
            // if ($notify_store['status'] == "505" || $notify_store['status'] == "404"  ) {
            //     $wpdb->query("ROLLBACK");
            //     return array(
            //         "status" => $notify_store['status'],
            //         "message" => $notify_store['message']
            //     );
            // }else{
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "data" => $user["stid"]
                );
            // }
        }
    }