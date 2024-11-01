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

class AddAjax {	
	protected static $_instance = null;
	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function add_create() {
		check_ajax_referer('myajax-nonce', 'ajaxNonce');
		
		WP_Filesystem();
		global $wp_filesystem;

		$src_dir = wp_normalize_path(\SCU\PATH.'templates/new/');
		if (!$wp_filesystem->is_dir( $src_dir ) ) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => '501',
					'message' => $src_dir.__(' not found.', 'ultimate-shortcodes-creator'),
					'type' => 'dir'
				)
			);
			echo(json_encode($status));
			wp_die();
		}
		$src_dir=realpath($src_dir);	// Really not needed

		$target_folder = wp_unique_filename($wp_filesystem->find_folder(\SCU\SC_PATH), __('new', 'ultimate-shortcodes-creator'));
		$target_dir = $wp_filesystem->find_folder(\SCU\SC_PATH.$target_folder);
		$wp_filesystem->mkdir($target_dir);

		$result = copy_dir($src_dir, $target_dir);
		if(!$result) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => '501',
					'message' => __('new shortcode can not be created.', 'ultimate-shortcodes-creator'),
					'type' => 'dir'
				)
			);
			echo(json_encode($status));
			wp_die();
			//wp_redirect( esc_url_raw( add_query_arg( array('page' => 'scu_edit_shortcode', 'shortcode' => $target_folder ) ) ) );
		}
		
		$status = array (
			'OK' => true,
			'shortcode' => $target_folder,
			'url_redirect' => admin_url('admin.php?page=scu_edit_shortcode&shortcode=').$target_folder
		);
		echo(json_encode($status));
		wp_die();
	}
	
	public function add_upload() {
		$result = wp_verify_nonce($_REQUEST['_wpnonce'],'scu-upload-nonce');

		WP_Filesystem();
		global $wp_filesystem;

		if(!$result) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Error in the nonce', 'ultimate-shortcodes-creator'),
					'type' => __('Error in the nonce', 'ultimate-shortcodes-creator')
				)
			);
			echo(json_encode($status));
			wp_die();
		}		
		$result = move_uploaded_file($_FILES["file"]["tmp_name"], \SCU\PATH."temp/".$_FILES['file']['name']);
		if(!$result) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Error when uploading the file', 'ultimate-shortcodes-creator'),
					'type' => __('Error when uploading the file', 'ultimate-shortcodes-creator')
				)
			);
			echo(json_encode($status));
			wp_die();
		}
		$uploaded_file_path = wp_normalize_path(\SCU\PATH."temp/".$_FILES['file']['name']);		
		$file_name = basename( basename( \SCU\PATH."temp/".$_FILES['file']['name'], '.tmp' ), '.zip' );
		$file_name_unique = wp_unique_filename(\SCU\SC_PATH, $file_name);
		$shortcodes_dir = wp_normalize_path(\SCU\SC_PATH.$file_name_unique);
		
		// Unzip zip shortcode to shortcodes directory			
		$result = unzip_file( $uploaded_file_path, $shortcodes_dir );
		if(is_wp_error( $result )) {
			$result = unlink( $uploaded_file_path );
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Error when unzipping the file', 'ultimate-shortcodes-creator'),
					'type' => $result->get_error_message()
				)
			);			
			echo(json_encode($status));
			wp_die();
		}
		if(!file_exists($shortcodes_dir.'/scu-config.ini')) {
			$status = array (
				'OK' => false,
				'error' => array (
					'dir' => $result,
					'code' => 1,
					'message' => __('Not valid shortcode. File scu-config.ini does not exist', 'ultimate-shortcodes-creator'),
					'type' => __('Not valid shortcode. File scu-config.ini does not exist', 'ultimate-shortcodes-creator')
				)
			);
			
			$wp_filesystem->rmdir($shortcodes_dir, true);
			echo(json_encode($status));
			wp_die();
		}

		// Once extracted, delete the uploaded file
		$result = unlink( $uploaded_file_path );

		//$shortcodes_files = list_files($working_dir, 1);
		$status = array (
			'OK' => true,
			'message' => 'Shortcode: '.$file_name_unique.' '.__('installed succesfully', 'ultimate-shortcodes-creator') ,
		);
		echo(json_encode($status));			
		wp_die();
	}

	public function show_remote() {
		check_ajax_referer('myajax-nonce', 'ajaxNonce');
		$tab = $_POST["tab"];
		if(!$tab) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Error in the nonce', 'ultimate-shortcodes-creator'),
					'type' => __('Error in the nonce', 'ultimate-shortcodes-creator')
				)
			);
			echo(json_encode($status));
			wp_die();
		}

		$url = "http://www.shortcodescreator.com/API/data.php";

		$data = array (
			'tab'	=>	$tab,
			'password'	=>	'1234'
		);
		$post_args = array (
			'body' => $data,
			'cookies' => array()
		);
		$response = wp_remote_post($url, $post_args);
 
  		if ( is_wp_error( $response ) ) {			
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => $response->get_error_message(),
					'type' => $response->get_error_message()
				)
			);
			echo(json_encode($status));
			wp_die();			
		}		

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );		

		$status = array (			
			'OK' => true,
			'tab' => $response_body['tab'],
			'shortcodes' => $response_body['shortcodes'],
			'all' => $response_body
		);
		echo(json_encode($status));
		wp_die();
	}

	public function add_remote() {
		check_ajax_referer('myajax-nonce', 'ajaxNonce');
		$url_shortcode = $_POST["url_shortcode"];		

		$tmp_file = download_url( $url_shortcode );

 
		// Sets file final destination.
		$tmp_file_path = \SCU\PATH."temp/";//.basename($url_shortcode);		
		// Copies the file to the final destination and deletes temporary file.
		//copy( $tmp_file, $filepath );
		//@unlink( $tmp_file );

		WP_Filesystem();
		global $wp_filesystem;
		$newfilename = wp_unique_filename( $tmp_file_path, basename($url_shortcode));
		$uploaded_file_path = wp_normalize_path($tmp_file_path.$newfilename);
		$result = $wp_filesystem->copy($tmp_file, $uploaded_file_path);
		//$result = move_uploaded_file($tmp_file, \SCU\PATH."temp/"."cesar.zip");
		@unlink( $tmp_file );		

		if(!$result) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Error when downloading the file', 'ultimate-shortcodes-creator'),
					'type' => __('Error when downloading the file', 'ultimate-shortcodes-creator')
				)
			);
			echo(json_encode($status));
			wp_die();
		}			
		$file_name = basename( basename( $uploaded_file_path, '.tmp' ), '.zip' );
		$file_name_unique = wp_unique_filename(\SCU\SC_PATH, $file_name);
		$shortcodes_dir = wp_normalize_path(\SCU\SC_PATH.$file_name_unique);
		
		// Unzip zip shortcode to shortcodes directory			
		$result = unzip_file( $uploaded_file_path, $shortcodes_dir );
		if(!$result) {
			$result = unlink( $uploaded_file_path );
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Error when unzipping the file', 'ultimate-shortcodes-creator'),
					'type' => __('Error when unzipping the file', 'ultimate-shortcodes-creator')
				)
			);			
			echo(json_encode($status));
			wp_die();
		}
		if(!file_exists($shortcodes_dir.'/scu-config.ini')) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Not valid shortcode. File scu-config.ini does not exist', 'ultimate-shortcodes-creator'),
					'type' => __('Not valid shortcode. File scu-config.ini does not exist', 'ultimate-shortcodes-creator')
				)
			);
			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->rmdir($shortcodes_dir, true);
			echo(json_encode($status));
			wp_die();
		}

		// Once extracted, delete the uploaded file
		$result = unlink( $uploaded_file_path );

		//$shortcodes_files = list_files($working_dir, 1);
		$status = array (
			'OK' => true,
			'message' => 'Shortcode: '.$file_name_unique.' '.__('installed succesfully', 'ultimate-shortcodes-creator') ,
		);
		echo(json_encode($status));			
		wp_die();
	}

	public function __construct() {
		add_action('wp_ajax_scu_add_create', array ($this, 'add_create'), 10);
		add_action('wp_ajax_scu_add_upload', array ($this, 'add_upload'), 10);
		add_action('wp_ajax_scu_show_remote', array ($this, 'show_remote'), 10);
		add_action('wp_ajax_scu_add_remote', array ($this, 'add_remote'), 10);
	}		
}
$addAjaxInstance = new AddAjax();	// Without Singletron Pattern
?>