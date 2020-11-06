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
	class MP_Update_Role_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['role_id'] = $_POST['role_id'];
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_role = MP_ROLES_v2;
            $tbl_role_field = MP_ROLES_FILED_v2;
            $tbl_permission = MP_PERMISSION_v2;
            $tbl_permission_field = MP_PERMISSION_FIELD_v2;
            $tbl_access = MP_ACCESS_v2;
            $access_data = array();

            $plugin = MP_Globals_v2::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

			// Step 2: Validate user
			// if (DV_Verification::is_verified() == false) {
            //     return array(
            //         "status" => "unknown",
            //         "message" => "Please contact your administrator. Verification issues!",
            //     );
            // }

            $user = self::catch_post();
            $smp = array();

            $role_data = $wpdb->get_row("SELECT * FROM $tbl_role r WHERE  hsid = '{$user["role_id"]}' AND id IN ( SELECT MAX( id ) FROM $tbl_role WHERE r.hsid = hsid GROUP BY hsid )");

            $access = $wpdb->get_results("SELECT access FROM $tbl_permission p WHERE roid = '$role_data->hsid' ");

            isset($_POST['title']) && !empty($_POST['title']) ? $user['title'] =  $_POST['title'] :  $user['title'] = $role_data->title ;
            isset($_POST['info']) && !empty($_POST['info']) ? $user['info'] =  $_POST['info'] :  $user['info'] = $role_data->info ;
            isset($_POST['data']) && !empty($_POST['data']) ? $user['access'] = $_POST['data']['access']  :  $user['access'] = $access_data['value'] = $access ;

            foreach ($access as $key => $value) {
                foreach ($user['access'] as $keys => $values) {
                    if (in_array($value->access,  $values ) ) {
                        $smp['access'] = $value->access;
                    }
                }
            }

            return $smp;
            // return;
            $wpdb->query("START TRANSACTION");

                // IMPORT ROLE DATA
                $reuslts = $wpdb->query("INSERT INTO
                    $tbl_role
                        (`hsid`,$tbl_role_field,`status`)
                    VALUES
                        ( '$role_data->hsid', '{$user["title"]}', '{$user["info"]}', '$role_data->stid','{$user["wpid"]}','$role_data->status' ) ");

                // END
                foreach ($user['access'] as $key => $value) {
                    foreach ($value as $keys => $values) {
                        // IMPORT PERMISSION
                        $permission = $wpdb->query("INSERT INTO
                            $tbl_permission
                                ($tbl_permission_field)
                            VALUES
                                ('$role_data->hsid', '$values', '{$user["wpid"]}' ) ");
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
                    "message" => "Data has been updated successfully."
                );
            }
        }
    }