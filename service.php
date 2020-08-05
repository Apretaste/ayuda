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
	public function _main(Request $request, Response &$response)
	{
		return $this->_faq($request, $response);
	}

	/**
	 * Show the FAQ
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _faq(Request $request, Response &$response)
	{
		// get the faq
		$faq = Database::query('SELECT id, title, views FROM support_faq ORDER BY (upvote - downvote) DESC');

		// send data to the view
		$response->setCache();
		$response->setTemplate('faq.ejs', ['faq' => $faq]);
	}

	/**
	 * Show the answer for an FAQ entry
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _respuesta(Request $request, Response &$response)
	{
		// get the ID
		$id = $request->input->data->id;

		// increase the views
		Database::query("UPDATE support_faq SET views=views+1 WHERE id=$id");

		// get the faq
		$answer = Database::query("SELECT id, title, body, views FROM support_faq WHERE id=$id")[0];
		$answer->body = nl2br($answer->body);

		// send data to the view
		$response->setCache();
		$response->setTemplate('answer.ejs', ['answer' => $answer]);
	}

	/**
	 * Vote on the answer
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _votar(Request $request, Response &$response)
	{
		// get the ID
		$id = $request->input->data->id;
		$vote = $request->input->data->vote;

		// upvote or downvote
		Database::query("UPDATE support_faq SET {$vote}vote={$vote}vote+1 WHERE id=$id");
	}

	public function _soporte(Request $request, Response &$response)
	{
		// get the list of messages
		$tickets = Database::query("
			SELECT A.*, (select count(id) from support_tickets where parent = A.id) as comments
			FROM support_tickets A 
			LEFT JOIN person B ON A.from = B.email OR A.from_id = B.id
			WHERE A.from_id = {$request->person->id} AND A.parent is null and (A.status <> 'ARCHIVED')
			ORDER BY A.creation_date DESC");

		// prepare chats for the view
		$chat = [];
		foreach ($tickets as $ticket) {
			$isMe = $ticket->from == $request->person->email;
			$message = $ticket;
			$message->text = $ticket->body;
			$message->date = $ticket->creation_date;
			$chat[] = $message;
		}

		// get the support email address
		$supportEmail = Config::pick('general')['support_email'];

		// create data for the view
		$content = [
			'tickets' => $chat,
			'support' => $supportEmail];

		// send data to the view
		$response->setTemplate('tickets.ejs', $content);
	}

	/**
	 * Show the conversation with Support
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _chat(Request $request, Response &$response)
	{
		$parent = $response->input->data->parent ?? '';

		if (empty($parent)) {
			$parent = $response->input->data->parentTicket ?? '';
		}

		if (empty($parent)) {
			return $this->_soporte($request, $response);
		}

		$ticketRecord = Database::queryFirst("select * from support_tickets where id = '$parent'");

		// get the list of messages
		$tickets = Database::query("
			SELECT A.*, B.username, B.gender, B.avatar, B.avatarColor as color, 
			       IF(A.from_id = B.id, 'right', 'left') as position 
			FROM support_tickets A 
			LEFT JOIN person B
			ON A.from = B.email OR A.from_id = B.id
			WHERE A.from_id = {$request->person->id} AND A.parent = '$parent'
			ORDER BY A.creation_date DESC");

		// prepare chats for the view
		$chat = [];
		foreach ($tickets as $ticket) {
			$isMe = $ticket->from == $request->person->email;
			$message = $ticket;
			$message->class = $isMe ? "me" : "you";
			$message->from = $ticket->username;
			$message->text = $ticket->body;
			$message->date = $ticket->creation_date;
			$chat[] = $message;
		}

		$chat = array_reverse($chat);

		// get the support email address
		$supportEmail = Config::pick('general')['support_email'];

		// create data for the view
		$content = [
			'chat' => $chat,
			'username' => $request->person->username,
			'support' => $supportEmail,
			'parentTicket' => $parent,
			'ticket' => $ticketRecord
		  ];

		// send data to the view
		$response->setTemplate('soporte.ejs', $content);
	}

	/**
	 * Create a new ticket
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _escribir(Request $request, Response &$response)
	{
		// get params to save
		$email = $request->person->email;
		$appName = $request->input->app ?? '';
		$appVersion = $request->input->appversion ?? '';
		$osVersion = $request->input->osversion ?? '';
		$body = Database::escape($request->input->data->message, 1024);
		$parentTicket = $request->input->data->parentTicket ?? '';

		// insert the ticket
		Database::query("
			INSERT INTO support_tickets (`from_id`, `subject`, `body`, app_name, app_version, os_version, parent)
			VALUES ({$request->person->id}, 'Ticket from $email', '$body', '$appName', '$appVersion', '$osVersion', NULLIF('$parentTicket', ''))");

		// save report
		Database::query('
			INSERT INTO support_reports (inserted, new_count) VALUES (CURRENT_DATE, 1)
			ON DUPLICATE KEY UPDATE new_count=new_count+1');

		// mark challenge as completed
		Challenges::complete("write-to-support", $request->person->id);

		if (empty($parentTicket)) {
			return $this->_soporte($request, $response);
		}

		$request->input->data->parent = $parentTicket;
		return $this->_chat($request, $response);
	}
}
