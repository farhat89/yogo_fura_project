<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("SELECT o.*, m.name AS product_name, c.name AS customer_name, v.name AS vendor_name FROM orders o JOIN menu m ON o.menu_id = m.id JOIN users c ON o.customer_id = c.id JOIN users v ON o.vendor_id = v.id ORDER BY o.id DESC");
$orders = $stmt->fetchAll();

// Calculate statistics
$totalOrders = count($orders);
$pendingOrders = count(array_filter($orders, function($order) { return $order['status'] === 'pending'; }));
$confirmedOrders = count(array_filter($orders, function($order) { return $order['status'] === 'confirmed'; }));
$processingOrders = count(array_filter($orders, function($order) { return $order['status'] === 'processing'; }));
$deliveredOrders = count(array_filter($orders, function($order) { return $order['status'] === 'delivered'; }));
$totalRevenue = array_sum(array_column($orders, 'total_price'));
?>

<?php include '../includes/header.php'; ?>

<style>
.order-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.order-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
}

.order-id {
    font-weight: 700;
    color: #2c3e50;
    font-size: 1.1rem;
}

.order-body {
    padding: 20px;
}

.customer-info, .vendor-info {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 16px;
    margin-right: 12px;
    flex-shrink: 0;
}

.customer-avatar {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.vendor-avatar {
    background: linear-gradient(135deg, #f093fb, #f5576c);
}

.product-info {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.product-info h6 {
    margin: 0;
    font-weight: 600;
}

.quantity-price {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.quantity-badge {
    background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
    color: #2d3436;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.price-tag {
    font-size: 1.2rem;
    font-weight: 700;
    color: #27ae60;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending {
    background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
    color: #2d3436;
}

.status-confirmed {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
}

.status-processing {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.status-delivered {
    background: linear-gradient(135deg, #55efc4, #00b894);
    color: white;
}

.status-cancelled {
    background: linear-gradient(135deg, #fab1a0, #e17055);
    color: white;
}

.page-header {
    background: linear-gradient(135deg, #FF6B35, #F7931E);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 0 0 20px 20px;
}

.page-header h1 {
    margin: 0;
    font-weight: 700;
    font-size: 2.5rem;
}

.page-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
}

.stats-row {
    margin-bottom: 30px;
}

.stats-card {
    border-radius: 15px;
    padding: 25px;
    color: white;
    text-align: center;
    height: 100%;
}

.stats-card h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 5px 0;
}

.stats-card p {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}

.stats-total {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.stats-pending {
    background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
    color: #2d3436;
}

.stats-completed {
    background: linear-gradient(135deg, #55efc4, #00b894);
}

.stats-revenue {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.search-filter-row {
    margin-bottom: 30px;
}

.search-box {
    position: relative;
}

.search-box input {
    border: 2px solid #e9ecef;
    border-radius: 50px;
    padding: 12px 20px 12px 50px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.search-box input:focus {
    border-color: #FF6B35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.search-box i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 18px;
}

.filter-select {
    border: 2px solid #e9ecef;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.filter-select:focus {
    border-color: #FF6B35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #495057;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .quantity-price {
        flex-direction: column;
        gap: 10px;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .stats-card h3 {
        font-size: 1.8rem;
    }
}
</style>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-clipboard-list me-3"></i>Order Management</h1>
                <p>Monitor and manage all customer orders in real-time</p>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row stats-row">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card stats-total">
                <h3><?php echo $totalOrders; ?></h3>
                <p>Total Orders</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card stats-pending">
                <h3><?php echo $pendingOrders; ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card stats-completed">
                <h3><?php echo $deliveredOrders; ?></h3>
                <p>Completed Orders</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card stats-revenue">
                <h3>₦<?php echo number_format($totalRevenue, 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>

    <div class="row search-filter-row">
        <div class="col-md-8">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" id="orderSearch" placeholder="Search orders by ID, customer, vendor, or product...">
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-select filter-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div id="ordersContainer">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card order-item" 
                     data-id="<?php echo $order['id']; ?>"
                     data-customer="<?php echo strtolower($order['customer_name']); ?>"
                     data-vendor="<?php echo strtolower($order['vendor_name']); ?>"
                     data-product="<?php echo strtolower($order['product_name']); ?>"
                     data-status="<?php echo $order['status']; ?>">
                    <div class="order-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="order-id">
                                <i class="fas fa-hashtag me-2"></i>Order #<?php echo $order['id']; ?>
                            </div>
                            <div class="order-date">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?php echo date('M d, Y', strtotime($order['created_at'] ?? 'now')); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="customer-info">
                                    <div class="avatar customer-avatar">
                                        <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Customer</small>
                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                    </div>
                                </div>
                                
                                <div class="vendor-info">
                                    <div class="avatar vendor-avatar">
                                        <?php echo strtoupper(substr($order['vendor_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Vendor</small>
                                        <strong><?php echo htmlspecialchars($order['vendor_name']); ?></strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="product-info">
                                    <h6><i class="fas fa-utensils me-2"></i><?php echo htmlspecialchars($order['product_name']); ?></h6>
                                </div>
                                
                                <div class="quantity-price">
                                    <div class="quantity-badge">
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        Qty: <?php echo $order['quantity']; ?>
                                    </div>
                                    <div class="price-tag">
                                        ₦<?php echo number_format($order['total_price'], 2); ?>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $statusIcons = [
                                            'pending' => 'fas fa-clock',
                                            'confirmed' => 'fas fa-check',
                                            'processing' => 'fas fa-spinner',
                                            'delivered' => 'fas fa-truck',
                                            'cancelled' => 'fas fa-times'
                                        ];
                                        $icon = $statusIcons[$order['status']] ?? 'fas fa-info-circle';
                                        ?>
                                        <i class="<?php echo $icon; ?> me-1"></i>
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No orders found</h3>
                <p>Orders will appear here once customers start placing them.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="noResults" class="empty-state" style="display: none;">
        <i class="fas fa-search"></i>
        <h3>No orders found</h3>
        <p>Try adjusting your search terms or filter settings</p>
    </div>
</div>

<script>
// Search and filter functionality
function filterOrders() {
    const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const orderItems = document.querySelectorAll('.order-item');
    const noResults = document.getElementById('noResults');
    let visibleCount = 0;
    
    orderItems.forEach(item => {
        const id = item.dataset.id;
        const customer = item.dataset.customer;
        const vendor = item.dataset.vendor;
        const product = item.dataset.product;
        const status = item.dataset.status;
        
        const matchesSearch = !searchTerm || 
                            id.includes(searchTerm) || 
                            customer.includes(searchTerm) || 
                            vendor.includes(searchTerm) || 
                            product.includes(searchTerm);
        
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

document.getElementById('orderSearch')?.addEventListener('input', filterOrders);
document.getElementById('statusFilter')?.addEventListener('change', filterOrders);

// Add smooth scroll animation for new orders
document.querySelectorAll('.order-card').forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
    card.style.animation = 'fadeInUp 0.5s ease forwards';
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>

<?php include '../includes/footer.php'; ?>