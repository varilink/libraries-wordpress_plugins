<?php
/**
 * Plugin Name: Varilink Forms
 * Description: Provides various form related tags as WordPress shortcodes.
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

function vari_transaction( $atts ) {

    $atts = shortcode_atts( [
        'action' => NULL
    ], $atts );

    $input_tag = '<input type="hidden" name="transaction"';

    if ( array_key_exists( 'transaction', $_GET ) ) {

        # We have been passed a transaction id as a query parameter. Test if it
        # is the transaction id for this instance of a transaction input tag.

        $transaction = $_GET[ 'transaction' ];

        if ( isset( $atts[ 'action' ] ) ) {
            $action = $atts[ 'action' ];
            if ( substr( $transaction, 0, strlen( $action ) ) === $action ) {
                $input_tag .= " value=\"$transaction\"";
            } 
        } else {
            $input_tag .= " value=\"$transaction\"";
        }

    }

    $input_tag .= '>';

    return $input_tag;

}

add_action('init', function () {

    add_shortcode('vari-transaction', 'vari_transaction');

});

function vari_input_tag ($atts) {

    // This shortocde function outputs an input tag by combining what is passed
    // as shortcode attributes with anything stored from the previous steps in
    // the transaction if one has already been created. The transaction can hold
    // values or validation errors for the input tag, which are stored as class
    // names.

    // Register the valid shortcode attributes and their default values.
    $atts = shortcode_atts( [
        'checked' => NULL, # used only when type is radio or checkbox
        'class' => NULL,
        'copy' => NULL, # does not correspond to a HTML attribute - see below 
        'id' => NULL,
        'name' => NULL,
        'placeholder' => NULL,
        'type' => 'text',
        'value' => NULL,
    ], $atts);

    // Start the input tag.
    $input_tag = '<input';

    foreach ( [ 'id', 'name', 'type', 'placeholder', 'value' ] as $var ) {

        // If these shortcode attributes are set then they always map directly
        // to the value of an input tag attribute value in the HTML that we
        // output. They are never affected by what's stored in the transaction.
        // Thus we can use them immediately in the output HTML.

        if ( isset( $atts[$var] ) ) {
            $$var = $atts[$var];
            $input_tag .= " $var=\"{$$var}\"";
        }

    }

    $class_attr_written = FALSE; // We have not yet written a class attribute.

    if ( isset( $atts[ 'name' ] ) ) {

        // The input is named, which means that it will be included in form
        // submission. Hence there may be information stored about it in the
        // transaction, if one has already been created.

        $name = $atts[ 'name' ]; # convenience variable

        if ( array_key_exists( 'transaction', $_GET ) ) {

            // We have a transaction in process so it's possible that this input
            // may have a value stored in the transation that we need to restore
            // from.

            // Create a couple of convenience, transaction related variables.
            $transaction = $_GET[ 'transaction' ]; # transaction id
            $params = $_SESSION[ $transaction ]; # transaction params

            if ( array_key_exists( $name, $params ) ) {

                // The transaction contains a parameter with the same name as
                // this input. When that is the case that parameter contains the
                // value that has previously been assinged to this input and so
                // must be restored to this input.

                $value = $params[ $name ]; # convenience variable

                if (
                    $atts[ 'type' ] === 'radio' && $atts[ 'value' ] === $value
                ) {

                    // This input is a radio button that the transaction's
                    // stored value for the input matches, so set the button to
                    // checked.
                    $input_tag .= ' checked';

                } elseif ( $atts[ 'type' ] === 'checkbox' && $value === 'on' ) {

                    // This input is a checkbox for which the transaction's
                    // stored value is "on", so set the checkbox to checked.
                    $input_tag .= ' checked';

                } else {

                    // The transaction has the value to restore for an input
                    // that is neither a radio button nor a checkbox, so simply
                    // set the value of this input to the stored value.
                    $input_tag .= " value=\"$value\"";

                }

            } else {

                if (
                    isset( $atts[ 'copy' ] ) &&
                    array_key_exists( $atts[ 'copy' ], $params )
                ) {

                    $value = $params[ $atts[ 'copy' ] ];
                    $input_tag .= " value=\"$value\"";

                }

            }

            if ( array_key_exists( "{$name}_class", $params ) ) {

                // The transaction holds value(s) for the class attribute of
                // this input. These usually serve to highlight the displayed
                // attribute, indicating that it has failed validation. Merge
                // this/these classes with the ones provided via the shortcode
                // attribute if there are any.

                $class = $params[ "{$name}_class" ];
                if ( isset( $atts[ 'class' ] ) ) {
                    $class .= " {$atts['class']}";
                }
                $input_tag .= " class=\"$class\"";
                $class_attr_written = TRUE;

            }

        } else {

            // There is no stored transaction yet.

            if ( $atts['type'] === 'radio' && isset( $atts['checked'] ) ) {

                // This is a radio button that the shortcode attributes say
                // should be checked by default. Since there is no transaction
                // with a stored value to tell us otherwise, set it to checked.

                $input_tag .= ' checked';

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

    if ( isset( $atts[ 'name' ] ) ) {

        $name = $atts[ 'name' ];

        if ( array_key_exists( 'transaction', $_GET ) ) {

            $transaction = $_GET[ 'transaction' ]; # transaction id
            $params = $_SESSION[ $transaction ]; # transaction params

            if ( array_key_exists( $name, $params ) ) {
                $label_tag .= $params[ $name ];
            }

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