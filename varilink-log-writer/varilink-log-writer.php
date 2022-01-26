<?php
/**
 * Plugin Name: Varilink Log Writer
 * Description: Tool to make the writing of entries to a debug log file more convenient.
 * Version: 1.0
 * Author: David Williamson @ Varilink Computing Ltd
 * Author URI: https://www.varilink.co.uk
 */

// Protect from being called outside of WordPress
defined ( 'ABSPATH' ) or die ( 'Access Denied' ) ;

// Create varilink_write_log function for convenient output to the debug log
if ( ! function_exists ( 'varilink_write_log' ) ) {
    function varilink_write_log ( $log ) {
        if ( is_array ( $log ) || is_object ( $log ) ) {
            error_log ( print_r ( $log , true ) ) ;
        } else {
            error_log ( $log ) ;
        }
    }
}
