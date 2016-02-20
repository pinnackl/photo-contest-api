<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main application class
 */

namespace Sophwork\app\app;

use Sophwork\core\Sophwork;
use Sophwork\app\view\AppView;
use Sophwork\app\model\AppModel;
use Sophwork\app\controller\AppController;
use Sophwork\modules\handlers\dispatchers\AppDispatcher;
use Sophwork\modules\handlers\dispatchers\RouteMiddleware;

class SophworkApp extends Sophwork
{
	public $appName;
	public $config;
	public $appView;
	public $appModel;
	public $appController;

	protected $routes;

	protected $debug;

	protected $errors;

	protected $before;
	protected $after;

	private $_factory;
	/**
	 *	@param none
	 *	instanciate all Sophwork classes :
	 *		AppView
	 *		AppController
	 *		AppModel
	 *
	 * 	This class is the base of the sophork architecture.
	 * 	All you need to do is instanciate this class in your index file
	 * 	and then, acccess throught it to all the others class
	 *
	 * 	Beacause all others classes and controllers inherite from this class
	 * 	appController is use as a singleton
	 */
	public function __construct($config = null) 
	{
		parent::__construct();
		if (is_null($config)) {
			if (Sophwork::getConfig())
				$this->config 			= Sophwork::getConfig();
			else {
				$this->config 			= [
					"baseUri"			=> "",
					"template"			=> "",
					];
			}
		}
		else
			$this->config 			= $config;

		$this->appView 			 	= new AppView($this->config);
		$this->appModel 		 	= new AppModel($this->config);

		if(!($this instanceof AppController))
			$this->appController 	= new AppController($this->appModel);
		
		if(!($this instanceof AppDispatcher))
			$this->appDispatcher 	= new AppDispatcher($this);

		$this->routes 				= [];

		$this->debug 				= false;

		$this->_factory 			= [];
	}
	
	public function __set($param, $value) {
		$this->$param = $value;
	}

	public function __get($param) {
		return $this->$param;
	}

	/**
	 * GET route method
	 * @param  [type] $route        [description]
	 * @param  [type] $toController [description]
	 */
	public function get($route, $toController, $alias = null) 
	{
		$route = [
			'route' => $route,
			'toController' => $toController,
			'alias' => $alias,
			'isDynamic' => preg_match("/{([^{}?&]+)}/", $route),
		];
		if (!is_null($alias))
			$this->routes['GET'][$alias] = $route;
		else
			$this->routes['GET'][] = $route;
		return new RouteMiddleware($this->appDispatcher, $route['route']);
	}

	/**
	 * POST route method
	 * @param  [type] $route        [description]
	 * @param  [type] $toController [description]
	 */
	public function post($route, $toController, $alias = null) 
	{
		$route = [
			'route' => $route,
			'toController' => $toController,
			'alias' => $alias,
			'isDynamic' => preg_match("/{([^{}?&]+)}/", $route),
		];
		$this->routes['POST'][] = $route;
	}

	public function request($route, $toController) 
	{

	}

	public function inject($depenency) 
	{
		$depenencyName = $depenency->init($this);
		$this->$depenencyName = $depenency;
	}

	/**
	 * Registers a service provider. (Symfony compatibility)
	 * @param  ServiceProviderInterface $provider A ServiceProviderInterface instance
	 * @param  array                    $values   An array of values that customizes the provider (Not supported yet)
	 * @return static
	 */
	public function register($provider, array $values = array())
	{
	    $provider->register($this);
	    foreach ($values as $key => $value) {
	        $this[$key] = $value;
	    }
	    return $this;
	}

	public function errors($callable = null) 
	{
		$this->errors = $callable;
	}

	public function before($callable = null) 
	{
		if (is_callable($callable))
			$this->before = $callable;
		return $this;
	}

	public function after($callable = null) 
	{
		if (is_callable($callable))
			$this->after = $callable;
		return $this;
	}

	public function abort($errorCode = 500, $message = null) 
	{
		if (class_exists("\Sophwork\\modules\\handlers\\responses\\Responses"))
			return new \Sophwork\modules\handlers\responses\Responses($message, $errorCode);
		else {
			http_response_code($errorCode);		
			return is_null($message) ? '' . $message : $message;
		}
	}

