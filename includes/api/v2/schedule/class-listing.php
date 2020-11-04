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
	class MP_Listing_Schedule_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['stid'] = $_POST['stid'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_schedule = MP_SCHEDULES_v2;

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

            if (!isset($_POST['stid']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknwon!"
                );
            }

            if (empty($_POST['stid']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknwon!"
                );
            }

            $user = self::catch_post();

            $sql = "SELECT
                    *
                FROM
                    $tbl_schedule s
                WHERE
                    id IN ( SELECT MAX( id ) FROM $tbl_schedule WHERE s.hsid = hsid GROUP BY hsid )
                AND
                    types IN ('mon','tue','wed','thu','fri','sat','sun')
                AND
                    stid = '{$user["stid"]}'
                ";

            $data = $wpdb->get_results($sql);

            return array(
                "status" => "success",
                "data" => $data
            );
        }
    }