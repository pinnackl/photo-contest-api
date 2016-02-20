<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Simple Object PHP Framework - Sophwork
 */

namespace Sophwork\core;

class Sophwork
{
	const NAME 			= "Sophwork";
	const DESCRIPTION 	= "Simple Object PHP Framework - Sophwork";
	const VERSION 		= "0.3.0";

	public function __construct(){

	}

	public static function prevar($value)
	{
		echo '<pre>';
		var_dump($value);
		echo '</pre>';
	}

	public static function predie($value)
	{
		echo '<pre>';
		var_dump($value);
		echo '</pre>';
		die;
	}

	public static function getParam($param_name, $init_value) 
	{
		$param_value = $init_value;
		if (isset($_GET[$param_name])) {
			$param_value = htmlspecialchars($_GET[$param_name]);
		}    
		return $param_value;
	}

	public static function getConfig()
	{
		$config = null;
		if ( !file_exists(dirname(dirname(__FILE__)) . '/../config.local.php') ) {
			return false;
		}
		require(dirname(dirname(__FILE__)) . '/../config.local.php');
		return $config;
	}

	/**
	 * Create the config file containing database credential
	 * Need to lowercase database name
	 * @param $POST
	 */
	public static function setConfig($POST)
	{
		$handle = fopen(dirname(dirname(__FILE__)) . '/../config.local.php', "w+");
		$text = "<?php\n\$config = array(\n'db_host' => '".$POST['db_host']."',\n'db_name' => '".strtolower($POST['db_name'])."',\n'db_login' => '".$POST['db_login']."',\n'db_password' => '".$POST['db_password']."',\n);
		 ";
		fwrite($handle, $text);
		fclose($handle);
		require_once(dirname(dirname(__FILE__)) . '/../config.local.php');
	}
	
	public static function redirect($parameters = null)
	{
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		
		$cnf = @file_get_contents( __DIR__ . '/../../Sophwork.json');
		
		if ($cnf !== false) {
			$cnf = json_decode($cnf);
			$localUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . "/" . $cnf->root . $parameters;
			header("Location: " . $localUrl);
			exit;
		}
		
		// correspond to this specific case
		$URI = preg_split("/\//",$_SERVER['REQUEST_URI']);
		$c = count($URI);
		if ($c < 3)
			$localUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . "/" . $parameters;
		else
			$localUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . "/" . $URI[1] . "/" . $parameters;
		header("Location: " . $localUrl);
		exit;
	}

	public static function redirectTo($referer)
	{
		header('Location: '.$referer);
		exit;
	}

	public static function getUrl($parameters = null)
	{
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		
		$cnf = @file_get_contents( __DIR__ . '/../../Sophwork.json');
		
		if ($cnf !== false) {
			$cnf = json_decode($cnf);
			$localUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . "/" . $cnf->root . $parameters;
			return $localUrl;
		}
		
		// correspond to this specific case
		$URI = preg_split("/\//",$_SERVER['REQUEST_URI']);
		$c = count($URI);
		if ($c < 3)
			$localUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . "/" . $parameters;
		else
			$localUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . "/" . $URI[1] . "/" . $parameters;
		return $localUrl;
	}

	public static function camelCase($str, array $noStrip = [])
	{
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);
 
        return $str;
	}

	public static function slug($string)
	{
		$char = array(
			'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', '@' => 'a',
			'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e',
			'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
			'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u',
			'Œ' => 'oe', 'œ' => 'oe',
			'$' => 's');
	 
		$string = strtr($string, $char);
		$string = preg_replace('#[^A-Za-z0-9]+#', '-', $string);
		$string = trim($string, '-');
		$string = strtolower($string);
	 
		return $string;
	}

}

