<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sliding-background"></div>

    <div id="login-container">
        <form action="process_form_register.php" method="post" autocomplete="off">
            <!-- Image -->
            <div class="img-container">
                <img src="../img/login_img.png" alt="Avatar" class="avatar">
            </div>

            <!-- Inputs -->
            <div class="container">
                <label for="email"><span class="bold">Email</span></label>
                <input type="text" placeholder="Enter Email" name="email" required autofocus>

                <label for="uname"><span class="bold">Username</span></label>
                <input type="text" placeholder="Enter Username" name="uname" required>

                <label for="psw"><span class="bold">Password</span></label>
                <input type="password" placeholder="Enter Password" name="psw" required>
                    
                <button type="submit">Register</button>
            </div>

            <!-- Footer -->
            <div class="container" style="background-color:#f1f1f1; text-align: end">
                <span class="text">Already registered ? <a href="login.php">Login</a></span>
            </div>
        </form>
    </div>
</body>
</html>