<?php
/**
 * Vendor Management Dashboard - TheFresh.Corner Admin
 *
 * This file displays all vendor accounts and allows admins to manage their status.
 *
 * Key Features:
 * - Only accessible by authenticated admin users (session check).
 * - Lists all vendors with name, email, registration date, and current status.
 * - Update vendor status (pending, approved, rejected) via form submission.
 * - Search/filter vendors by name or email (client-side JS).
 * - Responsive UI with animated cards and status badges.
 * - Displays empty state when no vendors are found.
 *
 * Maintenance Notes:
 * - Keep vendor status logic in sync with business rules.
 * - Extend vendor info display as needed.
 * - Ensure search and UI remain performant for large datasets.
 * - Consider adding pagination, bulk actions, or export features for scalability.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07-01
 */

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

<!-- Override header.php body styles -->
<style>
    body {
        background: linear-gradient(135deg, #FFBF78 0%, #c08df2ff 100%) !important;
        min-height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
    }
</style>

<div class="container-fluid py-4" style="min-height: 100vh;">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center bg-white rounded-3 shadow-sm p-4">
                    <div>
                        <h1 class="h3 mb-1 text-dark fw-bold">
                            <i class="fas fa-users-cog text-primary me-2"></i>
                            Vendor Management
                        </h1>
                        <p class="text-muted mb-0">Manage and monitor all vendor accounts</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative">
                            <input type="text" id="vendorSearch" class="form-control form-control-lg ps-5" 
                                   placeholder="Search vendors..." style="min-width: 300px; border-radius: 25px;">
                            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        </div>
                        <button class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm" 
                                data-bs-toggle="modal" data-bs-target="#addVendorModal">
                            <i class="fas fa-plus me-2"></i>Add Vendor
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stat-card bg-gradient-primary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1">Total Vendors</h6>
                                <h2 class="text-white fw-bold mb-0"><?php echo count($vendors); ?></h2>
                                <small class="text-white-50">
                                    <i class="fas fa-arrow-up me-1"></i>All registered vendors
                                </small>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-users text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stat-card bg-gradient-success">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1">Approved</h6>
                                <h2 class="text-white fw-bold mb-0"><?php echo count(array_filter($vendors, fn($v) => $v['status'] === 'approved')); ?></h2>
                                <small class="text-white-50">
                                    <i class="fas fa-check-circle me-1"></i>Active vendors
                                </small>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-check-circle text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stat-card bg-gradient-warning">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1">Pending</h6>
                                <h2 class="text-white fw-bold mb-0"><?php echo count(array_filter($vendors, fn($v) => $v['status'] === 'pending')); ?></h2>
                                <small class="text-white-50">
                                    <i class="fas fa-clock me-1"></i>Awaiting approval
                                </small>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-clock text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stat-card bg-gradient-danger">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1">Rejected</h6>
                                <h2 class="text-white fw-bold mb-0"><?php echo count(array_filter($vendors, fn($v) => $v['status'] === 'rejected')); ?></h2>
                                <small class="text-white-50">
                                    <i class="fas fa-times-circle me-1"></i>Declined vendors
                                </small>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-times-circle text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Vendor Cards -->
        <div class="row" id="vendorContainer">
            <?php foreach ($vendors as $vendor): ?>
            <div class="col-xl-4 col-lg-6 mb-4 vendor-card">
                <div class="card border-0 shadow-sm h-100 vendor-item">
                    <div class="card-header bg-transparent border-0 p-4 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    <i class="fas fa-store text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($vendor['name']); ?></h5>
                                    <small class="text-muted">Vendor ID: #<?php echo $vendor['id']; ?></small>
                                </div>
                            </div>
                            <span class="badge status-badge status-<?php echo $vendor['status']; ?> px-3 py-2">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                <?php echo ucfirst($vendor['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 pt-2">
                        <div class="vendor-info mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-wrapper me-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Email Address</small>
                                    <span class="text-dark"><?php echo htmlspecialchars($vendor['email']); ?></span>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-wrapper me-3">
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Registration Date</small>
                                    <span class="text-dark"><?php echo date('M d, Y', strtotime($vendor['created_at'] ?? 'now')); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" action="" class="status-form">
                            <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                            <div class="row g-2">
                                <div class="col-8">
                                    <select name="status" class="form-select status-select">
                                        <option value="pending" <?php echo $vendor['status'] == 'pending' ? 'selected' : ''; ?>>
                                            Pending Review
                                        </option>
                                        <option value="approved" <?php echo $vendor['status'] == 'approved' ? 'selected' : ''; ?>>
                                            Approved
                                        </option>
                                        <option value="rejected" <?php echo $vendor['status'] == 'rejected' ? 'selected' : ''; ?>>
                                            Rejected
                                        </option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-1"></i>Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <?php if (empty($vendors)): ?>
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-store text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                        <h4 class="text-muted mt-3">No vendors found</h4>
                        <p class="text-muted">Get started by adding your first vendor</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                            <i class="fas fa-plus me-2"></i>Add Vendor
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --danger-gradient: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
}

.bg-gradient-primary {
    background: var(--primary-gradient);
}

.bg-gradient-success {
    background: var(--success-gradient);
}

.bg-gradient-warning {
    background: var(--warning-gradient);
}

.bg-gradient-danger {
    background: var(--danger-gradient);
}

.stat-card {
    border-radius: 15px;
    transition: all 0.3s ease;
    transform: translateY(0);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.vendor-item {
    border-radius: 15px;
    transition: all 0.3s ease;
    background: white;
    overflow: hidden;
}

.vendor-card:hover .vendor-item {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.status-badge {
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-approved {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.status-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.status-rejected {
    background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
    color: white;
}

.icon-wrapper {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    background: rgba(102, 126, 234, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.vendor-info {
    border-left: 3px solid #667eea;
    padding-left: 15px;
    margin-left: 15px;
}

.status-select {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.status-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

#vendorSearch {
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

#vendorSearch:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.empty-state {
    padding: 3rem 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.align-items-center.gap-3 {
        flex-direction: column;
        width: 100%;
    }
    
    #vendorSearch {
        min-width: 100%;
    }
}

/* Animation for cards appearing */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.vendor-card {
    animation: fadeInUp 0.5s ease forwards;
}

.vendor-card:nth-child(1) { animation-delay: 0.1s; }
.vendor-card:nth-child(2) { animation-delay: 0.2s; }
.vendor-card:nth-child(3) { animation-delay: 0.3s; }
.vendor-card:nth-child(4) { animation-delay: 0.4s; }
.vendor-card:nth-child(5) { animation-delay: 0.5s; }
.vendor-card:nth-child(6) { animation-delay: 0.6s; }
</style>

<script>
// Enhanced search functionality with debouncing
let searchTimeout;
document.getElementById('vendorSearch').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.vendor-card');
        let visibleCount = 0;
        
        cards.forEach((card, index) => {
            const name = card.querySelector('h5').textContent.toLowerCase();
            const email = card.querySelector('.vendor-info span').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                card.style.display = 'block';
                card.style.animation = `fadeInUp 0.5s ease forwards`;
                card.style.animationDelay = `${index * 0.1}s`;
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show/hide empty state
        const emptyState = document.querySelector('.empty-state');
        if (visibleCount === 0 && searchTerm.length > 0) {
            if (!emptyState) {
                const container = document.getElementById('vendorContainer');
                container.innerHTML = `
                    <div class="col-12">
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-search text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                <h4 class="text-muted mt-3">No vendors found</h4>
                                <p class="text-muted">Try adjusting your search terms</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
    }, 300);
});

// Add loading state for form submissions
document.querySelectorAll('.status-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const button = this.querySelector('button[type="submit"]');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    });
});

// Add smooth scrolling for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth reveal animation for stats cards
    const statCards = document.querySelectorAll('.stat-card');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    statCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

<?php include '../includes/footer.php'; ?>