<?php
/**
 * The file that defines the core plugin class
 *
 * @author   cesar@shortcodescreator.com
 * @category API
 * @package  SCU_Admin
 */

namespace SCU\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin_Render_Atts {
	public static $shortcodeListTable;
	//private $shortcodeRedirect;

	private function _render($shortcode_ini, $attributes_ini) { ?>
		<div id="block-general" class="scu-edit-block">
			<div class="accordion"><?php _e('General', 'ultimate-shortcodes-creator'); ?></div>
			<div class="panel" style="display: none;">
				<p style="margin-top:0px">General parameters of the block.</p>
				<p>
					<label for="block-general-description"><?php _e('Block Description', 'ultimate-shortcodes-creator'); ?>:</label><br>
					<input class="widefat" name="block-general-description" type="text" value="<?php
					echo($shortcode_ini['block-general']['description']);
					?>">
				</p>
				<p>
					<label for="block-general-icon"><?php _e('Icon', 'ultimate-shortcodes-creator'); ?>:</label><br>
					<input id="block-general-icon" class="widefat dashicons-picker" name="block-general-icon" data-target="#block-general-icon" type="text" value="<?php
					echo($shortcode_ini['block-general']['icon']);
					?>">
				</p>
				<p>
					<label for="block-general-hascontent"><?php _e('Has Content', 'ultimate-shortcodes-creator'); ?>: <i>[scu]content[/scu]</i></label><br>
					<input class="widefat" id="block-general-hascontent" name="block-general-hascontent" type="checkbox" value="1" <?php
					echo(($shortcode_ini['block-general']['has_content']) ? 'checked' : '');
					?>>
				</p>				
				
				<div id="block-general-defaultcontent-div" style="<?php echo((!$shortcode_ini['block-general']['has_content']) ? 'display: none;' : '') ?>">
				<p>
					<label for="block-general-defaultcontent"><?php _e('Default Content', 'ultimate-shortcodes-creator'); ?>:</label><br>
					<textarea class="widefat" id="block-general-defaultcontent" name="block-general-defaultcontent" rows="4"><?php echo($shortcode_ini['block-general']['defaultcontent']);?></textarea>
				</p>
				</div>				
				<br>
			</div>
		</div>

		<div id="block-attributes" class="scu-edit-block">
			<?php
			add_thickbox(); // See https://codex.wordpress.org/ThickBox
			foreach ($attributes_ini as $key => $att) { // All that stuf to make all render from js ?>
				<div class="block-attributes-orig">		
					<input type="hidden" class="scu-attribute-orig-name" value="<?php echo($att['name']);?>">
					<input type="hidden" class="scu-attribute-orig-description" value="<?php echo($att['description']);?>">
					<input type="hidden" class="scu-attribute-orig-type" value="<?php echo($att['type']);?>">
					<input type="hidden" class="scu-attribute-orig-params" value="<?php echo($att['params']);?>">
				</div>  <!-- block-attributes-inputs -->
			<?php }	?>	<!-- foreach -->
			<div class="accordion"><?php _e('Attributes', 'ultimate-shortcodes-creator'); ?></div>
			<div class="panel" style="display: none;">
				<button id="scu-add-attribute" class="button" style="margin-bottom:15px;">
					<?php _e('New', 'ultimate-shortcodes-creator'); ?>
				</button>
				<div class="scu-attributes-sort">
				</div>  <!-- scu-attributes-sort -->
				<br>
			</div>  <!-- panel outter-->
		</div>  <!-- block-attributes -->

		<div id="scu-edit-params" style="display:none;">		<!-- Same for every attribute -->
			<div id="scu-params-content"></div>			<!-- This div will be filled in scu-edit-block.js events -->
			<br class="clear">		
			<div class="widget-control-actions">
				<div class="alignleft">
					<button type="button" id="scu-edit-attribute-done" class="button-link"><?php _e( 'Done' ); ?></button>
					<span class="widget-control-close-wrapper">|
					<button type="button" id="scu-edit-attribute-cancel" class="button-link widget-control-close" onclick="tb_remove();"><?php _e('Cancel'); ?></button>
					</span>
				</div>
				<div class="alignright">
						
				</div>
				<br class="clear">
			</div>
			<br>
		</div>

	<?php
	}
	
	public function __construct($shortcode_ini, $attributes_ini) {
		$this->_render($shortcode_ini, $attributes_ini);
	}
}
$adminRenderAtts = new Admin_Render_Atts($shortcode_ini, $attributes_ini);	// Without Singletron Pattern
?>
