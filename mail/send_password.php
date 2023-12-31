
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgotten password</title>
    <link rel="stylesheet" href="../login/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="sliding-background"></div>
    
    <div id="login-container">
        <form action="process_send_password.php" method="post" autocomplete="off">
            <div class="img-container">
                <img src="../img/login_img.png" alt="Avatar" class="avatar">
            </div>

            <div class="container">
                <label for="email"><span class="bold">Email</span></label>
                <input type="text" placeholder="Enter Email" name="email" required autofocus>
                <button type="submit">Send</button>
            </div>
        </form>
    </div>
</body>
</html>