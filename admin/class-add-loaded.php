<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cesar@shortcodescreator.com
 * @category API
 * @package  SCU_Admin
 */

// We don't use settings API here (https://developer.wordpress.org/plugins/settings/)
// Nothing to be saved in the wp_options table of the wordpress database

// add_action('current_screen', ...) is the first hook where $current_screen, $pagenow, and $plugin_page globals are available 

namespace SCU\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Add_Loaded {
	public static $retrieved_shortcodes;
	//private $shortcodeRedirect;


	public static function loaded() {		// Fired from load-scu_add action hook

		//self::$shortcodeListTable = new Shortcode_List_Table();
		//self::$shortcodeListTable->prepare_items();

		$css_ver = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/css/scu-add.css'));
		$css_file_url = \SCU\URL.'/admin/assets/css/scu-add.css';
		wp_enqueue_style('scu-add-css', $css_file_url, false, $css_ver, 'all');

		$js_ver  = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/js/scu-add.js'));
		$js_file_url = \SCU\URL.'/admin/assets/js/scu-add.js';
		wp_register_script('scu-add-js', $js_file_url, array( 'jquery' ), $js_ver , false);		
		wp_enqueue_script ('scu-add-js');
		wp_localize_script('scu-add-js', 'scu_ajax_add', array(		// Send vars to the javascript file			
			'ajaxurl' => admin_url( 'admin-ajax.php' ),	
			'ajaxNonce' => wp_create_nonce( 'myajax-nonce' ),
		));
		wp_enqueue_script( 'thickbox' );
    	wp_enqueue_style( 'thickbox' );
	}
	
	public function __construct() {
		//add_action('admin_menu', array( $this, 'addMenu'), 10);		// Create sub page inside Settings menu in the admin pannel
	}
}
//$adminManage = new Admin_Manage();	// Without Singletron Pattern
?>
