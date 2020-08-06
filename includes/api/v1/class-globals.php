<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	
	/** 
        * @package mobilepos-wp-plugin
        * @version 0.1.0
	*/
?>
<?php
  	class MP_Globals {
        public static function date_stamp(){
            return date("Y-m-d h:i:s");
		}
		
		// verify if datavice plugin is activated
		public static function verifiy_datavice_plugin(){
            if(!class_exists('DV_Verification')){
                return false;
            }else{
                return true;
            }
        }
    }
