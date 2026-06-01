<?php
require_once __DIR__ . '/../../functions.php';

$data = getJsonPayload();
$phone = normalizePhone($data['phone_number'] ?? '');
if ($phone === '' || strlen(preg_replace('/[^0-9]/', '', $phone)) < 9) {
    jsonResponse(['success' => false, 'message' => 'Please enter a valid phone number.'], 422);
}

try {
    $user = getUserByPhone($phone);
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Phone number not found. Please sign up first.'], 404);
    }

    // If this account is marked as admin, skip the PIN and authenticate immediately.
    if (!empty($user['is_admin'])) {
        session_start();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone_number' => $user['phone_number'],
            'is_admin' => true,
            'logged_in' => true,
        ];
        jsonResponse(['success' => true, 'message' => 'Admin authenticated. Redirecting...', 'redirect_url' => '/admin.php']);
    }

    // Regular users receive a PIN
    $pin = generatePinCode();
    $pdo = db();
    $pdo->prepare('DELETE FROM login_pins WHERE phone_number = :phone')->execute(['phone' => $phone]);
    $pdo->prepare('INSERT INTO login_pins (phone_number, pin, expires_at, created_at) VALUES (:phone, :pin, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())')
        ->execute(['phone' => $phone, 'pin' => $pin]);

    $smsSent = sendSms($phone, "Your FamilyTree login PIN: {$pin}. It expires in 10 minutes.");
    if (!$smsSent) {
        error_log('Failed to send Twilio PIN to ' . $phone);
    }

    jsonResponse(['success' => true, 'message' => 'PIN code sent to your phone. Please enter it to continue.', 'data' => ['phone' => $phone, 'is_admin' => !empty($user['is_admin'])]]);
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Unable to process login at the moment.'], 500);
}
