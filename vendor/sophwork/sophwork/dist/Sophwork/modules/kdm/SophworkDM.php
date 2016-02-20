<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Sophpkwork module : ORM Data mapper
 *	Data mapper class
 */

namespace Sophwork\modules\kdm;

use Sophwork\modules\kdm\SophworkDMEntities;

class SophworkDM{
	public $link;
	public $config;

	public function __construct($config)
	{
		$this->setConfig($config);
		$this->link = $this->dbConnect();
	}

	public function __get($param)
	{
		return $this->$param;
	}

	public function __set($param, $value)
	{
		$this->$param = $value;
	}

	public function setConfig($config)
	{
		$this->config = $config;
	}
	
	public function getConfig()
	{
		return $this->config;
	}

	public function dbConnect()
	{
		if(sizeof($this->config) >= 4){
			extract($this->config);
			try{
				$link = new \PDO('mysql:host='.$db_host.';dbname='.$db_name,$db_login, $db_password,
				array(
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
					)
				);
			}
			catch(Exeption $e){
				die('ERREUR :'.$e->getMessage);
			}
			return $link;
		}
		return null;
	}

	public function create($entityName)
	{
		$entity = new SophworkDMEntities();
			$entity->setTable($entityName);
		$req1 = $this->link->query("show tables where Tables_in_".$this->config['db_name']." = '".$entityName."'");
		while($table = $req1->fetch()){
			$req2 = $this->link->query("SHOW COLUMNS FROM ".$table['Tables_in_'.$this->config['db_name']]);
			while($columns = $req2->fetch()){
				$entity->__setData($columns['Field'] , null);
			}
		}
		$req3 = $this->link->query("SHOW KEYS FROM ".$entityName." WHERE Key_name = 'PRIMARY'");
			$primaryKey = $req3->fetch();
				$entity->setPk($primaryKey['Column_name']);
		$req4 = $this->link->query("SHOW KEYS FROM ".$entityName);
			while($indexes = $req4->fetch()){$entity->indexes[] = $indexes['Column_name'];}
		$entity->setLink($this->link);
		
		foreach ($entity->data as $key => $value) {
			$entity->setSetterMethod($key);
			$entity->setGetterMethod($key);
		}
		
		foreach ($entity->indexes as $key => $value) {
			$entity->setKeyMethod($value);
			$entity->setFilterMethod($value);
			$entity->setOrderMethod($value);
		}
		return $entity;
	}

    public function select($table, $where = '', $fields = '*', $order = '', $limit = null, $offset = null)
    {
        $query = 'SELECT ' . $fields . ' FROM ' . $table
               . (($where) ? ' WHERE ' . $where : '')
               . (($limit) ? ' LIMIT ' . $limit : '')
               . (($offset && $limit) ? ' OFFSET ' . $offset : '')
               . (($order) ? ' ORDER BY ' . $order : '');
        return $req = $this->link->query($query);
    }

    public function insert($table, array $data)
    {
        $fields = implode(',', array_keys($data));
        $prepare = ':' . implode(', :', array_keys($data));
        $query = 'INSERT INTO ' . $table . ' (' . $fields . ') ' . ' VALUES (' . $prepare . ')';
		$req = $this->link->prepare($query);
        foreach ($data as $key => $value) {
        	if(getType($value) == 'array')
        		$data[$key] = $value[0];
        }
		$req->execute($data);
        $this->data[$this->getPk()] = $this->link->lastInsertId();
    }

    public function update($table, array $data, $where = '')
    {
        $set = array();
        foreach ($data as $field => $value) {
            $set[] = $field . '= :' . $field;
        }
        $set = implode(',', $set);
        $query = 'UPDATE ' . $table . ' SET ' . $set
               . (($where) ? ' WHERE ' . $where : '');
        $req = $this->link->prepare($query);
        foreach ($data as $key => $value) {
        	if(getType($value) == 'array')
        		$data[$key] = $value[0];
        }
        $req->execute($data);

        return $req->rowCount();
    }

    public function delete($table, $where = '')
    {	// FIXME : To test
        $query = 'DELETE FROM ' . $table
               . (($where) ? ' WHERE ' . $where : '');
        $req = $this->link->query($query);
        return $req->rowCount();
    }
}