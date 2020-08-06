
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
?>
<?php
	// class DV_Authenticate {

	// 	//Get the user session token string and if nothing, create and return one.
	// 	public static function dv_get_session( $user_id ) {
	// 		//Grab WP_Session_Token from wordpress.
	// 		$wp_session_token = WP_Session_Tokens::get_instance($user_id);

	// 		//Create a session entry unto the session tokens of user with X expiry.
	// 		$expiration = time() + apply_filters('auth_cookie_expiration', 1 * DAY_IN_SECONDS, $user_id, true); //
	// 		$session_now = $wp_session_token->create($expiration);
	
	// 		return $session_now;
	// 	}

	// 	//Authenticate user via Rest Api.
	// 	public static function initialize() {
		
	// 		// Check that we're trying to authenticate
	// 		if (!isset($_POST["UN"]) || !isset($_POST["PW"])) {
	// 			return rest_ensure_response( 
	// 				array(
	// 					"status" => "unknown",
	// 					"message" => "Please contact your administrator. Authentication Unknown!",
	// 				)
	// 			);
	// 		}

	// 		//Listens for POST values.
	// 		$username = sanitize_user($_POST["UN"]);
	// 		$password = $_POST["PW"];

	// 		//Initialize wp authentication process.
	// 		$user = wp_authenticate($username, $password);
			
	// 		//Check for wp authentication issue.
	// 		if ( is_wp_error($user) ) {
	// 			return rest_ensure_response( 
	// 				array(
	// 					"status" => "error",
	// 					"message" => $user->get_error_message(),
	// 				)
	// 			);
	// 		}
	
	// 		return rest_ensure_response( 
	// 			array(
	// 				"status" => "success",
	// 				"data" => array(
	// 					"snky" => DV_Authenticate::dv_get_session($user->ID), 
	// 					"wpid" => $user->ID
	// 					)
	// 				)  
	// 			);
	// 	}
	// }

?>