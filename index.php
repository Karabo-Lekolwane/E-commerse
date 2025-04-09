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

// Get featured products with proper error handling
$sql = "SELECT * FROM products WHERE featured = 1 LIMIT 6";
$featuredProducts = $conn->query($sql);

// Get categories with proper error handling
$sql = "SELECT * FROM categories LIMIT 5";
$categories = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Electronics Shop</title>
    <style>
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
        
        .search-bar {
            display: flex;
            margin: 15px 0;
        }
        
        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }
        
        .search-bar button {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 80px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        .hero h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .categories {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #3498db;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 50px;
            justify-content: center;
        }
        
        .category-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .category-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            justify-items: center;
        }
        
        .category-card h3 {
            padding: 15px;
            text-align: center;
            color: black;
        }
        
        .products {
            margin-bottom: 40px;
        }
        
        .product-grid {
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
            
            .hero h1 {
                font-size: 28px;
            }
            
            .product-grid, .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        .hero {
            background-image: url('https://images.unsplash.com/photo-1624823183493-ed5832f48f18?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8ZWxlY3Ryb25pYyUyMGFjY2Vzc29yaWVzfGVufDB8fDB8fHww');
    background-position: center;
    background-repeat: no-repeat;
    height: 400px; /* Adjust as needed */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: white; /* Light text for contrast */
    position: relative;
}

/* Optional: Dark overlay for better text readability */
.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
    z-index: 1;
}

.hero h1,
.hero p,
.hero .btn {
    position: relative;
    z-index: 2; /* Ensures text appears above overlay */
}
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Tech<span>Store</span></div>
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
        <div class="search-bar">
            <input type="text" placeholder="Search for products...">
            <button>Search</button>
        </div>

        <div class="hero">
            <h1>Premium Electronics for Everyone</h1>
            <p>Discover the latest laptops, phones, and PCs at unbeatable prices!</p>
            <a href="products.php" class="btn">Shop Now</a>
        </div>

        <section class="categories">
            <h2 class="section-title">Shop by Category</h2>
            <div class="category-grid">
                <?php if ($categories && $categories->num_rows > 0): ?>
                    <?php while($category = $categories->fetch_assoc()): ?>
                        <div class="category-card">
                            <a href="products.php?category=<?php echo $category['id']; ?>">
                                <?php if (!empty($category['image']) && file_exists($category['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <?php else: ?>
                                    <img src="" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No categories found. Please check the database.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="products">
            <h2 class="section-title">Featured Products</h2>
            <div class="product-grid">
                <?php if ($featuredProducts && $featuredProducts->num_rows > 0): ?>
                    <?php while($product = $featuredProducts->fetch_assoc()): ?>
                        <div class="product-card">
                            <a href="productdetails.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="productdetails.php?id=<?php echo $product['id']; ?>" style="color: inherit; text-decoration: none;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-price">R<?php echo number_format($product['price'], 2); ?></p>
                                <button class="add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No featured products found. Please check the database.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>TechStore</h3>
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
                <p>&copy; 2025 TechStore. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Basic JavaScript for add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                // Send AJAX request to add to cart
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
                    } else {
                        alert(data.message || 'Error adding product to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>