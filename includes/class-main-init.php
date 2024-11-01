<?php
/**
 * The file that defines the main initialization class. Used for both, administration and frontend sides
 *
 * @author   cesar@shortcodescreator.com
 * @category API
 * @package  SCU_Initialize
 */

namespace SCU\includes;
use SCU\Main as Main;
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Main_Init {	
	private function _setAvailableShortcodes() {
		$sc_path = \SCU\SC_PATH.'*';		
		Main::$availableShortcodes = glob($sc_path, GLOB_ONLYDIR);
		foreach (Main::$availableShortcodes as $key => $shortcode) {
			Main::$availableShortcodes[$key] = basename($shortcode);
		}		
	}
	
	public static function sample_admin_notice() {		
		//echo('<script>alert("hola");</script>');
		if( get_transient( 'fx-admin-notice-example' ) ){
			?>
			<div class="updated notice is-dismissible">
				<p>Thank you for using this plugin! <strong>You are awesome</strong>.</p>
			</div>
			<?php
			/* Delete transient, only display this notice once. */
			delete_transient( 'fx-admin-notice-example' );
		}		
		//wp_die('Bien', E_USER_ERROR );
	}
	public function __construct() {
		$this->_setAvailableShortcodes();
	}
}

$maiInitInstance = new Main_Init();	// Without Singletron Pattern

?>