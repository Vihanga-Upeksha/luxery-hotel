<?php
/**
 * Admin Panel - Payment Management
 * Allows staff to view bookings and process remaining balance payments at check-in
 */

require 'db_connect.php';
require 'config.php';
require 'payment_functions.php';

// Simple authentication check (you should implement proper authentication)
$authenticated = false;
if (isset($_SESSION['admin_id'])) {
    $authenticated = true;
}

// For demo purposes, allow with password
if (!$authenticated && isset($_POST['admin_login'])) {
    $password = $_POST['admin_password'] ?? '';
    // Simple password check (use proper hashing in production)
    if ($password === 'admin123') {
        $_SESSION['admin_id'] = 1;
        $authenticated = true;
    }
}

// Handle payment completion
if ($authenticated && isset($_POST['mark_paid'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    if (markRemainingBalancePaid($pdo, $booking_id)) {
        $_SESSION['success_message'] = "Remaining balance marked as paid successfully!";
    } else {
        $_SESSION['error_message'] = "Error processing remaining balance payment.";
    }
    
    header("Location: admin_payments.php");
    exit;
}

// Fetch all bookings with pending balance
$bookings_query = "SELECT b.*, r.name as room_name 
                   FROM bookings b 
                   JOIN rooms r ON b.room_id = r.id 
                   WHERE b.remaining_balance > 0 
                   ORDER BY b.check_in ASC";

$stmt = $pdo->prepare($bookings_query);
$stmt->execute();
$pending_bookings = $stmt->fetchAll();

// Get today's check-ins
$today_checkins = [];
foreach ($pending_bookings as $booking) {
    if (strtotime($booking['check_in']) <= time() && strtotime($booking['check_in']) >= time() - 86400) {
        $today_checkins[] = $booking;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - The Villa Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #d4af37, #c9a227);
            color: white;
            border-radius: 8px;
        }
        .admin-header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .logout-btn {
            background-color: white;
            color: #d4af37;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #f9f9f9;
        }
        .section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .section h2 {
            margin-top: 0;
            color: #1f2937;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 0.75rem;
        }
        .booking-card {
            border: 1px solid #e0e0e0;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            background-color: #f9f9f9;
        }
        .booking-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background-color: white;
        }
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        .booking-info {
            flex: 1;
        }
        .booking-ref {
            font-weight: bold;
            font-size: 1.1rem;
            color: #d4af37;
        }
        .booking-guest {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        .booking-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 0.75rem;
            font-size: 0.9rem;
        }
        .detail-item {
            background: white;
            padding: 0.75rem;
            border-radius: 4px;
            border-left: 3px solid #d4af37;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
            font-size: 0.8rem;
            text-transform: uppercase;
            letterspacse: 0.5px;
        }
        .detail-value {
            font-size: 1rem;
            color: #1f2937;
            margin-top: 0.25rem;
        }
        .balance-highlight {
            color: #d97706;
            font-weight: bold;
        }
        .payment-action {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-success {
            background-color: #2ecc71;
            color: white;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .btn-info {
            background-color: #3498db;
            color: white;
        }
        .btn-info:hover {
            background-color: #2980b9;
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left-color: #2ecc71;
        }
        .alert-error {
            background-color: #fee;
            color: #a00;
            border-left-color: #e74c3c;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        table th {
            background-color: #f9f9f9;
            padding: 0.75rem;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #d4af37;
        }
        table td {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        table tr:hover {
            background-color: #f9f9f9;
        }
        .login-form {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .login-form h2 {
            text-align: center;
            color: #d4af37;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background-color: #d4af37;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #c9a227;
        }
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">The <span>Villa</span></div>
        <nav>
            <ul>
                <li><a href="../index.html">Home</a></li>
                <li><a href="index.php">Book Now</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-container">
        <?php if (!$authenticated): ?>
            <!-- Login Form -->
            <div class="login-form">
                <h2>Admin Login</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="admin_password" required placeholder="Enter admin password">
                    </div>
                    <button type="submit" name="admin_login" class="btn-login">Login</button>
                </form>
                <p style="text-align: center; color: #999; margin-top: 1rem; font-size: 0.9rem;">Demo Password: admin123</p>
            </div>

        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="admin-header">
                <h1><i class="fas fa-credit-card"></i> Payment Management</h1>
                <a href="?logout" class="logout-btn">Logout</a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Today's Check-ins -->
            <?php if (!empty($today_checkins)): ?>
                <div class="section">
                    <h2><i class="fas fa-calendar-check"></i> Today's Check-ins with Pending Balance</h2>
                    <?php foreach ($today_checkins as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-info">
                                    <div class="booking-ref">Booking #<?php echo $booking['id']; ?></div>
                                    <div class="booking-guest">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($booking['guest_name']); ?> 
                                        | <?php echo htmlspecialchars($booking['guest_email']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="booking-details-grid">
                                <div class="detail-item">
                                    <div class="detail-label">Room</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['room_name']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Check-in</div>
                                    <div class="detail-value"><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Remaining Balance</div>
                                    <div class="detail-value balance-highlight">$<?php echo number_format($booking['remaining_balance'], 2); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Total Booking</div>
                                    <div class="detail-value">$<?php echo number_format($booking['total_price'], 2); ?></div>
                                </div>
                            </div>

                            <div class="payment-action">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="mark_paid" class="btn-small btn-success" onclick="return confirm('Mark remaining balance ($<?php echo number_format($booking['remaining_balance'], 2); ?>) as paid?')">
                                        <i class="fas fa-check"></i> Mark Balance Paid
                                    </button>
                                </form>
                                <a href="confirmation.php?id=<?php echo $booking['id']; ?>" class="btn-small btn-info" style="text-decoration: none;">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- All Pending Payments -->
            <div class="section">
                <h2><i class="fas fa-hourglass-half"></i> All Bookings with Pending Balance</h2>
                
                <?php if (!empty($pending_bookings)): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Remaining Balance</th>
                                    <th>Payment Plan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_bookings as $booking): ?>
                                    <tr>
                                        <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                                        <td style="color: #d97706; font-weight: bold;">$<?php echo number_format($booking['remaining_balance'], 2); ?></td>
                                        <td><?php echo ucfirst($booking['payment_plan']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="mark_paid" class="btn-small btn-success" title="Mark as Paid">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No bookings with pending balance. All payments are up to date!</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <h4>The Villa Hotel</h4>
                <p>Experience the art of hospitality.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 The Villa Hotel Admin Panel. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
