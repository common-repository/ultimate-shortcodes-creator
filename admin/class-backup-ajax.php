<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cmorillas1@gmail.com
 * @category API
 * @package  Frontend
 */
 
namespace SCU\admin;
use SCU\Main as Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BackupAjax {	
	protected static $_instance = null;
	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function change_upload_import_path($dir) {		
		//$shortcode = sanitize_text_field($_POST['scu_shortcode']);
		//$resource_type = sanitize_text_field($_POST['resource_type']);
		
		$upload_dir = realpath(\SCU\PATH.'temp/');
		
		return array(
			'path'   => $upload_dir,
			//'url'    => $dir['baseurl'] . '/mycustomdir',
			//'subdir' => '/mycustomdir',
		) + $dir;
	}

	private function _install_shortcode($shortcode_file_path) {
		$file_name = basename( basename( $shortcode_file_path, '.tmp' ), '.zip' );
		$file_name_unique = wp_unique_filename(\SCU\SC_PATH, $file_name);
		$shortcode_dir = wp_normalize_path(\SCU\SC_PATH.$file_name_unique);

		// Unzip zip shortcode to shortcodes directory		
		$result = unzip_file( $shortcode_file_path, $shortcode_dir );
		if(!$result) {
			$result = unlink( $shortcode_file_path );
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Error when unzipping the file', 'ultimate-shortcodes-creator'),
					'type' => __('Error when unzipping the file', 'ultimate-shortcodes-creator'),
					'shortcode' => $file_name
				)
			);			
			return($status);
		}		
		if(!file_exists($shortcode_dir.'/scu-config.ini')) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => 1,
					'message' => __('Not valid shortcode. File scu-config.ini does not exist', 'ultimate-shortcodes-creator'),
					'type' => __('Not valid shortcode. File scu-config.ini does not exist', 'ultimate-shortcodes-creator'),
					'shortcode' => $file_name_unique
				)
			);
			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->rmdir($shortcode_dir, true);
			return($status);
		}

		// Once extracted, delete the uploaded file
		$result = unlink( $shortcode_file_path );

		//$shortcodes_files = list_files($working_dir, 1);
		$status = array (
			'OK' => true,
			'message' => 'Shortcode: '.$file_name_unique.' '.__('installed succesfully', 'ultimate-shortcodes-creator'),
			'shortcode' => $file_name_unique
		);
		return($status);
	}

	public function backup_upload() {
		check_ajax_referer('scu-upload-nonce');
		WP_Filesystem();
		global $wp_filesystem;
		require_once(realpath(\SCU\PATH."vendor/PluploadHandler.php"));	// See https://github.com/moxiecode/plupload-handler-php
		$upload_dir = wp_normalize_path(\SCU\PATH.'temp/');
		if (!$wp_filesystem->is_dir( $upload_dir ) ) {
			$wp_filesystem->mkdir( $upload_dir, true );
		}
		$upload_dir=realpath($upload_dir);				// Really not needed
		//$wp_filesystem->delete($upload_dir, true);		// Clean up working directory if desired

		$ph = new \PluploadHandler(array(
			'target_dir' => $upload_dir,
			'allow_extensions' => 'zip'
		));
		$ph->sendNoCacheHeaders();
		//$ph->sendCORSHeaders();
		$result = $ph->handleUpload();
		if(isset($result["chunk"])) {		// If the call is a chunk, return inmediatly for another ajax call asking another chunk
			wp_die(json_encode($result));
		}

		if(!isset($result["name"])) {
			$status = array (
				'OK' => false,
				'error' => array (
					'code' => $ph->getErrorCode(),
					'message' => __('Error when uploading file', 'ultimate-shortcodes-creator'),
					'type' => $ph->getErrorMessage()
				)
			);
			echo(json_encode($status));
			wp_die();
		}
		
		$uploaded_file_path = realpath($result["path"]);
		$file_name = basename( basename( $uploaded_file_path, '.tmp' ), '.zip' );
		$file_name_unique = wp_unique_filename($uploaded_file_path, $file_name);
		$tmp_dir = wp_normalize_path($upload_dir.'/'.$file_name_unique);
		
		// Unzip zip shortcode to shortcodes directory			
		$result = unzip_file( $uploaded_file_path, $tmp_dir );
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

		// Once extracted, delete the uploaded .zip file
		$result = unlink( $uploaded_file_path );

		$shortcodes_files = list_files($tmp_dir, 1);
		
		$result = array();
		foreach ($shortcodes_files as $key => $shortcode_file) {
			$result[$key]=self::_install_shortcode($shortcode_file);
		}

		// Once extracted each shortcode .zip file, delete the unzipped temporary directory
		$wp_filesystem->rmdir($tmp_dir, true);

		echo(json_encode($result));			
		wp_die();
		
		$status = array (
			'OK' => true,
			'shortcode' => $file_name_unique,
		);
		echo(json_encode($status));			
		wp_die();
	}

	public function backup_download() {
		check_ajax_referer('myajax-nonce', 'ajaxNonce');
		WP_Filesystem();
		global $wp_filesystem;
		require_once(realpath(\SCU\PATH."vendor/ZipData.php"));
		$sc_path = \SCU\SC_PATH.'*';
		$shortcodes = glob($sc_path, GLOB_ONLYDIR);
		$tmp_dir = wp_normalize_path(\SCU\PATH.'temp/scu_backup/');
		$wp_filesystem->rmdir($tmp_dir, true);
		$wp_filesystem->mkdir($tmp_dir);
		foreach ($shortcodes as $key => $shortcode) {
			$source = realpath(\SCU\SC_PATH.basename($shortcode));			
			$destination = wp_normalize_path(\SCU\PATH.'temp/scu_backup/'.basename($shortcode).'.zip');
			$result = \ZipData::zip_files($source, $destination);
			if(!$result) {
				$status = array (
					'OK' => false,
					'error' => array (
						'code' => 1,
						'message' => __('Error when zipping the shortcode directory', 'ultimate-shortcodes-creator'),
						'type' => __('Error when zipping the shortcode directory', 'ultimate-shortcodes-creator')
					)
				);
				echo(json_encode($status));
				wp_die();
			}
			$shortcode_zip[$key]=$destination;
		}
		$source = $tmp_dir;
		$destination = wp_normalize_path(\SCU\PATH.'temp/scu_backup.zip');		
		$result = \ZipData::zip_files($source, $destination);

		$status = array (
			'OK' => true
		);

		// Once extracted each shortcode .zip file, delete the unzipped temporary directory
		$wp_filesystem->rmdir($tmp_dir, true);

		echo(json_encode($destination));
		wp_die();
	}

	public function file_remove() {
		check_ajax_referer('myajax-nonce', 'ajaxNonce');	
		$file = $_REQUEST['file'];
		WP_Filesystem();
		global $wp_filesystem;
		$bool = $wp_filesystem->delete($file);
		echo(json_encode($bool));
		wp_die();
	}
	
	public function __construct() {
		add_action('wp_ajax_scu_backup_upload', array ($this, 'backup_upload'), 10);
		add_action('wp_ajax_scu_backup_download', array ($this, 'backup_download'), 10);
		add_action('wp_ajax_scu_backup_file_remove', array ($this, 'file_remove'), 10);
	}		
}
$backupAjaxInstance = new BackupAjax();	// Without Singletron Pattern
?>