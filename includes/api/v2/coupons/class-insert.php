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
	class MP_Insert_Coupons_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();

            $curl_user['pdid'] = $_POST['pdid'];
            $curl_user['expiry'] = $_POST['expiry'];
            $curl_user['limit'] = $_POST['limit'];
            $curl_user['title'] = $_POST['title'];
            $curl_user['action'] = $_POST['action'];
            isset($_POST['value']) && !empty($_POST['value'])? $curl_user['extra'] =  ($_POST['value'] / 100) :  $curl_user['extra'] = null ;
            $curl_user['wpid'] = $_POST['wpid'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_coupon = MP_COUPONS_v2;
            $tbl_coupon_field = MP_COUPONS_FIELD_v2;
            $date = MP_Globals_v2::date_stamp();

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

            if(!isset($_POST['pdid']) || !isset($_POST['expiry']) || !isset($_POST['limit']) || !isset($_POST['action'])  || !isset($_POST['title']) || !isset($_POST['qty']) ){
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown"
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

            isset($_POST['info']) && !empty($_POST['info'])? $user['info'] =  $_POST['info'] :  $user['info'] = null ;

            if($_POST['action'] != "free_ship" || $_POST['action'] != "discount" || $_POST['action'] != "min_spend" || $_POST['action'] != "less"  ){
                return array(
                    "status" => "failed",
                    "message" => "Invalid value of action."
                );
            }

            // VALIDATE COUNPON
            $check_coupon = $wpdb->get_results("SELECT * FROM $tbl_coupon WHERE title LIKE '%{$user["title"]}%' AND pdid = '{$user["pdid"]}' ");
            if (!empty($check_coupon)) {
                return array(
                    "status" => "failed",
                    "message" => "This coupons is already exists."
                );
            }
            // END

            if ( $user['expiry'] < $date ) {
                return array(
                    "status" => "failed",
                    "message" => "Please select correct value of expiry."
                );
            }

            $wpdb->query("START TRANSACTION");

            $resulst = $wpdb->query("INSERT INTO
                $tbl_coupon
                    ($tbl_coupon_field)
                VALUES
                    ('{$user["pdid"]}', '{$user["title"]}', '{$user["info"]}', '{$user["action"]}', '{$user["extra"]}', '{$user["limit"]}', '{$user["expiry"]}', '{$user["wpid"]}' )");

            if ($resulst < 1) {
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