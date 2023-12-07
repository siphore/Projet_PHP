<?php

require_once('../myDB/config/autoload.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve token and password from the form;
    $token = $_POST['token'];
    $password = $_POST['password'];
    $rPassword = $_POST['rPassword'];

    try {
        $db = DBManager::getDB();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // looks for the same token in the database 
        $sql = "SELECT * FROM password_reset WHERE token = :token";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // checks if an user was found
        if ($user === false) {
            die("Token not found");
        }

        // checks if the token is still available 
        if (strtotime($user["expiry"]) <= time()) {
            die("Token has expired");
        }

        //retrieves the email from the database
        $email = DBManager::getEmailByToken($token);

        // Check if both passwords match
        if ($password === $rPassword) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        } else {
            die("Passwords don't match, please try again");
        }

        // updates password in form_data table
        if ($email != null) {
            DBManager::updatePassword($hashedPassword, $email);
            echo "Password updated successfully!<br>";
            echo "Revenir au <a href='../login/login.php'>login</a>";
        } else {
            die("Email not found for the given token");
        }
    } catch (PDOException $e) {
        die("Database connection error: " . $e->getMessage());
    }
}
