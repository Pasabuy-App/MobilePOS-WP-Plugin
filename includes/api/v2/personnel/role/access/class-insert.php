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

	class MP_Insert_Access_v2 {
        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();

            $curl_user['groups'] =  $_POST['groups'];
            $curl_user['action'] =  $_POST['action'];
            $curl_user['title'] =  $_POST['title'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_access = MP_ACCESS_v2;
            $tbl_access_filed = MP_ACCESS_FIELD_v2;

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

            if (!isset($_POST['groups']) || !isset($_POST['action']) || !isset($_POST['title'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
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

            if ($user['groups'] != 'product'
                && $user['groups'] != 'store'
                && $user['groups'] != 'category'
                && $user['groups'] != 'wallet'
                && $user['groups'] != 'variant'
                && $user['groups'] != 'document'
                && $user['groups'] != 'coupon'
                && $user['groups'] != 'order'
                && $user['groups'] != 'report'
                && $user['groups'] != 'dashboard'
                && $user['groups'] != 'operation'
                && $user['groups'] != 'personnel'
                && $user['groups'] != 'role' ) {

                return array(
                    "status" => "failed",
                    "message" => "Invalid value of groups"
                );
            }

            $import = $wpdb->query("INSERT INTO $tbl_access ($tbl_access_filed, title) VALUES ('{$user["groups"]}', '{$user["action"]}', '{$user["title"]}') ");
            $import_id = $wpdb->insert_id;

            $wpdb->query("UPDATE $tbl_access SET hsid = sha2($import_id, 256) WHERE ID = $import_id ");

            if ($import < 1) {
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to sever."
                );
            }else{
                return array(
                    "status" => "success",
                    "message" => "Data has been added successfully."
                );
            }
        }
    }