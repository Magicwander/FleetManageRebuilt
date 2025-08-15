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

// Get trip for editing if requested
$edit_trip = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM trips WHERE id = ? AND user_id = ? AND status = 'scheduled'");
    $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
    $edit_trip = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle trip operations
if ($_POST) {
    if (isset($_POST['create_trip'])) {
        $origin = $_POST['origin'] ?? '';
        $destination = $_POST['destination'] ?? '';
        $distance = $_POST['distance'] ?? 0;
        $start_time = $_POST['start_time'] ?? date('Y-m-d H:i:s', strtotime('+1 hour'));
        $purpose = $_POST['purpose'] ?? 'Customer Request';
        
        if ($origin && $destination) {
            $stmt = $db->prepare("
                INSERT INTO trips (user_id, vehicle_id, driver_id, start_location, end_location, start_time, start_mileage, distance, status, purpose, created_at, updated_at) 
                VALUES (?, 1, 1, ?, ?, ?, 0, ?, 'scheduled', ?, datetime('now'), datetime('now'))
            ");
            $stmt->execute([$_SESSION['user_id'], $origin, $destination, $start_time, $distance, $purpose]);
            header('Location: /raw-customer.php');
            exit;
        }
    }
    
    if (isset($_POST['update_trip'])) {
        $trip_id = $_POST['trip_id'] ?? 0;
        $origin = $_POST['origin'] ?? '';
        $destination = $_POST['destination'] ?? '';
        $distance = $_POST['distance'] ?? 0;
        $start_time = $_POST['start_time'] ?? '';
        $purpose = $_POST['purpose'] ?? '';
        
        // Only allow editing if trip is still scheduled
        $stmt = $db->prepare("
            UPDATE trips 
            SET start_location = ?, end_location = ?, distance = ?, start_time = ?, purpose = ?, updated_at = datetime('now')
            WHERE id = ? AND user_id = ? AND status = 'scheduled'
        ");
        $stmt->execute([$origin, $destination, $distance, $start_time, $purpose, $trip_id, $_SESSION['user_id']]);
        header('Location: /raw-customer.php');
        exit;
    }
    
    if (isset($_POST['cancel_trip'])) {
        $trip_id = $_POST['trip_id'] ?? 0;
        
        // Only allow cancelling if trip is scheduled
        $stmt = $db->prepare("
            UPDATE trips 
            SET status = 'cancelled', updated_at = datetime('now')
            WHERE id = ? AND user_id = ? AND status = 'scheduled'
        ");
        $stmt->execute([$trip_id, $_SESSION['user_id']]);
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
            <h2><?= $edit_trip ? '✏️ Edit Booking' : '📝 Create New Booking' ?></h2>
            <form method="POST">
                <?php if ($edit_trip): ?>
                    <input type="hidden" name="trip_id" value="<?= $edit_trip['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="origin">Origin</label>
                    <input type="text" id="origin" name="origin" value="<?= htmlspecialchars($edit_trip['start_location'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="destination">Destination</label>
                    <input type="text" id="destination" name="destination" value="<?= htmlspecialchars($edit_trip['end_location'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="distance">Distance (km)</label>
                    <input type="number" id="distance" name="distance" step="0.1" value="<?= $edit_trip['distance'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Preferred Start Time</label>
                    <input type="datetime-local" id="start_time" name="start_time" value="<?= $edit_trip ? date('Y-m-d\TH:i', strtotime($edit_trip['start_time'])) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="purpose">Purpose</label>
                    <input type="text" id="purpose" name="purpose" value="<?= htmlspecialchars($edit_trip['purpose'] ?? '') ?>" placeholder="e.g., Business meeting, Personal travel">
                </div>

                <button type="submit" name="<?= $edit_trip ? 'update_trip' : 'create_trip' ?>" class="btn">
                    <?= $edit_trip ? 'Update Booking' : 'Create Booking' ?>
                </button>
                <?php if ($edit_trip): ?>
                    <a href="/raw-customer.php" class="btn" style="background: #6b7280;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>🚚 My Orders/Trips</h2>
            <p><strong>Total Orders:</strong> <?= count($trips) ?></p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Route</th>
                        <th>Distance</th>
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
                            <small style="color: #64748b;">→ <?= htmlspecialchars($trip['end_location']) ?></small>
                        </td>
                        <td><?= $trip['distance'] ?> km</td>
                        <td><?= date('M j, Y H:i', strtotime($trip['start_time'])) ?></td>
                        <td><span class="status <?= $trip['status'] ?>"><?= ucfirst(str_replace('_', ' ', $trip['status'])) ?></span></td>
                        <td>
                            <?php if ($trip['status'] === 'scheduled'): ?>
                                <a href="?edit=<?= $trip['id'] ?>" class="btn" style="padding: 6px 12px; font-size: 12px; background: #f59e0b;">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                                    <button type="submit" name="cancel_trip" class="btn" style="padding: 6px 12px; font-size: 12px; background: #ef4444;" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #64748b; font-size: 12px;">No actions</span>
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