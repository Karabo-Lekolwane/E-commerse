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

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'];

// Get product details
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: products.php");
    exit;
}

$product = $result->fetch_assoc();

// Get product reviews
/*$sql = "SELECT r.*, u.name as user_name FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result();*/

// Get related products
$sql = "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result();

// Get category name
$sql = "SELECT name FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product['category_id']);
$stmt->execute();
$category_result = $stmt->get_result();
$category_name = ($category_result->num_rows > 0) ? $category_result->fetch_assoc()['name'] : 'Uncategorized';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - TechStore</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #343a40;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }
        
        .logo span {
            color: #4d61fc;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #4d61fc;
        }
        
        /* Product Detail Styles */
        .breadcrumb {
            background-color: #ffffff;
            padding: 10px 0;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .breadcrumb ul {
            display: flex;
            list-style: none;
            flex-wrap: wrap;
        }
        
        .breadcrumb ul li {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #6c757d;
        }
        
        .breadcrumb ul li:not(:last-child)::after {
            content: '/';
            margin: 0 10px;
        }
        
        .breadcrumb ul li a {
            color: #4d61fc;
            text-decoration: none;
        }
        
        .breadcrumb ul li a:hover {
            text-decoration: underline;
        }
        
        .product-detail {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin: 30px 0;
            padding: 30px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            background-color: #4d61fc;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .back-button i {
            margin-right: 8px;
        }
        
        .back-button:hover {
            background-color: #3949c6;
        }
        
        .product-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .product-image {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .product-image img {
            width: 100%;
            height: auto;
            object-fit: contain;
            background-color: #ffffff;
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .product-image img:hover {
            transform: scale(1.03);
        }
        
        .product-info h1 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #212529;
            font-weight: 600;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #4d61fc;
            margin-bottom: 15px;
        }
        
        .product-stock {
            color: #28a745;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .out-of-stock {
            color: #dc3545;
        }
        
        .product-description {
            color: #495057;
            line-height: 1.7;
            margin-bottom: 30px;
            font-size: 15px;
        }
        
        .product-options {
            margin-bottom: 30px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .quantity-selector label {
            margin-right: 15px;
            font-weight: 500;
        }
        
        .quantity-selector input {
            width: 70px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            text-align: center;
            font-size: 15px;
        }
        
        .add-to-cart-btn {
            background-color: #4d61fc;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .add-to-cart-btn i {
            margin-right: 8px;
        }
        
        .add-to-cart-btn:hover {
            background-color: #3949c6;
        }
        
        .product-meta {
            margin-top: 25px;
            font-size: 14px;
            color: #6c757d;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .product-meta p {
            margin-bottom: 5px;
        }
        
        .product-meta span {
            color: #495057;
            font-weight: 500;
        }
        
        .rating {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .rating .stars {
            color: #ffc107;
            margin-right: 10px;
        }
        
        .rating .count {
            color: #6c757d;
            font-size: 14px;
        }
        
        .reviews-section {
            margin: 40px 0;
        }
        
        .section-title {
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: 600;
            color: #212529;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 60px;
            background-color: #4d61fc;
        }
        
        .review {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .review:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .review-author {
            font-weight: 600;
            color: #212529;
        }
        
        .review-date {
            color: #6c757d;
            font-size: 14px;
        }
        
        .review-rating {
            color: #ffc107;
            margin-bottom: 10px;
        }
        
        .review-content {
            color: #495057;
            line-height: 1.6;
        }
        
        .no-reviews {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 30px;
            text-align: center;
            color: #6c757d;
        }
        
        .related-products {
            margin: 40px 0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            padding: 15px;
            background-color: #ffffff;
            transition: transform 0.3s;
        }
        
        .product-card:hover img {
            transform: scale(1.05);
        }
        
        .product-card-info {
            padding: 15px;
        }
        
        .product-card-title {
            font-size: 16px;
            margin-bottom: 10px;
            color: #212529;
            text-decoration: none;
            display: block;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .product-card-title:hover {
            color: #4d61fc;
        }
        
        .product-card-price {
            font-size: 18px;
            font-weight: bold;
            color: #4d61fc;
            margin-bottom: 15px;
        }
        
        .product-card-btn {
            background-color: #4d61fc;
            color: white;
            border: none;
            padding: 8px 15px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-card-btn i {
            margin-right: 5px;
        }
        
        .product-card-btn:hover {
            background-color: #3949c6;
        }
        
        footer {
            background-color: #212529;
            color: #f8f9fa;
            padding: 60px 0 20px;
            margin-top: 60px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 40px;
            background-color: #4d61fc;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 12px;
        }
        
        .footer-column ul li a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .footer-column ul li a i {
            margin-right: 8px;
            font-size: 12px;
        }
        
        .footer-column ul li a:hover {
            color: #4d61fc;
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #343a40;
            color: #adb5bd;
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            nav ul li {
                margin: 0 10px 10px;
            }
            
            .product-content {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .section-title {
                text-align: center;
            }
            
            .section-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer-column h3 {
                text-align: center;
            }
            
            .footer-column h3::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer-column ul {
                text-align: center;
            }
        }
        
        @media (max-width: 576px) {
            .breadcrumb {
                display: none;
            }
            
            .product-detail {
                padding: 20px 15px;
            }
            
            .product-info h1 {
                font-size: 22px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">Tech<span>Store</span></a>
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
        <div class="breadcrumb">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($category_name); ?></a></li>
                <li><?php echo htmlspecialchars($product['name']); ?></li>
            </ul>
        </div>
        
        <div class="product-detail">
            <a href="products.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Products</a>
            
            <div class="product-content">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="rating">
                        <div class="stars">
                            <?php
                            // Display average rating (mock data for now)
                            $avg_rating = 4; // This would come from your database
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $avg_rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } else if ($i - 0.5 <= $avg_rating) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="count">(12 Reviews)</span>
                    </div>
                    <p class="product-price">R<?php echo number_format($product['price'], 2); ?></p>
                    <p class="product-stock <?php echo $product['stock'] <= 0 ? 'out-of-stock' : ''; ?>">
                        <?php echo $product['stock'] > 0 ? '<i class="fas fa-check-circle"></i> In Stock (' . $product['stock'] . ' available)' : '<i class="fas fa-times-circle"></i> Out of Stock'; ?>
                    </p>
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                    <div class="product-options">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                        </div>
                        <button class="add-to-cart-btn" data-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="product-options">
                        <button class="add-to-cart-btn" disabled style="background-color: #6c757d;">
                            <i class="fas fa-shopping-cart"></i> Out of Stock
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="product-meta">
                        <p>Category: <span><?php echo htmlspecialchars($category_name); ?></span></p>
                        <p>SKU: <span>TECH<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></span></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="reviews-section">
            <h2 class="section-title">Customer Reviews</h2>
            <?php if ($reviews && $reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="review">
                    <div class="review-header">
                        <span class="review-author"><?php echo htmlspecialchars($review['user_name'] ?? 'Anonymous'); ?></span>
                        <span class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                    </div>
                    <div class="review-rating">
                        <?php
                        $rating = $review['rating'];
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <div class="review-content">
                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reviews">
                    <p><i class="far fa-comment-alt"></i> No reviews yet. Be the first to review this product!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="related-products">
            <h2 class="section-title">Related Products</h2>
            <div class="products-grid">
                <?php if ($related_products && $related_products->num_rows > 0): ?>
                    <?php while ($related = $related_products->fetch_assoc()): ?>
                    <div class="product-card">
                        <a href="productdetails.php?id=<?php echo $related['id']; ?>">
                            <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        </a>
                        <div class="product-card-info">
                            <a href="productdetails.php?id=<?php echo $related['id']; ?>" class="product-card-title"><?php echo htmlspecialchars($related['name']); ?></a>
                            <p class="product-card-price">$<?php echo number_format($related['price'], 2); ?></p>
                            <button class="product-card-btn" data-id="<?php echo $related['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No related products found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>TechStore</h3>
                    <ul>
                        <li><a href="about.php"><i class="fas fa-angle-right"></i> About Us</a></li>
                        <li><a href="contact.php"><i class="fas fa-angle-right"></i> Contact Us</a></li>
                        <li><a href="blog.php"><i class="fas fa-angle-right"></i> Blog</a></li>
                        <li><a href="careers.php"><i class="fas fa-angle-right"></i> Careers</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="faq.php"><i class="fas fa-angle-right"></i> FAQ</a></li>
                        <li><a href="shipping.php"><i class="fas fa-angle-right"></i> Shipping Policy</a></li>
                        <li><a href="returns.php"><i class="fas fa-angle-right"></i> Returns & Exchanges</a></li>
                        <li><a href="terms.php"><i class="fas fa-angle-right"></i> Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>My Account</h3>
                    <ul>
                        <li><a href="account.php"><i class="fas fa-angle-right"></i> Account</a></li>
                        <li><a href="orders.php"><i class="fas fa-angle-right"></i> Order History</a></li>
                        <li><a href="wishlist.php"><i class="fas fa-angle-right"></i> Wishlist</a></li>
                        <li><a href="newsletter.php"><i class="fas fa-angle-right"></i> Newsletter</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Connect With Us</h3>
                    <ul>
                        <li><a href="#"><i class="fab fa-facebook-f"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-youtube"></i> YouTube</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 TechStore. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Add to cart functionality with quantity
        document.querySelector('.add-to-cart-btn').addEventListener('click', function() {
            if (this.hasAttribute('disabled')) return;
            
            const productId = this.getAttribute('data-id');
            const quantity = document.getElementById('quantity').value;
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
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
        
        // Also handle related products add to cart buttons
        document.querySelectorAll('.product-card-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId + '&quantity=1'
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

<?php $conn->close(); ?>
