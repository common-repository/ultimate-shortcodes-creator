<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cmorillas1@gmail.com
 * @category API
 * @package  CMB_Admin
 */

// We don't use settings API here (https://developer.wordpress.org/plugins/settings/)
// Nothing to be saved in the wp_options table of the wordpress database

// add_action('current_screen', ...) is the first hook where $current_screen, $pagenow, and $plugin_page globals are available 

namespace SCU\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Edit_Add_Scripts {
	public static function add_scripts($hook) {
		if ( $hook != 'shortcodes_page_scu_edit_shortcode' ) {
        	return;
    	}

    	// Adds dashicon picker. Thanks to https://github.com/bradvin/dashicons-picker/
		$css = \SCU\URL . 'vendor/dashicons-picker/dashicons-picker.css';
    	wp_enqueue_style( 'dashicons-picker', $css, array( 'dashicons' ), '1.0' );
		$js = \SCU\URL . 'vendor/dashicons-picker/dashicons-picker.js';
		wp_enqueue_script( 'dashicons-picker', $js, array( 'jquery' ), '1.0' );

		wp_enqueue_code_editor(array('type'=>'text/html'));		// Add wordpress core code-editor.js and all required stuff
		//wp_enqueue_script( 'media-editor' );	
		//wp_plupload_default_settings();
		//$test = wp_get_code_editor_settings($args);
		//wp_enqueue_script('php-js', 'https://codemirror.net/mode/php/php.js');

    	$css_ver = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/css/scu-edit.css'));
		$css_file_url = \SCU\URL.'/admin/assets/css/scu-edit.css';
		wp_enqueue_style('scu-edit-css', $css_file_url, false, $css_ver, 'all');

		$css_ver = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/css/jquery-ui.css'));
		$css_file_url = \SCU\URL.'/admin/assets/css/jquery-ui.css';
		wp_enqueue_style('scu-jqueryui-css', $css_file_url, false, $css_ver, 'all');

		$js_ver  = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/js/scu-edit.js'));		
		$js_file_url = \SCU\URL.'/admin/assets/js/scu-edit.js';
		
		wp_register_script('scu-edit-js', $js_file_url, array( 'jquery' ), $js_ver , false);		
		wp_enqueue_script ('scu-edit-js');
		wp_localize_script('scu-edit-js', 'scu_ajax_edit', array(		// Send vars to the javascript file			
			'ajaxurl' => admin_url( 'admin-ajax.php' ),	
			'ajaxNonce' => wp_create_nonce( 'myajax-nonce' ),					
		));	

		$js_ver  = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/js/scu-edit-block.js'));		
		$js_file_url = \SCU\URL.'/admin/assets/js/scu-edit-block.js';
		wp_register_script('scu-edit-block-js', $js_file_url, array( 'jquery', 'jquery-ui-sortable', 'wp-i18n'), $js_ver , false);
		wp_enqueue_script ('scu-edit-block-js');

		$js_ver  = date("ymd-Gis", filemtime( \SCU\PATH.'/admin/assets/js/scu-edit-plupload-init.js'));
		$js_file_url = \SCU\URL.'/admin/assets/js/scu-edit-plupload-init.js';
		wp_register_script('scuEditPluploadInit', $js_file_url, array( 'jquery', 'plupload-all' ), $js_ver , false);		
		wp_enqueue_script ('scuEditPluploadInit');

		wp_localize_script('scuEditPluploadInit', 'scu_plupload_config_css', array (		// Send vars to the javascript file
			'browse_button'			=> 'plupload-browse-button-css',
			'container'				=> 'plupload-upload-ui-css',
			'drop_element'			=> 'drag-drop-area-css',
			'file_data_name'		=> 'async-upload',
			'runtimes'				=> 'html5, silverlight, flash, html4',
			'flash_swf_url'			=> includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url'	=> includes_url('js/plupload/plupload.silverlight.xap'),
			'url'					=> admin_url('admin-ajax.php'), //$upload_action_url,
			'filters'				=> array (
			    'max_file_size'	=> wp_max_upload_size().'b',
			    'mime_types'	=> array (
					array (	'title' 		=> __('Allowed Files', 'ultimate-shortcodes-creator'),
							'extensions'	=> 'css',
					),
				),
			),
			'multipart_params'		=> array (
				'_ajax_nonce'	=> wp_create_nonce('scu-upload-nonce'),
				'action'		=> 'scu_files_upload',			// the ajax action name
				'scu_shortcode'	=> sanitize_text_field($_GET['shortcode']),
				'resource_type'	=> 'css',
			),
		));
		wp_localize_script('scuEditPluploadInit', 'scu_plupload_config_js', array (		// Send vars to the javascript file
			'browse_button'			=> 'plupload-browse-button-js',
			'container'				=> 'plupload-upload-ui-js',
			'drop_element'			=> 'drag-drop-area-js',
			'file_data_name'		=> 'async-upload',
			'runtimes'				=> 'html5, silverlight, flash, html4',
			'flash_swf_url'			=> includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url'	=> includes_url('js/plupload/plupload.silverlight.xap'),
			'url'					=> admin_url('admin-ajax.php'), //$upload_action_url,
			'filters'				=> array (
			    'max_file_size'	=> wp_max_upload_size().'b',
			    'mime_types'	=> array (
					array (	'title' 		=> __('Allowed Files', 'ultimate-shortcodes-creator'),
							'extensions'	=> 'js',
					),
				),
			),
			'multipart_params'		=> array (
				'_ajax_nonce'	=> wp_create_nonce('scu-upload-nonce'),
				'action'		=> 'scu_files_upload',			// the ajax action name
				'scu_shortcode'	=> sanitize_text_field($_GET['shortcode']),
				'resource_type'	=> 'js',				
			),
		));
		wp_localize_script('scuEditPluploadInit', 'scu_plupload_config_assets', array (		// Send vars to the javascript file
			'browse_button'			=> 'plupload-browse-button-assets',
			'container'				=> 'plupload-upload-ui-assets',
			'drop_element'			=> 'drag-drop-area-assets',
			'file_data_name'		=> 'async-upload',
			'runtimes'				=> 'html5, silverlight, flash, html4',
			'flash_swf_url'			=> includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url'	=> includes_url('js/plupload/plupload.silverlight.xap'),
			'url'					=> admin_url('admin-ajax.php'), //$upload_action_url,
			'filters'				=> array (
			    'max_file_size'	=> wp_max_upload_size().'b',
			    'mime_types'	=> array (
					array (	'title' 		=> __('Allowed Files', 'ultimate-shortcodes-creator'),
							'extensions'	=> '*',							
					),
				),
			),
			'multipart_params'	=> array (
				'_ajax_nonce'	=> wp_create_nonce('scu-upload-nonce'),
				'action'		=> 'scu_files_upload',			// the ajax action name
				'scu_shortcode'	=> sanitize_text_field($_GET['shortcode']),
				'resource_type'	=> 'assets',				
			),
		));
	}
}
?>
