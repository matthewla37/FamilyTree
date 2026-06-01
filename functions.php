<?php
require_once __DIR__ . '/config.php';

function db() {
    static $pdo;
    if ($pdo) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}

function jsonResponse(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function getJsonPayload(): array {
    $payload = file_get_contents('php://input');
    if (!$payload) {
        return [];
    }
    $data = json_decode($payload, true);
    return is_array($data) ? $data : [];
}

function normalizePhone(string $phone): string {
    $phone = trim($phone);
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (strpos($phone, '+') !== 0) {
        $phone = '+' . $phone;
    }
    return $phone;
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function requireAdmin(): void {
    session_start();
    if (empty($_SESSION['user']) || empty($_SESSION['user']['logged_in']) || empty($_SESSION['user']['is_admin'])) {
        jsonResponse(['success' => false, 'message' => 'Admin authentication required.'], 403);
    }
}

function requireLogin(): void {
    session_start();
    if (empty($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
        header('Location: ' . LOGIN_PAGE);
        exit;
    }
}

function sendSms(string $to, string $message): bool {
    if (empty(TWILIO_ACCOUNT_SID) || empty(TWILIO_AUTH_TOKEN) || empty(TWILIO_FROM_NUMBER)) {
        return false;
    }

    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Messages.json';
    $payload = http_build_query([
        'From' => TWILIO_FROM_NUMBER,
        'To' => $to,
        'Body' => $message,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error || $httpCode < 200 || $httpCode >= 300) {
        error_log('Twilio SMS error: ' . ($error ?: $response));
        return false;
    }

    return true;
}

function sendSesEmail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool {
    if (empty(SES_SMTP_HOST) || empty(SES_SMTP_USERNAME) || empty(SES_SMTP_PASSWORD) || empty(SES_FROM_EMAIL)) {
        return false;
    }

    $boundary = '==PHPMIMEBOUNDARY_' . md5(time());
    $headers = [];
    $message = '';

    $headers[] = 'From: ' . SES_FROM_EMAIL;
    $headers[] = 'To: ' . $to;
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

    $body = '--' . $boundary . "\r\n";
    $body .= 'Content-Type: text/plain; charset=UTF-8\r\n\r\n';
    $body .= ($textBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody))) . "\r\n\r\n";
    $body .= '--' . $boundary . "\r\n";
    $body .= 'Content-Type: text/html; charset=UTF-8\r\n\r\n';
    $body .= $htmlBody . "\r\n\r\n";
    $body .= '--' . $boundary . '--';

    $socket = stream_socket_client('ssl://' . SES_SMTP_HOST . ':' . SES_SMTP_PORT, $errno, $errstr, 30);
    if (!$socket) {
        error_log('SES SMTP connect failed: ' . $errno . ' ' . $errstr);
        return false;
    }

    $commands = [
        "EHLO localhost",
        'AUTH LOGIN',
        base64_encode(SES_SMTP_USERNAME),
        base64_encode(SES_SMTP_PASSWORD),
        'MAIL FROM:<' . SES_FROM_EMAIL . '>',
        'RCPT TO:<' . $to . '>',
        'DATA',
        'From: ' . SES_FROM_EMAIL,
        'To: ' . $to,
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        '',
        $body,
        '.',
        'QUIT',
    ];

    foreach ($commands as $command) {
        fwrite($socket, $command . "\r\n");
        $response = fgets($socket, 515);
        if ($response === false) {
            error_log('SES SMTP response error while sending: ' . $command);
            fclose($socket);
            return false;
        }
    }

    fclose($socket);
    return true;
}

function generatePinCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function getUserByPhone(string $phone): ?array {
    $sql = 'SELECT * FROM users WHERE phone_number = :phone AND status = "active" LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute(['phone' => $phone]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function getUserById(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}
