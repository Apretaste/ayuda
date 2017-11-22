<?php

class Ayuda extends Service
{
	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// create response
		$response = new Response();
		$response->subject = "Ayuda de Apretaste";
		$response->createFromTemplate("basic.tpl", ["userEmail" => $request->email]);
		return $response;
	}
}
