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
	class MP_Insert_Order_v2 {

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

            $wpdb->query("START TRANSACTION");

            // IMPORT ORDER DATA
                $insert_order = $wpdb->query("INSERT INTO
                    $tbl_order
                        (`delivery_charges`,$tbl_order_field)
                    VALUES
                        ( '{$user["dlfee"]}','{$user["opid"]}', 'pending', '{$user["adid"]}', '{$user["msg"]}', '{$user["wpid"]}') ");
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

                // Counpons
                foreach ($user['payments'] as $key => $value) {
                    // Get coupon value
                    // NOTE: Temporary commented discount validation
                    // if ($discount == false) {
                        if ($value['method'] == "coupon") {
                            $counpon = $wpdb->get_row("SELECT * FROM $tbl_coupons WHERE hsid = '{$value["value"]}'");
                            if (empty($counpon)) {
                                $wpdb->query("ROLLBACK");
                                return  array(
                                    "status" => "failed",
                                    "message" => "This coupon does not exits!",
                                );
                            }

                            switch ($counpon->action) {

                                case 'free_ship':
                                    $delivery_fee = 0;
                                    $counpon_val = 0;
                                    break;

                                case 'percentage':

                                    // Compute total price
                                    $total = $TOTAL_PRICE - (($TOTAL_PRICE * (double)$counpon->extra) + ($TOTAL_PRICE * $discount)) ;
                                    break;

                                case 'fix_amount':

                                    $counpon_val = (double)$counpon->extra;

                                    break;
                            }
                        }else{
                            $total = $TOTAL_PRICE + $delivery_fee;
                        }
                    // }
                }

                // END
                // NOTE: Temporary comment for discount
                // if ($discount != false) {
                //     $total = $TOTAL_PRICE - ($TOTAL_PRICE * $discount);
                // }else{
                //     $total = $TOTAL_PRICE - ($TOTAL_PRICE * $counpon_val);
                // }

                foreach ($user['payments'] as $key => $value) {

                    if ($value['method'] == "cash") {
                        $payment = self::save_payment($insert_order_pubkey, 'cash', $total, '0');
                        if ($payment == false) {
                            return  array(
                                "status" => "failed",
                                "message" => "An error occured while submitting data to server!",
                            );
                        }
                    }

                    if ($value['method'] == "card") {
                        $payment = self::save_payment($insert_order_pubkey, 'card', $total, '0');
                        if ($payment == false) {
                            return  array(
                                "status" => "failed",
                                "message" => "An error occured while submitting data to server!",
                            );
                        }
                    }

                    // Pasabuy wallet
                    if ($value['method'] == "savings") {

                        $saving = self::savings($_POST['wpid'], 'savings', $total, $user['stid']);
                        if($saving['status'] == false){
                            return array(
                                "status" => "failed",
                                "message" => $saving['message']
                            );
                        }

                        $payment = self::save_payment($insert_order_pubkey, 'savings', $total, '0');
                        if ($payment == false) {
                            return  array(
                                "status" => "failed",
                                "message" => "An error occured while submitting data to server!",
                            );
                        }
                    }
                    // End Pasabuy wallet
                }
            /**
             * END Process payment
            */
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

        public static function savings($wpid, $curency, $amount, $stid){

            global $wpdb;
            $tbl_wallet = MP_WALLETS_v2;
            $master_key = DV_Library_Config::dv_get_config('master_key', 123);

            $wpdb->query("START TRANSACTION");

            // Check if currency exists
            $get_currency = $wpdb->get_row("SELECT * FROM cp_currencies WHERE title like '%{$curency}%' ");

            if(empty($get_currency)){
                return array(
                    "status" => false,
                    "message" => "This currency does not exists."
                );
            }
            // END

            // Get wallet data
            $wallet = $wpdb->get_row($wpdb->prepare("SELECT public_key, currency FROM cp_wallets WHERE wpid = %d AND currency = '%s' ", $wpid, $get_currency->ID ));

            if (empty($wallet)) {
                return array(
                    "status" => false,
                    "message" => "This user does not have wallet."
                );
            }
            // END

            // Check balance
            $balance = $wpdb->get_row(
                $wpdb->prepare(" SELECT
                    COALESCE(
                        SUM(COALESCE( CASE WHEN recipient = '%s' THEN amount END , 0 ))  -
                        SUM(COALESCE( CASE WHEN sender = '%s' THEN amount END, 0 ))
                        , 0 ) as balance
                        FROM cp_transaction", $wallet->public_key, $wallet->public_key));

            if ($balance->balance < $amount) {
                return array(
                    "status" => false,
                    "message" => "You dont have balance in your wallet."
                );
            }
            // END

            // Check if Store has wallet
            $store_wallet = $wpdb->get_row("SELECT * FROM $tbl_wallet WHERE stid = '$stid' ");
            if (empty($store_wallet)) {
                return array(
                    "status" => false,
                    "message" => "This store does not have wallet."
                );
            }
            // End

            // Step 13: Executing of transaction
            $send_money = $wpdb->query("INSERT INTO cp_transaction ( `sender`, `recipient`, `amount`, `currency` ) VALUES ( '$wallet->public_key', '$store_wallet->pubkey', '$amount', '$get_currency->hash_id' )  ");
            $get_money_id = $wpdb->insert_id;

            $get_money_data = $wpdb->get_row("SELECT * FROM cp_transaction WHERE ID = $get_money_id");

            // Step 14: Hash transaction data for curhash
            $hash = hash( 'sha256', $get_money_data->sender.$get_money_data->recipient.$get_money_data->amount.$get_money_data->date_created);

            $hash_prevhash = hash( 'sha256', $master_key. $get_money_data->date_created );

            $update_transaction = $wpdb->query("UPDATE cp_transaction SET `curhash` = '$hash', `prevhash` = '$hash_prevhash', `hash_id` = SHA2( '$get_money_id' , 256) WHERE ID = $get_money_id ");

            // Step 15: Check if any queries above failed
            if ( $send_money < 1 || $get_money_id < 1 || empty($get_money_data) || $update_transaction < 1 ) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => false,
                    "message" => "An error occured while submitting data to server.",
                );
            }else{
            // Step 16 : Commit if no errors found
                $wpdb->query("COMMIT");
                return array(
                    "status" => true,
                    "message" => "Data has been added successfully.",
                );
            }
        }

        public static function save_payment($odid, $method, $amount, $extra){
            global $wpdb;
            $wpdb->query("START TRANSACTION");
            $tbl_payment = MP_PAYMENTS_v2;
            $tbl_payment_filed = MP_PAYMENTS_FIELD_v2;

            $data = $wpdb->query("INSERT INTO $tbl_payment ($tbl_payment_filed) VALUES ('$odid', '$method', $extra, $amount ) ");
            $data_id = $wpdb->insert_id;
            $data_hsid = MP_Globals_v2::generating_pubkey($data_id, $tbl_payment, 'hsid', false, 64);

            if ($data < 1) {
                $wpdb->query("ROLLBACK");
                return false;
            }else{
                $wpdb->query("COMMIT");
                return true;
            }
        }
    }