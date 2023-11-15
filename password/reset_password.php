<?php

//  User Clicks on the Link
if (isset($_GET['token'])) {
    $token = $_GET['token'];

} 

$tokenAvailable = verifyToken($token, $db); 

function verifyToken($token, $pdo) {

    // Prepare a SELECT query to retrieve the token information
    $query = $pdo->prepare("SELECT * FROM password_reset WHERE token = ? AND expiry > CURRENT_TIMESTAMP");
    $query->execute([$token]);

    // Fetch the result
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Token is valid
        return true;
    } else {
        // Token is invalid or expired
        return false;
    }
}

$sql = "SELECT * FROM user
        WHERE reset_token_hash = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user === null) {
    die("token not found");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("token has expired");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>

    <h1>Reset Password</h1>

    <form method="post" action="process-reset-password.php">

        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for="password">New password</label>
        <input type="password" id="password" name="password">

        <label for="password_confirmation">Repeat password</label>
        <input type="password" id="password_confirmation" name="password_confirmation">

        <button>Send</button>
    </form>

</body>
</html>