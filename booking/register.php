<?php
require 'db_connect.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered. Please login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (name, email, password_hash, phone) VALUES (?, ?, ?, ?)');
            $insert->execute([$name, $email, $hash, $phone]);
            $user_id = $pdo->lastInsertId();
            // Log the user in
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $return_to = $_POST['return'] ?? ($_GET['return'] ?? null);
            if ($return_to) {
                header('Location: ' . $return_to);
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - The Villa</title>
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
        .auth-visual h3 { font-size: 1.8rem; color:#fff; font-family: var(--font-heading); line-height: 1.3; }
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
        .password-strength { margin-top: 8px; height: 4px; background: #f0f0f0; border-radius: 4px; overflow: hidden; }
        .password-strength-bar { height: 100%; width: 0%; transition: all 0.3s ease; border-radius: 4px; }
        .strength-weak { background: #ff6b6b; width: 33%; }
        .strength-medium { background: #ffa94d; width: 66%; }
        .strength-strong { background: #51cf66; width: 100%; }
        .strength-text { font-size: 0.8rem; margin-top: 4px; font-weight: 600; }
        .btn-submit { width: 100%; padding: 14px; border: none; background: linear-gradient(135deg, #d4af37 0%, #c99d2f 100%); color: #1a1a1a; font-size: 1rem; font-weight: 700; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3); }
        .btn-submit:active { transform: translateY(0); }
        .alert { padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.95rem; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-error { background: #ffe6e6; color: #9b1c1c; border-left: 4px solid #ff6b6b; }
        .terms { font-size: 0.85rem; color: #666; margin-top: 20px; line-height: 1.5; }
        .terms a { color: #667eea; text-decoration: none; font-weight: 600; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.95rem; }
        .auth-footer a { color: #667eea; text-decoration: none; font-weight: 700; }
        @media (max-width: 768px) { .auth-container { grid-template-columns: 1fr; } .auth-visual { padding: 40px 30px; order: -1; } .auth-form-wrap { padding: 40px 30px; } .auth-visual h3 { font-size: 1.5rem; } }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-visual">
                <div>
                    <div class="logo">The <span>Villa</span></div>
                    <h3>Join Our Luxury Community</h3>
                    <p>Create your account to access exclusive bookings, special offers, and personalized experiences at The Villa Hotel.</p>
                </div>
                <div>
                    <p style="font-size:0.9rem;">Already have an account?</p>
                    <a href="login.php" style="display: inline-block; margin-top: 8px;"><i class="fas fa-arrow-right" style="margin-right: 8px;"></i>Login to your account</a>
                </div>
            </div>
            <div class="auth-form-wrap">
                <div>
                    <h2>Create Account</h2>
                    <p class="subtitle">Get started in just a few minutes</p>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <input type="hidden" name="return" value="<?php echo htmlspecialchars($_GET['return'] ?? 'dashboard.php'); ?>" />
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user" style="margin-right: 8px; color: #d4af37;"></i>Full Name</label>
                            <input type="text" name="name" id="name" required placeholder="John Doe" />
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope" style="margin-right: 8px; color: #d4af37;"></i>Email Address</label>
                            <input type="email" name="email" id="email" required placeholder="you@example.com" />
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock" style="margin-right: 8px; color: #d4af37;"></i>Password</label>
                            <input type="password" name="password" id="password" required placeholder="Create a strong password" oninput="checkPasswordStrength()" />
                            <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone" style="margin-right: 8px; color: #d4af37;"></i>Phone (Optional)</label>
                            <input type="text" name="phone" id="phone" placeholder="+1 (555) 123-4567" />
                        </div>
                        <button type="submit" class="btn-submit"><i class="fas fa-user-check" style="margin-right: 8px;"></i>Create Account</button>
                    </form>
                    <p class="terms">By creating an account, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            strengthBar.className = 'password-strength-bar';
            if (password.length === 0) { strengthText.textContent = ''; strengthBar.style.width = '0'; }
            else if (strength <= 1) { strengthBar.classList.add('strength-weak'); strengthText.textContent = 'Weak password'; strengthText.style.color = '#ff6b6b'; }
            else if (strength === 2) { strengthBar.classList.add('strength-medium'); strengthText.textContent = 'Medium password'; strengthText.style.color = '#ffa94d'; }
            else { strengthBar.classList.add('strength-strong'); strengthText.textContent = 'Strong password'; strengthText.style.color = '#51cf66'; }
        }
    </script>
</body>
</html>