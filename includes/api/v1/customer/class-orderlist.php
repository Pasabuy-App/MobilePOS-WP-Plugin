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
    class MP_OrderList {

        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }
        public static function list_open(){

            global $wpdb;
            $table_store = TP_STORES_TABLE;
            $table_prod = TP_PRODUCT_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;
            $table_ord = MP_ORDERS_TABLE;
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            $table_mprevs = MP_REVISIONS_TABLE;

            //Step 1: Check if prerequisites plugin are missing
            $plugin = MP_Globals::verify_prerequisites();
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
            
            // Step 5: Check post stage if valid
            if ( isset($_POST['stage']) ){
                if ( empty($_POST['stage']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                if ( !($_POST['stage'] === 'pending')
                    && !($_POST['stage'] === 'received')
                    && !($_POST['stage'] === 'accepted')
                    && !($_POST['stage'] === 'completed')
                    && !($_POST['stage'] === 'shipping')
                    && !($_POST['stage'] === 'cancelled')) {
                    return array(
                            "status" => "failed",
                            "message" => "Invalid stage.",
                    );
                }
                $stage = $_POST['stage'];
            }

            $colname = "";
            $uid = "0";

            $sql = "SELECT 
                    mo.ID, "; 

            if (isset($_POST['stid'])){
                if ( empty($_POST['stid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $uid = $_POST['stid'];
                $colname = "stid";
                $sql .= "mo.wpid AS user_id, 
                (SELECT display_name FROM wp_users WHERE ID = mo.wpid) AS customer, 
                (SELECT meta_value FROM wp_usermeta WHERE user_id = mo.wpid and meta_key = 'avatar' ) AS avatar, ";
            }
            else{
                $uid = $_POST['wpid'];
                $colname = "wpid";
                $sql .= "mo.stid,
                (SELECT child_val FROM tp_revisions WHERE ID =(SELECT title FROM tp_stores WHERE ID = mo.stid)) AS store_name,
                (SELECT child_val FROM tp_revisions WHERE ID =(SELECT logo FROM tp_stores WHERE ID = mo.stid)) AS store_logo, 
                (SELECT child_val FROM dv_revisions WHERE ID =(SELECT street FROM dv_address WHERE ID = (SELECT address FROM tp_stores WHERE ID = mo.stid))) AS store_street, 
                (SELECT brgy_name FROM dv_geo_brgys WHERE ID = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT brgy FROM dv_address WHERE ID = (SELECT address FROM tp_stores WHERE ID = mo.stid)))) AS store_brgy,  
                (SELECT city_name FROM dv_geo_cities WHERE city_code = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT city FROM dv_address WHERE ID = (SELECT address FROM tp_stores WHERE ID = mo.stid)))) AS store_city,  
                (SELECT prov_name FROM dv_geo_provinces WHERE prov_code = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT province FROM dv_address WHERE ID = (SELECT address FROM tp_stores WHERE ID = mo.stid)))) AS store_province, 
                (SELECT country_name FROM dv_geo_countries WHERE ID = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT country FROM dv_address WHERE ID = (SELECT address FROM tp_stores WHERE ID = mo.stid)))) AS store_country,  
                
                (SELECT child_val FROM dv_revisions WHERE ID =(SELECT street FROM dv_address WHERE ID = (SELECT child_val FROM mp_revisions WHERE revs_type = 'orders' AND child_key = 'address' AND parent_id = mo.ID))) AS my_street, 
                (SELECT brgy_name FROM dv_geo_brgys WHERE ID = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT brgy FROM dv_address WHERE ID = (SELECT child_val FROM mp_revisions WHERE revs_type = 'orders' AND child_key = 'address' AND parent_id = mo.ID)))) AS my_brgy,
                (SELECT city_name FROM dv_geo_cities WHERE city_code = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT city FROM dv_address WHERE ID = (SELECT child_val FROM mp_revisions WHERE revs_type = 'orders' AND child_key = 'address' AND parent_id = mo.ID)))) AS my_city,
                (SELECT prov_name FROM dv_geo_provinces WHERE prov_code = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT province FROM dv_address WHERE ID = (SELECT child_val FROM mp_revisions WHERE revs_type = 'orders' AND child_key = 'address' AND parent_id = mo.ID)))) AS my_province,
                (SELECT country_name FROM dv_geo_countries WHERE ID = (SELECT child_val FROM dv_revisions WHERE ID =(SELECT country FROM dv_address WHERE ID = (SELECT child_val FROM mp_revisions WHERE revs_type = 'orders' AND child_key = 'address' AND parent_id = mo.ID)))) AS my_country,  ";
            }

            if ( isset($_POST['odid']) ){
                if ( empty($_POST['odid']) ){
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $odid = $_POST['odid'];
                $sql .= " moi.quantity, moi.pdid, moi.ID AS item_id, (SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) AS qty,                
                (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS price, 
                (SELECT child_val FROM tp_revisions WHERE ID = (SELECT title FROM tp_products  WHERE ID = moi.pdid)) AS product_name,
                0 AS variant_price,  ";
            }

            $sql .= "0 AS totalprice,
                    (SELECT child_val FROM mp_revisions WHERE ID = mo.`status`) AS stage,
                    (SELECT child_val FROM mp_revisions WHERE ID = mo.`method`) AS method,
                    'Free' AS delivery_fee,
                    mo.date_created
                FROM 
                    mp_orders AS mo
                INNER JOIN 
                    mp_order_items AS moi ON moi.odid = mo.ID
                WHERE 
                    mo.$colname = '$uid' ";

            if (isset($_POST['stage']) ){
                $sql .= " AND (SELECT child_val FROM mp_revisions WHERE ID = mo.`status`) = '$stage' ";
            }
            
            if ( isset($_POST['odid']) ){
                $odid = $_POST['odid'];
                $sql .= " AND mo.ID = '$odid' ORDER BY mo.ID DESC ";
            }
            else{
                $sql .= " GROUP BY mo.ID DESC ";
            }

            $result = $wpdb->get_results($sql);

            if (isset($_POST['odid']) ){
                foreach ($result as $key => $value) {
                
                    $get_quantity = $wpdb->get_row("SELECT child_val AS qty FROM mp_revisions  WHERE ID  = '$value->quantity' ");
    
                    $get_price = $wpdb->get_row("SELECT child_val AS price FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = '$value->pdid' ) ");
    
                    $variants_check = $wpdb->get_results("SELECT * FROM mp_order_item_variant WHERE item_id = '$value->item_id' ");
                    if ($variants_check){
                        $var_price = 0;
                        foreach ($variants_check as $keys => $values) {
        
                            $total = $wpdb->get_row(" (SELECT child_val AS var_price FROM tp_revisions WHERE ID = (SELECT MAX(ID) FROM tp_revisions WHERE revs_type = 'variants' 
                            AND child_key = 'price' AND parent_id = '$values->vrid' )) ");
                            $var_price += $total->var_price;
                        }
                        $value->variant_price = (string)$var_price;
                        $value->totalprice += (($var_price + $get_price->price) *  $get_quantity->qty);
                        $value->totalprice = (string)$value->totalprice;
                    }
                    else{
                        $value->totalprice += ($get_price->price *  $get_quantity->qty);
                        $value->totalprice = (string)$value->totalprice;
                    }
                }
            }
            else{
                foreach ($result as $k => $v) {
                    $get_item = $wpdb->get_results("SELECT * FROM mp_order_items WHERE odid = '$v->ID' ");
                    if ($get_item){
                        foreach ($get_item as $key => $value) {
                
                            $get_quantity = $wpdb->get_row("SELECT child_val AS qty FROM mp_revisions  WHERE ID  = '$value->quantity' ");
            
                            $get_price = $wpdb->get_row("SELECT child_val AS price FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = '$value->pdid' ) ");
            
                            $variants_check = $wpdb->get_results("SELECT * FROM mp_order_item_variant WHERE item_id = '$value->ID' ");
                            if ($variants_check){
                                $var_price = 0;
                                foreach ($variants_check as $keys => $values) {
                
                                    $total = $wpdb->get_row(" (SELECT child_val AS var_price FROM tp_revisions WHERE ID = (SELECT MAX(ID) FROM tp_revisions WHERE revs_type = 'variants' 
                                    AND child_key = 'price' AND parent_id = '$values->vrid' )) ");
    
                                    $var_price += $total->var_price;
                                    //$value->totalprice = (string)$value->totalprice += $total->child_val;
                                }
                                $v->totalprice += (($var_price + $get_price->price) *  $get_quantity->qty);
                                $v->totalprice = (string)$v->totalprice;
                            }
                            else{
                                $v->totalprice += ($get_price->price *  $get_quantity->qty);
                                $v->totalprice = (string)$v->totalprice;
                            }
                        }
                    }
                }
            }

            // Step 12: Return result
            return array(
                "status" => "success",
                "data" => $result
            );

            // // Step 3: Check if parameter is passed
            // if ( !isset($_POST['stid']) ) {
            //     return array(
			// 		"status" => "unknown",
			// 		"message" => "Please contact your administrator. Request unknown!",
            //     );
            // }

            // // Step 4: Check if parameter is empty
            // if ( empty($_POST['stid']) ) {
            //     return array(
            //         "status" => "failed",
            //         "message" => "Required fileds cannot be empty.",
            //     );
            // }

                // Step 5: Check post stage if valid
                // if ( isset($_POST['stage']) ){
                //     if ( empty($_POST['stage']) ) {
                //         return array(
                //             "status" => "failed",
                //             "message" => "Required fileds cannot be empty.",
                //         );
                //     }
                //     if ( !($_POST['stage'] === 'pending')
                //         && !($_POST['stage'] === 'received')
                //         && !($_POST['stage'] === 'accepted')
                //         && !($_POST['stage'] === 'completed')
                //         && !($_POST['stage'] === 'shipping')
                //         && !($_POST['stage'] === 'cancelled')) {
                //         return array(
                //                 "status" => "failed",
                //                 "message" => "Invalid stage.",
                //         );
                //     }
                //     $stage = $_POST['stage'];
                // }
    
                // $colname = "";
                // $uid = "0";
    
                // if (isset($_POST['stid'])){
                //     if ( empty($_POST['stid']) ) {
                //         return array(
                //             "status" => "failed",
                //             "message" => "Required fileds cannot be empty.",
                //         );
                //     }
                //     $uid = $_POST['stid'];
                //     $colname = "stid";
                // }
                // else{
                //     $uid = $_POST['wpid'];
                //     $colname = "wpid";
                // }
    
                // // Step 4: Start mysql transaction
                // $sql = "SELECT
                //     moi.ID,
                //     mo.wpid AS user_id,
                //     mo.stid, 
                //     (SELECT display_name FROM wp_users WHERE ID = mo.wpid) AS customer, 
                //     (SELECT meta_value FROM wp_usermeta WHERE user_id = mo.wpid and meta_key = 'avatar' ) AS avatar, 
                //     mo.ID AS odid,";
    
                // if ( isset($_POST['odid']) ){
                //     if ( empty($_POST['odid']) ){
                //         return array(
                //             "status" => "failed",
                //             "message" => "Required fileds cannot be empty.",
                //         );
                //     }
                //     $odid = $_POST['odid'];
                //     $sql .= " (SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) AS qty,                
                //     (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS price, 
                //     (SELECT child_val FROM tp_revisions WHERE ID = (SELECT title FROM tp_products  WHERE ID = moi.pdid)) AS product_name, ";
    
                //     $sql .= " (SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) * (SELECT child_val FROM tp_revisions WHERE ID = 
                //      (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS totalprice,  ";
    
                //     //$sql .= " (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS totalprice,  ";
    
                // }
                // else{
                //      $sql .= " SUM((SELECT child_val FROM mp_revisions WHERE ID = moi.quantity) * (SELECT child_val FROM tp_revisions WHERE ID = 
                //      (SELECT price FROM tp_products WHERE ID = moi.pdid ))) AS totalprice,   ";
                    
                //     $sql .= " (SELECT child_val FROM tp_revisions WHERE ID = (SELECT price FROM tp_products WHERE ID = moi.pdid )) AS totalprice,  ";
    
                // }
                // if ($colname == "wpid"){
                //     $sql .= "
                //     (SELECT child_val FROM tp_revisions WHERE ID =(SELECT title FROM tp_stores WHERE ID = mo.stid)) AS store_name,
                //     (SELECT child_val FROM tp_revisions WHERE ID =(SELECT logo FROM tp_stores WHERE ID = mo.stid)) AS store_logo, ";
                // }
    
                // $sql .= " (SELECT child_val FROM mp_revisions WHERE ID = mo.`status`) AS stage,
                //         (SELECT child_val FROM mp_revisions WHERE ID = mo.`method`) AS method, 
                //         0 as variant_price,
                //     mo.date_created
                // FROM
                //     mp_order_items AS moi
                //     INNER JOIN mp_orders AS mo ON mo.ID = moi.odid
                //     WHERE mo.$colname = '$uid' ";
    
                // if (isset($_POST['stage']) ){
                //     $sql .= " AND (SELECT child_val FROM mp_revisions WHERE ID = mo.`status`) = '$stage' ";
                // }
    
                // if ( isset($_POST['odid']) ){
                //     $sql .= " AND moi.odid = '$odid' ORDER BY moi.ID DESC ";
                // }
                // else{
                //     $sql .= " GROUP BY moi.odid DESC ";
                // }
                
                // $result = $wpdb->get_results($sql);
                
                // foreach ($result as $key => $value) {
    
                //     $variants_check = $wpdb->get_results("SELECT * FROM mp_order_item_variant WHERE item_id = '$value->ID' ");
                //     if ($variants_check){
                //         foreach ($variants_check as $keys => $values) {
        
                //         $total = $wpdb->get_row(" (SELECT child_val FROM tp_revisions WHERE ID = (SELECT MAX(ID) FROM tp_revisions WHERE revs_type = 'variants' 
                //         AND child_key = 'price' AND parent_id = '$values->vrid' )) ");
                //         $value->variant_price = (string)$value->variant_price += $total->child_val;
                //         }
        
                //          $quantity_check = $wpdb->get_results(" SELECT ID FROM mp_order_items WHERE odid = '$value->odid'");
                //         foreach ($quantity_check as $keyss => $valuess) {
                //             $qty = $wpdb->get_row(" (SELECT child_val FROM mp_revisions WHERE revs_type = 'order_items' AND child_key = 'quantity' AND parent_id = '$valuess->ID' ) ");
                //             $value->totalprice = (string)(($value->variant_price + $value->totalprice) * $qty->child_val);
                //         }
                //     }
    
                // }
                
                // TODO : Check order item if have a variants
    
                // foreach ($result as $key => $value) {
    
                //     $total = $wpdb->get_row(" (SELECT child_val FROM tp_revisions WHERE revs_type = 'variants' 
                //     AND child_key = 'price' AND parent_id = (SELECT vrid FROM mp_order_item_variant WHERE item_id = '$value->ID' ) ) ");
                //     $price += $total;
                //     $value->totalprice = $price;
                // }
    
                //return $sql;
    
                // Step 11: Check if no rows found
                // if (!$result) {
                //     // return array(
                //     //     "status" => "failed",
                //     //     "message" => "No order found.",
                //     // );
                // }
        }
    }