<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: /raw-login.php');
    exit;
}

// Database connection
$db = new PDO('sqlite:../database/database.sqlite');

// Get user's trips
$stmt = $db->prepare("SELECT * FROM trips WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new trip creation
if ($_POST && isset($_POST['create_trip'])) {
    $origin = $_POST['origin'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $distance = $_POST['distance'] ?? 0;
    
    if ($origin && $destination) {
        $stmt = $db->prepare("
            INSERT INTO trips (user_id, origin, destination, distance, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'pending', datetime('now'), datetime('now'))
        ");
        $stmt->execute([$_SESSION['user_id'], $origin, $destination, $distance]);
        header('Location: /raw-customer.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - FleetSync</title>
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
            grid-template-columns: 1fr 2fr;
            gap: 30px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #f8fafc;
            font-size: 14px;
        }

        .btn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #4361ee, #3a7bd5);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
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
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>🚛 Customer Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>
        <a href="/raw-login.php?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h2>📝 Create New Trip</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="origin">Origin</label>
                    <input type="text" id="origin" name="origin" required>
                </div>

                <div class="form-group">
                    <label for="destination">Destination</label>
                    <input type="text" id="destination" name="destination" required>
                </div>

                <div class="form-group">
                    <label for="distance">Distance (km)</label>
                    <input type="number" id="distance" name="distance" step="0.1" required>
                </div>

                <button type="submit" name="create_trip" class="btn">Create Trip</button>
            </form>
        </div>

        <div class="card">
            <h2>🚚 My Orders/Trips</h2>
            <p><strong>Total Orders:</strong> <?= count($trips) ?></p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                        <td><?= htmlspecialchars($trip['origin']) ?></td>
                        <td><?= htmlspecialchars($trip['destination']) ?></td>
                        <td><?= $trip['distance'] ?> km</td>
                        <td><span class="status <?= $trip['status'] ?>"><?= ucfirst($trip['status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($trip['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>