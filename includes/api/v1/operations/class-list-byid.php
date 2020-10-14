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
                    IF(o.date_close is null, '', o.date_close) as date_close,
                    IF(o.date_open is null, '', o.date_open) as date_open,
                    IF((SELECT child_val FROM mp_revisions WHERE ID = o.open_by AND child_key = 'open_by') is null , '',
                    (SELECT child_val FROM mp_revisions WHERE ID = o.open_by AND child_key = 'open_by') ) as open_by,
                    IF((SELECT child_val FROM mp_revisions WHERE ID = o.close_by AND child_key = 'close_by')is null, '',
                    (SELECT child_val FROM mp_revisions WHERE ID = o.close_by AND child_key = 'close_by')) as close_by,
                    o.stid
                FROM mp_operations o ";

                if (isset($_POST['stid'])) {
                    if (!empty($_POST['stid'])) {
                        $stid = $_POST['stid'];
                        $sql .= " WHERE o.stid = $stid ";
                    }
                }

                if(isset($_POST['opid'])){
                    if (!empty($_POST['opid'])) {
                        $opid = $_POST['opid'];
                        if (isset($_POST['stid']) && $_POST['stid'] != null) {
                            $sql .= " AND o.ID = $opid ";
                        }else{
                            $sql .= " WHERE o.ID = $opid ";
                        }
                    }
                }

                $data = $wpdb->get_results($sql);

                return array(
                    "status" => "success",
                    "data" => $data
                );
            }
        }