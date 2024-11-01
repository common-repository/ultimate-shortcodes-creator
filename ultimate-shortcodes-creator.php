<?php

/**
 * The plugin bootstrap file
 *
 * This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://shortcodescreator.com
 * @since             1.0.0
 * @package           ultimate-shortcodes-creator
 * @charset			  'UTF-8';
 *
 * @wordpress-plugin
 * Plugin Name:       Shortcodes Blocks Creator Ultimate
 * Plugin URI:        http://shortcodescreator.com
 * Description:       Create gutenberg blocks for custom shortcodes with the option of make ajax calls and inject code.
 * Version:           2.1.3
 * Author:            C&eacute;sar Morillas
 * Author URI:        http://shortcodescreator.com/contact/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ultimate-shortcodes-creator
 * Domain Path:       /languages 
 */

// Using Namespace
namespace SCU;

// Useful plugins for developement:
// · https://wordpress.org/plugins/display-text-domains/
// · https://wordpress.org/plugins/simply-show-hooks/
// · https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#naming-conventions

// Using Chrome Logger por php debug: https://craig.is/writing/chrome-logger
//require_once (plugin_dir_path( __FILE__ ). 'vendor/ChromePhp.php');	
//\ChromePhp::log('PHP debug console active!');

//error_reporting(E_ALL);
//ini_set("display_errors", "On");

// Exit if accessed directly
if(!defined('ABSPATH')) {
	die('Please don\'t access this file directly.');
}
// All the main defines
define ('SCU\URL', plugins_url( '', __FILE__ ).'/');							// URL del plugin
define ('SCU\PATH', realpath(plugin_dir_path( __FILE__ )).DIRECTORY_SEPARATOR);	// PATH del plugin
define ('SCU\VER', '2.1');														// Version
define ('SCU\PLUGIN_NAME', dirname(plugin_basename(__FILE__)));					// Plugin Name
define ('SCU\PLUGIN_PHPFILE', plugin_basename(__FILE__));						// Main File Name with relative path (needed for deactivate_plugins)

$scu_url = wp_get_upload_dir()['baseurl'].'/scu_shortcodes';
$scu_path = wp_get_upload_dir()['basedir'].'/scu_shortcodes';
if (!is_dir($scu_path)) {
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;
	$wp_filesystem->mkdir($scu_path);
	$src_dir = \SCU\PATH.'templates';		
	$result = copy_dir($src_dir, $scu_path);
	if(!$result) {
		$scu_error = new WP_Error( '501', __( 'Error copying directory', 'ultimate-shortcodes-creator' ), 'file');
		wp_die(__( 'Ultimate Shortcode Creator Unable to write in the filesystem', 'ultimate-shortcodes-creator' ));
	}
	$wp_filesystem->rmdir($scu_path.'/new', true);	
}
// wp_normalize_path vs realpath vs $wp_filesystem->find_folder
define ('SCU\SC_PATH', realpath($scu_path).DIRECTORY_SEPARATOR);	// PATH of the shortcodes directory
define ('SCU\SC_URL',  $scu_url.'/');									// URL of the shortcodes directory

// Needed for deactivate_plugins function
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// All stuff for installer and uninstaller
require_once( dirname( __FILE__ ).'/includes/class-installer.php');
register_activation_hook(__FILE__, array(__NAMESPACE__.'\includes\Installer','activate'));	// Call to activate() when activate the plugin
register_deactivation_hook(__FILE__, array(__NAMESPACE__.'\includes\Installer','deactivate'));
register_uninstall_hook(__FILE__, array(__NAMESPACE__.'\includes\Installer','uninstall'));

// All stuff for load text domain
// For debug text domains: https://es.wordpress.org/plugins/display-text-domains/	
function load_scu_textdomain() {
	load_plugin_textdomain('ultimate-shortcodes-creator', false, dirname(plugin_basename(__FILE__)).'/languages' );			
}
add_action('plugins_loaded', __NAMESPACE__.'\load_scu_textdomain');	// May use 'init' hook instead of plugins_loaded


/*********************************************************
/* Main Class
**********************************************************/
class Main {	
	protected static $_instance = null;	
	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	// Define all public vars
	public static $availableShortcodes;	
	
	public function __construct() {

		/*********************************************************
		/* Core Section 	(for both admin and frontend sections either doing ajax or not)
		**********************************************************/
		require_once(\SCU\PATH.'includes/class-main-init.php');			
		//add_action('plugins_loaded', array($this, 'checkAdmin'));
		
		/*********************************************************
		/* Ajax Section		(only when called from admin-ajax.php)
		/* Tecnically wp-admin/admin-ajax.php is an admin page and loads wp core again with '/wp-load.php'
		/* So it passes here (in the __construct) twice
		*********************************************************/		
		if ( wp_doing_ajax() ) {	// is_admin() is always true if coming from admin-ajax.
			// The only way I know to distinguish where it comes from (admin or frontend) is 
			// comparing $_SERVER['HTTP_REFERER'] with get_admin_url(). But Referer is not always available
			
			// Only include if comes from any used shortcode scu-script.js in frontend
			if($_REQUEST["action"]=='scu_ajax_handler') { 
				require_once(realpath(dirname( __FILE__ ).'/frontend/class-frontend-ajax.php'));
			}			
			// Only include if comes from admin edit menu (scu-edit-plupload-init.js or scu-edit.js)
			if($_REQUEST["action"]=='scu_files_upload' || $_REQUEST["action"]=='scu_file_remove')  {	
				require_once(\SCU\PATH.'admin/class-edit-ajax.php');
			}
			// Only include if comes from admin add menu (scu-add.js)
			if($_REQUEST["action"]=='scu_add_create' || $_REQUEST["action"]=='scu_add_upload' || $_REQUEST["action"]=='scu_show_remote' || $_REQUEST["action"]=='scu_add_remote') { 
				require_once(\SCU\PATH.'admin/class-add-ajax.php');
			}
			// Only include if comes from backup/restore menu (scu-backup.js)
			if($_REQUEST["action"]=='scu_backup_upload' || $_REQUEST["action"]=='scu_backup_download' || $_REQUEST["action"]=='scu_backup_file_remove') { 
				require_once(\SCU\PATH.'admin/class-backup-ajax.php');
			}
		}

		/*********************************************************
		/* Admin Section 	(only for admin section no ajax)
		*********************************************************/		
		elseif( is_admin() && !wp_doing_ajax() ) {
			require_once(\SCU\PATH.'/admin/class-admin.php');
		//	$adminInstance = NAMESPACE\admin\Admin::getInstance();
		}
		
		/*********************************************************
		/* Frontend Section	(only for the frontend section no ajax)
		**********************************************************/	
		else {
			require_once(\SCU\PATH.'/frontend/class-frontend.php');			
			//add_action('login_enqueue_scripts', 'edba_login_scripts');		// In the login section
			//add_action('wp_enqueue_scripts', 'edba_frontend_scripts');		// In the frontend section
		}		
	}
}
$mainInstance = Main::getInstance();	//Singletron Pattern
?>
