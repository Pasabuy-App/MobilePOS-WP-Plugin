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
	class MP_Insert_Personnel {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['roid'] = $_POST['roid'];
            $curl_user['user_id'] = $_POST['user_id'];
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['pincode'] = md5($_POST['pincode']);
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
		    $tbl_personnel = MP_PERSONNELS;
		    $tbl_personnel_field = MP_PERSONNELS_FIELD;

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

            $user = self::catch_post();

            $validate = MP_Globals::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }

            $wpdb->query("START TRANSACTION");

            $personnel = $wpdb->query("INSERT INTO
                $tbl_personnel
                    ($tbl_personnel_field)
                VALUES
                    ('{$user["stid"]}', '{$user["user_id"]}', '{$user["roid"]}', '{$user["pincode"]}','{$user["wpid"]}') ");


            if($personnel < 1){
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