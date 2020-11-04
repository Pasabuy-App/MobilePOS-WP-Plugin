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
	class MP_Delete_Role_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
            $curl_user['role_id'] = $_POST['roid'];
            $curl_user['wpid'] = $_POST['wpid'];
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $table_role = MP_ROLES_v2;
            $table_role_field = MP_ROLES_FILED_v2;
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

            $validate = MP_Globals_v2::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }

            // Check if this role is already used
                $check_role = $wpdb->get_row("SELECT ID FROM $tbl_personnel WHERE roid = '{$user["role_id"]}' AND `status` = 'active' AND activated = 'true' ");

                if (!empty($check_role)) {
                    return array(
                        "status" => "failed",
                        "message" => "This role cannot be deleted because it currently used by an active personnel."
                    );
                }
            // End

            $get_data =  $wpdb->get_row("SELECT `hsid`, `status`, `title`, `stid`, `info` FROM  $table_role r WHERE hsid = '{$user["role_id"]}'
                AND
                    id IN ( SELECT MAX( id ) FROM $table_role WHERE r.hsid = hsid GROUP BY hsid ) ");

            if (empty($get_data)) {
                return array(
                    "status" => "failed",
                    "message" => "This role does not exists."
                );
            }

            if ($get_data->status == "inactive") {
                return array(
                    "status" => "failed",
                    "message" => "This role is currently inactive."
                );
            }

            $results = $wpdb->query("INSERT INTO
                $table_role
                    (`hsid`,$table_role_field, `status`)
                VALUES
                    ('$get_data->hsid', '$get_data->title', '$get_data->info',' $get_data->stid', '{$user["wpid"]}', 'inactive' ) ");
            $results_id = $wpdb->insert_id;
            // $hsid = MP_Globals_v2::generating_pubkey($results_id, $table_role, 'hsid', false, 64);

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