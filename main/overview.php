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
        if ($id !== "-1") $edit = $dataFromParent[1];

        // Store $id in session
        $_SESSION['id'] = $id;
    }

    if ($id !== "-1") {
        $jsonFile = json_decode(file_get_contents("data.json"))[$id-1];
        $title = $jsonFile->title;
        $artists = $jsonFile->artists;
        $imgSrc = $jsonFile->image_url;
        $audioSrc = $jsonFile->audio_file;
    }

    // Edit mode handling
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        // Perform the action based on the request
        switch ($_POST['action']) {
            case 'editOn':
                $_SESSION['edit'] = true;
                break;

            case 'editOff':
            case 'new':
                // Handle the title field
                if (isset($_POST['title'], $_POST['artists'])) {
                    $newTitle = $_POST['title'];
                    $newArtists = $_POST['artists'];
                }

                // Handle the artists field
                if ($id !== "-1") {
                    $artistsId = [];
                    foreach(explode(",", $artists) as $artist) {
                        $artistId = DBManager::getArtistIdFromNames($artist);
                        array_push($artistsId, $artistId);
                    }
                }

                // Handle the file upload for audios
                if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
                    $allowedAudioFormats = ['mp3', 'wav', 'ogg']; // Add or modify allowed audio formats as needed

                    $uploadDir = '../audios/';
                    $uploadFile = $uploadDir . basename($_FILES['audio']['name']);
                    $fileExtension = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);

                    // Check if the file format is allowed
                    if (in_array(strtolower($fileExtension), $allowedAudioFormats)) {
                        if (move_uploaded_file($_FILES['audio']['tmp_name'], $uploadFile)) {
                            // Update the audio file path in the database
                            $audioSrc = $uploadFile;
                        } else {
                            echo "Error moving uploaded file.";
                        }
                    } else {
                        echo "Invalid audio file format. Allowed formats: " . implode(', ', $allowedAudioFormats);
                    }
                }

                // Handle the file upload for images
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $allowedImageFormats = ['jpg', 'jpeg', 'png', 'gif']; // Add or modify allowed image formats as needed

                    $uploadDir = '../img/';
                    $uploadFile = $uploadDir . basename($_FILES['image']['name']);
                    $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                    // Check if the file format is allowed
                    if (in_array(strtolower($fileExtension), $allowedImageFormats)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                            // Update the image file path in the database
                            $imgSrc = $uploadFile;
                        } else {
                            echo "Error moving uploaded file.";
                        }
                    } else {
                        echo "Invalid image file format. Allowed formats: " . implode(', ', $allowedImageFormats);
                    }
                }

                // Update DB
                if ($id !== "-1") {
                    // Update existing
                    DBManager::updatePodcast($_SESSION['id'], $newTitle, $artistsId, $newArtists, $audioSrc, $imgSrc);
                } else {
                    // Create new
                    DBManager::createPodcast($newTitle, $newArtists, $audioSrc, $imgSrc);
                }
                
                $_SESSION['edit'] = false;

                // Set a session variable to indicate that the update is complete
                $_SESSION['update_complete'] = true;
                break;

            case 'delete':
                // Handle the delete action
                if (isset($_SESSION['id'])) {
                    // Delete the podcast and check if deletion was successful
                    $deleted = DBManager::deletePodcast($_SESSION['id']);

                    if ($deleted) {
                        $_SESSION['update_complete'] = true;
                    } else {
                        // Error occurred during deletion, handle appropriately
                        echo "Error deleting podcast.";
                    }
                }
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
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script>
        // Check if the update is complete
        if (<?php echo (isset($_SESSION['update_complete']) && $_SESSION['update_complete']) ? 'true' : 'false'; ?>) {
            // Close the popup in the parent window
            closePopupInParent();
            <?php $_SESSION['update_complete'] = false; ?>
        }

        function showWarning(event) {
            // Prevent the default form submission
            event.preventDefault();

            // Show a confirmation dialog
            const userConfirmed = confirm("Are you sure you want to delete this podcast?");

            // If the user confirms, proceed with the form submission
            if (userConfirmed) document.getElementById('deleteForm').submit();
        }

    </script>
</head>
<body>
    <?php if ($id !== "-1") { ?>
        <?php if ($edit === true) { ?>
            <!-- Edit -->
            <div class="container">
                <h1>Edit</h1>
                <form action="overview.php" method="post" enctype="multipart/form-data">
                    <!-- Title field -->
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" autocomplete="off" autofocus required />
                    </div>

                    <!-- Artists field (assuming it's a comma-separated list) -->
                    <div class="form-group">
                        <label for="artists">Artists:</label>
                        <input type="text" name="artists" value="<?php echo htmlspecialchars($artists); ?>" autocomplete="off">
                    </div>

                    <!-- File input for image -->
                    <div class="form-group">
                        <label>Select Image:</label>
                        <input type="file" name="image">
                    </div>

                    <!-- File input for audio -->
                    <div class="form-group">
                        <label>Select Audio:</label>
                        <input type="file" name="audio">
                    </div>

                    <!-- Submit -->
                    <div class="form-group">
                        <button type="submit" name="action" value="editOff">Done</button>
                    </div>
                </form>
            </div>
        <?php } else { ?>
            <!-- Infos -->
            <div class="container">
                <img src="<?php echo $imgSrc ?>">
                <h1><?php echo $title ?></h1>
                <p><?php  echo str_replace(",", ", ", $artists) ?></p>
                <form id="deleteForm" action="overview.php" method="post">
                    <input name="action" value="delete" type="hidden">
                    <button type="submit" name="action" value="editOn">Edit</button>
                    <button class="delete" name="action" value="delete" onclick="showWarning(event);">Delete</button>
                </form>
            </div>
        <?php } ?>
    <?php } else { ?>
        <!-- New -->
        <div class="container">
            <h1>New podcast</h1>
            <form action="overview.php" method="post" enctype="multipart/form-data">
                <!-- Title field -->
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" name="title" autocomplete="off" required>
                </div>

                <!-- Artists field (assuming it's a comma-separated list) -->
                <div class="form-group">
                    <label for="artists">Artists:</label>
                    <input type="text" name="artists" autocomplete="off" required>
                </div>

                <!-- File input for image -->
                <div class="form-group">
                    <label>Select Image:</label>
                    <input type="file" name="image">
                </div>

                <!-- File input for audio -->
                <div class="form-group">
                    <label>Select Audio:</label>
                    <input type="file" name="audio">
                </div>

                <!-- Submit -->
                <div class="form-group">
                    <button type="submit" name="action" value="new">Done</button>
                </div>
            </form>
        </div>
    <?php } ?>
</body>
</html>