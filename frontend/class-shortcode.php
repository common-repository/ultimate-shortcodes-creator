<?php
/*************************************************************************
* Shortcode: 	[cmb_example]Content[/cmb_example]
* Desciption: 	Simply Test with enclosed content
* Auhtor:		cmorillas1@gmail.com
* Attributes: 	none
* Return:		string Output html 
**************************************************************************/

 // Using Namespace
namespace SCU\frontend;
use SCU\Main as Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ShortCode {	
	
	private $shortcode;	
	private $shortcode_url;				// URL of the shortcode
	private $shortcode_path;			// Path of the shortcode
	private $shortcode_config_file;		// Shortcode configuration file	
	private $resources_css_dir;			// Path to the shortcode css resources directory
	private $resources_js_dir;			// Path to the shortcode js resources directory

	
	private function _initprops($shortcode) {
		$this->shortcode=$shortcode;
		$this->shortcode_url =  esc_url(\SCU\SC_URL.$shortcode).'/';		
		$this->shortcode_path = \SCU\SC_PATH.$shortcode.DIRECTORY_SEPARATOR;
		$this->shortcodes_config_file = $this->shortcode_path.'scu-config.ini';
		if(!file_exists($this->shortcodes_config_file)) {
			wp_die($this->shortcodes_config_file.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
		}
		$this->config_array = parse_ini_file($this->shortcodes_config_file, true);
		$this->resources_css_dir = wp_normalize_path($this->shortcode_path.'resources/css');
		if(!is_dir($this->resources_css_dir)) {
			wp_die($this->resources_css_dir.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
		}
		$this->resources_js_dir = wp_normalize_path($this->shortcode_path.'resources/js');
		if(!is_dir($this->resources_js_dir)) {
			wp_die($this->resources_js_dir.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
		}
		
		$cssdir = realpath($this->shortcode_path.'resources/css');				
		if(is_dir($cssdir)) {
			$this->resources_css = array_merge(array_diff(scandir($cssdir), array('..', '.')));
		}
		else {
			wp_die($cssdir.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
		}

		$jsdir = realpath($this->shortcode_path.'resources/js');		
		if(is_dir($jsdir)) {
			$this->resources_js = array_merge(array_diff(scandir($jsdir), array('..', '.')));
		}
		else {
			wp_die($jsdir.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);	
		}
	}

	private function _includeHeaderAndFooter() {
		if($this->config_array["type"]["head_or_footer"]) {
			add_action( 'wp_head', function() {			
				$head_file_url =  $this->shortcode_url.'scu-head.html';
				$head_file_path = wp_normalize_path($this->shortcode_path.'scu-head.html');
				if(!file_exists($head_file_path)) {
					wp_die($head_file_path.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
				}
				ob_start();			
				include ($head_file_path);
				$scu_head_output = ob_get_clean();
				echo $scu_head_output;
			
			}, 8);		// 8 is after jquery and before enqueue scripts

			add_action( 'wp_footer', function() {
				$footer_file_url =  $this->shortcode_url.'scu-footer.html';
				$footer_file_path = wp_normalize_path($this->shortcode_path.'scu-footer.html');
				if(!file_exists($footer_file_path)) {
					wp_die($footer_file_path.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
				}
				ob_start();			
				include ($footer_file_path);
				$scu_footer_output = ob_get_clean();
				echo $scu_footer_output;
			}, 10);
		}		
	}

	private function _enqueueResources() {
		//wp_die('enqueue');
		if($this->config_array["type"]["resources_css"]) {			
			foreach ($this->resources_css as $key => $resource_css) {
				$cssfile_url = $this->shortcode_url.'resources/css/'.$resource_css;				
				wp_register_style($resource_css, $cssfile_url, array(), null, 'all');
				wp_enqueue_style($resource_css);
			}
		}		
		if($this->config_array["type"]["resources_js"]) {
			foreach ($this->resources_js as $key => $resource_js) {
				$jsfile_url = $this->shortcode_url.'resources/js/'.$resource_js;
				wp_register_script($resource_js, $jsfile_url, array('jquery'), null, 'all');				
				wp_enqueue_script($resource_js);
			}
		}
	}

	private function _enqueueCssAndJS() {		
		if($this->config_array["type"]["css"]) {
			$css_file_url =  $this->shortcode_url.'scu-style.css';
			$css_file_path = wp_normalize_path($this->shortcode_path.'scu-style.css');
			if(!file_exists($css_file_path)) {
				wp_die($css_file_path.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
			}
			$css_ver = date("ymd-Gis", filemtime($css_file_path));	
			wp_register_style('scu-'.$this->shortcode, $css_file_url, array(), $css_ver, 'all');
			wp_enqueue_style ('scu-'.$this->shortcode);	// enqueue styles css (the extension -css is automatically added)	
		}
		if($this->config_array["type"]["js"]) {
			//$js_file_url = \CMBShortcodes\URL.'/frontend/shortcodeskeleton.php?shortcode='.$this->shortcode;
			$js_file_url = $this->shortcode_url.'scu-script.js';
			$js_file_path = wp_normalize_path($this->shortcode_path.'scu-script.js');
			if(!file_exists($js_file_path)) {
				wp_die($js_file_path.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
			}
			$js_ver = date("ymd-Gis", filemtime($js_file_path));
			//wp_register_script('scu-'.$this->shortcode.'-js',  $js_file_url, array('wp-i18n'), $js_ver, false);
			//wp_register_script('scu-'.$this->shortcode.'-js',  $js_file_url, $this->resources_js, $js_ver, false);
			if($this->config_array["type"]["resources_js"])  {
				wp_register_script('scu-'.$this->shortcode.'-js',  $js_file_url, $this->resources_js, $js_ver, false);
			}
			else {
				wp_register_script('scu-'.$this->shortcode.'-js',  $js_file_url, '', $js_ver, false);	
			}
			wp_set_script_translations('scu-'.$this->shortcode.'-js', 'ultimate-shortcodes-creator', wp_normalize_path($this->shortcode_path. 'languages' ));
			
			add_filter('script_loader_tag', function ($tag, $handle, $src) {				
				if ($handle === 'scu-'.$this->shortcode.'-js') {
					$arr_params = array( 'name' => $this->shortcode );
					$src_query = add_query_arg($arr_params, $src);
					$tag = "<script type='text/javascript' id='scu-shortcode-js' data-name='".$this->shortcode."' src='" . esc_url( $src_query ) ."'></script>"."\n";
				}		
				return $tag;
			}, 10, 3);
		
			wp_enqueue_script ('scu-'.$this->shortcode.'-js');	// enqueue script js. (enqueue avoid include the script and its dependencies files twice)
		/*
			$l10n = array (
					'word1' => __('word1','ultimate-shortcodes-creator' ),
					'word2' => __('word2','ultimate-shortcodes-creator' ),
			);
			// Warning because javascript doesn't allow dash (-) in var name
			// Think in another solution. I think it has no sense localization here
			wp_localize_script('scu-'.$this->shortcode.'-js', 'scu_'.str_replace('-', '__', $this->shortcode), array (	// Send vars to the javascript file
				'l10n' => $l10n,
				)
			);
		*/
		}
	}

	
	public function __construct($shortcode='') {		
		$this->_initprops($shortcode);	
		$this->_includeHeaderAndFooter();
		$this->_enqueueResources();
		$this->_enqueueCssAndJS();		
	}	
}
?>