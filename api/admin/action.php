<?php
require_once __DIR__ . '/../../functions.php';
requireAdmin();

$data = getJsonPayload();
action = $data['action'] ?? '';

try {
    $pdo = db();

    if ($action === 'accept') {
        $requestId = (int) ($data['request_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM signup_requests WHERE id = :id AND status = "pending" LIMIT 1');
        $stmt->execute(['id' => $requestId]);
        $request = $stmt->fetch();
        if (!$request) {
            jsonResponse(['success' => false, 'message' => 'Pending signup request not found.'], 404);
        }

        $insert = $pdo->prepare(
            'INSERT INTO users (first_name, last_name, email, phone_number, is_admin, status, created_at, updated_at)
             VALUES (:first_name, :last_name, :email, :phone_number, 0, "active", NOW(), NOW())'
        );
        $insert->execute([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'email' => $request['email'],
            'phone_number' => $request['phone_number'],
        ]);

        $pdo->prepare('UPDATE signup_requests SET status = "accepted", processed_at = NOW() WHERE id = :id')
            ->execute(['id' => $requestId]);

        sendSms($request['phone_number'], "Your FamilyTree signup request has been accepted. You can now log in with your phone number.");
        jsonResponse(['success' => true, 'message' => 'User request accepted and user added.']);
    }

    if ($action === 'decline') {
        $requestId = (int) ($data['request_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM signup_requests WHERE id = :id AND status = "pending" LIMIT 1');
        $stmt->execute(['id' => $requestId]);
        $request = $stmt->fetch();
        if (!$request) {
            jsonResponse(['success' => false, 'message' => 'Pending signup request not found.'], 404);
        }

        $pdo->prepare('UPDATE signup_requests SET status = "declined", processed_at = NOW() WHERE id = :id')
            ->execute(['id' => $requestId]);

        sendSms($request['phone_number'], "Your FamilyTree signup request has been declined. Contact support for help.");
        jsonResponse(['success' => true, 'message' => 'Signup request declined.']);
    }

    if ($action === 'delete') {
        $userId = (int) ($data['user_id'] ?? 0);
        $user = getUserById($userId);
        if (!$user || $user['status'] !== 'active') {
            jsonResponse(['success' => false, 'message' => 'User not found or already deleted.'], 404);
        }

        $pdo->prepare('UPDATE users SET status = "deleted", updated_at = NOW() WHERE id = :id')
            ->execute(['id' => $userId]);

        sendSms($user['phone_number'], "Your FamilyTree account has been deleted by the admin.");
        jsonResponse(['success' => true, 'message' => 'User deleted and SMS notification sent.']);
    }

    if ($action === 'update') {
        $userId = (int) ($data['user_id'] ?? 0);
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = normalizePhone($data['phone_number'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '' || !validateEmail($email) || $phone === '') {
            jsonResponse(['success' => false, 'message' => 'Complete and valid name, email, and phone are required.'], 422);
        }

        $user = getUserById($userId);
        if (!$user || $user['status'] !== 'active') {
            jsonResponse(['success' => false, 'message' => 'User not found or not active.'], 404);
        }

        $conflict = $pdo->prepare('SELECT id FROM users WHERE (email = :email OR phone_number = :phone) AND id != :id LIMIT 1');
        $conflict->execute(['email' => $email, 'phone' => $phone, 'id' => $userId]);
        if ($conflict->fetch()) {
            jsonResponse(['success' => false, 'message' => 'The email or phone is already assigned to another user.'], 409);
        }

        $pdo->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone_number = :phone, updated_at = NOW() WHERE id = :id')
            ->execute(['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'phone' => $phone, 'id' => $userId]);

        sendSms($phone, "Your FamilyTree profile has been updated by admin.");
        jsonResponse(['success' => true, 'message' => 'User updated and SMS sent.']);
    }

    jsonResponse(['success' => false, 'message' => 'Unknown admin action.'], 400);
} catch (Exception $e) {
    error_log('Admin action error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Unable to perform the requested action.'], 500);
}
