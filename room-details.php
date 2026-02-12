<?php
// Start session for user info
session_start();

// Database connection
include 'booking/db_connect.php';

// Get room ID from URL parameter
$room_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$room = null;
$error_message = '';

// Fetch room details from database
if ($room_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$room) {
            $error_message = 'Room not found. It may have been removed or the ID is invalid.';
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: Unable to fetch room details.';
    }
} else {
    $error_message = 'Invalid room selection. Please try again.';
}

// Room amenities (default set - can be customized per room type)
$amenities = [
    ['icon' => 'fas fa-wifi', 'name' => 'Free Wi-Fi'],
    ['icon' => 'fas fa-tv', 'name' => '42" Smart TV'],
    ['icon' => 'fas fa-wind', 'name' => 'Air Conditioning'],
    ['icon' => 'fas fa-bath', 'name' => 'Luxury Bathroom'],
    ['icon' => 'fas fa-utensils', 'name' => 'In-Room Dining'],
    ['icon' => 'fas fa-concierge-bell', 'name' => '24/7 Concierge'],
];

// Sample gallery images (in production, these would come from database)
$raw_main_image = 'images/' . $room['image_url'];
$gallery_1 = 'images/' . $room['gallery_image_1'];
$gallery_2 = 'images/' . $room['gallery_image_2'];
$gallery_images = [
    $raw_main_image,
    $gallery_1,
    $gallery_2,
];

// Calculate occupancy percentage
$occupancy = 0;
if ($room && $room['total_rooms'] > 0) {
    $occupancy = (($room['total_rooms'] - $room['available_rooms']) / $room['total_rooms']) * 100;
}

require_once 'booking/db_connect.php';

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
    die("
    <div style='background:#f8d7da; color:#721c24; padding:20px; border-radius:8px; margin:20px;'>
        <h3>Setup Required</h3>
        <p><strong>Database Connection Error</strong></p>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ");
}

