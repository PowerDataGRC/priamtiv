<?php
header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'error' => 'Method not allowed.']));
}

// Guard: config.php must exist (it is gitignored — create it from config.example.php)
if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(503);
    exit(json_encode(['ok' => false, 'error' => 'Contact form is not yet configured on the server.']));
}
require_once __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Honeypot ──────────────────────────────────────────────────────────────────
// Bots fill every field; real users leave this blank.
if (!empty($_POST['website'])) {
    exit(json_encode(['ok' => true])); // silent success — don't tip off the bot
}

// ── Session-based rate limiting (3 submissions per 10 min per session) ────────
session_start();
$now    = time();
$window = 600;
$limit  = 3;
if (!isset($_SESSION['form_attempts'])) {
    $_SESSION['form_attempts'] = [];
}
$_SESSION['form_attempts'] = array_filter(
    $_SESSION['form_attempts'],
    fn($t) => ($now - $t) < $window
);
if (count($_SESSION['form_attempts']) >= $limit) {
    http_response_code(429);
    exit(json_encode(['ok' => false, 'error' => 'Too many submissions. Please wait a few minutes and try again.']));
}

// ── reCAPTCHA v3 verification ─────────────────────────────────────────────────
$token = trim($_POST['recaptcha_token'] ?? '');
if (!$token) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'CAPTCHA token missing.']));
}
$verify  = file_get_contents('https://www.google.com/recaptcha/api/siteverify?' . http_build_query([
    'secret'   => RECAPTCHA_SECRET,
    'response' => $token,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
]));
$captcha = json_decode($verify, true);
if (empty($captcha['success']) || ($captcha['score'] ?? 0) < 0.5) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CAPTCHA verification failed. Please refresh and try again.']));
}

// ── Input validation ──────────────────────────────────────────────────────────
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$company = trim($_POST['company'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'Name, email, and message are required.']));
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'Please enter a valid email address.']));
}
if (mb_strlen($name) > 120 || mb_strlen($message) > 4000 || mb_strlen($company) > 200) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'Input exceeds maximum allowed length.']));
}

// ── Sanitize for HTML email ───────────────────────────────────────────────────
$safeName    = htmlspecialchars($name,    ENT_QUOTES, 'UTF-8');
$safeEmail   = htmlspecialchars($email,   ENT_QUOTES, 'UTF-8');
$safeCompany = htmlspecialchars($company, ENT_QUOTES, 'UTF-8');
$safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
$companyLine = $safeCompany ? "<p><strong>Company:</strong> {$safeCompany}</p>" : '';

// ── Send via PHPMailer ────────────────────────────────────────────────────────
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_USER, 'PRIAMTIV Website');
    $mail->addAddress(MAIL_TO, 'PRIAMTIV');
    $mail->addReplyTo($email, $name);

    $mail->Subject = "Contact: {$name}" . ($company ? " ({$company})" : '');
    $mail->isHTML(true);
    $mail->Body = "
        <p><strong>Name:</strong> {$safeName}</p>
        {$companyLine}
        <p><strong>Email:</strong> <a href='mailto:{$safeEmail}'>{$safeEmail}</a></p>
        <p><strong>Message:</strong><br>{$safeMessage}</p>
        <hr><small>Sent from priamtiv.com contact form</small>
    ";
    $mail->AltBody = "Name: {$name}\n"
        . ($company ? "Company: {$company}\n" : '')
        . "Email: {$email}\nMessage:\n{$message}";

    $mail->send();
    $_SESSION['form_attempts'][] = $now;
    exit(json_encode(['ok' => true]));

} catch (Exception $e) {
    error_log('PHPMailer error: ' . $mail->ErrorInfo);
    http_response_code(500);
    exit(json_encode(['ok' => false, 'error' => 'Could not send your message. Please try again shortly.']));
}