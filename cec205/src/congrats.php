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
$weekActivity = $provider->getResponse($request);
$weeklySteps = 0;
foreach($weekActivity["activities-steps"] as $dailySteps) {
    $weeklySteps += intval($dailySteps['value']);
}

$request = $provider->getAuthenticatedRequest(
    'GET',
    'https://api.fitbit.com/1/user/-/profile.json',
    $accessToken
);
// Make the authenticated API request and get the response.
$userData = $provider->getResponse($request);

$name = $userData['user']['displayName'];
$gender = $userData['user']['gender'];
$heightMeters = $userData['user']['height']/100;
$weight = $userData['user']['weight'];

$bmi = $weight/($heightMeters*$heightMeters);
$sunScore = intval(($weeklySteps/10)/$bmi);
$kyleSteps = 57340;
$karenSteps = 49126;
$joeSteps = 73072;
$leadDiv = '<p>';
if($weeklySteps > $joeSteps) {
    $leadDiv.='Congratulations, you\'re in lead this week. Keep it up!';
}
else {
    $leadDiv.='Step it up! You\'re '.($joeSteps - $weeklySteps).' steps from the lead!';
}
$leadDiv.='</p>';

switch(true) {
    case ($sunScore >= 400):
        $discount = 5;
        $sunScoreMessage = 'You have the maximum discount!';
        $max = 400;
        break;
    case ($sunScore >= 350):
        $discount = 4;
        $sunScoreMessage = 'Raise your SunScore '.(400 - $sunScore).' points to get a 5% discount!';
        $max = 400;
        break;
    case ($sunScore >= 300):
        $discount = 3;
        $sunScoreMessage = 'Raise your SunScore '.(350 - $sunScore).' points to get a 4% discount!';
        $max = 350;
        break;
    case ($sunScore >= 250):
        $discount = 2;
        $sunScoreMessage = 'Raise your SunScore '.(300 - $sunScore).' points to get a 3% discount!';
        $max = 300;
        break;
    default:
        $discount = 0;
        $sunScoreMessage = 'Raise your SunScore '.(250 - $sunScore).' points to get a 2% discount!';
        $max = 250;
}

$barData = '{
            labels : ["You","Kyle", "Karen","Joe"],
            datasets : [
                {
                    fillColor : "#77CAF3",
                    strokeColor : "#4D9DC4",
                    data : ['.$weeklySteps.','.$kyleSteps.','.$karenSteps.','.$joeSteps.']
                }

            ]
        }';

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
        
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        

        <script src='Chart.min.js'></script>
        
    </head>
    
    <body>

    <nav class = "navbar navbar-default navbar-fixed-top" role = "navigation">

            <div class = "navbar-header">
                <button type = "button" class = "navbar-toggle" 
                 data-toggle = "collapse" data-target = "#example-navbar-collapse">
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                </button>

                <a class = "navbar-brand" href = "#">BEAT <i class="fa fa-heartbeat" style="color:#B63131"></i></a>
            </div>

            <div class = "collapse navbar-collapse" id = "example-navbar-collapse">

                <ul class = "nav navbar-nav">
                    <li class = "active"><a href="#"><b><i class="fa fa-dashboard"> </i></b> Dashboard</a></li>
                    <li><a href="<?php echo "profile.php?accessToken=".$accessToken ?>"><b><i class="fa fa-user"> </i></b> My Profile</a></li>
                    <li><a href = "#"><b><i class="fa fa-gear"> </i></b> Settings</a></li>
                    <li><a href = "#"><b><i class="fa fa-sign-out"> </i></b> Log Out (<?php echo $name; ?>)</a></li>

                </ul>
            </div>

        </nav>
        
        <center>
            <div class="page-header" style="margin-top: 70px;">
              <h1>SunScore Progress <small>Current Score: <b><?php echo $sunScore; ?></b></small></h1>
            </div>
            <canvas id="countries" height="200px" width="300px"></canvas>

            <div class="sunscore-message">
                <center>
                    <p>You've saved:</p>
                    <h1><?php echo $discount; ?>%</h1>
                    <p>On your life insurance next month</p>
                    <p><?php echo $sunScoreMessage; ?></p>
                </center>
            </div>
            
            <div class="page-header">
              <h1>Your Competitions</h1>
            </div>

            
            <canvas id="income" width="300" height="200"></canvas>


            <br><br><b><?php echo $leadDiv ?></b><br>
        </center>
        
        <div class="container">
    <div class="col-lg-4 col-sm-6 text-center">
    <div class="well">
        <h4>What's on your mind?</h4>
    <div class="input-group">
        <input type="text" id="userComment" class="form-control input-sm chat-input" placeholder="Write your message here..." />
	    <span class="input-group-btn" onclick="addComment()">     
            <a href="#" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-comment"></span> Add Comment</a>
        </span>
    </div>
    <hr data-brackets-id="12673">
    <ul data-brackets-id="12674" id="sortable" class="list-unstyled ui-sortable">
        <strong class="pull-left primary-font">Joe</strong>
        <small class="pull-right text-muted">
           <span class="glyphicon glyphicon-time"></span>7 mins ago</small>
        </br>
        <li class="ui-state-default">About to start my workout... I'll be reaching first place soon!</li>
        </br>
         <strong class="pull-left primary-font">Kyle</strong>
        <small class="pull-right text-muted">
           <span class="glyphicon glyphicon-time"></span>14 mins ago</small>
        </br>
        <li class="ui-state-default">Wow, I've gotten in a lot more steps today than I thought I would!</li>
        
    </ul>
    </div>
</div>


    </body>
    
    <script>
        
        /* NOTE, FILL THESE CHARTS WITH DATA FROM THE RESPONSE IN NEXT ITERATION */
        /*DONUT GRAPH*/
        var donutData = [
            {
                value: <?php echo ($max - $sunScore); ?>,
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
        
        var barData = <?php echo $barData ?>;
        
    
        var income = document.getElementById("income").getContext("2d");
        new Chart(income).Bar(barData);
        
 
    </script>
</html>
