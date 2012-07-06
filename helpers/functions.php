<?php 

/**
 * Tells WordPress to show Reindexer error
 *
 * @uses get_option()
 * @uses delete_option()
 * @uses add_action()
 * @uses reindexer_error_text()
 *
 * @access public
 * @package WordPress
 * @subpackage Envato Reindexer
 * @since 1.0
 * @author Matt Ward
 *
 * @return void
 */
function show_reindexer_error() 
{
  if ( get_option( 'display_reindexer_error' ) )
  {
    delete_option( 'display_reindexer_error' );
    delete_option( 'has_reindexer_error' );
    add_action( 'admin_notices', reindexer_error_text() );
  }
   add_action( 'admin_notices', reindexer_error_text() );
}

/**
 * Sets Reindexer error text
 *
 * @uses get_bloginfo()
 *
 * @access public
 * @package WordPress
 * @subpackage Envato Reindexer
 * @since 1.0
 * @author Matt Ward
 *
 * @return string
 */
function reindexer_error_text() 
{
  $admin = admin_url('admin.php?page=envato_tweets_settings');
  echo '<div id="message" class="error"><p><strong>Envato Tweets</strong> authentication was not successful, make sure the <a href="'.$admin.'">settings</a> are correct. Please contact <a href="mailto:derek@envato.com?subject=Envato Tweets is broken on '.get_bloginfo('site_name').'!" target="_blank">Derek Herman</a> if the problem persists.</p></div>';
}


?>