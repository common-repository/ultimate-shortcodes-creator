<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cmorillas1@gmail.com
 * @category API
 * @package  Frontend
 */
 
namespace SCU\frontend;
use SCU\Main as Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Frontend /*extends \CMBShortcodes\Main */{

	//Main::$installedShortcodes				// Array of installed shortcodes
	protected $shortcodes_in_page = array();	// Array of shortcodes that are present in current page (including widgets)
	protected $shortcodes_in_theme = array();	// Array of shortcodes that are always enabled for their use in the theme
	protected $activated_shortcodes = array();	// Array of shortcodes objects that are in page or theme, installed and config.ini-enabled
	protected $added_shortcodes = array();		// Array of shortcodes objects
		
	
	private function _set_variables() {
		// Set $this->shortcodes_in_page
		// Check shortcodes in current post
		global $post;
		$present_shortcodes = $this->_presentShortcodes($post->post_content, 'scu');
		$this->shortcodes_in_page = array_merge($this->shortcodes_in_page, $present_shortcodes);

		// Check shortcodes in any text widget
		$text_widgets = get_option('widget_text');

		foreach ($text_widgets as $key => $text_widget) {
			//wp_die(var_dump($text_widget));
			if(isset($text_widget['text'])) {
				$present_shortcodes = $this->_presentShortcodes($text_widget['text'], 'scu');
				$this->shortcodes_in_page = array_merge($this->shortcodes_in_page, $present_shortcodes);
			}			
		}

		// Check shortcodes in any custom html widget
		$html_widgets = get_option('widget_custom_html');
		foreach ($html_widgets as $key => $html_widget) {
			if(isset($html_widget['content'])) {
				$present_shortcodes = $this->_presentShortcodes($html_widget['content'], 'scu');
				$this->shortcodes_in_page = array_merge($this->shortcodes_in_page, $present_shortcodes);
			}
		}

		// Set $this->shortcodes_in_theme
		foreach (Main::$availableShortcodes as $key => $shortcode) {			
			$ini_file = wp_normalize_path(\SCU\SC_PATH.$shortcode.'/scu-config.ini');
			if(file_exists($ini_file)) {
				$config_array = parse_ini_file($ini_file, true);				
				$theme_use = $config_array["config"]["theme_use"];
				if($theme_use) {					
					$this->shortcodes_in_theme[] = $shortcode;
				}
			}
			else {
				wp_die($ini_file.__(' does not exit', 'ultimate-shortcodes-creator'));
			}
		}

		// Set $this->activated_shortcodes
		$detected_shortcodes = array_merge($this->shortcodes_in_page, $this->shortcodes_in_theme);
		$potencial_shortcodes = array_intersect(Main::$availableShortcodes, $detected_shortcodes);
		foreach ($potencial_shortcodes as $key => $shortcode) {			
			$ini_file = wp_normalize_path(\SCU\SC_PATH.$shortcode.'/scu-config.ini');
			if(file_exists($ini_file)) {
				$config_array = parse_ini_file($ini_file, true);
				$enabled = $config_array["config"]["enabled"];
				if($enabled) {
					$this->activated_shortcodes[] = $shortcode;
				}
			}
			else {
				wp_die($ini_file.__(' does not exit', 'ultimate-shortcodes-creator'));
			}
		}
	}

	private function _presentShortcodes( $content, $tag ) {		// Own version of the original has_shortcode() in shortcodes.php
		$shortcodes_in_page = array();
		if ( false === strpos( $content, '[' ) ) {
			return array();
		}
		preg_match_all( '/' . get_shortcode_regex([$tag]) . '/', $content, $matches, PREG_SET_ORDER );		
		if ( empty( $matches ) ) {
			return array();
		}
		foreach ( $matches as $shortcode ) {
			if ( $tag === $shortcode[2] ) {
				$attr = shortcode_parse_atts( $shortcode[3] );
				if(isset($attr['name'])) {
					//$this->shortcodes_in_page[] = $attr['name'];
					$shortcodes_in_page[] = $attr['name'];
				}
			} 
			if ( ! empty( $shortcode[5] )) {	// && _presentShortcodes( $shortcode[5], $tag ) ) {
				$this->_presentShortcodes($shortcode[5], $tag);
			}
		}
		return $shortcodes_in_page;
	}

	public function addShortcodes() {
		// !!! Markup is only viewed from the post content in the database wp_die(get_the_content('','', $id));
    	// wp_die(get_the_content('','', 2));

		$this->_set_variables();
		if(empty($this->activated_shortcodes)) {
			return;
		}

		// Shortcode(s) has to be created

		$this->enqueueCommonScripts();

		add_shortcode( 'scu', array( $this, 'shortcode_handler' ) );
		
		//$this->activated_shortcodes = array_unique(array_merge($this->shortcodes_in_theme, $this->active_shortcodes), SORT_REGULAR);

		require_once(\SCU\PATH.'frontend/class-shortcode.php');	// See also wp_normalize_path()
		foreach ($this->activated_shortcodes as $key => $shortcode) {
			$this->added_shortcodes[] = new Shortcode($shortcode);
		}
	}

	public function enqueueCommonScripts() {		
		// Enqueue common styles and scripts
		$js_ver = '1.0';
		$js_file_url = \SCU\URL.'frontend/assets/js/scu-common.js';		
		wp_register_script('scu-common-js', $js_file_url, array( 'jquery' ), $js_ver , false);
		wp_enqueue_script ('scu-common-js');
		wp_localize_script('scu-common-js', 'scu_common', array(		// Send vars to the javascript file			
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'scu_url'	=> \SCU\SC_URL,
			'ajaxNonce' => wp_create_nonce( 'scu-ajax-nonce' ),
			'requests' => $_REQUEST
		));		
	}

	public function shortcode_handler( $atts = [], $content = null ) {		
		static $scu_count = 0;				// Increments when a new shortcode is executed in the page
		$scu_count++;
		//$atts = shortcode_atts(array('param1' => 'default1','param2' => 'default2'), $atts);	//set defaults values if needed
		//wp_die(!isset($atts["name"]));
		if(!isset($atts["name"])) {
			return($scu_count.'<span>'.__('attribute "name" does not exit', 'ultimate-shortcodes-creator').'</span>');			
		}
		$shortcode = $atts["name"];
		if(!in_array($shortcode, $this->activated_shortcodes )) {
			return('<span>[scu name="'.$shortcode.'"] '.__('is not installed or enabled', 'ultimate-shortcodes-creator').'</span>');
		}
		$resources_url = \SCU\SC_URL.$shortcode.'/resources/assets/';
		$scu_output = '<div class="scu-shortcode sc-'.$shortcode.'"';
		if($atts) {
			foreach($atts as $key => $att) {
				$scu_output .= ' data-'.$key.'="'.$att.'"';
			}
		}
		$scu_output .= '>';
		//$scu_output .= '<div class="scu-content" style="display:none;">'.$content.'</div>';
		$scu_shortcode_config_file = wp_normalize_path(\SCU\SC_PATH.$shortcode.'/scu-config.ini'); // File exists because it has been already checked before
		$scu_config_array = parse_ini_file($scu_shortcode_config_file, true);
		if($scu_config_array["type"]["html"]) {
			$scu_html_file_path = wp_normalize_path(\SCU\SC_PATH.$shortcode.'/scu-html.php');
			if(!file_exists($scu_html_file_path)) {
				wp_die($scu_html_file_path.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
			}
			ob_start();			
			include ($scu_html_file_path);
			$scu_output .= ob_get_clean();			
		}
		else {
			$scu_output .= $content;
		}
	
		$scu_output .= "</div>";		
	
		return do_shortcode($scu_output);
	}
	
	public function __construct() {

		// Check if url/?safe-mode=true in case the site brokes
		if(isset($_GET["safe-mode"]) && (strtolower($_GET["safe-mode"])=='true')) {
			return;
		}
		
   		add_action('wp', array( $this, 'addShortcodes'), 12);	// 'wp' is the earliest action when $post is available

	}		
}

$frontendInstance = new Frontend();	// Without Singletron Pattern
?>