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

	class MP_Listing_Access {
        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();

            isset($_POST['groups']) && !empty($_POST['groups'])? $curl_user['groups'] =  $_POST['groups'] :  $curl_user['groups'] = null ;
            // isset($_POST['actions']) && !empty($_POST['actions'])? $curl_user['actions'] =  $_POST['actions'] :  $curl_user['actions'] = null ;

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_access = MP_ACCESS;

            $plugin = MP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

			// Step 2: Validate user
		/* 	if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
            } */

            $user = self::catch_post();

            $sql_group = "SELECT groups FROM $tbl_access ";

            if ($user['groups'] != null) {
                $sql_group .= " WHERE groups = '{$user["groups"]}' ";
            }

            $groups = $wpdb->get_results($sql_group);
            $smp = array_values( array_unique(array_column($groups, 'groups')));
            $var = array();

            foreach($smp as $key => $value){
                $access = $wpdb->get_results("SELECT hsid as ID, title, actions, groups  FROM $tbl_access WHERE groups = '$value' ");
                $var[$value] = $access;
            }

            return array(
                "status" => "success",
                "data" => $var
            );
        }
    }