<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) )
	{
		exit;
	}

	/**
        * @package tindapress-wp-plugin
        * @version 0.1.0
	*/
    class MP_Order_Listing {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }
        public static function list_open(){

            global $wpdb;
            $table_store = TP_STORES_TABLE;
            $table_prod = TP_PRODUCT_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
            $table_ord = MP_ORDERS_TABLE;
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            $table_mprevs = MP_REVISIONS_TABLE;

            //Step 1: Check if prerequisites plugin are missing
            $plugin = MP_Globals::verify_prerequisites();
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

            // // Step 3: Check if parameter is passed
            // if ( !isset($_POST['stid']) ) {
            //     return array(
			// 		"status" => "unknown",
			// 		"message" => "Please contact your administrator. Request unknown!",
            //     );
            // }

            // // Step 4: Check if parameter is empty
            // if ( empty($_POST['stid']) ) {
            //     return array(
            //         "status" => "failed",
            //         "message" => "Required fileds cannot be empty.",
            //     );
            // }

                // Step 5: Check post stage if valid
            if ( isset($_POST['stage']) ){
                if ( empty($_POST['stage']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                if ( !($_POST['stage'] === 'pending')
                    && !($_POST['stage'] === 'received')
                    && !($_POST['stage'] === 'accepted')
                    && !($_POST['stage'] === 'completed')
                    && !($_POST['stage'] === 'shipping')
                    && !($_POST['stage'] === 'cancelled')) {
                    return array(
                            "status" => "failed",
                            "message" => "Invalid stage.",
                    );
                }
                $stage = $_POST['stage'];
            }

            $colname = "";
            $uid = "0";

            if (isset($_POST['stid'])){
                if ( empty($_POST['stid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $uid = $_POST['stid'];
                $colname = "stid";
            }
            else{
                $uid = $_POST['wpid'];
                $colname = "wpid";
            }


            // Step 4: Start mysql transaction
            $sql = "SELECT
                moi.ID,
                mo.stid, (SELECT display_name FROM wp_users WHERE ID = mo.wpid) AS customer, mo.ID AS odid,
                (SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) AS qty,
                (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS price,
                (SELECT child_val FROM tp_revisions WHERE ID = (SELECT title FROM tp_products  WHERE ID = moi.pdid)) AS product_name,";

            if ( isset($_POST['odid']) ){
                if ( empty($_POST['odid']) ){
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $odid = $_POST['odid'];
                $sql .= " (SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) *
                    (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS totalprice,  ";
            }
            if ($colname == "stid"){
                $sql .= "SUM((SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) *
                    (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid ))) AS totalprice,  ";
            }
            if ($colname == "wpid"){
                $sql .= " (SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) *
                (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS totalprice,
                (SELECT child_val FROM tp_revisions WHERE ID =(SELECT title FROM tp_stores WHERE ID = mo.stid)) AS store_name,
                (SELECT child_val FROM tp_revisions WHERE ID =(SELECT logo FROM tp_stores WHERE ID = mo.stid)) AS store_logo, ";
            }

            $sql .= " (SELECT child_val FROM mp_revisions WHERE ID = mo.`status`) AS stage,
                    (SELECT child_val FROM mp_revisions WHERE ID = mo.`method`) AS method,
                mo.date_created
            FROM
                mp_order_items AS moi
                INNER JOIN mp_orders AS mo ON mo.ID = moi.odid
                WHERE mo.$colname = '$uid' ";

            if (isset($_POST['stage']) ){
                $sql .= " AND (SELECT child_val FROM mp_revisions WHERE ID = mo.`status`) = '$stage' ";
            }

            if ( isset($_POST['odid']) ){
                $sql .= " AND moi.odid = '$odid' ORDER BY moi.ID DESC ";
            }
            else{
                $sql .= " GROUP BY moi.odid DESC ";
            }
            //return $sql;
            $result = $wpdb->get_results($sql);

            // Step 11: Check if no rows found
            if (!$result) {
                // return array(
                //     "status" => "failed",
                //     "message" => "No order found.",
                // );
            }

            // Step 12: Return result
            return array(
                "status" => "success",
                "data" => $result
            );
        }



        //Function for checking if date is valid and invalid format(2020-01-01).
        //Invalid dates such us 2020-02-31 will return false
        public static function validateDate($date, $format = 'Y-m-d H:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }