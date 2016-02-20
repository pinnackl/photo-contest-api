<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Sophpkwork module : Template Engine
 *	Parser class
 */

namespace Sophwork\modules\kte;

use Sophwork\app\controller\appController;

class SophworkTEParser{
	protected $template;
	protected $lexer;
	protected $data;

	private $rule;

	public function __construct($template, $data=null)
	{
			$this->addData($data);
			$this->template = $template;
			$this->lexer = new SophworkTELexer();
			$this->useRule('variable');
	}

	public function __get($param)
	{
		return $this->$param;
	}

	public function __set($param, $value)
	{
		$this->$param = $value;
	}

	public function addData($data)
	{
		$this->data = $data;
	}

	public function useRule($rulename)
	{
		$this->rule = $this->lexer->getRule($rulename)[0];
	}

	public function parseTemplate($option = null)
	{
		ob_start();
		if(preg_match_all($this->lexer->getRule('variable-search')[0], $this->template)){
			$this->useRule('variable');
			$this->observer();
		}
		ob_clean();
		return $this->template;
	}

	private function convert($data)
	{
		$object = new stdClass();
		foreach ($data as $key => $value)
		{
			if(gettype($value)=='array')
				$value = $this->convert($value);
			$object->$key = $value;
		}

		return $object;
	}

	private function observer()
	{
		foreach ($this->data as $key => $value) {
			if(gettype($value) == "array"){
				$this->useRule('variable');
				$rule = $this->rule;
				
				$this->replaceBlockArray($rule, $key, $value);
				$this->replaceBlock($rule, $key, $value);
			}
			else{
				$this->useRule('variable');
				$rule = $this->rule;

				$value = $this->optionalModifier($value);
				
				$this->replaceBlock($rule, $key, $value);
				$this->replaceOne($rule, $key, $value);
			}
		}
	}
	public function optionalModifier($value, $option = null)
	{
		if(gettype($value)!='array'){
			if($option == 'lower')
				return strtolower($value);
			if($option == 'upper')
				return strtoupper ($value);
			return htmlspecialchars($value);
		}
		else{
			foreach ($value as $key => $val) {
				$val = $this->optionalModifier($val, $option);

				if($option == 'lower')
					$value[$key] = strtolower($val);
				if($option == 'upper')
					$value[$key] = strtoupper ($val);
				$value[$key] = htmlspecialchars($val);
			}
		}
		return $value[0];
	}

	public function replaceOne($rule, $key, $value, $block = null)
	{
		
		eval("\$rule = \"$rule\";");
		if($block !== null){
			if(gettype($value) != 'array')
				return preg_replace($rule, $value, $block);
		}
		$this->template = preg_replace($rule, $value, $this->template);
	}

	public function replaceBlock($rule, $key, $value)
	{
		// match if any macro block was created
		// if so get the matched result
		// use the simple value parser to replace the tag
		// then replace in the template the macro block with the right data in the template
		preg_match($this->lexer->getRule('macro-search')[0], $this->template, $match);
		if(isset($match[1])){
			preg_match_all("/{{(\\w+)}}/", trim($match[1]), $matches);
			if(isset($matches[1][0]) && $matches[1][0] != $key)
				return;

			$block = trim($match[1]);

			$this->useRule('variable');
			$rule = $this->rule;
			$value = $this->optionalModifier($value,'');
			$block = $this->replaceOne($rule, $key, $value, $block);

			$this->useRule('variable-lower');
			$rule = $this->rule;
			$value = $this->optionalModifier($value,'lower');
			$block = $this->replaceOne($rule, $key, $value, $block);

			$this->useRule('variable-upper');
			$rule = $this->rule;
			$value = $this->optionalModifier($value,'upper');
			$block = $this->replaceOne($rule, $key, $value, $block);

			$this->template = preg_replace($this->lexer->getRule('macro-capture')[0], $block, $this->template);
		}
	}

	public function replaceBlockArray($rule, $key, $value)
	{
		preg_match($this->lexer->getRule('macros-search')[0], $this->template, $match);

		$block = "";
		if(isset($match[1])){
			preg_match_all("/{{(\\w+)}}/", trim($match[1]), $matches);
			for($j=0;$j<sizeof($matches[1]);$j++){
				for($i=0;$i<sizeof($value);$i++){
				if(isset($matches[1][$j]) && $matches[1][$j] != $key)
					continue;
						$node = trim($match[1]);
							$node = $this->setActiveMenu($node, $value[$i]);

						$block .= $node;
						
						$this->useRule('variable');
						$rule = $this->rule;
						$value[$i] = $this->optionalModifier($value[$i],'');
						$block = $this->replaceOne($rule, $key, $value[$i], $block);

						$this->useRule('variable-lower');
						$rule = $this->rule;
						$value[$i] = $this->optionalModifier($value[$i],'lower');
						$block = $this->replaceOne($rule, $key, $value[$i], $block);
						
						$this->useRule('variable-upper');
						$rule = $this->rule;
						$value[$i] = $this->optionalModifier($value[$i],'upper');
						$block = $this->replaceOne($rule, $key, $value[$i], $block);

					}
				}
			$this->template = preg_replace($this->lexer->getRule('macros-capture')[0], $block, $this->template);
		}
	}

	public function setActiveMenu($node, $value)
	{
		$cont = new appController(); $page = $cont->page; // greedy to use appController class ?
		$this->useRule('variable'); $rule = $this->rule;
		if($page == $this->optionalModifier($value,'lower'))
			return $this->replaceOne($rule, 'active', $this->optionalModifier('active',''), $node);
		return $this->replaceOne($rule, 'active', $this->optionalModifier('',''), $node);
	}
}