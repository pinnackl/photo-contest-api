<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main route middleware class
 */

namespace Sophwork\modules\handlers\dispatchers;

use Sophwork\core\Sophwork;
use Sophwork\app\app\SophworkApp;

class RouteMiddleware
{
	protected $currentRoute;
	protected $appDispatcher;
	protected $before;
	protected $after;

	public function __construct($appDispatcher, $route)
	{
		$this->appDispatcher 	= $appDispatcher;
		$this->currentRoute 	= $route;
	}

	public function setMiddlewares($hook, $callable)
	{
		$this->appDispatcher->setMiddlewares($hook, $this->currentRoute, $callable);
	}

	public function before($callable = null)
	{
		if (is_callable($callable)) {
			$this->before = $callable;
		}
		$this->setMiddlewares('before', $this->before);
		return $this;
	}

	public function after($callable = null)
	{
		if (is_callable($callable)) {
			$this->after = $callable;
		}
		$this->setMiddlewares('after', $this->after);
		return $this;
	}
}