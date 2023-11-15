<? 
if (isset($_GET['token'])) {
    $token = $_GET['token'];

} 
$db = DBManager::getDB(); 

$tokenAvailable = verifyToken($token, $db); 

function verifyToken($token, $db) {

    // Prepare a SELECT query to retrieve the token information
    $query = $db->prepare("SELECT * FROM password_reset WHERE token = ? AND expiry > CURRENT_TIMESTAMP");
    $query->execute([$token]);

    // Fetch the result
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return true; 
    } else {
        die("token not found");
    }; 
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve email from the form;
    $password = $_POST['password'];
    $rPassword = $_POST['rPassword']; 
};

// checks is both passwords match
if ($password === $rPassword) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
} else {
    die("Passwords don't match, please try again");
}

function getEmailByToken($token, $db) {
    // Prepare a SELECT query to retrieve the email associated with the token
    $query = $db->prepare("SELECT email FROM password_reset WHERE token = ?");
    $query->execute([$token]);

    // Fetch the result
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Return the email if the token is found
        return $result['email'];
    } else {
        // Return null if the token is not found
        return null;
    }
}

$email = getEmailByToken($token, $db);

if ($email != null) {
    DBManager::updatePassword($hashedPassword, $email); 
}




