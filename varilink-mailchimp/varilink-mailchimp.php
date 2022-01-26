<?php
/**
 * Plugin Name: Varilink Mailchimp
 * Description: Plugin for REST API integration with Mailchimp.
 * Version: 1.0
 * Author: David Williamson @ Varilink Computing Ltd
 * Author URI: https://www.varilink.co.uk
 */

// Protect from being called outside of WordPress
defined ( 'ABSPATH' ) or die ( 'Access Denied' ) ;

function varilink_mailchimp_information_about_a_list_member (
  $api_key , $api_root , $list_id , $email , $fields
) {

  if ( function_exists ( 'varilink_write_log' ) ) {
    varilink_write_log (
      'function varilink_mailchimp_information_about_a_list_member called'
    ) ;
  }

  $subscriber_hash = md5 ( strtolower ( $email ) ) ;

  $ch = curl_init ( ) ;
  curl_setopt (
    $ch ,
    CURLOPT_URL ,
    "https://$api_root/lists/$list_id/members/$subscriber_hash?fields=$fields"
  ) ;
  curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_USERPWD , "anystring:$api_key") ;
  $body = curl_exec ( $ch ) ;
  $rc = curl_getinfo ( $ch , CURLINFO_HTTP_CODE ) ;
  if ( $rc === 200 ) {
    $response = json_decode ( $body ) ;
  } else {
    $response = FALSE ;
  }
  curl_close ( $ch ) ;

  return $response ;

}

function varilink_mailchimp_add_or_update_list_member (
  $api_key , $api_root , $list_id , $request
) {

  $subscriber_hash = md5 ( strtolower ( $request [ 'email_address' ] ) ) ;

  $ch = curl_init ( ) ;
  curl_setopt (
    $ch ,
    CURLOPT_URL ,
    "https://$api_root/lists/$list_id/members/$subscriber_hash"
  ) ;
  curl_setopt ( $ch , CURLOPT_CUSTOMREQUEST , 'PUT' ) ;
  curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , TRUE ) ;
  curl_setopt ( $ch , CURLOPT_USERPWD , "anystring:$api_key") ;
  $json = json_encode ( $request ) ;
  curl_setopt ( $ch , CURLOPT_POSTFIELDS , $json ) ;
  curl_setopt (
    $ch ,
    CURLOPT_HTTPHEADER ,
    array ( 'Content-Type: application/json' )
  ) ;
  $body = curl_exec ( $ch ) ;
  $rc = curl_getinfo ( $ch , CURLINFO_HTTP_CODE ) ;
  $response = json_decode ( $body ) ;
  curl_close ( $ch ) ;

  return $response ;

}
