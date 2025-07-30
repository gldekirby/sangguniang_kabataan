<?php
header('Content-Type: application/json');

// Load Composer's autoloader (if using PHPMailer via Composer)
require 'C:\xampp\htdocs\sangguniang_kabataan\vendor\autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

// Validate inputs
if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Configure SMTP (Elastic Email or your domain SMTP)
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.elasticemail.com'; // Elastic Email SMTP (or your domain SMTP)
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kirbygeldore19@gmail.com'; // Your SMTP username
    $mail->Password   = '950D9504A21477AAF89CE3FF82859B0707DB'; // Your SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 2525; // Elastic Email uses 2525 (or 587 for TLS)

    // Recipients
    $mail->setFrom('sangguniangkabataanyouth@gmail.com', 'kirby'); // Your email and name
    $mail->addAddress('sangguniangkabataanyouth@gmail.com'); // Where emails should go

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = "
        <h3>New Contact Form Submission</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Message:</strong> $message</p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Failed to send email. Error: {$mail->ErrorInfo}"]);
}