<?php
/**
 * Plugin Name: Varilink PayPal
 * Description: Plugin for REST API integration with PayPal.
 * Version: 1.0
 * Author: David Williamson @ Varilink Computing Ltd
 * Author URI: https://www.varilink.co.uk
 */

// Protect from being called outside of WordPress
defined ( 'ABSPATH' ) or die ( 'Access Denied' ) ;

function varilink_paypal_get_access_token (
  $api_domain , $app_client_id , $app_secret
) {

  $ch = curl_init ( ) ;
  curl_setopt ( $ch , CURLOPT_URL , "$api_domain/v1/oauth2/token" ) ;
  curl_setopt ( $ch , CURLOPT_POST , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_HTTPHEADER , array ( 'Accept: application/json' , 'Accept-Language: en_US' ) ) ;
  curl_setopt ( $ch , CURLOPT_USERPWD , "$app_client_id:$app_secret" ) ;
  curl_setopt ( $ch , CURLOPT_POSTFIELDS , 'grant_type=client_credentials' ) ;
  $body = curl_exec ( $ch ) ;
  $rc = curl_getinfo ( $ch , CURLINFO_HTTP_CODE ) ;
  $data = json_decode ( $body ) ;
  curl_close ( $ch ) ;

  return (object) [ 'rc' => $rc, 'data' => $data ] ;

}

function varilink_paypal_capture_payment (
  $api_domain , $access_token , $order_id
) {

  $ch = curl_init ( ) ;
  curl_setopt ( $ch , CURLOPT_URL , "$api_domain/v2/checkout/orders/$order_id/capture" ) ;
  curl_setopt ( $ch , CURLOPT_POST , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_HTTPHEADER , array ( "Authorization: Bearer $access_token" , 'Content-Type: application/json' ) ) ;
  $body = curl_exec ( $ch ) ;
  $rc = curl_getinfo ( $ch , CURLINFO_HTTP_CODE ) ;
  $data = json_decode ( $body ) ;
  curl_close ( $ch ) ;

  return (object) [ 'rc' => $rc, 'data' => $data ] ;

}

function varilink_paypal_create_order (
  $api_domain , $access_token , $request
) {

  $ch = curl_init ( ) ;
  curl_setopt ( $ch , CURLOPT_URL , "$api_domain/v2/checkout/orders" ) ;
  curl_setopt ( $ch , CURLOPT_POST , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_HTTPHEADER , array ( "Authorization: Bearer $access_token" , 'Content-Type: application/json' ) ) ;
  $json = json_encode ( $request ) ;
  curl_setopt ( $ch , CURLOPT_POSTFIELDS , $json ) ;
  $body = curl_exec ( $ch ) ;
  $rc = curl_getinfo ( $ch , CURLINFO_HTTP_CODE ) ;
  $data = json_decode ( $body ) ;
  curl_close ( $ch ) ;

  return (object) [ 'rc' => $rc, 'data' => $data ] ;

}

function varilink_paypal_verify_webhook_signature(
    $api_domain, $access_token, $webhook_id, $notification
) {

    // Get the request headers and body, which contains the notification.
    $headers = getallheaders();

    // Test the webhook signature.
    $ch = curl_init();
    curl_setopt(
        $ch, CURLOPT_URL,
        "$api_domain/v1/notifications/verify-webhook-signature"
    );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt(
        $ch, CURLOPT_HTTPHEADER,
        [
            "Authorization: Bearer $access_token", 'Accept: application/json',
            'Content-Type: application/json'
        ]
    );
    $request = [
        'auth_algo' => $headers['PAYPAL-AUTH-ALGO'],
        'cert_url' => $headers['PAYPAL-CERT-URL'],
        'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'],
        'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'],
        'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'],
        'webhook_id' => "$webhook_id",
        'webhook_event' => $notification
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $request ) );
    $body = curl_exec( $ch );

    // Check the result of testing the webhook signature.
    $rc = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    $data = json_decode ( $body ) ;
    curl_close( $ch );
    return (object) [ 'rc' => $rc, 'data' => $data ] ;

}
