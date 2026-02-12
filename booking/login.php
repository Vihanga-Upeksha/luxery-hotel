<?php
// Ensure session is started and DB connection is loaded
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $return_to = $_POST['return'] ?? ($_GET['return'] ?? null);

    if (!$email || !$password) {
        $error = 'Please enter email and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Login DB error: ' . $e->getMessage());
            $error = 'Server error. Please try again later.';
            $user = false;
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            // Redirect back if return param provided
            // sanitize return path
            if ($return_to) {
                $safe_return = filter_var($return_to, FILTER_SANITIZE_URL);
                header('Location: ' . $safe_return);
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - The Villa</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-body); background: linear-gradient(135deg, #ffffff 0%, #faf9f7 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .auth-wrapper { width: 100%; max-width: 900px; }
        .auth-container { display: grid; grid-template-columns: 1fr 1fr; gap: 0; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(212, 175, 55, 0.15); animation: slideIn 0.5s ease-out; border: 1px solid #f0f0f0; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .auth-visual { background: linear-gradient(135deg, rgba(26, 26, 26, 0.8) 0%, rgba(45, 45, 45, 0.8) 100%), url('../images/manuel-moreno-DGa0LQ0yDPc-unsplash.jpg'); background-size: cover; background-position: center; color: white; padding: 60px 40px; display: flex; flex-direction: column; justify-content: center; gap: 30px; border-right: 3px solid #d4af37; }
        .auth-visual .logo { font-family: var(--font-heading); color:#fff; font-size: 2rem; font-weight: 700; margin-bottom: 10px; }
        .auth-visual .logo span { color: #d4af37; }
        .auth-visual h3 { font-size: 1.8rem; font-family: var(--font-heading); color:#fff; line-height: 1.3; }
        .auth-visual p { font-size: 0.95rem; opacity: 0.9; line-height: 1.6; }
        .auth-visual a { color: #d4af37; text-decoration: none; font-weight: 600; }
        .auth-form-wrap { padding: 60px 40px; display: flex; flex-direction: column; justify-content: center; }
        .auth-form-wrap h2 { font-size: 1.8rem; font-family: var(--font-heading); margin-bottom: 10px; color: #1a1a1a; }
        .auth-form-wrap .subtitle { font-size: 0.9rem; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; font-weight: 600; color: #333; margin-bottom: 8px; font-size: 0.95rem; }
        .form-group input { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; font-family: inherit; }
        .form-group input:focus { outline: none; border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1); background: #fffef8; }
        .form-group input::placeholder { color: #aaa; }
        .remember-forgot { display: flex; justify-content: space-between; align-items: center; margin: 15px 0 25px; font-size: 0.9rem; }
        .remember-forgot a { color: #d4af37; text-decoration: none; font-weight: 600; }
        .remember-forgot label { margin: 0; font-weight: 500; display: flex; align-items: center; cursor: pointer; }
        .remember-forgot input[type="checkbox"] { width: 18px; height: 18px; margin-right: 8px; cursor: pointer; accent-color: #d4af37; }
        .btn-submit { width: 100%; padding: 14px; border: none; background: linear-gradient(135deg, #d4af37 0%, #c99d2f 100%); color: #1a1a1a; font-size: 1rem; font-weight: 700; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3); }
        .btn-submit:active { transform: translateY(0); }
        .alert { padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.95rem; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-error { background: #ffe6e6; color: #9b1c1c; border-left: 4px solid #ff6b6b; }
        .divider { text-align: center; margin: 25px 0; position: relative; }
        .divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e0e0e0; }
        .divider span { background: white; padding: 0 10px; position: relative; color: #999; font-size: 0.9rem; }
        @media (max-width: 768px) { .auth-container { grid-template-columns: 1fr; } .auth-visual { padding: 40px 30px; order: -1; } .auth-form-wrap { padding: 40px 30px; } .auth-visual h3 { font-size: 1.5rem; } }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-visual">
                <div>
                    <div class="logo">The <span>Villa</span></div>
                    <h3>Welcome Back</h3>
                    <p>Log in to your account to access your bookings, manage reservations, and enjoy exclusive member benefits.</p>
                </div>
                <div>
                    <p style="font-size:0.9rem;">Don't have an account?</p>
                    <a href="register.php" style="display: inline-block; margin-top: 8px;"><i class="fas fa-user-plus" style="margin-right: 8px;"></i>Create new account</a>
                </div>
            </div>
            <div class="auth-form-wrap">
                <div>
                    <h2>Member Login</h2>
                    <p class="subtitle">Sign in to your account</p>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <input type="hidden" name="return" value="<?php echo htmlspecialchars($_GET['return'] ?? 'dashboard.php'); ?>" />
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope" style="margin-right: 8px; color: #d4af37;"></i>Email Address</label>
                            <input type="email" name="email" id="email" required placeholder="you@example.com" />
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock" style="margin-right: 8px; color: #d4af37;"></i>Password</label>
                            <input type="password" name="password" id="password" required placeholder="Enter your password" />
                        </div>
                        <div class="remember-forgot">
                            <label><input type="checkbox" name="remember" /> Remember me</label>
                            <a href="#"><i class="fas fa-key" style="margin-right: 4px;"></i>Forgot password?</a>
                        </div>
                        <button type="submit" class="btn-submit"><i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>