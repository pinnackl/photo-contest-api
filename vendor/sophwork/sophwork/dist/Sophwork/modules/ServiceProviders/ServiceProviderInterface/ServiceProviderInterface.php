<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.3.0
 *	@author : Syu93
 *	--
 *	Serivice provider interface
 */
namespace Sophwork\modules\ServiceProviders\ServiceProviderInterface;

use Sophwork\app\app\SophworkApp;

interface ServiceProviderInterface
{
	public function init (SophworkApp $app, Array $parameters = []);
}