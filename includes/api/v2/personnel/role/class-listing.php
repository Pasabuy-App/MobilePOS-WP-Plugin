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
	class MP_Listing_Role_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            isset($_POST['roid']) && !empty($_POST['roid'])? $curl_user['role_id'] =  $_POST['roid'] :  $curl_user['role_id'] = null ;
            isset($_POST['stid']) && !empty($_POST['stid'])? $curl_user['store_id'] =  $_POST['stid'] :  $curl_user['store_id'] = null ;
            isset($_POST['status']) && !empty($_POST['status'])? $curl_user['status'] =  $_POST['status'] :  $curl_user['status'] = null ;
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;

            $tbl_role = MP_ROLES_v2;
            $tbl_access = MP_ACCESS_v2;
            $tbl_permission = MP_PERMISSION_v2;

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

            $sql = "SELECT
                hsid as ID,
                stid,
                title,
                info,
                `status`,
                created_by,
                date_created
            FROM $tbl_role  ";

            if ($user['role_id'] != null) {
                $sql .= " WHERE hsid = '{$user["role_id"]}' ";
            }

            if ($user['status'] != null) {
                if ($user['role_id'] != null) {
                    if ($user['status'] != 'active' && $user['status'] != 'inactive'  ) {
                        return array(
                            "status" => "failed",
                            "message" => "Invalid value of status."
                        );
                    }
                    $sql .= " AND `status` = '{$user["status"]}' ";

                }else{
                    $sql .= " WHERE hsid = '{$user["role_id"]}' ";
                }
            }


            if ($user['store_id'] != null) {
                if ($user['role_id'] != null || $user['status'] != null) {
                    $sql .= " AND `stid` = '{$user["store_id"]}' ";

                }else{
                    $sql .= " WHERE `stid` = '{$user["store_id"]}' ";
                }
            }
            $sql ." GROUP BY title DESC ";

            $results =  $wpdb->get_results($sql);

            return array(
                "status" => "success",
                "data" => $results
            );
        }
    }