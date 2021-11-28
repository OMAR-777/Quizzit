<?php
require_once 'controllers/authController.php';
//verify user using token

$editError = array();
$message = array();
//check if user logged in
if (!isset($_SESSION['id'])) {
    header('location: login.php?errorSession=Please sign in again!');
    exit(); //stop execution
}
//change username
if (isset($_POST['submitUn'])) {
    if (isset($_POST['newUn']) && (!empty($_POST['newUn']))) {
        if ($_SESSION['username'] != $_POST['newUn']) {
            $newUn = $_POST['newUn'];
            $sql = 'SELECT username FROM users WHERE username=?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $newUn); //s- string , add email instead of ?
            $result = $stmt->execute();
            $stmt->store_result();
            $numOfUsers = $stmt->num_rows;
            $stmt->close();

            if ($result) {
                if ($numOfUsers == 0) {
                    if (changeUsername($newUn)) {
                        $message['unChanged'] = 'Username is successfully changed!';
                    } else {
                        $editError['unChangeError'] = 'Could not change username!';
                    }
                } else {
                    $editError['usernameExists'] = 'Username already exists';
                }
            } else {
                $editError['dbError'] = 'problem in DB';
            }
        } else {
            $editError['unEmpty'] = 'You already have the same username';
        }
    } else {
        $editError['unEmpty'] = 'Username is not set or empty';
    }
}
//avatar change
if (isset($_POST['submitAvatar'])) {
    //https://stackoverflow.com/questions/20556773/php-display-image-blob-from-mysql
    if (isset($_FILES['file']) && $_FILES['file']['name'] != null) {
        $file = $_FILES['file'];

        $fileName = $_FILES['file']['name'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileError = $_FILES['file']['error'];

        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));

        $allowed = array('jpg', 'jpeg', 'png');

        if (in_array($fileActualExt, $allowed)) {
            if ($fileError === 0) {
                if ($fileSize < 10000000) { //less than 20mb
                    $image = addslashes(file_get_contents($_FILES['file']['tmp_name']));
                    //you keep your column name setting for insertion. I keep image type Blob.
                    if(checkAvatarExist($_SESSION['id'])){
                        deleteAvatar($_SESSION['id']);
                    }
                    insertAvatar($image);
                } else {
                    $editError['size'] = 'size of your file is too big, make sure its less than 20mb';
                }
            } else {
                $editError['uploadError'] = 'Error uploading your file!';
            }
        } else {
            $editError['ext'] = 'You cannot upload files not in jpg, jpeg, png extension';
        }
    } else {
        $editError['null'] = 'No file choosen';
    }
}
function insertAvatar($image)
{
    global $conn;
    $userID = $_SESSION['id'];
    $insertAv = "INSERT INTO user_avatar(image,user_id) VALUES('$image','$userID')";
    mysqli_query($conn, $insertAv);
}
function displayAvatar($userID)
{
    global $conn;
    $userID = $_SESSION['id'];
    $query = "SELECT * FROM user_avatar WHERE user_id = '$userID'";
    $qry = mysqli_query($conn, $query);
    $result = mysqli_fetch_array($qry);
    echo '<img src="data:image/jpeg;base64,' . base64_encode($result['image']) . '" class="rounded-circle myavatar"/>';
}
function displayDefaultAvatar()
{
    echo '<img src="imgs/profile/default.png" class="rounded-circle myavatar"/>';
}
function deleteAvatar($userID){
    global $conn;
    $userID = $_SESSION['id'];
    $query = "DELETE FROM user_avatar WHERE user_id = '$userID'";
    $result = mysqli_query($conn, $query);
}
function checkAvatarExist($userID)
{
    global $conn;
    $userID = $_SESSION['id'];
    $query = "SELECT * FROM user_avatar WHERE user_id = '$userID'";
    $result = mysqli_query($conn, $query);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        return true;
    }
    return false;
}
function updateSession()
{
    global $conn;
    //Updating session variables
    $sqlProf = "SELECT * FROM users where id=" . $_SESSION['id'];
    $resultProf = mysqli_query($conn, $sqlProf);
    $user = mysqli_fetch_assoc($resultProf);
    $_SESSION['username'] = $user['username'];
}

