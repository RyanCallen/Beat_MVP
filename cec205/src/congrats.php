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

$request = $provider->getAuthenticatedRequest(
    'GET',
    'https://api.fitbit.com/1/user/-/friends/leaderboard.json',
    $accessToken
);
// Make the authenticated API request and get the response.
$friends = $provider->getResponse($request);

$bmi = $weight/($heightMeters*$heightMeters);
$sunScore = intval(($weeklySteps/10)/$bmi);

$barDataFriends = 0;
$labels = '';
$data = '';
$leadSteps = 0;
$userIncluded = false;
if(sizeof($friends['friends']) > 1) {
    foreach($friends['friends'] as $friend) {
        if($barDataFriends < 5) {
            $labels.= ('"'.$friend['user']["displayName"].'",');
            $data.= ('"'.$friend['summary']['steps'].'",');
            $barDataFriends++;
            if($leadSteps == 0) {
                $leadSteps = $friend['summary']['steps'];
            }
            if($friend['user']["displayName"] === $name) {
                $userIncluded = true;
            }
        }
        else {
            break;
        }
    }
    if($userIncluded == false) {
        $labels.= ('"'.$name.'",');
        $data.= ('"'.$weeklySteps.'",');
    }
}

rtrim($labels, ",");
rtrim($data, ",");

$leadDiv = '';
if($weeklySteps >= $leadSteps) {
    $leadDiv.='Congratulations, you\'re in lead this week. Keep it up!';
}
else {
    $leadDiv.='You\'re '.($leadSteps - $weeklySteps).' steps from the lead!';
}


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
            labels : ['.$labels.'],
            datasets : [
                {
                    fillColor : "#ffd480",
                    strokeColor : "#ffd480",
                    data : ['.$data.']
                }

            ]
        }';

$request = $provider->getAuthenticatedRequest(
    'GET',
    'https://api.fitbit.com/1/user/-/sleep/minutesAsleep/date/today/1w.json',
    $accessToken
);
// Make the authenticated API request and get the response.
$sleep = $provider->getResponse($request);

$sleepValues = [];
foreach($sleep["sleep-minutesAsleep"] as $night) {
    if(intval($night['value']) != 0) {
        $sleepValues[] = intval($night['value']);
    }
}
var_dump($sleepValues);
if(sizeof($sleepValues) != 0){
    $sleepAvg = array_sum($sleepValues)/sizeof($sleepValues);
}
else {
    $sleepAvg = 0;
}
$sleepHours = convertToHoursMins($sleepAvg);
$sleepDiv = "";
switch(true) {
    case $sleepAvg == 0:
        $sleepDiv .= "To get sleep quality information, use your Fitbit tracker to monitor your sleep";
        break;
    case $sleepAvg > 540:
        $sleepDiv .= "Your average time slept this week ($sleepHours) is above the recommended sleep amount. You might want to consider sleeping less!";
        break;
    case $sleepAvg > 420:
        $sleepDiv .= "Your average time slept this week ($sleepHours) is a healthy sleep amount. Keep it up!";
        break;
    default:
        $sleepDiv .= "Your average time slept this week ($sleepHours) is below the recommended sleep amount. Try to get more rest!";
}

