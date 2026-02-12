# Hotel Booking System - Payment & Deposit Implementation Guide

## Overview
This enhanced booking system includes support for full payment and deposit (partial) payment options. Guests can choose to pay 100% upfront or pay 30% as a deposit with the remaining 70% due at check-in.

## Key Features

### 💳 Payment Options
- **Full Payment (100%)**: Guest pays the complete booking amount upfront
- **Deposit Payment (30%)**: Guest pays 30% as deposit, remaining 70% due at check-in

### 📊 Payment Tracking
- New payment status tracking: `pending`, `deposit_paid`, `paid_in_full`, `cancelled`
- Separate `payments` table to track all payment transactions
- Payment history for each booking
- Remaining balance calculation and tracking

### 📧 Email Notifications
- Booking confirmation email with payment details
- Optional payment reminder email for remaining balance
- Customizable email templates

### 👨‍💼 Admin Panel
- View all bookings with pending balance
- Mark remaining balance as paid at check-in
- Track payment history for each booking
- Filter by check-in dates (today's check-ins highlighted)

---

## Database Schema Updates

### New Columns in `bookings` Table
```sql
payment_plan ENUM('full', 'deposit') DEFAULT 'full'
deposit_amount DECIMAL(10, 2) DEFAULT 0
amount_paid DECIMAL(10, 2) DEFAULT 0
remaining_balance DECIMAL(10, 2) DEFAULT 0
payment_status ENUM('pending', 'deposit_paid', 'paid_in_full', 'cancelled') DEFAULT 'pending'
status ENUM('pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out') DEFAULT 'pending'
notes TEXT
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

### New `payments` Table
```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_type ENUM('deposit', 'full_payment', 'remaining_balance') DEFAULT 'full_payment',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);
```

---

## File Structure

```
booking/
├── config.php                  # Configuration settings & payment gateway
├── payment_functions.php       # Core payment functions
├── db_connect.php             # Database connection
├── index.php                  # Booking form with payment options
├── process.php                # Form processing & deposit calculation
├── confirmation.php           # Booking confirmation with payment details
├── admin_payments.php         # Admin panel for payment management
├── schema.sql                 # Updated database schema
└── README.md                  # This file
```

---

## Configuration

Edit `config.php` to set up:

### Payment Gateway
```php
define('PAYMENT_GATEWAY', 'stripe'); // Options: 'stripe', 'paypal', 'manual'

// Stripe (Sandbox)
define('STRIPE_PUBLIC_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');

// PayPal (Sandbox)
define('PAYPAL_CLIENT_ID', 'your_client_id');
define('PAYPAL_SECRET', 'your_secret');
```

### Email Settings
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('HOTEL_EMAIL', 'reservations@thevilla.com');
```

### Deposit Percentage
```php
define('DEPOSIT_PERCENTAGE', 0.30); // 30% deposit
```

---

## How It Works

### Customer Journey

1. **Browse Rooms** (`index.php`)
   - Select room, check-in/check-out dates
   - View calculated total price
   - **NEW**: Choose payment plan (Full Payment or Deposit)

2. **Payment Plan Selection**
   - Full Payment (100%): "Pay full amount now"
   - Deposit Payment (30%): "Pay 30% now, 70% at check-in"
   - Real-time calculation of amounts due

3. **Process Booking** (`process.php`)
   - Validates all inputs
   - Calculates deposit/total amounts
   - Creates booking record
   - Records payment transaction
   - Sends confirmation email

4. **View Confirmation** (`confirmation.php`)
   - Displays booking details
   - Shows payment plan and amounts
   - Displays payment history
   - Clear notice about remaining balance (if applicable)

### Admin Workflow

1. **Access Admin Panel** (`admin_payments.php`)
   - Login with admin password (demo: "admin123")
   - See today's check-ins with pending balance highlighted
   - View all bookings with remaining balance

2. **Process Remaining Balance**
   - Click "Mark Balance Paid" button
   - System records payment transaction
   - Updates booking payment status
   - Can view payment history

---

## Core Payment Functions

### In `payment_functions.php`

#### Calculate Deposit
```php
$deposit = calculateDeposit($total_price);
// Returns: $total_price * 0.30
```

