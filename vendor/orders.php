<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$status, $order_id, $_SESSION['user_id']]);
    $_SESSION['message'] = "Order status updated successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: orders.php");
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, m.name AS product_name, u.name AS customer_name FROM orders o JOIN menu m ON o.menu_id = m.id JOIN users u ON o.customer_id = u.id WHERE o.vendor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Calculate order statistics
$total_orders = count($orders);
$pending_count = count(array_filter($orders, function($order) { return $order['status'] == 'pending'; }));
$processing_count = count(array_filter($orders, function($order) { return $order['status'] == 'processing'; }));
$confirmed_count = count(array_filter($orders, function($order) { return $order['status'] == 'confirmed'; }));
$delivered_count = count(array_filter($orders, function($order) { return $order['status'] == 'delivered'; }));
$cancelled_count = count(array_filter($orders, function($order) { return $order['status'] == 'cancelled'; }));

function getStatusBadge($status) {
$badges = [
    'pending' => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>',
    'confirmed' => '<span class="badge bg-info text-white"><i class="fas fa-check me-1"></i>Confirmed</span>',
    'processing' => '<span class="badge bg-primary text-white"><i class="fas fa-spinner me-1"></i>Processing</span>',
    'delivered' => '<span class="badge bg-success text-white"><i class="fas fa-truck me-1"></i>Delivered</span>',
    'cancelled' => '<span class="badge bg-danger text-white"><i class="fas fa-times me-1"></i>Cancelled</span>'
];
    return $badges[$status] ?? '<span class="badge bg-secondary"><i class="fas fa-question-circle me-1"></i>Unknown</span>';
}
?>

<?php include '../includes/header.php'; ?>

<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-card.warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stats-card.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stats-card.success {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stats-card.delivered {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
}

.stats-card.danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.order-table {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table th {
    background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 20px 15px;
}

.table td {
    padding: 20px 15px;
    vertical-align: middle;
    border-color: #f8f9fa;
}

.table tbody tr:hover {
    background-color: #f8f9ff;
    transition: background-color 0.3s ease;
}

.status-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.status-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.update-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.update-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.page-header {
    background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
    color: white;
    padding: 30px 0;
    border-radius: 15px;
    margin-bottom: 30px;
}

.search-box {
    border: 2px solid #e9ecef;
    border-radius: 50px;
    padding: 12px 20px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.search-box:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.order-id {
    font-weight: bold;
    color: #667eea;
}

.customer-name {
    font-weight: 600;
    color: #2c3e50;
}

.product-name {
    color: #7f8c8d;
    font-style: italic;
}

.delivery-info {
    font-size: 14px;
    color: #6c757d;
}

.quantity-badge {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    color: #2c3e50;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .table-responsive {
        border-radius: 15px;
    }
    
    .stats-card {
        margin-bottom: 15px;
    }
}
</style>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header text-center">
        <h1 class="mb-0"><i class="fas fa-clipboard-list me-3"></i>Order Management Dashboard</h1>
        <p class="mb-0 mt-2 opacity-75">Manage and track all your orders efficiently</p>
    </div>

    <!-- Success Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title mb-0"><?php echo $total_orders; ?></h3>
                        <i class="fas fa-shopping-bag fa-2x opacity-75"></i>
                    </div>
                    <p class="card-text mb-0">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card stats-card warning h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title mb-0"><?php echo $pending_count; ?></h3>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                    <p class="card-text mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card stats-card success h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title mb-0"><?php echo $confirmed_count; ?></h3>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                    <p class="card-text mb-0">Confirmed</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card stats-card info h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title mb-0"><?php echo $processing_count; ?></h3>
                        <i class="fas fa-spinner fa-2x opacity-75"></i>
                    </div>
                    <p class="card-text mb-0">Processing</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card stats-card delivered h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title mb-0"><?php echo $delivered_count; ?></h3>
                        <i class="fas fa-truck fa-2x opacity-75"></i>
                    </div>
                    <p class="card-text mb-0">Delivered</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card stats-card danger h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title mb-0"><?php echo $cancelled_count; ?></h3>
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                    <p class="card-text mb-0">Cancelled</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control search-box border-start-0" placeholder="Search orders by customer name, product, or order ID..." id="searchInput">
            </div>
        </div>
        <div class="col-md-6">
            <select class="form-select status-select" id="statusFilter">
                <option value="">All Orders</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="order-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="ordersTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>Order ID</th>
                        <th><i class="fas fa-user me-2"></i>Customer</th>
                        <th><i class="fas fa-utensils me-2"></i>Product</th>
                        <th><i class="fas fa-sort-numeric-up me-2"></i>Quantity</th>
                        <th><i class="fas fa-truck me-2"></i>Delivery</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Address</th>
                        <th><i class="fas fa-calendar-alt me-2"></i>Scheduled</th>
                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                        <th><i class="fas fa-cog me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No orders found</h5>
                                <p class="text-muted">Orders will appear here once customers start placing them.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="order-id">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><span class="quantity-badge"><?php echo $order['quantity']; ?></span></td>
                                <td>
                                    <span class="badge <?php echo $order['delivery_type'] == 'delivery' ? 'bg-primary' : 'bg-secondary'; ?>">
                                        <i class="fas <?php echo $order['delivery_type'] == 'delivery' ? 'fa-truck' : 'fa-store'; ?> me-1"></i>
                                        <?php echo ucfirst($order['delivery_type']); ?>
                                    </span>
                                </td>
                                <td class="delivery-info">
                                    <?php if ($order['delivery_address']): ?>
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($order['delivery_address']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Store Pickup</span>
                                    <?php endif; ?>
                                </td>
                                <td class="delivery-info">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('M j, Y g:i A', strtotime($order['scheduled_time'])); ?>
                                </td>
                                <td><?php echo getStatusBadge($order['status']); ?></td>
                                <td>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <div class="d-flex gap-2 align-items-center">
                                            <select name="status" class="form-select status-select form-select-sm" style="min-width: 130px;">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm update-btn">
                                                <i class="fas fa-sync-alt me-1"></i>Update
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#ordersTable tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Status filter functionality
document.getElementById('statusFilter').addEventListener('change', function() {
    const filterValue = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#ordersTable tbody tr');
    
    tableRows.forEach(row => {
        if (filterValue === '') {
            row.style.display = '';
        } else {
            const statusCell = row.cells[7]; // Status column
            const statusText = statusCell.textContent.toLowerCase();
            row.style.display = statusText.includes(filterValue) ? '' : 'none';
        }
    });
});

// Auto-hide success messages
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

<?php include '../includes/footer.php'; ?>