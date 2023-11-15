<?php

// Ce script permet d'envoyer un mail sur le serveur mail : MailHog en local
// Remarque : Pour que cela fonctionne, il faut avoir démarré le serveur ;-)
// Libraire permettant l'envoi de mail (Symfony Mailer)
require_once './lib/vendor/autoload.php';
require_once('../myDB/config/autoload.php');

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

DBManager::createPasswordFormData(); 

// Retrieves form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the username and password from the form
    $username = $_POST['uname'];
    $email = $_POST['email'];
};


if (DBManager::emailExists($email)) {

    // Generates Token and Send Email
    $token = bin2hex(random_bytes(32)); // Generate a random token
    $tokenExpiry = time() + 3600; // Token expires in 1 hour
    

    // MailHog awaits  mails on 1025 port
    $transport = Transport::fromDsn('smtp://localhost:1025');
    // mail construction
    $mailer = new Mailer($transport);
    $email = (new Email())
        ->from('lohann.kasper@heig-vd.ch')
        ->to("$email")
        //->cc('cc@exemple.com')
        //->bcc('bcc@exemple.com')
        //->replyTo('replyto@exemple.com')
        ->priority(Email::PRIORITY_HIGH)
        ->subject("Concerne $username : Récupération mot de passe")
        ->text('Un peu de texte')
        ->html('<h1>Un peu de html</h1>');
    $result = $mailer->send($email);

    if ($result==null) echo "Un mail de récupération a été envoyé ! <a href='http://localhost:8025'>voir le mail</a>";
    else echo "Un problème lors de l'envoi du mail est survenu";
} else {
    echo "L'adresse email n'est pas rattachée à un compte existant.";
}
