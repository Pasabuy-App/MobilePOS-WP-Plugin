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
	class MP_Listing_Access {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;


            return $reuslts;
        }
    }