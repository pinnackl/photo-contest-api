<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Main model class
 */

namespace Sophwork\app\model;

use Sophwork\core\Sophwork;

class AppModel
{

    public $config;
	protected $data;
    protected $link;

	public function __construct($config = null) 
	{
		if (is_null($config))
			$this->config 				= Sophwork::getConfig();
		else
			$this->config 				= $config;

        $this->link = $this->connectDatabase();
	}

	public function __get($param) 
	{
		return $this->$param;
	}

	public function __set($param, $value) 
	{
        $this->$param = $value;
	}

	public function connectDatabase()
	{
        if(is_null($this->config) || (!isset($db_host) || !isset($db_name) || !isset($db_login) ||!isset($db_password)))
        	return null;
        extract($this->config);
		try{
			$link = new \PDO('mysql:host='.$db_host.';dbname='.$db_name,$db_login,$db_password,
			array(
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			));
		} catch(Exception $e) {
			die("Erreur : ".$e->getMessage());
		}
		return $link;
	}
}