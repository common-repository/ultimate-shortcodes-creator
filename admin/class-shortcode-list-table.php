<?php
/**
 * This class handles the table for manage shortcodes in the main menu
 *
 * @author   cmorillas1@gmail.com
 * @category API
 * @package  SCU_Admin
 */

// We don't use settings API here (https://developer.wordpress.org/plugins/settings/)
// Nothing to be saved in the wp_options table of the wordpress database

// add_action('current_screen', ...) is the first hook where $current_screen, $pagenow, and $plugin_page globals are available 

namespace SCU\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Shortcode_List_Table extends \WP_List_Table {
	private $data = array();
	
	private function setShortcodesData() {
		// Array('ID'=> , 'name'=> , 'description'=> )
		//$availableShortcodes = glob(\SCU\PATH.'shortcodes/*', GLOB_ONLYDIR);
		$availableShortcodes = \SCU\Main::$availableShortcodes;
		
		
		foreach ($availableShortcodes as $key => $shortcode) {			
			$ini_file = wp_normalize_path(\SCU\SC_PATH.basename($shortcode).'/scu-config.ini');
			if(!file_exists($ini_file)) {
				$message = $ini_file . ' '. __('does not exists', 'ultimate-shortcodes-creator');
				add_settings_error('scuFileError', __('File Error', 'ultimate-shortcodes-creator'), $message, 'error');
				//wp_die($ini_file.__(' does not exit', 'ultimate-shortcodes-creator'), E_USER_ERROR);
			}
			settings_errors('scuFileError');
			//wp_die(var_dump($ini_file));
			$shortcode_ini = parse_ini_file($ini_file, true);
			$this->data[] = array (
				'ID'			=> $key+1,				
				'name'			=> basename($shortcode),
				'description'	=> $shortcode_ini['general']['description'],
				'author'		=> $shortcode_ini['general']['author'],
				'shortcode'		=> '[scu name="'.basename($shortcode).'"]',
				'enabled'		=> $shortcode_ini['config']['enabled'],
			);			
		}
	}	

	protected function column_default($item, $column_name) {
		switch($column_name){
			case 'ID':
			case 'name':
			case 'description':
			case 'shortcode':
				return $item[$column_name];
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}
	protected function column_cb($item){		
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("shortcode")
			/*$2%s*/ $item['name']               //The value of the checkbox should be the record's name
		);
	}