#### Calculate Remaining Balance
```php
$remaining = calculateRemainingBalance($total_price, $deposit_amount);
// Returns: $total_price - $deposit_amount
```

#### Record Payment
```php
recordPayment($pdo, $booking_id, $amount, $payment_type, $payment_method, $transaction_id);
// Inserts payment record in payments table
```

#### Update Booking Payment Status
```php
updateBookingPaymentStatus($pdo, $booking_id, $payment_status, $amount_paid, $remaining_balance);
// Updates booking payment status and amounts
```

#### Send Confirmation Email
```php
sendBookingConfirmationEmail($guest_email, $guest_name, $booking_details);
// Sends HTML email with booking and payment details
```

#### Mark Remaining Balance As Paid
```php
markRemainingBalancePaid($pdo, $booking_id);
// Records payment and updates status to paid_in_full
```

---

## Payment Status Flow

```
Deposit Payment:
pending → deposit_paid → paid_in_full

Full Payment:
pending → paid_in_full

Cancelled:
Any status → cancelled
```

---

## Integration Examples

### Example 1: Calculate Deposit for a Booking
```php
$total_price = 1000; // 3 nights × $250/night + taxes
$deposit = calculateDeposit($total_price);
// $deposit = 300 (30% of $1000)

$remaining = calculateRemainingBalance($total_price, $deposit);
// $remaining = 700
```

### Example 2: Record Deposit Payment
```php
recordPayment(
    $pdo,
    $booking_id = 42,
    $amount = 300,
    $payment_type = 'deposit',
    $payment_method = 'stripe',
    $transaction_id = 'ch_1234567890'
);
```

### Example 3: Complete Remaining Balance at Check-in
```php
markRemainingBalancePaid($pdo, $booking_id = 42);
// - Records $700 payment as 'remaining_balance' type
// - Updates payment_status to 'paid_in_full'
// - Sets remaining_balance to 0
```

### Example 4: Get Booking Payment Details
```php
$booking = getBookingPaymentDetails($pdo, $booking_id = 42);
// Returns:
// [
//     'id' => 42,
//     'guest_name' => 'John Doe',
//     'room_name' => 'Deluxe Ocean View',
//     'total_price' => 1000,
//     'payment_plan' => 'deposit',
//     'deposit_amount' => 300,
//     'amount_paid' => 300,
//     'remaining_balance' => 700,
//     'payment_status' => 'deposit_paid',
//     ...
// ]
```

### Example 5: Get Payment History
```php
$payments = getPaymentHistory($pdo, $booking_id = 42);
// Returns array of payment records:
// [
//     [
//         'id' => 1,
//         'booking_id' => 42,
//         'amount' => 300,
//         'payment_type' => 'deposit',
//         'status' => 'completed',
//         'payment_date' => '2024-02-12 14:30:00',
//         ...
//     ],
//     ...
// ]
```

---

## User Interface Features

### Booking Form (`index.php`)
- **Payment Plan Selection**: Radio buttons for Full/Deposit options
- **Real-time Calculation**: Shows amount due now and remaining balance
- **Visual Feedback**: Different styling for each payment option
- **Clear Messaging**: "Pay 30% now, 70% at check-in" for deposit option

### Confirmation Page (`confirmation.php`)
- **Payment Status Badge**: Visual indicator (Deposit Paid, Paid in Full)
- **Payment Breakdown**: Shows deposit, remaining balance, and total
- **Important Notice**: Warning about remaining balance due at check-in
- **Payment History Table**: Lists all transactions for the booking

### Admin Panel (`admin_payments.php`)
- **Quick View**: Today's check-ins highlighted separately
- **One-Click Payment Processing**: Mark balance paid button
- **Booking Details Grid**: Shows key information at a glance
- **Full Booking List**: Table view of all pending payments
- **Color-coded**: Different colors indicate payment status

---

## Security Considerations

### Input Validation
- All user inputs are sanitized using `FILTER_SANITIZE_*`
- Email validated with `FILTER_SANITIZE_EMAIL`
- Room ID converted to integer
- Dates validated using DateTime

### SQL Injection Prevention
- All database queries use prepared statements with parameterized queries
- No direct SQL concatenation

