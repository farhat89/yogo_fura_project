<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role = 'customer'");
    $stmt->execute([$user_id]);
    header("Location: customers.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name, email, contact FROM users WHERE role = 'customer' AND status = 'pending'");
$customers = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<h2>Manage Customers</h2>
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                <td><?php echo htmlspecialchars($customer['contact']); ?></td>
                <td>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                        <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($customers)): ?>
            <tr>
                <td colspan="4">No pending customers.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php include '../includes/footer.php'; ?>