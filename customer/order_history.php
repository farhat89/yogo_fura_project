<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, m.name AS product_name, v.name AS vendor_name FROM orders o JOIN menu m ON o.menu_id = m.id JOIN users v ON o.vendor_id = v.id WHERE o.customer_id = ? ORDER BY o.id DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<style>
    .orders-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .orders-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .orders-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: shimmer 4s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .orders-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: white;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .orders-subtitle {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.9);
        position: relative;
        z-index: 1;
    }
    
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .orders-grid {
        display: grid;
        gap: 1.5rem;
    }
    
    .order-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .order-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .order-id {
        font-size: 1.1rem;
        font-weight: bold;
        color: #333;
    }
    
    .order-date {
        font-size: 0.9rem;
        color: #666;
    }
    
    .order-content {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: center;
    }
    
    .order-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .product-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .vendor-name {
        font-size: 0.95rem;
        color: #667eea;
        font-weight: 500;
    }
    
    .order-meta {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #666;
    }
    
    .meta-item i {
        color: #667eea;
    }
    
    .order-summary {
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem;
    }
    
    .order-price {
        font-size: 1.4rem;
        font-weight: bold;
        color: #667eea;
    }
    
    .order-quantity {
        font-size: 0.9rem;
        color: #666;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.5rem;
    }
    
    .status-pending {
        background: linear-gradient(135deg, #ffc107, #ff9800);
        color: white;
    }
    
    .status-completed {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .status-cancelled {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        color: white;
    }
    
    .status-processing {
        background: linear-gradient(135deg, #17a2b8, #007bff);
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        margin-top: 2rem;
    }
    
    .empty-icon {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
    }
    
    .empty-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .empty-subtitle {
        color: #666;
        margin-bottom: 2rem;
    }
    
    .cta-button {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .cta-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .cta-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .cta-button:hover::before {
        left: 100%;
    }
    
    .filter-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .filter-label {
        font-weight: 600;
        color: #333;
        margin-right: 0.5rem;
    }
    
    .filter-select {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        background: white;
        color: #333;
        font-size: 0.9rem;
        transition: border-color 0.3s ease;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .orders-count {
        margin-left: auto;
        color: #666;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .orders-container {
            padding: 1rem;
        }
        
        .orders-title {
            font-size: 2rem;
        }
        
        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .order-content {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .order-summary {
            align-items: flex-start;
            text-align: left;
        }
        
        .order-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-section {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .orders-count {
            margin-left: 0;
        }
    }
</style>

<div class="orders-container">
    <div class="container">
        <!-- Header Section -->
        <div class="orders-header">
            <div class="orders-title">
                <i class="fas fa-history me-2"></i>
                Order History
            </div>
            <div class="orders-subtitle">Track all your past orders and their status</div>
        </div>

        <?php if (empty($orders)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="empty-title">No Orders Yet</div>
                <div class="empty-subtitle">You haven't placed any orders yet. Start browsing our delicious menu!</div>
                <a href="../customer/menu.php" class="cta-button">
                    <i class="fas fa-utensils me-2"></i>
                    Browse Menu
                </a>
            </div>
        <?php else: ?>
            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-number"><?php echo count($orders); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo count(array_filter($orders, function($o) { return $o['status'] == 'pending'; })); ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo count(array_filter($orders, function($o) { return $o['status'] == 'completed'; })); ?></div>
                    <div class="stat-label">Completed Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-naira-sign"></i>
                    </div>
                    <div class="stat-number">₦<?php echo number_format(array_sum(array_column($orders, 'total_price')), 0); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <span class="filter-label">
                    <i class="fas fa-filter me-2"></i>
                    Filter by:
                </span>
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select class="filter-select" id="vendorFilter">
                    <option value="">All Vendors</option>
                    <?php 
                    $vendors = array_unique(array_column($orders, 'vendor_name'));
                    foreach ($vendors as $vendor): ?>
                        <option value="<?php echo htmlspecialchars($vendor); ?>"><?php echo htmlspecialchars($vendor); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="orders-count">
                    <i class="fas fa-list me-1"></i>
                    <span id="ordersCount"><?php echo count($orders); ?></span> orders found
                </div>
            </div>

            <!-- Orders Grid -->
            <div class="orders-grid" id="ordersGrid">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card" data-status="<?php echo $order['status']; ?>" data-vendor="<?php echo htmlspecialchars($order['vendor_name']); ?>">
                        <div class="order-header">
                            <div class="order-id">
                                <i class="fas fa-hashtag me-1"></i>
                                Order #<?php echo $order['id']; ?>
                            </div>
                            <div class="order-date">
                                <?php echo date('M j, Y', strtotime($order['created_at'] ?? 'now')); ?>
                            </div>
                        </div>
                        
                        <div class="order-content">
                            <div class="order-info">
                                <div class="product-name">
                                    <i class="fas fa-utensils me-2"></i>
                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                </div>
                                <div class="vendor-name">
                                    <i class="fas fa-store me-1"></i>
                                    by <?php echo htmlspecialchars($order['vendor_name']); ?>
                                </div>
                                <div class="order-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-sort-numeric-up"></i>
                                        Qty: <?php echo $order['quantity']; ?>
                                    </div>
                                    <?php if (isset($order['delivery_type'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-<?php echo $order['delivery_type'] == 'delivery' ? 'motorcycle' : 'store'; ?>"></i>
                                            <?php echo ucfirst($order['delivery_type']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($order['scheduled_time'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('g:i A', strtotime($order['scheduled_time'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="order-summary">
                                <div class="order-price">₦<?php echo number_format($order['total_price'], 2); ?></div>
                                <div class="order-quantity"><?php echo $order['quantity']; ?> item<?php echo $order['quantity'] > 1 ? 's' : ''; ?></div>
                                <div class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php 
                                    $statusIcons = [
                                        'pending' => 'fas fa-clock',
                                        'processing' => 'fas fa-spinner',
                                        'completed' => 'fas fa-check-circle',
                                        'cancelled' => 'fas fa-times-circle'
                                    ];
                                    ?>
                                    <i class="<?php echo $statusIcons[$order['status']] ?? 'fas fa-info-circle'; ?> me-1"></i>
                                    <?php echo ucfirst($order['status']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const statusFilter = document.getElementById('statusFilter');
        const vendorFilter = document.getElementById('vendorFilter');
        const ordersGrid = document.getElementById('ordersGrid');
        const ordersCount = document.getElementById('ordersCount');
        
        function filterOrders() {
            const statusValue = statusFilter.value.toLowerCase();
            const vendorValue = vendorFilter.value.toLowerCase();
            const orderCards = ordersGrid.querySelectorAll('.order-card');
            let visibleCount = 0;
            
            orderCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status').toLowerCase();
                const cardVendor = card.getAttribute('data-vendor').toLowerCase();
                
                const statusMatch = !statusValue || cardStatus === statusValue;
                const vendorMatch = !vendorValue || cardVendor === vendorValue;
                
                if (statusMatch && vendorMatch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (ordersCount) {
                ordersCount.textContent = visibleCount;
            }
        }
        
        if (statusFilter && vendorFilter) {
            statusFilter.addEventListener('change', filterOrders);
            vendorFilter.addEventListener('change', filterOrders);
        }
        
        // Add smooth scroll animation for cards
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1
        });
        
        document.querySelectorAll('.order-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    });
</script>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>