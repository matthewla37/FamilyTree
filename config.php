<?php
// Application configuration
// Update these values before using the app.

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'familytree');
define('DB_USER', 'root');
define('DB_PASS', '');

define('ADMIN_EMAIL', 'admin@example.com');

define('SES_SMTP_HOST', 'email-smtp.us-east-1.amazonaws.com');
define('SES_SMTP_PORT', 465);
define('SES_SMTP_USERNAME', 'YOUR_SES_SMTP_USERNAME');
define('SES_SMTP_PASSWORD', 'YOUR_SES_SMTP_PASSWORD');
define('SES_FROM_EMAIL', 'no-reply@familytree.com');

define('TWILIO_ACCOUNT_SID', 'YOUR_TWILIO_ACCOUNT_SID');
define('TWILIO_AUTH_TOKEN', 'YOUR_TWILIO_AUTH_TOKEN');
define('TWILIO_FROM_NUMBER', '+15551234567');

define('ADMIN_PHONE', '+1(555)345-6789');

define('SITE_ROOT', '/');

define('ADMIN_DASHBOARD_URL', SITE_ROOT . 'admin.php');

define('LOGIN_PAGE', SITE_ROOT . 'login.html');

define('SIGNUP_PAGE', SITE_ROOT . 'signup.html');
