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
	class MP_Insert_Role {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }


        public static function catch_post(){
            $curl_user = array();
        }

        public static function list_open(){

            global $wpdb;
            $tbl_role = MP_ROLES;
            $tbl_role_field = MP_ROLES_FILED;


            

            $reuslts = $wpdb->query("INSERT INTO $tbl_role ($tbl_role_field) VALUES (`title`, `info`, `created_by`) ");
            return $reuslts;
        }
    }