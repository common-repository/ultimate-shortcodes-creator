<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cmorillas1@gmail.com
 * @category API
 * @package  Frontend
 */
 
namespace SCU\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class FrontendAjax /*extends \CMBShortcodes\Main */ {
	
	private function registerAjaxHandler($file) {
				//wp_die(); // this is required to terminate immediately and return a proper response
		add_action( 'wp_ajax_scu_ajax_handler', function() use ($file) {			// Executes for users that are logged in
			require_once ($file);
		}, 10, 2);	
		add_action( 'wp_ajax_nopriv_scu_ajax_handler', function() use ($file) {		// Executes for users that are not logged in
			require_once ($file);
		}, 10, 2);	
	}
	
	public function __construct() {		
		
		$shortcode = $_REQUEST["shortcode"];		
		
		$shortcode_config_file = wp_normalize_path(\SCU\SC_PATH.$shortcode.'/scu-config.ini');
		$config_array = parse_ini_file($shortcode_config_file, true);
		if($config_array["type"]["ajax"]) {
			$sc_ajax_handler_file = wp_normalize_path(\SCU\SC_PATH.$shortcode.'/scu-ajax-handler.php');
			if(file_exists($sc_ajax_handler_file)) {
				$this->registerAjaxHandler($sc_ajax_handler_file);		// Must be done before wp hook
			}
			else {
				header( "Content-Type: application/json" );
				echo json_encode($sc_ajax_handler_file.__(' does not exit', 'ultimate-shortcodes-creator'));
				wp_die(); // this is required to terminate immediately and return a proper response
			}	
		}				
	}		
}

$frontendAjaxInstance = new FrontendAjax();	// Without Singletron Pattern
?>