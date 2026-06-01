<?php
require_once __DIR__ . '/functions.php';
session_start();
if (empty($_SESSION['user']) || empty($_SESSION['user']['logged_in']) || empty($_SESSION['user']['is_admin'])) {
    header('Location: login.html');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — FamilyTree</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-height: 100vh; background: #eef2f4; color: #1d2938; }
        .app { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #fff, #f4f6f9); border-right: 1px solid #d7dde5; display: flex; flex-direction: column; justify-content: space-between; padding: 28px 18px; position: relative; }
        .sidebar::before { content: ''; position: absolute; inset: 0; background: url('sidebar.png') no-repeat center top / cover; opacity: 0.08; pointer-events: none; }
        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; margin-bottom: 32px; }
        .brand img { width: 56px; height: auto; }
        .brand h1 { font-size: 18px; line-height: 1.1; color: #264653; }
        .brand p { font-size: 12px; color: #5e6f7e; margin-top: 4px; }
        .sidebar nav { position: relative; z-index: 1; display: grid; gap: 10px; }
        .sidebar nav a { display: block; padding: 12px 14px; background: #fff; border: 1px solid #d7dde5; border-radius: 10px; color: #2d3a4d; text-decoration: none; font-weight: 700; }
        .sidebar nav a.active { background: #4a8b2a; color: #fff; }
        .logout-panel { position: relative; z-index: 1; display: flex; align-items: center; gap: 10px; }
        .logout-panel a { text-decoration: none; color: #c0392b; font-weight: 700; }

        .content { flex: 1; padding: 28px 32px; }
        .header { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 18px; margin-bottom: 24px; }
        .header h2 { font-size: 28px; letter-spacing: -0.3px; }
        .header small { color: #58616d; }
        .panel { background: #fff; border: 1px solid #d7dde5; border-radius: 18px; padding: 24px; box-shadow: 0 20px 60px rgba(92, 104, 124, 0.08); margin-bottom: 24px; }
        .panel h3 { margin-bottom: 16px; font-size: 18px; color: #2b3a4d; }
        .panel .row { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-bottom: 18px; }
        .panel .row input { border: 1px solid #d7dde5; border-radius: 10px; padding: 11px 14px; width: 220px; }
        .panel .row button { border: none; border-radius: 10px; padding: 12px 18px; background: #4a8b2a; color: #fff; cursor: pointer; font-weight: 700; }
        .panel .row button.danger { background: #c0392b; }
        .panel table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .panel table th, .panel table td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #e6ebf0; }
        .panel table th { color: #536170; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; font-size: 12px; }
        .status-pill { display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .status-pending { background: #fff7e4; color: #a66516; }
        .status-active { background: #e8f7ec; color: #20623b; }
        .btn-action { border: none; background: #4a8b2a; color: #fff; padding: 8px 11px; border-radius: 9px; cursor: pointer; font-size: 13px; margin-right: 6px; }
        .btn-action.decline { background: #d64545; }
        .btn-action.edit { background: #3b82f6; }
        .hero-card { display: grid; grid-template-columns: 1fr 260px; gap: 20px; align-items: stretch; margin-bottom: 24px; }
        .hero-card .hero-text { padding: 22px; border-radius: 18px; background: #fff; border: 1px solid #d7dde5; }
        .hero-card .hero-image { border-radius: 18px; background: url('dashboard.png') center/cover no-repeat; min-height: 220px; }
        .msg { display: none; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; }
        .msg.success { background: #e6f6e9; color: #1f5a34; border: 1px solid #a9d5b3; }
        .msg.error { background: #feecee; color: #7d1f23; border: 1px solid #f2b6b8; }
        @media(max-width: 900px) {
            .app { flex-direction: column; }
            .sidebar { width: 100%; order: 2; }
            .content { padding: 20px; }
            .hero-card { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div>
                <div class="brand">
                    <img src="logo.png" alt="FamilyTree logo">
                    <div>
                        <h1>FamilyTree Admin</h1>
                        <p>Manage signups and users</p>
                    </div>
                </div>
                <nav>
                    <a href="#pending" class="active">Requests</a>
                    <a href="#users">Users</a>
                </nav>
            </div>
            <div class="logout-panel">
                <div>
                    <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong><br>
                    <small><?= htmlspecialchars($user['email']) ?></small>
                </div>
                <a href="logout.php">Logout</a>
            </div>
        </aside>

        <main class="content">
            <div class="header">
                <div>
                    <h2>Admin Dashboard</h2>
                    <small>Review pending signups, search users, and manage account status.</small>
                </div>
                <div><strong>Admin Panel</strong></div>
            </div>

            <div class="hero-card">
                <div class="hero-text">
                    <h3>Notifications</h3>
                    <p>Any new signup request will appear below. Accept or decline requests as soon as they arrive to keep the database current.</p>
                </div>
                <div class="hero-image"></div>
            </div>

            <div id="msg" class="msg"></div>

            <section id="pending" class="panel">
                <h3>Pending Signup Requests</h3>
                <table id="pendingTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Requested</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>

            <section id="users" class="panel">
                <div class="row">
                    <div style="flex:1; min-width:240px;"><input id="searchInput" type="search" placeholder="Search by name, email or phone"></div>
                    <button onclick="loadUsers()">Search</button>
                </div>
                <h3>Active Users</h3>
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        const msgEl = document.getElementById('msg');
        function showMsg(type, html) {
            msgEl.className = 'msg ' + type;
            msgEl.innerHTML = html;
            msgEl.style.display = 'block';
            setTimeout(() => msgEl.style.display = 'none', 8000);
        }

        async function apiRequest(path, method = 'GET', body = null) {
            const options = { method, headers: {} };
            if (body) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body);
            }
            const res = await fetch(path, options);
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Server error');
            return data;
        }

        async function loadPending() {
            try {
                const data = await apiRequest('api/admin/pending.php');
                const body = document.querySelector('#pendingTable tbody');
                body.innerHTML = '';
                if (!data.data.length) {
                    body.innerHTML = '<tr><td colspan="5">No pending requests.</td></tr>';
                    return;
                }
                data.data.forEach(req => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${req.first_name} ${req.last_name}</td>
                        <td>${req.email}</td>
                        <td>${req.phone_number}</td>
                        <td>${req.created_at}</td>
                        <td>
                            <button class="btn-action" onclick="processRequest(${req.id}, 'accept')">Accept</button>
                            <button class="btn-action decline" onclick="processRequest(${req.id}, 'decline')">Decline</button>
                        </td>
                    `;
                    body.appendChild(row);
                });
            } catch (err) {
                showMsg('error', err.message);
            }
        }

        async function loadUsers() {
            try {
                const query = document.getElementById('searchInput').value.trim();
                const url = query ? `api/admin/users.php?q=${encodeURIComponent(query)}` : 'api/admin/users.php';
                const data = await apiRequest(url);
                const body = document.querySelector('#usersTable tbody');
                body.innerHTML = '';
                if (!data.data.length) {
                    body.innerHTML = '<tr><td colspan="5">No users found.</td></tr>';
                    return;
                }
                data.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.first_name} ${user.last_name}</td>
                        <td>${user.email}</td>
                        <td>${user.phone_number}</td>
                        <td>${user.created_at}</td>
                        <td>
                            <button class="btn-action edit" onclick="editUser(${user.id})">Edit</button>
                            <button class="btn-action decline" onclick="deleteUser(${user.id})">Delete</button>
                        </td>
                    `;
                    body.appendChild(row);
                });
            } catch (err) {
                showMsg('error', err.message);
            }
        }

        async function processRequest(requestId, action) {
            const message = action === 'accept'
                ? 'Confirm accept this signup request?'
                : 'Confirm decline this signup request?';
            if (!confirm(message)) return;
            try {
                const data = await apiRequest('api/admin/action.php', 'POST', { action, request_id: requestId });
                showMsg('success', data.message);
                loadPending();
                loadUsers();
            } catch (err) {
                showMsg('error', err.message);
            }
        }

        async function deleteUser(userId) {
            if (!confirm('Delete this user permanently? SMS notification will be sent.')) return;
            try {
                const data = await apiRequest('api/admin/action.php', 'POST', { action: 'delete', user_id: userId });
                showMsg('success', data.message);
                loadUsers();
                loadPending();
            } catch (err) {
                showMsg('error', err.message);
            }
        }

        async function editUser(userId) {
            const firstName = prompt('New first name:');
            if (firstName === null) return;
            const lastName = prompt('New last name:');
            if (lastName === null) return;
            const email = prompt('New email address:');
            if (email === null) return;
            const phone = prompt('New phone number:');
            if (phone === null) return;
            if (!confirm('Save changes for this user?')) return;
            try {
                const data = await apiRequest('api/admin/action.php', 'POST', {
                    action: 'update',
                    user_id: userId,
                    first_name: firstName.trim(),
                    last_name: lastName.trim(),
                    email: email.trim(),
                    phone_number: phone.trim(),
                });
                showMsg('success', data.message);
                loadUsers();
            } catch (err) {
                showMsg('error', err.message);
            }
        }

        loadPending();
        loadUsers();
    </script>
</body>
</html>
