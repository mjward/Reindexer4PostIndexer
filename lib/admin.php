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
 * Blog Table class
 */
require('blog-table-class.php');

class Envato_Reindexer {

 /**
   * Add administration menus
   *
   * @uses add_menu_page()
   * @uses add_action()
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return void
   */
	function env_reindexer_menu() {
		$env_reindexer_admin_page = add_menu_page('Reindexer', 'Post Reindexer', 'manage_options', 'env_reindexer', array($this, 'env_reindexer_options'),   plugins_url('reindexer/assets/images/icon.png'));

		// Add Menu Items
    	add_action("admin_print_styles-$env_reindexer_admin_page", array( $this, 'env_reindexer_admin_load' ) );
	}

 /**
   * Load Scripts & Styles
   *
   * @uses wp_enqueue_style()
   * @uses wp_enqueue_script()
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return void
   */
 	function env_reindexer_admin_load() {
		// Enqueue Styles
		wp_enqueue_style('et-css', ERI_PLUGIN_URL.'/assets/css/style.css', false, false, 'screen');
		wp_enqueue_style('noty-css', ERI_PLUGIN_URL.'/assets/css/jquery.noty.css', false, false, 'screen');
		//wp_enqueue_style('noty-default_theme-css', ERI_PLUGIN_URL.'/assets/css/noty_theme_default.css', false, false, 'screen');
		wp_enqueue_style('noty-twitter_theme-css', ERI_PLUGIN_URL.'/assets/css/noty_theme_twitter.css', false, false, 'screen');
    
 		// Enqueue Scripts
 		
		wp_enqueue_script('eri-js', ERI_PLUGIN_URL.'/assets/js/scripts.js', array('jquery'), false);
		wp_enqueue_script('noty-js', ERI_PLUGIN_URL.'/assets/js/jquery.noty.js', array('jquery'), false);
      wp_enqueue_script('asyncqueue-js', ERI_PLUGIN_URL.'/assets/js/jquery.asyncqueue.js', array('jquery'), false);
	}

 /**
   * Draw the UI of the plugin
   *
   * @uses current_user_can()
   * @uses ENV_Blog_List_Table
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return void
   */
	function env_reindexer_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap" id="reindexer">
	      	<div class="icon32" id="icon-tools"><br></div>
	      	<h2>Post Reindexer</h2>
            
            <div id='reindex-notifications'></div>

	      	
	      	<?php 

		    $testListTable = new ENV_Blog_List_Table();
		    $testListTable->prepare_items();

    ?>
    <div>
	    <form id='reindex-form' method='post'>
	    	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	   		<?php $testListTable->display(); ?>
	   
			<?php if ( function_exists('wp_nonce_field') && wp_nonce_field('reindex-blog') ) { ?>
	    	 <input type="hidden" name="execute_reindex" value="doit" />
	    	 <?php } ?>
		</form>

		<div id='form-actions'>
         <input type='button' value='Reindex All' class='button-primary' id='reindex-all' name='reindex-all' onclick='return false;' /> 
		</div>

      <div id='reindex-log'>
         <h3>Reindexer Log</h3>
         <ul></ul>
      </div>

		
	</div>	

  	</div>
	<?php
	}


