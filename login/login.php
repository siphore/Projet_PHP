<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sliding-background"></div>

    <div id="login-container">
        <form action="process_form_login.php" method="post" autocomplete="off">
            <!-- Image -->
            <div class="img-container">
                <img src="../img/login_img.png" alt="Avatar" class="avatar">
            </div>

            <!-- Inputs -->
            <div class="container">
                <label for="uname"><span class="bold">Username</span></label>
                <input type="text" placeholder="Enter Username" name="uname" required autofocus>

                <label for="psw"><span class="bold">Password</span></label>
                <input type="password" placeholder="Enter Password" name="psw" required>
                    
                <button type="submit">Login</button>
            </div>

            <!-- Footer -->
            <div class="container" style="background-color:#f1f1f1">
                <li>
                    <ul>
                        <span class="psw text">Forgot <a href="../mail/send_password.php">password?</a></span>
                    </ul>
                    <ul>
                        <span class="text">Don't have an account ? Register <a href="register.php">here</a></span>
                    </ul>
                </li>
            </div>
        </form>
    </div>
</body>
</html>