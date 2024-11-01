<?php
/***************************************************************************
* Template for a PHP Ajax Response
* Author:		cesar@shortcodescreator.com
* Description:	Compose json response
* Return:		json html
****************************************************************************/
	
if(!defined('ABSPATH')) {
	die('Please don\'t access this file directly.');
}

// Check if the call is from the right place
if ( ! wp_verify_nonce(  $_POST['security'], 'scu-ajax-nonce' ) ) {
	die ( 'Busted!' );
}

global	$wp_version;

$response  = "WP Version: ".$wp_version;

header( "Content-Type: application/json" );

echo json_encode($response);
wp_die(); // this is required to terminate immediately and return a proper response

?>