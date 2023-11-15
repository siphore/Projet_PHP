<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password</title>
    <link rel="stylesheet" href="../login/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="sliding-background"></div>

    <div id="login-container">
        <form action="process_reset_password.php" method="post" autocomplete="off">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="img-container">
                <img src="../img/login_img.png" alt="Avatar" class="avatar">
            </div>

            <div class="container">
                <label for="password"><span class="bold">New password</span></label>
                <input type="password" placeholder="Enter new password" name="password" required autofocus>

                <label for="rPassword"><span class="bold">Repeat password</span></label>
                <input type="password" placeholder="Confirm password" name="rPassword" required>
                    
                <button type="submit">send</button>
            </div>
        </form>
    </div>
</body>
</html>