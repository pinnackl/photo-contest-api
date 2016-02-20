<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main dispatcher class
 */

namespace Sophwork\modules\handlers\dispatchers;

use Sophwork\core\Sophwork;
use Sophwork\app\app\SophworkApp;

class AppDispatcher
{
	protected $requests;
	protected $middlewares;

	public function __construct(SophworkApp $app)
	{
		$this->app 				= $app;
		$this->middlewares		= ['before' => null, 'after' =>  null];
	}

	public function setMiddlewares($hook, $route, $callable)
	{
		$this->middlewares[$hook][$route] = $callable;
		return $this;
	}

	public function matche($requests) 
	{
		$this->requests = $requests;
		if(!($this->requests->requestMethod))
			return null;

		if (isset($this->app->routes[$this->requests->requestMethod])){
			foreach ($this->app->routes[$this->requests->requestMethod] as $key => $value) {
				$controllersAndArgs = $this->dispatch($value['route'], $value['toController']);
				if (isset($controllersAndArgs['controller']) && is_callable($controllersAndArgs['controller'])){
					$controllers = preg_split("/::/", $controllersAndArgs['controller']);
					$controler = new $controllers[0];
					if (!is_null($controllersAndArgs['before'])) {
						$beforeMiddleware = call_user_func_array($controllersAndArgs['before'], $controllersAndArgs['args']);
						if (is_object($beforeMiddleware) && get_class($beforeMiddleware) === "Sophwork\\modules\\handlers\\responses\\Responses") {
							return $beforeMiddleware;
						}
					}
					$response = call_user_func_array([$controler, $controllers[1]], $controllersAndArgs['args']);
					if (!is_null($controllersAndArgs['after'])) {
						$controllersAndArgs['args']['response'] = $response;
						$afterMiddleware = call_user_func_array($controllersAndArgs['after'], [
								$controllersAndArgs['args']['app'],
								$controllersAndArgs['args']['response'],
								$controllersAndArgs['args']['requests'],
							]);
						if (isset($afterMiddleware)) {
							$response = $afterMiddleware;
						}
					}
					return $response;
				} else if (isset($controllersAndArgs['controllerClosure']) && is_callable($controllersAndArgs['controllerClosure'])){
					if (!is_null($controllersAndArgs['before'])) {
						$beforeMiddleware = call_user_func_array($controllersAndArgs['before'], $controllersAndArgs['args']);
						if (is_object($beforeMiddleware) && get_class($beforeMiddleware) === "Sophwork\\modules\\handlers\\responses\\Responses") {
							return $beforeMiddleware;
						}
					}
					$response = call_user_func_array($controllersAndArgs['controllerClosure'], $controllersAndArgs['args']);
					if (!is_null($controllersAndArgs['after'])) {
						$controllersAndArgs['args']['response'] = $response;
						$afterMiddleware = call_user_func_array($controllersAndArgs['after'], [
								$controllersAndArgs['args']['app'],
								$controllersAndArgs['args']['response'],
								$controllersAndArgs['args']['requests'],
							]);
						if (isset($afterMiddleware)) {
							$response = $afterMiddleware;
						}
					}
					return $response;
				}
			}
			http_response_code(404);
			throw new \Exception("<h3>Error ! No route found  for : </h3>\"<b>" . $this->resolve() . "</b>\"");
		} else {
			http_response_code(500);
			throw new \Exception("<h3>Fatal error !</h3>\"<b>No routes declared for this application !</b>\"");
		}
	}

	/**
	 * Dispatch the route to the right controller
	 * @param  String $routes       String to match in URI
	 * @param  String $toController Controller to use when mattch
	 * @return String/Object        Class controller to use when match case
	 */
	protected function dispatch ($routes, $toController) 
	{
		/**
		 * $routes - Routes from the list of declared routes
		 * $route  - Actual route from the URI
		 */
		$route = $this->resolve();

		preg_match_all("/{([^{}?&]+)}/", $routes, $matches);

		// Non dynamic route
		if (empty($matches[0])) {
			if (is_callable($toController)){
				if ($route === $routes) {
					$middlewareRoute 	= $route;

					$before = null;
					if (isset($this->middlewares['before'][$middlewareRoute]))
						$before = $this->middlewares['before'][$middlewareRoute];

					$after = null;
					if (isset($this->middlewares['after'][$middlewareRoute]))
						$after = $this->middlewares['after'][$middlewareRoute];

					return [
						'controllerClosure' => $toController,
						'args' => ['app' => $this->app, 'requests' => $this->requests],
						'before' => $before,
						'after' => $after,
					];
				} else {
					return null;
				}
			} else if (is_array($toController)) {
				if ($route === $routes) {
					$middlewareRoute 	= $route;
					$controller 		= array_keys($toController);
					$action 			= array_values($toController);

					$before = null;
					if (isset($this->middlewares['before'][$middlewareRoute]))
						$before = $this->middlewares['before'][$middlewareRoute];

					$after = null;
					if (isset($this->middlewares['after'][$middlewareRoute]))
						$after = $this->middlewares['after'][$middlewareRoute];

					return [
						'controller' => sprintf("%s::%s", $controller[0],$action[0]),
						'args' => ['app' => $this->app, 'requests' => $this->requests],
						'before' => $before,
						'after' => $after,
					];
				} else {
					return null;
				}
			}

		} 
		// Dynamic route
		else {
			$middlewareRoute 	= $routes;
			$routes 			= str_replace("/", "\/", $routes);
			$routes 			= preg_replace("/{([^{}]+)}/", "([^\/]+)", $routes);

			if (is_callable($toController)){
				if (preg_match_all("#$routes$#", $route, $matchRoute)) {
					array_shift($matchRoute);

					$args = ['app' => $this->app, 'requests' => $this->requests];
					foreach ($matchRoute as $key => $value) {
						$args[] = $value[0];
					}

					$before = null;
					if (isset($this->middlewares['before'][$middlewareRoute]))
						$before = $this->middlewares['before'][$middlewareRoute];

					$after = null;
					if (isset($this->middlewares['after'][$middlewareRoute]))
						$after = $this->middlewares['after'][$middlewareRoute];

					return [
						'controllerClosure' => $toController,
						'args' => $args,
						'before' => $before,
						'after' => $after,
					];
				} else {
					return null;
				}
			} else if (is_array($toController)) {
				if (preg_match_all("#^$routes$#", $route, $matchRoute)) {
					array_shift($matchRoute);

					$controller = array_keys($toController);
					$action 	= array_values($toController);

					$args = ['app' => $this->app, 'requests' => $this->requests];
					foreach ($matchRoute as $key => $value) {
						$args[] = $value[0];
					}

					$before = null;
					if (isset($this->middlewares['before'][$middlewareRoute]))
						$before = $this->middlewares['before'][$middlewareRoute];

					$after = null;
					if (isset($this->middlewares['after'][$middlewareRoute]))
						$after = $this->middlewares['after'][$middlewareRoute];

					return [
						'controller' => sprintf("%s::%s", $controller[0],$action[0]),
						'args' => $args,
						'before' => $before,
						'after' => $after,
					];
				} else {
					return null;
				}
			}
		}
	}

	protected function resolve () 
	{
		$baseUri = isset($this->app->config['baseUri']) ? $this->app->config['baseUri'] : "";

		preg_match("#".$baseUri."([^?&]*)#", $this->requests->uri, $matches);
		return isset($matches[1])? $matches[1] : false;
	}
}