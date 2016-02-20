<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main controller class
 */

namespace Sophwork\app\controller;

use Sophwork\core\Sophwork;

class AppController
{
	public $appModel;

	public function __construct($appModel = null) 
	{
		$this->appModel = $appModel;
	}

	public function __get($param) 
	{
		if(isset($this->$param))
			return $this->$param;
		return false;
	}

	public function __set($param, $value) 
	{
		$this->$param = $value;
	}
}