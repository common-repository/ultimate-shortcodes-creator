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
use SCU\Main as Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin_Backup {
	private static function filesize_formatted($path) {
		$size = filesize($path);
		$units = array( 'b', 'kb', 'mb', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$power = $size > 0 ? floor(log($size, 1024)) : 0;
		return number_format($size / pow(1024, $power), 0, '.', ',') . ' ' . $units[$power];
	}
	public static function menu_loaded() {		// Fired from load-scu_import_shortcodes action hook
		$css_ver = date("ymd-Gis", filemtime( \SCU\PATH.'admin/assets/css/scu-edit.css'));
    	$css_file_url = \SCU\URL.'/admin/assets/css/scu-edit.css';
		wp_enqueue_style('scu-edit-css', $css_file_url, false, $css_ver, 'all');

		$js_ver  = date("ymd-Gis", filemtime( \SCU\PATH.'/admin/assets/js/scu-backup.js'));
		$js_file_url = \SCU\URL.'/admin/assets/js/scu-backup.js';
		wp_register_script('scu-backup', $js_file_url, array( 'jquery', 'plupload-all' ), $js_ver , false);		
		wp_enqueue_script ('scu-backup');

		wp_localize_script('scu-backup', 'scu_ajax_backup', array(		// Send vars to the javascript file			
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'plugin_url' => \SCU\URL,
			'ajaxNonce' => wp_create_nonce( 'myajax-nonce' ),
		));

		$log_path = wp_normalize_path(\SCU\PATH.'temp/error.log');
		$chunk_size = min(wp_max_upload_size()-1024, 1024*1024*4-1024);			// Don't know why use 4mb is a min
		wp_localize_script('scu-backup', 'scu_plupload_config_js', array (		// Send vars to the javascript file
			'browse_button'			=> 'plupload-browse-button-js',
			'container'				=> 'plupload-upload-ui-js',
			'drop_element'			=> 'drag-drop-area-js',
			//'file_data_name'		=> 'async-upload',
			'file_data_name'		=> 'file',
			'runtimes'				=> 'html5, silverlight, flash, html4',
			//'max_file_size'		=> wp_max_upload_size().'b',
			'chunk_size'			=> $chunk_size.'b',
			'flash_swf_url'			=> includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url'	=> includes_url('js/plupload/plupload.silverlight.xap'),
			'url'					=> admin_url('admin-ajax.php'), //$upload_action_url,	
			'multi_selection'		=> false,
			'unique_names'			=> false,
			//'debug'					=> true,
			//'log_path'				=> $log_path,
			'filters'				=> array (
			    //'max_file_size'		=> wp_max_upload_size().'b',		   
			    'mime_types'	=> array (
					array (	'title' 		=> __('Allowed Files', 'ultimate-shortcodes-creator'),
							'extensions'	=> 'zip',
					),
				),
			),
			'multipart_params'		=> array (
				'_ajax_nonce'	=> wp_create_nonce('scu-upload-nonce'),
				'action'		=> 'scu_backup_upload',			// the ajax action name							
			),
		));
	}
	public static function render() {
		//require_once (\SCU\PATH.'/vendor/WriteiniFile.php');
		//add_settings_error('scuFileError', __('File Error', 'ultimate-shortcodes-creator'), 'file error', 'error');		
		//settings_errors('scuFileError');
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
			<?php _e('Import Shortcodes', 'ultimate-shortcodes-creator'); ?>
			</h1>
			<hr class="wp-header-end">
			<div id="scu-backup-admin-notices"></div>			
			<h2 class="nav-tab-wrapper" style="margin-top: 20px;">
				<div id="scu-backup-tab" class="nav-tab nav-tab-active" style="cursor: default; "><?php _e('Backup', 'ultimate-shortcodes-creator'); ?>
				</div>
				<div id="scu-restore-tab" class="nav-tab" style="cursor: default; "><?php _e('Restore', 'ultimate-shortcodes-creator'); ?>
				</div>
			</h2>

			<div id="scu-backupmenu-content" style="visibility: hidden; margin-top: 20px;">

				<div id="scu-backup-div" class="tab-div">					
					<?php
						echo('<p>');
						echo count(Main::$availableShortcodes);
						_e(' shortcodes will be backed up.', 'ultimate-shortcodes-creator');
						echo('</p>');
						echo('<p>');			
						_e(' Press button to download file in .zip format', 'ultimate-shortcodes-creator');
						echo('</p>');
						echo('<button id="scu-backup-download-button" class="button">');
						_e('Download', 'ultimate-shortcodes-creator');
						echo('</button>');
					?>			
				</div>

				<div id="scu-restore-div" class="tab-div">
					<div id="scu-edit-resources-js-div">
						<p><?php
							_e('File size not limited to ', 'ultimate-shortcodes-creator');
							echo ini_get('upload_max_filesize');
							_e('. Using chunks', 'ultimate-shortcodes-creator');
							?>
						</p>
						<div id="plupload-upload-ui-js" class="hide-if-no-js">
							<div id="drag-drop-area-js" class="drag-drop-area">
								<div class="drag-drop-inside">
									<p class="drag-drop-info"><?php _e('Drop zip file here', 'ultimate-shortcodes-creator'); ?></p>
									<p><?php _ex('or', 'Uploader: Drop file here - or - Select File','ultimate-shortcodes-creator'); ?></p>
									<p class="drag-drop-buttons"><input id="plupload-browse-button-js" type="button" value="<?php esc_attr_e('Select File', 'ultimate-shortcodes-creator'); ?>" class="button" /></p>
								</div>
							</div>
							<div id="scu-filelist-js" class="scu-filelist">
							</div>
						</div>
					</div>
					<div id="scu-backup-result"></div>
				</div>
			</div>			
		</div>
		<?php
	}
}
?>
