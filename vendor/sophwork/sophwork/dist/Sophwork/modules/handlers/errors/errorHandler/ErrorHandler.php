<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main ErrorHandler class
 *	
 *	Service provider - Must containt (init) method
 *	and must return its application name
 */
namespace Sophwork\modules\handlers\errors\errorHandler;

use Sophwork\app\app\SophworkApp;
use Sophwork\modules\handlers\errors\exception\SophworkErrorException;
use Sophwork\modules\ServiceProviders\ServiceProviderInterface\ServiceProviderInterface;

class ErrorHandler implements ServiceProviderInterface
{

	public function init (SophworkApp $app, Array $parameters = [])
	{
		if ($app->debug) {
			error_reporting(E_ALL|E_STRICT);
			ini_set('display_errors', $app->debug?1:0);

			set_error_handler([$this, "errorHandler"]);
			set_exception_handler([$this, "exceptionHandler"]);
		}

		return 'ErrorHandler';
	}

	public function errorHandler($severity, $message, $file, $line) 
	{
		//FIXME : chech all case
	    switch ($severity) {
	        case E_NOTICE:
	        case E_USER_NOTICE:
	        case E_DEPRECATED:
	        case E_USER_DEPRECATED:
	        case E_STRICT:
	            try {
	            	throw new SophworkErrorException($message, 0, $severity, $file, $line);	            	
	            } catch (SophworkErrorException $e) {
	            	$this->exceptionHandler($e, 'NOTICE');
	            }
	            break;

	        case E_WARNING:
	        case E_USER_WARNING:
	            try {
	            	throw new SophworkErrorException($message, 0, $severity, $file, $line);	            	
	            } catch (SophworkErrorException $e) {
	            	$this->exceptionHandler($e, 'WARNING');
	            }
	            break;

	        case E_ERROR:
	        case E_USER_ERROR:
	            try {
	            	throw new SophworkErrorException($message, 0, $severity, $file, $line);	            	
	            } catch (SophworkErrorException $e) {
	            	$this->exceptionHandler($e, 'FATAL');
	            }
	            exit("FATAL error $message at $file:$line");

	        default:
	            exit("Unknown error at $file:$line");
	    }
	}

	public function exceptionHandler ($exception, $type = null) 
	{
		echo'<pre style="word-wrap: break-word;font-size:1.1em;">';
		echo "<b><u>Sophwork exception</u> : $type</b><br>";
		echo 'With message : <b>',$exception->getMessage(),'</b><br>';
		echo 'In file : <b>',$exception->getFile(),'(',$exception->getLine(),')</b><br>';
		echo 'Stack trace:<br>',$exception->getTraceAsString();
		echo'</pre>';
	}
}