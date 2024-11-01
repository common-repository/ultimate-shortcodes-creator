<?php
// Avoid access directly to the .php file
if(!defined('ABSPATH')) {
	die('Please don\'t access this file directly.');
}

// Check if the call is made from the right place
if ( ! wp_verify_nonce(  $_POST['security'], 'scu-ajax-nonce' ) ) {
	die ( 'Busted!' );
}

$response = '';
/*
	Your code can be inserted here
*/

header( "Content-Type: application/json" );
echo json_encode($response);
wp_die(); // this is required to terminate immediately and return a proper response

?>