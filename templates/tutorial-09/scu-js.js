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