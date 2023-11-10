<?php

require_once(__DIR__ . '/../myDB/config/autoload.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $username = $_POST['uname'];
    $password = $_POST['psw'];
    $email = $_POST['email'];

    // Create the form_data table if it doesn't exist
    DBManager::createFormData();

    // Check if the username already exists in the database
    if (DBManager::usernameExists($username)) {
        echo "Username already in use. Please choose a different username.";
    } else if (DBManager::emailExists($email)) {
        echo "Email already in use. Please choose a different email.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert the username and hashed password into the database
        if (DBManager::updateFormData($username, $hashedPassword, $email)) {
            // Registration successful, redirect to the login page
            header("Location: login.php");
        } else {
            // Registration failed
            echo "Registration failed. Please try again later.";
        }
    }
}

?>