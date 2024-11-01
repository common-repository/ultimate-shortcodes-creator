
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
					var ajaxdata = {
						shortcode: shortcode,
						action: 'scu_ajax_handler',
						security: scu_common.ajaxNonce,						
						//i18n: i18n,						
					};				
					
				/***************************************************
				* Begin specific shortcode js
				****************************************************/
							
				
				/***************************************************
				* End of specific shortcode js
				****************************************************/
				
				});
			});
			
		})(jQuery);

		