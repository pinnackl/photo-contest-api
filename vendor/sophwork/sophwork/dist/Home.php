<?php
namespace MyApp\Controller;
use Sophwork\app\app\SophworkApp;
use Sophwork\app\view\AppView;
use Sophwork\modules\handlers\requests\Requests;
use Sophwork\modules\handlers\responses\Responses;
use Sophwork\modules\generators\urls\UrlGenerator;
class Home
{
	public function show(SophworkApp $app, Requests $requests) {
		// Return direct response
		return 'Hello World';
		// 
		// Render the needed template using the template path configuration
		// $view = $app->appView;
		// 
		// End up this application if something go wrong
		// return Responses('Error page not found', 404);
		// or
		// return $app->abort();
		// return $view->renderView('home');
	}

	public function hello(SophworkApp $app, Requests $requests, $name) {
		return '<h1>Hello ' . $name . '</h1>';
	}
}