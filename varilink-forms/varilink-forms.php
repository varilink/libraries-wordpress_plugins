<?php
/*
 * Plugin Name: Varilink Forms
 * Description: Provides various form related tags as shortcodes.
 */

function vari_form_tag ($atts) {

    $atts = shortcode_atts(array(
        'action' => '/wp-admin/admin-post.php',
        'enctype' => 'application/x-www-form-urlencoded',
        'id' => '',
        'method' => 'post'
    ), $atts);

    array_key_exists('action', $_POST)
        ? $action = $_POST['action']
        : $action = "{$atts['action']}";

    $form_tag  = "<form id=\"{$atts['id']}\" action=\"$action\"";
    $form_tag .= " method=\"{$atts['method']}\" enctype=\"{$atts['enctype']}\"";
    if ( defined('THEME_NOVALIDATE') and THEME_NOVALIDATE ) {
        $form_tag .= ' novalidate';
    }
    $form_tag .= '>';

    return $form_tag;

}

add_action('init', function () {

    add_shortcode('vari-form-tag', 'vari_form_tag');

});

function vari_input_tag ($atts) {

    $atts = shortcode_atts([
        'id' => NULL,
        'name' => NULL,
        'type' => 'text',
        'class' => NULL,
        'placeholder' => NULL
    ], $atts);

    $input_tag = '<input';

    foreach ( [ 'id', 'name', 'type', 'placeholder' ] as $var ) {

        if ( isset( $atts[$var] ) ) {
            $$var = $atts[$var];
            $input_tag .= " $var=\"{$$var}\"";
        }

    }

    $class_attr_written = FALSE;

    if ( $atts['name'] ) {

        $name = $atts['name'];

        if ( session_status() === PHP_SESSION_ACTIVE ) {

            if ( array_key_exists( $name, $_SESSION ) ) {
                $input_tag .= " value=\"{$_SESSION[$name]}\"";
                if ( $atts[ 'type' ] != 'hidden' ) {
                    unset( $_SESSION[ $name ] );
                }
            }

            if ( array_key_exists( "{$name}_class", $_SESSION ) ) {
                $class = $_SESSION["{$name}_class"];
                if ( isset( $atts['class' ] ) ) {
                    $class .= " {$atts['class']}";
                }
                $input_tag .= " class=\"$class\"";
                $class_attr_written = TRUE;
                unset( $_SESSION[ "{$name}_class" ] );
            }

        }

    }

    if ( ! $class_attr_written && isset( $atts['class' ] ) ) {
        $input_tag .= " class=\"{$atts['class']}\"";
    }

    $input_tag .= '>';

    return $input_tag;

}

add_action('init', function () {

    add_shortcode('vari-input-tag', 'vari_input_tag');

});

function vari_label_tag ( $atts ) {

    $atts = shortcode_atts( [
        'id' => NULL,
        'name' => NULL,
        'for' => NULL,
        'class' => NULL
    ], $atts );

    $label_tag = '<label';
    foreach ( [ 'id', 'name', 'for', 'class' ] as $var ) {

        if ( isset( $atts[ $var ] ) ) {
            $$var = $atts[ $var ];
            $label_tag .= " $var=\"{$$var}\"";
        }

    }
    $label_tag .= '>';

    if ( $atts[ 'name' ] ) {

        $name = $atts[ 'name' ];

        if (
            session_status() === PHP_SESSION_ACTIVE
            && array_key_exists( $name, $_SESSION )
        ) {
            $label_tag .= $_SESSION[ $name ];
            unset( $_SESSION[ $name ] );
        }
    }

    $label_tag .= '</label>';

    return $label_tag;

}

add_action('init', function () {

    add_shortcode('vari-label-tag', 'vari_label_tag');

});

function vari_nonce_field ($atts) {

    $atts = shortcode_atts([
        'action' => '',
        'name' => ''
    ], $atts);

    if ( defined( strtoupper( $atts['action'] ) . '_CONTEXT' ) ) {
        $action_context
            = constant( strtoupper( $atts['action'] ) . '_CONTEXT' );
    } else {
        $action_context = $atts['action'];
    }

    return wp_nonce_field( $action_context, $atts['name'], TRUE, FALSE );

}

add_action( 'init', function() {

    add_shortcode( 'vari-nonce-field', 'vari_nonce_field' );

} );

function vari_script ( $atts ) {

    $atts = shortcode_atts( [
        'src' => ''
    ], $atts);

    return "<script src='{$atts['src']}'></script>";

}

add_action( 'init', function() {

    add_shortcode( 'vari-script-tag', 'vari_script-tag' );

} );