// IIFE (Immediately Invoked Function Expression)
// Anonymous function (no named function), can be executed only once
// Wraps the code so that $ is jQuery inside that closure, even if $ means something else outside of it					
// Closure. Protect the global scope
// If function would be assigned to a var (not IIFE), it is a mean of  Namespacing the javascript. var cmb_shortcodes = cmb_shortcodes || {};

(function($) {
	var isSubmitting = false;		
	$(function() {	
		"use strict";	// Throws more exceptions

		var editorSettings = wp.codeEditor.defaultSettings;

		editorSettings.codemirror.mode = 'css';		
		var editor_css = wp.codeEditor.initialize( $('#code_editor_css'), editorSettings);
	
		editorSettings.codemirror.mode = 'javascript';		
		var editor_js = wp.codeEditor.initialize( $('#code_editor_js'), editorSettings);

		editorSettings.codemirror.mode = 'php';
		editorSettings.codemirror.lint = false;
		var editor_php = wp.codeEditor.initialize( $('#code_editor_ajax'), editorSettings);

		editorSettings.codemirror.mode = 'php';
		editorSettings.codemirror.lint = false;
		var editor_html = wp.codeEditor.initialize( $('#code_editor_html'), editorSettings);

		
			
		/*
		$('form').submit(function() {
			isSubmitting = true;
		});
		$('form').data('initial-state', $('form').serialize());
		$(window).on('beforeunload', function() {
			if (!isSubmitting && $('form').serialize() != $('form').data('initial-state')) {
				return 'You have unsaved changes which will not be saved.'
			}			
		});
		*/

		$("input[name='shortcode_name']").on('keypress', function(e) {
			if((e.charCode < 97 || e.charCode > 122) && (e.charCode < 48 || e.charCode > 57) && (e.charCode != 45)) return false;   			
		});

		$(".tab-div").hide();
		$("#scu-edit-general-div").show();
		$("#scu-edit-content").css("visibility", "visible");

		$(".nav-tab-wrapper").on("click", ".nav-tab", function (event) {
			$(".nav-tab").removeClass("nav-tab-active");
			$(".tab-div").hide();
			$(event.target).addClass("nav-tab-active");
			$('#'+event.target.id.replace("tab", "div")).show();
			switch(event.target.id) {
				case 'scu-edit-general-tab':
					$("#title").prop('disabled', false);
					$(".submit").show();
					break;
				case 'scu-edit-block-tab':
					$("#title").prop('disabled', false);
					$(".submit").show();					
					break;
				case 'scu-edit-html-tab':
					$("#title").prop('disabled', false);
					$(".submit").show();					
					break;
				case 'scu-edit-css-tab':
					$("#title").prop('disabled', false);
					$(".submit").show();				
					break;
				case 'scu-edit-js-tab':
					$("#title").prop('disabled', false);
					$(".submit").show();				
					break;
				case 'scu-edit-ajax-tab':
					$("#title").prop('disabled', false);
					$(".submit").show();				
					break;		
				case 'scu-edit-resources-css-tab':
					$("#title").prop('disabled', true);
					$(".submit").hide();					
					break;
				case 'scu-edit-resources-js-tab':
					$("#title").prop('disabled', true);
					$(".submit").hide();				
					break;
				case 'scu-edit-resources-assets-tab':
					$("#title").prop('disabled', true);
					$(".submit").hide();				
					break;
				case 'scu-edit-head-or-footer-tab':
					$("#title").prop('disabled', false);
					$(".submit").show();				
					break;
				default:
					break;
			}
		});

		$("#scu-edit-general-blocks").on("click", "input[type='checkbox']", function (event) {
			if($("#scu-edit-general-blocks input:checkbox:checked").length > 0) {
				$("#scu-edit-block-tab").show();
			}
			else {
				$("#scu-edit-block-tab").hide();
			}		
		});

		$("#scu-edit-general-type").on("click", "input[type='checkbox']", function (event) {
			switch(event.target.id) {
				case 'general_type_html':
					$("#scu-edit-html-tab").toggle();					
					break;
				case 'general_type_css':
					$("#scu-edit-css-tab").toggle();					
					break;
				case 'general_type_js':
					$("#scu-edit-js-tab").toggle();					
					break;
				case 'general_type_ajax':
					$("#scu-edit-ajax-tab").toggle();
					break;
				case 'general_type_resources_css':
					$("#scu-edit-resources-css-tab").toggle();					
					break;				
				case 'general_type_resources_js':
					$("#scu-edit-resources-js-tab").toggle();					
					break;
				case 'general_type_resources_assets':
					$("#scu-edit-resources-assets-tab").toggle();					
					break;
				case 'general_type_head_or_footer':
					$("#scu-edit-head-or-footer-tab").toggle();					
					break;			
				default:
					break;
			}
		});

		$(".scu-filelist").on("click", ".scu-file-action", function(e) {
			e.preventDefault();
			const urlParams = new URLSearchParams(window.location.search);
			const shortcode = urlParams.get('shortcode');
			let resource_type;
			switch($(this).parent().parent().attr('id')) {
				case 'scu-filelist-assets':
					resource_type = 'assets'
					break;
				case 'scu-filelist-css':
					resource_type = 'css'
					break;
				case 'scu-filelist-js':
					resource_type = 'js'
					break;
			}
			const file_name = $(this).parent(".scu-file").children(".scu-file-name").children("b").text();
			const fileDom = $(this).parent(".scu-file");
			//console.log('Shortcode: '+shortcode+'; Resource Type: '+resource_type+'; File Name: '+file_name);

			$.ajax({
				method: "POST",
				url: scu_ajax_edit.ajaxurl,
				dataType: "json",
				data: {action: 'scu_file_remove', ajaxNonce: scu_ajax_edit.ajaxNonce, scu_shortcode: shortcode, resource_type: resource_type, file_name: file_name},
			})
			.done(function(response) {
				if(response==true) {
					fileDom.remove();
				}		
			});		

		});
	});
})(jQuery);