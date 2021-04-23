<?php
require_once 'controllers/authController.php';
//verify user using token

//check if user logged in
if (!isset($_SESSION['id'])) {
    header('location: login.php?errorSession=Please sign in again!');
    exit(); //stop execution
}
function checkAddedFav($quizID, $userID)
{
    global $conn;
    $sqlFav = "SELECT * FROM quiz_liked WHERE quiz_id='$quizID' AND user_id='$userID'";
    $resultFav = mysqli_query($conn, $sqlFav);
    if ($resultFav) {
        $nResult = mysqli_num_rows($resultFav);
        if ($nResult > 0) {
            return true;
        } else {
            return false;
        }
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

    $title = $addedFav ? 'Remove from favorite.' : 'Add to favorite!';
    $nPlayed = mysqli_num_rows($result);
    if ($nPlayed == 0) {
        return '</p><br><a href="Quiz/startQuiz.php?requestQuiz=' . $quizName . '" type="submit" class="btn btn-sm btn-success">Start Quiz
        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-play-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.596 8.697l-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z"/>
          </svg>
    </a><button type="submit" data-qname="' . $quizName . '" data-added="' . $starBlack . '" onclick="addToFavorite(event)" class="btn btn-sm btn-light" title="' . $title . '">
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
function printQuizzes($category = 'All')
{
    global $conn;

    if ($category == 'All')
        $sqlCategory = "SELECT * FROM quiz";
    else
        $sqlCategory = "SELECT * FROM quiz WHERE category='$category'";

    $resultCategory = mysqli_query($conn, $sqlCategory);
    if (mysqli_num_rows($resultCategory) > 0) {
        while ($rowQuiz = mysqli_fetch_assoc($resultCategory)) {
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
    <title>Quizzit | Explore</title>
    <link rel="icon" href="imgs/logo/quizzit-icon.png">

    <script>
        //for quiz-title text overflow
        $(document).ready(function() {
            $(".ellipsis").each(function() {
                $(this).html("<span style>" + $(this).text() + "</span>");
            });
            $(".ellipsis").hover(function() {
                // console.log("#");
                var speed = parseInt($(this).attr("speed"));
                var length = $(this).find("span").width() - $(this).width();
                var time = length / speed;
                $(this).find("span").css("transition", "left " + time + "s linear").css("left", "-" + length + "px");
            }, function() {
                $(this).find("span").attr("style", "");
            });
            $(".ellipsis").on("click", function() {
                ;
                if ($(this).find("span").attr("style") == "") {
                    var speed = parseInt($(this).attr("speed"));
                    var length = $(this).find("span").width() - $(this).width();
                    var time = length / speed;
                    $(this).find("span").css("transition", "left " + time + "s linear").css("left", "-" + length + "px");
                    var $this = $(this);
                } else {
                    $(this).find("span").attr("style", "");
                }
            });
        });
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
    </script>

</head>

<body>


    <div class="container-fluid">
        <div id="footer-nav">
        <?php require_once 'footer-nav.php'; printNavbar('exploretab'); ?>
        </div>
        

        <div class="row">

            <div class="col-md-12 explore-div" style="border-radius: 0px;">
                <h1 class="text-center">Categories: </h1>
                <hr>

                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards0" href=#cards0>
                    <h3>All ✨🧠</h3>
                </a>
                <div id="cards0" class="collapse show">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printQuizzes();
                        ?>

                    </div>
                </div>
                <hr>
                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards1" href=#cards1>
                    <h3>General knowledge 📚</h3>
                </a>
                <div id="cards1" class="collapse">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printQuizzes('General knowledge');
                        ?>

                    </div>
                </div>
                <hr>
                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards2" href=#cards1>
                    <h3>Science 🪐</h3>
                </a>
                <div id="cards2" class="collapse">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printQuizzes('Science');
                        ?>
                    </div>
                </div>
                <hr>
                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards3" href=#cards1>
                    <h3>History 📜</h3>
                </a>
                <div id="cards3" class="collapse">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printQuizzes('History');
                        ?>
                    </div>
                </div>
                <hr>
                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards4" href=#cards1>
                    <h3>Movies & TV 🎬</h3>
                </a>
                <div id="cards4" class="collapse">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printQuizzes('Movies & TV');
                        ?>
                    </div>
                </div>
                <hr>
                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards5" href=#cards1>
                    <h3>Music 🎧</h3>
                </a>
                <div id="cards5" class="collapse">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printQuizzes('Music');
                        ?>
                    </div>
                </div>
                <hr>
                <a class="text-success font-weight-bold text-center" data-toggle="collapse" data-target="#cards6" href=#cards1>
                    <h3>Sports 🚴‍♂️</h3>
                </a>
                <div id="cards6" class="collapse">
                    <div class="cards-explore scrolling-wrapper">
                        <?php
                        printQuizzes('Sports');
                        ?>
                    </div>
                </div>
                <hr>



            </div>
        </div>
    </div>


</body>

</html>