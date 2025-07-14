<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("SELECT m.*, u.name AS vendor_name FROM menu m JOIN users u ON m.vendor_id = u.id WHERE u.status = 'approved'");
$products = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
<h3>Available Yoghurt-Fura Products</h3>
<div class="row">
    <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <img src="<?php echo BASE_URL; ?>uploads/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $product['name']; ?> (<?php echo $product['size']; ?>)</h5>
                    <p class="card-text">Vendor: <?php echo $product['vendor_name']; ?></p>
                    <p class="card-text">Toppings: <?php echo $product['toppings']; ?></p>
                    <p class="card-text">Price: ₦<?php echo $product['price']; ?></p>
                    <a href="order.php?menu_id=<?php echo $product['id']; ?>" class="btn btn-primary">Order Now</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>