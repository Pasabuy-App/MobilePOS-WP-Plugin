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
	class MP_Delete_Personnel_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
            $curl_user['personel_id'] = $_POST['pid'];
            $curl_user['wpid'] = $_POST['wpid'];
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_personnel = MP_PERSONNELS_v2;
            $tbl_personnel_filed = MP_PERSONNELS_FIELD_v2;

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

            $validate = MP_Globals_v2::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }
            // AND `status` = 'active' AND activated = 'true'
            $get_data =  $wpdb->get_row("SELECT
            hsid as ID,
            stid,
            wpid,
            `status`,
            date_created
            FROM
                $tbl_personnel
            WHERE hsid = '{$user["personel_id"]}'
            GROUP BY `wpid`
            DESC");

            if (empty($get_data)) {
                return array(
                    "status" => "failed",
                    "message" => "This personnel does not exists."
                );
            }

            if ($get_data->status == "inactive") {
                return array(
                    "status" => "failed",
                    "message" => "This personnel is currently inactive."
                );
            }

            $results = $wpdb->query("INSERT INTO
                $tbl_personnel
                    ($tbl_personnel_filed, `status`)
                VALUES
                    #`stid`, `wpid`, `roid`, `pincode`, `assigned_by`
                    ('$get_data->stid', '$get_data->wpid', '$get_data->roid', '$get_data->pincode', '$get_data->assigned_by', 'inactive' ) ");

            $results_id = $wpdb->insert_id;
            $hsid = MP_Globals_v2::generating_pubkey($results_id, $tbl_personnel, 'hsid', false, 64);

            if ($results < 1) {
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
                return array(
                    "status" => "success",
                    "message" => "Data has been deleted successfully."
                );
            }
        }
    }