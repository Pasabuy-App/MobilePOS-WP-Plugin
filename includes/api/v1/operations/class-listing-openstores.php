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
    class MP_Listing_Open {
        
        public static function listen(){
            return rest_ensure_response( 
                MP_Listing_Open:: list_open()
            );
        }
        
        public static function list_open(){

            global $wpdb;
            $table_revs = TP_REVISIONS_TABLE;
            $table_store = TP_STORES_TABLE;
            $table_ope = MP_OPERATIONS_TABLE;
            $table_add = DV_ADDRESS_TABLE; 
            $table_ctry = DV_COUNTRY_TABLE; 
            $table_prov = DV_PROVINCE_TABLE; 
            $table_ct = DV_CITY_TABLE; 
            $table_brgy = DV_BRGY_TABLE; 
            $table_dvrev = DV_REVS_TABLE;

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
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }
            
            // Step 3: Convert timezone to user specific timezone
            $date = MP_Globals::get_user_date($_POST['wpid']);
            
            // Step 4: Start mysql transaction
            $sql = "SELECT st.ID, 
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.title ) AS `title`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.short_info ) AS `short_info`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.long_info ) AS `long_info`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.logo ) AS `logo`,
                ( SELECT rev.child_val FROM $table_revs rev WHERE ID = st.banner ) AS `banner`,
                ( SELECT $table_dvrev.child_val FROM $table_dvrev WHERE ID = dv_add.street ) AS `street`,
                ( SELECT brgy_name FROM $table_brgy WHERE ID = ( SELECT child_val FROM $table_dvrev WHERE ID = dv_add.brgy ) ) AS brgy,
                ( SELECT city_name FROM $table_ct WHERE city_code = ( SELECT child_val FROM $table_dvrev WHERE ID = dv_add.city ) ) AS city,
                ( SELECT prov_name FROM $table_prov WHERE prov_code = ( SELECT child_val FROM $table_dvrev WHERE ID = dv_add.province ) ) AS province,
                ( SELECT country_name FROM $table_ctry WHERE ID = ( SELECT child_val FROM $table_dvrev WHERE ID = dv_add.country ) ) AS country,
                ops.date_open, ops.date_close 
            FROM
                $table_store st
            INNER JOIN 
                $table_revs rev ON rev.ID = st.`status` 
            INNER JOIN 
                $table_add dv_add ON st.address = dv_add.ID
            INNER JOIN
                $table_ope ops ON ops.stid = st.ID	
            WHERE 
                rev.child_val = 1
            AND
            '$date' BETWEEN ops.`date_open` AND ops.`date_close`";
            
            $result = $wpdb->get_results($sql);

            // Step 5: Return result
            return array(
                "status" => "success",
                "data" => $result
            );
        }
    }