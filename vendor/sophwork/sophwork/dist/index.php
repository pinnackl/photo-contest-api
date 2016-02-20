<?php
/**
 *	This file is a part of the Sophwork project
 *	@version : Sophwork.0.2.0
 *	@author : Syu93
 *	--
 *	This file if for exemple purpose
 *	It's show you the way to use the Sophwork framework basics
 *	feel free to edit it or recreate your own for your project.
 *	
 *	NOTE : Uncomment the exemples bellow to see it works
 */
require_once(__DIR__ . '/../dist/autoloader.php');

use Sophwork\core\Sophwork;
use Sophwork\app\app\SophworkApp;
use Sophwork\modules\handlers\errors\errorHandler\ErrorHandler;

use Sophwork\modules\handlers\requests\Requests;
use Sophwork\modules\handlers\responses\Responses;

// Set up the source path for the autoloader
$autoloader->sources = __DIR__ . '/../src/';

/*
 *	Create a new applicaion with the Sophwork class
 * 	It will create 3 new class :
 *		- appController class
 *		- appModel class
 *		- appView class
 *
 * 	Set up app parameters here or in the config file
 */
$app = new SophworkApp([
	// 'baseUri' => '',
	'template' => __DIR__ . '/../template/',
]);

/**
 * Sophwork provide in its module a simple error handle
 * to display error more readable
 */

// Force the application to display errors
$app->debug = true;

// Inject the ErrorHandler in the app
$app->inject(new ErrorHandler());

/**
 * Declare your own error handler and customise your message to the user
 *
 * NOTE : this only work if your injet the Sophwork errorHandler  in your app !
 */
$app->errors(function($e, $errorCode) {
	echo '<h1>Custom Error !</h1>';
	die;
});

/**
 * Simply declare your routes and the patern to match
 * and link them to the controller in your sources
 *
 * You can match the following http requests
 * 		- get
 * 		- post
 * 	You can also attribute them a name so you can use UrlGenerator to create links
 */

// Separate controller file (recommended)
$route = $app->get('/', ['MyApp\Controller\Home' => 'show'], 'home');

/**
 * You can use "controller hooks" to manage default behavior of your controllers
 *
 * "controller before hook" allows you to manage the Request before the controller is called
 * 
 * NOTE : If a "before hook" or "controller before hook" returns a Responses object
 * the controller that match the route will not be run
 * and the response passe directly to the "after hook"
 */
$route->before(function($app, $request){
	echo 'before controller<br>';
	// return new Responses('before controller that return a response object<br>');
});

/**
 * "controller after hook" allows you to manage the response after the controller have been called
 * but before the "after hook"
 */
$route->after(function($app, $response, $requests){
	echo 'controller after<br>';
	return $response . '<h2>We came in peace!</h2>';
});

$app->get('/{name}/', function(SophworkApp $app, requests $request, $name){		// Inline controller
	return "<h1>Hello " . $name . "</h1>";
});

$app->post('/form', ['MyApp\Controller\Home' => 'form']);

/**
 * You can use "hooks" to manage default behavior of your app
 * 
 * "before hook" allows you to manage the Request before the controller is called
 */
$app->before(function ($app, $requests) {
	// $requests // ...
	echo 'before app<br>';
	// return new Responses('before controller that return a response object<br>');
});

/**
 * "after hook" allows you to manage the response after the controller have been called
 */
$app->after(function ($app, $responses) {
	// What ever you have to do here ...
	// $responses->getResponse();
	echo 'after app<br>';
});

// Run the actual app
$app->run();