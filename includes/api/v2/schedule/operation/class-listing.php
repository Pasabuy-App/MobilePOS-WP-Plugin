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
	class MP_Operation_Listing_v2 {

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

            

        }
    }