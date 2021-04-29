<?php

require_once 'auth.php';

//check if user logged in
if (!isset($_SESSION['adminID'])) {
    header('location: loginA.php?errorSession=Please sign in again!');
    exit(); //stop execution

}

$errors = array();
$messages = array();
$warnings = array();

function addAvatar()
{
    $file = $_FILES['file'];

    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];

    $imageName = "";
    if ($_FILES['file']['name'] == null) {
        return $imageName;
    }

    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = array('jpg', 'jpeg', 'png');

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 10000000) { //less than 20mb
                $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                $fileDestination = ROOT . '/imgs/quiz/' . $fileNameNew;
                move_uploaded_file($fileTmpName, $fileDestination);
                $messages['fileAccepted'] = 'image accepted!';
                $imageName = $fileNameNew;
            } else {
                $errors['fileSize'] = 'size of your file is too big, make sure its less than 20mb';
            }
        } else {
            $errors['fileUpload'] = 'Error uploading your file!';
        }
    } else {
        echo $errors['fileExt'] = 'You cannot upload files not in jpg, jpeg, png extension';
    }
    return $imageName;
}

if (isset($_POST['submitted'])) {
    //cannot add string with (', "")
    $quizName = $_POST['quizname'];
    $quizCategory = $_POST['quizCategory'];
    //insert question
    $imageName = addAvatar();
    if (isset($_FILES['file']) && $_FILES['file']['name'] != null && $imageName != null) { //if image added
        $sqlQuiz = "INSERT INTO quiz (name, category,image) VALUES (?,?,?)";
        $stmt = $conn->prepare($sqlQuiz);
        $stmt->bind_param('sss', $quizName, $quizCategory, $imageName);
    } else {
        $warnings["noImage"] = "no image posted";
        $sqlQuiz = "INSERT INTO quiz (name, category) VALUES (?,?)";
        $stmt = $conn->prepare($sqlQuiz);
        $stmt->bind_param('ss', $quizName, $quizCategory);
    }
    $resultQuiz = $stmt->execute();
    $stmt->close();

    $quizID = $conn->insert_id;
    $warnings['newID'] = "new Quiz id is: " . $quizID;
    $totalQuestions = 0;
    $totalAnswers = 0;
    if ($resultQuiz) {
        $i = 1;
        while (isset($_POST['question' . $i])) {
            $question = $_POST['question' . $i];
            $rightAnswer = $_POST[$i . 'radio'];
            //insert question
            $sqlQuestion = "INSERT INTO question (quiz_id, question) VALUES ('$quizID', ?)";
            $stmt = $conn->prepare($sqlQuestion);
            $stmt->bind_param('s', $question);
            $resultQuestion = $stmt->execute();
            $stmt->close();

            if ($resultQuestion)
                $totalQuestions++;
            $questionID = $conn->insert_id;
            $j = 1;
            while (isset($_POST[$i . 'answer' . $j])) {
                $answer = $_POST[$i . 'answer' . $j];
                $isRight = false;
                //insert answer
                if ($rightAnswer == $i . 'answer' . $j) {
                    $isRight = true;
                }
                $sqlAnswer = "INSERT INTO answer (question_id, answer, is_right) VALUES ('$questionID', ?,'$isRight')";
                $stmt = $conn->prepare($sqlAnswer);
                $stmt->bind_param('s', $answer);
                $resultAnswer = $stmt->execute();
                $stmt->close();

                if ($resultAnswer)
                    $totalAnswers++;
                $j++;
            }
            $i++;
        }

        $messages['questions'] = 'You have successfully stored ' . $totalQuestions . ' questions in the database';
        $messages['answers'] = 'You have successfully stored ' . $totalAnswers . ' answers in the database';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--hello-->
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">

    <!-- google -->

    <title>Quizzit | Admin</title>
    <link rel="icon" href="../imgs/logo/quizzit-icon.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            var i = 1;

            $("#addQuestion").click(function() {
                $("#quiz").append('<div class="form-group" id="question' + i + '"><hr><hr>' +
                    '<label for="username">Question ' + i + ':</label>' +
                    ' <textarea class="form-control" name="question' + i + '" rows="3" required></textarea>' +
                    '<div class="form-group">' +
                    '<input  type="radio" name="' + i + 'radio" value="' + i + 'answer1" required>' + //
                    '<label for="username"> Answer 1:</label>' +
                    '<input type="text" name="' + i + 'answer1" class="form-control form-control-sm" required>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<input type="radio" name="' + i + 'radio" value="' + i + 'answer2">' + //
                    '<label for="username"> Answer 2:</label>' +
                    '<input type="text" name="' + i + 'answer2" class="form-control form-control-sm" required>' +
                    '</div>' +
                    '<div class="last2 form-group">' +
                    '<input type="radio" name="' + i + 'radio" value="' + i + 'answer3">' + //
                    '<label for="username"> Answer 3:</label>' +
                    '<input type="text" name="' + i + 'answer3" class="form-control form-control-sm"required>' +
                    '</div>' +
                    '<div class="last1 form-group">' +
                    '<input type="radio" name="' + i + 'radio" value="' + i + 'answer4" >' + //
                    '<label for="username"> Answer 4:</label>' +
                    '<input type="text" name="' + i + 'answer4" class="form-control form-control-sm" required>' +
                    '</div>' +
                    '<button type="button" class="removeAnswer btn btn-danger btn-block btn-md">Remove answer</button>' +
                    '</div>');
                i++;
                if (i == 2) {
                    $('.removeQuestion').show();
                }
            });



            $(document).on('click', '.removeAnswer', function() {
                if ($(this).parent().children(".last1").length > 0)
                    $(this).parent().children(".last1").remove();
                else if ($(this).parent().children(".last2").length > 0) {
                    $(this).parent().children(".last2").remove();
                    $(this).remove();
                }
            });
            $(document).on('click', '.removeQuestion', function() {
                if (i >= 2) {
                    $("#question" + (i - 1)).remove();
                    i--;
                    if (i == 1) {
                        $('.removeQuestion').hide();
                    }
                }
            });
        });

        function changeAvatar(elem) {
            $(elem).hide();
            $("#editedProfile").show();

        }

        function cancelAvatar() {
            $("#editedProfile").hide();
            $("#changeBtn").show();
        }

        function validateImg() {
            var file = document.getElementById('uploadFile').files[0];
            var fileErrorSize = document.getElementById('fileErrorSize');

            if (document.getElementById("uploadFile").files.length != 0) {


                size = file.size;
                console.log(size);
                //in Bytes, this is 1MB
                if (size >= 1000000) {
                    $(fileErrorSize).show();
                    document.getElementById('uploadFile').value = '';
                } else {
                    $(fileErrorSize).hide();
                }
            } else {
                $(fileErrorSize).hide();
            }
        }
    </script>


