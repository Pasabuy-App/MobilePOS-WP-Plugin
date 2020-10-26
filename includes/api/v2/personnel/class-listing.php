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
	class MP_Listing_Personnel_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            isset($_POST['stid']) && !empty($_POST['stid'])? $curl_user['store_id'] =  $_POST['stid'] :  $curl_user['store_id'] = null ;
            isset($_POST['status']) && !empty($_POST['status'])? $curl_user['status'] =  $_POST['status'] :  $curl_user['status'] = null ;
            isset($_POST['user_id']) && !empty($_POST['user_id'])? $curl_user['user_id'] =  $_POST['user_id'] :  $curl_user['user_id'] = null ;
            isset($_POST['plid']) && !empty($_POST['plid'])? $curl_user['plid'] =  $_POST['plid'] :  $curl_user['plid'] = null ;
            isset($_POST['search']) && !empty($_POST['search'])? $curl_user['search'] =  $_POST['search'] :  $curl_user['search'] = null ;

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_personnel = MP_PERSONNELS_v2;

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
                wpid,
                (SELECT display_name FROM wp_users WHERE ID = wpid ) as display_name,
                null as avatar,
                null as dname,
                `status`,
                date_created
                FROM
                    $tbl_personnel
                WHERE
                    id IN ( SELECT MAX( id ) FROM $tbl_personnel GROUP BY wpid )
            ";


            if ($user['status'] != null) {
                if ($user['status'] != "active" && $user['status'] != "inactive") {
                    return array(
                        "status" => "failed",
                        "message" => "Invalid value of status."
                    );
                }
                $sql .= " AND `status` = '{$user["status"]}' ";
            }

            if ($user['user_id'] != null) {
                $sql .= " AND wpid = '{$user["user_id"]}' ";
            }

            if ($user['store_id'] != null) {
                $sql .= " AND stid = '{$user["store_id"]}' ";
            }

            if ($user['plid'] != null) {
                $sql .= " AND hsid = '{$user["plid"]}' ";
            }

            if ($user['search'] != null) {
                $sql .= " HAVING  display_name LIKE '%{$user["search"]}%' ";
            }

            $get_data = $wpdb->get_results($sql);

            foreach ($get_data as $key => $value) {
                $wp_user = get_user_by("ID", $value->wpid);

                $value->avatar = $wp_user->avatar != null? $wp_user->avatar: $wp_user->avatar= TP_PLUGIN_URL. "assets/images/default-avatar.png" ;
            }

            return array(
                "status" => "success",
                "data" => $get_data
            );
        }
    }