function changeUsername($newUn)
{
    global $conn;

    $sql = 'UPDATE users SET username=? WHERE id=' . $_SESSION['id'];
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $newUn); //s- string , add email instead of ?
    $result = $stmt->execute();

    $stmt->close();
    if ($result) {
        return true;
    } else {
        return false;
    }
}
function changeUserPicTo($name)
{
    global $conn;

    $sql = 'UPDATE users SET avatar=? WHERE id=' . $_SESSION['id'];
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $name); //s- string , add email instead of ?
    $result = $stmt->execute();

    $stmt->close();
    if ($result) {
        $message['changed'] = 'horraay!!';
        $currentAvatar = $_SESSION['avatar'];
        $file = 'imgs/profile/' . $currentAvatar;
        if ($currentAvatar != 'default.png') { //if not default pic, then delete from server
            if (unlink($file)) {
            } else {
                echo "Error: file is not deleted";
            }
        }
    } else {
        $editError['database'] = 'could not connect to the DB';
    }
}

function checkAddedFav($quizID, $userID)
{
    global $conn;
    $sqlFav = "SELECT * FROM quiz_liked WHERE quiz_id='$quizID' AND user_id='$userID'";
    $resultFav = mysqli_query($conn, $sqlFav);
    $nResult = mysqli_num_rows($resultFav);

    if ($nResult > 0) {
        return true;
    } else {
        return false;
    }
}
function getStartingButton($quizID, $quizName)
{
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM quizstarted WHERE user_id=" . $_SESSION['id'] . " AND quiz_id='$quizID'");
    $addedFav = checkAddedFav($quizID, $_SESSION['id']);
    $starBlack = $addedFav ? "yes" : "no";
    $star = $addedFav ? '<svg style="pointer-events:none;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-star-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.283.95l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
  </svg>' : '<svg style="pointer-events:none;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-star" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.523-3.356c.329-.314.158-.888-.283-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767l-3.686 1.894.694-3.957a.565.565 0 0 0-.163-.505L1.71 6.745l4.052-.576a.525.525 0 0 0 .393-.288l1.847-3.658 1.846 3.658a.525.525 0 0 0 .393.288l4.052.575-2.906 2.77a.564.564 0 0 0-.163.506l.694 3.957-3.686-1.894a.503.503 0 0 0-.461 0z"/>
  </svg>';
    $nPlayed = mysqli_num_rows($result);
    if ($nPlayed == 0) {
        return '</p><br><a href="Quiz/startQuiz.php?requestQuiz=' . $quizName . '" type="submit" class="btn btn-sm btn-success">Start Quiz
        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-play-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.596 8.697l-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z"/>
          </svg>
    </a><button type="submit" data-qname="' . $quizName . '" data-added="' . $starBlack . '" onclick="addToFavorite(event)" class="btn btn-sm btn-light" title="Add to favorite!">
    ' . $star . '
</button>';
    } else {
        $row = mysqli_fetch_assoc($result);
        $correct = '<span class="badge badge-success">✔ ' . $row["cAns"] . '</span>';
        $wrong = '<span class="badge badge-danger">✖ ' . $row["wAns"] . '</span>';
        $points = '<span class="badge badge-primary">' . $row["points"] . 'pts</span>';
        return '' . $correct . ' ' . $wrong . '<br>' . $points . '</p><a href="Quiz/restartQuiz.php?requestQuiz=' . $quizName . '" type="submit" class="btn btn-sm btn-warning">Restart Quiz
        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-clockwise" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
        </svg>
    </a><button type="submit" data-qname="' . $quizName . '" data-added="' . $starBlack . '" onclick="addToFavorite(event)" class="btn btn-sm btn-light" title="Add to favorite!">
    ' . $star . '
</button>';
    }
}
function printFavoriteQuizzes()
{
    global $conn;

    $sqlLiked = "SELECT * FROM quiz_liked WHERE user_id=" . $_SESSION['id'];

    $resultLiked = mysqli_query($conn, $sqlLiked);
    if ($resultLiked && (mysqli_num_rows($resultLiked) > 0)) {
        while ($rowLikedQuiz = mysqli_fetch_assoc($resultLiked)) {
            $sqlQuiz = "SELECT * FROM quiz WHERE id=" . $rowLikedQuiz['quiz_id'];

            $resultQuiz = mysqli_query($conn, $sqlQuiz);
            $rowQuiz = mysqli_fetch_assoc($resultQuiz);

            $sqlNum = "SELECT * FROM question WHERE quiz_id=" . $rowQuiz['id'] . "";
            $resultNum = mysqli_query($conn, $sqlNum);
            $nQuestions = mysqli_num_rows($resultNum);


            echo '<div class="card shadow">
                        <img class="card-img-top" src="imgs/quiz/' . $rowQuiz['image'] . '" alt="Card image">
                        <div class="card-body">
                            <div class="quiz-title ellipsis" speed="120">' . $rowQuiz['name'] . '</div>
                            <p class="card-text"><span class="badge badge-pill badge-secondary">' . $nQuestions . ' Qs</span>
                           ' . getStartingButton($rowQuiz['id'], $rowQuiz['name']) . '
                        </div>
                    </div>';
        }
    } else {
        echo '<p class=text-secondary>You have not added any Quizzes to Favorite!</p>';
    }
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
        $active = 'active-ratio text-' . $color;
        $inActive = 'text-secondary';
        echo '<div class="ratio">
                    <div style="font-size: 40px;"class="ratio text-' . $color . '">' . $ratio . '%</div>
                     <div style="font-size: 15px;">
                     <span class="' . (($ratio >= 0 && $ratio < 20) ? $active : $inActive) . ' col">GG</span>
                     <span class="' . (($ratio >= 20 && $ratio < 40) ? $active : $inActive) . ' col">Mehh</span>
                     <span class="' . (($ratio >= 40 && $ratio < 60) ? $active : $inActive) . ' col">Average</span>
                     <span class="' . (($ratio >= 60 && $ratio < 80) ? $active : $inActive) . ' col">Smart</span>
                     <span class="' . (($ratio >= 80 && $ratio <= 100) ? $active : $inActive) . ' col">Genius!!</span>
                     </div>
                     <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated bg-' . $color . '" style="width:' . $ratio . '%"></div></div>
                     </div>';
    } else {
        echo '<p class=text-secondary>You have to finish atleast 1 Question to show C/W ratio.</p>';
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
    <title>Quizzit | Profile</title>
    <link rel="icon" href="imgs/logo/quizzit-icon.png">

    <script>
        var likerID = <?php echo $_SESSION['id']; ?>;
        var likerName = "<?php echo $_SESSION['username']; ?>";

        function addToFavorite(e) {
            favButton = e.target;
            var qname = $(favButton).attr('data-qname');
            var added = $(favButton).attr('data-added');
            var btnID = $(favButton).attr('data-id');


            if (qname != null && added != null) {
                console.log(qname + " " + added + " " + likerID + " " + likerName);
                $.ajax({
                    url: 'Quiz/quizController.php',
                    type: 'POST',
                    data: {
                        quizLikedName: qname,
                        addedToFav: added,
                        userID: likerID,
                        username: likerName,
                    },
                    success: function(result) {
                        if (result == "addSuccess") {
                            favButton.title = 'Remove from favorites';
                            $(favButton).attr('data-added', "yes");
                            favButton.innerHTML = `<svg style="pointer-events:none;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-star-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.283.95l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>`;
                        } else if (result == "removeSuccess") {
                            $(favButton).parent().parent().remove();
                            favButton.title = 'Add to favorites!';
                            $(favButton).attr('data-added', "no");
                            favButton.innerHTML = ` <svg style="pointer-events:none;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-star" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.523-3.356c.329-.314.158-.888-.283-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767l-3.686 1.894.694-3.957a.565.565 0 0 0-.163-.505L1.71 6.745l4.052-.576a.525.525 0 0 0 .393-.288l1.847-3.658 1.846 3.658a.525.525 0 0 0 .393.288l4.052.575-2.906 2.77a.564.564 0 0 0-.163.506l.694 3.957-3.686-1.894a.503.503 0 0 0-.461 0z"/>
</svg>`;
                        }

                    }
                });
            }

        }

        function changeAvatar(elem) {
            $(elem).hide();
            $("#editedProfile").show();

        }

        function cancelAvatar() {
            $("#editedProfile").hide();
            $("#changeBtn").show();
        }

        function changeUn(elem) {
            $('#username').hide();
            $('#changeUn').hide();
            $("#editedUn").show();

        }

        function cancelUn() {
            $("#editedUn").hide();
            $("#username").show();
            $('#changeUn').show();
        }

        function validateImg() {
            var file = document.getElementById('uploadFile').files[0];
            var submit = document.getElementById('submitFile');
            var fileErrorSize = document.getElementById('fileErrorSize');

            if (document.getElementById("uploadFile").files.length != 0) {


                size = file.size;
                console.log(size);
                //in Bytes, this is 1MB
                if (size >= 1000000) {
                    submit.disabled = true;
                    $(fileErrorSize).show();
                } else {
                    submit.disabled = false;
                    $(fileErrorSize).hide();
                }
            } else {
                submit.disabled = true;
                $(fileErrorSize).hide();

            }
        }
    </script>


</head>

<body>

    <div class="container">
        <div id="footer-nav">
            <?php require_once 'footer-nav.php';
            printNavbar('profiletab'); ?>
        </div>

        <div class="row">
            <div class="col-md-6 offset-md-3 profile-div text-center">
                <?php
                updateSession();

                ?>
                <?php 
                if (checkAvatarExist($_SESSION['id'])) {
                    displayAvatar($_SESSION['id']);
                } else {
                    displayDefaultAvatar();
                }
                ?>
                <br>
                <button id="changeBtn" onclick="changeAvatar(this)" class="btn btn-primary">Change avatar</button>

                <div id="editedProfile" style="display: none;">
                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <div id=fileErrorSize style="display: none;" class="alert alert-danger">
                            Please choose an image that is less than 1MB.
                        </div>
                        <input onchange="validateImg(this)" id="uploadFile" type="file" name="file">
                        <div>
                            <button id="submitFile" class="btn btn-primary" type="submit" name="submitAvatar" disabled>Upload</button>
                            <button onclick="cancelAvatar()" type="button" class="btn btn-danger">Cancel</button>
                        </div>
                    </form>
                </div>
                <hr>
                <?php if (count($editError) > 0) : ?>
                    <div class="alert alert-danger">
                        <?php foreach ($editError as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (count($message) > 0) : ?>
                    <div class="alert alert-success">
                        <?php foreach ($message as $mes) : ?>
                            <li><?php echo $mes; ?></li>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <h1 class="font-weight-bold">Profile information: </h1>
                <hr>
                <div class="font-weight-bold text-secondary">Username: <button onclick="changeUn()" id="changeUn" class="btn btn-primary btn-sm">Change</button></div>
                <div id="username" class="font-weight-bold text-primary"><?php echo $_SESSION['username']; ?>
                    <!-- <svg class="text-secondary" style="cursor:pointer;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
</svg> -->
                </div>
                <div id="editedUn" style="display: none;">
                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <div class="form-group"> <input type="text" name="newUn" placeholder="Enter a new username">
                            <button class="btn btn-sm btn-primary" type="submit" name="submitUn">Submit</button>
                            <button onclick="cancelUn()" type="button" class="btn btn-sm btn-danger">Cancel</button>
                        </div>
                        <div id="result"></div>
                    </form>
                </div>
                <hr>

                <div class="font-weight-bold text-secondary">E-mail: <?php
                                                                        if ($_SESSION['verified']) {
                                                                            echo "<span class='text-success'>Verified</span>";
                                                                        } else {
                                                                            echo "<span class='text-danger'>Not Verified</span>";
                                                                        } ?>
                </div>
                <div class="font-weight-bold text-primary"><?php echo $_SESSION['email']; ?></div>
                <hr>

                <h4 class="font-weight-bold">Statistics of my game completion: </h4>
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
                <p class="font-weight-bold text-secondary">Total points:<span class="text-primary"> <?php echo $points ?>pts</span></p>
                <hr>
                <p class="font-weight-bold text-secondary">Total questions:<span class="text-primary"> <?php echo ($cAns + $wAns); ?></span></p>
                <hr>
                <p class="font-weight-bold text-secondary">Correct answers:<span class="text-success"> <?php echo $cAns; ?></span> </p>
                <hr>
                <p class="font-weight-bold text-secondary">Wrong answers: <span class="text-danger"> <?php echo $wAns; ?></span> </p>
                <hr>
                <div class="font-weight-bold text-secondary">Correctence ratio: </div>
                <?php
                printRatio($cAns, $wAns);
                ?>
                <hr>
                <p class="font-weight-bold text-secondary">Total Games playes: <span class="text-primary"> <?php echo $nGames ?></span> </p>

                <hr>

                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards0" href=#cards0>
                    <h3>Favorite quizzes!💖</h3>
                </a>
                <div id="cards0" class="collapse">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printFavoriteQuizzes();
                        ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>