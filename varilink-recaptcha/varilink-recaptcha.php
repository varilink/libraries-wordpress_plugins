<?php
/**
 * Plugin Name: Varilink reCAPTCHA
 * Description: Plugin for Integration with Google's reCAPTCHA.
 * Version: 1.0
 * Author: David Williamson @ Varilink Computing Ltd
 * Author URI: https://www.varilink.co.uk
 */

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'google-recaptcha', 'https://www.google.com/recaptcha/api.js'
    );
} );

add_filter( 'script_loader_tag', function ( $tag, $handle ) {
    if ( $handle != 'google-recaptcha' ) { return $tag; }
    return str_replace( 'src', 'async src', $tag );
}, 10, 2 );
