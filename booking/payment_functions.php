<?php
/**
 * Payment Functions Module
 * Handles payment calculations, recording, and deposit logic
 */

require_once 'config.php';

/**
 * Calculate deposit amount
 * @param float $total_price Total booking price
 * @return float Deposit amount (30% of total)
 */
function calculateDeposit($total_price) {
    return round($total_price * DEPOSIT_PERCENTAGE, 2);
}

/**
 * Calculate remaining balance
 * @param float $total_price Total booking price
 * @param float $deposit_amount Amount paid as deposit
 * @return float Remaining balance
 */
function calculateRemainingBalance($total_price, $deposit_amount) {
    return round($total_price - $deposit_amount, 2);
}

/**
 * Record payment transaction in database
 * @param PDO $pdo Database connection
 * @param int $booking_id Booking ID
 * @param float $amount Payment amount
 * @param string $payment_type Type of payment (deposit, full_payment, remaining_balance)
 * @param string $payment_method Payment method used
 * @param string $transaction_id Transaction ID from payment gateway
 * @return bool Success status
 */
function recordPayment($pdo, $booking_id, $amount, $payment_type = 'full_payment', $payment_method = 'manual', $transaction_id = '') {
    try {
        $sql = "INSERT INTO payments (booking_id, amount, payment_type, payment_method, transaction_id, status) 
                VALUES (?, ?, ?, ?, ?, 'completed')";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$booking_id, $amount, $payment_type, $payment_method, $transaction_id]);
    } catch (PDOException $e) {
        error_log("Payment Recording Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update booking payment status
 * @param PDO $pdo Database connection
 * @param int $booking_id Booking ID
 * @param string $payment_status New payment status
 * @param float $amount_paid Amount paid
 * @param float $remaining_balance Remaining balance
 * @return bool Success status
 */
function updateBookingPaymentStatus($pdo, $booking_id, $payment_status, $amount_paid, $remaining_balance) {
    try {
        $sql = "UPDATE bookings 
                SET payment_status = ?, amount_paid = ?, remaining_balance = ?, updated_at = NOW()
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$payment_status, $amount_paid, $remaining_balance, $booking_id]);
    } catch (PDOException $e) {
        error_log("Update Booking Status Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get booking payment details
 * @param PDO $pdo Database connection
 * @param int $booking_id Booking ID
 * @return array Booking details with payment info
 */
function getBookingPaymentDetails($pdo, $booking_id) {
    try {
        $sql = "SELECT b.*, r.name as room_name, r.price_per_night
                FROM bookings b 
                JOIN rooms r ON b.room_id = r.id 
                WHERE b.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get Booking Details Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get total amount paid for a booking
 * @param PDO $pdo Database connection
 * @param int $booking_id Booking ID
 * @return float Total amount paid
 */
function getTotalPaidForBooking($pdo, $booking_id) {
    try {
        $sql = "SELECT SUM(amount) as total_paid FROM payments 
                WHERE booking_id = ? AND status = 'completed'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        $result = $stmt->fetch();
        return $result['total_paid'] ?? 0;
    } catch (PDOException $e) {
        error_log("Get Total Paid Error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get payment history for a booking
 * @param PDO $pdo Database connection
 * @param int $booking_id Booking ID
 * @return array Payment history records
 */
function getPaymentHistory($pdo, $booking_id) {
    try {
        $sql = "SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get Payment History Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Send booking confirmation email
 * @param string $guest_email Guest email address
 * @param string $guest_name Guest name
 * @param array $booking_details Booking details array
 * @return bool Success status
 */
function sendBookingConfirmationEmail($guest_email, $guest_name, $booking_details) {
    try {
        $subject = "Booking Confirmation - The Villa Hotel - Ref: #" . ($booking_details['id'] ?? '');

        // Prepare email content based on payment plan
        $payment_info = "";
        if (!empty($booking_details['payment_plan']) && $booking_details['payment_plan'] === 'deposit') {
            $deposit_pct = round(DEPOSIT_PERCENTAGE * 100);
            $payment_info = "<h3>Payment Plan: Deposit Payment (" . $deposit_pct . "% Upfront)</h3>" .
                "<p><strong>Deposit Amount Due Now:</strong> " . CURRENCY_SYMBOL . number_format($booking_details['deposit_amount'] ?? 0, 2) . "</p>" .
                "<p><strong>Remaining Balance (Due at Check-in):</strong> " . CURRENCY_SYMBOL . number_format($booking_details['remaining_balance'] ?? 0, 2) . "</p>" .
                "<p style='color: #d97706; font-size: 14px;'><strong>Important:</strong> Please note that the remaining " . number_format((($booking_details['remaining_balance'] ?? 0) / max(1, ($booking_details['total_price'] ?? 1))) * 100, 0) . "% of your total booking amount must be settled at the time of check-in.</p>";
        } else {
            $payment_info = "<h3>Payment Plan: Full Payment</h3>" .
                "<p><strong>Total Amount Paid:</strong> " . CURRENCY_SYMBOL . number_format($booking_details['total_price'] ?? 0, 2) . "</p>";
        }

        // Build dashboard URL (user should login to view booking details)
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $dashboard_link = $scheme . '://' . $host . '/dashboard/villa/booking/dashboard.php?booking=' . urlencode($booking_details['id'] ?? '');

        $html_body = '<html><head><meta charset="utf-8"><style>' .
            'body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }' .
            '.container { max-width: 600px; margin: 0 auto; padding: 20px; }' .
            '.header { background-color: #d4af37; padding: 20px; text-align: center; color: white; }' .
            '.header h1 { margin: 0; font-size: 28px; }' .
            '.content { background-color: #f9f9f9; padding: 20px; margin: 10px 0; border-radius: 5px; }' .
            '.booking-details { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #d4af37; }' .
            '.detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #ddd; }' .
            '.detail-row:last-child { border-bottom: none; }' .
            '.label { font-weight: bold; }' .
            '.footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }' .
            '.btn { display: inline-block; padding: 10px 20px; background-color: #d4af37; color: white; text-decoration: none; border-radius: 3px; }' .
        '</style></head><body>' .
            '<div class="container">' .
                '<div class="header"><h1>The Villa Hotel</h1><p>Your Luxury Retreat Awaits</p></div>' .
                '<div class="content">' .
                    '<h2>Dear ' . htmlspecialchars($guest_name) . ',</h2>' .
                    '<p>Thank you for booking with us! We are delighted to confirm your reservation at The Villa Hotel.</p>' .
                    '<div class="booking-details">' .
                        '<h3>Booking Confirmation Details</h3>' .
                        '<div class="detail-row"><span class="label">Booking Reference:</span><span>#' . ($booking_details['id'] ?? '') . '</span></div>' .
                        '<div class="detail-row"><span class="label">Room:</span><span>' . htmlspecialchars($booking_details['room_name'] ?? '') . '</span></div>' .
                        '<div class="detail-row"><span class="label">Check-In Date:</span><span>' . (isset($booking_details['check_in']) ? date('F j, Y', strtotime($booking_details['check_in'])) : '') . '</span></div>' .
                        '<div class="detail-row"><span class="label">Check-Out Date:</span><span>' . (isset($booking_details['check_out']) ? date('F j, Y', strtotime($booking_details['check_out'])) : '') . '</span></div>' .
                        '<div class="detail-row"><span class="label">Total Booking Amount:</span><span>' . CURRENCY_SYMBOL . number_format($booking_details['total_price'] ?? 0, 2) . '</span></div>' .
                    '</div>' .
                    $payment_info .
                    '<div class="booking-details"><h3>What\'s Next?</h3>' .
                        '<p>1. Complete your payment to secure this reservation.</p>' .
                        '<p>2. You will receive a payment confirmation once processed.</p>' .
                        (isset($booking_details['payment_plan']) && $booking_details['payment_plan'] === 'deposit' ? '<p>3. The remaining balance is due at check-in. You have the option to pay online or upon arrival.</p>' : '') .
                        '<p>If you have any questions, feel free to contact us.</p>' .
                    '</div>' .
                    '<div style="text-align: center; margin: 20px 0;">' .
                        '<a href="' . htmlspecialchars($dashboard_link) . '" class="btn">View Your Booking Online</a>' .
                    '</div>' .
                '</div>' .
                '<div class="footer"><p><strong>The Villa Hotel</strong></p><p>' . HOTEL_ADDRESS . '</p><p>Phone: ' . HOTEL_PHONE . '</p><p>Email: ' . HOTEL_EMAIL . '</p></div>' .
            '</div>' .
        '</body></html>';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . HOTEL_EMAIL . "\r\n";

        // Using PHP mail function (basic implementation)
        // For production, use PHPMailer or similar library
        return mail($guest_email, $subject, $html_body, $headers);

    } catch (Exception $e) {
        error_log("Email Send Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send payment reminder email for remaining balance
 * @param string $guest_email Guest email address
 * @param string $guest_name Guest name
 * @param array $booking_details Booking details array
 * @return bool Success status
 */
function sendPaymentReminderEmail($guest_email, $guest_name, $booking_details) {
    try {
        $subject = "Payment Reminder - The Villa Hotel - Ref: #" . $booking_details['id'];
        
        $check_in_date = date('F j, Y', strtotime($booking_details['check_in']));
        $days_until_checkin = floor((strtotime($booking_details['check_in']) - time()) / 86400);

        $html_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #d4af37; padding: 20px; text-align: center; color: white; }
                .content { background-color: #f9f9f9; padding: 20px; margin: 10px 0; border-radius: 5px; }
                .payment-box { background-color: #fff3cd; padding: 15px; margin: 15px 0; border-left: 4px solid #ffc107; border-radius: 3px; }
                .detail-row { display: flex; justify-content: space-between; padding: 8px 0; }
                .label { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>The Villa Hotel</h1>
                    <p>Payment Reminder</p>
                </div>
                
                <div class='content'>
                    <h2>Dear " . htmlspecialchars($guest_name) . ",</h2>
                    <p>This is a friendly reminder about the remaining payment for your upcoming stay.</p>
                    
                    <div class='payment-box'>
                        <h3>Outstanding Balance</h3>
                        <div class='detail-row'>
                            <span class='label'>Booking Reference:</span>
                            <span>#" . $booking_details['id'] . "</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Room:</span>
                            <span>" . htmlspecialchars($booking_details['room_name']) . "</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Check-In Date:</span>
                            <span>" . $check_in_date . " (" . $days_until_checkin . " days from now)</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Remaining Balance Due:</span>
                            <span style='color: #d97706; font-weight: bold;'>" . CURRENCY_SYMBOL . number_format($booking_details['remaining_balance'], 2) . "</span>
                        </div>
                    </div>
                    
                    <p>Please complete this payment at your earliest convenience to secure your reservation.</p>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to contact us.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . HOTEL_EMAIL . "\r\n";

        return mail($guest_email, $subject, $html_body, $headers);

    } catch (Exception $e) {
        error_log("Reminder Email Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark remaining balance as paid
 * @param PDO $pdo Database connection
 * @param int $booking_id Booking ID
 * @return bool Success status
 */
function markRemainingBalancePaid($pdo, $booking_id) {
    try {
        // Get current booking details
        $booking = getBookingPaymentDetails($pdo, $booking_id);
        
        if (!$booking || $booking['remaining_balance'] <= 0) {
            return false;
        }

        // Record the payment
        recordPayment($pdo, $booking_id, $booking['remaining_balance'], 'remaining_balance', 'manual');

        // Update booking status
        return updateBookingPaymentStatus(
            $pdo, 
            $booking_id, 
            PAYMENT_STATUS_PAID_IN_FULL, 
            $booking['total_price'], 
            0
        );
    } catch (Exception $e) {
        error_log("Mark Balance Paid Error: " . $e->getMessage());
        return false;
    }
}

?>
