<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main view class
 */

namespace Sophwork\app\view;

use Sophwork\core\Sophwork;

class AppView
{
	public $templateSrc;
	public $modifiers = [
		'S' => 'htmlspecialchars',
		'U' => 'mb_strtoupper',
		'L' => 'mb_strtolower',
		'FU' => 'ucfirst',
		'FL' => 'lcfirst',
	];
	public $viewData;

	public function __construct($config) 
	{
		$this->viewData 	= [];
		$this->templateSrc 	= isset($config['template']) ? $config['template'] : null;
	}

	public function __set($param, $value) 
	{
		$this->$param = $value;
	}
	
	public function __get($param) 
	{
		return $this->$param;
	}

	public function e($value, $modifier = 'S') 
	{
		return $this->modifiers[$modifier]($value);
	}

	public function renderView($template, Array $data = []) 
	{
		extract($data);
		$filename = $this->templateSrc . '/' .$template.'.tpl';
		ob_start();
		if(file_exists($filename))
			include_once($this->templateSrc . '/' .$template.'.tpl');
		else
			throw new \Exception("<h1>Error template file not found !</h1>");
		$output = ob_get_contents();
		ob_clean();
		return $output;
	}

	public function getLayout($template, Array $data = []) 
	{
		extract($data);
		$filename = $this->templateSrc . '/' .$template.'.tpl';
		ob_start();
		if(file_exists($filename))
			include_once($this->templateSrc . '/' .$template.'.tpl');
		else
			throw new \Exception("<h1>Error template layout file not found !</h1>");
		ob_flush();
		ob_clean();
	}
}