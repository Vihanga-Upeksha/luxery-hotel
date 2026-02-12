<?php
require 'db_connect.php';
require 'config.php';
require 'payment_functions.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id) {
    $stmt = $pdo->prepare("
        SELECT b.*, r.name as room_name 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    // Get payment history
    $payment_history = getPaymentHistory($pdo, $booking_id);
}
else {
    $booking = null;
    $payment_history = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | The Villa</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-box {
            max-width: 600px;
            margin: 5rem auto;
            background: #fff;
            padding: 3rem;
            border: 1px solid #e0e0e0;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .success-icon {
            font-size: 4rem;
            color: #2ecc71;
            margin-bottom: 1rem;
        }
        .booking-details {
            margin-top: 2rem;
            text-align: left;
            border-top: 1px solid #eee;
            padding-top: 2rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            border-bottom: 1px dashed #eee;
            padding-bottom: 0.5rem;
        }
        .print-btn {
            margin-top: 2rem;
        }
        .payment-section {
            background-color: #f0f4f8;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
            border-left: 4px solid #d4af37;
        }
        .payment-highlight {
            color: #d4af37;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .deposit-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 4px;
            color: #92400e;
        }
        .payment-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .status-deposit-paid {
            background-color: #fbbf24;
            color: white;
        }
        .status-paid-full {
            background-color: #2ecc71;
            color: white;
        }
        .status-pending {
            background-color: #95a5a6;
            color: white;
        }
        .payment-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .payment-details-table th,
        .payment-details-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .payment-details-table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">The <span>Villa</span></div>
        <div class="mobile-toggle"><i class="fas fa-bars"></i></div>
        <nav>
            <ul>
                <li><a href="../index.html">Home</a></li>
                <li><a href="index.php" class="btn btn-primary">Book Again</a></li>
            </ul>
        </nav>
    </header>

    <div class="confirmation-box">
        <?php if ($booking): ?>
            <i class="fas fa-check-circle success-icon"></i>
            <h2>Booking Confirmed!</h2>
            <p>Thank you, <?php echo htmlspecialchars($booking['guest_name']); ?>. Your reservation has been successfully placed.</p>
            
            <div class="booking-details">
                <div class="detail-row">
                    <strong>Booking ID:</strong>
                    <span>#<?php echo $booking['id']; ?></span>
                </div>
                <div class="detail-row">
                    <strong>Room:</strong>
                    <span><?php echo htmlspecialchars($booking['room_name']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Check-In:</strong>
                    <span><?php echo date('F j, Y', strtotime($booking['check_in'])); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Check-Out:</strong>
                    <span><?php echo date('F j, Y', strtotime($booking['check_out'])); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Total Amount:</strong>
                    <span style="color: var(--color-secondary); font-weight: bold;">$<?php echo number_format($booking['total_price'], 2); ?></span>
                </div>
            </div>

            <!-- Payment Information Section -->
            <div class="payment-section">
                <h3 style="margin-top: 0; color: #1f2937;">Payment Details</h3>
                
                <div class="detail-row">
                    <strong>Payment Plan:</strong>
                    <span><?php echo ucwords(str_replace('_', ' ', $booking['payment_plan'])); ?> <?php echo ($booking['payment_plan'] === 'deposit') ? '(30% Deposit)' : ''; ?></span>
                </div>

                <div class="detail-row">
                    <strong>Payment Status:</strong>
                    <span class="payment-status status-<?php echo str_replace('_', '-', $booking['payment_status']); ?>">
                        <?php 
                            $status_text = ucwords(str_replace('_', ' ', $booking['payment_status']));
                            echo $status_text;
                        ?>
                    </span>
                </div>

                <?php if ($booking['payment_plan'] === 'deposit'): ?>
                    <div class="detail-row">
                        <strong>Deposit Paid (30%):</strong>
                        <span style="color: #2ecc71; font-weight: bold;">$<?php echo number_format($booking['deposit_amount'], 2); ?></span>
                    </div>

                    <div class="detail-row">
                        <strong>Remaining Balance (70%):</strong>
                        <span class="payment-highlight">$<?php echo number_format($booking['remaining_balance'], 2); ?></span>
                    </div>

                    <div class="deposit-warning">
                        <strong>⚠️ Important Notice:</strong>
                        <p style="margin: 0.5rem 0 0 0;">You have paid 30% of your booking as a deposit. The remaining 70% (<strong>$<?php echo number_format($booking['remaining_balance'], 2); ?></strong>) must be settled at check-in. You can also arrange to pay this amount online before your arrival date.</p>
                    </div>
                <?php else: ?>
                    <div class="detail-row">
                        <strong>Total Amount Paid:</strong>
                        <span style="color: #2ecc71; font-weight: bold;">$<?php echo number_format($booking['total_price'], 2); ?></span>
                    </div>

                    <div style="background-color: #d1fae5; padding: 1rem; border-radius: 4px; margin-top: 1rem; color: #065f46;">
                        <strong>✓ Payment Received</strong>
                        <p style="margin: 0.5rem 0 0 0;">Your full payment has been successfully received. No further payment is required.</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($payment_history)): ?>
                    <div style="margin-top: 1.5rem;">
                        <h4 style="margin-bottom: 0.75rem;">Payment History</h4>
                        <table class="payment-details-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_history as $payment): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y h:i A', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo ucwords(str_replace('_', ' ', $payment['payment_type'])); ?></td>
                                        <td><strong>$<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                        <td><span class="payment-status status-<?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <button onclick="window.print()" class="btn btn-primary print-btn">Print Receipt</button>
        <?php
else: ?>
            <h2 style="color: red;">Booking Not Found</h2>
            <p>We couldn't retrieve your booking details. Please contact support.</p>
        <?php
endif; ?>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <div class="logo">The <span>Villa</span></div>
                <p style="margin-top: 1rem; color: #ccc;">Experience the art of hospitality. Your luxury escape awaits.</p>
                <div class="social-icons" style="margin-top: 1rem;">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="../index.html">Home</a></li>
                    <li><a href="../about.html">About Us</a></li>
                    <li><a href="../rooms.html">Rooms & Suites</a></li>
                    <li><a href="../gallery.html">Gallery</a></li>
                    <li><a href="../contact.html">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Facilities</h4>
                <ul class="footer-links">
                    <li><a href="../facilities.html#spa">Luxury Spa</a></li>
                    <li><a href="../facilities.html#dining">Fine Dining</a></li>
                    <li><a href="../facilities.html#pool">Infinity Pool</a></li>
                    <li><a href="../facilities.html#fitness">Fitness Center</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact Us</h4>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt"></i> 123 Luxury Ave, Paradise City</li>
                    <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                    <li><i class="fas fa-envelope"></i> reservations@thevilla.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 The Villa Hotel. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
