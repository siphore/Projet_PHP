<?php

    require_once(__DIR__ . '/../myDB/config/autoload.php');

    session_start();

    // Retrieve $id from session
    $id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

    // Retrieve the edit state from the session
    $edit = isset($_SESSION['edit']) ? $_SESSION['edit'] : false;

    if (isset($_GET['data'])) {
        $dataFromParent = explode(" ", $_GET['data']);
        $id = htmlspecialchars($dataFromParent[0]);
        $edit = $dataFromParent[1];

        // Store $id in session
        $_SESSION['id'] = $id;
    }

    $jsonFile = json_decode(file_get_contents("data.json"))[$id-1];
    $title = $jsonFile->title;
    $artists = $jsonFile->artists;
    $imgSrc = $jsonFile->image_url;

    // Edit mode handling
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        // Perform the action based on the request
        switch ($_POST['action']) {
            case 'editOn':
                $_SESSION['edit'] = true;
                break;

            case 'editOff':
                // Handle the form fields
                if (isset($_POST['title'], $_POST['artists'])) {
                    $title = $_POST['title'];
                    $newArtists = $_POST['artists'];
                }

                DBManager::updatePodcast($_SESSION['id'], $title, $artists, $newArtists);
                DBManager::getPodcastData();
                
                $_SESSION['edit'] = false;
                break;
        }

        header("Location: overview.php");
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcast overview</title>
    <link rel="stylesheet" href="overview.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php if ($edit === true) { ?>
        <div class="container">
            <h1>Edit</h1>
            <form action="overview.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="editOff">

                <!-- Title field -->
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>

                <!-- Artists field (assuming it's a comma-separated list) -->
                <div class="form-group">
                    <label for="artists">Artists:</label>
                    <input type="text" id="artists" name="artists" value="<?php echo htmlspecialchars($artists); ?>" required>
                </div>

                <!-- File input for audio -->
                <div class="form-group">
                    <label for="audio">Select Audio:</label>
                    <input type="file" id="audio" name="audio">
                </div>

                <div class="form-group">
                    <button type="submit">Done</button>
                </div>
            </form>
        </div>
    <?php } else { ?>
        <div class="container">
            <img src="<?php echo $imgSrc ?>">
            <h1><?php echo $title ?></h1>
            <p><?php  echo str_replace(",", ", ", $artists) ?></p>
            <form action="overview.php" method="post">
                <input type="hidden" name="action" value="editOn">
                <button type="submit">Edit</button>
            </form>
        </div>
    <?php } ?>
</body>
</html>