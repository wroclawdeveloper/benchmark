<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$subject = 'benchmarktest';
$email = 'benchmarktest@gmail.com';
$to = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($email);
    $mail->addAddress($to);     // Add a recipient Name is optional

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body = '<strong>Score:</strong> '.$info['total_time'];

    $mail->send();

} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}
