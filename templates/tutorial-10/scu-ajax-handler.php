<?php	
if(!defined('ABSPATH')) {
	die('Please don\'t access this file directly.');
}

// Check if the call is from the right place
if ( ! wp_verify_nonce(  $_POST['security'], 'scu-ajax-nonce' ) ) {
	die ( 'Busted!' );
}

global	$wp_version;

//$content = (($_REQUEST["content"]!=='')) ? $_REQUEST["content"] : null;
$shortcode = $_REQUEST["shortcode"];
$email = (isset($_REQUEST["email"])) ? $_REQUEST["email"] : null;
$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
$scu_referer = (isset($_SERVER['HTTP_SCU_REFERER'])) ? $_SERVER['HTTP_SCU_REFERER'] : null;

// Send a email to check php server ajax code works succesfully
$name = (isset($_POST["name"])) ? sanitize_text_field($_POST["name"]) : null;
$email = (isset($_POST["email"])) ? sanitize_email($_POST["email"]) : null;
$message = (isset($_POST["message"])) ? sanitize_textarea_field($_POST["message"]) : null;

$to = $email;
$subject = "New Mail from: ".$referer;
$body = $scu_referer;
$headers[] = 'Content-Type: text/html; charset=UTF-8';
//$headers[] = 'From: Me Myself <me@example.net>';
$email_response = wp_mail( $to, $subject, $body, $headers );

// Build the response
$response = '<p><strong>AJAX CALL MADE SUCCESFULLY !</strong></p>';
$response .= "WP Version: ".$wp_version;
$response .= "<br>Shortcode: ".$shortcode;
$response .= "<br>Email: ".$email;
$response .= "<br>Header Referer: ".$referer;
$response .= "<br>Header SCU-Referer: ".$scu_referer;
$response .= "<br>";
 
header( "Content-Type: application/json" );
echo json_encode($response);
wp_die(); // this is required to terminate immediately and return a proper response

?>