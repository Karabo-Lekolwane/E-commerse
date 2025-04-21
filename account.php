<?php
// Your PHP logic [unchanged from your original post]
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ... keep existing PHP logic EXACTLY as you have it above (including session, DB, etc.) ...

// Load PHPMailer
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
} else {
    die("Error: Composer autoloader not found. Please run 'composer install'");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Database connection
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "ecommerse";
$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user details update
if (isset($_POST['update_details']) && $isLoggedIn) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    
    $sql = "UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            address = ?, 
            city = ?, 
            state = ?, 
            zip = ?, 
            country = ?, 
            phone = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $address, $city, $state, $zip, $country, $phone, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $detailsSuccess = "Details updated successfully!";
        // Update session name
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;

        // Send email notification
        sendUpdateEmail(
            $_SESSION['user_email'],
            $_SESSION['user_name'],
            "personal details"
        );
    } else {
        $detailsError = "Error updating details: " . $stmt->error;
    }
}

// Handle payment method update
if (isset($_POST['update_payment']) && $isLoggedIn) {
    $card_name = $_POST['card_name'];
    $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
    $card_expiry = $_POST['card_expiry'];
    $card_cvv = $_POST['card_cvv'];
    
    // Basic validation
    if (strlen($card_number) < 15 || strlen($card_number) > 16) {
        $paymentError = "Invalid card number";
    } else {
        // Get card type
        $card_type = '';
        if (preg_match('/^4/', $card_number)) {
            $card_type = 'Visa';
        } elseif (preg_match('/^5[1-5]/', $card_number)) {
            $card_type = 'Mastercard';
        } elseif (preg_match('/^3[47]/', $card_number)) {
            $card_type = 'American Express';
        }
        
        // Store only last 4 digits
        $card_last_four = substr($card_number, -4);
        
        $sql = "UPDATE users SET 
                card_name = ?, 
                card_last_four = ?, 
                card_expiry = ?, 
                card_type = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $card_name, $card_last_four, $card_expiry, $card_type, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $paymentSuccess = "Payment method updated successfully!";
            // Send email notification
            sendUpdateEmail(
                $_SESSION['user_email'],
                $_SESSION['user_name'],
                "payment method"
            );
        } else {
            $paymentError = "Error updating payment method: " . $stmt->error;
        }
    }
}

// Get current user details
$userDetails = [];
if ($isLoggedIn) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userDetails = $result->fetch_assoc();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Function to send update email
function sendUpdateEmail($userEmail, $userName, $updateType) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'krs.tech.store@gmail.com';
        $mail->Password   = 'ftqgwwkkjhfgjxbz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Recipients
        $mail->setFrom('krs.tech.store@gmail.com', "KR's Tech Store");
        $mail->addAddress($userEmail, $userName);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = "Your KR's Tech Account Was Updated";
        
        $message = "🔐 Account Update Confirmation\n\n";
        $message .= "Hello $userName,\n";
        $message .= "Your $updateType at KR's Tech Store has been successfully updated.\n\n";
        $message .= "If you didn't make this change, please contact us immediately at krs.tech.store@gmail.com\n\n";
        $message .= "Thanks for shopping with us!\n";
        $message .= "KR's Tech Store Team\n";
        $message .= "www.yourstore.com";
        
        $mail->Body = $message;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

$conn->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - KR's Tech</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #95a5a6;
            --secondary-dark: #7f8c8d;
            --card-bg: #fff;
            --card-border: #ebebeb;
            --gray: #8e9196;
            --background: #f5f5f5;
            --nav-bg: #2c3e50;
            --nav-link-hover: #3498db;
            --success: #d4edda;
            --success-border: #c3e6cb;
            --success-text: #155724;
            --danger: #f8d7da;
            --danger-border: #f5c6cb;
            --danger-text: #721c24;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 16px; }

        header {
    background-color: var(--nav-bg); /* Changed from #2c3e50 to variable */
    color: #fff;
    padding: 22px 0 15px 0; /* Changed from 15px 0 to asymmetric padding */
    position: sticky;
    top: 0;
    z-index: 101; /* Changed from 100 to 101 */
    box-shadow: 0 2px 6px rgba(44,62,80,0.05); /* Added box shadow */
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem; /* Added gap */
}

