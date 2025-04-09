<?php
// Start session for admin authentication
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect non-admin users to the account page with an error message
    header("Location: account.php?error=unauthorized");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerse";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle product deletion
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    
    // Get image path before deleting
    $img_query = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($img_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $image_path = $row['image'];
        // Delete image file if it exists and is not a default image
        if ($image_path && file_exists($image_path) && !strpos($image_path, 'placeholder')) {
            unlink($image_path);
        }
    }
    $stmt->close();
    
    // Delete the product
    $delete_query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $delete_message = "Product deleted successfully";
    } else {
        $delete_error = "Error deleting product: " . $conn->error;
    }
    $stmt->close();
}

// Handle product form submission (Add or Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_product'])) {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // File upload handling
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['name']) {
        $target_dir = "images/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
            
            // If editing and new image uploaded, delete old image
            if ($product_id) {
                $img_query = "SELECT image FROM products WHERE id = ?";
                $stmt = $conn->prepare($img_query);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $old_image = $row['image'];
                    if ($old_image && file_exists($old_image) && !strpos($old_image, 'placeholder')) {
                        unlink($old_image);
                    }
                }
                $stmt->close();
            }
        } else {
            $upload_error = "Error uploading file.";
        }
    } else if ($product_id) {
        // Keep existing image if editing and no new image uploaded
        $img_query = "SELECT image FROM products WHERE id = ?";
        $stmt = $conn->prepare($img_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $image_path = $row['image'];
        }
        $stmt->close();
    } else {
        // Default image for new products
        $image_path = "images/products/placeholder.jpg";
    }
    
    if ($product_id) {
        // Update existing product
        $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, stock=?, featured=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiissi", $name, $description, $price, $category_id, $stock, $featured, $image_path, $product_id);
        
        if ($stmt->execute()) {
            $success_message = "Product updated successfully";
        } else {
            $error_message = "Error updating product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Add new product
        $sql = "INSERT INTO products (name, description, price, category_id, stock, featured, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiiis", $name, $description, $price, $category_id, $stock, $featured, $image_path);
        
        if ($stmt->execute()) {
            $success_message = "Product added successfully";
        } else {
            $error_message = "Error adding product: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle user management - toggle admin status
if (isset($_GET['toggle_admin']) && is_numeric($_GET['toggle_admin'])) {
    $user_id = $_GET['toggle_admin'];
    
    // Get current admin status
    $sql = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $new_status = $row['is_admin'] ? 0 : 1;
        
        // Update admin status
        $update_sql = "UPDATE users SET is_admin = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_status, $user_id);
        
        if ($update_stmt->execute()) {
            $admin_message = "User admin status updated successfully";
        } else {
            $admin_error = "Error updating user admin status";
        }
        $update_stmt->close();
    }
    $stmt->close();
}

// Get product for editing
$edit_product = null;
if (isset($_GET['edit_product']) && is_numeric($_GET['edit_product'])) {
    $product_id = $_GET['edit_product'];
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
    $stmt->close();
}

// Get all categories for the form dropdown
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($categories_sql);

// Get all products for listing
$products_sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC";
$products = $conn->query($products_sql);

// Get all users for user management
$users_sql = "SELECT id, username, email, first_name, last_name, is_admin FROM users ORDER BY id";
$users = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KR's Tech</title>
    <style>
        /* Include the same CSS as other pages */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo span {
            color: #3498db;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #3498db;
        }

        /* Admin-specific styles */
        .page-title {
            margin: 30px 0;
            font-size: 28px;
            text-align: center;
        }
        
        .admin-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-success {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        /* Form styles */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
        
        .form-check input {
            margin-right: 8px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        /* Table styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Tab navigation */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-item {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: bold;
        }
        
        .tab-item.active {
            border-bottom-color: #3498db;
            color: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .admin-badge {
            background-color: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .footer-column h3 {
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-column ul li a:hover {
            color: #3498db;
        }
        
        .copyright {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #34495e;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 15px;
                justify-content: center;
            }
            
            nav ul li {
                margin: 0 10px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .product-image {
                width: 60px;
                height: 60px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
            
            .tab-item {
                padding: 8px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">KR's<span>Tech</span> Admin</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="admin.php">Admin</a></li>
                        <li><a href="account.php?logout=1">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Admin Dashboard</h1>
        
        <div class="admin-content">
            <div class="card">
                <div class="tabs">
                    <div class="tab-item active" onclick="showTab('products')">Manage Products</div>
                    <div class="tab-item" onclick="showTab('users')">Manage Users</div>
                    <div class="tab-item" onclick="showTab('orders')">View Orders</div>
                </div>
                
                <!-- Products Tab -->
                <div id="products" class="tab-content active">
                    <!-- Product Form Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title"><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
                        </div>
                        
                        <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form action="admin.php" method="POST" enctype="multipart/form-data">
                            <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="price">Price (R)</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="stock">Stock Quantity</label>
                                    <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $edit_product ? $edit_product['stock'] : '0'; ?>" required>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Product Image</label>
                                    <?php if ($edit_product && $edit_product['image']): ?>
                                    <div style="margin-bottom: 10px;">
                                        <img src="<?php echo htmlspecialchars($edit_product['image']); ?>" alt="Current product image" style="max-width: 150px; max-height: 150px;">
                                        <p><small>Current image - upload a new one to replace</small></p>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $edit_product ? '' : 'required'; ?>>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="featured" name="featured" <?php echo ($edit_product && $edit_product['featured'] == 1) ? 'checked' : ''; ?>>
                                        <label for="featured">Featured Product</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="submit_product" class="btn btn-success">
                                    <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                                </button>
                                <?php if ($edit_product): ?>
                                <a href="admin.php" class="btn btn-primary" style="margin-left: 10px;">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Products List Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Products List</h2>
                        </div>
                        
                        <?php if (isset($delete_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $delete_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($delete_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $delete_error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($products->num_rows > 0): ?>
                                        <?php while ($product = $products->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                                </td>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                <td><?php echo $product['stock']; ?></td>
                                                <td><?php echo $product['featured'] ? 'Yes' : 'No'; ?></td>
                                                <td class="actions">
                                                    <a href="admin.php?edit_product=<?php echo $product['id']; ?>" class="btn btn-primary">Edit</a>
                                                    <a href="admin.php?delete_product=<?php echo $product['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" style="text-align: center;">No products found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Users Tab -->
                <div id="users" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">User Management</h2>
                        </div>
                        
                        <?php if (isset($admin_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $admin_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($admin_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $admin_error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Admin Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users->num_rows > 0): ?>
                                        <?php while ($user = $users->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php if ($user['is_admin']): ?>
                                                        <span class="admin-badge">Admin</span>
                                                    <?php else: ?>
                                                        Customer
                                                    <?php endif; ?>
                                                </td>
                                                <td class="actions">
                                                    <a href="admin.php?toggle_admin=<?php echo $user['id']; ?>" class="btn btn-primary">
                                                        <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center;">No users found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Tab -->
                <div id="orders" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Order Management</h2>
                        </div>
                        
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No orders found</td>
                                    </tr>
                                    <!-- Orders would be populated here from the database -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>KR's Tech</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="careers.php">Careers</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="shipping.php">Shipping Policy</a></li>
                        <li><a href="returns.php">Returns & Exchanges</a></li>
                        <li><a href="terms.php">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>My Account</h3>
                    <ul>
                        <li><a href="account.php">Account</a></li>
                        <li><a href="orders.php">Order History</a></li>
                        <li><a href="wishlist.php">Wishlist</a></li>
                        <li><a href="newsletter.php">Newsletter</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Connect With Us</h3>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">YouTube</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 KR's Tech. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function showTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab items
            document.querySelectorAll('.tab-item').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to the selected tab
            document.querySelector(`.tab-item[onclick="showTab('${tabId}')"]`).classList.add('active');
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>