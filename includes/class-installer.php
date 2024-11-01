<?php
/**
 * The file that defines the core plugin class for activation and deactivation
 *
 * @author   cmorillas1@gmail.com
 * @category API
 * @package  Installer
 */

namespace SCU\includes;
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Installer {
	// It is impossible to use add_action() or add_filter() type calls
	// See: https://codex.wordpress.org/Function_Reference/register_activation_hook#Process_Flow
	public static function activate() {
		// Check if the shortcode already exist.
		if ( shortcode_exists( 'scu' ) ) {
   			// The [scu] shortcode exist.
   			//deactivate_plugins( \SCU\PLUGIN_PHPFILE );
   			die('Plugin NOT activated: [scu] shortcode already exists');
   			//trigger_error('[sc-ultimate] already exists. There is a conflict with another plugin', E_USER_ERROR);
   		}
		
		//wp_die(__CLASS__, E_USER_ERROR );
		/* Create transient data */
		//set_transient( 'fx-admin-notice-example', true, 5 );
		//add_action( 'admin_notices', array('\SCU\includes\Installer','sample_admin_notice'));
		$url = "http://www.shortcodescreator.com/API/check.php";

		$data = array (
			'license'	=>	true,
		);
		$post_args = array (
			'body' => $data,
		);
		$response = wp_remote_post($url, $post_args);
		if ( is_wp_error( $response ) ) {			
			define ('SCU\LICENSE', 'FALSE' );		
		}
		else {
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
			define ('SCU\LICENSE', $response_body );
		}
	}

	public static function deactivate() {
		$url = "http://www.shortcodescreator.com/API/check.php";

		$data = array (
			'license'	=>	false,
		);
		$post_args = array (
			'body' => $data,
		);
		$response = wp_remote_post($url, $post_args);
		if ( is_wp_error( $response ) ) {			
			// Nothing to do for now.
		}
		else {
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
			// Nothing to do for now. Release license in the future
		}
	}

	public static function uninstall() {
		WP_Filesystem();
		global $wp_filesystem;		
		$wp_filesystem->rmdir(\SCU\SC_PATH, true);
	}
}

?>