<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Sophpkwork module : ORM Data mapper
 *	Data mapper entities class
 */

namespace Sophwork\modules\kdm;

use Sophwork\core\Sophwork;
use Sophwork\modules\kdm\SophworkDM;

class SophworkDMEntities extends SophworkDM{
	public $link;
	protected $table;
	protected $primaryKey;
	protected $indexes;
	protected $data;
	protected $criteria;
	protected $order;



	public function __construct()
	{
		$this->data = [];
		$this->indexes = [];
		$this->criteria = '';
		$this->order = '';
	}

	public function __call($method, $args)
	{
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
	}

	// FIXME Create dynamic getseter
	public function __setData($param, $value) 
	{
		$this->data[$param] = $value;
	}
	
	public function __getData($param) {
		if (array_key_exists($param, $this->data)) 
		{
			return $this->data[$param];
		}

		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __get(): ' . $param .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);
		return null;
	}

	public function __set($param, $value) 
	{
		$this->$param = $value;
	}
	
	public function __get($param) 
	{
		return $this->$param;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getIndexes()
	{
		return $this->indexes;
	}

	public function setLink($link)
	{
		$this->link = $link;
	}

	public function getLink()
	{
		return $this->link;
	}
	
	public function setPk($primaryKey)
	{
		$this->primaryKey = $primaryKey;
	}

	public function getPk()
	{
		return $this->primaryKey;
	}
	
	public function setTable($table)
	{
		$this->table = $table;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function save()
	{
		if($this->__getData($this->primaryKey) === NULL){
			// echo 'Insert';
			$this->insert($this->table, $this->data);
		}else{
			// echo 'Update';
			$pkValue = $this->__getData($this->primaryKey);
			if(is_array($this->__getData($this->primaryKey)))
				$pkValue = $this->__getData($this->primaryKey)[0];
			$this->update($this->table, $this->data, "$this->primaryKey = \"$pkValue\" ");
		}
	}

	public function erase()
	{
		$pkValue = is_array($this->getData()[$this->primaryKey])?
			$this->getData()[$this->primaryKey][0]:
			$this->getData()[$this->primaryKey];
		// echo'<pre style="background:#ffffff">';
		// var_dump($pkValue);
		// echo'</pre>';die;
		$this->delete($this->table, "$this->primaryKey = \"$pkValue\" ");
	}

	public function findOne($value)
	{
		$criteria = '';
		for($i=0;$i<sizeof($this->indexes);$i++) {
			($i < 1)? $criteria .= $this->indexes[$i] . "=" . "'". $value ."'"
				: $criteria .= " OR " . $this->indexes[$i] . "=" . "'". $value ."'";
		}
		$result = $this->select($this->table, $criteria)->fetch();
		foreach ($this->data as $key => $value) {
			$this->data[$key] = $result[$key];
		}
	}

	public function find($value = null)
	{
		$criteria = '';
		for($i=0;$i<sizeof($this->indexes);$i++) {
			($i < 1)? $criteria .= $this->indexes[$i] . "=" . "'". $value ."'"
				: $criteria .= " OR " . $this->indexes[$i] . "=" . "'". $value ."'";
		}
		if(is_null($value))
			$criteria = $value;
		$result = $this->select($this->table, $criteria)->fetchAll();
		foreach ($result as $key1 => $value1) {
			foreach ($this->data as $key2 => $value2) {
				$this->data[$key2][$key1] = $result[$key1][$key2];
			}
		}
	}

	public function setKeyMethod($key)
	{
		$find = 'find';
		$method = $$find.preg_replace("/_/", "", implode('_', array_map('ucfirst', explode('_', $key))));
		
		$this->$method = function($value) use ($key){
			$criteria = $key . '=' . "\"" . $value . "\"";
			$result = $this->select($this->table, $criteria)->fetchAll();
			foreach ($result as $key1 => $value1) {
				foreach ($this->data as $key2 => $value2) {
					$this->data[$key2][$key1] = $result[$key1][$key2];
				}
			}
		};
	}
	
	public function setSetterMethod($key){
		$set = 'set';
		$method = $$set.preg_replace("/_/", "", implode('_', array_map('ucfirst', explode('_', $key))));
		$this->$method = function($value) use ($key){
			$this->__setData($key, $value);
		};
	}

	public function setGetterMethod($key)
	{
		$get = 'get';
		$method = $$get.preg_replace("/_/", "", implode('_', array_map('ucfirst', explode('_', $key))));
		
		$this->$method = function() use ($key){
			return $this->__getData($key);
		};
	}

	public function setFilterMethod($key)
	{
		$filter = 'filter';
		$method = $$filter.preg_replace("/_/", "", implode('_', array_map('ucfirst', explode('_', $key))));

		$this->$method = function($value) use ($key){
			$this->criteria .= $key . '=' . "\"" . $value . "\"";
			return $this;
		};
	}

	public function setOrderMethod($key)
	{
		$orderBy = 'orderBy';
		$method = $$orderBy.preg_replace("/_/", "", implode('_', array_map('ucfirst', explode('_', $key))));

		$this->$method = function($direction = 'ASC') use ($key){
			$this->order .= $key . ' ' . $direction;
			return $this;
		};
	}

	public function __or()
	{
		$this->criteria .= ' OR ';
		return $this;
	}

	public function __and()
	{
		$this->criteria .= ' AND ';
		return $this;
	}

	public function querySelect()
	{
		$result = $this->select($this->table, $this->criteria, '*', $this->order)->fetchAll();
		$this->criteria = '';
		$this->order = '';
		foreach ($result as $key1 => $value1) {
			foreach ($this->data as $key2 => $value2) {
				$this->data[$key2][$key1] = $result[$key1][$key2];
			}
		}
	}
}