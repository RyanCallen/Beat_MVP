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
$heightInches = $userData['user']['height'] / 0.393701;
$weight = $userData['user']['weight'] * 2.20462;

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
    "https://api.fitbit.com/1/user/-/activities/steps/date/today/1m.json",
    $accessToken
);
$sunScore = 170;
// Make the authenticated API request and get the response.
$monthActivity = $provider->getResponse($request);
$monthActivityJson = json_encode($monthActivity);
//var_dump($monthActivityJson);
echo "\n\n";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>BEAT</title>

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta charset=utf-8 />
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
        
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
        

        <script src='Chart.min.js'></script>
        
    </head>
    
    <body>
        <!-- NAV BAR -->
        <nav class="navbar navbar-default">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">
                BEAT <i class="fa fa-heartbeat" style="color:#B63131"></i>
              </a>
            </div>
          </div>
        </nav>
        
        <center>
            <div class="page-header">
              <h1>SunScore Progress <small>Current Score: <b><?php echo $sunScore; ?></b></small></h1>
            </div>
            <canvas id="countries" height="200px" width="300px"></canvas>
            
            <div class="page-header">
              <h1>Your Competitions</h1>
            </div>

            
            <canvas id="income" width="300" height="200"></canvas>
            
            
            
        </center>
        <a class="navbar-brand" href="<?php echo "profile.php?accessToken=".$accessToken ?>">
            PROFILE PAGE
        </a>
    </body>
    
    <script>
        
        /* NOTE, FILL THESE CHARTS WITH DATA FROM THE RESPONSE IN NEXT ITERATION */
        /*DONUT GRAPH*/
        var donutData = [
            {
                value: <?php echo (200 - $sunScore); ?>,
                color:"#C2F6C9",
                label: "Points to go"
            },
            {
                value : <?php echo $sunScore; ?>,
                color : "#5DD46D",
                label: "Current Score"
            }
        ];
        
        var donutOptions = {
            segmentShowStroke : false,
            animateScale : true,  
        }
        
        var countries= document.getElementById("countries").getContext("2d");
        new Chart(countries).Doughnut(donutData, donutOptions);
        /**********************/
        
        var barData = {
            labels : ["Adam","Ryan","Michael"],
            datasets : [
                {
                    fillColor : "#77CAF3",
                    strokeColor : "#4D9DC4",
                    data : [9513,6266,8427]
                }

            ]
        }
        
    
        var income = document.getElementById("income").getContext("2d");
        new Chart(income).Bar(barData);
        
 
    </script>
</html>
