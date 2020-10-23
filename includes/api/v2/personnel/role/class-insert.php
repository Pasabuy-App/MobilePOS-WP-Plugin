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
	class MP_Insert_Role {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['title'] = $_POST['title'];
            $curl_user['info'] = $_POST['info'];
            $curl_user['access'] = $_POST['data']['access'];
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_role = MP_ROLES;
            $tbl_role_field = MP_ROLES_FILED;
            $tbl_permission = MP_PERMISSION;
            $tbl_permission_field = MP_PERMISSION_FIELD;

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

                // IMPORT ROLE DATA
                $reuslts = $wpdb->query("INSERT INTO
                    $tbl_role
                        ($tbl_role_field)
                    VALUES
                        ('{$user["title"]}', '{$user["info"]}', '{$user["wpid"]}') ");
                $reuslts_id = $wpdb->insert_id;

                $reuslts_hsid = MP_Globals::generating_pubkey($reuslts_id, $tbl_role, 'hsid', true, 64);
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
                        $permission_hsid = MP_Globals::generating_pubkey($permission_id, $tbl_permission, 'hsid', false, 64);
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