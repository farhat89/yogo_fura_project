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
    $_SESSION['message'] = "Order status updated!";
    $_SESSION['message_type'] = "success";
    header("Location: orders.php");
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, m.name AS product_name, u.name AS customer_name FROM orders o JOIN menu m ON o.menu_id = m.id JOIN users u ON o.customer_id = u.id WHERE o.vendor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<h2>Manage Orders</h2>
<table class="table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Delivery Type</th>
            <th>Address</th>
            <th>Scheduled Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['customer_name']; ?></td>
                <td><?php echo $order['product_name']; ?></td>
                <td><?php echo $order['quantity']; ?></td>
                <td><?php echo ucfirst($order['delivery_type']); ?></td>
                <td><?php echo $order['delivery_address'] ?: 'N/A'; ?></td>
                <td><?php echo $order['scheduled_time']; ?></td>
                <td><?php echo $order['status']; ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary mt-2">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include '../includes/footer.php'; ?>