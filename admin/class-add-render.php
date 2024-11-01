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

class Add_Render {
	private static function filesize_formatted($path) {
		$size = filesize($path);
		$units = array( 'b', 'kb', 'mb', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$power = $size > 0 ? floor(log($size, 1024)) : 0;
		return number_format($size / pow(1024, $power), 0, '.', ',') . ' ' . $units[$power];
	}
	public static function render() {
		$output = '<div class="wrap"><h1 class="wp-heading-inline">';
		$output .= __('Add shortcodes', 'ultimate-shortcodes-creator');
		$output .= '&nbsp;</h1><button id="scu-add-button-create" class="page-title-action">';
		$output .= __('New', 'ultimate-shortcodes-creator');
		$output .= '</button>';
		$output .= '&nbsp;</h1><button id="scu-add-button-upload" class="page-title-action" role="button" aria-expanded="true">';
		$output .= __('Upload', 'ultimate-shortcodes-creator');
		$output .= '</button>';
		$output .= '<hr class="wp-header-end">';
		echo ($output);
		?>		
		<div id="scu-add-menu-notices"></div>

		<div id="scu-add-tab-upload" style="display: none">
			<div class="upload-plugin">
				<p class="install-help"><?php _e( 'If you have a shortcode in a .zip format, you may install it by uploading it here.', 'ultimate-shortcodes-creator' ); ?>
				</p>
				<form id="scu-add-upload-form" method="post" enctype="multipart/form-data" class="wp-upload-form">
					<?php wp_nonce_field( 'scu-upload-nonce' ); ?>
					<input type="hidden" name="action" value="scu_add_upload">
					<label class="screen-reader-text" for="shortcodezip"><?php _e( 'Plugin zip file', 'ultimate-shortcodes-creator' ); ?>
					</label>
					<p style="margin: -10px 0 10px 5px;"><?php _e('Max file size: ', 'ultimate-shortcodes-creator'); echo ini_get('upload_max_filesize');?></p>
					<input type="file" accept="application/zip" id="file" name="file" />
					<?php submit_button( __( 'Install Now', 'ultimate-shortcodes-creator' ), '', 'install-shortcode-submit', false ); ?>
				</form>
			</div>
		</div>
		<div id="scu-add-tab-remote">
			<div class="wp-filter">
				<ul class="filter-links">
					<li id="plugin-install-simple" class="scu-remote-li"><a href="<?php 
						$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'tab' ) );
						$url_redirect = esc_url_raw( add_query_arg( 'tab', 'simple' ) );
						echo($url_redirect);						
						echo (!isset($_GET['tab']) || $_GET['tab'] == 'simple') ? '" class="current" ' : '" ';
						?>
						aria-current="page">Simple</a>
					</li>
					<li id="plugin-install-easy" class="scu-remote-li"><a href="<?php 
						$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'tab' ) );
						$url_redirect = esc_url_raw( add_query_arg( 'tab', 'easy' ) );
						echo($url_redirect);						
						echo (!isset($_GET['tab']) && $_GET['tab'] == 'easy') ? '" class="current" ' : '" ';
						?>
						aria-current="page">Easy</a>
					</li>			
					<li id="plugin-install-intermediate" class="scu-remote-li"><a href="<?php 
						$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'tab' ) );
						$url_redirect = esc_url_raw( add_query_arg( 'tab', 'intermediate' ) );
						echo($url_redirect);						
						echo (isset($_GET['tab']) && $_GET['tab'] == 'intermediate') ? '" class="current" ' : '" ';
						?>
						aria-current="page">Intermediate</a>
					</li>
					<li id="plugin-install-advanced" class="scu-remote-li"><a href="<?php 
						$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'tab' ) );
						$url_redirect = esc_url_raw( add_query_arg( 'tab', 'advanced' ) );
						echo($url_redirect);						
						echo (isset($_GET['tab']) && $_GET['tab'] == 'advanced') ? '" class="current" ' : '" ';
						?>
						aria-current="page">Advanced</a>
					</li>
					<li id="plugin-install-expert" class="scu-remote-li"><a href="<?php 
						$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'tab' ) );
						$url_redirect = esc_url_raw( add_query_arg( 'tab', 'expert' ) );
						echo($url_redirect);						
						echo (isset($_GET['tab']) && $_GET['tab'] == 'expert') ? '" class="current" ' : '" ';
						?>
						aria-current="page">Expert</a>
					</li>
					
				</ul>
				<form class="search-form search-plugins" method="post" action="http://shortcodescreator.com/API/data.php">
					<input type="hidden" name="tab" value="search">
					<label class="screen-reader-text" for="typeselector">Buscar plugins por:</label>
					<select name="type" id="typeselector">
						<option value="term" selected="selected">Palabra clave</option>
						<option value="author">Autor</option>
						<option value="tag">Etiqueta</option>
					</select>
					<label><span class="screen-reader-text">Buscar plugins</span>
						<input type="search" name="s" value="" class="wp-filter-search" placeholder="Buscar plugins..." aria-describedby="live-search-desc">
					</label>
					<input type="submit" id="search-submit" class="button hide-if-js" value="Buscar plugins">
				</form>
			</div>
			<div id="scu-remote-shortcodes">
			</div>
		</div>
	<?php
	}
	
	public function __construct() {
		//add_action('admin_menu', array( $this, 'addMenu'), 10);		// Create sub page inside Settings menu in the admin pannel
	}
}
?>
