<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );



add_filter( 'enter_title_here', 'basic_testimonials_title_placeholder', 10, 2 );
/**
 * Change the placeholder for the title of the testimonial editor.
 *
 * @param  string           $title_placeholder   The original placeholder title
 * @param  WP_Post object   $post                The current post object
 * @return string           $title_placeholder   Modified placeholder title
 */
function basic_testimonials_title_placeholder( $title_placeholder, $post ){
    if ( 'testimonial' == get_post_type() ){
        $title_placeholder = __( 'Name of the author', 'basic-testimonials' );
    }
    return $title_placeholder;
}



add_filter( 'post_updated_messages', 'basic_testimonials_updated_messages' );
/**
 * Change the messages displayed when you save a testimonial.
 *
 * @param   array   $messages   The original update messages.
 * @return  array   $messages   The modified update messages for the 'testimonial' post type.
 */
function basic_testimonials_updated_messages( $messages ) {
	global $post;
    
	if ( 'testimonial' === get_post_type() ) {

        $post_type   = get_post_type();
		$post_url    = get_permalink();
		$preview_url = esc_url( add_query_arg( 'preview', 'true', $post_url ) );
		$post_url    = esc_url( $post_url );

		$messages[ $post_type ] = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __( 'Testimonial updated. <a href=\'%s\'>View testimonial</a>', 'basic-testimonials' ), $post_url ),
			 2 => __( 'Custom field updated.' ),
			 3 => __( 'Custom field deleted.' ),
			 4 => __( 'Testimonial updated.', 'basic-testimonials' ),
			 /* translators: %s: date and time of the revision */
			 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Testimonial restored to revision from %s', 'basic-testimonials' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __( 'Testimonial published. <a href=\'%s\'>View testimonial</a>', 'basic-testimonials' ), $post_url ),
			 7 => __( 'Testimonial saved.', 'basic-testimonials' ),
			 8 => sprintf( __( 'Testimonial submitted. <a target=\'_blank\' href=\'%s\'>Preview testimonial</a>', 'basic-testimonials' ), $preview_url ),
			 9 => sprintf( __( 'Testimonial scheduled for: <strong>%1$s</strong>. <a target=\'_blank\' href=\'%2$s\'>Preview testimonial</a>', 'basic-testimonials' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), $post_url ),
			10 => sprintf( __( 'Testimonial draft updated. <a target=\'_blank\' href=\'%s\'>Preview testimonial</a>', 'basic-testimonials' ), $preview_url ),
		);

	}

	return $messages;
}



add_filter( 'bulk_post_updated_messages', 'basic_testimonials_bulk_update_messages', 10, 2 );
/**
 * Change the messages displayed when you bulk update testimonials.
 * 
 * @param   array   $bulk_messages   The original update messages.
 * @param   int     $bulk_counts     Number of updated items.
 * @return  array   $bulk_messages   Array of modified messages.
 */
function basic_testimonials_bulk_update_messages( $bulk_messages, $bulk_counts ) {
	$bulk_messages['testimonial'] = array(
		'updated'   => _n( '%s testimonial updated.', '%s testimonials updated.', $bulk_counts['updated'], 'basic-testimonials' ),
		'locked'    => _n( '%s testimonial not updated, somebody is editing it.', '%s testimonials not updated, somebody is editing them.', $bulk_counts['locked'], 'basic-testimonials' ),
		'deleted'   => _n( '%s testimonial permanently deleted.', '%s testimonials permanently deleted.', $bulk_counts['deleted'], 'basic-testimonials' ),
		'trashed'   => _n( '%s testimonial moved to the Trash.', '%s testimonials moved to the Trash.', $bulk_counts['trashed'], 'basic-testimonials' ),
		'untrashed' => _n( '%s testimonial restored from the Trash.', '%s testimonials restored from the Trash.', $bulk_counts['untrashed'], 'basic-testimonials' ),
	);

	return $bulk_messages;
}



add_action( 'admin_head', 'basic_testimonials_help_tab' );
/*
 * Add help messages of the testimonial editing screen
 */
function basic_testimonials_help_tab() {
  global $current_screen;

  if ( 'testimonial' != get_post_type() ) {
    return;
  }

  $args = array(
    'id'       => 'basic_testimonials_help_tab', 
    'title'    => __( 'Testimonial help', 'basic-testimonials' ),
    'callback' => 'basic_testimonials_help_tab_html'
  );
  
  $current_screen->add_help_tab( $args );
}

// Callback that actually renders the help tab content 
function basic_testimonials_help_tab_html(){
    $html  = '';
    $html .= '<p><strong>' . esc_html__( 'Here is some help to fill in this testimonial', 'basic-testimonials' ) . '</strong></p>';
    $html .= '<ul>';
    $html .= '<li>' . esc_html__( 'Title field: Enter the name of your client. This text will serve as an anchor for the link, so you can also add your client\'s website name, seperated from his name by a comma.', 'basic-testimonials' ) . '</li>';
    $html .= '<li>' . esc_html__( 'Content editor: Enter the content of your client\'s comment.', 'basic-testimonials' ) . '</li>';
    $html .= '<li>' . esc_html__( 'Website URL: Enter the URL of your client\'s website. Leave blank if you do not want to link to the wild.', 'basic-testimonials' ) . '</li>';
    $html .= '</ul>';
    echo $html;
}