/**
   * AJAX request for info about the blog its about to index
   *
   * @uses check_ajax_referer()
   * @uses env_reindex_blog()
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return JSON representative of the outcome of the reindex
   */
   function env_ajax_blog_info_request(){

      //check_ajax_referer( 'reindex-blog' );

      $blog_id = (isset($_POST['blog_id'])) ? mysql_real_escape_string($_POST['blog_id']) : 0;

      $data = get_blog_details( $blog_id, true );

      echo json_encode(get_object_vars($data));
      exit;
   }

 /**
   * AJAX request to reindex blog
   *
   * @uses check_ajax_referer()
   * @uses env_reindex_blog()
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return JSON representative of the outcome of the reindex
   */
	function env_ajax_reindex_request(){
		
		$data = array();

		check_ajax_referer( 'reindex-blog' );

		$blog_id = (isset($_POST['blog_id'])) ? mysql_real_escape_string($_POST['blog_id']) : 0;

		if($blog_id) {
			$data = $this->env_reindex_blog($blog_id);
		} else {
			$data['status'] = 'fail';
			$data['reason'] = 'invalid blog_id';
		}
		
		echo json_encode($data);
      exit;
	}

 /**
   * Reindexes blog
   *
   * @uses switch_to_blog()
   * @uses get_posts()
   * @uses wp_update_post()
   * @uses wp_cache_flush()
   * @uses restore_current_blog()
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return Array representative of the outcome of the reindex
   */
	function env_reindex_blog($blog_id){
      global $wpdb;
		if( switch_to_blog($blog_id, true) ){

         $failed_posts = array();

			$args = array( 'numberposts' => 99999, 'post_type' => 'post' );
			$posts = get_posts( $args );
			wp_cache_flush();

			foreach($posts as $p){
				$args = array( 'ID' => $p->ID );
				if(!$post_id = wp_update_post( $args )){
               $failed_posts[] = $p->ID;
            };

				wp_cache_flush();
			}

			restore_current_blog();
			
         $last_indexed = date('Y-m-d H:i');

			$data['status'] = 'success';
			$data['posts'] = count($posts);
         $data['timestamp'] = $last_indexed;

         if(!empty($failed_posts)){
            $data['failed_posts'] = $failed_posts;
         }

         $record = array();
         $wpdb->query("INSERT INTO {$wpdb->prefix}site_index_history (blog_id, indexed_items, last_indexed) VALUES({$blog_id}, {$data['posts']}, '$last_indexed') 
                       ON DUPLICATE KEY UPDATE blog_id=VALUES(blog_id), indexed_items=VALUES(indexed_items),last_indexed=VALUES(last_indexed)" );

		} else {
			$data['status'] = 'fail';
			$data['reason'] = 'no blog exists with this id'; 
		}

		return $data;
	}

 /**
   * Initialises database by deleting all old indexed data
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return JSON all blogs
   */
   function env_ajax_init_reindex(){
      global $wpdb;

      $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}site_posts");
      $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}site_terms");
      $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}site_term_relationships");
      $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}site_term_taxonomy");
      $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}term_counts");

      $blogs = $wpdb->get_results("SELECT blog_id, domain 
                                   FROM {$wpdb->prefix}blogs");

      foreach($blogs as &$blog){
         $data = get_blog_details( $blog->blog_id, true );
         $blog->blog_name = $data->blogname;
         $blog->post_count = $data->post_count;
      }

      echo json_encode($blogs);
      exit;

   }

 /**
   * Activate plugin
   *
   * @uses dbDelta()
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return void
   */
	function env_reindexer_install(){

		global $wpdb;

      if( !is_multisite() ) { 
         exit("This plugin requires a Wordpress Multisite enabled installation"); 
      }

      if( !is_plugin_active_for_network('post-indexer/post-indexer.php') ){
         exit("This plugin relys on the WPMU-DEV Post Indexer Plugin. Ensure this is installed and activated before attempting to activate the reindexer"); 
      }

		$table_name = $wpdb->prefix . 'site_index_history';

		$sql = 'CREATE TABLE IF NOT EXISTS `' . $table_name . '` (
				`index_history_id` int(11) NOT NULL AUTO_INCREMENT,
				`blog_id` int(11) NOT NULL,
				`indexed_items` int(11) NOT NULL,
				`last_indexed` datetime NOT NULL,
				PRIMARY KEY  (`index_history_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

	}

 /**
   * Determines when to run the reindex based on request
   *
   * @access public
   * @package WordPress
   * @subpackage Envato Reindexer
   * @since 1.0
   * @author Matt Ward
   *
   * @return void
   */
  function request_handler() {

  	if ( isset($_POST[ 'execute_reindex' ]) && $_POST[ 'execute_reindex' ] == 'doit' ) {

      // Check Referer
      check_admin_referer( 'env_reindexer');
      
      // Read posted value
      $blogs     = $_POST[ 'blogs' ];

      print_r($blogs);
      exit;

      // Save posted values
      if ( !empty($blogs) ) {
        
        $reindex_status = $this->env_reindex_posts($blogs);
        
      }
      // redirect
      wp_redirect( admin_url( 'admin.php?page=env_reindexer&status=w00p') );
    
    }
    
  }

}

?>