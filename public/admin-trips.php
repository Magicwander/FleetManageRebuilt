<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

// Database connection
$db = new PDO('sqlite:../database/database.sqlite');

// Handle trip operations
if ($_POST) {
    if (isset($_POST['create_trip'])) {
        $user_id = $_POST['user_id'] ?? null;
        $vehicle_id = $_POST['vehicle_id'] ?? 1;
        $driver_id = $_POST['driver_id'] ?? 1;
        $start_location = $_POST['start_location'] ?? '';
        $end_location = $_POST['end_location'] ?? '';
        $start_time = $_POST['start_time'] ?? date('Y-m-d H:i:s');
        $distance = $_POST['distance'] ?? 0;
        $purpose = $_POST['purpose'] ?? '';
        
        $stmt = $db->prepare("
            INSERT INTO trips (user_id, vehicle_id, driver_id, start_location, end_location, start_time, start_mileage, distance, status, purpose, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'scheduled', ?, datetime('now'), datetime('now'))
        ");
        $stmt->execute([$user_id, $vehicle_id, $driver_id, $start_location, $end_location, $start_time, $distance, $purpose]);
        header('Location: /admin-trips.php');
        exit;
    }
    
    if (isset($_POST['update_trip'])) {
        $trip_id = $_POST['trip_id'] ?? 0;
        $user_id = $_POST['user_id'] ?? null;
        $vehicle_id = $_POST['vehicle_id'] ?? 1;
        $driver_id = $_POST['driver_id'] ?? 1;
        $start_location = $_POST['start_location'] ?? '';
        $end_location = $_POST['end_location'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $distance = $_POST['distance'] ?? 0;
        $status = $_POST['status'] ?? 'scheduled';
        $purpose = $_POST['purpose'] ?? '';
        
        $stmt = $db->prepare("
            UPDATE trips 
            SET user_id = ?, vehicle_id = ?, driver_id = ?, start_location = ?, end_location = ?, start_time = ?, distance = ?, status = ?, purpose = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$user_id, $vehicle_id, $driver_id, $start_location, $end_location, $start_time, $distance, $status, $purpose, $trip_id]);
        header('Location: /admin-trips.php');
        exit;
    }
    
    if (isset($_POST['delete_trip'])) {
        $trip_id = $_POST['trip_id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM trips WHERE id = ?");
        $stmt->execute([$trip_id]);
        header('Location: /admin-trips.php');
        exit;
    }
}

// Get all trips with related data
$stmt = $db->query("
    SELECT t.*, u.name as user_name, u.email as user_email, 
           d.first_name as driver_first_name, d.last_name as driver_last_name,
           v.license_plate, v.make, v.model
    FROM trips t 
    LEFT JOIN users u ON t.user_id = u.id 
    LEFT JOIN drivers d ON t.driver_id = d.id
    LEFT JOIN vehicles v ON t.vehicle_id = v.id
    ORDER BY t.created_at DESC
");
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get users for dropdown
$stmt = $db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get drivers for dropdown
$stmt = $db->query("SELECT id, first_name, last_name, employee_id FROM drivers WHERE status = 'active' ORDER BY first_name");
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get vehicles for dropdown
$stmt = $db->query("SELECT id, license_plate, make, model FROM vehicles WHERE status = 'active' ORDER BY license_plate");
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get trip for editing if requested
$edit_trip = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM trips WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_trip = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Management - FleetSync Admin</title>
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

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .form-group input, .form-group select, .form-group textarea {
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

        .status {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .status.completed {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status.in_progress {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .status.scheduled {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .status.cancelled {
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
            <h1>🚛 Trip Management</h1>
            <div class="nav-links">
                <a href="/dashboard.php">Dashboard</a>
                <a href="/admin-trips.php" class="active">Trips</a>
                <a href="/admin-users.php">Users</a>
                <a href="/admin-reports.php">Reports</a>
            </div>
        </div>
        <a href="/login.php?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h2><?= $edit_trip ? '✏️ Edit Trip' : '➕ Create New Trip' ?></h2>
            <form method="POST">
                <?php if ($edit_trip): ?>
                    <input type="hidden" name="trip_id" value="<?= $edit_trip['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="user_id">Customer</label>
                    <select id="user_id" name="user_id">
                        <option value="">Select Customer (Optional)</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($edit_trip && $edit_trip['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="vehicle_id">Vehicle</label>
                        <select id="vehicle_id" name="vehicle_id" required>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?= $vehicle['id'] ?>" <?= ($edit_trip && $edit_trip['vehicle_id'] == $vehicle['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vehicle['license_plate']) ?> - <?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="driver_id">Driver</label>
                        <select id="driver_id" name="driver_id" required>
                            <?php foreach ($drivers as $driver): ?>
                                <option value="<?= $driver['id'] ?>" <?= ($edit_trip && $edit_trip['driver_id'] == $driver['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?> (<?= htmlspecialchars($driver['employee_id']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_location">Start Location</label>
                        <input type="text" id="start_location" name="start_location" value="<?= htmlspecialchars($edit_trip['start_location'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_location">End Location</label>
                        <input type="text" id="end_location" name="end_location" value="<?= htmlspecialchars($edit_trip['end_location'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="datetime-local" id="start_time" name="start_time" value="<?= $edit_trip ? date('Y-m-d\TH:i', strtotime($edit_trip['start_time'])) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="distance">Distance (km)</label>
                        <input type="number" id="distance" name="distance" step="0.1" value="<?= $edit_trip['distance'] ?? '' ?>">
                    </div>
                </div>

                <?php if ($edit_trip): ?>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="scheduled" <?= ($edit_trip['status'] === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                        <option value="in_progress" <?= ($edit_trip['status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= ($edit_trip['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($edit_trip['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="purpose">Purpose</label>
                    <input type="text" id="purpose" name="purpose" value="<?= htmlspecialchars($edit_trip['purpose'] ?? '') ?>">
                </div>

                <button type="submit" name="<?= $edit_trip ? 'update_trip' : 'create_trip' ?>" class="btn">
                    <?= $edit_trip ? 'Update Trip' : 'Create Trip' ?>
                </button>
                <?php if ($edit_trip): ?>
                    <a href="/admin-trips.php" class="btn btn-warning">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>🚚 All Trips</h2>
            <p><strong>Total Trips:</strong> <?= count($trips) ?></p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td>#<?= $trip['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($trip['user_name'] ?? 'N/A') ?><br>
                            <small><?= htmlspecialchars($trip['user_email'] ?? '') ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($trip['driver_first_name'] . ' ' . $trip['driver_last_name']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($trip['license_plate']) ?><br>
                            <small><?= htmlspecialchars($trip['make'] . ' ' . $trip['model']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($trip['start_location']) ?><br>
                            <small>→ <?= htmlspecialchars($trip['end_location']) ?></small>
                        </td>
                        <td><span class="status <?= $trip['status'] ?>"><?= ucfirst(str_replace('_', ' ', $trip['status'])) ?></span></td>
                        <td><?= date('M j, Y', strtotime($trip['created_at'])) ?></td>
                        <td>
                            <a href="?edit=<?= $trip['id'] ?>" class="btn btn-warning">Edit</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                                <button type="submit" name="delete_trip" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this trip?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>