<?php
/**
 * Customer Management Dashboard - TheFresh.Corner Admin
 *
 * This file displays all pending customer registrations and allows admins to approve them.
 *
 * Key Features:
 * - Only accessible by authenticated admin users (session check).
 * - Lists pending customers with name, email, and contact info.
 * - Approve customers via form submission (updates status in database).
 * - Search/filter customers by name, email, or contact (client-side JS).
 * - Responsive UI with styled cards and badges for pending status.
 * - Displays empty state when no pending approvals exist.
 *
 * Maintenance Notes:
 * - Keep approval logic in sync with business rules.
 * - Extend customer info display as needed.
 * - Ensure search and UI remain performant for large datasets.
 * - Consider adding pagination or bulk actions for scalability.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-
 */
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
    $_SESSION['message'] = 'Customer approved successfully!';
    $_SESSION['message_type'] = 'success';
    header("Location: customers.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name, email, contact FROM users WHERE role = 'customer' AND status = 'pending'");
$customers = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<style>
.customer-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-radius: 12px;
    overflow: hidden;
}

.customer-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.customer-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FF6B35, #F7931E);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 24px;
    margin-right: 20px;
    flex-shrink: 0;
}

.customer-info h5 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
}

.customer-info p {
    margin: 2px 0;
    color: #7f8c8d;
    font-size: 14px;
}

.customer-info .contact-info {
    color: #34495e;
    font-weight: 500;
}

.pending-badge {
    background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
    color: #2d3436;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.approve-btn {
    background: linear-gradient(135deg, #00b894, #00cec9);
    border: none;
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.approve-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 184, 148, 0.4);
    background: linear-gradient(135deg, #00a085, #00b7b3);
}

.approve-btn:active {
    transform: translateY(0);
}

.page-header {
    background: linear-gradient(135deg, #FF6B35, #F7931E);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 0 0 20px 20px;
}

.page-header h1 {
    margin: 0;
    font-weight: 700;
    font-size: 2.5rem;
}

.page-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
}

.search-box {
    position: relative;
    margin-bottom: 30px;
}

.search-box input {
    border: 2px solid #e9ecef;
    border-radius: 50px;
    padding: 12px 20px 12px 50px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.search-box input:focus {
    border-color: #FF6B35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.search-box i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 18px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #495057;
    margin-bottom: 10px;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
}

.stats-card h3 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
}

.stats-card p {
    margin: 5px 0 0 0;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .customer-card .d-flex {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .customer-avatar {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
}
</style>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-users-cog me-3"></i>Customer Management</h1>
                <p>Review and approve pending customer registrations</p>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h3><?php echo count($customers); ?></h3>
                    <p>Pending Approvals</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <?php if (!empty($customers)): ?>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" id="customerSearch" placeholder="Search customers by name, email, or contact...">
        </div>

        <div class="row" id="customerGrid">
            <?php foreach ($customers as $customer): ?>
                <div class="col-lg-6 col-xl-4 mb-4 customer-item" 
                     data-name="<?php echo strtolower(htmlspecialchars($customer['name'])); ?>"
                     data-email="<?php echo strtolower(htmlspecialchars($customer['email'])); ?>"
                     data-contact="<?php echo htmlspecialchars($customer['contact']); ?>">
                    <div class="card customer-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="customer-avatar">
                                    <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                </div>
                                <div class="customer-info flex-grow-1">
                                    <h5><?php echo htmlspecialchars($customer['name']); ?></h5>
                                    <p><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($customer['email']); ?></p>
                                    <p class="contact-info"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($customer['contact']); ?></p>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="pending-badge">
                                    <i class="fas fa-clock me-1"></i>Pending
                                </span>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirmApproval('<?php echo htmlspecialchars($customer['name']); ?>')">
                                    <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                                    <button type="submit" name="approve" class="btn approve-btn">
                                        <i class="fas fa-check me-2"></i>Approve
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noResults" class="empty-state" style="display: none;">
            <i class="fas fa-search"></i>
            <h3>No customers found</h3>
            <p>Try adjusting your search terms</p>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-user-check"></i>
            <h3>All caught up!</h3>
            <p>There are no pending customer approvals at the moment.</p>
            <small class="text-muted">New customer registrations will appear here for your review.</small>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmApproval(customerName) {
    return confirm(`Are you sure you want to approve ${customerName}?`);
}

// Search functionality
document.getElementById('customerSearch')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const customerItems = document.querySelectorAll('.customer-item');
    const noResults = document.getElementById('noResults');
    let visibleCount = 0;
    
    customerItems.forEach(item => {
        const name = item.dataset.name;
        const email = item.dataset.email;
        const contact = item.dataset.contact;
        
        if (name.includes(searchTerm) || email.includes(searchTerm) || contact.includes(searchTerm)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
});

// Add loading state to approve buttons (after form submission starts)
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const btn = this.querySelector('.approve-btn');
        if (btn) {
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                btn.disabled = true;
            }, 100);
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>