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
                op.ID,
                                    op.hash_id,
                                    op.stid,
                                    IF(op.date_close is null, '', op.date_close) as date_close,
                                    IF(op.date_open is null, '', op.date_open) as date_open,
                                    IF((SELECT child_val FROM mp_revisions WHERE ID = op.open_by AND child_key = 'open_by') is null , '',
                                    (SELECT child_val FROM mp_revisions WHERE ID = op.open_by AND child_key = 'open_by') ) as open_by,
                                    IF((SELECT child_val FROM mp_revisions WHERE ID = op.close_by AND child_key = 'close_by')is null, '',
                                    (SELECT child_val FROM mp_revisions WHERE ID = op.close_by AND child_key = 'close_by')) as close_by,
                                    op.stid,
                                    null as total_sale,
                                    op.date_open as `date`

                            FROM
                                mp_operations op
                                        LEFT JOIN mp_orders m ON m.opid = op.ID
                            LEFT JOIN mp_order_items moi on moi.odid = m.ID

                        ";



                if (isset($_POST['stid'])) {
                    if (!empty($_POST['stid'])) {
                        $stid = $_POST['stid'];
                        $sql .= " AND op.stid = $stid ";
                    }
                }

                if(isset($_POST['opid'])){
                    if (!empty($_POST['opid'])) {
                        $opid = $_POST['opid'];
                        $sql .= " AND op.ID = $opid ";
                    }
                }
                //return $sql;
                $data = $wpdb->get_results($sql);

                foreach ($data as $key => $value) {
                    $smp = $wpdb->get_row("SELECT
                    COALESCE(SUM((SELECT (SELECT child_val FROM tp_revisions WHERE ID = p.price AND revs_type = 'products' AND child_key = 'price') FROM tp_products p WHERE ID = moi.pdid ))) as total_sale

                    FROM
                    mp_orders mo
                    LEFT JOIN mp_order_items moi on moi.odid = mo.ID
                    WHERE (SELECT child_val FROM mp_revisions WHERE ID = mo.`status` ) = 'completed' AND  mo.stid  = '$value->stid'");
                    $value->total_sale = $smp->total_sale;
                }

                return array(
                    "status" => "success",
                    "data" => $data
                );
            }
        }