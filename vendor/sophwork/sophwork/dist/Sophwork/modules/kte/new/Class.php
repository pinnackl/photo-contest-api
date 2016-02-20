<?php

// namespace templateEngine;

Class KTE{

	protected static $modifiers = [
		'S' => 'htmlspecialchars',
		'U' => 'strtoupper',
		'L' => 'strtolower',
		'FU' => 'ucfirst',
		'FL' => 'lcfirst',
	];

	public function __construct()
	{

	}

	public static function e($value, $modifier = 'S')
	{
		$method = self::$modifiers[$modifier];
		echo $method($value);
	}
}

