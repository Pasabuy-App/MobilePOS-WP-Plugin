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

        public static function custom_update($parent_id, $wpid, $rev_type, $parent_table, $revisions_table, $data, $where){

            global $wpdb;

            $date = DV_Globals:: date_stamp();

            if ( ! is_array( $data ) || ! is_array( $where ) ) {
                return false;
            }

            //Initialize empty array
            $fields     = array();
            $insert_fields = array();
            $insert_values = array();
            $conditions = array();
            $values     = array();

            //Remove null data
            foreach ( $data as $field => $value ) {
                if ( is_null( $value ) ) {
                    unset($data[$field]);
                    continue;
                }
            }

            $wpdb->query("START TRANSACTION");

            //Insert into revisions table
            foreach ($data as $key => $value) {
                $insert_result = $wpdb->query("INSERT INTO $revisions_table (`revs_type`, `parent_id`, `child_key`, `child_val`, `created_by`, `date_created`) VALUES ('$rev_type', '$parent_id', '$key', '$value', '$wpid', '$date')");
                if ($insert_result < 1) {
                    $wpdb->query("ROLLBACK");
                    return false;
                }
                $insert_values[$key] = $wpdb->insert_id;
            }

            //Get all `where` conditions
            foreach ( $where as $field => $value ) {
                if ( is_null( $value ) ) {
                    $conditions[] = "`$field` IS NULL";
                    continue;
                }

                $conditions[] = "`$field` = " . $value;
            }

            //Make fields a comma seperated values
            $conditions = implode( ' AND ', $conditions );

            foreach ($insert_values as $key => $value) {
                $result = $wpdb->query("UPDATE $parent_table SET $key = $value WHERE ID = $parent_id");
                if ($result < 1) {
                    $wpdb->query("ROLLBACK");
                    return false;
                }
            }

            $wpdb->query("COMMIT");
            return true;

        }

        public static function date_stamp(){
            return date("Y-m-d h:i:s");
		}

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

        public static function get_user_date($wpid){
            global $wpdb;
            $user_timezone = MP_Globals::get_timezone($wpid);
            date_default_timezone_set($user_timezone->time_zone);
            return date("Y-m-d H:i:s");

        }

        public static function convert_date($wpid, $date){
            global $wpdb;
            $user_timezone = MP_Globals::get_timezone($wpid);
            date_default_timezone_set($user_timezone->time_zone);

            return date('Y-m-d H:i:s', strtotime($date));
        }

        public static function update_hash_id_hash($primary_key, $table_name, $column_name){
			global $wpdb;

			$results = $wpdb->query("UPDATE  $table_name SET $column_name = concat(
                    substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand($primary_key)*4294967296))*36+1, 1),
                    substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                    substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                    substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                    substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed)*36+1, 1)
                )
                WHERE ID = $primary_key;");

			if ($results == false ) {
				return false;
			}else{
				if ($results == 1) {
					return true;
				}
			}
        }


        public static function call_usn_notify(){
            $http = file_get_contents("http://usn.pasabuy.app:5050/notify");

            switch ($http) {
                case "success":
                    return array(
                        "status" => "success",
                        "message" => "Data has ben sent."
                    );
                    break;
                case "failed":
                    return array(
                        "status" => "505",
                        "message" => "Please contact your administrator. Error 505!"
                    );
                    break;
                case "unknown":
                    return array(
                        "status" => "404",
                        "message" => "Please contact your administrator. Not found"
                    );
                    break;
            }
        }

        public static function call_usn_message($wpid, $message, $event){

            $http = file_get_contents("http://usn.pasabuy.app:5050/notify?wpid={$wpid}&event={$event}&msg={$message}");

            switch ($http) {
                case "success":
                    return array(
                        "status" => "success",
                        "message" => "Data has ben sent."
                    );
                    break;
                case "failed":
                    return array(
                        "status" => "505",
                        "message" => "Please contact your administrator. Error 505!"
                    );
                    break;
                case "unknown":
                    return array(
                        "status" => "404",
                        "message" => "Please contact your administrator. Not found"
                    );
                    break;
            }
        }
    }
