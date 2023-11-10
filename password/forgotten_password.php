<?php

require_once(__DIR__ . '/../myDB/config/autoload.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="password.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sliding-background"></div>

    <div id="login-container">
        <form action="process_form_password.php" method="post" autocomplete="off">
            <div class="img-container">
                <img src="../img/login_img.png" alt="Avatar" class="avatar">
            </div>

            <div class="container">
                <label for="uname"><span class="bold">Username</span></label>
                <input type="text" placeholder="Enter Username" name="uname" required autofocus>

                <label for="email"><span class="bold">Email</span></label>
                <input type="text" placeholder="Enter Email" name="email" required>
                    
                <button type="submit">send</button>
            </div>
        </form>
    </div>
</body>
</html>