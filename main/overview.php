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
                // header("Location: overview.php");
                break;

            case 'editOff':
                // Handle the title field
                if (isset($_POST['title'], $_POST['artists'])) {
                    $newTitle = $_POST['title'];
                    $newArtists = $_POST['artists'];
                }

                // Handle the artists field
                $artistsId = [];
                foreach(explode(",", $artists) as $artist) {
                    array_push($artistsId, DBManager::getArtistIdFromNames($artist));
                }

                // Handle the audio upload
                if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
                    $allowedAudioFormats = ['mp3', 'wav', 'ogg']; // Add or modify allowed audio formats as needed

                    $uploadDir = '../audios/';
                    $uploadFile = $uploadDir . basename($_FILES['audio']['name']);
                    $fileExtension = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);

                    // Check if the file format is allowed
                    if (in_array(strtolower($fileExtension), $allowedAudioFormats)) {
                        if (move_uploaded_file($_FILES['audio']['tmp_name'], $uploadFile)) {
                            // Update the audio file path in the database
                            $audioFilePath = $uploadFile;
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
                            $imageFilePath = $uploadFile;
                        } else {
                            echo "Error moving uploaded file.";
                        }
                    } else {
                        echo "Invalid image file format. Allowed formats: " . implode(', ', $allowedImageFormats);
                    }
                }


                // Update DB
                DBManager::updatePodcast($_SESSION['id'], $newTitle, $artistsId, $newArtists, $audioFilePath, $imageFilePath);
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

                <!-- File input for image -->
                <div class="form-group">
                    <label for="image">Select Image:</label>
                    <input type="file" id="image" name="image">
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