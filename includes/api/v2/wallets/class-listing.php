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
  	class HP_Listing_Wallet_v2 {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            isset($_POST['status']) && !empty($_POST['status'])? $curl_user['status'] =  $_POST['status'] :  $curl_user['status'] = null ;
            isset($_POST['wid']) && !empty($_POST['wid'])? $curl_user['wid'] =  $_POST['wid'] :  $curl_user['wid'] = null ;
            isset($_POST['user_id']) && !empty($_POST['user_id'])? $curl_user['user_id'] =  $_POST['user_id'] :  $curl_user['user_id'] = null ;

            return $curl_user;
        }

        public static function listen_open(){

            // Initialize WP global variable
            global $wpdb;

            $user = self::catch_post();

            $sql = "SELECT
                    *
                FROM
                    mp_v2_wallets w
                WHERE
                    ID = (SELECT MAX(ID) FROM mp_v2_wallets WHERE stid = w.stid )
                ";


            if($user["status"]){
                $sql .= " AND  ";
            }


            $sql .=" GROUP BY `stid` ";
            $data = $wpdb->query($sql);

            return array(
                "status" => "succcess",
                "data" => $data
            );
        }
    }