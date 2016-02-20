<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main UrlGenerator class
 *	
 *	Service provider - Must containt (init) method
 *	and must return its application name
 */
namespace Sophwork\modules\generators\urls;

use Sophwork\app\app\SophworkApp;
use Sophwork\modules\ServiceProviders\ServiceProviderInterface\ServiceProviderInterface;

class UrlGenerator implements ServiceProviderInterface
{
	protected $generatedUrl;
	
	private $routes;

	public function init (SophworkApp $app, Array $parameters = []) 
	{
		$this->routes 		= $app->routes;
		if (!is_null($this->routes))
			$this->routes 	= $this->routes;

		$this->generatedUrl = "";
		return 'UrlGenerator';
	}

	public function generate($route = "/", Array $parameters = [], $rewrited = true) 
	{
		if ($rewrited) {
			foreach ($this->routes as $key => $routes) {
				if (array_key_exists($route, $routes)) {
					if ($routes[$route]['isDynamic']) {
						$this->generatedUrl = $routes[$route]['route'];
						foreach ($parameters as $key => $value) {
							$this->generatedUrl = preg_replace("/{($key)}/", $value, $this->generatedUrl);
						}
						return $this->generatedUrl;
					} else {
						$separator = "?";
						$this->generatedUrl = $routes[$route]['route'];
						foreach ($parameters as $key => $value) {
							$this->generatedUrl .= $separator . $key . "=" . $value;
							$separator = "&";
						}
						return $this->generatedUrl;
					}
				} else {
					$separator = "?";
					$this->generatedUrl = $route;
					foreach ($parameters as $key => $value) {
						$this->generatedUrl .= $separator . $key . "=" . $value;
						$separator = "&";
					}
					return $this->generatedUrl;
				}
			}
		} else {
			$separator = "?";
			$this->generatedUrl = $route;
			foreach ($parameters as $key => $value) {
				$this->generatedUrl .= $separator . $key . "=" . $value;
				$separator = "&";
			}
			return $this->generatedUrl;
		}
	}
}