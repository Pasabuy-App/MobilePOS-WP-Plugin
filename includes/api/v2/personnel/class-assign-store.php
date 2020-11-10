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
            $tbl_store      = TP_STORES_v2;
            $tbl_personnel  = MP_PERSONNELS_v2;
            $tbl_roles      = MP_ROLES_v2;
            $tbl_access     = MP_ACCESS_v2;
            $tbl_permission = MP_PERMISSION_v2;

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
                (SELECT `title` FROM $tbl_roles WHERE hsid = p.roid ) as role_title,
                (SELECT `title` FROM $tbl_store WHERE hsid = p.stid  AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as title,
                (SELECT `info` FROM $tbl_store WHERE hsid = p.stid AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as `info`,
                (SELECT `avatar` FROM $tbl_store WHERE hsid = p.stid AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as avatar,
                (SELECT `banner` FROM $tbl_store WHERE hsid = p.stid AND id IN ( SELECT MAX( id ) FROM $tbl_store GROUP BY hsid ) ) as banner,
                activated,
                `status`
            FROM
                $tbl_personnel p
            WHERE
                id IN ( SELECT MAX( id ) FROM $tbl_personnel WHERE p.hsid = hsid GROUP BY hsid )
            AND wpid = '$wpid' AND activated = 'true' AND assigned_by is not null
            ";

            $data = $wpdb->get_results($sql);

            foreach ($data as $key => $value) {
                if (is_numeric($value->avatar)) {

                    $image = wp_get_attachment_image_src( $value->avatar, 'medium', $icon =false );
                    if ($image != false) {
                        $value->avatar = $image[0];
                    }else{
                        $get_image = $wpdb->get_row("SELECT meta_value FROM wp_postmeta WHERE meta_id = $value->avatar ");
                        if(!empty($get_image)){
                            // $value->avatar = 'https://pasabuy.app/wp-content/uploads/'.$get_image->meta_value;
                            $value->avatar =   $value->avatar;
                        }else{
                            $value->avatar = $get_image->meta_value;
                        }
                    }

                }else{
                    $value->avatar = '';
                }

                if (is_numeric($value->banner)) {

                    $image = wp_get_attachment_image_src( $value->banner, 'medium', $icon =false );
                    if ($image != false) {
                        $value->banner = $image[0];
                    }else{
                        $get_image = $wpdb->get_row("SELECT meta_value FROM wp_postmeta WHERE meta_id = $value->banner ");
                        if(!empty($get_image)){
                            $value->banner = $get_image->meta_value;
                        }else{
                            $value->banner =   $value->banner;
                        }
                    }

                }else{
                    $value->banner = '';
                }

                // Import Access
                    $get_permissions = $wpdb->get_results("SELECT (SELECT title FROM $tbl_access WHERE hsid = p.access ) as access, (SELECT `actions` FROM $tbl_access WHERE hsid = p.access ) as `action` FROM $tbl_permission p WHERE roid = '$value->roid'   ");
                    if (!empty($get_permissions)) {
                        $value->permissions = $get_permissions;
                    }
                // End
            }
            return array(
                "status" => "success",
                "data" => $data
            );
        }
    }