.logo {
    font-size: 2rem; /* Added logo styling */
    font-weight: 700;
    letter-spacing: 2px;
}

.logo span {
    color: var(--primary); /* Added colored span */
}

nav ul {
    display: flex;
    gap: 20px; /* Changed from margin to gap */
    list-style: none;
}

nav ul li a {
    color: #fff;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    transition: color .3s, border-bottom-color .3s; /* Added transitions */
    border-bottom: 2px solid transparent;
    padding-bottom: 2px;
}

nav ul li a:hover,
nav ul li a.active {
    color: var(--nav-link-hover);
    border-bottom: 2px solid var(--nav-link-hover);
}

/* Responsive adjustments - changed from 768px to 700px */
@media (max-width: 700px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start; /* Changed from center to flex-start */
        gap: 0.5rem; /* Added smaller gap */
    }
    
    nav ul {
        margin-top: 0; /* Removed the 15px margin */
        flex-wrap: wrap; /* Added wrapping */
        gap: 10px; /* Smaller gap for mobile */
        justify-content: flex-start; /* Changed from center */
    }
    
    /* Remove the unrelated product grid styles from here */
}

        .account-container { display: flex; gap: 32px; margin: 42px 0; flex-wrap: wrap; }
        .user-dashboard { flex: 1 1 300px; min-width: 300px; }
        .card {
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.06);
            padding: 30px 26px 26px 26px;
            margin-bottom: 28px;
            border: 1px solid var(--card-border);
            transition: box-shadow 0.2s;
        }
        .card h2, .card h3 { color: var(--nav-bg); border-bottom: 2px solid #f2f2f2; padding-bottom: 8px; margin-bottom: 20px; font-weight: 600;}
        .profile-info { margin-bottom: 24px; }
        .profile-info p { margin-bottom: 8px; color: #555; font-size: 1rem;}
        .profile-info p strong { color: var(--nav-bg);}
        .btn, .payment-btn, .proceed-btn {
            display: inline-block; padding: 11px 20px; margin: 0 3px;
            border: none; border-radius: 6px; font-weight: 600; font-size: 1rem;
            cursor: pointer; 
            background: var(--primary); color: #fff; outline: none;
            box-shadow: 0 1px 4px rgba(44,62,80,0.05);
            transition: background .2s, transform .15s, box-shadow .2s;
            text-decoration: none; text-align: center;
        }
        .btn:hover, .payment-btn:hover, .proceed-btn:hover { background: var(--primary-dark); transform: scale(1.03);}
        .btn-secondary { background: var(--secondary); color: #fff;}
        .btn-secondary:hover { background: var(--secondary-dark);}
        .admin-access { color: #27ae60; }

        .alert { padding: 12px 18px; margin-bottom: 15px; border-radius: 6px; font-size: 1rem;}
        .alert-success { background: var(--success); color: var(--success-text); border: 1px solid var(--success-border);}
        .alert-danger { background: var(--danger); color: var(--danger-text); border: 1px solid var(--danger-border);}

        .cart-table { width: 100%; border-collapse: collapse; background: #fafbfc; border-radius: 10px; }
        .cart-table th, .cart-table td { padding: 14px 8px; }
        .cart-table th { border-bottom: 2px solid #eee; text-align: left; font-weight: 700;}
        .cart-table td { border-bottom: 1px solid #eee; }
        .cart-empty { text-align: center; padding: 52px 0 26px 0;}
        .cart-empty p { font-size: 1.15rem; margin-bottom: 18px; color: #7f8c8d;}
        .product-img { width: 74px; height: 74px; object-fit: cover; border-radius: 6px; border: 1px solid #e0e0e0; background: #f9f9f9;}
        .product-name { font-weight: 600; color: var(--nav-bg);}
        .quantity-control { display: flex; align-items: center; gap: 4px; }
        .quantity-btn { width: 30px; height: 30px; background: #f2f6fa; border-radius: 4px; border: 1px solid #ddd; font-size: 17px; font-weight: 500;}
        .quantity-btn:hover { background: var(--primary); color: #fff;}
        .quantity-input { width: 42px; text-align: center; border: 1px solid #ddd; border-radius: 3px;}
        .remove-btn { color: #e74c3c; font-size: 1.1rem; background: none; border:none; cursor:pointer; font-weight: 600; }
        .remove-btn:hover { text-decoration: underline;}

        .cart-summary { display: flex; justify-content: flex-end; margin-top: 20px; }
        .cart-total { width: 320px; background: #f9f9f9; padding: 20px; border-radius: 10px; }
        .cart-total-row { display: flex; justify-content: space-between; margin-bottom: 9px;}
        .cart-total-label { font-weight: 700;}
        .grand-total { font-size: 1.2rem; margin-top: 9px; padding-top: 8px; border-top: 1px solid #ddd;}
        .continue-shopping { color: var(--primary); text-decoration: none; display: inline-block; margin-top: 22px; font-weight: 500;}
        .continue-shopping:hover { text-decoration: underline;}

        .details-form { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 1rem;}
        .form-section { margin-bottom: 25px;}
        .form-section h3 { color: var(--nav-bg); font-size: 1.1rem;}
        .form-row { margin-bottom: 15px; }
        .form-row label { display: block; margin-bottom: 7px; color: #555; font-size: 0.99rem;}
        .form-row input, .form-row select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;
            background: #fcfcfe; font-size: 1rem; outline: none;
            transition: border-color .2s;
        }
        .form-row input:focus, .form-row select:focus { border-color: var(--primary); }
        .form-actions { margin-top: 16px; display: flex; justify-content: flex-end; gap: 16px;}
        .saved-details {
            background: #f8f8fc;
            padding: 18px 18px 6px 18px;
            border-radius: 10px;
            margin-bottom: 28px; border: 1px solid #edeaf1;
        }
        .saved-details h4 { margin-bottom: 14px; color: var(--nav-bg);}
        .saved-details p { color: #444; margin-bottom: 7px;}
        .payment-methods { display: flex; gap: 15px; margin-bottom: 23px; flex-wrap: wrap; justify-content: flex-start; }
        .payment-method {
            border: 2px solid #eee;
            padding: 15px;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: all .18s;
            display: flex;
            align-items: center; gap: 12px;
            min-width: 170px;
        }
        .payment-method.active, .payment-method:focus { border-color: var(--primary); box-shadow: 0 0 0 2px #b7e1fc;}
        .payment-method img { height: 32px;}
        .card-icons { display: flex; gap: 10px; margin-left: 5px;}
        .card-icons img { height: 22px;}
        .button-group { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-top: 20px;}
        .payment-btn, .proceed-btn { min-width: 180px; }
        .proceed-btn { background: #4CAF50;} .proceed-btn:hover { background: #3e8e41;}
        @media (max-width:1000px) {
            .details-form { display: block; }
        }
        @media (max-width: 620px) {
            .container { padding: 0 4px;}
            .card { padding: 16px 7px;}
            .account-container { gap: 12px;}
        }
        @media (max-width: 490px) {
            .details-form { grid-template-columns: 1fr;}
            .cart-summary, .cart-total { width: 100%; }
        }
        footer {
            background: var(--nav-bg);
            color: white;
            padding: 45px 0 25px 0;
            margin-top: 56px;
            box-shadow: 0 -2px 9px rgba(44,62,80,0.05);
        }
        .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 25px;}
        .footer-column h3 { font-size: 1.11rem; color: #e1e7ee; margin-bottom: 15px; font-weight: 600;}
        .footer-column ul { list-style: none; }
        .footer-column ul li { margin-bottom: 9px;}
        .footer-column ul li a { color: #bdc3c7; text-decoration: none;}
        .footer-column ul li a:hover { color: var(--primary);}
        .copyright { text-align: center; margin-top: 30px; padding-top: 17px; border-top: 1px solid #34495e; font-size: 0.98rem;}

        .cart-count {
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            margin-left: 5px;
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
                        <li>
                            <a href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                Cart <span id="cart-count" class="cart-count">0</span>
                            </a>
                        </li>
                        <li>
                            <a href="account.php">
                                <i class="fas fa-user"></i>
                                Account
                            </a>
                        </li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="account-container">
            <div class="user-dashboard">
                <div class="card">
                    <h2>My Account</h2>
                    <div class="profile-info">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <div style="margin: 20px 0;">
                                <p><strong>Admin Access:</strong> <span class="admin-access">Enabled</span></p>
                                <a href="admin.php" class="btn" style="margin-right: 10px;">Admin Dashboard</a>
                            </div>
                        <?php endif; ?>
                        <a href="account.php?logout=true" class="btn btn-secondary">Logout</a>
                    </div>
                </div>

                <!-- CART -->
                <div class="card">
                    <h2>My Cart</h2>
                    <?php if (empty($_SESSION['cart'])): ?>
                        <div class="cart-empty">
                            <p>Your cart is empty</p>
                            <a href="products.php" class="btn">Continue Shopping</a>
                        </div>
                    <?php else: ?>
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th><th>Name</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                                <tr>
                                    <td>
                                        <a href="productdetails.php?id=<?php echo urlencode($item['id']); ?>">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-img">
                                        </a>
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
                                <div class="cart-total-row"><span class="cart-total-label">Subtotal:</span><span>R<?php echo number_format($total, 2); ?></span></div>
                                <div class="cart-total-row"><span class="cart-total-label">Shipping:</span><span>R0.00</span></div>
                                <div class="cart-total-row"><span class="cart-total-label">VAT:</span><span>R<?php echo number_format($total * 0.1, 2); ?></span></div>
                                <div class="cart-total-row grand-total"><span class="cart-total-label">Total:</span><span>R<?php echo number_format($total + ($total * 0.1), 2); ?></span></div>
                            </div>
                        </div>
                        <a href="products.php" class="continue-shopping">Continue Shopping</a>
                    <?php endif; ?>
                </div>

                <!-- DETAILS FORM -->
                <div class="card">
                    <h2>My Details</h2>
                    <?php if (isset($detailsSuccess)): ?>
                        <div class="alert alert-success"><?php echo $detailsSuccess; ?></div>
                    <?php endif; ?>
                    <?php if (isset($detailsError)): ?>
                        <div class="alert alert-danger"><?php echo $detailsError; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($userDetails['address']) || !empty($userDetails['phone'])): ?>
                        <div class="saved-details">
                            <h4>Saved Contact Information</h4>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?></p>
                            <?php if (!empty($userDetails['address'])): ?>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($userDetails['address']); ?></p>
                                <p><strong>City:</strong> <?php echo htmlspecialchars($userDetails['city']); ?></p>
                                <p><strong>State:</strong> <?php echo htmlspecialchars($userDetails['state']); ?></p>
                                <p><strong>ZIP:</strong> <?php echo htmlspecialchars($userDetails['zip']); ?></p>
                                <p><strong>Country:</strong> <?php echo htmlspecialchars($userDetails['country']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($userDetails['phone'])): ?>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($userDetails['phone']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <form action="account.php" method="post" class="details-form">
                        <div class="form-section">
                            <h3>Personal Information</h3>
                            <div class="form-row">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($userDetails['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($userDetails['last_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="email">Email</label>
                                <input type="tel" id="email" name="email" value="<?php echo htmlspecialchars($userDetails['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userDetails['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-section">
                            <h3>Shipping Address</h3>
                            <div class="form-row">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($userDetails['address'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($userDetails['city'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($userDetails['state'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="zip">ZIP/Postal Code</label>
                                <input type="text" id="zip" name="zip" value="<?php echo htmlspecialchars($userDetails['zip'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="country">Country</label>
                                <select id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="South Africa" <?php echo (isset($userDetails['country']) && $userDetails['country'] === 'South Africa' ? 'selected' : ''); ?>>South Africa</option>
                                    <option value="Lesotho" <?php echo (isset($userDetails['country']) && $userDetails['country'] === 'Lesotho' ? 'selected' : ''); ?>>Lesotho</option>
                                    <option value="Eswatini" <?php echo (isset($userDetails['country']) && $userDetails['country'] === 'Eswatini' ? 'selected' : ''); ?>>Eswatini</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="update_details" class="btn">Save Details</button>
                        </div>
                    </form>
                    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

                    <?php if (isset($paymentSuccess)): ?>
                        <div class="alert alert-success"><?php echo $paymentSuccess; ?></div>
                    <?php endif; ?>
                    <?php if (isset($paymentError)): ?>
                        <div class="alert alert-danger"><?php echo $paymentError; ?></div>
                    <?php endif; ?>

                    <h3>Payment Method</h3>
                    <div class="payment-methods">
                        <div class="payment-method active" id="credit-card-method" tabindex="0">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa">
                            <div class="card-icons">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/American_Express_logo_%282018%29.svg" alt="American Express">
                            </div>
                        </div>
                        <div class="payment-method" id="paypal-method" tabindex="0">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" style="height: 32px;">
                        </div>
                    </div>
                    <div class="saved-details">
                        <?php if (!empty($userDetails['card_last_four'])): ?>
                            <h4>Saved Payment Method</h4>
                            <p><strong>Card Type:</strong> <?php echo htmlspecialchars($userDetails['card_type']); ?></p>
                            <p><strong>Name on Card:</strong> <?php echo htmlspecialchars($userDetails['card_name']); ?></p>
                            <p><strong>Card Number:</strong> **** **** **** <?php echo htmlspecialchars($userDetails['card_last_four']); ?></p>
                            <p><strong>Expires:</strong> <?php echo htmlspecialchars($userDetails['card_expiry']); ?></p>
                        <?php else: ?>
                            <p>No payment method saved yet.</p>
                        <?php endif; ?>
                    </div>
                    <form action="account.php" method="post" class="details-form payment-form" style="margin-bottom:0;">
                        <div class="form-section">
                            <div class="form-row">
                                <label for="card_name">Name on Card</label>
                                <input type="text" id="card_name" name="card_name" value="<?php echo htmlspecialchars($userDetails['card_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" value="" required>
                                <small style="color:#888;">Full card number is not stored for security</small>
                            </div>
                        </div>
                        <div class="form-section">
                            <div class="form-row">
                                <label for="card_expiry">Expiration Date</label>
                                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" value="<?php echo htmlspecialchars($userDetails['card_expiry'] ?? ''); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="card_cvv">Security Code (CVV)</label>
                                <input type="text" id="card_cvv" name="card_cvv" value="" required>
                                <small style="color:#888;">CVV is not stored for security</small>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="button-group">
                                <button type="submit" name="update_payment" class="btn payment-btn">
                                    Update Payment Method
                                </button>
                                <button type="button" onclick="window.location.href='payment.php'" class="btn payment-btn proceed-btn">
                                    Proceed with Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div><!--./user-dashboard-->
        </div><!--./account-container-->
    </div><!--./container-->
    <script>
    // Toast notification functions
    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        
        toastMessage.textContent = message;
        toast.classList.add('show');
        
        // Auto-hide after 3 seconds
        setTimeout(hideToast, 3000);
    }
    
    function hideToast() {
        document.getElementById('toast').classList.remove('show');
    }

    // Function to update cart count
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('cart-count').textContent = data.count;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Update cart count on page load
    updateCartCount();

    // Payment method toggle (credit card <-> PayPal)
    document.addEventListener('DOMContentLoaded', function() {
        const creditCardMethod = document.getElementById('credit-card-method');
        const paypalMethod = document.getElementById('paypal-method');
        const paymentForm = document.querySelector('.payment-form');
        const updateButton = document.querySelector('button[name="update_payment"]');
        const proceedButton = document.querySelector('.proceed-btn');
        const paymentFields = document.querySelectorAll('.payment-form .form-section');

        function showPaypalOnly() {
            paymentFields.forEach(field => field.style.display = 'none');
            updateButton.style.display = 'none';
            proceedButton.style.display = 'block';
            proceedButton.textContent = 'Proceed with PayPal';
        }
        
        function showCreditCardForm() {
            paymentFields.forEach(field => field.style.display = 'block');
            updateButton.style.display = 'block';
            proceedButton.style.display = 'block';
            proceedButton.textContent = 'Proceed with Payment';
        }
        
        if (creditCardMethod && paypalMethod) {
            creditCardMethod.addEventListener('click', function() {
                creditCardMethod.classList.add('active');
                paypalMethod.classList.remove('active');
                showCreditCardForm();
            });
            
            paypalMethod.addEventListener('click', function() {
                paypalMethod.classList.add('active');
                creditCardMethod.classList.remove('active');
                showPaypalOnly();
            });
            
            // Set initial state
            showCreditCardForm();
        }
    });
</script>
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
