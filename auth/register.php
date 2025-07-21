<?php
/**
 * Registration Page & Handler - TheFresh.Corner
 *
 * This file provides the registration form and handles new user sign-ups for customers and vendors.
 *
 * Key Features:
 * - Accepts name, email, contact, password, and role via POST.
 * - Sanitizes and validates user input.
 * - Hashes passwords securely before storing.
 * - Sets user status to 'pending' for admin approval.
 * - Displays success or error messages after registration.
 * - Responsive UI with promotional section and password visibility toggle.
 *
 * Maintenance Notes:
 * - Extend registration logic for new roles or additional fields as needed.
 * - Ensure validation and security best practices are followed.
 * - Avoid leaking sensitive information in error messages.
 * - Consider adding email verification or CAPTCHA for security.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07-19
 */

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING); // Added for contact
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Set status based on role (no admin role, so all will be pending)
    $status = 'pending';

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, contact, status) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $email, $password, $role, $contact, $status]);
        $_SESSION['message'] = "Registration successful. Awaiting admin approval.";
        $_SESSION['message_type'] = "success";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['message'] = "Registration failed: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
}
?>

<?php include '../includes/header.php'; ?>

<style>
.register-container {
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: stretch;
}

.register-section {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 40px;
    background: #f8f9fa;
}

.register-form-container {
    width: 100%;
    max-width: 450px;
}

.register-title {
    font-size: 2.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 40px;
    text-align: center;
}

.form-group {
    margin-bottom: 20px;
}

.form-input {
    width: 100%;
    padding: 15px 20px;
    padding-left: 50px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    background: white;
    box-sizing: border-box;
    position: relative;
}

.form-input:focus {
    outline: none;
    border-color: #ff6b1a;
    box-shadow: 0 0 0 3px rgba(255, 107, 26, 0.1);
}

.form-input::placeholder {
    color: #999;
}

.input-container {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 1.1rem;
    z-index: 1;
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

.select-input {
    width: 100%;
    padding: 15px 20px;
    padding-left: 50px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    background: white;
    box-sizing: border-box;
    cursor: pointer;
}

.select-input:focus {
    outline: none;
    border-color: #ff6b1a;
    box-shadow: 0 0 0 3px rgba(255, 107, 26, 0.1);
}

.register-btn {
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
    margin-top: 10px;
}

.register-btn:hover {
    background: #e55a0f;
}

.register-btn:active {
    transform: translateY(1px);
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
    font-size: 2.5rem;
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

.promo-signin-btn {
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

.promo-signin-btn:hover {
    background: white;
    color: #ff6b1a;
    text-decoration: none;
}

.promo-illustration {
    margin-top: 40px;
    width: 300px;
    height: 200px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 200"><rect x="50" y="50" width="120" height="80" rx="15" fill="rgba(0,0,0,0.8)"/><rect x="55" y="55" width="110" height="70" rx="10" fill="%23ff6b1a"/><circle cx="230" cy="150" r="40" fill="rgba(255,255,255,0.9)"/><rect x="215" y="135" width="30" height="40" rx="5" fill="%23333"/><circle cx="220" cy="140" r="8" fill="white"/><rect x="200" y="185" width="60" height="10" rx="5" fill="%23333"/><circle cx="210" cy="190" r="8" fill="%23333"/><circle cx="250" cy="190" r="8" fill="%23333"/><path d="M170 90 Q200 70 230 90" stroke="white" stroke-width="3" fill="none" opacity="0.8"/><text x="110" y="95" fill="white" font-family="Arial" font-size="12">15min</text></svg>') no-repeat center;
    background-size: contain;
}

@media (max-width: 768px) {
    .register-container {
        flex-direction: column;
        min-height: auto;
    }
    
    .register-section {
        order: 1;
        padding: 40px 20px;
    }
    
    .promo-section {
        order: 2;
        padding: 40px 20px;
    }
    
    .promo-title {
        font-size: 2rem;
    }
    
    .promo-illustration {
        width: 250px;
        height: 150px;
        margin-top: 20px;
    }
}
</style>

<div class="register-container">
    <div class="register-section">
        <div class="register-form-container">
            <h2 class="register-title">Sign up</h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <div class="input-container">
                        <span class="input-icon">👤</span>
                        <input type="text" class="form-input" name="name" placeholder="Full Name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-container">
                        <span class="input-icon">✉️</span>
                        <input type="email" class="form-input" name="email" placeholder="Email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-container">
                        <span class="input-icon">📞</span>
                        <input type="tel" class="form-input" name="contact" placeholder="Contact No" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-container password-input-container">
                        <span class="input-icon">🔒</span>
                        <input type="password" class="form-input" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <span id="password-icon">👁️</span>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-container">
                        <span class="input-icon">👥</span>
                        <select class="select-input" name="role" required>
                            <option value="">Select Role</option>
                            <option value="customer">Customer</option>
                            <option value="vendor">Vendor</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="register-btn">SIGN UP</button>
            </form>
        </div>
    </div>
    
    <div class="promo-section">
        <div class="promo-content">
            <h1 class="promo-title">Our Customer?</h1>
            <p class="promo-text">Sign in to continue enjoying our delicious Yoghurt-Fura and manage your orders seamlessly.</p>
            <a href="login.php" class="promo-signin-btn">Sign In</a>
            <div class="promo-illustration"></div>
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