function convertToHoursMins($time, $format = '%d hours and %d minutes') {
    if ($time < 1) {
        return 0;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}
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
        <div class="page-header" style="margin-top: 50px;">
            <h1 style="text-shadow: 2px 2px #f2f2f2">SunScore Progress <small>Current Score: <b><?php echo $sunScore; ?></b></small></h1>
        </div>
        <canvas id="countries" height="200px" width="300px"></canvas>

        <br><br>
        <div class="panel panel-default" style="background-color: #389076; max-width: 90%; box-shadow: 2px 2px 5px #d9d9d9; padding: 2px;">
            <div class="panel-body">
                
                <p class="text-center" style="font-size: 25px; font-weight: bold; color:#86C3B1; text-shadow: 2px 2px 5px black"><i class="fa fa-money" style="font-size: 25px"></i> You've saved <?php echo $discount; ?>%</p>
                <p class="text-center" style="font-size: 18px; font-weight: bold; color:white; text-shadow: 1px 1px 5px #1d493c">On your life insurance this month</p>
                <p class="text-center" style="font-size: 15px; font-weight: bold; color:white; text-shadow: 1px 1px 5px #1d493c"><?php echo $sunScoreMessage; ?></p>
            </div>
        </div>

        <div class="page-header">
            <h2 style="text-shadow: 2px 2px #f2f2f2">Your Competitions</h>
        </div>

        <?php
            if(sizeof($friends['friends']) > 1) {
                echo "<canvas id=\"income\" width=\"300\" height=\"200\"></canvas>";
            }
            else {
                echo "
                    <div class=\"page-header\">
                        <h2 style=\"text-shadow: 2px 2px #f2f2f2\">Add friends on Fitbit to get enter weekly competitions!</h2>
                    </div>
                ";
            }
        ?>

        <br><br>

        <div class="panel panel-default" style="background-color: #86C3B1; max-width: 90%; box-shadow: 2px 2px 5px #d9d9d9;">
            <div class="panel-body">
                <p class="text-center" style="font-size: 25px; font-weight: bold; color: #ddeee9; text-shadow: 2px 2px 5px black"><i class="fa fa-thumbs-up"></i> Step it up!</p>
                <p class="text-center" style="font-size: 19px; font-weight: bold;"><?php echo $leadDiv ?></p>
            </div>
        </div>

        <div class="panel panel-default" style="background-color: #223c44; max-width: 90%; box-shadow: 2px 2px 5px #d9d9d9">
            <div class="panel-body">
                <p class="text-center" style="font-size: 25px; font-weight: bold; color: white; text-shadow: 2px 2px 5px #1a2d33"><i class="fa fa-moon-o"></i> Rise and Shine!</p>
                <p class="text-center" style="font-size: 15px; font-weight: bold; color: white"><?php echo $sleepDiv ?></p>
            </div>
        </div>

        <?php
            if(sizeof($friends['friends']) >= 2) {
                echo "
                <div class=\"container\">
                <div class=\"col-lg-4 col-sm-6 text-center\">
                     <div class=\"well\">
                    <h4>What's on your mind?</h4>
                    <div class=\"input-group\">
                    <input type=\"text\" id=\"userComment\" class=\"form-control input-sm chat-input\" placeholder=\"Write your message here...\" />
                    <span class=\"input-group-btn\" onclick=\"addComment()\">
                        <a href=\"#\" class=\"btn btn-primary btn-sm\"><span class=\"glyphicon glyphicon-comment\"></span> Add Comment</a>
                    </span>
                </div>
                <hr data-brackets-id=\"12673\">
                <ul data-brackets-id=\"12674\" id=\"sortable\" class=\"list-unstyled ui-sortable\">
                    <strong class=\"pull-left primary-font\">".$friends['friends'][0]['user']["displayName"]."</strong>
                    <small class=\"pull-right text-muted\">
                        <span class=\"glyphicon glyphicon-time\"></span>7 mins ago</small>
                    </br>
                    <li class=\"ui-state-default\">I'm finally in first. Good luck catching up!</li>
                    </br>
                    <strong class=\"pull-left primary-font\">".$friends['friends'][1]['user']["displayName"]."</strong>
                    <small class=\"pull-right text-muted\">
                        <span class=\"glyphicon glyphicon-time\"></span>14 mins ago</small>
                    </br>
                    <li class=\"ui-state-default\">Wow, I've gotten in a lot more steps today than I thought I would!</li>

                </ul>
                </div>
                </div>
            ";
            }
            // Else don't show the message board
        ?>
        </center>

    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
              <center><h4 class="modal-title">Welcome to BEAT <i class="fa fa-heartbeat" style="color:#B63131"></i></h4></center>
          </div>
          <div class="modal-body">
            <p>BEAT is an application meant to help you:</p>
            <b>
            <ol>
                <li>Maintain a healthy lifestyle</li>
                <li>Compete with peers to be the most active</li>
                <li>Earn discounts on your Suncorp life insurance</li> 
            </ol>
            </b>
            
            <p>You'll be assigned a <b>SunScore</b>. Your SunScore is a numerical figure that ranks your current health status. We'll provide you with a SunScore goal to meet -- reach it by staying active and maintaining a healthy lifestyle!</p>
            <p>Every time you reach a SunScore goal, we'll reward you with some savings on your Suncorp insurance!</p>
              <div class="page-header"></div>
            <p>View the graphs on your dashboard to find out how close you are to achieving your next SunScore goal, as well as how your activity for the week matches up against what your friends are doing!</p>  
            
              
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">I got it!</button>
          </div>
        </div>

      </div>
    </div>
    

</body>

<script>

  
    //opens the modal when page loads
    $(window).load(function(){
        $('#myModal').modal('show');
    });
    

    

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
        tooltipFontSize: 20,
        tooltipTitleFontSize: 14
    }

    var barOptions = {
        tooltipFontSize: 20,
        tooltipTitleFontSize: 14,
        scaleLabel: function (valuePayload) {
            return Number(valuePayload.value)/1000 + 'k Steps';
        }

    }

    var countries= document.getElementById("countries").getContext("2d");
    new Chart(countries).Doughnut(donutData, donutOptions);
    /**********************/

    var barData = <?php echo $barData ?>;


    var income = document.getElementById("income").getContext("2d");
    new Chart(income).Bar(barData, barOptions);


</script>
</html>
