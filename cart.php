<?php
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - TechStore</title>
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
        
        /* Cart-specific styles */
        .page-title {
            margin: 30px 0;
            font-size: 28px;
            text-align: center;
        }
        
        .cart-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .cart-empty {
            text-align: center;
            padding: 40px 0;
        }
        
        .cart-empty p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #7f8c8d;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .cart-table th {
            text-align: left;
            padding: 15px 10px;
            border-bottom: 2px solid #eee;
            font-weight: bold;
        }
        
        .cart-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .product-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            background-color: #f0f0f0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-input {
            width: 40px;
            height: 30px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .remove-btn {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .cart-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .cart-total {
            width: 300px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .cart-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .cart-total-label {
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 20px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        .checkout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .checkout-btn:hover {
            background-color: #27ae60;
        }
        
        .continue-shopping {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .continue-shopping:hover {
            text-decoration: underline;
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
            
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            
            .cart-summary {
                flex-direction: column;
            }
            
            .cart-total {
                width: 100%;
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
        <h1 class="page-title">Your Shopping Cart</h1>

        <div class="cart-container">
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="cart-empty">
                    <p>Your cart is empty</p>
                    <a href="index.php" class="btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-img">
                                </td>
                                <td class="product-name"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>R<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <div class="quantity-control">
                                        <button class="quantity-btn decrease" data-index="<?php echo $index; ?>">-</button>
                                        <input type="text" class="quantity-input" value="<?php echo $item['quantity']; ?>" readonly>
                                        <button class="quantity-btn increase" data-index="<?php echo $index; ?>">+</button>
                                    </div>
                                </td>
                                <td>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <button class="remove-btn" data-index="<?php echo $index; ?>">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-summary">
                    <div class="cart-total">
                        <div class="cart-total-row">
                            <span class="cart-total-label">Subtotal:</span>
                            <span>R<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="cart-total-row">
                            <span class="cart-total-label">Shipping:</span>
                            <span>R0.00</span>
                        </div>
                        <div class="cart-total-row">
                            <span class="cart-total-label">VAT:</span>
                            <span>R<?php echo number_format($total * 0.1, 2); ?></span>
                        </div>
                        <div class="cart-total-row grand-total">
                            <span class="cart-total-label">Total:</span>
                            <span>R<?php echo number_format($total + ($total * 0.1), 2); ?></span>
                        </div>
                        
                        <button class="checkout-btn" onclick="window.location.href='account.php';">Proceed to Checkout</button>

                        
                    </div>
                </div>

                <a href="index.php" class="continue-shopping">Continue Shopping</a>
            <?php endif; ?>
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
        // Cart update functionality
        document.querySelectorAll('.increase').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                updateQuantity(index, 1);
            });
        });

        document.querySelectorAll('.decrease').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                updateQuantity(index, -1);
            });
        });

        document.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                removeItem(index);
            });
        });

        function updateQuantity(index, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `index=${index}&change=${change}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }

        function removeItem(index) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `index=${index}&remove=1`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>