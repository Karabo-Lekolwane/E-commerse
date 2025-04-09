<?php
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

// Get category filter
$category_id = isset($_GET['category']) ? $_GET['category'] : null;

// Get price filter
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 10000;

// Get pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$items_per_page = 9;
$offset = ($page - 1) * $items_per_page;

// Build SQL query
$sql = "SELECT * FROM products WHERE price >= $min_price AND price <= $max_price";
if ($category_id) {
    $sql .= " AND category_id = $category_id";
}
$sql .= " LIMIT $items_per_page OFFSET $offset";

$products = $conn->query($sql);

// Get total products count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM products WHERE price >= $min_price AND price <= $max_price";
if ($category_id) {
    $count_sql .= " AND category_id = $category_id";
}
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $items_per_page);

// Get categories
$categories_sql = "SELECT * FROM categories";
$categories = $conn->query($categories_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - KR's Tech</title>
    <style>
        /* Include the same CSS as index.php */
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
        
        /* Products-specific styles */
        .page-title {
            margin: 30px 0;
            font-size: 28px;
            text-align: center;
        }
        
        .products-container {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .filters {
            width: 250px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            align-self: flex-start;
            position: sticky;
            top: 90px;
        }
        
        .filter-section {
            margin-bottom: 20px;
        }
        
        .filter-title {
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .filter-list {
            list-style: none;
        }
        
        .filter-list li {
            margin-bottom: 10px;
        }
        
        .filter-list label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .filter-list input {
            margin-right: 10px;
        }
        
        .price-range {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .price-inputs {
            display: flex;
            gap: 10px;
        }
        
        .price-input {
            width: 50%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .apply-filter {
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .apply-filter:hover {
            background-color: #2980b9;
        }
        
        .products-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-title {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 20px;
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .add-to-cart {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .add-to-cart:hover {
            background-color: #27ae60;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .pagination a:hover, .pagination a.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .no-products {
            text-align: center;
            padding: 50px 0;
            color: #7f8c8d;
            grid-column: 1 / -1;
        }
        
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
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
        
        /* Responsive adjustments */
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
            
            .products-container {
                flex-direction: column;
            }
            
            .filters {
                width: 100%;
                position: static;
                margin-bottom: 20px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">KR's<span>Tech</span></div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <li><a href="account.php">Account</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Our Products</h1>

        <div class="products-container">
            <aside class="filters">
                <form action="products.php" method="GET">
                    <div class="filter-section">
                        <h3 class="filter-title">Categories</h3>
                        <ul class="filter-list">
                            <li>
                                <label>
                                    <input type="radio" name="category" value="" <?php echo !$category_id ? 'checked' : ''; ?>>
                                    All Categories
                                </label>
                            </li>
                            <?php if ($categories->num_rows > 0): ?>
                                <?php while($category = $categories->fetch_assoc()): ?>
                                    <li>
                                        <label>
                                            <input type="radio" name="category" value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'checked' : ''; ?>>
                                            
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </label>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li>
                                    <label>
                                        <input type="radio" name="category" value="1">
                                        Laptops
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="radio" name="category" value="2">
                                        Smartphones
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="radio" name="category" value="3">
                                        Desktop PCs
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="radio" name="category" value="4">
                                        Accessories
                                    </label>
                                </li>
                               
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-title">Price Range</h3>
                        <div class="price-range">
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Min" class="price-input" value="<?php echo $min_price; ?>">
                                <input type="number" name="max_price" placeholder="Max" class="price-input" value="<?php echo $max_price != 10000 ? $max_price : ''; ?>">
                            </div>
                            <button type="submit" class="apply-filter">Apply Filters</button>
                        </div>
                    </div>
                </form>
            </aside>

            <div class="products-grid">
                <?php if ($products->num_rows > 0): ?>
                    <?php while($product = $products->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-price">R<?php echo number_format($product['price'], 2); ?></p>
                                <button class="add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No products found with current filters</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $min_price ? '&min_price=' . $min_price : ''; ?><?php echo $max_price != 10000 ? '&max_price=' . $max_price : ''; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>KR's Tech</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
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
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Product added to cart!');
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>