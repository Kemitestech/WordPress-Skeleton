<?php

/*
  Plugin Name: Events Planner PRO
  Plugin URI: http://wpEventsPlanner.com
  Description: A comprehensive event management plugin that contains support for multiple event types, payments, custom forms, and etc.

  Version: 2.1.0.3

  Author: Abel Sekepyan
  Author URI: http://wpEventsPlanner.com

  Copyright (c) 2015 Abel Sekepyan  All Rights Reserved.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

if ( !function_exists( 'add_action' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

add_action( 'plugins_loaded', 'start_events_planner_pro' );

define( "EPL_FULL_PATH", plugin_dir_path( __FILE__ ) );
define( "EPL_BASENAME", plugin_basename( __FILE__ ) );
define( "EPL_REL_PATH", dirname( plugin_basename( __FILE__ ) ) );
define( "EPL_APPLICATION_FOLDER", EPL_FULL_PATH . 'application/' );
define( "EPL_FULL_URL", plugin_dir_url( __FILE__ ) );
define( "EPL_EMAIL_TEMPLATES_FOLDER", EPL_FULL_PATH . 'application/views/email/' );
define( "EPL_EMAIL_TEMPLATES_URL", plugin_dir_url( __FILE__ ) . 'application/views/email/' );

define( "EPL_PLUGIN_VERSION", '2.1.0.3' );
define( "EPL_PLUGIN_DB_VERSION", '1.0' );
define( "EPL_PLUGIN_DB_DELIM", '|~|' );
define( "EPL_CONTROLLER_FOLDER", EPL_FULL_PATH . 'application/controllers/' );
define( "EPL_SYSTEM_FOLDER", EPL_FULL_PATH . 'system/' );

define( "EPL_DEBUG", false );


function start_events_planner_pro() {
    static $processed = false;

    if ( $processed )
        return;

    require_once(dirname( __FILE__ ) . '/application/config/config.php');

//Starting the output buffer here for paypal redirect to work
    //Will see if conflict with other plugins.  If so, will use init hook
    ob_start();

    //error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);
    //ini_set( 'display_errors', 1 );

    require_once EPL_SYSTEM_FOLDER . 'epl-base.php'; //load super object
    require_once EPL_SYSTEM_FOLDER . 'epl-init.php'; //load init class
    require_once EPL_SYSTEM_FOLDER . 'epl-router.php'; //load router class
    require_once EPL_SYSTEM_FOLDER . 'epl-controller.php'; //load parent controller
    require_once EPL_SYSTEM_FOLDER . 'epl-model.php'; //load parent model
    //initialize the plugin (menus, load js, css....), the base super object

    $init = new EPL_Init;

    add_action( 'init', array( $init, 'route' ) );
    //add_action( 'template_redirect', array( $init, 'route' ) );
    add_filter( 'single_template', array( $init, 'route' ), 11 );
    /**
     * Shortcode
     */
    add_shortcode( 'events_planner', array( $init, 'shortcode_route' ) );

    /*
     * ajax
     */
    add_action( 'wp_ajax_events_planner_form', array( $init, 'route' ) );
    add_action( 'wp_ajax_nopriv_events_planner_form', array( $init, 'route' ) );

    /*
     * widgets, in pro version
     */

    add_action( 'widgets_init', array( $init, 'register_widgets' ) );

    /*
     * cron processors
     */
    add_action( 'epl_hourly_cron', 'epl_hourly_cron' );

    $processed = true;
}

/*
 * activation hooks, need to be called early
 */

register_activation_hook( __FILE__, 'epl_activate' );
register_deactivation_hook( __FILE__, 'epl_deactivate' );


function epl_activate() {

    require_once(dirname( __FILE__ ) . '/application/config/install_defaults.php');


    update_option( 'events_planner_version', EPL_PLUGIN_VERSION );
    update_option( 'events_planner_active', 1 );

    global $default_vals, $wpdb;

    foreach ( $default_vals as $key => $data ) {
        /* check for option then update if necessary */
        if ( !get_option( $key ) ) {
            add_option( $key, $data );
        }
    }
    //need to make WP aware of EP CPTs before flushing rules. Otherwise won't work.

    require_once('system/epl-base.php');
    require_once('system/epl-init.php');
    require_once('application/helpers/common-helper.php');
    $init = new EPL_Init;
    $init->create_post_types();
    flush_rewrite_rules();


    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $init->register_tables();

    $tables = $init->tables();

    foreach ( $tables as $table ) {
        dbDelta( $table );
    }

    $uploads = wp_upload_dir();

    $epl_dir = $uploads['basedir'] . '/epl_uploads';

    if ( !is_dir( $epl_dir ) && is_writable( $uploads['basedir'] ) ) {
        mkdir( $epl_dir, 0755 );

        file_put_contents( $uploads['basedir'] . '/epl_uploads/.htaccess', 'deny from all' );
        file_put_contents( $uploads['basedir'] . '/epl_uploads/index.html', '' );
    }

    //wp_schedule_event( time() + 100, 'hourly', 'epl_hourly_cron' );
}


function epl_deactivate() {
    //wp_clear_scheduled_hook( 'epl_hourly_cron' );
    update_option( 'events_planner_active', 0 );
    flush_rewrite_rules();
}


function epl_log( $log = '', $message = '' ) {
    return null;
}