<?php

require_once(__DIR__ . '/../myDB/config/autoload.php');

// Start the session
session_start();

DBManager::getPodcastData();

// If no session active
if (!isset($_SESSION["username"])) {
    header("Location: ../login/login.php");
    exit();
}

// Logout handling
if (filter_has_var(INPUT_GET, 'logout')) {
    session_destroy();
    header("Location: ../login/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library</title>
    <link rel="stylesheet" href="library.css?v=<?php echo time(); ?>">
    <script src="script.js?v=<?php echo time(); ?>"></script>
</head>
<body onload="readCSV();">
    <div class="navbar">
        <input type="text" id="search-input" placeholder="Rechercher..." oninput="filterCards();">
    </div>
    <a href="?logout" class="cancel">Logout</a>

    <div id="cards-container"></div>

    <div class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePopup()">&#x2573;</span>
            <div class="iframe-container">
                <iframe width="100%" height="100%"></iframe>
            </div>
        </div>
    </div>
</body>
</html>