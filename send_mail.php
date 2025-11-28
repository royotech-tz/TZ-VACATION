<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Invalid request method.']);
    exit;
}

// Configuration
$to = 'info@tanzaniavacation.co.tz';
$from = 'no-reply@tanzaniavacation.co.tz'; // Must exist on your server
$siteName = 'TZ VACATION Website';

// Get and sanitize input
$name    = trim(strip_tags($_POST['name'] ?? ''));
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$mobile  = trim(strip_tags($_POST['mobile'] ?? ''));
$subjectInput = trim(strip_tags($_POST['subject'] ?? ''));
$msg     = trim(strip_tags($_POST['message'] ?? ''));

// Validation
$errors = [];
if (empty($name)) $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (empty($mobile)) $errors[] = 'Mobile number is required.';
if (empty($subjectInput)) $errors[] = 'Subject is required.';
if (empty($msg)) $errors[] = 'Message is required.';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Fix the errors:', 'errors' => $errors]);
    exit;
}

// Prepare email content
$subject = "New Contact Form Submission: {$subjectInput}";
$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

$body = "You received a new message from your website contact form.\n\n";
$body .= "Name: {$name}\n";
$body .= "Email: {$email}\n";
$body .= "Phone: {$mobile}\n";
$body .= "Subject: {$subjectInput}\n\n";
$body .= "Message:\n{$msg}\n";

// Clean for email injection
$to      = str_replace(["\r", "\n", "%0a", "%0d"], '', $to);
$subject = str_replace(["\r", "\n", "%0a", "%0d"], '', $subject);

// Email headers
$headers = "From: {$siteName} <{$from}>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
$success = mail($to, $subject, $body, $headers, "-f{$from}");

if ($success) {
    echo json_encode(['ok' => true, 'msg' => 'Thank you! Your message has been sent.']);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Error sending message. Try again later.']);
}
?>
