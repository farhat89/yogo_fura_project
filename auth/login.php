<?php
/**
 * Login Page & Authentication Handler - TheFresh.Corner
 *
 * This file provides the login form and handles user authentication for all roles.
 *
 * Key Features:
 * - Accepts email and password via POST.
 * - Sanitizes and validates user input.
 * - Verifies credentials against the database using secure password hashing.
 * - Checks user status (approved/pending) before granting access.
 * - Sets session variables for authenticated users and redirects to role-specific dashboard.
 * - Displays error messages for invalid credentials or pending approval.
 * - Responsive UI with promotional section and password visibility toggle.
 *
 * Maintenance Notes:
 * - Ensure authentication logic remains secure and up-to-date.
 * - Extend login logic for new roles or multi-factor authentication if needed.
 * - Avoid leaking sensitive information in error messages.
 * - Consider rate limiting or logging failed login attempts for security.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07
 */

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        error_log("User found: " . print_r($user, true)); // Debug log
        if (password_verify($password, $user['password'])) {
            if ($user['status'] == 'approved') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                header("Location: ../{$user['role']}/dashboard.php");
                exit;
            } else {
                $_SESSION['message'] = "Your account is pending admin approval.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Invalid password.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Email not found.";
        $_SESSION['message_type'] = "danger";
    }
}
?>

<?php include '../includes/header.php'; ?>

<style>
.login-container {
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: stretch;
}

.promo-section {
    background: linear-gradient(135deg, #ff8c42 0%, #ff6b1a 100%);
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 60px 40px;
    color: white;
    position: relative;
    overflow: hidden;
}

.promo-section::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
    pointer-events: none;
}

.promo-content {
    text-align: center;
    z-index: 1;
    max-width: 400px;
}

.promo-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
}

.promo-text {
    font-size: 1.1rem;
    margin-bottom: 30px;
    opacity: 0.95;
    line-height: 1.6;
}

.promo-signup-btn {
    background: transparent;
    border: 2px solid white;
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.promo-signup-btn:hover {
    background: white;
    color: #ff6b1a;
    text-decoration: none;
}

.promo-illustration {
    margin-top: 40px;
    width: 250px;
    height: 150px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 150"><g fill="white" opacity="0.9"><circle cx="60" cy="40" r="25"/><rect x="45" y="65" width="30" height="40" rx="5"/><circle cx="190" cy="40" r="25"/><rect x="175" y="65" width="30" height="40" rx="5"/><rect x="110" y="80" width="30" height="50" rx="5" fill="rgba(255,255,255,0.8)"/><path d="M95 90 Q125 70 155 90" stroke="white" stroke-width="2" fill="none"/></g></svg>') no-repeat center;
    background-size: contain;
}

.login-section {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 40px;
    background: #f8f9fa;
}

.login-form-container {
    width: 100%;
    max-width: 400px;
}

.login-title {
    font-size: 2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 30px;
    text-align: center;
}

.form-group {
    margin-bottom: 20px;
}

.form-input {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    background: white;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #ff6b1a;
    box-shadow: 0 0 0 3px rgba(255, 107, 26, 0.1);
}

.form-input::placeholder {
    color: #999;
}

.password-input-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 1.1rem;
    padding: 0;
    z-index: 2;
}

.login-btn {
    width: 100%;
    padding: 15px;
    background: #ff6b1a;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.login-btn:hover {
    background: #e55a0f;
}

.login-btn:active {
    transform: translateY(1px);
}

@media (max-width: 768px) {
    .login-container {
        flex-direction: column;
        min-height: auto;
    }
    
    .promo-section {
        padding: 40px 20px;
    }
    
    .promo-title {
        font-size: 2rem;
    }
    
    .promo-illustration {
        width: 200px;
        height: 120px;
        margin-top: 20px;
    }
    
    .login-section {
        padding: 40px 20px;
    }
}
</style>

<div class="login-container">
    <div class="promo-section">
        <div class="promo-content">
            <h1 class="promo-title">New to TheFresh.Corner?</h1>
            <p class="promo-text">Join us today and savor the convenience of ordering authentic Nigerian Yoghurt-Fura online.<br>Enjoy exclusive offers and track your orders with ease.</p>
            <a href="../auth/register.php" class="promo-signup-btn">Sign up</a>
            <div class="promo-illustration"></div>
        </div>
    </div>
    
    <div class="login-section">
        <div class="login-form-container">
            <h2 class="login-title">Sign in</h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" class="form-input" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <div class="password-input-container">
                        <input type="password" class="form-input" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <span id="password-icon">👁️</span>
                        </button>
                    </div>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('password-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        passwordIcon.textContent = '👁️';
    }
}
</script>

<?php include '../includes/footer.php'; ?>