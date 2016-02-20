<?php

namespace Sophwork\modules\handlers\requests;

use Sophwork\core\Sophwork;

class Requests
{
	protected $requestMethod;
	protected $url;
	protected $server;
	protected $inputs;

	public function __construct()
	{
		$this->requestMethod 	= $_SERVER['REQUEST_METHOD'];
		$this->uri 				= $_SERVER['REQUEST_URI'];
		$this->server 			= $_SERVER;
		$this->inputs			= $_GET + $_POST;

		unset($_GET); unset($_POST); unset($_SERVER);
	}

	public function __get ($param) 
	{
		return $this->$param;
	}

	public function getHeader($url) 
	{
		return get_headers($url);
	}

	public function get($parameter) 
	{
		if (isset($this->inputs[$parameter]))
			return $this->inputs[$parameter];
		return false;
	}
}