<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) )
	{
		exit;
	}

	/**
        * @package mobilepos-wp-plugin
        * @version 0.2.0
	*/
	class MP_Listing_Personnels_Store_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function list_open(){

            global $wpdb;
            $tbl_store = TP_STORES_v2;
            $tbl_personnel = MP_PERSONNELS_v2;
            $tbl_roles = MP_ROLES_v2;

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

            $wpid = $_POST['wpid'];

            $sql = "SELECT
                stid as ID,
                roid,
                wpid,
                (SELECT title FROM $tbl_roles WHERE hsid = p.roid ) as role_title,
                (SELECT title FROM $tbl_store WHERE hsid = p.stid  AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as title,
                (SELECT `info` FROM $tbl_store WHERE hsid = p.stid AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as `info`,
                (SELECT avatar FROM $tbl_store WHERE hsid = p.stid AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as avatar,
                (SELECT banner FROM $tbl_store WHERE hsid = p.stid AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as banner,
                activated,
                `status`
            FROM
                $tbl_personnel p
            WHERE
                id IN ( SELECT MAX( id ) FROM $tbl_personnel WHERE p.hsid = hsid GROUP BY hsid )
            AND wpid = '$wpid' AND activated = 'true' AND assigned_by is not null
            ";

            $data = $wpdb->get_results($sql);

            return array(
                "status" => "success",
                "data" => $data
            );
        }
    }
