<?php
header('Content-Type: application/json');

/* Allow only POST requests */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok'  => false,
        'msg' => 'Invalid request method.'
    ]);
    exit;
}

/* Configuration */
$to       = 'info@tanzaniavacation.co.tz';
$from     = 'no-reply@tanzaniavacation.co.tz'; // MUST exist in cPanel
$siteName = 'TZ VACATION Website';

/* Get and sanitize input */
$name    = trim(strip_tags($_POST['name'] ?? ''));
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$mobile  = trim(strip_tags($_POST['mobile'] ?? ''));
$subjectInput = trim(strip_tags($_POST['subject'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

/* Validation */
$errors = [];

if ($name === '') {
    $errors[] = 'Name is required.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
}

if ($mobile === '') {
    $errors[] = 'Mobile number is required.';
}

if ($subjectInput === '') {
    $errors[] = 'Subject is required.';
}

if ($message === '') {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'ok'     => false,
        'msg'    => 'Please correct the errors below.',
        'errors' => $errors
    ]);
    exit;
}

/* Prepare email */
$subject = "New Contact Form Submission: {$subjectInput}";
$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

$body  = "You received a new message from your website contact form.\n\n";
$body .= "Name: {$name}\n";
$body .= "Email: {$email}\n";
$body .= "Phone: {$mobile}\n";
$body .= "Subject: {$subjectInput}\n\n";
$body .= "Message:\n{$message}\n";

/* Prevent email header injection */
$to      = str_replace(["\r", "\n"], '', $to);
$subject = str_replace(["\r", "\n"], '', $subject);

/* Headers */
$headers  = "From: {$siteName} <{$from}>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

/* Send email */
$sent = mail($to, $subject, $body, $headers, "-f{$from}");

if ($sent) {
    echo json_encode([
        'ok'  => true,
        'msg' => 'Thank you! Your message has been sent successfully.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'ok'  => false,
        'msg' => 'Unable to send message at this time. Please try again later.'
    ]);
}
