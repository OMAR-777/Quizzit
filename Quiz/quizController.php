<?php
// require 'controllers/authController.php';

if($_SERVER["DOCUMENT_ROOT"]=='C:/xampp/htdocs'){//if local host
    $ROOT=dirname(__DIR__);
}else{
    $ROOT=$_SERVER["DOCUMENT_ROOT"];
}

require_once $ROOT.'/controllers/authController.php';
if (!isset($_SESSION['id'])) {
    header('location: ../login.php?errorSession=Please sign in again!');
    exit(); //stop execution
}

function getQuizID($quizName)
{
    global $conn;
    $sqlID = "SELECT id FROM quiz WHERE name=?";
    $stmt = $conn->prepare($sqlID);
    $stmt->bind_param('s', $quizName);
    $resultID = $stmt->execute();
    if ($resultID) {
        $stmt->bind_result($id);
        while ($stmt->fetch()) {
            $quizID = $id;
        }
        $stmt->close();
    }
    return $quizID;
}


function startedQuiz($userID, $quizID)
{
    global $conn;
    $sqlStarted = "SELECT * FROM quizstarted WHERE user_id='$userID' AND quiz_id='$quizID'";
    $result = mysqli_query($conn, $sqlStarted);

    if ($result) {
        if (mysqli_num_rows($result) != 0) {
            return true;
        } else {
            return false;
        }
    }
}
if (isset($_POST["startedQuiz"])) {
    //from quiz page

    $startedQuizName = $_POST["startedQuiz"];
    $sqlID = "SELECT id FROM quiz WHERE name=?";
    $stmt = $conn->prepare($sqlID);
    $stmt->bind_param('s', $startedQuizName);
    $resultID = $stmt->execute();

    if ($resultID) {
        $userID = $_SESSION['id'];
        $userName = $_SESSION['username'];
        $quizID;
        $stmt->bind_result($id);
        while ($stmt->fetch()) {
            $quizID = $id;
        }
        $stmt->close();
        $sqlQuizz = "INSERT INTO quizstarted (quiz_id,quiz_name,user_id,user_name) VALUES ('$quizID',?,'$userID',?)";
        $stmt = $conn->prepare($sqlQuizz);
        $stmt->bind_param('ss', $startedQuizName, $userName);
        $resultID = $stmt->execute();
        if ($resultID) {
        } else {
            die("Error: could not register a started quiz");
        }
        $stmt->close();
        $_SESSION['quizID'] = $quizID;
    }
}

if (isset($_POST["totalPoints"]) && isset($_POST["totalWrong"]) && isset($_POST["totalCorrect"]) && isset($_SESSION['quizID'])) {
    //done quiz from quiz page
    $userID = $_SESSION['id'];
    $points = $_POST["totalPoints"];
    $nWrong = $_POST["totalWrong"];
    $nCorrect = $_POST["totalCorrect"];
    $quizID = $_SESSION['quizID'];
    //update user
    $sqlUpdate = "UPDATE users
    SET points = points+'$points', cAns=cAns+ '$nCorrect', wAns=wAns+'$nWrong'
    WHERE id = '$userID'";
    $resultUpdate = mysqli_query($conn, $sqlUpdate);

    if (!$resultUpdate) {
        die("Error updating user");
    }
    //update startedquiz
    $sqlUpdate = "UPDATE quizstarted
    SET points ='$points', cAns='$nCorrect', wAns='$nWrong'
    WHERE user_id = '$userID' AND quiz_id='$quizID'";

    $resultUpdate = mysqli_query($conn, $sqlUpdate);

    if (!$resultUpdate) {
        die("Error updating quiz");
    }
}

if (isset($_POST["quizLikedName"]) && isset($_POST['addedToFav']) && isset($_POST['userID'])&&isset($_POST['username'])) {
    $quizLikedName = $_POST["quizLikedName"];
    $quizLikedID=getQuizID($quizLikedName);
    $added = $_POST["addedToFav"];
    $likerID = $_POST['userID'];
    $likerName = $_POST['username'];

    if ($added == "no") {
        $sqlLiked = "INSERT INTO quiz_liked(quiz_id,user_id,quiz_name,user_name) VALUES('$quizLikedID','$likerID',?,?)";
        $stmt = $conn->prepare($sqlLiked);
        $stmt->bind_param('ss', $quizLikedName, $likerName);
        $resultLiked = $stmt->execute();
        if ($resultLiked){
            echo "addSuccess";
        }
        else{
            echo "fail database";
        }
        $stmt->close();
            
    } elseif($added == "yes") {
        $sqlUnliked = "DELETE FROM quiz_liked WHERE quiz_id='$quizLikedID' AND user_id='$likerID'";
        $resultUnliked=mysqli_query($conn,$sqlUnliked);
        if($resultUnliked){
            echo "removeSuccess";
        }
        else{
            echo "fail database";
        }
    }
}
