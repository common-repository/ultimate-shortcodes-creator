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

class Admin_Manage {
	public static $shortcodeListTable;
	//private $shortcodeRedirect;


	public static function manage_menu_loaded() {		// Fired from load-scu_manage_shortcodes action hook		
		require_once (\SCU\PATH.'/admin/class-shortcode-list-table.php');
		self::$shortcodeListTable = new Shortcode_List_Table();
		self::$shortcodeListTable->prepare_items();
		if(isset($_REQUEST['result'])) {
			$message = 'Shortcode(s) succesfully '.$_REQUEST['result'];
			add_settings_error('manage_menu_notice', 'manage_menu_notice', $message, 'success');
			settings_errors('manage_menu_notice');
		}
		//$this->add_admin_notices();
	}

	public static function manage_menu_render() {
		$output = '<div class="wrap"><h1 class="wp-heading-inline">';
		$output .= __('All Shortcodes', 'ultimate-shortcodes-creator');
		$output .= '&nbsp;</h1><a href="?page=scu_add_shortcode" class="page-title-action">';
		$output .= __('Add New', 'ultimate-shortcodes-creator');
		$output .= '</a><hr class="wp-header-end">';
		echo ($output);
 		?>
		<form id="list-table-form" method="get">
			<!-- For plugins, we also need to ensure that the form posts back to our current page -->
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<!-- Now we can render the completed list table -->
			<?php self::$shortcodeListTable->display() ?>
		</form>
        <?php
 		echo ('</div> <!-- wrap div-->');	
	}
	
	public function __construct() {
		//add_action('admin_menu', array( $this, 'addMenu'), 10);		// Create sub page inside Settings menu in the admin pannel
	}
}
//$adminManage = new Admin_Manage();	// Without Singletron Pattern
?>
