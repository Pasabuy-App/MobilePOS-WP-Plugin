<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
        * @package hatidpress-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/
  	class HP_Wallet_Info_v2 {

        public static function listen(){
            return rest_ensure_response(
                self::listen_open()
            );
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user["stid"] = $_POST["stid"];

            return $curl_user;
        }

        public static function listen_open(){

            // Initialize WP global variable
            global $wpdb;

            $tbl_transaction = CP_TRANSACTION;
            $tbl_wallet = MP_WALLETS_v2;
            $tbl_payment = MP_PAYMENTS_v2;
            $tbl_order = MP_ORDERS_v2;
            $tbl_operation = MP_OPERATIONS_v2;
            $user = self::catch_post();
            $data = array();

            // Get wallet pubkey of store wallet
                $store_wallet = $wpdb->get_row("SELECT pubkey, assigned_by FROM $tbl_wallet w WHERE stid = '{$user["stid"]}' AND id IN ( SELECT MAX( id ) FROM $tbl_wallet WHERE w.hsid = hsid GROUP BY pubkey ) ");

                if (empty($store_wallet)) {
                    return array(
                        "status" => "failed",
                        "message" => "This store does not have a wallet."
                    );
                }else{
                    $data['pubkey'] = $store_wallet->pubkey;
                    $user_data = get_userdata( $store_wallet->assigned_by );
                    $data['assigned_by'] = $user_data->display_name;
                }
            // End

            $balance = $wpdb->get_row(
                $wpdb->prepare(" SELECT
                    COALESCE(
                        SUM(COALESCE( CASE WHEN recipient = '%s' THEN amount END , 0 ))  -
                        SUM(COALESCE( CASE WHEN sender = '%s' THEN amount END, 0 ))
                        , 0 ) as balance
                        FROM	cp_transaction", $store_wallet->pubkey, $store_wallet->pubkey));

            $data['balance'] = $balance->balance;

            $data['transactions'] = $wpdb->get_results("SELECT
                    *,
                   IF ( (SELECT meta_value FROM wp_usermeta WHERE meta_key = 'avatar' AND `user_id` IN ( SELECT order_by FROM mp_v2_orders WHERE opid IN(SELECT hsid FROM mp_v2_operation WHERE stid = '{$user["stid"]}' ) ) ) is null, 'https://pasabuy.app/wp-content/uploads/2020/10/default-avatar.png',
                        (SELECT meta_value FROM wp_usermeta WHERE meta_key = 'avatar' AND `user_id` IN ( SELECT order_by FROM mp_v2_orders WHERE opid IN(SELECT hsid FROM mp_v2_operation WHERE stid = '{$user["stid"]}' ) ) ) ) as avatar,
                        (SELECT display_name FROM wp_users WHERE ID IN ( SELECT order_by FROM mp_v2_orders WHERE opid IN(SELECT hsid FROM mp_v2_operation WHERE stid = '{$user["stid"]}' ) ) )  as `name`
                FROM
                    mp_v2_payments
                WHERE odid IN ( SELECT pubkey FROM mp_v2_orders WHERE opid IN(SELECT hsid FROM mp_v2_operation WHERE stid = '{$user["stid"]}' ) ) AND method = 'savings';");

            return array(
                "status" => "success",
                "data" => array($data)
            );
        }
    }