<?php

/**
 * @author Ireneusz Kierkowski <ircykk@gmail.com>
 * @copyright 2016 Ireneusz Kierkowski <https://github.com/ircykk>
 * @license http://www.opensource.org/licenses/MIT MIT License
 */

// For debug
ini_set('display_errors', true);

include 'lib/AllegroRestApi.php';
include 'lib/AllegroRestApiClient.php';
include 'lib/session/SessionInterface.php';
include 'lib/session/FilesystemSession.php';
include 'lib/session/PhpSession.php';
include 'lib/exception/RestApiException.php';
include 'lib/exception/SessionException.php';

// Allegro REST API credentials
// You have to register your application before you start
// Visit https://credentials.allegroapi.io/
define('APP_URL', 		'http://localhost/***/');
define('CLIENT_ID', 	'52f***8cc');
define('CLIENT_SECRET', 'oLk***b1E');
define('API_KEY', 		'eyJ***k4=');

// GET vars
$OauthCode = isset($_GET['code']) ? $_GET['code'] : null;

$client = new AllegroRestApiClient();

if($OauthCode) {
	// We have Oauth code from API
	// start brand new session
	$client->startSession($OauthCode);

	// Redirect with valid session [optional]
	header('Location: '.APP_URL);
	exit();
	
} else if ($client->initSession()) {
	// Ok we have valid session
	// rest of the app logic goes here
	// ... PHP CODE ....
	echo '<h1>Authenticated! Session expires: '.date("Y-m-d H:i:s", $client->getSessionExpiresTime()).'</h1>';

} else {
	// Session expired and unable to refresh
	// since there is no exception 
	// propably session expired pernamently
	// show link for reauthenticate
	echo '<h1>Authentication required: <a href="'.$client->getAuthUrl('authorize', array('response_type' => 'code', 'client_id' => CLIENT_ID, 'api-key' => API_KEY, 'redirect_uri' => APP_URL)) .'">link</a></h1>';
}
