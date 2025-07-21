<?php
/**
 * Place Order Page & Handler - TheFresh.Corner Customer
 *
 * This file displays the order form for a selected product and handles order creation for customers.
 *
 * Key Features:
 * - Only accessible by authenticated customers (session check).
 * - Fetches product details from the menu for the selected item.
 * - Allows customers to select quantity, delivery type (pickup/delivery), address, and scheduled time.
 * - Calculates total price including delivery fee and displays a live order summary.
 * - Validates form input and prevents submission without required fields.
 * - Inserts new order into the database and redirects to checkout on success.
 * - Responsive UI with modern cards, icons, and interactive controls.
 *
 * Maintenance Notes:
 * - Extend order logic for new delivery types, fees, or product options.
 * - Ensure validation and security best practices are followed.
 * - Avoid leaking sensitive information in error messages.
 * - Consider adding order confirmation, notifications, or payment integration.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07
 */

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$menu_id = $_GET['menu_id'];
$stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$menu_id]);
$product = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = $_POST['quantity'];
    $delivery_type = $_POST['delivery_type'];
    $delivery_address = $_POST['delivery_address'] ?? '';
    $scheduled_time = $_POST['scheduled_time'];
    $total_price = $product['price'] * $quantity;

    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, vendor_id, menu_id, quantity, delivery_type, delivery_address, scheduled_time, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $product['vendor_id'], $menu_id, $quantity, $delivery_type, $delivery_address, $scheduled_time, $total_price]);

    $order_id = $pdo->lastInsertId();
    header("Location: checkout.php?order_id=$order_id");
    exit;
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .order-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .order-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .order-card:hover {
        transform: translateY(-5px);
    }
    
    .product-header {
        background: linear-gradient(135deg, #ff6b6b, #ffa726);
        color: white;
        padding: 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .product-header::before {
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
    
    .product-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .product-price {
        font-size: 1.5rem;
        font-weight: 600;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }
    
    .form-section {
        padding: 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.95rem;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        background: white;
        outline: none;
    }
    
    .quantity-controls {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 12px;
        padding: 0.25rem;
        border: 2px solid #e9ecef;
        transition: border-color 0.3s ease;
    }
    
    .quantity-controls:focus-within {
        border-color: #667eea;
    }
    
    .quantity-btn {
        background: #667eea;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .quantity-btn:hover {
        background: #5a67d8;
        transform: scale(1.05);
    }
    
    .quantity-input {
        border: none;
        background: transparent;
        text-align: center;
        font-size: 1.1rem;
        font-weight: 600;
        width: 60px;
        padding: 0.5rem;
    }
    
    .quantity-input:focus {
        outline: none;
    }
    
    .delivery-options {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    .delivery-option {
        flex: 1;
        position: relative;
    }
    
    .delivery-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    
    .delivery-option label {
        display: block;
        padding: 1rem;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        font-weight: 600;
    }
    
    .delivery-option input[type="radio"]:checked + label {
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .delivery-option:hover label {
        border-color: #667eea;
    }
    
    .delivery-address {
        margin-top: 1rem;
        opacity: 0;
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .delivery-address.show {
        opacity: 1;
        max-height: 100px;
    }
    
    .order-summary {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    
    .summary-row.total {
        font-weight: bold;
        font-size: 1.2rem;
        color: #667eea;
        border-top: 2px solid #e9ecef;
        padding-top: 0.5rem;
        margin-top: 1rem;
    }
    
    .checkout-btn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }
    
    .checkout-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .checkout-btn:hover::before {
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
    
    @media (max-width: 768px) {
        .order-container {
            padding: 1rem;
        }
        
        .product-title {
            font-size: 2rem;
        }
        
        .delivery-options {
            flex-direction: column;
        }
        
        .quantity-controls {
            max-width: 200px;
            margin: 0 auto;
        }
    }
</style>

<div class="order-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="../customer/dashboard.php">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="../customer/menu.php">Menu</a>
            <span class="mx-2">/</span>
            <span class="active">Order</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="order-card">
                    <!-- Product Header -->
                    <div class="product-header">
                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price">₦<?php echo number_format($product['price'], 2); ?></div>
                    </div>

                    <!-- Order Form -->
                    <div class="form-section">
                        <form method="POST" action="" id="orderForm">
                            <!-- Quantity Selection -->
                            <div class="form-group">
                                <label for="quantity" class="form-label">
                                    <i class="fas fa-plus-circle me-2"></i>Quantity
                                </label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">−</button>
                                    <input type="number" class="quantity-input" id="quantity" name="quantity" value="1" min="1" required readonly>
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                                </div>
                            </div>

                            <!-- Delivery Type -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-truck me-2"></i>Delivery Options
                                </label>
                                <div class="delivery-options">
                                    <div class="delivery-option">
                                        <input type="radio" id="pickup" name="delivery_type" value="pickup" required>
                                        <label for="pickup">
                                            <i class="fas fa-store mb-2 d-block"></i>
                                            Pickup
                                        </label>
                                    </div>
                                    <div class="delivery-option">
                                        <input type="radio" id="delivery" name="delivery_type" value="delivery" required>
                                        <label for="delivery">
                                            <i class="fas fa-motorcycle mb-2 d-block"></i>
                                            Delivery
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Address -->
                            <div class="delivery-address" id="delivery_address_field">
                                <label for="delivery_address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
                                </label>
                                <input type="text" class="form-control" id="delivery_address" name="delivery_address" placeholder="Enter your delivery address">
                            </div>

                            <!-- Scheduled Time -->
                            <div class="form-group">
                                <label for="scheduled_time" class="form-label">
                                    <i class="fas fa-clock me-2"></i>Scheduled Time
                                </label>
                                <input type="datetime-local" class="form-control" id="scheduled_time" name="scheduled_time" required>
                            </div>

                            <!-- Order Summary -->
                            <div class="order-summary">
                                <h5 class="mb-3">Order Summary</h5>
                                <div class="summary-row">
                                    <span>Item Price:</span>
                                    <span id="item-price">₦<?php echo number_format($product['price'], 2); ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Quantity:</span>
                                    <span id="quantity-display">1</span>
                                </div>
                                <div class="summary-row">
                                    <span>Delivery Fee:</span>
                                    <span id="delivery-fee">₦0.00</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total:</span>
                                    <span id="total-price">₦<?php echo number_format($product['price'], 2); ?></span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="checkout-btn">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Proceed to Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const basePrice = <?php echo $product['price']; ?>;
    const deliveryFee = 500; // ₦500 delivery fee
    
    function changeQuantity(change) {
        const quantityInput = document.getElementById('quantity');
        let currentValue = parseInt(quantityInput.value);
        let newValue = currentValue + change;
        
        if (newValue < 1) newValue = 1;
        
        quantityInput.value = newValue;
        updateOrderSummary();
    }
    
    function updateOrderSummary() {
        const quantity = parseInt(document.getElementById('quantity').value);
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
        
        const subtotal = basePrice * quantity;
        const delivery = deliveryType && deliveryType.value === 'delivery' ? deliveryFee : 0;
        const total = subtotal + delivery;
        
        document.getElementById('quantity-display').textContent = quantity;
        document.getElementById('delivery-fee').textContent = '₦' + delivery.toLocaleString('en-NG', {minimumFractionDigits: 2});
        document.getElementById('total-price').textContent = '₦' + total.toLocaleString('en-NG', {minimumFractionDigits: 2});
    }
    
    // Handle delivery type change
    document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const deliveryAddressField = document.getElementById('delivery_address_field');
            const deliveryAddress = document.getElementById('delivery_address');
            
            if (this.value === 'delivery') {
                deliveryAddressField.classList.add('show');
                deliveryAddress.required = true;
            } else {
                deliveryAddressField.classList.remove('show');
                deliveryAddress.required = false;
                deliveryAddress.value = '';
            }
            
            updateOrderSummary();
        });
    });
    
    // Set minimum date to current date and time
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('scheduled_time').min = now.toISOString().slice(0, 16);
        
        // Set default to 1 hour from now
        now.setHours(now.getHours() + 1);
        document.getElementById('scheduled_time').value = now.toISOString().slice(0, 16);
    });
    
    // Form validation
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
        const deliveryAddress = document.getElementById('delivery_address');
        
        if (!deliveryType) {
            e.preventDefault();
            alert('Please select a delivery option');
            return;
        }
        
        if (deliveryType.value === 'delivery' && !deliveryAddress.value.trim()) {
            e.preventDefault();
            alert('Please enter a delivery address');
            deliveryAddress.focus();
            return;
        }
    });
</script>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>