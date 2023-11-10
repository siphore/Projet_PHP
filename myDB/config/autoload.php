<?php

spl_autoload_register(function ($class) {
    // Define a mapping of class names to file paths
    $classMap = [
        'DBManager' => 'myDB/ch/comem/DBManager.php'
    ];

    if (isset($classMap[$class])) {
        // Define the base directory for your project
        $baseDirectory = __DIR__ . '/../..';  // Adjust this path as needed

        // Build the full path to the class file
        $file = $baseDirectory. '/' . $classMap[$class];

        // Check if the class file exists and load it
        if (file_exists($file)) {
            require_once($file);
        }
    }
});

?>