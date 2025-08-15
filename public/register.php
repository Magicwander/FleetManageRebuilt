<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: /dashboard.php');
    } elseif ($_SESSION['user_role'] === 'driver') {
        header('Location: /driver.php');
    } else {
        header('Location: /customer.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Database connection
        $db = new PDO('sqlite:../database/database.sqlite');
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email address is already registered';
        } else {
            // Create user account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO users (name, email, password, role, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, 'active', datetime('now'), datetime('now'))
            ");
            
            if ($stmt->execute([$name, $email, $hashed_password, $role])) {
                $user_id = $db->lastInsertId();
                
                // If registering as driver, create driver profile
                if ($role === 'driver') {
                    $names = explode(' ', $name, 2);
                    $first_name = $names[0];
                    $last_name = isset($names[1]) ? $names[1] : '';
                    $employee_id = 'EMP' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                    
                    $stmt = $db->prepare("
                        INSERT INTO drivers (first_name, last_name, email, phone, employee_id, license_number, license_expiry, status, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, 'PENDING', '2025-12-31', 'active', datetime('now'), datetime('now'))
                    ");
                    $stmt->execute([$first_name, $last_name, $email, $phone, $employee_id]);
                }
                
                $success = 'Account created successfully! You can now log in.';
            } else {
                $error = 'Failed to create account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleetSync | Register</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #4361ee;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .logo p {
            color: #94a3b8;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cbd5e1;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px 20px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #f8fafc;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .register-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4361ee, #3a7bd5);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #22c55e;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .role-info {
            background: rgba(67, 97, 238, 0.1);
            border: 1px solid rgba(67, 97, 238, 0.2);
            color: #4361ee;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-top: 5px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 14px;
        }

        .login-link a {
            color: #4361ee;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🚛 FleetSync</h1>
            <p>Create Your Account</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="role">Account Type *</label>
                    <select id="role" name="role" required>
                        <option value="customer" <?= ($_POST['role'] ?? 'customer') === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="driver" <?= ($_POST['role'] ?? '') === 'driver' ? 'selected' : '' ?>>Driver</option>
                    </select>
                    <div class="role-info">
                        <strong>Customer:</strong> Book and manage trips<br>
                        <strong>Driver:</strong> Manage profile and assigned trips
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Optional">
                </div>
            </div>

            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="/login.php">Sign in here</a>
        </div>
    </div>
</body>
</html>