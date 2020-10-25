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
  	class HP_Insert_Operation_v2 {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['sdid'] = $_POST['sdid'];
            $curl_user['wpid'] = $_POST['wpid'];
            return $curl_user;
        }

        public static function listen_open(){

            // Initialize WP global variable
            global $wpdb;
            $table_schedule = MP_SCHEDULES;
            $table_attendance = MP_OPERATIONS;
            $table_attendance_field = MP_OPERATIONS_FIELD;

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

            if (!isset($_POST['stid']) || !isset($_POST['sdid'])) {
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

            $check_schedule = $wpdb->get_row("SELECT * FROM $table_schedule WHERE hsid = '{$user["sdid"]}' ");

            if(empty($check_schedule)){
                return array(
                    "status" => "failed",
                    "message" => "This shedule does not exists."
                );
            }

            if( $check_schedule->executed_by == null && $check_schedule->activated == "false"  ){
                return array(
                    "status" => "failed",
                    "message" => "This schedule is not available."
                );
            }

            // TODO: Validating Store
            // // Check if mover exists
            // $check_mover = $wpdb->get_row("SELECT * FROM $tbl_mover WHERE hsid = '{$user["mvid"]}' AND `status` = 'active' ");
            // if (empty($check_mover)) {
            //     return array(
            //         "status" => "failed",
            //         "message" => "This mover does not exists"
            //     );
            // }

            $wpdb->query("START TRANSACTION");

            $results = $wpdb->query("INSERT INTO
                $table_attendance
                    ($table_attendance_field)
                VALUES
                    ('{$user["stid"]}', '{$user["sdid"]}', '{$user["wpid"]}' )");
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