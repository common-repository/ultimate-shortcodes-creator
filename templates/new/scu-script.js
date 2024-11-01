
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
 * <div class="scu-shortcode sc-tutorial" data-name="tutorial" data-att="some att">
 * 		(The output of the HTML/PHP Code tab)
 * </div>
 * 
 * shortcode content and attributes can be easily retrieved :
 * 	var content = $(this).html();
 *	var atts = $(this).data();
 
 * There are some available useful JS variables:
 * 	shortcode:		(String) name of the shortcode
 * 	resources_url:	(String) resources files URL
 * 
 * And there are also some needed variables for the ajax calls:
*  ajaxurl:			(String) the url for admin-ajax.php
*  ajaxdata:		(Object) for data parameters in jQuery ajax. Which includes:
* 						ajaxdata["shortcode"]: (needed to redirect to the shortcode specific scu-ajax-handler.php)
*						ajaxdata["security"]: (with an ajaxNonce)
*						ajaxdata["action"]: (needed for hook in the admin-ajax.php) 
****************************************************************************************/
				/***************************************************
				* End of specific shortcode js
				****************************************************/
				
				});
			});
			
		})(jQuery);

		