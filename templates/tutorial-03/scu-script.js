
		(function($) {					
			"use strict";	// Throws more exceptions
			var shortcode = document.currentScript.getAttribute('data-name');
			var ajaxurl = scu_common.ajaxurl;
			var scu_url = scu_common.scu_url;
			let url = document.currentScript.src;
			var resources_url = scu_url+shortcode+"/resources/assets/";
			//var resources_url = url.substring(0, url.lastIndexOf('/')) + "/resources/assets/";

			$(document).ready(function() {
				$(".sc-"+shortcode).each(function() {
					//var current = this;
					//var content = $(this).children(".scu-content").html();
					//var content = $(this).html();					
					//var atts = $(this).data();			
					var ajaxdata = {
						action: 'scu_ajax_handler',
						security: scu_common.ajaxNonce,
						//content: content,
						//i18n: i18n,
						//atts: atts
					};
				
					
				/***************************************************
				* Begin specific shortcode js
				****************************************************/
							
				//current.querySelector("p").style.setProperty('--scu-color', atts['color']);
current.querySelector(".scu-tutorial-3").style.setProperty('--scu-color', atts['color']);
				/***************************************************
				* End of specific shortcode js
				****************************************************/
				
				});
			});
			
		})(jQuery);

		