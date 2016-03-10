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
    unset($_GET['accessToken']);
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

$name = $userData['user']['displayName'];
$gender = $userData['user']['gender'];
$heightInches = round($userData['user']['height'] * 0.393701);
$weight = intval($userData['user']['weight'] * 2.20462);

$heightString = (intval($heightInches/12)).'\' '.($heightInches%12).'"';

?>

<!DOCTYPE html>
<html lang="en">
<ul>
    <li>name = <?php echo $name; ?></li>
    <li>gender = <?php echo $gender; ?></li>
    <li>heightInches = <?php echo $heightInches; ?></li>
    <li>weight = <?php echo $weight; ?></li>
    <li>heightString = <?php echo $heightString; ?></li>
</ul>9
</html>
