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
$age = $userData['user']['age'];

$heightString = (intval($heightInches/12)).'\' '.($heightInches%12).'"';

$bmi = intval(($weight/($heightInches*$heightInches))*703);


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
        <!-- NAV BAR -->
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
                    <li><a href="<?php echo "congrats.php?accessToken=".$accessToken ?>"><b><i class="fa fa-dashboard"> </i></b> Dashboard</a></li>
                    <li class = "active"><a href="#"><b><i class="fa fa-user"> </i></b> My Profile</a></li>
                    <li><a href = "#"><b><i class="fa fa-gear"> </i></b> Settings</a></li>
                    <li><a href = "#"><b><i class="fa fa-sign-out"> </i></b> Log Out (<?php echo $name; ?>)</a></li>

                </ul>
            </div>

        </nav>
        
        
    <div class="panel panel-default" style="margin-top: 50px;">
        <div class="panel-heading">
            <h3 class="panel-title" id="nameLabel" style="font-size: 30px; color:#404040;"><center><b><?php echo $name; ?>, <?php echo $age; ?></b></center></h3>
        </div>
        <div class="panel-body">
            <center><img class="img-circle" style="max-width: 50%; max-height: 50%" src="http://bit.ly/1U4dF6H"></center>
            <br>
            <ul class="list-group" style="font-size: 20px">
                

                <center>
                <li class="list-group-item" id="genderLabel">Hi2</li>
                <li class="list-group-item"><b>Height:</b> <?php echo $heightString; ?></li>
                <li class="list-group-item"><b>Weight:</b> <?php echo $weight; ?></li>
                <li class="list-group-item"><b>BMI:</b> <?php echo $bmi; ?></li>
                </center>
                
                <script>
                    
                    var element = document.getElementById("genderLabel");
                    var element2 = document.getElementById("nameLabel");
                    var gen = '<?php echo $gender ;?>';
                    var genString = gen.toString();
                    
                    var g = 1;
                    
                    if(genString == 'MALE'){
                        element.innerHTML = "<b>Gender: <i class='fa fa-male fa-lg'></i></b>";
                        element2.style.color = '#4d88ff';
                    }
                    
                    else if(genString == 'FEMALE'){
                        element.innerHTML = "<b>Gender: <i class='fa fa-female fa-lg'></i></b>";
                        element2.style.color = '#ff80bf';
                    }
                    else {
                        element.innerHTML = "NA";
                    }


                </script>

            </ul>
        </div>
    </div>
        
    </body>
    
</html>
