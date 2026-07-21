<?php

require_once './config/mailer.php';
use PHPMailer\PHPMailer\PHPMailer;
require_once './vendor/autoload.php';
/**
 * Retourne un objet PhpMailer actif et configuré
 */
function getMailer(): PHPMailer
{
    // create a new object
    $mail = new PHPMailer();
    // configure an SMTP
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = MAIL_PORT;
    $mail->CharSet = "UTF-8";
    $mail->Encoding = "base64";

    return $mail;
}