<?php
session_start();

if($_SERVER["DOCUMENT_ROOT"]=='C:/xampp/htdocs'){//if local host
    define('ROOT', dirname(__DIR__));
}else{
    define('ROOT', $_SERVER["DOCUMENT_ROOT"]);
}

require ROOT.'/config/db.php';




$errors = array(); //will be available on signup
$username = "";
$email = "";

//if user click on the sign up button
if (isset($_POST['signup-btn'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordConf = $_POST['passwordConf'];

    //validation
    if (empty($username)) {
        $errors['username'] = "Username required";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //IF TRUE INVALID
        $errors['email'] = "Email address is invalid";
    }
    if (empty($email)) {
        $errors['email'] = "Email required";
    }
    if (empty($password)) {
        $errors['password'] = "Password required";
    }
    if ($password !== $passwordConf) {
        $errors['password'] = "The two passwords do not match";
    }

    $emailQuery = "SELECT * FROM users WHERE email=? LIMIT 1"; // (?) using prepared statements, LIMIT1 if u see one record then stop searching
    $stmt = $conn->prepare($emailQuery);
    $stmt->bind_param('s', $email); //s- string , add email instead of ?
    $stmt->execute();
    $result = $stmt->get_result();
    $userCount = $result->num_rows;
    $stmt->close();

    if ($userCount > 0) {
        $errors['email'] = "Email already exists";
    }

    $userQuery = "SELECT * FROM users WHERE username=? LIMIT 1"; // (?) using prepared statements, LIMIT1 if u see one record then stop searching
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param('s', $username); //s- string , add email instead of ?
    $stmt->execute();
    $result = $stmt->get_result();
    $userCount = $result->num_rows;
    $stmt->close();

    if ($userCount > 0) {
        $errors['username'] = "Username already exists";
    }

    if (count($errors) === 0) {
        $password = password_hash($password, PASSWORD_DEFAULT); //ENCRYPT
        $token = bin2hex(random_bytes(50)); // unique random string of length 100
        $verified = false;

        $sql = "INSERT INTO users (username, email, verified, token, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssbss', $username, $email, $verified, $token, $password); //string string boolean.... 
        if ($stmt->execute()) {
            //login user
            $user_id = $conn->insert_id; // get the last inserted id from conn object
            $_SESSION['id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['verified'] = $verified;
            $_SESSION['avatar'] = 'default.png';



            //flash message
            $_SESSION['message'] = "You are now logged in";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit(); // not execute any other thing from here

        } else {
            $errors['db_error'] = "Database error: failed to register";
        }
    }
}

//if user clicks on the login button
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
        $sql = "SELECT * FROM users WHERE (email=? OR username=?) AND EXTERNAL_TYPE IS NULL LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $username, $username); //user might enter either email or username
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
            header('location: index.php');
            exit(); // dont execute any other thing from here

        } else {
            $errors['login_fail'] = "Wrong username or password!";
        }
    }
}

//logout user
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['verified']);
    unset($_SESSION['avatar']);

    header('location: login.php?loggedOut=We hope to see you again ❤');
    exit();
}
function settingUserSession($user)
{
    $_SESSION['id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['verified'] = $user['verified'];
    $_SESSION['avatar'] = $user['avatar'];
}

//verify user by token
function verifyUser($token)
{ // each user has a unique token
    global $conn;
    $sql = "SELECT * FROM users WHERE token='$token' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result); // get row as assoc array
        $update_query = "UPDATE users SET verified=1 WHERE token='$token'";

        if (mysqli_query($conn, $update_query)) { //if executed proberly user is updated/activated
            //log user in
            settingUserSession($user);
            $_SESSION['verified']=1;
            //flash message
            $_SESSION['message'] = "Your email address is successfully verified!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit(); // not execute any other thing from here


        }
    } else { // if token not found from any user
        echo 'User not found';
    }
}
//if user clicks on the forgot password button 
// if (isset($_POST['forgot-password'])) {
//     $email = $_POST['email'];

//     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //IF TRUE INVALID
//         $errors['email'] = "Email address is invalid";
//     }
//     if (empty($email)) {
//         $errors['email'] = "Email required";
//     }
//     if (count($errors) == 0) {
//         $sql = "SELECT * FROM users WHERE email=? AND (EXTERNAL_TYPE IS NULL) LIMIT 1";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param('s', $email); //string string boolean.... 
//         $stmt->execute();
//         $result = $stmt->get_result();
//         if ($result) {
//             $num = $result->num_rows;
//             echo $num;
//             if ($num != 0) {
//                 $user = $result->fetch_assoc();
//                 $stmt->close();
//                 $token = $user['token'];
//                 sendPasswordResetLink($email, $token);
//                 header('location: password_message.php');
//                 exit(0); // "its good to exit after redirecting so the rest of php here doesn't continue executing" Awa melvine.
//             } else {
//                 $errors['email']="Email is not signed up to this website!";
//             }
//         } else {
//             $errors['db_error'] = "Database error: failed to execute";
//         }
//         $stmt->close();
//     }
// }
function resetPassword($token)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE token='$token' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    $_SESSION['email'] = $user['email'];
    header('location: reset_password.php');
    exit(0);
}
//if user clicked reset-password-btn
if(isset($_POST['reset-password-btn'])){
    $password=$_POST['password'];
    $passwordConf=$_POST['passwordConf'];
    if (empty($password) || empty($passwordConf)) {
        $errors['password'] = "Password required";
    }
    if ($password !== $passwordConf) {
        $errors['password'] = "The two passwords do not match";
    }

    $password=password_hash($password, PASSWORD_DEFAULT);
    $email= $_SESSION['email'];


    if(count($errors)==0){
        $sql="UPDATE users SET password='$password' WHERE email='$email'";
        $result= mysqli_query($conn, $sql);
        if($result){
            header('location: login.php?passwordReset=Your password is reset successfully!');
            exit(0);
        }else{
            $errors['password-reset']="Database error: failed to execute";
        }
    }
    unset($_SESSION['email']);

}



