<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("SELECT COUNT(*) AS total_orders, SUM(total_price) AS total_sales FROM orders");
$stats = $stmt->fetch();

$stmt = $pdo->query("SELECT COUNT(*) AS total_vendors FROM users WHERE role = 'vendor' AND status = 'approved'");
$vendors = $stmt->fetch();

$stmt = $pdo->query("SELECT COUNT(*) AS pending_customers FROM users WHERE role = 'customer' AND status = 'pending'");
$pending_customers = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>
<h2>Admin Dashboard</h2>
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text"><?php echo $stats['total_orders']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <p class="card-text">₦<?php echo $stats['total_sales'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Approved Vendors</h5>
                <p class="card-text"><?php echo $vendors['total_vendors']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pending Customers</h5>
                <p class="card-text"><?php echo $pending_customers['pending_customers']; ?></p>
            </div>
        </div>
    </div>
</div>
<a href="vendors.php" class="btn btn-primary mt-3">Manage Vendors</a>
<a href="orders.php" class="btn btn-primary mt-3">View All Orders</a>
<a href="customers.php" class="btn btn-primary mt-3">Manage Customers</a>
<?php include '../includes/footer.php'; ?>