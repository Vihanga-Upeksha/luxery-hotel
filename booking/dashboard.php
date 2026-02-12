<?php
require 'db_connect.php';
require 'config.php';
require 'payment_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Fetch user bookings
$stmt = $pdo->prepare("SELECT b.*, r.name as room_name, r.available_rooms FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.user_id = ? ORDER BY b.check_in DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Bookings - The Villa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="logo">The <span>Villa</span></div>
        <nav>
            <ul>
                <li><a href="index.php">Book Now</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="booking-container" style="max-width:1000px; margin: 4rem auto 6rem;">
        <div class="profile-header">
            <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
            <div>
                <div class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
                <div style="color:var(--color-text-light);">Member since <?php
                    $stmt2 = $pdo->prepare('SELECT created_at FROM users WHERE id = ?'); $stmt2->execute([$user_id]); $u = $stmt2->fetch();
                    echo isset($u['created_at']) ? date('F Y', strtotime($u['created_at'])) : '—';
                ?></div>
            </div>
        </div>

        <h2 style="margin-bottom:1rem;">My Bookings</h2>
        <?php if (empty($bookings)): ?>
            <div class="booking-card">You have no bookings yet. <a href="index.php">Book now</a></div>
        <?php else: ?>
            <div class="booking-card">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Room</th>
                            <th>Rooms</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Status</th>
                            <th>Remaining Rooms</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?php echo $b['id']; ?></td>
                            <td><?php echo htmlspecialchars($b['room_name']); ?></td>
                            <td><?php echo (int)$b['rooms_booked']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($b['check_in'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($b['check_out'])); ?></td>
                            <td><span class="status-pill <?php echo $b['payment_status'] === 'paid_in_full' ? 'status-paid' : 'status-pending'; ?>"><?php echo str_replace('_',' ', $b['payment_status']); ?></span></td>
                            <td><?php echo (int)$b['available_rooms']; ?></td>
                            <td><a href="confirmation.php?id=<?php echo $b['id']; ?>" class="btn">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>