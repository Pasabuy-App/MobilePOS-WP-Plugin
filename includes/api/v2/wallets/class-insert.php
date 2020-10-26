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
	class MP_Insert_Wallet_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['stid'] = $_POST['stid'];
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['user_id'] = $_POST['user_id'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_wallet = MP_WALLETS_v2;
            $tbl_wallet_field = MP_WALLETS_FIELD_v2;

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
                    "message" => "Please contact your administrator. Request unknown!",
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

            // CHECK IF STORE WALLET IS ALREADY EXISTS
                $check_wallet = $wpdb->get_row("SELECT hsid, `status` FROM $tbl_wallet WHERE stid = '{$user["stid"]}' ");
                if (!empty($check_wallet)) {
                    return array(
                        "status" => "failed",
                        "message" => "This store is already have an wallet."
                    );
                }
            // END

            $insert = $wpdb->query("INSERT INTO $tbl_wallet ($tbl_wallet_field, `created_by`) VALUES ( '{$user["stid"]}', '', '{$user["user_id"]}', '{$user["wpid"]}' ) ");
            $insert_id = $wpdb->insert_id;

            $generate_pubkey = MP_Globals_v2::generating_pubkey($insert_id, $tbl_wallet, 'pubkey', true, 9);

            $update_data = $wpdb->query("UPDATE $tbl_wallet SET hsid = sha2($insert_id, 256) WHERE ID = '$insert_id' ");

            if ($insert < 1 || $generate_pubkey == false || $update_data < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
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