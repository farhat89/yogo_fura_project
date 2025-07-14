<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vendor_id = $_POST['vendor_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'vendor'");
    $stmt->execute([$status, $vendor_id]);
    $_SESSION['message'] = "Vendor status updated!";
    $_SESSION['message_type'] = "success";
    header("Location: vendors.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'vendor'");
$vendors = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<h2>Manage Vendors</h2>
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vendors as $vendor): ?>
            <tr>
                <td><?php echo $vendor['name']; ?></td>
                <td><?php echo $vendor['email']; ?></td>
                <td><?php echo $vendor['status']; ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="pending" <?php echo $vendor['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $vendor['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $vendor['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary mt-2">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include '../includes/footer.php'; ?>