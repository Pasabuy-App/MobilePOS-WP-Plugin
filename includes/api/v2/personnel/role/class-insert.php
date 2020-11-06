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
	class MP_Insert_Role_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['title'] = $_POST['title'];
            $curl_user['access'] = $_POST['data']['access'];
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_role = MP_ROLES_v2;
            $tbl_role_field = MP_ROLES_FILED_v2;
            $tbl_permission = MP_PERMISSION_v2;
            $tbl_permission_field = MP_PERMISSION_FIELD_v2;

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

            if(!isset($_POST['title']) || !isset($_POST['title']) || !isset($_POST['data'])  || !isset($_POST['stid']) ){
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!"
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

            $wpdb->query("START TRANSACTION");

                $check_role = $wpdb->get_row("SELECT `status` FROM $tbl_role r WHERE title LIKE '%{$user["title"]}%' AND r.status = 'active'  AND id IN ( SELECT MAX( id ) FROM $tbl_role WHERE r.hsid = hsid GROUP BY hsid )  ");

                if (!empty($check_role)) {
                    return array(
                        "status" => "failed",
                        "message" => "This Role is already exists."
                    );
                }

                // IMPORT ROLE DATA
                $reuslts = $wpdb->query("INSERT INTO
                    $tbl_role
                        ($tbl_role_field)
                    VALUES
                        ('{$user["title"]}', '{$user["info"]}', '{$user["stid"]}','{$user["wpid"]}') ");
                $reuslts_id = $wpdb->insert_id;

                $reuslts_hsid = MP_Globals_v2::generating_pubkey($reuslts_id, $tbl_role, 'hsid', true, 64);
                // END
                foreach ($user['access'] as $key => $value) {
                    foreach ($value as $keys => $values) {
                        // IMPORT PERMISSION
                        $permission = $wpdb->query("INSERT INTO
                            $tbl_permission
                                ($tbl_permission_field)
                            VALUES
                                ( '$reuslts_hsid', '$values', '{$user["wpid"]}' ) ");
                        $permission_id = $wpdb->insert_id;
                        $permission_hsid = MP_Globals_v2::generating_pubkey($permission_id, $tbl_permission, 'hsid', false, 64);
                    }
                }

            if($reuslts < 1){
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