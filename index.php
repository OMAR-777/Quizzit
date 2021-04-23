<?php
require_once 'controllers/authController.php';
//verify user using token
if (isset($_GET['token'])) { //if token came from email is set 
    $token = $_GET['token'];
    verifyUser($token);
}

//Reset user's password using token
if (isset($_GET['password-token'])) { //if token came from email is set 
    $passwordToken = $_GET['password-token'];
    resetPassword($passwordToken);
}

//check if user logged in
if (!isset($_SESSION['id'])) {
    header('location: login.php?errorSession=Please sign in.');
    exit(); //stop execution

}
$someVar = 1;
function getUserRank()
{
    global $conn;
    $sqlLd = "SELECT * FROM users ORDER BY points desc";
    $result = mysqli_query($conn, $sqlLd);
    $i = 1;
    $pos = '<span class="badge badge-light"></span>';
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['id'] == $_SESSION['id']) {
            break;
        }
        $i++;
    }
    switch ($i) {
        case 1:
            $pos = '👑<span class="badge badge-success">1st</span>';
            break;
        case 2:
            $pos = '⚡<span class="badge badge-info">2nd</span>';
            break;
        case 3:
            $pos = '✨<span class="badge badge-primary">3rd</span>';
            break;
        case ($i == 11 || $i == 12 || $i == 13):
            $pos = '<span class="badge badge-light">' . $i . 'th</span>';
            break;
        case $i % 10 == 1:
            $pos = '<span class="badge badge-light">' . $i . 'st</span>';;
            break;
        case $i % 10 == 2:
            $pos = '<span class="badge badge-light">' . $i . 'nd</span>';;
            break;
        case $i % 10 == 3:
            $pos = '<span class="badge badge-light">' . $i . 'rd</span>';;
            break;
        default:
            $pos = '<span class="badge badge-light">' . $i . 'th</span>';
    }
    return $pos;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CDN-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <link rel="stylesheet" href="styles.css">
    <title>Quizzit | Home</title>
    <link rel="icon" href="imgs/logo/quizzit-icon.png">



</head>

<body>


    <div class="container-fluid">
        <div id="footer-nav">
        <?php require_once 'footer-nav.php'; printNavbar('hometab'); ?>
        </div>
        
        <div class="row">
            <div class="col-md-12 explore-div text-center" style="border-radius: 0px;">
                <h1 class="display-4">Welcome to<img style="display: inline;" class="logo" src="imgs/logo/auth-logo.png"></h1>

                <h3>Hello <b><?php echo $_SESSION['username']; ?></b>!</h3>
                <hr>
                <?php if (isset($_SESSION['message'])) : ?>
                    <div class="alert   <?php echo $_SESSION['alert-class']; ?>">
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        unset($_SESSION['alert-class']);

                        ?>
                    </div>

                <?php endif; ?>

               

             

            
                <hr>
                <?php
                $result = mysqli_query($conn, "SELECT points,cAns,wAns FROM users WHERE id=" . $_SESSION['id']);
                $user = mysqli_fetch_assoc($result);
                $points = $user['points'];
                $cAns = $user['cAns'];
                $wAns = $user['wAns'];

                $result = mysqli_query($conn, "SELECT * FROM quizstarted WHERE user_id=" . $_SESSION['id']);
                $nGames = mysqli_num_rows($result);
                ?>
                <p class="font-weight-bold text-secondary">Your points: <span class="text-primary"><?php echo $points; ?>pts</span> </p>
                <hr>
                <p class="font-weight-bold text-secondary">Your rank: <?php echo getUserRank(); ?></p>



            </div>
        </div>
    </div>



</body>


</html>