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


        public static function catch_post(){
            $curl_user = array();

            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['method'] = $_POST['method'];
            $curl_user['adid'] = $_POST['adid'];
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['items'] = $_POST['data']['items'];
            isset($_POST['msg']) && !empty($_POST['msg'])? $curl_user['msg'] =  $_POST['msg'] :  $curl_user['msg'] = null ;

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            return $_POST['data'];

            $tbl_order = MP_ORDERS;
            $tbl_order_field = MP_ORDERS_FILED;
            $tbl_order_times = MP_ORDERS_ITEMS;
            $tbl_order_times_field = MP_ORDERS_ITEMS_FIELD;
            $tbl_order_times_vars = MP_ORDERS_ITEMS_VARS;
            $tbl_order_times_vars_field = MP_ORDERS_ITEMS_VARS_FIELD;

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
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            if (!isset($_POST['wpid']) || !isset($_POST['method'])
                || !isset($_POST['adid']) || !isset($_POST['stid'])
                    || !isset($_POST['items']) ) {
                return  array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request Unknown!",
                );
            }

            $user = self::catch_post();

            $validate = MP_Globals::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }

            $wpdb->query("START TRANSACTION");

            // IMPORT ORDER DATA
            $insert_order = $wpdb->query("INSERT INTO
                $tbl_order
                    ($tbl_order_field)
                VALUES
                    ( 'awdawd', 'pending', '{$user["adid"]}', '{$user["method"]}', '{$user["msg"]}', '{$user["wpid"]}') ");
            $insert_order_id = $wpdb->insert_id;

            $insert_order_hsid = MP_Globals::generating_pubkey($insert_order_id, $tbl_order, 'pubkey', true, 5);

            // IMPORT ORDER ITEMS DATA
            foreach ($user['items'] as $key => $value) {

                $insert_order_items = $wpdb->query("INSERT INTO
                    $tbl_order_times
                        ($tbl_order_times_field)
                    VALUES
                        ('$insert_order_hsid',  '{$value["pdid"]}', '{$value["qty"]}', '{$user["wpid"]}' ) ");
                $insert_order_items_id = $wpdb->insert_id;

                $insert_order_items_hsid = MP_Globals::generating_pubkey($insert_order_items_id, $tbl_order_times, 'hsid', true, 64);

                // IMPORT ORDER ITEMS VARS DATA
                if ( isset($value['variants']) ) {
                    foreach ($value['variants'] as $key => $value) {
                        $insert_order_items_vars = $wpdb->query("INSERT INTO
                            $tbl_order_times_vars
                                ($tbl_order_times_vars_field)
                            VALUES
                                ( '$insert_order_items_hsid', '$value', '{$user["wpid"]}') ");

                        $insert_order_items_vars_id = $wpdb->insert_id;

                        $insert_order_items_vars_hsid = MP_Globals::generating_pubkey($insert_order_items_vars_id, $tbl_order_times_vars, 'hsid', false, 64);
                    }
                }
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