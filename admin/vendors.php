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

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Vendor Management</h2>
        <div class="d-flex">
            <input type="text" id="vendorSearch" class="form-control me-2" placeholder="Search vendors..." style="max-width: 250px;">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                <i class="fas fa-plus me-2"></i>Add Vendor
            </button>
        </div>
    </div>

    <!-- Vendor Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Vendors</h5>
                    <h2 class="card-text"><?php echo count($vendors); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Approved</h5>
                    <h2 class="card-text"><?php echo count(array_filter($vendors, fn($v) => $v['status'] === 'approved')); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="card-text"><?php echo count(array_filter($vendors, fn($v) => $v['status'] === 'pending')); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Cards Grid -->
    <div class="row" id="vendorContainer">
        <?php foreach ($vendors as $vendor): ?>
        <div class="col-lg-4 col-md-6 mb-4 vendor-card">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo htmlspecialchars($vendor['name']); ?></h5>
                    <span class="badge bg-<?php
                        echo $vendor['status'] == 'approved' ? 'success' :
                             ($vendor['status'] == 'pending' ? 'warning' : 'danger');
                    ?>">
                        <?php echo ucfirst($vendor['status']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($vendor['email']); ?>
                    </p>
                    <form method="POST" action="" class="mt-3">
                        <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                        <div class="input-group mb-3">
                            <select name="status" class="form-select">
                                <option value="pending" <?php echo $vendor['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $vendor['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $vendor['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.stat-card {
    border-radius: 10px;
    transition: transform 0.3s ease;
    border: none;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.vendor-card {
    transition: transform 0.3s ease;
}

.vendor-card:hover {
    transform: translateY(-3px);
}

.card {
    border-radius: 10px;
    overflow: hidden;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.card-header {
    background: rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.8rem;
    padding: 0.5em 0.75em;
}
</style>

<script>
// Simple search functionality
document.getElementById('vendorSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.vendor-card');
    
    cards.forEach(card => {
        const name = card.querySelector('h5').textContent.toLowerCase();
        const email = card.querySelector('.card-text').textContent.toLowerCase();
        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>