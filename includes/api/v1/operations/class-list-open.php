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
    class TP_List_Open {

        public static function listen(){
            return rest_ensure_response(
                TP_List_Open:: list_open()
            );
        }

        public static function list_open(){

            //Initial QA done 2020-08-11 11:10 AM
            // 2nd Initial QA 2020-08-24 5:53 PM - Miguel

            global $wpdb;
            $table_revs = TP_REVISIONS_TABLE;
            $table_categories = TP_CATEGORIES_TABLE;
            $table_store = TP_STORES_TABLE;

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

            // Step 3: Convert timezone to user specific timezone
            $date = TP_Globals::get_user_date($_POST['wpid']);

            // Step 4: Start mysql query
            $open_stores = $wpdb->get_results("SELECT
                    st.ID,
                    date_open,
                    date_close,
                    ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.title ) AS `title`,
                    ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.short_info ) AS `short_info`,
                    ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.long_info ) AS `long_info`,
                    ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.logo ) AS `logo`,
                    ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.banner ) AS `banner`,
                    ( SELECT dv_revisions.child_val FROM dv_revisions WHERE ID = dv_add.street ) AS `street`,
                    ( SELECT brgy_name FROM dv_geo_brgys WHERE ID = ( SELECT child_val FROM dv_revisions WHERE ID = dv_add.brgy ) ) AS brgy,
                    ( SELECT city_name FROM dv_geo_cities WHERE city_code = ( SELECT child_val FROM dv_revisions WHERE ID = dv_add.city ) ) AS city,
                    ( SELECT prov_name FROM dv_geo_provinces WHERE prov_code = ( SELECT child_val FROM dv_revisions WHERE ID = dv_add.province ) ) AS province,
                    ( SELECT country_name FROM dv_geo_countries WHERE ID = ( SELECT child_val FROM dv_revisions WHERE ID = dv_add.country ) ) AS country
                FROM
                    $table_store st
                INNER JOIN
                    $table_revs rev ON rev.ID = st.`status`
                INNER JOIN
                    dv_address dv_add ON st.address = dv_add.ID
                INNER JOIN
                    mp_operations ops ON ops.stid = st.ID
                WHERE
                    rev.child_val = 1
                AND
                    '$date' BETWEEN `date_open` AND `date_close`");

            // Step 8: Return a success status and message
            return array(
                "status" => "success",
                "data" => $open_stores
            );
        }
    }