<?php
// Use an absolute path to include config.php from the project root
require_once dirname(__DIR__) . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YogoFura - Local Yoghurt-Fura Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9e8d0;
            color: #4a2c2a;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #f4a261;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
                <img src="<?php echo BASE_URL; ?>assets/images/Logo .jpg" alt="Logo">
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
    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>