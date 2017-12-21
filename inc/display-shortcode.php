<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );



add_shortcode( 'testimonial', 'basic_testimonials_shortcode_html' );
/**
 * Creates a shortcode for displaying a single testmonial
 * 
 * @param array   $atts   Attributes added to the shortcode
 **/
function basic_testimonials_shortcode_html( $atts ) {

    // Define default attributes and extract the user's shortcode attributes
    $default = array( 'id' => null, 'name' => null );
    $atts = shortcode_atts( $default, $atts, 'testimonial' );

    // Prepare the argument for the custom query
    $args = array(
        'post_type' => 'testimonial',
        'posts_per_page' => 1,
        'orderby' => 'rand',
    );

    if ( $atts['id'] ){
        $args['p'] = absint( $atts['id'] );
        unset( $args['orderby'] );
    }

    if ( $atts['name'] ){
        $args['name'] = $atts['name'];
        unset( $args['orderby'] );

        // If there's a name and an ID, just use the name.
        if ( $atts['id'] ){
            unset( $args['p'] );
        }
    }

    // Initialize our query, and display the result.
    $wp_query = new WP_Query( $args );
    $html = '';

    if ( $wp_query->have_posts() ) :
        while ( $wp_query->have_posts() ) : $wp_query->the_post();

            $avatar = get_the_post_thumbnail( null, 'thumbnail' );
            $website_url = get_post_meta( get_the_ID(), 'basic_testimonials_website_url', true );
            
            if ( ! empty( $website_url ) ) {
                $cite = '<a href="' . esc_url( $website_url ) .  '" title="' . the_title_attribute( array( 'echo' => false ) ) . '" >' . get_the_title() .'</a>';
            } else {
                $cite = get_the_title();
            }

            $html .= '<blockquote class="testimonial" cite="' . esc_url( $website_url ) . '">';
            $html .= $avatar;
            $html .= '<div class="testimonial-content"><p>' . get_the_content() . '</p>';
            $html .= '<footer class="testimonial-footer"><cite>';
            $html .= $cite;
            $html .= '</cite></footer>';
            $html .= '</div></blockquote>';
            
        endwhile; 
    endif;

    wp_reset_postdata();

    return $html;
}



/**
 * Add styles for the testimonial blocs.
 **/
add_action( 'wp_enqueue_scripts', 'basic_testimonials_styles' );
function basic_testimonials_styles(){
    wp_enqueue_style( 'basic-testimonials-styles', esc_url( plugins_url( '../css/basic-testimonials-style.css', __FILE__ ) ) );
}



/*
 * Make sure our testimonial shortcode works in text widgets
 */
if ( ! has_filter( 'widget_text', 'do_shortcode' ) ) {
    add_filter( 'widget_text', 'do_shortcode' );
}