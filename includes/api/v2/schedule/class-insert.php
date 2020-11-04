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
	class MP_Insert_Schedule_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['stid'] = $_POST['stid'];
            $curl_user['type'] = $_POST['type'];
            $curl_user['started'] = $_POST['started'];
            $curl_user['ended'] = $_POST['ended'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_schedule = MP_SCHEDULES_v2;
            $tbl_schedule_fields = MP_SCHEDULES_FIELD_v2;

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

            if (!isset($_POST['type']) || !isset($_POST['stid']) || !isset($_POST['started']) || !isset($_POST['ended'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknwon!"
                );
            }

            if ($_POST['type'] !== "mon"
                && $_POST['type'] !== "tue"
                && $_POST['type'] !== "wed"
                && $_POST['type'] !== "thu"
                && $_POST['type'] !== "fri"
                && $_POST['type'] !== "sat"
                && $_POST['type'] !== "sun") {
                return array(
                    "status" => "failed",
                    "message" => "Invalid value of type",
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

            $wpdb->query("START TRANSACTION");

            // Check document of user
            $check_schedule = $wpdb->get_row("SELECT * FROM $tbl_schedule WHERE types = '{$user["type"]}' AND stid = '{$user["stid"]}'    ");
            $count_schedule = $wpdb->get_row("SELECT count(ID)as sched FROM $tbl_schedule WHERE stid = '{$user["stid"]}'    ");

            if (!empty($check_schedule)) {
                if( $check_schedule->executed_by != null && $check_schedule->activated == "true"  ){
                    return array(
                        "status" => "failed",
                        "message" => "This schedule is already exists."
                    );
                }

                if( $check_schedule->executed_by == null && $check_schedule->activated == "false"  ){
                    return array(
                        "status" => "failed",
                        "message" => "This schedule is already exists. Pending for approve."
                    );
                }
            }

            if ($count_schedule->sched >= 7 ) {
                return array(
                    "status" => "failed",
                    "message" => "You already have an schedule for a week."
                );
            }

            $result = $wpdb->query("INSERT INTO
                $tbl_schedule
                    ($tbl_schedule_fields)
                VALUES
                    ('{$user["stid"]}', '{$user["type"]}', '{$user["started"]}',  '{$user["ended"]}')");
            $result_id = $wpdb->insert_id;
            $hsid = MP_Globals_v2::generating_pubkey($result_id, $tbl_schedule, 'hsid', false, 64);

            if ($result < 1 ) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server!"
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