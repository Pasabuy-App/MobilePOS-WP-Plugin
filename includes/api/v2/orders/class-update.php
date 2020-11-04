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

            $curl_user['stages'] = $_POST['stages'];
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
            $status = '';
            $get_data = $wpdb->get_row("SELECT * FROM $tbl_order m WHERE pubkey = '{$user["odid"]}'
                AND
                    id IN ( SELECT MAX( id ) FROM $tbl_order WHERE m.pubkey = pubkey  GROUP BY pubkey ) ");

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

            }else{

                if($user['stages'] != "accepted" && $user['stages'] != "cancelled"
                   && $user['stages'] != "preparing" && $user['stages'] != "shipping"){
                    return array(
                        "status" => "failed",
                        "message" => "Invalid value of stages."
                    );
                }

                switch ($user['stages']) {

                    case 'accepted':
                    case 'cancelled':

                        if ($get_data->stages != "pending" ) {
                            return array( "status" => "failed", "message" => "This order is already been $get_data->stages.");
                        }else{
                            $status = $user["stages"];
                        }
                        break;

                    case 'preparing':

                        if ($get_data->stages != "ongoing" ) {
                            return array( "status" => "failed", "message" => "This order is already been $get_data->stages.");
                        }else{
                            $status = $user["stages"];
                        }
                        break;

                    case 'shipping':

                        if ($get_data->stages != "preparing" ) {
                            return array( "status" => "failed", "message" => "This order is already been $get_data->stages.");
                        }else{
                            $status = $user["stages"];
                        }
                        break;

                    case 'completed':

                        if ($get_data->stages != "shipping" ) {
                            return array( "status" => "failed", "message" => "This order is already been $get_data->stages.");
                        }else{
                            $status = $user["stages"];
                        }
                        break;
                }

            }

            $wpdb->query("START TRANSACTION");

            $order_data = $wpdb->query("INSERT INTO
                $tbl_order
                    (`pubkey`, `opid`, `stages`, `status`, `adid`, `instructions`,  `delivery_charges`, `psb_fee`, `order_by` )
                VALUES
                    ( '$get_data->pubkey',
                      '$get_data->opid',
                      '$status',
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