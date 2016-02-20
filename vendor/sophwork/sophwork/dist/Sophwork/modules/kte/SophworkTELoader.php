<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Sophpkwork module : Template Engine
 *	Loader class
 */

namespace Sophwork\modules\kte;

class SophworkTELoader{
	protected $template;

	public function __construct()
	{ // FIXME : add the loading of multiple template in 1 loader (array)

	}

	public function __get($param)
	{
		return $template;
	}

	public function loadFromArray($template)
	{
		// not used yet
	}

	public function loadFromFile($template)
	{
		if(file_exists($template))
			return $this->template = file_get_contents($template);
	}
}