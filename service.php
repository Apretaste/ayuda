<?php

use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;
use Framework\Config;
use Framework\Zendesk;
use Framework\Database;
use Framework\GoogleAnalytics;

class Service
{
	/**
	 * Service starting point
	 *
	 * @param Request
	 * @param Response
	 */
	public function _main(Request $request, Response $response)
	{
		return $this->_faq($request, $response);
	}

	/**
	 * Show the FAQ list
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _faq(Request $request, Response $response)
	{
		// get the faq
		$faq = Database::query('
			SELECT id, title, views 
			FROM support_faq 
			ORDER BY (upvote - downvote) DESC');

		// send data to the view
		$response->setCache();
		$response->setTemplate('faq.ejs', ['faq' => $faq]);
	}

	/**
	 * Display an FAQ entry
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _respuesta(Request $request, Response $response)
	{
		// get the ID
		$id = $request->input->data->id ?? '';

		// display an error message
		if(empty($id)) {
			return $response->setTemplate('message.ejs', [
				'header' => 'Algo raro pasó',
				'icon' => 'sentiment_dissatisfied',
				'text' => 'Hemos encontrado un problema temporal abriendo el servicio. Es posible que se arregle si reintenta desde el inicio.',
				'btnLink' => 'AYUDA', 
				'btnCaption' => 'Reintentar'
			]);
		}

		// increase the views
		Database::query("UPDATE support_faq SET views=views+1 WHERE id=$id");

		// get the faq
		$answer = Database::queryFirst("SELECT id, title, body, views FROM support_faq WHERE id = $id");
		$answer->body = nl2br($answer->body);

		// send data to the view
		$response->setCache();
		$response->setTemplate('answer.ejs', ['answer' => $answer]);
	}

	/**
	 * Vote on an FAQ entry
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _votar(Request $request, Response $response)
	{
		// get the ID
		$id = $request->input->data->id ?? '';
		$vote = $request->input->data->vote ?? '';

		// do not continue with blank data
		if(empty($id) || empty($vote)) {
			return false;
		}

		// upvote or downvote
		Database::query("UPDATE support_faq SET {$vote}vote={$vote}vote+1 WHERE id=$id");
	}

	/**
	 * List your requests to support
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _soporte(Request $request, Response $response)
	{
		// get the list of messages
		$tickets = Zendesk::getTickets($request->person);

		// get the support email address
		$supportEmail = Config::pick('general')['support_email'];

		// create data for the view
		$content = [
			'tickets' => array_reverse($tickets),
			'support' => $supportEmail];

		// send data to the view
		$response->setTemplate('tickets.ejs', $content);
	}

	/**
	 * Create a new ticket
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _ticket(Request $request, Response $response)
	{
		// escape message text
		$message = $request->input->data->message ?? '';
		$title = trim(Database::escape($message));

		// display an error message
		if(empty($title)) {
			return $response->setTemplate('message.ejs', [
				'header' => 'Algo raro pasó',
				'icon' => 'sentiment_dissatisfied',
				'text' => 'Hemos encontrado un problema agregando su ticket. Es posible que se arregle si reintenta nuevamente.',
				'btnLink' => 'AYUDA SOPORTE',
				'btnCaption' => 'Reintentar'
			]);
		}

		// create the ticket in Zendesk
		Zendesk::createTicket($request->person, $request->input, $title);

		// mark challenge as completed
		Challenges::complete("write-to-support", $request->person->id);

		// submit to Google Analytics 
		GoogleAnalytics::event('support_ticket', $request->person->id);

		// display the OK message
		$response->setTemplate('message.ejs', [
			'header' => 'Ticket creado',
			'icon' => 'thumb_up',
			'text' => 'Su ticket ha sido creado satisfactoriamente y le daremos respuesta en menos de 72 horas. Si desea detallar mejor el tema, escriba sus comentarios dentro del ticket.',
			'btnLink' => 'AYUDA SOPORTE',
			'btnCaption' => 'Ver tickets']);
	}

	/**
	 * Show the conversation with support
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _chat(Request $request, Response $response)
	{
		// get params
		$id = $response->input->data->id ?? '';

		// get the ticket
		$ticket = Zendesk::getTicketDetails($id);

		// display an error message
		if(empty($ticket)) {
			return $response->setTemplate('message.ejs', [
				'header' => 'Algo raro pasó',
				'icon' => 'sentiment_dissatisfied',
				'text' => 'Hemos encontrado un problema abriendo este ticket. Intente nuevamente; si no se arregla, por favor cree un nuevo ticket.',
				'btnLink' => 'AYUDA SOPORTE', 
				'btnCaption' => 'Reintentar'
			]);
		}

		// add ticket person values
		$owner = (Object) [
			'username' => $request->person->username,
			'gender' => $request->person->gender,
			'avatar' => $request->person->avatar,
			'avatarColor' => $request->person->avatarColor
		];

		// add user data to the ticket
		foreach ($ticket->comments as $item) {
			$item->position = ($ticket->creator == $item->creator) ? 'right' : 'left';
			$item->username = ($ticket->creator == $item->creator) ? $request->person->username : 'apretin';
			$item->avatar = ($ticket->creator == $item->creator) ? $request->person->avatar : 'apretin';
			$item->avatarColor = ($ticket->creator == $item->creator) ? $request->person->avatarColor : 'verde';
		}

		// get the support email address
		$supportEmail = Config::pick('general')['support_email'];

		// create data for the view
		$content = [
			'owner' => $owner,
			'ticket' => $ticket,
			'support' => $supportEmail
		];

		// send data to the view
		$response->setTemplate('chats.ejs', $content);
	}

	/**
	 * Send a new chat to support
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _escribir(Request $request, Response $response)
	{
		// get params to save
		$id = $request->input->data->id ?? '';
		$message = $request->input->data->message ?? '';

		// escape the message
		$message = trim(Database::escape($message, 200));

		// do not save empty chats
		if(empty($id) || empty($message)) {
			return false;
		}

		// submit to Google Analytics 
		GoogleAnalytics::event('support_comment', $request->person->id);

		// comment the ticket
		Zendesk::createTicketComment($id, $request->person, $message);
	}
}
