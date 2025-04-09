<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - KR's Tech</title>
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
        
        .page-title {
            text-align: center;
            margin: 40px 0;
        }
        
        .page-title h1 {
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .page-title p {
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .contact-info {
            flex: 1;
            min-width: 300px;
        }
        
        .contact-form {
            flex: 2;
            min-width: 300px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            height: 100%;
        }
        
        .card h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 10px;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 25px;
        }
        
        .info-item .icon {
            width: 50px;
            height: 50px;
            background-color: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-right: 15px;
        }
        
        .info-item .content h3 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .info-item .content p {
            color: #7f8c8d;
        }
        
        .social-links {
            display: flex;
            margin-top: 30px;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background-color: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .social-links a:hover {
            background-color: #2980b9;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 150px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .map-container {
            height: 400px;
            margin-bottom: 60px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: 0;
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
            
            .form-row {
                flex-direction: column;
                gap: 0;
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
        <div class="page-title">
            <h1>Contact Us</h1>
            <p>Have questions? We'd love to hear from you!</p>
        </div>

        <div class="contact-container">
            <div class="contact-info">
                <div class="card">
                    <h2>Get In Touch</h2>
                    
                    <div class="info-item">
                        <div class="icon">📍</div>
                        <div class="content">
                            <h3>Our Location</h3>
                            <p>123 Tech Street, Silicon Valley, CA 94043, USA</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">📞</div>
                        <div class="content">
                            <h3>Phone Number</h3>
                            <p>+1 (555) 123-4567</p>
                            <p>+1 (555) 987-6543</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">✉️</div>
                        <div class="content">
                            <h3>Email Address</h3>
                            <p>support@krtech.com</p>
                            <p>sales@krtech.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">⏰</div>
                        <div class="content">
                            <h3>Working Hours</h3>
                            <p>Monday - Friday: 9:00 AM - 4:00 PM</p>
                            <p>Saturday: 10:00 AM - 3:00 PM</p>
                            <p>Sunday: Closed</p>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <a href="#" title="Facebook">f</a>
                        <a href="#" title="Twitter">t</a>
                        <a href="#" title="Instagram">i</a>
                        <a href="#" title="LinkedIn">in</a>
                        <a href="#" title="YouTube">yt</a>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <div class="card">
                    <h2>Send Us A Message</h2>
                    
                    <?php if (isset($_POST['submit'])): ?>
                        <div class="alert alert-success">
                            Thank you for your message! We'll get back to you shortly.
                        </div>
                    <?php endif; ?>
                    
                    <form action="contact.php" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Your Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Your Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="inquiry-type">Inquiry Type</label>
                            <select id="inquiry-type" name="inquiry_type" required>
                                <option value="">Select an option</option>
                                <option value="general">General Inquiry</option>
                                <option value="support">Technical Support</option>
                                <option value="returns">Returns & Exchanges</option>
                                <option value="feedback">Product Feedback</option>
                                <option value="business">Business Inquiry</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        
                        <button type="submit" name="submit" class="btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3168.6396782294627!2d-122.0843652123092!3d37.42210066624173!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x808fba027f087f5f%3A0xd86b53a4c2fb9228!2sGoogle!5e0!3m2!1sen!2sus!4v1635420309915!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
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

    <?php
    // Process form submission
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $subject = $_POST['subject'];
        $inquiry_type = $_POST['inquiry_type'];
        $message = $_POST['message'];
        
        // This would normally be where you would process the form
        // e.g., send an email, save to database, etc.
        
        // For example, to send an email (this won't actually work in this environment):
        /*
        $to = "support@krtech.com";
        $headers = "From: $email";
        $email_subject = "Contact Form: $subject ($inquiry_type)";
        $email_body = "You have received a new message from your website contact form.\n\n"."Here are the details:\n\nName: $name\n\nEmail: $email\n\nInquiry Type: $inquiry_type\n\nMessage:\n$message";
        
        mail($to, $email_subject, $email_body, $headers);
        */
        
        // Redirect to prevent form resubmission
        // header("Location: contact.php?status=success");
        // exit;
    }
    ?>
</body>
</html>