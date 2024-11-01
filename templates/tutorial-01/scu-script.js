
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
							
				/***************************************************************************
 * Shortcode: 	[scu name=example_complete]content[/scu]
 * Desciption: 	Change the font color and add a button 
 ***************************************************************************/

/***************************************************************************
* Available JS variables:
*  current:		(DOM Selector) points to each of the automatically created divs
*  shortcode:	(String) name of the shortcode
*  resourcesUrl:(String) resources files URL
*  content:		(String) shortcode content
*  atts:		(Array)  of shortcode atts  
*  ajaxurl:		(String) the url for admin-ajax.php
*  ajaxdata:	(Object) for data parameters in jQuery ajax. Including:
*						 param security (with an ajaxNonce)
*						 param action (needed for hook in the admin-ajax.php) 
*						 param shortcode (needed to redirect to the shortcode specific cmb-ajax-handler.php)*						 
*						 param content (with the var content value)
*						 param atts (with the var atts value)
****************************************************************************/

				/***************************************************
				* End of specific shortcode js
				****************************************************/
				
				});
			});
			
		})(jQuery);

		