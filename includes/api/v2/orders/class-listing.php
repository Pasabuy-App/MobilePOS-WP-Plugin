<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) )
	{
		exit;
	}

	/**
        * @package mobilepos-wp-plugin
        * @version 0.1.0
	*/
	class MP_Listing_Order_v2 {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            isset($_POST['odid']) && !empty($_POST['odid'])? $curl_user['odid'] =  $_POST['odid'] :  $curl_user['odid'] = null ;


            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            // MP
            $tbl_orders_v2 = MP_ORDERS_v2;
            $tbl_operation = MP_OPERATIONS_v2;
            $tbl_order_items = MP_ORDERS_ITEMS_v2;
            $tbl_order_items_vars = MP_ORDERS_ITEMS_VARS_v2;
            // DV
            $tbl_address = DV_ADDRESS_TABLE;
            // WP
            $tbl_user = $wpdb->prefix.'users';
            // TP
            $tbl_store_view = TP_STORES_VIEW;

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

            $user = self::catch_post();

            $validate = MP_Globals_v2::check_listener($user);
            if ($validate !== true) {
                return array(
                    "status" => "failed",
                    "message" => "Required fileds cannot be empty "."'".ucfirst($validate)."'"."."
                );
            }

            $sql = "SELECT
                pubkey,
                (SELECT stid FROM  $tbl_operation WHERE hsid = m.opid ) as operation,

                # Customer Data
                (SELECT display_name FROM $tbl_user WHERE ID = m.order_by) AS customer,
                (SELECT meta_value FROM wp_usermeta WHERE `user_id` = m.order_by and meta_key = 'avatar' ) AS avatar,
                CONCAT(
                (SELECT child_val FROM dv_revisions WHERE ID = (SELECT street FROM dv_address WHERE ID = m.adid )),', ',
                (SELECT brgy_name FROM dv_geo_brgys WHERE ID = (SELECT child_val FROM dv_revisions WHERE ID = (SELECT brgy FROM dv_address WHERE ID = m.adid ))) , ', ',
                (SELECT city_name FROM dv_geo_cities WHERE city_code = (SELECT child_val FROM dv_revisions WHERE ID = (SELECT city FROM dv_address WHERE ID = m.adid ))) ,', ',
                (SELECT prov_name FROM dv_geo_provinces WHERE prov_code = (SELECT child_val FROM dv_revisions WHERE ID = (SELECT province FROM dv_address WHERE ID = m.adid ))),', ',
                (SELECT country_name FROM dv_geo_countries WHERE country_code = (SELECT child_val FROM dv_revisions WHERE ID = (SELECT country FROM dv_address WHERE ID = m.adid ))) )as cutomer_address,
                (SELECT child_val FROM dv_revisions WHERE ID = (SELECT latitude FROM dv_address WHERE ID = m.adid )) as cutomer_lat,
                (SELECT child_val FROM dv_revisions WHERE ID = (SELECT longitude FROM dv_address WHERE ID = m.adid )) as cutomer_long,
                opid,
                # Store Data
                null as store_name,
                null as store_logo,
                null as store_address,
                null as store_lat,
                null as store_long,
                stages,
                # Product Data
                null as products,
                null as total_price,
                date_created
            FROM
                $tbl_orders_v2 m
            WHERE
                id IN ( SELECT MAX( id ) FROM $tbl_orders_v2 GROUP BY pubkey )";

            if ($user['odid'] != null) {
                $sql .= " AND pubkey = '{$user["odid"]}' ";
            }

            $order_data = $wpdb->get_results($sql);

            $total_variants = '';
            $total_variants_price = 0;
            $total_price = 0;

            foreach ($order_data as $key => $value) {
                // Get Store Data
                    $get_store_id = $wpdb->get_row("SELECT stid FROM $tbl_operation WHERE hsid = '$value->opid'");
                    $get_store_data = $wpdb->get_row("SELECT *  FROM $tbl_store_view WHERE ID =  $get_store_id->stid");
                    $value->store_address = $get_store_data->street.', '.$get_store_data->brgy.', '.$get_store_data->city.', '.$get_store_data->province.', '.$get_store_data->country;
                    $value->store_name = $get_store_data->title;
                    $value->store_logo = $get_store_data->avatar;
                    $value->store_lat = $get_store_data->lat;
                    $value->store_long = $get_store_data->long;
                // End

                // Get Product Data
                    $get_product = $wpdb->get_results("SELECT
                        hsid as ID,
                        (SELECT child_val FROM tp_revisions WHERE ID = (SELECT title FROM tp_products  WHERE ID = mi.pdid)) AS product_name,
                        (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products  WHERE ID = mi.pdid)) AS price,
                        mi.quantity,
                        null as variants,
                        null as variants_price
                    FROM $tbl_order_items mi
                    WHERE odid = '$value->pubkey'
                    ");

                    foreach ($get_product as $keys => $values) {
                        $get_order_variants = $wpdb->get_results("SELECT
                            ( SELECT child_val FROM tp_revisions rev WHERE  rev.parent_id = vrid AND rev.child_key = 'name' AND rev.revs_type = 'variants' AND rev.ID = ( SELECT MAX(ID) FROM tp_revisions WHERE  parent_id = rev.parent_id AND revs_type ='variants' AND child_key = 'name'  ) ) as `name`,
                            (SELECT child_val FROM tp_revisions rev WHERE parent_id = vrid AND child_key = 'price' AND revs_type ='variants'  AND ID = ( SELECT MAX(ID) FROM tp_revisions WHERE  parent_id = rev.parent_id AND revs_type ='variants' AND child_key = 'price'  ) ) as `price`

                        FROM
                            $tbl_order_items_vars
                        WHERE otid = '$values->ID'");

                        foreach ($get_order_variants as $var_key => $var_value) {
                            $total_variants .= ' '.$var_value->name;
                            $total_variants_price += $var_value->price;
                        }

                        $value->total_price += $values->price + $total_variants_price;
                        $values->variants = $total_variants;
                        $values->variants_price = $total_variants_price;

                    }

                    $value->products = $get_product;
                // End
            }

            return array(
                "status" => "success",
                "data" => $order_data
            );
        }
    }