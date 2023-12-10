<?php
/**
 * Plugin Name: Varilink reCAPTCHA
 * Description: Plugin for integrating reCAPTCHA into a website.
 * Version: 1.0
 * Author: David Williamson @ Varilink Computing Ltd
 * Author URI: https://www.varilink.co.uk
 */

// Protect from being called outside of WordPress
defined ( 'ABSPATH' ) or die ( 'Access Denied' );

function vl_recaptcha_enqueue_script () {

    wp_enqueue_script(
        'recaptcha',
        'https://www.google.com/recaptcha/api.js?render='
        . VL_RECAPTCHA_SITE_KEY . '&#038;ver=3.0',
        [],
        NULL
    );

}

add_action( 'wp_enqueue_scripts', 'vl_recaptcha_enqueue_script' );

function vl_recaptcha_verify_user_response ( $secret ) {

    $ch = curl_init();

    // Determine the remote IP, taking into account whether we are behind a
    // proxy or not. This assumes my standard setup whereby HTTP_X_REAL_IP is
    // set for proxied requests. Note that when a proxy is used in my standard
    // setup then 'REMOTE_ADDR' is set to 'HTTP_X_REAL_IP' in the WordPress
    // config file but checking again here means that this function could be
    // used outside of WordPress if required.
    if ( array_key_exists( 'HTTP_X_REAL_IP', $_SERVER ) ) {
        $remoteip = $_SERVER[ 'HTTP_X_REAL_IP' ];
    } else {
        $remoteip = $_SERVER[ 'REMOTE_ADDR' ];
    }

    $query = http_build_query( [
        'secret' => VL_RECAPTCHA_SECRET_KEY,
        'response' => $_POST[ 'g-recaptcha-response' ],
        'remoteip' => $remoteip
    ] );

    curl_setopt(
        $ch, CURLOPT_URL,
        "https://www.google.com/recaptcha/api/siteverify?$query"
    );

    curl_setopt( $ch, CURLOPT_POST, TRUE );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

    $body = curl_exec( $ch );
    $rc = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    curl_close( $ch );

    return [ 'rc' => $rc, 'body' => json_decode( $body, TRUE ) ];

}
