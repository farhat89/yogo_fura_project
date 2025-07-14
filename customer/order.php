<?php
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
<h2>Order <?php echo $product['name']; ?></h2>
<form method="POST" action="">
    <div class="mb-3">
        <label for="quantity" class="form-label">Quantity</label>
        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
    </div>
    <div class="mb-3">
        <label for="delivery_type" class="form-label">Delivery Type</label>
        <select class="form-control" id="delivery_type" name="delivery_type" required>
            <option value="pickup">Pickup</option>
            <option value="delivery">Delivery</option>
        </select>
    </div>
    <div class="mb-3" id="delivery_address_field" style="display: none;">
        <label for="delivery_address" class="form-label">Delivery Address</label>
        <input type="text" class="form-control" id="delivery_address" name="delivery_address">
    </div>
    <div class="mb-3">
        <label for="scheduled_time" class="form-label">Scheduled Time</label>
        <input type="datetime-local" class="form-control" id="scheduled_time" name="scheduled_time" required>
    </div>
    <button type="submit" class="btn btn-primary">Proceed to Checkout</button>
</form>
<script>
    document.getElementById('delivery_type').addEventListener('change', function() {
        document.getElementById('delivery_address_field').style.display = this.value === 'delivery' ? 'block' : 'none';
    });
</script>
<?php include '../includes/footer.php'; ?>