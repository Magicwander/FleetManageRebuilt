<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /raw-login.php');
    exit;
}

// Database connection
$db = new PDO('sqlite:../database/database.sqlite');

// Get all trips with user information
$stmt = $db->query("
    SELECT t.*, u.name as user_name, u.email as user_email 
    FROM trips t 
    LEFT JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC
");
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all users
$stmt = $db->query("SELECT * FROM users ORDER BY name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FleetSync</title>
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
            grid-template-columns: 1fr 1fr;
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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table th {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
            font-weight: 600;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status.completed {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status.pending {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .status.cancelled {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
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
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>🚛 Admin Dashboard</h1>
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <a href="/raw-dashboard.php" style="color: #4361ee; text-decoration: none; padding: 8px 16px; background: rgba(67, 97, 238, 0.2); border-radius: 6px;">Dashboard</a>
                <a href="/raw-admin-trips.php" style="color: #cbd5e1; text-decoration: none; padding: 8px 16px; border-radius: 6px;">Trips</a>
                <a href="/raw-admin-users.php" style="color: #cbd5e1; text-decoration: none; padding: 8px 16px; border-radius: 6px;">Users</a>
                <a href="/raw-admin-reports.php" style="color: #cbd5e1; text-decoration: none; padding: 8px 16px; border-radius: 6px;">Reports</a>
            </div>
            <p style="margin-top: 10px;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>
        <a href="/raw-login.php?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h2>📊 System Statistics</h2>
            <p><strong>Total Trips:</strong> <?= count($trips) ?></p>
            <p><strong>Total Users:</strong> <?= count($users) ?></p>
            <p><strong>Active Orders:</strong> <?= count(array_filter($trips, fn($t) => $t['status'] === 'pending')) ?></p>
        </div>

        <div class="card">
            <h2>👥 User Management</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="role-badge <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>🚚 All User Orders/Trips</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Distance</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $trip): ?>
                <tr>
                    <td>#<?= $trip['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($trip['user_name'] ?? 'Unknown') ?><br>
                        <small><?= htmlspecialchars($trip['user_email'] ?? 'N/A') ?></small>
                    </td>
                    <td><?= htmlspecialchars($trip['start_location']) ?></td>
                    <td><?= htmlspecialchars($trip['end_location']) ?></td>
                    <td><?= $trip['distance'] ?> km</td>
                    <td><span class="status <?= $trip['status'] ?>"><?= ucfirst($trip['status']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($trip['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>