<?php 
if($_SERVER["DOCUMENT_ROOT"]=='C:/xampp/htdocs'){//if local host
    define('ROOT', dirname(__DIR__));
}else{
    define('ROOT', $_SERVER["DOCUMENT_ROOT"]);
}

require ROOT.'/config/db.php';
session_start();
$errors=array();
if (isset($_POST['login-btn'])) {
    $username = $_POST['username']; //either email or username
    $password = $_POST['password'];

    //validation
    if (empty($username)) {
        $errors['username'] = "Username required";
    }
    if (empty($password)) {
        $errors['password'] = "Password required";
    }
    if (count($errors) === 0) {
        $sql = "SELECT * FROM admins WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username); //user might enter either email or username
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); //return a user full ROW with * coloumns as an associative array

        //check if the user has the same password as registered

        if ($user != null && password_verify($password, $user['password'])) { //check if the given pwd from user match the encrypted pwd stored in DB
            //login success
            //login user
            settingUserSession($user);
            //flash message
            $_SESSION['message'] = "You are now logged in";
            $_SESSION['alert-class'] = "alert-success";
              echo "you are now logged in";
            header('location: quizAdder.php');
            // echo $_SESSION['id'];
            // echo $_SESSION['username'];
            // exit(); // dont execute any other thing from here

        } else {
            $errors['login_fail'] = "Wrong username or password!";
        }
    }
}

//logout user
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['adminID']);
    unset($_SESSION['adminUsername']);

    header('location: loginA.php?loggedOut=We hope to see you again ❤');
    exit();
}
function settingUserSession($user)
{
    $_SESSION['adminID'] = $user['id'];
    $_SESSION['adminUsername'] = $user['username'];
  
}

