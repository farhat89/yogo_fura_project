<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("SELECT o.*, m.name AS product_name, c.name AS customer_name, v.name AS vendor_name FROM orders o JOIN menu m ON o.menu_id = m.id JOIN users c ON o.customer_id = c.id JOIN users v ON o.vendor_id = v.id");
$orders = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<h2>All Orders</h2>
<table class="table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Vendor</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['customer_name']; ?></td>
                <td><?php echo $order['vendor_name']; ?></td>
                <td><?php echo $product_name; ?></td>
                <td><?php echo $order['quantity']; ?></td>
                <td>₦<?php echo $order['total_price']; ?></td>
                <td><?php echo $order['status']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include '../includes/footer.php'; ?>