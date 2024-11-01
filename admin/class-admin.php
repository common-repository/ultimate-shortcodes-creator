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

class Admin {
	public function add_menus() {
		
		//wp_die(var_dump($_REQUEST['page']));	
		
		$hook_main = add_menu_page(		//$hook_suffix_menu = add_options_page ( 	In case menu under options
			__('Shortcodes Configuration', 'ultimate-shortcodes-creator'),	// Text to be displayed on the web browser Title
			__('Shortcodes', 'ultimate-shortcodes-creator'),				// Text to be displayed in the wordpress admin section menu
			'manage_options',					// Which type of users can see this menu
			'scu_main_page',					// The unique ID - that is, the slug. (Page parameter in options-general.php or admin.php)
			'#',								// The name of the function to call when rendering the menu for this page
			'dashicons-editor-code',			// Icon
			'66'								// Position in the menu
		);
		// if submenu of add_options_page or null, linked to options.php?page=scu_edit_shortcode
		// if submenu of add_menu page, linked to admin.php?page=scu_edit_shortcode
		$hook_manage = add_submenu_page(			
			'scu_main_page',	// The slug name for the parent menu (or the file name of a standard WordPress admin page)		
			__('Shortcodes Configuration', 'ultimate-shortcodes-creator'),	// Text to be displayed on the web browser Title
			__('Installed Shortcodes', 'ultimate-shortcodes-creator'),			// Text to be displayed in the wordpress admin section menu
			'manage_options',					// Which type of users can see this menu
			'scu_manage_shortcodes',				// The unique ID - that is, the slug. (Page parameter in options-general.php)
			array($this, 'manage_menu_render')		// The name of the function to call when rendering the menu for this page
		);
		$hook_add = add_submenu_page(			
			'scu_main_page',	// The slug name for the parent menu (or the file name of a standard WordPress admin page)		
			__('Add Shortcode', 'ultimate-shortcodes-creator'),	// Text to be displayed on the web browser Title
			__('Add Shortcode', 'ultimate-shortcodes-creator'),	// Text to be displayed in the wordpress admin section menu
			'manage_options',					// Which type of users can see this menu
			'scu_add_shortcode',				// The unique ID - that is, the slug. (Page parameter in admin.php)
			array($this, 'add_menu_render')	// The name of the function to call when rendering the menu for this page
		);						
		if ( isset( $_REQUEST['page'] ) && 'scu_edit_shortcode' === $_REQUEST['page']) {
			$hook_edit = add_submenu_page(			
				'scu_main_page',				// The slug name for the parent menu (or the file name of a standard WordPress admin page)	
				__('Edit Shortcode', 'ultimate-shortcodes-creator'),	// Text to be displayed on the web browser Title
				__('Edit Shortcode', 'ultimate-shortcodes-creator'),	// Text to be displayed in the wordpress admin section menu
				'manage_options',					// Which type of users can see this menu
				'scu_edit_shortcode',				// The unique ID - that is, the slug. (Page parameter in admin.php)
				array($this, 'edit_menu_render')		// The name of the function to call when rendering the menu for this page
			);
		} else {
			$hook_edit = add_submenu_page(			
				'_some_hash',					// The slug name for the parent menu (or the file name of a standard WordPress admin page)	
				'Edit Shortcodes',				// Text to be displayed on the web browser Title
				'Not Wanted to Display',		// Text to be displayed in the wordpress admin section menu
				'manage_options',				// Which type of users can see this menu
				'scu_edit_shortcode',			// The unique ID - that is, the slug. (Page parameter in admin.php)
				array($this, 'edit_menu_render')		// The name of the function to call when rendering the menu for this page
			);
		}
		$hook_backup = add_submenu_page(			
			'scu_main_page',	// The slug name for the parent menu (or the file name of a standard WordPress admin page)		
			__('SCU Backup/Restore Shortcodes', 'ultimate-shortcodes-creator'),	// Text to be displayed on the web browser Title
			__('Backup/Restore', 'ultimate-shortcodes-creator'),			// Text to be displayed in the wordpress admin section menu
			'manage_options',					// Which type of users can see this menu
			'scu_backup_shortcodes',				// The unique ID - that is, the slug. (Page parameter in options-general.php)
			array($this, 'backup_menu_render')		// The name of the function to call when rendering the menu for this page
		);

		// For demonstration porpouses
		//global $submenu;
		//$submenu['scu_main_page'][] = array('Example', 'manage_options', 'http://www.example.com/');

		// Highligh this parent submenu item must be done with js becasue $parent_file global is always overrided when get_admin_page_parent() is called in wp_admin/menu-header.php line 50 (v5.0.2)
		remove_submenu_page('scu_main_page', 'scu_main_page');		// Not wanted the automatically duplicated submenu of the menu

		add_action('load-' . $hook_manage, array($this, 'manage_menu_loaded'), 10);	//Executed before the admin manage page be loaded (needed for redirection in shortcode bulk actions in list table class before send the headers)
		add_action('load-' . $hook_edit, array($this, 'edit_menu_loaded'), 10);
		add_action('load-' . $hook_add, array($this, 'add_menu_loaded'), 10);
		add_action('load-' . $hook_backup, array($this, 'backup_menu_loaded'), 10);
	}

