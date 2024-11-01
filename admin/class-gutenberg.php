<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cesar@shortcodescreator.com
 * @category API
 * @package  SCU_Admin
 */

namespace SCU\admin;
use SCU\Main as Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin_Gutenberg {
	static $config = array();		// Array of gutenberg shortcodes config parameters
	
	private static function _set_config_var($available_shortcodes, $orphaned_shortcodes) {
	
		foreach ($orphaned_shortcodes as $key => $shortcode) {
			// Recreate attributes as inputcontrol
			$regex_pattern = get_shortcode_regex(array('scu'));
			preg_match_all('/'.$regex_pattern.'/s', $shortcode["innerHTML"], $matches);			
			$sc_atts = shortcode_parse_atts($matches[3][0]);
			$content = $matches[5][0];
			$attributes = array();
			$i = 0;
			foreach (array_slice($sc_atts, 1) as $key => $att) {				
				$attributes[$i]['name'] = $key;
				$attributes[$i]['description'] = 'caca';
				$attributes[$i]['type'] = 'inputcontrol';
				$attributes[$i]['params'] = '|';
				$i = $i + 1;
			}
			self::$config[] = [
				'shortcode'	=> $shortcode["name"],
				'description' => 'Orphaned Shortcode',
				'icon'	=> 'thumbs-down',
				'has_content' => ($content) ? "1" : "0",
				'defaultcontent' => '',
				'attributes' => $attributes
			];
		}	

		foreach ($available_shortcodes as $key => $shortcode) {			
			$ini_file = wp_normalize_path(\SCU\SC_PATH.$shortcode.'/scu-config.ini');
			if(!file_exists($ini_file)) {
				wp_die($ini_file.__(' does not exit', 'ultimate-shortcodes-creator'));
			}
			$config_array = parse_ini_file($ini_file, true);
			$x = 1;
			$attributes = array(); 
			while(isset($config_array["attribute-".$x])) {
				$attributes[] = $config_array["attribute-".$x];
				$x++;
			}			
			self::$config[] = [
				'shortcode'	=> $shortcode,
				'description' => $config_array["block-general"]["description"],
				'icon'	=> $config_array["block-general"]["icon"],
				'has_content' => $config_array["block-general"]["has_content"],
				'defaultcontent' => $config_array["block-general"]["defaultcontent"],
				'attributes' => $attributes
			];
		}
	}

	public static function add_blocks($available_shortcodes, $orphaned_shortcodes) {		
		self::_set_config_var($available_shortcodes, $orphaned_shortcodes);		

		$css_ver = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/css/scu-gutenberg.css'));
		$css_file_url = \SCU\URL.'/admin/assets/css/scu-gutenberg.css';
		wp_register_style('scu-gutenberg-css', $css_file_url, false, $css_ver, 'all');		

		$js_ver  = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/js/scu-gutenberg.js'));		
		$js_file_url = \SCU\URL.'admin/assets/js/scu-gutenberg.js';
		wp_register_script('scu-gutenberg', $js_file_url, array( 'wp-blocks', 'wp-components', 'wp-i18n', 'wp-shortcode', 'wp-element', 'wp-editor', 'wp-block-editor' ), $js_ver, false);
		wp_localize_script('scu-gutenberg', 'scu_gutenberg_config', array(		// Send vars to the javascript file			
			'config' => self::$config,			
		));		
		
		register_block_type( 'ultimate-shortcodes-creator/all', array (
        	'editor_script' => 'scu-gutenberg',		// Must much the wp_register_script name
        	//'style' => '',		// css for the frontend ( a .css must be registerd with wp_register_style)
        	'editor_style' => 'scu-gutenberg-css',	// css for the admin ( a .css must be registerd with wp_register_style)
        	//shortcode_parse_atts()
        	
        	'attributes'      => [
        		'scu_atts3'  => [
            		'type'  => 'array',
            		'items' => ["4", "5"] //shortcode_parse_atts()
            	]
        	] 
        	    	
    	));
    	wp_set_script_translations( 'scu-gutenberg', 'ultimate-shortcodes-creator' );
    	
	}
}
?>
