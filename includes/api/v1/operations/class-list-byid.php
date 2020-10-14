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
    class MP_List_By_Id_Operations {

        public static function listen(){
            return rest_ensure_response(
                self:: listen_open()
            );
        }

        /*  Methods */
            public static function listen_open(){
                global $wpdb;

                // Step 1: Check if prerequisites plugin are missing
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

                $sql = "SELECT
                    o.ID,
                    o.hash_id,
                    o.date_open,
                    o.date_close,
                    (SELECT child_val FROM mp_revisions WHERE ID = o.open_by AND child_key = 'open_by') as open_by,
                    (SELECT child_val FROM mp_revisions WHERE ID = o.close_by AND child_key = 'close_by') as close_by,
                    o.stid
                FROM mp_operations o ";

                if (isset($_POST['stid'])) {
                    if (!empty($_POST['stid'])) {
                        $stid = $_POST['stid'];
                        $sql .= " WHERE o.stid = $stid ";
                    }
                }
                $data = $wpdb->get_results();

                return array(
                    "status" => "success",
                    "data" => $data
                );
            }
        }