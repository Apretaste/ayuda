<?php

use Framework\Config;
use Framework\Database;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;

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
		$id = $request->input->data->id;

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
		$id = $request->input->data->id;
		$vote = $request->input->data->vote;

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
		$tickets = Database::query("
			SELECT 
				id, title, status, inserted,
				(SELECT COUNT(id) FROM support_chats WHERE ticket_id = support_tickets.id) AS comments
			FROM support_tickets
			WHERE person_id = {$request->person->id}
			ORDER BY inserted DESC");

		// get the support email address
		$supportEmail = Config::pick('general')['support_email'];

		// create data for the view
		$content = [
			'tickets' => $tickets,
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
		// get params
		$message = Database::escape($request->input->data->message, 500);
		$appName = $request->input->appName ?? '';
		$appVersion = $request->input->appVersion ?? '';
		$osVersion = $request->input->osVersion ?? '';

		// insert the ticket
		Database::query("
			INSERT INTO support_tickets (person_id, title, app_name, app_version, os_version)
			VALUES ({$request->person->id}, '$message', '$appName', '$appVersion', '$osVersion')");

		// save report
		Database::query('
			INSERT INTO support_reports (inserted, new_count) VALUES (CURRENT_DATE, 1)
			ON DUPLICATE KEY UPDATE new_count=new_count+1');

		// mark challenge as completed
		Challenges::complete("write-to-support", $request->person->id);

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
		$ticket = Database::queryFirst("
			SELECT 
				A.id, A.title, A.status, A.inserted,
				B.username, B.gender, B.avatar, B.avatarColor
			FROM support_tickets A 
			JOIN person B ON A.person_id = B.id
			WHERE A.id = $id");

		// get the chats
		$chats = Database::query("
			SELECT 
				A.message, A.inserted, B.username, B.gender, B.avatar, B.avatarColor,
				IF(B.id = {$request->person->id}, 'right', 'left') AS position
			FROM support_chats A
			JOIN person B ON A.person_id = B.id
			WHERE A.ticket_id = $id
			ORDER BY A.inserted ASC
			LIMIT 50");

		// get the support email address
		$supportEmail = Config::pick('general')['support_email'];

		// create data for the view
		$content = [
			'ticket' => $ticket,
			'chats' => $chats,
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
		$id = $request->input->data->id;
		$message = Database::escape($request->input->data->message, 200);

		// do not save empty chats
		if(empty($id) || empty($message)) {
			return false;
		}

		// insert the chat
		Database::query("
			INSERT INTO support_chats (ticket_id, person_id, message) 
			VALUES ($id, {$request->person->id}, '$message')");
	}
}
