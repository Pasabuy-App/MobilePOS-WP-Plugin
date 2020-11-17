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

            isset($_POST['order_by']) && !empty($_POST['order_by'])? $curl_user['order_by'] =  $_POST['order_by'] :  $curl_user['order_by'] = null ;
            isset($_POST['odid']) && !empty($_POST['odid'])? $curl_user['odid'] =  $_POST['odid'] :  $curl_user['odid'] = null ;
            isset($_POST['stages']) && !empty($_POST['stages'])? $curl_user['stages'] =  $_POST['stages'] :  $curl_user['stages'] = null ;
            isset($_POST['stid']) && !empty($_POST['stid'])? $curl_user['stid'] =  $_POST['stid'] :  $curl_user['stid'] = null ;

            return $curl_user;
        }

        public static function list_open(){

            global $wpdb;
            // MP
            $tbl_orders_v2 = MP_ORDERS_v2;
            $tbl_operation = MP_OPERATIONS_v2;
            $tbl_order_items = MP_ORDERS_ITEMS_v2;
            $tbl_order_items_vars = MP_ORDERS_ITEMS_VARS_v2;
            $tbl_payment = MP_PAYMENTS_v2;
            // DV
            $tbl_address_view = DV_ADDRESS_VIEW;
            $tbl_address = DV_ADDRESS_TABLE;
            // WP
            $tbl_user = $wpdb->prefix.'users';
            // TP
            $tbl_store = TP_STORES_v2;
            $tbl_product = TP_PRODUCT_v2;
            $tbl_variants = TP_PRODUCT_VARIANTS_v2;
            // Hp
            $tbl_delivery = HP_DELIVERIES_v2;
            $tbl_vehicle = HP_VEHICLES_v2;
            $tbl_mover = HP_MOVERS_v2;

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

            $sql = "SELECT
                pubkey,
                (SELECT stid FROM  $tbl_operation WHERE hsid = m.opid ) as stid,
                # Customer Data
                order_by,
                (SELECT display_name FROM $tbl_user WHERE ID = m.order_by) AS customer,
                (SELECT meta_value FROM wp_usermeta WHERE `user_id` = m.order_by and meta_key = 'avatar' ) AS avatar,
                (SELECT child_val FROM dv_revisions WHERE ID = (SELECT latitude FROM dv_address WHERE ID = m.adid )) as cutomer_lat,
                (SELECT child_val FROM dv_revisions WHERE ID = (SELECT longitude FROM dv_address WHERE ID = m.adid )) as cutomer_long,
                opid,
                adid,
                delivery_charges,
                (SELECT method FROM $tbl_payment WHERE odid = m.pubkey ) as method,
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
                id IN ( SELECT MAX( id ) FROM $tbl_orders_v2 WHERE m.pubkey = pubkey  GROUP BY pubkey )";

            if ($user['odid'] != null) {
                $sql .= " AND pubkey = '{$user["odid"]}' ";
            }

            if ($user['stid'] != null) {
                $sql .= " AND (SELECT stid FROM  $tbl_operation WHERE hsid = m.opid )  = '{$user["stid"]}' ";
            }

            if ($user['order_by'] != null) {
                $sql .= " AND order_by = '{$user["order_by"]}' ";
            }

            if ($user["stages"] != null) {
                if ($user["stages"] != "2" && $user["stages"] != "1" && $user["stages"] != "3" && $user["stages"] != "4") {
                    return array(
                        "status" => "failed",
                        "message" => "Invalid value of stages."
                    );
                }

                switch ($user["stages"]) {
                    case '1':
                        $sql .= " HAVING stages IN('ongoing', 'accepted', 'pending')  ";
                        break;
                    case '2':
                        $sql .= " HAVING stages IN('preparing', 'shipping')   ";
                        break;
                    case '3':
                        $sql .= " HAVING stages IN('completed')   ";
                        break;
                    case '4':
                        $sql .= " HAVING stages IN('cancelled')  ";
                        break;
                }
            }

            $order_data = $wpdb->get_results($sql);
            $smp = array();
            $total_variants = '';
            $total_variants_price = 0;
            $total_price = 0;

            foreach ($order_data as $key => $value) {
                // Get Store Data
                    $get_store_id = $wpdb->get_row("SELECT stid FROM $tbl_operation WHERE hsid = '$value->opid'");

                    $get_store_data = $wpdb->get_row("SELECT adid, title, avatar FROM $tbl_store WHERE hsid = '$get_store_id->stid' ");

                    $get_store_address = $wpdb->get_row("SELECT * FROM $tbl_address_view WHERE ID = '$get_store_data->adid' ");
                    $get_customer_address = $wpdb->get_row("SELECT * FROM $tbl_address_view WHERE ID = '$value->adid' ");

                    $value->store_address = $get_store_address->street.', '.$get_store_address->brgy.', '.$get_store_address->city.', '.$get_store_address->province.', '.$get_store_address->country;
                    $value->store_name = $get_store_data->title;
                    // $value->store_logo = $get_store_data->avatar;
                    $value->store_lat = $get_store_address->latitude;
                    $value->store_long = $get_store_address->longitude;

                    $value->cutomer_address = $get_customer_address->street.', '.$get_customer_address->brgy.', '.$get_customer_address->city.', '.$get_customer_address->province.', '.$get_customer_address->country;

                    if (is_numeric($get_store_data->avatar)) {

                        $image = wp_get_attachment_image_src( $get_store_data->avatar, 'medium', $icon =false );
                        if ($image != false) {
                            $get_store_data->avatar = $image[0];
                        }else{
                            $get_image = $wpdb->get_row("SELECT meta_value FROM wp_postmeta WHERE meta_id = $get_store_data->avatar ");
                            if(!empty($get_image)){
                                // $value->preview = 'https://pasabuy.app/wp-content/uploads/'.$get_image->meta_value;
                                $value->store_logo = 'None';
                            }else{
                                $value->store_logo = 'None';
                            }
                        }

                    }else{
                        $value->avatar = 'None';
                    }

                // End

                // Get Product Data
                    $get_product = $wpdb->get_results("SELECT
                            hsid as ID,
                            (SELECT title FROM $tbl_product WHERE  hsid = mi.pdid AND  ID IN ( SELECT MAX( pdd.ID ) FROM $tbl_product  pdd WHERE pdd.hsid = hsid GROUP BY hsid )  ) as product_name,
                            #(SELECT child_val FROM tp_revisions WHERE ID = (SELECT title FROM tp_products  WHERE ID = mi.pdid)) AS product_name,
                            remarks,
                            (SELECT price FROM $tbl_product WHERE  hsid = mi.pdid AND  ID IN ( SELECT MAX( pdd.ID ) FROM $tbl_product  pdd WHERE pdd.hsid = hsid GROUP BY hsid )  ) as price,
                            mi.quantity,
                            null as variants,
                            null as variants_price
                        FROM
                            $tbl_order_items mi
                        WHERE
                            odid = '$value->pubkey'
                        AND
                            ID IN ( SELECT MAX( ID ) FROM $tbl_order_items WHERE mi.hsid = hsid GROUP BY hsid ) ");

                    foreach ($get_product as $keys => $values) {

                        $get_order_variants = $wpdb->get_results("SELECT
                            (SELECT title FROM $tbl_variants v  WHERE hsid = iv.vrid  AND id IN ( SELECT MAX( id ) FROM $tbl_variants WHERE v.hsid = hsid  GROUP BY hsid ) ) as `name`,
                            (SELECT price FROM $tbl_variants v  WHERE hsid = iv.vrid  AND id IN ( SELECT MAX( id ) FROM $tbl_variants WHERE v.hsid = hsid  GROUP BY hsid ) ) as `price`
                        FROM
                            $tbl_order_items_vars iv
                        WHERE
                            otid = '$values->ID'");

                        foreach ($get_order_variants as $var_key => $var_value) {

                            $total_variants .= ' '.$var_value->name;
                            $total_variants_price += $var_value->price;

                        }

                        $value->total_price += ( $values->price + $total_variants_price ) * $values->quantity ;
                        $values->variants = $total_variants;
                        $values->variants_price = $total_variants_price;
                    }
                    $value->products = $get_product;
                    $value->stages = ucfirst($value->stages);
                    $value->method = ucfirst($value->method);
                // End


                // Get Driver
                    if ($value->stages == "Completed" || $value->stages == "Shipping" || $value->stages == "Ongoing" || $value->stages == "Preparing") {
                        #return $value->pubkey;
                        $driver_data = $wpdb->get_row("SELECT vhid FROM $tbl_delivery WHERE order_id = '$value->pubkey' ");

                        $get_mover_data = $wpdb->get_row("SELECT
                                mvid
                            FROM
                                $tbl_vehicle v
                            WHERE
                                hsid = '$driver_data->vhid'
                            AND
                                id IN ( SELECT MAX( id ) FROM $tbl_vehicle WHERE hsid = v.hsid GROUP BY hsid ) ");
                        #                        $get_mover_avatar
                        $get_mover_wpid = $wpdb->get_row("SELECT  `wpid`  FROM  $tbl_mover WHERE pubkey = '$get_mover_data->mvid' AND id IN ( SELECT MAX( id ) FROM $tbl_mover v WHERE hsid = v.hsid GROUP BY hsid ) ");
                        $wp_user = get_user_by("ID", $get_mover_wpid->wpid);
                        $value->driver_name = $wp_user->display_name;
                        $value->mover_id = $get_mover_data->mvid;
                        $avatar = get_user_meta( $get_mover_wpid->wpid,  $key = 'avatar', $single = false );
                        $value->driver_avatar = !$avatar ? SP_PLUGIN_URL . "assets/default-avatar.png" : $avatar[0];
                    }else{
                        $value->driver_name = "";
                        $value->mover_id = "";
                        $value->driver_avatar = "";
                    }
                // End

                #$value->stages = ucfirst($value->stages);
            } // End

            return array(
                "status" => "success",
                "data" => $order_data
            );
        }
    }