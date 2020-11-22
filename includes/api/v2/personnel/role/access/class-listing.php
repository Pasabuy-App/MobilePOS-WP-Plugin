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

	class MP_Listing_Access_v2 {
        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();

            isset($_POST['groups']) && !empty($_POST['groups'])? $curl_user['groups'] =  $_POST['groups'] :  $curl_user['groups'] = null ;
            isset($_POST['actions']) && !empty($_POST['actions'])? $curl_user['actions'] =  $_POST['actions'] :  $curl_user['actions'] = null ;

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_access = MP_ACCESS_v2;

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

            $sql_group = "SELECT groups FROM $tbl_access  WHERE  id IN ( SELECT MAX( id ) FROM $tbl_access a WHERE a.hsid = hsid GROUP BY hsid )";

            if ($user['groups'] != null) {
                $sql_group .= " AND groups = '{$user["groups"]}' ";
            }

            $groups = $wpdb->get_results($sql_group);
            $smp = array_values( array_unique(array_column($groups, 'groups')));
            $var = array();

            $count = 0;
            foreach($smp as $key => $value){
                $access = $wpdb->get_results("SELECT hsid as ID, title, actions, groups  FROM $tbl_access WHERE groups = '$value' AND  id IN ( SELECT MAX( id ) FROM $tbl_access a WHERE a.hsid = hsid GROUP BY hsid ) ");
                $var[$count]['name'] = ucfirst($value);
                $var[$count]['access'] = $access;
                $count ++;
            }

            return array(
                "status" => "success",
                "data" => $var
            );
        }
    }