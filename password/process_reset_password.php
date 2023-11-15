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
        // Token is valid
        return true;
    } else {
        // Token is invalid or expired
        return false;
    }
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

if ($email !== null) {
    // Token is valid, proceed with the email (e.g., for password reset)
    echo "Email associated with the token: $email";
} else {
    // Token is invalid or not found, handle accordingly (e.g., show an error message)
    echo 'Invalid or expired token';
}

