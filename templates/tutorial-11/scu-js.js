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