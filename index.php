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

// Tournament API call
// Get all owned tournaments
$app->get('/tournaments/get/{apikey}/', function(SophworkApp $app, requests $request, $apikey){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$t = $c->getTournaments();
	echo json_encode($t);
	return "";
});

// Create a tournament
$app->get('/tournament/create/{apikey}/{name}/', function(SophworkApp $app, requests $request, $apikey, $tournamentName){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$t = $c->createTournament( array(
		"tournament[name]" => urlencode("pinnackl_" . $tournamentName),
		"tournament[url]" => urlencode("pinnackl_" . $tournamentName),
		"tournament[hold_third_place_match]" => urlencode(true),
		"tournament[private]" => urlencode(true),
	) );
	echo json_encode($t);
	return "";
});

// Read a tournament
$app->get('/tournament/get/{apikey}/{id}/', function(SophworkApp $app, requests $request, $apikey, $id){
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$params = array("include_matches " => 1, "include_participants" => 1);
	$t = $c->getTournament($id);
	echo json_encode($t);
	return "";
});

// Update a tournament
$app->get('/tournament/update/{apikey}/{id}/{name}/', function(SophworkApp $app, requests $request, $apikey, $id, $tournamentName){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$t = $c->updateTournament($id, array(
		"tournament[name]" => urlencode("pinnackl_" . $tournamentName),
		"tournament[url]" => urlencode("pinnackl_" . $tournamentName),
		"tournament[hold_third_place_match]" => urlencode(true),
		"tournament[private]" => urlencode(true),
	) );
	echo json_encode($t);
	return "";
});

// Delete a tournament
$app->get('/tournament/delete/{apikey}/{id}/', function(SophworkApp $app, requests $request, $apikey, $id){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$t = $c->deleteTournament($id);
	echo json_encode($t);
	return "";
});

// Tournament participant API call
// Create a participant
$app->get('/tournament/{id}/participant/create/{apikey}/{participantName}/', function(SophworkApp $app, requests $request, $id, $apikey, $participantName){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	
	$params = array("participant[name]" => $participantName . '_' .rand());
	$participant = $c->createParticipant($id, $params);
	echo json_encode($participant);
	return "";
});

// Read participant
$app->get('/tournament/{id}/participant/get/{apikey}/{participantId}/', function(SophworkApp $app, requests $request, $id, $apikey, $participantId){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$params = array("inlcude_matches" => "1");
	$participant = $c->getParticipant($id, $participantId, $params);
	echo json_encode($participant);
	return "";
});

// Get all matches for a tournament
$app->get('/tournament/{id}/matches/get/{apikey}/', function(SophworkApp $app, requests $request, $id, $apikey){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$params = array();
	$matches = $c->getMatches($id, $params);
	echo json_encode($matches);
	return "";
});

// Get one matche for a tournament
$app->get('/tournament/{id}/match/get/{apikey}/{matchId}/', function(SophworkApp $app, requests $request, $id, $apikey, $matchId){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$match = $c->getMatch($id, $matchId);
	echo json_encode($match);
	return "";
});

// Update a match and submit scores
$app->get('/tournament/{id}/match/update/{apikey}/{matchId}/score/{score}/', function(SophworkApp $app, requests $request, $id, $apikey, $matchId, $score){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$params = array(
	  	"match[scores_csv]" => $score
	);
	$match = $c->updateMatch($id, $matchId, $params);
	echo json_encode($match);
	return "";
});

// Update a match and submit winner
$app->get('/tournament/{id}/match/update/{apikey}/{matchId}/winner/{winnerId}/score/{score}', function(SophworkApp $app, requests $request, $id, $apikey, $matchId, $winnerId, $score){		// Inline controller
	header('Content-Type: application/json');
	$c = new ChallongeAPI($apikey);
	$params = array(
		"match[scores_csv]" => $score,
	  	"match[winner_id]" => $winnerId
	);
	$match = $c->updateMatchMatch($id, $matchId, $params);
	echo json_encode($match);
	return "";
});


$app->run();
