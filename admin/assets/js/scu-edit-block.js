// IIFE (Immediately Invoked Function Expression)
// Anonymous function (no named function), can be executed only once
// Wraps the code so that $ is jQuery inside that closure, even if $ means something else outside of it					
// Closure. Protect the global scope
// If function would be assigned to a var (not IIFE), it is a mean of  Namespacing the javascript. var cmb_shortcodes = cmb_shortcodes || {};

(function($) {
	var isSubmitting = false;	
	const { __, _x, _n, _nx } = wp.i18n;

	function isEmpty(str) {
		if(str === undefined || str === null || str==='') {
			return true;
		} else {
			return false;
		}
	}

	/* Adds a new attribute. If index is defined is because it comes from previous saved attributes
	   which are stored in .block-attributes-orig */
	function addNewAttr(attr = {name:"", description:"", type:"", params:""}) {
		var html = '<div class="scu-attribute">';
			html += '<div class="accordion">'+__('Name', 'ultimate-shortcodes-creator');
			html += ': <span style="color: #666">'+attr.name+'</span></div>';
			html += '<div class="panel" style="display:none; width:auto">';
			html += '<label for="scu-attribute-name">'+__('Name', 'ultimate-shortcodes-creator')+':</label><br>';
			html += '<input class="widefat" name="scu-attribute-name[]" style="width:auto" type="text" value="';
			html += attr.name+'">';			
			html += '<p>';
			html += '<label for="scu-attribute-description">'+__('Description', 'ultimate-shortcodes-creator');
			html += ':</label><br>';
			html += '<input class="widefat" name="scu-attribute-description[]" type="text" value="';
			html += attr.description+'">';			
			html += '</p>';
			html += '<p>';
			html += '<label for="scu-attribute-type">'+__('Type', 'ultimate-shortcodes-creator')+':</label><br>';
			html +=	'<!-- https://github.com/WordPress/gutenberg/tree/master/packages/components/src -->';
			html += '<select class="scu-attribute-type" name="scu-attribute-type[]">';
			html += '<option value="inputcontrol">'+__('Input Control', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="colorpicker">'+__('Color Picker', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="numbercontrol">'+__('Number Control', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="togglecontrol">'+__('Toggle Control', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="selectcontrol">'+__('Select Control', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="rangecontrol">'+__('Range Control', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="radiocontrol">'+__('Radio Control', 'ultimate-shortcodes-creator')+'</option>';
			//html += '<option value="radiogroup">'+__('Radio Group', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="timepicker">'+__('Time Picker', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="unitcontrol">'+__('Unit Control', 'ultimate-shortcodes-creator')+'</option>';
			html += '<option value="filepicker">'+__('File Picker', 'ultimate-shortcodes-creator')+'</option>';
			html += '</select>';
			html += '<input type="hidden" name="scu-attribute-params[]" value="';
			html += attr.params+'">';
			html += '<a href="#TB_inline?&width=600&height=550&inlineId=scu-edit-params" ';
			html += 'class="button button-small thickbox scu-edit-button-params" ';
			html += 'name="'+__('Edit Parameters', 'ultimate-shortcodes-creator')+'" ';
			html += 'style="vertical-align: middle; margin-left:10px;">';
			html += '<span class="dashicons dashicons-arrow-right" style="vertical-align: middle; padding-bottom: 1px; margin: 0 -2px 0 -5px;"></span>';
			html += __('Params', 'ultimate-shortcodes-creator');
			html += '</a>';
			html += '</p>';
			html += '<br class="clear">';
			html += '<div class="widget-control-actions">';
			html += '<div class="alignleft">';
			html += '<button type="button" class="button-link button-link-delete scu-edit-attribute-del">';
			html += __( 'Delete' );
			html += '</button>';
			html += '</div>';
			html += '<div class="alignright"></div>';
			html += '<br class="clear">';
			html += '</div>';
			html += '<br>';
			html += '</div>  <!-- panel -->';
			html += '<!-- scu-attribute --> </div>'; 
			
			$(".scu-attributes-sort").append(html);
			$("#scu-edit-block-div").find(".scu-attribute-type").last().val(attr.type).change();
	}

	$(function() {
		$( ".scu-attributes-sort" ).sortable({
			placeholder: "ui-state-highlight"
		});
		$( ".scu-attributes-sort" ).disableSelection();

		// Show or hide content textarea if has_content checkbox is checked or not
		$("#block-general-hascontent").on("click", function(event) {
			$("#block-general-defaultcontent-div").toggle();			
		});

		// Accordion
		$("#scu-edit-block-div").on("click",".accordion", function(event) {
			var $panel = $(this).next();
			$panel.toggle();
			$(this).toggleClass("active");
		});

		// Set the saved attributes which are stored in .blocl-attributes-orig and call to add New Attribute
		$(".block-attributes-orig" ).each(function( index, element ) {
			let attr = {name:"", description:"", type:"", params:""};
			attr.name = $(this).find(".scu-attribute-orig-name").val();
			attr.description = $(this).find(".scu-attribute-orig-description").val();
			attr.type = $(this).find(".scu-attribute-orig-type").val();
			attr.params = $(this).find(".scu-attribute-orig-params").val();

			addNewAttr(attr);
		})		

		/* Change parameters in hidden input[name=scu-attribute-params] when params thickbox done button clicked */
		$("#scu-edit-attribute-done").click(function(event) {
			var inputValues = $(".scu-edit-param").map(function() {
				return $(this).val();
			}).toArray().join('|');
			$(".scu-attribute-active").find("input[name='scu-attribute-params[]']").val(inputValues);
			tb_remove();
		});

		/* Change the name of the attribute title when changing in the input field*/
		$("#scu-edit-block-div").on("keyup paste", ".scu-attribute input[name='scu-attribute-name[]']", function(event) {
			$(this).parents(".scu-attribute").find(".accordion span").text($(this).val());
		});

		/* Remove attribute when delete button clicked */
		$("#scu-edit-block-div").on("click", ".scu-edit-attribute-del", function(event) {
			$(this).parents(".scu-attribute").fadeOut(500, function() {
				$(this).remove();
			});
		});

		/* Add new option in select params */
		$("#scu-params-content").on("click", "#scu-params-select-add", function(event) {			
			html = '<tr>';
			html += '<th style="padding-right:30px">'+__('Option', 'ultimate-shortcodes-creator')+': </th>';
			html += '<td><input class="scu-edit-param" type="text" value=""></td>';
			html += '<td><input class="scu-edit-param" type="text" value=""></td>';
			html += '<td><button type="button" class="button-link button-link-delete scu-params-select-del">';
			html += __( 'Delete' )+'</button></td>';				
			html += '</tr>';
			$("#scu-params-select-table").append(html);
		});

		/* Remove option in select params */
		$("#scu-params-content").on("click", ".scu-params-select-del", function(event) {
			$(this).closest("tr").fadeOut(500, function() {
				$(this).remove();
			});
		});

		/* Fill the #scu-edit-params modal thickbox */
		$("#scu-edit-block-div").on("click", ".scu-edit-button-params", function(event) {
			$(".scu-attribute").removeClass("scu-attribute-active");
			$(this).parents(".scu-attribute").addClass("scu-attribute-active");
			var type = $(this).parents(".scu-attribute").find(".scu-attribute-type").val();
			var params = $(this).parents(".scu-attribute").find("input[name='scu-attribute-params[]']").val().split('|');			
			switch(type) {
			case 'inputcontrol':
				var html = '<h3>Input Control</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				html += '<p>';
				html += '<label>Helper:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[1]))?(params[1]):'')+'">';
				html += '</p>';
				break;
			case 'colorpicker':
				var html = '<h3>Color Picker</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				break;
			case 'timepicker':
				var html = '<h3>Time Picker</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				break;
			case 'numbercontrol':
				var html = '<h3>Number Control</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				html += '<p>';
				html += '<label>Min Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[1]))?(params[1]):'')+'">';
				html += '</p>';
				html += '<label>Max Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[2]))?(params[2]):'')+'">';
				html += '</p>';
				html += '<label>Step:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[3]))?(params[3]):'')+'">';
				html += '</p>';
				break;
			case 'togglecontrol':
				var html = '<h3>Toggle Control</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				html += '<p>';
				html += '<label>Helper True:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[1]))?(params[1]):'')+'">';
				html += '</p>';
				html += '<p>';
				html += '<label>Helper False:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[2]))?(params[2]):'')+'">';
				html += '</p>';
				break;
			case 'rangecontrol':
				var html = '<h3>Range Control</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				html += '<p>';
				html += '<label>Min Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[1]))?(params[1]):'')+'">';
				html += '</p>';
				html += '<label>Max Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[2]))?(params[2]):'')+'">';
				html += '</p>';
				html += '<label>Step:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[3]))?(params[3]):'')+'">';
				html += '</p>';
				html += '<p>';
				html += '<label>Helper:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[2]))?(params[2]):'')+'">';
				html += '</p>';
				break;
			case 'selectcontrol':
				var html = '<h3>Select Control</h3>';				
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';			
				html += '<table id="scu-params-select-table" style="margin-bottom:20px;"><tr><th></th>';
				html += '<th>'+__('Text', 'ultimate-shortcodes-creator')+':</th>';
				html += '<th>'+__('Value', 'ultimate-shortcodes-creator')+':</th>';
				html += '</tr>';
				html += '<tr>';
				html += '<th style="padding-right:30px">'+__('Option', 'ultimate-shortcodes-creator')+': </th>';
				html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[1]))?(params[1]):'')+'"></td>';
				html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[2]))?(params[2]):'')+'"></td>';					
				html += '</tr>';
				for (i = 3; i < params.length; i=i+2) {					
					html += '<tr>';
					html += '<th style="padding-right:30px">'+__('Option', 'ultimate-shortcodes-creator')+': </th>';
					html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[i]))?(params[i]):'')+'"></td>';
					html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[i+1]))?(params[i+1]):'')+'">';
					html += '<td><button type="button" class="button-link button-link-delete scu-params-select-del">';
					html += __( 'Delete' )+'</button></td>';					
					html += '</tr>';
				};
				html += '</table>';
				html += '<button id="scu-params-select-add" class="button button-small" style="margin-bottom: 20px;">';
				html += __('Add', 'ultimate-shortcodes-creator')+'</button>';
				break;
			case 'radiocontrol':
				var html = '<h3>Radio Control</h3>';				
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';			
				html += '<table id="scu-params-select-table" style="margin-bottom:20px;"><tr><th></th>';
				html += '<th>'+__('Text', 'ultimate-shortcodes-creator')+':</th>';
				html += '<th>'+__('Value', 'ultimate-shortcodes-creator')+':</th>';
				html += '</tr>';
				html += '<tr>';
				html += '<th style="padding-right:30px">'+__('Option', 'ultimate-shortcodes-creator')+': </th>';
				html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[1]))?(params[1]):'')+'"></td>';
				html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[2]))?(params[2]):'')+'"></td>';					
				html += '</tr>';
				for (i = 3; i < params.length; i=i+2) {					
					html += '<tr>';
					html += '<th style="padding-right:30px">'+__('Option', 'ultimate-shortcodes-creator')+': </th>';
					html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[i]))?(params[i]):'')+'"></td>';
					html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[i+1]))?(params[i+1]):'')+'">';
					html += '<td><button type="button" class="button-link button-link-delete scu-params-select-del">';
					html += __( 'Delete' )+'</button></td>';					
					html += '</tr>';
				};
				html += '</table>';
				html += '<button id="scu-params-select-add" class="button button-small" style="margin-bottom: 20px;">';
				html += __('Add', 'ultimate-shortcodes-creator')+'</button>';
				break;
			case 'radiogroup':
				var html = '<h3>Radio Group</h3>';				
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';			
				html += '<table id="scu-params-select-table" style="margin-bottom:20px;"><tr><th></th>';
				html += '<th>'+__('Text', 'ultimate-shortcodes-creator')+':</th>';
				html += '<th>'+__('Value', 'ultimate-shortcodes-creator')+':</th>';
				html += '</tr>';
				html += '<tr>';
				html += '<th style="padding-right:30px">'+__('Option', 'ultimate-shortcodes-creator')+': </th>';
				html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[1]))?(params[1]):'')+'"></td>';
				html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[2]))?(params[2]):'')+'"></td>';					
				html += '</tr>';
				for (i = 3; i < params.length; i=i+2) {					
					html += '<tr>';
					html += '<th style="padding-right:30px">'+__('Option', 'ultimate-shortcodes-creator')+': </th>';
					html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[i]))?(params[i]):'')+'"></td>';
					html += '<td><input class="scu-edit-param" type="text" value="'+((!isEmpty(params[i+1]))?(params[i+1]):'')+'">';
					html += '<td><button type="button" class="button-link button-link-delete scu-params-select-del">';
					html += __( 'Delete' )+'</button></td>';					
					html += '</tr>';
				};
				html += '</table>';
				html += '<button id="scu-params-select-add" class="button button-small" style="margin-bottom: 20px;">';
				html += __('Add', 'ultimate-shortcodes-creator')+'</button>';
				break;
			case 'unitcontrol':
				var html = '<h3>Color Picker</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				break;
			case 'filepicker':
				var html = '<h3>File Picker</h3>';
				html += '<p>';
				html += '<label>Default Value:</label><br>';
				html += '<input class="widefat scu-edit-param" type="text" value="'+((!isEmpty(params[0]))?(params[0]):'')+'">';
				html += '</p>';
				break;
			default:
				// code block
			}
			$("#scu-params-content").html(html);
		});

		/* Add new blank attribute */
		$("#scu-add-attribute").click(function(event) {
			event.preventDefault();
			addNewAttr();
		});
	});
})(jQuery);