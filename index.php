<?php
// Include config.php at the top to ensure session_start() happens before output
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TheFresh.Corner - Taste Authentic Yoghurt-Fura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9e8d0;
            color: #4a2c2a;
        }
        .navbar {
            background-color: #f49961ff;
            padding: 5px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            color: #fff !important;
            font-weight: bold;
            margin-left: -20px;
        }
        .navbar-brand img {
            width: 60px;
            height: 60px;
            margin-right: 10px;
            border-radius: 50%;
        }
        .nav-link {
            color: #fff !important;
            margin-left: 15px;
        }
        .nav-link:hover {
            color: #e76f51 !important;
        }
        .hero {
            background-image: url('assets/images/heroBg.png');
            background-size: cover;
            background-position: center;
            height: 600px;
            position: relative;
            color: #fff;
            text-align: center;
            padding-top: 150px;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        .btn-custom {
            background-color: #f4a261;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-custom:hover {
            background-color: #e76f51;
            color: #fff;
        }
        .stats {
            background-color: #fff;
            padding: 40px 0;
            text-align: center;
        }
        .stats .col-md-3 {
            margin-bottom: 20px;
        }
        .stats h3 {
            font-size: 2.5rem;
            color: #e76f51;
        }
        .stats p {
            font-size: 1rem;
            color: #4a2c2a;
        }
        .why-choose, .meet-vendors {
            padding: 60px 0;
            text-align: center;
            background-color: #f9e8d0;
        }
        .why-choose h2, .meet-vendors h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .why-choose p, .meet-vendors p {
            font-size: 1.1rem;
            color: #6b4e31;
            max-width: 600px;
            margin: 0 auto 40px;
        }
        .feature-card, .vendor-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            transition: transform 0.3s;
        }
        .feature-card:hover, .vendor-card:hover {
            transform: scale(1.05);
        }
        .feature-card i, .vendor-card i {
            font-size: 2rem;
            color: #f4a261;
            margin-bottom: 10px;
        }
        .vendor-card img {
            border-radius: 10px;
            max-width: 100%;
            height: auto;
        }
        .vendor-card ul {
            list-style: none;
            padding-left: 0;
            text-align: left;
            margin-top: 20px;
        }
        .vendor-card ul li {
            margin-bottom: 10px;
        }
        .vendor-card ul li i {
            margin-right: 10px;
            color: #e76f51;
        }
        .footer {
            background-color: #4a2c2a;
            color: #fff;
            padding: 40px 0;
            text-align: center;
        }
        .footer h5 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .footer p {
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .footer .btn-signin {
            background-color: #4a2c2a;
            color: #fff;
            border: 1px solid #fff;
            padding: 8px 15px;
            transition: background-color 0.3s;
        }
        .footer .btn-signin:hover {
            background-color: #3c231f;
            color: #fff;
        }
        .footer .social-icons a {
            color: #fff;
            margin: 0 10px;
            font-size: 1.5rem;
            transition: color 0.3s;
        }
        .footer .social-icons a:hover {
            color: #f4a261;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
                <img src="assets\images\Logo .jpg" alt="TheFresh.Corner Logo">
                TheFresh.Corner
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a>
                <a class="nav-link" href="#vendors">Vendors</a>
                <a class="nav-link" href="#about">About</a>
                <a class="nav-link" href="#contact">Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="<?php echo BASE_URL; ?><?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a>
                    <a class="nav-link" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo BASE_URL; ?>auth/login.php">Sign In</a>
                    <a class="nav-link" href="<?php echo BASE_URL; ?>auth/register.php">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1>Taste Authentic Yoghurt-Fura</h1>
            <p>Connect with local Nigerian vendors and enjoy fresh, traditional Yoghurt-Fura delivered right to your doorstep.</p>
            <a href="<?php echo BASE_URL; ?>customer/dashboard.php" class="btn btn-custom mx-2">Order Now</a>
            <a href="<?php echo BASE_URL; ?>auth/register.php?role=vendor" class="btn btn-custom mx-2">Become a Vendor</a>
        </div>
    </section>

    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h3>50+</h3>
                    <p>Verified Vendors</p>
                </div>
                <div class="col-md-3">
                    <h3>1000+</h3>
                    <p>Happy Customers</p>
                </div>
                <div class="col-md-3">
                    <h3>24/7</h3>
                    <p>Service Available</p>
                </div>
                <div class="col-md-3">
                    <h3>5 ⭐</h3>
                    <p>Average Rating</p>
                </div>
            </div>
        </div>
    </section>

    <section class="why-choose" id="about">
        <div class="container">
            <h2>Why Choose TheFresh.Corner?</h2>
            <p>We're bringing traditional Nigerian flavors to the digital age with modern convenience and trusted quality.</p>
            <div class="row">
                <div class="col-md-3">
                    <div class="feature-card">
                        <i class="fas fa-cart-plus"></i>
                        <h4>Easy Ordering</h4>
                        <p>Browse local vendors and order your favorite Yoghurt-Fura with just a few clicks.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card">
                        <i class="fas fa-truck"></i>
                        <h4>Quick Delivery</h4>
                        <p>Get your fresh Yoghurt-Fura delivered fast or schedule for pickup at your convenience.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Trusted Vendors</h4>
                        <p>All vendors are verified and approved to ensure quality and authenticity.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <h4>Local Network</h4>
                        <p>Supporting local Nigerian vendors and bringing traditional flavors to your doorstep.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="meet-vendors" id="vendors">
        <div class="container">
            <h2>Meet Our Local Vendors</h2>
            <p>We partner with experienced local vendors who have been serving authentic Yoghurt-Fura for generations. Each vendor is carefully vetted to ensure the highest quality and authentic taste.</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="vendor-card">
                        <ul>
                            <li><i class="fas fa-check-circle"></i> Verified and approved vendors only</li>
                            <li><i class="fas fa-award"></i> Quality guaranteed with every order</li>
                            <li><i class="fas fa-map-marker-alt"></i> Supporting local Nigerian businesses</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>customer/dashboard.php" class="btn btn-custom mt-3">Explore Vendors</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="vendor-card">
                        <img src="assets\images\vendor.png" alt="Local Vendors">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer" id="contact">
        <div class="container">
            <h5>Experience the Tradition & Taste of YogoFura</h5>
            <p>Ready for authentic, fresh Yoghurt-Fura? Start your order today and join thousands of customers who trust our local vendors to deliver Nigerian flavors to your doorstep with modern technology and support since 2024.</p>
            <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-signin mt-3">Sign In</a>
            <div class="social-icons mt-3">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></i></a>
                <a href="https://www.instagram.com/thefresh.corner?igsh=MWRwaHMycWRxNm5xZw=="><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
            <div class ="text-center mt-4">
                 © <?php echo date("Y"); ?> TheFresh.Corner - All Rights Reserved
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>