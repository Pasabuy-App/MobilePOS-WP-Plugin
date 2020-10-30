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
	class MP_Update_Order_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['odid'] = $_POST['odid'];
            $curl_user['status'] = $_POST['status'];
            isset($_POST['msg']) && !empty($_POST['msg'])? $curl_user['msg'] =  $_POST['msg'] :  $curl_user['msg'] = null ;

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;

            $tbl_order = MP_ORDERS_v2;
            $tbl_order_field = MP_ORDERS_FILED_v2;

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

            $user = self::catch_post();

            $get_data = $wpdb->get_row("SELECT * FROM $tbl_order WHERE pubkey = '{$user["odid"]}' ");
            if (empty($get_data)) {
                return array(
                    "status" => "failed",
                    "message" => "This order does not exists."
                );
            }

            if ($user['status'] == $get_data->stages) {
                return array(
                    "status" => "failed",
                    "message" => "This order is already been $get_data->stages."
                );
            }

            $wpdb->query("START TRANSACTION");

            $order_data = $wpdb->query("INSERT INTO
                $tbl_order
                    (`pubkey`, `opid`, `stages`, `status`, `adid`, `instructions`,  `delivery_charges`, `psb_fee`, `order_by` )
                VALUES
                    ( '$get_data->pubkey',
                      '$get_data->opid',
                      '{$user["status"]}',
                      'active',
                      '$get_data->adid',
                      '$get_data->instructions',
                      '$get_data->delivery_charges',
                      '$get_data->psb_fee',
                      '$get_data->order_by' )");
            $order_data_id = $wpdb->insert_id;

            $order_data_hsid = MP_Globals_v2::generating_pubkey($order_data_id, $tbl_order, 'hsid', false, 64);

            if ($order_data < 1 || $order_data_hsid == false) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server!"
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