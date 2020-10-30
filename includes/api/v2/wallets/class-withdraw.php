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
            
        }
    }