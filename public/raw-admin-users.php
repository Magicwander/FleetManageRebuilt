<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /raw-login.php');
    exit;
}

// Database connection
$db = new PDO('sqlite:../database/database.sqlite');

// Handle user operations
if ($_POST) {
    if (isset($_POST['create_user'])) {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'customer';
        $status = $_POST['status'] ?? 'active';
        
        if ($name && $email && $password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO users (name, email, password, role, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))
            ");
            $stmt->execute([$name, $email, $hashed_password, $role, $status]);
        }
        header('Location: /raw-admin-users.php');
        exit;
    }
    
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'customer';
        $status = $_POST['status'] ?? 'active';
        
        $stmt = $db->prepare("
            UPDATE users 
            SET name = ?, email = ?, role = ?, status = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$name, $email, $role, $status, $user_id]);
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
        }
        
        header('Location: /raw-admin-users.php');
        exit;
    }
    
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'] ?? 0;
        // Don't allow deleting the current admin user
        if ($user_id != $_SESSION['user_id']) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        header('Location: /raw-admin-users.php');
        exit;
    }
}

// Get all users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user for editing if requested
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get user statistics
$stats = [];
$stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['role']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - FleetSync Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #f8fafc;
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: rgba(30, 41, 59, 0.8);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #4361ee;
            font-size: 24px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover, .nav-links a.active {
            background: rgba(67, 97, 238, 0.2);
            color: #4361ee;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(30, 41, 59, 0.8);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card h2 {
            color: #4361ee;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(15, 23, 42, 0.3);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h3 {
            color: #4361ee;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #cbd5e1;
            font-size: 12px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #f8fafc;
            font-size: 14px;
        }

        .btn {
            padding: 10px 16px;
            background: linear-gradient(135deg, #4361ee, #3a7bd5);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 8px;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th,
        .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
        }

        .table th {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
            font-weight: 600;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .role-badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .role-badge.admin {
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6;
        }

        .role-badge.customer {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .role-badge.driver {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .status-badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-badge.active {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status-badge.inactive {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>👥 User Management</h1>
            <div class="nav-links">
                <a href="/raw-dashboard.php">Dashboard</a>
                <a href="/raw-admin-trips.php">Trips</a>
                <a href="/raw-admin-users.php" class="active">Users</a>
                <a href="/raw-admin-reports.php">Reports</a>
            </div>
        </div>
        <a href="/raw-login.php?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h2><?= $edit_user ? '✏️ Edit User' : '➕ Create New User' ?></h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?= $stats['admin'] ?? 0 ?></h3>
                    <p>Admins</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['customer'] ?? 0 ?></h3>
                    <p>Customers</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['driver'] ?? 0 ?></h3>
                    <p>Drivers</p>
                </div>
            </div>
            
            <form method="POST">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($edit_user['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password <?= $edit_user ? '(leave blank to keep current)' : '' ?></label>
                    <input type="password" id="password" name="password" <?= $edit_user ? '' : 'required' ?>>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="customer" <?= ($edit_user && $edit_user['role'] === 'customer') ? 'selected' : '' ?>>Customer</option>
                            <option value="driver" <?= ($edit_user && $edit_user['role'] === 'driver') ? 'selected' : '' ?>>Driver</option>
                            <option value="admin" <?= ($edit_user && $edit_user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active" <?= ($edit_user && $edit_user['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($edit_user && $edit_user['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="<?= $edit_user ? 'update_user' : 'create_user' ?>" class="btn">
                    <?= $edit_user ? 'Update User' : 'Create User' ?>
                </button>
                <?php if ($edit_user): ?>
                    <a href="/raw-admin-users.php" class="btn btn-warning">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>👥 All Users</h2>
            <p><strong>Total Users:</strong> <?= count($users) ?></p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="role-badge <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                        <td><span class="status-badge <?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <a href="?edit=<?= $user['id'] ?>" class="btn btn-warning">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>