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