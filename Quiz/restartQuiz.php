<?php

require_once 'quizController.php';


if (isset($_GET['requestQuiz'])) {
    $quizName = $_GET['requestQuiz'];
    $userID = $_SESSION['id'];
    $quizID = getQuizID($quizName);
    if (!startedQuiz($userID, $quizID)) {
        header('location: startQuiz.php?requestQuiz=' . $quizName);
    }
} else {
    die("Error: Could not load the page.");
}
?>
<?php

$quizID = getQuizID($quizName);
$sqlQuests = "SELECT * FROM question WHERE quiz_id=" . $quizID . "";

$resultQuests = mysqli_query($conn, $sqlQuests);
$nQuests = mysqli_num_rows($resultQuests);


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
    <link rel="stylesheet" href="hover.css">

    <title>Quizzit | <?php echo $quizName; ?></title>
    <link rel="icon" href="../imgs/logo/quizzit-icon.png">
    <script>

        var notShuffledQuestions = [];

        <?php
        $i = 0;
        while ($rowQuest = mysqli_fetch_assoc($resultQuests)) {
            echo 'notShuffledQuestions.push( {
        question: "' . htmlspecialchars($rowQuest['question'],ENT_QUOTES) . '",
        answers: []
      });';
            $sqlAns = "SELECT * FROM answer WHERE question_id=" . $rowQuest['id'] . "";
            $resultAns = mysqli_query($conn, $sqlAns);
            while ($rowAns = mysqli_fetch_assoc($resultAns)) {
                echo "notShuffledQuestions[" . $i . "].answers.push({
          text: '" . htmlspecialchars($rowAns['answer'],ENT_QUOTES) . "',
          correct:" . $rowAns['is_right'] . "
        });";
            }
            $i++;
        }
        ?>

        const questions = notShuffledQuestions.sort(() => Math.random() - .5);
        const nQuestions = questions.length;
        const time = 20;
        var questionIndex = 0;
        var nCorrect = 0;
        var nWrong = 0;
        var currentTime;
        var Timer;
        var currentCount;
        var Counter;
        var Answers = document.createElement("div");
        var CorrectSound;
        var WrongSound;

        function storeAnswer(answer) {
            Answers.innerHTML += "<hr><hr>" + answer.innerHTML;
        }

        function showAnswers() {
            (document.getElementById("answersShow")).append(Answers);
        }

        window.onload = function() {
            numberOfQuestions = document.getElementById("numberOfQuestions");
            numberOfQuestions.innerHTML = nQuestions;
            timePerQuestion = document.getElementById("timePerQuestion");
            timePerQuestion.innerHTML = time;

        }

        function sound(src) {
            this.sound = document.createElement("audio");
            this.sound.src = src;
            this.sound.setAttribute("preload", "auto");
            this.sound.setAttribute("controls", "none");
            this.sound.style.display = "none";
            document.body.appendChild(this.sound);
            this.play = function() {
                this.sound.play();
            }
            this.stop = function() {
                this.sound.pause();
            }
        }

        function startGame() {
            var startBox = document.getElementById("startBox");
            startBox.classList.add("hide");

            CountdownSound = new sound("sound/countdown.mp3");
            CorrectSound = new sound("sound/correct.mp3");
            WrongSound = new sound("sound/wrong.mp3");
            GameOverSound = new sound("sound/gameover.mp3");
            TimerSound = new sound("sound/timer.mp3")

            startCounter(3);
            // showNextQuestion();
        }

        function startCounter(num) {
            CountdownSound.play();
            currentCount = num;
            var counterBox = document.getElementById("counterBox");
            counterBox.classList.remove("hide");
            counterBox.children[0].innerHTML = currentCount;

            Counter = setInterval(handleCounter, 1000);
        }

        function handleCounter() {
            var countingNumber = document.getElementById("countingNumber");

            console.log(currentCount);
            currentCount--;

            if (currentCount != 0) {
                countingNumber.innerHTML = currentCount;
            } else {
                clearInterval(Counter);
                countingNumber.parentElement.classList.add("hide");
                showNextQuestion();
            }
        }

        function choose(e) {
            clearInterval(Timer);
            var choice = e.target;
            var answerBox = document.getElementById("answers");

            choice.classList.add("choice");
            //disable Buttons
            disableButtons(answerBox);

            // revealAnswers
            revealAnswers(answerBox);

            var correct = checkAnswer(choice);
            //play sound
            if (correct) {
                TimerSound.stop();
                CorrectSound.play();
            } else {
                TimerSound.stop();
                WrongSound.play();
            }

            var alertAnswer = correct ? `<div class="alert alert-success">Correct answer! 
                                            <button id="next" type="button" class="btn btn-sm btn-primary float-right">Next
                                            </button></div>` :
                `<div class="alert alert-danger">Wrong answer! 
                                            <button id="next" type="button" class="btn btn-sm btn-primary float-right">Next
                                            </button></div>`;

            var wrongBar = correct ? 'bg-success' : 'bg-danger';

            answerBox.innerHTML += `<div id="alertAns" style="display:none;" class="next-btn text-center">
                                        ` + alertAnswer + `
                                    </div>`;

            questionIndex++;
            var progressBar = document.getElementById("progressBar");
            progressBar.innerHTML = '<div class="progress-bar ' + wrongBar + ' progress-bar-striped progress-bar-animated" style="width:' +
                (questionIndex / nQuestions) * 100 + '%"></div>';

            $("#alertAns").slideDown();
            next = document.getElementById("next");

            storeAnswer(document.getElementById("qBox"));

            next.addEventListener("click", showNextQuestion);

        }

        function timerEnding() {
            var answerBox = document.getElementById("answers");
            //disable Buttons
            disableButtons(answerBox);

            // revealAnswers
            revealAnswers(answerBox);
            WrongSound.play();

            nWrong++;
            var alertAnswer = `<div class="alert alert-danger">Timer ended! 
                                <button id="next" type="button" class="btn btn-sm btn-primary float-right">Next
                                </button>
                              </div>`;

            answerBox.innerHTML += `<div id="alertAns" style="display:none;" class="next-btn text-center">
                                        ` + alertAnswer + `
                                    </div>`;

            questionIndex++;
            var progressBar = document.getElementById("progressBar");
            progressBar.innerHTML = '<div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" style="width:' +
                (questionIndex / nQuestions) * 100 + '%"></div>';

            $("#alertAns").slideDown();
            next = document.getElementById("next");

            storeAnswer(document.getElementById("qBox"));

            next.addEventListener("click", showNextQuestion);
        }

        function checkAnswer(choice) {
            if (questions[questionIndex].answers[choice.id].correct) {
                // alert("Answer is correct!");
                nCorrect++;
                return true;
            } else {
                // alert("answer is wrong!!")
                nWrong++;
                return false;
            }
        }

        function disableButtons(answerBox) {
            for (var i = 0; i < answerBox.childNodes.length; i++) {
                answerBox.childNodes[i].removeAttribute("onclick");
                answerBox.childNodes[i].classList.remove("hvr-pulse-shrink");
            }
        }

        function revealAnswers(answerBox) {
            for (var i = 0; i < answerBox.childNodes.length; i++) {
                if (questions[questionIndex].answers[i].correct) {
                    answerBox.childNodes[i].style.backgroundColor = "green";
                } else {
                    answerBox.childNodes[i].style.backgroundColor = "red";
                }
            }
        }

        function startTimer() {
            Timer = setInterval(handleTimer, 1000);
        }

        function handleTimer() {
            currentTime--;
            var timer = document.getElementById("timer");
            console.log(~~(time / 2));

            if (currentTime == 5) {
                timer.style.backgroundColor = "red";
                TimerSound = new sound("sound/timer.mp3");
                TimerSound.play();
            }
            timer.innerHTML = currentTime;

            if (currentTime == 0) {
                clearInterval(Timer);
                timerEnding();
            }
        }

        function clearPreviousState() {
            timer.style.backgroundColor = null;
            var progressBar = document.getElementById("progressBar");
            progressBar.innerHTML = '<div class="progress-bar progress-bar-striped progress-bar-animated" style="width:' +
                (questionIndex / nQuestions) * 100 + '%"></div>';
        }

        function showNextQuestion() {
            var quizBox = document.getElementById("qBox");
            if (questionIndex < nQuestions) {
                quizBox.classList.remove("hide");

                currentTime = time;
                var timer = document.getElementById("timer");
                timer.innerHTML = currentTime;
                clearPreviousState();

                var qn = document.getElementById("qn");
                qn.innerHTML = "Qusetion " + (questionIndex + 1) + ":";

                var question = document.getElementById("question");
                question.innerHTML = questions[questionIndex].question;

                var answerBox = document.getElementById("answers");
                answerBox.innerHTML = "";
                var nAnswers = questions[questionIndex].answers.length;
                for (var i = 0; i < nAnswers; i++) {
                    answerBox.innerHTML += '<a id="' + i + '" onclick="choose(event)" class="text-left hvr-pulse-shrink btn btn-block btn-primary ">' +
                        questions[questionIndex].answers[i].text + '</a>';
                }

                startTimer();
            } else {
                quizBox.classList.add('hide');

                endBox = document.getElementById('endBox');
                endBox.classList.remove('hide');

                endQuiz();
            }

        }

        function endQuiz() {
            GameOverSound.play();
            endTable = document.getElementById('endTable');
            endTable.innerHTML = `<tbody>
                            <tr>
                                <td>Number of Questions:</td>
                                <td>` + nQuestions + `</td>
                            </tr>
                            <tr>
                                <td>Correct answers:</td>
                                <td class="text-success">` + nCorrect + `</td>
                            </tr>
                            <tr>
                                <td>Wrong answers:</td>
                                <td class="text-danger">` + nWrong + `</td>
                            </tr>
                            <tr>
                                <td>Total points</td>
                                <td class="text-primary"> 0pts</td>
                            </tr>
                        </tbody>`;
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="row">
            <!--Starter-->
            <div id=startBox class="startquiz-box col-md-4 offset-md-4 shadow text-center">
                <div class="jumbotron">
                    <h3 id="quizName" class="quiz-title font-weight-bold"><?php echo $quizName; ?></h3>
                    <hr>
                    <p>This quiz contains <span id="numberOfQuestions">0</span> Questions!</p>
                    <p>You have <span id="timePerQuestion">0</span> seconds per question to answer each one.</p>
                    <p>You will not get any points from this Quiz because you have started it before..</p>
                    <hr>
                    <button id="startQuiz" name="startedQuiz" value="Game of thrones" class="btn btn-lg btn-info" onclick="startGame()">Restart Quiz!</button>
                    <a class="btn btn-lg btn-danger" href="../explore.php">Quit</a>
                </div>
            </div>
            <!--Counter page-->
            <div id=counterBox class="hide quiz-box col-md-4 offset-md-4 shadow text-center">
                <div id=countingNumber class="counterBox rounded-circle">3</div>
            </div>

            <!--Question-->
            <div id=qBox class="hide quiz-box col-md-4 offset-md-4 shadow text-center">
                <div class="question-stats">
                    <div id=timer class="float-right badge badge-info"></div>
                    <div id="pts" class="float-left text-info">Points gained: <span class="font-weight-bold">0</span>
                    </div>
                </div><br>
                <h4 id=qn class="question-title font-weight-bold"> </h4>
                <hr>
                <div id=question class="question font-weight-bold"> What's you name?</div>
                <div id=answers class="answer-box">
                    <!-- <a class="text-left hvr-pulse-shrink answer btn btn-block btn-primary">Faisal?</a>
                    <a class="text-left hvr-pulse-shrink btn btn-block btn-primary ">Lana?</a>
                    <a class="text-left hvr-pulse-shrink btn btn-block btn-primary">Kady?</a>
                    <a class="text-left hvr-pulse-shrink btn btn-block btn-primary">Obama?</a> -->
                </div>

                <div id="progressBar" class="progress">

                </div>
            </div>

            <!--Finish-->
            <div id="endBox" class="hide endquiz-box col-md-4 offset-md-4 shadow text-center">
                <div class="jumbotron">
                    <h3 class="quiz-title font-weight-bold" style="font-family: orbitron;">GAME OVER!</h3>
                    <hr>

                    <table id=endTable class="table">

                    </table>
                    <a class="btn btn-sm btn-success" href="../explore.php">Explore page</a>
                    <button onclick="showAnswers()" class="btn btn-sm btn-primary">Show my answers!</button>
                    <div id="answersShow"></div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>