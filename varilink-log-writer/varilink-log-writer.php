<?php
/**
 * Plugin Name: Varilink Log Writer
 * Description: Makes writing to a debug log file more convenient.
 * Version: 1.0
 * Author: David Williamson @ Varilink Computing Ltd
 * Author URI: https://www.varilink.co.uk
 */

// Protect from being called outside of WordPress
defined ( 'ABSPATH' ) or die ( 'Access Denied' ) ;

// Create varilink_write_log function for convenient output to the debug log
function varilink_write_log (
    $input,        # What it is that we're being asked to write to the log.
    $prefix = NULL # An optional prefix to be applied to the output if provided.
) {

    if ( is_array($input) || is_object($input) ) {
        // Convert to a human readable format.
        $output = print_r($input, TRUE);
    } else {
        $output = $input;
    }

    if ( isset( $prefix ) ) {
        // A prefix has been provided for the log output.
        $output = "$prefix: $output";
    }

    file_put_contents (
        ABSPATH . '/../varilink.log', "$output\n", FILE_APPEND
    );

}
