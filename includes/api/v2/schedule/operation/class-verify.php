<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
        * @package hatidpress-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/
  	class MP_Verify_Operation_v2 {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['wpid'] = $_POST['wpid'];
            return $curl_user;
        }

        public static function listen_open(){

			// Initialize WP global variable
            global $wpdb;
            $table_schedule = MP_SCHEDULES_v2;
            $tbl_attendance = MP_OPERATIONS_v2;
            $time = time();
            $date = date("Y:m:d");
            $day = lcfirst(date('D', $time));


             // Step 1: Check if prerequisites plugin are missing
            $plugin = HP_Globals_v2::verify_prerequisites();
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

            if(!isset($_POST['stid'])){
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknwon!"
                );
            }

            if(empty($_POST['stid'])){
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty! 'stid'."
                );
            }

            $user = self::catch_post();

            // Verify store schedule for this day
                $check_schedule = $wpdb->get_row("SELECT `executed_by`, `activated`, `hsid` FROM $table_schedule WHERE stid = '{$user["stid"]}' AND types = '$day' AND id IN ( SELECT MAX( id ) FROM $table_schedule s WHERE s.hsid = hsid GROUP BY hsid ) ");

                if(empty($check_schedule)){
                    return array(
                        "status" => "failed",
                        "message" => "This store does not have schedule for today. "
                    );
                }

                if( $check_schedule->executed_by == null && $check_schedule->activated == "false"  ){
                    return array(
                        "status" => "failed",
                        "message" => "Your schedule for this day is current pending for approval."
                    );
                }

                if( $check_schedule->executed_by != null && $check_schedule->activated == "false"  ){
                    return array(
                        "status" => "failed",
                        "message" => "Your schedule for this day is current not approved."
                    );
                }

            // End

            // Check store attance
            $check_attendance = $wpdb->get_row("SELECT ID FROM $tbl_attendance WHERE DATE(date_created) = '$date' AND  stid = '{$user["stid"]}' AND sdid = '$check_schedule->hsid' AND id IN ( SELECT MAX( id ) FROM $tbl_attendance a WHERE a.hsid = hsid GROUP BY hsid )   ");
            if (empty($check_attendance)) {
                return array(
                    "status" => "failed",
                    "message" => "This store does not have have attendance for today."
                );
            }else{
                return array(
                    "status" => "success",
                    "message" => "This store is already have an attendance for today."
                );
            }
        }// End
    }