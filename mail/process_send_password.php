<?php

// Ce script permet d'envoyer un mail sur le serveur mail : MailHog en local
// Remarque : Pour que cela fonctionne, il faut avoir démarré le serveur ;-)
// Libraire permettant l'envoi de mail (Symfony Mailer)
require_once './lib/vendor/autoload.php';
require_once('../myDB/config/autoload.php');

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

// Is user on windows or mac
$user_agent = getenv("HTTP_USER_AGENT");
if (strpos($user_agent, "Win") !== FALSE) $os = "Windows";
else if (strpos($user_agent, "Mac") !== FALSE) $os = "Mac";

// Get root directory
$dir = explode(DIRECTORY_SEPARATOR, __DIR__);
$root = [];
foreach($dir as $path) {
    array_push($root, $path);
    if ($path === "htdocs") break;
}
$root = implode(DIRECTORY_SEPARATOR, array_diff($dir, $root)).DIRECTORY_SEPARATOR;

DBManager::createPasswordFormData();

// Retrieves form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST['email'];
};

// checks is the email exists in our database
if (DBManager::emailExists($mail)) {

    // Generates Token and Send Email
    $token = bin2hex(random_bytes(16)); // Generate a random token
    $tokenExpiry = date("Y-m-d H:i:s", time() + 60 * 30);; // Token expires in 1 hour
    
    // Sends data to database
    DBManager::updatePasswordFormData($mail, $token, $tokenExpiry);

    // MailHog awaits  mails on 1025 port
    $transport = Transport::fromDsn('smtp://localhost:1025');
    // Mail construction
    $mailer = new Mailer($transport);
    $email = (new Email())
        ->from('lohann.kasper@heig-vd.ch')
        ->to("$mail")
        //->cc('cc@exemple.com')
        //->bcc('bcc@exemple.com')
        //->replyTo('replyto@exemple.com')
        ->priority(Email::PRIORITY_HIGH)
        ->subject("Concerne : Change of password")
        ->text("Click <a href=http://localhost:8888/mail/reset_password.php?token=$token>here </a> to reset your password")
        ->html("Click <a href=http://localhost:8888/mail/reset_password.php?token=$token>here </a> to reset your password");
        // ->text("Click <a href=http://localhost:".($os === "Windows" ? "80" : "8888".DIRECTORY_SEPARATOR."mail").DIRECTORY_SEPARATOR.$root."reset_password.php?token=$token>here </a> to reset your password")
        // ->html("Click <a href=http://localhost:".($os === "Windows" ? "80" : "8888".DIRECTORY_SEPARATOR."mail").DIRECTORY_SEPARATOR.$root."reset_password.php?token=$token>here </a> to reset your password");
    $result = $mailer->send($email);

    if ($result==null) echo "Un mail de récupération a été envoyé ! <a href='http://localhost:8025'>voir le mail</a>";
    else echo "Un problème lors de l'envoi du mail est survenu";
} else {
    echo "L'adresse email n'est pas rattachée à un compte existant.";
}

?>