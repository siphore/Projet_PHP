<?php

class DBManager {
    private static $db;
    private const ALL_USERS = 'all';

    public static function getDB() {
        if (self::$db === null) {
            $config = parse_ini_file("../myDB/config/db.ini");
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

    public static function updateFormData($username, $password, $email) {
        // self::deleteFormData(self::ALL_USERS);

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
            $stmt = self::$db->prepare("UPDATE form_data SET psw = $password WHERE email = $email;");
            $result = $stmt->execute();

            return $result;
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    public static function updatePasswordFormData($email, $token, $tokenExpiry) {
        // self::deleteFormData(self::ALL_USERS);

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

    public static function deleteFormData($username) {
        try {
            self::$db = self::getDB();

            if ($username == self::ALL_USERS) {
                // Prepare and execute a query to delete all records in the form_data table
                $stmt = self::$db->prepare("DELETE FROM form_data");
                $stmt->execute();
            } else {
                // Prepare and execute a query to delete data based on the username
                $stmt = self::$db->prepare("DELETE FROM form_data WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
            }

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
        } catch (PDOException $e) {
            // Handles any database connection or query errors
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // public static function getPasswordResetData($userId) {
    //     try {
    //         self::$db = self::getDB();
    //         $stmt = self::$db->prepare("SELECT * FROM password_reset WHERE user_id = :user_id");
    //         $stmt->bindParam(':user_id', $userId);
    //         $result = $stmt->execut();

    //         return $result;
    //     } catch (PDOException $e) {
    //         // Handles any database connection or query errors
    //         echo "Database error: " . $e->getMessage();
    //         return false;
    //     }
    // }

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