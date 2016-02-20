<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

require_once(__DIR__ . '/vendor/sophwork/sophwork/dist/autoloader.php');

use Sophwork\core\Sophwork;
use Sophwork\app\app\SophworkApp;
use Sophwork\modules\handlers\errors\errorHandler\ErrorHandler;

use Sophwork\modules\handlers\requests\Requests;
use Sophwork\modules\handlers\responses\Responses;

// Set up the source path for the autoloader
// $autoloader->sources = __DIR__ . '/../src/';

$app = new SophworkApp();

$app->debug = true;

$app->inject(new ErrorHandler());

include('challonge.class.php');

$app->get('/', function ()
{
	return 'api.pinnackl.com';
});

$app->get('/get/{apikey}/', function(SophworkApp $app, requests $request, $apikey){		// Inline controller
	// header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$t = $c->getTournaments();
	// echo json_encode($t);
	return "";
});

$app->get('/create/{apikey}/', function(SophworkApp $app, requests $request, $apikey){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);

	echo json_encode(['value']);
	return "";
});

$app->run();