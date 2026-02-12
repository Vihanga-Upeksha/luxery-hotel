<?php
require 'db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);

// Fetch rooms for the dropdown (include availability)
$stmt = $pdo->query("SELECT id, name, price_per_night, total_rooms, available_rooms FROM rooms");
$rooms = $stmt->fetchAll();

// Get selected room from URL if available
$selected_room_id = isset($_GET['room']) ? $_GET['room'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay | The Villa</title>
    <!-- Link to the main CSS file -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-top: -100px; /* Overlap hero */
            position: relative;
            z-index: 10;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        .form-row {
            display: flex;
            gap: 1.5rem;
        }
        .form-row .form-group {
            flex: 1;
        }
        /* Ensure the header 'Book Now' button text stays white and hover doesn't change it */
        nav ul li a.active.btn.btn-primary {
            color: #ffffff !important;
        }
        nav ul li a.active.btn.btn-primary:hover {
            color: #ffffff !important;
            background-color: #b99828 !important;
            text-decoration: none;
        }
        /* User dropdown menu */
        .nav-user { position: relative; }
        .nav-user-trigger { cursor: pointer; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; }
        .nav-user-trigger .user-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--color-secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; }
        .nav-user-dropdown { position: absolute; top: 100%; right: 0; background: white; min-width: 180px; border: 1px solid #eee; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 999; display: none; overflow: hidden; }
        .nav-user.active .nav-user-dropdown { display: block; }
        .nav-user-dropdown a { display: block; padding: 0.75rem 1rem; color: var(--color-text-main); transition: all 0.2s ease; }
        .nav-user-dropdown a:hover { background: #f5f5f5; color: var(--color-secondary); }
        .nav-user-dropdown a.logout { color: #9b1c1c; }
        .nav-user-dropdown a.logout:hover { background: #ffe6e6; }
      
    </style>
</head>
<body>

    <header>
        <div class="logo">The <span>Villa</span></div>
        <div class="mobile-toggle" ><i class="fas fa-bars"></i></div>
        <nav>
            <ul>
                <li><a href="../index.html">Home</a></li>
                <li><a href="../rooms.php">Rooms & Suites</a></li>
                <li><a href="../facilities.html">Facilities</a></li>
                <li><a href="../gallery.html">Gallery</a></li>
                <li><a href="../about.html">About Us</a></li>
                <li><a href="../contact.html">Contact</a></li>
                <li><a href="index.php" class="active btn btn-primary">Book Now</a></li>
                <li class="nav-user">
                    <?php if ($is_logged_in): ?>
                        <div class="nav-user-trigger" onclick="toggleUserMenu(event)">
                            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                            <!-- <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span> -->
                            <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                        </div>
                        <div class="nav-user-dropdown">
                            <a href="dashboard.php"><i class="fas fa-chart-line" style="margin-right:0.5rem;"></i>My Bookings</a>
                            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt" style="margin-right:0.5rem;"></i>Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="nav-user-trigger" onclick="toggleUserMenu(event)">
                            <i class="fas fa-user" style="font-size: 1.1rem;"></i>
                            <span>Login</span>
                        </div>
                        <div class="nav-user-dropdown">
                            <a href="login.php"><i class="fas fa-sign-in-alt" style="margin-right:0.5rem;"></i>Login</a>
                            <a href="register.php"><i class="fas fa-user-plus" style="margin-right:0.5rem;"></i>Register</a>
                        </div>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>

    <section style="width: 100%; height: 50vh; background-image: url('../images/manuel-moreno-DGa0LQ0yDPc-unsplash.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;  display: flex; align-items: center; justify-content: center;">
        <h1 style="font-size: 3.5rem; color: white; text-shadow: 2px 2px 8px rgba(0,0,0,0.6); text-align: center; font-family: var(--font-heading); font-weight: 700;">Book Your Room</h1>
    </section>

    <div class="booking-container">
        <?php if (!$is_logged_in): ?>
            <div class="booking-card" style="border-left:4px solid var(--color-secondary);">
                <p style="margin:0 0 0.5rem 0; font-weight:700;">Please sign in to make a booking</p>
                <p style="margin:0 0 0.5rem 0;color:var(--color-text-light);">You need an account to confirm a reservation. <a href="login.php">Login</a> or <a href="register.php">Register</a>.</p>
            </div>
        <?php endif; ?>
        <form action="process.php" method="POST">
            <div class="section-header">
                <h2>Booking Details</h2>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="check_in">Check-In Date</label>
                    <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="check_out">Check-Out Date</label>
                    <input type="date" id="check_out" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
            </div>

                <div class="form-group">
                <label for="room_id">Select Room</label>
                <select id="room_id" name="room_id" required onchange="updateRoomPrice()">
                    <option value="">-- Choose a Room --</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>" data-price="<?php echo $room['price_per_night']; ?>" data-available="<?php echo $room['available_rooms']; ?>" <?php echo($selected_room_id == $room['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($room['name']); ?> - $<?php echo $room['price_per_night']; ?>/night
                        </option>
                    <?php
    endforeach; ?>
                </select>
                <small id="rooms_available_text" style="display:block; margin-top:6px; color:#666;">Rooms available: <span id="rooms_available">-</span></small>
            </div>

            <div class="form-group">
                <label for="rooms_booked">Number of Rooms</label>
                <input type="number" id="rooms_booked" name="rooms_booked" value="1" min="1" max="1" required />
            </div>

            <div class="form-group">
                <label>Total Booking Price: <strong id="total_price_display">$0.00</strong></label>
            </div>

            <div class="section-header" style="margin-top: 2rem;">
                <h2>Payment Plan</h2>
            </div>

            <div class="form-group">
                <label>Choose Payment Option:</label>
                <div style="margin-top: 1rem;">
                    <div style="margin-bottom: 1rem; padding: 1rem; border: 2px solid #ddd; border-radius: 4px; cursor: pointer;" onclick="selectPaymentPlan('full')">
                        <input type="radio" id="payment_full" name="payment_plan" value="full" checked required onchange="updatePaymentDisplay()">
                        <label for="payment_full" style="cursor: pointer; margin: 0;">
                            <strong>Full Payment (100%)</strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #666;">Pay the complete amount now</p>
                        </label>
                    </div>

                    <div style="margin-bottom: 1rem; padding: 1rem; border: 2px solid #ddd; border-radius: 4px; cursor: pointer;" onclick="selectPaymentPlan('deposit')">
                        <input type="radio" id="payment_deposit" name="payment_plan" value="deposit" required onchange="updatePaymentDisplay()">
                        <label for="payment_deposit" style="cursor: pointer; margin: 0;">
                            <strong>Deposit Payment (30%)</strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #666;">Pay 30% now as deposit, 70% at check-in</p>
                        </label>
                    </div>
                </div>

                <div id="payment_summary" style="background: #f9f9f9; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                    <div style="margin-bottom: 0.5rem;">
                        <strong>Amount Due Now:</strong> <span id="amount_due">$0.00</span>
                    </div>
                    <div id="remaining_display" style="display: none;">
                        <strong>Remaining Balance:</strong> <span id="amount_remaining">$0.00</span>
                        <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0 0 0;">Payable at check-in</p>
                    </div>
                </div>
            </div>

            <div class="section-header" style="margin-top: 2rem;">
                <h2>Guest Information</h2>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="guest_name">Full Name</label>
                    <input type="text" id="guest_name" name="guest_name" required>
                </div>
                <div class="form-group">
                    <label for="guest_phone">Phone Number</label>
                    <input type="tel" id="guest_phone" name="guest_phone" required>
                </div>
            </div>

            <div class="form-group">
                <label for="guest_email">Email Address</label>
                <input type="email" id="guest_email" name="guest_email" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; text-align: center;" <?php echo !$is_logged_in ? 'disabled' : ''; ?>>Confirm Booking</button>
        </form>
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
                    <li><a href="../rooms.php">Rooms & Suites</a></li>
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

    <script src="../js/main.js"></script>
    <script>
        // Toggle user dropdown menu
        function toggleUserMenu(event) {
            event.stopPropagation();
            const navUser = event.target.closest('.nav-user');
            navUser.classList.toggle('active');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.nav-user.active').forEach(el => el.classList.remove('active'));
        });

        // Simple date validation to ensure check-out is after check-in
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');

        checkIn.addEventListener('change', function() {
            checkOut.min = this.value;
            updateRoomPrice();
        });

        checkOut.addEventListener('change', function() {
            updateRoomPrice();
        });

        function updateRoomPrice() {
            const roomSelect = document.getElementById('room_id');
            const checkInDate = document.getElementById('check_in').value;
            const checkOutDate = document.getElementById('check_out').value;
            const roomsBookedInput = document.getElementById('rooms_booked');
            const roomsBooked = parseInt(roomsBookedInput.value) || 1;
            
            if (!roomSelect.value || !checkInDate || !checkOutDate) {
                document.getElementById('total_price_display').textContent = '$0.00';
                document.getElementById('amount_due').textContent = '$0.00';
                document.getElementById('amount_remaining').textContent = '$0.00';
                return;
            }

            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const pricePerNight = parseFloat(selectedOption.getAttribute('data-price'));
            const available = parseInt(selectedOption.getAttribute('data-available')) || 0;
            document.getElementById('rooms_available').textContent = available;
            roomsBookedInput.max = available > 0 ? available : 1;
            
            const date1 = new Date(checkInDate);
            const date2 = new Date(checkOutDate);
            const diffTime = Math.abs(date2 - date1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 1) {
                document.getElementById('total_price_display').textContent = '$0.00';
                document.getElementById('amount_due').textContent = '$0.00';
                document.getElementById('amount_remaining').textContent = '$0.00';
                return;
            }

            const totalPrice = pricePerNight * diffDays * roomsBooked;
            document.getElementById('total_price_display').textContent = '$' + totalPrice.toFixed(2);
            
            updatePaymentDisplay();
        }

        function selectPaymentPlan(plan) {
            document.getElementById('payment_' + plan).checked = true;
            updatePaymentDisplay();
        }

        function updatePaymentDisplay() {
            const totalPriceText = document.getElementById('total_price_display').textContent.replace('$', '');
            const totalPrice = parseFloat(totalPriceText) || 0;
            const paymentPlan = document.querySelector('input[name="payment_plan"]:checked').value;
            const remainingDisplay = document.getElementById('remaining_display');

            if (paymentPlan === 'deposit') {
                const depositAmount = totalPrice * 0.30;
                const remainingBalance = totalPrice - depositAmount;
                
                document.getElementById('amount_due').textContent = '$' + depositAmount.toFixed(2);
                document.getElementById('amount_remaining').textContent = '$' + remainingBalance.toFixed(2);
                remainingDisplay.style.display = 'block';
            } else {
                document.getElementById('amount_due').textContent = '$' + totalPrice.toFixed(2);
                remainingDisplay.style.display = 'none';
            }
        }

        // Initialize on page load
        window.addEventListener('load', function() {
            updateRoomPrice();
        });
    </script>
</body>
</html>
