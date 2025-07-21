<?php
/**
 * Vendor Dashboard - TheFresh.Corner
 *
 * This file displays the main dashboard for vendors, providing business performance insights and quick management actions.
 *
 * Key Features:
 * - Only accessible by authenticated vendors (session check).
 * - Shows welcome section with vendor name and active status.
 * - Displays statistics cards for total orders and total earnings.
 * - Quick actions for managing products and viewing orders.
 * - Responsive UI with modern cards, icons, and alert tips for best practices.
 *
 * Maintenance Notes:
 * - Extend dashboard logic for new metrics or business features.
 * - Ensure statistics calculations remain accurate and performant.
 * - Keep UI and navigation consistent with the rest of the vendor portal.
 * - Consider adding charts, notifications, or personalized recommendations.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07-19
 */

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_orders, SUM(total_price) AS total_earnings FROM orders WHERE vendor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>

<style>
    .dashboard-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 120px);
        padding: 2rem 0;
    }
    
    .welcome-section {
        background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
        color: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(255, 107, 53, 0.3);
    }
    
    .welcome-section h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .welcome-section p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #ff6b35, #ff8c42);
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .orders-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .earnings-icon {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .stats-label {
        font-size: 0.9rem;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    .stats-change {
        font-size: 0.8rem;
        color: #48bb78;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    
    .action-section {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 2rem;
    }
    
    .action-section h3 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
    }
    
    .action-btn {
        background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        color: white;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        margin-right: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .action-btn i {
        font-size: 1.1rem;
    }
    
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .dashboard-title {
        color: #2d3748;
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0;
    }
    
    .dashboard-subtitle {
        color: #718096;
        font-size: 1rem;
        margin-top: 0.25rem;
    }
    
    .performance-indicator {
        display: inline-block;
        background: #e6fffa;
        color: #38b2ac;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .welcome-section h1 {
            font-size: 2rem;
        }
        
        .stats-number {
            font-size: 2rem;
        }
        
        .action-btn {
            width: 100%;
            justify-content: center;
            margin-right: 0;
        }
    }
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="dashboard-container">
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
                    <p>Here's an overview of your business performance on TheFresh.Corner</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="performance-indicator">
                        <i class="fas fa-chart-line me-1"></i>
                        Active Vendor
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div>
                <h2 class="dashboard-title">Dashboard Overview</h2>
                <p class="dashboard-subtitle">Track your sales and manage your business</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="quick-stats">
            <div class="stats-card">
                <div class="stats-icon orders-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-number"><?php echo $stats['total_orders'] ?: '0'; ?></div>
                <div class="stats-label">Total Orders</div>
                <div class="stats-change">
                    <i class="fas fa-arrow-up"></i>
                    Your order history
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon earnings-icon">
                    <i class="fas fa-naira-sign"></i>
                </div>
                <div class="stats-number">₦<?php echo number_format($stats['total_earnings'] ?? 0, 2); ?></div>
                <div class="stats-label">Total Earnings</div>
                <div class="stats-change">
                    <i class="fas fa-arrow-up"></i>
                    Revenue generated
                </div>
            </div>
        </div>

        <!-- Action Section -->
        <div class="action-section">
            <h3><i class="fas fa-tools me-2"></i>Quick Actions</h3>
            <div class="row">
                <div class="col-md-6">
                    <a href="products.php" class="action-btn">
                        <i class="fas fa-box"></i>
                        Manage Products
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="orders.php" class="action-btn">
                        <i class="fas fa-list-alt"></i>
                        View Orders
                    </a>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Tip:</strong> Keep your product listings updated and respond to orders quickly to maintain high customer satisfaction!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>