</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form-div">
                <!-- logo -->
                <form action="quizAdder.php" method="post" enctype="multipart/form-data">

                    <!-- error-info -->
                    <?php if (count($errors) > 0) : ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <!-- warnings-info -->
                    <?php if (count($warnings) > 0) : ?>
                        <div class="alert alert-warning">
                            <?php foreach ($warnings as $warning) : ?>
                                <li><?php echo $warning; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <!-- success-info -->
                    <?php if (count($messages) > 0) : ?>
                        <div class="alert alert-success">
                            <?php foreach ($messages as $message) : ?>
                                <li><?php echo $message; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div id="quiz" class="text-center">
                        <img class="logo" src="../imgs/logo/auth-logo.png">
                        <a href="loginA.php?logout=1" class="btn btn-danger float-right">Log out</a>
                        <h3 class="text-center">Add a Quiz!</h3>
                        <p style="color:grey;" class="text-center">Click on the radio button for the correct answer.</p>

                        <div class="form-group">
                            <label for="username">Quiz name:</label>
                            <input type="text" name="quizname" class="form-control form-control-lg" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Quiz Category:</label>
                            <select name="quizCategory" class="form-control form-control-lg">
                                <option value="General Knowledge">General knowledge</option>
                                <option value="Science">Science</option>
                                <option value="History">History</option>
                                <option value="Movies & TV">Movies & TV</option>
                                <option value="Music">Music</option>
                                <option value="Sports">Sports</option>
                            </select>
                        </div>
                        

                        <button id="changeBtn" type="button" onclick="changeAvatar(this)" class="btn btn-info">Add a pic?</button>

                        <div class="form-group" id="editedProfile" style="display: none;">
                            <div id=fileErrorSize style="display: none;" class="alert alert-danger">
                                Please choose an image that is less than 1MB.
                            </div>
                            <input onchange="validateImg()" id="uploadFile" type="file" name="file">
                            <!-- <div>
                                <button class="btn btn-primary" type="submit" name="submitAvatar">Upload</button>
                                <button onclick="cancelAvatar()" type="button" class="btn btn-danger">Cancel</button>
                            </div> -->
                        </div>



                    </div>
                    <button style="display:none;" type="button" class="removeQuestion btn btn-danger btn-block btn-md">Remove Question</button>
                    <button type="button" id="addQuestion" class="btn btn-primary btn-block btn-md">Add a
                        question!</button>
                    <button type="submit" name="submitted" class="btn btn-primary btn-block btn-lg">Submit</button>
                </form>

            </div>
        </div>
        <div class="row">
            <div class="col-md-10 offset-md-1 form-div">

                <?php
                $sql = "SELECT id,name,category FROM quiz";
                $resultQuiz = mysqli_query($conn, $sql);

                if (mysqli_num_rows($resultQuiz) > 0) {
                    // output data of each row
                    while ($rowQuiz = mysqli_fetch_assoc($resultQuiz)) {
                        echo '<table class="table table-light table-hover table-bordered">
                        <thead class="thead-dark">
                        <tr>
                            <th>Quiz id: ' . $rowQuiz["id"] . '</th>
                            <th>' . $rowQuiz["name"] . '</th>
                            <th>Category: ' . $rowQuiz["category"] . '</th>
                        </tr>
                        </thead>'; //quiz
                        // echo "id: " . $rowQuiz["id"] . " - Name: " . $rowQuiz["name"] . "<br>";
                        $sql = "SELECT * FROM question WHERE quiz_id=" . $rowQuiz['id'];
                        $resultQuestion = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($resultQuestion) > 0)
                            while ($rowQuestion = mysqli_fetch_assoc($resultQuestion)) {
                                echo '
                                <thead class="bg-secondary text-light">
                                <tr>
                                    <th>Question ID: ' . $rowQuestion["id"] . '</th>
                                    <th colspan=2>' . $rowQuestion["question"] . '</th>
                                </tr>
                                </thead>';
                                //echo "id: " . $rowQuestion["id"] . " -- Qustion: " . $rowQuestion["question"] . "<br>";
                                $sql = "SELECT * FROM answer WHERE question_id=" . $rowQuestion['id'];
                                $resultAnswer = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($resultAnswer) > 0) {
                                    echo '
                                <thead class="bg-secondary text-light">
                                <tr>
                                    <th>Answer ID</th>
                                    <th>Answer</th>
                                    <th>Correctness(0:False/1:True)</th>
                                </tr>
                                </thead>';
                                    echo '<tbody>';
                                    while ($rowAnswer = mysqli_fetch_assoc($resultAnswer)) {
                                        echo '
                                        <tr ';
                                        echo ($rowAnswer["is_right"] == true) ? print('class="table-success"') : "";
                                        echo '>
                                        <td>' . $rowAnswer["id"] . '</td>
                                        <td>' . $rowAnswer["answer"] . '</td>
                                        <td>' . $rowAnswer["is_right"] . '</td>
                                        </tr>';
                                        //echo "id: " . $rowAnswer["id"] . " ---- Answer: " . $rowAnswer["answer"] . " " . $rowAnswer["is_right"] . "<br>";
                                    }
                                    echo '<tbody>';
                                }
                            }
                        echo "</table>";
                    }
                } else {
                    echo "0 results";
                }

                mysqli_close($conn);
                ?>
            </div>
        </div>
    </div>


</body>

</html>