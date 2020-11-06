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
  	class MP_Insert_Operation_v2 {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['status'] = $_POST['status'];
            return $curl_user;
        }

        public static function listen_open(){

            // Initialize WP global variable
            global $wpdb;
            $tbl_store_v2 = TP_STORES_v2;
            $table_schedule = MP_SCHEDULES_v2;
            $table_attendance = MP_OPERATIONS_v2;
            $table_attendance_field = MP_OPERATIONS_FIELD_v2;
            $time = time();
            $date = date("Y:m:d");
            $day = lcfirst(date('D', $time));

            // Step 1: Check if prerequisites plugin are missing
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

            if (!isset($_POST['stid'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknwon!"
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

            $check_store = $wpdb->get_row("SELECT ID FROM $tbl_store_v2 WHERE hsid = '{$user["stid"]}' AND `status` = 'active' ");
            if (empty($check_store)) {
                return array(
                    "status" => "failed",
                    "message" => "This store does not exists."
                );
            }

            $check_schedule = $wpdb->get_row("SELECT * FROM $table_schedule WHERE stid = '{$user["stid"]}' AND types = '$day' ");

            if(empty($check_schedule)){
                return array(
                    "status" => "failed",
                    "message" => "This store does not have schedule today. "
                );
            }

            if( $check_schedule->executed_by == null && $check_schedule->activated == "false"  ){
                return array(
                    "status" => "failed",
                    "message" => "Your schedule for this day is current not approved."
                );
            }

            // Check if operation is already exists
                $check_operation = $wpdb->get_row("SELECT ID,`type` FROM  $table_attendance WHERE sdid = '$check_schedule->hsid' AND DATE(date_created) = '$date' AND `type` = '{$user["status"]}' ");
                if (!empty($check_operation)) {
                    return array(
                        "status" => "failed",
                        "message" => "You've already been ".ucfirst($user["status"])."."
                    );
                }
            // End

            $wpdb->query("START TRANSACTION");

            $results = $wpdb->query("INSERT INTO
                $table_attendance
                    ($table_attendance_field, `type`)
                VALUES
                    ('{$user["stid"]}', '$check_schedule->hsid', '{$user["wpid"]}', '{$user["status"]}' )");
            $results_id = $wpdb->insert_id;

            $wpdb->query("UPDATE $table_attendance SET hsid = sha2($results_id, 256) WHERE ID = $results_id");

            if ($results < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            } else {
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been added succesfully."
                );
            }
        }
    }