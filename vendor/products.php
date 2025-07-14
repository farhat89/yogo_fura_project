<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
<h2>Manage Products</h2>
<form method="POST" action="" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="name" class="form-label">Product Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="size" class="form-label">Size</label>
        <select class="form-control" id="size" name="size" required>
            <option value="small">Small</option>
            <option value="medium">Medium</option>
            <option value="large">Large</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Toppings</label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="toppings[]" value="Nuts"> Nuts
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="toppings[]" value="Honey"> Honey
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="toppings[]" value="Fruit"> Fruit
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="toppings[]" value="Granola"> Granola
        </div>
    </div>
    <div class="mb-3">
        <label for="price" class="form-label">Price (₦)</label>
        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Product Image</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
    </div>
    <button type="submit" class="btn btn-primary">Add Product</button>
</form>
<h3 class="mt-4">Your Products</h3>
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Toppings</th>
            <th>Price</th>
            <th>Image</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo $product['name']; ?></td>
                <td><?php echo $product['size']; ?></td>
                <td><?php echo $product['toppings']; ?></td>
                <td>₦<?php echo $product['price']; ?></td>
                <td><img src="<?php echo BASE_URL; ?>uploads/<?php echo $product['image']; ?>" width="50"></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include '../includes/footer.php'; ?>