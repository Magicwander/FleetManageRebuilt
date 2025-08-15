<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /raw-login.php');
    exit;
}

// Database connection
$db = new PDO('sqlite:../database/database.sqlite');

// Get date range from request
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Trip Statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_trips,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_trips,
        COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_trips,
        COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_trips,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_trips,
        SUM(CASE WHEN distance IS NOT NULL THEN distance ELSE 0 END) as total_distance
    FROM trips 
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$trip_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Driver Performance
$stmt = $db->prepare("
    SELECT 
        d.first_name, d.last_name, d.employee_id,
        COUNT(t.id) as total_trips,
        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_trips,
        SUM(CASE WHEN t.distance IS NOT NULL THEN t.distance ELSE 0 END) as total_distance
    FROM drivers d
    LEFT JOIN trips t ON d.id = t.driver_id AND DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY d.id
    ORDER BY completed_trips DESC
");
$stmt->execute([$start_date, $end_date]);
$driver_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vehicle Usage
$stmt = $db->prepare("
    SELECT 
        v.license_plate, v.make, v.model,
        COUNT(t.id) as total_trips,
        SUM(CASE WHEN t.distance IS NOT NULL THEN t.distance ELSE 0 END) as total_distance
    FROM vehicles v
    LEFT JOIN trips t ON v.id = t.vehicle_id AND DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY v.id
    ORDER BY total_trips DESC
");
$stmt->execute([$start_date, $end_date]);
$vehicle_usage = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Customer Activity
$stmt = $db->prepare("
    SELECT 
        u.name, u.email,
        COUNT(t.id) as total_bookings,
        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_bookings
    FROM users u
    LEFT JOIN trips t ON u.id = t.user_id AND DATE(t.created_at) BETWEEN ? AND ?
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY total_bookings DESC
");
$stmt->execute([$start_date, $end_date]);
$customer_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daily Trip Counts
$stmt = $db->prepare("
    SELECT 
        DATE(created_at) as trip_date,
        COUNT(*) as trip_count,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
    FROM trips 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY trip_date DESC
    LIMIT 30
");
$stmt->execute([$start_date, $end_date]);
$daily_trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle report export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="fleet_report_' . $start_date . '_to_' . $end_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Trip Statistics
    fputcsv($output, ['Fleet Management Report - ' . $start_date . ' to ' . $end_date]);
    fputcsv($output, []);
    fputcsv($output, ['TRIP STATISTICS']);
    fputcsv($output, ['Total Trips', $trip_stats['total_trips']]);
    fputcsv($output, ['Completed Trips', $trip_stats['completed_trips']]);
    fputcsv($output, ['In Progress Trips', $trip_stats['in_progress_trips']]);
    fputcsv($output, ['Scheduled Trips', $trip_stats['scheduled_trips']]);
    fputcsv($output, ['Cancelled Trips', $trip_stats['cancelled_trips']]);
    fputcsv($output, ['Total Distance (km)', $trip_stats['total_distance']]);
    fputcsv($output, []);
    
    // Driver Performance
    fputcsv($output, ['DRIVER PERFORMANCE']);
    fputcsv($output, ['Driver Name', 'Employee ID', 'Total Trips', 'Completed Trips', 'Total Distance (km)']);
    foreach ($driver_performance as $driver) {
        fputcsv($output, [
            $driver['first_name'] . ' ' . $driver['last_name'],
            $driver['employee_id'],
            $driver['total_trips'],
            $driver['completed_trips'],
            $driver['total_distance']
        ]);
    }
    fputcsv($output, []);
    
    // Vehicle Usage
    fputcsv($output, ['VEHICLE USAGE']);
    fputcsv($output, ['License Plate', 'Make/Model', 'Total Trips', 'Total Distance (km)']);
    foreach ($vehicle_usage as $vehicle) {
        fputcsv($output, [
            $vehicle['license_plate'],
            $vehicle['make'] . ' ' . $vehicle['model'],
            $vehicle['total_trips'],
            $vehicle['total_distance']
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - FleetSync Admin</title>
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

        .report-controls {
            background: rgba(30, 41, 59, 0.8);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .report-controls input {
            padding: 10px 12px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #f8fafc;
        }

        .btn {
            padding: 10px 16px;
            background: linear-gradient(135deg, #4361ee, #3a7bd5);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(30, 41, 59, 0.8);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #4361ee;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-card p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .reports-grid {
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
            font-size: 18px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
        }

        .table th {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
            font-weight: 600;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .progress-bar {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            background: linear-gradient(135deg, #4361ee, #3a7bd5);
            height: 100%;
            transition: width 0.3s ease;
        }

        .chart-container {
            background: rgba(15, 23, 42, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>📊 Reports & Analytics</h1>
            <div class="nav-links">
                <a href="/raw-dashboard.php">Dashboard</a>
                <a href="/raw-admin-trips.php">Trips</a>
                <a href="/raw-admin-users.php">Users</a>
                <a href="/raw-admin-reports.php" class="active">Reports</a>
            </div>
        </div>
        <a href="/raw-login.php?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="report-controls">
        <form method="GET" style="display: flex; gap: 15px; align-items: center;">
            <label for="start_date" style="color: #cbd5e1;">From:</label>
            <input type="date" id="start_date" name="start_date" value="<?= $start_date ?>">
            
            <label for="end_date" style="color: #cbd5e1;">To:</label>
            <input type="date" id="end_date" name="end_date" value="<?= $end_date ?>">
            
            <button type="submit" class="btn">Update Report</button>
            <a href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&export=csv" class="btn btn-success">Export CSV</a>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $trip_stats['total_trips'] ?></h3>
            <p>Total Trips</p>
        </div>
        <div class="stat-card">
            <h3><?= $trip_stats['completed_trips'] ?></h3>
            <p>Completed Trips</p>
        </div>
        <div class="stat-card">
            <h3><?= $trip_stats['in_progress_trips'] ?></h3>
            <p>In Progress</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($trip_stats['total_distance'], 1) ?> km</h3>
            <p>Total Distance</p>
        </div>
        <div class="stat-card">
            <h3><?= $trip_stats['total_trips'] > 0 ? round(($trip_stats['completed_trips'] / $trip_stats['total_trips']) * 100, 1) : 0 ?>%</h3>
            <p>Completion Rate</p>
        </div>
    </div>

    <div class="reports-grid">
        <div class="card">
            <h2>🚗 Driver Performance</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Trips</th>
                        <th>Completed</th>
                        <th>Distance</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($driver_performance, 0, 10) as $driver): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?><br>
                            <small style="color: #64748b;"><?= htmlspecialchars($driver['employee_id']) ?></small>
                        </td>
                        <td><?= $driver['total_trips'] ?></td>
                        <td><?= $driver['completed_trips'] ?></td>
                        <td><?= number_format($driver['total_distance'], 1) ?> km</td>
                        <td>
                            <?php 
                            $rate = $driver['total_trips'] > 0 ? ($driver['completed_trips'] / $driver['total_trips']) * 100 : 0;
                            echo round($rate, 1) . '%';
                            ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $rate ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>🚛 Vehicle Usage</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Trips</th>
                        <th>Distance</th>
                        <th>Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $max_trips = max(array_column($vehicle_usage, 'total_trips'));
                    foreach (array_slice($vehicle_usage, 0, 10) as $vehicle): 
                    ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($vehicle['license_plate']) ?><br>
                            <small style="color: #64748b;"><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?></small>
                        </td>
                        <td><?= $vehicle['total_trips'] ?></td>
                        <td><?= number_format($vehicle['total_distance'], 1) ?> km</td>
                        <td>
                            <?php 
                            $utilization = $max_trips > 0 ? ($vehicle['total_trips'] / $max_trips) * 100 : 0;
                            echo round($utilization, 1) . '%';
                            ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $utilization ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>👥 Customer Activity</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Bookings</th>
                        <th>Completed</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($customer_activity, 0, 10) as $customer): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($customer['name']) ?><br>
                            <small style="color: #64748b;"><?= htmlspecialchars($customer['email']) ?></small>
                        </td>
                        <td><?= $customer['total_bookings'] ?></td>
                        <td><?= $customer['completed_bookings'] ?></td>
                        <td>
                            <?php 
                            $rate = $customer['total_bookings'] > 0 ? ($customer['completed_bookings'] / $customer['total_bookings']) * 100 : 0;
                            echo round($rate, 1) . '%';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>📅 Daily Trip Activity</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Trips</th>
                        <th>Completed</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daily_trips as $day): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($day['trip_date'])) ?></td>
                        <td><?= $day['trip_count'] ?></td>
                        <td><?= $day['completed_count'] ?></td>
                        <td>
                            <?php 
                            $rate = $day['trip_count'] > 0 ? ($day['completed_count'] / $day['trip_count']) * 100 : 0;
                            echo round($rate, 1) . '%';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>