	// FIXME : To refactor
	public function run()
	{
		//	Factory
		$this->_factory['request'] = new \Sophwork\modules\handlers\requests\Requests;

		// custom hook
		$beforeMiddlewareResponse = null;
		if (!is_null($this->before)) {
			$beforeMiddleware = call_user_func_array($this->before, [$this, $this->_factory['request']]);
			if (is_object($beforeMiddleware) && get_class($beforeMiddleware) === "Sophwork\\modules\\handlers\\responses\\Responses") {
				$beforeMiddlewareResponse = $beforeMiddleware;
			}			
		}

		// check if the Sophwork error exception handler is used for this application
		if (isset($this->ErrorHandler)) {
			// check if the custom error messages have been set
			// use the default exception messages
			if(is_null($this->errors)) {
				try {
					// Case if the before middleware return a respose object
					if (!is_null($beforeMiddlewareResponse))
						$matche = $beforeMiddlewareResponse;
					else {
						// matche return the controller response object to set to the user
						// if no match happen the dispatchers send an exception with the appropriate http status code
						$matche = $this->appDispatcher->matche($this->_factory['request']);
					}

					if (!is_object($matche)) {
						if (!is_null($matche)) {
							echo $matche;
						}
						else {
							http_response_code(500);
							throw new \Exception("<h3>Error !</h3>\"<b>Controller must return something !</b>\"");
						}
					} else
						echo $matche->getResponse();
				} catch (\Sophwork\modules\handlers\errors\exception\SophworkErrorException\ErrorHandler $e) {
					echo $e->getMessage(), "<br>";
					if ($this->debug){
						echo '<b>DEBUG MODE </b>: TRUE';
						exit;
					}
				}
			} else {
				// check if custom exception messages have been set into a callable
				if (is_callable($this->errors)) {
					try {
						// Case if the before middleware return a respose object
						if (!is_null($beforeMiddlewareResponse))
							$matche = $beforeMiddlewareResponse;
						else {
							// matche return the controller response object to set to the user
							// if no match happen the dispatchers send an exception with the appropriate http status code
							$matche = $this->appDispatcher->matche($this->_factory['request']);
						}
						if (!is_object($matche)) {
							if (!is_null($matche))
								echo $matche;
							else {
								http_response_code(500);
								throw new \Exception("<h3>Error !</h3>\"<b>Controller must return something !</b>\"");
							}
						} else
							echo $matche->getResponse();
					} catch (\Exception $e) {
						// custom exception messages handling 
						ob_start();
						$response = call_user_func_array($this->errors, [$e, http_response_code()]);
						$directOutput = ob_get_contents();
						ob_clean();

						// check the response of the custom exception messages callable
						if (!is_object($response)) {
							if (!empty($directOutput)) {
								echo $directOutput;
								exit;
							} elseif (!empty($response)) {
								echo $response;
								exit;
							} else {
								http_response_code(500);
								throw new \Exception("<h3>Error !</h3>\"<b>Error handler must return something !</b>\"");
								exit;
							}
						} else {
							echo $response->getResponse();
							exit;
						}
					}
				}
			}
		} else {
			try {
				// Case if the before middleware return a respose object
				if (!is_null($beforeMiddlewareResponse))
					$matche = $beforeMiddlewareResponse;
				else {
					// matche return the controller response object to set to the user
					// if no match happen the dispatchers send an exception with the appropriate http status code
					$matche = $this->appDispatcher->matche($this->_factory['request']);
				}
				if (!is_object($matche)) {
					if (!is_null($matche))
						echo $matche;
					else {
						http_response_code(500);
						throw new \Exception("<h3>Error !</h3>\"<b>Controller must return something !</b>\"");
					}
				} else
					echo $matche->getResponse();
			} catch (\Exception $e) {
				http_response_code(500);
				echo $e->getMessage(), "<br>";
				if ($this->debug){
					echo '<b>DEBUG MODE </b>: TRUE';
					exit;
				}
			}
		}

		// custom hook
		if (!is_null($this->after)) {
			return call_user_func_array($this->after, [$this, new \Sophwork\modules\handlers\responses\Responses($matche)]);
		}
	}
}
