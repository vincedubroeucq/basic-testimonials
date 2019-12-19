<?php
/*
Plugin Name: Basic Testimonials
Plugin URI:  https://vincentdubroeucq.com
Description: Basic Testimonials allows you to add testimonials to your site via a 'testimonial' custom post type, and a super easy to use [testimonial] shortcode to embed them in your content.
Version:     1.1.3
Author:      Vincent Dubroeucq
Author URI:  https://vincentdubroeucq.com
Text Domain: basic-testimonials
Domain Path: /languages
License:     GPLv2
Licence URI: https://www.gnu.org/licenses/gpl-2.0.html
 
Basic Testimonials is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Sobtware Foundation, either version 2 of the License, or
any later version.
 
Basic Testimonials is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Basic Testimonials. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );



add_action( 'init' , 'basic_testimonials_load_textdomain' );
/**
 * Load the text domain for the plugin
 */
function basic_testimonials_load_textdomain(){
	load_plugin_textdomain( 'basic-testimonials', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}



/**
 * Register the activation and deactivation hooks.
 * Basically register the post type and flush the permalinks on activation and theme switching.
 * Re-flush the permalinks on deactivation
 **/
register_activation_hook( __FILE__, 'basic_testimonials_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
add_action( 'after_theme_switch', 'basic_testimonials_activate' );
function basic_testimonials_activate() {
    basic_testimonials_register_testimonials();
    flush_rewrite_rules();
}



include( 'inc/custom_post_type.php' );
include( 'inc/admin_help.php' );
include( 'inc/display-shortcode.php' );
include( 'inc/form-shortcode.php' );