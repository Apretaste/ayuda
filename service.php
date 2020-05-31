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

	/**
	 * Show the conversation with Support
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _soporte(Request $request, Response &$response)
	{
		// get the list of messages
		$tickets = Database::query("
			SELECT A.*, B.username 
			FROM support_tickets A 
			LEFT JOIN person B
			ON A.from_id = B.id
			WHERE A.from_id = {$request->person->id} 
			ORDER BY A.creation_date DESC");

		// prepare chats for the view
		$chat = [];
		foreach ($tickets as $ticket) {
			$isMe = $ticket->requester != $request->person->email;

			$message = new stdClass();
			$message->class = $isMe ? "me" : "you";
			$message->from = $isMe ? $request->person->username : "apretaste";
			$message->text = $ticket->body;
			$message->date = date_format((new DateTime($ticket->creation_date)), 'd/m/Y h:i a');
			$chat[] = $message;
		}

		$chat = array_reverse($chat);

		// get the support email address
		$supportEmail = Config::pick('general')['support_email'];

		// create data for the view
		$content = [
			'chat' => $chat,
			'username' => $request->person->username,
			'support' => $supportEmail];

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

		// insert the ticket
		Database::query("
			INSERT INTO support_tickets (`from_id`, `subject`, `body`, app_name, app_version, os_version)
			VALUES ({$request->person->id}, 'Ticket from $email', '$body', '$appName', '$appVersion', '$osVersion')");

		// save report
		Database::query('
			INSERT INTO support_reports (inserted, new_count) VALUES (CURRENT_DATE, 1)
			ON DUPLICATE KEY UPDATE new_count=new_count+1');

		// mark challenge as completed
		Challenges::complete("write-to-support", $request->person->id);
	}
}
