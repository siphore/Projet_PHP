<?php

class DBManager {
    private static $db;

    public static function getDB() {
        if (self::$db === null) {
            $config = parse_ini_file("../myDB/config/db.ini");
            // echo $config["dsn"]."<br>";
            // $dbPath = str_replace('|', DIRECTORY_SEPARATOR, $config["dsn"]);
            // echo $dbPath."<br>";
            // self::$db = new PDO($dbPath);
            self::$db = new PDO($config["dsn"]);
        }

        return self::$db;
    }

    public static function createFormData() {
        try {
            self::$db = self::getDB();
            self::$db->exec("CREATE TABLE IF NOT EXISTS form_data (
                id INTEGER PRIMARY KEY,
                username TEXT UNIQUE,
                psw TEXT,
                email TEXT UNIQUE
            )");
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // creates the password reset table 
    public static function createPasswordFormData() {
        try {
            self::$db = self::getDB(); 
            self::$db->exec("CREATE TABLE IF NOT EXISTS password_reset (
                id INTEGER PRIMARY KEY,
                email TEXT NOT NULL,
                token TEXT NOT NULL,
                expiry DATETIME NOT NULL
            )");
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // Useless
    public static function readFormData($username) {
        try {
            self::$db = self::getDB();
            $stmt = self::$db->prepare("SELECT * FROM form_data WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Fetch the data
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Close the database connection
            self::$db = null;

            return $data;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // inserts the registration data into database
    public static function updateFormData($username, $password, $email) {
        try {
            self::$db = self::getDB();
            $stmt = self::$db->prepare("INSERT INTO form_data (username, psw, email) VALUES (:username, :psw, :email)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':psw', $password);
            $stmt->bindParam(':email', $email);
            $result = $stmt->execute();

            return $result;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }


    public static function updatePassword ($password, $email) {
        try {
            self::$db = self::getDB();
            $stmt = self::$db->prepare("UPDATE form_data SET psw = :psw WHERE email = :email");
            $stmt->bindParam(":psw", $password);
            $stmt->bindParam(":email", $email);
            $result = $stmt->execute();

            return $result;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // 
    public static function updatePasswordFormData($email, $token, $tokenExpiry) {
        try {
            self::$db = self::getDB();
            $stmt = self::$db->prepare("INSERT INTO password_reset (email, token, expiry) VALUES (:email, :token, :tokenExpiry)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':tokenExpiry', $tokenExpiry);
            $result = $stmt->execute();

            return $result;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // Useless
    public static function deleteFormData($username) {
        try {
            self::$db = self::getDB();

            // Prepare and execute a query to delete data based on the username
            $stmt = self::$db->prepare("DELETE FROM form_data WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Check if any rows were affected (record deleted)
            $rowsAffected = $stmt->rowCount();

            // Close the database connection
            self::$db = null;

            return $rowsAffected; // Returns the number of rows deleted (0 if none)
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return -1;
        }
    }

    public static function getPodcastData() {
        try {
            self::$db = self::getDB();
            $stmt = self::$db->prepare("SELECT podcasts.podcast_id, podcasts.title, podcasts.image_url, podcasts.audio_file, GROUP_CONCAT(artists.fname || ' ' || artists.lname) as artists
            FROM podcasts
            INNER JOIN podcast_artists ON podcasts.podcast_id = podcast_artists.podcast_id
            INNER JOIN artists ON podcast_artists.artist_id = artists.artist_id
            GROUP BY podcasts.title
            ORDER BY podcasts.podcast_id;");
            $stmt->execute();
        
            $data = array();
        
            // Retrieve every row returned by the sql request into the data array
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
        
            // Close the db connection
            self::$db = null;
        
            // Write the recovered data in a json file
            file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));

            return $data;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    public static function createPodcast($title, $artists, $audioSrc, $imgSrc) {
        try {
            self::$db = self::getDB();

            // Insert the new podcast into the 'podcasts' table
            $stmt = self::$db->prepare("INSERT INTO podcasts (title, image_url, audio_file) VALUES (?, ?, ?)");
            $stmt->execute([$title, $imgSrc, $audioSrc]);

            // Get the last inserted ID (auto-incremented primary key)
            $podcastId = self::$db->lastInsertId();

            // Insert artists into the 'artists' table and associate with the podcast in 'podcast_artists' table
            $artistNames = explode(",", $artists);

            foreach ($artistNames as $artistName) {
                $nameParts = explode(" ", trim($artistName, " "));
                $fname = $nameParts[0];
                $lname = $nameParts[1] ?? '';

                // Insert or update the artist in the 'artists' table
                $stmt = self::$db->prepare("INSERT OR REPLACE INTO artists (fname, lname) VALUES (?, ?)");
                $stmt->execute([$fname, $lname]);

                // Get the artist ID
                $artistId = self::$db->lastInsertId();

                // Associate the artist with the podcast in the 'podcast_artists' table
                $stmt = self::$db->prepare("INSERT INTO podcast_artists (podcast_id, artist_id) VALUES (?, ?)");
                $stmt->execute([$podcastId, $artistId]);
            }

            // Close the database connection
            self::$db = null;

            return $podcastId;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            echo "Error creating podcast: " . $e->getMessage();
            return false;
        }
    }

    public static function updatePodcast($id, $title, $artistsId, $newArtists, $audio, $img) {
        try {
            self::$db = self::getDB();

            // Update title, img & audio
            $stmt = self::$db->prepare("UPDATE podcasts SET title = :title, image_url = :image_url, audio_file = :audio_file WHERE podcast_id = :podcast_id");
            $stmt->bindParam(':podcast_id', $id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':image_url', $img);
            $stmt->bindParam(':audio_file', $audio);
            $stmt->execute();

            // Delete from podcast_artists
            $stmt = self::$db->prepare("DELETE FROM podcast_artists WHERE podcast_id = :podcast_id");
            $stmt->bindParam('podcast_id', $id);
            $stmt->execute();
            
            // Update artists
            $artists = explode(",", $newArtists);
            for ($i = 0; $i < count($artists); ++$i) {
                $artist = explode(" ", $artists[$i]);
                $fname = $artist[0];
                $lname = $artist[1];
                $artistId = $artistsId[$i];

                // If artist already in DB
                if (self::isArtistInDB($artistId)) {
                    // Update artist name
                    $stmt = self::$db->prepare("UPDATE artists SET fname = :fname, lname = :lname WHERE artist_id = :artist_id");
                    $stmt->bindParam(':artist_id', $artistId);
                    $stmt->bindParam(':fname', $fname);
                    $stmt->bindParam(':lname', $lname);
                    $stmt->execute();
                } else {
                    // Insert into artists
                    $stmt = self::$db->prepare("INSERT INTO artists (fname, lname) VALUES (:fname, :lname)");
                    $stmt->bindParam(':fname', $fname);
                    $stmt->bindParam(':lname', $lname);
                    $stmt->execute();

                    $artistId = self::getLastArtist()['artist_id'];
                }

                // Insert into podcast_artists
                $stmt = self::$db->prepare("INSERT INTO podcast_artists (podcast_id, artist_id) VALUES (:podcast_id, :artist_id)");
                $stmt->bindParam(':podcast_id', $id);
                $stmt->bindParam(':artist_id', $artistId);
                $stmt->execute();
            }

            // Reset the id sequences from sqlite_sequence
            $stmt = self::$db->prepare("DELETE FROM sqlite_sequence");
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            error_log("Error updating podcast: " . $e->getMessage());
            return false;
        }
    }

    private static function isArtistInDB($artistId) {
        try {
            self::$db = self::getDB();
    
            // Check if the artist with the given ID exists
            $stmt = self::$db->prepare("SELECT COUNT(*) FROM artists WHERE artist_id = :artist_id");
            $stmt->bindParam(':artist_id', $artistId, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
    
            return $count > 0;
        } catch (PDOException $e) {
            // Handle any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    private static function getLastArtist() {
        try {
            self::$db = self::getDB();
            $stmt = self::$db->query("SELECT * FROM artists ORDER BY artist_id DESC LIMIT 1");
            
            // Fetch the result as an associative array
            $lastEntry = $stmt->fetch(PDO::FETCH_ASSOC);
        
            // $lastEntry now contains the data of the last entry
            return $lastEntry;
        } catch (PDOException $e) {
            // Handle any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    public static function deletePodcast($podcastId) {
        try {
            self::$db = self::getDB();

            // Get the associated artist IDs
            $stmt = self::$db->prepare("SELECT artist_id FROM podcast_artists WHERE podcast_id = :podcastId");
            $stmt->bindParam(':podcastId', $podcastId, PDO::PARAM_INT);
            $stmt->execute();
            $artistIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete the podcast from the podcasts table
            $stmt = self::$db->prepare("DELETE FROM podcasts WHERE podcast_id = :podcastId");
            $stmt->bindParam(':podcastId', $podcastId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete associated records in the podcast_artists table
            $stmt = self::$db->prepare("DELETE FROM podcast_artists WHERE podcast_id = :podcastId");
            $stmt->bindParam(':podcastId', $podcastId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete associated artists from the artists table
            foreach ($artistIds as $artistId) {
                $stmt = self::$db->prepare("DELETE FROM artists WHERE artist_id = :artistId");
                $stmt->bindParam(':artistId', $artistId, PDO::PARAM_INT);
                $stmt->execute();
            }
    
            // Reset the id sequences from sqlite_sequence
            $stmt = self::$db->prepare("DELETE FROM sqlite_sequence");
            $stmt->execute();

            // Rearrange podcast ids
            self::rearrangePodcastIds();
    
            // Close the database connection
            self::$db = null;
    
            return true;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    private static function rearrangePodcastIds() {
        try {
            self::$db = self::getDB();
    
            // Get the remaining podcasts after deletion
            $stmt = self::$db->prepare("SELECT podcast_id FROM podcasts ORDER BY podcast_id");
            $stmt->execute();
            $podcastIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
            // Update the podcast IDs to remove the gap
            foreach ($podcastIds as $index => $podcastId) {
                $newPodcastId = $index + 1;
    
                $stmt = self::$db->prepare("UPDATE podcasts SET podcast_id = :newPodcastId WHERE podcast_id = :podcastId");
                $stmt->bindParam(':newPodcastId', $newPodcastId, PDO::PARAM_INT);
                $stmt->bindParam(':podcastId', $podcastId, PDO::PARAM_INT);
                $stmt->execute();
            }
    
            // Close the database connection
            self::$db = null;
    
            return true;
        } catch (PDOException $e) {
            // Handle any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }
    
    public static function getArtistIdFromNames($artist) {
        try {
            self::$db = self::getDB();
            $artist = explode(" ", $artist);
            $fname = $artist[0];
            $lname = $artist[1];

            $stmt = self::$db->prepare("SELECT artist_id FROM artists WHERE fname = :fname and lname = :lname");
            $stmt->bindParam(':fname', $fname);
            $stmt->bindParam(':lname', $lname);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result['artist_id'];
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    public static function usernameExists($username) {
        try {
            self::$db = self::getDB();
            $query = "SELECT COUNT(*) FROM form_data WHERE username = :username";
            $stmt = self::$db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            // Close the database connection
            self::$db = null;

            return $count > 0;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    public static function emailExists($email) {
        try {
            self::$db = self::getDB();
            $query = "SELECT COUNT(*) FROM form_data WHERE email = :email";
            $stmt = self::$db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            // Close the database connection
            self::$db = null;

            return $count > 0;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    public static function getHashedPassword($username) {
        try {
            self::$db = self::getDB();
            $query = "SELECT psw FROM form_data WHERE username = :username";
            $stmt = self::$db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $hashedPassword = $stmt->fetchColumn();

            self::$db = null; // Close the database connection

            return $hashedPassword;
        } catch (PDOException $e) {
            // Handle database connection or query error
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }
}

?>