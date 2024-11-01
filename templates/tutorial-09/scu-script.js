
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
							
				/****************************************************************************************
 * Shortcodes create a wrapping div as:
 * <div class="scu-shortcode sc-tutorial-8" data-name="tutorial-8" data-att="some att">
 * 		(The output of the HTML/PHP Code tab)
 * </div>
 * 
 * shortcode content and attributes can be easily retrieved :
 * 	var content = $(this).html();
 *	var atts = $(this).data();
 
 * There are also some available useful JS variables:
 * 	shortcode:		(String) name of the shortcode
 * 	resources_url:	(String) resources files URL
****************************************************************************************/

let content = $(this).html();
let atts = $(this).data();
let background_color = atts["color"];

$(this).html('<img src="'+resources_url+'image.png" style="float:left";>');
$(this).append('<button style="background-color:'+background_color+'">'+content+'</button>');

$(this).on("click", "button", function(event) {
	alert('Button has been clicked !!!');
});
				/***************************************************
				* End of specific shortcode js
				****************************************************/
				
				});
			});
			
		})(jQuery);

		