### CSRF Protection
- Implement token validation for critical operations (see notes below)

### Password Security
- Admin passwords should use proper hashing (bcrypt, password_hash)
- Demo password "admin123" is for development only
- Use HTTPS for all payment operations

### Recommendations for Production
1. **Enable SSL/HTTPS**: Mandatory for payment processing
2. **Implement CSRF tokens**: Add token validation to forms
3. **Use payment gateway APIs**: Implement Stripe/PayPal SDK
4. **Hash admin passwords**: Use `password_hash()` function
5. **Add rate limiting**: Prevent brute force attacks
6. **Log all payments**: Maintain audit trail
7. **Use sessions securely**: Set `session.secure` and `session.httponly`

---

## Email Template Customization

Edit the HTML in `payment_functions.php`:
- `sendBookingConfirmationEmail()`: Booking confirmation email
- `sendPaymentReminderEmail()`: Remaining balance reminder

Colors and styling:
- Primary color: `#d4af37` (gold)
- Success: `#2ecc71` (green)
- Warning: `#f59e0b` (amber)

---

## Payment Gateway Integration (Next Steps)

### For Stripe:
```php
// In process.php, after booking creation:
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$charge = \Stripe\Charge::create([
    'amount' => $deposit_amount * 100,
    'currency' => 'usd',
    'description' => 'Hotel Booking #' . $booking_id,
    'token' => $_POST['stripeToken']
]);
```

### For PayPal:
```php
// Use PayPal SDK for processing payments
// Store transaction ID for reference
recordPayment($pdo, $booking_id, $amount, $payment_type, 'paypal', $transaction_id);
```

---

## Testing Scenarios

### Scenario 1: Full Payment
1. Book a room for 3 nights ($750 total)
2. Select "Full Payment" option
3. Should see: "Amount Due Now: $750"
4. After submission: Payment status = "Paid in Full"

### Scenario 2: Deposit Payment
1. Book a room for 3 nights ($750 total)
2. Select "Deposit Payment" option
3. Should see: "Amount Due Now: $225" and "Remaining: $525"
4. After submission: Payment status = "Deposit Paid"
5. Remaining balance shown in confirmation

### Scenario 3: Admin Check-in Process
1. Login to admin panel (password: admin123)
2. See today's check-ins with pending balance
3. Click "Mark Balance Paid"
4. Status updates to "Paid in Full"
5. Payment recorded in payment history

---

## Troubleshooting

### Email Not Sending
- Check SMTP credentials in `config.php`
- Enable "Less secure app access" for Gmail
- Use app-specific passwords for Gmail accounts
- Check PHP error logs

### Payment Status Not Updating
- Verify database columns exist (run schema.sql)
- Check payment_status enum values
- Verify booking_id is correct

### Admin Panel Not Accessible
- Demo password is "admin123" (change in production)
- Session may have expired; clear cookies and re-login

### Deposit Amount Incorrect
- Verify `DEPOSIT_PERCENTAGE` is correct (0.30 = 30%)
- Check calculation: `total_price * 0.30`

---

## Future Enhancements

1. **Stripe/PayPal Integration**: Real payment processing
2. **Email Reminders**: Automated emails for outstanding balance
3. **Payment Plans**: Multiple payment schedule options
4. **Invoice Generation**: PDF invoices for guests
5. **Refund Processing**: Handle cancellations and refunds
6. **Analytics Dashboard**: Payment reports and statistics
7. **Multi-currency Support**: Accept payments in different currencies
8. **Payment Gateway Webhooks**: Handle async payment confirmations

---

## Support & Questions

For issues or questions:
1. Check the troubleshooting section above
2. Review error logs in PHP/MySQL
3. Verify configuration in `config.php`
4. Check database schema is properly updated
5. Ensure all files are in the correct location

---

## Version History

- **v2.0** (Current): Added deposit/partial payment support
  - New payment planning options
  - Payment tracking with transactions table
  - Admin panel for check-in balance collection
  - Email notifications with payment details
  - Real-time deposit calculation

- **v1.0**: Initial booking system
  - Basic booking form and confirmation
  - Single payment model

---

**Last Updated**: February 12, 2026  
**Maintained By**: Your Team  
**License**: Proprietary - The Villa Hotel
