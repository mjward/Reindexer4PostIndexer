<?php
/*
Plugin Name: Reindex4PostIndexer
Plugin URI: 
Description: Reindex all old posts
Version: 1.0
Author: Matt Ward
Author URI: http://redbak.net.au
*/


/**
 * Define Globals
 *
 * @since 1.0
 */
define( 'ERI_VERSION', '1.0' );
define( 'ERI_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.dirname( plugin_basename( __FILE__ ) ) );
define( 'ERI_PLUGIN_URL', WP_PLUGIN_URL.'/'.dirname( plugin_basename( __FILE__ ) ) );


/**
 * required files
 *
 * @since 1.0
 */
require_once('lib/admin.php');
require_once('helpers/functions.php');

/**
 * Instantiate classes
 *
 * @since 1.0
 */
$reindexer = new Envato_Reindexer();


/**
 * Required add_action() calls
 *
 * @uses add_action()
 *
 * @since 1.0
 */
add_action( 'init', array( $reindexer, 'request_handler' ), 10 );
add_action( 'network_admin_menu', array( $reindexer, 'env_reindexer_menu' ) );
add_action( 'wp_ajax_reindex_blog', array( $reindexer, 'env_ajax_reindex_request' ));
add_action( 'wp_ajax_blog_info_request', array( $reindexer, 'env_ajax_blog_info_request' ));
add_action( 'wp_ajax_init_reindex', array( $reindexer, 'env_ajax_init_reindex' ));
register_activation_hook(__FILE__, array( $reindexer, 'env_reindexer_install'));


?>