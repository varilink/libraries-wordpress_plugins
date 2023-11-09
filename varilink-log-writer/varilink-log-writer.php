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
    $input,          # What to write to the log
    $divider = FALSE # Whether to output a divider before writing the output
) {

    if ( is_array($input) || is_object($input) ) {
        // Convert to a human readable format.
        $output = print_r($input, TRUE);
    } else {
        $output = $input;
    }

    if ( $divider ) {
        file_put_contents (
            ABSPATH . '/../varilink.log',
            str_repeat( '-', 80 ) . "\n",
            FILE_APPEND
        );
    }

    file_put_contents (
        ABSPATH . '/../varilink.log', "$output\n", FILE_APPEND
    );

}
