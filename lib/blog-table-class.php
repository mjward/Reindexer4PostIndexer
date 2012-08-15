<?php

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class ENV_Blog_List_Table extends WP_List_Table {

        var $blogs = array();
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
        
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'blog',     //singular name of the listed records
            'plural'    => 'blogs',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    

    function column_default($item, $column_name){
        switch($column_name){
            case 'indexed_items':
                return $item->$column_name;
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    function column_blog_name($item){
        //Return the title contents
        return sprintf('%1$s <span style="color:silver"></span>',
            /*$1%s*/ $item->blog_name
        );
    }

    function column_last_indexed($item){
        //Return the title contents
        return sprintf('%1$s <span style="color:silver"></span>',
            /*$1%s*/ (!is_null($item->last_indexed)) ? $item->last_indexed : 'never'
        );
    }

    function column_indexed_items($item){
        //Return the title contents
        return sprintf('%1$s <span style="color:silver"></span>',
            /*$1%s*/ (!is_null($item->indexed_items)) ? $item->indexed_items : '-'
        );
    }
    
    function column_cb($item){
        return sprintf(
            '<input type="hidden" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item->blog_id                //The value of the checkbox should be the record's id
        );
    }

    /**
     * Generate the table navigation above or below the table
     *
     * @since 3.1.0
     * @access protected
     */
    function display_tablenav( $which ) {
        if ( 'top' == $which ) {
        ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">

                <div id='reindex-status' class="alignleft actions">
                    <strong>Status:</strong> <span></span>
                </div>
        <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
        ?>

                <br class="clear" />
            </div>
        <?php }
    }

    /**
     * Display the table
     *
     * @since 3.1.0
     * @access public
     */
    function display() {
        extract( $this->_args );

        $this->display_tablenav( 'top' );

        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>" cellspacing="0">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"<?php if ( $singular ) echo " class='list:$singular'"; ?>>
                <?php $this->display_rows_or_placeholder(); ?>
            </tbody>
        </table>
        <?php
                $this->display_tablenav( 'bottom' );
        }
    
    
    function get_columns(){
        $columns = array(
            'cb'        => '', //Render a checkbox instead of text
            'blog_name'     => 'Blog',
            'last_indexed'  => 'Last Indexed',
            'indexed_items'  => 'No. Posts Indexed'
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'domain'     => array('domain',true)
        );
        return $sortable_columns;
    }
    
    
    function prepare_items() {
        
        global $wpdb;

        $per_page = 10;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $data = $wpdb->get_results("  SELECT b.blog_id, b.domain, DATE_FORMAT(ih.last_indexed, '%Y-%m-%d %H:%i') as last_indexed, ih.indexed_items 
                                      FROM wp_blogs b
                                      LEFT JOIN wp_site_index_history ih ON b.blog_id = ih.blog_id
                                      GROUP BY b.blog_id
                                      ORDER BY b.blog_id, ih.last_indexed DESC" );
        
        foreach($data as &$blog){
            $blog->blog_name = get_blog_details( $blog->blog_id, true )->blogname;
        }

        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
    
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}