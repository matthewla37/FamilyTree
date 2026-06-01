<?php
require_once __DIR__ . '/../../functions.php';
requireAdmin();

$search = trim($_GET['q'] ?? '');

try {
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = db()->prepare(
            'SELECT id, first_name, last_name, email, phone_number, created_at
             FROM users
             WHERE status = "active" AND (first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR phone_number LIKE :q)
             ORDER BY created_at DESC'
        );
        $stmt->execute(['q' => $like]);
    } else {
        $stmt = db()->prepare('SELECT id, first_name, last_name, email, phone_number, created_at FROM users WHERE status = "active" ORDER BY created_at DESC');
        $stmt->execute();
    }

    $users = $stmt->fetchAll();
    jsonResponse(['success' => true, 'data' => $users]);
} catch (Exception $e) {
    error_log('Admin users error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Unable to load users.'], 500);
}
