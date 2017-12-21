<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );



add_action( 'init', 'basic_testimonials_register_testimonials' );
/**
 * Register a custom post type called "Testimonial", available in the admin area.
 * There is no archive pages for the post type, and it is excluded from search.
 */
function basic_testimonials_register_testimonials() {
    $labels = array(
        'name'                  => _x( 'Testimonials', 'Post type general name', 'basic-testimonials' ),
        'singular_name'         => _x( 'Testimonial', 'Post type singular name', 'basic-testimonials' ),
        'menu_name'             => _x( 'Testimonials', 'Admin Menu text', 'basic-testimonials' ),
        'name_admin_bar'        => _x( 'Testimonial', 'Add New on Toolbar', 'basic-testimonials' ),
        'add_new'               => __( 'Add New', 'basic-testimonials' ),
        'add_new_item'          => __( 'Add New Testimonial', 'basic-testimonials' ),
        'new_item'              => __( 'New Testimonial', 'basic-testimonials' ),
        'edit_item'             => __( 'Edit Testimonial', 'basic-testimonials' ),
        'view_item'             => __( 'View Testimonial', 'basic-testimonials' ),
        'all_items'             => __( 'All Testimonials', 'basic-testimonials' ),
        'search_items'          => __( 'Search Testimonial', 'basic-testimonials' ),
        'parent_item_colon'     => __( 'Parent Testimonial:', 'basic-testimonials' ),
        'not_found'             => __( 'No Testimonial found.', 'basic-testimonials' ),
        'not_found_in_trash'    => __( 'No Testimonials found in Trash.', 'basic-testimonials' ),
        'featured_image'        => _x( 'Testimonial Author Picture', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'basic-testimonials' ),
        'set_featured_image'    => _x( 'Set Author Picture', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'basic-testimonials' ),
        'remove_featured_image' => _x( 'Remove Author Picture', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'basic-testimonials' ),
        'use_featured_image'    => _x( 'Use as Author Picture', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'basic-testimonials' ),
        'archives'              => _x( 'Testimonial archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'basic-testimonials' ),
        'insert_into_item'      => _x( 'Insert into testimonial', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'basic-testimonials' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this testimonial', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'basic-testimonials' ),
        'filter_items_list'     => _x( 'Filter testimonials list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'basic-testimonials' ),
        'items_list_navigation' => _x( 'Testimonials list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'basic-testimonials' ),
        'items_list'            => _x( 'Testimonials list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'basic-testimonials' ),
    );
 
    $args = array(
        'labels'             => $labels,
        'description'        => 'A few quotes from happy clients',
        'menu_icon'          => 'dashicons-format-quote',
        'public'             => true,
        'has_archive'        => false,
        'exclude_from_search' => true,
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
    );
 
    register_post_type( 'testimonial', $args );
}



add_filter( 'manage_testimonial_posts_columns' , 'basic_testimonials_add_column' );
/**
 * Add an 'Author Picture' and a 'Testimonial Content' columns in the testimonial list page in the admin area.
 *
 * @param   array   $columns   An array of the standard column names for default posts.
 * @return  array   $columns   Modified array with two new columns.
 */
function basic_testimonials_add_column( $columns ) {
    $columns = array_merge(
        $columns,
        array( 
            'thumb'   => __( 'Author Picture', 'basic-testimonials' ), 
            'content' => __( 'Testimonial Content', 'basic-testimonials' ),
        )
    );
    return $columns;
}



add_action( 'manage_testimonial_posts_custom_column', 'basic_testimonials_custom_column_data' );
/**
 * Add the relevant data to the new columns declared.
 * @param   string   $name   The name of the column being processed.
 */
function basic_testimonials_custom_column_data( $name ) {
    if ( 'thumb' === $name ) {
        the_post_thumbnail( array( 75, 75 ) );
    }
    if ( 'content' === $name ){
        the_excerpt();
    }
}



add_action( 'admin_print_styles-edit.php', 'basic_testimonials_testimonial_list_css' );
/**
 * Adjust the styling of the columns in the testimonial list table by adding CSS in the head.
 */
function basic_testimonials_testimonial_list_css() {
    global $typenow;

    if ( 'testimonial' != $typenow ) {
        return;
    }
    ?>
    <style>
        .column-thumb {
            width: 100px;
        }
        .thumb img {
            border-radius: 50%;
        }
    </style>
    <?php
}



/**
 * Adds a metabox for the user to enter the name of the site and its URL.
 **/
add_action( 'add_meta_boxes', 'basic_testimonials_register_metabox' );
function basic_testimonials_register_metabox() {
    add_meta_box( 
        'basic_testimonials_author_site',                    // ID 
        __( 'Website information', 'basic-testimonials' ),   // Title 
        'basic_testimonials_metabox_html',                   // Callback 
        'testimonial',                                       // Screen 
        'side'                                               // Context
    );
}



/**
 * Meta box display callback.
 * @param WP_Post   $post   Current post object.
 */
function basic_testimonials_metabox_html( $post ) {
    // Create a nonce for security
    wp_nonce_field( 'basic_testimonials_save', 'basic_testimonials_nonce' );

    // Retrieve author's site URL
    $website_url = get_post_meta( $post->ID, 'basic_testimonials_website_url', true );

    ?>
        <label><?php esc_html_e( 'Website URL: ' );?></label><br />
        <input type="url" id="basic_testimonials_website_url" name="basic_testimonials_website_url" value="<?php echo esc_attr( esc_url( $website_url ) ); ?>" />
    <?php
}


 
add_action( 'save_post_testimonial', 'basic_testimonials_metabox_save' );
/**
 * Save meta box content.
 * @param int   $post_id   Post ID
 */
function basic_testimonials_metabox_save( $post_id ) {
    
    // Check user's permissions.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check nonce
    if ( isset( $_POST['basic_testimonials_nonce'] ) && wp_verify_nonce( $_POST['basic_testimonials_nonce'], 'basic_testimonials_save' ) ) {
        $new_website_url = ( isset( $_POST['basic_testimonials_website_url'] ) ) ? esc_url_raw( $_POST['basic_testimonials_website_url'] ) : '';
        update_post_meta( $post_id, 'basic_testimonials_website_url', $new_website_url );
    } 
}