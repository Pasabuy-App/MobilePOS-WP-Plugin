<?php

    /*
        Plugin Name: MobilePOS WP Plugin
        Plugin URI: http://www.bytescrafter.net/projects/mobilepos
        Description: mobilepos WP Plugin is a user management for your application or game users.
        Version: 0.1.0
        Author: Bytes Crafter
        Author URI:   https://www.bytescrafter.net/about-us
        Text Domain:  mobilepos-wp-plugin

        * @package      mobilepos-wp-plugin
        * @author       Bytes Crafter

        * @copyright    2020 Bytes Crafter
        * @version      0.1.0

        * @wordpress-plugin
        * WC requires at least: 2.5.0
        * WC tested up to: 5.4.2
    */

    #region WP Recommendation - Prevent direct initilization of the plugin.
    if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
    if ( ! function_exists( 'is_plugin_active' ) ) 
    {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    #endregion

    //Important config files and plugin updates.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/config.php' );
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/update.php' );

     //Make sure to create required mysql tables.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/dbhook.php' );

    //Includes assets if page is defined.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/core/assets.php' );

    //Include the REST API of USocketNet to be accessible.
    include_once ( plugin_dir_path( __FILE__ ) . '/includes/api/routes.php' );

?>