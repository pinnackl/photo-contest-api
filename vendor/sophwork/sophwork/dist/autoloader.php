<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Namespace autoloader
 */

/**
 * DEPRECATED
 */
// function __autoload($c)
// {
// // Autoloader
// 	$c = preg_replace("(\\\\)", DIRECTORY_SEPARATOR, $c);
// 
// 	try{
// 		if(file_exists(dirname(__FILE__) . "/.." . __NAMESPACE__ . "/". $c . ".php"))
// 			require_once dirname(__FILE__) . "/.." . __NAMESPACE__ . "/". $c . ".php";
// 		else
// 			throw new Exception('<b>' . $c . '</b> not found');
// 	}
// 	catch(Exception $e) {
// 		die("Autoload fatal error : ".$e->getMessage());
// 	} 
// }

class Autoloader
{
	public $sources;

	public function __construct()
	{
		$this->sources = __DIR__ . '/../src/';
		spl_autoload_register([$this, 'autoload']);
		spl_autoload_register([$this, 'thirdPartyAutoload']);
	}

	/**
	 * Sophwork system autoloader
	 * @param  Object $className
	 * @return node
	 */
	public function autoload($className)
	{
	    $className = ltrim($className, '\\');
	    $fileName  = '';
	    $namespace = '';
	    if ($lastNsPos = strrpos($className, '\\')) {
	        $namespace = substr($className, 0, $lastNsPos);
	        $className = substr($className, $lastNsPos + 1);
	        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, dirname(__FILE__). DIRECTORY_SEPARATOR . $namespace) . DIRECTORY_SEPARATOR;
	    }
	    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	    if(file_exists($fileName))
    		require $fileName;    		
	}

	/**
	 * Third party autoloader
	 * @param  Object $className
	 * @return none
	 */
	public function thirdPartyAutoload($className)
	{
	    $className = ltrim($className, '\\');
	    $fileName  = '';
	    $namespace = '';
	    if ($lastNsPos = strrpos($className, '\\')) {
	        $namespace = substr($className, 0, $lastNsPos);
	        $className = substr($className, $lastNsPos + 1);
	        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $this->sources . DIRECTORY_SEPARATOR . $namespace) . DIRECTORY_SEPARATOR;
	    }
	    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	    if(file_exists($fileName))
    		require $fileName;
	}
}

$autoloader = new Autoloader;