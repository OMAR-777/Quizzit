<?php require_once 'controllers/authController.php';
if (isset($_SESSION['id'])) {
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


    <title>Quizzit | Forgot Password</title>
    <link rel="icon" href="imgs/logo/quizzit-icon.png">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form-div">
                <form action="forgot_password.php" method="post">
                    <!-- logo -->
                    <img class="logo-auth" src="imgs/logo/auth-logo.png">
                    <h3 class="text-center">Recover your password</h3>

                    <?php if (count($errors) > 0) : ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <p>
                            Please enter your email address you used to sign up on this site
                            and we will send you an email to recover your password.</p>
                        <input type="email" name="email" class="form-control form-control-lg">
                    </div>

                    <div class="form-group">
                        <button type="submit" name="forgot-password" class="btn btn-primary btn-block btn-lg">Recover passsword</button>
                    </div>
                </form>

            </div>
        </div>
    </div>


</body>

</html>