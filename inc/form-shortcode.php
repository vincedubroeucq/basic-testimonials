<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );



add_shortcode( 'testimonial-form', 'basic_testimonials_form_shortcode_html' );
/**
 * Creates a shortcode for displaying the testmonial submission form
 * 
 * @param array   $atts   Attributes added to the shortcode
 **/
function basic_testimonials_form_shortcode_html( $atts ) {

    // Define default attributes and extract the user's shortcode attributes
    $default = array();
    $atts = shortcode_atts( $default, $atts, 'testimonial-form' );

    // If the form has just been submitted, print a preview of the testimonial
    if ( isset( $_GET['preview'] ) ){

        $testimonial_status = get_post_status( $_GET['preview'] );
        
        if ( 'draft' != $testimonial_status ){
            return ;
        }

        $success_message = apply_filters( 'basic_testimonials_submission_success_message', __( 'Thanks for submitting ! Here\'s a preview of your testimonial.', 'basic-testimonials') );
        $html  = '<p class="message">' . esc_html( $success_message ) . '</p>';
        $html .= do_shortcode( '[testimonial id=' . $_GET['preview'] .']' );
        
        return $html;

    }

    // Else print out a basic form.
    $form_message = apply_filters( 'basic_testimonials_form_message', __( 'Please fill in the form below to submit your testimonial. Don\'t forget to include a nice photo !' , 'basic-testimonial' ) );

    $html = '<p class="message">' . esc_html( $form_message ) . '</p>'; 
    $html .= '<form action="' . admin_url( 'admin-post.php' ) . '" method="POST" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="action" value="basic_testimonials_form_submit" />';
    $html .= wp_nonce_field( 'basic_testimonials_form_submit', 'basic_testimonials_form_nonce', true, false );

    $html .= '<p><label for="basic_testimonial_name">' . esc_html__( 'Type in your name and/or website name or job', 'basic-testimonials' ) . '</label>';
    $html .= '<input type="text" name="basic_testimonial_name" placeholder="John Doe, designer at example.com" required /></p>';
 
    $html .= '<p><label for="basic_testimonial_website_url">' . esc_html__( 'Type in your website url', 'basic-testimonials' ) . '</label>';
    $html .= '<input type="url" name="basic_testimonial_website_url" placeholder="http://example.com" /></p>';

    $html .= '<p><label for="basic_testimonial_content">' . esc_html__( 'Type in your testimonial', 'basic-testimonials' ) . '</label>';
    $html .= '<textarea rows=6 name="basic_testimonial_content" placeholder="Type in your comment." required /></textarea></p>';

    $html .= '<p id="basic_testimonial_file_input_wrapper"><label for="basic_testimonial_thumbnail">' . esc_html__( 'Upload a nice photo ! (200px by 200px .png or .jpg)', 'basic-testimonials' ) . '</label><br />';
    $html .= '<input type="file" id="basic_testimonial_thumbnail" name="basic_testimonial_thumbnail" multiple="false" accept="image/png, image/jpeg" /><span></span></p>';

    $html .= basic_testimonials_captcha_field();

    $html .= '<p><input type="submit" value="' . esc_html__( 'Submit', 'basic-testimonials' ) . '" /></p>';
    $html .= '</form>';

    return $html;
}



add_action( 'admin_post_basic_testimonials_form_submit', 'basic_testimonials_process_form' );
add_action( 'admin_post_nopriv_basic_testimonials_form_submit', 'basic_testimonials_process_form' );
/* 
 * Process the data submitted by the testimonial submit form on the front end.
 */
function basic_testimonials_process_form() {

    if ( ! isset( $_POST['basic_testimonials_form_nonce'] ) || ! wp_verify_nonce( $_POST['basic_testimonials_form_nonce'], 'basic_testimonials_form_submit' ) ){
        wp_die( 'There was an error.', 'basic-testimonials' );
    }

    // Check the captcha first
    $is_captcha_correct = basic_testimonials_check_captcha();
    
    if ( ! $is_captcha_correct ){
        wp_die( 'There was an error.', 'basic-testimonials' );
    }

    // Get our form details
    $name = isset( $_POST['basic_testimonial_name'] ) ? sanitize_text_field( $_POST['basic_testimonial_name'] ) : '';
    $website_url = isset( $_POST['basic_testimonial_website_url'] ) ? esc_url_raw( $_POST['basic_testimonial_website_url'] ) : '';
    $content = isset( $_POST['basic_testimonial_content'] ) ? wp_kses_post( $_POST['basic_testimonial_content'] ) : '';
    
    // Create a testimonial
    $testimonial_id = wp_insert_post( array(
        'post_content' => $content,
        'post_title' => $name,
        'post_type' => 'testimonial',
        'meta_input' => array(
            'basic_testimonials_website_url' => $website_url,
        ),
    ) );

    // Check the filetype of the submitted file, upload it and create an attachment.
    if ( wp_check_filetype( $_FILES['basic_testimonial_thumbnail']['name'] ) && getimagesize( $_FILES['basic_testimonial_thumbnail']['tmp_name'] ) ) {
                
        $attachment_id = media_handle_upload( 'basic_testimonial_thumbnail', $testimonial_id );
        
        // If everything worked fine, set the thumbnail.
        if ( $testimonial_id && ! is_wp_error( $attachment_id ) ){
            set_post_thumbnail( $testimonial_id, $attachment_id );
        }

    }
        
    // Redirect after submitting the form.
    if ( isset( $_POST['_wp_http_referer'] ) ){
        wp_redirect( $_POST['_wp_http_referer'] . '?preview=' . $testimonial_id );
        exit; 
    } else {
        wp_redirect( site_url() );
        exit;
    }
    
}



/**
 * Prints the simple captcha field
 **/
function basic_testimonials_captcha_field(){
    
    $term1 = rand(1, 10);
    $term2 = rand(1, 10);
    $result = $term1 + $term2;
    $token = md5( 'captcha' . $result );

    $html  = '<input id="basic_testimonials_form_captcha_token" type="hidden" name="basic_testimonials_form_captcha_token" value="' . $token . '" />';
    $html .= '<p><label for="basic_testimonials_form_captcha_answer">' . esc_html__( 'Please show you\'re human.', 'basic-testimonials' ) . '</label><br />';
    $html .= $term1 . ' + ' . $term2 . ' = <input type="text" name="basic_testimonials_form_captcha_answer" required /></p>';
 
    return $html;
}



/**
 * Checks the captcha is correct
 *
 **/
function basic_testimonials_check_captcha(){

    if ( ! isset( $_POST['basic_testimonials_form_captcha_token'], $_POST['basic_testimonials_form_captcha_answer'] ) ){
        return false;
    }

    $expected_result = $_POST['basic_testimonials_form_captcha_token'];
    $user_result = md5( 'captcha' . $_POST['basic_testimonials_form_captcha_answer'] );

    return hash_equals( $expected_result, $user_result );

}