	protected function column_name($item) {        
		//Build row actions
		$outputEnable = '<a style="font-weight:normal" href="?page='.$_REQUEST['page'].'&action=';
		$outputEnable .= ($item['enabled']) ? "disable" : "enable";
		$outputEnable .= '&shortcode[]='.$item['name'];
		$outputEnable .= '&_wpnonce='.wp_create_nonce('bulk-'.$this->_args['plural']).'">';		
		$outputEnable .= ($item['enabled']) ? __('Disable', 'ultimate-shortcodes-creator') : __('Enable', 'ultimate-shortcodes-creator');
		$outputEnable .= '</a>';
		$outputEdit = '<a style="font-weight:normal" href="?page=scu_edit_shortcode&shortcode='.$item['name'].'">';
		$outputEdit .= __('Edit', 'ultimate-shortcodes-creator').'</a>';
		$outputDelete = '<a style="font-weight:normal" href="?page='.$_REQUEST['page'].'&action=delete&shortcode[]='.$item['name'];
		$outputDelete .= '&_wpnonce='.wp_create_nonce('bulk-'.$this->_args['plural']).'"';
		$outputDelete .= ' onclick="return confirm('."'Shortcode will be permanently removed. Are you sure?');";
		$outputDelete .= '">Delete</a>';
		$outputExport = '<a style="font-weight:normal" href="?page='.$_REQUEST['page'].'&action=export&shortcode[]='.$item['name'];
		$outputExport .= '&_wpnonce='.wp_create_nonce('bulk-'.$this->_args['plural']).'">';
		$outputExport .= __('Export', 'ultimate-shortcodes-creator').'</a>';

		//wp_die(wp_nonce_field('bulk-'.$this->_args['plural']));

		$actions = array(
			//'edit'	=> sprintf('<a href="?page=%s&action=%s&shortcode=%s">Edit</a>', 'scu_edit_shortcode','edit',$item['name']),
			//'delete'	=> sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['name']),
			'enable'	=> $outputEnable,
			'edit'		=> $outputEdit,
			'delete'	=> $outputDelete,
			'export'	=> $outputExport
		);
		//http://localhost/wordpress/wp-admin/options.php?page=scu_edit_shortcode&shortcode=example
		//Return the title contents
		return sprintf('%1$s <span style="color:silver; font-weight:normal">(id:%2$s)</span>%3$s',
			/*$1%s*/ $item['name'],
			/*$2%s*/ $item['name'],
			/*$3%s*/ $this->row_actions($actions)
		);
	}
    protected function column_description($item) {
		//http://localhost/wordpress/wp-admin/options.php?page=scu_edit_shortcode&shortcode=example
		//Return the title contents
		$color = ($item['enabled']) ? "#555" : "silver";
		return sprintf('%1$s <div style="color:%2$s; font-weight:normal">%3$s</div>%4$s',			
			/*$1%s*/ $item['description'],
			/*$2%s*/ $color,
			/*$3%s*/ $item['author'],
			/*$4%s*/ ''//$this->row_actions($actions)
		);
	}
    public function get_columns() {
		$columns = array(
			'cb'			=> __('ID', 'ultimate-shortcodes-creator'), 	// Render a checkbox instead of text
			'name'			=> __('Name', 'ultimate-shortcodes-creator'),
			'description'	=> __('Description', 'ultimate-shortcodes-creator'),
			'shortcode'		=> __('SCU Shortcode', 'ultimate-shortcodes-creator'),
		);
		return $columns;	
	}
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'ID'			=> array('ID', false),	//true means it's already sorted
			'name'			=> array('name', false),	//true means it's already sorted			
			'description'	=> array('description', true),
			'shortcode'		=> array('shortcode', true)
		);
		return $sortable_columns;
	}
	protected function get_bulk_actions() {
		$actions = array(
			'delete'	=> __('Delete', 'ultimate-shortcodes-creator'),
			'enable'	=> __('Enable', 'ultimate-shortcodes-creator'),
			'disable'	=> __('Disable', 'ultimate-shortcodes-creator'),
			'export'	=> __('Export', 'ultimate-shortcodes-creator'),			
		);
		return $actions;
	}
	public function process_bulk_actions() {		
		// check if comes from a column_name action or bulk action
		if(!$this->current_action()) {			
			return;
		}		
		// nonce field is set by the parent class. wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		if(!wp_verify_nonce( wp_unslash($_REQUEST['_wpnonce']), 'bulk-'.$this->_args['plural'] )) {
			wp_die('wp_nonce Failed');
		}

		if(!isset($_GET['shortcode'])) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'shortcode', 'action', 'action2', '_wpnonce', '_wp_http_referer', 'paged', 'result' ) );
			wp_redirect( $_SERVER['REQUEST_URI'] );	
			exit;		
		}
		$shortcodesToDoAction = $_GET['shortcode'];

		switch ($this->current_action()) {			
			case 'delete':				
				/* Do not echo anything because later it will be redirect */	
				foreach ($shortcodesToDoAction as $key => $shortcode) {
					WP_Filesystem();
					global $wp_filesystem;
					$src_dir = $wp_filesystem->find_folder(\SCU\SC_PATH.$shortcode);
					$wp_filesystem->rmdir($src_dir, true);
				}
				$result = 'deleted';
				break;
			case 'enable':
				foreach ($shortcodesToDoAction as $key => $shortcode) {								
					$iniFileName = \SCU\SC_PATH.basename($shortcode).'/scu-config.ini';
					$iniFile = new \WriteiniFile\WriteiniFile($iniFileName);
					$iniFile->update(['config' => ['enabled' => true]]);
					$iniFile->write();
				}
				$result = 'enabled';
				break;
			case 'disable':
				foreach ($shortcodesToDoAction as $key => $shortcode) {
					$iniFileName = \SCU\SC_PATH.basename($shortcode).'/scu-config.ini';
					$iniFile = new \WriteiniFile\WriteiniFile($iniFileName);
					$iniFile->update(['config' => ['enabled' => false]]);
					$iniFile->write();
				}
				$result = 'disabled';
				break;
			case 'export':
				WP_Filesystem();
				global $wp_filesystem;
				require_once(realpath(\SCU\PATH."vendor/ZipData.php"));

				if(count($shortcodesToDoAction) == 1) {					
					$source_dir = realpath(\SCU\SC_PATH.basename($shortcodesToDoAction[0]));			
					$destination_file = wp_normalize_path(\SCU\PATH.'temp/'.basename($shortcodesToDoAction[0]).'.zip');
										
					$result = \ZipData::zip_files($source_dir, $destination_file);
					
					if(!$result) {
						$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'shortcode', 'action', 'action2', '_wpnonce', '_wp_http_referer', 'paged' ) );
						wp_redirect( esc_url_raw( add_query_arg( 'result', 'failed' ) ) );
						exit;
					}					

					ob_clean();
					ob_end_flush(); // more important function - (without - error corrupted zip)
					header('Content-Description: File Transfer');
					header('Content-Type: application/zip');
					header('Content-Disposition: attachment; filename="'.basename($shortcodesToDoAction[0]).'.zip"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header("Content-Transfer-Encoding: binary");
					header('Pragma: public');
					header('Content-Length: '.filesize($destination_file));
					readfile($destination_file);
					unlink($destination_file);
					exit;
				} else {
					$tmp_dir = wp_normalize_path(\SCU\PATH.'temp/scu_backup/');
					$wp_filesystem->rmdir($tmp_dir, true);
					$wp_filesystem->mkdir($tmp_dir);
					foreach ($shortcodesToDoAction as $key => $shortcode) {		
						$source = realpath(\SCU\SC_PATH.basename($shortcode));			
						$destination = wp_normalize_path(\SCU\PATH.'temp/scu_backup/'.basename($shortcode).'.zip');
						$result = \ZipData::zip_files($source, $destination);
						if(!$result) {
							$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'shortcode', 'action', 'action2', '_wpnonce', '_wp_http_referer', 'paged' ) );
							wp_redirect( esc_url_raw( add_query_arg( 'result', 'failed' ) ) );
							exit;
						}						
					}
					$source = $tmp_dir;
					$destination_file = wp_normalize_path(\SCU\PATH.'temp/scu_backup.zip');
					$result = \ZipData::zip_files($source, $destination_file);
					header('Content-Description: File Transfer');
					header('Content-Type: application/zip');
					header('Content-Disposition: attachment; filename="scu_partial_backup-'.date("Ymd-His").'.zip"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header("Content-Transfer-Encoding: binary");
					header('Pragma: public');
					header('Content-Length: '.filesize($destination_file));
					readfile($destination_file);
					unlink($destination_file);
					$wp_filesystem->rmdir($tmp_dir, true);
					exit;
				}							
				//$result = 'exported';
				break;
			default:
				break;
		}
		
		if ( isset( $result ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'shortcode', 'action', 'action2', '_wpnonce', '_wp_http_referer', 'paged' ) );
			wp_redirect( esc_url_raw( add_query_arg( 'result', $result ) ) );
			exit;
		}
				
	}

    function usort_reorder($a,$b) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'name'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
     }
	function prepare_items() {
        global $wpdb; //This is used only if making any database queries        
		
		$per_page = 15;						// Records per page
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_actions();		// Process the bulk actions
        $data = $this->data;        
        usort($data, array($this, 'usort_reorder'));
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);		
		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}

	public function single_row( $item ) {
		//$class = ($item['enabled']) ? "scu-shortcode-enabled" : "scu-shortcode-disabled";
    	//echo '<tr class="'.$class.'">';
		$style = ($item['enabled']) ? "background-color: rgba(120,200,230,.06); font-weight:bold" : "background-color: #f6f6f6";		
    	echo '<tr style="'.$style.'">';
    	$this->single_row_columns( $item );
    	echo '</tr>';
	}

	/* Setup the class */
	public function __construct() {
		require_once (\SCU\PATH.'/vendor/WriteiniFile.php');
		
		parent::__construct(array(
			'ajax'     => true,
			'plural'   => 'shortcodes',
			'singular' => 'shortcode',
		));

		$this->setShortcodesData();

		add_action('admin_head', array($this, 'table_css'), 10);	// Add css to the table
	}

	public function table_css() {
		echo '<style type="text/css">
        .column-name { width:20%; overflow:hidden }
        .column-description { width: 40% !important; overflow:hidden }
        .column-shortcode { width: 40% !important; overflow:hidden }
    	</style>';
	}
}
?>