<?php 
require_once 'auth.php';

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">

    <!-- google -->
    <meta name="google-signin-client_id" content="513063692014-5p61l12fgfombki3goimnro7abi4svep.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer>

    </script>


    <title>Quizzit | Admin login</title>
    <link rel="icon" href="../imgs/logo/quizzit-icon.png">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form-div">
                <form action="loginA.php" method="post">
                    <!-- logo -->
                    <img class="logo" src="../imgs/logo/auth-logo.png">
                    <h3 class="text-center"> Admin Sign in</h3>

                    <?php if (count($errors) > 0) : ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['errorSession'])) : ?>
                        <div class="alert alert-danger">
                            <?php echo $_GET['errorSession']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['loggedOut'])) : ?>
                        <div class="alert alert-warning">
                            <?php echo $_GET['loggedOut']; ?>
                        </div>
                    <?php endif; ?>


                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" class="form-control form-control-lg">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="login-btn" class="btn btn-primary btn-block btn-lg">Sign in</button>
                    </div>
                   
                </form>

            </div>
        </div>
    </div>


</body>

</html>