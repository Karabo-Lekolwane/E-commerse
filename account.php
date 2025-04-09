<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Handle login form submission
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password_db = "";
    $dbname = "ecommerse";
    
    $conn = new mysqli($servername, $username, $password_db, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check user credentials
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password (in a real app, use password_verify with hashed passwords)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Check if user is an admin
            $_SESSION['is_admin'] = isset($user['is_admin']) ? $user['is_admin'] : 0;
            
            header("Location: account.php");
            exit;
        } else {
            $loginError = "Invalid password";
        }
    } else {
        $loginError = "User not found";
    }
    
    $conn->close();
}

// Handle registration form submission
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    
    // Simple validation
    if ($password !== $confirm_password) {
        $registerError = "Passwords do not match";
    } else {
        // Database connection
        $servername = "localhost";
        $username_db = "root";
        $password_db = "";
        $dbname = "ecommerse";
        
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Check if email already exists
        $sql = "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $registerError = "Email or username already registered";
        } else {
            // In a real app, hash the password with password_hash()
            $hashed_password = $password; // Replace with password_hash($password, PASSWORD_DEFAULT)
            $sql = "INSERT INTO users (username, email, password, first_name, last_name, is_admin) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);
            
            if ($stmt->execute()) {
                $registerSuccess = "Registration successful. Please login.";
            } else {
                $registerError = "Error: " . $stmt->error;
            }
        }
        
        $conn->close();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: account.php");
    exit;
}

// Check for unauthorized access error
$unauthorizedError = "";
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $unauthorizedError = "You don't have permission to access the requested page.";
}

// Get user orders if logged in
$orders = [];
if ($isLoggedIn) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password_db = "";
    $dbname = "ecommerse";
    
    $conn = new mysqli($servername, $username, $password_db, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get user orders
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - KR's Tech</title>
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
        
        .account-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 40px 0;
        }
        
        .auth-forms {
            flex: 1;
            min-width: 300px;
        }
        
        .user-dashboard {
            flex: 2;
            min-width: 300px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .card h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .alert {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .profile-info {
            margin-bottom: 20px;
        }
        
        .profile-info p {
            margin-bottom: 10px;
            color: #555;
        }
        
        .profile-info p strong {
            color: #2c3e50;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th, .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background-color: #f9f9f9;
            color: #555;
            font-weight: 600;
        }
        
        .orders-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #777;
        }
        
        .empty-state p {
            margin-bottom: 15px;
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
            
            .account-container {
                flex-direction: column;
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
        <?php if (isset($unauthorizedError)): ?>
            <div class="alert alert-danger" style="margin-top: 20px;">
                <?php echo $unauthorizedError; ?>
            </div>
        <?php endif; ?>
        
        <div class="account-container">
            <?php if (!$isLoggedIn): ?>
                <div class="auth-forms">
                    <div class="card">
                        <h2>Login</h2>
                        <?php if (isset($loginError)): ?>
                            <div class="alert alert-danger">
                                <?php echo $loginError; ?>
                            </div>
                        <?php endif; ?>
                        <form action="account.php" method="post">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <button type="submit" name="login" class="btn">Login</button>
                        </form>
                    </div>
                    
                    <div class="card">
                        <h2>Register</h2>
                        <?php if (isset($registerError)): ?>
                            <div class="alert alert-danger">
                                <?php echo $registerError; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($registerSuccess)): ?>
                            <div class="alert alert-success">
                                <?php echo $registerSuccess; ?>
                            </div>
                        <?php endif; ?>
                        <form action="account.php" method="post">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                            <div class="form-group">
                                <label for="reg-email">Email</label>
                                <input type="email" id="reg-email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="reg-password">Password</label>
                                <input type="password" id="reg-password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="register" class="btn">Register</button>
                        </form>
                    </div>
                </div>
                
                <div class="user-dashboard">
                    <div class="card">
                        <h2>Welcome to KR's Tech</h2>
                        <div class="empty-state">
                            <p>Please login or register to access your account.</p>
                            <p>As a registered user, you can:</p>
                            <ul style="text-align: left; margin-left: 20px; margin-bottom: 20px;">
                                <li>Track your orders</li>
                                <li>Save your shipping addresses</li>
                                <li>Create a wishlist</li>
                                <li>Get personalized recommendations</li>
                                <li>And much more!</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="user-dashboard">
                    <div class="card">
                        <h2>My Account</h2>
                        <div class="profile-info">
                            <p><strong>Name:</strong> <?php echo $_SESSION['user_name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $_SESSION['user_email']; ?></p>
                            
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <div style="margin: 20px 0;">
                                <p><strong>Admin Access:</strong> <span style="color: #27ae60;">Enabled</span></p>
                                <a href="admin.php" class="btn" style="margin-right: 10px;">Admin Dashboard</a>
                            </div>
                            <?php endif; ?>
                            
                            <a href="account.php?logout=true" class="btn btn-secondary">Logout</a>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h2>My Orders</h2>
                        <?php if (count($orders) > 0): ?>
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $order['status']; ?></td>
                                            <td>$<?php echo number_format($order['total'], 2); ?></td>
                                            <td><a href="order-details.php?id=<?php echo $order['id']; ?>">View</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>You haven't placed any orders yet.</p>
                                <a href="products.php" class="btn">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card">
                        <h2>My Addresses</h2>
                        <div class="empty-state">
                            <p>No saved addresses found.</p>
                            <button class="btn">Add New Address</button>
                        </div>
                    </div>
                </div>
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
</body>
</html>