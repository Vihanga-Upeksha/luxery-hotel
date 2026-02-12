<?php
/**
 * Payment Configuration File
 * Handles payment settings and constants
 */

// Deposit percentage (30% of total price)
define('DEPOSIT_PERCENTAGE', 0.30);

// Payment Gateway Settings
// Note: Update these with your actual credentials
define('PAYMENT_GATEWAY', 'stripe'); // Options: 'stripe', 'paypal', 'manual'

// Stripe Configuration (Sandbox Mode)
define('STRIPE_PUBLIC_KEY', 'pk_test_your_stripe_public_key_here');
define('STRIPE_SECRET_KEY', 'sk_test_your_stripe_secret_key_here');

// PayPal Configuration (Sandbox Mode)
define('PAYPAL_MODE', 'sandbox'); // Options: 'sandbox', 'live'
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id_here');
define('PAYPAL_SECRET', 'your_paypal_secret_here');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('HOTEL_EMAIL', 'reservations@thevilla.com');
define('HOTEL_NAME', 'The Villa Hotel');

// Hotel Contact Information
define('HOTEL_PHONE', '+1 (555) 123-4567');
define('HOTEL_ADDRESS', '123 Luxury Ave, Paradise City');

// Booking Settings
define('CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');

// Payment Status Constants
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_DEPOSIT_PAID', 'deposit_paid');
define('PAYMENT_STATUS_PAID_IN_FULL', 'paid_in_full');
define('PAYMENT_STATUS_CANCELLED', 'cancelled');

// Booking Status Constants
define('BOOKING_STATUS_PENDING', 'pending');
define('BOOKING_STATUS_CONFIRMED', 'confirmed');
define('BOOKING_STATUS_CHECKED_IN', 'checked_in');
define('BOOKING_STATUS_CHECKED_OUT', 'checked_out');
define('BOOKING_STATUS_CANCELLED', 'cancelled');

// Payment reminder settings (in days before check-in)
define('PAYMENT_REMINDER_DAYS', 7);

?>
