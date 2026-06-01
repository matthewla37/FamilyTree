<?php
require_once __DIR__ . '/../../functions.php';

$data = getJsonPayload();
$phone = normalizePhone($data['phone_number'] ?? '');
$pin = trim($data['pin'] ?? '');
if ($phone === '' || $pin === '') {
    jsonResponse(['success' => false, 'message' => 'Phone number and PIN are required.'], 422);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM login_pins WHERE phone_number = :phone AND pin = :pin AND expires_at >= NOW() ORDER BY created_at DESC LIMIT 1');
    $stmt->execute(['phone' => $phone, 'pin' => $pin]);
    $record = $stmt->fetch();
    if (!$record) {
        jsonResponse(['success' => false, 'message' => 'Invalid PIN or PIN has expired.'], 401);
    }

    $user = getUserByPhone($phone);
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found. Please sign up first.'], 404);
    }

    session_start();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'phone_number' => $user['phone_number'],
        'is_admin' => !empty($user['is_admin']),
        'logged_in' => true,
    ];

    $redirect = !empty($user['is_admin']) ? '/admin.php' : '/restricted/index.php';
    jsonResponse(['success' => true, 'message' => 'Authenticated successfully.', 'redirect_url' => $redirect]);
} catch (Exception $e) {
    error_log('Verify PIN error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Unable to validate PIN right now.'], 500);
}
