<?php
/**
 * Plugin Name: Varilink Mailchimp
 * Description: Plugin for REST API integration with Mailchimp.
 * Version: 1.0
 * Author: David Williamson @ Varilink Computing Ltd
 * Author URI: https://www.varilink.co.uk
 */

// Protect this plugin from being called outside of WordPress

defined( 'ABSPATH' ) or die( 'Access Denied' );

// This plugin provides a list of functions that provide access to the Mailchimp
// marketing API, see the API reference here:
// https://mailchimp.com/developer/marketing/api/
//
// Each function below corresponds to one of the API resources listed in that
// reference. The name of the corresponding API resource is given in a comment
// above the start of the function definition.

function varilink_mailchimp_get_member_info(
  $api_key, $api_root, $list_id, $email_address, $query_parms
) {

  // This function integrates with the Mailchimp "Get member info" API.

  // Convert email address to a subscriber hash and set the URL path.
  $subscriber_hash = md5( strtolower( $email_address ) );
  $curlopt_url = "https://$api_root/lists/$list_id/members/$subscriber_hash";

  // Build the query string from the array of query parms
  $query = http_build_query( $query_parms );
  if ( $query ) { $curlopt_url .= "?$query"; }

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $curlopt_url );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
  curl_setopt( $ch, CURLOPT_USERPWD, "anystring:$api_key");
  $body = curl_exec( $ch );
  $rc = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
  curl_close( $ch );

  return [ 'rc' => $rc, 'body' => json_decode( $body, TRUE ) ];

}

// Add or update list member

function varilink_mailchimp_add_or_update_list_member (
  $api_key, $api_root, $list_id, $request
) {

  $subscriber_hash = md5( strtolower( $request [ 'email_address' ] ) );

  $ch = curl_init();
  curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://$api_root/lists/$list_id/members/$subscriber_hash"
  );
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
  curl_setopt( $ch, CURLOPT_USERPWD, "anystring:$api_key");
  $json = json_encode( $request );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $json );
  curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array( 'Content-Type: application/json' )
  );
  $body = curl_exec( $ch );
  $rc = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
  curl_close( $ch );

  return [ 'rc' => $rc, 'body' => json_decode( $body, TRUE ) ];

}
