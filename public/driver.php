<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'driver') {
    header('Location: /login.php');
    exit;
}

// Database connection
$db = new PDO('sqlite:../database/database.sqlite');

// Get driver information
$stmt = $db->prepare("SELECT * FROM drivers WHERE email = ?");
$stmt->execute([$_SESSION['user_name']]); // Using name as email for now
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

// If no driver profile exists, we'll create a basic one
if (!$driver) {
    // Get user email from users table
    $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $stmt = $db->prepare("SELECT * FROM drivers WHERE email = ?");
        $stmt->execute([$user['email']]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Get assigned trips
$driver_id = $driver ? $driver['id'] : 1; // Default to driver 1 if no profile
$stmt = $db->prepare("
    SELECT t.*, v.license_plate, v.make, v.model 
    FROM trips t 
    LEFT JOIN vehicles v ON t.vehicle_id = v.id 
    WHERE t.driver_id = ? 
    ORDER BY t.start_time DESC
");
$stmt->execute([$driver_id]);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle profile updates
if ($_POST && isset($_POST['update_profile'])) {
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $emergency_contact_name = $_POST['emergency_contact_name'] ?? '';
    $emergency_contact_phone = $_POST['emergency_contact_phone'] ?? '';
    
    if ($driver) {
        // Update existing profile
        $stmt = $db->prepare("
            UPDATE drivers 
            SET phone = ?, address = ?, emergency_contact_name = ?, emergency_contact_phone = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$phone, $address, $emergency_contact_name, $emergency_contact_phone, $driver['id']]);
    }
    header('Location: /driver.php');
    exit;
}

// Handle trip status updates
if ($_POST && isset($_POST['update_trip_status'])) {
    $trip_id = $_POST['trip_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $end_mileage = $_POST['end_mileage'] ?? null;
    
    if ($trip_id && $status) {
        $update_fields = "status = ?, updated_at = datetime('now')";
        $params = [$status];
        
        if ($status === 'completed' && $end_mileage) {
            $update_fields .= ", end_time = datetime('now'), end_mileage = ?";
            $params[] = $end_mileage;
        }
        
        $params[] = $trip_id;
        $stmt = $db->prepare("UPDATE trips SET $update_fields WHERE id = ? AND driver_id = ?");
        $params[] = $driver_id;
        $stmt->execute($params);
    }
    header('Location: /driver.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - FleetSync</title>
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
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .form-group input, .form-group textarea, .form-group select {
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
            margin-right: 10px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
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

        .status.in_progress {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .status.scheduled {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .profile-info {
            background: rgba(15, 23, 42, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .profile-info p {
            margin-bottom: 8px;
        }

        .profile-info strong {
            color: #4361ee;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>🚛 Driver Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>
        <a href="/login.php?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h2>👤 My Profile</h2>
            
            <?php if ($driver): ?>
            <div class="profile-info">
                <p><strong>Employee ID:</strong> <?= htmlspecialchars($driver['employee_id']) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($driver['email']) ?></p>
                <p><strong>License:</strong> <?= htmlspecialchars($driver['license_number']) ?></p>
                <p><strong>License Expiry:</strong> <?= date('M j, Y', strtotime($driver['license_expiry'])) ?></p>
                <p><strong>Status:</strong> <span class="status <?= $driver['status'] ?>"><?= ucfirst($driver['status']) ?></span></p>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($driver['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?= htmlspecialchars($driver['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= htmlspecialchars($driver['emergency_contact_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="emergency_contact_phone">Emergency Contact Phone</label>
                    <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= htmlspecialchars($driver['emergency_contact_phone'] ?? '') ?>">
                </div>

                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>

        <div class="card">
            <h2>🚚 My Assigned Trips</h2>
            <p><strong>Total Trips:</strong> <?= count($trips) ?></p>
            <p><strong>Active Trips:</strong> <?= count(array_filter($trips, fn($t) => in_array($t['status'], ['scheduled', 'in_progress']))) ?></p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Route</th>
                        <th>Vehicle</th>
                        <th>Start Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td>#<?= $trip['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($trip['start_location']) ?><br>
                            <small>→ <?= htmlspecialchars($trip['end_location']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($trip['license_plate'] ?? 'N/A') ?><br>
                            <small><?= htmlspecialchars($trip['make'] . ' ' . $trip['model']) ?></small>
                        </td>
                        <td><?= date('M j, Y H:i', strtotime($trip['start_time'])) ?></td>
                        <td><span class="status <?= $trip['status'] ?>"><?= ucfirst(str_replace('_', ' ', $trip['status'])) ?></span></td>
                        <td>
                            <?php if ($trip['status'] === 'scheduled'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" name="update_trip_status" class="btn btn-small btn-warning">Start Trip</button>
                                </form>
                            <?php elseif ($trip['status'] === 'in_progress'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                                    <input type="hidden" name="status" value="completed">
                                    <input type="number" name="end_mileage" placeholder="End Mileage" style="width: 100px; padding: 4px; margin-right: 5px;" required>
                                    <button type="submit" name="update_trip_status" class="btn btn-small btn-success">Complete</button>
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