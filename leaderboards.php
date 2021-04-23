<?php
require_once 'controllers/authController.php';
//verify user using token

//check if user logged in
if (!isset($_SESSION['id'])) {
    header('location: login.php?errorSession=Please sign in again!');
    exit(); //stop execution
}

function printRatio($cAns, $wAns)
{
    $n = $cAns + $wAns;
    // $style='font-size:30px; color:"'.$color.'";';
    if ($n != 0) {
        $ratio = ($n - $wAns) / ($n) * 100;
        $color = '';
        switch (true) {
            case $ratio <= 20:
                $color = 'danger';
                break;
            case $ratio <= 40:
                $color = 'warning';
                break;
            case $ratio <= 60:
                $color = 'primary';
                break;
            case $ratio <= 80:
                $color = 'info';
                break;
            case $ratio <= 100:
                $color = 'success';
                break;
        }
        $ratio = number_format((float)$ratio, 2, '.', '');
        return '<span class="text-' . $color . '">' . $ratio . '%</span>';
    }
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
    <title>Quizzit | Leaderboards</title>
    <link rel="icon" href="imgs/logo/quizzit-icon.png">


<script>
    $(document).ready(function() {
            $("#ldr").fadeIn();
        });
</script>
</head>

<body>


    <div class="container-fluid">
        <div id="footer-nav">
            <?php require_once 'footer-nav.php'; printNavbar('leaderboardstab'); ?>
        </div>
       
        <div class="row">
            <div class="col-md-12 explore-div text-center" style="border-radius: 0px;">
                <h1>Leaderboards:</h1>
                <hr>

                <div id="ldr" class="leaderboards-cards" style="display:none;">
                    <?php
                    $sqlLd = "SELECT * FROM users ORDER BY points desc";
                    $result = mysqli_query($conn, $sqlLd);
                    $i = 1;
                    $pos = '<span class="badge badge-light"></span>';
                    while ($row = mysqli_fetch_assoc($result)) {
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

                        $current = $_SESSION['id'] == $row['id'] ? 'text-primary' : '';
                        echo '<div class="shadow card">
                    <div class="' . $current . ' card-body text-left font-weight-bold">' . $pos . ' <img class="leaderboards-avatar rounded-circle"src=imgs/profile/' . $row['avatar'] . '> <span class="leaderboards-info">' . $row['username'] . ' <span class="float-right">' . printRatio($row['cAns'], $row['wAns']) . ' | ' . $row['points'] . 'pts</span></span></div>
                  </div>';
                        $i++;
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>


</body>

</html>