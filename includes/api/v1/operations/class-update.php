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
    class MP_Update_Operations {

        public static function listen(){
            return rest_ensure_response(
                self:: listen_open()
            );
        }

        /*  Catch user post request */
        public static function catch_post(){
            $curl_user = array();

            $curl_user['store_id'] = $_POST['stid'];
            $curl_user['user_id'] = $_POST['wpid'];
            $curl_user['type'] = $_POST['type'];

            return $curl_user;
        }

        /*  Methods */
            public static function listen_open(){
                global $wpdb;
                $table_operations = MP_OPERATIONS_TABLE;
                $table_rev = MP_REVISIONS_TABLE;
                $date = MP_Globals::date_stamp();

                $plugin = TP_Globals::verify_prerequisites();
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
                        "message" => "Please contact your administrator. Verification Issues!",
                    );

                }

                if (!isset($_POST['type']) || !isset($_POST['stid']) ) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!"
                    );
                }

                if (empty($_POST['type']) || empty($_POST['stid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty."
                    );
                }

                if ($_POST['type'] !== 'open' && $_POST['type'] !== 'close') {
                    return array(
                        "status" => "failed",
                        "message" => "Invalid value of type."
                    );
                }

                $user = self::catch_post();

                //  Get store and status

                    $store_id = $user['store_id'];

                    $get_store = $wpdb->get_row("SELECT
                            tp_str.ID,
                            ( SELECT tp_rev.child_val FROM tp_revisions tp_rev WHERE ID = tp_str.status ) AS `status`
                        FROM
                            tp_stores tp_str
                        INNER JOIN
                            tp_revisions tp_rev ON tp_rev.ID = tp_str.`status`
                        WHERE tp_str.ID = $store_id
                    ");

                    // Check if no rows found
                    if ( !$get_store ) {
                        return rest_ensure_response(
                            array(
                                "status" => "failed",
                                "message" => "This store does not exists.",
                            )
                        );
                    }

                    // Check if status = 0
                    if ( $get_store->status == 0 ) {
                        return array(
                            "status" => "failed",
                            "message" => "This store is currently deactivated.",
                        );
                    }

                //  End Get store and status

                $wpdb->query("START TRANSACTION");

                    $store_ope = $wpdb->get_row("SELECT ID FROM $table_operations WHERE stid = '{$user["store_id"]}' ");

                    switch ($user['type']) {
                        case 'open':
                            $status = $wpdb->query("INSERT INTO $table_rev (revs_type, parent_id, child_key, child_val, created_by, date_created) VALUES ('operations', '$store_ope->ID', 'open_by', '{$user["user_id"]}', '{$user["user_id"]}', '$date') ");
                            $status_id = $wpdb->insert_id;

                            $type = $wpdb->query("UPDATE $table_operations SET date_open = '$date', open_by = '$status_id' WHERE stid = '{$user["store_id"]}' ");
                            break;

                        case 'close':
                            $status = $wpdb->query("INSERT INTO $table_rev (revs_type, parent_id, child_key, child_val, created_by, date_created) VALUES ('operations', '$store_ope->ID', 'close_by', '{$user["user_id"]}', '{$user["user_id"]}', '$date') ");
                            $status_id = $wpdb->insert_id;

                            $type = $wpdb->query("UPDATE $table_operations SET date_close = '$date', close_by = '$status_id' WHERE stid = '{$user["store_id"]}' ");
                            break;
                    }

                    if ($status == false || $type == false ) {
                        $wpdb->query("ROLLBACK");
                        return array(
                            "status" => "failed",
                            "message" => "An error occured while submitting data to server."
                        );
                    }else{
                        $wpdb->query("COMMIT");
                        return array(
                            "status" => "success",
                            "message" => "Data has been added successfully."
                        );
                    }
            }
        /*  End */

    }