<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("SELECT m.*, u.name AS vendor_name FROM menu m JOIN users u ON m.vendor_id = u.id WHERE u.status = 'approved'");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart YogoFura - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff5f0 0%, #f8f9fa 100%);
            min-height: 100vh;
            color: #333;
        }

        /* Navigation Bar */
        .navbar {
            background: linear-gradient(135deg, #FFB366 0%, #FF9A4D 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(255, 179, 102, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
        }

        .logo i {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem;
            border-radius: 50%;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 25px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #A8E6CF 0%, #7FDBCD 100%);
            padding: 3rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="40" r="25" fill="rgba(255,255,255,0.1)"/><ellipse cx="50" cy="65" rx="30" ry="10" fill="rgba(255,255,255,0.1)"/><circle cx="45" cy="35" r="3" fill="rgba(255,255,255,0.2)"/><circle cx="55" cy="38" r="2" fill="rgba(255,255,255,0.2)"/></svg>') no-repeat;
            background-size: contain;
            opacity: 0.3;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero-content p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .hero-image {
            font-size: 8rem;
            color: rgba(255, 255, 255, 0.8);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Quick Actions Section */
        .quick-actions {
            max-width: 1200px;
            margin: 0 auto 3rem;
            padding: 0 2rem;
        }

        .quick-actions-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .quick-actions-header h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .quick-actions-header p {
            color: #666;
            font-size: 1rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }

        .action-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .action-card.order-history {
            background: linear-gradient(135deg, #6C63FF 0%, #5A52FF 100%);
            color: white;
        }

        .action-card.browse-products {
            background: linear-gradient(135deg, #FFB366 0%, #FF9A4D 100%);
            color: white;
        }

        .action-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .action-card p {
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.4;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .search-input {
            width: 100%;
            max-width: 400px;
            padding: 1rem;
            border: 2px solid #f0f0f0;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #FFB366;
            box-shadow: 0 0 0 3px rgba(255, 179, 102, 0.1);
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #FFB366 0%, #A8E6CF 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .product-image i {
            position: relative;
            z-index: 2;
        }

        .product-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="30" cy="30" r="2" fill="rgba(255,255,255,0.3)"/><circle cx="70" cy="40" r="3" fill="rgba(255,255,255,0.2)"/><circle cx="50" cy="70" r="2" fill="rgba(255,255,255,0.4)"/></svg>') repeat;
            opacity: 0.5;
            z-index: 1;
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.9);
            color: #FFB366;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 3;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .product-vendor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .product-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .detail-tag {
            background: #f8f9fa;
            color: #666;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FFB366;
            margin-bottom: 1rem;
        }

        .order-btn {
            width: 100%;
            background: linear-gradient(135deg, #FFB366 0%, #FF9A4D 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 179, 102, 0.4);
            color: white;
            text-decoration: none;
        }

        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .no-products i {
            font-size: 4rem;
            color: #FFB366;
            margin-bottom: 1rem;
        }

        .no-products h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Loading Animation */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #FFB366;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero-container {
                flex-direction: column;
                text-align: center;
                gap: 2rem;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-stats {
                justify-content: center;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }

        /* Animation Classes */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.6s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Include your existing header if you want to use it -->
    <?php // include '../includes/header.php'; ?>
    <?php include '../includes/header.php'; ?>

    <!-- Navigation -->
    <!-- <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>" class="logo">
                <i class="fas fa-ice-cream"></i>
                Smart YogoFura
            </a>
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>vendors/"><i class="fas fa-store"></i> Vendors</a></li>
                <li><a href="<?php echo BASE_URL; ?>about/"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="<?php echo BASE_URL; ?>contact/"><i class="fas fa-envelope"></i> Contact</a></li>
                <li><a href="<?php echo BASE_URL; ?>customer/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav> -->

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h1>
                <p>Discover delicious yogurt-fura combinations crafted by local vendors. Fresh, healthy, and delivered right to your doorstep!</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($products); ?>+</span>
                        <span class="stat-label">Fresh Products</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php 
                            $vendorStmt = $pdo->query("SELECT COUNT(DISTINCT vendor_id) as vendor_count FROM menu m JOIN users u ON m.vendor_id = u.id WHERE u.status = 'approved'");
                            $vendorCount = $vendorStmt->fetch();
                            echo $vendorCount['vendor_count'];
                        ?>+</span>
                        <span class="stat-label">Local Vendors</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <i class="fas fa-ice-cream"></i>
            </div>
        </div>
    </section>

    <!-- Quick Actions Section -->
    <section class="quick-actions">
        <div class="quick-actions-header">
            <h2>Quick Actions</h2>
            <p>Access your frequently used features</p>
        </div>
        <div class="actions-grid">
            <a href="order_history.php" class="action-card order-history fade-in">
                <i class="fas fa-history"></i>
                <h3>Order History</h3>
                <p>View your past orders and track delivery status</p>
            </a>
            <a href="#products" class="action-card browse-products fade-in" style="animation-delay: 0.1s;">
                <i class="fas fa-search"></i>
                <h3>Browse Products</h3>
                <p>Explore fresh yogurt-fura combinations from local vendors</p>
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content" id="products">
        <div class="section-header">
            <h2>Available Yoghurt-Fura Products</h2>
            <p>Explore our selection of fresh, locally-made yogurt-fura combinations from approved vendors in your area.</p>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search products..." id="searchInput">
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="no-products">
                <i class="fas fa-ice-cream"></i>
                <h3>No Products Available</h3>
                <p>There are currently no yogurt-fura products available. Please check back later!</p>
            </div>
        <?php else: ?>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $index => $product): ?>
                    <div class="product-card fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;" data-product-name="<?php echo strtolower($product['name']); ?>" data-vendor-name="<?php echo strtolower($product['vendor_name']); ?>" data-toppings="<?php echo strtolower($product['toppings']); ?>">
                        <div class="product-image">
                            <?php if (!empty($product['image']) && file_exists("../uploads/" . $product['image'])): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <i class="fas fa-ice-cream"></i>
                            <?php endif; ?>
                            <div class="product-badge"><?php echo ucfirst(htmlspecialchars($product['size'])); ?></div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-vendor">
                                <i class="fas fa-store"></i>
                                <span><?php echo htmlspecialchars($product['vendor_name']); ?></span>
                            </div>
                            <div class="product-details">
                                <?php 
                                $toppings = explode(',', $product['toppings']);
                                foreach ($toppings as $topping): 
                                    $topping = trim($topping);
                                    if (!empty($topping)):
                                ?>
                                    <div class="detail-tag">
                                        <i class="fas fa-seedling"></i>
                                        <?php echo htmlspecialchars($topping); ?>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            <div class="product-price">₦<?php echo number_format($product['price'], 2); ?></div>
                            <a href="order.php?menu_id=<?php echo $product['id']; ?>" class="order-btn">
                                <i class="fas fa-ice-cream"></i>
                                Order Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Include your existing footer if you want to use it -->
    <?php // include '../includes/footer.php'; ?>

    <script>
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const productCards = document.querySelectorAll('.product-card');
            const productsGrid = document.getElementById('productsGrid');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;

                productCards.forEach(card => {
                    const productName = card.getAttribute('data-product-name');
                    const vendorName = card.getAttribute('data-vendor-name');
                    const toppings = card.getAttribute('data-toppings');

                    const isMatch = productName.includes(searchTerm) || 
                                  vendorName.includes(searchTerm) || 
                                  toppings.includes(searchTerm);

                    if (isMatch) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/hide no results message
                const existingNoResults = document.querySelector('.no-results');
                if (existingNoResults) {
                    existingNoResults.remove();
                }

                if (visibleCount === 0 && searchTerm !== '') {
                    const noResults = document.createElement('div');
                    noResults.className = 'no-products no-results';
                    noResults.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your search terms or browse all products.</p>
                    `;
                    productsGrid.appendChild(noResults);
                }
            });

            // Add interactive effects to order buttons
            const orderButtons = document.querySelectorAll('.order-btn');
            orderButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    ripple.style.cssText = `
                        position: absolute;
                        background: rgba(255, 255, 255, 0.6);
                        border-radius: 50%;
                        pointer-events: none;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        width: 20px;
                        height: 20px;
                        left: ${e.offsetX - 10}px;
                        top: ${e.offsetY - 10}px;
                    `;
                    
                    this.style.position = 'relative';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Smooth scroll for browse products button
            const browseProductsBtn = document.querySelector('.action-card.browse-products');
            browseProductsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('#products').scrollIntoView({ 
                    behavior: 'smooth' 
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>