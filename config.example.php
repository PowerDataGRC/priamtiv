<?php
// Copy this file to config.php and fill in your real values.
// config.php is in .gitignore — it will never be committed.

// Hostinger mailbox (create one in hPanel → Emails, e.g. hello@priamtiv.com)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Where to deliver contact form submissions (can be any address)
define('MAIL_TO',   '');

// reCAPTCHA v3 secret key (google.com/recaptcha → your site → Settings)
define('RECAPTCHA_SECRET', 'your-recaptcha-v3-secret-key');
