<?php
require_once __DIR__ . '/../functions.php';
session_start();
if (empty($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    header('Location: /login.html');
    exit;
}
if (!empty($_SESSION['user']['is_admin'])) {
    header('Location: /admin.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FamilyTree Protected Area</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f3f7fb; color: #17212b; }
        .frame { width: min(780px, 95%); background: #fff; border-radius: 22px; box-shadow: 0 28px 70px rgba(24, 40, 61, 0.12); overflow: hidden; display: grid; grid-template-columns: 1fr 320px; }
        .info { padding: 36px 40px; }
        .info h1 { font-size: 34px; margin-bottom: 20px; }
        .info p { margin-bottom: 18px; color: #4b5668; line-height: 1.7; }
        .actions { display: flex; gap: 14px; flex-wrap: wrap; }
        .actions a { display: inline-flex; align-items: center; justify-content: center; padding: 14px 20px; background: #4a8b2a; color: #fff; text-decoration: none; border-radius: 12px; font-weight: 700; }
        .panel { background: #eef1f6; padding: 28px; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 18px; }
        .panel img { max-width: 100%; height: auto; border-radius: 18px; }
    </style>
</head>
<body>
<div class="frame">
    <div class="info">
        <h1>Welcome back, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
        <p>Your FamilyTree account is verified. This protected page is only available after login.</p>
        <p>Use the site navigation to continue your journey. If you need to log out, click the button below.</p>
        <div class="actions">
            <a href="/logout.php">Logout</a>
            <a href="/">Home</a>
        </div>
    </div>
    <div class="panel">
        <img src="../dashboard.png" alt="FamilyTree dashboard illustration">
        <p style="color:#334155; text-align:center; max-width:260px;">This page is protected by session validation and Apache folder-level rewrite routing.</p>
    </div>
</div>
</body>
</html>
