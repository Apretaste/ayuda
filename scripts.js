$(document).ready(function () {
	$('.modal').modal();
	$('.tabs').tabs();

	// display either the list of chats or the message
	if (typeof chat == 'undefined') chat = [];
	if(chat.length) $('#chat').css('display', 'block');
	else $('#msg').css('display', 'block');
});

function sendMessage() {
	// get the message
	var message = $('#message').val().trim();

	// do not allow short messages
	if(message.length < 30) {
		M.toast({html: 'Mínimo 30 caracteres'});
		return false;
	}

	// post the message
	apretaste.send({
		'command': 'AYUDA ESCRIBIR',
		'data': {'message': message},
		'redirect': false
	});

	// add the message to the screen
	addTextBubble("salvi", message);
}

function messageLengthValidate() {
	var message = $('#message').val().trim();
	if(message.length <= 1000) $('.helper-text').html('Restante: '+(1000-message.length));
	else $('.helper-text').html('Limite excedido');
}

function addTextBubble(username, message) {
	// prepare in case ios first time
	$('#chat').show();
	$('#msg').hide();

	// create bubble date
	var now = new Date(Date.now()).toLocaleString();
	now = now.replace('p. m.','pm');
	now = now.replace('a. m.','am');

	// append the bubble to teh screen
	$('#bubbles').append('<div class="bubble me"><small><b>'+username+'</b> - '+now+'<div class="chip small white-text blue lighten-1">NEW</div></small><br>'+message+'</div>');

	// scroll to the last bubble
	var lastBubble = $(".bubble:last-of-type");
	if(lastBubble) {
		$('html, body').animate({
			scrollTop: $(".bubble:last-of-type").offset().top
		}, 1000);
	}
}

function vote(id, vote) {
	// save the vote in the backend
	apretaste.send({
		"command": "AYUDA VOTAR",
		"data": {'id':id,'vote':vote},
		"redirect": false
	});

	// change the message
	$('#survey').html('¡Gracias por dejarnos su opinión!');
}