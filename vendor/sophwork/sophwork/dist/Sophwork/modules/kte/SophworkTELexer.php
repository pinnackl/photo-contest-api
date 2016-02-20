<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Sophpkwork module : Template Engine
 *	Lexer class
 */

namespace Sophwork\modules\kte;

class SophworkTELexer{
	public $token;
	public $rules;
	public $environment;


	public function __construct()
	{
		$this->rules = [];
		$this->addLexerRule('variable-search', '/{{(\w+)}}/');
		$this->addLexerRule('variable', '/{{($key)}}/');
		
		$this->addLexerRule('variable-lower-search', '/{{(.*)}\\[L]}/');
		$this->addLexerRule('variable-lower', '/{{($key)}\\[L]}/');
		
		$this->addLexerRule('variable-upper-search', '/{{(.*)}\\[U]}/');
		$this->addLexerRule('variable-upper', '/{{($key)}\\[U]}/');

		$this->addLexerRule('macro-search', '/{% macro %}(.*){% endmacro %}/s');
		$this->addLexerRule('macro-capture', '/({% macro %})(.*)({% endmacro %})/s');

		$this->addLexerRule('macros-search', '/{% macros %}(.*){% endmacros %}/s');
		$this->addLexerRule('macros-capture', '/({% macros %})(.*)({% endmacros %})/s');
	}
	public function __get($param)
	{
		return $this->$param;
	}

	public function getRule($rulename)
	{
		return $this->rules[$rulename];
	}

	public function addEnvironment($environment)
	{
		$this->environment = $environment;
	}

	// NOTE !: If you pute variable in your rule beware that they no be interpreted use single quote '' and encapsule with {}
	public function addLexerRule($rulename, $rule)
	{
		$this->rules[$rulename] = [];
		$this->rules[$rulename][] = $rule; 
	}
}