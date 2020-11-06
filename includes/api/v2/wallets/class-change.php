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
  	class HP_Wallet_Change_v2 {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['pubkey'] = $_POST['key'];
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['user_id'] = $_POST['user_id'];

            return $curl_user;
        }

        public static function listen_open(){

            // Initialize WP global variable
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
			// if (DV_Verification::is_verified() == false) {
            //     return array(
            //         "status" => "unknown",
            //         "message" => "Please contact your administrator. Verification issues!",
            //     );
            // }

            if(!isset($_POST['key']) || !isset($_POST['user_id'])  ){
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

            $check_wallet = $wpdb->get_row("SELECT * FROM $tbl_wallet w WHERE pubkey = '{$user["pubkey"]}'  AND  id IN ( SELECT MAX( id ) FROM $tbl_wallet WHERE w.hsid = hsid  GROUP BY pubkey )  ");

            if (empty($check_wallet)) {
                return array(
                    "status" => "failed",
                    "message" => "This wallet does not exists."
                );
            }

            if ($check_wallet->status == "inactive") {
                return array(
                    "status" => "failed",
                    "message" => "This wallet is currently inactive."
                );
            }

            if ($check_wallet->assigned_by == $user["user_id"]) {
                return array(
                    "status" => "failed",
                    "message" => "This user is already assigned to this wallet."
                );
            }

            $data = $wpdb->query("INSERT INTO
                $tbl_wallet
                    (`hsid`,$tbl_wallet_field)
                VALUES
                    ('$check_wallet->hsid','$check_wallet->stid', '{$user["pubkey"]}', '{$user["user_id"]}', '{$user["wpid"]}') ");

            if($data < 1){
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );

            }else{
                return array(
                    "status" => "success",
                    "message" => "Data has been change successfully."
                );
            }
        }
    }