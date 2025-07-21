<?php
/**
 * Vendor Products Management Dashboard - TheFresh.Corner
 *
 * This file allows vendors to add, update, and delete their yogurt-fura products.
 *
 * Key Features:
 * - Only accessible by authenticated vendors (session check).
 * - Add new products with name, size, toppings, price, and image upload.
 * - Edit existing products via modal form with live preview and topping selection.
 * - Delete products with confirmation prompt.
 * - Lists all products in a responsive grid with details and actions.
 * - Displays success/error messages for all product actions.
 * - Modern UI with styled cards, file upload area, and interactive controls.
 *
 * Maintenance Notes:
 * - Extend product logic for new attributes or business rules.
 * - Ensure validation and security best practices for file uploads and form data.
 * - Keep UI and product management consistent with the rest of the vendor portal.
 * - Consider adding pagination, bulk actions, or analytics for scalability.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07
 */

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle product deletion
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("DELETE FROM menu WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    $_SESSION['message'] = "Product deleted successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: products.php");
    exit;
}

// Handle product update
if (isset($_POST['update_product']) && isset($_POST['product_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $size = $_POST['size'];
    $toppings = implode(',', $_POST['toppings'] ?? []);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $image = $_FILES['image']['name'] ?: null;

    if ($image) {
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOADS_DIR . $image);
    } else {
        $stmt = $pdo->prepare("SELECT image FROM menu WHERE id = ? AND vendor_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        $image = $stmt->fetchColumn() ?: '';
    }

    $stmt = $pdo->prepare("UPDATE menu SET name = ?, size = ?, toppings = ?, price = ?, image = ? WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$name, $size, $toppings, $price, $image, $product_id, $_SESSION['user_id']]);
    $_SESSION['message'] = "Product updated successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: products.php");
    exit;
}

// Handle new product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_product']) && !isset($_POST['delete_product'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $size = $_POST['size'];
    $toppings = implode(',', $_POST['toppings'] ?? []);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $image = $_FILES['image']['name'];

    if ($image) {
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOADS_DIR . $image);
    }

    $stmt = $pdo->prepare("INSERT INTO menu (vendor_id, name, size, toppings, price, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $name, $size, $toppings, $price, $image]);
    $_SESSION['message'] = "Product added successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: products.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM menu WHERE vendor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<style>
    .products-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 120px);
        padding: 2rem 0;
    }
    
    .page-header {
        background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
        color: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(255, 107, 53, 0.3);
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .page-header p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #ff6b35, #ff8c42);
    }
    
    .form-card h3 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }
    
    .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }
    
    .toppings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    .topping-item {
        background: #f8f9fa;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }
    
    .topping-item:hover {
        border-color: #ff6b35;
        background: #fff5f0;
    }
    
    .topping-item input[type="checkbox"] {
        display: none;
    }
    
    .topping-item input[type="checkbox"]:checked + .topping-content {
        color: #ff6b35;
        font-weight: 600;
    }
    
    .topping-item input[type="checkbox"]:checked + .topping-content::before {
        content: '✓';
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: #ff6b35;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    
    .topping-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 500;
        position: relative;
    }
    
    .add-product-btn {
        background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .add-product-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .product-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: none;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-info {
        padding: 1.5rem;
    }
    
    .product-name {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .product-details {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .product-badge {
        background: #e2e8f0;
        color: #4a5568;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .product-badge.size {
        background: #e6fffa;
        color: #38b2ac;
    }
    
    .product-badge.price {
        background: #f0fff4;
        color: #38a169;
        font-weight: 600;
    }
    
    .product-toppings {
        color: #718096;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .product-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }
    
    .btn-edit {
        background: #4299e1;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-edit:hover {
        background: #3182ce;
    }
    
    .btn-delete {
        background: #f56565;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-delete:hover {
        background: #e53e3e;
    }
    
    .no-products {
        text-align: center;
        padding: 3rem;
        color: #718096;
    }
    
    .no-products i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #cbd5e0;
    }
    
    .back-btn {
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }
    
    .back-btn:hover {
        background: #4b5563;
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }
    
    .file-upload-area {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .file-upload-area:hover {
        border-color: #ff6b35;
        background: #fff5f0;
    }
    
    .file-upload-area.file-selected {
        border-color: #38a169;
        background: #f0fff4;
    }
    
    .file-upload-area input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        top: 0;
        left: 0;
    }
    
    .upload-content {
        pointer-events: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .upload-icon {
        font-size: 2rem;
        color: #cbd5e0;
        transition: all 0.3s ease;
    }
    
    .upload-icon.success {
        color: #38a169;
    }
    
    .upload-text {
        color: #4a5568;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .upload-text.success {
        color: #38a169;
    }
    
    .upload-subtext {
        color: #718096;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
    
    .image-preview {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        margin-top: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 10px;
        width: 80%;
        max-width: 500px;
    }
    
    .close {
        float: right;
        font-size: 24px;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 2rem;
        }
        
        .toppings-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="products-container">
    <div class="container">
        <!-- Back Button -->
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-box me-2"></i>Manage Products</h1>
                    <p>Add new products and manage your existing inventory</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="performance-indicator">
                        <i class="fas fa-chart-line me-1"></i>
                        <?php echo count($products); ?> Products
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="form-card">
            <h3><i class="fas fa-plus-circle"></i>Add New Product</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag"></i>Product Name
                            </label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="size" class="form-label">
                                <i class="fas fa-expand-arrows-alt"></i>Size
                            </label>
                            <select class="form-select" id="size" name="size" required>
                                <option value="">Select size</option>
                                <option value="small">Small</option>
                                <option value="medium">Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="price" class="form-label">
                                <i class="fas fa-naira-sign"></i>Price (₦)
                            </label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-cookie-bite"></i>Toppings
                    </label>
                    <div class="toppings-grid">
                        <div class="topping-item">
                            <input type="checkbox" name="toppings[]" value="Nuts" id="nuts">
                            <label for="nuts" class="topping-content">
                                <i class="fas fa-seedling"></i>Nuts
                            </label>
                        </div>
                        <div class="topping-item">
                            <input type="checkbox" name="toppings[]" value="Honey" id="honey">
                            <label for="honey" class="topping-content">
                                <i class="fas fa-tint"></i>Honey
                            </label>
                        </div>
                        <div class="topping-item">
                            <input type="checkbox" name="toppings[]" value="Fruit" id="fruit">
                            <label for="fruit" class="topping-content">
                                <i class="fas fa-apple-alt"></i>Fruit
                            </label>
                        </div>
                        <div class="topping-item">
                            <input type="checkbox" name="toppings[]" value="Granola" id="granola">
                            <label for="granola" class="topping-content">
                                <i class="fas fa-cookie"></i>Granola
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-camera"></i>Product Image
                    </label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" name="image" id="imageInput" accept="image/*" required>
                        <div class="upload-content" id="uploadContent">
                            <div class="upload-icon" id="uploadIcon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text" id="uploadText">Click to upload or drag and drop</div>
                            <div class="upload-subtext">PNG, JPG, GIF up to 10MB</div>
                        </div>
                        <div id="imagePreview" style="display: none;">
                            <img id="previewImg" class="image-preview" alt="Preview">
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="add-product-btn">
                        <i class="fas fa-plus"></i>Add Product
                    </button>
                </div>
            </form>
        </div>

        <!-- Products List -->
        <div class="form-card">
            <h3><i class="fas fa-store"></i>Your Products</h3>
            
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h4>No products yet</h4>
                    <p>Start by adding your first product above!</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                    <img src="<?php echo BASE_URL; ?>uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-image" style="font-size: 3rem; color: #cbd5e0;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-details">
                                    <span class="product-badge size">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                        <?php echo ucfirst($product['size']); ?>
                                    </span>
                                    <span class="product-badge price">
                                        <i class="fas fa-naira-sign"></i>
                                        <?php echo number_format($product['price'], 2); ?>
                                    </span>
                                </div>
                                <?php if ($product['toppings']): ?>
                                    <div class="product-toppings">
                                        <i class="fas fa-cookie-bite"></i>
                                        Toppings: <?php echo htmlspecialchars($product['toppings']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="product-actions">
                                    <form method="POST" action="" style="display:inline;" class="delete-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="delete_product" value="1">
                                        <button type="submit" class="btn-delete" onclick="return confirmDelete(this)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Edit Product Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <form method="POST" action="" enctype="multipart/form-data" id="editForm">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name" class="form-label">
                                    <i class="fas fa-tag"></i>Product Name
                                </label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_size" class="form-label">
                                    <i class="fas fa-expand-arrows-alt"></i>Size
                                </label>
                                <select class="form-select" id="edit_size" name="size" required>
                                    <option value="small">Small</option>
                                    <option value="medium">Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_price" class="form-label">
                                    <i class="fas fa-naira-sign"></i>Price (₦)
                                </label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-cookie-bite"></i>Toppings
                        </label>
                        <div class="toppings-grid" id="edit_toppings">
                            <!-- Toppings will be populated by JavaScript -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-camera"></i>Product Image
                        </label>
                        <div class="file-upload-area" id="edit_fileUploadArea">
                            <input type="file" name="image" id="edit_imageInput" accept="image/*">
                            <div class="upload-content" id="edit_uploadContent">
                                <div class="upload-icon" id="edit_uploadIcon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text" id="edit_uploadText">Click to upload or drag and drop</div>
                                <div class="upload-subtext">PNG, JPG, GIF up to 10MB</div>
                            </div>
                            <div id="edit_imagePreview" style="display: none;">
                                <img id="edit_previewImg" class="image-preview" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="add-product-btn" name="update_product">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Existing file upload functionality
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const uploadArea = document.getElementById('fileUploadArea');
    const uploadContent = document.getElementById('uploadContent');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadText = document.getElementById('uploadText');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (file) {
        uploadArea.classList.add('file-selected');
        uploadIcon.classList.add('success');
        uploadText.classList.add('success');
        uploadIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
        uploadText.textContent = `File selected: ${file.name}`;
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        uploadArea.classList.remove('file-selected');
        uploadIcon.classList.remove('success');
        uploadText.classList.remove('success');
        uploadIcon.innerHTML = '<i class="fas fa-cloud-upload-alt"></i>';
        uploadText.textContent = 'Click to upload or drag and drop';
        imagePreview.style.display = 'none';
    }
});

const fileUploadArea = document.getElementById('fileUploadArea');
const fileInput = document.getElementById('imageInput');
fileUploadArea.addEventListener('dragover', function(e) { e.preventDefault(); fileUploadArea.style.borderColor = '#ff6b35'; fileUploadArea.style.background = '#fff5f0'; });
fileUploadArea.addEventListener('dragleave', function(e) { e.preventDefault(); if (!fileUploadArea.classList.contains('file-selected')) { fileUploadArea.style.borderColor = '#e2e8f0'; fileUploadArea.style.background = ''; } });
fileUploadArea.addEventListener('drop', function(e) { e.preventDefault(); const files = e.dataTransfer.files; if (files.length > 0) { fileInput.files = files; const event = new Event('change', { bubbles: true }); fileInput.dispatchEvent(event); } });

document.querySelectorAll('.topping-item').forEach(item => {
    item.addEventListener('click', function() {
        const checkbox = this.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        if (checkbox.checked) { this.style.borderColor = '#ff6b35'; this.style.background = '#fff5f0'; } else { this.style.borderColor = '#e2e8f0'; this.style.background = '#f8f9fa'; }
    });
});

document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = document.querySelector('.add-product-btn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Product...';
    submitBtn.disabled = true;
});

// Edit modal functionality
let currentProduct = null;

function openEditModal(product) {
    currentProduct = product;
    const modal = document.getElementById('editModal');
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_size').value = product.size;
    document.getElementById('edit_price').value = product.price;

    const toppingsDiv = document.getElementById('edit_toppings');
    toppingsDiv.innerHTML = '';
    const toppings = product.toppings ? product.toppings.split(',') : [];
    ['Nuts', 'Honey', 'Fruit', 'Granola'].forEach(topping => {
        const div = document.createElement('div');
        div.className = 'topping-item';
        div.innerHTML = `<input type="checkbox" name="toppings[]" value="${topping}" id="edit_${topping.toLowerCase()}">
                         <label for="edit_${topping.toLowerCase()}" class="topping-content">
                             <i class="fas fa-seedling"></i>${topping}
                         </label>`;
        if (toppings.includes(topping)) {
            div.querySelector('input').checked = true;
            div.style.borderColor = '#ff6b35';
            div.style.background = '#fff5f0';
        }
        toppingsDiv.appendChild(div);
    });

    const editFileUploadArea = document.getElementById('edit_fileUploadArea');
    const editImageInput = document.getElementById('edit_imageInput');
    const editUploadContent = document.getElementById('edit_uploadContent');
    const editUploadIcon = document.getElementById('edit_uploadIcon');
    const editUploadText = document.getElementById('edit_uploadText');
    const editImagePreview = document.getElementById('edit_imagePreview');
    const editPreviewImg = document.getElementById('edit_previewImg');

    editImageInput.value = '';
    editFileUploadArea.classList.remove('file-selected');
    editUploadIcon.classList.remove('success');
    editUploadText.classList.remove('success');
    editUploadIcon.innerHTML = '<i class="fas fa-cloud-upload-alt"></i>';
    editUploadText.textContent = 'Click to upload or drag and drop';
    editImagePreview.style.display = 'none';
    if (product.image) {
        editImagePreview.style.display = 'block';
        editPreviewImg.src = '<?php echo BASE_URL; ?>uploads/' + product.image;
    }

    editImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            editFileUploadArea.classList.add('file-selected');
            editUploadIcon.classList.add('success');
            editUploadText.classList.add('success');
            editUploadIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
            editUploadText.textContent = `File selected: ${file.name}`;
            const reader = new FileReader();
            reader.onload = function(e) {
                editPreviewImg.src = e.target.result;
                editImagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    editFileUploadArea.addEventListener('dragover', function(e) { e.preventDefault(); editFileUploadArea.style.borderColor = '#ff6b35'; editFileUploadArea.style.background = '#fff5f0'; });
    editFileUploadArea.addEventListener('dragleave', function(e) { e.preventDefault(); if (!editFileUploadArea.classList.contains('file-selected')) { editFileUploadArea.style.borderColor = '#e2e8f0'; editFileUploadArea.style.background = ''; } });
    editFileUploadArea.addEventListener('drop', function(e) { e.preventDefault(); const files = e.dataTransfer.files; if (files.length > 0) { editImageInput.files = files; const event = new Event('change', { bubbles: true }); editImageInput.dispatchEvent(event); } });

    document.querySelectorAll('#edit_toppings .topping-item').forEach(item => {
        item.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            if (checkbox.checked) { this.style.borderColor = '#ff6b35'; this.style.background = '#fff5f0'; } else { this.style.borderColor = '#e2e8f0'; this.style.background = '#f8f9fa'; }
        });
    });

    modal.style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
};

document.getElementById('editForm').addEventListener('submit', function() {
    // Explicitly set the update_product parameter
    const updateInput = document.createElement('input');
    updateInput.type = 'hidden';
    updateInput.name = 'update_product';
    updateInput.value = '1';
    this.appendChild(updateInput);
    
    const submitBtn = this.querySelector('.add-product-btn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating Product...';
    submitBtn.disabled = true;
    
    // Ensure the form will submit
    return true;
});
</script>

<?php include '../includes/footer.php'; ?>