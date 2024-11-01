<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cmorillas1@gmail.com
 * @category API
 * @package  Frontend
 */
 
namespace SCU\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class EditAjax {	
	protected static $_instance = null;
	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function change_upload_path($dir) {		
		$shortcode = sanitize_text_field($_POST['scu_shortcode']);
		$resource_type = sanitize_text_field($_POST['resource_type']);
		
		$upload_dir = realpath(\SCU\SC_PATH.$shortcode.'/resources/'.$resource_type);
		
		return array(
			'path'   => $upload_dir,
			//'url'    => $dir['baseurl'] . '/mycustomdir',
			//'subdir' => '/mycustomdir',
		) + $dir;
	}

	public function files_upload() {
		check_ajax_referer('scu-upload-nonce');
		$upload_overrides = array (
			'test_form'=>true,				
			'test_type' => false,
			//'mimes' => array ('jpg|jpeg|jpe'	=> 'image/jpeg'),
			//'mimes' => array ('css'	=> 'text/css',
			//					'js'	=> 'application/javascript'),
			'action' => 'scu_files_upload',
		);

		// Register our path override.
		add_filter( 'upload_dir', array ($this,'change_upload_path'), 10, 2 ); 
		
		//$_FILES['async-upload']['name'] = remove_accents($_FILES['async-upload']['name']);
		add_filter( 'sanitize_file_name', 'remove_accents', 10, 1 );
		$tmp_file = $_FILES['async-upload'];
		//echo(json_encode($tmp_file_path));
		$status = wp_handle_upload($tmp_file, $upload_overrides);
		
		// Set path back to normal.
		remove_filter( 'upload_dir', array ($this, 'change_upload_path') );
		
		echo(json_encode($status));
		//echo 'Uploaded to: '.$status['url'];			
		wp_die();		
	}

	public function file_remove() {
		check_ajax_referer('myajax-nonce', 'ajaxNonce');
		$shortcode = sanitize_text_field($_POST['scu_shortcode']);
		$resource_type = sanitize_text_field($_POST['resource_type']);
		$file_name = sanitize_text_field($_POST['file_name']);
		$file_path = realpath(\SCU\SC_PATH.$shortcode.'/resources/'.$resource_type.'/'.$file_name);
		
		WP_Filesystem();
		global $wp_filesystem;
		$bool = $wp_filesystem->delete($file_path);
		echo(json_encode($bool));
		wp_die();
	}
	
	public function __construct() {
		add_action('wp_ajax_scu_files_upload', array ($this, 'files_upload'), 10);
		add_action('wp_ajax_scu_file_remove', array ($this, 'file_remove'), 10);
	}		
}
$editAjaxInstance = new EditAjax();	// Without Singletron Pattern
?>