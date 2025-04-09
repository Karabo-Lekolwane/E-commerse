<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default username for local development
$password = ""; // Default empty password for local development
$dbname = "shoes";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch shoes from database
$sql = "SELECT * FROM shoe_products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoe Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
        }
        
        header {
            background-color: #212529;
            color: white;
            padding: 1.5rem 0;
            text-align: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #ddd;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 1.5rem;
        }
        
        nav ul li a {
            color: #212529;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #0d6efd;
        }
        
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1542291026-7eec264c27ff');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0b5ed7;
        }
        
        .products {
            padding: 2rem 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .product-info p.price {
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        
        .product-info p.description {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        footer {
            background-color: #212529;
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-top: 2rem;
        }
        
        footer p {
            margin-bottom: 1rem;
        }
        
        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            nav {
                flex-direction: column;
            }
            
            nav ul {
                margin-top: 1rem;
            }
            
            nav ul li {
                margin-left: 1rem;
                margin-right: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">ShoesHub</div>
        </div>
    </header>
    
    <div class="container">
        <nav>
            <div class="logo-small">ShoesHub</div>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Shop</a></li>
                <li><a href="#">Men</a></li>
                <li><a href="#">Women</a></li>
                <li><a href="#">Kids</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
        
        <div class="hero">
            <div class="hero-content">
                <h1>Step Into Style</h1>
                <p>Discover the latest trends in footwear</p>
                <a href="#" class="btn">Shop Now</a>
            </div>
        </div>
        
        <section class="products">
            <h2 class="section-title">Featured Products</h2>
            <div class="product-grid">
                <?php
                if ($result && $result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="product-card">';
                        echo '<img src="' . $row["image_url"] . '" alt="' . $row["name"] . '">';
                        echo '<div class="product-info">';
                        echo '<h3>' . $row["name"] . '</h3>';
                        echo '<p class="price">$' . $row["price"] . '</p>';
                        echo '<p class="description">' . $row["description"] . '</p>';
                        echo '<a href="#" class="btn">Add to Cart</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    // Display static product cards if no database results
                    ?>
                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff" alt="Nike Shoe">
                        <div class="product-info">
                            <h3>Nike Air Max</h3>
                            <p class="price">$120</p>
                            <p class="description">Comfortable and stylish Nike Air Max shoes for everyday wear.</p>
                            <a href="#" class="btn">Add to Cart</a>
                        </div>
                    </div>
                    
                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5" alt="Adidas Shoe">
                        <div class="product-info">
                            <h3>Adidas Ultraboost</h3>
                            <p class="price">$150</p>
                            <p class="description">Performance running shoes with responsive cushioning.</p>
                            <a href="#" class="btn">Add to Cart</a>
                        </div>
                    </div>
                    
                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa" alt="Puma Shoe">
                        <div class="product-info">
                            <h3>Puma RS-X</h3>
                            <p class="price">$100</p>
                            <p class="description">Bold and chunky retro-inspired sneakers from Puma.</p>
                            <a href="#" class="btn">Add to Cart</a>
                        </div>
                    </div>
                    
                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1605348532760-6753d2c43329" alt="New Balance Shoe">
                        <div class="product-info">
                            <h3>New Balance 574</h3>
                            <p class="price">$90</p>
                            <p class="description">Classic and comfortable lifestyle shoes for everyday use.</p>
                            <a href="#" class="btn">Add to Cart</a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </section>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; 2025 ShoesHub. All rights reserved.</p>
            <div class="social-links">
                <a href="#">Facebook</a>
                <a href="#">Instagram</a>
                <a href="#">Twitter</a>
            </div>
        </div>
    </footer>
    
    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>