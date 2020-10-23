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
	class MP_Insert_Personnel {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['title'] = $_POST['title'];
            $curl_user['info'] = $_POST['info'];
            $curl_user['access'] = $_POST['data']['access'];
            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;


            $user = self::catch_post();

            $validate = MP_Globals::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }
     

            $personnel = $wpdb->query("INSERT INTO mp_personnel () VALUES () ");


        }
    }