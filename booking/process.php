<?php
require 'db_connect.php';
require 'config.php';
require 'payment_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_SANITIZE_NUMBER_INT);
    $check_in = filter_input(INPUT_POST, 'check_in', FILTER_SANITIZE_STRING);
    $check_out = filter_input(INPUT_POST, 'check_out', FILTER_SANITIZE_STRING);
    $guest_name = filter_input(INPUT_POST, 'guest_name', FILTER_SANITIZE_STRING);
    $guest_email = filter_input(INPUT_POST, 'guest_email', FILTER_SANITIZE_EMAIL);
    $guest_phone = filter_input(INPUT_POST, 'guest_phone', FILTER_SANITIZE_STRING);
    $payment_plan = filter_input(INPUT_POST, 'payment_plan', FILTER_SANITIZE_STRING);
    $rooms_booked = filter_input(INPUT_POST, 'rooms_booked', FILTER_SANITIZE_NUMBER_INT);

    if (!$room_id || !$check_in || !$check_out || !$guest_name || !$guest_email || !$payment_plan) {
        die("Please fill in all required fields.");
    }

    // Require authentication for booking
    if (!isset($_SESSION['user_id'])) {
        // Redirect user to login with return path
        header('Location: login.php?return=' . urlencode('booking/index.php'));
        exit;
    }

    // Validate payment plan
    if (!in_array($payment_plan, ['full', 'deposit'])) {
        die("Invalid payment plan selected.");
    }

    // Calculate nights
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $interval = $date1->diff($date2);
    $nights = $interval->days;

    if ($nights < 1) {
        die("Check-out date must be after check-in date.");
    }

    // Fetch room price and availability
    $stmt = $pdo->prepare("SELECT price_per_night, available_rooms FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        die("Invalid room selection.");
    }

    $rooms_booked = max(1, (int)$rooms_booked);

    if ($rooms_booked < 1) {
        die("Invalid number of rooms selected.");
    }

    // Check availability
    if ($room['available_rooms'] < $rooms_booked) {
        die("Not enough rooms available for the selected room type.");
    }

    $total_price = $room['price_per_night'] * $nights * $rooms_booked;
    $deposit_amount = 0;
    $remaining_balance = 0;
    $amount_paid = 0;
    $payment_status = PAYMENT_STATUS_PENDING;

    // Calculate amounts based on payment plan
    if ($payment_plan === 'deposit') {
        $deposit_amount = calculateDeposit($total_price);
        $remaining_balance = calculateRemainingBalance($total_price, $deposit_amount);
        $amount_paid = $deposit_amount;
        $payment_status = PAYMENT_STATUS_DEPOSIT_PAID;
    } else {
        $amount_paid = $total_price;
        $payment_status = PAYMENT_STATUS_PAID_IN_FULL;
    }

    // Insert booking record and decrement availability inside a transaction
    try {
        $pdo->beginTransaction();

        // Lock the row and re-check availability
        $lockStmt = $pdo->prepare("SELECT available_rooms FROM rooms WHERE id = ? FOR UPDATE");
        $lockStmt->execute([$room_id]);
        $roomLock = $lockStmt->fetch();

        if (!$roomLock || $roomLock['available_rooms'] < $rooms_booked) {
            $pdo->rollBack();
            die("Not enough rooms available at the time of booking. Please try again.");
        }

        // Decrement availability
        $updateStmt = $pdo->prepare("UPDATE rooms SET available_rooms = available_rooms - ? WHERE id = ?");
        $updateStmt->execute([$rooms_booked, $room_id]);

        // Prepare booking insert (include rooms_booked and user_id if present)
            // Use logged-in user id
            $user_id = (int)$_SESSION['user_id'];

            // If guest fields weren't provided, try to prefill from users table
            if (empty($guest_name) || empty($guest_email)) {
                $uStmt = $pdo->prepare('SELECT name, email, phone FROM users WHERE id = ?');
                $uStmt->execute([$user_id]);
                $uRow = $uStmt->fetch();
                if ($uRow) {
                    $guest_name = $guest_name ?: $uRow['name'];
                    $guest_email = $guest_email ?: $uRow['email'];
                    $guest_phone = $guest_phone ?: $uRow['phone'];
                }
            }

        $sql = "INSERT INTO bookings (
                    room_id, user_id, rooms_booked, guest_name, guest_email, guest_phone, 
                    check_in, check_out, total_price, payment_plan,
                    deposit_amount, amount_paid, remaining_balance, 
                    payment_status, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $room_id, $user_id, $rooms_booked, $guest_name, $guest_email, $guest_phone,
            $check_in, $check_out, $total_price, $payment_plan,
            $deposit_amount, $amount_paid, $remaining_balance,
            $payment_status, BOOKING_STATUS_CONFIRMED
        ]);

        $booking_id = $pdo->lastInsertId();

        // Commit the transaction (rooms availability and booking saved)
        $pdo->commit();

        // Record payment transaction
        $payment_type = ($payment_plan === 'deposit') ? 'deposit' : 'full_payment';
        recordPayment($pdo, $booking_id, $amount_paid, $payment_type, 'online');

        // Get full booking details for email
        $booking_details = getBookingPaymentDetails($pdo, $booking_id);

        // Send confirmation email (includes dashboard link)
        $email_sent = sendBookingConfirmationEmail($guest_email, $guest_name, $booking_details);

        if (!$email_sent) {
            error_log("Warning: Booking confirmation email could not be sent for booking #" . $booking_id);
        }

        // Redirect to confirmation page
        header("Location: confirmation.php?id=" . $booking_id);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Booking Error: " . $e->getMessage());
        die("Error processing booking: " . $e->getMessage());
    }
} else {
    // Redirect if accessed directly
    header("Location: index.php");
    exit;
}
?>
