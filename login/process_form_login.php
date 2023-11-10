<?php

require_once(__DIR__ . '/../myDB/config/autoload.php');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the username and password from the form
    $username = $_POST['uname'];
    $password = $_POST['psw'];

    $hashedPassword = DBManager::getHashedPassword($username);

    if ($hashedPassword && password_verify($password, $hashedPassword)) {
        session_start();
        $_SESSION['username'] = $username;

        header('Location: ../main/library.php'); // Redirect to the dashboard page
        exit();
    } else {
        // Password does not match, display an error message
        echo "Invalid username or password. Please try again.";
    }
}

?>