//if google sign in
// if (isset($_POST['googleIdToken']) && isset($_POST['googleClientID'])) {
//     require_once ROOT.'/files-needed-google/vendor/autoload.php';

//     function googleUserExists($userid)
//     {
//         global $conn;
//         $sql = "SELECT * FROM users WHERE EXTERNAL_TYPE='google' AND EXTERNAL_ID='$userid' LIMIT 1";
//         $result = mysqli_query($conn, $sql);
//         $userCount = mysqli_num_rows($result);

//         if ($userCount > 0) {
//             $user = mysqli_fetch_assoc($result);
//             return array(true, $user);
//         } else {
//             return array(false, null);
//         }
//     }
//     function emailAlreadyExists($useremail)
//     {
//         global $conn;
//         $emailQuery = "SELECT * FROM users WHERE email=? LIMIT 1"; // (?) using prepared statements, LIMIT1 if u see one record then stop searching
//         $stmt = $conn->prepare($emailQuery);
//         $stmt->bind_param('s', $useremail); //s- string , add email instead of ?
//         $stmt->execute();
//         $result = $stmt->get_result();
//         $userCount = $result->num_rows;
//         $stmt->close();

//         if ($userCount > 0) {
//             return true;
//         } else {
//             return false;
//         }
//     }
//     function usernameAlreadyExists($username)
//     {
//         global $conn;
//         $usernameQuery = "SELECT * FROM users WHERE username=? LIMIT 1"; // (?) using prepared statements, LIMIT1 if u see one record then stop searching
//         $stmt = $conn->prepare($usernameQuery);
//         $stmt->bind_param('s', $username); //s- string , add email instead of ?
//         $stmt->execute();
//         $result = $stmt->get_result();
//         $userCount = $result->num_rows;
//         $stmt->close();

//         if ($userCount > 0) {
//             return true;
//         } else {
//             return false;
//         }
//     }
//     function registerGoogleUser($useremail, $userid, $username)
//     {
//         global $conn;

//         $sql = "INSERT INTO users (username, email, verified, token, password, EXTERNAL_TYPE, EXTERNAL_ID) VALUES (?, ?, ?, ?, ?, ?, ?)";
//         $stmt = $conn->prepare($sql);
//         $verified = 1;
//         $token = 'none';
//         $password = 'none';
//         $eType = 'google';

//         if (usernameAlreadyExists($username)) {
//             $username = $useremail;
//         }

//         $stmt->bind_param('ssissss', $username, $useremail, $verified, $token, $password, $eType, $userid);
//         if ($stmt->execute()) {
//             $stmt->close();
//             //login user
//             $user_id = $conn->insert_id; // get the last inserted id from conn object
//             $_SESSION['id'] = $user_id;
//             $_SESSION['username'] = $username;
//             $_SESSION['email'] = $useremail;
//             $_SESSION['verified'] = 1;
//             $_SESSION['avatar'] = 'default.png';


//             //flash message
//             $_SESSION['message'] = "You are now logged in";
//             $_SESSION['alert-class'] = "alert-success";

//             return true;
//         } else {
//             $stmt->close();
//             return false;
//         }
//     }


//     // Get $id_token via HTTPS POST.
//     $id_token = $_POST['googleIdToken'];
//     $CLIENT_ID = $_POST['googleClientID'];
//     $client = new Google_Client(['client_id' => $CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend

//     $payload = $client->verifyIdToken($id_token);
//     if ($payload) { //Valid ID token
//         $userid = $payload['sub'];
//         $useremail = $payload['email'];
//         $username = $payload['name'];
//         // If request specified a G Suite domain:
//         //$domain = $payload['hd'];
//         $googleUser = googleUserExists($userid);
//         if ($googleUser[0]) { //user exists, log him in
//             settingUserSession($googleUser[1]);
//             $_SESSION['message'] = "You are now logged in";
//             $_SESSION['alert-class'] = "alert-success";
//             echo "success-loggedin";
//             exit();
//         } else { //doesn't exist, register him
//             if (emailAlreadyExists($useremail)) {
//                 echo "googleError: there is already a user signed up with the same email";
//                 exit();
//             } else {
//                 if (registerGoogleUser($useremail, $userid, $username)) {
//                     echo "success-registered";
//                     exit();
//                 } else {
//                     echo "Database googleError: failed to register";
//                     exit();
//                 }
//             }
//         }
//     } else {
//         echo "googleError: could not login with google (invalid id_token)";
//         exit();
//         // Invalid ID token
//     }
// }
