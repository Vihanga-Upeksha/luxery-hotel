<?php
require_once 'booking/db_connect.php';

// Check if this is a featured rooms request (for homepage)
$is_featured_request = isset($_GET['featured']) && $_GET['featured'] === '1';

// Fetch all rooms from database
try {
    $stmt = $pdo->query("
        SELECT 
            id, 
            name, 
            type, 
            description, 
            price_per_night, 
            capacity, 
            total_rooms,
            available_rooms,
            image_url
        FROM rooms 
        ORDER BY price_per_night ASC
    ");
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    if ($is_featured_request) {
        http_response_code(500);
        echo '<div style="color: #d32f2f;">Error loading rooms.</div>';
        exit;
    }
    die("
    <div style='background:#f8d7da; color:#721c24; padding:20px; border-radius:8px; margin:20px;'>
        <h3>Setup Required</h3>
        <p><strong>Database Connection Error</strong></p>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ");
}

$is_logged_in = isset($_SESSION['user_id']);

// If featured request, return only the room cards HTML (for homepage)
if ($is_featured_request) {
    header('Content-Type: text/html; charset=utf-8');
    if (!empty($rooms)) {
        foreach (array_slice($rooms, 0, 3) as $room) {
            $available = $room['available_rooms'];
            $image_url = 'images/' . $room['image_url'];
            $short_desc = substr($room['description'] ?? '', 0, 80) . (strlen($room['description'] ?? '') > 80 ? '...' : '');
            ?>
            <!-- Room Card -->
            <a href="room-details.php?id=<?php echo intval($room['id']); ?>" class="room-card" title="View <?php echo htmlspecialchars($room['name']); ?> details">
                <div class="room-card-image">
                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" loading="lazy">
                    <?php if ($available == 0): ?>
                        <div class="room-badge unavailable">Fully Booked</div>
                    <?php elseif ($available <= 2): ?>
                        <div class="room-badge limited">Last <?php echo $available; ?> Left</div>
                    <?php else: ?>
                        <div class="room-badge available"><?php echo $available; ?> Available</div>
                    <?php endif; ?>
                </div>
                <div class="room-card-content">
                    <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                    <p class="room-card-type"><?php echo htmlspecialchars($room['type']); ?> • <?php echo $room['capacity']; ?> guests</p>
                    <p class="room-card-description"><?php echo htmlspecialchars($short_desc); ?></p>
                    <div class="room-card-footer">
                        <div class="room-card-price">
                            <span class="price">$<?php echo number_format($room['price_per_night'], 0); ?></span>
                            <span class="per-night">/night</span>
                        </div>
                        <button class="btn-view-details">View Details →</button>
                    </div>
                </div>
            </a>
            <?php
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms & Suites | The Villa Luxury Hotel</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/room-facility.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="logo">The <span>Villa</span></div>
        <div class="mobile-toggle" ><i class="fas fa-bars"></i></div>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="rooms.php" class="active">Rooms & Suites</a></li>
                <li><a href="facilities.html">Facilities</a></li>
                <li><a href="gallery.html">Gallery</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="booking/index.php" class="active btn btn-primary">Book Now</a></li>
                <li class="nav-user">
                    <?php if ($is_logged_in): ?>
                        <div class="nav-user-trigger" onclick="toggleUserMenu(event)">
                            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                            <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                        </div>
                        <div class="nav-user-dropdown">
                            <a href="booking/dashboard.php"><i class="fas fa-chart-line" style="margin-right:0.5rem;"></i>My Bookings</a>
                            <a href="booking/logout.php" class="logout"><i class="fas fa-sign-out-alt" style="margin-right:0.5rem;"></i>Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="nav-user-trigger" onclick="toggleUserMenu(event)">
                            <i class="fas fa-user" style="font-size: 1.1rem;"></i>
                            <span>Login</span>
                        </div>
                        <div class="nav-user-dropdown">
                            <a href="booking/login.php"><i class="fas fa-sign-in-alt" style="margin-right:0.5rem;"></i>Login</a>
                            <a href="booking/register.php"><i class="fas fa-user-plus" style="margin-right:0.5rem;"></i>Register</a>
                        </div>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>
    <!-- Hero Section -->
    <section class="hero-static" style="background-image: url('images/manuel-moreno-DGa0LQ0yDPc-unsplash.jpg'); background-size: cover; background-position: center; margin-top: 80px;">
        <div class="hero-content">
            <h1>Accommodation</h1>
            <p style="color: white; font-size: 1.5rem;">Sanctuaries of Peace and Luxury</p>
        </div>
    </section>

    <!-- Rooms Container -->
    <div class="rooms-container">
        
        <div class="page-header">
            <h1>Our Room Collections</h1>
            <p>Experience unparalleled luxury and comfort in every room</p>
        </div>

        <?php if (empty($rooms)): ?>
            <div style="text-align: center; padding: var(--spacing-lg); color: var(--color-text-light);">
                <p>No rooms available at this time. Please check back soon.</p>
            </div>
        <?php else: ?>
            <div class="rooms-grid">
                <?php foreach ($rooms as $room): ?>
                    <?php
                        $available = $room['available_rooms'];
                        $image_url = 'images/' . $room['image_url'] ;
                        $short_desc = substr($room['description'] ?? '', 0, 80) . (strlen($room['description'] ?? '') > 80 ? '...' : '');
                    ?>
                    
                    <!-- Room Card -->
                    <a href="room-details.php?id=<?php echo intval($room['id']); ?>" class="room-card" title="View <?php echo htmlspecialchars($room['name']); ?> details">
                        <div class="room-card-image">
                            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" loading="lazy">
                            <?php if ($available == 0): ?>
                                <div class="room-badge unavailable">Fully Booked</div>
                            <?php elseif ($available <= 2): ?>
                                <div class="room-badge limited">Last <?php echo $available; ?> Left</div>
                            <?php else: ?>
                                <div class="room-badge available"><?php echo $available; ?> Available</div>
                            <?php endif; ?>
                        </div>
                        <div class="room-card-content">
                            <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                            <p class="room-card-type"><?php echo htmlspecialchars($room['type']); ?> • <?php echo $room['capacity']; ?> guests</p>
                            <p class="room-card-description"><?php echo htmlspecialchars($short_desc); ?></p>
                            <div class="room-card-footer">
                                <div class="room-card-price">
                                    <span class="price">$<?php echo number_format($room['price_per_night'], 0); ?></span>
                                    <span class="per-night">/night</span>
                                </div>
                                <button class="btn-view-details">View Details →</button>
                            </div>
                        </div>
                    </a>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <div class="logo" style="color: white;">The <span>Villa</span></div>
                <p style="margin-top: 1rem; color: #ccc;">Experience the art of hospitality. Your luxury escape awaits.</p>
                <div class="social-icons" style="margin-top: 1rem;">
                    <a href="#" style="color: var(--color-secondary); transition: all 0.3s ease;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" style="color: var(--color-secondary); transition: all 0.3s ease;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" style="color: var(--color-secondary); transition: all 0.3s ease;">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" style="color: var(--color-secondary); transition: all 0.3s ease;">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
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
                    <li><i class="fas fa-map-marker-alt" style="color: var(--color-secondary); margin-right: 0.5rem;"></i> 123 Luxury Ave, Paradise City</li>
                    <li><i class="fas fa-phone" style="color: var(--color-secondary); margin-right: 0.5rem;"></i> +1 (555) 123-4567</li>
                    <li><i class="fas fa-envelope" style="color: var(--color-secondary); margin-right: 0.5rem;"></i> reservations@thevilla.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 The Villa Hotel. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        // Toggle user menu
        function toggleUserMenu(event) {
            event.stopPropagation();
            const userNav = event.currentTarget.closest('.nav-user');
            userNav.classList.toggle('active');
        }

        // Close menu on outside click
        document.addEventListener('click', function() {
            document.querySelectorAll('.nav-user.active').forEach(el => {
                el.classList.remove('active');
            });
        });

        // Mobile menu toggle
        document.querySelector('.mobile-toggle')?.addEventListener('click', function() {
            document.querySelector('nav').classList.toggle('active');
        });

        // Room cards link functionality
        document.querySelectorAll('.room-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Links are already clickable, this adds extra styling feedback
                if (!e.target.closest('.btn-view-details')) {
                    const link = this.href;
                    if (link) window.location.href = link;
                }
            });
        });
    </script>

</body>
</html>
