<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ładowanie PHPMailer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        http_response_code(400);
        echo "Nieprawidłowy adres e-mail.";
        exit;
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Obsługa przesłanych plików
    $uploadedFiles = [];
    foreach ($_FILES as $file) {
        $filePath = $uploadDir . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $uploadedFiles[] = $filePath;
        }
    }

    if (empty($uploadedFiles)) {
        http_response_code(400);
        echo "Wysłanie plików nie powiodło się.";
        exit;
    }

    // Zapis do pliku CSV
    $csvFile = 'data.csv';
    $fileHandler = fopen($csvFile, 'a');
    fputcsv($fileHandler, [$email, implode(", ", $uploadedFiles)]);
    fclose($fileHandler);

    // Wysyłanie maila
    $mail = new PHPMailer(true);
    try {
        // Konfiguracja SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.mail.eu-west-1.awsapps.com'; // Twój SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'konkurs@akademialubella.pl'; // Twój email SMTP
        $mail->Password = 'rUY2W93bwH933'; // Twoje hasło SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Odbiorca
        $mail->setFrom('konkurs@akademialubella.pl', 'Formularz');
        $mail->addAddress($email);

        // Treść wiadomości
        $mail->isHTML(true);
        $mail->Subject = 'Potwierdzenie wysyłki formularza';
        $mail->Body = '<p>Dziękujemy za przesłanie formularza.</p>';

        $mail->send();
        echo "Formularz został wysłany.";
    } catch (Exception $e) {
        echo "Wysłanie e-maila nie powiodło się: {$mail->ErrorInfo}";
    }
}
