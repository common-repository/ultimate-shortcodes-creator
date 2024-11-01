<form id="customform" class="myform" action="#" method="post">
	<div class="elem-group">
		<label for="name">Your Name</label>
		<input type="text" id="name" name="visitor_name" placeholder="John Doe" pattern=[A-Z\sa-z]{3,20} required>
	</div>
	<div class="elem-group">
		<label for="email">Your E-mail</label>
		<input type="email" id="email" name="visitor_email" placeholder="john.doe@email.com" required>
	</div>
	<div class="elem-group">
		<label for="phone">Your Phone</label>
		<input type="text" id="phone" name="visitor_phone" placeholder="915200012" required>
  </div>
  <div class="elem-group">
    <label for="position">Your Position</label>
    <input type="text" id="position" name="visitor_position" placeholder="CEO of the company" required>
  </div>  
  <div class="elem-group" style="width:100%">
    <label for="message">Write your message</label>
    <textarea id="message" name="visitor_message" placeholder="Say whatever you want." required></textarea>
  </div>
  <button type="submit">Send Message</button>
</form>
<div class="form-success" style="display:none">
	<h2>Thanks for your interest</h2>
	<p></p>
</div>