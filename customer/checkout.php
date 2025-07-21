<?php
/**
 * Checkout Page & Payment Handler - TheFresh.Corner Customer
 *
 * This file displays the checkout summary and handles payment simulation for customer orders.
 *
 * Key Features:
 * - Only accessible by authenticated customers (session check).
 * - Fetches order details, calculates subtotal and delivery fee, and displays a full price breakdown.
 * - Shows scheduled time, delivery/pickup info, and interactive Google Maps embed for delivery address.
 * - Handles payment simulation: updates order total, inserts payment record, and redirects to order history.
 * - Responsive UI with styled cards, icons, and secure payment section.
 * - Includes loading state for payment button and smooth scrolling for anchor links.
 *
 * Maintenance Notes:
 * - Ensure payment logic matches real payment gateway integration if implemented.
 * - Extend checkout logic for new delivery types, fees, or payment methods.
 * - Validate all user input and order data for security.
 * - Avoid leaking sensitive information in error messages.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 *
 */

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$order_id = $_GET['order_id'];
$stmt = $pdo->prepare("SELECT o.*, m.name AS product_name, m.price AS product_price FROM orders o JOIN menu m ON o.menu_id = m.id WHERE o.id = ? AND o.customer_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

// Calculate delivery fee
$delivery_fee = $order['delivery_type'] == 'delivery' ? 500 : 0;
$subtotal = $order['product_price'] * $order['quantity'];
// FIX: Calculate the actual total including delivery fee
$total_amount = $subtotal + $delivery_fee;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reference = 'TRX_' . time();
    
    // FIX: Update the order's total_price to include delivery fee before payment
    $update_stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $update_stmt->execute([$total_amount, $order_id]);
    
    // Insert payment record with correct total to database
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, method, status, reference, amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, 'Paystack', 'completed', $reference, $total_amount]);
    
    $_SESSION['message'] = "Payment simulated successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: order_history.php");
    exit;
}

?>

<?php include '../includes/header.php'; ?>

