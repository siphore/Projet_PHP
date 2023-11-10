<?php

    if (isset($_GET['data'])) {
        $dataFromParent = $_GET['data'];
        $id = htmlspecialchars($dataFromParent);
    }

    $jsonFile = json_decode(file_get_contents("data.json"))[$id-1];
    $title = $jsonFile->title;
    $artists = $jsonFile->artists;
    $imgSrc = $jsonFile->image_url;

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
    <div class="container">
        <img src="<?php echo $imgSrc ?>">
        <h1><?php echo $title ?></h1>
        <p><?php  echo str_replace(",", ", ", $artists) ?></p>
    </div>
</body>
</html>