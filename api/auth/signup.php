<?php
require_once __DIR__ . '/../../functions.php';

$data = getJsonPayload();
$firstName = trim($data['first_name'] ?? '');
$lastName = trim($data['last_name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = normalizePhone($data['phone_number'] ?? '');

$errors = [];
if ($firstName === '') {
    $errors['first_name'] = 'First name is required.';
}
if ($lastName === '') {
    $errors['last_name'] = 'Last name is required.';
}
if ($email === '' || !validateEmail($email)) {
    $errors['email'] = 'Please enter a valid email address.';
}
if ($phone === '' || strlen(preg_replace('/[^0-9]/', '', $phone)) < 9) {
    $errors['phone_number'] = 'Please enter a valid phone number.';
}

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors], 422);
}

try {
    $pdo = db();
    $existing = $pdo->prepare('SELECT id FROM users WHERE phone_number = :phone OR email = :email LIMIT 1');
    $existing->execute(['phone' => $phone, 'email' => $email]);
    if ($existing->fetch()) {
        jsonResponse(['success' => false, 'message' => 'This email or phone number is already registered.'], 409);
    }

    $pending = $pdo->prepare('SELECT id, status FROM signup_requests WHERE phone_number = :phone OR email = :email ORDER BY created_at DESC LIMIT 1');
    $pending->execute(['phone' => $phone, 'email' => $email]);
    $existingPending = $pending->fetch();
    if ($existingPending && $existingPending['status'] === 'pending') {
        jsonResponse(['success' => false, 'message' => 'A signup request with this contact already exists and is waiting approval.'], 409);
    }

    $insert = $pdo->prepare(
        'INSERT INTO signup_requests (first_name, last_name, email, phone_number, status, created_at)
         VALUES (:first_name, :last_name, :email, :phone_number, "pending", NOW())'
    );
    $insert->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone_number' => $phone,
    ]);

    $emailBody = "<p>A new signup request has arrived.</p>" .
        "<p><strong>Name:</strong> {$firstName} {$lastName}<br>" .
        "<strong>Email:</strong> {$email}<br>" .
        "<strong>Phone:</strong> {$phone}</p>" .
        "<p>Review the request in the admin dashboard.</p>";

    $emailSent = sendSesEmail(ADMIN_EMAIL, 'New FamilyTree Signup Request', $emailBody);
    if (!$emailSent) {
        error_log('SES email failed for signup notice: ' . $phone);
    }

    jsonResponse(['success' => true, 'message' => 'Signup request submitted. The admin has been notified.', 'data' => [
        'user' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone_number' => $phone,
        ],
    ]]);
} catch (Exception $e) {
    error_log('Signup error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Unable to process signup request right now.'], 500);
}