<style>
    .checkout-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .checkout-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .checkout-header {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        padding: 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .checkout-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .checkout-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .checkout-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }
    
    .order-summary {
        padding: 2rem;
    }
    
    .order-item {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #667eea;
        position: relative;
        overflow: hidden;
    }
    
    .order-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .product-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .product-name {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
    }
    
    .product-price {
        font-size: 1.2rem;
        color: #667eea;
        font-weight: 600;
    }
    
    .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .detail-item {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .detail-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.5rem;
    }
    
    .detail-value {
        font-weight: bold;
        color: #333;
    }
    
    .delivery-info {
        background: #e8f5e8;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #4CAF50;
    }
    
    .delivery-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .delivery-icon {
        background: #4CAF50;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    
    .delivery-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
    }
    
    .delivery-address {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 1px solid #e0e0e0;
    }
    
    .map-container {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        height: 300px;
        position: relative;
    }
    
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .price-breakdown {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .price-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 1.1rem;
    }
    
    .price-row.total {
        font-weight: bold;
        font-size: 1.3rem;
        color: #667eea;
        border-top: 2px solid #e9ecef;
        padding-top: 0.75rem;
        margin-top: 1rem;
    }
    
    .payment-section {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .payment-icon {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
    }
    
    .payment-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .payment-subtitle {
        color: #666;
        margin-bottom: 2rem;
    }
    
    .payment-btn {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-size: 1.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        min-width: 250px;
    }
    
    .payment-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(76, 175, 80, 0.4);
    }
    
    .payment-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .payment-btn:hover::before {
        left: 100%;
    }
    
    .breadcrumb {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        backdrop-filter: blur(10px);
    }
    
    .breadcrumb a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .breadcrumb a:hover {
        color: white;
    }
    
    .breadcrumb .active {
        color: white;
        font-weight: 600;
    }
    
    .security-info {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        backdrop-filter: blur(5px);
    }
    
    .security-info p {
        margin: 0;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
        text-align: center;
    }
    
    .scheduled-time {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .scheduled-time i {
        color: #f39c12;
        margin-right: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .checkout-container {
            padding: 1rem;
        }
        
        .checkout-title {
            font-size: 2rem;
        }
        
        .order-details {
            grid-template-columns: 1fr;
        }
        
        .product-info {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .map-container {
            height: 250px;
        }
        
        .payment-btn {
            min-width: 200px;
        }
    }
</style>

<div class="checkout-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="../customer/dashboard.php">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="../customer/menu.php">Menu</a>
            <span class="mx-2">/</span>
            <a href="order.php?menu_id=<?php echo $order['menu_id']; ?>">Order</a>
            <span class="mx-2">/</span>
            <span class="active">Checkout</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <!-- Checkout Header -->
                <div class="checkout-card">
                    <div class="checkout-header">
                        <div class="checkout-title">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Checkout
                        </div>
                        <div class="checkout-subtitle">Review your order and complete payment</div>
                    </div>

                    <div class="order-summary">
                        <!-- Order Item -->
                        <div class="order-item">
                            <div class="product-info">
                                <div class="product-name">
                                    <i class="fas fa-utensils me-2"></i>
                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                </div>
                                <div class="product-price">
                                    ₦<?php echo number_format($order['product_price'], 2); ?>
                                </div>
                            </div>

                            <div class="order-details">
                                <div class="detail-item">
                                    <div class="detail-label">Quantity</div>
                                    <div class="detail-value"><?php echo $order['quantity']; ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Unit Price</div>
                                    <div class="detail-value">₦<?php echo number_format($order['product_price'], 2); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Subtotal</div>
                                    <div class="detail-value">₦<?php echo number_format($subtotal, 2); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Order ID</div>
                                    <div class="detail-value">#<?php echo $order['id']; ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Scheduled Time -->
                        <div class="scheduled-time">
                            <i class="fas fa-clock"></i>
                            <strong>Scheduled for:</strong> 
                            <?php echo date('F j, Y \a\t g:i A', strtotime($order['scheduled_time'])); ?>
                        </div>

                        <!-- Delivery Information -->
                        <div class="delivery-info">
                            <div class="delivery-header">
                                <div class="delivery-icon">
                                    <i class="fas fa-<?php echo $order['delivery_type'] == 'delivery' ? 'motorcycle' : 'store'; ?>"></i>
                                </div>
                                <div class="delivery-title">
                                    <?php echo ucfirst($order['delivery_type']); ?> Information
                                </div>
                            </div>

                            <?php if ($order['delivery_type'] == 'delivery'): ?>
                                <div class="delivery-address">
                                    <strong><i class="fas fa-map-marker-alt me-2"></i>Delivery Address:</strong>
                                    <div class="mt-2"><?php echo htmlspecialchars($order['delivery_address']); ?></div>
                                </div>

                                <div class="map-container">
                                    <iframe 
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.835434509374!2d3.379205315316!3d6.524379695279!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s<?php echo urlencode($order['delivery_address']); ?>!5e0!3m2!1sen!2sng!4v1631234567890"
                                        allowfullscreen="" 
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                            <?php else: ?>
                                <div class="delivery-address">
                                    <strong><i class="fas fa-store me-2"></i>Pickup Location:</strong>
                                    <div class="mt-2">TheFresh Corner Main Branch<br>
                                    Janet Duniya Street,Apo, Abuja FCT.</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="price-breakdown">
                            <h5 class="mb-3"><i class="fas fa-receipt me-2"></i>Price Breakdown</h5>
                            <div class="price-row">
                                <span>Subtotal (<?php echo $order['quantity']; ?> item<?php echo $order['quantity'] > 1 ? 's' : ''; ?>):</span>
                                <span>₦<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span><?php echo ucfirst($order['delivery_type']); ?> Fee:</span>
                                <span>₦<?php echo number_format($delivery_fee, 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Tax & Service:</span>
                                <span>₦0.00</span>
                            </div>
                            <div class="price-row total">
                                <span>Total Amount:</span>
                                <span>₦<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="payment-section">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="payment-title">Secure Payment</div>
                            <div class="payment-subtitle">
                                Complete your order with our secure payment system
                            </div>

                            <form method="POST" action="" id="paymentForm">
                                <button type="submit" class="payment-btn" id="payBtn">
                                    <i class="fas fa-lock me-2"></i>
                                    Pay ₦<?php echo number_format($total_amount, 2); ?>
                                </button>
                            </form>

                            <div class="security-info">
                                <p>
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Your payment information is secure and encrypted
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const payBtn = document.getElementById('payBtn');
        
        // Disable button and show loading state
        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
        
        // Add a small delay to show the loading state
        setTimeout(() => {
            // The form will submit normally after the delay
            this.submit();
        }, 1000);
        
        e.preventDefault();
        return false;
    });
    
    // Add smooth scrolling for any anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>