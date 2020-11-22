
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

	class MP_Update_Access_v2 {
        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();

            $curl_user['acid'] =  $_POST['acid'];
            $curl_user['wpid'] =  $_POST['wpid'];

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

            if (!isset($_POST['acid'])) {
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

            $get_access = $wpdb->get_row("SELECT * FROM $tbl_access WHERE hsid = '{$user["acid"]}' AND `status` = 'active'
                AND
                    id IN ( SELECT MAX( id ) FROM $tbl_access a WHERE a.hsid = hsid GROUP BY hsid ) ");

            if (empty($get_access)) {
                return array(
                    "status" => "failed",
                    "message" => "This access does not exists."
                );
            }

            isset($_POST['title']) && !empty($_POST['title']) ? $user['title'] =  $_POST['title'] :  $user['title'] = $get_access->title ;
            isset($_POST['groups']) && !empty($_POST['groups']) ? $user['groups'] =  $_POST['groups'] :  $user['groups'] = $get_access->groups ;
            isset($_POST['action']) && !empty($_POST['action']) ? $user['action'] =  $_POST['action'] :  $user['action'] = $get_access->actions ;

            $import = $wpdb->query("INSERT INTO $tbl_access (`hsid`, $tbl_access_filed, `title`, `status`, `created_by`) VALUES ( '$get_access->hsid', '{$user["groups"]}', '{$user["action"]}', '{$user["title"]}', '$get_access->status', '{$user["wpid"]}'  ) ");
            $import_id = $wpdb->insert_id;

            if ($import < 1) {
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to sever."
                );
            }else{
                return array(
                    "status" => "success",
                    "message" => "Data has been updated successfully."
                );
            }
        }
    }