
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
							
				var current = this;
$(this).on("click", "button", function(event) {
	event.preventDefault()
	ajaxdata['name'] = $(current).find("#name").val();	// Example if you want send fields one by one
	ajaxdata['form'] = $(current).find("#customform").serialize();
	
	$.ajax({
		method: "POST",
		url: ajaxurl,
		dataType: "json",
		data: ajaxdata
	})
	.done(function(response) {
		if(response==1) {
			$(current).find(".form-success > p").html("Your mail has been sent successfully and inserted in the database");
			$(current).find(".myform").hide();
			$(current).find(".form-success").show();
			//$(current).html('Everything is OK');
		}
		else {
			alert('Something went wrong');
		}		
	});
});
				/***************************************************
				* End of specific shortcode js
				****************************************************/
				
				});
			});
			
		})(jQuery);

		