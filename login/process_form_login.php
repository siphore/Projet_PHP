<?php

require_once(__DIR__ . '/../myDB/config/autoload.php');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieves the username and password from the form
    $username = $_POST['uname'];
    $password = $_POST['psw'];

    //retrieves password from database
    $hashedPassword = DBManager::getHashedPassword($username);

    //checks if the password is correct
    if ($hashedPassword && password_verify($password, $hashedPassword)) {
        session_start();
        $_SESSION['username'] = $username;

        header('Location: ../main/library.php'); // Redirect to the dashboard page
        exit();
    } else {
        // Password does not match, display an error message
        $msg = "Invalid username or password. Please try again.";
        echo "<script>";
        echo "async function showError(msg) {alert(msg); return true;}";
        echo "(async function getError() {const ok = await showError('".$msg."'); if (ok) location.href = 'login.php';}());";
        echo "</script>";
    }
}

?>