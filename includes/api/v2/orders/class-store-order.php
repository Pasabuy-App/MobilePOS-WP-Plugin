
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
	class MP_Insert_Order_Store_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['opid'] = $_POST['opid'];
            $curl_user['dlfee'] = $_POST['dlfee'];
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['adid'] = $_POST['adid'];
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['items'] = $_POST['data']['items'];
            $curl_user['payments'] = $_POST['data']['payments'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_product = TP_PRODUCT_v2;
            $tbl_order = MP_ORDERS_v2;
            $tbl_coupons = MP_COUPONS_v2;
            $tbl_order_field = MP_ORDERS_FILED_v2;
            $tbl_order_times = MP_ORDERS_ITEMS_v2;
            $tbl_order_times_field = MP_ORDERS_ITEMS_FIELD_v2;
            $tbl_order_times_vars = MP_ORDERS_ITEMS_VARS_v2;
            $tbl_order_times_vars_field = MP_ORDERS_ITEMS_VARS_FIELD_v2;
            $tbl_inventory = MP_INVENTORY_v2;
            $tbl_inventory_fields = MP_INVENTORY_FIELD_v2;



            $plugin = MP_Globals_v2::verify_prerequisites();
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
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            if (!isset($_POST['wpid']) || !isset($_POST['adid']) || !isset($_POST['stid']) ) {
                return  array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request Unknown!",
                );
            }

            $user = self::catch_post();

            $validate = MP_Globals_v2::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }
            isset($_POST['msg']) && !empty($_POST['msg'])? $user['msg'] =  $_POST['msg'] :  $user['msg'] = null ;

            if (empty($_POST['data']['payments'])) {
                return array(
                    "status" => "failed",
                    "message" => "Payment method cannot be empty."
                );
            }

            // CHECK IF PRODUCT EXISTS
                foreach ($user['items'] as $key => $value) {

                    $check_product = $wpdb->get_row("SELECT `status`, `title` FROM $tbl_product WHERE hsid = '{$value["pdid"]}' AND ID IN ( SELECT MAX( pdd.ID ) FROM $tbl_product  pdd WHERE pdd.hsid = hsid GROUP BY hsid ) ");
                    if (empty($check_product)) {
                        return array(
                            "status" => "failed",
                            "message" => "This product does not exists. $check_product->hsid"
                        );
                    }

                    if ($check_product->status == "inactive") {
                        return array(
                            "status" => "failed",
                            "message" => "This product is currently inactive. $check_product->title"
                        );
                    }

                }
            // END

            // Set expiration date of this order
                $start = date('Y-m-d H:i:s');

                $expiry_config = MP_Library_Config::get_config('order_expiry', '');

                $expiry = date('Y-m-d H:i:s',strtotime( $expiry_config['expiry'], strtotime($start)));
            // End

            $wpdb->query("START TRANSACTION");

            // IMPORT ORDER DATA
                $insert_order = $wpdb->query("INSERT INTO
                    $tbl_order
                        (`delivery_charges`,$tbl_order_field)
                    VALUES
                        ( '{$user["dlfee"]}','{$user["opid"]}', 'completed', '{$user["adid"]}', '{$user["msg"]}', '{$user["wpid"]}', '$expiry') ");
                $insert_order_id = $wpdb->insert_id;

                $insert_order_pubkey = MP_Globals_v2::generating_pubkey($insert_order_id, $tbl_order, 'pubkey', true, 5);
                $insert_order_hsid = MP_Globals_v2::generating_pubkey($insert_order_id, $tbl_order, 'hsid', false, 64);
            // END

            // IMPORT ORDER ITEMS DATA
                foreach ($user['items'] as $key => $value) {

                    $insert_order_items = $wpdb->query("INSERT INTO
                        $tbl_order_times
                            ($tbl_order_times_field)
                        VALUES
                            ('$insert_order_pubkey',  '{$value["pdid"]}', '{$value["remarks"]}', '{$value["qty"]}', '{$user["wpid"]}' ) ");
                    $insert_order_items_id = $wpdb->insert_id;

                    $insert_order_items_hsid = MP_Globals_v2::generating_pubkey($insert_order_items_id, $tbl_order_times, 'hsid', true, 64);

                    // IMPORT ORDER ITEMS VARS DATA
                    if ( isset($value['variants']) ) {
                        foreach ($value['variants'] as $key => $value) {
                            $insert_order_items_vars = $wpdb->query("INSERT INTO
                                $tbl_order_times_vars
                                    ($tbl_order_times_vars_field)
                                VALUES
                                    ( '$insert_order_items_hsid', '$value', '{$user["wpid"]}') ");

                            $insert_order_items_vars_id = $wpdb->insert_id;

                            $insert_order_items_vars_hsid = MP_Globals_v2::generating_pubkey($insert_order_items_vars_id, $tbl_order_times_vars, 'hsid', false, 64);
                        }
                    }
                }
            // End

            /**
             * Process payment
            */
                $counpon_val = 0;
                $delivery_fee = $user["dlfee"];
                // IMPORT PAYMENT

                $TOTAL_PRICE = 0;
                $discount = false;

                foreach ($user['items'] as $key => $value) {

                    // get product
                        $get_product_data = $wpdb->get_row("SELECT title, price, discount, `status`, `inventory` FROM $tbl_product WHERE hsid = '{$value["pdid"]}' ");
                    // End

                    if($get_product_data->inventory == "true"){

                        $import_inventory = $wpdb0>query("INSERT INTO  $tbl_inventory ($tbl_inventory_fields) VALUES ('{$value["pdid"]}', '$insert_order_pubkey', `negative`, '{$value["qty"]}') ");

                        if ($import_inventory < 1) {
                            $wpdb->query("ROLLBACK");
                            return  array(
                                "status" => "failed",
                                "message" => "An error occured while submitting data to server."
                            );
                        }
                    }

                    $TOTAL_PRICE += ( $get_product_data->price - ( $get_product_data->price *  ( $get_product_data->discount == null ? 0 : $get_product_data->discount  / 100 ) ) ) * (int)$value["qty"] ;

                }

            if ($insert_order < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been added successfully."
                );
            }
        }
    }