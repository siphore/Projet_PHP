<?php

require_once('../myDB/config/autoload.php');

$token = $_GET["token"];

try {
    $db = DBManager::getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM password_reset WHERE token = :token";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        die("Token not found");
    }

    if (strtotime($user["expiry"]) <= time()) {
        die("Token has expired");
    }

} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>

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
                    
                <button type="submit">Send</button>
            </div>
        </form>
    </div>
</body>
</html> 
