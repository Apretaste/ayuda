var status = {NEW: 'NUEVO', PENDING: 'PENDIENTE', DONE: 'TERMINADO'};

$(document).ready(function () {
	$('.modal').modal();
	$('.tabs').tabs();

	// default values
	if (typeof chat == 'undefined') chat = [];
	if (typeof username == 'undefined') username = "";

	// display either the list of chats or the message
	if (chat.length) $('#chat').css('display', 'block');
	else $('#msg').css('display', 'block');

	if (chat.length) {
		$('html, body').animate({
			scrollTop: $(".bubble:last-of-type").offset().top
		}, 1000);
	}
});

function sendMessage(minLength) {

	if (typeof minLength == 'undefined') minLength = 30;

	// get the message
	var message = $('#message').val().trim();
	var parentTicket = $('#parent').val().trim();

	// do not allow short messages
	if (message.length < minLength) {
		M.toast({html: 'Describanos mejor su caso'});
		return false;
	}

	var data = {
		message: message
	};

	if (parentTicket !== '') {
		data.parentTicket = parentTicket;
	}

	// post the message
	apretaste.send({
		command: 'AYUDA ESCRIBIR',
		data: data,
		redirect: true /*,
		callback: {
			name: 'sendMessageCallback',
			data: message
		}*/
	});
}

function messageLengthValidate() {
	var message = $('#message').val().trim();
	if (message.length <= 200) $('.helper-text').html('Restante: ' + (200 - message.length));
	else $('.helper-text').html('Limite excedido');
}

function addTextBubble(username, message) {
	// prepare in case ios first time
	$('#chat').show();
	$('#msg').hide();

	// create bubble date
	var now = new Date(Date.now()).toLocaleString();
	now = now.replace('p. m.', 'pm');
	now = now.replace('a. m.', 'am');

	// append the bubble to teh screen
	$('#bubbles').append('<div class="bubble me"><span class="small"><b>@' + username + '</b> - ' + now + '</span><br>' + message + '</div>');

	// scroll to the first bubble, if exist
	if (chat.length) {
		$('html, body').animate({
			scrollTop: $(".bubble:last-of-type").offset.top
		}, 1000);
	}
}

function vote(id, vote) {
	// save the vote in the backend
	apretaste.send({
		"command": "AYUDA VOTAR",
		"data": {'id': id, 'vote': vote},
		"redirect": false
	});

	// change the message
	$('#survey').html('¡Gracias por dejarnos su opinión!');
}

function sendMessageCallback(message) {
	if (chat.length == 0) {
		// Jquery Bug, fixed in 1.9, insertBefore or After deletes the element and inserts nothing
		// $('#messageField').insertBefore("<div class=\"chat\"></div>");
		$('#nochats').remove();
		$('#chat').append("<div id=\"bubbles\"></div>");
	}

	addTextBubble(username, message)

	$('#message').val('');
}