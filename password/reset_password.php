<?php

require_once('../myDB/config/autoload.php');

//  User Clicks on the Link
if (isset($_GET['token'])) {
    $token = $_GET['token'];

} 
$db = DBManager::getDB(); 

$tokenAvailable = verifyToken($token, $db); 

function verifyToken($token, $pdo) {

    // Prepare a SELECT query to retrieve the token information
    $query = $pdo->prepare("SELECT * FROM password_reset WHERE token = ? AND expiry > CURRENT_TIMESTAMP");
    $query->execute([$token]);

    // Fetch the result
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        die("token error");
    } 
}

?>

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
                <input type="password" placeholder="Enter password" name="password" required autofocus>

                <label for="Rpassword"><span class="bold">Repeat password</span></label>
                <input type="Rpassword" placeholder="Enter password" name="rPassword" required aurofocus>
                    
                <button type="submit">send</button>
            </div>
        </form>
    </div>
</body>
</html>