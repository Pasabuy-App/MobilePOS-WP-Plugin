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
    class MP_OrdersByStatus {

        public static function listen(){
            return rest_ensure_response( 
                MP_OrdersByStatus:: list_open()
            );
        }

        public static function list_open(){
            
            global $wpdb;

            // variables for query
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
            
            $colname = "wpid";
            $user_id = $_POST['wpid'];
            
            // Step 3: Check if required parameters are passed
            if ( isset($_POST['stage']) ) {
                
            // Step 4: Check if parameters passed are empty
                if ( empty($_POST['stage']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }

            // Step 5: Ensures that `stage` is correct
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
            // Step 8: Check if required parameters is passed and numeric
            if ( isset($_POST['stid']) ){
                if ( empty($_POST['stid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $colname = "stid";
                $user_id = $_POST['stid'];
            }

            // Step 8: Check if required parameters is passed and numeric
            if ( isset($_POST['odid']) ){
                if ( empty($_POST['odid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $odid = $_POST['odid'];
            }

            // Step 9: Check if required parameters is passed and valid
            if ( isset($_POST['date']) ){
                if ( empty($_POST['date']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $dt = TP_Globals::convert_date($_POST["wpid"],$_POST["date"]);
                $valdt= TP_OrdersByDate::validateDate($dt);   
                if ( !$valdt) {
                    return array(
                        "status" => "failed",
                        "message" => "Date is not in valid format!",
                    );
                }
            }

            // Step 10: Start mysql transaction
            $sql = "SELECT
                mp_ordtem.ID AS item_id,
                (SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT title FROM $table_store  WHERE id = mp_ord.stid )) AS store,
                (SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT title FROM $table_prod  WHERE id = mp_ordtem.pdid )) AS product,
                mp_ordtem.quantity AS quantity,
                (SELECT child_val FROM $table_mprevs WHERE ID = mp_ord.`status`) AS status,
                (SELECT date_created FROM $table_mprevs WHERE ID = mp_ord.`status`)  AS date_created,
                mp_ord.date_created AS date_ordered 
            FROM
                $table_ord_it  AS mp_ordtem
            INNER JOIN 
                $table_ord  AS mp_ord ON mp_ord.ID = mp_ordtem.odid 
            WHERE 
                mp_ord.$colname = '$user_id' 
            ";
             
            if($stage != NULL){ // If stage is not null, filter result using stage/status
                $sql .= " AND (SELECT child_val FROM $table_mprevs WHERE ID = mp_ord.`status`) = '$stage'";
            }
            if($odid != NULL){ // If odid is not null, filter result using odid
                $sql .= " AND mp_ord.ID = '$odid'";
            }
            if($dt != NULL){ // If date is not null, filter result using date
                $sql .= " AND DATE(mp_ord.date_created) = '$dt' ";
            }
            
            $result = $wpdb->get_results($sql);
            
            // Step 11: Check if no rows found
            if (!$result) {
                return array(
                    "status" => "success",
                    "message" => "No order found.",
                );
            }
            
            // Step 12: Return result
            return array(
                "status" => "success",
                "data" => $result
            );
            
        }

    }