	public function manage_menu_loaded() {
		require_once (\SCU\PATH.'admin/class-manage.php');
		\SCU\admin\Admin_Manage::manage_menu_loaded();
	}
	public function manage_menu_render() {
		require_once (\SCU\PATH.'admin/class-manage.php');		// Not needed because it has been already called in menu-loaded()
		\SCU\admin\Admin_Manage::manage_menu_render();
	}
	public function edit_menu_loaded() {
		if(!isset($_REQUEST['shortcode'])) {		// Just for security
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'page' ) );			
			wp_redirect( esc_url_raw( add_query_arg( array('page' => 'scu_manage_shortcodes' ) ) ) );
			exit;
		}
		else if ( isset( $_POST['submit'] ) ) {		// It comes here after saving shortcode
			if(!wp_verify_nonce( wp_unslash($_POST['_wpnonce']), 'saved-shortcode')) {
				wp_die('wp_nonce Failed: '.$_POST['_wpnonce']. "vs ". wp_create_nonce('saved-shortcode'));
			}
			require_once (\SCU\PATH.'admin/class-edit-save-shortcode.php');
			\SCU\admin\Edit_Save_Shortcode::save_shortcode();
		}

		require_once (\SCU\PATH.'admin/class-edit-add-scripts.php');
		add_action('admin_enqueue_scripts', array('\SCU\admin\Edit_Add_Scripts', 'add_scripts'), 10);
			
		//	require_once (\SCU\PATH.'admin/class-admin-edit.php');
		//	\SCU\admin\Admin_Edit::edit_menu_loaded();
	}	
	public function edit_menu_render() {
		require_once (\SCU\PATH.'admin/class-edit-render.php');
		\SCU\admin\Edit_Render::render();
	}
	public function add_menu_loaded() {
		require_once (\SCU\PATH.'admin/class-add-loaded.php');
		\SCU\admin\Add_Loaded::loaded();
	}
	public function add_menu_render() {
		require_once (\SCU\PATH.'admin/class-add-render.php');
		\SCU\admin\Add_Render::render();
	}
	public function backup_menu_loaded() {
		require_once (\SCU\PATH.'admin/class-backup.php');
		\SCU\admin\Admin_Backup::menu_loaded();
	}
	public function backup_menu_render() {
		require_once (\SCU\PATH.'admin/class-backup.php');		// Not needed because it has been already called in menu-loaded()
		\SCU\admin\Admin_Backup::render();
	}

	public function add_gutenberg_blocks() {		;
		// First, let's check if there is some orphaned scu gutenberg block

		//$blocks = parse_blocks( get_the_content() );		// Not available until 'the_post' hook

		// If $blocks is wanted before 'the_post' hook
		$parts = explode('/', parse_url($_SERVER['REQUEST_URI'])['path']);		
		$file_path = $parts[count($parts)-1];
		$action = (isset($_GET["action"]) ? $_GET["action"] : false);
		if($file_path == 'post.php' && $action=='edit') {			
			$blocks = parse_blocks(get_the_content('','', $_GET["post"]));
			$orphaned_shortcodes = [];
			foreach ($blocks as $key => $block) {
				if(strtok($block['blockName'], '/')=='shortcodes-creator-ultimate' ) {
					$present_blockshortcode = explode("/", $block['blockName'], 2)[1];					
					if(!in_array($present_blockshortcode, Main::$availableShortcodes)) {
						$orphaned_shortcodes[$key]['name'] =  $present_blockshortcode;
						$orphaned_shortcodes[$key]['innerHTML'] = $block['innerHTML'];						
					}
				}
			}
			$orphaned_shortcodes = array_values($orphaned_shortcodes);	// Recreate array without associative keys
			
			require_once (\SCU\PATH.'admin/class-gutenberg.php');	// It has been already called in menu-loaded()
			// Finally, add blocks for both: available shortcodes and not found shortcodes
			\SCU\admin\Admin_Gutenberg::add_blocks(Main::$availableShortcodes, $orphaned_shortcodes );
		}
	}

	public function add_block_categories($categories) {
		$categories[] = [
			'slug' => 'scu-shortcodes',
			'title' => __('SCU Shortcodes', 'ultimate-shortcodes-creator'),
			'icon' => NULL
		];
		return $categories;
	}
	
	public function __construct() {		
		add_action('admin_menu', array( $this, 'add_menus'), 10);	// Create sub page inside Settings menu in the admin pannel
		add_filter('block_categories', array($this, 'add_block_categories'), 10);
		add_action('init', array( $this, 'add_gutenberg_blocks'), 10);	// The earliest hook where post/page is available is 'the_post'
	}
}
$adminInstance = new Admin();	// Without Singletron Pattern
?>
