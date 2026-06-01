<?php
require_once __DIR__ . '/../../functions.php';
requireAdmin();

try {
    $stmt = db()->prepare('SELECT id, first_name, last_name, email, phone_number, created_at FROM signup_requests WHERE status = "pending" ORDER BY created_at DESC');
    $stmt->execute();
    $requests = $stmt->fetchAll();
    jsonResponse(['success' => true, 'data' => $requests]);
} catch (Exception $e) {
    error_log('Admin pending error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Unable to load pending requests.'], 500);
}
