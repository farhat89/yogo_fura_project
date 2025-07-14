<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_orders, SUM(total_price) AS total_earnings FROM orders WHERE vendor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>
<h2>Vendor Dashboard - <?php echo $_SESSION['name']; ?></h2>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text"><?php echo $stats['total_orders']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Earnings</h5>
                <p class="card-text">₦<?php echo $stats['total_earnings'] ?? 0; ?></p>
            </div>
        </div>
    </div>
</div>
<a href="products.php" class="btn btn-primary mt-3">Manage Products</a>
<a href="orders.php" class="btn btn-primary mt-3">View Orders</a>
<?php include '../includes/footer.php'; ?>