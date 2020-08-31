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
    class MP_Order_Listing {

        public static function listen(){
            return rest_ensure_response( 
                MP_Order_Listing:: list_open()
            );
        }

        public static function list_open(){
            
            global $wpdb;

            // tables for query
            $table_store = TP_STORES_TABLE;
            $table_prod = TP_PRODUCT_TABLE;
            $table_tp_revs = TP_REVISIONS_TABLE;                               
            $table_ord = MP_ORDERS_TABLE;
            $table_ord_it = MP_ORDER_ITEMS_TABLE;
            $table_mprevs = MP_REVISIONS_TABLE;
            $table_ope = MP_OPERATIONS_TABLE;
            
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
            
            // Step 6: Check if required parameters is passed and numeric
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

            // Step 7: Check if required parameters is passed and numeric
            if ( isset($_POST['odid']) ){
                if ( empty($_POST['odid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $odid = $_POST['odid']; // Validate order id
            }

            // Step 8: Check if required parameters is passed and valid
            if ( isset($_POST['date']) ){
                if ( empty($_POST['date']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                $dt = MP_Globals::convert_date($_POST["wpid"],$_POST["date"]);
                $valdt= MP_Order_Listing::validateDate($dt);   
                if ( !$valdt) {
                    return array(
                        "status" => "failed",
                        "message" => "Date is not in valid format.",
                    );
                }
            }
            
            // Step 9: Check if required parameters is passed and numeric
            if ( isset($_POST['opid']) ){
                if ( empty($_POST['opid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fileds cannot be empty.",
                    );
                }
                
                $colname = "opid";
                $user_id = $_POST['opid'];
                //$opid = $_POST['opid']; // Validate operation id
            }

            // Step 10: Start mysql transaction 
            $sql = "SELECT mp_ordtem.ID AS item_id, ";
            
            if (!($colname === "stid")) 
            {
                $sql .= "(SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT title FROM $table_store  WHERE id = mp_ord.stid )) AS store, ";
            }

            if (!($colname === "wpid")) 
            {
                $sql .= "(SELECT display_name FROM wp_users WHERE id = mp_ord.wpid ) AS customer, ";
            }
            
            if (!($colname === "opid")) 
            {
                $sql .= "(SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT title FROM $table_store  WHERE id = mp_ord.stid )) AS store, ";
                $sql .= "(SELECT display_name FROM wp_users WHERE id = mp_ord.wpid ) AS customer, ";
            }
            
            $sql .="(SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT title FROM $table_prod  WHERE id = mp_ordtem.pdid )) AS product,
                (SELECT child_val FROM $table_tp_revs  WHERE id = ( SELECT price FROM $table_prod  WHERE id = mp_ordtem.pdid )) AS price,
                mp_ordtem.quantity AS quantity,
                (SELECT child_val FROM $table_mprevs WHERE ID = mp_ord.`status`) AS status,
                (SELECT date_created FROM $table_mprevs WHERE ID = mp_ord.`status`)  AS date_created,";
            
            if ( isset($_POST['opid']) )
            {
                $sql .= "(SELECT date_open FROM $table_ope WHERE ID = mp_ord.opid)  AS date_open,
                    (SELECT date_close FROM $table_ope WHERE ID = mp_ord.opid)  AS date_close, ";
            }
            
            $sql .= " mp_ord.date_created AS date_ordered 
                FROM
                    $table_ord_it  AS mp_ordtem
                INNER JOIN 
                    $table_ord  AS mp_ord ON mp_ord.ID = mp_ordtem.odid 
                INNER JOIN 
                        $table_ope AS mp_ope ON mp_ope.ID = mp_ord.opid 
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
        
        //Function for checking if date is valid and invalid format(2020-01-01).
        //Invalid dates such us 2020-02-31 will return false
        public static function validateDate($date, $format = 'Y-m-d H:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }