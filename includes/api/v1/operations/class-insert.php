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
    class MP_Insert_Operations {

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

                if (!isset($_POST['stid'])) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request Unknown!",
                    );
                }

                if (empty($_POST['stid'])) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty!",
                    );
                }

                // Call Catch post fucntion
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

                $wpdb->query("  START TRANSACTION");

                    $insert_ope = $wpdb->query("INSERT INTO $table_operations ( date_open,  stid )
                                                        VALUES (  '$date',  '{$user["store_id"]}' )");
                    $ope_id = $wpdb->insert_id;

                    $insert_open_by = $wpdb->query("INSERT INTO $table_rev (revs_type, parent_id, child_key, child_val, created_by, date_created) VALUES ('operations', '$ope_id', 'open_by', '{$user["user_id"]}', '{$user["user_id"]}', '$date') ");
                    $open_id = $wpdb->insert_id;

                    $update_parent = $wpdb->query("UPDATE $table_operations SET open_by = '$open_id' WHERE ID = '$ope_id' ");

                    $hash_id = MP_Globals::update_hash_id_hash($ope_id, $table_operations, 'hash_id');

                    if ($insert_ope == false || $insert_open_by  == false || $update_parent  == false || $hash_id == false) {
                        $wpdb->query("ROLLBACK");
                        return array(
                            "status" => "failed",
                            "message" => "An erro occured while submitting data to server.",

                        );
                    }else{
                        $wpdb->query("COMMIT");
                        return array(
                            "status" => "success",
                            "message" => "Data has been added successfully.",
                        );
                    }
            }
        /*  End Method */

    }