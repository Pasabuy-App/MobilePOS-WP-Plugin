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

            isset($_POST['sdid'])   && !empty($_POST['sdid'])   ? $curl_user['sdid']   =  $_POST['sdid']   :  $curl_user['sdid'] = null ;
            isset($_POST['ID'])     && !empty($_POST['ID'])     ? $curl_user['ID']     =  $_POST['ID']     :  $curl_user['ID'] = null ;
            isset($_POST['stid'])   && !empty($_POST['stid'])   ? $curl_user['stid']   =  $_POST['stid']   :  $curl_user['stid'] = null ;
            isset($_POST['status']) && !empty($_POST['status']) ? $curl_user['status'] =  $_POST['status'] :  $curl_user['status'] = null ;
            isset($_POST['type'])   && !empty($_POST['type'])   ? $curl_user['type']   =  $_POST['type']   :  $curl_user['type'] = null ;
            $curl_user['wpid'] = $_POST['wpid'];

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;

            $tbl_operation = MP_OPERATIONS_v2;
            $tbl_order = MP_ORDERS_v2;
            $tbl_order_items = MP_ORDERS_ITEMS_v2;
            $tbl_payment = MP_PAYMENTS_v2;
            $tbl_product = TP_PRODUCT_v2;
            $tbl_variants = TP_PRODUCT_VARIANTS_v2;
            $tbl_order_items_vars = MP_ORDERS_ITEMS_VARS_v2;
            $tbl_schedule = MP_SCHEDULES_v2;
            $get_amount = 0;

            // Step 1: Check if prerequisites plugin are missing
            $plugin = MP_Globals_v2::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }


			// Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            $user = self::catch_post();

            $sql = "SELECT
                    hsid as ID,
                    stid,
                    sdid,
                    (SELECT `types` FROM $tbl_schedule s WHERE hsid = op.sdid AND  id IN ( SELECT MAX( id ) FROM $tbl_schedule WHERE s.hsid = hsid  GROUP BY hsid )   )as `type`,
                    (SELECT `started` FROM $tbl_schedule s WHERE hsid = op.sdid AND  id IN ( SELECT MAX( id ) FROM $tbl_schedule WHERE s.hsid = hsid  GROUP BY hsid )   )as date_open,
                    (SELECT `ended` FROM $tbl_schedule s WHERE hsid = op.sdid AND  id IN ( SELECT MAX( id ) FROM $tbl_schedule WHERE s.hsid = hsid  GROUP BY hsid )   )as date_close,
                    `status`,
                    date_created
                FROM
                    $tbl_operation op
                WHERE
                    id IN ( SELECT MAX( id ) FROM $tbl_operation WHERE op.hsid = hsid  GROUP BY hsid ) ";

            if ($user['status'] != null) {
                $sql .= " AND  `status` = '{$user["status"]}' ";
            }

            if ($user['stid'] != null) {
                $sql .= " AND  `stid` = '{$user["stid"]}' ";
            }

            if ($user['ID'] != null) {
                $sql .= " AND  `hsid` = '{$user["ID"]}' ";
            }

            if ($user['type'] != null) {
                $sql .= " AND (SELECT `types` FROM $tbl_schedule s WHERE hsid = op.sdid AND  id IN ( SELECT MAX( id ) FROM $tbl_schedule WHERE s.hsid = hsid  GROUP BY hsid )) = '{$user["type"]}' ";
            }


            $data = $wpdb->get_results($sql);

            foreach ($data as $key => $value) {
                $order_data = $wpdb->get_row("SELECT
                    (SELECT SUM(amount) FROM $tbl_payment WHERE odid = o.pubkey) as total
                FROM
                    $tbl_order o
                WHERE
                    opid = '$value->ID'
                AND
                    id IN ( SELECT MAX( id ) FROM $tbl_order WHERE o.pubkey = pubkey  GROUP BY pubkey ) ");
                if (!empty($order_data)) {
                    $value->total_sale = $order_data->total;
                }else{
                    $value->total_sale = '0';
                }
            }

            return array(
                "status" => "success",
                "data" => $data
            );
        }
    }