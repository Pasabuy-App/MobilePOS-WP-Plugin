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
	class MP_Listing_Order_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['dlfee'] = $_POST['dlfee'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            $tbl_orders_v2 = MP_ORDERS_v2;


            return $wpdb->get_results("SELECT * FROM $tbl_orders_v2");
        }
    }