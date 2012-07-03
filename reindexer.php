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
 * Instantiate class
 *
 * @since 1.0
 */
$reindexer = new Envato_Reindexer();

/**
 * Required filter & action calls
 *
 * @since 1.0
 */
//add_filter('the_content', array( $tuts_ads, 'tuts_ads_process' ) );
add_action( 'network_admin_menu', array( $reindexer, 'env_reindexer_menu' ) );

class Envato_Reindexer {

	function env_reindexer_menu() {
		add_menu_page('Reindexer', 'Envato', 'manage_options', 'myplugin/myplugin-index.php', array($this, 'env_reindexer_options'),   plugins_url('reindexer/images/icon.png'));
	}

	function env_reindexer_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div class="wrap">';
		echo '<p>Here is where the form would go if I actually had options.</p>';
		echo '</div>';
	}
}
?>