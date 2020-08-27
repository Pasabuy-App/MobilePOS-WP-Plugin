<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	
	/** 
        * @package mobilepos-wp-plugin
        * @version 0.1.0
    */
    
  	class MP_Globals {
        public static function date_stamp(){
            return date("Y-m-d h:i:s");
		}
		
		// verify if datavice plugin is activated
		/*public static function verify_plugins(){
            if(!class_exists('DV_Verification') || !class_exists('TP_Globals') ){
                return false;
            }else{
                return true;
            }
        }*/

        public static function verify_prerequisites(){

            if(!class_exists('DV_Verification') ){
                return 'DataVice';
            }

            if(!class_exists('TP_Globals') ){
                return 'TindaPress';
             }

            return true;

        }

        public static function get_timezone($wpid){
            global $wpdb;

            $result = $wpdb->get_row("SELECT
                (SELECT tzone_name FROM dv_geo_timezone WHERE country_code = 
                (SELECT country_code FROM dv_geo_countries WHERE ID =  (SELECT child_val FROM dv_revisions WHERE child_key = 'country' AND ID = dv_address.country  ))) as time_zone
            FROM
                dv_address 
            WHERE
                wpid = $wpid");

            if (! $result  ) {
                return false;

            }else{
                return $result;

            }
        }

        public static function convert_date($wpid, $date){
            global $wpdb;
            $user_timezone = MP_Globals::get_timezone($wpid);
            date_default_timezone_set($user_timezone->time_zone);

            return date('Y-m-d H:i:s', strtotime($date));
        }
        
    }