$is_logged_in = isset($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $room ? htmlspecialchars($room['name']) . ' | Luxury Hotel' : 'Room Details'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/room-facility.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
    <!-- Navigation -->
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
    <div class="hero-section" style="background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/manuel-moreno-DGa0LQ0yDPc-unsplash.jpg') center/cover; min-height: 50vh; display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
        <div>
            <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem; font-family: 'Playfair Display', serif;">Room Details</h1>
            <p>Experience Unparalleled Luxury</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="rooms-container">
        <?php if ($error_message): ?>
            <!-- Error State -->
            <div style="text-align: center; padding: var(--spacing-xl); background: var(--color-bg-light); border-radius: 12px; margin-bottom: var(--spacing-lg);">
                <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: var(--color-secondary); margin-bottom: var(--spacing-md);"></i>
                <h2><?php echo htmlspecialchars($error_message); ?></h2>
                <p style="margin-bottom: var(--spacing-md); color: var(--color-text-light);">Please try again or browse our other available rooms.</p>
                <a href="rooms.php" class="btn" style="display: inline-block; padding: 12px 30px; background: var(--color-primary); color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>Back to Rooms
                </a>
            </div>
        <?php else: ?>
            <!-- Room Details -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
                
                <!-- Left: Gallery -->
                <div>
                    <!-- Main Image -->
                    <div id="gallery-main" style="position: relative; width: 100%; padding-top: 75%; overflow: hidden; border-radius: 12px; background: var(--color-bg-light); margin-bottom: var(--spacing-md);">
                        <img id="main-image" src="<?php echo htmlspecialchars($gallery_images[0]); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <!-- Thumbnail Gallery -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-sm);">
                        <?php foreach ($gallery_images as $index => $img): ?>
                            <div style="cursor: pointer; border-radius: 8px; overflow: hidden; border: 3px solid <?php echo $index === 0 ? 'var(--color-primary)' : 'var(--color-border)'; ?>; transition: all 0.3s ease;" onclick="switchImage(this, '<?php echo htmlspecialchars($img); ?>')">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Room gallery image <?php echo $index + 1; ?>" style="width: 100%; height: auto; display: block; object-fit: cover; aspect-ratio: 1;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right: Details -->
                <div>
                    <!-- Room Header -->
                    <span style="display: inline-block; padding: 8px 16px; background: var(--color-primary); color: white; border-radius: 20px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: var(--spacing-sm); font-weight: 600;">
                        <?php echo htmlspecialchars($room['type']); ?>
                    </span>

                    <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin: 0 0 var(--spacing-sm) 0; color: var(--color-text-dark);">
                        <?php echo htmlspecialchars($room['name']); ?>
                    </h1>

                    <!-- Availability Status -->
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg); padding: var(--spacing-md); background: var(--color-bg-light); border-radius: 8px;">
                        <?php if ($room['available_rooms'] == 0): ?>
                            <i class="fas fa-times-circle" style="font-size: 1.5rem; color: #f44336;"></i>
                            <div>
                                <p style="margin: 0; font-weight: 600; color: #f44336;">Fully Booked</p>
                                <p style="margin: 0; font-size: 0.9rem; color: var(--color-text-light);">This room is currently unavailable</p>
                            </div>
                        <?php elseif ($room['available_rooms'] <= 2): ?>
                            <i class="fas fa-exclamation-circle" style="font-size: 1.5rem; color: #ff9800;"></i>
                            <div>
                                <p style="margin: 0; font-weight: 600; color: #ff9800;">Only <?php echo $room['available_rooms']; ?> Room<?php echo $room['available_rooms'] > 1 ? 's' : ''; ?> Left!</p>
                                <p style="margin: 0; font-size: 0.9rem; color: var(--color-text-light);">Book now to secure your reservation</p>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-check-circle" style="font-size: 1.5rem; color: #4caf50;"></i>
                            <div>
                                <p style="margin: 0; font-weight: 600; color: #4caf50;"><?php echo $room['available_rooms']; ?>/<?php echo $room['total_rooms']; ?> Available</p>
                                <p style="margin: 0; font-size: 0.9rem; color: var(--color-text-light);">Occupancy: <?php echo round($occupancy); ?>%</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Facts -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg);">
                        <div style="padding: var(--spacing-md); border: 1px solid var(--color-border); border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary);">
                                <i class="fas fa-users" style="margin-right: 0.5rem;"></i><?php echo $room['capacity']; ?>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--color-text-light);">Max Guests</div>
                        </div>
                        <div style="padding: var(--spacing-md); border: 1px solid var(--color-border); border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary);">
                                <i class="fas fa-door-open" style="margin-right: 0.5rem;"></i><?php echo $room['total_rooms']; ?>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--color-text-light);">Total Rooms</div>
                        </div>
                    </div>

                    <!-- Price Section -->
                    <div style="background: linear-gradient(135deg, var(--color-bg-light) 0%, var(--color-bg-white) 100%); padding: var(--spacing-lg); border-radius: 12px; border: 2px solid var(--color-border); margin-bottom: var(--spacing-lg);">
                        <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-bottom: var(--spacing-sm);">
                            <span style="font-size: 2.5rem; font-weight: 700; color: var(--color-primary);">
                                $<?php echo number_format($room['price_per_night'], 0); ?>
                            </span>
                            <span style="color: var(--color-text-light);">per night</span>
                        </div>
                        <p style="margin: 0; font-size: 0.9rem; color: var(--color-text-light); margin-bottom: var(--spacing-md);">Plus taxes & applicable fees</p>
                        
                        <div style="padding-top: var(--spacing-md); border-top: 1px solid var(--color-border);">
                            <p style="margin: 0 0 0.5rem 0; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);">
                                <i class="fas fa-credit-card" style="color: var(--color-primary); margin-right: 0.5rem;"></i>Payment Options:
                            </p>
                            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.9rem; color: var(--color-text-light);">
                                <li>30% deposit to secure reservation</li>
                                <li>Full payment upon check-in or upfront</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Booking Button -->
                    <?php if ($room['available_rooms'] > 0): ?>
                        <a href="booking/index.php?room=<?php echo $room['id']; ?>" class="btn" style="display: block; width: 100%; padding: 18px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: var(--transition); margin-bottom: var(--spacing-sm);">
                            <i class="fas fa-calendar-check" style="margin-right: 0.5rem;"></i>Book This Room Now
                        </a>
                        <a href="booking/index.php?room=<?php echo $room['id']; ?>" onmouseover="this.style.background = '#c4935f'" onmouseout="this.style.background = 'var(--color-secondary)'" style="display: block; width: 100%; padding: 18px; background: var(--color-secondary); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: var(--transition);">
                            <i class="fas fa-heart" style="margin-right: 0.5rem;"></i>Add to Wishlist
                        </a>
                    <?php else: ?>
                        <button disabled style="display: block; width: 100%; padding: 18px; background: #ccc; color: #999; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 700; cursor: not-allowed; margin-bottom: var(--spacing-sm);">
                            <i class="fas fa-ban" style="margin-right: 0.5rem;"></i>Fully Booked
                        </button>
                        <a href="rooms.php" style="display: block; width: 100%; padding: 18px; background: var(--color-secondary); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: var(--transition);">
                            <i class="fas fa-search" style="margin-right: 0.5rem;"></i>See Other Rooms
                        </a>
                    <?php endif; ?>

                    <!-- Back to Rooms Link -->
                    <a href="rooms.php" style="display: inline-block; margin-top: var(--spacing-md); text-decoration: none; color: var(--color-primary); font-weight: 600;">
                        <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>Back to All Rooms
                    </a>
                </div>
            </div>

            <!-- Full Description Section -->
            <div style="margin-bottom: var(--spacing-xl);">
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: var(--spacing-md); color: var(--color-primary);">About This Room</h2>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-light); margin-bottom: var(--spacing-md);">
                    <?php echo htmlspecialchars($room['description']); ?>
                </p>
                <p style="font-size: 1rem; line-height: 1.8; color: var(--color-text-light);">
                    This luxurious <?php echo htmlspecialchars($room['type']); ?> room is designed to provide the ultimate in comfort and elegance. Perfect for both leisure and business travelers, our <?php echo htmlspecialchars($room['name']); ?> features premium furnishings, state-of-the-art amenities, and breathtaking views to enhance your stay.
                </p>
            </div>

            <!-- Amenities Section -->
            <div style="margin-bottom: var(--spacing-xl);">
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: var(--spacing-md); color: var(--color-primary);">Room Amenities & Features</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
                    <?php foreach ($amenities as $amenity): ?>
                        <div style="padding: var(--spacing-md); background: var(--color-bg-light); border-radius: 12px; border-left: 4px solid var(--color-primary);">
                            <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                <i class="<?php echo htmlspecialchars($amenity['icon']); ?>" style="font-size: 1.5rem; color: var(--color-primary);"></i>
                                <h3 style="margin: 0; font-size: 1.1rem; color: var(--color-text-dark);"><?php echo htmlspecialchars($amenity['name']); ?></h3>
                            </div>
                            <p style="margin: 0; font-size: 0.95rem; color: var(--color-text-light);">Premium feature included with this room</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Room Comparison CTA -->
            <!-- <div style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); color: white; padding: var(--spacing-xl); border-radius: 12px; text-align: center; margin-bottom: var(--spacing-xl);">
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: var(--spacing-md);">Want to Compare?</h2>
                <p style="margin-bottom: var(--spacing-md); font-size: 1.1rem;">View all our luxury rooms and suites to find the perfect fit for your needs.</p>
                <a href="rooms.php" style="display: inline-block; padding: 14px 30px; background: white; color: var(--color-primary); border-radius: 6px; text-decoration: none; font-weight: 700; transition: var(--transition);">
                    <i class="fas fa-th" style="margin-right: 0.5rem;"></i>View All Rooms
                </a>
            </div> -->

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
        // Gallery switching
        function switchImage(element, imageSrc) {
            // Update main image
            document.getElementById('main-image').src = imageSrc;
            
            // Update active thumbnail border
            document.querySelectorAll('#gallery-main').forEach(el => el.style.border = '3px solid var(--color-border)');
            if (element?.parentElement) {
                element.parentElement.style.borderColor = 'var(--color-primary)';
            }
        }

        // Mobile menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');
        
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
