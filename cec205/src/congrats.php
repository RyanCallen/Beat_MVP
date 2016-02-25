<?php
require __DIR__ . '/../vendor/autoload.php';
use djchen\OAuth2\Client\Provider\Fitbit;

// Constant clientId
const CLIENT_ID = '227HB2';
// Constant Client Secret
const CLIENT_SECRET = 'b62af360ee95e6f8ab519f16f7a9fdef';
// Constant redirect URI
const REDIRECT_URI = 'http://localhost/beat_mvp/cec205/src/hello.php';
$accessToken = "";

if (isset($_GET['accessToken'])) {
    $accessToken = $_GET['accessToken'];
}

$provider = new Fitbit([
    'clientId'          => CLIENT_ID,
    'clientSecret'      => CLIENT_SECRET,
    'redirectUri'       => REDIRECT_URI
]);


// The provider provides a way to get an authenticated API request for
// the service, using the access token; it returns an object conforming
// to Psr\Http\Message\RequestInterface.



$request = $provider->getAuthenticatedRequest(
    'GET',
    'https://api.fitbit.com/1/user/-/profile.json',
    $accessToken
);
// Make the authenticated API request and get the response.
$userData = $provider->getResponse($request);
//var_dump($userData);
echo "\n\n";

$date = date("Y-m-d");
$request = $provider->getAuthenticatedRequest(
    'GET',
    "https://api.fitbit.com/1/user/-/activities/date/$date.json",
    $accessToken
);
// Make the authenticated API request and get the response.
$activity = $provider->getResponse($request);
//var_dump($activity);
echo "\n\n";

$request = $provider->getAuthenticatedRequest(
    'GET',
    "https://api.fitbit.com/1/user/-/activities/steps/date/today/1w.json",
    $accessToken
);
// Make the authenticated API request and get the response.
$monthActivity = $provider->getResponse($request);
var_dump($monthActivity);
echo "\n\n";
