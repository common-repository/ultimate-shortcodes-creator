// IIFE (Immediately Invoked Function Expression)
// Anonymous function (no named function), can be executed only once
// Wraps the code so that $ is jQuery inside that closure, even if $ means something else outside of it					
// Closure. Protect the global scope
// If function would be assigned to a var (not IIFE), it is a mean of  Namespacing the javascript. var cmb_shortcodes = cmb_shortcodes || {};

(function($) {
	var isSubmitting = false;		
	$(function() {	
		"use strict";	// Throws more exceptions		
		
		$("#scu-add-menu-notices").on("click", ".notice-dismiss", function(e) {
			$(this).parent().fadeOut(1000, function(){ $(this).remove();});
		});

		$("#scu-add-button-upload").on("click", function(e) {
			e.preventDefault();
			$("#scu-add-tab-upload").toggle();
			$(".upload-plugin").toggle();
		});

		$(".wrap").on("click", "#scu-add-button-create", function(e) {
			e.preventDefault();
			var data = {
				action: 'scu_add_create',
				ajaxNonce: scu_ajax_add.ajaxNonce
			};
			$.ajax({
				method: "POST",
				url: scu_ajax_add.ajaxurl,
				dataType: "json",
				data: data
			})
			.done(function(response) {
				if(response.OK) {
					console.log(response.url_redirect);
					window.location.replace(response.url_redirect);
				}
				else {
					var html ='<div id="scu-add-menu-notice" class="notice settings-error is-dismissible notice-error" style="display:block">';
					html += '<p><strong>Error: '+response.error.message+'</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-add-menu-notices').append(html);
				}
			});
		});

		$("#scu-add-upload-form").submit(function(e) {
			e.preventDefault();
			var form = $(this);
    		var formdata = false;
    		if (window.FormData){
        		formdata = new FormData(form[0]);
    		}
			$.ajax({
				method: "POST",
				url: scu_ajax_add.ajaxurl,
				dataType: "json",
				data: formdata ? formdata : form.serialize(),
				cache: false,
				contentType : false,
				processData : false,				
			})
			.done(function(response) {
				if(!response.OK) {
					var html ='<div id="scu-add-menu-notice" class="notice settings-error is-dismissible notice-error">';
					html += '<p><strong>Error: '+response.error.message+'</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-add-menu-notices').append(html);
				}
				else {
					var html ='<div id="scu-add-menu-notice" class="notice settings-success is-dismissible notice-success">';
					html += '<p><strong>'+response.message+'</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-add-menu-notices').append(html);
				}
				$('#file').val('');
				$('#install-shortcode-submit').attr("disabled", true);
			});
		});

		$("#scu-add-tab-remote").on("click", ".scu-remote-li", function(e) {
			e.preventDefault();
			$(".scu-remote-li a").removeClass('current');
			$(e.target).addClass('current');
			switch(e.currentTarget.id) {
				case 'plugin-install-simple':					
					var tab = 'simple'
					break;
				case 'plugin-install-easy':
					var tab = 'easy'
					break;
				case 'plugin-install-intermediate':
					var tab = 'intermediate'
					break;
				case 'plugin-install-advanced':
					var tab = 'advanced'
					break;
				case 'plugin-install-expert':
					var tab = 'expert'
					break;			
			}

			var data = {
				tab: tab, 
				action: 'scu_show_remote',
				ajaxNonce: scu_ajax_add.ajaxNonce
			};
			$.ajax({
				method: "POST",
				url: scu_ajax_add.ajaxurl,
				dataType: "json",
				data: data
			})
			.done(function(response) {
				if(!response.OK) {
					var html ='<div id="scu-add-menu-notice" class="notice settings-error is-dismissible notice-error">';
					html += '<p><strong>Error: '+response.error.message+'</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-add-menu-notices').append(html);
				}
				else {
					//var html ='<div id="scu-add-menu-notice" class="notice settings-success is-dismissible notice-success">';
					//html += '<p><strong>'+response.message+'</strong></p>';
					//html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					//html += 'Descartar este aviso.</span></button></div>';
					//$('#scu-add-menu-notices').append(html);
					
					$('#scu-remote-shortcodes').html('');
					var remote_url='http://shortcodescreator.com?p=';
					response.shortcodes.forEach(function(item, index, arr) {
						var html = '<div class="plugin-card plugin-card-'+item.slug+'">';
						html += '<div class="plugin-card-top">';
						html += '<div class="name column-name">';
						html += '<h3><a href="'+remote_url+item.id
						//html += '&TB_iframe=true&width=600&height=550" ';
						html += ' class="thickbox open-plugin-details-modal" target="_blank">'+item.title;
						html += '<img src="'+item.url_img+'" class="plugin-icon">';
						html += '</a></h3></div>';
						html += '<div class="action-links">';
						//html += '<a href="'+item.url_download+'" class="install-now button">Install Now</a>';
						html += '<button data-urldownload="'+item.url_download+'" class="install-now button">Install Now</button>';
						html += '</div>';
						html += '<div class="desc column-description">';
						html += '<p>'+item.excerpt;
						html += '</p>';
						html += '<p class="authors"><cite>By <a href="#">César Morillas</a></cite>';
						html += '</p></div>';
						html += '<div>';
						$('#scu-remote-shortcodes').append(html);
					});					
				}
			});
		});

		$("#scu-remote-shortcodes").on("click", ".install-now", function(e) {			
			e.preventDefault();			
			var data = {
				url_shortcode: e.currentTarget.getAttribute('data-urldownload'),				
				action: 'scu_add_remote',
				ajaxNonce: scu_ajax_add.ajaxNonce
			};
			$.ajax({
				method: "POST",
				url: scu_ajax_add.ajaxurl,
				dataType: "json",
				data: data
			})
			.done(function(response) {
				//console.log(response);
				if(!response.OK) {
					var html ='<div id="scu-add-menu-notice" class="notice settings-error is-dismissible notice-error">';
					html += '<p><strong>Error: '+response.error.message+'</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-add-menu-notices').append(html);
				}
				else {
					var html ='<div id="scu-add-menu-notice" class="notice settings-success is-dismissible notice-success">';
					html += '<p><strong>'+response.message+'</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-add-menu-notices').append(html);
					$(e.currentTarget).attr("disabled", true);
					$(e.currentTarget).html('Installed');
				}								
			});			
		});

		$('#plugin-install-simple a').trigger('click');
	});
})(jQuery);