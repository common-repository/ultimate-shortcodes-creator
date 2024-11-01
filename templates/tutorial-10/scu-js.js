/****************************************************************************************
* The needed variables for the ajax call are:
*  ajaxurl:			(String) the url for admin-ajax.php
*  ajaxdata:		(Object) for data parameters in jQuery ajax. Which includes:
* 						ajaxdata["shortcode"]: (needed to redirect to the shortcode specific scu-ajax-handler.php)
*						ajaxdata["security"]: (with an ajaxNonce)
*						ajaxdata["action"]: (needed for hook in the admin-ajax.php) 
*****************************************************************************************/

$(this).html('<button>Make Ajax call</button>');

var current = this;
var email = $(current).data()["email"];			// Retrieve the shortcode attribute 'email'
$(this).on("click", "button", function(event) {	
	ajaxdata['email'] = email;	// You can add additional parameters in the ajax call to admin-ajax.php	
	$.ajax({
		method: "POST",
		headers : {'Scu-Referer' : url.substring(0, url.lastIndexOf('/'))},		// Optional custom header
		url: ajaxurl,
		dataType: "json",
		data: ajaxdata
	})
	.done(function(response) {				
		$(current).html(response);
	});
});