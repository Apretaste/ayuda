// create array of the statuses
var statusCode = {new:'Nuevo', open:'Abierto', pending:'Pendiente', solved:'Cerrado', closed:'Cerrado'};

// start the components
//
$(document).ready(function () {
	$('.modal').modal();
	$('.tabs').tabs();
});

// start a new ticket
//
function createTicket() {
	// get the message
	var message = $('#message').val().trim();

	// do not allow short messages
	if (message.length < 30) {
		M.toast({html: 'Describa mejor su caso'});
		return false;
	}

	// create the ticket
	apretaste.send({
		command: 'AYUDA TICKET',
		data: {message: message},
		redirect: true
	});
}

// send a new chat
//
function chat() {
	// get message and user features
	var message = $('#message').val().trim();
	var ticketId = $('#message').attr('ticket');
	var username = $('#message').attr('username');
	var gender = $('#message').attr('gender');
	var avatar = $('#message').attr('avatar');
	var avatarColor = $('#message').attr('avatarColor');

	// do not allow short messages
	if (message.length < 2) {
		M.toast({html: 'Debe escribir un texto'});
		return false;
	}

	// send the chat
	apretaste.send({
		command: 'AYUDA ESCRIBIR',
		data: {message:message, id:ticketId},
		redirect: false
	});

	// if is the first chat
	$('#chat').show();
	$('#msg').hide();

	// append the bubble to teh screen
	$('#chat').append(
		'<li id="last" class="right">' +
		'	<div class="person-avatar circle" face="'+ avatar +'" color="'+ avatarColor +'" size="30"></div>' +
		'	<div class="head">' +
		'		<a href="#!" class="' + gender + '">@' + username + '</a>' +
		'		<span class="date">' +  moment().format('MMM D, YYYY h:mm A') + '</span>' +
		'	</div>' +
		'	<span class="text">' + message + '</span>' +
		'</li>');

	// re-create avatar
	setElementAsAvatar($('#last .person-avatar').get());

	// clean the chat field
	$('#message').val('');

	// scroll to the lastest chat
	$('html, body').animate({
		scrollTop: $("#last").offset().top
	}, 1000);
}

// calculate remaining characteres
//
function checkLength(size=200) {
	var message = $('#message').val().trim();
	if (message.length <= size) $('.helper-text').html('Restante: ' + (size - message.length));
	else {
		message = message.substring(0, size);
		$('#message').val(message);
		$('.helper-text').html('Límite excedido');
	}
}

// vote for an FAQ entry
//
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
