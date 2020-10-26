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
            $curl_user['stid'] = $_POST['stid'];
            $curl_user['pubkey'] = $_POST['key'];
            $curl_user['wpid'] = $_POST['wpid'];
            return $curl_user;
        }

        public static function listen_open(){

            // Initialize WP global variable
            global $wpdb;
            $tbl_wallet = MP_WALLETS_v2;
            $tbl_wallet_field = MP_WALLETS_FIELD_v2;

            $user = self::catch_post();

            $check_wallet = $wpdb->get_results("SELECT * FROM $tbl_wallet WHERE pubkey = '{$user["pubkey"]}' ");
            $check_wallet;

            $data = $wpdb->query("INSERT INTO $tbl_wallet ($tbl_wallet_field) VALUES (`stid`, `pubkey`, `assigned_by`) ");
        }
    }