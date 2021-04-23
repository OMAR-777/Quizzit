<?php require_once 'controllers/authController.php'; 
if(isset($_SESSION['id'])){
    header('location: index.php');
    exit(0);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <!-- Bootstrap CDN-->
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="styles.css">

    <!-- google -->
    <meta name="google-signin-client_id" content="977266602124-8tu57h2s78moebmdsj699qhgr4t1jtoo.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer>

    </script>

    <script>
        // function onSignIn(googleUser) {
        //     // var profile = googleUser.getBasicProfile();
        //     var id_token = googleUser.getAuthResponse().id_token;

        //     // console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
        //     // console.log('Name: ' + profile.getName());
        //     // console.log('id_token: ' + id_token);
        //     // console.log('Image URL: ' + profile.getImageUrl());
        //     // console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.

        //     var xhr = new XMLHttpRequest();
        //     xhr.open('POST', 'controllers/authController.php');
        //     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        //     xhr.onload = function() {
        //         console.log('Signed in as: ' + xhr.responseText);

        //         if(xhr.responseText=='success-loggedin'||xhr.responseText=='success-registered'){
        //             signOut();
        //             window.location.replace("index.php");
        //         }else{
        //             signOut();
        //             console.log(xhr.responseText);
        //             var googleAlert=document.getElementById("googleAlert");
        //             googleAlert.innerHTML=xhr.responseText;
        //             googleAlert.style.display=null;
        //         }
        //     };
            
        //     xhr.send('googleIdToken=' + id_token + '&googleClientID=977266602124-8tu57h2s78moebmdsj699qhgr4t1jtoo.apps.googleusercontent.com');//Careful here!!

        // }

        // function signOut() {
        //     var auth2 = gapi.auth2.getAuthInstance();
        //     auth2.signOut().then(function() {
        //         console.log('User signed out.');

        //     });
        // }
    </script>

    <title>Quizzit | Sign in</title>
    <link rel="icon" href="imgs/logo/quizzit-icon.png">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form-div">
                <form action="login.php" method="post">
                    <!-- logo -->
                    <img class="logo-auth" src="imgs/logo/auth-logo.png">
                    <h3 class="text-center">Sign in</h3>

                    <?php if (count($errors) > 0) : ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['errorSession'])) : ?>
                        <div class="alert alert-info">
                            <?php echo $_GET['errorSession']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['loggedOut'])) : ?>
                        <div class="alert alert-warning">
                            <?php echo $_GET['loggedOut']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['passwordReset'])) : ?>
                        <div class="alert alert-success">
                            <?php echo $_GET['passwordReset']; ?>
                        </div>
                    <?php endif; ?>

                    <div style="display: none;" id="googleAlert"class="alert alert-danger"></div>

                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" name="username" value="<?php echo $username; ?>" class="form-control form-control-lg">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="login-btn" class="btn btn-primary btn-block btn-lg">Sign in</button>
                    </div>
                    <hr>
                    <p class="text-center">Not yet a member? <a href="signup.php">Sign up</a></p>
                    <p class="text-center"><a href="forgot_password.php">Forget your password?</a></p>



                </form>

            </div>
        </div>
    </div>


</body>

</html>