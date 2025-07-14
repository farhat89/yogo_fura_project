<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$order_id = $_GET['order_id'];
$stmt = $pdo->prepare("SELECT o.*, m.name AS product_name FROM orders o JOIN menu m ON o.menu_id = m.id WHERE o.id = ? AND o.customer_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reference = 'TRX_' . time();
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, method, status, reference) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, 'Paystack', 'completed', $reference]);
    $_SESSION['message'] = "Payment simulated successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: order_history.php");
    exit;
}
?>

<?php include '../includes/header.php'; ?>
<h2>Checkout</h2>
<p>Order: <?php echo $order['product_name']; ?> (<?php echo $order['quantity']; ?>)</p>
<p>Total: ₦<?php echo $order['total_price']; ?></p>
<p>Delivery Type: <?php echo ucfirst($order['delivery_type']); ?></p>
<?php if ($order['delivery_type'] == 'delivery'): ?>
    <p>Address: <?php echo $order['delivery_address']; ?></p>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.835434509374!2d3.379205315316!3d6.524379695279!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s<?php echo urlencode($order['delivery_address']); ?>!5e0!3m2!1sen!2sng!4v1631234567890" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
<?php endif; ?>
<form method="POST" action="">
    <button type="submit" class="btn btn-success">Simulate Paystack Payment</button>
</form>
<?php include '../includes/footer.php'; ?>