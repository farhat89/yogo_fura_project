<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) AS total_orders, SUM(total_price) AS total_sales FROM orders");
$stats = $stmt->fetch();

$stmt = $pdo->query("SELECT COUNT(*) AS total_vendors FROM users WHERE role = 'vendor' AND status = 'approved'");
$vendors = $stmt->fetch();

$stmt = $pdo->query("SELECT COUNT(*) AS pending_customers FROM users WHERE role = 'customer' AND status = 'pending'");
$pending_customers = $stmt->fetch();

// Get total users count
$stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
$total_users = $stmt->fetch();

// Get all users for the table
$stmt = $pdo->query("SELECT id, name, email, role, status, contact, created_at FROM users ORDER BY created_at DESC");
$all_users = $stmt->fetchAll();

// Get recent orders for chart
$stmt = $pdo->query("SELECT DATE(created_at) as order_date, COUNT(*) as daily_orders, SUM(total_price) as daily_sales FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY order_date");
$chart_data = $stmt->fetchAll();

// Get order status distribution
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$order_status_data = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<style>
.dashboard-container {
    padding: 0;
    min-height: calc(100vh - 60px); /* Account for header */
    background: #f8f9fa;
}

.dashboard-content {
    padding: 1.5rem;
    width: 100%;
    min-height: 100%;
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

@media (min-width: 992px) {
    .dashboard-content {
        padding: 2rem;
    }
}

.dashboard-header {
    text-align: center;
    margin-bottom: 3rem;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.dashboard-subtitle {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.metric-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.metric-card.orders::before {
    background: linear-gradient(90deg, #4CAF50, #45a049);
}

.metric-card.sales::before {
    background: linear-gradient(90deg, #2196F3, #1976D2);
}

.metric-card.vendors::before {
    background: linear-gradient(90deg, #FF9800, #F57C00);
}

.metric-card.customers::before {
    background: linear-gradient(90deg, #9C27B0, #7B1FA2);
}

.metric-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.metric-card.orders .metric-icon {
    color: #4CAF50;
}

.metric-card.sales .metric-icon {
    color: #2196F3;
}

.metric-card.vendors .metric-icon {
    color: #FF9800;
}

.metric-card.customers .metric-icon {
    color: #9C27B0;
}

.metric-title {
    font-size: 1rem;
    font-weight: 600;
    color: #7f8c8d;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metric-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.metric-change {
    font-size: 0.9rem;
    margin-top: 0.5rem;
    color: #27ae60;
}

.chart-container {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.chart-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 3rem;
}

.action-btn {
    background: linear-gradient(145deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    color: white;
    text-decoration: none;
}

.action-btn i {
    font-size: 1.1rem;
}

.recent-activity {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    margin-top: 2rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-weight: bold;
}

.activity-icon.success {
    background: #27ae60;
}

.activity-icon.warning {
    background: #f39c12;
}

.activity-icon.info {
    background: #3498db;
}

@media (max-width: 768px) {
    .dashboard-content {
        margin: 1rem;
        padding: 1rem;
        border-radius: 15px;
    }
    
    .dashboard-title {
        font-size: 2rem;
    }
    
    .metric-card {
        padding: 1.5rem;
    }
    
    .metric-value {
        font-size: 2rem;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<div class="dashboard-container">
    <div class="container-fluid">
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Admin Dashboard</h1>
                <p class="dashboard-subtitle">Welcome back! Here's what's happening with TheFresh.Corner today.</p>
            </div>

            <!-- Key Metrics -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="metric-card h-100 orders">
                        <i class="fas fa-shopping-cart metric-icon"></i>
                        <h5 class="metric-title">Total Orders</h5>
                        <p class="metric-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></p>
                        <div class="metric-change">
                            <i class="fas fa-arrow-up"></i> +12% from last week
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card sales">
                        <i class="fas fa-naira-sign metric-icon"></i>
                        <h5 class="metric-title">Total Sales</h5>
                        <p class="metric-value">₦<?php echo number_format($stats['total_sales'] ?? 0, 2); ?></p>
                        <div class="metric-change">
                            <i class="fas fa-arrow-up"></i> +8% from last week
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card vendors">
                        <i class="fas fa-store metric-icon"></i>
                        <h5 class="metric-title">Approved Vendors</h5>
                        <p class="metric-value"><?php echo number_format($vendors['total_vendors'] ?? 0); ?></p>
                        <div class="metric-change">
                            <i class="fas fa-arrow-up"></i> +3 new this week
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card users">
                        <i class="fas fa-users metric-icon"></i>
                        <h5 class="metric-title">Total Users</h5>
                        <p class="metric-value"><?php echo number_format($total_users['total_users'] ?? 0); ?></p>
                        <div class="metric-change">
                            <i class="fas fa-clock"></i> All system users
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-chart-line"></i> Sales Trend (Last 7 Days)
                        </h3>
                        <canvas id="salesChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-chart-pie"></i> Order Status
                        </h3>
                        <canvas id="orderStatusChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="chart-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="chart-title mb-0">
                                <i class="fas fa-users-cog"></i> User Management
                            </h3>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($all_users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['role'] == 'admin' ? 'danger' : 
                                                    ($user['role'] == 'vendor' ? 'warning' : 'info'); 
                                            ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['status'] == 'approved' ? 'success' : 
                                                    ($user['status'] == 'pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['email']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3 class="chart-title">
                    <i class="fas fa-clock"></i> Recent Activity
                </h3>
                <div class="activity-item">
                    <div class="activity-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <strong>New Order Received</strong>
                        <p class="mb-0 text-muted">Order #1234 from customer John Doe - ₦5,500</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon info">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <strong>New Vendor Registration</strong>
                        <p class="mb-0 text-muted">Fresh Fruits Ltd. has applied for vendor status</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <strong>Low Stock Alert</strong>
                        <p class="mb-0 text-muted">5 products are running low in inventory</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="vendors.php" class="action-btn">
                    <i class="fas fa-store"></i> Manage Vendors
                </a>
                <a href="orders.php" class="action-btn">
                    <i class="fas fa-list-alt"></i> View All Orders
                </a>
                <a href="customers.php" class="action-btn">
                    <i class="fas fa-users"></i> Manage Customers
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="customer">Customer</option>
                            <option value="vendor">Vendor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addUser()">Add User</button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js and Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Sales Trend Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: [
            <?php 
            foreach($chart_data as $data) {
                echo "'" . date('M j', strtotime($data['order_date'])) . "',";
            }
            ?>
        ],
        datasets: [{
            label: 'Sales (₦)',
            data: [
                <?php 
                foreach($chart_data as $data) {
                    echo ($data['daily_sales'] ?? 0) . ",";
                }
                ?>
            ],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₦' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// User Management Functions
function addUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    
    fetch('add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User added successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the user.');
    });
}

function deleteUser(id, email) {
    if (confirm(`Are you sure you want to delete user "${email}"?`)) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the user.');
        });
    }
}

// Order Status Chart
const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php 
            foreach($order_status_data as $data) {
                echo "'" . ucfirst($data['status']) . "',";
            }
            ?>
        ],
        datasets: [{
            data: [
                <?php 
                foreach($order_status_data as $data) {
                    echo $data['count'] . ",";
                }
                ?>
            ],
            backgroundColor: [
                '#4CAF50',
                '#FF9800',
                '#2196F3',
                '#9C27B0',
                '#F44336'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 1,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>