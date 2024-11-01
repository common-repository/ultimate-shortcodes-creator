<?php	
if(!defined('ABSPATH')) {
	die('Please don\'t access this file directly.');
}

// Check if the call is from the right place
if ( ! wp_verify_nonce(  $_POST['security'], 'scu-ajax-nonce' ) ) {
	die ( 'Busted!' );
}
global	$wp_version;
$name = (isset($_POST["name"])) ? sanitize_text_field($_POST["name"]) : null;	// If each one in post
//$email = (isset($_POST["email"])) ? sanitize_email($_POST["email"]) : null;	// If each one in post
//$form = (isset($_POST["form"])) ? sanitize_text_field($_POST["form"]) : null;
$form = array();
parse_str($_POST["form"], $form);

$name = sanitize_text_field($form["visitor_name"]);
$email = sanitize_email($form["visitor_email"]);
$phone = sanitize_text_field($form["visitor_phone"]);
$position = sanitize_text_field($form["visitor_position"]);
$message = sanitize_text_field($form["visitor_message"]);

/******* First send and email ************************************************************/
$to = (isset($_POST["atts"]["email"])) ? sanitize_email($_POST["atts"]["email"]) : null;
$subject = "New Mail from: ".$email;
$body = $message;
$headers[] = 'Content-Type: text/html; charset=UTF-8';
//$headers[] = 'From: Me Myself <me@example.net>';

$response = wp_mail( $to, $subject, $body, $headers );

/*************** Next, save to a table in the same wordpress database *********************/
/******************************************************************************************/
global $wpdb;
$table_name = $wpdb->prefix."mycustomtable";
$charset_collate = $wpdb->get_charset_collate();
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );		// Needed for dbDelta() function

// Check if table exists, and create one if not exists
if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {	// Create the table if it doesn't already exist
	$sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	name tinytext,
	email tinytext,
	phone tinytext,
	position text,
	message varchar(55) DEFAULT '',
	PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );
}
// Next, Insert the field
$return = $wpdb->insert( 
	$table_name, 
	array( 
		'name' => $name, 
		'email' => $email,
		'phone' => $phone, 
		'position' => $position,
		'message' => $message, 
	) 
);

header( "Content-Type: application/json" );
echo json_encode($return);

wp_die(); // this is required to terminate immediately and return a proper response

?>