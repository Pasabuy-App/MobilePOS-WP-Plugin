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
	class MP_Process {

        public static function listen(){
            return rest_ensure_response( 
                MP_Process:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;

            // Get Order ID and status order (received/rejected)
            $date = MP_Globals:: date_stamp(); 
            $table_store = TP_STORES_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
            $table_ord = MP_ORDERS_TABLE;                                     
            $table_mp_revs = MP_REVISIONS_TABLE;
            $fields_mp_revs = MP_REVISIONS_TABLE_FIELD; 
            $wpid = $_POST['wpid'];
            $stid = $_POST['stid'];
            $odid = $_POST['odid'];
            $stage = $_POST['stage'];

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
            if (!isset($odid) 
                || !isset($stage)
                || !isset($stid)) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($odid) 
                || empty($stid)
                || empty($stage)) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Check if stage input is not received or cancelled
            if ($stage === 'pending' || $stage === 'completed') {
                return array(
                    "status" => "failed",
                    "message" => "This process is not for $stage.",
                );
            }

            // Step 6: Check if stage input is received or cancelled
            if ($stage === 'received' || $stage === 'cancelled' || $stage === 'shipping') {

                // Step 7: Validate store and store staus if active
                $verify_store =$wpdb->get_row("SELECT ID FROM $table_store WHERE ID = '$stid' "); // Check if store is exist or not
                $verify_store_stat =$wpdb->get_row("SELECT child_val AS status FROM $table_tp_revs WHERE ID = (SELECT status FROM $table_store WHERE ID = '$stid') "); // If exist, check status
                if (!$verify_store || !($verify_store_stat->status === '1')) {
                    return array(
                        "status" => "failed",
                        "message" => "No store found.",
                    );
                }
    
                // Step 8: Validate order using order id and store id in mp orders table
                $verify_order =$wpdb->get_row("SELECT ID FROM $table_ord WHERE ID = '$odid' AND stid = '$stid' ");
                if (!$verify_order) {
                    return array(
                        "status" => "failed",
                        "message" => "No order found.",
                    );
                }

                // Step 9: Check if order status is the same in stage input
                $verify_stage = $wpdb->get_row("SELECT child_val AS status FROM $table_mp_revs WHERE ID = (SELECT `status` FROM $table_ord WHERE ID = '$odid' AND stid = '$stid') ");
                if ($verify_stage->status === $stage) {
                    return array(
                        "status" => "failed",
                        "message" => "This order has already been $verify_stage->status.",
                    );
                }

                if ($stage === 'received' || $stage === 'cancelled'){
                    // Step 9: Check if order status is pending
                    if (!($verify_stage->status === 'pending')) {
                        return array(
                            "status" => "failed",
                            "message" => "This order can't be $stage.",
                        );
                    }
                }
                
                if ($stage === 'shipping'){
                    // Step 9: Check if order status is received
                    if (!($verify_stage->status === 'received')) {
                        return array(
                            "status" => "failed",
                            "message" => "This order can't be $stage.",
                        );
                    }
                }
            
                // Step 11: Update query
                $wpdb->query("START TRANSACTION");
                // Insert into table revision (type = orders, order id, key = status, value = status value, customer id and date)
                
                if ($stage === 'cancelled') {
                    $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'key_type', 'preparing', '$wpid', '$date') ");
                    $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'cancel_by', 'store', '$stid', '$date') ");
                }

                $insert = $wpdb->query("INSERT INTO $table_mp_revs $fields_mp_revs VALUES ('orders', '$odid', 'status', '$stage', '$wpid', '$date') ");
                $order_status = $wpdb->insert_id;
                $result = $wpdb->query("UPDATE $table_ord SET created_by = '$wpid', status = '$order_status' WHERE ID IN ($odid) ");

                // Step 12: Check result
                if ( $insert < 1 || $result < 1 ) {
                    $wpdb->query("ROLLBACK");
                    return array(
                        "status"  => "failed",
                        "message" => "An error occured while submiting data to server."
                    );
                } else {
                    $wpdb->query("COMMIT");
                    return array(
                        "status"  => "success",
                        "message" => "Order has been $stage successfully."
                    );
                }
            }
            else{
                return array(
                    "status" => "failed",
                    "message" => "Invalid stage.",
                );
            }

        }
    }